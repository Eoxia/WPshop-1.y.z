<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<!-- <h3 class="wps-h3"><?php _e( 'Cart summary', 'wpshop' ); ?></h3> -->
<ul class="wps-cart-resume-list">
	<?php foreach( $cart_items as $item_id => $item ) :
			$product_key = $item_id;
			$item_post_type = get_post_type( $item_id );
			if ( $item_post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
				$parent_def = wpshop_products::get_parent_variation( $item_id );
				$parent_post = $parent_def['parent_post'];
				$item_id = $parent_post->ID;
				$item_title =  $parent_post->post_title;
			} else {
				$item_title = $item['item_name'];
			}
			?>
			<li data-id="<?php echo $product_key; ?>" >
				<div class="wps-cart-item-img">
					<a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>">
						<?php echo get_the_post_thumbnail( $item_id, 'thumbnail' ); ?>
					</a>
				</div>
				<div class="wps-cart-item-content">
					<a class="wps-cart-item-title" href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>">
						<?php echo $item_title; ?>
					</a>
					<?php //echo $variations_indicator; ?>
					<div class="wps-productQtyForm ">
						<a href="" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" class="wps-cart-reduce-product-qty"><i class="wps-icon-minus"></i></a>
						<input type="text" name="french-hens" id="wps-cart-product-qty-<?php echo $product_key; ?>" value="<?php echo $item['item_qty']; ?>" class="wps-circlerounded wps-cart-product-qty">
						<a href="" class="wps-cart-add-product-qty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>"><i class="wps-icon-plus"></i></a>
			    	</div>
				</div>
				<div class="wps-cart-item-price">
			    	<span class="wps-price"><?php echo wpshop_tools::formate_number( $item['item_total_ttc'] ); ?> <span><?php echo $currency; ?></span></span>
			    	<span class="wps-tva"><?php _e( 'ATI', 'wpshop'); ?></span><br>
				</div>
			</li>
	<?php endforeach; ?>
</ul>
<div class="wps-cart-cartouche">
	<div class="wps-cart-message">
		<?php echo do_shortcode('[wps_apply_coupon]'); ?>
	</div>
	<div class="wps-cart-total">
		<p><?php _e( 'Shipping cost ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $shipping_cost_ati ); ?></strong><?php echo $currency; ?></span></p>
		<?php if ( !empty( $cart_content['coupon_id']) ) : ?>
			<p><?php _e( 'Total ATI before discount', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $order_total_before_discount ); ?></strong><?php echo $currency; ?></span></p>
			<p><?php _e( 'Discount', 'wpshop'); ?><span class="wps-inline-alignRight"><strong><?php echo wpshop_tools::formate_number( $coupon_value ); ?></strong><?php echo $currency; ?></span></p>
		<?php endif; ?>
		<p class="wps-hightlight">
			<?php _e( 'Total ATI', 'wpshop'); ?>
			<span class="wps-inline-alignRight">
				<strong><?php echo wpshop_tools::formate_number( $total_ati ); ?></strong>
				<?php echo $currency; ?>
			</span>
		</p>
	</div><!-- wp-cart-total -->
</div><!-- wps-cart-cartouche -->

<?php $wps_payment_mode = get_option('wps_payment_mode'); ?>
<?php if( isset( $wps_payment_mode['mode']['paypal']['active'] ) ): ?>
	<div class="wps-secured-logos">
		<span class="wps-logo-paypal"></span>
		<span class="wps-logo-visa"></span>
		<span class="wps-logo-mastercard"></span>
	</div>
<?php endif; ?>
