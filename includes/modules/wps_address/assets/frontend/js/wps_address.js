jQuery( document ).ready( function() {

	if ( ( jQuery( '#wps-shipping_to_billing' ).length > 0 ) && jQuery( '#wps-shipping_to_billing' ).is( ':checked' ) ) {
		jQuery( '.wps-billing-address' ).hide();
	}

	if ( jQuery( '#wps_submit_address_form' ).length || jQuery( '.wps_submit_address_form' ).length ) {
		jQuery( '#wps-checkout-valid-step-three' ).hide();
	}

	jQuery( document ).on( 'click', '#wps-shipping_to_billing', function() {
		if ( jQuery( '#wps-shipping_to_billing' ).is( ':checked' ) ) {
			jQuery( '.wps-billing-address' ).slideUp( 'slow' );
		} else {
			jQuery( '.wps-billing-address' ).slideDown( 'slow' );
		}
	});

	jQuery( '#wps_address_form_save_first_address' ).ajaxForm({
		dataType:  'json',
		beforeSubmit: function( formData, jqForm, options ) {
			jqForm.find( 'button' ).addClass( 'wps-bton-loading' );
		},
		success: function( response, statusText, xhr, $form ) {
			$form.find( 'button' ).removeClass( 'wps-bton-loading' );
			if ( response[0] ) {
				jQuery( '.wps-address-first-address-creation-container' ).slideUp( 'slow' ).remove();
				jQuery( '.wps-add-an-address' ).removeClass( 'hidden' );

				if ( response[1] ) {
					jQuery( '#wps-address-container-' + response[2] ).html( response[1] );
				}

				if ( response[3] != '' ) {
					reload_address_container( response[3], '' );
					if ( jQuery( '#wps-checkout-valid-step-three' ).length ) {
						jQuery('#wps-checkout-valid-step-three').slideDown( "slow" );
					}
				} else if ( jQuery('#wps-checkout-valid-step-three').length ) {
					jQuery('#wps-checkout-valid-step-three').show().click();
				}
			} else {
				jQuery('#wps_address_error_container').html( response[1] );
			}
		}
	});

	jQuery( document ).on( 'click', '#wps_submit_address_form', function() {
		/** Ajax Form Address Save **/
		jQuery('#wps_address_form_save').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#wps_submit_address_form' ).addClass( 'wps-bton-loading' );
			},
      success: function( response ) {
      	if ( response[0] ) {
      		jQuery( '.wpsjq-closeModal').click();
      		jQuery( '#wps_submit_address_form' ).removeClass( 'wps-bton-loading' );

      		reload_address_container( response[2], '' );
      		if( response[3] != null && response[3] != "" ) {
      			reload_address_container( response[3], '' );
      			setTimeout(function() {
      				var height_tab = parseFloat( jQuery( '#wps-address-container-' + response[2] + ' .wps-adresse-listing-select').height() );
      				jQuery( '#wps-address-container-' + response[3] + ' .wps-adresse-listing-select').height( height_tab );
      			}, 5000);
      		}
      	}
      	else {
      		jQuery('#wps_address_error_container').html( response[1] );
      		jQuery( '#wps_submit_address_form' ).removeClass( 'wps-bton-loading' );
      	}
      },
		});
	});

	/** Add an address **/
	jQuery( document ).on( 'click', '.wps-add-an-address', function(e) {
		e.preventDefault();
		var address_infos = jQuery( this ).attr( 'id' ).replace( 'wps-add-an-address-', '');
		jQuery( this ).addClass( 'wps-bton-loading');
		address_infos = address_infos.split( '-' );
		var data = {
				action: "wps_load_address_form",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				address_type_id : address_infos[0]
			};
			jQuery.post(ajaxurl, data, function(response) {
				fill_the_modal( response[1], response[0], '' );
				jQuery( '.wps-add-an-address').removeClass( 'wps-bton-loading');
			}, 'json');
	});

	/** Edit an address **/
	jQuery( document ).on( 'click', '.wps-address-edit-address', function(e) {
		e.preventDefault();
		var address_id = jQuery( this ).attr( 'id' ).replace( 'wps-address-edit-address-', '' );
		jQuery( this ).closest( 'li' ).addClass( 'wps-bloc-loading' );
		var data = {
			action: "wps_load_address_form",
			_wpnonce: jQuery( this ).data( 'nonce' ),
			address_id :  address_id,
			address_type_id: jQuery( this ).data( "address_type" )
		};
		jQuery.post( ajaxurl, data, function(response) {
			fill_the_modal( response[1], response[0], '' );
			jQuery( '.wps-address-edit-address' ).closest( 'li' ).removeClass( 'wps-bloc-loading' );
		}, 'json' );
	});

	/** Delete an address */
	jQuery( document ).on( 'click', '.wps-address-delete-address', function(e){
		e.preventDefault();
		if( confirm(WPSHOP_CONFIRM_DELETE_ADDRESS) ) {
		var address_infos = jQuery( this ).attr( 'id' ).replace( 'wps-address-delete-address-', '' );
		address_infos = address_infos.split( '-' );
		var data = {
				action: "wps_delete_an_address",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				address_id :  address_infos[0]
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					reload_address_container( address_infos[1], '' );
				}

			}, 'json');
		}
	});


	jQuery( document ).on( 'click', '.wps-bloc-loader', function() {
		jQuery(this).parent().children( 'li' ).removeClass( 'wps-activ' );
		jQuery(this).addClass( 'wps-activ' );
		jQuery(this).find( 'input[type=radio]:first' ).prop('checked', true);
	});

	function reload_address_container( address_type, address_id  ) {
		var data = {
				action: "wps_reload_address_interface",
				_wpnonce: jQuery( '#wps-address-container-' + address_type ).data( 'nonce' ),
				address_id :  address_id,
				address_type : address_type
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery( '#wps-address-container-' + address_type ).animate({'opacity' : 0.1}, 350, function() {
						jQuery( '#wps-address-container-' + address_type ).html( response['response'] );
						jQuery( '#wps-address-container-' + address_type ).animate({'opacity' : 1}, 350, function() {
							wp_select_adresses( '.wps-change-adresse');
							jQuery('.wps-billing-address').slideDown( 'slow' );
							jQuery( '.wps_address_use_same_addresses' ).fadeOut();
						});
					});
				}

			}, 'json');
	}

});
