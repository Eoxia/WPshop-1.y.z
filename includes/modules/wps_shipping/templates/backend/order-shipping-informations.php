<ul id="wps-order-shipping-informations">
	<li><strong><?php _e('Order shipping date','wpshop') ?> :</strong><?php echo ( empty($order_postmeta['order_shipping_date']) ? __('Unknow','wpshop') : mysql2date('d F Y H:i:s', $order_postmeta['order_shipping_date'],true) ); ?></li>
	<li><strong><?php _e('Tracking number','wpshop'); ?> :</strong> <?php echo ( !empty($order_postmeta['order_trackingNumber']) ) ? $order_postmeta['order_trackingNumber'] : __('Unknow','wpshop'); ?></li>
	<li><strong><?php _e('Tracking link','wpshop'); ?> :</strong> <?php echo ( !empty($order_postmeta['order_trackingLink']) ) ? $order_postmeta['order_trackingLink'] : __('Unknow','wpshop'); ?></li>
</ul>
