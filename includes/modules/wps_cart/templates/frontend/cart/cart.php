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
			$item_post_type = get_post_type( $item['item_id'] );
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
				$variations_indicator .= '</ul>';

			}
			$parent_def = array();
			$item_title = $item['item_name'];
			$item_id = $item['item_id'];
			if ( $item_post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
				$parent_def = wpshop_products::get_parent_variation( $item['item_id'] );
				if( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
					$parent_post = $parent_def['parent_post'];
					$item_id = $parent_post->ID;
					$item_title =  $parent_post->post_title;
				}
			}

			/** Downloadable link in Order recap **/
			$download_link = '';
			if( !empty($parent_def) ) {
				$parent_meta = $parent_def['parent_post_meta'];
				if ( !empty($parent_meta['is_downloadable_']) ) {
					$query = $wpdb->prepare( 'SELECT value FROM '. WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS .' WHERE id = %d', $parent_meta['is_downloadable_'] );
					$downloadable_option_value = $wpdb->get_var( $query );
					if ( !empty( $downloadable_option_value) ) {
						$item['item_is_downloadable_'] = $downloadable_option_value;
					}
				}
			}

			if ( !empty($item) && !empty($item['item_is_downloadable_']) && ( strtolower( __( $item['item_is_downloadable_'], 'wpshop') ) == strtolower( __('Yes', 'wpshop') ) ) ) {
				$item_id_for_download = $item_id;
				$download_codes = get_user_meta( get_current_user_id(), '_order_download_codes_'.$oid, true);
				/**	Check if the current product exist into download code list, if not check if there is a composition between parent product and children product	*/
				if ( empty( $download_codes[$item_id_for_download] ) ) {
					$item_id_component = explode( "__", $item_id_for_download );
					if ( !empty( $item_id_component ) && ( $item_id_component[ 0 ] != $item_id_for_download ) ) {
						$item_id_for_download = $item_id_component[ 0 ];
					}
					else if ( !empty( $download_codes[ $item['item_id'] ] ) ) {
						$item_id_for_download = $item['item_id'];
					}
				}

				if ( !empty($download_codes) && !empty($download_codes[$item_id_for_download]) && !empty($download_codes[$item_id_for_download]['download_code']) ) {
					$download_link = '<a href="' .WPSHOP_URL. '/download_file.php?oid=' .$oid. '&amp;download=' .$download_codes[$item_id_for_download]['download_code']. '" target="_blank" class="wps-bton-fourth-mini-rounded">' .__('Download the product','wpshop'). '</a>';
				}
			}


			/**	Check if product is an auto added product : don't display link to product, quantity and remover 	*/
			$auto_added_product = false;
			$item_options = get_post_meta( $item_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_options', true );
			if ( !empty($item_options['cart']) && !empty($item_options['cart']['auto_add']) && ($item_options['cart']['auto_add'] == 'yes')) :
				$auto_added_product = true;
			endif;

			require( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR,"frontend", "cart/cart", "item") );
		endforeach;
	?>
</ul>
<?php require_once( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR,"frontend", "cart/cart", "total") ); ?>
<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) : ?>
<?php echo apply_filters( 'wps_cart_footer_extra_content', ''); ?>
<?php endif?>

