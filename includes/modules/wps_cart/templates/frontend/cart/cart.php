<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !$account_origin ) : ?>
<div id="wps_cart_error_container" class="wps-alert-error"></div>
<?php endif; ?>
<ul class="wps-fullcart">
	<li class="wps-clearfix cart_header">
		<div class="wps-cart-item-img"></div>

		<div class="wps-cart-item-content"><?php _e( 'Product name', 'wpshop'); ?></div>

		<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
		<div class="wps-cart-item-unit-price"><?php _e( 'P.U', 'wpshop' ); ?></div>
		<?php endif; ?>
		<?php if( $cart_option == 'simplified_et' ) : ?>
		<div class="wps-cart-item-unit-price"><?php _e( 'Unit price ET', 'wpshop' ); ?></div>
		<?php endif; ?>

		<div class="wps-cart-item-quantity"><?php _e( 'Qty', 'wpshop'); ?></div>

		<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
		<div class="wps-cart-item-price"><?php _e( 'Total', 'wpshop' ); ?></div>
		<?php endif; ?>
		<?php if( $cart_option == 'simplified_et' ) : ?>
		<div class="wps-cart-item-price"><?php _e( 'Total ET', 'wpshop' ); ?></div>
		<?php endif; ?>
		<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) : ?>
		<div class="wps-cart-item-close"></div>
		<?php endif; ?>
	</li>

	<?php
		foreach( $cart_items as $item_id => $item ) :
			$product_key = $item_id;
			/** Check if it's a product or a variation **/
			$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($item['item_id'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
			$output_order = array();
			if ( count($product_attribute_order_detail) > 0  && is_array($product_attribute_order_detail) ) {
				foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
					foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
						if ( !empty($attribute_def->code) )
							$output_order[$attribute_def->code] = $position;
					}
				}
			}
			$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $item['item_meta'], $output_order, 'cart' );
			ksort($variation_attribute_ordered['attribute_list']);
			$variations_indicator = '';
			if( !empty($variation_attribute_ordered['attribute_list']) ) {

				$variations_indicator .= '<ul class="wps-cart-item-variations" >';
				foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
					if ( !empty($attribute_variation_to_output) ) {
						$variations_indicator .= $attribute_variation_to_output;
					}
				}
				$variations_indicator = apply_filters( 'wps_cart_item_variation_list', $variations_indicator, $variation_attribute_ordered, $item, $oid );
				$variations_indicator .= '</ul>';

			}

			$item_title = $item['item_name'];
			$item_post_type = get_post_type( $item['item_id'] );
			if ( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION === $item_post_type ) {
				$parent_def = wpshop_products::get_parent_variation( $item['item_id'] );
				if ( ! empty( $parent_def ) && ! empty( $parent_def['parent_post'] ) ) {
					$parent_post = $parent_def['parent_post'];
					$item_id = $parent_post->ID;
					$item_title = $parent_post->post_title;
				}
			}

			$download_link = wps_download_file_ctr::get_product_download_link( $oid, $item );
			if ( false === $download_link ) {
				$download_link = '';
			} else {
				$download_link = '<a href="' . $download_link . '" target="_blank" class="wps-bton-fourth-mini-rounded">' . __( 'Download the product', 'wpshop' ) . '</a>';
			}

			/**	Check if product is an auto added product : don't display link to product, quantity and remover 	*/
			$auto_added_product = false;
			$item_options = get_post_meta( $item_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_options', true );
			if ( ! empty( $item_options['cart'] ) && ! empty( $item_options['cart']['auto_add'] ) && ( $item_options['cart']['auto_add'] == 'yes' ) ) :
				$auto_added_product = true;
			endif;

			require( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR,"frontend", "cart/cart", "item") );
		endforeach;
	?>
</ul>
<?php if( ! is_admin() ) {
	require_once( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR,"frontend", "cart/cart", "total") );
} else {
	require_once( WPS_CART_TPL_DIR . "frontend/cart/cart-total.php" );
} ?>
<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) : ?>
<?php echo apply_filters( 'wps_cart_footer_extra_content', ''); ?>
<?php endif?>
