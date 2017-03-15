jQuery( document ).ready(function() {

   jQuery( '#shipping_mode_list_container' ).sortable();


   // Save payment configuration
	jQuery( document ).on( 'click', '.wps_save_payment_mode_configuration', function() {
		jQuery( this ).addClass( 'wps-bton-loading' );
		jQuery( '#TB_closeWindowButton' ).click();
		jQuery( '#wps_shipping_config_save_message' ).fadeIn( 'slow' );
		setTimeout( function() {
			jQuery( 'input[name="Submit"]' ).click();
		}, 500 );
	});

   jQuery( '.wps_shipping_mode_configuation_min_max' ).each(function() {
	   var id = jQuery( this ).attr( 'id' );
		id = id.replace( '_min_max_activate', '' );
		if ( jQuery( this ).is( ':checked' ) ) {
			jQuery( '#' + id + '_min_max_shipping_rules_configuration' ).slideDown( 'slow' );
		} else {
			jQuery( '#' + id + '_min_max_shipping_rules_configuration' ).slideUp( 'slow' );
		}
   });

	jQuery( document ).on( 'click', '.wps_shipping_mode_configuation_min_max', function() {
		var id = jQuery( this ).attr( 'id' );
		id = id.replace( '_min_max_activate', '' );
		if ( jQuery( this ).is( ':checked' ) ) {
			jQuery( '#' + id + '_min_max_shipping_rules_configuration' ).slideDown( 'slow' );
		} else {
			jQuery( '#' + id + '_min_max_shipping_rules_configuration' ).slideUp( 'slow' );
		}
	});

	 jQuery( '.activate_free_shipping_cost_from' ).each(function() {
		   var id = jQuery( this ).attr( 'id' );
			id = id.replace( '_free_shipping', '' );
			if ( jQuery( this ).is( ':checked' ) ) {
				jQuery( '#' + id + '_activate_free_shipping' ).slideDown( 'slow' );
			} else {
				jQuery( '#' + id + '_activate_free_shipping' ).slideUp( 'slow' );
			}
	   });

		jQuery( document ).on( 'click', '.activate_free_shipping_cost_from', function() {
			var id = jQuery( this ).attr( 'id' );
			id = id.replace( '_free_shipping', '' );
			if ( jQuery( this ).is( ':checked' ) ) {
				jQuery( '#' + id + '_activate_free_shipping' ).slideDown( 'slow' );
			} else {
				jQuery( '#' + id + '_activate_free_shipping' ).slideUp( 'slow' );
			}
		});


	/** Hide Notice Message **/
	jQuery( document ).on( 'click', '.wps_hide_notice_message', function() {
		var data = {
				action: 'wps_hide_notice_messages',
        _wpnonce: jQuery( this ).data( 'nonce' ),
				indicator: jQuery( '#hide_messages_indicator' ).val()
			};
			jQuery.post( ajaxurl, data, function( response ) {
				if ( response['status'] )  {
					jQuery( '#wpshop_shop_sale_type_notice' ).hide();
				}
			}, 'json' );
	});



	/**
	 * Add a logo to shipping mode
	 */
	jQuery( document ).on( 'click', '.add_logo_to_shipping_mode', function( e ) {
		e.preventDefault();
		var id = jQuery( this ).attr( 'id' );
		id = id.replace( 'add_logo_to_shipping_mode_', '' );
		jQuery( this ).addClass( 'wps-bton-loading' );
		// Open media gallery
		var uploader_category = wp.media({
					multiple: false
				}).on( 'select', function() {
					var selected_picture = uploader_category.state().get( 'selection' );
					var attachment = selected_picture.first().toJSON();

					jQuery( '#wps_shipping_mode_logo_' + id ).val( attachment.id );
					jQuery( '#wps_shipping_mode_logo_container_' + id ).html( '<img src="' + attachment.url + '" alt="" width="70"/>' );
				}).open();

		jQuery( '.add_logo_to_shipping_mode' ).removeClass( 'wps-bton-loading' );
	});

	jQuery( document ).on( 'change', '.wps_shipping_mode_active', function( e ) {
		radio = '#' + jQuery( this ).attr( 'id' ) + '_radio_default';
        if ( ! jQuery( this ).is( ':checked' ) ) {
        	jQuery( radio ).attr( 'disabled', 'disabled' );
        	jQuery( radio ).removeAttr( 'checked' );
        } else {
        	jQuery( radio ).removeAttr( 'disabled' );
        }
	});


	/**
	 * Create a new shipping mode
	 */
	jQuery( document ).on( 'click', '.wps_create_new_shipping_mode', function( e ) {
		e.preventDefault;
		jQuery( this ).addClass( 'wps-bton-loading' );
		jQuery( this ).attr( 'disabled', true );
		jQuery( '#wps_shipping_mode_list_container' ).addClass( 'wps-bloc-loading' );
		var data = {
			action: 'wps_add_new_shipping_mode',
	        _wpnonce: jQuery( this ).data( 'nonce' )
		};
		jQuery.post( ajaxurl, data, function( response ) {
			if ( response['status'] )  {
				jQuery( '#wps_shipping_mode_list_container > script' ).remove();
				jQuery( '#wps_shipping_mode_list_container' ).append( response['response'] );
				jQuery( '.wps_create_new_shipping_mode' ).addClass( 'wps-bton-loading' );
				jQuery( '.wps_create_new_shipping_mode' ).attr( 'disabled', false );
				jQuery( '#wps_shipping_mode_list_container' ).removeClass( 'wps-bloc-loading' );
			} else {
				alert( wps_an_error_occured );
				jQuery( '.wps_create_new_shipping_mode' ).addClass( 'wps-bton-loading' );
				jQuery( '.wps_create_new_shipping_mode' ).attr( 'disabled', false );
				jQuery( '#wps_shipping_mode_list_container' ).removeClass( 'wps-bloc-loading' );
			}
		}, 'json' );
	});

	/**
	 * Delete shipping mode
	 */
	jQuery( document ).on( 'click', '.wps_delete_shipping_mode', function( e ) {
		e.preventDefault();
		var btn = jQuery( this );
		var parent = btn.closest( '.wps_shipping_mode_container' );
		//Btn.addClass( 'wps-bton-loading' );
		var data = {
			action: 'wps_delete_shipping_mode',
			_wpnonce: btn.data( 'nonce' ),
			shipping_mode: parent.find( '[id^=\'wps_custom_shipping_mode_\']' ).attr( 'id' ).replace( '_shipping_configuration_interface', '' )
		};
		jQuery.post( ajaxurl, data, function( response ) {
			if ( response['success'] ) {
				parent.fadeOut(function() {
					parent.remove();
				});
			} else {
				alert( wps_an_error_occured );
			}
		}, 'json' );
	} );

	jQuery( 'div[id*=_shipping_rules_container]' ).sortable( {
		items: '.wps-table-content',
		stop: function( event, ui ) {
			var element = ui.item.data( 'element' ).split('|');
			reg = new RegExp(/{[^{]+}/g);
			var result;
			while((result = reg.exec(ui.item.closest( '.wps-boxed' ).find( 'textarea[id*=_wpshop_custom_shipping]' ).val())) !== null) {
				reg2 = new RegExp(/([a-z]+) ?: ?"(.+)"/g);
				var result2;
				var newVars = '';
				var matchDestination = false;
				while((result2 = reg2.exec(result)) !== null) {
					if( result2[1] == 'destination' && result2[2] == element[0] ) {
						jQuery( '.wps-table-content[data-element*=' + element[0] + ']' ).each( function( index, node ) {
							var result3 = jQuery( node ).data( 'element' ).split('|');
							newVars += ((matchDestination) ? ', ' : '') + result3[1] + ':' + result3[2];
							matchDestination = true;
						} );
					}
					if( matchDestination && result2[1] == 'fees' ) {
						ui.item.closest( '.wps-boxed' ).find( 'textarea[id*=_wpshop_custom_shipping]' ).text(
							ui.item.closest( '.wps-boxed' ).find( 'textarea[id*=_wpshop_custom_shipping]' ).val().replace( result2[2], newVars )
						);
					}
				}
			}
		}
	} );

	/* Save rule Action */
	jQuery( document ).on( 'click', '.save_rules_button', function() {
		var id_shipping_method = jQuery( this ).attr( 'id' );
		id_shipping_method = id_shipping_method.replace( '_save_rule', '' );
		jQuery( this ).addClass( 'wps-bton-loading' );
		var _wpnonce = jQuery( this ).data( 'nonce' );

		var selected_country = '';
		if ( jQuery( '#' + id_shipping_method + '_main_rule' ).is( ':checked' ) && jQuery( '#' + id_shipping_method + '_custom_shipping_active_cp' ).is( ':checked' ) ) {
			if ( jQuery( '#country_list' ).val() != 0 ) {
				selected_country = jQuery( '#' + id_shipping_method + '_country_list' ).val() + '-' + jQuery( '#' + id_shipping_method + '_main_rule' ).val();
			} else {
				alert( wps_options_country_choose_for_custom_fees );
			}
		} else if ( jQuery( '#' + id_shipping_method + '_custom_shipping_active_cp' ).is( ':checked' ) ) {
			if ( ( jQuery( '#' + id_shipping_method + '_country_list' ).val() != 0 ) && ( jQuery( '#' + id_shipping_method + '_postcode_rule' ).val() != null ) ) {
				selected_country = jQuery( '#' + id_shipping_method + '_country_list' ).val() + '-' + jQuery( '#' + id_shipping_method + '_postcode_rule' ).val();
			} else {
				alert( wps_options_country_postcode_choose_for_custom_fees );
			}
		} else if ( jQuery( '#' + id_shipping_method + '_custom_shipping_active_department' ).is( ':checked' ) && ( jQuery( '#' + id_shipping_method + '_department_rule' ).val() != '' ) ) {
			selected_country = jQuery( '#' + id_shipping_method + '_country_list' ).val() + '-'+jQuery('#' + id_shipping_method + '_department_rule').val();
		}
		else if( jQuery("#" + id_shipping_method + "_main_rule").is(':checked') ) {
			selected_country = jQuery("#" + id_shipping_method + "_main_rule").val();
		}
		else {
			selected_country = jQuery("#" + id_shipping_method + "_country_list").val();
		}

		if ((jQuery("#" + id_shipping_method + "_weight_rule").val() != '') && (jQuery("#" + id_shipping_method + "_shipping_price").val() != '')) {
			var data = {
				action: "save_shipping_rule",
				_wpnonce: _wpnonce,
				weight_rule : jQuery("#" + id_shipping_method + "_weight_rule").val(),
				shipping_price : jQuery("#" + id_shipping_method + "_shipping_price").val(),
				selected_country : selected_country,
				fees_data : jQuery("#" + id_shipping_method + "_wpshop_custom_shipping").val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery("#" + id_shipping_method + "_wpshop_custom_shipping").val( response['reponse'] );
					refresh_shipping_rules_display( id_shipping_method );
					jQuery("#" + id_shipping_method + "_country_list").val(0);
					jQuery("#" + id_shipping_method + "_shipping_price").val('');
					jQuery("#" + id_shipping_method + "_weight_rule").val('');
					jQuery("#" + id_shipping_method + "_main_rule").removeAttr("checked");

					jQuery( '.save_rules_button' ).removeClass( 'wps-bton-loading' );
				}
				else {
					jQuery( '.save_rules_button' ).removeClass( 'wps-bton-loading' );
				}

			}, 'json');
		}
		else {
			alert( wps_options_shipping_weight_for_custom_fees );
			jQuery( '.save_rules_button' ).removeClass( 'wps-bton-loading' );
		}
	});



	/** Delete Rule **/
	jQuery(document).on('click', '.delete_rule', function( e ) {
		e.preventDefault();
		var id = jQuery(this).attr('title');
		jQuery("#" + id + "_shipping_rules_container").addClass( 'wps-bloc-loading' );
		var data = {
				action: "delete_shipping_rule",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				country_and_weight: jQuery(this).attr('id'),
				fees_data : jQuery("#" + id + "_wpshop_custom_shipping").val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery("#" + id + "_wpshop_custom_shipping").val( response['reponse'] );
					refresh_shipping_rules_display( id );
					jQuery("#" + id + "_shipping_rules_container").removeClass( 'wps-bloc-loading' );
				}
				else {
					jQuery("#" + id + "_shipping_rules_container").removeClass( 'wps-bloc-loading' );
				}


			}, 'json');
	});



	function refresh_shipping_rules_display( id ) {
		jQuery("#" + id + "_shipping_rules_container").addClass( 'wps-bloc-loading' );
		var data = {
			action: "display_shipping_rules",
			_wpnonce: jQuery( '#' + id + '_shipping_rules_container' ).data( 'nonce' ),
			fees_data : jQuery("#" + id + "_wpshop_custom_shipping").val(),
			shipping_mode_id : id
		};
		jQuery.post(ajaxurl, data, function(response) {
			if ( response['status'] ) {
				jQuery("#" + id + "_shipping_rules_container").html( response['reponse'] );
				jQuery("#" + id + "_shipping_rules_container").removeClass( 'wps-bloc-loading' );
			}
			else {
				jQuery("#" + id + "_shipping_rules_container").removeClass( 'wps-bloc-loading' );
			}
		}, 'json');

	}



	checked_active_custom_fees();

	jQuery( document ).on( 'click', '.active_postcode_custom_shipping', function() {
		checked_active_custom_fees();
	});
	jQuery( document ).on( 'click', '.active_department_custom_shipping', function() {
		checked_active_custom_fees();
	});

	function checked_active_custom_fees() {
		if ( jQuery('.active_postcode_custom_shipping').is(':checked') ) {
			jQuery( '.postcode_rule' ).fadeIn( 'slow' );
		}
		else {
			jQuery( '.postcode_rule' ).fadeOut( 'slow' );
		}
	}
	function checked_active_custom_fees() {
		/** Postcode **/
		if ( jQuery('.active_postcode_custom_shipping').is(':checked') ) {
			jQuery( '.postcode_rule' ).fadeIn( 'slow' );
		}
		else {
			jQuery( '.postcode_rule' ).fadeOut( 'slow' );
		}
		/** Department **/
		if ( jQuery('.active_department_custom_shipping').is(':checked') ) {
			jQuery( '.department_rule' ).fadeIn( 'slow' );
		}
		else {
			jQuery( '.department_rule' ).fadeOut( 'slow' );
		}
	}
});
