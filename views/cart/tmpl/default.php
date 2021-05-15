<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// index.php/en/single-product.html?controller=cart&task=add&product_id=501

// adding responsive tables, they are more or less template relevant
CrBcHelpers::addCssFile(JUri::root(true).'/components/com_breezingcommerce/js/responsive-tables/ng_responsive_tables.css');
JFactory::getDocument()->addScript(JUri::root(true).'/components/com_breezingcommerce/js/responsive-tables/ng_responsive_tables.js');
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('table.crbc-responsive').ngResponsiveTables({
            smallPaddingCharNo: 13,
            mediumPaddingCharNo: 18,
            largePaddingCharNo: 30
    });
});
</script>

<div class="crbc crbc-page crbc-cart-page<?php echo $this->pageclass_sfx != '' ? ' ' . $this->pageclass_sfx : ''; ?>">
    
    <?php
    if(!isset( $this->empty_cart ) && isset($this->return_url) && $this->return_url != ''){
    ?>
    <a class="crbc-cart-back-button crbc-btn btn btn-primary pull-right" href="<?php echo $this->return_url; ?>"><i class="crbc-fa crbc-fa-chevron-left"></i> Back</a>
    <?php
    }
    ?>
    
    <div class="crbc-cart-title page-header">
        <h2><?php echo JText::_('COM_BREEZINGCOMMERCE_CART_TITLE');?></h2>
    </div>
    
    <div class="crbc-product crbc-row row-fluid">

        <?php
        // return just a message if there is an empty cart
        if( isset( $this->empty_cart ) ):
        ?>
        <div class="crbc-poster hero-unit"><div><?php echo JText::_('COM_BREEZINGCOMMERCE_CART_EMPTY');?></div></div>
        <?php
        else:
        ?>
        
        <table class="crbc-cart crbc-responsive table table-striped">

            <thead>
                <th class="crbc-product-column"><?php echo JText::_('COM_BREEZINGCOMMERCE_PRODUCT')?></th>
                
                <!--<th><?php echo JText::_('COM_BREEZINGCOMMERCE_QUANTITY')?></th>-->
                
                <?php if($this->config->get('cart_display_unit_net_column', 1) == 1): ?>
                <th><?php echo JText::_('COM_BREEZINGCOMMERCE_UNIT_PRICE_NET')?></th>
                <?php endif;?>
                
                <?php if($this->config->get('cart_display_total_net_column', 1) == 1): ?>
                <th><?php echo JText::_('COM_BREEZINGCOMMERCE_TOTAL_PRICE_NET')?></th>
                <?php endif;?>
                
                <?php if($this->config->get('cart_display_unit_tax_column', 1) == 1): ?>
                <th><?php echo JText::_('COM_BREEZINGCOMMERCE_UNIT_TAX')?></th>
                <?php endif;?>
                
                <?php if($this->config->get('cart_display_unit_gross_column', 1) == 1): ?>
                <th><?php echo JText::_('COM_BREEZINGCOMMERCE_UNIT_PRICE')?></th>
                <?php endif;?>
                
                <?php if($this->config->get('cart_display_total_tax_column', 1) == 1): ?>
                <th><?php echo JText::_('COM_BREEZINGCOMMERCE_TOTAL_TAX')?></th>
                <?php endif;?>
                
                <?php if($this->config->get('cart_display_total_gross_column', 1) == 1): ?>
                <th><?php echo JText::_('COM_BREEZINGCOMMERCE_TOTAL_PRICE')?></th>
                <?php endif;?>
                
            </thead>
            
            <tbody>
                
                <?php foreach($this->items As $item):?>

                <tr class="crbc-item" id="crbc-item-<?php echo $item->id;?>">
                    <td class="crbc-product-column">
                        <div class="crbc-item-info">
                            
                            <h4 class="crbc-product-title"><?php echo $item->title?></h4>
                            
                            <?php
                            // by default using the first image,
                            // template overrides may want to do it differently
                            if(is_array($item->images) && count($item->images)){
                                $image = $item->images[0];
                                if(JFile::exists(JPATH_SITE.'/images/breezingcommerce/products/medium/'.$image->physical_name)){
                            ?>
                            <div class="crbc-product-image">
                                <img class="img-polaroid" title="<?php echo $image->alt_name;?>" alt="" src="<?php echo JUri::root(true).'/images/breezingcommerce/products/medium/'.$image->physical_name;?>"/>
                            </div>
                            <?php
                                }
                            }
                            ?>
                            
                            <?php
                            $product_form = new JRegistry(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'product.xml');
                            $form = $product_form->loadString($item->attribs);
                            $item->minimum_amount = intval($form->get('minimum_amount', 1));
                            $item->maximum_amount = intval($form->get('maximum_amount', 99999));
							if(in_array($this->config->get('backorders', 'DISALLOWED_HIDDEN'), array('DISALLOWED_HIDDEN','DISALLOWED_VISIBLE')) && $item->stock < $item->maximum_amount){
								$item->maximum_amount = $item->stock;
							}
                            if($item->maximum_amount > $item->minimum_amount){
                            ?>
                            <label class="crbc-amount-input" for="crbc-cart-item-amount-<?php echo $item->id;?>"><?php echo JText::_('COM_BREEZINGCOMMERCE_QUANTITY')?></label>
                            <div class="crbc-cart-item-amount input-append">
                                <input type="text" id="crbc-cart-item-amount-<?php echo $item->id;?>" onchange="crbc_cart.change_item_amount(parseInt(this.value), <?php echo $item->product_id;?>, <?php echo $item->id;?>, 'change', <?php echo $item->minimum_amount; ?>, <?php echo $item->maximum_amount; ?>);" value="<?php echo intval($item->amount); ?>">
                                <button class="crbc-btn btn" type="button" onclick="crbc_cart.change_item_amount(1, <?php echo $item->product_id;?>, <?php echo $item->id;?>, 'click', <?php echo $item->minimum_amount; ?>, <?php echo $item->maximum_amount; ?>);"><i class="crbc-fa crbc-fa-chevron-up"></i></button>
                                <button class="crbc-btn btn" type="button" onclick="crbc_cart.change_item_amount(-1, <?php echo $item->product_id;?>, <?php echo $item->id;?>, 'click', <?php echo $item->minimum_amount; ?>, <?php echo $item->maximum_amount; ?>);"><i class="crbc-fa crbc-fa-chevron-down"></i></button>
                                <?php if($this->ajax_cart_add): ?>
                                <button class="crbc-btn btn" type="button" onclick="crbc_cart.remove_from_cart(<?php echo $item->id;?>,<?php echo $item->id;?>);"><i class="crbc-fa crbc-fa-trash-o"></i></button>
                                <?php else: ?>
                                <button class="crbc-btn btn" type="button" onclick="crbc_cart.refresh_cart_pricing(<?php echo $item->id;?>,<?php echo $item->id;?>)"><i class="crbc-fa crbc-fa-refresh"></i></button>
                                <?php endif; ?>
                            </div>
                            <?php
                            } else {
                            ?>
                            <div class="crbc-cart-item-amount input-append">
                                <input disabled="disabled" type="text" id="crbc-cart-item-amount-<?php echo $item->id;?>" value="<?php echo intval($item->amount); ?>">
                                <?php if($this->ajax_cart_add): ?>
                                <button class="crbc-btn btn" type="button" onclick="crbc_cart.remove_from_cart(<?php echo $item->id;?>,<?php echo $item->id;?>);"><i class="crbc-fa crbc-fa-trash-o"></i></button>
                                <?php else: ?>
                                <button class="crbc-btn btn" type="button" onclick="crbc_cart.refresh_cart_pricing(<?php echo $item->id;?>,<?php echo $item->id;?>)"><i class="crbc-fa crbc-fa-refresh"></i></button>
                                <?php endif; ?>
                            </div>  
                            <?php
                            }
                            ?>
                            <?php if(!$this->ajax_cart_add): ?>
                            <div class="crbc-cart-remove-button crbc-btn-group btn-group">
                                <button class="crbc-btn btn" type="button" onclick="crbc_cart.remove_from_cart(<?php echo $item->id;?>,<?php echo $item->id;?>);"><i class="crbc-fa crbc-fa-trash-o"></i></button>
                            </div>
                            <?php endif; ?>
                            
                            
                            <div class="crbc-product-info">
                                 
                                <?php echo $item->properties ? '<p>' . $item->properties . '</p>': ''?> 
                                <?php echo $item->attributes?>
                                <?php 
                                foreach( $item->order_item_info As $order_item_info ){
                                ?>
                                <div class="crbc-order-item-info">
                                    <h4><?php echo $this->escape($order_item_info->title); ?></h4>
                                    <?php 
                                    echo $order_item_info->text;
                                    ?>
                                </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                        
                    </td>
                    <!--<td>
                        original quantity column
                    </td>-->

                    <?php if($this->config->get('cart_display_unit_net_column', 1) == 1): ?>
                    <td><div class="crbc-cart-item-price-single-net" id="crbc-cart-item-price-single-net-<?php echo $item->id;?>"><?php echo $this->cart_instance->formatPrice($item->price_single_net)?></div></td>
                    <?php endif;?>

                    <?php if($this->config->get('cart_display_total_net_column', 1) == 1): ?>
                    <td><div class="crbc-cart-item-price-net" id="crbc-cart-item-price-net-<?php echo $item->id;?>"><?php echo $this->cart_instance->formatPrice($item->price_net)?></div></td>
                    <?php endif;?>

                    <?php if($this->config->get('cart_display_unit_tax_column', 1) == 1): ?>
                    <td>
                        <div class="crbc-cart-item-price-single-tax" id="crbc-cart-item-price-single-tax-<?php echo $item->id;?>">
                            <?php
                            $tax_list = $item->single_price_tax_list;
                            foreach($tax_list As $tax){
                            ?>
                            <div class="crbc-price-single-tax"><?php echo $this->cart_instance->formatPrice($tax['tax']) . '<br />' . $tax['name'] . ' ('.CrBcCart::formatNumber($tax['rate']).'%)<br />';?></div>
                            <?php
                            }
                            ?>
                        </div>
                    </td>
                    <?php endif;?>
                    
                    <?php if($this->config->get('cart_display_unit_gross_column', 1) == 1): ?>
                    <td><div class="crbc-cart-item-price-single-gross" id="crbc-cart-item-price-single-gross-<?php echo $item->id;?>"><?php echo $this->cart_instance->formatPrice($item->price_single_gross)?></div></td>
                    <?php endif;?>

                    <?php if($this->config->get('cart_display_total_tax_column', 1) == 1): ?>
                    <td>
                        <div class="crbc-cart-item-price-total-tax" id="crbc-cart-item-price-total-tax-<?php echo $item->id;?>">
                            <?php
                            $tax_list = $item->price_tax_list;
                            foreach($tax_list As $tax){
                            ?>
                            <div class="crbc-price-tax"><?php echo $this->cart_instance->formatPrice($tax['tax']) . '<br />' . $tax['name'] . ' ('.CrBcCart::formatNumber($tax['rate']).'%)<br />';?></div>
                            <?php
                            }
                            ?>
                        </div>
                    </td>
                    <?php endif;?>
                    
                    <?php if($this->config->get('cart_display_total_gross_column', 1) == 1): ?>
                    <td><div class="crbc-cart-item-price-gross" id="crbc-cart-item-price-gross-<?php echo $item->id;?>"><?php echo $this->cart_instance->formatPrice($item->price_gross)?></div></td>
                    <?php endif;?>

                </tr>

                <?php endforeach; ?>
            
            </tbody>
        </table>
        
        <div class="crbc-cart-plugins">
        
            <?php
            if(count($this->order_info->shipping_options)){
            ?>
            <div class="crbc-cart-shipping-plugins">
                <h4 class="crbc-cart-shipping-title"><?php echo JText::_('COM_BREEZINGCOMMERCE_SHIPPING_CART_TITLE');?></h4>
                <p class="crbc-cart-shipping-description"><?php echo JText::_('COM_BREEZINGCOMMERCE_SHIPPING_CART_DESCRIPTION');?></p>
                
                <div class="crbc-shipping-error crbc-alert alert alert-error"></div>
                
                <form name="shipping_method_form" id="shipping_method_form" method="post" action="#">
                    <div class="controls">
                <?php
                    $shipping_options_count = count($this->order_info->shipping_options);
                    $i = 0;
                    foreach($this->order_info->shipping_options As $shipping_option){
                ?>
                    <div class="crbc-shipping-selection crbc-alert-info alert alert-info">

                        <?php
                        if($shipping_options_count > 1){
                        ?>
                        <label class="crbc-control-label control-label" for="shipping_option<?php echo $shipping_option['id']; ?>">
                            <input <?php echo $this->shipping_plugin_id == $shipping_option['id'] || ( $this->shipping_plugin_id == 0 && $i  == 0 ) ? 'checked="checked" ' : ''; ?>type="radio" name="shipping_option" id="shipping_option<?php echo $shipping_option['id']; ?>" value="<?php echo $shipping_option['id']; ?>"/>
                            <span class="crbc-shipping-plugin-title"><?php echo htmlentities($shipping_option['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                        
                        <div onclick="document.getElementById('shipping_option<?php echo $shipping_option['id']; ?>').checked = true;">
                        <?php 
                        }
                        else{
                        ?>
                            <input type="hidden" name="shipping_option" id="shipping_option<?php echo $shipping_option['id']; ?>" value="<?php echo $shipping_option['id']; ?>"/>
                        <?php
                        }
                        
                        echo $shipping_option['options'];
                        
                        if($shipping_options_count > 1){
                        ?>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                <?php
                        $i++;
                    }
                ?>
                    </div>
                </form>
                <button class="crbc-add-shipping-button crbc-btn btn btn-secondary"><i class="crbc-fa crbc-fa-arrow-circle-o-right"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_ADD_SHIPPING_OPTION'); ?></button>
            </div>
            
            <div class="crbc-clearfix clearfix"></div>
            
            <?php
            }
            ?>
            
            <?php
            if(count($this->cart_plugins)){
            ?>
            <div class="crbc-cart-plugin-instances">
            <?php
                foreach($this->cart_plugins As $cart_plugin){
            ?>
                
            <h4 class="crbc-cart-plugin-title"><?php echo $cart_plugin->getPluginDisplayName();?></h4>
            <p class="crbc-cart-plugin-description"><?php echo $cart_plugin->getPluginDescription();?></p>
            
            <div id="cart-plugin-<?php echo $cart_plugin->type; ?>-<?php echo $cart_plugin->name; ?>-error" class="cart-plugin-error crbc-alert alert alert-error"></div>
            
            <form name="cart-plugin-form" id="cart-plugin-form-<?php echo $cart_plugin->type; ?>-<?php echo $cart_plugin->name; ?>" method="post" action="<?php echo $cart_plugin->getViewportFormAction(); ?>">
                    <div class="controls">
                        <div class="crbc-cart-plugin-selection crbc-alert-info alert alert-info">
                        <?php echo $cart_plugin->getViewport(); ?>
                        </div>
                    </div>
            </form>
            <?php
            if( $cart_plugin->getViewportSubmitButtonClass() ){
            ?>
                <button class="<?php echo $cart_plugin->getViewportSubmitButtonClass();?> crbc-cart-plugins-submit-button crbc-btn btn btn-secondary"><?php echo $cart_plugin->getViewportSubmitButtonText(); ?></button>
            <?php
            }
            ?>
            
            <div class="crbc-clearfix clearfix"></div>
            
            <?php
                }
            ?>
            </div>
            <?php
            }
            ?>
            
        </div>
        
        <div class="crbc-cart-final crbc-well well">

                    <!-- Taxes if in calculation -->
                    <?php if($this->config->get('cart_display_tax_position', 'below_calculation') == 'in_calculation'): ?>
                    
                        <!-- Subtotal -->
                        <?php if($this->config->get('cart_display_subtotal_net_column', 1) == 1): ?>

                        <div class="crbc-cart-price-net-block">

                            <div class="crbc-cart-final-left"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_SUBTOTAL_NET' ); ?></div>

                            <div class="crbc-cart-final-middle"></div>

                            <div id="crbc-cart-price-net" class="crbc-cart-final-right"><?php echo $this->cart_instance->formatPrice($this->order_info->price_net)?></div>

                            <div class="crbc-clearfix clearfix"></div>

                        </div>

                        <?php endif; ?>
                        
                        <?php foreach($this->order_info->price_tax_list As $key => $info):?>

                        <div class="crbc-cart-tax-<?php echo $key;?>-block">

                            <div class="crbc-cart-final-left"><?php echo $info['name']?> (<?php echo CrBcCart::formatNumber($info['rate'])?> %)</div>

                            <div class="crbc-cart-final-middle"></div>

                            <div id="crbc-cart-tax-<?php echo $key;?>" class="crbc-cart-final-right"><?php echo $this->cart_instance->formatPrice($info['tax'])?></div>

                            <div class="crbc-clearfix clearfix"></div>

                        </div>

                    <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Subtotal Gross -->
                    <?php if($this->config->get('cart_display_subtotal_gross_column', 1) == 1): ?>

                        <div class="crbc-cart-price-gross-block">

                            <div class="crbc-cart-final-left"><?php echo JText::_('COM_BREEZINGCOMMERCE_SUBTOTAL')?></div>

                            <div class="crbc-cart-final-middle"></div>

                            <div id="crbc-cart-price-gross" class="crbc-cart-final-right"><?php echo $this->cart_instance->formatPrice($this->order_info->price_gross)?></div>

                            <div class="crbc-clearfix clearfix"></div>

                        </div>

                    <?php endif;?>

                    <!-- Shipping -->
                    <div class="crbc-cart-shipping-block">

                        <div class="crbc-cart-final-left"><?php echo JText::_('COM_BREEZINGCOMMERCE_SHIPPING')?></div>

                        <div class="crbc-cart-final-middle"></div>

                        <div id="crbc-cart-price-shipping" class="crbc-cart-final-right"><?php echo $this->order_info->selected_shipping_id != 0 || CrBcCart::isVirtualOrder($this->cart_items) ? $this->cart_instance->formatPrice($this->order_info->shipping_costs) : JText::_('COM_BREEZINGCOMMERCE_SHIPPING_NOT_SELECTED_YET');?></div>

                        <div class="crbc-clearfix clearfix"></div>

                    </div>

                    <!-- Grand Total -->

                    <div class="crbc-cart-grand-total-block">

                        <div class="crbc-cart-final-left"><strong><?php echo JText::_('COM_BREEZINGCOMMERCE_GRAND_TOTAL')?></strong></div>

                        <div class="crbc-cart-final-middle"></div>

                        <strong id="crbc-cart-grand-total" class="crbc-cart-final-right"><?php echo $this->cart_instance->formatPrice($this->order_info->grand_total)?></strong>

                        <div class="crbc-clearfix clearfix"></div>

                    </div>

                    
                    <!-- Taxes if position below -->
                    <?php if(count($this->order_info->price_tax_list) && $this->config->get('cart_display_tax_position', 'below_calculation') == 'below_calculation'): ?>
                    <div class="crbc-muted crbc-small muted small">

                        <h5><?php echo JText::_('COM_BREEZINGCOMMERCE_INCLUDED_TAXES'); ?></h5>

                        <div class="crbc-clearfix clearfix"></div>
                        
                        <?php foreach($this->order_info->price_tax_list As $key => $info):?>

                            <div id="crbc-cart-tax-<?php echo $key;?>-block" class="crbc-cart-tax-block">

                                <div class="crbc-cart-final-left"><?php echo $info['name']?> (<?php echo CrBcCart::formatNumber($info['rate'])?>%)</div>

                                <div class="crbc-cart-final-right" id="crbc-cart-tax-<?php echo $key;?>"><?php echo $this->cart_instance->formatPrice($info['tax'])?></div>

                                <div class="crbc-clearfix clearfix"></div>

                            </div>

                        <?php endforeach; ?>
                        
                        <!-- Subtotal -->
                        <?php if($this->config->get('cart_display_subtotal_net_column', 1) == 1): ?>

                        <div class="crbc-cart-price-net-block">

                            <div class="crbc-cart-final-left"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_SUBTOTAL_NET' ); ?></div>

                            <div class="crbc-cart-final-middle"></div>

                            <div id="crbc-cart-price-net" class="crbc-cart-final-right"><?php echo $this->cart_instance->formatPrice($this->order_info->price_net)?></div>

                            <div class="crbc-clearfix clearfix"></div>

                        </div>

                        <?php endif; ?>
                        
                    </div>
                    <?php endif; ?>
        </div>
        
        <div class="crbc-clearfix clearfix"></div>
        
        <div class="crbc-cart-controls form-controls">
            <a class="crbc-cart-continue crbc-btn btn btn-link btn-large pull-left" href="index.php"><i class="crbc-fa crbc-fa-arrow-left"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_CONTINUE_SHOPPING'); ?></a>
            <a class="crbc-cart-checkout crbc-btn btn btn-primary btn-large pull-right" href="<?php echo JRoute::_('index.php?option=com_breezingcommerce&controller=checkout&Itemid='.JRequest::getInt('Itemid',0)); ?>"><i class="crbc-fa crbc-fa-shopping-cart"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT'); ?></a>
        </div>
        
        <?php
        endif;
        ?>
        
    </div>
    
    <div style="crbc-clearfix clearfix"></div>
    
</div>