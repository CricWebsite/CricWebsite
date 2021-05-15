/**
 * Class to handle Cart events and anything that is related to it.
 */
crbc_cart_notifiers = [];
crbc_cart_add_callers = [];

var CrBcCart = function(){
    
    this.ajax_cart = false;
    this.current_url = '';
    this.url_parameters_init = '?';
    this.add_to_cart_msg = '';
    
    switch(arguments.length){
        case 1: this.ajax_cart = arguments[0]; break;
        case 2: 
            this.ajax_cart = arguments[0]; 
            this.current_url = arguments[1]; 
            
            if(this.current_url.indexOf('?') !== -1){
                this.url_parameters_init = '&';
            }
        case 3: 
            this.ajax_cart = arguments[0]; 
            this.current_url = arguments[1]; 
            
            if(this.current_url.indexOf('?') !== -1){
                this.url_parameters_init = '&';
            }   
            this.add_to_cart_msg = arguments[2]; 
        break;
    }
    
    var _ajax_cart = this.ajax_cart;
    var _current_url = this.current_url;
    var _url_parameters_init = this.url_parameters_init;
    var _this = this;
    
    jQuery(document).ready(function(){
        jQuery('.crbc-add-shipping-button').on('click', function(){
            _this.submit_shipping_method();
        });
    });
    
    this.remove_from_cart = function(item_id, order_item_id){
        
        if(!_ajax_cart){
            
            location.href = _current_url + _url_parameters_init + 'controller=cart&Itemid='+crbc_item_id+'&task=remove&order_item_ids[]='+order_item_id;
            
        } else {
            
            jQuery.ajax(
            {
                // we need the cart's json representation, so we can stay where we are
                url : 'index.php?option=com_breezingcommerce&controller=cart&Itemid='+crbc_item_id+'&format=json&layout=json_response&task=remove',
                type: "POST",
                data: { order_item_ids : [order_item_id] },
                success: function(data, textStatus, jqXHR){
                    //console.log(data);
                    if( ( typeof data.null != "undefined" && data.null == null ) || data.items.length == 0 ) {
                        location.href = _current_url + _url_parameters_init + 'controller=cart';
                    } else {
                        jQuery('#crbc-item-'+order_item_id).remove();
                        _this.populate_cart_totals(data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){ console.log(errorThrown); }
            });
        }
    }
    
    this.submit_add_to_ajax = function(e)
    {
        
        var postData = jQuery(this).serializeArray();
        var immediate = jQuery(this).find('input[name="immediate_checkout"]').val();
        
        jQuery.ajax(
        {
            url : 'index.php?option=com_breezingcommerce&controller=cart&format=json&layout=json_response',
            type: "POST",
            data : postData,
            success:function(data, textStatus, jqXHR) 
            {
                //console.log(textStatus);
                //console.log(jqXHR);
                //console.log(data);

                if(typeof data.error != "undefined"){
                    
                    //alert(data.error);
                    
                    if(typeof crbc_cart_error_close == "undefined"){
                        
                        crbc_cart_error_close = 'Close';
                    }
                    
                    crbcLoadHtmlModal(
                        '<div class="crbc-alert crbc-alert-info alert alert-info alert-block">' + data.error + '</div>'
                        + '<button data-remodal-action="close" class="crbc-mod-minicart-button crbc-btn btn btn-secondary"><i class="crbc-fa crbc-fa-remove"></i> '+crbc_cart_error_close+'</button> '

                , 600, 170, true);
                    
                    return;
                }

                for(var i = 0; i < crbc_cart_notifiers.length; i++){
                    if(typeof crbc_cart_notifiers[i].notifyCartAdd != "undefined" && typeof crbc_cart_notifiers[i].notifyCartAdd == "function"){
                        crbc_cart_notifiers[i].notifyCartAdd();
                    }
                }

                var return_url = '';
                if( typeof data.return_url != 'undefined' && jQuery.trim(data.return_url) != ''){
                    return_url = '&return_url='+data.return_url;
                }
                
                var cart_parameters_init = '?';
                if(typeof crbc_cart_url != "undefined"){
                    if(crbc_cart_url.indexOf('?') !== -1){
                        cart_parameters_init = '&';
                    }
                }
                
                var checkout_parameters_init = '?';
                if(typeof crbc_checkout_url != "undefined"){
                    if(crbc_checkout_url.indexOf('?') !== -1){
                        checkout_parameters_init = '&';
                    }
                }
                
                //var immediate_url = typeof crbc_checkout_url != "undefined" ? crbc_checkout_url + cart_parameters_init + return_url : _current_url + _url_parameters_init + 'option=com_breezingcommerce&controller=checkout'+return_url;
                //var url           = typeof crbc_cart_url != "undefined" ? crbc_cart_url + checkout_parameters_init + return_url : _current_url + _url_parameters_init + 'option=com_breezingcommerce&controller=cart'+return_url;

                var immediate_url = typeof crbc_checkout_url != "undefined" ? crbc_checkout_url : _current_url + _url_parameters_init + 'option=com_breezingcommerce&controller=checkout';
                var url           = typeof crbc_cart_url != "undefined" ? crbc_cart_url : _current_url + _url_parameters_init + 'option=com_breezingcommerce&controller=cart';


                for(var i = 0; i < crbc_cart_add_callers.length; i++){
                    if(typeof crbc_cart_add_callers[i].notifyCartAddMsg != "undefined" && typeof crbc_cart_add_callers[i].notifyCartAddMsg == "function"){
                        crbc_cart_add_callers[i].notifyCartAddMsg(_this.add_to_cart_msg, ( immediate == 1 ? immediate_url : url ), immediate == 1 ? true : false );
                        break; // we only need one message, so who comes first wins
                    }
                }

                if(crbc_cart_add_callers.length == 0){

                    if(immediate == 0){

                        var confirmed = confirm(_this.add_to_cart_msg);

                        if(confirmed){
                            location.href = immediate_url;
                        }

                    }else{

                        location.href = url;
                    }
                
                }
            },
            error:function(jqXHR, textStatus, errorThrown) 
            {
                //console.log(textStatus);
                //console.log(errorThrown);
                //console.log(jqXHR);
                alert(errorThrown);
            }
        });
        e.preventDefault();
    };
    
    // may only be called when being in product view!
    this.add_to_cart_ajax = function(){
        
        // "this" is a jquery object and automatically hand over when clicking the add to cart button
        // additionally, it may be passed as argument from another source
        
        var _add_to_cart_ajax = this;
        
        switch(arguments.length){
            case 2: _add_to_cart_ajax = arguments[1]; break;
        }
        
        // prevent firing the same multiple times
        jQuery(_add_to_cart_ajax).closest('form').off('submit', _this.submit_add_to_ajax);
        jQuery(_add_to_cart_ajax).closest('form').on('submit', _this.submit_add_to_ajax);
        jQuery(_add_to_cart_ajax).closest('form').trigger('submit');
        
    };
    
    this.validateAttributes = function(subject){
        
        jQuery('.crbcAttributeWrapper').removeClass('crbc-alert');
        
        if(typeof crbcValidateAttributes != "undefined"){
            
            crbcTriggerCartValidations = true;
            
            var add_cart_trigger_button = jQuery(subject).closest('.crbc-order-form').attr('id');
            
            if(!crbcValidateAttributes(add_cart_trigger_button)){
                
                var error_element = null;
                
                if(jQuery.isArray(crbcCartValidationErrorElement)){
                    error_element = crbcCartValidationErrorElement[0];
                }else{
                    error_element = crbcCartValidationErrorElement;
                }
                
                
                var error_trigger_button = jQuery(error_element).closest('.crbc-order-form').attr('id');
                
                if(add_cart_trigger_button == error_trigger_button){
                    
                    jQuery(error_element).closest('.crbcAttributeWrapper').addClass('crbc-alert');

                    if(typeof crbcCartValidationErrorText != "undefined"){

                        alert(crbcCartValidationErrorText);

                    }else{

                        alert('Please select all required accessories!');
                    }

                    jQuery(error_element).closest('.crbcAttributeWrapper').ScrollTo();
                
                    return false;
                }
            }
        }
        
        return true;
    };
    
    // may only be called when being in product view!
    // this is the button that triggers this function onclick
    this.add_to_cart = function(){
        
        if( !_this.validateAttributes(this) ){
            
            return;
        }
       
        var event = null;
        
        switch(arguments.length){
            case 1: event = arguments[0]; break;
        }
        
        if(!_ajax_cart){
            // "this" is a jquery object and automatically hand over when clicking the add to cart button
            jQuery(this).closest('form').get(0).submit();
        }else{
            _this.add_to_cart_ajax(event, this);
        }
    };
    
    this.populate_cart_totals = function(data){
        jQuery('#crbc-cart-price-net').html(data.price_net_formatted);
                
        for(var crbc_x in data.price_tax_list_formatted){
            jQuery('#crbc-cart-tax-'+crbc_x).html(data.price_tax_list_formatted[crbc_x]['tax']);
        }

        jQuery('#crbc-cart-price-gross').html(data.price_gross_formatted);
        jQuery('#crbc-cart-grand-total').html(data.grand_total_formatted);
        
        // check for any changes in the shipping methods and reset them if necessary
        
        var tmp_loaded = jQuery('#shipping_method_form').html();
        
        jQuery('#shipping_method_form').load(_this.current_url+' #shipping_method_form > *', function(){
            
            var tmp_current = jQuery('#shipping_method_form').html();
            
            if(tmp_loaded != tmp_current){
               
                // we have a change detected with the shipping options
                // now reset the previously selected one

                jQuery.ajax(
                {
                    url : 'index.php?option=com_breezingcommerce&controller=checkout&Itemid='+crbc_item_id+'&format=json&layout=json_shipping_method_result&task=reset_shipping_method',
                    type: "GET",
                    success: function(data, textStatus, jqXHR){
                        if(typeof data.null == "undefined"){

                            jQuery('.crbc-cart-final').load(
                                    _this.current_url+' .crbc-cart-final > *');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){ console.log(errorThrown); }
                });
            }
        });
    }
    
    // may only be called when being in cart view!
    this.change_cart_amount_ajax = function(product_id, order_item_id, amount){
        
        //alert(this.current_url+this.url_parameters_init+'format=json&layout=json_response&lookup_product_id='+product_id+"&lookup_amount="+amount);
        
        jQuery.ajax(
        {
            // we need the cart's json representation, so we can stay where we are
            url : 'index.php?option=com_breezingcommerce&Itemid='+crbc_item_id+'&controller=cart&format=json&layout=json_response&lookup_item_id='+order_item_id+'&lookup_product_id='+product_id+"&lookup_amount="+amount,
            type: "GET",
            success: function(data, textStatus, jqXHR){
                if(typeof data.null == "undefined"){
                    //console.log(data);
                    _this.populate_cart_totals(data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown){ console.log(errorThrown); }
        });
    }
    
    // this is _only_ for the cart refreshing button if ajax is turned off!
    this.refresh_cart_pricing = function(product_id, order_item_id){
        var amount = parseInt(jQuery('#crbc-cart-item-amount-'+order_item_id).val());
        amount = isNaN(amount) ? 1 : amount;
        
        jQuery('body').append(
            '<form id="crbc-tmp-form" action="'+_current_url+'" method="post">'
            + '<input type="hidden" name="product_id" value="'+product_id+'">'
            + '<input type="hidden" name="amount" value="'+amount+'">'
            + '<input type="hidden" name="task" value="update_amount">'
            + '<input type="hidden" name="controller" value="cart">'
            + '<input type="hidden" name="option" value="com_breezingcommerce">'
            + '<input type="hidden" name="Itemid" value="'+crbc_item_id+'">'
            +'</form>'
        );

        jQuery('#crbc-tmp-form').trigger('submit');
    };
    
    // may only be called when being in cart view!
    this.change_item_amount = function(amount, item_id, order_item_id){
    
        var _event = 'click';
        var _min_amount = 1;
        var _max_amount = 99999;
        
        switch(arguments.length){
            case 4: _event = arguments[3]; break;
            case 5: _event = arguments[3]; _min_amount = arguments[4]; break;
            case 6: _event = arguments[3]; _min_amount = arguments[4]; _max_amount = arguments[5]; break;
        }
        
        var num = Number( jQuery('#crbc-cart-item-amount-'+order_item_id).val() );
        
        if(!isNaN(num) && !isNaN(amount)){

            num = parseInt(num);
            amount = parseInt(amount);

            var result = _event == 'click' ? num + amount : amount;

            if(result < _min_amount){
                result = _min_amount;
            }
            
            if(result > _max_amount){
                result = _max_amount;
            }
            
            jQuery('#crbc-cart-item-amount-'+order_item_id).val(result);
            
            // it's an ajax cart, re-calculate the price based from the json cart info
            if(this.ajax_cart){
                
                var _cart = this;
                
                // prevent from clicking too quickly and disable all buttons
                jQuery('.crbc-cart-item-amount .btn').attr('disabled','disabled');
                
                // update the price visually
                jQuery.ajax(
                {
                    url : 'index.php?Itemid='+crbc_item_id+'&product_id='+item_id+'&order_item_id='+order_item_id+'&amount='+result+'&option=com_breezingcommerce&controller=cart&format=json&layout=json_get_item_price&task=get_item_price',
                    type: "GET",
                    success: function(data, textStatus, jqXHR){
                        
                        jQuery('#crbc-cart-item-price-net-'+order_item_id).html(data.price_net_formatted);
                        jQuery('#crbc-cart-item-price-gross-'+order_item_id).html(data.price_gross_formatted);
                        //console.log(data);
                        var price_tax_list = '';
                        for(var crbc_x in data.price_tax_list_formatted){
                            price_tax_list += '<div class="crbc-price-tax">'+data.price_tax_list_formatted[crbc_x]['tax']+'<br />'+data.price_tax_list_formatted[crbc_x]['tax_name']+'</div>';
                        }
                        jQuery('#crbc-cart-item-price-total-tax-'+order_item_id).html(price_tax_list);
                        
                        // update the price for the cart itself (nailing it down)
                        jQuery.ajax(
                        {
                            url : 'index.php?Itemid='+crbc_item_id+'&order_item_id='+order_item_id+'&product_id='+item_id+'&amount='+result+'&option=com_breezingcommerce&controller=cart&task=update_amount&format=json',
                            type: "GET",
                            async: true,
                            success: function(data, textStatus, jqXHR){

                                // update the totals visually
                                _cart.change_cart_amount_ajax(item_id, order_item_id, result);
                                
                                // re-enable the buttons after
                                jQuery('.crbc-cart-item-amount .btn').removeAttr('disabled');

                            },
                            error: function(jqXHR, textStatus, errorThrown){ console.log(errorThrown); jQuery('.crbc-cart-item-amount .btn').removeAttr('disabled'); }
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown){ console.log(errorThrown); jQuery('.crbc-cart-item-amount .btn').removeAttr('disabled'); }
                });
            }
        }
    };
    
    this.submit_shipping_method = function(){
        
        if(_ajax_cart){
            
            jQuery('.crbc-shipping-error').css('display','none');
            jQuery('.crbc-shipping-error').html();
            
            var postData = jQuery('#shipping_method_form').serializeArray();
            
            // crbc-add-shipping-button
            var tmpl_button_value = jQuery('.crbc-add-shipping-button')
                .attr('disabled','disabled')
                .html();
            
            jQuery('.crbc-add-shipping-button')
                .attr('disabled','disabled')
                .html('<i class="crbc-fa crbc-fa-refresh crbc-fa-spin"></i> ' + tmpl_button_value);
            
            jQuery.ajax(
            {
                url : 'index.php?option=com_breezingcommerce&controller=checkout&Itemid='+crbc_item_id+'&format=json&layout=json_shipping_method_result&task=update_shipping_method',
                type: "POST",
                data: postData,
                success: function(data, textStatus, jqXHR){
                    
                    if(typeof data.null != "undefined" && data.null == null){
                        
                        alert(data.info);
                        location.href = data.return_url;
                        
                    } else {
                        
                        if(typeof data.shipping_method_result != "undefined" && data.shipping_method_result['errors'] && data.shipping_method_result['errors'].length > 0){
                            
                            var errors = '';
                            
                            for(var i = 0; i < data.shipping_method_result['errors'].length; i++){
                                errors += data.shipping_method_result['errors'][i].message+'<br />';
                            }
                            
                            jQuery('.crbc-shipping-error').css('display','block');
                            jQuery('.crbc-shipping-error').html(errors);
                            
                        }else{
                            
                            jQuery('.crbc-cart-final').load(
                                    _this.current_url+' .crbc-cart-final > *');
                        }
                        
                    }
                    
                    jQuery('.crbc-add-shipping-button')
                            .removeAttr('disabled')
                            .html(tmpl_button_value);
                },
                error: function(jqXHR, textStatus, errorThrown){
                    
                    jQuery('.crbc-add-shipping-button')
                            .removeAttr('disabled')
                            .html(tmpl_button_value);
                    
                    console.log(jqXHR);
                    console.log(errorThrown);
                }
            });
        }
        
    };
};

