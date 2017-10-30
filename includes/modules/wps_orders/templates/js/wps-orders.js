jQuery( document ).ready( function() {
	jQuery( document ).on( 'click', '.wps-orders-details-opener', function() {
		var orderID = jQuery( this ).attr( 'id' ).replace( 'wps-order-details-opener-', '' );
		var data = {
			action: 'wps_orders_load_details',
			_wpnonce: jQuery( this ).data( 'nonce' ),
			order_id: orderID
		};
		jQuery( this ).addClass( 'wps-bton-loading' );
		jQuery.post( ajaxurl, data, function( response ) {
			if ( response['status'] ) {
				fill_the_modal( response['title'], response['content'], '' );
				jQuery( '#wps-order-details-opener-' + orderID ).removeClass( 'wps-bton-loading' );
			} else {
				jQuery( '#wps-order-details-opener-' + orderID ).removeClass( 'wps-bton-loading' );
			}
		}, 'json' );
	});
});
