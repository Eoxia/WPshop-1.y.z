<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-alert wpshopHide" id="wps-pos-order-content-alert" ></div>
<div id="wps_cart_container" data-nonce="<?php echo wp_create_nonce( 'wps_pos_order_content' ); ?>" class="wps-bloc-loader">
	<?php echo $this->display_wps_pos_order_content(); ?>
</div>
