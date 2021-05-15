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

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');

class BreezingcommerceModelFiles extends JModelLegacy
{
    function  __construct($config)
    {
        parent::__construct();
        
        $mainframe = JFactory::getApplication();
        $option = 'com_breezingcommerce';
        
        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
        
        // synching files that might have been added later to products of orders a customer (user) might have purchased
        
        $db = JFactory::getDbo();
        
        if( JFactory::getUser()->get('id', 0) > 0 ){
            
            $db->setQuery("Select id From #__breezingcommerce_customers Where userid = " . intval( JFactory::getUser()->get('id', 0) ) . " Limit 1");
            $customer_id = $db->loadResult();
            
            if($customer_id){
                CrBcCart::synchFileVerifications($customer_id);
            }
        }
    }
    
    function getFile(){
        
        if(JFactory::getUser()->get('id', 0) <= 0){
            
            return null;
        }
        
        $db = JFactory::getDbo();
        
        $db->setQuery("Select id From #__breezingcommerce_customers Where userid = " . intval( JFactory::getUser()->get('id', 0) ) . " Limit 1");
        $customer_id = $db->loadResult();
        
        $db->setQuery("
            Select 
                c.*,
                b.download_tries,
                b.forever,
                b.id As order_item_file_id
            From 
                #__breezingcommerce_orders As a, 
                #__breezingcommerce_orderitemfiles As b,
                #__breezingcommerce_files As c,
                #__breezingcommerce_order_items As d
            Where
                a.customer_id = " . intval( $customer_id ) . "
            And
                a.paid = 1 
            And 
                a.payment_date <> '0000-00-00 00:00:00' 
            And 
                a.id = b.order_id
            And
                b.id = ".JRequest::getInt('order_item_file_id', 0)."
            And
                c.id = b.file_id
            And
                d.verified = 1
            And
                d.order_id = a.id
            And
                b.order_item_id = d.id
            And
                c.published = 1
            And 
                a.published = 1
            And
                (
                    b.valid > ".$db->quote(JFactory::getDate('now', 'UTC')->toSql())."
                  Or
                    b.forever = 1
                  Or
                    c.verification_days = 0
                )
            Order By 
                a.payment_date,
                c.`ordering`");
        
        $order = $db->loadObject();
        
        return $order;
    }
    
    function getFilesQuery(){
        
        if(JFactory::getUser()->get('id', 0) <= 0){
            
            return null;
        }
        
        $db = JFactory::getDbo();
        
        $db->setQuery("Select id From #__breezingcommerce_customers Where userid = " . intval( JFactory::getUser()->get('id', 0) ) . " Limit 1");
        $customer_id = $db->loadResult();
        
        return "
            Select 
                a.id As order_id,
                a.order_number,
                a.payment_date,
                c.*,
                b.download_tries,
                b.forever,
                b.id As order_item_file_id,
                c.verification_download_tries,
                e.title As product_title,
                b.valid
            From 
                #__breezingcommerce_orders As a, 
                #__breezingcommerce_orderitemfiles As b,
                #__breezingcommerce_files As c,
                #__breezingcommerce_order_items As d,
                #__breezingcommerce_products As e
            Where
                a.customer_id = " . intval( $customer_id ) . "
            And
                a.paid = 1 
            And 
                a.payment_date <> '0000-00-00 00:00:00' 
            And 
                a.id = b.order_id
            And
                c.id = b.file_id
            And
                d.verified = 1
            And
                d.order_id = a.id
            And
                b.order_item_id = d.id
            And
                b.order_id = a.id
            And
                d.product_id = e.id
            And
                c.published = 1
            And 
                a.published = 1
            And
                (
                    b.valid > ".$db->quote(JFactory::getDate('now', 'UTC')->toSql())."
                  Or
                    b.forever = 1
                  Or
                    c.verification_days = 0
                )
            Order By 
                a.id Desc,
                a.payment_date Desc,
                c.`ordering`
            ";
        
    }
    
    function getData()
    {
        if(JFactory::getUser()->get('id', 0) <= 0){
            
            return null;
        }
        
        $db = JFactory::getDbo();
        
        $db->setQuery($this->getFilesQuery(), $this->getState('limitstart'), $this->getState('limit'));
        
        $orders = $db->loadObjectList();
        
        foreach($orders As $order){
            $translated_object = CrBcHelpers::loadTranslation($order->id, 'product_file');
            if($translated_object !== null){
                $order->description = $translated_object->body;
            }
            
            $translated_object = CrBcHelpers::loadTranslation($order->product_id, 'product');
            if($translated_object !== null){
                $order->product_title = $translated_object->title;
            }
        }
        
        return $orders;

    }
    
    function getTotal() {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->getFilesQuery();
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
