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

class BreezingcommerceModelCart extends JModelLegacy
{
    protected $current_category_id = 0;
   
    function  __construct($config)
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();
        $option = 'com_breezingcommerce';
        
        $product_id = JRequest::getInt('product_id',  0);
        
        /** get current category id **/
        $menu = JFactory::getApplication()->getMenu();
        $active = $menu->getActive();
        $this->active = $active;
        if(isset($this->active) && isset($this->active->params)){
            
            $this->current_category_id = $this->active->params->get('category_id', 0);
        }
        
        $this->setId($product_id);
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
    
    function isPublished(){
        $this->_db->setQuery("Select published From #__breezingcommerce_products Where id = " . intval( $this->_id ));
        return $this->_db->loadResult() == 1 ? true : false;
    }
    
    function updateAmount($product_id = 0, $order_item_id = 0, $amount = 1){
        
        if(!$this->isPublished()){
            throw new Exception('Product not found!', 404);
        }
        
        $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );
        
        // request vars win over parameters
        if(JRequest::getInt('amount', null) !== null && JRequest::getInt('amount', null)){
            $amount = JRequest::getInt('amount', 1) < 1 ? 1 : JRequest::getInt('amount', 1);
        }
        
        if(JRequest::getInt('product_id', null) !== null && JRequest::getInt('product_id', null)){
            $product_id = JRequest::getInt('product_id', 0) < 0 ? 0 : JRequest::getInt('product_id', 0);
        }
        
        if(JRequest::getInt('order_item_id', null) !== null && JRequest::getInt('order_item_id', null)){
            $order_item_id = JRequest::getInt('order_item_id', 0) < 0 ? 0 : JRequest::getInt('order_item_id', 0);
        }
        
        $cart->updateAmount($product_id, $order_item_id, $amount);
    }
    
    function getItemPrice($product_id = 0, $order_item_id = 0, $order_id = 0, $amount = 1){
        
        if(!$this->isPublished()){
            throw new Exception('Product not found!', 404);
        }
        
        $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );
        
        // request vars win over parameters
        if(JRequest::getInt('amount', null) !== null && JRequest::getInt('amount', null)){
            $amount = JRequest::getInt('amount', 1) < 1 ? 1 : JRequest::getInt('amount', 1);
        }
        
        if(JRequest::getInt('order_id', null) !== null && JRequest::getInt('order_id', null)){
            $order_id = JRequest::getInt('order_id', 0) < 0 ? 0 : JRequest::getInt('order_id', 0);
        }
        
        if(JRequest::getInt('product_id', null) !== null && JRequest::getInt('product_id', null)){
            $product_id = JRequest::getInt('product_id', 0) < 0 ? 0 : JRequest::getInt('product_id', 0);
        }
        
        if(JRequest::getInt('order_item_id', null) !== null && JRequest::getInt('order_item_id', null)){
            $order_item_id = JRequest::getInt('order_item_id', 0) < 0 ? 0 : JRequest::getInt('order_item_id', 0);
        }
         
        $order_info = CrBcCart::getOrderInfoFromCart($order_id);
        
        if( isset($order_info['items']) && is_array($order_info['items']) ){
            foreach($order_info['items'] As $item){
                if( $order_item_id == $item->order_item_id ){
                    $item_price = new stdClass();
                    $item_price->id = $item->id;
                    $item_price->price_single_net = $item->price_single_net;
                    $item_price->price_single_gross = $item->price_single_gross;
                    $item_price->price_single_tax_list = $item->price_single_tax_list;
                    $item_price->price_tax_list = $item->price_tax_list;
                    $item_price->single_off = $item->single_off;
                   
                    $item_price->price_net = $item->price_single_net * $amount;
                    $item_price->price_gross = $item->price_single_gross * $amount;
                    $item_price->off = $item->single_off * $amount;
                    
                    $item_price->price_single_net_formatted = $cart->formatPrice($item_price->price_single_net);
                    $item_price->price_single_gross_formatted = $cart->formatPrice($item_price->price_single_gross);
                    $item_price->single_off_formatted = $cart->formatPrice($item_price->single_off);
                    $item_price->price_net_formatted = $cart->formatPrice($item_price->price_net);
                    $item_price->price_gross_formatted = $cart->formatPrice($item_price->price_gross);
                    $item_price->off_formatted = $cart->formatPrice($item_price->off);
                    
                    return $item_price;
                }
            }
        }
           
        return '{"null":null}';
    }
    
    function clear(){
        
        $cart = new CrBcCart(JFactory::getSession()->get('crbc_cart', array()));
        $cart->clear();
    }
    
    function remove($order_item_ids){
        
        JArrayHelper::toInteger($order_item_ids);
        
        $cart = new CrBcCart(JFactory::getSession()->get('crbc_cart', array()));
        
        foreach($order_item_ids As $order_item_id){
            $cart->remove($order_item_id);
        }
    }
    
    function add(){
        
        if(!$this->isPublished()){
            throw new Exception('Product not found!', 404);
        }
		
		$this->_db->setQuery("Select category_id From #__breezingcommerce_product_categories Where product_id = " . $this->_db->quote($this->_id) . " Order By ordering");
		$category_ids = $this->_db->loadColumn();
		
		foreach($category_ids As $category_id){
		
			if($category_id){		    
				$this->_db->setQuery("Select * From #__breezingcommerce_categories Where id = ". $this->_db->quote($category_id) ." And published = 1 Limit 1");
				$category = $this->_db->loadObject();
				if($category && $category->catalog)
					return JText::_('COM_BREEZINGCOMMERCE_CART_ADD_ERROR');				
				while($category && $category->parent_id){
					$this->_db->setQuery("Select * From #__breezingcommerce_categories Where id = ". $this->_db->quote($category->parent_id) ." And published = 1 Limit 1");
					$category = $this->_db->loadObject();
					if($category && $category->catalog_children)
						return JText::_('COM_BREEZINGCOMMERCE_CART_ADD_ERROR');
				}
			}
			$this->_db->setQuery("Select catalog_strict From #__breezingcommerce_categories Where id = " . $category_id);
			$catalog_strict = $this->_db->loadResult();
			if($catalog_strict){
				return JText::_('COM_BREEZINGCOMMERCE_CART_ADD_ERROR');
			}			
		}
		
        
        $customer_id = 0;
        
        // cart preparation and session setting
        // customer id will be set upon first cart add and being kept in session
        if( JFactory::getSession()->get('crbc_cart', null) === null ){
            
            $group_data = null;
            
            // ok no group, then let's see if there is one determinable by the user's location
            if( !$group_data ){
                
                $group_data = CrBcHelpers::getBestCustomerGroupByLocation(true); // "true" to return the result as object, not assoc
            }
            
            // still nothing, try the default group
            if( !$group_data ){
                
                JFactory::getDbo()->setQuery("Select id, default_country_id, default_region_id From #__breezingcommerce_customergroups Where `default` = 1");
                $group_data = JFactory::getDbo()->loadObject();
            }
            
            $default_customer_group_id = 0;
            $default_country_id = 0;
            $default_region_id = 0;
             
            if( $group_data ){
                $default_customer_group_id = $group_data->id;
                $default_country_id = $group_data->default_country_id;
                $default_region_id = $group_data->default_region_id;
            }
            
            $customer_data = array(
                'id' => 0, 
                'lastname' => '', 
                'customergroup_id' => $default_customer_group_id,
                'country_id' => $default_country_id,
                'region_id' => $default_region_id,
                'shipping_country_id' => $default_country_id,
                'shipping_region_id' => $default_region_id,
                'is_tmp' => 1,
                'company' => '',
                'firstname' => ''
            );
            
            $customer_data = CrBcCart::updateCustomer($customer_data);
            
            if( $customer_data !== null ){
                $customer_id = $customer_data['id'];
                JFactory::getSession()->set('crbc_cart', CrBcCart::buildCart($customer_data['id'], $customer_data['company'] . $customer_data['firstname'] . $customer_data['lastname']));
            }
            
        } 
        // cart has been set already, now retrieve the customer id
        else {
            
            $cart_array = JFactory::getSession()->get('crbc_cart', null);
            
            if(isset($cart_array['customer_id'])){   
                $customer_id = intval($cart_array['customer_id']);
            }
        }
        
        if($customer_id){
        
            $data = JRequest::get( 'post' );

            $cart = new CrBcCart(JFactory::getSession()->get('crbc_cart', array()));

            try{
                
                $item = $cart->add(
                        $this->_id,
                        !isset($data['amount']) || intval( $data['amount'] ) < 1 ? 1 : intval($data['amount']),
                        isset($data['crbcProperties']) ? $data['crbcProperties'] : null,
                        isset($data['crbcAttributes']) ? $data['crbcAttributes'] : null,
                        isset($data['crbcAttributesAmounts']) ? $data['crbcAttributesAmounts'] : null,
                        $customer_id
                );
            }
            catch(CrBcOutOfStockException $e){
                return JText::_('COM_BREEZINGCOMMERCE_OUT_OF_STOCK_ERROR');
            }
            catch(CrBcAttributesOutOfStockException $e){
                return JText::_('COM_BREEZINGCOMMERCE_ATTRIBUTE_OUT_OF_STOCK_ERROR');
            }
            catch(CrBcCartException $e){
                return JText::_('COM_BREEZINGCOMMERCE_CART_ADD_ERROR');
            }

            JFactory::getSession()->set('crbc_cart', $cart->getArray());
            
            // productdetails PLUGINS INIT - BEGIN
            $this->_db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'productdetails' Order By `ordering`");
            $productdetails_plugins = $this->_db->loadAssocList();

            $productdetails_plugin_instances = array();
            foreach( $productdetails_plugins As $plugin ){

                $plugin_instance = CrBcPlugin::getPluginInstance($plugin['name'], $plugin['type'], 'site');
                if($plugin_instance instanceof CrBcProductdetailsSitePlugin){
                    $plugin_instance->init($cart);
                    $plugin_instance->afterCartAdd($item);
                }
            }
            // PRODUCTDETAILS PLUGINS INIT - END
            
        } else {
            
            return JText::_('COM_BREEZINGCOMMERCE_NO_CUSTOMER');
        }
		
		return '';
    }
}
