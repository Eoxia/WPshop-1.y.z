<?php
/**
 * Display the last order list
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Average orders amounts
 */
$order_amounts = array(
	'min'			=> array(
		'count' => 0,
		'id'		=> null,
	),
	'sum'			=> 0,
	'average' => 0,
	'max'			=> array(
		'count' => 0,
		'id'		=> null,
	),
);
$i = 0;
$product_numbers = 0;
foreach ( $shop_orders as $order ) {
	$order_amounts['sum'] += $order['order_postmeta']['order_grand_total'];

	/** Count how many products in each order */
	$product_numbers += count( $order['order_postmeta']['order_items'] );

	if ( ( 0 === $i ) || ( ( 0 != $order['order_postmeta']['order_grand_total'] ) && ( $order['order_postmeta']['order_grand_total'] < $order_amounts['min']['count'] ) ) ) {
		$order_amounts['min']['count'] = $order['order_postmeta']['order_grand_total'];
		$order_amounts['min']['id'] = $order['post']->ID;
	}
	if ( $order['order_postmeta']['order_grand_total'] > $order_amounts['max']['count'] ) {
		$order_amounts['max']['count'] = $order['order_postmeta']['order_grand_total'];
		$order_amounts['max']['id'] = $order['post']->ID;
	}
	$i++;
}
$order_amounts['average'] = $order_amounts['sum'] / count( $shop_orders );
$product_average = round( $product_numbers / count( $shop_orders ) );

/**
 * Average time between orders
 */
$average_in_minutes = ( $this->get_average_time_between_orders( $shop_orders ) / 60 );
$format = '%hh %imin';
if ( 1440 <= $average_in_minutes ) {
	$format = '%aj ' . $format;
}
$human_readable_time = wpshop_tools::minutes_to_time( $average_in_minutes, $format );

?>
<div class="wps-table">
	<div class="wps-table-row">
		<div class="wps-table-cell textleft" ><?php esc_html_e( 'Orders average amount', 'wpshop' ); ?></div>
		<div class="wps-table-cell textleft" ><?php echo esc_html( number_format( $order_amounts['average'], 2, '.', '' ).' '.wpshop_tools::wpshop_get_currency( false ) ); ?></div>
	</div>
	<div class="wps-table-row">
		<div class="wps-table-cell textleft" ><?php esc_html_e( 'Average time between orders', 'wpshop' ); ?></div>
		<div class="wps-table-cell textleft" >
			<?php	echo esc_html( $human_readable_time ); ?>
			<?php
				$order_duration_state = $this->check_current_time_since_last_order();
				$duration = ( $order_duration_state['duration'] / 60 );
				$format = '%hh %imin';
				if ( 1440 <= $duration ) {
					$format = '%aj ' . $format;
				}
				$time_since_last_order = wpshop_tools::minutes_to_time( $duration, $format );
				if ( true === $order_duration_state['status'] ) :
					$text_color = 'red';
					$icon = 'dashicons-warning';
				else:
					$text_color = 'green';
					$icon = 'dashicons-yes';
				endif;
			?>
			<span style="color: <?php echo $text_color; ?>;" ><i class="dashicons <?php echo $icon; ?>"></i><?php echo esc_html( sprintf( __( 'Last order date %1$s - Time since: %2$s', 'wpshop' ), mysql2date( get_option( 'date_format' ), $order_duration_state['last_date'], true ), $time_since_last_order ) ); ?></span>
	</div>
	</div>
	<div class="wps-table-row">
		<div class="wps-table-cell textleft" ><?php esc_html_e( 'Average product number into an order', 'wpshop' ); ?></div>
		<div class="wps-table-cell textleft" ><?php echo esc_html( $product_average ); ?></div>
	</div>
</div>
