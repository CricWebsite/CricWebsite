<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="<?php echo $this->view_type == 'list' ? 'crbc-view-type-list ' : 'crbc-view-type-block ';?>crbc-complex crbc crbc-page crbc-category-page<?php echo $this->pageclass_sfx != '' ? ' ' . $this->pageclass_sfx : ''; ?>">

        <?php if($this->show_page_heading || JFactory::getSession()->get('com_breezingcommerce.filter', null) !== null):?>
        <div class="crbc-category-title page-header">
            <h1><?php echo $this->escape( $this->page_heading != '' || JFactory::getSession()->get('com_breezingcommerce.filter', null) !== null ? $this->page_heading : $this->category->title );?> (<?php echo $this->total;?>)</h1>
        </div>
        <?php endif; ?>

        <?php if( $this->enable_category_description && JFactory::getSession()->get('com_breezingcommerce.filter', null) === null ): ?>
    
            <?php $has_image = 12; if($this->image_tag != '' || $this->category->introtext != ''): ?>
            <div class="crbc-category-introtext crbc-row row-fluid">

                <?php if($this->image_tag != ''): ?>
                <div class="crbc-span4 span4">
                    <div class="crbc-category-image crbc-thumbnail crbc-span4 thumbnail span4">
                       <?php $has_image = 8; echo $this->image_tag; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($this->category->introtext != ''): ?>
                <div class="crbc-span<?php echo $has_image; ?> span<?php echo $has_image; ?>">
                    <div class="crbc-category-introtext">
                        <?php echo $this->category->introtext; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            <?php endif; ?>

            <div class="crbc-clearfix clear clearfix"></div>

            <?php if($this->category->fulltext != ''): ?>
            <div class="crbc-category-fulltext crbc-row row-fluid">
                <div class="crbc-span12 span12">
                    <div class="crbc-category-fulltext">
                        <?php echo $this->category->fulltext; ?>
                    </div>
                </div>
            </div>

            <div class="crbc-clearfix clear clearfix"></div>

            <?php endif; ?>

        <?php endif; ?>
        
        <?php 
        if( $this->pagination->pagesTotal > 1 ){
        ?>
        <div class="crbc-pull-left pull-left">
            <span class="label"><?php echo $this->pagination->getPagesCounter(); ?></span>
        </div>
        <?php
        }
        
        $size = count($this->products);
        if($size > 0){
        ?>
        
        <form class="crbc-form-horizontal form-horizontal" action="<?php echo JUri::getInstance()->toString(); ?>" method="post">
            
            <div class="crbc-pull-right pull-right">
                <div class="crbc-control-group control-group">
                    <div class="crbc-controls controls">
                        <button id="crbc-view-type-block" class="crbc-btn btn btn-secondary<?php echo $this->view_type == 'block' ? ' crbc-disabled disabled' : ''?>"><i class="crbc-fa crbc-fa-th"></i></button>
                    </div>
                </div>
                
            </div>
            
            <div class="crbc-pull-right pull-right">
                <div class="crbc-control-group control-group">
                    <div class="crbc-controls controls">
                        <button id="crbc-view-type-list" class="crbc-btn btn btn-secondary<?php echo $this->view_type == 'list' ? ' crbc-disabled disabled' : ''?>"><i class="crbc-fa crbc-fa-list"></i></button>
                    </div>
                </div>
            </div>
            
            <div class="crbc-limit-container crbc-pull-right pull-right">
                
                <div class="crbc-limit crbc-control-group control-group">
                    <label class="crbc-control-label control-label" for="limit"><?php echo JText::_('COM_BREEZINGCOMMERCE_SHOW_PER_PAGE'); ?></label>
                    <div class="crbc-controls controls">
                      <?php echo $this->pagination->getLimitBox(); ?>
                    </div>
                </div>
            </div>
            
            <div class="crbc-sortby-container crbc-pull-right pull-right">
                <div class="crbc-sortby crbc-control-group control-group">
                    <label class="crbc-control-label control-label" for="crbc-product-order"><?php echo JText::_('COM_BREEZINGCOMMERCE_SORT_BY'); ?></label>
                    <div class="crbc-controls controls">
                        <select name="product_order" id="crbc-product-order" data-chosen="bla">
                            <option value=""<?php echo $this->product_order == '' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_BREEZINGCOMMERCE_ORDER_DEFAULT'); ?></option>
                            <option value="name_a_z"<?php echo $this->product_order == 'name_a_z' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_BREEZINGCOMMERCE_ORDER_NAME_A_Z'); ?></option>
                            <option value="name_z_a"<?php echo $this->product_order == 'name_z_a' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_BREEZINGCOMMERCE_ORDER_NAME_Z_A'); ?></option>
                            <option value="price_high_to_low"<?php echo $this->product_order == 'price_high_to_low' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_BREEZINGCOMMERCE_ORDER_PRICE_HIGH_TO_LOW'); ?></option>
                            <option value="price_low_to_high"<?php echo $this->product_order == 'price_low_to_high' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_BREEZINGCOMMERCE_ORDER_PRICE_LOW_TO_HIGH'); ?></option>
                        </select>
                    </div>
                </div>
                
            </div>
            
        </form>
            
        <?php
        }
        ?>
        
        <div class="crbc-clearfix clear clearfix"></div>
    
        <?php
        $size = count($this->products);
        $i = 0;
        foreach($this->products As $product){
            $cols = intval($this->col_break);
            if($i == 0){
                echo '<div class="crbc-category-products crbc-row row-fluid">';
            }
        ?>

            <div class="crbc-category-product-entry crbc-span<?php echo 12 / $cols; ?> crbc-well span<?php echo 12 / $cols; ?> well well-small">

                <form class="crbc-order-form" id="crbc-order-form-<?php echo $product->id; ?>" onsubmit="return false;" action="<?php echo JRoute::_( 'index.php' ); ?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">

                    <div class="crbc-product-title">
                        <?php if(!$this->link_title): ?>
                        <h2><?php echo $this->escape( $product->title );?></h2>
                        <?php else: ?>
                        <h2><a title="<?php echo $this->escape(JText::_('COM_BREEZINGCOMMERCE_DETAILS')); ?>" href="<?php echo $product->url; ?>"><?php echo $this->escape( $product->title );?></a></h2>
                        <?php endif;?>
                    </div>

                    <?php
                    if( !$product->hide_price ){
                    ?>
                    <div class="crbc-price-container">
                    
                        <?php 
                        if( count( $product->display_plugins['before_price'] ) ){
                        ?>
                        <div class="crbc-info-before-price" id="crbc-info-before-price-<?php echo $product->id; ?>">
                        <?php
                            $x = 0;
                            foreach( $product->display_plugins['before_price'] As $plugin_info ){
                        ?>
                            <div class="crbc-info-item" id="crbc-info-item-before-price-<?php echo $product->id; ?>-<?php echo $x; ?>"><?php echo $plugin_info; ?></div>
                        <?php
                                $x++;
                            }
                        ?>
                        </div>
                        <?php
                        }
                        ?>

                        <?php if($product->sale_price_group != null) : ?>
                        <div class="crbc-product-sale-price-container">
                            <s id="crbc-product-sale-price-<?php echo $product->id; ?>" class="crbc-product-price alert alert-error">
                                <div class="crbc-product-price-label"><?php echo $product->sale_price_group[0];?></div>
                                <?php if(count($product->sale_price_group) == 4): ?>
                                <hr />
                                <div class="crbc-sub-price">
                                    <sub><?php echo $product->sale_price_group[1];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub><?php echo $product->sale_price_group[2];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub><?php echo $product->sale_price_group[3];?></sub>
                                </div>
                                <?php endif; ?>
                            </s>
                        </div>
                        <?php endif; ?>

                        <div class="crbc-product-price-container">
                            <div id="crbc-product-price-<?php echo $product->id; ?>" class="crbc-product-price alert alert-success">
                                <div class="crbc-product-price-label" id="crbc-product-price-group-<?php echo $product->id; ?>"><?php echo $product->price_group[0];?></div>
                                <?php if(count($product->price_group) == 4): ?>
                                <hr />
                                <div class="crbc-sub-price">
                                    <sub id="crbc-product-price-<?php echo $product->id; ?>-1"><?php echo $product->price_group[1];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub id="crbc-product-price-<?php echo $product->id; ?>-2"><?php echo $product->price_group[2];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub id="crbc-product-price-<?php echo $product->id; ?>-3"><?php echo $product->price_group[3];?></sub>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php 
                        if( count( $product->display_plugins['after_price'] ) ){
                        ?>
                        <div class="crbc-info-after-price" id="crbc-info-after-price-<?php echo $product->id; ?>">
                        <?php
                            $x = 0;
                            foreach( $product->display_plugins['after_price'] As $plugin_info ){
                        ?>
                            <div class="crbc-info-item" id="crbc-info-item-after-price-<?php echo $product->id; ?>-<?php echo $x; ?>"><?php echo $plugin_info; ?></div>
                        <?php
                                $x++;
                            }
                        ?>
                        </div>
                        <?php
                        }
                        ?>

                        <?php if($product->sale_price_info_group != null) : ?>
                        <div class="crbc-product-sale-price-info-container">
                            <s id="crbc-product-sale-price-info-<?php echo $product->id; ?>" class="crbc-product-price alert alert-error">
                                <div class="crbc-product-price-label"><?php echo $product->sale_price_info_group[0];?></div>
                                <?php if(count($product->sale_price_info_group) == 4): ?>
                                <hr />
                                <div class="crbc-sub-price">
                                    <sub><?php echo $product->sale_price_info_group[1];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub><?php echo $product->sale_price_info_group[2];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub><?php echo $product->sale_price_info_group[3];?></sub>
                                </div>
                                <?php endif; ?>
                            </s>
                        </div>
                        <?php endif; ?>

                        <?php if($product->price_info_group != null) : ?>
                        <div class="crbc-product-price-info-container">
                            <div id="crbc-product-price-info-<?php echo $product->id; ?>" class="crbc-product-price alert alert-success">
                                <div class="crbc-product-price-label"><?php echo $product->price_info_group[0];?></div>
                                <?php if(count($product->price_info_group) == 4): ?>
                                <hr />
                                <div class="crbc-sub-price">
                                    <sub><?php echo $product->price_info_group[1];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub><?php echo $product->price_info_group[2];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub><?php echo $product->price_info_group[3];?></sub>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <?php
                    }
                    ?>

                    <?php if($this->view_type != 'list' && ( $this->cart_add_button_location == 'top' || $this->cart_add_button_location == 'top_bottom') ): ?>
                    <?php if($product->force_details): ?>
                    <div class="crbc-add-to-cart-button">
                        <a title="<?php echo $this->escape(JText::_('COM_BREEZINGCOMMERCE_BUY')); ?>" href="<?php echo $product->url; ?>" class="crbc-btn btn btn-primary btn-large"><i class="crbc-fa crbc-fa-shopping-cart"></i> <?php echo $this->escape(JText::_('COM_BREEZINGCOMMERCE_BUY')); ?></a>
                    </div>
                    <?php else:?>
                    <div class="crbc-add-to-cart-button">
                        <button type="button" class="crbc-btn btn btn-primary btn-large"><i class="crbc-fa crbc-fa-shopping-cart"></i> <?php echo $product->immediate_checkout ? JText::_('COM_BREEZINGCOMMERCE_BUY') : JText::_('COM_BREEZINGCOMMERCE_CARTADD'); ?></button>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if(trim($product->product_image) != '' && $this->display_image): ?>
                    <div class="crbc-product-image-container crbc-polaroid img-polaroid">
                        <?php echo trim($product->product_image); ?>
                    </div>
                    <?php endif; ?>

                    <?php if($this->display_text):?>
                    <div class="crbc-product-description">
                        <!-- product description -->
                        <?php echo $product->introtext;?>
                    </div>
                    <?php endif; ?>

                    <?php $property_selectors = CrBcProperties::getPropertySelectors($product->id, 1, array(), $product->use_combinations); ?>
                    <?php if($product->has_properties && trim($property_selectors) != '') : ?>
                        <?php echo $property_selectors; ?>
                    <?php endif; ?>

                    <?php $attribute_selectors = CrBcAttributes::getAttributesForm($product->id, 1, array());?>
                    <?php if($product->has_attributes && trim($attribute_selectors) != '') : ?>
                        <?php echo $attribute_selectors; ?>
                    <?php endif; ?>

                    <?php if( $product->maximum_amount > $product->minimum_amount ): ?>
                    <div class="crbc-quantity crbc-well controls form-horizontal well">
                        <label for="crbc-quantity-<?php echo $product->id?>"><?php echo JText::_('COM_BREEZINGCOMMERCE_QTY'); ?></label>
                        <div class="crbc-cart-item-amount input-append">
                            <input class="crbc-quantity-input" id="crbc-quantity-<?php echo $product->id?>" onchange="crbcUpdatePrices()" name="amount" value="1" type="text"/>
                            <button class="crbc-btn btn" type="button" onclick="crbc_products_assoc['<?php echo $product->id; ?>'].update_qty(+1, <?php echo $product->minimum_amount; ?>, <?php echo $product->maximum_amount; ?>)"><i class="crbc-fa crbc-fa-chevron-up"></i></button>
                            <button class="crbc-btn btn" type="button" onclick="crbc_products_assoc['<?php echo $product->id; ?>'].update_qty(-1, <?php echo $product->minimum_amount; ?>, <?php echo $product->maximum_amount; ?>)"><i class="crbc-fa crbc-fa-chevron-down"></i></button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if( ( $product->has_properties && trim($property_selectors) != '' ) || ( $product->has_attributes != '' && trim($attribute_selectors) != '' ) || $product->maximum_amount > $product->minimum_amount): ?>
                    <div id="crbc-total-<?php echo $product->id; ?>" class="crbc-total crbc-well well">
                    <?php
                    $total_text = '';
                    if($product->has_properties && trim($property_selectors) != ''){
                        $total_text .= ', '. JText::_('COM_BREEZINGCOMMERCE_PRODUCT_OPTIONS');
                    }
                    if($product->has_attributes && trim($attribute_selectors) != ''){
                        $total_text .= ', '. JText::_('COM_BREEZINGCOMMERCE_PRODUCT_ACCESSORIES');
                    }
                    echo '<strong>'.JText::_('COM_BREEZINGCOMMERCE_PRODUCT_TOTAL_QUANTITY').$total_text.':</strong>';
                    ?>
                        <div id="crbc-total-price-<?php echo $product->id; ?>" class="crbc-total-price label label-info">
                            <div class="crbc-total-price-spinner crbc-fa crbc-fa-refresh crbc-fa-spin" id="crbc-total-price-spinner-<?php echo $product->id; ?>"></div>
                            <div class="crbc-product-price-label" id="crbc-total-price-<?php echo $product->id; ?>-0"></div>
                            <div class="crbc-total-price-group-category" id="crbc-total-price-group-<?php echo $product->id; ?>">
                                <hr />
                                <div class="crbc-sub-price">
                                    <sub id="crbc-total-price-<?php echo $product->id; ?>-1"></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub id="crbc-total-price-<?php echo $product->id; ?>-2"></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub id="crbc-total-price-<?php echo $product->id; ?>-3"></sub>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div style="clear:both"></div>
                    
                    <?php if($this->view_type == 'list' || ( $this->cart_add_button_location == 'bottom' || $this->cart_add_button_location == 'top_bottom') ): ?>
                    <?php if($product->force_details): ?>
                    <div class="crbc-add-to-cart-button">
                        <a title="<?php echo $this->escape(JText::_('COM_BREEZINGCOMMERCE_BUY')); ?>" href="<?php echo $product->url; ?>" class="crbc-btn btn btn-primary btn-large"><i class="crbc-fa crbc-fa-shopping-cart"></i> <?php echo $this->escape(JText::_('COM_BREEZINGCOMMERCE_BUY')); ?></a>
                    </div>
                    <?php else:?>
                    <div class="crbc-add-to-cart-button">
                        <button type="button" class="crbc-btn btn btn-primary btn-large"><i class="crbc-fa crbc-fa-shopping-cart"></i> <?php echo $product->immediate_checkout ? JText::_('COM_BREEZINGCOMMERCE_BUY') : JText::_('COM_BREEZINGCOMMERCE_CARTADD'); ?></button>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if(!$this->hide_details): ?>
                    <div class="crbc-details-button">
                        <a href="<?php echo $product->url; ?>" class="crbc-btn btn btn-primary btn-large"><i class="crbc-fa crbc-fa-search"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_DETAILS'); ?></a>
                    </div>
                    <?php endif; ?>

                    <!-- _NEVER_ remove these hidden inputs in template overrides -->
                    <input type="hidden" name="option" value="com_breezingcommerce"/>
                    <input type="hidden" name="controller" value="cart"/>
                    <input type="hidden" name="task" value="add"/>
                    <input type="hidden" name="product_id" value="<?php echo $this->escape($product->id); ?>"/>
                    <input type="hidden" name="return_url" value="<?php echo $this->escape($this->return_url); ?>"/>
                    <input type="hidden" name="immediate_checkout" value="<?php echo intval($product->immediate_checkout); ?>"/>
                    <input type="hidden" name="immediate_checkout_singleton" value="<?php echo intval($product->immediate_checkout_singleton); ?>"/>
                    <input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid', 0); ?>"/>
                    </form>
                </div>

            <?php
            if($i + 1 < $size && $i % intval($this->col_break) == intval($this->col_break) - 1){
                echo '</div>';
                echo '<div class="crbc-clearfix clearfix"></div>';
                echo '<div class="crbc-category-products crbc-row row-fluid">';
            } else if( $i + 1 >= $size ) {
            ?>
            </div>
            <?php
            }
            $i++;
        }
        
        if( $this->pagination->pagesTotal > 1 ){
        ?>
        <div class="crbc-text-center text-center">
            <form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="adminForm" id="adminForm">

                <?php echo $this->pagination->getListFooter(); ?>
            </form>
        </div>
        <?php
        }
        ?>

        <script type="text/javascript">
        <!--
        jQuery(document).ready(function(){
            
            jQuery('#crbc-view-type-list').on('click', function(){
                jQuery(this).closest('form').append('<input type="hidden" name="view_type" value="list"/>');
                jQuery(this).closest('form').submit();
            });
            
            jQuery('#crbc-view-type-block').on('click', function(){
                jQuery(this).closest('form').append('<input type="hidden" name="view_type" value="block"/>');
                jQuery(this).closest('form').submit();
            });
            
            jQuery('#crbc-product-order').on('change', function(){
                jQuery(this).closest('form').submit();
            });
            
            // fixes paging issues not having the parameters in their links as they should when being on home page
            jQuery('#adminForm li a').each(function(){
                
                //console.log('form: '+crbc_urlParam('filter', jQuery(this).closest('form').attr('action')));
                //console.log('url: '+crbc_urlParam('filter', jQuery(this).attr('href')));
                
                var form_filter = crbc_urlParam('filter', jQuery(this).closest('form').attr('action'));
                var link_filter = crbc_urlParam('filter', jQuery(this).attr('href'));
                
                if(typeof form_filter != undefined && form_filter == 1 && ( typeof link_filter == undefined || link_filter != 1 ) ){
                    
                    jQuery(this).attr('href', jQuery(this).attr('href') + '&filter=1&controller=category')
                }
            });
        });
        <?php echo $this->products_to_add; ?>
        //-->
        </script>

        <div class="crbc-centered-spinner crbc-fa crbc-fa-refresh crbc-fa-spin crbc-fa-5x"></div>
</div>
