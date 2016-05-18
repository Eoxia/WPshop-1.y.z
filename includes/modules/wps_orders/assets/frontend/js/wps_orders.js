jQuery( document ).ready( function() {
	/** Open order details */
	jQuery( document ).on( 'click', '.wps-orders-details-opener', function() {
		var order_id = jQuery( this ).attr( 'id' ).replace( 'wps-order-details-opener-','');
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
				action: "wps_orders_load_details",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				order_id : order_id
			};
			jQuery.post(ajaxurl, data, function(response) {
					if( response['status'] ) {
						fill_the_modal( response['title'], response['content'], '' );
						jQuery( '#wps-order-details-opener-' + order_id ).removeClass( 'wps-bton-loading' );
					}
					else {
						jQuery( '#wps-order-details-opener-' + order_id ).removeClass( 'wps-bton-loading' );
					}

			}, 'json');
	});
	/** Delete order */
	jQuery(document).on('click', '.wps-orders-delete', function() {
		var element = jQuery(this);
		var order_id = jQuery(this).data('id');
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
			action: "wps_delete_order",
			_wpnonce: jQuery( this ).data( 'nonce' ),
			order_id: order_id,
		};
		jQuery.post(ajaxurl, data, function( response ) {
			element.removeClass('wps-bton-loading');
			if( response['status'] ) {
				element.closest('.wps-table-row').fadeOut("slow", function(){
				   var div = jQuery( response['row'] ).hide();
				   jQuery( this ).replaceWith(div);
				   div.fadeIn("slow");
				});
			}
		}, 'json');
	});
	/** Pay billing */
	jQuery(document).on('click', '.wps-quotation-checkout', function() {
		var element = this;
		var data = {
			action: "wps_checkout_quotation",
			_wpnonce: jQuery( this ).data( 'nonce' ),
			order_id: element.dataset.oid,
		};
		jQuery( element ).addClass( 'wps-bton-loading' );
		jQuery.post( ajaxurl, data, function( response ) {
			if( response['status'] ) {
				window.location.replace( response['301'] );
			}
		}, 'json' )
		.always( function() {
			jQuery( element ).removeClass( 'wps-bton-loading' );
		} );
	});
});
