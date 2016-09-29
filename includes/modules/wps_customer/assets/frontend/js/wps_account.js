jQuery( document ).ready( function() {

	jQuery( document ).on( 'click', '#wps_modal_account_informations_opener', function() {
		jQuery( '#wps_modal_account_informations_opener' ).addClass( 'wps-bton-loading');
		var data = {
			action: "wps_fill_account_informations_modal",
			_wpnonce: jQuery( this ).data( 'nonce' ).toString().trim(),
		};
		jQuery.post(ajaxurl, data, function(response) {
			if( response['status'] ) {
				fill_the_modal( response['title'], response['content'], '' );
				jQuery( '#wps_modal_account_informations_opener' ).removeClass( 'wps-bton-loading');
			}
			else {
				jQuery( '#wps_modal_account_informations_opener' ).removeClass( 'wps-bton-loading');
			}
		}, 'json');
	});

	jQuery( document ).on( 'click', '#wps_account_form_button', function() {
		jQuery('#wps_account_informations_form').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#wps_account_form_button').addClass( 'wps-bton-loading' );
			},
      success: function( response ) {
      	if ( response['status'] ) {
        	jQuery( '.wpsjq-closeModal').click();
      		jQuery( '#wps_account_form_button').removeClass( 'wps-bton-loading' );
      		jQuery( '.wps-modal-opened .wps-modal-container' ).animate({scrollTop: jQuery( "#wps_signup_error_container" ).offset().top},'slow');
      		reload_account_informations();
      	}
      	else {
      		jQuery( '#wps_account_form_button').removeClass( 'wps-bton-loading' );
      		jQuery( 'html,body' ).animate({scrollTop: jQuery( "#wps_signup_error_container" ).offset().top},'slow');
      		jQuery( '.wps-modal-opened .wps-modal-container' ).animate({scrollTop: jQuery( "#wps_signup_error_container" ).offset().top},'slow');
      		jQuery( '#wps_signup_error_container').html( response['response'] ).slideDown( 'slow' ).delay( 3000 ).slideUp( 'slow' );
      	}
      },
		}).submit();
	});

	function reload_account_informations() {
		var data = {
			action: "wps_account_reload_informations",
			_wpnonce: jQuery( '#wps_account_informations_container' ).data( 'nonce' ),
		};
		jQuery.post(ajaxurl, data, function(response) {
			if ( response['status'] ){
				jQuery( '#wps_account_informations_container' ).fadeOut( 'slow', function() {
					jQuery( '#wps_account_informations_container' ).html( response['response'] );
					jQuery( '#wps_account_informations_container' ).fadeIn( 'slow' );
				});
			}
		}, 'json');
	}

});
