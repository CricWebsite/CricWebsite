<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class BreezingcommerceControllerCheckout extends JControllerLegacy
{
    function __construct()
    {
        parent::__construct();
    }
    
    function validate_checkout_progress(){
        
        $cart = $this->getModel('checkout');
        
        $result = $cart->validateCheckoutProgress(JRequest::getVar('can_enter',''));
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_validate_checkout_progress_result');
            
        }
        
        JRequest::setVar('validate_checkout_progress_result', $result);
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'checkout');
        parent::display();
        
    }
    
    function get_user_data(){
       
        $cart = $this->getModel('checkout');
        
        try{
        
            $userdata = $cart->getUserData();
            if($userdata){
                $userdata->info = JText::_('COM_BREEZINGCOMMERCE_REGISTERED_WITH_USERNAME');
            }
            
        }catch(Exception $e){
            
            $result = array(array('field' => '', 'type' => 'error', 'message' => $e));
        }
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_get_user_data');
            
        }
        
        JRequest::setVar('userdata', $userdata);
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'checkout');
        parent::display();
    }
    
    function update_order(){
        
        $cart = $this->getModel('checkout');
        
        try{
        
            $result = $cart->submitOrder();
            
        }catch(Exception $e){
            
            $result = array(array('type' => 'error', 'message' => $e));
        }
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_order_result');
            
        }
        
        JRequest::setVar('update_order_result', $result);
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'checkout');
        parent::display();
    }
    
    function update_payment_information(){
        
        $cart = $this->getModel('checkout');
        
        try{
        
            $result = $cart->updatePaymentInformation();
            
        }catch(Exception $e){
            
            $result = array(array('type' => 'error', 'message' => $e));
        }
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_payment_result');
            
        }
        
        JRequest::setVar('update_payment_result', $result);
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'checkout');
        parent::display();
    }
    
	function reset_shipping_method(){
        
        $cart = $this->getModel('checkout');
        
        try{
        
            $result = $cart->resetShippingMethod();
            
        }catch(Exception $e){
            
            $result = array(array('type' => 'error', 'message' => $e));
        }
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_shipping_method_result');
            
        }
        
        JRequest::setVar('shipping_method_result', $result);
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'checkout');
        parent::display();
    }
	
    function update_shipping_method(){
        
        $cart = $this->getModel('checkout');
        
        try{
        
            $result = $cart->updateShippingMethod();
            
        }catch(Exception $e){
            
            $result = array(array('type' => 'error', 'message' => $e));
        }
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_shipping_method_result');
            
        }
        
        JRequest::setVar('shipping_method_result', $result);
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'checkout');
        parent::display();
    }
    
    function update_billing_information(){
       
        $cart = $this->getModel('checkout');
        
        try{
        
            $result = $cart->updateBillingInformation( JFactory::getApplication()->input->getBool('shipping') );
            $userdata = $cart->getUserData();
            
        }catch(Exception $e){
            
            $result = array(array('field' => '', 'type' => 'error', 'message' => $e));
        }
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_billing_result');
            
        }
        
        JRequest::setVar('update_billing_result', $result);
        JRequest::setVar('userid', $userdata ? $userdata->id : 0);
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'checkout');
        parent::display();
    }
    
    function perform_checkout(){
        
        JRequest::setVar('error', '');
        JRequest::setVar('payment_output','');
        
        $checkout = $this->getModel('checkout');
        
        if( JRequest::getBool('verify_payment', false) ){
            
            $result = $checkout->verifyPayment(JRequest::getInt('order_id', 0));
            
            if($result == 'thankyou'){
                
                require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
                $db = JFactory::getDbo();

                $_cart = new CrBcCart( array() );
                
                // restore the cart to make sure calling payment verifiers that don't rely on sessions
                // are able to verify (for example PayPal IPN)
                $_cart->restoreCart(intval(JRequest::getInt('order_id', 0)));
                
                // to make sure it is re-populated
                //JFactory::getSession()->set('crbc_cart', $_cart->getArray());
                
                $checkout_result = $checkout->checkout(JRequest::getInt('order_id', 0));
                
                if(!$checkout_result){
                    
                    $result = JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_ERROR');
                    
                } else {
                    
                    // only paid products will allow an automatic verification
                    $checkout->verifyItems(JRequest::getInt('order_id', 0));
                    
                    // same goes for files
                    $checkout->verifyFiles(JRequest::getInt('order_id', 0));
                    
                    // try to send a status mail
                    $checkout->sendStatusMail(JRequest::getInt('order_id', 0));
                    
                    // update the stock
                    $checkout->stockUpdate(JRequest::getInt('order_id', 0));
                    
                    $checkout->sendInvoiceAdmins(JRequest::getInt('order_id', 0));
                    
                    if(!$_cart->hasLimitedAddressData()){
                        // create the invoice and send if possible
                        $invoice_result = $checkout->sendAndCreateInvoice(JRequest::getInt('order_id', 0));

                        if(!$invoice_result){

                            $result = JText::_('COM_BREEZINGCOMMERCE_INVOICE_SEND_ERROR');
                        }
                    }
                }
            }
            
        } else {
        
            $result = $checkout->performCheckout();
        }
            
        switch($result){
            case 'payment_fail':
                JRequest::setVar('layout','payment_fail');
                break;
            case 'payment':
                $output = $checkout->initPayment();
                JRequest::setVar('payment_output',$output);
                JRequest::setVar('layout','payment');
            break;
            case 'thankyou':
                JRequest::setVar('layout','thankyou');
            break;
            default:
                JRequest::setVar('error', $result);
                JRequest::setVar('layout','error');
                
        }
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'checkout');
        parent::display();
    }
    
    function display($cachable = false, $urlparams = array())
    {
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            JRequest::setVar('layout','json_checkout');
        }
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'checkout');
        parent::display();
    }
}
