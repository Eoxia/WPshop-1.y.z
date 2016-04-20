function fill_the_modal( title, content, footer ) {
	jQuery( '.wps-modal-header h3').html( title );
	jQuery( '.wps-modal-body').html( content );
	jQuery( '.wps-modal-footer').html( footer );
	
	jQuery('.wps-modal-wrapper').addClass('wps-modal-opened');
	jQuery('html').addClass('wpsjq-modal-opened');
}