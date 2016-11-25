jQuery( document ).ready(function() {

	/** Modal opener **/
	jQuery( document ).on( 'click', '.wps-modal-forgot-password-opener', function(e) {
		e.preventDefault();
		var data = {
				action: "wps_fill_forgot_password_modal",
				_wpnonce: jQuery( this ).data( 'nonce' ),
			};
			jQuery.post(ajaxurl, data, function(response) {
				if( response['status'] ) {
					fill_the_modal( response['title'], response['content'], '' );
					//jQuery('.wps-password-reminder-content').html( response['content'] );
				}
			}, 'json');
	});


	jQuery( document ).on('click', '#wps_send_forgot_password_request', function() {
		jQuery( '#wps_renew_password_error_container').hide();
		jQuery('#wps_forgot_password_form').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#wps_send_forgot_password_request' ).addClass( 'wps-bton-loading' );
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		jQuery( '#wps_send_forgot_password_request' ).removeClass( 'wps-bton-loading' );
	        		jQuery('#wps_renew_password_error_container').html( response[1] ).slideDown( 200 ).delay( 3000 ).slideUp( 200 );
	        	}
	        	else {
	        		jQuery( '#wps_send_forgot_password_request' ).removeClass( 'wps-bton-loading' );
	        		jQuery('#wps_renew_password_error_container').html( response[1] ).slideDown( 200 ).delay( 3000 ).slideUp( 200 );
	        	}
	        },
		});
	});

	jQuery( document ).on('click', '#wps_send_forgot_password_renew', function() {
		jQuery('#wps_forgot_password_form_renew').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#wps_renew_password_error_container' ).hide();
				jQuery( '#wps_renew_password_error_container_true' ).hide();
				jQuery( '#wps_send_forgot_password_renew').addClass( 'wps-bton-loading' );
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		jQuery( '#wps_send_forgot_password_renew').removeClass( 'wps-bton-loading' );

	        		jQuery( '#wps_renew_password_error_container_true' ).html( response[1] ).slideDown('slow').delay( 5000 ).slideUp( 'slow' );
	        		jQuery( '#wps_password_renew' ).slideUp( 'slow', function() {
	        			jQuery( '#wps_password_renew' ).after( response[2] );
	        		});
	        	}
	        	else {
	        		jQuery( '#wps_send_forgot_password_renew').removeClass( 'wps-bton-loading' );
	        		jQuery('#wps_renew_password_error_container').html( response[1] ).slideDown( 'slow' ).delay( 3000 ).slideUp( 'slow' );
	        	}
	        },
		});
	});


});
