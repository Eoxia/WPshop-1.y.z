jQuery(document).ready(function() {
	jQuery( '#wps_signup_error_container').hide();
	
	/** When press return in the form */
	jQuery( '#wps_signup_form input' ).keyup( function(e) {
		if( 13 === e.which ) {
			jQuery( '#wps_signup_button' ).click();
		}
	});
	
	jQuery( document ).on( 'click', '#wps_signup_button', function() {
		jQuery('#wps_signup_form').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#wps_signup_button').addClass( 'wps-bton-loading' );
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		jQuery( '#wps_signup_button').removeClass( 'wps-bton-loading' );
	        		window.location.replace( response[1] );
	        	}
	        	else {
	        		jQuery( '#wps_signup_button').removeClass( 'wps-bton-loading' );
	        		jQuery( '#wps_signup_error_container').html( response[1] ).slideDown( 'slow' ).delay( 3000 ).slideUp( 'slow' );
	        	}

	        },
		}).submit();	
	});
});