/*
 * Event for print barcode in print button click
 */
jQuery( document ).on( 'click', '#print_barcode', function(e) {
	jQuery("img#barcode").printElement();
} );


jQuery( document ).on('click', '#display_barcode', function(e) {
	post_ID = jQuery('#post_ID')[0].value;

	if ( jQuery('#wps_barcode_coupons').length ) {
		display_barcode(post_ID, 'barcode_img_coupons', jQuery( this ).data( 'nonce' ) );
	}
	else if ( jQuery('#wps_barcode_product').length ) {
		display_barcode(post_ID, 'barcode_img_product', jQuery( this ).data( 'nonce' ) );
	}

});

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

function display_barcode(post_ID, action, _wpnonce) {
	jQuery.post(ajaxurl, {
			'action': action,
			'_wpnonce': _wpnonce,
			'postID': post_ID
		}, function(data){
			jQuery('#display_barcode').remove();

			if ( action === 'barcode_img_product' ) {
				jQuery('#wps_barcode_product .inside').append(data.img);
			}

			else if (action === 'barcode_img_coupons') {
				jQuery('#wps_barcode_coupons .inside').append(data.img);
			}

	       }, 'json');
};

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
