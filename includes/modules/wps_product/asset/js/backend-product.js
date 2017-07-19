jQuery( document ).ready( function() {
	jQuery( document ).on( 'change', '.wpshop-admin-post-type-wpshop_product .wpshop_form_input_element input,.wpshop-admin-post-type-wpshop_product .wpshop_form_input_element select,.wpshop-admin-post-type-wpshop_product .wpshop_form_input_element textarea', function() {
		console.log( 'beforeunload' );
		jQuery( window ).on( 'beforeunload.edit-post', function() {
			return true;
		});
	});
});
