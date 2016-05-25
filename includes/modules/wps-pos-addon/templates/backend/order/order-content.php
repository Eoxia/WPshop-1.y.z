<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
	<ul class="wps-fullcart">
		<li class="wps-clearfix cart_header">
			<div class="wps-cart-item-content"><?php _e( 'Product name', 'wpshop'); ?></div>
			<div class="wps-cart-item-quantity"><?php _e( 'Qty', 'wpshop'); ?></div>
			<div class="wps-cart-item-unit-price"><?php _e( 'P.U ATI', 'wpshop' ); ?></div>
			<div class="wps-cart-item-price"><?php _e( 'Total ATI', 'wpshop' ); ?></div>
			<div class="wps-cart-item-close"></div>
		</li>

	<?php if ( !empty( $order_items ) ) : ?>
		<?php foreach ( $order_items as $order_item_id => $order_item ) : ?>
		<li id="wps_product_<?php echo $order_item_id; ?>" class="wps-clearfix cart-item" >
			<div class="wps-cart-item-content">
				<?php echo wpshop_tools::trunk( $order_item['item_name'], 50 ); ?>
				<?php
					$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta( $order_item['item_id'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true ) );
					$output_order = array();
					if ( count($product_attribute_order_detail) > 0  && is_array($product_attribute_order_detail) ) {
						foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
							foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
								if ( !empty($attribute_def->code) )
									$output_order[$attribute_def->code] = $position;
							}
						}
					}
					$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $order_item['item_meta'], $output_order, 'cart' );
					ksort($variation_attribute_ordered['attribute_list']);
				?>
				<?php if ( !empty( $variation_attribute_ordered[ 'attribute_list' ] ) ) : ?>
					<ul class="wps-cart-item-variations" >
				<?php foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) : ?>
					<?php if ( !empty( $attribute_variation_to_output ) ) : ?>
						<?php echo $attribute_variation_to_output; ?>
					<?php endif; ?>
				<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<div><button class="wps-bton-second-mini-rounded item_qty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" data-action="decrease" data-id="<?php echo $order_item_id; ?>" type="button" >-</button><input type="text" class="wpspos-dashboard-order-summary_qty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" data-id="<?php echo $order_item_id; ?>" id="item_qty_<?php echo $order_item_id; ?>" value="<?php echo $order_item['item_qty']; ?>" /><button class="wps-bton-second-mini-rounded item_qty" data-action="increase" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" data-id="<?php echo $order_item_id; ?>" type="button" >+</button></td></div>
			<div><input type="text"  class="wpspos-dashboard-order-summary_unit_price" id="price_<?php echo $order_item_id; ?>" value="<?php echo number_format($order_item['item_pu_ttc'], 2, '.', ''); ?>" /><?php echo wpshop_tools::wpshop_get_currency(); ?></td></div>
			<div><?php echo number_format($order_item['item_total_ttc'], 2, '.', ''); ?> <?php echo wpshop_tools::wpshop_get_currency(); ?></div>
			<div><button data-id="<?php echo $order_item_id; ?>" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" class="wps-bton-icon-close wps-pos-delete-product-of-order" type="button"></button></div>
		</li>
		<?php endforeach; ?>
	<?php endif; ?>

	</ul>

	<div class="wps-gridwrapper2-padded">
		<div>&nbsp;</div>
		<div class="alignright" >
			<div class="wps-boxed" >
				<?php if( !empty($cart_content['order_tva']) ) : ?>

					<?php foreach( $cart_content['order_tva'] as $order_vat_rate => $order_vat_value ) : ?>
						<?php if( $order_vat_rate != 'VAT_shipping_cost') : ?>
				<p>
					<?php printf( __( 'VAT (%s %%)', 'wpshop'), $order_vat_rate); ?>
					<span class="alignright">
						<strong><?php echo wpshop_tools::formate_number( $order_vat_value ); ?></strong> <?php echo $currency; ?>
					</span>
				</p>
						<?php endif; ?>
					<?php endforeach; ?>

				<?php endif; ?>

				<p class="wps-hightlight"><?php _e( 'Total ET', 'wpshop'); ?><span class="alignright"><strong><?php echo number_format($cart_content['order_total_ht'], 2, '.', ''); ?></strong> <?php echo wpshop_tools::wpshop_get_currency(); ?></span></p>
				<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="alignright"><strong><?php echo wpshop_tools::formate_number( ( !empty( $cart_content['order_amount_to_pay_now'] ) && !empty($oid) && $cart_content['order_amount_to_pay_now'] > 0 ) ? $cart_content['order_amount_to_pay_now'] : ( (!empty($cart_content['order_grand_total']) ) ? $cart_content['order_grand_total'] : 0 ) ); ?></strong> <?php echo wpshop_tools::wpshop_get_currency(); ?></span></p>
			</div>
			<a title="<?php _e( 'Finalize order', 'wps-pos-i18n' ); ?>" href="<?php print wp_nonce_url( admin_url( 'admin-ajax.php?action=wpspos-finalize-order&width=560&height=420' ), 'wps_pos_finalize_order', '_wpnonce' ); ?>" class="thickbox wps-bton-first-rounded alignright" id="wpspos-finalize-order" ><?php _e( 'Finalize order', 'wps-pos-i18n' ); ?></a>
			<a href="<?php echo admin_url( 'admin.php?page=wps-pos&new_order=yes' ); ?>" class="wps-bton-second-rounded alignright" id="wpspos-neworder" ><?php _e( 'Empty order', 'wps-pos-i18n' ); ?></a>
		</div>
	</div>

	<div class="wps-pos-order-finalization" >
		<div class="wpspos-order-final-step-container" ></div>
	</div>
