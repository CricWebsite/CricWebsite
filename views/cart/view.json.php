<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class BreezingcommerceViewCart extends JViewLegacy
{
    function display($tpl = null)
    {
        // case of item price
        if( JRequest::getVar('layout') == 'json_get_item_price' ){
            
            $_session_cart = JFactory::getSession()->get('crbc_cart', array());
            $_cart = new CrBcCart( $_session_cart );
            
            $this->assignRef('cart_instance', $_cart);
            
            //JRequest::setVar('order_id', 52);
            JRequest::setVar('product_id', JRequest::getInt('product_id', 0));
            JRequest::setVar('order_item_id', JRequest::getInt('order_item_id', 0));
            JRequest::setVar('amount', JRequest::getInt('amount', 0));
            
            $price = $this->get('ItemPrice');
            $this->assignRef('price', $price);
            
            parent::display($tpl);
            
        } else if( JRequest::getVar('layout') == 'json_error' ){
			$error  = JRequest::getVar('error');
			$this->assignRef('error', $error);
			parent::display($tpl);
		}
        // default case: displaying the cart
        else {
        
            $_session_cart = JFactory::getSession()->get('crbc_cart', array());
            $_cart = new CrBcCart( $_session_cart );
            
            if(!isset( $_session_cart['order_id'] ) || intval($_session_cart['order_id']) < 1 ){
                $empty_cart = JText::_('COM_BREEZINGCOMMERCE_CART_EMPTY');
                $this->assignRef('empty_cart', $empty_cart);
                parent::display($tpl);
                return;
            }

            // using this if we'd like to restore it from database
            //$_cart = $_cart->restoreCart($this->_id); // we want to restore the cart from database

            $db = JFactory::getDbo();
            
            // SHIPPING PLUGIN INIT - BEGIN
            $db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'shipping' Order By `ordering`");
            $shipping_plugins = $db->loadAssocList();
            // SHIPPING PLUGIN INIT - END
            
            $customer = null;
            if(isset($_cart->cart['customer_id']) && $_cart instanceof CrBcCart){
                JFactory::getDbo()->setQuery("Select * From #__breezingcommerce_customers Where id = " . intval($_cart->cart['customer_id']) . " Limit 1");
                $customer = JFactory::getDbo()->loadObject();
            }

            $_cart_items = $_cart->getItems(true);

            $data = CrBcCart::getData($_session_cart['order_id'], $_cart_items, -1, -1);

            $_order_info = CrBcCart::getOrder(
                                        $_session_cart['order_id'], 
                                        $_cart, 
                                        $_session_cart, 
                                        $_cart_items, 
                                        $customer,
                                        $data,
                                        $shipping_plugins,
                                        array()
                                );

            $config = CrBcHelpers::getBcConfig();
            $this->assignRef('config', $config);

            $this->assignRef('cart_instance', $_cart);
            $this->assignRef('order_info', $_order_info);
            $this->assignRef('items', $data);

            $return_url = '';
            $this->assignRef('return_url', $return_url);
            if(trim(JFactory::getApplication()->input->get('return_url', '')) != ''){
                $return_url = trim(JFactory::getApplication()->input->get('return_url', ''));
                $return_url = CrBcHelpers::bSixtyFourDecode($return_url);
                if(JUri::isInternal($return_url)){
                    // for json return we re-wrap the url again
                    // as it's not used to redirect from this type of format anyway
                    $return_url = CrBcHelpers::bSixtyFourEncode($return_url);
                    $this->assignRef('return_url', $return_url);
                }
            }

            $lookup_item_id = 0;
            $lookup_product_id = 0;
            $lookup_amount = 1;
            if(JRequest::getInt('lookup_product_id',0) > 0 && JRequest::getInt('lookup_item_id',0) > 0){
                $lookup_product_id = JRequest::getInt('lookup_product_id', 0);
                $lookup_item_id = JRequest::getInt('lookup_item_id', 0);
                $lookup_amount = JRequest::getInt('lookup_amount', 0);
            }
            
            $this->assignRef('lookup_product_id', $lookup_product_id);
            $this->assignRef('lookup_item_id', $lookup_item_id);
            $this->assignRef('lookup_amount', $lookup_amount);
            
            parent::display($tpl);
        
        }
    }
}
