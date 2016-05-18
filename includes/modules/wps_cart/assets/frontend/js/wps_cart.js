jQuery( document ).ready( function() {

	jQuery( '#wps_cart_error_container' ).hide();

	/** Product Qty Management in cart **/
	jQuery( document ).on( 'click',  '.wps-cart-reduce-product-qty', function(e) {
		e.preventDefault();
		if( jQuery(this).closest( 'li' ).length ) {
			var li_element = jQuery(this).closest( 'li' );
			var product_id;
			if( typeof li_element.data( 'id' ) == 'undefined' ) {
				product_id = li_element.attr( 'id' ).replace( 'wps_product_', '' );
			} else {
				product_id = li_element.data( 'id' );
			}
			var qty = jQuery( '#wps-cart-product-qty-' + product_id ).val();
			qty = parseInt( qty ) - 1;
			jQuery( '#wpshop_pdt_qty' ).val( qty );
			change_product_qty_in_cart( product_id, qty);
		}
		else {

			if( parseInt( jQuery('.wpshop_product_qty_input').val() ) == 1 ) {
				jQuery('.wpshop_product_qty_input').val( 1 );
			}
			else {
				jQuery('.wpshop_product_qty_input').val( parseInt( jQuery('.wpshop_product_qty_input').val() ) - 1 );
				jQuery( '#wpshop_pdt_qty' ).val( jQuery('.wpshop_product_qty_input').val() );
			}
		}
	});

	jQuery( document ).on( 'keyup', '.wpshop_product_qty_input', function() {
		jQuery( '#wpshop_pdt_qty' ).val( parseInt( jQuery('.wpshop_product_qty_input').val() ) );
	});

	/** Product Qty Management in cart **/
	jQuery( document ).on( 'click',  '.wps-cart-add-product-qty', function(e) {
		e.preventDefault();
		if( jQuery(this).closest( 'li' ).length ) {
			var li_element = jQuery(this).closest( 'li' );
			var product_id;
			if( typeof li_element.data( 'id' ) == 'undefined' ) {
				product_id = li_element.attr( 'id' ).replace( 'wps_product_', '' );
			} else {
				product_id = li_element.data( 'id' );
			}
			var qty = jQuery( '#wps-cart-product-qty-' + product_id ).val();
			qty = parseInt( qty ) + 1;
			change_product_qty_in_cart( product_id, qty);
		}
		else {
			jQuery('.wpshop_product_qty_input').val( parseInt( jQuery('.wpshop_product_qty_input').val() ) + 1 );
			jQuery( '#wpshop_pdt_qty' ).val( jQuery('.wpshop_product_qty_input').val() );
		}
	});

	/** Delete product **/
	jQuery( document ).on( 'click', '.wps_cart_delete_product', function(e) {
		e.preventDefault();
		var li_element = jQuery(this).closest( 'li' );
		var product_id;
		if( typeof li_element.data( 'id' ) == 'undefined' ) {
			product_id = li_element.attr( 'id' ).replace( 'wps_product_', '' );
		} else {
			product_id = li_element.data( 'id' );
		}
		change_product_qty_in_cart( product_id, 0 );
	});

	/** Delete product **/
	jQuery( document ).on( 'click', '.wps_mini_cart_delete_product', function(e) {
		e.preventDefault();
		var li_element = jQuery(this).closest( 'li' );
		var product_id;
		if( typeof li_element.data( 'id' ) == 'undefined' ) {
			product_id = li_element.attr( 'id' ).replace( 'wps_product_', '' );
		} else {
			product_id = li_element.data( 'id' );
		}
		change_product_qty_in_cart( product_id, 0 );
	});

	/** Apply Coupon Action **/
	jQuery( document ).on( 'click', '.wpsjs-apply-coupon', function(e) {
		e.preventDefault();
		_this = jQuery(this);
		// jQuery( '.wps-cart-wrapper' ).addClass( 'wps-bloc-loading');
		_this.addClass('wps-loading');
		jQuery( '#wps_coupon_alert_container' ).hide();
		var data = {
				action: "wps_apply_coupon",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				coupon_code : jQuery( '#wps_coupon_code' ).val()
			};
			jQuery.post(ajaxurl, data, function(response){
				if ( response['status'] ) {

					// jQuery( '.wps-cart-wrapper' ).removeClass( 'wps-bloc-loading');
					jQuery( '#wps_coupon_alert_container' ).html( response['response'] ).slideDown( 300 ).delay( 4000 ).slideUp( 300 );
					jQuery( '#wps_coupon_code' ).val( '' );
					reload_wps_cart();
					reload_mini_cart();
				}
				else {
					jQuery( '#wps_cart_container' ).removeClass( 'wps-bloc-loading');
					jQuery( '#wps_coupon_alert_container' ).html( response['response'] ).slideDown( 300 ).delay( 4000 ).slideUp( 300 );
					jQuery( '#wps_coupon_code' ).val( '' );
				}
				_this.removeClass('wps-loading');
		}, 'json');
	});


	jQuery( document ).on( 'click', '#wps-cart-order-action', function() {
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
				action: "wps_cart_pass_to_step_two",
				_wpnonce: jQuery( this ).data( 'nonce' )
			};
			jQuery.post(ajaxurl, data, function(response){
				if( response['status'] ) {
					window.location.replace( response['response'] );
				}
				else {
					jQuery( '#wps_cart_error_container' ).html( response['response']).slideDown( 'slow' ).delay( 3500 ).slideUp( 'slow' );
					jQuery( this ).removeClass( 'wps-bton-loading' );
				}
			}, 'json');
	});

	jQuery( document ).on( 'keyup', '.wps-cart-product-qty', function() {
		var pid = jQuery( this ).attr('id').replace( 'wps-cart-product-qty-', '' );
		var qty = jQuery( this ).val();
		if( jQuery.isNumeric( qty ) ) {
			change_product_qty_in_cart( pid, qty );
		}
 	});



	/** Change product Qty in cart **/
	function change_product_qty_in_cart( product_id, product_qty ) {
		jQuery( '#wps_cart_container' ).addClass( 'wps-bloc-loading');
		var data = {
				action: "wpshop_set_qtyfor_product_into_cart",
				product_id: product_id,
				product_qty: product_qty,
			};
			jQuery.post(ajaxurl, data, function(response){
				if(response[0] == 'success') {
					reload_wps_cart();
					reload_mini_cart();
					reload_summary_cart();
				}
				else {
					jQuery( '#wps_cart_error_container' ).html( response[0] );
					jQuery( '#wps_cart_container' ).removeClass( 'wps-bloc-loading' );
					jQuery( '#wps_cart_error_container' ).slideDown( 'slow' ).delay( 3500 ).slideUp( 'slow' );
				}
			}, 'json');
	}


	jQuery( document ).on( 'click', '.emptyCart', function() {
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
				action: "wps_empty_cart",
				_wpnonce: jQuery( this ).data( 'nonce' ),
			};
			jQuery.post(ajaxurl, data, function(response){
				if(response['status']) {
					reload_wps_cart();
					reload_mini_cart();
					reload_summary_cart();
				}
				jQuery( '.emptyCart' ).removeClass( 'wps-bton-loading' );
			}, 'json');
	});


});




/** Reload cart action **/
function reload_wps_cart() {
	jQuery( '#wps_cart_container' ).addClass( 'wps-bloc-loading');
	var data = {
			action: "wps_reload_cart",
			_wpnonce: jQuery( '#wps_cart_container' ).data( 'nonce' ),
		};
		jQuery.post(ajaxurl, data, function(response){
				//jQuery( '#wps_cart_container').animate({'opacity' : 0.1}, 450, function() {
				jQuery( '#wps_cart_container').html( response['response']);
				jQuery( '#wps_cart_error_container' ).hide();
				jQuery( '#wps_cart_container' ).removeClass( 'wps-bloc-loading');
				//jQuery( '#wps_cart_container').delay( 200 ).animate({'opacity' : 1}, 450 );
			//});
	}, 'json');
}

/** Reload Mini cart **/
function reload_mini_cart() {
	var type= 'mini';
	if( jQuery( '.wps-fixed-cart-container').length ) {
		type = 'fixed';
		jQuery( '.wps-fixed-cart-container').addClass( 'wps-bloc-loading' );
	}
	var data = {
			action: "wps_reload_mini_cart",
			_wpnonce: jQuery( '.wps-fixed-cart-container' ).data( 'nonce' ),
			type : type
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery( '.wps-mini-cart-body').animate({'opacity' : 0.1}, 450, function() {
				jQuery( '.wps-mini-cart-body').delay( 500 ).html( response['response']);
				jQuery( '.wps-mini-cart-body').delay( 200 ).animate({'opacity' : 1}, 450 );
				jQuery( '.wps-mini-cart-free-shipping-alert' ).fadeOut( 'slow' ).html( response['free_shipping_alert']).fadeIn( 'slow' );
				jQuery( '.wps-fixed-cart-container').removeClass( 'wps-bloc-loading' );
			});
			jQuery( '.wps-numeration-cart').each( function() {
				jQuery( this ).html(response['count_items']);
			});
	}, 'json');
}
/** Reload Summary Cart **/
function reload_summary_cart() {
	var data = {
			action: "wps_reload_summary_cart",
			_wpnonce: jQuery( "#wps_resume_cart_container" ).data( 'nonce' ),
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery( '#wps_resume_cart_container').animate({'opacity' : 0.1}, 450, function() {
				jQuery( '#wps_resume_cart_container').delay( 500 ).html( response['response']);
				jQuery( '#wps_resume_cart_container').delay( 200 ).animate({'opacity' : 1}, 450 );
			});

	}, 'json');
}
