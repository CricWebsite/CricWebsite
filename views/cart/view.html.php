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

class BreezingcommerceViewCart extends JViewLegacy
{
    function display($tpl = null)
    {
        // clear (unverify anything that's no longer valid
        // (downloads and verified items)
        CrBcCart::unverifyItemsGlobally();

        // re-check to recover previously lost groups if items intersect
        CrBcCart::verifyItemsGlobally();
        
        // TODO: enable / disable ajax system in config
        $ajax_cart_add = true;
        
        JHtml::_('behavior.keepalive');
        
        CrBcHelpers::addCssFile(Juri::root(true).'/components/com_breezingcommerce/css/font-awesome/css/font-awesome.min.css');
        JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/cart.js');
        JFactory::getDocument()->addScriptDeclaration(
        '
        var crbc_item_id = '.CrBcHelpers::jsonEncode(JRequest::getInt('Itemid', 0)).';
		var crbc_cart_error_close = '.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_CLOSE')).';
        var crbc_cart_url = '.CrBcHelpers::jsonEncode(JRoute::_('index.php?option=com_breezingcommerce&controller=cart')).';
        var crbc_checkout_url = '.CrBcHelpers::jsonEncode(JRoute::_('index.php?option=com_breezingcommerce&controller=checkout')).';
        var crbc_cart = new CrBcCart('.CrBcHelpers::jsonEncode($ajax_cart_add).', '.CrBcHelpers::jsonEncode(JUri::getInstance()->toString()).','.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_ADD_TO_CART_MSG')).');
        ');
        
        $_session_cart = JFactory::getSession()->get('crbc_cart', array());
        $_cart = new CrBcCart( $_session_cart !== null ? $_session_cart : array() );
        
        //print_r($_session_cart);
        
        if(!isset( $_session_cart['order_id'] ) || intval($_session_cart['order_id']) < 1 ){
            $empty_cart = JText::_('COM_BREEZINGCOMMERCE_CART_EMPTY');
            $this->assignRef('empty_cart', $empty_cart);
            parent::display($tpl);
            return;
        }
        
        //print_r($_cart);
        //$_cart->clear();
        // using this if we'd like to restore it from database
        //$_cart = $_cart->restoreCart($this->_id); // we want to restore the cart from database
        
        $db = JFactory::getDbo();
        
        // SHIPPING PLUGINS INIT - BEGIN
        $db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'shipping' Order By `ordering`");
        $shipping_plugins = $db->loadAssocList();
        
        if(isset($_session_cart['checkout']) && isset($_session_cart['checkout']['shipping_plugin_id'])){

            $this->assignRef('shipping_plugin_id', $_session_cart['checkout']['shipping_plugin_id']);

        }else{
            $shipping_plugin_id = 0;
            $this->assignRef('shipping_plugin_id', $shipping_plugin_id);
        }
        // SHIPPING PLUGINS INIT - END
        
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
        
        $customer = null;
        if(isset($_cart->cart['customer_id']) && $_cart instanceof CrBcCart){
            JFactory::getDbo()->setQuery("Select * From #__breezingcommerce_customers Where id = " . intval($_cart->cart['customer_id']) . " Limit 1");
            $customer = JFactory::getDbo()->loadObject();
        }
        
        $_cart_items = $_cart->getItems(true);
        
        // 2nd check, mostly for when something has been unpublished while the cart is open 
        if(count($_cart_items) == 0){
            $empty_cart = JText::_('COM_BREEZINGCOMMERCE_CART_EMPTY');
            $this->assignRef('empty_cart', $empty_cart);
            parent::display($tpl);
            return;
        }
        
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
        $this->assignRef('cart_items', $_cart_items);
        $this->assignRef('order_info', $_order_info);
        
        
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
                    $info = $plugin_instance->handleOrderItemDisplay($data[$i]->order_item_info, 'cart');
                    $data[$i]->order_item_info = $info;
                }
            }
            // PRODUCTDETAILS PLUGINS INIT - END
        }
        
        $this->assignRef('items', $data);
        
        $return_url = '';
        $this->assignRef('return_url', $return_url);
        if(trim(JFactory::getApplication()->input->get('return_url', '')) != ''){
            $return_url = trim(JFactory::getApplication()->input->get('return_url', ''));
            $return_url = CrBcHelpers::bSixtyFourDecode($return_url);
            if(JUri::isInternal($return_url)){
                $this->assignRef('return_url', $return_url);
            }
        }
        
        $this->assignRef('ajax_cart_add', $ajax_cart_add);
            
        $menu = JFactory::getApplication()->getMenu();
        $active = $menu->getActive();
        
        if( is_object($active) ){
            $pageclass_sfx = $active->params->get('pageclass_sfx');
            $this->assignRef('pageclass_sfx', $pageclass_sfx);
        }else{
            $empty = '';
            $this->assignRef('pageclass_sfx', $empty);
        }
        
        CrBcHelpers::setPageTitle(JFactory::getDocument()->getTitle());
        
        JFactory::getApplication()->getPathway()->addItem(JText::_('COM_BREEZINGCOMMERCE_CART_TITLE'));
        
        parent::display($tpl);
    }
}
