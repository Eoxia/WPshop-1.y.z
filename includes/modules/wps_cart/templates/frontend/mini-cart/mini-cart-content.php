<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<span class="wps-mini-cart-body-title">
<strong><?php echo $total_cart_item; ?></strong><?php _e( 'item(s)', 'wpshop'); ?><span><strong><?php echo number_format( $total_cart, 2, '.', ''); ?></strong><?php echo $currency; ?></span>
</span>
<div class="wps-cart-resume">
	<ul class="wps-fullcart">
		<?php foreach( $cart_items as $item_id => $item ) :
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
			<li class="wps-clearfix" id="wps_min_cart_product_<?php echo $item['item_id']; ?>">
				<div class="wps-cart-item-img">
					<a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>">
						<?php echo get_the_post_thumbnail( $item_id, 'thumbnail', array('class' => 'wps-circlerounded') ); ?>
					</a>
				</div>
				<div class="wps-cart-item-content">
					<a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>">
						<?php echo $item_title; ?>
					</a>
				</div>
				<div class="wps-cart-item-price">
			    	<span class="wps-price"><?php echo wpshop_tools::formate_number( $item['item_total_ttc'] ); ?> <span><?php echo $currency; ?></span></span>
			    	<span class="wps-tva"><?php _e( 'ATI', 'wpshop'); ?></span><br>
				</div>
				<div class="wps-cart-item-close">
					<button type="button" class="wps-bton-icon-close wps_mini_cart_delete_product" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>"></button>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
	<p><?php _e( 'Shipping cost ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $shipping_cost_ati ); ?></strong><?php echo $currency; ?></span></p>
	<?php if ( !empty( $cart_content['coupon_id']) ) : ?>
		<p><?php _e( 'Total ATI before discount', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $order_total_before_discount ); ?></strong><?php echo $currency; ?></span></p>
		<p><?php _e( 'Discount', 'wpshop'); ?><span class="wps-inline-alignRight"><strong><?php echo wpshop_tools::formate_number( $coupon_value ); ?></strong><?php echo $currency; ?></span></p>
	<?php endif; ?>
	<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-inline-alignRight"><strong><?php echo wpshop_tools::formate_number( $total_ati ); ?></strong><?php echo $currency; ?></span></p>
	<a href="<?php echo get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ); ?>" role="button" class="wps-bton-first"><?php _e( 'Order', 'wpshop' ); ?></a>
</div>
