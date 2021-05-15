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
<div class="crbc crbc-page crbc-customer-profile-page<?php echo $this->pageclass_sfx != '' ? ' ' . $this->pageclass_sfx : ''; ?>">
    
    <div class="crbc-customer-profile-title page-header">
        <h1><?php echo $this->escape( $this->heading );?></h1>
    </div>
    
    <div class="crbc-customer-profile-billing-information crbc-row row-fluid">
                
        <fieldset>
        
        <legend><?php echo JText::_('COM_BREEZINGCOMMERCE_BILLING_INFORMATION'); ?></legend>
        
        <form class="form-vertical" enctype="multipart/form-data" name="billing_form" id="billing_form" method="post" action="#">

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
            
            <fieldset>
            
            <legend><?php echo JText::_('COM_BREEZINGCOMMERCE_USERDATA'); ?></legend>
                
            <div class="crbc-registration-error-wrap crbc-row row-fluid">
                <div class="crbc-registration-error crbc-alert-error crbc-span12 span12 alert alert-error"></div>
            </div>

            <div class="crbc-registration-data crbc-row row-fluid">

                <div class="crbc-span6 span6">

                    <div class="control-group">
                        <div class="control-label">
                            <label for="register_username"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_USERNAME' ); ?> <i class="crbc-fa crbc-fa-asterisk"></i></label>
                        </div>
                        <div class="controls">
                            <input type="text" name="username" id="register_username" maxlength="250" value="<?php echo $this->escape(JFactory::getUser()->get('username')); ?>" disabled="disabled" />
                        </div>
                    </div>

                </div>


            </div>
			
			<div class="crbc-registration-data crbc-row row-fluid">
				
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

            <div class="crbc-registration-data crbc-row row-fluid">

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
                
            </fieldset>

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

        </form>

        <div class="crbc-row row-fluid">
            <button onclick="crbc_customerprofile.submit_billing_information(this, false)" class="crbc-customer-profile-method-save-button btn pull-right"><i class="crbc-fa crbc-fa-edit"></i> <span><?php echo JText::_('COM_BREEZINGCOMMERCE_SAVE_BILLING_INFORMATION'); ?></span></button>
        </div>
        
        </fieldset>

    </div>
    
    <div class="<?php echo $this->item->use_shipping_address == 1 ? 'crbc-customer-profile-shipping-enabled ' : 'crbc-customer-profile-shipping-disabled '; ?>crbc-customer-profile-shipping-information crbc-row row-fluid">
        
        <hr />
        
        <fieldset>
        
        <legend><?php echo JText::_('COM_BREEZINGCOMMERCE_SHIPPING_INFORMATION'); ?></legend>

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
            <button onclick="crbc_customerprofile.submit_shipping_information(this)" class="crbc-customer-profile-method-save-button btn pull-right"><i class="crbc-fa crbc-fa-edit"></i> <span><?php echo JText::_('COM_BREEZINGCOMMERCE_SAVE_SHIPPING_INFORMATION'); ?></span></button>
        </div>
        
        </fieldset>

    </div>
    
    <div class="crbc-clearfix clearfix"></div>
    
</div>