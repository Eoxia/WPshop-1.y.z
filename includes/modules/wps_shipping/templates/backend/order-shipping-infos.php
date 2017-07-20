<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($shipping_method_name) ) : ?>
	<div class="wps-alert-info"><strong><?php _e( 'Selected shipping method', 'wpshop'); ?></strong> : <?php echo $shipping_method_name; ?></div>
<?php else : ?>
	<div class="wps-alert-info"><?php _e( 'No selected shipping method', 'wpshop'); ?></div>
<?php endif; ?>

<div class="wps-boxed">
	<span class="wps-h5"><?php _e( 'Shipping informations', 'wpshop');?></span>
	<?php if ( !empty($order_postmeta['order_status']) && $order_postmeta['order_status'] != 'shipped' ) : ?>
			<div><a data-id="<?php echo $order->ID; ?>" class="wps-bton-first-mini-rounded markAsShipped order_<?php echo $order->ID; ?>" data-nonce="<?php echo wp_create_nonce("wpshop_dialog_inform_shipping_number"); ?>"><?php _e('Mark as shipped', 'wpshop'); ?></a></div>
	<?php else : ?>
		<div>
			<?php require( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, $this->template_dir, 'backend', 'order-shipping-informations' ) ); ?>
		</div>
<?php endif; ?>

<?php if ( !empty($order_postmeta['order_invoice_ref']) ) : ?>
	<div><a href="<?php echo admin_url( 'admin-post.php?action=wps_invoice&order_id='.$order->ID.'&invoice_ref='.$order_postmeta['order_invoice_ref'].'&bon_colisage=ok&mode=pdf' ); ?>" target="_blank" class="wps-bton-second-mini-rounded" ><?php _e('Download the product list', 'wpshop'); ?></a></div>
<?php endif; ?>
</div>
