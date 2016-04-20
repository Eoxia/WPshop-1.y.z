jQuery( document ).ready( function() {
	jQuery( "#wps_payment_mode_list_container" ).sortable();
	
	// Save payment configuration
	jQuery( document ).on( 'click', '.wps_save_payment_mode_configuration', function() {
		jQuery( this ).addClass( 'wps-bton-loading');
		jQuery( '#TB_closeWindowButton').click();
		jQuery( '#wps_payment_config_save_message').fadeIn( 'slow' );
		setTimeout( function() {
			jQuery( 'input[name="Submit"]').click();
		},500);
	});
	
	
	// Add a logo to payment mode
	jQuery( document ).on( 'click', '.add_logo_to_payment_mode', function(e) {
		e.preventDefault();
		var id = jQuery( this ).attr( 'id' );
		id = id.replace( 'add_logo_to_payment_mode_', '' );
		jQuery( this ).addClass( 'wps-bton-loading' );
		// Open media gallery
		var uploader_category = wp.media({
					multiple : false
				}).on('select', function() {
					var selected_picture = uploader_category.state().get( 'selection' );
					var attachment = selected_picture.first().toJSON();

					jQuery( '#wps_payment_mode_logo_' + id ).val( attachment.id );
					jQuery( '#wps_payment_mode_logo_container_' + id ).html( '<img src="' + attachment.url + '" alt="" width="70"/>' );
				}).open();
		
		jQuery( '.add_logo_to_payment_mode' ).removeClass( 'wps-bton-loading' );
	});
	
	jQuery( document ).on( 'change', '.wps_payment_active', function(e) {
		radio = '#' + jQuery(this).attr('id') + '_radio_default';
        if (!jQuery(this).is(':checked')) {
        	jQuery( radio ).attr('disabled','disabled');
        	jQuery( radio ).removeAttr('checked');
        }
        else {
        	jQuery( radio ).removeAttr('disabled');
        }
	});
});