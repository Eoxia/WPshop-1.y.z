<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($shipping_method_name) ) : ?>
	<div class="wps-alert-info"><strong><?php _e( 'Selected shipping method', 'wpshop'); ?></strong> : <?php echo $shipping_method_name; ?></div>
<?php else : ?>
	<div class="wps-alert-info"><?php _e( 'No selected shipping method', 'wpshop'); ?></div>
<?php endif; ?>

<div class="wps-boxed">
	<span class="wps-h5"><?php _e( 'Shipping informations', 'wpshop');?></span>
	<?php if ( !empty($order_postmeta['order_status']) && $order_postmeta['order_status'] != 'shipped' ) : ?>
			<div><a data-id="<?php echo $order->ID; ?>" class="wps-bton-first-mini-rounded markAsShipped order_<?php echo $order->ID; ?>"><?php _e('Mark as shipped', 'wpshop'); ?></a></div>
	<?php else : ?>
		<div>
			<ul id="wps-order-shipping-informations">
				<li><strong><?php _e('Order shipping date','wpshop') ?> :</strong><?php echo ( empty($order_postmeta['order_shipping_date']) ? __('Unknow','wpshop') : mysql2date('d F Y H:i:s', $order_postmeta['order_shipping_date'],true) ); ?></li>
				<li><strong><?php _e('Tracking number','wpshop'); ?> :</strong> <?php echo ( !empty($order_postmeta['order_trackingNumber']) ) ? $order_postmeta['order_trackingNumber'] : __('Unknow','wpshop'); ?></li>
				<li><strong><?php _e('Tracking link','wpshop'); ?> :</strong> <?php echo ( !empty($order_postmeta['order_trackingLink']) ) ? $order_postmeta['order_trackingLink'] : __('Unknow','wpshop'); ?></li>
			</ul>
		</div>
<?php endif; ?>

<?php if ( !empty($order_postmeta['order_invoice_ref']) ) : ?>
	<div><a href="<?php echo admin_url( 'admin-post.php?action=wps_invoice&order_id='.$order->ID.'&invoice_ref='.$order_postmeta['order_invoice_ref'].'&bon_colisage=ok&mode=pdf' ); ?>" target="_blank" class="wps-bton-second-mini-rounded" ><?php _e('Download the product list', 'wpshop'); ?></a></div>
<?php endif; ?>
</div>
