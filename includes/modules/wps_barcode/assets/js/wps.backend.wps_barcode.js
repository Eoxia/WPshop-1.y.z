/*
 * Event for print barcode in print button click
 */
jQuery( document ).on( 'click', '#print_barcode', function(e) {
	jQuery("img#barcode").printElement();
} );

/*
 * Ready fonction for listen all event
 */
jQuery(document).ready(function(){
	barcode_options_display( jQuery("select#barcode_type").val() );
	
	/*
	 * Event for change select option
	 */
	jQuery('#barcode_type').on('change', function() {
		barcode_options_display( jQuery("select#barcode_type").val() );
	} );
} );

/*
 * Change display section
*/
function barcode_options_display(val) {
	if ( val == 'internal' ) {
		if ( jQuery("div.wpshop_admin_box_options_barcode_normal").is(':visible') ) {
			jQuery("div.wpshop_admin_box_options_barcode_normal").slideToggle("slow");
		}
		
		if ( jQuery("div.wpshop_admin_box_options_barcode_internal").is(':hidden') ) {
			jQuery("div.wpshop_admin_box_options_barcode_internal").slideToggle("slow");
		}
	}
	else if ( val == 'normal' ) {
		if ( jQuery("div.wpshop_admin_box_options_barcode_normal").is(':hidden') ) {
			jQuery("div.wpshop_admin_box_options_barcode_normal").slideToggle("slow");
		}
		
		if ( jQuery("div.wpshop_admin_box_options_barcode_internal").is(':visible') ) {
			jQuery("div.wpshop_admin_box_options_barcode_internal").slideToggle("slow");
		}
	}
}
