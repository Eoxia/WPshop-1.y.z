jQuery( document ).ready(function() {
	/** AutoFocus search */
	jQuery( 'input[name=\'wps-pos-product-to-choose\']' ).focus();

	/** Save state if search only barcode */
	jQuery( 'input[name=wps-pos-search-in]' ).change( function() {
		/** Save state on db without return */
		var checkbox = 'unchecked';
		if ( jQuery( 'input[name=wps-pos-search-in]' ).is( ':checked' ) ) {

			checkbox = 'checked';
		}
		var data = {
			action: 'wpspos_save_config_barcode_only',
			_wpnonce: jQuery( 'input[name=wps-pos-search-in]' ).data( 'nonce' ),
			value_checkbox: checkbox
		};
		jQuery.post( ajaxurl, data, function() {}, 'json' );
	});

	/** SESSION: is_quotation */
	//Default state
	var data = {
		action: 'wpspos_state_is_quotation',
		value_checkbox: 'unchecked'
	};

	jQuery.post( ajaxurl, data, function() {}, 'json' );

	jQuery( 'input[name=wpspos-is-quotation]' ).attr( 'checked', false );
	//Save state on session
	jQuery( 'input[name=wpspos-is-quotation]' ).change( function() {
		var checkbox = 'unchecked';
		if ( jQuery( 'input[name=wpspos-is-quotation]' ).is( ':checked' ) ) {
			checkbox = 'checked';
		}
		var data = {
			action: 'wpspos_state_is_quotation',
			value_checkbox: checkbox
		};
		jQuery.post( ajaxurl, data, function() {}, 'json' );
	});

	/** SESSION: is_receipt */
	//Default state
	var data = {
		action: 'wpspos_state_is_receipt',
		value_checkbox: 'unchecked'
	};
	jQuery.post( ajaxurl, data, function() {}, 'json' );
	jQuery( 'input[name=wpspos-is-receipt]' ).attr( 'checked', false );
	//Save state on session
	jQuery( 'input[name=wpspos-is-receipt]' ).change( function() {
		var checkbox = 'unchecked';
		if ( jQuery( 'input[name=wpspos-is-receipt]' ).is( ':checked' ) ) {
			checkbox = 'checked';
		}
		var data = {
			action: 'wpspos_state_is_receipt',
			value_checkbox: checkbox
		};
		jQuery.post( ajaxurl, data, function() {}, 'json' );
	});

	/** Add folded class to admin menu on POS page	*/
	setTimeout( function() {
		jQuery( 'body' ).addClass( 'folded' );
	}, 1500 );

	/** Trigger event on alphabet letters for element search */
	jQuery( '.wps-pos-dashboard-wrap' ).on( 'click', '.wps-pos-letter-choice', function( event ) {
		event.preventDefault();

		if ( jQuery( this ).hasClass( 'wps-bton-first-rounded' ) || jQuery( this ).hasClass( 'wps-bton-third-rounded' ) || ( jQuery( this ).hasClass( 'wps-bton-second-rounded' ) && confirm( wpspos_sure_to_load_element_having_nothing ) ) ) {
			/**	Empty search field	*/
			jQuery( this ).closest( '.inside' ).children( '.wps-pos-element-metabox-selection' ).children( 'input' ).val( '' );

			/**	Change current button color	*/
			jQuery( this ).closest( '.wps-pos-alphabet-container' ).children( 'button.wps-bton-third-rounded' ).each( function() {
				jQuery( this ).removeClass( 'wps-bton-third-rounded' );
				jQuery( this ).addClass( 'wps-bton-first-rounded' );
			});
			jQuery( this ).toggleClass( 'wps-bton-first-rounded wps-bton-third-rounded' );

			/**	First start by adding the loading class	*/
			jQuery( '.wps-pos-' + jQuery( this ).attr( 'data-type' ) + '-listing' ).addClass( 'wps-bloc-loading' );

			/**	Launch an ajax request for displaying	*/
			var data = {
				action: 'wpspos_load_element_from_letter',
				_wpnonce: jQuery( this ).data( 'nonce' ),
				element_type: jQuery( this ).attr( 'data-type' ),
				term: jQuery( this ).attr( 'data-id' )
			};
			jQuery.post( ajaxurl, data, function( response ) {
				jQuery( '.wps-pos-' + response[ 'element_type' ] + '-listing' ).html( response[ 'output' ] );
				jQuery( '.wps-pos-' + response[ 'element_type' ] + '-listing' ).removeClass( 'wps-bloc-loading' );
			}, 'json' );
		}
	});

	/**	Trigger event on search input */
	jQuery( '.wps-pos-element-metabox-selection input' ).focus( function() {
		jQuery( this ).select();
	} );


/**
 * Customers' actions
 */
	/**	Trigger event on customer choice button	*/
	jQuery( document ).on( 'click', '.wps-pos-addon-choose-customer, .wps-pos-addon-customer-line', function( event ) {
		event.preventDefault();

		/**	First start by adding the loading class	*/
		jQuery( '.wps-pos-customer-listing' ).addClass( 'wps-bloc-loading' );

		/**	Launch an ajax request for displaying	*/
		wpspos_set_customer_for_order( jQuery( this ).attr( 'data-id' ), jQuery( this ).data( 'nonce' ) );
	});

	/**	Trigger event on change customer button */
	jQuery( '.wps-pos-dashboard-wrap' ).on( 'click', '#wps-pos-change-customer', function( event ) {
		event.preventDefault();

		jQuery( '.wpspos-dashboard-contents' ).addClass( 'wpspos-current-step-1' );
		jQuery( '.wpspos-dashboard-contents' ).removeClass( 'wpspos-current-step-2' );
	});

	/**	Trigger event on customer search input	*/
	var customer_search = null;
	jQuery( 'input[name=wps-pos-customer-to-choose]' ).keyup( function() {
		if ( customer_search != null ) customer_search.abort();
		if ( 1 <= jQuery( this ).val().length ) {
			jQuery( '.wps-pos-customer-listing' ).addClass( 'wps-bloc-loading' );
			jQuery( '#wpspos-dashboard-customer-metabox' ).children( '.wps-pos-alphabet-container' ).children( 'button' ).each( function() {
				jQuery( this ).removeClass( 'wps-bton-third-rounded' ).addClass( 'wps-bton-first-rounded' );
			});
			jQuery( '#wpspos-dashboard-customer-metabox' ).children( '.wps-pos-alphabet-container' ).children( 'button:first-child' ).removeClass( 'wps-bton-first-rounded' ).addClass( 'wps-bton-third-rounded' );
			var data = {
				action: 'wpspos-customer-search',
				_wpnonce: jQuery( this ).data( 'nonce' ),
				term: jQuery( this ).val()
			};
			customer_search = jQuery.post( ajaxurl, data, function( response ) {
				jQuery( '.wps-pos-customer-listing' ).html( response );
				jQuery( '.wps-pos-customer-listing' ).removeClass( 'wps-bloc-loading' );
			});
		}
	});

	/**	Trigger event on button for continuing using already selected customer	*/
	jQuery( '.wps-pos-dashboard-wrap' ).on( 'click', '.wpspos-continue-using-selected-customer', function( event ) {
		event.preventDefault();

		jQuery( '.wpspos-dashboard-contents' ).addClass( 'wpspos-current-step-2' );
		jQuery( '.wpspos-dashboard-contents' ).removeClass( 'wpspos-current-step-1' );
	});


/**
 * Products' actions
 */
	/**	Trigger event on add product to cart button */
	jQuery( '.wps-pos-dashboard-wrap' ).on( 'click', '.wps-pos-addon-product-line', function( event ) {
		event.preventDefault();

		/**	First start by adding the loading class	*/
		jQuery( '.wps-pos-product-listing' ).addClass( 'wps-bloc-loading' );

		/**	Get the current produt identifier	*/
		var product_title = jQuery( this ).find( 'td:first' ).html().split( '<br>' )[0];
		var product_id = jQuery( this ).data( 'id' );
		var product_type = jQuery( this ).data( 'subtype' );
		var _wpnonce = jQuery( this ).data( 'nonce' );

		/** Check if it's a product with variation */
		if ( 'variations' == product_type ) {
			/** Open the modal to select the variation **/
			tb_show( product_title, ajaxurl + '?action=wps-pos-product-variation-selection&_wpnonce=' + _wpnonce + '&product_id=' + product_id + '&width=350px' );
			/**	First start by adding the loading class	*/
			jQuery( '.wps-pos-product-listing' ).removeClass( 'wps-bloc-loading' );
		} else {
			/** Add product to cart **/
			wps_pos_add_simple_product_to_cart( product_id, _wpnonce );
		}
	});
	/**	Trigger event on add product with variation to order button	*/
	jQuery( document ).on( 'click', '#wpspos-product-with-variation-add-to-cart', function() {
		var form_options_add_to_cart = {
			dataType: 'json',
			beforeSubmit: function( formData, jqForm, options ) {
				/**	First start by adding the loading class	*/
				jQuery( '.wps-pos-product-listing' ).addClass( 'wps-bloc-loading' );
			},
			success: function( responseText, statusText, xhr, $form ) {
				if ( responseText[0] ) {
					wps_pos_addon_refresh_cart();
					jQuery( '#TB_closeWindowButton' ).click();
				} else {
					jQuery( '.wps-pos-product-selection-alert-box' ).html( responseText[1] ).addClass( 'wps-alert-error' ).show();
					setTimeout(function() {
						jQuery( '.wps-pos-product-selection-alert-box' ).fadeOut( 'slow', function() {
							jQuery( '.wps-pos-product-selection-alert-box' ).html( '' );
							jQuery( '.wps-pos-product-selection-alert-box' ).hide();
						});
		            }, 2500 );
				}

				/**	At last remove the loading class	*/
				jQuery( '.wps-pos-product-listing' ).removeClass( 'wps-bloc-loading' );
			}
		};

		/**	Submit form for product variation selection	*/
		jQuery( '#wpshop_add_to_cart_form' ).ajaxForm( form_options_add_to_cart );
		jQuery( '#wpshop_add_to_cart_form' ).submit();
	});

	/**	Trigger event on product search input	*/
	var product_search = null;
	jQuery( '#wpspos-dashboard-product-metabox' ).on( 'click', '#wpspos-product-search', function( event ) {
		if ( product_search != null ) product_search.abort();

		search_product( event );
	});
	jQuery( '#wpspos-dashboard-product-metabox' ).on( 'keyup', 'input[name=wps-pos-product-to-choose]', function( event ) {
		if ( product_search != null ) product_search.abort();

		var code = event.keyCode || event.which;
		if ( 13 == code ) {
			search_product( event );
		}
	});

/**
 * Order's actinos
 */
	/**	Trigger event on quantity button	*/
	jQuery( "#wps_cart_container" ).on( "click", ".item_qty", function(){
		var _wpnonce = jQuery( this ).data( "nonce" );
		var product_id = jQuery( this ).data( "id" );
		var qty = jQuery( '#item_qty_' + product_id ).val();
		if ( jQuery( this ).data( "action" ) == 'increase' ) {
			qty = parseInt(qty) + 1;
		}
		else {
			qty = parseInt(qty) - 1;
		}
		if ( ( 0 < qty ) || confirm( wpspos_confirm_product_deletion_from_order ) ) {
			jQuery("#wps_cart_container").addClass( 'wps-bloc-loading' );
			jQuery( '#item_qty_' + product_id ).val( qty );
			updateQty( product_id, qty, _wpnonce );
		}
	});
	jQuery( "#wps_cart_container" ).on( "blur", ".wpspos-dashboard-order-summary_qty", function(){
		var _wpnonce = jQuery( this ).data( "nonce" );
		if ( !jQuery( this ).is( "[readonly]" ) ) {
			jQuery("#wps_cart_container").addClass( 'wps-bloc-loading' );
			var product_id = jQuery( this ).data( "id" );
			var qty = jQuery( this ).val();
			updateQty( product_id, qty, _wpnonce );
		}
	});

	/**	Trigger event on delete product from order	*/
	jQuery( "#wps_cart_container" ).on( "click", ".wps-pos-delete-product-of-order", function(){
		var _wpnonce = jQuery( this ).data( "nonce" );
		jQuery("#wps_cart_container").addClass( 'wps-bloc-loading' );
		var product_id = jQuery( this ).data( "id" );
		updateQty( product_id, 0, _wpnonce );
	});

	/**	Trigger event on payment method choice	*/
	jQuery( document ).on( "click", ".wpspos-order-payment-method li label", function(){
		jQuery( this ).closest( "ul" ).children( "li" ).each( function(){
			jQuery( this ).removeClass( "wpspos-selected-payment-method" );
		});
		jQuery( this ).closest( "li" ).addClass( "wpspos-selected-payment-method" );
		display_money_cash_back();
	});

	/**	Trigger event on order payment cancel button	*/
	jQuery( document ).on( "click", "#wpspos-cancel-order-cash", function( event ){
		event.preventDefault();
		jQuery( "#TB_closeWindowButton" ).click();
	});

	/**	Trigger event on order payment amount	*/
	jQuery( document ).on( "click", "#wpspos-customer-paid-full-amount", function( event ){
		if ( !jQuery( this ).is( ":checked" ) ) {
			jQuery('#wpspos-received-amount-container').fadeIn( 'slow' );
		}
		else {
			jQuery('#wpspos-received-amount-container').fadeOut( 'slow' );
		}
	});

	/**	Trigger event on received amount input	*/
	jQuery( document ).on( "keyup", ".wpspos-order-received-amount", function() {
		display_money_cash_back();
	});

	jQuery( document ).on( 'click', '.wpspos_select_address', function() {
		jQuery( this ).closest( 'ul' ).children( 'li' ).removeClass( 'wps-activ' );
		jQuery( this ).addClass( 'wps-activ' );
		jQuery( this ).find( '.wps_select_pos_address' ).prop( "checked", true );

		// Update data
		/*var type = jQuery( this ).attr( 'name' ).replace( '_address_id', '' );
		jQuery( '#wps_order_selected_address_' + type ).val( jQuery( this ) .val() );*/
	});

	jQuery( document ).on( 'click', ".lnk_load_order", function(e){
		e.preventDefault();
		order_id = this.dataset.oid;
		customer_id = this.dataset.cid;
		this_element = jQuery( this );
		jQuery.post( ajaxurl, {action:'wps-pos-display-order-content', order_id:order_id, _wpnonce: this_element.data( 'display-nonce' )} , function( response ) {
			jQuery("#wps_cart_container").html( response );
			jQuery.post( ajaxurl, {action:'wpspos-finish-order', _wpnonce: this_element.data( 'finish-nonce' ), order_id:order_id, customer_id:customer_id} , function( responseJson ) {
				if ( true == responseJson.status ) {
					jQuery( ".wpspos-order-final-step-container" ).html( responseJson.output );
					jQuery( "#wps_cart_container input[type=text]" ).each( function(){
						this_element.prop( "readonly", true );
				    });
					jQuery( ".wpspos-dashboard-contents" ).removeClass( "wpspos-current-step-2" );
					jQuery( ".wpspos-dashboard-contents" ).addClass( "wpspos-current-step-3" );
			    }
			    else {
				    jQuery( "#wps-pos-order-content-alert" ).html( responseText[ 'message' ] );
			    	jQuery( "#wps-pos-order-content-alert" ).show().addClass( "wps-alert-error" );
			    }
			}, 'json');
		});
	});

	jQuery( document ).on( 'click', '.toggle-historic', function(e) {
		e.preventDefault();
		if( jQuery( this ).hasClass('dashicons-arrow-down') ) {
			toremove = 'dashicons-arrow-down';
			toadd = 'dashicons-arrow-up';
		} else {
			toadd = 'dashicons-arrow-down';
			toremove = 'dashicons-arrow-up';
		}
		jQuery( this ).removeClass( toremove );
		jQuery( this ).addClass( toadd );
		jQuery( ".toggle-historic-group" ).toggle();
	});
});


function search_product( event ) {
	var where_to_search = "";
	if ( ( "undefined" != jQuery( "input[name=wps-pos-search-in]" ).val() ) && ( jQuery( "input[name=wps-pos-search-in]" ).is( ":checked" ) ) ) {
		where_to_search = jQuery( "input[name=wps-pos-search-in]" ).val();
	}

	/**	Disable the field while treatment have not been completly done	*/
	//jQuery( "input[name=wps-pos-product-to-choose]" ).prop( "disabled", true );

	jQuery( ".wps-pos-product-listing" ).addClass( "wps-bloc-loading" );
	jQuery( "#wpspos-dashboard-product-metabox" ).children( ".wps-pos-alphabet-container" ).children( "button" ).each( function(){
		jQuery( this ).removeClass( "wps-bton-third-rounded" ).addClass( "wps-bton-first-rounded" );
	});
	jQuery( "#wpspos-dashboard-product-metabox" ).children( ".wps-pos-alphabet-container" ).children( "button:first-child" ).removeClass( "wps-bton-first-rounded" ).addClass( "wps-bton-third-rounded" );
	var data = {
		action: "wpspos-product-search",
		_wpnonce: jQuery( "input[name=wps-pos-product-to-choose]" ).data( 'nonce' ),
		term: jQuery( "input[name=wps-pos-product-to-choose]" ).val(),
		search_in: where_to_search,
	};

	product_search = jQuery.post( ajaxurl, data, function( response ){
		if ( response[ 'status' ] ) {
			if ( "direct_to_cart" == response[ 'action' ] ) {
				jQuery("#wps_cart_container").addClass( 'wps-bloc-loading' );
				wps_pos_add_simple_product_to_cart( response[ 'output' ], response[ '_wpnonce'] );
			}
			else if ( "variation_selection" == response[ 'action' ] )  {
				/** Open the modal to select the variation **/
				tb_show( wps_pos_product_with_variation_box, ajaxurl + "?action=wps-pos-product-variation-selection&_wpnonce=" + response[ '_wpnonce' ] + "&product_id=" + response[ 'output' ] + "&width=350px");
			}
			else {
				jQuery( ".wps-pos-product-listing" ).html( response[ 'output' ] );
			}
			jQuery( ".wps-pos-product-listing" ).removeClass( "wps-bloc-loading" );
		}
		else {
			jQuery( ".wps-pos-product-listing" ).html( response[ 'output' ] );
			jQuery( ".wps-pos-product-listing" ).removeClass( "wps-bloc-loading" );
		}
	}, 'json');
}

/**
 * Add a simple product to order
 *
 * @param product_id The product identifier to add to the cart
 */
function wps_pos_add_simple_product_to_cart( product_id, _wpnonce ) {
	var data = {
		action: "wpshop_add_product_to_cart",
		_wpnonce: _wpnonce,
		wpshop_cart_type : 'cart',
		wpshop_pdt: product_id
	};
	jQuery.post( ajaxurl, data, function(response) {
		if ( response[0] ) {
			wps_pos_addon_refresh_cart();
		}
		else {
			jQuery('.wps-pos-product-selection-alert-box').html( response[1] ).addClass( 'wps-alert-error' ).show();
			setTimeout(function(){
				jQuery('.wps-pos-product-selection-alert-box').fadeOut( 'slow', function(){
					jQuery('.wps-pos-product-selection-alert-box').html( '' );
					jQuery('.wps-pos-product-selection-alert-box').hide();
				});
            }, 2500);
		}

		/**	At last remove the loading class	*/
		jQuery( ".wps-pos-product-listing" ).removeClass( "wps-bloc-loading" );
	}, 'json');
}

/**
 * Refresh the current order content display
 */
function wps_pos_addon_refresh_cart() {
	if ( !jQuery("#wps_cart_container").hasClass( 'wps-bloc-loading' ) ) {
		jQuery("#wps_cart_container").addClass( 'wps-bloc-loading' );
	}
	var data = {
		action: 'wps-pos-display-order-content',
		_wpnonce: jQuery( "#wps_cart_container" ).data( 'nonce' ),
	};
	jQuery.post( ajaxurl, data, function( response ){
		jQuery("#wps_cart_container").html( response );
		jQuery("#wps_cart_container").removeClass( 'wps-bloc-loading' );
		jQuery("input[name=wps-pos-product-to-choose]").val( "" );
		jQuery("input[name=wps-pos-product-to-choose]").focus();
		/**	Enable the field because treatment have been completly done	*/
		//jQuery( "input[name=wps-pos-product-to-choose]" ).prop( "disabled", false );
	});
}

/**
 * Set a quantity for a product into order
 *
 * @param pid integer The product identifer to change quantity for
 * @param qty integer The quantity to set
 */
function updateQty( pid, qty, _wpnonce ) {
	qty = qty < 0 ? 0 : qty;

	var data = {
		action: "wpshop_set_qtyfor_product_into_cart",
		_wpnonce: _wpnonce,
		product_id: pid,
		product_qty: qty,
	};
	jQuery.post(ajaxurl, data, function(response){
		if ( "success" != response ) {
			jQuery( "#wps-pos-order-content-alert" ).html( response );
	    	jQuery( "#wps-pos-order-content-alert" ).show().addClass( "wps-alert-error" );

	    	setTimeout(function(){
				jQuery('#wps-pos-order-content-alert').fadeOut( 'slow', function(){
					jQuery('#wps-pos-order-content-alert').html( '' );
					jQuery('#wps-pos-order-content-alert').hide();
				});
            }, 3000);
		}
		wps_pos_addon_refresh_cart();
	}, 'json');
}

function display_money_cash_back() {
	if ( ( "money" == jQuery( "input[name=wpspos-payment-method]:checked" ).val() ) && jQuery.isNumeric( jQuery( ".wpspos-order-received-amount" ).val() ) && ( jQuery( ".wpspos-order-received-amount" ).val() > parseInt( jQuery( 'input[name=wps-pos-total-order-amount]' ).val() ) ) ) {
		var make_change_value = jQuery( ".wpspos-order-received-amount" ).val() - jQuery('input[name=wps-pos-total-order-amount]').val();

		jQuery( "#wpspos-back-cash" ).show();
		jQuery( "#wpspos-back-cash .wpspos-due-change" ).html( make_change_value.toFixed(2) );
	}
	else {
		jQuery( "#wpspos-back-cash" ).hide();
		jQuery( "#wpspos-back-cash .wpspos-due-change" ).html( "" );
	}
}


function wpspos_set_customer_for_order( customer_to_choose, _wpnonce ) {
	var data = {
		action: "wpspos_set_customer_order",
		_wpnonce: _wpnonce,
		customer: customer_to_choose,
	};
	jQuery.post( ajaxurl, data, function( response ) {
		if ( response[ 'status' ] ) {
			jQuery( ".wpspos-customer-selected-container" ).html( response[ 'output' ] );
			jQuery( ".wpspos-dashboard-contents" ).removeClass( "wpspos-current-step-0" );
			jQuery( ".wpspos-dashboard-contents" ).removeClass( "wpspos-current-step-1" );
			jQuery( ".wpspos-dashboard-contents" ).addClass( "wpspos-current-step-2" );
			jQuery( "input[name=wps-pos-customer-to-choose]" ).val( "" );
			jQuery( "#wpspos-dashboard-" + response[ 'element_type' ] + "-metabox" ).children( ".wps-pos-alphabet-container" ).children( "button:first-child" ).click();
		}
		jQuery( ".wps-pos-" + response[ 'element_type' ] + "-listing" ).removeClass( "wps-bloc-loading" );
	}, 'json');
}
