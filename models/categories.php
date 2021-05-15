<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class BreezingcommerceModelCategories extends JModelLegacy
{
    protected $active = null;
    protected $product_categories = array();
    protected $subcategories = array();
    
    function  __construct($config)
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();
        $option = 'com_breezingcommerce';
        
        $menu = JFactory::getApplication()->getMenu();
        $active = $menu->getActive();
        $this->active = $active;
        
        $config = CrBcHelpers::getBcConfig();
        
        if( isset($this->active->params) && $this->active->params->get('override_default', 0) == 0 ){
            $this->active->params->merge($config);
        }
        
        $filter = JFactory::getSession()->get('com_breezingcommerce.module_filter', null);
        $filter_settings = JFactory::getSession()->get('com_breezingcommerce.filter_settings', null);
        
        if(isset($filter_settings['params']) && $filter_settings['params'] !== null){
            
            $the_params = new JRegistry();
            $the_params->loadString($filter_settings['params']);
            
            if($the_params->get('override_default',0) == 1){
                if(isset($this->active->params)){
                    $this->active->params->merge($the_params);
                }
            }
            if(isset($this->active->params)){
                $this->active->params->merge($filter_settings['params']);
            }
        }
        
        if(is_object($active)){
            $this->setId($active->params->get('parent_category_id'));
        }
        
        if( JRequest::getInt('parent_category_id', 0) != 0 ){
            $this->setId(JRequest::getInt('parent_category_id', 0));
        }
        
        if(!$this->getId()){
            throw new Exception(JText::_('COM_BREEZINGCOMMERCE_CATEGORY_NOT_FOUND'), 404);
        }
        
        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }
    
    function getShowPageHeading(){
        
        if(is_object($this->active)){
            return $this->active->params->get('show_page_heading', 0);
        }
        
        return false;
    }
    
    function getPageTitle(){
        if(is_object($this->active)){
            return $this->active->params->get('page_title', '');
        }else{
            return '';
        }
    }
    
    function getLinkTitle(){
        
        if(is_object($this->active)){
            return $this->active->params->get('link_title', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getDisplayText(){
        
        if(is_object($this->active)){
            return $this->active->params->get('display_text', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getDisplayImage(){
        
        if(is_object($this->active)){
            return $this->active->params->get('display_image', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getLinkCategoryTitle(){
        
        if(is_object($this->active)){
            return $this->active->params->get('link_category_title', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getDisplayCategoryText(){
        
        if(is_object($this->active)){
            return $this->active->params->get('display_category_text', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getDisplayCategoryImage(){
        
        if(is_object($this->active)){
            return $this->active->params->get('display_category_image', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getMenuTitle(){
        
        if(is_object($this->active)){
            return $this->active->title;
        }
        
        return false;
    }
    
    function getTotal() {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->getCategoriesQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }
    
    function getPagination() {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
        }
        return $this->_pagination;
    }
    
    function getColumnBreak(){
        if(is_object($this->active)){
            return $this->active->params->get('column_break', 3);
        }else{
            return 3;
        }
    }
    
    function getPageHeading(){
        if(is_object($this->active)){
            return $this->active->params->get('page_heading', '');
        }else{
            return '';
        }
    }
    
    function getPageClass(){
        if(is_object($this->active)){
            return $this->active->params->get('pageclass_sfx', '');
        }else{
            return '';
        }
    }
    
    /**
     * Only call after this::getCategories()
     * 
     * @param int $cat_id
     */
    function categoryHasProducts($cat_id){
        
        foreach( $this->product_categories As $product_category ){
            if( isset( $product_category->category_id ) && $product_category->category_id == $cat_id ){
                return true;
            }
        }
        
        return false;
    }
    
    function categoryHasSubcategories($cat_id){
        
        foreach( $this->subcategories As $subcategories ){
            if( isset( $subcategories->id ) && $subcategories->id == $cat_id ){
                return true;
            }
        }
        
        return false;
    }

    /**
     *
     * @param int $id 
     */
    function setId($id) {
        // Set id and wipe data
        $this->_id      = $id;
        $this->_data    = null;
    }

    function getId(){
        return $this->_id;
    }
    
    function getCategoriesQuery(){
        
        // make sure not to show categories that haven't been translated at least partially
        $check_for_translation_tbl = '';
        $check_for_translation_lookup = '';

        $default = JComponentHelper::getParams('com_languages')->get('site');
        if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){

            $check_for_translation_tbl = ' Left Join #__breezingcommerce_translations As trns ';
            $check_for_translation_tbl .= ' On c.id = trns.item_id And trns.language_code = ' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ' And `type` = "category" ';
            $check_for_translation_lookup = ' And trns.item_id Is Not Null And ( trns.title <> "" Or trns.alias <> "" Or trns.body <> "" ) ';
        }
        
        return "
            Select
                c.*
            From
                #__breezingcommerce_categories As c $check_for_translation_tbl
            Where
                c.parent_id = ".$this->_db->quote( $this->getId() )."
            $check_for_translation_lookup
            And
                c.published = 1
            Order By c.ordering
        ";
    }
    
    function getThisCategory(){
        
        $db = JFactory::getDbo();
        
        $db->setQuery( "
            Select
                *
            From
                #__breezingcommerce_categories
            Where
                id = ".$this->_db->quote( $this->getId() )."
            And
                published = 1
        ");
        
        $row = $db->loadObject();
        
        $row = CrBcHelpers::populateTranslation($row, 'category', array('title' => 'title', 'alias' => 'alias', 'body' => 'description'));
        
        return $row;
    }
    
    function getCategories(){
        
        
        $db = JFactory::getDbo();
        
        $db->setQuery($this->getCategoriesQuery(), $this->getState('limitstart'), $this->getState('limit'));
        
        $rows = $db->loadObjectList();
        
        if( count( $rows ) == 0 ){
            throw new Exception(JText::_('COM_BREEZINGCOMMERCE_CATEGORY_NOT_FOUND'), 404);
        }
        
        $cats = array();
        
        foreach( $rows As $row ){
            
            $cats[] = $row->id;
            
            $translated_object = CrBcHelpers::loadTranslation($row->id, 'category');
            if($translated_object !== null){
                $row->description = $translated_object->body;
                $row->title = $translated_object->title;
                $row->alias = $translated_object->alias;
            }
        }
        
        if(count( $cats ) > 0){
            
            $db->setQuery("Select c.* From #__breezingcommerce_product_categories As c, #__breezingcommerce_products As p Where c.category_id In (".implode(',',$cats).") And p.id = c.product_id And p.published = 1");
            $this->product_categories = $db->loadObjectList();
            
            $db->setQuery("Select parent_id As id From #__breezingcommerce_categories Where parent_id In (".implode(',',$cats).") And published = 1");
            $this->subcategories = $db->loadObjectList();
        }
        
        foreach( $rows As $row ){
            
            $row->has_products = $this->categoryHasProducts($row->id);
            $row->has_subcategories = $this->categoryHasSubcategories($row->id);
        }
        
        return $rows;
    }
}
