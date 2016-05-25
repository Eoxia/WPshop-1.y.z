jQuery( document ).ready( function() {
	var xhr = null;


	/** Add a product to quotation **/
	jQuery( document ).on( 'click', '.add_product_to_order_quotation', function() {
		var id = jQuery( this ).attr('id').replace( 'add_product_to_cart_', '' );
		var qty = jQuery( '#add_product_to_order_qty_' + id ).val();
		if ( jQuery.isNumeric( qty) == false ) {
			qty = 1;
		}
		jQuery( '#add_to_cart_loader_' + id ).show();
		if ( xhr != null ) xhr.abort();
		var data = {
				action: "wps_add_product_to_quotation",
				product_id : id,
				order_id : jQuery( '#post_ID' ).val(),
				qty : qty
			};
		xhr = jQuery.post(ajaxurl, data, function( response ){
				if ( response['status'] ) {
					jQuery( '#add_to_cart_loader_' + id ).hide();
					var data = {
							action: "wps_order_refresh_in_admin",
							order_id : jQuery( '#post_ID' ).val()
						};
					jQuery.post(ajaxurl, data, function( response ){
							if ( response['status'] ) {
								jQuery('#wps_order_content_container').html( response['response'] );
							}
					}, 'json');
				}
				else {
					if ( response['product_with_variations'] ) {
						var url = ajaxurl+'?action=wps_orders_load_variations_container&pid='+id+'&oid='+jQuery( '#post_ID' ).val()+'&qty='+qty;
						tb_show("Choose your variations", url);
					}
				}
			}, 'json');
	});


	/** Action on button "Recalculate order in admin" **/
	jQuery( document ).on('click', '#wpshop_admin_order_recalculate', function() {
		update_order_product_content( jQuery( '#post_ID' ).val(), 0);
	});


	jQuery( document ).on( 'click', '.remove', function() {
		var id = jQuery(this).parent().parent().attr('id');
		id = id.replace( 'product_', '');
		update_order_product_content( jQuery( '#post_ID' ).val() , id);
	});



	/** Change Product list **/
	jQuery( document ).on( 'click', '.product_list_change', function() {
		var letter = jQuery( this ).attr('id').replace( 'products_', '' );
		jQuery( '#wps_orders_products_list_for_quotation_container' ).fadeOut( 'slow' );
		jQuery('#wps_products_list_change_loader').show();
		if ( xhr != null ) xhr.abort();
		var data = {
				action: "wps_change_product_list",
				letter : letter
			};
		xhr = jQuery.post(ajaxurl, data, function( response ){
				if ( response['status'] ) {
					jQuery( '#wps_orders_products_list_for_quotation_container' ).html( response['response'] );
					jQuery( '#wps_orders_products_list_for_quotation_container' ).fadeIn( 'slow' );
					jQuery('#wps_products_list_change_loader').hide();
				}
			}, 'json');
	});


	/** Add a product with varaition **/
	jQuery( document ).on('click', '#wps_order_product_with_variation', function() {
		jQuery('#wps_orders_add_to_cart_variation_loader').show();
		var form_options_add_to_cart = {
				dataType:		'json',
				success: 		function_after_form_success,
			};
		jQuery('#wpshop_add_to_cart_form').ajaxForm( form_options_add_to_cart ).submit();
	});


	jQuery( document ).on( 'click', '#wps_order_choose_customer', function( e ) {
		e.preventDefault();
		jQuery( this ).addClass( 'wps-bton-loading' );

		var data = {
				action: "wps_order_choose_customer",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				customer_id : jQuery( '#user_customer_id').val()
			};
		jQuery.post(ajaxurl, data, function( response ){
			if ( response['status'] ) {
				jQuery( '#wps_customer_id' ).val( jQuery( '#user_customer_id').val() );
				jQuery( '.wps_billing_data_container' ).html( response['billing_data'] );
				jQuery( '.wps_shipping_data_container' ).html( response['shipping_data'] );
				jQuery ( '#wps_order_choose_customer' ).removeClass( 'wps-bton-loading' );
			}
			else {
				alert( 'An error was occured...');
			}
		}, 'json');
	});


	/** Success actions of Ajax form **/
	function function_after_form_success() {
		jQuery( '.tb-close-icon' ).click();
		var data = {
				action: "wps_order_refresh_in_admin",
				order_id : jQuery( '#post_ID' ).val()
			};
		jQuery.post(ajaxurl, data, function( response ){
				if ( response['status'] ) {
					jQuery('#order_product_container').html( response['response'] );
				}
		}, 'json');

		jQuery( '.add_to_cart_loader' ).hide();
	}

	function update_order_product_content(order_id, product_to_delete){
		var product_list_qty_to_update = new Array();
		jQuery("input[name=productQty]").each(function(){
			product_list_qty_to_update.push(jQuery(this).attr("id").replace("wpshop_product_order_", "") + "_x_" + jQuery(this).val());
		});
		var data = {
				action: "wps_order_refresh_in_admin",
				order_id : order_id,
				product_to_delete : product_to_delete,
				product_to_update_qty : product_list_qty_to_update,
				order_shipping_cost : jQuery(".wpshop_order_shipping_cost_custom_admin").val()
			};
			jQuery.post(ajaxurl, data, function(response){
				if ( response['status'] ) {
					jQuery('#wps_order_content_container').html( response['response'] );
				}
			}, 'json');

	}



});
