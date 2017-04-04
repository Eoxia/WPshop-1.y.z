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
		<div class="wps-table-cell"><?php esc_html_e( 'Order total amount', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php esc_html_e( 'Order count', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php esc_html_e( 'Customer subscription', 'wpshop' ); ?></div>
	</div>

<?php
foreach ( $dates as $label => $date_def ) :
	$args = wp_parse_args( array(
		'date_query'			=> $date_def,
	), $orders_default_args );
	$orders = new WP_Query( $args );

	$orders_total_amount = 0;
	if ( $orders->have_posts() ) {
		foreach ( $orders->posts as $order ) {
			$order_data = get_post_meta( $order->ID, '_order_postmeta', true );

			if ( 'denied' === $order_data['order_status'] || 'awaiting_payment' === $order_data['order_status'] ) {
				continue;
			}

			if ( in_array( $order_data['order_status'], array( 'completed', 'shipped' ), true ) ) {
				$orders_total_amount += $order_data['order_grand_total'];
			}
		}
	}

	$user_subscription_number = new WP_User_Query( array(
		'date_query'	=> $date_def,
		'count_total' => true,
	) );
?>
<div class="wps-table-row">
	<div class="wps-table-cell"><?php echo esc_html( $label ); ?></div>
	<div class="wps-table-cell"><?php echo esc_html( $orders_total_amount ) . '&nbsp;' . wpshop_tools::wpshop_get_currency(); ?></div>
	<div class="wps-table-cell"><?php echo esc_html( $orders->found_posts ); ?></div>
	<div class="wps-table-cell"><?php echo esc_html( $user_subscription_number->get_total() ); ?></div>
</div>
<?php endforeach; ?>
</div>

<?php require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, 'backend', 'metabox', 'customerStats' ) ); ?>

<center><a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=wpshop_statistics' ); ?>"><span class="dashicons dashicons-chart-line"></span><?php esc_html_e( 'View all statistics', 'wpshop' ); ?></a></center>
