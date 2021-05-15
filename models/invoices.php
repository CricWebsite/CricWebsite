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

class BreezingcommerceModelInvoices extends JModelLegacy
{
    function  __construct($config)
    {
        parent::__construct();
        
        // synching yet to generate invoice (partial customer data)
        
        $mainframe = JFactory::getApplication();
        $option = 'com_breezingcommerce';
        
        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }
    
    function sendAndCreateInvoice($order_id){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $config = CrBcHelpers::getBcConfig();
        
        // make sure to only load the default backend language
        $lang_tmp = JFactory::getLanguage()->getTag();
        $lang_selected = $lang_tmp;
        
        switch($config->get('invoice_language','backend')){
            case 'backend':
                $lang_selected = JComponentHelper::getParams('com_languages')->get('administrator');
                break;
            case 'frontend':
                $lang_selected = JComponentHelper::getParams('com_languages')->get('site');
                break;
            case 'frontend_user':
                $lang_selected = JFactory::getLanguage()->getTag();
                break;
        }
        
        JFactory::getLanguage()->load('com_breezingcommerce', JPATH_BASE, $lang_selected, true);
        
        $db = JFactory::getDbo();
        
        $_cart = new CrBcCart( array() );
        $_cart->restoreCart(intval($order_id));
        
        $_cart_items = $_cart->getItems(true);
        
        if(count($_cart_items) > 0){
            
            $_cart_array = $_cart->getArray();
            
            $data = CrBcCart::getData(intval($order_id), $_cart_items, -1, -1);
            
            $db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1");
            $shipping_plugins = $db->loadAssocList();
            
            $_order_info = CrBcCart::getOrder(
                                intval($order_id), 
                                $_cart, 
                                $_cart_array, 
                                $_cart_items, 
                                $_cart_array['customer_id'],
                                $data,
                                $shipping_plugins,
                                array()
                        );
            
            
            $order = $this->getTable('order');

            if(!$order->load($order_id)){
                throw new Exception('Could not load the order!');
            }
            
            $order->invoice_created = 1;
            $_order_info->invoice_date = JFactory::getDate()->toSql();
            
            CrBcCart::createAndSendInvoice(
                    $_order_info->customer, 
                    $order, 
                    $_order_info, 
                    $_cart, 
                    $_cart_items
            );
            
            JFactory::getLanguage()->load('com_breezingcommerce', JPATH_BASE, $lang_tmp, true);
            
            if(!$order->store()){
                throw new Exception('Could not store the order!');
            }
            
            return true;
        }
        
        JFactory::getLanguage()->load('com_breezingcommerce', JPATH_BASE, $lang_tmp, true);
            
        return false;
    }
    
    function getInvoice(){
        
        $db = JFactory::getDbo();
        
        $db->setQuery("Select id From #__breezingcommerce_customers Where userid = " . intval( JFactory::getUser()->get('id', 0) ) . " Limit 1");
        $customer_id = $db->loadResult();
        
        $order = $this->getTable('order');
        
        if( !$order->load( array( 'id' => JRequest::getInt('order_id'), 'customer_id' => $customer_id ) ) && JRequest::getInt('order_id') > 0 && $customer_id > 0 ){
            throw new Exception('Could not load the order!');
        }
        
        $invoice_folder = JPATH_SITE . DS . 'media' . DS . 'breezingcommerce' . DS . 'invoice';
        
        $pdfname = $invoice_folder.DS.'invoices'.DS.$order->invoice_name.'.pdf';
        
        if( !$order->invoice_created || $order->invoice_number == '' 
                || 
                ( $order->invoice_created && !JFile::exists($pdfname) )
                || 
                ( $order->invoice_number != '' && !JFile::exists($pdfname) ) ){
            
            $this->sendAndCreateInvoice(JRequest::getInt('order_id'));

        }
        
        if( !$order->load( array( 'id' => JRequest::getInt('order_id'), 'customer_id' => $customer_id ) ) && JRequest::getInt('order_id') > 0 && $customer_id > 0 ){
            throw new Exception('Could not determine the order after invoice created or found!');
        }
        
        return $pdfname;
    }
    
    function isCustomerDataComplete(){
        
        $db = JFactory::getDbo();
        
        $db->setQuery("Select * From #__breezingcommerce_customers Where userid = " . intval( JFactory::getUser()->get('id', 0) ) . " Limit 1");
        $customer = $db->loadObject();
        
        if($customer !== null){
           return $customer->address_complete == 1 ? true : false;
        }
        
        return false;
    }
    
    function getInvoicesQuery(){
        $db = JFactory::getDbo();
        
        $db->setQuery("Select id From #__breezingcommerce_customers Where userid = " . intval( JFactory::getUser()->get('id', 0) ) . " Limit 1");
        $customer_id = $db->loadResult();
        
        return "
                Select 
                    * 
                From 
                    #__breezingcommerce_orders 
                Where 
                    customer_id = " . intval( $customer_id ) . "
                And
                    checked_out = 1
                And
                    published = 1
                Order By 
                    id Desc,
                    invoice_date Desc
        ";
    }
    
    function getData()
    {
        $db = JFactory::getDbo();
        
        $db->setQuery($this->getInvoicesQuery(), $this->getState('limitstart'), $this->getState('limit'));
        
        return $db->loadObjectList();
    }
    
    function getTotal() {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->getInvoicesQuery();
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
