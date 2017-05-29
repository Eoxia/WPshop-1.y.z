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
			title = newPost.find( 'input[name="post_title"]' ).val();
			jQuery.post( ajaxurl, { action: 'wps_mass_3_new', title: title, screen: list_args.screen.id }, function( response ) {
				jQuery( '#the-list' ).prepend( response.data.row );
				jQuery( '.subsubsub' ).html( response.data.subsubsub );
				jQuery( '.tablenav.top' ).prevUntil( '#posts-filter' ).andSelf().html( response.data.tablenav_top );
				jQuery( '.tablenav.bottom' ).html( response.data.tablenav_bottom );
				newPost.remove();
				toMuchRows = response.data.per_page - jQuery( '#the-list > tr' ).length;
				if ( toMuchRows < 0 ) {
					jQuery( '#the-list > tr' ).slice( toMuchRows ).hide();
				}
				jQuery( element ).removeClass( 'hidden' );
				jQuery( '.no-items' ).remove();
				jQuery( '#the-list > tr' ).first().find( '.chosen-select' ).each( function() {
					applyChosen( this );
				} );
				sendPostWait = true;
			} );
		}
	}
	jQuery( '#the-list' ).prepend( newPost );
}

function savePosts() {
	var checkeds = jQuery( '#the-list input:checkbox[name^=cb]:checked' );
	var datas = checkeds.closest( 'tr' ).find( ':input:not(.toggle-row, .chosen-search-input), select, textarea' ).add( '<input type="text" name="action" value="wps_mass_3_save"/>' );
	if ( checkeds.length <= 0 ) {
		return;
	}
	jQuery( '.bulkactions .spinner' ).addClass( 'is-active' );
	jQuery.post( ajaxurl, datas.serialize(), function( response ) {
		jQuery( '.bulkactions .spinner' ).removeClass( 'is-active' );
		checkeds.prop( 'checked', false );
		jQuery( ':input[id^=cb-select-all]' ).prop( 'checked', false );

		//Notice = jQuery( '.hidden.notice' ).clone().addClass( 'notice-success' ).removeClass( 'hidden' );
		//notice.find( 'p' ).text( response.data.notice );
		//jQuery( '.hidden.notice' ).after( notice );
		window.location.reload();
	} );
}

function applyChosen( element ) {
	var stateAdd, chosen, select;
	select = jQuery( element );
	chosen = select.chosen( { width: '100%', no_results_text: 'No results match. Press enter to add ' } );
	chosen.on( 'change', function( evt, params ) {
		stateAdd = false;
	} );
	chosen.on( 'chosen:no_results', function( evt, params ) {
		stateAdd = true;
	} );
	jQuery( element ).siblings( '.chosen-container' ).find( '.chosen-search-input' ).keydown( function( evt ) {
		var stroke, _ref, target, value;
		stroke = null != ( evt.which = _ref ) ? _ref : evt.keyCode;
		if ( 13 === stroke && stateAdd ) {
			target = jQuery( evt.target );
			value = jQuery.trim( target.val() );
			newPossibility = document.createElement( 'OPTION' );
			newPossibility.setAttribute( 'value', 'new' );
			newPossibility.appendChild( document.createTextNode( value ) );
			newPossibility = jQuery( newPossibility ).prop( 'selected', true );
			select.append( newPossibility );
			select.trigger( 'chosen:close' );
			select.trigger( 'chosen:updated' );
			return false;
		}
		return true;
	} );
}

jQuery( document ).on( 'change', '#the-list :input:not(input:checkbox[name^=cb]), #the-list select, #the-list textarea', function() {
	jQuery( this ).closest( 'tr' ).find( 'input:checkbox[name^=cb]' ).prop( 'checked', true );
});

jQuery( document ).on( 'click', '.bulk-save', function() {
	savePosts();
} );

// CTRL - S
jQuery( document ).keydown(function( e ) {
	var key = undefined;
	var possible = [ e.key, e.keyIdentifier, e.keyCode, e.which ];
	while ( key === undefined && possible.length > 0 ) {
		key = possible.pop();
	}
	if ( key && ( key == '115' || key == '83' ) && ( e.ctrlKey || e.metaKey ) && ! ( e.altKey ) ) {
		e.preventDefault();
		jQuery( ':focus' ).blur();
		savePosts();
		return false;
	}
	return true;
} );

jQuery( document ).on( 'click', '.notice-dismiss', function() {
	jQuery( this ).parent( 'div' ).remove();
} );

jQuery( document ).ready( function() {
	jQuery( '.chosen-select' ).each( function() {
		applyChosen( this );
	} );
} );
