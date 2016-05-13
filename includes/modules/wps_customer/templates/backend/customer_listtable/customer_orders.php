<?php if ( !defined( 'ABSPATH' ) ) exit;

	/**	Get customer orders list for statistic displaying	*/
	$wps_orders_mdl = new wps_orders_mdl();
	$orders = $wps_orders_mdl->get_customer_orders( $current_user_id_in_list );
	$color_label = array( 'awaiting_payment' => 'jaune', 'canceled' => 'rouge', 'partially_paid' => 'orange', 'incorrect_amount' => 'orange', 'denied' => 'rouge', 'shipped' => 'bleu', 'pos' => 'bleu', 'payment_refused' => 'rouge', 'completed' => 'vert', 'refunded' => 'rouge');

if ( !empty( $orders ) ) :
	$currency = wpshop_tools::wpshop_get_currency( false );
	$orders_status = unserialize( WPSHOP_ORDER_STATUS );

	$customer_order_total_amount = $customer_order_real_total_amount = 0;
	$order_statuses = array();
	$ordered_products = array();
	foreach( $orders as $order ) :
		$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );

		if ( empty( $order_statuses[ $order_meta['order_status'] ] ) ) {
			$order_statuses[ $order_meta['order_status'] ] = 1;
		}
		else {
			$order_statuses[ $order_meta['order_status'] ] += 1;
		}

		$customer_order_total_amount += $order_meta['order_grand_total'];
		if ( 'completed' == $order_meta['order_status'] ) {
			$customer_order_real_total_amount += $order_meta['order_grand_total'];
		}

		if ( !empty( $order_meta ) && !empty( $order_meta[ 'order_items' ] ) ) {
			foreach ( $order_meta[ 'order_items' ] as $order_item ) :
				$ordered_products[ $order_item[ 'item_id' ] ] = $order_item[ 'item_name' ];
			endforeach;
		}
	endforeach;

	$order_list = '  ';
	foreach ( $order_statuses as $order_status => $nb_of_order_with_status ) :
		ob_start();
?>
		<span class="wps-label-<?php echo $color_label[ strtolower($order_status) ]; ?>"><?php printf( __( '%2$s %1$s', 'wpshop' ), __( $orders_status[ strtolower($order_status) ], 'wpshop' ), $nb_of_order_with_status ); ?></span>
<?php
		$order_list .= ob_get_contents();
		ob_end_clean();
	endforeach;

?>
<ul class="wp-shop-customers-list-orders-stats" >
	<li>
		<?php printf( __( 'Orders total number : %d', 'wpshop' ), count( $orders ) ); ?>  <?php echo mb_substr( $order_list, 0, -2, 'UTF-8' ); ?>
	</li>
	<li>
		<?php printf( __( 'Orders total amount : %s', 'wpshop' ), wpshop_tools::formate_number( $customer_order_real_total_amount ).' '.$currency ); ?>
		<?php if ( !empty( $customer_order_total_amount ) && ( $customer_order_total_amount != $customer_order_real_total_amount ) ) : ?>( <?php printf( __( 'If all orders were paid : %s', 'wpshop' ), wpshop_tools::formate_number( $customer_order_total_amount ).' '.$currency ); ?> )<?php endif; ?>
	</li>
</ul>
<div class="wp-shop-customers-list-ordered-product" >
	<p><?php _e( 'List of ordered product', 'wpshop' ); ?></p>
	<?php echo implode( ', ', $ordered_products ); ?>
</div>
<?php else: ?>
	<div class="wps-alert-info"><?php _e( 'No order have been created for the moment', 'wpshop'); ?></div>
<?php endif; ?>