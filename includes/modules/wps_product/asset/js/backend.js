/** modules/wps_product/asset/js/backend.js wps_product */

jQuery( document ).ready( function() {
	wps_product.init();
});

var wps_product = {
	init: function() {
		wps_product.event();
		wps_product.product_check_data();
	},

	event: function() {

	},

	product_check_data: function() {
		jQuery( '.wps-product-check-data-form' ).submit(function() {
			jQuery(this).ajaxSubmit({
				data: {
					action: 'save_products_prices',
				},
				success: function( response ) {
					jQuery( '#ui-id-8' ).html( response.template );
					jQuery( '#ui-id-8 form' ).append( '<h3>' + response.template_number + '</h3>' );
					wps_product.product_check_data();
				}
			});
			return false;
		});
	}
};
