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
		event.preventDefault();
	}

};

jQuery( document ).ready( function() {
	wpshopCRMFront.init();
});
