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

class BreezingcommerceModelProduct extends JModelLegacy
{
    protected $active = null;
    static $BACKORDERS_DISALLOWED_VISIBLE = 'DISALLOWED_VISIBLE';
    static $BACKORDERS_DISALLOWED_HIDDEN = 'DISALLOWED_HIDDEN';
    
    function  __construct($config)
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();
        $option = 'com_breezingcommerce';
        
        $product_id = CrBcHelpers::determineCurrentProductId();
        
        $this->setId($product_id);

        $menu = JFactory::getApplication()->getMenu();
        $active = $menu->getActive();
        $this->active = $active;
        
        $config = CrBcHelpers::getBcConfig();
        
        if( isset($this->active->params) && $this->active->params->get('override_default', 0) == 0 ){
            $this->active->params->merge($config);
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
        
        /*
        if(JRequest::getBool('recreate_cart', false)){
            require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
            $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );
            $cart->restoreCart(JRequest::getInt('recreate_order_id', 0));
            JFactory::getSession()->set('crbc_cart', $cart->getArray());
        }*/
    }
    
    function getEnableAjaxCartAdd(){
        
        if(is_object($this->active)){
            return $this->active->params->get('enable_ajax_cart_add', 1) == 1 ? true : false;
        }
        
        return true;
    }
    
    function getPageTitle(){
        if(is_object($this->active)){
            return $this->active->params->get('page_title', '');
        }else{
            return '';
        }
    }
    
    function getEnableDynamicPrice(){
        
        if(is_object($this->active)){
            return $this->active->params->get('enable_dynamic_price', 1) == 1 ? true : false;
        }
        
        return true;
    }

    function getAmountImages()
    {
        $this->_db->setQuery( 'Select count(id) As amount From #__breezingcommerce_images Where product_id = ' . $this->_db->Quote( $this->getId() ) );
        $res = $this->_db->loadObjectList();
        if(count($res) != 0){
            return $res[0]->amount;
        }
        return 0;
    }

    function getAmountFiles()
    {
        $this->_db->setQuery( 'Select count(id) As amount From #__breezingcommerce_files Where product_id = ' . $this->_db->Quote( $this->getId() ) );
        $res = $this->_db->loadObjectList();
        if(count($res) != 0){
            return $res[0]->amount;
        }
        return 0;
    }

    function getCategories()
    {
        $array = array();
        $this->_db->setQuery("Select category_id From #__breezingcommerce_product_categories Where product_id = " . $this->getId() );
        $cats = $this->_db->loadObjectList();
        foreach($cats As $cat)
        {
            $array[] = $cat->category_id;
        }
        return $array;
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
    
    function isPublished(){
        $this->_db->setQuery("Select published From #__breezingcommerce_products Where id = " . intval( $this->_id ));
        return $this->_db->loadResult() == 1 ? true : false;
    }

    function getProductPrice(){
        
        if(!$this->isPublished()){
            throw new Exception('Product not found!', 404);
        }
        
        $price = 0;
        
        $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );
        
        $global_amount = JRequest::getInt('amount', 0);
        
        if(!$global_amount){
            $global_amount = 1;
        }
        
        $db = JFactory::getDBO();
        
        $product_attribute_item_ids = JRequest::getVar('product_attribute_item_ids', array(), 'POST', 'ARRAY');
        
        $attribute_amounts = array();
        
        foreach($product_attribute_item_ids As $attribute_key => $attribute){
            $amount = intval($attribute['amount']);
            foreach($attribute['product_attribute_item_ids'] As $item_id){
                $attribute_amounts[intval($item_id)] = $amount;
            }
        }
        
        $properties = JRequest::getVar('product_property_ids', array(), 'POST', 'ARRAY');
        JArrayHelper::toInteger($properties); 
        
        $db->setQuery("Select id, price, sale_price, producttaxclass_id, use_combinations From #__breezingcommerce_products Where id = " . $this->_id . " And published = 1");
        $product_price = $db->loadAssoc();
        
        $applied = CrBcCart::applyProductPricePlugins($product_price['id']);

        if($applied !== null){

            $product_price['price'] = $applied->price;
            $product_price['sale_price'] = $applied->sale_price;

        }
        
        $customer_id = 0;
        
        if($cart instanceof CrBcCart && JFactory::getUser()->get('id', 0) != 0){
            
            JFactory::getDbo()->setQuery("Select id From #__breezingcommerce_customers Where userid = " . intval(JFactory::getUser()->get('id', 0)) . " Limit 1");
            $customer_id = JFactory::getDbo()->loadResult();

        } else {
            
            $customer_id = isset($cart->cart['customer_id']) ? $cart->cart['customer_id'] : 0;

        }
        
        $db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'productprice' Order By `ordering`");
        $plugins = $db->loadObjectList();
        
        $new_price = $product_price['price'];

        foreach($plugins As $plugin){

            $class = CrBcPlugin::getPluginClass($plugin->name, $plugin->type);
            $class_instance = new $class();

            $combination_id = 0;
            if($product_price['use_combinations']){
                foreach($properties As $property){
                    $combination_id = $property;
                    break;
                }
            }
            
            $new_price = $class::getPrice($product_price['price'], $combination_id, $global_amount, $new_price);
            if($new_price !== null){
                $product_price['price'] = $new_price;
            }
        }
        
        $price_single = CrBcCart::getItemPrice( $product_price['price'], $cart->currency_conversion_rate, $product_price['producttaxclass_id'], $properties, $attribute_amounts, 1, $customer_id, $product_price['use_combinations'] == 1 ? true :  false);
        $price = CrBcCart::getItemPrice( $product_price['price'], $cart->currency_conversion_rate, $product_price['producttaxclass_id'], $properties, $attribute_amounts, $global_amount, $customer_id, $product_price['use_combinations'] == 1 ? true :  false);
        $sale_price_single = CrBcCart::getItemPrice( $product_price['sale_price'], $cart->currency_conversion_rate, $product_price['producttaxclass_id'], $properties, $attribute_amounts, 1, $customer_id, $product_price['use_combinations'] == 1 ? true :  false);
        $sale_price = CrBcCart::getItemPrice( $product_price['sale_price'], $cart->currency_conversion_rate, $product_price['producttaxclass_id'], $properties, $attribute_amounts, $global_amount, $customer_id, $product_price['use_combinations'] == 1 ? true :  false);

        $price['gross'] = $price_single['gross'] * $global_amount;
        $sale_price['gross'] = $sale_price_single['gross'] * $global_amount;
        
        $price['sale_price'] = $sale_price;
        
        return $price;
    }
    
    /**
    * Gets the products
    * @return array List of products
    */
    function getData()
    {
        // Load the data
        if (empty( $this->_data )) {
            
            // make sure not to show a product that hasn't been translated at least partially
            $check_for_translation_tbl = '';
            $check_for_translation_lookup = '';
            
            $default = JComponentHelper::getParams('com_languages')->get('site');
            if( count(JFactory::getLanguage()->getKnownLanguages()) > 1 && $default != JFactory::getLanguage()->getTag() ){

                $check_for_translation_tbl = ' Left Join #__breezingcommerce_translations As trns ';
                $check_for_translation_tbl .= ' On p.id = trns.item_id And trns.language_code = ' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ' And `type` = "product" ';
                $check_for_translation_lookup = ' And trns.item_id Is Not Null And ( trns.title <> "" Or trns.alias <> "" Or trns.body <> "" ) ';
            }
            
            $query = ' Select p.* From #__breezingcommerce_products As p ' . $check_for_translation_tbl .
                    '  Where p.published = 1 And p.id = '.$this->_id . $check_for_translation_lookup;
            
            $this->_db->setQuery( $query );
            $this->_data = $this->_db->loadObject();
            $this->_db->setQuery("Select * From #__breezingcommerce_producttaxclasses Order By `name`");
        }
        
        if (!$this->_data) {
            JError::raiseError(404, JText::_('COM_BREEZINGCOMMERCE_PRODUCT_NOT_FOUND'));
        }

        $form = new JRegistry('', JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'forms'.DS.'product.xml');
        $form->loadString($this->_data->attribs);
        $this->_data->form = $form;

        $this->_data->taxclasses = $this->_db->loadObjectList();
        foreach($this->_data->taxclasses As $taxclass) {
            $taxclass->selected = false;
            if($taxclass->id == $this->_data->producttaxclass_id) {
                $taxclass->selected = true;
                break;
            }
        }

        $this->_data->weight_type_big = 'kg';
        $this->_data->weight_type_small = 'g';
        if( CrBcHelpers::getBcConfig()->get('weight_type',0) == 1){
            $this->_data->weight_type_big = 'lb';
            $this->_data->weight_type_small = 'oz';
        }

        $this->_data->dimension_type_big = 'm';
        $this->_data->dimension_type_small = 'cm';
        if( CrBcHelpers::getBcConfig()->get('dimension_type',0) == 1){
            $this->_data->dimension_type_big = 'ft';
            $this->_data->dimension_type_small = 'in';
        }

        $this->_db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'productprice' Order By `ordering`");
        $this->_data->price_plugins = $this->_db->loadObjectList();

        $this->_db->setQuery("Select * From #__breezingcommerce_plugins Where published = 1 And type = 'productdetails' Order By `ordering`");
        $this->_data->display_plugins = $this->_db->loadObjectList();

        // translations
        $this->_data->description_translation = '';
        $this->_data->title_translation = '';
        $this->_data->alias_translation = '';
        
        if($this->_id){
            $translated_object = CrBcHelpers::loadTranslation($this->_id, 'product');
            if($translated_object !== null){
                $this->_data->description = $translated_object->body;
                $this->_data->title = $translated_object->title;
                $this->_data->alias = $translated_object->alias;
            }
        }
        
        // translations end
        
        $introtext = '';
        $fulltext  = '';
        $desc = str_replace('<br>', '<br />', $this->_data->description);

        // Search for the {readmore} tag and split the text up accordingly.
        $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
        $tagPos = preg_match($pattern, $desc);

        if ($tagPos == 0) {
            $introtext = $desc;
        } else {
            list($introtext, $fulltext) = preg_split($pattern, $desc, 2);
        }

        $this->_data->introtext = $introtext;
        $this->_data->fulltext = $fulltext;
        
        return $this->_data;

    }

    function hasAttributes()
    {
        $this->_db->setQuery("Select count(id) From #__breezingcommerce_product_attributes Where product_id = " . $this->_id);
        return $this->_db->loadResult() != 0 ? true : false;
    }
}
