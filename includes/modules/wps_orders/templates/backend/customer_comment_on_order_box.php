<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($order->post_excerpt) ) : ?>
	<div class="wps-boxed"><?php echo $order->post_excerpt; ?></div>
<?php else : ?>
	<div class="wps-alert-info">
		<?php _e('No comment for this order', 'wpshop'); ?>
	</div>
<?php endif; ?>
