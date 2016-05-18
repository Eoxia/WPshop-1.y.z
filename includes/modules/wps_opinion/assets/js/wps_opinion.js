jQuery( document ).ready( function() {
	jQuery( document ).on( 'click', '.wps-add-opinion-opener', function() {
		var id = jQuery( this ).attr( 'id' );
		var _wpnonce = jQuery( this ).data( 'nonce' );
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
				action: "wps_fill_opinion_modal",
				_wpnonce: _wpnonce,
				pid : id.replace( 'wps-add-opinion-', '' )
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					fill_the_modal( response['title'], response['content'], '' );
					jQuery( '#' + id ).removeClass( 'wps-bton-loading' );
				}
				else {
					jQuery( '#' + id ).removeClass( 'wps-bton-loading' );
				}

			}, 'json');
	});


	jQuery( document ).on( 'click', '#wps-save-opinion', function() {
		jQuery('#wps-add-opinion-form').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#wps-save-opinion' ).addClass( 'wps-btn-loading' );
			},
	        success: function( response ) {
	        	if ( response['status'] ) {
	        		jQuery('.wpsjq-closeModal').click();
	        		jQuery( '#wps-opinion-rate' ).val( '' );
	        		jQuery( '#wps-opinion-comment' ).val( '' );
	        		jQuery( '#wps-save-opinion' ).removeClass( 'wps-btn-loading' );
	        		refresh_opinion_list();
	        		alert( response['response'] );
	        	}
	        	else {
	        		jQuery( '#wps-save-opinion' ).removeClass( 'wps-btn-loading' );
	        		alert( response['response'] );
	        	}
	        },
		}).submit();
	});


	jQuery( document ).on( 'change', '#wps-opinion-rate', function() {
		change_star_rate();
	});

	function change_star_rate() {
		var data = {
				action: "wps-update-opinion-star-rate",
				_wpnonce: jQuery( '#wps-opinion-star-container' ).data( 'nonce' ),
				rate :  jQuery( '#wps-opinion-rate' ).val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery( '#wps-opinion-star-container').html( response['response'] );
				}

			}, 'json');
	}

	function refresh_opinion_list() {
		var data = {
				action: "wps-refresh-add-opinion-list",
				_wpnonce: jQuery( '#wps_dashboard_content' ).data( 'nonce' ),
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery( '#wps_dashboard_content' ).html( response['response'] );
				}

			}, 'json');
	}
});
