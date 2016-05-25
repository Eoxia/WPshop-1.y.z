jQuery( document ).ready( function(){
	jQuery( "#wps-quotation-check-code-button" ).click( function( event ){
		event.preventDefault();
		jQuery( this ).addClass( "wps-bton-loader" );
		jQuery( ".wps-quotation-addon-state-message-container" ).html( "" ).removeClass( "wps-alert-success wps-alert-error" );
		var code_to_check = jQuery( "#wps-quotation-check-code-value" ).val();

		var data = {
			action: "check_code_for_activation",
			_wpnonce: jQuery( this ).data( 'nonce' ),
			code: code_to_check,
		};
		jQuery.post( ajaxurl, data, function( response ){
			jQuery( this ).removeClass( "wps-bton-loader" );
			jQuery( ".wps-quotation-addon-state-message-container" ).html( response[ 'message' ] );
			if ( response[ 'status' ] ) {
				jQuery( ".wps-quotation-addon-state-container" ).hide();
				jQuery( ".wps-quotation-addon-state-message-container" ).addClass( "wps-alert-success" );
			}
			else {
				jQuery( ".wps-quotation-addon-state-message-container" ).addClass( "wps-alert-error" );
			}
		}, 'json');
	});
});
