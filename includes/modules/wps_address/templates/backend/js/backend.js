var jq_wpeogeoloc = jQuery.noConflict();

jq_wpeogeoloc( document ).ready(function() {

	/**	Listen actions on address title in order to open close choosen	*/
	jQuery( document ).on( "click", ".wps-address-item-header > a", function( e ){
		e.preventDefault();
		if ( jQuery( this ).hasClass( "wps-address-arrow-right" ) ) {
			jQuery( this ).closest( "li" ).children( ".wps-address-item-content" ).slideDown();
		}
		else {
			jQuery( this ).closest( "li" ).children( ".wps-address-item-content" ).slideUp();
		}
		jQuery( this ).toggleClass( "wps-address-arrow-right wps-address-arrow-down" );
	});

	/**	Listen actions on address actions button	*/
	jQuery( document ).on( "click", ".wps-address-actions-container a", function( e ){
		e.preventDefault();
		var action = jQuery( this ).attr( "id" ).replace( "wps-address-action-", "" ).split( "-for-" );
		var address_id = action[ 1 ];
		var element_id = jQuery( "#post_ID" ).val();

		if ( action[ 0 ] == "edit" ) {
			jQuery( this ).closest( "span" ).html( wps_address_loading_picture );
			var data = {
				action: "wps-address-edition-form-load",
				element_id: address_id,
				post_id: element_id,
			};
			jQuery( "#wps-address-item-" + address_id + " .wps-address-item-content" ).load( ajaxurl, data, function() {
				jQuery( "#wps-address-item-" + address_id + " .wps-address-item-header > a.wps-address-arrow-right" ).click();
				jQuery( "#wps-address-item-" + address_id + " span.wps-address-actions-container" ).remove();
			} );
		}
	});

	/**	Listen actions on address add button	*/
	jQuery( "#wps_attached_addresses a.wps-address-icon-add" ).click( function( e ){
		e.preventDefault();
		var element_id = jQuery( this ).attr( "id" ).replace( "wps-address-add-for-", "" );
		var data = {
			action: "wps-address-add-new",
			_wpnonce: jQuery( this ).data( 'nonce' ),
			element_id: 0,
			post_id: element_id,
		};
		jQuery( this ).closest( "div.inside" ).children( ".wps-address-list-container" ).append( '<div id="wps-overlay" class="wps-overlay-background" ></div><div id="wps-overlay-load" style="top: 45%;" ><img src="' + thickboxL10n.loadingAnimation + '" /></div>' ).css( "height", "100px" );
		jQuery( this ).closest( "div.inside" ).children( ".wps-address-list-container" ).load( ajaxurl, data, function() {
			jQuery( this ).closest( "div.inside" ).children( ".wps-address-list-container" ).css( "height", "" );
		} );
		jQuery( this ).hide();
	});

});

/**
 * Load addresses for a given element
 *
 * @param post_id The post id to display
 */
function wps_address_load_addresses_list( post_id ) {
	var data = {
		action: "wps-address-display-list",
		_wpnonce: jQuery("div.wps-address-list-container").data( 'nonce' ),
		post_id: post_id,
	};
	jQuery("div.wps-address-list-container" ).load( ajaxurl, data );
}

/**
*	Allows to convert html special chars to normal chars in javascript messages
*
*	@param string text The text we want to change html special chars into normal chars
*
*/
function wps_address_convert_html_accent(text) {
	text = text.replace(/&Agrave;/g, "\300");
	text = text.replace(/&Aacute;/g, "\301");
	text = text.replace(/&Acirc;/g, "\302");
	text = text.replace(/&Atilde;/g, "\303");
	text = text.replace(/&Auml;/g, "\304");
	text = text.replace(/&Aring;/g, "\305");
	text = text.replace(/&AElig;/g, "\306");
	text = text.replace(/&Ccedil;/g, "\307");
	text = text.replace(/&Egrave;/g, "\310");
	text = text.replace(/&Eacute;/g, "\311");
	text = text.replace(/&Ecirc;/g, "\312");
	text = text.replace(/&Euml;/g, "\313");
	text = text.replace(/&Igrave;/g, "\314");
	text = text.replace(/&Iacute;/g, "\315");
	text = text.replace(/&Icirc;/g, "\316");
	text = text.replace(/&Iuml;/g, "\317");
	text = text.replace(/&Eth;/g, "\320");
	text = text.replace(/&Ntilde;/g, "\321");
	text = text.replace(/&Ograve;/g, "\322");
	text = text.replace(/&Oacute;/g, "\323");
	text = text.replace(/&Ocirc;/g, "\324");
	text = text.replace(/&Otilde;/g, "\325");
	text = text.replace(/&Ouml;/g, "\326");
	text = text.replace(/&Oslash;/g, "\330");
	text = text.replace(/&Ugrave;/g, "\331");
	text = text.replace(/&Uacute;/g, "\332");
	text = text.replace(/&Ucirc;/g, "\333");
	text = text.replace(/&Uuml;/g, "\334");
	text = text.replace(/&Yacute;/g, "\335");
	text = text.replace(/&THORN;/g, "\336");
	text = text.replace(/&Yuml;/g, "\570");
	text = text.replace(/&szlig;/g, "\337");
	text = text.replace(/&agrave;/g, "\340");
	text = text.replace(/&aacute;/g, "\341");
	text = text.replace(/&acirc;/g, "\342");
	text = text.replace(/&atilde;/g, "\343");
	text = text.replace(/&auml;/g, "\344");
	text = text.replace(/&aring;/g, "\345");
	text = text.replace(/&aelig;/g, "\346");
	text = text.replace(/&ccedil;/g, "\347");
	text = text.replace(/&egrave;/g, "\350");
	text = text.replace(/&eacute;/g, "\351");
	text = text.replace(/&ecirc;/g, "\352");
	text = text.replace(/&euml;/g, "\353");
	text = text.replace(/&igrave;/g, "\354");
	text = text.replace(/&iacute;/g, "\355");
	text = text.replace(/&icirc;/g, "\356");
	text = text.replace(/&iuml;/g, "\357");
	text = text.replace(/&eth;/g, "\360");
	text = text.replace(/&ntilde;/g, "\361");
	text = text.replace(/&ograve;/g, "\362");
	text = text.replace(/&oacute;/g, "\363");
	text = text.replace(/&ocirc;/g, "\364");
	text = text.replace(/&otilde;/g, "\365");
	text = text.replace(/&ouml;/g, "\366");
	text = text.replace(/&oslash;/g, "\370");
	text = text.replace(/&ugrave;/g, "\371");
	text = text.replace(/&uacute;/g, "\372");
	text = text.replace(/&ucirc;/g, "\373");
	text = text.replace(/&uuml;/g, "\374");
	text = text.replace(/&yacute;/g, "\375");
	text = text.replace(/&thorn;/g, "\376");
	text = text.replace(/&yuml;/g, "\377");
	text = text.replace(/&oelig;/g, "\523");
	text = text.replace(/&OElig;/g, "\522");
	return text;
}
