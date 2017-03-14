<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-header-mini-cart wps-cart-activator">
	<div class="wps-mini-cart-header">
		<a href="<?php echo get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id ') ) ); ?>" class="wps-mini-cart-opener">
			<?php _e( 'My cart', 'wpshop' ); ?>
			<i class="wps-icon-basket"></i>
			<?php echo do_shortcode('[wps-numeration-cart]'); ?>
		</a>
		<span class="wps-mini-cart-free-shipping-alert"><?php echo wpshop_tools::create_custom_hook('wpshop_free_shipping_cost_alert'); ?></span>
	</div>
	<div class="wps-mini-cart-body">
		<?php echo $mini_cart_body; ?>
	</div>
</div>
