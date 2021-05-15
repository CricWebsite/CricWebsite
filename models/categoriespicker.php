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

class BreezingcommerceModelCategoriespicker extends JModelLegacy
{
    /**
     * Items total
     * @var integer
     */
    private $_total = null;

    /**
     * Pagination object
     * @var object
     */
    private $_pagination = null;

    function  __construct($config) {
        parent::__construct($config);

        $mainframe = JFactory::getApplication();
        $option = 'com_breezingcommerce';
        
        $search = $mainframe->getUserStateFromRequest("$option.category_search", 'category_search', '', 'string');
        $this->setState('category_search', $search);

        $search = $mainframe->getUserStateFromRequest("$option.category_search_by", 'category_search_by', '', 'string');
        $this->setState('category_search_by', $search);

        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $filter_order     = $mainframe->getUserStateFromRequest(  $option.'category_filter_order', 'filter_order', 'c.ordering', 'cmd' );
        $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'category_filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );

        $this->setState('category_filter_order', $filter_order);
        $this->setState('category_filter_order_Dir', $filter_order_Dir);

        $filter_state = $mainframe->getUserStateFromRequest( $option.'category_filter_state', 'filter_state', '', 'word' );
        $this->setState('category_filter_state', $filter_state);
    }

    /*
     *
     * MAIN LIST AREA
     * 
     */

    private function buildOrderBy() {
        $mainframe = JFactory::getApplication();
        $option = 'com_breezingcommerce';

        $orderby = '';
        $filter_order     = $this->getState('category_filter_order');
        $filter_order_Dir = $this->getState('category_filter_order_Dir');

        /* Error handling is never a bad thing*/
        if(!empty($filter_order) && $filter_order != 'c.ordering' && !empty($filter_order_Dir) ) {
            $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir . ' , c.lft Asc ';
        } else {
           $orderby = ' ORDER BY c.lft Asc ';
        }

        return $orderby;
    }


    /**
     * @return string The query
     */
    private function _buildQuery(){

        $search_tbl = '';
        $where = '';
        $match = '';
        
        if($this->getState('category_search_by') == 'fulltext' && $this->getState('category_search') != '')
        {
                $default = JComponentHelper::getParams('com_languages')->get('site');
                if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){

                $search_tbl = ' Left Join #__breezingcommerce_translations As t ';
                $search_tbl .= ' On  t.item_id = c.id And t.type = "category" And t.language_code = '. $this->_db->quote(JFactory::getLanguage()->getTag()) . ' ';
                
                $match .= ' And ( ' . CrBcHelpers::createMatchAgainstLookupSql($this->getState('category_search'), array('title','body'), 't', '');
                
                $match .= CrBcHelpers::createMatchAgainstLookupSql($this->getState('category_search'), array('title','description_fulltext'), 'c', 'Or') . ' ) ';
                
            }
            else
            {
                $match = CrBcHelpers::createMatchAgainstLookupSql($this->getState('category_search'), array('title','description_fulltext'), 'c', 'And');

            }
        }
       
        // make sure not to show categories that haven't been translated at least partially
        $check_for_translation_tbl = '';
        $check_for_translation_lookup = '';

        $default = JComponentHelper::getParams('com_languages')->get('site');
        if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){

            $check_for_translation_tbl = ' Left Join #__breezingcommerce_translations As trns ';
            $check_for_translation_tbl .= ' On c.id = trns.item_id And trns.language_code = ' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ' And `type` = "category" ';
            $check_for_translation_lookup = ' And trns.item_id Is Not Null And ( trns.title <> "" Or trns.alias <> "" Or trns.body <> "" ) ';
        }
        
        $where = ' Where c.id <> 1 And c.alias <> "root" And c.title <> "ROOT" ' . $check_for_translation_lookup;
        
        return 'Select SQL_CALC_FOUND_ROWS c.* From #__breezingcommerce_categories As c '.$check_for_translation_tbl.' '.$search_tbl.' ' . $where . $match . $this->buildOrderBy();
        
    }

    /**
    * Gets the categories
    * @return array List of categories
    */
    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $this->_data = $this->_getList( $this->_buildQuery(), $this->getState('limitstart'), $this->getState('limit') );
            
            foreach( $this->_data As $row ){

                $translated_object = CrBcHelpers::loadTranslation($row->id, 'category');
                if($translated_object !== null){
                    $row->description = $translated_object->body;
                    $row->title = $translated_object->title;
                    $row->alias = $translated_object->alias;
                }
            }
            
        }

        return $this->_data;
    }

    function getTotal() {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
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



}
