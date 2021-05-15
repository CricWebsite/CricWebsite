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

class BreezingcommerceModelCheckout extends JModelLegacy
{
    
    function  __construct($config)
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();
        $option = 'com_breezingcommerce';
        
        $this->setId(JRequest::getInt('order_id', 0));
    }

    /*
     * MAIN DETAILS AREA
     */

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
    
    /**
    * Gets the products
    * @return array List of products
    */
    function getData()
    {
        if (!$this->_data) {
            
            $this->_data = new stdClass();
            $this->_data->id = 0;
            
            $this->_data->customergroup_id = 0;
            
            $this->_data->username = 0;
            $this->_data->userid = 0;
            $this->_data->firstname = null;
            $this->_data->lastname = null;
            $this->_data->company = null;
            $this->_data->email = null;
            $this->_data->address = null;
            $this->_data->address2 = null;
            $this->_data->city = null;
            $this->_data->zip = null;
            $this->_data->region_id = null;
            $this->_data->country_id = null;
            $this->_data->phone = null;
            $this->_data->mobile = null;
            $this->_data->fax = null;

            $this->_data->shipping_firstname = null;
            $this->_data->shipping_lastname = null;
            $this->_data->shipping_company = null;
            $this->_data->shipping_email = null;
            $this->_data->shipping_address = null;
            $this->_data->shipping_address2 = null;
            $this->_data->shipping_city = null;
            $this->_data->shipping_zip = null;
            $this->_data->shipping_region_id = null;
            $this->_data->shipping_country_id = null;
            $this->_data->shipping_phone = null;
            $this->_data->shipping_mobile = null;
            $this->_data->shipping_fax = null;
            
            $this->_data->use_shipping_address = false;
            $this->address_complete = 0;
        }
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        $_cart = new CrBcCart( $_session_cart );
        
        if(isset( $_session_cart['order_id'] ) && isset($_cart->cart['customer_id']) && intval( $_cart->cart['customer_id'] ) > 0){
            $customer_id = intval( $_cart->cart['customer_id'] );
            $this->_db->setQuery("Select * From #__breezingcommerce_customers Where id = " . intval($customer_id) . ' Limit 1');
            $this->_data = $this->_db->loadObject();
        }
        
        $query = ' Select * From #__breezingcommerce_countries Where published = 1';
        $this->_db->setQuery($query);
        $this->_data->countries = $this->_db->loadObjectList();
        
        $this->_data->countries = CrBcHelpers::populateTranslation($this->_data->countries, 'country', array('title' => 'name'));
        
        foreach ($this->_data->countries As $country) {
            $country->regions = array();
            $query = ' Select * From #__breezingcommerce_country_regions Where country_id = ' . $country->id;
            $this->_db->setQuery($query);
            $regions = $this->_db->loadObjectList();
            
            foreach ($regions As $region) {
                $country->regions[] = $region;
            }
            
            $country->regions = CrBcHelpers::populateTranslation($country->regions, 'country_region_' . $country->id, array('title' => 'name'));
            
        }
        
        return $this->_data;

    }
    
    function stockUpdate($order_id){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
         
        $_cart = new CrBcCart( array() );
        $_cart->restoreCart(intval($order_id));
        
        $_cart_items = $_cart->getItems(true);
        
        if(count($_cart_items) > 0){
            
            CrBcCart::stockUpdate($order_id, $_cart_items, false,'-');
        }
    }
    
    function sendStatusMail($order_id){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
         
        $_cart = new CrBcCart( array() );
        $_cart->restoreCart(intval($order_id));
        
        $_cart_items = $_cart->getItems(true);
        
        if(count($_cart_items) > 0){
        
            $_cart_array = $_cart->getArray();
            
            $data = CrBcCart::getData($order_id, $_cart_items, -1, -1);
            
            $_order_info = CrBcCart::getOrder(
                    intval($order_id), 
                    $_cart, 
                    $_cart_array, 
                    $_cart_items, 
                    $_cart_array['customer_id'],
                    $data,
                    array(),
                    array()
            );
            
            $results = CrBcCart::getCustomerResults($_order_info, $_order_info->order_status_id, $_order_info->customer);

            $email = $results['email'];
            $result = $results['result'];
            $result2 = $results['result2'];
            $customer_result = $results['customer_result'];

            $order = $this->getTable('order');

            if(!$order->load($order_id)){
                throw new Exception('Could not load the order!');
            }
            
            CrBcCart::sendStatusMail(
                    $order, 
                    $email, 
                    $result, 
                    $result2, 
                    $customer_result, 
                    intval($order_id)
            );
        }
    }
    
    function sendInvoiceAdmins($order_id){
        
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
            
            CrBcCart::sendInvoiceAdmins(
                    $_order_info->customer, 
                    $order, 
                    $_order_info, 
                    $_cart, 
                    $_cart_items
            );
            
            // revert to original language after all
            JFactory::getLanguage()->load('com_breezingcommerce', JPATH_BASE, $lang_tmp, true);

            return true;
        }
        
        // revert to original language after all
        JFactory::getLanguage()->load('com_breezingcommerce', JPATH_BASE, $lang_tmp, true);

        return false;
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
                throw new Exception('Could store the order!');
            }
            
            
            return true;
        }
        
        JFactory::getLanguage()->load('com_breezingcommerce', JPATH_BASE, $lang_tmp, true);
            
        return false;
    }
    
    function getUserData(){
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        
        if(!isset($_session_cart['checkout'])){
            return 0;
        }
        
        if(!isset($_session_cart['checkout']['userid'])){
            return 0;
        }
        
        $userid = intval($_session_cart['checkout']['userid']);
        
        $db = JFactory::getDbo();
        $db->setQuery("Select * From #__users Where id = " . $db->quote($userid));
        return $db->loadObject();
        
    }
    
    function initPayment(){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $db = JFactory::getDbo();
        
        $result = 'thankyou';
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        $_cart = new CrBcCart( $_session_cart );
        
        // re-retrieving the session cart as CrBcCart does integrity tests
        $_session_cart = $_cart->getArray(); 
        
        // payment required, check if the payment plugin exists and send to the payment first
        
        if(!isset($_session_cart['checkout']) || !isset($_session_cart['checkout']['payment_plugin_id']) || intval($_session_cart['checkout']['payment_plugin_id']) <= 0){
            throw new Exception('Cannot init payment, no valid payment plugin id given');
        }
        
        $payment_plugin_id = intval($_session_cart['checkout']['payment_plugin_id']);

        // first check, if it's a pay manually payment like bank transfer
        // in that case, perform the checkout but don't verify nothing

        $db->setQuery("Select * From #__breezingcommerce_plugins Where id = " . $payment_plugin_id . " And published = 1");
        $plugin = $db->loadObject();

        $plugin_instance = CrBcPlugin::getPluginInstance($plugin->name, $plugin->type, 'site');
        if($plugin_instance instanceof CrBcPaymentPlugin){
        
            return $plugin_instance->getInitOutput();
            
        }
        else{
            throw new Exception('Payment option not found');
        }
    }
    
    function checkout($order_id){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $db = JFactory::getDbo();
        
        $_cart = new CrBcCart( array() );
        $_cart->restoreCart(intval($order_id));
        
        $_cart_items = $_cart->getItems(true);
        
        if(count($_cart_items) > 0){
            
            CrBcCart::checkout(intval($order_id), $_cart, $_cart_items, $_cart->getArray());
            
            $_cart->clear();
            
            return true;
        }
        
        return false;
    }
    
    function verifyPayment($order_id){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $db = JFactory::getDbo();
        
        $result = 'thankyou';
        
        $_cart = new CrBcCart( array() );
        $_cart->restoreCart(intval($order_id));
        
        $_cart_items = $_cart->getItems(true);
        
        if(count($_cart_items) > 0){
            
            $db->setQuery("Select * From #__breezingcommerce_orders Where id = " . intval($order_id));
            $order = $db->loadObject();
            
            if($order->checked_out == 1){
                
                //JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_PAYMENT_DONE_ERROR'),'error');
                JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_breezingcommerce&controller=checkout&layout=thankyou&task=perform_checkout', false));
            }
            
            // payment required, check if the payment plugin exists and send to the payment first
            $payment_plugin_id = JRequest::getInt('payment_plugin_id', 0);

            // first check, if it's a pay manually payment like bank transfer
            // in that case, perform the checkout but don't verify nothing
        
            $db->setQuery("Select * From #__breezingcommerce_plugins Where id = " . $payment_plugin_id . " And published = 1");
            $plugin = $db->loadObject();
            
            $plugin_instance = CrBcPlugin::getPluginInstance($plugin->name, $plugin->type, 'site');
            if($plugin_instance instanceof CrBcPaymentPlugin){
               
                $payment_result = $plugin_instance->verifyPayment($_cart, $order);
                
                if(!$payment_result){
                    
                    $result = 'payment_fail';
                }
                else{
                    
                    $tx = $plugin_instance->getPaymentTransactionId();
                    
                    $date = JFactory::getDate();
                    
                    $paid = 1;
                    
                    if($plugin_instance && $plugin_instance->isOfflinePayment()){
                        
                        $paid = 0;
                    }
                    
                    $db->setQuery("
                        Update 
                            #__breezingcommerce_orders 
                        Set 
                            paid = ".$db->quote($paid).",
                            payment_plugin_id = ".intval($payment_plugin_id).",
                            payment_date = ".$db->quote($date->toSql()).",
                            payment_raw_data = ".$db->quote(json_encode($_REQUEST)).",
                            payment_transaction_id = ".$db->quote($tx)."
                        Where 
                            id = " . intval($order_id));
                    
                    $db->query();
                }
            }
            else{
                
                return JText::_('COM_BREEZINGCOMMERCE_PAYMENT_OPTION_NOT_FOUND_ERROR');
            }
            
        } else {
            
            return JText::_('COM_BREEZINGCOMMERCE_INVALID_CART_ERROR');
        }
        
        return $result;
    }
    
    function performCheckout(){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $db = JFactory::getDbo();
        
        $result = 'thankyou';
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        $_cart = new CrBcCart( $_session_cart );
        
        // re-retrieving the session cart as CrBcCart does integrity tests
        $_session_cart = $_cart->getArray();
        
        // let's check what we got, all must be fullfilled
        
        if( !isset( $_session_cart['checkout'] ) ){
            return JText::_('COM_BREEZINGCOMMERCE_NO_DATA_ERROR');
        }
        
        if(!isset($_session_cart['checkout']['.crbc-checkout-method-guest-register-login_done'])){
            return JText::_('COM_BREEZINGCOMMERCE_NO_DATA_ERROR');
        }
        
        if(!isset($_session_cart['checkout']['.crbc-checkout-billing-information_done'])){
            return JText::_('COM_BREEZINGCOMMERCE_NO_BILLING_INFORMATION_ERROR');
        }
        
        if(!isset($_session_cart['checkout']['.crbc-checkout-shipping-information_done'])){
            return JText::_('COM_BREEZINGCOMMERCE_NO_SHIPPING_INFORMATION_ERROR');
        }
        
        if(!isset($_session_cart['checkout']['.crbc-checkout-payment-information_done']) || !isset($_session_cart['checkout']['payment_plugin_id'])){
            return JText::_('COM_BREEZINGCOMMERCE_NO_PAYMENT_INFORMATION_ERROR');
        }
        
        if(!isset($_session_cart['checkout']['.crbc-checkout-order-review-information_done'])){
            return JText::_('COM_BREEZINGCOMMERCE_ORDER_NOT_REVIEWED_ERROR');
        }
        
        // first we determine if payment should be done or not.
        // if not, we can start the checkout and verification process right away....
        
        $_cart_items = $_cart->getItems(true);

        $virtual_order = $_cart->isVirtualOrder($_cart_items);
        
        if(!isset($_session_cart['checkout']['.crbc-checkout-shipping-method_done']) && !$virtual_order){
            return JText::_('COM_BREEZINGCOMMERCE_NO_SHIPPING_METHOD_ERROR');
        }
        
        $data = CrBcCart::getData($_session_cart['order_id'], $_cart_items, -1, -1);

        $db->setQuery("Select id From #__breezingcommerce_order_status Order By ordering");
        $default_order_status_id = $db->loadResult();
        
        $_order_info = CrBcCart::getOrder(
                                    $_session_cart['order_id'], 
                                    $_cart, 
                                    $_session_cart, 
                                    $_cart_items, 
                                    $_session_cart['customer_id'],
                                    $data,
                                    array(),
                                    array()
                            );
        //print_r($_session_cart['checkout']);
        //print_r($_order_info);
        //exit;
        $payment_plugin_id = intval($_session_cart['checkout']['payment_plugin_id']);

        // first check, if it's a pay manually payment like bank transfer
        // in that case, perform the checkout but don't verify nothing

        // TODO: implement shipping plugin selection
        $shipping_plugin_id = isset($_session_cart['checkout']['shipping_plugin_id']) ? intval($_session_cart['checkout']['shipping_plugin_id']) : 0;
        
        $db->setQuery("Select * From #__breezingcommerce_plugins Where id = " . $shipping_plugin_id . " And published = 1");
        $shipping_plugin = $db->loadObject();
        
        $db->setQuery("Select * From #__breezingcommerce_plugins Where id = " . $payment_plugin_id . " And published = 1");
        $payment_plugin = $db->loadObject();

        $plugin_instance = null;
        
        if($payment_plugin){
            
            $plugin_instance = CrBcPlugin::getPluginInstance($payment_plugin->name, $payment_plugin->type, 'site');
        }
        
        // not paid yet? Do something about it
        if(!$_order_info->checked_out){
        
            // pre-fill the history for currency_code, conversion rate and symbol
            // as it will be important after successfull payments because they can happen out of session context
            
            $shipping_id = 0;
            $shipping_costs = 0;
            $shipping_name = '';
            $shipping_plugin = '';
            
            $update = '';
            
            if(!$virtual_order){
                
                $shipping_id = $_session_cart['checkout']['shipping_plugin_data']['shipping_id'];
                $shipping_costs = $_session_cart['checkout']['shipping_plugin_data']['shipping_costs'];
                $shipping_name = $_session_cart['checkout']['shipping_plugin_data']['shipping_name'];;
                $shipping_plugin = $_session_cart['checkout']['shipping_plugin_data']['shipping_plugin'];;
            
            } else {
                
                $shipping_costs = 0;
                $shipping_name = JText::_('COM_BREEZINGCOMMERCE_ELECTRONIC_DELIVERY');
                $shipping_plugin = JText::_('COM_BREEZINGCOMMERCE_NO_SHIPPING');
            }
            
            $update .= ', selected_shipping_id='.$db->quote($shipping_id);
            $update .= ', plugin_id='.$db->quote($shipping_plugin_id);
            $update .= ', history_shipping_costs='.$db->quote($shipping_costs);
            $update .= ', history_shipping_name='.$db->quote($shipping_name);
            $update .= ', history_shipping_plugin='.$db->quote($shipping_plugin);
            
            if($payment_plugin){
                
                $payment_plugin_info = '';
                
                $class = CrBcPlugin::getPluginClass($payment_plugin->name, $payment_plugin->type, 'admin');
                $instance = new $class();
                $payment_plugin = $instance->getPluginDisplayName();
                if(!$_order_info->paid){
                    $payment_plugin_info = $plugin_instance->getPaymentInfo();
                }else{
                    $payment_plugin_info = $plugin_instance->getAfterPaymentInfo();
                }
                
                $update .= ', history_payment_plugin='.$db->quote($payment_plugin);
                $update .= ', history_payment_plugin_info='.$db->quote($payment_plugin_info);
            }
            
            $db->setQuery("
                    Update 
                        #__breezingcommerce_orders 
                    Set 
                        history_symbol = ".$db->quote($_cart->currency_symbol).",
                        history_currency_code = ".$db->quote($_cart->currency_code).",
                        history_conversion_rate = ".$db->quote($_cart->currency_conversion_rate).",
                        order_status_id = ".intval($default_order_status_id).",
                        payment_plugin_id = ".intval($payment_plugin_id)."
                        ".$update."
                    Where
                        id = ".intval($_session_cart['order_id'])."
                    ");
            
            $db->execute();
            
            // no payment required, go straight to checkout
            if($_order_info->grand_total + $shipping_costs <= 0){

                $checkout_result = $this->checkout(intval($_session_cart['order_id']));

                if($checkout_result){

                    $update = '';
                    $update .= ', history_payment_plugin='.$db->quote(JText::_('COM_BREEZINGCOMMERCE_FREE_CHECKOUT'));
                    $update .= ', history_payment_plugin_info='.$db->quote(JText::_('COM_BREEZINGCOMMERCE_FREE_CHECKOUT_INFO'));
                    
                    $db->setQuery("
                            Update 
                                #__breezingcommerce_orders 
                            Set 
                                paid = 1
                                ".$update."
                            Where
                                id = ".intval($_session_cart['order_id'])."
                            ");

                    $db->execute();
                    
                    // only paid products (like ones that sum up in an entirely free cart) 
                    // will allow an automatic verification
                    $this->verifyItems(intval($_session_cart['order_id']));
                    
                    // same goes for files
                    $this->verifyFiles(intval($_session_cart['order_id']));
                    
                    $results = CrBcCart::getCustomerResults($_order_info, $data, $_order_info->customer);
                    
                    $email = $results['email'];
                    $result = $results['result'];
                    $result2 = $results['result2'];
                    $customer_result = $results['customer_result'];

                    $order = $this->getTable('order');

                    if(!$order->load(intval($_session_cart['order_id']))){
                        throw new Exception('Could not load the order!');
                    }
                    
                    CrBcCart::sendStatusMail(
                            $order, 
                            $email, 
                            $result, 
                            $result2, 
                            $customer_result, 
                            intval($_session_cart['order_id'])
                    );
                    
                    $this->stockUpdate(intval($_session_cart['order_id']));
                    
                    if(!$_cart->hasLimitedAddressData()){
                        $this->sendAndCreateInvoice(intval($_session_cart['order_id']));
                    }
                    
                    $this->sendInvoiceAdmins(intval($_session_cart['order_id']));
                    
                    return 'thankyou';

                } else{

                    return JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_ERROR');
                }

            }

            // payment required, check if the payment plugin exists and send to the payment first
        
            if($plugin_instance instanceof CrBcPaymentPlugin){
                
                $suitable = true;
                if(method_exists($plugin_instance, 'isPaymentSuitable')){
                    $suitable = $plugin_instance->isPaymentSuitable();
                    if($suitable === false){
                        return JText::_('COM_BREEZINGCOMMERCE_PAYMENT_NOT_SUITABLE');
                    }
                }
                
                // manual payment? Then do an immediate checkout as well but don't perform a payment,
                // keeps the "paid" flag off
                if(method_exists($plugin_instance, 'isManualPayment') && $plugin_instance->isManualPayment()){
                    
                    $checkout_result = $this->checkout(intval($_session_cart['order_id']));

                    if($checkout_result){

                        // product verification is not allowed here, so it won't appear here
                        
                        $results = CrBcCart::getCustomerResults($_order_info, $data, $_order_info->customer);
                    
                        $email = $results['email'];
                        $result = $results['result'];
                        $result2 = $results['result2'];
                        $customer_result = $results['customer_result'];

                        $order = $this->getTable('order');

                        if(!$order->load(intval($_session_cart['order_id']))){
                            throw new Exception('Could not load the order!');
                        }
                        
                        CrBcCart::sendStatusMail(
                                $order, 
                                $email, 
                                $result, 
                                $result2, 
                                $customer_result, 
                                intval($_session_cart['order_id'])
                        );

                        $this->stockUpdate(intval($_session_cart['order_id']));

                        if(!$_cart->hasLimitedAddressData()){
                            $this->sendAndCreateInvoice(intval($_session_cart['order_id']));
                        }
                        
                        $this->sendInvoiceAdmins(intval($_session_cart['order_id']));
                        
                        return 'thankyou';

                    } else{

                        return JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_ERROR');
                    }
                }
                else
                {
                    return 'payment';
                }
                
            }
            else{
                
                return JText::_('COM_BREEZINGCOMMERCE_PAYMENT_OPTION_NOT_FOUND_ERROR');
            }
        }
        
        return $result;
    }
    
    function getInformationData($checkout_information1){
        //// checkout information BEGIN
        
        if(!class_exists('ContentHelperRoute')) require_once (JPATH_SITE . '/components/com_content/helpers/route.php'); 
        
        $db = JFactory::getDbo();
        
        $lang = JFactory::getLanguage();

        $checkout_information_article1_link = '';
        $checkout_information_article1_title = '';
        $checkout_information_article1_route = '';

        $checkout_information_article1 = $checkout_information1;

        $checkout_information_article1 = explode(',', $checkout_information_article1);
        $checkout_information_article1_size = count($checkout_information_article1);

        for($i = 0; $i < $checkout_information_article1_size; $i++){

            $info = explode(':',$checkout_information_article1[$i]);

            if(count($info) == 2 && $info[0] == $lang->getTag()){
                $db->setQuery("Select catid, language, title From #__content Where id = " . $db->quote($info[1]));
                $result = $db->loadObject();
                if(is_object($result)){
                    $checkout_information_article1_route = ContentHelperRoute::getArticleRoute($info[1], $result->catid, $result->language);
                    $checkout_information_article1_title = $result->title;
                    break;
                }
            }
        }

        if($checkout_information_article1_route != ''){

            $checkout_information_article1_link = JRoute::_($checkout_information_article1_route.(strstr($checkout_information_article1_route,'?') !== false ? '&' : '?').'tmpl=component');
        }
        
        return array('link' => $checkout_information_article1_link, 'title' => $checkout_information_article1_title);

        //// checkout information END
    }
    
    function submitOrder(){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        $_cart = new CrBcCart( $_session_cart );
        
        // re-retrieving the session cart as CrBcCart does integrity tests
        $_session_cart = $_cart->getArray();
        
        if( isset($_session_cart['checkout']['.crbc-checkout-order-review-information_done']) ){
            unset($_session_cart['checkout']['.crbc-checkout-order-review-information_done']);
        }
        
        if( isset($_session_cart['checkout']['checkout_confirm_information1']) ){
            unset($_session_cart['checkout']['checkout_confirm_information1']);
        }
        
        if( isset($_session_cart['checkout']['checkout_confirm_information2']) ){
            unset($_session_cart['checkout']['checkout_confirm_information2']);
        }
        
        if( isset($_session_cart['checkout']['checkout_confirm_information3']) ){
            unset($_session_cart['checkout']['checkout_confirm_information3']);
        }
        
        if( isset($_session_cart['checkout']['checkout_confirm_information4']) ){
            unset($_session_cart['checkout']['checkout_confirm_information4']);
        }
        
        $config = CrBcHelpers::getBcConfig();
        
        $errors = array();
        
        if(!isset($_session_cart['order_id'])){
            $errors[] = array('field' => '', 'type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_INVALID_ORDER'));
        }
        
        // do we even need to check for the info confirmation? getInformationData will give us a clue
        $info = $this->getInformationData($config->get('checkout_information1', ''));
        
        if($info['link'] != '' && $config->get('checkout_confirm_information1', 0) && !JRequest::getBool('checkout_confirm_information_article1', false)){
            $errors[] = array('field' => 'checkout_confirm_information_article1', 'type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_CONFIRM_ORDER'));
        } else if($info['link'] != '' && $config->get('checkout_confirm_information1', 0)){
            $_session_cart['checkout']['checkout_confirm_information1'] = true;
        }
        
        $info = $this->getInformationData($config->get('checkout_information2', ''));
        
        if($info['link'] != '' && $config->get('checkout_confirm_information2', 0) && !JRequest::getBool('checkout_confirm_information_article2', false)){
            $errors[] = array('field' => 'checkout_confirm_information_article2', 'type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_CONFIRM_ORDER'));
        } else if($info['link'] != '' && $config->get('checkout_confirm_information2', 0)){
            $_session_cart['checkout']['checkout_confirm_information2'] = true;
        }
        
        $info = $this->getInformationData($config->get('checkout_information3', ''));
        
        if($info['link'] != '' && $config->get('checkout_confirm_information3', 0) && !JRequest::getBool('checkout_confirm_information_article3', false)){
            $errors[] = array('field' => 'checkout_confirm_information_article3', 'type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_CONFIRM_ORDER'));
        } else if($info['link'] != '' && $config->get('checkout_confirm_information3', 0)){
            $_session_cart['checkout']['checkout_confirm_information3'] = true;
        }
        
        $info = $this->getInformationData($config->get('checkout_information4', ''));
        
        if($info['link'] != '' && $config->get('checkout_confirm_information4', 0) && !JRequest::getBool('checkout_confirm_information_article4', false)){
            $errors[] = array('field' => 'checkout_confirm_information_article4', 'type' => 'error', 'message' => JText::_('Please confirm to place your order'));
        } else if($info['link'] != '' && $config->get('checkout_confirm_information4', 0)){
            $_session_cart['checkout']['checkout_confirm_information4'] = true;
        }
        
        if(count($errors) == 0){
            
            $_session_cart['checkout']['.crbc-checkout-order-review-information_done'] = true;
            
            // prepare redirect either to payment (if total > 0) or bypass payment if total == 0.
            // also perform verifications after payments or if a payment is not required
            
            $_cart_items = $_cart->getItems(true);

            $data = CrBcCart::getData($_session_cart['order_id'], $_cart_items, -1, -1);

            $_order_info = CrBcCart::getOrder(
                                        $_session_cart['order_id'], 
                                        $_cart, 
                                        $_session_cart, 
                                        $_cart_items, 
                                        $_session_cart['customer_id'],
                                        $data,
                                        array(),
                                        array()
                                );
            
            $shipping_costs = @$_session_cart['checkout']['shipping_plugin_data']['shipping_costs'];
            
            if($_order_info->grand_total + $shipping_costs <= 0){
                $_session_cart['checkout']['.crbc-checkout-payment-information_done'] = true;
                $_session_cart['checkout']['payment_plugin_id'] = 0;
            }
        }
        
        JFactory::getSession()->set('crbc_cart', $_session_cart);
        
        return $errors;
    }
    
    function updatePaymentInformation(){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $db = JFactory::getDbo();
        $data = JRequest::get( 'post' );
        $errors = array();
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        $_cart = new CrBcCart( $_session_cart );
        // re-retrieving the session cart as CrBcCart does integrity tests
        $_session_cart = $_cart->getArray();
        
        if(!isset($data['payment_option'])){
            
            if(isset($_session_cart['checkout']['.crbc-checkout-payment-information_done'])){
                unset($_session_cart['checkout']['.crbc-checkout-payment-information_done']);
            }
            if(isset($_session_cart['checkout']['payment_plugin_id'])){
                unset($_session_cart['checkout']['payment_plugin_id']);
            }
            JFactory::getSession()->set('crbc_cart', $_session_cart);
            
            $errors[] = array('type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_NO_PAYMENT_OPTION_ERROR'));
            return $errors;
        }
        
        $payment_plugin_id = intval($data['payment_option']);
        
        $db->setQuery("Select * From #__breezingcommerce_plugins Where id = " . $payment_plugin_id . " And published = 1");
        $plugin = $db->loadObject();
        
        if(!$plugin){
            
            if(isset($_session_cart['checkout']['.crbc-checkout-payment-information_done'])){
                unset($_session_cart['checkout']['.crbc-checkout-payment-information_done']);
            }
            if(isset($_session_cart['checkout']['payment_plugin_id'])){
                unset($_session_cart['checkout']['payment_plugin_id']);
            }
            JFactory::getSession()->set('crbc_cart', $_session_cart);
            $errors[] = array('type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_PAYMENT_OPTION_NOT_FOUND_ERROR'));
            return $errors;
        }
        
        $plugin_instance = CrBcPlugin::getPluginInstance($plugin->name, $plugin->type, 'site');
        if(method_exists($plugin_instance, 'isPaymentSuitable') && $plugin_instance instanceof CrBcPaymentPlugin){
            $result = $plugin_instance->isPaymentSuitable();
            if($result === false){
                
                if(isset($_session_cart['checkout']['.crbc-checkout-payment-information_done'])){
                    unset($_session_cart['checkout']['.crbc-checkout-payment-information_done']);
                }
                if(isset($_session_cart['checkout']['payment_plugin_id'])){
                    unset($_session_cart['checkout']['payment_plugin_id']);
                }
                JFactory::getSession()->set('crbc_cart', $_session_cart);
                $errors[] = array('type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_PAYMENT_OPTION_NOT_AVAILABLE_INFO'));
                return $errors;
            }
        }
        
        $_session_cart['checkout']['.crbc-checkout-payment-information_done'] = true;
        $_session_cart['checkout']['payment_plugin_id'] = $payment_plugin_id;
        JFactory::getSession()->set('crbc_cart', $_session_cart);
        
        return $errors;
    }
    
    function updateBillingInformation($shipping = false){
        
        $config = CrBcHelpers::getBcConfig();
        
        if($shipping){
            $shipping = 'shipping_';
        } else {
            $shipping = '';
        }
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $db = JFactory::getDbo();
        
        $data = JRequest::get( 'post' );
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        
        $_cart = new CrBcCart( $_session_cart );
        
        // re-retrieving the session cart as CrBcCart does integrity tests
        $_session_cart = $_cart->getArray();
        
        // resetting the done infos to none initially
        if(isset($_session_cart['checkout'])){
            
            if($shipping){
                
                unset($_session_cart['checkout']['.crbc-checkout-shipping-information_done']);
                
            }else{
                
                unset($_session_cart['checkout']['.crbc-checkout-billing-information_done']);
            }
            
            if(isset($data['register']) && $data['register'] == '0'){
                
                if(isset($_session_cart['checkout']['register']) && $_session_cart['checkout']['register'] == '1' && isset($_session_cart['checkout']['.crbc-checkout-method-guest-register-login_done'])){
                    
                    unset($_session_cart['checkout']['.crbc-checkout-method-guest-register-login_done']);
                    
                    if(isset($_session_cart['checkout']['userid'])){
                    
                        unset($_session_cart['checkout']['userid']);
                    }
                }
                
                $_session_cart['checkout']['register'] = 0;
                
            } else if(isset($data['register']) && $data['register'] == '1'){
                
                if(isset($_session_cart['checkout']['register']) && $_session_cart['checkout']['register'] == '0' && isset($_session_cart['checkout']['.crbc-checkout-method-guest-register-login_done'])){
                    
                    unset($_session_cart['checkout']['.crbc-checkout-method-guest-register-login_done']);
                }
                
                $_session_cart['checkout']['register'] = 1;
            }
            
            JFactory::getSession()->set('crbc_cart', $_session_cart);
        }
        
        if(isset( $_session_cart['order_id'] ) && isset($_cart->cart['customer_id']) && intval( $_cart->cart['customer_id'] ) > 0){
            
            $data['id'] = intval( $_cart->cart['customer_id'] );
        }
        
        $errors = array();
        
        if(JFactory::getUser()->get('id', 0) == 0 && $config->get('allow_guest_checkout') == 0 && isset($data['register']) && $data['register'] == '0'){
            $errors[] = array('field' => '__registration', 'type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_AS_GUEST_ERROR'));
        }
        
        if(!isset($data[$shipping.'firstname']) || trim($data[$shipping.'firstname']) == ''){
            $errors[] = array('field' => $shipping.'firstname', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_FIRSTNAME_REQUIRED'));
        }
        
        if(!isset($data[$shipping.'lastname']) || trim($data[$shipping.'lastname']) == ''){
            $errors[] = array('field' => $shipping.'lastname', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_LASTNAME_REQUIRED'));
        }

        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_breezingcommerce' . DS . 'classes' . DS . 'CrBcHelpers.php');

        if(!isset($data[$shipping.'email']) || !CrBcHelpers::isEmail($data[$shipping.'email'])){
            $errors[] = array('field' => $shipping.'email', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_MISSING_OR_INVALID_EMAIL_ADDRESS'));
        }
		
		if(!isset($data[$shipping.'email_repeat']) || !isset($data[$shipping.'email']) || $data[$shipping.'email'] != $data[$shipping.'email_repeat']){
            $errors[] = array('field' => $shipping.'email_repeat', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_EMAIL_REPEAT_NOMATCH'));
			$errors[] = array('field' => $shipping.'email', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_EMAIL_REPEAT_NOMATCH'));
        }
        
        $country_error = false;
        if(!isset($data[$shipping.'country_id']) || trim($data[$shipping.'country_id']) == ''){
            $country_error = true;
            $errors[] = array('field' => $shipping.'country_id', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_COUNTRY_REQUIRED'));
        }
        
        $db->setQuery("Select count(id) From #__breezingcommerce_country_regions Where country_id = " . intval($data[$shipping.'country_id']));
        $amount_regions = $db->loadResult();
        
        if($amount_regions > 0 || $country_error){
            if(!isset($data[$shipping.'region_id']) || trim($data[$shipping.'region_id']) == '' || trim($data[$shipping.'region_id']) == '*' || trim($data[$shipping.'region_id']) == '0'){
                $errors[] = array('field' => $shipping.'region_id', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_REGION_REQUIRED'));
            }
        }

        if( !$_cart->hasLimitedAddressData() ){
        
            if(!isset($data[$shipping.'address']) || trim($data[$shipping.'address']) == ''){
                $errors[] = array('field' => $shipping.'address', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_ADDRESS_REQUIRED'));
            }

            if(!isset($data[$shipping.'city']) || trim($data[$shipping.'city']) == ''){
                $errors[] = array('field' => $shipping.'city', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_CITY_REQUIRED'));
            }

            if(!isset($data[$shipping.'zip']) || trim($data[$shipping.'zip']) == ''){
                $errors[] = array('field' => $shipping.'zip', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_ZIP_REQUIRED'));
            }

            //if(!isset($data[$shipping.'phone']) || trim($data[$shipping.'phone']) == ''){
            //    $errors[] = array('field' => $shipping.'phone', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_PHONE_REQUIRED'));
            //}
        
        }

        if(count($errors) == 0 && !isset($_session_cart['checkout']['.crbc-checkout-method-guest-register-login_done']) && $shipping == '' && JFactory::getUser()->get('id', 0) < 1 && isset($data['register']) && $data['register'] == '1'){

            if(isset($data['userid']) && intval($data['userid']) < 0){
                $data['userid'] = 0;
            }
            
            $inner_errors = array();

            if(!isset($data['username']) || trim($data['username']) == ''){
                $inner_errors[] = array('field' => 'username', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_USERNAME_REQUIRED'));
            }
            else if( preg_match( "#[<>\"'%;()&]#i", $data['username']) || strlen(utf8_decode($data['username'] )) < 2 )
            {
                $inner_errors[] = array('field' => 'username', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_INVALID_USERNAME').': <>"\'%;()&');
            }
            
            if(!isset($data['password'])  || trim($data['password']) == ''){
                $inner_errors[] = array('field' => 'password', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_PASSWORD_REQUIRED'));
            }
            
            if(!isset($data['password2'])  || trim($data['password2']) == ''){
                $inner_errors[] = array('field' => 'password2', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_PASSWORD_REPEAT_REQUIRED'));
            }
            
            if(!count($inner_errors)){

                if (function_exists('mb_strlen')) {
                    
                    if (mb_strlen($data['password']) < 8) {
                        $errors[] = array('field' => 'password', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_PASSWORD_LENGTH_INFO'));
                    }
                    
                } else {
                    
                    if (strlen($data['password']) < 8) {
                        $errors[] = array('field' => 'password', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_PASSWORD_LENGTH_INFO'));
                    }
                }
                
                if($data['password'] != $data['password2']) {
                    $errors[] = array('field' => 'password2', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_PASSWORD_NO_MATCH'));
                }
                
                if(!count($errors)){

                    $result = CrBcHelpers::registerUser(trim($data['firstname']) . ' ' . trim($data['lastname']), $data['username'], $data['email'], $data['password']);

                    if ($result !== false && is_numeric($result))
                    {
                        
                        $data['userid'] = $result;

                        if(!isset($_session_cart['checkout'])){
                            $_session_cart['checkout'] = array();
                        }

                        $_session_cart['checkout']['.crbc-checkout-method-guest-register-login_done'] = true;
                        $_session_cart['checkout']['userid'] = $data['userid'];

                    } else {

                        if($result === false || $result == ''){

                            $errors[] = array('field' => '__registration', 'type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_ACCOUNT_REGISTRATION_ERROR'));

                        }else{

                            $errors[] = array('field' => '__registration', 'type' => 'error', 'message' => $result);
                        }
                    }
                
                }
            }
            
            $errors = array_merge($errors, $inner_errors);
            
        }
        
        // return all errors before performing any action
        if(count($errors)){
            
            return $errors;
        }
        
        // some integrity checking
        if(JFactory::getUser()->get('id', 0) > 0) {
            
            // determine user id if any, then check if it's already a customer and determine his customer group
            // also override the customer id to make sure to prevent more than 1 customer entries for a user
            
            $data['userid'] = JFactory::getUser()->get('id', 0);
            
            $_session_cart['checkout']['.crbc-checkout-method-guest-register-login_done'] = true;
            $_session_cart['checkout']['userid'] = $data['userid'];
            
            $db->setQuery("Select customergroup_id, id From #__breezingcommerce_customers Where `userid` = " . intval($data['userid']) . " Limit 1");
            $customer = $db->loadObject();
            
            if($customer !== null){
                
                $data['customergroup_id'] = $customer->customergroup_id;
                $data['id'] = $customer->id;
                
                $user_ = new JUser;
                $user_->load(JFactory::getUser()->get('id', 0));
                $user_data['email'] = JStringPunycode::emailToPunycode($data[$shipping.'email']);
                
                if (!$user_->bind($user_data))
                {
                    $errors[] = array('field' => '__registration', 'type' => 'error', 'message' => JText::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()));
                }
                
                // Load the users plugin group.
                JPluginHelper::importPlugin('user');

                // Store the data.
                if (!$user_->save())
                {
                    $errors[] = array('field' => '__registration', 'type' => 'error', 'message' => JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
                }  
            }
        }
        
        if($shipping == ''){
            
            if(!isset($data['use_shipping_address'])){
                
                $data['use_shipping_address'] = 0;
                
            }else{
                
                $data['use_shipping_address'] = intval($data['use_shipping_address']) != 0 ? 1 : 0;
            }
        }
        
        // upon first save, the customer isn't temporary no longer (first contact)
        $data['is_tmp'] = 0;

        if(!$_cart->hasLimitedAddressData()){
            
            $data['address_complete'] = 1;
        }
        
        require_once(JPATH_SITE . '/administrator/components/com_breezingcommerce/tables/customer.php');
        $row = new TableCustomer($db);
        
        if (!$row->bind($data)) {
            throw new Exception('Error binding '.($shipping == '' ? 'billing' : 'shipping').' data');
        }

        if (!$row->check()) {
            throw new Exception('Error checking '.($shipping == '' ? 'billing' : 'shipping').' data');
        }

        if (!$row->store()) {
            throw new Exception('Error storing '.($shipping == '' ? 'billing' : 'shipping').' data');
        }
        
        // TODO: is this really necessary any longer?
        if(isset($data['id']) && $data['id'] > 0){
            $row->load($data['id']);
            if( $row->customergroup_id == 0 ){
                $db->setQuery("Select id From #__breezingcommerce_customergroups Where `default` = 1");
                $default_customer_group_id = intval($db->loadResult());
                $db->setQuery("Update #__breezingcommerce_customers Set customergroup_id = " . $db->quote($default_customer_group_id) . " Where id = " . $db->quote($data['id']));
                $db->execute();
            }
        }
        
        if(!isset($_session_cart['checkout'])){
            $_session_cart['checkout'] = array();
        }
        
        if($shipping){
            $_session_cart['checkout']['.crbc-checkout-shipping-information_done'] = true;
        } else {
            $_session_cart['checkout']['.crbc-checkout-billing-information_done'] = true;
        }
        
        // virtual orders don't need shipping, so we exclude it from being required as soon as the billing information has been stored
        if($_cart->isVirtualOrder($_cart->getItems(true)) || (isset($data['use_shipping_address']) && $data['use_shipping_address'] == '0')){
            $_session_cart['checkout']['.crbc-checkout-shipping-information_done'] = true;
        }
        
        // registration not required? then set it to done as well
        if(!isset($data['register']) || $data['register'] != '1'){
            $_session_cart['checkout']['.crbc-checkout-method-guest-register-login_done'] = true;
            $_session_cart['checkout']['userid'] = 0;
        }
        
        JFactory::getSession()->set('crbc_cart', $_session_cart);
        
        return $errors;
        
    }
    
	function resetShippingMethod(){
		
		 $db = JFactory::getDbo();
		
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
		
        $_cart = new CrBcCart( $_session_cart );
        
		// re-retrieving the session cart as CrBcCart does integrity tests
        $_session_cart = $_cart->getArray();
		
		if(isset($_session_cart['checkout']['.crbc-checkout-shipping-method_done'])){
			unset($_session_cart['checkout']['.crbc-checkout-shipping-method_done']);
		}
		
		if(isset($_session_cart['checkout']['shipping_plugin_id'])){
			unset($_session_cart['checkout']['shipping_plugin_id']);
		}
		
		if(isset($_session_cart['checkout']['shipping_plugin_data'])){
			unset($_session_cart['checkout']['shipping_plugin_data']);
		}
		
		$db->setQuery("
                    Update 
                        #__breezingcommerce_orders 
                    Set 
                        plugin_id = 0,
                        selected_shipping_id = 0
                    Where
                        id = ".intval($_session_cart['order_id'])."
                    ");
            
		$db->execute();
		
		JFactory::getSession()->set('crbc_cart', $_session_cart);
		
		return array('errors' => array(), 'shipping' => array(
			'shipping_id' => 0, 
			'shipping_costs' => 0,
			'shipping_name' => '',
			'shipping_plugin' => ''
		));
	}
	
    function updateShippingMethod(){
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        if(!isset($_session_cart['checkout'])){
            $_session_cart['checkout'] = array();
        }
        
        $db = JFactory::getDbo();
        $data = JRequest::get( 'post' );
        $errors = array();
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        $_cart = new CrBcCart( $_session_cart );
        // re-retrieving the session cart as CrBcCart does integrity tests
        $_session_cart = $_cart->getArray();
        $_cart_items = $_cart->getItems(true);
        
        if($_cart->isVirtualOrder($_cart_items)){
            
            $_session_cart['checkout']['shipping_plugin_id'] = 0;
            $_session_cart['checkout']['.crbc-checkout-shipping-method_done'] = true;
            $_session_cart['checkout']['shipping_plugin_data'] = array(
                'shipping_id' => 0, 
                'shipping_costs' => 0,
                'shipping_name' => '',
                'shipping_plugin' => ''
            );
            JFactory::getSession()->set('crbc_cart', $_session_cart);
            
            return $errors;
        }
        
        if(!isset($data['shipping_option'])){
            
            if(isset($_session_cart['checkout']['.crbc-checkout-shipping-method_done'])){
                unset($_session_cart['checkout']['.crbc-checkout-shipping-method_done']);
            }
            if(isset($_session_cart['checkout']['shipping_plugin_id'])){
                unset($_session_cart['checkout']['shipping_plugin_id']);
            }
            if(isset($_session_cart['checkout']['shipping_plugin_data'])){
                unset($_session_cart['checkout']['shipping_plugin_data']);
            }
            JFactory::getSession()->set('crbc_cart', $_session_cart);
            
            $errors[] = array('type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_NO_SHIPPING_METHOD_ERROR'));
            return $errors;
        }
        
        $shipping_plugin_id = intval($data['shipping_option']);
        
        $db->setQuery("Select * From #__breezingcommerce_plugins Where id = " . $shipping_plugin_id . " And published = 1");
        $plugin = $db->loadObject();
        
        if(!$plugin){
            
            if(isset($_session_cart['checkout']['.crbc-checkout-shipping-method_done'])){
                unset($_session_cart['checkout']['.crbc-checkout-shipping-method_done']);
            }
            if(isset($_session_cart['checkout']['shipping_plugin_id'])){
                unset($_session_cart['checkout']['shipping_plugin_id']);
            }
            if(isset($_session_cart['checkout']['shipping_plugin_data'])){
                unset($_session_cart['checkout']['shipping_plugin_data']);
            }
            JFactory::getSession()->set('crbc_cart', $_session_cart);
            $errors[] = array('type' => 'error', 'message' => JText::_('COM_BREEZINGCOMMERCE_SHIPPING_METHOD_NOT_FOUND_ERROR'));
            return $errors;
        }
        
        $plugin_instance = CrBcPlugin::getPluginInstance($plugin->name, $plugin->type, 'site');
        
        if ($plugin_instance instanceof CrBcShippingSitePlugin) {
            
            $invalid = $plugin_instance->validateSelectedOption(JRequest::getInt($plugin_instance->getOptionElementName(), 0), $this->_id);
            
            if( $invalid ){
                
                $errors[] = array('type' => 'error', 'message' => $plugin_instance->getPluginDisplayName() . ': ' . $invalid);
                
            } else {
            
                $plugin_instance->init($_cart);
                $plugin_instance->onOptionUpdate(
                        JRequest::getInt($plugin_instance->getOptionElementName(), 0),
                        $this->_id
                );
                $selected_shipping_id = JRequest::getInt($plugin_instance->getOptionElementName(), 0);
                $shipping_costs = $plugin_instance->getPrice($selected_shipping_id);
                $shipping_product_tax_class_id = $plugin_instance->getProductTaxclassId($selected_shipping_id);
                CrBcCart::$exclude_from_no_tax_list[] = $shipping_product_tax_class_id;
                $price_calc = CrBcCart::getPrice($shipping_costs, 1, $shipping_product_tax_class_id, 1, $_cart->cart['customer_id']);
                $shipping_costs = $price_calc['gross'];
                $shipping_name = $plugin_instance->getName($selected_shipping_id);
                $shipping_plugin = $plugin_instance->getPluginDisplayName();

                $_session_cart['checkout']['shipping_plugin_id'] = intval($data['shipping_option']);
                $_session_cart['checkout']['shipping_plugin_data'] = array(
                    'shipping_id' => $selected_shipping_id, 
                    'shipping_costs' => $shipping_costs,
                    'shipping_name' => $shipping_name,
                    'shipping_plugin' => $shipping_plugin
                );
                
                $db->setQuery("
                    Update 
                        #__breezingcommerce_orders 
                    Set 
                        plugin_id = ".$db->quote($shipping_plugin_id).",
                        selected_shipping_id = ".$db->quote($selected_shipping_id)."
                    Where
                        id = ".intval($_session_cart['order_id'])."
                    ");
            
                $db->execute();
                
                $_session_cart['checkout']['.crbc-checkout-shipping-method_done'] = true;

                JFactory::getSession()->set('crbc_cart', $_session_cart);
            
            }
            
        }
        
        if(count($errors) != 0){
            
            if(isset($_session_cart['checkout']['.crbc-checkout-shipping-method_done'])){
                unset($_session_cart['checkout']['.crbc-checkout-shipping-method_done']);
            }
            if(isset($_session_cart['checkout']['shipping_plugin_id'])){
                unset($_session_cart['checkout']['shipping_plugin_id']);
            }
            if(isset($_session_cart['checkout']['shipping_plugin_data'])){
                unset($_session_cart['checkout']['shipping_plugin_data']);
            }
            JFactory::getSession()->set('crbc_cart', $_session_cart);
            return $errors;
            
        }
        
        return array('errors' => $errors, 'shipping' => $_session_cart['checkout']['shipping_plugin_data']);
    }
    
    function validateCheckoutProgress($can_enter){
        
        if($can_enter == '.crbc-checkout-method-guest-register-login' ){
            return true;
        }
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        
        $_cart = new CrBcCart( $_session_cart );
        $_cart_items = $_cart->getItems(true);
        
        if(count($_cart_items) == 0){
            return false;
        }
        
        if(!isset($_session_cart['checkout']) || count($_session_cart['checkout']) == 0){
            return false;
        }
        
        /*
        $steps = array();
        
        // this indicates the exact order of steps to take
        //if(JFactory::getUser()->get('id', 0) == 0){
        //    $steps[] = '.crbc-checkout-method-guest-register-login';
        //}
        
        $steps[] = '.crbc-checkout-billing-information';

        if(!$_cart->isVirtualOrder($_cart_items)){
            $steps[] = '.crbc-checkout-shipping-information';
            $steps[] = '.crbc-checkout-shipping-method';
        }

        $steps[] = '.crbc-checkout-payment-information';
        $steps[] = '.crbc-checkout-order-review-information';
        */
        
        // now trying to determine if the step we want move to, is actually valid
        
        if(!isset($_session_cart['checkout'][$can_enter.'_done'])){
            return false;   
        }
        
        return true;
    }
    
    public function verifyItems($order_id){
        
        $order_id = intval($order_id);
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        CrBcCart::verifyItems($order_id);
    }
    
    public function verifyFiles($order_id){
        
        $order_id = intval($order_id);
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        CrBcCart::verifyFiles($order_id);
    }
}
