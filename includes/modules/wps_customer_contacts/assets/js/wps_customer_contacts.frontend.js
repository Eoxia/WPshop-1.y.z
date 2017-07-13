var wpshopCRMFront = {
	/**
	 * Main function for scripts initialisation and event dispatcher
	 */
	init: function() {
		jQuery( document ).on( 'change', '#wps-customer-contacts-user-selection', function( event ) {
			wpshopCRMFront.wps_customer_contacts_change_account( event, jQuery( this ) );
		} );
	},

	wps_customer_contacts_change_account: function( event, element ) {
		var customerid = jQuery( element ).val();
		var data = {
			action: 'wps_customer_switch_to',
			cid: customerid,
			nonce: jQuery( element ).attr( 'data-nonce' )
		};
		event.preventDefault();
		jQuery( 'div.entry-content' ).addClass( 'wps-bloc-loader wps-bloc-loading' );
		jQuery.post( ajaxurl, data, function( response ) {
			if ( response.status ) {
				window.location.reload();
			} else {
				alert( response.message );
				jQuery( 'div.entry-content' ).removeClass( 'wps-bloc-loader wps-bloc-loading' );
			}
		}, 'json' );
	}

};

jQuery( document ).ready( function() {
	wpshopCRMFront.init();
});
