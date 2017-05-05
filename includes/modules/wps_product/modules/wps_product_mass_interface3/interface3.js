function addPost( event, element ) {
	var newPost = jQuery( '#inline-edit' ).clone();
	event.preventDefault();
	newPost.show();
	jQuery( element ).addClass( 'hidden' );
	newPost.find( '.cancel' ).click( function() {
		newPost.remove();
		jQuery( element ).removeClass( 'hidden' );
	} );
	newPost.find( 'input[name="post_title"]' ).on( 'keydown', function( e ) {
		if ( e.which == 13 ) {
			e.preventDefault();
			sendPost();
		}
	} );
	newPost.find( '.save' ).click( function() {
		sendPost();
	} );
	sendPostWait = true;
	function sendPost() {
		if ( sendPostWait ) {
			sendPostWait = false;
			newPost.find( '.spinner' ).addClass( 'is-active' );
			var title = newPost.find( 'input[name="post_title"]' ).val();
			jQuery.post( ajaxurl, { action: 'wps_mass_3_new', title: title }, function( response ) {
				jQuery( '#the-list' ).prepend( response.data.row );
				newPost.remove();
				jQuery( element ).removeClass( 'hidden' );
				sendPostWait = true;
			} );
		}
	}
	jQuery( '#the-list' ).prepend( newPost );
}
