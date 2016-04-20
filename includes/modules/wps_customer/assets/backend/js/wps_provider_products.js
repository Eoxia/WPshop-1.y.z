jQuery( document ).ready( function() {
	jQuery( '.values' ).hide();
	jQuery( '.product' ).on( 'click', 'h1', function(e) {
		e.preventDefault();
		if( jQuery( '.values[data-id="' + this.dataset.id + '"]' ).is( ":hidden" ) ) {
			jQuery( '.values[data-id="' + this.dataset.id + '"]' ).slideDown( 'slow' );
		} else {
			jQuery( '.values[data-id="' + this.dataset.id + '"]' ).slideUp( 'slow' );
		}
	} );
	jQuery( '.delete_provider_btn' ).on( 'click', function(e) {
		e.preventDefault();
		jQuery( '.special_provider[data-id="' + this.dataset.id + '"]' ).val( 'delete' );
	} );
} );