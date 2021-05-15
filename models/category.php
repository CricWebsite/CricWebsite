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

class BreezingcommerceModelCategory extends JModelLegacy
{
    static $BACKORDERS_DISALLOWED_VISIBLE = 'DISALLOWED_VISIBLE';
    static $BACKORDERS_DISALLOWED_HIDDEN = 'DISALLOWED_HIDDEN';
    
    protected $active = null;
    protected $and_or = true;
    protected $search = ''; // currently used for ajax live search
    protected $product_order = '';
    
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
        
        if(JRequest::getVar('view_type','') == 'list'){
            JFactory::getSession()->set('breezingcommerce.view_type', 'list');
        }else if(JRequest::getVar('view_type','') == 'block'){
            JFactory::getSession()->set('breezingcommerce.view_type', 'block');
        }
        
        if( JRequest::getVar('product_order',null) !== null ){
            JFactory::getSession()->set('breezingcommerce.product_order', JRequest::getVar('product_order',''));
        }
        
        $this->product_order = JFactory::getSession()->get('breezingcommerce.product_order', '');
        
        // sanitizing product order
        switch($this->product_order ){
            case 'name_a_z':
            case 'name_z_a':
            case 'price_high_to_low':
            case 'price_low_to_high':
                break;
            default:
                $this->product_order = '';
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
        
        // consistency check
        
        if( $filter !== null && $filter_settings !== null && isset($filter_settings['and_or']) && $filter_settings['and_or'] == 0 ){
           
            $this->and_or = false;
        }
        
        if( ( $filter !== null && $filter_settings !== null && isset($filter_settings['bind_to']) && $filter_settings['bind_to'] != 4 && isset($filter['categories']) && count($filter['categories']) == 0 ) && ( $filter !== null && isset($filter['search']) && isset($filter['search']['query']) && trim($filter['search']['query']) == '' ) ){

            $filter['price'] = array();
            $filter['brands'] = array();
            $filter['options'] = array();
            $filter['custom_options'] = array();
            $filter['attributes'] = array();
            $filter['search'] = array();
            $filter['categories'] = array();
            $filter['sales'] = false;
            $filter['stock'] = false;
            
            JFactory::getSession()->set('com_breezingcommerce.module_filter', $filter);
        }
        
        // set the current category as default in filter if no category has been choosen in the filter yet
        
        if( JRequest::getInt('filter_module_reload', 0) == 0 && JRequest::getInt('filter', 0) == 0 && ( $filter === null || !isset($filter['categories']) || count( $filter['categories'] ) < 2 ) ){
            
            $category_id = 0;
            
            if(is_object($active) && $active->params->get('category_id')){
                $category_id = $active->params->get('category_id');
            }

            if( JRequest::getInt('category_id', 0) != 0 ){
                $category_id = JRequest::getInt('category_id', 0);
            }
            
            if( JRequest::getVar('option','') == 'com_breezingcommerce' && ( JRequest::getVar('view','') == 'category' || JRequest::getVar('controller','') == 'category'  ) && $category_id > 0){
                $filter['categories'] = array();
                $filter['categories'][] = $category_id;
                JFactory::getSession()->set('com_breezingcommerce.module_filter', $filter);
            }
        }
        
        // on filter reset, well, reset the filter (and redirect if necessary)
        
        if( JRequest::getInt('filter_reset', 0) == 1 ){
            
            JRequest::setVar('filter', 0);
            
            JFactory::getSession()->clear('com_breezingcommerce.filter');
            JFactory::getSession()->clear('com_breezingcommerce.module_filter');
            JRequest::setVar('limit', 20);
            
            if( ( !is_object($active) || !$active->params->get('category_id') ) && JRequest::getInt('category_id', 0) == 0 ){
                
                JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMRCE_FILTER_RESET_MSG'), 'info');
                
                $return_url = '';
                if(trim(JRequest::getVar('return_url','')) != ''){
                    $return_url = trim(JRequest::getVar('return_url',''));
                    $return_url = CrBcHelpers::bSixtyFourDecode($return_url);
                    if(JUri::isInternal($return_url)){
                        JFactory::getApplication()->redirect(str_replace(array('&filter_module_reload=1','?filter_module_reload=1','&filter_module_clean=1'),'',$return_url));
                    }else{
                        JFactory::getApplication()->redirect('index.php');
                    }
                } else {
                    JFactory::getApplication()->redirect('index.php');
                }
            }
        }
        
        if( JRequest::getVar('livesearch', '') != '' ){
            
            $this->search = JRequest::getVar('livesearch', '');
            
            $this->setId(0);
           
        // if no filter is set at all, act as regular category
        } else if( JRequest::getInt('filter', 0) == 0 ){
        
            JFactory::getSession()->clear('com_breezingcommerce.filter');
            
            if(is_object($active) && $active->params->get('category_id')){
                $this->setId($active->params->get('category_id'));
            }

            if( JRequest::getInt('category_id', 0) != 0 ){
                $this->setId(JRequest::getInt('category_id', 0));
            }

            if(!$this->getId()){
                throw new Exception('COM_BREEZINGCOMMERCE_CATEGORY_NOT_FOUND', 404);
            }
        
        // if a filter has been set, act as search results panel
            
        } else {
            
            $filter = JFactory::getSession()->get('com_breezingcommerce.module_filter', array());
            
            // BOF Search
            if( JRequest::getVar('search',null) !== null ){
                
                $filter['search'] = JRequest::getVar('search', array(), 'post', 'array');

                if(!isset($filter['search']['query'])){
                    
                    $filter['search'] = array();
                    $filter['search']['query'] = '';
                    
                }else{
                    // sanitize
                    $filter['search']['query'] = trim(JFilterInput::getInstance()->clean($filter['search']['query']));
                }
                
            } else if( JRequest::getInt('filter_module_triggered', 0) == 1 ){
                
                // helps to clear the last remaining attribute
                //$filter['search'] = array();
                //$filter['search']['query'] = '';
            }
            // EOF Attributes
            
            // BOF Attributes
            if( JRequest::getVar('attributes',null) !== null ){
                
                $filter['attributes'] = JRequest::getVar('attributes', array(), 'post', 'array');
                JArrayHelper::toInteger($filter['attributes']);

            } else if( JRequest::getInt('filter_module_triggered', 0) == 1 ){
                // helps to clear the last remaining attribute
                $filter['attributes'] = array();
            }
            // EOF Attributes
            
            // BOF Options
            if( JRequest::getVar('options',null) !== null ){
                
                $filter['options'] = JRequest::getVar('options', array(), 'post', 'array');
                JArrayHelper::toInteger($filter['options']);

            } else if( JRequest::getInt('filter_module_triggered', 0) == 1 ){
                // helps to clear the last remaining option
                $filter['options'] = array();
            }
            // EOF Options
            
            // BOF CUSTOM Options
            if( JRequest::getVar('custom_options',null) !== null ){
                
                $filter['custom_options'] = JRequest::getVar('custom_options', array(), 'post', 'array');
                JArrayHelper::toInteger($filter['custom_options']);

            } else if( JRequest::getInt('filter_module_triggered', 0) == 1 ){
                // helps to clear the last remaining option
                $filter['custom_options'] = array();
            }
            // EOF CUSTOM Options
            
            // BOF Brands
            if( JRequest::getVar('brands',null) !== null ){
                
                $filter['brands'] = JRequest::getVar('brands', array(), 'post', 'array');
                JArrayHelper::toInteger($filter['brands']);

            } else if( JRequest::getInt('filter_module_triggered', 0) == 1 ){
                // helps to clear the last remaining brand option
                $filter['brands'] = array();
            }
            // EOF Brands
            
            // BOF Price
            if( JRequest::getVar('price',null) !== null ){
                
                $filter['price'] = JRequest::getVar('price', array(), 'post', 'array');
                
                if(!isset($filter['price']['from']) || !isset($filter['price']['to'])){
                    
                    $filter['price'] = array();
                    $filter['price']['from'] = 0;
                    $filter['price']['to'] = 0;
                    $filter['price']['which'] = '';
                    
                }else{
                    // sanitize
                    $filter['price']['from'] = doubleval($filter['price']['from']);
                    $filter['price']['to'] = doubleval($filter['price']['to']);
                    $filter['price']['which'] = $filter['price']['which'];
                }

            } else if( JRequest::getInt('filter_module_triggered', 0) == 1 ){
                // helps to clear the last remaining brand option
                $filter['price'] = array();
                $filter['price']['from'] = 0;
                $filter['price']['to'] = 0;
                $filter['price']['which'] = '';
                
            }
            // EOF Price
            
            // BOF Categories
            $categories = JRequest::getVar('categoriesfilterpicker',null);
            
            if( $categories !== null && isset( $categories['categoriesfilter'] ) && count( $categories['categoriesfilter'] ) > 0 ){
                
                $filter['categories'] = JRequest::getVar('categoriesfilterpicker', array(), 'post', 'array');
                $filter['categories'] = $filter['categories']['categoriesfilter'];
                JArrayHelper::toInteger($filter['categories']);
                
            } else if( JRequest::getInt('filter_module_triggered', 0) == 1 ){
                // helps to clear the last remaining brand option
                $filter['categories'] = array();
            }
            
            // EOF Categories
            
            // BOF Sales
            if( JRequest::getBool('sales',false) === true ){
                
                $filter['sales'] = JRequest::getBool('sales', false);

            } else if( JRequest::getInt('filter_module_triggered', 0) == 1 ){
                // helps to clear the last remaining brand option
                $filter['sales'] = false;
            }
            // EOF Sales
            
            // BOF Stock
            if( JRequest::getBool('stock',false) === true ){
                
                $filter['stock'] = JRequest::getBool('stock', false);

            } else if( JRequest::getInt('filter_module_triggered', 0) == 1 ){
                // helps to clear the last remaining brand option
                $filter['stock'] = false;
            }
            // EOF Stock
            
            JFactory::getSession()->set('com_breezingcommerce.filter', $filter);
            // used by the filter module. might be branched out.
            JFactory::getSession()->set('com_breezingcommerce.module_filter', $filter);
            
            $this->setId(0);
        }
        
        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }
    
    function getHideDetails(){
        
        if(is_object($this->active)){
            return $this->active->params->get('hide_details', 0) ? true : false;
        }
        
        return false;
    }
    
    function getCartAddButtonLocation(){
        
        if(is_object($this->active)){
            return $this->active->params->get('cart_add_button_location', 'bottom');
        }
        
        return 'bottom';
    }
    
    function getEnableAjaxCartAdd(){
        
        if(is_object($this->active)){
            
            return $this->active->params->get('enable_ajax_cart_add', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getEnableDynamicPrice(){
        
        if(is_object($this->active)){
            return $this->active->params->get('enable_dynamic_price', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getEnableCategoryDescription(){
        
        if(is_object($this->active)){
            return $this->active->params->get('enable_category_description', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getEnableSimpleProductView(){
        
        if(is_object($this->active)){
            return $this->active->params->get('enable_simple_product_view', 1) == 1 ? true : false;
        }
        
        return true;
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
    
    function getEnableSortBy(){
        
        if(is_object($this->active)){
            return $this->active->params->get('enable_sortby', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getEnableShowPerPage(){
        
        if(is_object($this->active)){
            return $this->active->params->get('enable_showperpage', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getEnableBlockView(){
        
        if(is_object($this->active)){
            return $this->active->params->get('enable_blockview', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getEnableListView(){
        
        if(is_object($this->active)){
            return $this->active->params->get('enable_listview', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getShowPageHeading(){
        
        if(is_object($this->active)){
            return $this->active->params->get('show_page_heading', 0);
        }
        
        return false;
    }
    
    function getTotal() {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->getProductsQuery();
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
    
    function getPageTitle(){
        if(is_object($this->active)){
            return $this->active->params->get('page_title', '');
        }else{
            return '';
        }
    }
    
    function getPageHeading(){
        
        if( JFactory::getSession()->get('com_breezingcommerce.filter', null) !== null ){
            
            return JText::_('COM_BREEZINGCOMMERCE_FILTER_RESULTS');
        }
        
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
    
    function getProductsQuery(){
        
        $config = CrBcHelpers::getBcConfig();
        
        if( JFactory::getSession()->get('com_breezingcommerce.filter', null) === null || $this->search != '' ){
        
            $price_tbl = '';
            $search_tbl = '';
            $search_lookup = '';
            $product_order = '';
            
            // that's for livesearch, we don't need no special ordering here except the default one
            if( $this->search != '' ){
                
                $default = JComponentHelper::getParams('com_languages')->get('site');
                if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){
                  
                    $search_tbl = ' Left Join #__breezingcommerce_translations As t ';
                    $search_tbl .= ' On  t.item_id = a.id And t.type = "product" And t.language_code = '. $this->_db->quote(JFactory::getLanguage()->getTag()) . ' ';

                    $search_lookup = ' And ( ' . CrBcHelpers::createMatchAgainstLookupSql($this->search, array('title','body'), 't', '');
                    $search_lookup .= CrBcHelpers::createMatchAgainstLookupSql($this->search, array('title','description_fulltext'), 'a', 'Or') . ' ) ';
                    
                } else {
                
                    $search_lookup = CrBcHelpers::createMatchAgainstLookupSql($this->search, array('title', 'description_fulltext'), 'a', 'And');
                    
                }
                
                $check_for_translation_tbl = '';
                $check_for_translation_lookup = '';
                $default = JComponentHelper::getParams('com_languages')->get('site');
                if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){

                    $check_for_translation_tbl = ' Left Join #__breezingcommerce_translations As trns ';
                    $check_for_translation_tbl .= ' On a.id = trns.item_id And trns.language_code = ' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ' And `type` = "product" ';
                    $check_for_translation_lookup = ' And trns.item_id Is Not Null And ( trns.title <> "" Or trns.alias <> "" Or trns.body <> "" ) ';
                }
                
                return "
                    Select
                        SQL_CALC_FOUND_ROWS
                        a.*
                    From
                        #__breezingcommerce_products As a
                        ".$check_for_translation_tbl."
                        ".$search_tbl.",
                        #__breezingcommerce_product_categories As c
                    Where
                        c.product_id = a.id
                    $check_for_translation_lookup
                    And 
                        a.published = 1
                    And
                        ( 
                            a.virtual_product = 1
                           Or
                           (
                                (
                                    a.virtual_product = 0
                                   And
                                    a.use_combinations = 0
                                   And
                                    a.stock <= 0
                                   And
                                    a.backorders <> ''
                                   And
                                    a.backorders = ".$this->_db->quote(self::$BACKORDERS_DISALLOWED_HIDDEN)."
                                ) Is False
                                And
                                (
                                    a.virtual_product = 0
                                   And
                                    a.use_combinations = 0
                                   And
                                    a.stock <= 0
                                   And
                                    a.backorders = ''
                                   And
                                    1 = ".( $config->get('backorders','') == self::$BACKORDERS_DISALLOWED_HIDDEN ? '1' : '2' )."
                                ) Is False
                           )
                           
                        )
                      
                    ".$search_lookup."
                    Order By
                        c.ordering
                ";
            }
            
            // for the regular results we need to incorporate the sort oder
            
            // Let's start with order by title
            $default = JComponentHelper::getParams('com_languages')->get('site');
            if( ($this->product_order == 'name_a_z' || $this->product_order == 'name_z_a') && count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){
                 
                $search_tbl = ' Left Join #__breezingcommerce_translations As t ';
                $search_tbl .= ' On  t.item_id = a.id And t.type = "product" And t.language_code = '. $this->_db->quote(JFactory::getLanguage()->getTag()) . ' ';

                $product_order = ' t.`title` ';
                
            } else {
                
                if( $this->product_order == 'name_a_z' || $this->product_order == 'name_z_a' ){

                    $product_order = ' a.`title` ';
                }
            }
            
            if( $this->product_order == 'name_z_a' ){
                $product_order .= ' Desc ';
            }
            
            // now we check for price based ordering
            
            if( ($this->product_order == 'price_high_to_low' || $this->product_order == 'price_low_to_high') ){
                 
                $config = CrBcHelpers::getBcConfig();
                $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );
                  
                $price_tbl .= ' Left Join #__breezingcommerce_group_price_map As gpm';
                $price_tbl .= ' On a.id = gpm.product_id And gpm.customergroup_id = ' . $this->_db->quote( $cart->group->id ). ' ';
                $price_tbl .= ' Left Join #__breezingcommerce_currency_price_map As cpm';
                $price_tbl .= ' On a.id = cpm.product_id And cpm.currency_id = ' . $this->_db->quote( $cart->currency_id ) . ' ';

                $product_order = ' COALESCE(gpm.price, cpm.price, a.price) ';

                if( $this->product_order == 'price_high_to_low' ){
                    $product_order .= ' Desc ';
                }
            }
            
            $check_for_translation_tbl = '';
            $check_for_translation_lookup = '';
            $default = JComponentHelper::getParams('com_languages')->get('site');
            if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){
                
                $check_for_translation_tbl = ' Left Join #__breezingcommerce_translations As trns ';
                $check_for_translation_tbl .= ' On a.id = trns.item_id And trns.language_code = ' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ' And `type` = "product" ';
                $check_for_translation_lookup = ' And trns.item_id Is Not Null And ( trns.title <> "" Or trns.alias <> "" Or trns.body <> "" ) ';
            }
            
            return "
                Select
                    SQL_CALC_FOUND_ROWS
                    a.*
                From
                    #__breezingcommerce_products As a
                    ".$check_for_translation_tbl."
                    ".$search_tbl."
                    ".$price_tbl.",
                    #__breezingcommerce_categories As b,
                    #__breezingcommerce_product_categories As c
                Where
                    b.id = ".intval($this->getId())."
                $check_for_translation_lookup
                And
                    b.id = c.category_id
                And
                    c.product_id = a.id
                And
                    b.published = 1
                And 
                    a.published = 1
                And
                        ( 
                            a.virtual_product = 1
                           Or
                           (
                                (
                                    a.virtual_product = 0
                                   And
                                    a.use_combinations = 0
                                   And
                                    a.stock <= 0
                                   And
                                    a.backorders <> ''
                                   And
                                    a.backorders = ".$this->_db->quote(self::$BACKORDERS_DISALLOWED_HIDDEN)."
                                ) Is False
                                And
                                (
                                    a.virtual_product = 0
                                   And
                                    a.use_combinations = 0
                                   And
                                    a.stock <= 0
                                   And
                                    a.backorders = ''
                                   And
                                    1 = ".( $config->get('backorders','') == self::$BACKORDERS_DISALLOWED_HIDDEN ? '1' : '2' )."
                                ) Is False
                           )
                           
                        )
                Order By
                    ".($product_order != '' ? $product_order : ' c.ordering ' )."
            ";
        
        } else {
            
            $filter = JFactory::getSession()->get('com_breezingcommerce.filter');
            
            // BOF Search
            
            $search_tbl = '';
            $search_lookup = '';
            $product_order = '';
            
            // we might need this for ordering
            $default = JComponentHelper::getParams('com_languages')->get('site');
            if( ($this->product_order == 'name_a_z' || $this->product_order == 'name_z_a') && count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){
                 
                $search_tbl = ' Left Join #__breezingcommerce_translations As t ';
                $search_tbl .= ' On  t.item_id = a.id And t.type = "product" And t.language_code = '. $this->_db->quote(JFactory::getLanguage()->getTag()) . ' ';

                $product_order = ' t.`title` ';
                
            } else {
                
                if( $this->product_order == 'name_a_z' || $this->product_order == 'name_z_a' ){

                    $product_order = ' a.`title` ';
                }
            }
            
            if( $this->product_order == 'name_z_a' ){
                $product_order .= ' Desc ';
            }
            
            if( isset( $filter['search'] ) && isset($filter['search']['query']) && $filter['search']['query'] != '' ){
                
                $default = JComponentHelper::getParams('com_languages')->get('site');
                if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){
                    
                    /*
                    $search_tbl = ' , #__breezingcommerce_translations As t ';
                    $search_lookup = CrBcHelpers::createMatchAgainstLookupSql($filter['search']['query'], array('title', 'body'), 't', 'And');
                    $search_lookup .= ' And t.item_id = a.id And t.type = "product" And t.language_code = '. $this->_db->quote(JFactory::getLanguage()->getTag());
                    */
                    
                    // if sort order has been triggered on the name above, we don't need to repeat that
                    if( $search_tbl == '' ){
                        $search_tbl = ' Left Join #__breezingcommerce_translations As t ';
                        $search_tbl .= ' On  t.item_id = a.id And t.type = "product" And t.language_code = '. $this->_db->quote(JFactory::getLanguage()->getTag()) . ' ';
                    }
                    
                    $search_lookup = ' And ( ' . CrBcHelpers::createMatchAgainstLookupSql($filter['search']['query'], array('title','body'), 't', '');
                    $search_lookup .= CrBcHelpers::createMatchAgainstLookupSql($filter['search']['query'], array('title','description_fulltext'), 'a', 'Or') . ' ) ';

                    
                }
                else
                {
                    $search_lookup = CrBcHelpers::createMatchAgainstLookupSql($filter['search']['query'], array('title', 'description_fulltext'), 'a', 'And');
                }
                
                //echo $search_lookup;
                //exit;
            }
            
            // EOF Search
            
            // BOF Price
            
            $price_tbl = '';
            $price_tbl_sort = '';
            $price_lookup = '';
            
            $config = CrBcHelpers::getBcConfig();
            $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );
            
            if( ($this->product_order == 'price_high_to_low' || $this->product_order == 'price_low_to_high') ){
                  
                $price_tbl_sort .= ' Left Join #__breezingcommerce_group_price_map As gpm';
                $price_tbl_sort .= ' On a.id = gpm.product_id And gpm.customergroup_id = ' . $this->_db->quote( $cart->group->id ). ' ';
                $price_tbl_sort .= ' Left Join #__breezingcommerce_currency_price_map As cpm';
                $price_tbl_sort .= ' On a.id = cpm.product_id And cpm.currency_id = ' . $this->_db->quote( $cart->currency_id ) . ' ';

                $product_order = ' COALESCE(gpm.price, cpm.price, a.price) ';

                if( $this->product_order == 'price_high_to_low' ){
                    $product_order .= ' Desc ';
                }
            }
            
            if( isset( $filter['price'] ) && isset($filter['price']['from']) && isset($filter['price']['to']) && $filter['price']['from'] >= 0 && $filter['price']['to'] > 0  ){
                
                if($cart->group && $filter['price']['which'] == 'group'){
                    
                    $price_tbl = ',#__breezingcommerce_group_price_map As pm';
                    $price_lookup = ' And a.id = pm.product_id And pm.customergroup_id = ' . $this->_db->quote( $cart->group->id ) . ' And pm.price >= ' . $this->_db->quote($filter['price']['from']) . ' And pm.price <= ' . $this->_db->quote($filter['price']['to']);
                }
                else if( $filter['price']['which'] == 'currency' )
                {
                    $price_tbl = ',#__breezingcommerce_currency_price_map As pm';
                    $price_lookup = ' And a.id = pm.product_id And pm.currency_id = ' . $this->_db->quote( $cart->currency_id ) . ' And pm.price >= ' . $this->_db->quote($filter['price']['from']) . ' And pm.price <= ' . $this->_db->quote($filter['price']['to']);
                }
                else
                {
                    $price_lookup = ' And a.price >= ' . $this->_db->quote($filter['price']['from']) . ' And a.price <= ' . $this->_db->quote($filter['price']['to']);
                }
            }
            
            // EOF Price
            
            // BOF Brands
            
            $brands_tbl = '';
            $brands_lookup = '';
            
            if( isset( $filter['brands'] ) && count( $filter['brands'] ) > 0  ){
                
                $brands_tbl    = ',#__breezingcommerce_product_manufacturers As pmm';
                
                if(!$this->and_or){
                
                    $brands = '';
                    foreach($filter['brands'] As $brand){
                        $brands .= " pmm.manufacturer_id = " . $this->_db->quote($brand) . " And ";
                    }

                    $brands_lookup = ' And ' . $brands . ' pmm.product_id = a.id ';
                
                } else {
                
                    // OR
                    $brands_lookup = ' And pmm.manufacturer_id In (' . implode(',',$filter['brands']) . ') And pmm.product_id = a.id ';
                    
                }
            }
            
            // EOF Brands
            
            // BOF Attributes
            
            $attributes_tbl = '';
            $attributes_lookup = '';
            
            if( isset( $filter['attributes'] ) && count( $filter['attributes'] ) > 0  ){
                
                $attributes_tbl    = ',#__breezingcommerce_product_attribute_items As pai, #__breezingcommerce_product_attributes As patt ';
                
                if(!$this->and_or){
                
                    $attributes = '';
                    foreach($filter['attributes'] As $attribute){
                        $attributes .= " pai.attribute_item_id = " . $this->_db->quote($attribute) . " And ";
                    }
                    
                    $attributes_lookup = ' And ' . $attributes . ' pai.product_id = a.id And pai.published = 1 And pai.product_attribute_id = patt.id And patt.published = 1 ';
                    
                } else {
                    
                    // OR
                    $attributes_lookup = ' And pai.attribute_item_id In (' . implode(',',$filter['attributes']) . ') And pai.product_id = a.id And pai.published = 1 And pai.product_attribute_id = patt.id And patt.published = 1 ';
                }
            }
            
            // EOF Attributes
            
            // BOF Options
            
            $options_tbl = '';
            $options_lookup = '';
            
            if( isset( $filter['options'] ) && count( $filter['options'] ) > 0  ){
                
                $options_tbl    = ',#__breezingcommerce_product_property_values As ppv, #__breezingcommerce_product_property_keys As pprok ';
                
                if(!$this->and_or){
                
                    $options = '';
                    foreach($filter['options'] As $option){
                        $options .= " ppv.property_value_id = " . $this->_db->quote($option) . " And ";
                    }

                    $options_lookup = ' And ppv.custom_value = "" And ' . $options . ' ppv.product_id = a.id And ppv.published = 1 And pprok.product_id = ppv.product_id And pprok.property_key_id = ppv.property_key_id And pprok.published = 1  ';
                    
                } else {
                    
                    // OR
                    $options_lookup = ' And ppv.custom_value = "" And ppv.property_value_id In (' . implode(',',$filter['options']) . ') And ppv.product_id = a.id And ppv.published = 1 And pprok.product_id = ppv.product_id And pprok.property_key_id = ppv.property_key_id And pprok.published = 1  ';
                }
                
            }
            
            // EOF Options
            
            // BOF CUSTOM Options
            
            $custom_options_tbl = '';
            $custom_options_lookup = '';
            
            if( isset( $filter['custom_options'] ) && count( $filter['custom_options'] ) > 0  ){
                
                $custom_options_tbl    = ',#__breezingcommerce_product_property_values As ppv2, #__breezingcommerce_product_property_keys As pprok2 ';
                
                if(!$this->and_or){
                
                    $custom_options = '';
                    foreach($filter['custom_options'] As $custom_option){
                        $options .= " ppv2.id = " . $this->_db->quote($custom_option) . " And ";
                    }

                    $custom_options_lookup = ' And ' . $custom_options . ' ppv2.product_id = a.id And ppv2.published = 1 And pprok2.product_id = ppv2.product_id And pprok2.property_key_id = ppv2.property_key_id And pprok2.published = 1  ';
                    
                } else {
                    
                    // OR
                    $custom_options_lookup = ' And ppv2.id In (' . implode(',',$filter['custom_options']) . ') And ppv2.product_id = a.id And ppv2.published = 1 And pprok2.product_id = ppv2.product_id And pprok2.property_key_id = ppv2.property_key_id And pprok2.published = 1  ';
                }
                
            }
            
            // EOF CUSTOM Options
            
            // BOF Sales
            
            $sales_tbl = '';
            $sales_lookup = '';
            
            if( isset( $filter['sales'] ) && $filter['sales']  ){
                
                $sales_lookup = ' And a.sale_price > 0 ';
            }
            
            // EOF Sales
            
            // BOF Stock
            
            $stock_tbl = '';
            $stock_lookup = '';
            
            if( isset( $filter['stock'] ) && $filter['stock']  ){
                
                $stock_lookup = ' And ( a.stock > 0 Or a.virtual_product = 1 ) ';
            }
            
            // EOF Stock
            
            $categories_tbl = '';
            $categories_lookup = '';
            
            if( isset( $filter['categories'] ) && count( $filter['categories'] ) > 0  ){
                
                $categories_tbl    = ',#__breezingcommerce_categories As c, #__breezingcommerce_product_categories As pc';
                
                if(!$this->and_or){
                
                    $categories = '';
                    foreach($filter['categories'] As $category){
                        $categories .= " c.id = " . $this->_db->quote($category) . " And ";
                    }

                    $categories_lookup = ' And c.published = 1 And ' . $categories . ' pc.product_id = a.id And pc.category_id = c.id   ';

                } else {
                
                    // OR
                    $categories_lookup = ' And c.published = 1 And c.id In (' . implode(',',$filter['categories']) . ') And pc.product_id = a.id And pc.category_id = c.id ';
                    
                }
            }
            
            $check_for_translation_tbl = '';
            $check_for_translation_lookup = '';
            
            $default = JComponentHelper::getParams('com_languages')->get('site');
            if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){
                
                $check_for_translation_tbl = ' Left Join #__breezingcommerce_translations As trns ';
                $check_for_translation_tbl .= ' On a.id = trns.item_id And trns.language_code = ' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ' And `type` = "product" ';
                $check_for_translation_lookup = ' And trns.item_id Is Not Null And ( trns.title <> "" Or trns.alias <> "" Or trns.body <> "" ) ';
            }
            
            return "
                Select Distinct
                    SQL_CALC_FOUND_ROWS
                    a.*
                From
                    #__breezingcommerce_products As a
                    ".$check_for_translation_tbl."
                    ".$price_tbl_sort."
                    ".$search_tbl."
                    ".$price_tbl."
                    ".$attributes_tbl."
                    ".$brands_tbl."
                    ".$options_tbl."
                    ".$custom_options_tbl."
                    ".$categories_tbl."
                    ".$sales_tbl."
                    ".$stock_tbl."
                Where
                    a.published = 1
                    And
                        ( 
                            a.virtual_product = 1
                           Or
                           (
                                (
                                    a.virtual_product = 0
                                   And
                                    a.use_combinations = 0
                                   And
                                    a.stock <= 0
                                   And
                                    a.backorders <> ''
                                   And
                                    a.backorders = ".$this->_db->quote(self::$BACKORDERS_DISALLOWED_HIDDEN)."
                                ) Is False
                                And
                                (
                                    a.virtual_product = 0
                                   And
                                    a.use_combinations = 0
                                   And
                                    a.stock <= 0
                                   And
                                    a.backorders = ''
                                   And
                                    1 = ".( $config->get('backorders','') == self::$BACKORDERS_DISALLOWED_HIDDEN ? '1' : '2' )."
                                ) Is False
                           )
                           
                        )
                    ".$search_lookup."
                    ".$price_lookup."
                    ".$attributes_lookup."
                    ".$brands_lookup."
                    ".$options_lookup."
                    ".$custom_options_lookup."
                    ".$categories_lookup."
                    ".$sales_lookup."
                    ".$stock_lookup."
                    ".$check_for_translation_lookup."
                Order By
                    ".($product_order != '' ? $product_order : ' a.`title` ' )."
            ";
        }
    }
    
    function getProducts(){
        
        
        $db = JFactory::getDbo();
        
        $db->setQuery($this->getProductsQuery(), $this->getState('limitstart'), $this->getState('limit'));
        
        $rows = $db->loadObjectList();
        
        $db->setQuery('SELECT FOUND_ROWS();');
        $this->_total = $db->loadResult();
        
        foreach( $rows As $row ){
            
            $translated_object = CrBcHelpers::loadTranslation($row->id, 'product');
            if($translated_object !== null){
                $row->description = $translated_object->body;
                $row->title = $translated_object->title;
                $row->alias = $translated_object->alias;
            }
        }
        
        return $rows;
    }
    
    /**
    * Gets the products
    * @return array List of products
    */
    function getCategory()
    {
        $db = JFactory::getDbo();
        
        // make sure not to show a category that hasn't been translated at least partially
        $check_for_translation_tbl = '';
        $check_for_translation_lookup = '';

        $default = JComponentHelper::getParams('com_languages')->get('site');
        if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){

            $check_for_translation_tbl = ' Left Join #__breezingcommerce_translations As trns ';
            $check_for_translation_tbl .= ' On c.id = trns.item_id And trns.language_code = ' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ' And `type` = "category" ';
            $check_for_translation_lookup = ' And trns.item_id Is Not Null And ( trns.title <> "" Or trns.alias <> "" Or trns.body <> "" ) ';
        }
        
        $db->setQuery("
            Select
                c.*
            From 
                #__breezingcommerce_categories As c $check_for_translation_tbl
            Where
                c.id = ".intval($this->getId())."
            $check_for_translation_lookup
            And
                c.published = 1
        ");
        
        $return = $db->loadObject();
        
        if( !is_object($return) ){
            
            throw new Exception(JText::_('COM_BREEZINGCOMMERCE_CATEGORY_NOT_FOUND'), 404);
        }
        
        // translations
        $return->description_translation = '';
        $return->title_translation = '';
        $return->alias_translation = '';
        
        if($this->getId()){
            $translated_object = CrBcHelpers::loadTranslation($this->getId(), 'category');
            if($translated_object !== null){
                $return->description = $translated_object->body;
                $return->title = $translated_object->title;
                $return->alias = $translated_object->alias;
            }
        }
        
        // execute module positions
        
        $regex		= '/{loadposition\s+(.*?)}/i';
        
        preg_match_all($regex, $return->description, $matches, PREG_SET_ORDER);

        if ($matches) {

            $document	= JFactory::getDocument();
            $renderer	= $document->loadRenderer('modules');
            $options	= array('style' => 'xhtml');

            foreach ($matches as $match) {

                $matcheslist =  explode(',', $match[1]);
                $position = trim($matcheslist[0]);
                $output = $renderer->render($position, $options, null);
                $return->description = preg_replace("|$match[0]|", addcslashes($output, '\\'), $return->description, 1);
            }
        }
        
        return $return;

    }
}
