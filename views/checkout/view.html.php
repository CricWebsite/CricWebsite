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

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');

if(!class_exists('ContentHelperRoute')) require_once (JPATH_SITE . '/components/com_content/helpers/route.php'); 

class BreezingcommerceViewCheckout extends JViewLegacy
{
    function display($tpl = null)
    {
        $db = JFactory::getDbo();
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        $_cart = new CrBcCart( $_session_cart );
        $_cart_items = $_cart->getItems(true);

        $empty = false;
        
        if(count($_cart_items) == 0){
            $empty = true;
            $this->assignRef('empty', $empty);
        }
        
        // CART PLUGINS INIT - BEGIN
        $db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'cart' Order By `ordering`");
        $cart_plugins = $db->loadAssocList();

        $cart_plugin_instances = array();
        foreach( $cart_plugins As $plugin ){
            $plugin_instance = CrBcPlugin::getPluginInstance($plugin['name'], $plugin['type'], 'site');
            if($plugin_instance instanceof CrBcCartSitePlugin){
                $plugin_instance->init($_cart);
                if($plugin_instance->isSuitable() !== false){
                    $plugin_instance->viewportReceive();
                    $cart_plugin_instances[] = $plugin_instance;
                }
            }
        }

        $this->assignRef('cart_plugins', $cart_plugin_instances);
        // CART PLUGINS INIT - END
        
        if( JRequest::getVar('task') == 'update_billing_information' && JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_billing_result' ){
            
            $result = JRequest::getVar('update_billing_result', array());
            $userid = JRequest::getInt('userid', 0);
            $this->assignRef('update_billing_result', $result);
            $this->assignRef('userid', $userid);
            parent::display($tpl);
            
        } else if( JRequest::getVar('task') == 'update_payment_information' && JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_payment_result' ){
            
            $result = JRequest::getVar('update_payment_result', array());
            $this->assignRef('update_payment_result', $result);
            parent::display($tpl);
            
        } else if( JRequest::getVar('task') == 'update_shipping_method' && JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_shipping_method_result' ){
            
            $result = JRequest::getVar('shipping_method_result', array());
            $this->assignRef('shipping_method_result', $result);
            parent::display($tpl);
            
        } else if( JRequest::getVar('task') == 'reset_shipping_method' && JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_shipping_method_result' ){
            
            $result = JRequest::getVar('shipping_method_result', array());
            $this->assignRef('shipping_method_result', $result);
            parent::display($tpl);
            
        }  else if( JRequest::getVar('task') == 'update_order' && JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_order_result' ){
            
            $result = JRequest::getVar('update_order_result', array());
            $this->assignRef('update_order_result', $result);
            parent::display($tpl);
            
        } else if( JRequest::getVar('task') == 'validate_checkout_progress' && JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_validate_checkout_progress_result' ){
            
            $result = JRequest::getBool('validate_checkout_progress_result', false);
            $this->assignRef('validate_checkout_progress_result', $result);
            
            parent::display($tpl);
            
        }  else if( JRequest::getVar('task') == 'get_user_data' && JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_get_user_data' ){
            
            $result = JRequest::getVar('userdata', null);
            $this->assignRef('userdata', $result);
            
            parent::display($tpl);
            
        }   else if( JRequest::getVar('task') == 'perform_checkout' && JRequest::getVar('layout') == 'thankyou' ){
            
            $config = CrBcHelpers::getBcConfig();
            $info = $this->getInformationData($config->get('thank_you_message', ''));
            
            if( trim($info['content']) == '' ){
                $result = JText::_('COM_BREEZINGCOMMERCE_ORDER_THANK_YOU');
                $this->assignRef('thankyou_message', $result);
            } else {
                $content = trim($info['content']);
                $this->assignRef('thankyou_message', $content);
            }
            
            JFactory::getApplication()->getPathway()->addItem(JText::_('COM_BREEZINGCOMMERCE_CHECKOUT'));
            
            parent::display($tpl);
            
        }   else if( JRequest::getVar('task') == 'perform_checkout' && JRequest::getVar('layout') == 'error' ){
            
            $result = JRequest::getVar( 'error', '', 'REQUEST', 'STRING', JREQUEST_ALLOWRAW );
            $this->assignRef('error_message', $result);
            
            JFactory::getApplication()->getPathway()->addItem(JText::_('COM_BREEZINGCOMMERCE_CHECKOUT'));
           
            parent::display($tpl);
            
        }   else if( JRequest::getVar('task') == 'perform_checkout' && JRequest::getVar('layout') == 'payment' ){
            
            $result = JRequest::getVar( 'payment_output', '', 'REQUEST', 'STRING', JREQUEST_ALLOWRAW );
            $this->assignRef('payment_output', $result);
            
            JFactory::getApplication()->getPathway()->addItem(JText::_('COM_BREEZINGCOMMERCE_CHECKOUT'));
           
            parent::display($tpl);
            
        }   else {
        
            if($empty){
                JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_ERROR_MESSAGE'), 'info');
                JFactory::getApplication()->redirect('index.php');
                JFactory::getApplication()->close();
            }
            
            $ajax_cart_add = true;
            
            $libpath = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_breezingcommerce' . DS . 'classes' . DS . 'plugin' . DS;
            require_once($libpath . 'CrBcPlugin.php');
            
            // SHIPPING PLUGIN INIT - BEGIN
            $db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'shipping' Order By `ordering`");
            $shipping_plugins = $db->loadAssocList();
            // SHIPPING PLUGIN INIT - END
            
            // PAYMENT PLUGIN INIT - BEGIN
            $db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'payment' Order By `ordering`");
            $payment_plugins = $db->loadAssocList();

            foreach($payment_plugins As $plugin){
                $plugin_instance = CrBcPlugin::getPluginInstance($plugin['name'], $plugin['type'], 'site');
                if ($plugin_instance instanceof CrBcPaymentSitePlugin) {
                    $plugin_instance->init();
                }
            }
            // PAYMENT PLUGIN INIT - END
            
            $data = CrBcCart::getData($_session_cart['order_id'], $_cart_items);
        
            $_order_info = CrBcCart::getOrder(
                                        $_session_cart['order_id'], 
                                        $_cart, 
                                        $_session_cart, 
                                        $_cart_items, 
                                        $_session_cart['customer_id'],
                                        $data,
                                        $shipping_plugins,
                                        $payment_plugins
                                );
           
            $is_virtual_order = $_cart->isVirtualOrder($_cart_items);
            
            JHtml::_('behavior.keepalive');

            CrBcHelpers::addCssFile(Juri::root(true).'/components/com_breezingcommerce/css/font-awesome/css/font-awesome.min.css');
            JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/scrollto.js');
            JFactory::getDocument()->addScript(JURI::root(true).'/components/com_breezingcommerce/js/breezingcommerce.js');
            JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/cart.js');
            JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/checkout.js');
            JFactory::getDocument()->addScriptDeclaration(
            '
            var crbc_has_cart_plugins = '.(count($cart_plugin_instances) ? 'true' : 'false').';
			var crbc_cart_error_close = '.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_CLOSE')).';
            var crbc_needs_payment = '.CrBcHelpers::jsonEncode($_order_info->grand_total > 0).';
            var crbc_wait_msg = '.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_WAIT_MESSAGE')).';
            var crbc_cart_changed_msg = '.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_CART_CHANGED_MESSAGE')).';
            var crbc_cart_url = '.CrBcHelpers::jsonEncode(JRoute::_('index.php?option=com_breezingcommerce&controller=cart&Itemid='.CrBcHelpers::getDefaultMenuItemId('cart'))).';
            var crbc_checkout_url = '.CrBcHelpers::jsonEncode(JRoute::_('index.php?option=com_breezingcommerce&controller=checkout&Itemid='.CrBcHelpers::getDefaultMenuItemId('checkout'))).';
            var crbc_item_id = '.CrBcHelpers::jsonEncode(JRequest::getInt('Itemid', 0)).';
            var crbc_cart = new CrBcCart('.CrBcHelpers::jsonEncode($ajax_cart_add).', '.CrBcHelpers::jsonEncode(JUri::getInstance()->toString()).');
            var crbc_checkout = new CrBcCheckout('.CrBcHelpers::jsonEncode($ajax_cart_add).', '.CrBcHelpers::jsonEncode(JUri::getInstance()->toString()).', '.CrBcHelpers::jsonEncode($is_virtual_order).', '.CrBcHelpers::jsonEncode(JFactory::getUser()->get('id', 0) > 0 ? true : false).');
            ');
            
            $item = $this->get('Data');
            $this->assignRef('item', $item);

            $limited_address_data = $_cart->hasLimitedAddressData();
            $this->assignRef('limited_address_data', $limited_address_data);
            
            $items = CrBcCart::getData($_session_cart['order_id'], $_cart_items);
            
            // applying productdisplay order item handlers
            // productdetails PLUGINS INIT - BEGIN

            $db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'productdetails' Order By `ordering`");
            $productdetails_plugins = $db->loadAssocList();

            $cnt = count($data);
            for($i = 0; $i < $cnt; $i++){

                $productdetails_plugin_instances = array();
                foreach( $productdetails_plugins As $plugin ){

                    $plugin_instance = CrBcPlugin::getPluginInstance($plugin['name'], $plugin['type'], 'admin');
                    if($plugin_instance instanceof CrBcProductdetailsAdminPlugin){
                        $plugin_instance->init($_cart);
                        $info = $plugin_instance->handleOrderItemDisplay($items[$i]->order_item_info, 'cart');
                        $items[$i]->order_item_info = $info;
                    }
                }
                // PRODUCTDETAILS PLUGINS INIT - END
            }
            
            $this->assignRef('items', $items);
            
            $this->assignRef('cart_instance', $_cart);
            
            $this->assignRef('is_virtual_order', $is_virtual_order);
            $this->assignRef('ajax_cart', $ajax_cart);
            $this->assignRef('ajax_cart_add', $ajax_cart);

            $needs_payment = $_order_info->grand_total > 0;
            $this->assignRef('needs_payment', $needs_payment);
            
            $this->assignRef('order_info', $_order_info);
            
            if(isset($_session_cart['checkout']) && isset($_session_cart['checkout']['register'])){
                
                $this->assignRef('register', $_session_cart['checkout']['register']);
                
            }else{
                $register = 0;
                $this->assignRef('register', $register);
            }
            
            if(isset($_session_cart['checkout']) && isset($_session_cart['checkout']['shipping_plugin_id'])){
                
                $this->assignRef('shipping_plugin_id', $_session_cart['checkout']['shipping_plugin_id']);
                
            }else{
                $shipping_plugin_id = 0;
                $this->assignRef('shipping_plugin_id', $shipping_plugin_id);
            }
            
            if(isset($_session_cart['checkout']) && isset($_session_cart['checkout']['payment_plugin_id'])){
                
                $this->assignRef('payment_plugin_id', $_session_cart['checkout']['payment_plugin_id']);
                
            }else{
                $payment_plugin_id = 0;
                $this->assignRef('payment_plugin_id', $payment_plugin_id);
            }
            
            $config = CrBcHelpers::getBcConfig();
            $this->assignRef('config', $config);
            
            // INFO 1
            
            $info = $this->getInformationData($config->get('checkout_information1', ''));
            
            $this->assignRef('checkout_information_article1_link', $info['link']);
            $this->assignRef('checkout_information_article1_title', $info['title']);
            
            $checkout_confirm_information_article1 = $config->get('checkout_confirm_information1', 0);
            $this->assignRef('checkout_confirm_information_article1', $checkout_confirm_information_article1);
            
            // INFO 2
            
            $info = $this->getInformationData($config->get('checkout_information2', ''));
            
            $this->assignRef('checkout_information_article2_link', $info['link']);
            $this->assignRef('checkout_information_article2_title', $info['title']);
            
            $checkout_confirm_information_article2 = $config->get('checkout_confirm_information2', 0);
            $this->assignRef('checkout_confirm_information_article2', $checkout_confirm_information_article2);
            
            // INFO 3
            
            $info = $this->getInformationData($config->get('checkout_information3', ''));
            
            $this->assignRef('checkout_information_article3_link', $info['link']);
            $this->assignRef('checkout_information_article3_title', $info['title']);
            
            $checkout_confirm_information_article3 = $config->get('checkout_confirm_information3', 0);
            $this->assignRef('checkout_confirm_information_article3', $checkout_confirm_information_article3);
            
            // INFO 4
            
            $info = $this->getInformationData($config->get('checkout_information4', ''));
            
            $this->assignRef('checkout_information_article4_link', $info['link']);
            $this->assignRef('checkout_information_article4_title', $info['title']);
            
            $checkout_confirm_information_article4 = $config->get('checkout_confirm_information4', 0);
            $this->assignRef('checkout_confirm_information_article4', $checkout_confirm_information_article4);
            
            $menu = JFactory::getApplication()->getMenu();
            $active = $menu->getActive();
            
            if(is_object($active)){
                
                $pageclass_sfx = $active->params->get('pageclass_sfx');
                $this->assignRef('pageclass_sfx', $pageclass_sfx);
                
            } else {
            
                $empty = '';
                $this->assignRef('heading', $empty);
                $this->assignRef('pageclass_sfx', $empty);
            }
            
            CrBcHelpers::setPageTitle(JFactory::getDocument()->getTitle());
            
            JFactory::getApplication()->getPathway()->addItem(JText::_('COM_BREEZINGCOMMERCE_CHECKOUT'));
            
            parent::display($tpl);
        
        }
    }
    
    function getInformationData($checkout_information1){
        //// checkout information BEGIN
        
        $db = JFactory::getDbo();
        
        JHtml::_('behavior.modal');

        $lang = JFactory::getLanguage();

        $content = '';
        $checkout_information_article1_link = '';
        $checkout_information_article1_title = '';
        $checkout_information_article1_route = '';

        $checkout_information_article1 = $checkout_information1;

        $checkout_information_article1 = explode(',', $checkout_information_article1);
        $checkout_information_article1_size = count($checkout_information_article1);

        for($i = 0; $i < $checkout_information_article1_size; $i++){

            $info = explode(':',$checkout_information_article1[$i]);

            if(count($info) == 2 && $info[0] == $lang->getTag()){
                $db->setQuery("Select catid, language, title, introtext, `fulltext` From #__content Where id = " . $db->quote($info[1]));
                $result = $db->loadObject();
                if(is_object($result)){
                    $checkout_information_article1_route = ContentHelperRoute::getArticleRoute($info[1], $result->catid, $result->language);
                    $checkout_information_article1_title = $result->title;
                    $content = $result->introtext . '' . $result->fulltext;
                    break;
                }
            }
        }

        if($checkout_information_article1_route != ''){

            $checkout_information_article1_link = JRoute::_($checkout_information_article1_route.(strstr($checkout_information_article1_route,'?') !== false ? '&' : '?').'tmpl=component');
        }
        
        return array('link' => $checkout_information_article1_link, 'title' => $checkout_information_article1_title, 'content' => $content);

        //// checkout information END
    }
}
