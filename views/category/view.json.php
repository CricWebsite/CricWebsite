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
        
        $products_to_add = '';
        $this->assignRef('products_to_add', $products_to_add);
        
        $image_tag = '';
        $this->assignRef('image_tag', $image_tag);

        // apply module rendering on 
        $category = new stdClass();
        $category->introtext = '';
        $category->fulltext = '';
        
        $this->assignRef('category', $category);
        
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
        
        $cart = new CrBcCart( JFactory::getSession()->get('crbc_cart', array()) );
        
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
            
            $product->has_attributes = CrBcCart::hasAttributes($product->id);
            $product->has_properties = CrBcCart::hasProperties($product->id);
            
            $product->url = JRoute::_('index.php?option=com_breezingcommerce&alias='.$product->alias.'&controller=product&product_id='.$product->id.'&Itemid='.CrBcHelpers::getDefaultMenuItemId());
            
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
       
        $this->assignRef('cart_instance', $cart);
        
        $page_heading = $this->get('PageHeading');
        $this->assignRef('page_heading', $page_heading);
        
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
        
        
        parent::display($tpl);
    }
}
