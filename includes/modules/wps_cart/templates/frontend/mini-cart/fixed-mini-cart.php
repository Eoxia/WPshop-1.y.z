<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-fixed-tool-bar wps-cart-activator">
	<div class="wps-mini-cart wps-cart-resume">
		<div class="wps-mini-cart-header">
			<span class="wps-h1"><?php _e( 'My cart', 'wpshop'); ?></span>
				<span href="#" class="wps-mini-cart-opener wps-mini-cart-bton">
				<span><button type="button" class="wps-bton-icon-fullrounded wpsjq-closeFixedCart"><i class="wps-icon-close"></i></button></span>
			</span>
		</div>
		<div data-nonce="<?php echo wp_create_nonce( 'wps_reload_mini_cart' ); ?>" class="wps-mini-cart-body wps-fixed-cart-container wps-bloc-loader">
			<?php echo $mini_cart_body; ?>
		</div>
		<div class="wps-cls"></div>
	</div><!-- .wps-card -->
</div><!-- .fixed-toll-bar -->
<div class="wps-cart-overlay"></div>
