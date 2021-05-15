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
<div class="crbc crbc-page crbc-product-page<?php echo $this->pageclass_sfx != '' ? ' ' . $this->pageclass_sfx : ''; ?>">

    <form class="crbc-order-form" id="crbc-order-form-<?php echo $this->product->id; ?>"  onsubmit="return false;" action="<?php echo JRoute::_( 'index.php' ); ?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">
            
        <?php
        if(isset($this->return_url_list) && $this->return_url_list != ''){
        ?>
        <a class="crbc-cart-back-button btn btn-primary pull-right" href="<?php echo $this->return_url_list; ?>"><i class="crbc-fa crbc-fa-chevron-left"></i> Back</a>
        <?php
        }
        ?>

        <div class="crbc-product-title page-header">
            <h1><?php echo $this->escape( $this->page_heading != '' ? $this->page_heading : $this->product->title );?></h1>
        </div>

        <div class="crbc-product crbc-row row-fluid">

            <div class="crbc-product-info crbc-span7 span7">

                <?php
                if(trim($this->product_images) != ''){
                ?>
                <div class="crbc-product-images">
                    <?php echo trim($this->product_images); ?>
                </div>
                <?php
                }
                else if( trim($this->default_product_image) != '' )
                {
                ?>
                <div class="crbc-product-images crbc-product-image-noplugin">
                    <?php echo trim($this->default_product_image); ?>
                </div>
                <?php
                }
                ?>

                <div class="crbc-product-body">

                    <?php 
                    if( count( $this->display_plugins['before_description'] ) ){
                    ?>
                    <div class="crbc-info-before-description" id="crbc-info-before-description-<?php echo $this->product->id; ?>">
                    <?php
                        $x = 0;
                        foreach( $this->display_plugins['before_description'] As $plugin_info ){
                    ?>
                        <div class="crbc-info-item" id="crbc-info-item-before-description-<?php echo $this->product->id; ?>-<?php echo $x; ?>"><?php echo $plugin_info; ?></div>
                    <?php
                            $x++;
                        }
                    ?>
                    </div>
                    <?php
                    }
                    ?>

                    <div class="crbc-product-description">
                        <!-- product description -->
                        <?php echo $this->product->description;?>
                    </div>

                    <?php 
                    if( count( $this->display_plugins['after_description'] ) ){
                    ?>
                    <div class="crbc-info-after-description" id="crbc-info-after-description-<?php echo $this->product->id; ?>">
                    <?php
                        $x = 0;
                        foreach( $this->display_plugins['after_description'] As $plugin_info ){
                    ?>
                        <div class="crbc-info-item" id="crbc-info-item-after-description-<?php echo $this->product->id; ?>-<?php echo $x; ?>"><?php echo $plugin_info; ?></div>
                    <?php
                            $x++;
                        }
                    ?>
                    </div>
                    <?php
                    }
                    ?>
                </div>

            </div>

            <div class="crbc-category-product-entry crbc-product-controls crbc-span5 span5">


                    <?php 
                    if( count( $this->display_plugins['before_price'] ) ){
                    ?>
                    <div class="crbc-info-before-price" id="crbc-info-before-price-<?php echo $this->product->id; ?>">
                    <?php
                        $x = 0;
                        foreach( $this->display_plugins['before_price'] As $plugin_info ){
                    ?>
                        <div class="crbc-info-item" id="crbc-info-item-before-price-<?php echo $this->product->id; ?>-<?php echo $x; ?>"><?php echo $plugin_info; ?></div>
                    <?php
                            $x++;
                        }
                    ?>
                    </div>
                    <?php
                    }
                    ?>

                    <?php
                    if( !$this->product->hide_price ){
                    ?>
                    <?php if($this->sale_price_group !== null) : ?>
                    <div id="crbc-product-sale-price-container">
                        <s id="crbc-product-sale-price" class="alert alert-error">
                            <?php echo $this->sale_price_group[0];?>
                            <?php if(count($this->sale_price_group) >= 4): ?>
                            <hr />
                            <div class="crbc-sub-price">
                                <sub><?php echo $this->sale_price_group[1];?></sub>
                                <div class="crbc-sub-price-br"></div>
                                <sub><?php echo $this->sale_price_group[2];?></sub>
                                <div class="crbc-sub-price-br"></div>
                                <sub><?php echo $this->sale_price_group[3];?></sub>
                            </div>
                            <?php endif; ?>
                        </s>
                    </div>
                    <?php endif; ?>

                    <div id="crbc-product-price-container">
                        <div id="crbc-product-price" class="alert alert-success">
                            <div id="crbc-product-price-group-<?php echo $this->product->id; ?>"><?php echo $this->price_group[0];?></div>
                            <?php if(count($this->price_group) >= 4): ?>
                            <hr />
                            <div class="crbc-sub-price">
                                <sub id="crbc-product-price-<?php echo $this->product->id; ?>-1"><?php echo $this->price_group[1];?></sub>
                                <div class="crbc-sub-price-br"></div>
                                <sub id="crbc-product-price-<?php echo $this->product->id; ?>-2"><?php echo $this->price_group[2];?></sub>
                                <div class="crbc-sub-price-br"></div>
                                <sub id="crbc-product-price-<?php echo $this->product->id; ?>-3"><?php echo $this->price_group[3];?></sub>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                
                    <?php
                    }
                    ?>

                    <?php 
                    if( count( $this->display_plugins['after_price'] ) ){
                    ?>
                    <div class="crbc-info-after-price" id="crbc-info-after-price-<?php echo $this->product->id; ?>">
                    <?php
                        $x = 0;
                        foreach( $this->display_plugins['after_price'] As $plugin_info ){
                    ?>
                        <div class="crbc-info-item" id="crbc-info-item-after-price-<?php echo $this->product->id; ?>-<?php echo $x; ?>"><?php echo $plugin_info; ?></div>
                    <?php
                            $x++;
                        }
                    ?>
                    </div>
                    <?php
                    }
                    ?>

                    <?php if($this->sale_price_info_group != null) : ?>
                    <div id="crbc-product-sale-price-info-container">
                        <s id="crbc-product-sale-price-info" class="error alert-error">
                            <?php echo $this->sale_price_info_group[0];?>
                            <?php if(count($this->sale_price_info_group) == 4): ?>
                            <hr />
                            <div class="crbc-sub-price">
                                <sub><?php echo $this->sale_price_info_group[1];?></sub>
                                <div class="crbc-sub-price-br"></div>
                                <sub><?php echo $this->sale_price_info_group[2];?></sub>
                                <div class="crbc-sub-price-br"></div>
                                <sub><?php echo $this->sale_price_info_group[3];?></sub>
                            </div>
                            <?php endif; ?>
                        </s>
                    </div>
                    <?php endif; ?>

                    <?php if($this->price_info_group !== null): ?>
                    <div id="crbc-product-price-info-container">
                        <div id="crbc-product-price-info" class="info alert-info">
                            <em>
                                <?php echo $this->price_info_group[0];?>
                                <?php if(count($this->price_info_group) == 4): ?>
                                <hr />
                                <div class="crbc-sub-price">
                                    <sub><?php echo $this->price_info_group[1];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub><?php echo $this->price_info_group[2];?></sub>
                                    <div class="crbc-sub-price-br"></div>
                                    <sub><?php echo $this->price_info_group[3];?></sub>
                                </div>
                                <?php endif; ?>
                            </em>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($this->product->has_properties && trim($this->property_selectors) != '') : ?>
                        <?php echo $this->property_selectors; ?>
                    <?php endif; ?>

                    <?php if($this->product->has_attributes && trim($this->attribute_selectors) != '') : ?>
                        <?php echo $this->attribute_selectors; ?>
                    <?php endif; ?>

                    <?php if( $this->product->maximum_amount > $this->product->minimum_amount ): ?>
                    <div class="crbc-quantity crbc-well controls form-horizontal well">
                        <label for="crbc-quantity-<?php echo $this->product->id; ?>"><?php echo JText::_('COM_BREEZINGCOMMERCE_QTY'); ?></label>
                        <div class="crbc-cart-item-amount input-append">
                            <input class="crbc-quantity-input" id="crbc-quantity-<?php echo $this->product->id; ?>" onchange="crbcUpdatePrice()" name="amount" value="1" type="text"/>
                            <button class="crbc-btn btn" type="button" onclick="crbc_product.update_qty(+1, <?php echo $this->product->minimum_amount; ?>, <?php echo $this->product->maximum_amount; ?>)"><i class="crbc-fa crbc-fa-chevron-up"></i></button>
                            <button class="crbc-btn btn" type="button" onclick="crbc_product.update_qty(-1, <?php echo $this->product->minimum_amount; ?>, <?php echo $this->product->maximum_amount; ?>)"><i class="crbc-fa crbc-fa-chevron-down"></i></button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if( ( trim($this->property_selectors) != '' && $this->product->has_properties ) || ( trim($this->attribute_selectors) != '' && $this->product->has_attributes ) || $this->product->maximum_amount > $this->product->minimum_amount): ?>
                    <div id="crbc-total" class="crbc-well well">
                        <?php
                        $total_text = '';
                        if($this->product->has_properties && trim($this->property_selectors) != ''){
                            $total_text .= ', '. JText::_('COM_BREEZINGCOMMERCE_PRODUCT_OPTIONS');
                        }
                        if($this->product->has_attributes && trim($this->attribute_selectors) != ''){
                            $total_text .= ', '. JText::_('COM_BREEZINGCOMMERCE_PRODUCT_ACCESSORIES');
                        }
                        echo '<strong>'.JText::_('COM_BREEZINGCOMMERCE_PRODUCT_TOTAL_QUANTITY').$total_text.':</strong>';
                        ?>
                        <div id="crbc-total-price" class="label label-info">
                            <div class="crbc-total-price-spinner crbc-fa crbc-fa-refresh crbc-fa-spin" id="crbc-total-price-spinner"></div>
                            <div id="crbc-total-price-0"></div>
                            <div class="crbc-total-price-group">
                            <hr />
                            <div class="crbc-sub-price">
                                <sub id="crbc-total-price-1"></sub>
                                <div class="crbc-sub-price-br"></div>
                                <sub id="crbc-total-price-2"></sub>
                                <div class="crbc-sub-price-br"></div>
                                <sub id="crbc-total-price-3"></sub>
                            </div>
                            </div>

                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="crbc-add-to-cart-button">
                        <button type="button" class="crbc-btn btn btn-primary btn-large"><i class="crbc-fa crbc-fa-shopping-cart"></i> <?php echo $this->product->immediate_checkout ? JText::_('COM_BREEZINGCOMMERCE_BUY') : JText::_('COM_BREEZINGCOMMERCE_CARTADD'); ?></button>
                    </div>

                    <!-- _NEVER_ remove these hidden inputs in template overrides -->
                    <input type="hidden" name="option" value="com_breezingcommerce"/>
                    <input type="hidden" name="controller" value="cart"/>
                    <input type="hidden" name="task" value="add"/>
                    <input type="hidden" name="immediate_checkout" value="<?php echo intval($this->product->immediate_checkout); ?>"/>
                    <input type="hidden" name="immediate_checkout_singleton" value="<?php echo intval($this->product->immediate_checkout_singleton); ?>"/>
                    <input type="hidden" name="product_id" value="<?php echo $this->escape($this->product->id); ?>"/>
                    <input type="hidden" name="return_url" value="<?php echo $this->escape($this->return_url); ?>"/>
                    <input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid', 0); ?>"/>


            </div>

        </div>

        <div class="crbc-product-details-footer crbc-row row-fluid">
        <?php 
        if( count( $this->display_plugins['inside_footer'] ) ){
        ?>
        <div class="crbc-info-inside-footer" id="crbc-info-inside-footer-<?php echo $this->product->id; ?>">
        <?php
            $x = 0;
            foreach( $this->display_plugins['inside_footer'] As $plugin_info ){
        ?>
            <div class="crbc-info-item" id="crbc-info-item-inside-footer-<?php echo $this->product->id; ?>-<?php echo $x; ?>"><?php echo $plugin_info; ?></div>
        <?php
                $x++;
            }
        ?>
        </div>
        <?php
        }
        ?>
        </div>
    
    </form>
    
</div>
        
