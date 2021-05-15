jQuery(document).ready(function(){
   // seriously, we don't want this in the frontend for BC
   jQuery('.crbc select').data('chosen','no-chzn');
   jQuery('.crbc select').addClass("chzn-done");
   
});

/* 
 * @package BreezingCommerce
 * @author Markus Bopp
 */
function crbcLoadModal(){
    
    jQuery(document).ready(function(){
        
        if(jQuery('#crbc-categoriesfilterpicker-remodal').size() == 0){
            jQuery('body').append('<div style="display:none;" id="crbc-categoriesfilterpicker-remodal"><button data-remodal-action="close" class="remodal-close"></button><div id="crbc-categoriesfilterpicker-remodal-content"></div></div>');
        }
        
        jQuery('#crbc-categoriesfilterpicker-remodal').css('margin','0');
        jQuery('#crbc-categoriesfilterpicker-remodal').css('padding','20px');
        jQuery('#crbc-categoriesfilterpicker-remodal').css('box-sizing','border-box');
        jQuery('#crbc-categoriesfilterpicker-remodal').css('display','inline-block');
        
        var init = jQuery('#crbc-categoriesfilterpicker-remodal').remodal();
        
        jQuery('.remodal-trigger').on('click', function(e){
            e.preventDefault();
            if(init.getState() == 'open' || init.getState() == 'closing' || init.getState() == 'opening' ){
                return;
            }
            var href = jQuery(this).attr('href');
            var width = jQuery.parseJSON(jQuery(this).data('remodal-width'));
            var height = jQuery.parseJSON(jQuery(this).data('remodal-height'));
            jQuery('#crbc-categoriesfilterpicker-remodal').css('max-width',width+'px');
            jQuery('#crbc-categoriesfilterpicker-remodal').css('width','100%');
            jQuery('#crbc-categoriesfilterpicker-remodal').css('height',(height+60)+'px');
            init.open();
            jQuery('#crbc-categoriesfilterpicker-remodal-content').html('<iframe width="100%" height="'+height+'" allowtransparency="true" frameborder="0" scrolling="1" src="'+href+'"></iframe>');
        });
        
        jQuery('#crbc-categoriesfilterpicker-remodal').on('closed', function(){
            crbcCloseModal();
        });
    });
}

function crbcCloseModal(){
    jQuery('.remodal-trigger').off('click');
    jQuery('#crbc-categoriesfilterpicker-remodal').off('closed');
    jQuery('#crbc-categoriesfilterpicker-remodal-content').html('');
    jQuery('#crbc-categoriesfilterpicker-remodal').css('display','none');
    var init = jQuery('#crbc-categoriesfilterpicker-remodal').remodal();
    init.close();
    init.destroy();
    crbcLoadModal();
}

function crbcLoadHtmlModal(content, width, height){
    
    var click = arguments[3] ? arguments[3] : false;
    
    jQuery(document).ready(function(){
        
        if(jQuery('#crbc-categoriesfilterpicker-remodal-html').size() == 0){
            jQuery('body').append('<div style="display:none;" id="crbc-categoriesfilterpicker-remodal-html"><button data-remodal-action="close" class="remodal-close"></button><div id="crbc-categoriesfilterpicker-remodal-html-content"></div></div>');
        }
        
        jQuery('#crbc-categoriesfilterpicker-remodal-html').css('margin','0');
        jQuery('#crbc-categoriesfilterpicker-remodal-html').css('padding','20px');
        jQuery('#crbc-categoriesfilterpicker-remodal-html').css('box-sizing','border-box');
        jQuery('#crbc-categoriesfilterpicker-remodal-html').css('display','inline-block');
        
        var init = jQuery('#crbc-categoriesfilterpicker-remodal-html').remodal();
        
        var open = function(){
            
            if(init.getState() == 'open' || init.getState() == 'closing' || init.getState() == 'opening' ){
                return;
            }
            
            jQuery('#crbc-categoriesfilterpicker-remodal-html').css('max-width',width+'px');
            jQuery('#crbc-categoriesfilterpicker-remodal-html').css('width','100%');
            jQuery('#crbc-categoriesfilterpicker-remodal-html').css('height',(height+20)+'px');
            init.open();
            jQuery('#crbc-categoriesfilterpicker-remodal-html-content').html(content);
        }
        
        if(click){
            open();
        }
        
        jQuery('.remodal-html-trigger').on('click', function(e){
            
            e.preventDefault();
            
            open();
        });
        
        jQuery('#crbc-categoriesfilterpicker-remodal-html').on('closed', function(){
            crbcCloseHtmlModal();
        });
    });
}

function crbcCloseHtmlModal(){
    jQuery('.remodal-html-trigger').off('click');
    jQuery('#crbc-categoriesfilterpicker-remodal-html').off('closed');
    jQuery('#crbc-categoriesfilterpicker-remodal-html-content').html('');
    jQuery('#crbc-categoriesfilterpicker-remodal-html').css('display','none');
    var init = jQuery('#crbc-categoriesfilterpicker-remodal-html').remodal();
    init.close();
    init.destroy();
}

function crbcSwitchCountry(){
    
    formname = 'adminForm';
    
    switch(arguments.length){
        case 1: formname = arguments[0]; break;
    }
    
    var countrySelected = document[formname].country_id.selectedIndex != -1 ? document[formname].country_id.options[document[formname].country_id.selectedIndex].value : 0;
    var regionSelected = document[formname].region_id.selectedIndex != -1 ? document[formname].region_id.options[document[formname].region_id.selectedIndex].value : 0;
    crbcSwitchRegion(countrySelected, regionSelected, formname);
}

/**
 * requires a globally defined array called "countries" that holds country and region information
 */
function crbcSwitchRegion(countrySelected, regionSelected){
    
    formname = 'adminForm';
    
    switch(arguments.length){
        case 3: formname = arguments[2]; break;
    }
    document[formname].region_id.options.length = 0;
    for(var i = 0; i < countries.length; i++){
        
        var option_label = '*';
        
        if(countries[i].regions.length == 0){
            document[formname].region_id.disabled = true;
            if(typeof crbc_region_not_required != "undefined"){
                option_label = crbc_region_not_required;
            }
        } else {
            document[formname].region_id.disabled = false;
            if(typeof crbc_region_default != "undefined"){
                option_label = crbc_region_default;
            }
        }
        
        if(countries[i].country_id == countrySelected){
            document[formname].region_id.options[0] = new Option(option_label, '0', true, false);
            for(var j = 0; j < countries[i].regions.length; j++){
                document[formname].region_id.options[j+1] = new Option(countries[i].regions[j].name, countries[i].regions[j].id, true, regionSelected == countries[i].regions[j].id);
            }
            break;
        }
    }
}

function crbcSwitchShippingCountry(){
    
    formname = 'adminForm';
    
    switch(arguments.length){
        case 1: formname = arguments[0]; break;
    }
    
    var countrySelected = document[formname].shipping_country_id.selectedIndex != -1 ? document[formname].shipping_country_id.options[document[formname].shipping_country_id.selectedIndex].value : 0;
    var regionSelected = document[formname].shipping_region_id.selectedIndex != -1 ? document[formname].shipping_region_id.options[document[formname].shipping_region_id.selectedIndex].value : 0;
    crbcSwitchShippingRegion(countrySelected, regionSelected, formname);
}

/**
 * requires a globally defined array called "countries" that holds country and region information
 */
function crbcSwitchShippingRegion(countrySelected, regionSelected){
    
    formname = 'adminForm';
    
    switch(arguments.length){
        case 3: formname = arguments[2]; break;
    }
    
    document[formname].shipping_region_id.options.length = 0;
    for(var i = 0; i < countries.length; i++){
        
        var option_label = '*';
        
        if(countries[i].regions.length == 0){
            document[formname].shipping_region_id.disabled = true;
            if(typeof crbc_region_not_required != "undefined"){
                option_label = crbc_region_not_required;
            }
        } else {
            document[formname].shipping_region_id.disabled = false;
            if(typeof crbc_region_default != "undefined"){
                option_label = crbc_region_default;
            }
        }
        
        if(countries[i].country_id == countrySelected){
            document[formname].shipping_region_id.options[0] = new Option(option_label, '0', true, false);
            for(var j = 0; j < countries[i].regions.length; j++){
                document[formname].shipping_region_id.options[j+1] = new Option(countries[i].regions[j].name, countries[i].regions[j].id, true, regionSelected == countries[i].regions[j].id);
            }
            break;
        }
    }
}

function validEmail(email){
  var filter = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,8})+$/;
  return filter.test(email);
}

function crbc_center(target){
    jQuery(target).crbc_center();
    jQuery(window).resize(function(){
        jQuery(target).crbc_center();
    });
}

function crbc_disableScreen(){
    var target = 'body';
    
    switch(arguments.length){
        case 1: target = arguments[0]; break;
    }
    
    jQuery(target).append('<div id="crbc-overlay" style="background-color:black;position:absolute;top:0;left:0;height:100%;width:100%;z-index:'+(jQuery.topZIndex()+1)+';-ms-opacity:0.1;-o-opacity:0.1;-webkit-opacity:0.1;-moz-opacity:0.1;opacity:0.1;"></div>');            
}

function crbc_enableScreen(){
    jQuery("#crbc-overlay").remove();
}

function crbc_startSpinner(target){
    
    jQuery(target).append('<div class="crbc-spinner"><div class="wBall" id="wBall_1"><div class="wInnerBall"></div></div><div class="wBall" id="wBall_2"><div class="wInnerBall"></div></div><div class="wBall" id="wBall_3"><div class="wInnerBall"></div></div><div class="wBall" id="wBall_4"><div class="wInnerBall"></div></div><div class="wBall" id="wBall_5"><div class="wInnerBall"></div></div></div>');
}

function crbc_stopSpinner(target){
    jQuery(document).ready(
        function(){
            jQuery(target+' .crbc-spinner').remove();
        }
    );
}

// jQuery plugins

// center elements

jQuery.fn.crbc_center = function ()
{
    this.css("position","fixed");
    this.css("top", (jQuery(window).height() / 2) - (this.outerHeight() / 2));
    this.css("left", (jQuery(window).width() / 2) - (this.outerWidth() / 2));
    return this;
};


// highest zindex

(function (jQuery) {

    jQuery.topZIndex = function (selector) {
            /// <summary>
            /// 	Returns the highest (top-most) zIndex in the document
            /// 	(minimum value returned: 0).
            /// </summary>	
            /// <param name="selector" type="String" optional="true">
            /// 	(optional, default = "*") jQuery selector specifying
            /// 	the elements to use for calculating the highest zIndex.
            /// </param>
            /// <returns type="Number">
            /// 	The minimum number returned is 0 (zero).
            /// </returns>

            return Math.max(0, Math.max.apply(null, jQuery.map(((selector || "*") === "*")? jQuery.makeArray(document.getElementsByTagName("*")) : jQuery(selector),
                    function (v) {
                            return parseFloat(jQuery(v).css("z-index")) || null;
                    }
            )));
    };

    jQuery.fn.topZIndex = function (opt) {
            /// <summary>
            /// 	Increments the CSS z-index of each element in the matched set
            /// 	to a value larger than the highest current zIndex in the document.
            /// 	(i.e., brings all elements in the matched set to the top of the
            /// 	z-index order.)
            /// </summary>	
            /// <param name="opt" type="Object" optional="true">
            /// 	(optional) Options, with the following possible values:
            /// 	increment: (Number, default = 1) increment value added to the
            /// 		highest z-index number to bring an element to the top.
            /// 	selector: (String, default = "*") jQuery selector specifying
            /// 		the elements to use for calculating the highest zIndex.
            /// </param>
            /// <returns type="jQuery" />

            // Do nothing if matched set is empty
            if (this.length === 0) {
                    return this;
            }

            opt = jQuery.extend({increment: 1}, opt);

            // Get the highest current z-index value
            var zmax = jQuery.topZIndex(opt.selector),
                    inc = opt.increment;

            // Increment the z-index of each element in the matched set to the next highest number
            return this.each(function () {
                    this.style.zIndex = (zmax += inc);
            });
    };

})(jQuery);




