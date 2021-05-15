<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

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
<div class="crbc crbc-page crbc-checkout-single-page crbc-checkout-page<?php echo $this->pageclass_sfx != '' ? ' ' . $this->pageclass_sfx : ''; ?>">

    <div class="crbc-checkout-title page-header">
        <h1><?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT');?></h1>
    </div>
    
    <?php 
    if(count($this->items) == 1){
    ?>
    <div id="crbc-single-sale-info" class="crbc-poster hero-unit"><span><?php echo $this->escape($this->items[0]->title); ?>: <?php echo $this->cart_instance->formatPrice($this->order_info->grand_total)?></span></div>
    <?php
    }
    ?>
    
    <ul class="crbc-nav nav nav-pills nav-stacked">

        <?php if( JFactory::getUser()->get('id', 0) == 0 ):?>
        
        <li class="crbc-checkout-method-guest-register-login">

            <a onclick="crbc_checkout.step('.crbc-checkout-method-guest-register-login')" class="crbc-checkout-prev" href="javascript:void(0);">
                <?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_METHOD'); ?>
            </a>
            
            <div class="crbc-checkout-method-guest-register-row crbc-row row-fluid">
            
                <div class="crbc-checkout-method-guest-register-controls crbc-well span6 well">

                    <?php if($this->config->get('allow_guest_checkout')):?>
                    
                    <fieldset>
                        <legend><?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_AS_GUEST_OR_REGISTER'); ?></legend>
                    </fieldset>
                    
                    <label for="crbc-guest-or-register"><?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_REGISTER_INFO'); ?></label>

                    <form method="post" action="<?php echo JRoute::_('index.php?option=com_breezingcommerce&controller=checkout&Itemid='.JRequest::getInt('Itemid',0)); ?>">
                        
                        <div class="crbc-checkout-method-select-guest controls form-inline">
                            <input type="radio" name="guest_or_register" id="crbc-guest-or-register" value="guest"<?php echo $this->register ? '' : ' checked="checked"'?>/> <label for="crbc-guest-or-register"><?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_AS_GUEST'); ?></label>
                        </div>
                        
                        <div class="crbc-checkout-method-select-register controls form-inline">
                            <input type="radio" name="guest_or_register" id="crbc-guest-or-register-2" value="register"<?php echo $this->register ? ' checked="checked"' : ''?>/> <label for="crbc-guest-or-register-2"><?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_REGISTER'); ?></label>
                        </div>
                        
                    </form>
                    
                    <p class="crbc-checkout-method-guest-or-register-text">
                        <?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_REGISTER_INFO2');?>
                    </p>
                    
                    <?php else: ?>
                    
                    <fieldset>
                        <legend><?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT'); ?></legend>
                    </fieldset>
                    
                    <?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_WELCOME_MESSAGE'); ?>

                    <form method="post" action="<?php echo JRoute::_('index.php?option=com_breezingcommerce&controller=checkout&Itemid='.JRequest::getInt('Itemid',0)); ?>">
                        
                        <div style="display:none;" class="crbc-checkout-method-select-guest controls form-inline">
                            <input type="radio" name="guest_or_register" id="crbc-guest-or-register" value="guest"/> <label for="crbc-guest-or-register"><?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_AS_GUEST'); ?></label>
                        </div>
                        
                        <div style="display:none;" class="crbc-checkout-method-select-register controls form-inline">
                            <input type="radio" name="guest_or_register" id="crbc-guest-or-register-2" value="register" checked="checked"/> <label for="crbc-guest-or-register-2"><?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_REGISTER'); ?></label>
                        </div>
                        
                    </form>
                    
                    <?php endif; ?>
                    
                    <div class="crbc-row row-fluid">
                        <button class="crbc-checkout-method-continue-button btn pull-right"><span><?php echo JText::_('COM_BREEZINGCOMMERCE_CONTINUE'); ?></span></button>
                    </div>
                    
                </div>

                <div class="crbc-checkout-method-login-controls crbc-well span6 well">

                    <fieldset>
                        <legend><?php echo JText::_('COM_BREEZINGCOMMERCE_LOGIN'); ?></legend>
                    </fieldset>
                    
                    <?php
                    jimport( 'joomla.application.module.helper' );
                    $module = JModuleHelper::getModule( 'mod_login' );
                    $attribs = array('style' => 'html5');
                    $module->params = '{"pretext":"","posttext":"","login":"","logout":"","greeting":"1","name":"0","usesecure":"0","usetext":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"crbc-checkout-login-header","style":"0"}';
                    echo JModuleHelper::renderModule($module, $attribs);
                    ?>

                </div>
            
            </div>
            
        </li>
        
        <?php endif; ?>

        <li class="crbc-checkout-billing-information">

            <a onclick="crbc_checkout.step('.crbc-checkout-billing-information')" class="crbc-checkout-prev" href="javascript:void(0);">
                <?php echo JText::_('COM_BREEZINGCOMMERCE_BILLING_INFORMATION'); ?>
            </a>
            
            <div class="crbc-checkout-billing-information-controls crbc-row row-fluid">
                   
                <form enctype="multipart/form-data" name="billing_form" id="billing_form" method="post" action="#">

                    <div class="crbc-row row-fluid">

                        <div class="crbc-span4 span4">
                        
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="firstname"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_FIRSTNAME' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="firstname" id="firstname"  maxlength="250" value="<?php echo htmlentities( $this->item->firstname, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                            
                        </div>
                        
                        <div class="crbc-span4 span4">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="lastname"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_LASTNAME' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="lastname" id="lastname" maxlength="250" value="<?php echo htmlentities( $this->item->lastname, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="crbc-span4 span4">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="company"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_COMPANY' ); ?></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="company" id="company" maxlength="250" value="<?php echo htmlentities( $this->item->company, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>

                    </div>

                    <?php if(!$this->limited_address_data ): ?>
                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="address"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_ADDRESS' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input class="crbc-text-wide" type="text" name="address" id="address" maxlength="250" value="<?php echo htmlentities( $this->item->address, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="address2"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_ADDRESS_2' ); ?></label>
                                </div>
                                <div class="controls">
                                    <input class="crbc-text-wide" type="text" name="address2" id="address2" maxlength="250" value="<?php echo htmlentities( $this->item->address2, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="zip"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_ZIP' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="zip" id="zip" maxlength="250" value="<?php echo htmlentities( $this->item->zip, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="city"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_CITY' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="city" id="city" maxlength="250" value="<?php echo htmlentities( $this->item->city, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>

                    <?php endif; ?>
                    
                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="country_id"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_COUNTRY' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <select name="country_id" id="country_id" class="chzn-done" onchange="crbcSwitchCountry('billing_form')">
                                        <option value=""><?php echo JText::_('COM_BREEZINGCOMMERCE_SELECT_ONE');?></option>
                                        <?php
                                        foreach($this->item->countries As $country){
                                        ?>
                                        <option value="<?php echo $country->id;?>"<?php echo $country->id == $this->item->country_id ? ' selected="selected"': '' ?>><?php echo htmlentities( $country->name, ENT_QUOTES, 'UTF-8');?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="crbc-span6 span6">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="region_id"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_REGION' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <select name="region_id" id="region_id" class="chzn-done">
                                        <option value="0"><?php echo JText::_('COM_BREEZINGCOMMERCE_SELECT_LOCATION'); ?></option>
                                    </select>
                                    <script type="text/javascript">
                                    <!--
                                    countries = new Array();
                                    crbc_region_default = <?php echo CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_SELECT_LOCATION')); ?>;
                                    crbc_region_not_required = <?php echo CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_REGION_NOT_REQUIRED')); ?>;
                                    <?php
                                    foreach($this->item->countries As $country){
                                        $regions = '';
                                        $size = count($country->regions);
                                        $i = 0;
                                        foreach($country->regions As $region){
                                            $regions .= '{';
                                            $regions .= 'name: "'.addslashes($region->name).'", code: "'.addslashes($region->code).'", id: '.$region->id.'';
                                            $regions .= '}';
                                            if($i + 1 < $size){
                                                $regions .= ',';
                                            }
                                            $i++;
                                        }

                                        echo 'countries.push({country_id : "'.addslashes($country->id).'", regions: ['.$regions.']});'."\n";
                                    }
                                    ?>
                                    jQuery(document).ready(function(){
                                        var countrySelected = document.billing_form.country_id.selectedIndex != -1 ? document.billing_form.country_id.options[document.billing_form.country_id.selectedIndex].value : 0;
                                        var regionSelected  = <?php echo $this->item->region_id ? $this->item->region_id: 0?>;
                                        crbcSwitchRegion(countrySelected, regionSelected, 'billing_form');
                                    });
                                    //-->
                                    </script>
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>

                    <?php if(!$this->limited_address_data ): ?>
                    
                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="phone"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_PHONE' ); ?> <!--<i class="crbc-fa crbc-fa-asterisk"></i></label>-->
                                </div>
                                <div class="controls">
                                    <input type="text" name="phone" id="phone" maxlength="250" value="<?php echo htmlentities( $this->item->phone, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="crbc-span6 span6">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="mobile"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_MOBILE' ); ?></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="mobile" id="mobile" maxlength="250" value="<?php echo htmlentities( $this->item->mobile, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="fax"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_FAX' ); ?></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="fax" id="fax" maxlength="250" value="<?php echo htmlentities( $this->item->fax, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>
					
					<div class="crbc-row row-fluid">
						<div class="crbc-email-alternative-holder crbc-span6 span6"></div>
						<div class="crbc-email-repeat-alternative-holder crbc-span6 span6"></div>
					</div>
                    
                    <?php else: ?>
                    
                    <div class="crbc-row row-fluid">
                        <div class="crbc-email-alternative-holder crbc-span6 span6"></div>
						<div class="crbc-email-repeat-alternative-holder crbc-span6 span6"></div>
                    </div>
                    
                    <?php endif; ?>
                    
                    <div class="crbc-registered-partial crbc-row row-fluid">
                        <div class="crbc-registered-info crbc-well well"></div>
                    </div>
                    
                    <div class="crbc-registration-error-wrap crbc-row row-fluid">
                        <div class="crbc-registration-error crbc-alert-error crbc-span12 span12 alert alert-error"></div>
                    </div>
                    
                    <div class="crbc-register-partial crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="register_username"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_USERNAME' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="username" id="register_username" maxlength="250" value="" />
                                </div>
                            </div>
                            
                        </div>
                    </div>
					
					<div class="crbc-register-partial crbc-row row-fluid">
						
                        <div class="crbc-email-holder crbc-span6 span6">

                            <div class="crbc-control-group control-group">
                                <div class="control-label">
                                    <label for="email"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_EMAIL' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="email" id="email" maxlength="250" value="<?php echo htmlentities( $this->item->email, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        <div class="crbc-email-repeat-holder crbc-span6 span6">

                            <div class="crbc-control-group control-group">
                                <div class="control-label">
                                    <label for="email_repeat"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_EMAIL_REPEAT' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="email_repeat" id="email_repeat" maxlength="250" value="<?php echo htmlentities( $this->item->email, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <div class="crbc-register-partial crbc-row row-fluid">
                        
                        <div class="crbc-span6 span6">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="password"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_PASSWORD' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="password" name="password" id="password" maxlength="250" value="" />
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="crbc-span6 span6">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="password2"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_PASSWORD_REPEAT' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="password" name="password2" id="password2" maxlength="250" value="" />
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>
                    
                    <?php if(!$this->is_virtual_order):?>
                    <div class="crbc-span6 span6">

                        <div class="control-group">

                            <div class="control-label">
                                <label></label>
                            </div>

                            <div class="controls">

                                <label class="control-label" for="use_shipping_address_0">
                                    <input type="radio" value="0" name="use_shipping_address" id="use_shipping_address_0"<?php echo !$this->item->use_shipping_address ? ' checked="checked"' : '';?>/>
                                    <?php echo JText::_( 'COM_BREEZINGCOMMERCE_SHIP_TO_THIS_ADDRESS' ); ?>
                                </label>

                                <label class="control-label" for="use_shipping_address_1">
                                    <input type="radio" value="1" name="use_shipping_address" id="use_shipping_address_1"<?php echo $this->item->use_shipping_address ? ' checked="checked"' : '';?>/>
                                    <?php echo JText::_( 'COM_BREEZINGCOMMERCE_SHIP_TO_DIFFERENT_ADDRESS' ); ?>
                                </label>
                            </div>
                        </div>

                    </div>
                    <?php endif; ?>
                    
                    <input type="hidden" name="register" id="register" value="0"/>

                </form>
                
                <div class="crbc-row row-fluid">
                    <button onclick="crbc_checkout.submit_billing_information()" class="crbc-checkout-method-continue-button btn pull-right"><span><?php echo JText::_('COM_BREEZINGCOMMERCE_CONTINUE'); ?></span></button>
                </div>
                
            </div>

        </li>

        <?php if(!$this->is_virtual_order && !$this->limited_address_data):?>
        <li class="crbc-checkout-shipping-information">

            <a onclick="crbc_checkout.step('.crbc-checkout-shipping-information')" class="crbc-checkout-prev" href="javascript:void(0);">
                <?php echo JText::_('COM_BREEZINGCOMMERCE_SHIPPING_INFORMATION'); ?>
            </a>
            
            <div class="crbc-checkout-shipping-information-controls crbc-row row-fluid">
                
                    
                <form enctype="multipart/form-data" name="shipping_form" id="shipping_form" method="post" action="#">

                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                        
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_firstname"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_FIRSTNAME' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_firstname" id="shipping_firstname"  maxlength="250" value="<?php echo htmlentities( $this->item->shipping_firstname, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                            
                        </div>
                        
                        <div class="crbc-span6 span6">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_lastname"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_LASTNAME' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_lastname" id="shipping_lastname" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_lastname, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>

                    </div>

                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_company"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_COMPANY' ); ?></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_company" id="shipping_company" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_company, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>
					
					<div class="crbc-row row-fluid">
						
						<div class="crbc-span6 span6">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_email"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_EMAIL' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_email" id="shipping_email" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_email, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
						
						<div class="crbc-span6 span6">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_email_repeat"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_EMAIL_REPEAT' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_email_repeat" id="shipping_email_repeat" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_email, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
						
					</div>

                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_address"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_ADDRESS' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input class="crbc-text-wide" type="text" name="shipping_address" id="shipping_address" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_address, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_address2"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_ADDRESS_2' ); ?></label>
                                </div>
                                <div class="controls">
                                    <input class="crbc-text-wide" type="text" name="shipping_address2" id="shipping_address2" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_address2, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="crbc-row row-fluid">
                        
                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_zip"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_ZIP' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_zip" id="shipping_zip" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_zip, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_city"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_CITY' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_city" id="shipping_city" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_city, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>

                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="country_id"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_COUNTRY' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <select name="shipping_country_id" id="shipping_country_id" class="chzn-done" onchange="crbcSwitchShippingCountry('shipping_form')">
                                        <option value=""><?php echo JText::_('COM_BREEZINGCOMMERCE_SELECT_ONE');?></option>
                                        <?php
                                        foreach($this->item->countries As $country){
                                        ?>
                                        <option value="<?php echo $country->id;?>"<?php echo $country->id == $this->item->shipping_country_id ? ' selected="selected"': '' ?>><?php echo htmlentities( $country->name, ENT_QUOTES, 'UTF-8');?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="crbc-span6 span6">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_region_id"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_REGION' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                                </div>
                                <div class="controls">
                                    <select name="shipping_region_id" id="shipping_region_id" class="chzn-done">
                                        <option value="0">*</option>
                                    </select>
                                    <script type="text/javascript">
                                    <!--
                                    // countries have been generated above
                                    jQuery(document).ready(function(){
                                        var countrySelected = document.shipping_form.shipping_country_id.selectedIndex != -1 ? document.shipping_form.shipping_country_id.options[document.shipping_form.shipping_country_id.selectedIndex].value : 0;
                                        var regionSelected  = <?php echo $this->item->shipping_region_id ? $this->item->shipping_region_id: 0?>;
                                        crbcSwitchShippingRegion(countrySelected, regionSelected, 'shipping_form');
                                    });
                                    //-->
                                    </script>
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>

                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_phone"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_PHONE' ); ?> <!--<i class="crbc-fa crbc-fa-asterisk"></i></label>-->
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_phone" id="shipping_phone" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_phone, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="crbc-span6 span6">

                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_mobile"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_MOBILE' ); ?></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_mobile" id="shipping_mobile" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_mobile, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="crbc-row row-fluid">

                        <div class="crbc-span6 span6">
                    
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="shipping_fax"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_FAX' ); ?></label>
                                </div>
                                <div class="controls">
                                    <input type="text" name="shipping_fax" id="shipping_fax" maxlength="250" value="<?php echo htmlentities( $this->item->shipping_fax, ENT_QUOTES, 'UTF-8');?>" />
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>
                    
                </form>
                
                <div class="crbc-row row-fluid">
                    <div class="crbc-well well"><?php echo JText::_('COM_BREEZINGCOMMERCE_SHIPPING_TO_ADDRESS_ABOVE'); ?></div>
                </div>
                
                <div class="crbc-row row-fluid">
                    <a onclick="crbc_checkout.prev_step()" class="crbc-checkout-prev" href="javascript:void(0);">
                        <i class="crbc-fa crbc-fa crbc-fa-arrow-up"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_BACK'); ?>
                    </a>
                    <button onclick="crbc_checkout.submit_shipping_information()" class="crbc-checkout-method-continue-button btn pull-right"><span><?php echo JText::_('COM_BREEZINGCOMMERCE_CONTINUE'); ?></span></button>
                </div>
                
            </div>

        </li>


        <li class="crbc-checkout-shipping-method">

            <a onclick="crbc_checkout.step('.crbc-checkout-shipping-method')" class="crbc-checkout-prev" href="javascript:void(0);">
                <?php echo JText::_('COM_BREEZINGCOMMERCE_SHIPPING_METHOD'); ?>
            </a>
            
            <div class="crbc-row row-fluid">
                
                <?php
                if(count($this->order_info->shipping_options)){
                ?>
                
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
                        <label class="crbc-control-label control-label" for="shipping_option_<?php echo $shipping_option['id']; ?>">
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
                <?php
                }else{
                ?>
                <div class="crbc-alert alert alert-error">
                    <?php echo JText::_('COM_BREEZINGCOMMERCE_SHIPPING_OPTION_NOT_AVAILABLE'); ?>
                </div>
                <?php
                }
                ?>
                
                <div class="crbc-row row-fluid">
                    <a onclick="crbc_checkout.prev_step()" class="crbc-checkout-prev" href="javascript:void(0);">
                        <i class="crbc-fa crbc-fa crbc-fa-arrow-up"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_BACK'); ?>
                    </a>
                    <button onclick="crbc_checkout.submit_shipping_method()" class="crbc-checkout-method-continue-button btn pull-right"><span><?php echo JText::_('COM_BREEZINGCOMMERCE_CONTINUE'); ?></span></button>
                </div>
                
            </div>

        </li>

        <?php endif; ?>

        <li class="crbc-checkout-payment-information">

            <a onclick="crbc_checkout.step('.crbc-checkout-payment-information')" class="crbc-checkout-prev" href="javascript:void(0);">
                <?php echo JText::_('COM_BREEZINGCOMMERCE_PAYMENT_INFORMATION'); ?>
            </a>
            
            <div class="crbc-row row-fluid">
                
                <?php
                if(count($this->order_info->payment_options)){
                ?>
                
                <div class="crbc-payment-error crbc-alert alert alert-error"></div>
                
                <form name="payment_options_form" id="payment_options_form" method="post" action="#">
                    <div class="controls">
                <?php
                    $i = 0;
                    foreach($this->order_info->payment_options As $payment_option){
                ?>
                    <div class="crbc-payment-selection crbc-alert-info alert alert-info">

                        <label class="crbc-control-label control-label" for="payment_option_<?php echo $payment_option['id']; ?>">
                            <input <?php echo $this->payment_plugin_id == $payment_option['id'] || ( $this->payment_plugin_id == 0 && $i  == 0 ) ? 'checked="checked" ' : ''; ?>type="radio" name="payment_option" id="payment_option_<?php echo $payment_option['id']; ?>" value="<?php echo $payment_option['id']; ?>"/>
                            <?php echo $payment_option['icon']; ?> <?php echo htmlentities($payment_option['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </label>

                        <?php if(trim($payment_option['description']) != ''): ?>
                            <?php echo htmlentities($payment_option['description'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>

                    </div>
                <?php
                        $i++;
                    }
                ?>
                    </div>
                </form>
                <?php
                }else{
                ?>
                <div class="crbc-alert alert alert-error">
                    <?php echo JText::_('COM_BREEZINGCOMMERCE_PAYMENT_OPTION_NOT_AVAILABLE'); ?>
                </div>
                <?php
                }
                ?>
                
                <div class="crbc-row row-fluid">
                    <a onclick="crbc_checkout.prev_step()" class="crbc-checkout-prev" href="javascript:void(0);">
                        <i class="crbc-fa crbc-fa crbc-fa-arrow-up"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_BACK'); ?>
                    </a>
                    <button onclick="crbc_checkout.submit_payment_information()" class="crbc-checkout-method-continue-button btn pull-right"><span><?php echo JText::_('COM_BREEZINGCOMMERCE_CONTINUE'); ?></span></button>
                </div>
            </div>

        </li>
        
        <?php
        if(count($this->cart_plugins)){
        ?>
        <li class="crbc-cart-plugin-instances">
            
            <a onclick="crbc_checkout.step('.crbc-cart-plugin-instances')" class="crbc-checkout-prev" href="javascript:void(0);">
                <?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_CART_PLUGINS'); ?>
            </a>
            
            <div class="crbc-row row-fluid">
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
            
            <div class="crbc-row row-fluid">
                    <a onclick="crbc_checkout.prev_step()" class="crbc-checkout-prev" href="javascript:void(0);">
                        <i class="crbc-fa crbc-fa crbc-fa-arrow-up"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_BACK'); ?>
                    </a>
                    <button onclick="crbc_checkout.submit_plugin_instances()" class="crbc-checkout-method-continue-button btn pull-right"><span><?php echo JText::_('COM_BREEZINGCOMMERCE_CONTINUE'); ?></span></button>
                </div>
        </li>
        <?php
        }
        ?>

        <li class="crbc-checkout-order-review-information">

            <a onclick="crbc_checkout.step('.crbc-checkout-order-review-information')" class="crbc-checkout-prev" href="javascript:void(0);">
                <?php echo JText::_('COM_BREEZINGCOMMERCE_REVIEW_ORDER'); ?>
            </a>
            
            <div class="crbc-row row-fluid">
               
                <table class="crbc-cart crbc-responsive table table-striped">

                    <thead>
                        <th><?php echo JText::_('COM_BREEZINGCOMMERCE_PRODUCT')?></th>

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
                            <td>
                                <div class="crbc-item-info">
                                    <div class="crbc-pull-left pull-left">
                                        <h4><?php echo intval($item->amount); ?> x <?php echo $this->escape($item->title); ?></h4> 
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
                            <!--
                            <td>
                                <div class="crbc-checkout-item-amount">
                                    <?php echo intval($item->amount); ?>
                                </div>
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

                        <div id="crbc-cart-price-shipping" class="crbc-cart-final-right"><?php echo $this->cart_instance->formatPrice($this->order_info->shipping_costs);?></div>

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
                
                <form name="checkout_form" id="checkout_form" method="post" action="#">
                
                    <?php if($this->checkout_information_article1_link != ''): ?>
                    <div class="crbc-well well">
                        <label class="crbc-control-label control-label">
                            <?php if($this->checkout_confirm_information_article1):?>
                            <input type="checkbox" name="checkout_confirm_information_article1" id="checkout_confirm_information_article1" value="1" />
                            <?php endif; ?>
                            <a href="<?php echo $this->checkout_information_article1_link;?>" class="modal" rel="{handler: 'iframe', size: {x: 700, y: 350}}"><?php echo $this->escape($this->checkout_information_article1_title); ?></a>
                        </label>
                    </div>
                    <?php endif; ?>

                    <?php if($this->checkout_information_article2_link != ''): ?>
                    <div class="crbc-well well">
                        <label class="crbc-control-label control-label">
                            <?php if($this->checkout_confirm_information_article2):?>
                            <input type="checkbox" name="checkout_confirm_information_article2" id="checkout_confirm_information_article2" value="1" />
                            <?php endif; ?>
                            <a href="<?php echo $this->checkout_information_article2_link;?>" class="modal" rel="{handler: 'iframe', size: {x: 700, y: 350}}"><?php echo $this->escape($this->checkout_information_article2_title); ?></a>
                        </label>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($this->checkout_information_article3_link != ''): ?>
                    <div class="crbc-well well">
                        <label class="crbc-control-label control-label">
                            <?php if($this->checkout_confirm_information_article3):?>
                            <input type="checkbox" name="checkout_confirm_information_article3" id="checkout_confirm_information_article3" value="1" />
                            <?php endif; ?>
                            <a href="<?php echo $this->checkout_information_article3_link;?>" class="modal" rel="{handler: 'iframe', size: {x: 700, y: 350}}"><?php echo $this->escape($this->checkout_information_article3_title); ?></a>
                        </label>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($this->checkout_information_article4_link != ''): ?>
                    <div class="crbc-well well">
                        <label class="crbc-control-label control-label">
                            <?php if($this->checkout_confirm_information_article4):?>
                            <input type="checkbox" name="checkout_confirm_information_article4" id="checkout_confirm_information_article4" value="1" />
                            <?php endif; ?>
                            <a href="<?php echo $this->checkout_information_article4_link;?>" class="modal" rel="{handler: 'iframe', size: {x: 700, y: 350}}"><?php echo $this->escape($this->checkout_information_article4_title); ?></a>
                        </label>
                    </div>
                    <?php endif; ?>

                </form>

                <div class="crbc-row row-fluid">
                    <a onclick="crbc_checkout.prev_step()" class="crbc-checkout-prev" href="javascript:void(0);">
                        <i class="crbc-fa crbc-fa crbc-fa-arrow-up"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_BACK'); ?>
                    </a>
                    <button onclick="crbc_checkout.submit_order()" class="crbc-checkout-method-continue-button btn btn-large btn-primary pull-right"><span><i class="crbc-fa crbc-fa-check"></i>  <?php echo JText::_('COM_BREEZINGCOMMERCE_PLACE_ORDER'); ?></span></button>
                </div>
                
            </div>

        </li>

    </ul>
    
</div>