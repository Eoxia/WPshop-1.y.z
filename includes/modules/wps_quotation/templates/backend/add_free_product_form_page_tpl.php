<?php if ( !defined( 'ABSPATH' ) ) exit; ?>
<form id="wps_create_free_product" data-nonce="<?php echo wp_create_nonce( 'wps_add_product_to_order_admin' ); ?>" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">

	<input type="hidden" name="action" value="wps_create_new_free_product">
	<?php wp_nonce_field( 'wps_create_new_free_product' ); ?>

	<div class="wps-boxed" style="margin-top:10px;">
		<span class="wps-h5"><?php _e( 'Product', 'wpshop'); ?></span>

		<div class="wpshop_cls" >
			<div class="wpshop_form_label _product_title_label alignleft" >
				<label for="attribute_product_title" ><?php _e( 'Product name', 'wpshop'); ?> <span class="wpshop_required" >*</span></label>
			</div>
			<div class="wpshop_form_input_element _product_title_input alignleft" >
				<input type="text" name="post_title" id="post_title" value="" class="wpshop_product_attribute_post_title wpshop_attributes_display">
			</div>
		</div>

		<div class="wpshop_cls" >
			<div class="wpshop_form_label _description_label alignleft" >
				<label for="attribute_description" ><?php _e( 'Description', 'wpshop'); ?></label>
			</div>
			<div class="wpshop_form_input_element _description_input alignleft" >
				<textarea name="post_content" id="post_content" class="wpshop_product_post_content wpshop_attributes_display" rows="2"></textarea>
			</div>
		</div>

	</div>

	<?php
		$attribute_set_list = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code('wpshop_product'));
		$id_attribute_set = null;
		foreach( $attribute_set_list as $attribute_set ) {
			if( $attribute_set->slug == 'free_product' || $attribute_set->name == 'free_product' ) {
				$id_attribute_set = $attribute_set->id;
				break;
			}
		}
		$list = empty( $id_attribute_set ) ? array() : wpshop_attributes_set::getAttributeSetDetails($id_attribute_set);
		foreach($list as $group) {
	?>

	<div class="wps-boxed">
		<span class="wps-h5"><?php echo $group['name']; ?></span>
		<?php
			foreach($group['attribut'] as $attribute_key => $attribute) {
				if( !empty($attribute_key) && $attribute->status == 'valid' ) {
					$output = wpshop_attributes::display_attribute($attribute->code);
					echo $output['field'];
				}
			}
		?>
	</div>

	<?php
		}
	?>

	<button class="wps-bton-first-mini-rounded alignRight" id="add_free_product_form_bton"><?php _e( 'Add product', 'wpshop'); ?></button>

</form>

<script>
	jQuery( document ).ready( function() {
		 var options = {
			        beforeSubmit: showRequest,
			        success: showResponse,
			        resetForm: false
			    };
		jQuery( '#wps_create_free_product' ).ajaxForm(options);
	});
	function showRequest() {
		jQuery( '#add_free_product_form_bton' ).addClass( 'wps-bton-loading' );
	}
	function showResponse(response, status) {
		jQuery( '#add_free_product_form_bton' ).removeClass( 'wps-bton-loading' );
		var objJSON = JSON.parse(response);
		if(objJSON.status) {
			parent.eval('tb_remove()');
			addProductToOrder(objJSON.pid, jQuery( '#wps_create_free_product' ).data( 'nonce' ) );
		} else {
			alert(objJSON.message);
		}
	}
	function addProductToOrder(pid, _wpnonce) {
		if(pid != -1) {
			var data = {
					action : "wps_add_product_to_order_admin",
					_wpnonce: _wpnonce,
					pid : pid,
					oid : jQuery( '#post_ID' ).val(),
					qty : 1
				};
				jQuery.post(ajaxurl, data, function(response){
					if(response['status']) {
						refresh_cart();
					} else {
						alert( response['response'] );
					}
				}, 'json');
		}
	}
</script>
