jQuery( document ).ready( function() {

	/*if( jQuery('#wps_mass_products_edit_tab_container').length > 0 ) {
		jQuery( 'body' ).addClass( 'folded' );
	}
	else {
		jQuery( 'body' ).removeClass( 'folded' );
	}*/

	//--------------------------------------------------------------------------------

	function updater( element ) {
		element = (typeof element == 'undefined') ? jQuery( this ).closest( "tr" ).data( "id" ) : element;
		jQuery( '#trPid_' + element ).children( "td.wps-mass-interface-line-selector" ).children( '.wps-form-group' ).children( '.wps-form').children( 'center' ).children( "input[type=checkbox]" ).prop( "checked", true );
	}

	jQuery( document ).on( 'change', '.concurs select, .concurs input', updater );

	jQuery( document ).on( "click", ".add_concur", function() {
		var cloner = jQuery( this ).closest( "tr" ).prev();
		var id_p = cloner.data( "id" );
		var clone_string = cloner.clone().get( 0 ).outerHTML;
		clone_string = clone_string.replace( /%ID%/gi, jQuery(".concurs[data-id='" + id_p + "']").length - 1 )
		var clone = jQuery( clone_string );
		clone.find( ".is_row" ).val( 1 );
		clone.removeClass("cloner");
		cloner.before(clone);
		clone.show();
		clone.on( 'change', 'select, input', updater );
		updater( id_p );
		clone.find('.chosen_select_concur').chosen( WPSHOP_CHOSEN_ATTRS );
	} );

	jQuery( document ).on( "click", ".del_concur", function() {
		jQuery( this ).closest( "tr" ).remove();
	} );

	jQuery( document ).on( "click", ".datepicker_concur", function() {
		jQuery( this ).datepicker({dateFormat: 'yy-mm-dd'});
		jQuery( this ).datepicker('show');
	} );

	//--------------------------------------------------------------------------------

	/**	Trigger event on mass update pagination	*/
	jQuery( document ).on( "click", ".wps-mass-product-pagination li a", function( event ){
		event.preventDefault();
		var page_id = jQuery( this ).html();
		reload_list( jQuery( '#wps_mass_edit_products_default_attributes_set').val(), page_id );
		jQuery( '#wps_mass_edit_interface_current_page_id' ).val( page_id );

		jQuery( '.wps-alert-error, .wps-alert-success' ).hide();
	} );


	/** Trigger on change action on attributes set **/
	jQuery( document ).on( 'change', '#wps_mass_edit_products_default_attributes_set', function() {
		reload_list( jQuery( '#wps_mass_edit_products_default_attributes_set').val(), 1 );
		jQuery( '.wps-alert-error, .wps-alert-success' ).hide();
	});


	/**	Trigger event on text and textarea focus	*/
	jQuery( document ).on( 'focus', '.wps-product-mass-interface-table input[type="text"], .wps-product-mass-interface-table textarea', function() {
		jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( '.wps-form-group' ).children( '.wps-form').children( 'center' ).children( "input[type=checkbox]" ).prop( "checked", true );
	} );

	/**	Trigger event on dropdown change	*/
	jQuery( document ).on( 'change', '.wps-product-mass-interface-table select', function() {
		jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( '.wps-form-group' ).children( '.wps-form').children( 'center' ).children( "input[type=checkbox]" ).prop( "checked", true );
	});

	/**	Trigger event on radio button and checkboxes state change	*/
	jQuery( document ).on( 'click', '.wps-product-mass-interface-table input[type="radio"], .wps-product-mass-interface-table input[type="checkbox"]', function() {
		if( !jQuery( this ).hasClass( 'wps-save-product-checkbox' ) ) {
			jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( '.wps-form-group' ).children( '.wps-form').children( 'center' ).children( "input[type=checkbox]" ).prop( "checked", true );
		}
	});

	jQuery( document ).on( 'click', '.wps_add_picture_to_product_in_mass_interface', function() {
		jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( '.wps-form-group' ).children( '.wps-form').children( 'center' ).children( "input[type=checkbox]" ).prop( "checked", true );
	});

	jQuery( document ).on( 'click', '.wps_del_picture_to_product_in_mass_interface', function() {
		jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( '.wps-form-group' ).children( '.wps-form').children( 'center' ).children( "input[type=checkbox]" ).prop( "checked", true );
	});

	jQuery( document ).on( 'click', '.wps_add_files_to_product_in_mass_interface', function() {
		jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( '.wps-form-group' ).children( '.wps-form').children( 'center' ).children( "input[type=checkbox]" ).prop( "checked", true );
	});


	/**	Trigger event on new product button click	*/
	jQuery( document ).on( "click", "#wps-mass-interface-button-new-product", function( event ){
		event.preventDefault();
		var s = confirm(WPS_MASS_CONFIRMATION_NEW_PRODUCT);
		if( s == true ) {
			save_mass_interface();
			jQuery( '#wps_mass_products_edit_tab_container' ).animate( { 'opacity' : 0.15 }, 400, function() {
				var data = {
						action: "wps_mass_interface_new_product_creation",
						_wpnonce: jQuery( '#wps-mass-interface-button-new-product' ).data( 'nonce' ),
						attributes_set : jQuery( '#wps_mass_edit_products_default_attributes_set').val()
					};

					jQuery.post( ajaxurl, data, function( response ){
						if( response['status'] ) {
							reload_list( jQuery( '#wps_mass_edit_products_default_attributes_set').val(), 1 );
							jQuery( "#wps-mass-interface-button-new-product" ).removeClass( 'wps-bton-loading' );
						}
						else {
							jQuery( '.wps-alert-error, .wps-alert-success' ).hide();
							jQuery( 'html, body' ).animate( { 'scrollTop' : 0 }, 350, function() {
								jQuery('.wps-alert-error').html( WPS_MASS_ERROR_PRODUCT_CREATION );
								jQuery('.wps-alert-error').slideDown( 'slow' );
							});
							jQuery( '#wps_mass_products_edit_tab_container' ).animate( { 'opacity' : 1 }, 400 );
							jQuery( "#wps-mass-interface-button-new-product" ).removeClass( 'wps-bton-loading' );
						}
					}, 'json' );
			});
		}
	});



	/**	Trigger event on save button for sending product to update	*/
	jQuery( document ).on( 'click', '.wps-mass-interface-button-save', function() {
		save_mass_interface();
	});

	/**
	 * Upload picture
	 */
	jQuery( document ).on( 'click', '.wps_add_picture_to_product_in_mass_interface', function(e) {
		e.preventDefault();
		var id = jQuery( this ).attr( 'id' );
		id = id.replace( 'wps_add_picture_to_product_in_mass_interface_', '' );
		jQuery( this ).addClass( 'wps-bton-loading' );
		// Open media gallery
		var uploader_category = wp.media({
					multiple : false,
					title : jQuery( this ).html()
				}).on('select', function() {
					var selected_picture = uploader_category.state().get( 'selection' );
					var attachment = selected_picture.first().toJSON();

					jQuery( 'input[name="wps_mass_interface[' + id + '][picture]"]' ).val( attachment.id );
					jQuery( '#wps_mass_interface_picture_container_' + id ).html( '<img src="' + attachment.url + '" alt="" />' );
					jQuery( '#wps_add_picture_to_product_in_mass_interface_' + id ).hide();
					jQuery( '#wps_del_picture_to_product_in_mass_interface_' + id ).show();
				}).open();

		jQuery( '.wps_add_picture_to_product_in_mass_interface' ).removeClass( 'wps-bton-loading' );
	});

	jQuery( document ).on( 'click', '.wps_del_picture_to_product_in_mass_interface', function(e) {
		e.preventDefault();
		var id = jQuery( this ).attr( 'id' );
		id = id.replace( 'wps_del_picture_to_product_in_mass_interface_', '' );
		jQuery( 'input[name="wps_mass_interface[' + id + '][picture]"]' ).val( 'deleted' );
		jQuery( '#wps_mass_interface_picture_container_' + id ).html( '' );
		jQuery( '#wps_del_picture_to_product_in_mass_interface_' + id ).hide();
		jQuery( '#wps_add_picture_to_product_in_mass_interface_' + id ).show();
	});

	/**
	 * Upload Files
	 */
	jQuery( document ).on( 'click', '.wps_add_files_to_product_in_mass_interface', function(e) {
		e.preventDefault();
		var id = jQuery( this ).attr( 'id' );
		id = id.replace( 'wps_add_files_to_product_in_mass_interface_', '' );
		jQuery( this ).addClass( 'wps-bton-loading' );
		// Open media gallery
		var uploader = wp.media({
					multiple : true
				}).on('select', function() {
					var attachments = [];
					var selected_picture = uploader.state().get( 'selection' );
					selected_picture.map( function( attachment ) {
						attachment = attachment.toJSON();
						attachments.push( attachment );
					});
					var output = jQuery( 'input[name="wps_mass_interface[' + id + '][files]"]' ).val();
					jQuery( attachments ).each( function(k, v) {
						output += v.id + ',';
					});
					jQuery( 'input[name="wps_mass_interface[' + id + '][files]"]' ).val( output );
					reload_files_list( id );
				}).open();

		jQuery( '.wps_add_picture_to_product_in_mass_interface' ).removeClass( 'wps-bton-loading' );
	});

	/** Delete file **/
	jQuery( document ).on( 'click', '.wps-mass-delete-file', function() {
		var id = jQuery( this ).attr( 'id' );
		id = id.replace( 'wps-mass-delete-file-', '' );
		var data = {
					action: "wps_mass_delete_file",
					_wpnonce: jQuery( this ).data( 'nonce' ),
					file_id : id
				};
				jQuery.post(ajaxurl, data, function(response) {
					if ( response['status'] ) {
						reload_list( jQuery( '#wps_mass_edit_products_default_attributes_set').val(), jQuery( '#wps_mass_edit_interface_current_page_id').val() );
					}
					else {
						jQuery( '.wps-alert-error, .wps-alert-success' ).hide();
						jQuery( '.wps-alert-error' ).html( WPS_MASS_ERROR_INIT );
						jQuery('.wps-alert-error').slideDown( 'slow' );
						jQuery( '#wps_mass_products_edit_tab_container' ).animate( { 'opacity' : 1 }, 400 );
					}

				}, 'json');
	});

	/** Select All **/
	jQuery( document ).on( 'click', 'input[name=wps_product_quick_save_checkbox_column]', function() {
		if ( jQuery( this ).is( ":checked" ) ) {
			jQuery( '.wps-save-product-checkbox' ).prop( "checked", true );
			jQuery( 'input[name=wps_product_quick_save_checkbox_column]' ).prop( "checked", true );
		} else {
			jQuery( '.wps-save-product-checkbox' ).prop( "checked", false );
			jQuery( 'input[name=wps_product_quick_save_checkbox_column]' ).prop( "checked", false );
		}
	});

	/** Gestionnaire de produits en mass */
	jQuery( document ).on( 'click', '#wps_mass_products_edit_tab_container .submitdelete', function(e) {
		e.preventDefault();
		var id = jQuery( this ).attr( 'id' );
		id = id.replace( 'wps_mass_interface_post_delete_', '' );
		jQuery( '#wps_mass_interface_post_delete_input_' + id ).val( "true" );
		jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( '.wps-form-group' ).children( '.wps-form').children( 'center' ).children( "input[type=checkbox]" ).prop( "checked", true );
		jQuery( this ).closest( "tr" ).children( ".wps_mass_interface_line" ).hide();
		jQuery( this ).closest( "tr" ).children( ".wps_mass_interface_line_deleted" ).show();
	});

	jQuery( document ).on( 'click', '.wps_mass_interface_post_deleted_cancel', function(e) {
		e.preventDefault();
		var id = jQuery( this ).attr( 'id' );
		id = id.replace( 'wps_mass_interface_post_delete_cancel_', '' );
		jQuery( '#wps_mass_interface_post_delete_input_' + id ).val( "false" );
		jQuery( this ).closest( "tr" ).children( ".wps_mass_interface_line" ).show();
		jQuery( this ).closest( "tr" ).children( ".wps_mass_interface_line_deleted" ).hide();
	});

	/**
	 * Reload Product list
	 */
	function reload_list( attribute_set, page_id ) {
		jQuery( '#wps_mass_products_edit_tab_container' ).animate( { 'opacity' : 0.15 }, 400, function() {
			var data = {
					action: "wps_mass_edit_change_page",
					_wpnonce: jQuery( '#wps_mass_products_edit_tab_container' ).data( 'nonce' ),
					page_id : page_id,
					att_set_id : attribute_set
				};
				jQuery.post(ajaxurl, data, function(response) {
					if ( response['status'] ) {
						jQuery( '#wps_mass_products_edit_tab_container' ).html( response['response'] );
						jQuery( '.wps_mass_products_edit_pagination_container' ).html( response['pagination'] );
						jQuery( '#wps_mass_products_edit_tab_container' ).animate( { 'opacity' : 1 }, 400, function() {
							jQuery( 'html, body' ).animate( { 'scrollTop' : 0 }, 350 );
						});
					}
					else {
						jQuery( '.wps-alert-error, .wps-alert-success' ).hide();
						jQuery( '.wps-alert-error' ).html( WPS_MASS_ERROR_INIT );
						jQuery('.wps-alert-error').slideDown( 'slow' );
						jQuery( '#wps_mass_products_edit_tab_container' ).animate( { 'opacity' : 1 }, 400 );
					}

				}, 'json');
		});
	}

	/**
	* Update files list
	**/
	function reload_files_list( product_id ) {
			var data = {
					action: "wps_mass_edit_update_files_list",
					_wpnonce: jQuery( '#wps_mass_update_product_file_list_' + product_id ).data( 'nonce' ),
					product_id : product_id,
					files : jQuery( 'input[name="wps_mass_interface[' + product_id + '][files]"]' ).val()
				};
				jQuery.post(ajaxurl, data, function(response) {
					if ( response['status'] ) {
						jQuery( '#wps_mass_update_product_file_list_' + product_id ).html( response['response'] );
						jQuery( '.wps_add_files_to_product_in_mass_interface' ).removeClass( 'wps-bton-loading' );
					}
					else {
						jQuery( '.wps-alert-error, .wps-alert-success' ).hide();
						jQuery( '.wps-alert-error' ).html( WPS_MASS_ERROR_INIT );
						jQuery('.wps-alert-error').slideDown( 'slow' );
						jQuery( '.wps_add_files_to_product_in_mass_interface' ).removeClass( 'wps-bton-loading' );
					}

				}, 'json');
	}

	function save_mass_interface() {
		// Check if products are selected for sending form
		var nb_of_product_to_save = 0;
		jQuery( ".wps-mass-interface-line-selector input[type=checkbox]" ).each( function(){
			if ( jQuery( this ).is( ":checked" ) ) {
				nb_of_product_to_save += 1;
			}
		});

		if( nb_of_product_to_save == 0 ) {
			// Error display
			jQuery( '.wps-alert-error, .wps-alert-success' ).hide();
			jQuery( 'html, body' ).animate( { 'scrollTop' : 0 }, 350, function() {
				jQuery( '.wps-alert-error' ).html( WPS_MASS_ERROR_PRODUCT_SAVE );
				jQuery( '.wps-alert-error' ).slideDown( 'slow' );
			} );
		}
		else {
			// Send form
			jQuery('#wps_mass_edit_product_form').ajaxSubmit({
				dataType:  'json',
				beforeSubmit : function() {
					jQuery( '.wps-mass-interface-button-save' ).addClass( 'wps-bton-loading' );
				},
		        success: function( response ) {
		        	if( response['status'] ) {
		        		// Display confirmation message
		        		jQuery( '.wps-alert-error, .wps-alert-success' ).hide();
		        		jQuery( 'html, body' ).animate( { 'scrollTop' : 0 }, 350, function() {
							jQuery( '.wps-alert-success' ).html(  response['response'] );
							jQuery( '.wps-alert-success' ).slideDown( 'slow' );
							jQuery( '.wps-save-product-checkbox' ).attr( 'ckecked', '' );
							var page_id = jQuery( '#wps_mass_edit_interface_current_page_id' ).val();
			        		reload_list( jQuery( '#wps_mass_edit_products_default_attributes_set').val(), page_id );
		        		});
		        		jQuery( '.wps-mass-interface-button-save' ).removeClass( 'wps-bton-loading' );
		        	}
		        	else {
		        		jQuery( '.wps-alert-error, .wps-alert-success' ).hide();
		        		jQuery( 'html, body' ).animate( { 'scrollTop' : 0 }, 350, function() {
							jQuery( '.wps-alert-error' ).html( response['response'] );
							jQuery( '.wps-alert-error' ).slideDown( 'slow' );
		        		});
		        		jQuery( '.wps-mass-interface-button-save' ).removeClass( 'wps-bton-loading' );
		        	}
		        },
			});
		}
	}
});
