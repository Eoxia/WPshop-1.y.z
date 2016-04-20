jQuery(document).ready(function () {
	

	jQuery.address.init(function(event) {
		
	}).bind('change', function(event) {
		load_customer_account_section ( event.value );
	}); 
	
	/** On nav **/
	jQuery('#secondary').on('click', 'li', function() {
		jQuery.address.value( jQuery(this).attr('id') );
		return false;
	});
	
	/** On addresses section buttons **/
	jQuery('#wps_customer_account_dashboard_content').on('click', '.add_new_address', function() {
		jQuery.address.value( jQuery(this).attr('id') );
		return false;
	});
	
	/** Ajax Form Options **/
	var options_register = {
			dataType:  'json',
			success: show_Response 
	}; 

	
	
	
	function load_customer_account_section ( section ) {
		if (section == null ) {
			section = 'account_informations';
		}
		jQuery('#wps_customer_account_dashboard_content').fadeOut();
		var data = {
				action: "display_account_dashboard_section",
				dashboard_section : section, 
				from_front : true 
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery('#wps_customer_account_dashboard_content').html( response['response'] );
					jQuery('#wps_customer_account_dashboard_content').fadeIn();
				}
				jQuery('#register_form').ajaxForm(options_register);
				jQuery('#billingAndShippingForm').ajaxForm(options_register);
			}, 'json');
	}
	
	function show_Response(responseText, statusText, xhr, $form)  {
		if(responseText['status']) {
			
			jQuery('#reponseBox').fadeOut('slow');
			jQuery('#reponseBox').html(responseText['reponse']);
			jQuery('#reponseBox').fadeIn('slow');
		}
		else {
			jQuery('#reponseBox').hide().html(responseText['reponse']).fadeIn('slow');
			back2Element(jQuery('#reponseBox'));
		}
	}
	
	function back2Element(element) {
		// On remonte en haut de page
		var offset = element.offset();
		jQuery('html, body').animate({ scrollTop: offset.top }, 800);
	}
	
});