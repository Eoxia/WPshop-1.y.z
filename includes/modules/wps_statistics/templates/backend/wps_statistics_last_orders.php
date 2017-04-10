<?php
/**
 * Display the last order list
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$payment_status = unserialize( WPSHOP_ORDER_STATUS );
$last_orders = array_slice( $shop_orders, 0, $main_stats_count );
if ( !empty( $last_orders ) ) :
?>
<div class="wps-table">
<?php foreach( $last_orders as $order ) : ?>
	<?php
		$order_key = ( !empty($order['order_postmeta']['order_key']) ) ? $order['order_postmeta']['order_key'] : ( ( !empty($order['order_postmeta']['order_temporary_key']) ) ? $order['order_postmeta']['order_temporary_key'] : '' );
	?>
	<div class="wps-table-row">
		<div class="wps-table-cell"><?php echo ( !empty($order['order_postmeta']['order_date']) ) ? mysql2date('d F Y', $order['order_postmeta']['order_date'], true) : ''; ?></div>
		<div class="wps-table-cell"><a href="<?php echo admin_url( 'post.php?post=' .$order['post']->ID. '&action=edit' ); ?>" ><?php echo esc_html( $order_key ); ?></a></div>
		<div class="wps-table-cell"><?php echo ( !empty($order['order_info']) && !empty($order['order_info']['billing']) && !empty($order['order_info']['billing']['address']) && !empty($order['order_info']['billing']['address']['address_last_name']) && !empty($order['order_info']['billing']['address']['address_first_name']) ) ? $order['order_info']['billing']['address']['address_first_name'].' '.$order['order_info']['billing']['address']['address_last_name'] : ''; ?></div>
		<div class="wps-table-cell"><?php echo ( !empty($order['order_postmeta']['order_grand_total']) ) ? number_format( $order['order_postmeta']['order_grand_total'], 2, '.', '' ).' '.wpshop_tools::wpshop_get_currency( false ) : ''; ?></div>
		<div class="wps-table-cell wps_dashboard_<?php echo $order['order_postmeta']['order_status']; ?>"><?php _e($payment_status[ $order['order_postmeta']['order_status'] ], 'wpshop' ) ?></div>
	</div>
<?php endforeach; ?>
</div>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No orders have been placed for the moment', 'wpshop'); ?></div>
<?php endif; ?>
