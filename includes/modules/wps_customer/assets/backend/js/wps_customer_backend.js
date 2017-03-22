jQuery( document ).ready( function() {

	// Choose a customer in Order administration panel
	jQuery( document ).on( 'change', '#user_customer_id', function() {
		jQuery( '#wps_orders_selected_customer' ).val( jQuery( '#user_customer_id' ).chosen().val() );
		refresh_customer_informations_admin();
	});

	// Create a new customer in administration
	jQuery( document ).on( 'click', '#wps_signup_button', function() {
		jQuery( '#wps_signup_form' ).ajaxForm({
			dataType:  'json',
			beforeSubmit: function() {
				jQuery( '#wps_signup_button' ).addClass( 'wps-bton-loading' );
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		jQuery( '#TB_closeWindowButton' ).click();
	        		jQuery( '#wps_signup_button' ).removeClass( 'wps-bton-loading' );
	        		jQuery( '#wps_orders_selected_customer' ).val( response[2] );
	        		// Refresh User list
	        		jQuery( '#wps_customer_list_container' ).animate( { 'opacity': 0.15 }, 350 );
	        		var data = {
	        				action: 'wps_order_refresh_customer_list',
									_wpnonce: jQuery( '#wps_customer_list_container' ).data( 'nonce' ),
	        				customer_id: response[2]
	        			};
	        		jQuery.post( ajaxurl, data, function( return_data ) {
	        				if ( return_data['status'] ) {
	        					jQuery( '#wps_customer_list_container' ).html( return_data['response'] );
	        					jQuery( '#wps_customer_list_container' ).animate( { 'opacity': 1 }, 350, function() {
	        						jQuery( '#user_customer_id' ).chosen();
	        					});

	        				} else {
	        					alert( 'An error was occured...' );
	        					jQuery( '#wps_customer_list_container' ).animate( { 'opacity': 1 }, 350 );
	        				}
	        		}, 'json' );
	        		// Refresh address & account datas
	        		refresh_customer_informations_admin();
	        	} else {
	        		jQuery( '#wps_signup_error_container' ).html( response[1] );
	        		jQuery( '#wps_signup_button' ).removeClass( 'wps-bton-loading' );
	        	}
	        }
		}).submit();
	});
	setTimeout(function() {
 jQuery( window ).off( 'beforeunload.edit-post' );
 }, 1 );

	jQuery( document ).on( 'click', '#wps_account_form_button', function() {
		jQuery( this ).addClass( 'wps-bton-loading' );
	});

	update_selected_address_ids();

	/** Update Selected addresses ids **/
	function update_selected_address_ids() {
		/*if ( jQuery( '.wps_select_address' ).length == 0 ) {
			jQuery( '#wps_order_selected_address_billing' ).val( '' );
			jQuery( '#wps_order_selected_address_shipping' ).val( '' );
		}*/
		jQuery( '.wps_select_address' ).each( function(index, element) {
			if( jQuery( element ).prop( 'checked' ) ) {
				jQuery( '*[name="wps_order_selected_address[' + jQuery( element ).attr( 'name' ).substr( 0, jQuery( element ).attr( 'name' ).indexOf( '_address_id' ) ) + ']"]' ).val( jQuery( element ).val() );
			}
		} );
	}

	/**
	 * Refresh Customer inforations in order back-office panel
	 */
	function refresh_customer_informations_admin() {
		jQuery( '#wps_customer_account_informations' ).animate( { 'opacity': 0.15 }, 350 );
		jQuery( '#wps_customer_addresses' ).animate( { 'opacity': 0.15 }, 350 );
		var data = {
				action: 'wps_order_refresh_customer_informations',
				_wpnonce: jQuery( '#wps_customer_account_informations' ).data( 'nonce' ),
				customer_id: jQuery( '#wps_orders_selected_customer' ).val(),
				order_id: jQuery( '#post_ID' ).val()
			};
		jQuery.post( ajaxurl, data, function( response ) {
				if ( response['status'] ) {
					jQuery( '#wps_customer_account_informations' ).html( response['account'] );
					jQuery( '#wps_customer_account_informations' ).animate( { 'opacity': 1 }, 350 );

					jQuery( '#wps_customer_addresses' ).html( response['addresses'] );
					jQuery( '#wps_customer_addresses' ).animate( { 'opacity': 1 }, 350, function() {
						update_selected_address_ids();
					});
				} else {
					alert( 'An error was occured...' );
					jQuery( '#wps_selected_customer_informations' ).animate( {'opacity' : 1}, 350 );
				}
		}, 'json');
	}


});
