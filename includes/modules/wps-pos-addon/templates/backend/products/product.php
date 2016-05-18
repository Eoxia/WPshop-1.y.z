<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
	<tr data-nonce="<?php echo wp_create_nonce( 'ajax_pos_product_variation_selection' ); ?>" class="wps-pos-addon-product wps-pos-addon-product-line" data-id="<?php echo $product['ID']; ?>" data-subtype="<?php echo ( !empty( $product_variation_definition ) ? 'variations' : 'simple' ); ?>" >
		<td>#<?php echo $product['ID']; ?> - <?php echo $product['product_name']; ?><br/><?php _e('Barcode', 'wps-pos-i18n'); ?> : <?php echo $product['product_barcode'];?></td>
		<td class="wpshop_pos_addon_price"><?php echo wpshop_prices::get_product_price( $product_data, 'price_display', 'complete_sheet'); ?></td>
		<td>
			<!-- <a class="wps-pos-product-edition-link" target="wps_pos_product_edition" title="<?php _e( 'Edit this product', 'wps-pos-i18n' ); ?>" href="<?php echo admin_url( 'post.php?post=' . $product['ID'] . '&action=edit'); ?>" ><i class="dashicons dashicons-edit" ></i></a> -->
			<button class="wps-bton-first-rounded wps-pos-add-to-cart-button" type="button" data-type="product" data-subtype="<?php echo ( !empty( $product_variation_definition ) ? 'variations' : 'simple' ); ?>" data-id="<?php echo $product['ID']; ?>" > <i class="dashicons dashicons-cart" title="<?php _e( 'Choose this product', 'wps-pos-i18n' ); ?>" ></i></button>
		</td>
	</tr>
