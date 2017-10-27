<?php if ( !defined( 'ABSPATH' ) ) exit;

	/**	Get customer orders list for statistic displaying	*/
	$wps_orders_mdl = new wps_orders_mdl();
	$orders = $wps_orders_mdl->get_customer_orders( $customer_post->ID );
	$color_label = array( 'awaiting_payment' => 'jaune', 'canceled' => 'rouge', 'partially_paid' => 'orange', 'incorrect_amount' => 'orange', 'denied' => 'rouge', 'shipped' => 'bleu', 'pos' => 'bleu', 'payment_refused' => 'rouge', 'completed' => 'vert', 'refunded' => 'rouge');

if ( ! empty( $orders ) ) :
	$currency = wpshop_tools::wpshop_get_currency( false );
	$orders_status = unserialize( WPSHOP_ORDER_STATUS );
	$customer_order_total_amount = $customer_order_real_total_amount = 0;
	$order_statuses = array();
	$ordered_products = array();
	foreach( $orders as $order ) :
		$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
		$order_key = '';
		if ( ! empty( $order_meta['order_key'] ) ) {
			$order_key = $order_meta['order_key'];
		} elseif ( $order_meta['order_temporary_key']) {
			$order_key = $order_meta['order_temporary_key'];
		}
		?>
		<a href="<?php echo admin_url( 'post.php?post=' . $order->ID . '&amp;action=edit' ); ?>" ><strong>#<?php echo esc_attr( $order_key ); ?></strong></a>
		<span style="margin-left: 20px; display:inline-block; width:65px;" ><?php echo wpshop_tools::formate_number( $order_meta['order_grand_total'] ).' '.$currency; ?></span>
		<span style="margin-left: 20px;" class="wps-label-<?php echo $color_label[ strtolower($order_meta['order_status']) ]; ?>"><?php _e( $orders_status[ strtolower($order_meta['order_status']) ], 'wpshop' ); ?></span>
		<?php
		break;
	endforeach;
?>
<?php else : ?>
	&mdash;
<?php endif; ?>
