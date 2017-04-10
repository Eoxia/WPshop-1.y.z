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
	if ( is_array( $date_def ) ) {
		$args = array(
			'date_query'			=> $date_def,
		);
	} else {
		$args = array();
	}
	$orders = $this->wps_stats_mdl->wps_orders_all( $args );

	if ( null !== $orders ) :
		$orders_total_amount = 0;
		foreach ( $orders as $order ) {
			$order_data = $order['order_postmeta'];

			$orders_total_amount += $order_data['order_grand_total'];
		}
	endif;

	$user_subscription_number = new WP_User_Query( array(
		'date_query'	=> $date_def,
		'count_total' => true,
	) );
?>
<div class="wps-table-row">
	<div class="wps-table-cell textleft"><?php echo esc_html( $label ); ?></div>
	<?php if ( null !== $orders ) : ?>
	<div class="wps-table-cell"><?php echo esc_html( count( $orders ) ); ?></div>
	<div class="wps-table-cell"><?php echo esc_html( number_format( $orders_total_amount, 2, '.', '' ) . ' ' . wpshop_tools::wpshop_get_currency( false ) ); ?></div>
	<?php else : ?>
	<div class="wps-table-cell"><?php esc_html_e( 'No orders have been placed for the moment', 'wpshop' ); ?></div>
	<div class="wps-table-cell">&nbsp;</div>
	<?php endif; ?>
	<div class="wps-table-cell"><?php echo esc_html( $user_subscription_number->get_total() ); ?></div>
</div>

<?php endforeach; ?>
</div>
