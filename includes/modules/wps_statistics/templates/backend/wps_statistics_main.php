<?php
/**
 * Template for statistics displaying
 *
 * @package wpshop
 * @subpackage dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell">&nbsp;</div>
		<div class="wps-table-cell"><?php esc_html_e( 'Order count', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php esc_html_e( 'Order total amount', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php esc_html_e( 'Customer subscription', 'wpshop' ); ?></div>
	</div>

<?php
foreach ( $dates as $label => $date_def ) :

	$args = array();
	if ( is_array( $date_def ) ) :
		$args = array(
			'date_query'	=> $date_def,
		);
	endif;

	$orders_total_amount = 0;
	$order_count = 0;
	if ( null !== $shop_orders ) :
		foreach ( $shop_orders as $order ) :
			$order_data = $order['order_postmeta'];
			if ( empty( $args ) ) :
				$orders_total_amount += $order_data['order_grand_total'];
				$order_count = count( $shop_orders );
			elseif ( ( $date_def['after'] <= $order_data['order_date'] ) && ( $date_def['before'] >= $order_data['order_date'] ) ) :
				$orders_total_amount += $order_data['order_grand_total'];
				$order_count++;
			endif;
		endforeach;
	endif;

	$user_subscription_number = new WP_User_Query( array(
		'date_query'	=> $date_def,
		'count_total' => true,
	) );
?>

<div class="wps-table-row">
	<div class="wps-table-cell textleft"><?php echo esc_html( $label ); ?></div>
	<?php if ( null !== $shop_orders ) : ?>
	<div class="wps-table-cell"><?php echo esc_html( $order_count ); ?></div>
	<div class="wps-table-cell"><?php echo esc_html( number_format( $orders_total_amount, 2, '.', '' ) . ' ' . wpshop_tools::wpshop_get_currency( false ) ); ?></div>
	<?php else : ?>
	<div class="wps-table-cell"><?php esc_html_e( 'No orders have been placed for the moment', 'wpshop' ); ?></div>
	<div class="wps-table-cell">&nbsp;</div>
	<?php endif; ?>
	<div class="wps-table-cell"><?php echo esc_html( $user_subscription_number->get_total() ); ?></div>
</div>

<?php endforeach; ?>
</div>
