jQuery( document ).ready( function() {

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

});
