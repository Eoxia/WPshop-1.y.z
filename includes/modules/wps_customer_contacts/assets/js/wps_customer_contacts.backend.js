var wpshopCRM = {
	/**
	 * Main function for scripts initialisation and event dispatcher
	 */
	init: function() {
		/** Désactivation de la touche entrée dans le champs de recherche d'utilisateur / Disable return into user search input */
		jQuery( document ).on( 'keypress', '.wps-customer-autocomplete-input', function( event ) {
			if ( ( 13 === event.keyCode ) && ( 13 === event.which ) ) {
				return false;
			}
		} );

		/** Ecoute l'événement pour délier un utilisateur */
		jQuery( document ).on( 'click', '#wps_customer_contacts .inside table td.wps-customer-contacts-actions .dashicons-editor-unlink', function( event ) {
			wpshopCRM.wps_customer_contacts_dissociate( event, jQuery( this ) );
		});

		/** Ecoute l'événement pour afficher le champs d'association d'un utiilsateur à un client */
		jQuery( document ).on( 'click', '.wps-customer-contact-association-opener', function( event ) {
			event.preventDefault();
			jQuery( this ).closest( 'td' ).children( 'input' ).show();
			jQuery( this ).hide();
		} );

		jQuery( document ).on( 'click', '#wps_customer_contacts .inside table td.wps-customer-contacts-actions .dashicons-star-empty', function( event ) {
			wpshopCRM.wps_customer_contacts_change_default( event, jQuery( this ) );
		} );
	},

	/**
	 * Lance la recherche des utilisateurs/clients / Launch users/customers search
	 */
	user_search: function() {
		jQuery( '#wps_customer_contacts .wps-customer-autocomplete-input' ).autocomplete( {
			source: ajaxurl + '?action=wps_customer_search&_wpnonce=' + jQuery( '.wps-customer-autocomplete-input' ).attr( 'data-search-nonce' ) + '&search_in=' + jQuery( '.wps-customer-autocomplete-input' ).attr( 'data-types' ),
			select: function( event, ui ) {
				wpshopCRM.wps_customer_contacts_associate( ui.item );
				return false;
			}
		} ).autocomplete( 'instance' )._renderItem = function( ul, item ) {
			if ( null !== item.id ) {
				return jQuery( '<li>' )
					.append( '<div>#' + item.id + ' - ' + item.display_name + '<br>&nbsp;&nbsp;&nbsp;' + item.email + '<br>&nbsp;&nbsp;&nbsp;' + item.last_name + '&nbsp;' + item.first_name + '</div>' )
					.appendTo( ul );
			} else {
				return jQuery( '<li>' )
					.append( '<div>' + item.label + '</div>' )
					.appendTo( ul );
			}
		};
	},

	/**
	 * Launch user association
	 */
	wps_customer_contacts_associate: function( item ) {
		if ( null !== item.id ) {
			jQuery( '.wps-customer-autocomplete-input' ).val( item.email );
			jQuery( '#wps_customer_contacts .inside' ).addClass( 'wps-bloc-loader wps-bloc-loading' ).load( ajaxurl, {
				'action': 'wps_customer_contacts_associate',
				'_wpnonce': jQuery( '.wps-customer-autocomplete-input' ).attr( 'data-associate-nonce' ),
				'UID': item.id,
				'CID': jQuery( '.wps-customer-autocomplete-input' ).attr( 'data-customer' )
			}, function( response ) {
				jQuery( '#wps_customer_contacts .inside' ).removeClass( 'wps-bloc-loader wps-bloc-loading' );
			});
		} else {

		}
	},

	/**
	 * Dissociation d'un utilisateur à un client
	 */
	wps_customer_contacts_dissociate: function( event, element ) {
		if ( confirm( wpshopCrm.confirm_user_dissociation ) ) {
			jQuery( '#wps_customer_contacts .inside' ).addClass( 'wps-bloc-loader wps-bloc-loading' ).load( ajaxurl, {
				'action': 'wps_customer_contacts_dissociate',
				'_wpnonce': jQuery( element ).closest( 'table' ).attr( 'data-dissociate-nonce' ),
				'UID': jQuery( element ).closest( 'tr' ).attr( 'data-user-id' ),
				'CID': jQuery( element ).closest( 'tr' ).attr( 'data-customer-id' )
			}, function( response ) {
				jQuery( '#wps_customer_contacts .inside' ).removeClass( 'wps-bloc-loader wps-bloc-loading' );
			});
		}
	},

	wps_customer_contacts_change_default: function( event, element ) {
		var currentDefaultUser = jQuery( element ).closest( 'table' ).attr( 'data-default-user-id' );
		event.preventDefault();

		if ( ( 0 == currentDefaultUser ) || confirm( wpshopCrm.confirm_change_default_user ) ) {
			jQuery( '#wps_customer_contacts .inside' ).addClass( 'wps-bloc-loader wps-bloc-loading' ).load( ajaxurl, {
				'action': 'wps_customer_contacts_change_default',
				'_wpnonce': jQuery( element ).closest( 'table' ).attr( 'data-change-default-nonce' ),
				'UID': jQuery( element ).closest( 'tr' ).attr( 'data-user-id' ),
				'CID': jQuery( element ).closest( 'tr' ).attr( 'data-customer-id' ),
				'current_default_user_id': currentDefaultUser
			}, function( response ) {
				jQuery( '#wps_customer_contacts .inside' ).removeClass( 'wps-bloc-loader wps-bloc-loading' );
			});
		}
	}

};

jQuery( document ).ready( function() {
	wpshopCRM.init();
});
