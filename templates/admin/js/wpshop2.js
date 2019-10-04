jQuery( document ).ready(function() {
	jQuery( '.open-modal-notice-wpshop2' ).click(function() {
		var data = {};

		data.action = "load_wpshop2_notice_content";

		jQuery( this ).addClass( 'wpeo-loader' );
		jQuery( this ).append( '<span class="loader-spin"></span>' );

		var _this = jQuery( this );

		jQuery.post(ajaxurl, data, function( response ) {
			_this.removeClass( 'wpeo-loader' );
			_this.find( '.loader-spin' ).remove();

			jQuery( 'body' ).append( response.data.view );
			jQuery( '.modal-notice-wpshop .modal-title' ).text( response.data.title );
		});
	});

	jQuery( document ).on( 'click', '.modal-notice-wpshop .wpeo-button', function() {
		var popup = jQuery( '.modal-notice-wpshop' );
		jQuery( '.modal-notice-wpshop' ).removeClass( 'modal-active' );

		setTimeout( function() {
			popup.remove();
		}, 200 );
	});
});
