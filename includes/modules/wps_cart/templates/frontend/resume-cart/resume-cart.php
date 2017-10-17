<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<?php do_action( 'wps_cart_resume_sidebar_top' ); ?>
<div class="wps-cart-resume" id="wps_resume_cart_container" data-nonce="<?php echo wp_create_nonce( 'wps_reload_summary_cart' ); ?>" >
	<?php echo $cart_summary_content; ?>
</div>
<?php do_action( 'wps_cart_resume_sidebar_bottom' ); ?>
