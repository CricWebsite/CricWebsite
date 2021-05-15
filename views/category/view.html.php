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

class BreezingcommerceViewCategory extends JViewLegacy
{
    function display($tpl = null)
    {
        require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcAttributes.php' );
        require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcProperties.php' );
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcImage.php');
        
        // clear (unverify anything that's no longer valid
        // (downloads and verified items)
        CrBcCart::unverifyItemsGlobally();

        // re-check to recover previously lost groups if items intersect
        CrBcCart::verifyItemsGlobally();
        
        $page_heading = $this->get('PageHeading');
        $this->assignRef('page_heading', $page_heading);
        
        $canonical_url = '';
        
        $category = null;
        
        if( JFactory::getSession()->get('com_breezingcommerce.filter', null) === null ){
        
            $category = $this->get('Category');

            $image_tag = CrBcImage::getCategoryImageTag($category->image_physical_name, 'medium', $category->id, $category->image_alt_name);

            // we only want the pre-text
            // Clean text for xhtml transitional compliance
            $desc = $category->description;
            $introtext = '';
            $fulltext  = '';
            $desc = str_replace('<br>', '<br />', $desc);

            // Search for the {readmore} tag and split the text up accordingly.
            $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
            $tagPos = preg_match($pattern, $desc);

            if ($tagPos == 0) {
                $introtext = $desc;
            } else {
                list($introtext, $fulltext) = preg_split($pattern, $desc, 2);
            }

            $category->introtext = $introtext;
            $category->fulltext = $fulltext;

            $this->assignRef('image_tag', $image_tag);

            // apply module rendering on 

            $this->assignRef('category', $category);
            
            if(JRequest::getInt('start',null) === null && JRequest::getInt('limitstart',null) === null){
                
                $canonical_url = CrBcHelpers::setCanonical('category', $category);
                
            }
        
            $page_title = $this->get('PageTitle');
            
            CrBcHelpers::setPageTitle($page_title != '' ? $page_title . ' - ' . $category->title : $category->title);
        }
        
        $view_type = JFactory::getSession()->get('breezingcommerce.view_type','block');
        $this->assignRef('view_type', $view_type);
        
        $product_order = JFactory::getSession()->get('breezingcommerce.product_order','');
        $this->assignRef('product_order', $product_order);
        
        $products = $this->get('Products');
        $this->assignRef('products', $products);
        
        $hide_details = $this->get('HideDetails');
        $this->assignRef('hide_details', $hide_details);
        
        $show_page_heading = $this->get('ShowPageHeading');
        $this->assignRef('show_page_heading', $show_page_heading);
        
        $cart_add_button_location = $this->get('CartAddButtonLocation');
        $this->assignRef('cart_add_button_location', $cart_add_button_location);
        
        $enable_ajax_cart_add = $this->get('EnableAjaxCartAdd');
        $this->assignRef('enable_ajax_cart_add', $enable_ajax_cart_add);
        
        $enable_dynamic_price = $this->get('EnableDynamicPrice');
        $this->assignRef('enable_dynamic_price', $enable_dynamic_price);
        
        $enable_category_description = $this->get('EnableCategoryDescription');
        $this->assignRef('enable_category_description', $enable_category_description);
        
        $link_title = $this->get('LinkTitle');
        $this->assignRef('link_title', $link_title);
        
        $display_text = $this->get('DisplayText');
        $this->assignRef('display_text', $display_text);
        
        $display_image = $this->get('DisplayImage');
        $this->assignRef('display_image', $display_image);
        
        $getEnableSortBy = $this->get('EnableSortBy');
        $this->assignRef('enable_sortby', $getEnableSortBy);
        
        $getEnableShowPerPage = $this->get('EnableShowPerPage');
        $this->assignRef('enable_showperpage', $getEnableShowPerPage);
        
        $getEnableBlockView = $this->get('EnableBlockView');
        $this->assignRef('enable_blockview', $getEnableBlockView);
        
        $getEnableListView = $this->get('EnableListView');
        $this->assignRef('enable_listview', $getEnableListView);
        
        // TODO: enable / disable ajax system in config
        $ajax_cart_add = $enable_ajax_cart_add;

        CrBcHelpers::addCssFile(Juri::root(true).'/components/com_breezingcommerce/css/font-awesome/css/font-awesome.min.css');
        JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/scrollto.js');
        JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/product.js');
        JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/cart.js');
        
        if($enable_dynamic_price){
            CrBcHelpers::addCss('
                .crbc-total { display: block !important; }
            ');
        }
        
        $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );
        $products_to_add = '';
        
        // just in case we need the customer id for anything regarding prices
 
        $customer_id = 0;

        if($cart instanceof CrBcCart && JFactory::getUser()->get('id', 0) != 0){
            JFactory::getDbo()->setQuery("Select id From #__breezingcommerce_customers Where userid = " . intval(JFactory::getUser()->get('id', 0)) . " Limit 1");
            $customer_id = JFactory::getDbo()->loadResult();
        } else {
            $customer_id = isset($cart->cart['customer_id']) ? $cart->cart['customer_id'] : 0;
       }
        
        $product_form = new JRegistry(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'product.xml');
            
        $cart_items = $cart->getItems(true);
        
        $immediate_allowed = false;
        if(count($cart_items) <= 1){
            $immediate_allowed = true;
        }
        
		$price_arrays = array();
		
        foreach($products As $product){
            
            $desc = $product->description;
            $introtext = '';
            $fulltext  = '';
            $desc = str_replace('<br>', '<br />', $desc);

            // Search for the {readmore} tag and split the text up accordingly.
            $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
            $tagPos = preg_match($pattern, $desc);

            if ($tagPos == 0) {
                $introtext = $desc;
            } else {
                list($introtext, $fulltext) = preg_split($pattern, $desc, 2);
            }

            $product->introtext = $introtext;
            $product->fulltext = $fulltext;
            
            $display_plugins = CrBcHelpers::applyProductDisplayPlugins($product->id, 0, JRequest::getVar('view','') == 'category' || JRequest::getVar('controller','') == 'category');
            $product->display_plugins = $display_plugins;
            
            $force_details = false;
            foreach($product->display_plugins['force_details'] As $force_details){
                if($force_details){
                    $force_details = true;
                    break;
                }
            }
            
            $product->force_details = $force_details;
            
            $applied = CrBcCart::applyProductPricePlugins($product->id);
            
            if($applied !== null){
            
                $product->price = $applied->price;
                $product->sale_price = $product->sale_price;
            
            }
            
            $form = $product_form->loadString($product->attribs);
            $product->minimum_amount = intval($form->get('minimum_amount', 1));
            $product->maximum_amount = intval($form->get('maximum_amount', 99999));
            
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
            
            //$product->url = JRoute::_('index.php?option=com_breezingcommerce&controller=product&alias='.$product->alias.'&product_id='.$product->id.'&return_url_list='.CrBcHelpers::bSixtyFourEncode(JUri::getInstance()->toString()));
            $product->url = JRoute::_('index.php?option=com_breezingcommerce&controller=product&alias='.$product->alias.'&product_id='.$product->id.'&Itemid='.JRequest::getInt('Itemid',0));
            
            JFactory::getDbo()->setQuery("Select * From #__breezingcommerce_images Where published = 1 And product_id = " . intval($product->id) . " Order by ordering Limit 1");
            $main_image = JFactory::getDbo()->loadObject();
            
            if(is_object($main_image)){
                if(!$hide_details){
                    $product->product_image = '<a href="'.$product->url.'"><img class="crbc-product-image" id="crbc-product-image-'.$product->id.'" src="'.JUri::root(true).'/images/breezingcommerce/products/medium/'.$main_image->physical_name.'"></a>';
                }else{
                    $product->product_image = '<img class="crbc-product-image" id="crbc-product-image-'.$product->id.'" src="'.JUri::root(true).'/images/breezingcommerce/products/medium/'.$main_image->physical_name.'">';
                }
            }else{
                $product->product_image = '';
            }
            
            $products_to_add .= 'if( !crbc_products_assoc['.CrBcHelpers::jsonEncode($product->id).'] ){'."\n";
            $products_to_add .= '    crbc_the_product = new CrBcProduct('.CrBcHelpers::jsonEncode($product->id).', '.CrBcHelpers::jsonEncode($enable_dynamic_price).', '.CrBcHelpers::jsonEncode(JUri::getInstance()->toString()).', '.$product->minimum_amount.', '.$product->maximum_amount.');'."\n";
            $products_to_add .= '    crbc_the_product.isComplex = '.CrBcHelpers::jsonEncode(JRequest::getVar('view','') == 'category' || JRequest::getVar('controller','') == 'category').';'."\n";
            $products_to_add .= '    crbc_products.push(crbc_the_product);'."\n";
            $products_to_add .= '    crbc_products_assoc['.CrBcHelpers::jsonEncode($product->id).'] = crbc_the_product;'."\n";
            $products_to_add .= '}'."\n";
            
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
            
			$price_arrays[$product->id] = $price_array;
			
			$price_group = $cart->getOrderedPriceGroup($price_array);
            
            $product->price_group = $price_group;
            
            $sale_price_array = CrBcCart::getItemPrice($product->sale_price, $cart->currency_conversion_rate, $product->producttaxclass_id, array(), array(), 1, $customer_id);
            
            if($sale_price_array['gross'] == $price_array['gross']){
                $sale_price_array['gross'] = 0;
            }
            
            if($sale_price_array['gross'] > 0){
                $sale_price_group = $cart->getOrderedPriceGroup($sale_price_array);
                $product->sale_price_group = $sale_price_group;
            }else{
                $product->sale_price_group = null;
            }
            
            // do we want to display the currency info (only if force currency is enabled in the current customer group)
            if( $cart->currency_code_info !== null ){
                
                $price_info_array = CrBcCart::getItemPrice($product->price, $cart->currency_conversion_rate_info, $product->producttaxclass_id, array(), array(), 1, $customer_id);
                $price_info_group = $cart->getOrderedPriceGroup($price_info_array, $cart->currency_symbol_info, $cart->currency_code_info);
                $product->price_info_group = $price_info_group;
                
                $sale_price_info_array = CrBcCart::getItemPrice($product->sale_price, $cart->currency_conversion_rate_info, $product->producttaxclass_id, array(), array(), 1, $customer_id);
                
                if($sale_price_info_array['gross'] == $price_info_array['gross']){
                    $sale_price_info_array['gross'] = 0;
                }
                
                if($sale_price_info_array['gross'] > 0){
                    $sale_price_info_group = $cart->getOrderedPriceGroup($sale_price_info_array, $cart->currency_symbol_info, $cart->currency_code_info);
                    $product->sale_price_info_group = $sale_price_info_group;
                }else{
                    $product->sale_price_info_group = null;
                }
                
            } else {
                
                $product->price_info_group = null;
                $product->sale_price_info_group = null;
            }
            
            // we only want the pre-text
            // Clean text for xhtml transitional compliance
            $desc = $product->description;
            $introtext = '';
            $fulltext  = '';
            $desc = str_replace('<br>', '<br />', $desc);

            // Search for the {readmore} tag and split the text up accordingly.
            $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
            $tagPos = preg_match($pattern, $desc);

            if ($tagPos == 0) {
                $introtext = $desc;
            } else {
                list($introtext, $fulltext) = preg_split($pattern, $desc, 2);
            }
            
            $product->description = $introtext;
            
            
        }
        
        JFactory::getDocument()->addScriptDeclaration(
        '
        var crbc_item_id = '.CrBcHelpers::jsonEncode(JRequest::getInt('Itemid', 0)).';
		var crbc_cart_error_close = '.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_CLOSE')).';
        var crbc_cart_url = '.CrBcHelpers::jsonEncode(JRoute::_('index.php?option=com_breezingcommerce&controller=cart&Itemid='.CrBcHelpers::getDefaultMenuItemId('cart'))).';
        var crbc_checkout_url = '.CrBcHelpers::jsonEncode(JRoute::_('index.php?option=com_breezingcommerce&controller=checkout&Itemid='.CrBcHelpers::getDefaultMenuItemId('checkout'))).';
        var crbc_cart = new CrBcCart('.CrBcHelpers::jsonEncode($ajax_cart_add).', '.CrBcHelpers::jsonEncode(JUri::getInstance()->toString()).','.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_ADD_TO_CART_MSG')).');
        var crbc_products = new Array();
        var crbc_products_assoc = {};
        ');
        
        JFactory::getDocument()->addScriptDeclaration(CrBcProperties::getPropertyJs());
        JFactory::getDocument()->addScriptDeclaration(CrBcAttributes::getAttributeJs());
        JFactory::getDocument()->addScriptDeclaration(CrBcAttributes::getRequiredJs());
        
        $products_to_add .= '
        function crbcUpdatePrices(){
        
            if( typeof crbc_products != "undefined" ){
                for(var i = 0; i < crbc_products.length; i++){
                    crbc_products[i].update_price();
                }
            }
        }

        jQuery(document).ready(function(){
            jQuery(".crbc-add-to-cart-button button").on("click", crbc_cart.add_to_cart);
            if('.CrBcHelpers::jsonEncode($enable_dynamic_price).'){
                crbcUpdatePrices();
            };
        });
        ';
        
        $this->assignRef('products_to_add', $products_to_add);
        
        $this->assignRef('cart_instance', $cart);
        
        $pageclass_sfx = $this->get('PageClass');
        $this->assignRef('pageclass_sfx', $pageclass_sfx);
        
        $col_break = intval($this->get('ColumnBreak'));
        
        if($col_break < 1 || $col_break > 12 || 12 % $col_break != 0){
            $col_break = 1;
        }
        
        $this->assignRef('col_break', $col_break);
        
        $pagination = $this->get('Pagination');
        $this->assignRef('pagination', $pagination);
        
        $total = $this->get('Total');
        $this->assignRef('total', $total);
        
        $return_url = CrBcHelpers::bSixtyFourEncode(JUri::getInstance()->toString());
        $this->assignRef('return_url', $return_url);
        
        // META DATA
        
        if( $category !== null && JFactory::getSession()->get('com_breezingcommerce.filter', null) === null ){
        
            CrBcHelpers::setMetaData(
                    $category->metadesc, 
                    $category->metakey, 
                    $category->metadata, 
                    $category->id, 
                    'category_metadata'
            );
        
        
            $db = JFactory::getDbo();
            $db->setQuery("SELECT parent.title, parent.id, parent.alias
                                FROM #__breezingcommerce_categories AS node,
                                        #__breezingcommerce_categories AS parent
                                WHERE node.lft BETWEEN parent.lft AND parent.rgt
                                        AND node.id = ".$db->quote($category->id)."
                                ORDER BY node.lft");

            $entries = $db->loadObjectList();

            if( count($entries) ){

                unset($entries[0]);
                $entries = array_merge($entries);

            }

            if( count($entries) ){

                unset($entries[count($entries)-1]);
                $entries = array_merge($entries);
            }

            $schema_category_path = '';
            
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

            $schema_category_path .= '/' . $category->title;
            
            JFactory::getApplication()->getPathway()->addItem($category->title);
        
            // creating JSON/LD based Schema.org
            
            $schema_items = '';
            
            foreach($products As $product){
                
                // not setting, only returning (last param)
                $product_canonical_url = CrBcHelpers::setCanonical('product', $product, true);

                $default_product_image_path = '';
                
                if( isset($main_image->physical_name) && $main_image->physical_name != '' ){
                
                    $uri     = Juri::getInstance();
                    $current = $uri->toString( array('scheme', 'host', 'port'));
                    $default_product_image_path = $current . '/images/breezingcommerce/products/medium/'.$main_image->physical_name;

                }
                
                $content = strip_tags($introtext);
                if(trim($content) != ''){
                    $pos = @strpos($content, ' ', 160);
                    $content = substr($content,0,$pos );
                }
                
                $schema_items .= '
                    {
                        "@type": "Product",
                        "name": '.CrBcHelpers::jsonEncode(strip_tags($product->title)).',
                        "description": '.CrBcHelpers::jsonEncode($content).',
                        "url" : '.CrBcHelpers::jsonEncode($product_canonical_url).'
                        '.($schema_category_path != '' ?  ',"category" : ' . CrBcHelpers::jsonEncode(ltrim($schema_category_path,'/')) : '').'
                        '.(trim($product->sku) != '' ? ', "sku" : ' . CrBcHelpers::jsonEncode(trim($product->sku)) : '').'
                        '.($default_product_image_path != '' ? ', "image" : ' . CrBcHelpers::jsonEncode($default_product_image_path) : '').'
                        '.( ' , "offers" : { "availability" : '.($product->virtual_product || $product->stock > 0 || $product->use_combinations ? '"http://schema.org/InStock"' : '"http://schema.org/OutOfStock"').', "price" : '.CrBcHelpers::jsonEncode($cart->formatNumber($price_arrays[$product->id]['gross'],'.','')).', "priceCurrency" : '.CrBcHelpers::jsonEncode($cart->currency_code).' } ' ).'
                    },';
                
            }
            
            CrBcHelpers::addJsonLd('
                
                "@context": "http://schema.org/",
                "url" : '.CrBcHelpers::jsonEncode($canonical_url).',
                "@type": "ItemList",
                "itemListElement": [
                    '.rtrim(trim($schema_items),',').'
                ],
                "numberOfItems": "'.count($products).'"
            ');
            
            $config = CrBcHelpers::getBcConfig();
            
            if( $config->get('enable_alternate_tags',  false) ){
            
                $enabled_languages = CrBcHelpers::getEnabledLanguages();

                foreach($enabled_languages As $enabled_language){

                    $alias = $category->alias == '' ? CrBcHelpers::getSlug($category->title) : $category->alias;
                    $category_url = JRoute::_('index.php?option=com_breezingcommerce&controller=category&category_id='.$category->id.'&Itemid='.JRequest::getInt('Itemid', 0).'&alias='.$alias.'&lang='.$enabled_language->sef);

                    CrBcHelpers::setAlternate($category_url, $enabled_language->lang_code);
                }
            
            }
            
        }
        else
        {
            JFactory::getApplication()->getPathway()->addItem($page_heading);
        }
        
        parent::display($tpl);
    }
}
