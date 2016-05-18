jQuery( document ) .ready(function () {


	/**Ajax Form **/
	jQuery( document ).on( 'click', '#wps_save_credit_button', function() {
		jQuery('#wps_make_credit_form').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#save_credit_loader' ).show();
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		jQuery( '#wps_credit_list_container' ).html( response[1] );
	        		jQuery( '.tb-close-icon' ).click();

	        		/** POS Addon **/
	        		jQuery( '#wps_selected_order_shop_return' ).slideUp('slow', function() {
	        			jQuery( '#wps_selected_order_shop_return' ).html( response[1] );
	        			jQuery( '#wps_selected_order_shop_return' ).slideDown('slow');
	        		});
	        	}
	        	else {
	        		alert( response[1] );
	        		jQuery( '#save_credit_loader' ).hide();
	        	}
	        },
		}).submit();
	});


	jQuery( document ).on( 'change', '.wps_credit_change_status', function() {
		var id = jQuery( this ).attr('id');
		id = id.replace( 'credit_status_', '' );
		jQuery('#change_credit_status_loader').show();
		jQuery( '#wps_credit_list_container' ).fadeOut('slow');
		var data = {
				action: "wps_credit_change_status",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				order_id: jQuery("#post_ID").val(),
				credit_ref : id,
				selected_status : jQuery( this ).val()
			};
			jQuery.post(ajaxurl, data, function(response){
				if ( response["status"] )  {
					jQuery( '#wps_credit_list_container' ).html( response['response'] );
					jQuery( '#wps_credit_list_container' ).fadeIn('slow');
					jQuery('#change_credit_status_loader').hide();
				}
				else {
					jQuery('#change_credit_status_loader').hide();
					jQuery( '#wps_credit_list_container' ).fadeIn('slow');
				}
			}, "json");
	});


});
