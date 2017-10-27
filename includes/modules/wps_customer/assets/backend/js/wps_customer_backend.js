jQuery( document ).ready( function() {

	jQuery( '#wps-customer-due-amount-compil' ).click( function( event ) {
		var data = {
			action: 'wps-customer-due-amount-compil'
		};
		event.preventDefault();
		jQuery.post( ajaxurl, data, function( response ) {
			if ( response.success ) {
				window.location.reload();
			}
		});
	} );

	jQuery( '#wpshop_order_customer_information_box h2 span .page-title-action.thickbox' ).click( function( event ) {
		event.preventDefault();

		tb_show( jQuery( this ).html(), jQuery( this ).attr( 'href' ) );
	} );

	jQuery( ".wps-customer-name-container" ).hover( function( event ){
		event.preventDefault();

		jQuery( this ).children( ".wps-customer-contact-list-actions" ).toggle();
	});

	// Choose a customer in Order administration panel
	jQuery( document ).on( 'change', '#user_customer_id', function() {
		jQuery( '#wps_orders_selected_customer' ).val( jQuery( '#user_customer_id' ).val() );
		refreshCustomerInformationsInOrders( function() {
			jQuery( '#user_customer_id' ).chosen();
		} );
	} );

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
					jQuery( '#wps_orders_selected_customer' ).val( response[3] );

					// Refresh customer informations metabox into order.
					refreshCustomerInformationsInOrders();
				} else {
					jQuery( '#wps_signup_error_container' ).html( response[1] );
					jQuery( '#wps_signup_button' ).removeClass( 'wps-bton-loading' );
				}
			}
		}).submit();
	});

	jQuery( document ).on( 'change', '.wpshop-admin-post-type-wpshop_customers .wps-form-group input,.wpshop-admin-post-type-wpshop_customers .wps-form-group select,.wpshop-admin-post-type-wpshop_customers .wps-form-group textarea', function() {
		jQuery( window ).on( 'beforeunload.edit-post', function() {
			return true;
		});
	});

	jQuery( document ).on( 'click', '#wps_account_form_button', function() {
		jQuery( this ).addClass( 'wps-bton-loading' );
	});

	updateSelectedAddressesIDS();

	/** Update Selected addresses ids **/
	function updateSelectedAddressesIDS() {
		jQuery( '.wps_select_address' ).each( function( index, element ) {
			if ( jQuery( element ).prop( 'checked' ) ) {
				jQuery( '*[name="wps_order_selected_address[' + jQuery( element ).attr( 'name' ).substr( 0, jQuery( element ).attr( 'name' ).indexOf( '_address_id' ) ) + ']"]' ).val( jQuery( element ).val() );
			}
		} );
	}

	/**
	 * Refresh Customer inforations in order back-office panel
	 */
	function refreshCustomerInformationsInOrders( callback ) {
		var data = {
			action: 'wps_order_refresh_customer_informations',
			_wpnonce: jQuery( '#wps_customer_account_informations' ).data( 'nonce' ),
			customer_id: jQuery( '#wps_orders_selected_customer' ).val(),
			order_id: jQuery( '#post_ID' ).val()
		};

		jQuery( '#wpshop_order_customer_information_box .inside' ).addClass( 'wps-bloc-loader wps-bloc-loading' );
		jQuery.post( ajaxurl, data, function( response ) {
			if ( response.status ) {
				jQuery( '#wpshop_order_customer_information_box .inside' ).html( response.output );
				updateSelectedAddressesIDS();
				if ( typeof callback !== 'undefined' ) {
					callback();
				}
			} else {
				alert( 'Error #CustomerBackJS94' );
			}
			jQuery( '#wpshop_order_customer_information_box .inside' ).removeClass( 'wps-bloc-loader wps-bloc-loading' );
		}, 'json' );
	}

});
