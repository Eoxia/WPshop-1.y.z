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
			</div>
			<div class="wps-cart-item-price">
			   	<span class="wps-price"><?php echo wpshop_tools::formate_number( $item['item_total_ttc'] ); ?><span> <?php echo $currency; ?></span></span>
			   	<span class="wps-tva"><?php _e( 'ATI', 'wpshop'); ?></span><br>
			</div>
			<div class="wps-cart-item-close">
				<button type="button" class="wps-bton-icon wps_cart_delete_product"><i class="wps-icon-trash"></i></button>
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
