
jQuery( document ).on( 'click', 'a', function( event ) {
	if ( '#' == jQuery( this ).attr( 'href' ).charAt( 0 ) ) {
		event.preventDefault();
		window[ jQuery( this ).attr( 'href' ).substring( 1 ) ]( jQuery( this ) );
	}
} );

jQuery( document ).ready( function() {
	jQuery( '.chosen-select' ).each( function() {
		applyChosen( this );
	} );
} );

function addPost( element ) {
	var newPost = jQuery( '#inline-edit' ).clone();
	var nonce = jQuery( element ).data( 'nonce' );
	newPost.show();
	jQuery( element ).addClass( 'hidden' );
	newPost.find( '.cancel' ).click( function() {
		newPost.remove();
		jQuery( element ).removeClass( 'hidden' );
	} );
	newPost.find( 'input[name="post_title"]' ).on( 'keydown', function( e ) {
		if ( 13 == e.which ) {
			e.preventDefault();
			sendPost();
		}
	} );
	newPost.find( '.save' ).click( sendPost );
	sendPostWait = true;
	function sendPost() {
		if ( sendPostWait ) {
			sendPostWait = false;
			newPost.find( '.spinner' ).addClass( 'is-active' );
			title = newPost.find( 'input[name="post_title"]' ).val();
			hook = jQuery( '#hook' ).val();
			current_url = jQuery( location ).attr( 'href' );
			jQuery.post( ajaxurl, { action: 'wps_mass_3_new', _wpnonce: nonce, title: title, hook: hook, current_url: current_url }, function( response ) {
				jQuery( '#the-list' ).prepend( response.data.row );
				jQuery( '.subsubsub' ).prevUntil( '#posts-filter' ).andSelf().remove();
				jQuery( '#posts-filter' ).prepend( response.data.subsubsub );
				jQuery( '.tablenav.top' ).prevUntil( '#posts-filter' ).andSelf().remove();
				jQuery( '#posts-filter' ).after( response.data.tablenav_top );
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
		var stroke, _ref, target, value, data, selectCol, columnClass, attrCode;
		stroke = null != ( evt.which = _ref ) ? _ref : evt.keyCode;
		if ( 13 === stroke && stateAdd ) {
			target = jQuery( evt.target );
			value = jQuery.trim( target.val() );
			selectCol = jQuery( '*[name$=\'' + select.prop( 'name' ).replace( /row_.*?\[/gi, '[' ) + '\']' );
			newPossibility = document.createElement( 'OPTION' );
			newPossibility.appendChild( document.createTextNode( value ) );
			columnClass = select.parent( 'td' ).prop( 'class' ).match( /column-[\d\w-_]+/g );
			for ( i = 0; i < columnClass.length; i++ ) {
				attrCode = columnClass[i].replace( 'column-', '' );
				if ( i > 0 ) {
					console.log( 'Error' );
				}
			}
			data = {
				action: 'new_option_for_select_from_product_edition',
				wpshop_ajax_nonce: WPSHOP_NEWOPTION_CREATION_NONCE,
				attribute_code: attrCode,
				attribute_new_label: value,
				attribute_selected_values: []
			};
			jQuery.post( ajaxurl, data, function( response ) {
				newPossibility.setAttribute( 'value', response[3] );
				selectCol.each( function() {
					var currentElement = jQuery( this );
					var value = jQuery( newPossibility ).clone();
					if ( currentElement.prop( 'name' ) == select.prop( 'name' ) ) {
						value = value.prop( 'selected', true );
						currentElement.closest( 'tr' ).find( 'input:checkbox[name^=cb]' ).prop( 'checked', true );
					}
					currentElement.append( value );
					currentElement.trigger( 'chosen:close' );
					currentElement.trigger( 'chosen:updated' );
				} );
			}, 'json' );
			return false;
		}
		return true;
	} );
}

jQuery( document ).on( 'change', '#the-list :input:not(input:checkbox[name^=cb]), #the-list select, #the-list textarea', function() {
	jQuery( this ).closest( 'tr' ).find( 'input:checkbox[name^=cb]' ).prop( 'checked', true );
} );

jQuery( document ).on( 'click', '.bulk-save', function() {
	savePosts();
} );

// CTRL - S
jQuery( document ).keydown(function( e ) {
	var key = undefined;
	var possible = [ e.key, e.keyIdentifier, e.keyCode, e.which ];
	while ( undefined === key && possible.length > 0 ) {
		key = possible.pop();
	}
	if ( key && ( '115' == key || '83' == key ) && ( e.ctrlKey || e.metaKey ) && ! ( e.altKey ) ) {
		e.preventDefault();
		jQuery( ':focus' ).blur();
		savePosts();
		return false;
	}
	return true;
} );

function savePosts() {
	var checkeds = jQuery( '#the-list input:checkbox[name^=cb]:checked' );
	var datas = checkeds.closest( 'tr' ).find( ':input:not(.toggle-row, .chosen-search-input), select, textarea' ).add( '<input type="text" name="action" value="wps_mass_3_save"/>' );
	if ( checkeds.length <= 0 ) {
		return;
	}
	jQuery( '.bulkactions .spinner' ).addClass( 'is-active' );
	jQuery.post( ajaxurl, '_wpnonce=' + jQuery( '.bulkactions .bulk-save' ).data( 'nonce' ) + '&' + datas.serialize(), function( response ) {
		jQuery( '.bulkactions .spinner' ).removeClass( 'is-active' );
		checkeds.prop( 'checked', false );
		jQuery( ':input[id^=cb-select-all]' ).prop( 'checked', false );

		//Notice = jQuery( '.hidden.notice' ).clone().addClass( 'notice-success' ).removeClass( 'hidden' );
		//notice.find( 'p' ).text( response.data.notice );
		//jQuery( '.hidden.notice' ).after( notice );
		window.location.reload();
	} );
}

jQuery( document ).on( 'click', '.notice-dismiss', function() {
	jQuery( this ).parent( 'div' ).remove();
} );

function thumbnail( element ) {
	if ( element.children( 'span' ).length > 1 ) {
		element.siblings( 'input' ).val( '' );
		element.children( 'span' ).first().remove();
		element.closest( 'tr' ).find( 'input:checkbox[name^=cb]' ).prop( 'checked', true );
	} else {
		uploaderCategory = wp.media( {
			multiple: false,
			title: jQuery( this ).data( 'mediaTitle' ),
			library: {
				type: 'image'
			}
		} ).on( 'select', function() {
			var selectedPicture = uploaderCategory.state().get( 'selection' );
			var attachment = selectedPicture.first().toJSON();
			element.siblings( 'input' ).val( attachment.id );
			element.prepend( '<span class="img"><img width="25" height="25" src="' + attachment.sizes.thumbnail.url + '"></span>' );
			element.closest( 'tr' ).find( 'input:checkbox[name^=cb]' ).prop( 'checked', true );
		} ).open();
	}
}
