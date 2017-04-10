<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<!-- <span class="wps-mini-cart-body-title"><strong><?php echo $total_cart_item; ?></strong><?php _e( 'item(s)', 'wpshop'); ?><span><strong><?php echo number_format( $total_cart, 2, '.', ''); ?></strong> <?php echo $currency; ?></span></span>-->
<div>
	<ul>
		<?php foreach( $cart_items as $item_id => $item ) : ?>
		<?php
			$item_post_type = get_post_type( $item_id );
			if ( $item_post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
				$parent_def = wpshop_products::get_parent_variation( $item_id );
				$parent_post = $parent_def['parent_post'];
				$item_id = $parent_post->ID;
				$item_title =  $parent_post->post_title;
			}
			else {
				$item_title = $item['item_name'];
			}
		?>
		<li id="wps_product_<?php echo $item_id; ?>">
			<div class="wps-cart-item-img">
				<a href="<?php echo get_permalink( $item_id ); ?>" title="">
					<?php echo get_the_post_thumbnail( $item_id, 'thumbnail' ); ?>
				</a>
			</div>
			<div class="wps-cart-item-content">
				<a href="#" title="">
					<?php echo $item_title; ?>
				</a>
				<?php
				$variations_indicator = '';
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
				if( !empty($variation_attribute_ordered['attribute_list']) ) {
					$variations_indicator .= '<ul class="wps-cart-item-variations" >';
					foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
						if ( !empty($attribute_variation_to_output) ) {
							$variations_indicator .= $attribute_variation_to_output;
						}
					}
					$variations_indicator = apply_filters( 'wps_cart_item_variation_list', $variations_indicator, $variation_attribute_ordered, $item, 0 );
					$variations_indicator .= '</ul>';
				}
				echo $variations_indicator;
				?>
			</div>
			<div class="wps-cart-item-price">
			   	<span class="wps-price"><?php echo wpshop_tools::formate_number( $item['item_total_ttc'] ); ?><span> <?php echo $currency; ?></span></span>
			   	<span class="wps-tva"><?php _e( 'ATI', 'wpshop'); ?></span><br>
			</div>
			<div class="wps-cart-item-close">
				<button type="button" class="wps-bton-icon wps_cart_delete_product" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>"><i class="wps-icon-trash"></i></button>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<p><?php _e( 'Shipping cost', 'wpshop' ); ?><span class="wps-inline-alignRight"><strong><?php echo wpshop_tools::formate_number( $shipping_cost_ati ); ?></strong>€</span></p>

<?php if ( !empty( $cart_content['coupon_id']) ) : ?>
	<p><?php _e( 'Total ATI before discount', 'wpshop' ); ?><span class="wps-inline-alignRight"><strong><?php echo wpshop_tools::formate_number( $order_total_before_discount ); ?></strong> <?php echo $currency; ?></span></p>
	<p><?php _e( 'Discount', 'wpshop' ); ?><span class="wps-inline-alignRight"><strong><?php echo wpshop_tools::formate_number( $coupon_value ); ?></strong> <?php echo $currency; ?></span></p>
<?php endif; ?>

<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-inline-alignRight"><strong><?php echo wpshop_tools::formate_number( $total_ati ); ?></strong> <?php echo $currency; ?></span></p>
<button class="wps-bton-second-halfwidth wpsjq-closeFixedCart"><i class="wps-icon-arrowleft"></i><?php _e( 'Return', 'wpshop'); ?></button>
<a href="<?php echo get_permalink( wpshop_tools::get_page_id( get_option('wpshop_cart_page_id') ) ); ?>" class="wps-bton-first-halfwidth" role="button"><i class="wps-icon-paiement"></i><?php _e( 'Order', 'wpshop'); ?></a>
