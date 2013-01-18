wpshop(document).ready(function(){


	function gerer_affichage_element (elt,test) {
		elt_display = '.'+elt.attr('id')+'_content';

		if(elt.is(':checked')){
			jQuery(elt_display).stop(true).fadeIn();
			if(test){
				alert('checked');
			}
		}else{
			jQuery(elt_display).stop(true).fadeOut();
			if(test){
				alert('unchecked'+elt_display);
			}
		}
		
	}


	jQuery("#options-tabs").tabs();
	jQuery("#options-tabs li a").click(function(){
		jQuery("#wpshop_option_form").attr("action", "options.php"+jQuery(this).attr("href"));
	});
	jQuery(".slider_variable").parent().parent().addClass('ui-slider-row');

	jQuery("#paymentByPaypal").change(function(){
		gerer_affichage_element(jQuery(this));
	});
	jQuery("#paymentByCheck").change(function(){
		gerer_affichage_element(jQuery(this));
	});
	jQuery("#paymentByCreditCard_CIC").change(function(){
		gerer_affichage_element(jQuery(this));
	});

	jQuery("#wpshop_shipping_fees_freefrom_activation").change(function(){
		gerer_affichage_element(jQuery(this));
	});
	
	jQuery("#custom_shipping_active").change(function(){
		gerer_affichage_element(jQuery(this));
	});
	
	
	gerer_affichage_element(jQuery("#paymentByPaypal"));
	gerer_affichage_element(jQuery("#paymentByCheck"));
	gerer_affichage_element(jQuery("#paymentByCreditCard_CIC"));

	/*	Activation de module	*/
	jQuery(".addons_activating_button").live('click',function(){
		var addon_name = jQuery(this).attr('name').replace('_button', '');
		var addon_code = jQuery("#" + addon_name).val();
		jQuery(this).attr('disabled', true).css('opacity',0.5);

		var data = {
			action: "activate_wpshop_addons",
			addon: addon_name,
			code: addon_code,
			wpshop_ajax_nonce: jQuery("#wpshop_ajax_addons_nonce").val()
		}
		jQuery.post(ajaxurl, data, function(response) {
			if( response[0] ) {
				jQuery("#" + response[3]).remove();
				jQuery("#" + response[3] + "_button").remove();
			}
			else {
				alert(wpshopConvertAccentTojs(response[1]));
				jQuery("#" + response[3] + "_button").attr('disabled', false).css('opacity',1);
			}
			jQuery("#addon_" + response[3] + "_state").html(response[2]);
			jQuery("#addon_" + response[3] + "_state").attr('class', response[4]);
		}, 'json');
	});
	/*	Désactivation des modules	*/
	jQuery(".addons_desactivating_button").live('click',function(){
		if (confirm(wpshopConvertAccentTojs(WPSHOP_MSG_CONFIRM_ADDON_DEACTIVATION))) {
			var addon_name = jQuery(this).attr('name').replace('_button', '');
			var data = {
				action: "desactivate_wpshop_addons",
				addon: addon_name,
				wpshop_ajax_nonce: jQuery("#wpshop_ajax_addons_nonce").val()
			}
			jQuery.post(ajaxurl, data, function(response) {
				if( response[0] ) {
					jQuery("#" + response[3] + "_button").remove();
				}
				else {
					alert(wpshopConvertAccentTojs(response[1]));
					jQuery("#" + response[3] + "_button").attr('disabled', false).css('opacity',1);
				}
				jQuery("#addon_" + response[3] + "_state").html(response[2]);
				jQuery("#addon_" + response[3] + "_state").attr('class', response[4]);
			}, 'json');
		}
	});

	if ( jQuery("#wpshop_billing_address_integrate_into_register_form_integrate-billing-form-into-register-form").is(":checked") ) {
		display_extra_options_for_address_integration();
	};
	jQuery("#wpshop_billing_address_integrate_into_register_form_integrate-billing-form-into-register-form").live('click', function(){
		display_extra_options_for_address_integration();
	});
	jQuery("#wpshop_billing_address_choice").live("change", function(){
		display_extra_options_for_address_integration();
	});

	function display_extra_options_for_address_integration() {
		if ( jQuery("#wpshop_billing_address_integrate_into_register_form_integrate-billing-form-into-register-form").is(":checked")) {
			var data = {
				action: "integrate_billing_into_register",
				wpshop_ajax_nonce: jQuery("#wpshop_ajax_integrate_billin_into_register").val(),
				selected_field: jQuery("#wpshop_include_billing_form_into_register_where_value").val(),
				current_billing_address: jQuery("#wpshop_billing_address_choice").val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				jQuery(".wpshop_include_billing_form_into_register_where").html(response);
			});
		}
		else {
			jQuery(".wpshop_include_billing_form_into_register_where").html("");
		}
	}

});
