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

class BreezingcommerceViewProduct extends JViewLegacy
{
    function display($tpl = null)
    {
        require_once(JPATH_SITE.'/administrator/components/com_breezingcommerce/classes/CrBcImage.php');
        require_once(JPATH_SITE.'/administrator/components/com_breezingcommerce/classes/CrBcForm.php');
        require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcAttributes.php' );
        require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcProperties.php' );
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        
        // clear (unverify anything that's no longer valid
        // (downloads and verified items)
        CrBcCart::unverifyItemsGlobally();

        // re-check to recover previously lost groups if items intersect
        CrBcCart::verifyItemsGlobally();
        
        $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );
        
        if( JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_get_product_price' ){
            
            JRequest::setVar('product_id', JRequest::getInt('product_id', 0));
            JRequest::setVar('amount', JRequest::getInt('amount', 0));
            
            $price = $this->get('ProductPrice');
            $this->assignRef('price', $price);
            
            $this->assignRef('cart_instance', $cart);
            
            $price_group = $cart->getOrderedPriceGroup($price, $cart->currency_symbol, $cart->currency_code);
            $this->assignRef( 'price_group', $price_group );
            
            parent::display($tpl);
            
        } else if( JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_apply_display_plugins' ){
            
            // updates the product display plugins based on the variations (combinations) selected
            
            $display_plugins = CrBcHelpers::applyProductDisplayPlugins(JRequest::getInt('product_id'), JRequest::getInt('combination_id'), JRequest::getBool('is_complex', false));
            $this->assignRef( 'display_plugins', $display_plugins );
            
            parent::display($tpl);
            
        } else {
            
            $enable_ajax_cart_add = $this->get('EnableAjaxCartAdd');
            $this->assignRef('enable_ajax_cart_add', $enable_ajax_cart_add);

            $enable_dynamic_price = $this->get('EnableDynamicPrice');
            $this->assignRef('enable_dynamic_price', $enable_dynamic_price);

            $ajax_cart_add = $enable_ajax_cart_add;

            $product = $this->get('Data');
            
            $applied = CrBcCart::applyProductPricePlugins($product->id);
            
            if($applied !== null){
            
                $product->price = $applied->price;
                $product->sale_price = $product->sale_price;
            
            }

            $product_form = new JRegistry(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'product.xml');
            $form = $product_form->loadString($product->attribs);
            $product->minimum_amount = intval($form->get('minimum_amount', 1));
            $product->maximum_amount = intval($form->get('maximum_amount', 99999));
            
            $cart_items = $cart->getItems(true);
            
            $immediate_allowed = false;
            if(count($cart_items) <= 1){
                $immediate_allowed = true;
            }
            
            if($immediate_allowed){
                $product->immediate_checkout = intval($form->get('immediate_checkout', 0));
                $product->immediate_checkout_singleton = intval($form->get('immediate_checkout_singleton', 0));
            } else {
                $product->immediate_checkout = 0;
                $product->immediate_checkout_singleton = 0;
            }
            
            $product->hide_price = intval($form->get('hide_price', 0));
            
            $product->has_attributes = CrBcCart::hasAttributes($product->id);
            $product->has_properties = CrBcCart::hasProperties($product->id);
            
            CrBcHelpers::addCssFile(Juri::root(true).'/components/com_breezingcommerce/css/font-awesome/css/font-awesome.min.css');
            JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/scrollto.js');
            JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/product.js');
            JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/cart.js');
            if($enable_dynamic_price){
                CrBcHelpers::addCss('
                    #crbc-total { display: block; }
                ');
            }
            JFactory::getDocument()->addScriptDeclaration(
            '
            var crbc_min_amount = '.CrBcHelpers::jsonEncode($product->minimum_amount).';
			var crbc_cart_error_close = '.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_CLOSE')).';
            var crbc_max_amount = '.CrBcHelpers::jsonEncode($product->maximum_amount).';
            var crbc_item_id = '.CrBcHelpers::jsonEncode(JRequest::getInt('Itemid', 0)).';
            var crbc_cart_url = '.CrBcHelpers::jsonEncode(JRoute::_('index.php?option=com_breezingcommerce&controller=cart&Itemid='.CrBcHelpers::getDefaultMenuItemId('cart'))).';
            var crbc_checkout_url = '.CrBcHelpers::jsonEncode(JRoute::_('index.php?option=com_breezingcommerce&controller=checkout&Itemid='.CrBcHelpers::getDefaultMenuItemId('checkout'))).';
            var crbc_cart = new CrBcCart('.CrBcHelpers::jsonEncode($ajax_cart_add).', '.CrBcHelpers::jsonEncode(JUri::getInstance()->toString()).','.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_ADD_TO_CART_MSG')).');
            var crbc_product = new CrBcProduct('.CrBcHelpers::jsonEncode($product->id).', '.CrBcHelpers::jsonEncode($enable_dynamic_price).', '.CrBcHelpers::jsonEncode(JUri::getInstance()->toString()).', crbc_min_amount, crbc_max_amount);
                
            function crbcUpdatePrice(){
                if( typeof crbc_product != "undefined" ){
                    crbc_product.update_price();
                }
            }

            jQuery(document).ready(function(){
                jQuery(".crbc-add-to-cart-button button").on("click", crbc_cart.add_to_cart);
                if('.CrBcHelpers::jsonEncode($enable_dynamic_price).'){
                    crbcUpdatePrice();
                };
            });
            ');
            
            JFactory::getDocument()->addScriptDeclaration(CrBcProperties::getPropertyJs());
            JFactory::getDocument()->addScriptDeclaration(CrBcAttributes::getAttributeJs());
            JFactory::getDocument()->addScriptDeclaration(CrBcAttributes::getRequiredJs());

            $product_images = CrBcImage::renderProductImagesPlugins($product->id);
            
            $default_product_image_path = '';
            $default_product_image = '';
            
            JFactory::getDbo()->setQuery("Select * From #__breezingcommerce_images Where `product_id` = " . JFactory::getDbo()->quote($product->id) . " And published = 1 Order By ordering Limit 1");
            $image = JFactory::getDbo()->loadObject();
            if($image){
                $uri     = Juri::getInstance();
                $current = $uri->toString( array('scheme', 'host', 'port'));
                $default_product_image_path = $current . '/images/breezingcommerce/products/medium/'.$image->physical_name;
                $default_product_image = '<img src="'.JUri::root(true).'/images/breezingcommerce/products/medium/'.$image->physical_name.'" title="'.$image->alt_name.'">';
            }
            
            $this->assignRef( 'default_product_image', $default_product_image );
            $this->assignRef( 'default_product_image_path', $default_product_image_path );
            $this->assignRef( 'product_images', $product_images );

            // selectors
            $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );

            $property_selectors = CrBcProperties::getPropertySelectors($product->id, 1, array(), $product->use_combinations);
            $this->assignRef( 'property_selectors', $property_selectors );

            $attribute_selectors = CrBcAttributes::getAttributesForm($product->id, 1, array());
            $this->assignRef( 'attribute_selectors', $attribute_selectors );

            $display_plugins = CrBcHelpers::applyProductDisplayPlugins($product->id);
            $this->assignRef( 'display_plugins', $display_plugins );
            
            // just in case we need the customer id for anything regarding prices

            $customer_id = 0;

            if($cart instanceof CrBcCart && JFactory::getUser()->get('id', 0) != 0){
                JFactory::getDbo()->setQuery("Select id From #__breezingcommerce_customers Where userid = " . intval(JFactory::getUser()->get('id', 0)) . " Limit 1");
                $customer_id = JFactory::getDbo()->loadResult();
            
            } else {
                $customer_id = isset($cart->cart['customer_id']) ? $cart->cart['customer_id'] : 0;
            
            }

            // determine the pre-selected combination to display the right amount, right away upon page load
            $properties = array();
            if($product->use_combinations){
                
                JFactory::getDbo()->setQuery("Select id From #__breezingcommerce_product_property_combinations Where product_id = " . intval($product->id) . " And published = 1 And selected = 1");
                $properties = JFactory::getDbo()->loadColumn();
                
            } else {
                
                JFactory::getDbo()->setQuery("Select id From #__breezingcommerce_product_property_values Where product_id = " . intval($product->id) . " And published = 1 And selected = 1");
                $properties = JFactory::getDbo()->loadColumn();
            }
            
            $price_array = CrBcCart::getItemPrice($product->price, $cart->currency_conversion_rate, $product->producttaxclass_id, $properties, array(), 1, $customer_id, $product->use_combinations);
            $price_group = $cart->getOrderedPriceGroup($price_array);
            $this->assignRef( 'price_group', $price_group );

            $sale_price_array = CrBcCart::getItemPrice($product->sale_price, $cart->currency_conversion_rate, $product->producttaxclass_id, array(), array(), 1, $customer_id);
            
            if($sale_price_array['gross'] == $price_array['gross']){
                $sale_price_array['gross'] = 0;
            }
            
            if($sale_price_array['gross'] > 0){
                $sale_price_group = $cart->getOrderedPriceGroup($sale_price_array, null, null, true, true);
                $this->assignRef( 'sale_price_group', $sale_price_group );
            }else{
                $null = null;
                $this->assignRef( 'sale_price_group', $null );
            }
            
            // do we want to display the currency info (only if force currency is enabled in the current customer group)
            if( $cart->currency_code_info !== null ){
                
                $price_info_array = CrBcCart::getItemPrice($product->price, $cart->currency_conversion_rate_info, $product->producttaxclass_id, array(), array(), 1, $customer_id);
                $price_info_group = $cart->getOrderedPriceGroup($price_info_array, $cart->currency_symbol_info, $cart->currency_code_info);
                $this->assignRef( 'price_info_group', $price_info_group );
                
                $sale_price_info_array = CrBcCart::getItemPrice($product->sale_price, $cart->currency_conversion_rate_info, $product->producttaxclass_id, array(), array(), 1, $customer_id);
                
                if($sale_price_info_array['gross'] == $price_info_array['gross']){
                    $sale_price_info_array['gross'] = 0;
                }
                
                if($sale_price_info_array['gross'] > 0){
                    $sale_price_info_group = $cart->getOrderedPriceGroup($sale_price_info_array, $cart->currency_symbol_info, $cart->currency_code_info);
                    $this->assignRef( 'sale_price_info_group', $sale_price_info_group );
                }else{
                    $null = null;
                    $this->assignRef( 'sale_price_info_group', $null );
                }
                
            } else {
                $null = null;
                $this->assignRef( 'price_info_group', $null );
                $this->assignRef( 'sale_price_info_group', $null );
            }
            
            $this->assignRef( 'cart_instance', $cart );

            $categories = $this->getModel()->getCategories();
            $this->assignRef( 'categories', $categories );
            $hasAttributes = $this->getModel()->hasAttributes();
            $this->assignRef( 'hasAttributes', $hasAttributes );
            $limitstart = JRequest::getInt('limitstart', 0);
            $this->assignRef('limitstart', $limitstart);
            $amount_images = $this->getModel()->getAmountImages();
            $this->assignRef( 'amount_images', $amount_images );
            $amount_files = $this->getModel()->getAmountFiles();
            $this->assignRef( 'amount_files', $amount_files );
            $this->assignRef('product', $product);
            $state = $this->get( 'state' );

            $return_url = CrBcHelpers::bSixtyFourEncode(JUri::getInstance()->toString());
            $this->assignRef('return_url', $return_url);
            
            $return_url_list = '';
            $this->assignRef('return_url_list', $return_url_list);
            
            if(trim(JFactory::getApplication()->input->get('return_url_list', '')) != ''){
                $return_url_list = trim(JFactory::getApplication()->input->get('return_url_list', ''));
                $return_url_list = CrBcHelpers::bSixtyFourDecode($return_url_list);
                if(JUri::isInternal($return_url_list)){
                    $this->assignRef('return_url_list', $return_url_list);
                }
            }
        
            $this->assignRef('ajax_cart_add', $ajax_cart_add);

            $menu = JFactory::getApplication()->getMenu();
            $active = $menu->getActive();
            
            if(is_object($active)){
                
                $pageclass_sfx = $active->params->get('pageclass_sfx');
                $this->assignRef('pageclass_sfx', $pageclass_sfx);

                $page_heading = $active->params->get('page_heading');
                $this->assignRef('page_heading', $page_heading);
                
            } else {
                
                $empty = '';
                $this->assignRef('pageclass_sfx', $empty);
                $this->assignRef('page_heading', $page_title);
            }
            
            // META DATA

            CrBcHelpers::setMetaData(
                    $product->metadesc, 
                    $product->metakey, 
                    $product->metadata, 
                    $product->id, 
                    'product_metadata'
            );
        
            $canonical_url = CrBcHelpers::setCanonical('product', $product);
            
            // Setting page title
            
            $page_title = $this->get('PageTitle');
            
            CrBcHelpers::setPageTitle($page_title != '' ? $page_title . ' - ' . $product->title : $product->title);
            
            // setting pathway
			
            $schema_category_path = '';
            
            $db = JFactory::getDbo();

            $db->setQuery("Select * From #__breezingcommerce_product_categories Where product_id = " . $db->quote($product->id) . " Order By ordering Limit 1");
            $product_category = $db->loadObject();

            if($product_category){

                $db->setQuery("SELECT parent.title, parent.id, parent.alias
                                    FROM #__breezingcommerce_categories AS node,
                                            #__breezingcommerce_categories AS parent
                                    WHERE node.lft BETWEEN parent.lft AND parent.rgt
                                            AND node.id = ".$db->quote($product_category->category_id)."
                                    ORDER BY node.lft");

                $entries = $db->loadObjectList();

                if( count($entries) ){

                    unset($entries[0]);
                    $entries = array_merge($entries);

                }

                foreach( $entries As $entry ){

                    $translated_object = CrBcHelpers::loadTranslation($entry->id, 'category');
                    if($translated_object !== null){
                        $entry->description = $translated_object->body;
                        $entry->title = $translated_object->title;
                        $entry->alias = $translated_object->alias;
                    }

                    $alias = $entry->alias == '' ? CrBcHelpers::getSlug($entry->title) : $entry->alias;
                    $category_url = JRoute::_('index.php?option=com_breezingcommerce&controller=category&category_id='.$entry->id.'&Itemid='.CrBcHelpers::getDefaultMenuItemId().'&alias='.$alias);

                    $schema_category_path .= '/' . $entry->title;
                    
                    JFactory::getApplication()->getPathway()->addItem($entry->title, $category_url);
                }

            }
            
            JFactory::getApplication()->getPathway()->addItem($product->title);
            
            // creating JSON/LD based Schema.org
            
            $content = strip_tags($product->introtext);
            if(trim($content) != ''){
                $pos = @strpos($content, ' ', 160);
                $content = substr($content,0,$pos );
            }
            
            CrBcHelpers::addJsonLd('
                "@context": "http://schema.org/",
                "@type": "Product",
                "name": '.CrBcHelpers::jsonEncode(strip_tags($product->title)).',
                "description": '.CrBcHelpers::jsonEncode($content).',
                "url" : '.CrBcHelpers::jsonEncode($canonical_url).'
                '.($schema_category_path != '' ?  ',"category" : ' . CrBcHelpers::jsonEncode(ltrim($schema_category_path,'/')) : '').'
                '.(trim($product->sku) != '' ? ', "sku" : ' . CrBcHelpers::jsonEncode(trim($product->sku)) : '').'
                '.($default_product_image_path != '' ? ', "image" : ' . CrBcHelpers::jsonEncode($default_product_image_path) : '').'
                '.( ' , "offers" : { "availability" : '.($product->virtual_product || $product->stock > 0 || $product->use_combinations ? '"http://schema.org/InStock"' : '"http://schema.org/OutOfStock"').', "price" : '.CrBcHelpers::jsonEncode($cart->formatNumber($price_array['gross'],'.','')).', "priceCurrency" : '.CrBcHelpers::jsonEncode($cart->currency_code).' } ' ).'
            ');
            
            $config = CrBcHelpers::getBcConfig();
            
            if( $config->get('enable_alternate_tags',  false) ){
            
                $enabled_languages = CrBcHelpers::getEnabledLanguages();

                foreach($enabled_languages As $enabled_language){

                    $product_url = JRoute::_('index.php?option=com_breezingcommerce&controller=product&alias='.$product->alias.'&product_id='.$product->id.'&Itemid='.JRequest::getInt('Itemid',0).'&lang='.$enabled_language->sef);

                    CrBcHelpers::setAlternate($product_url, $enabled_language->lang_code);
                }
            
            }
            
            parent::display($tpl);
        
        }
    }
}
