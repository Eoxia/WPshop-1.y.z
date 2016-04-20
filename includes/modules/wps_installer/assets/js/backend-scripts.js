jQuery( document ).ready( function(){

	/**	Define a modal box for messages and pages modifications	*/
	jQuery( "#wps_installer_form button" ).click( function(){
		jQuery( this ).closest( "form" ).children( ".spinner" ).show();
	} );

	/**	Trigger event when clicking on close button for welcome panel	*/
	jQuery( ".wps-welcome-panel-close" ).click( function( event ){
		/**	Disable default event on clicked element	*/
		event.preventDefault();

		var data = {
			action: "wps-hide-welcome-panel",
			wpshop_ajax_nonce: jQuery( "#wps_installer_welcome_close_nonce" ).val(),
		};
		jQuery.post( ajaxurl, data, function( response) {
			if ( response [ 'status' ] ) {
				jQuery( ".wps-welcome-panel" ).remove();
			}
		}, "json");
	});

} );