<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-cart-resume" id="wps_resume_cart_container" data-nonce="<?php echo wp_create_nonce( 'wps_reload_summary_cart' ); ?>" >
	<?php echo $cart_summary_content; ?>
</div>
