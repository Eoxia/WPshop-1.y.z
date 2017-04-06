<?php
/**
 * Display statistics for custom date
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wps-table" style="margin: 0px;" >
	<div class="wps-table-row">
		<div class="wps-table-cell"><a class="wps-statistics-quick-links" href="#" data-from="<?php echo $last_month_start; ?>" data-to="<?php echo $last_month_end; ?>" ><?php esc_html_e( 'Last month', 'wpshop' ); ?></a></div>
		<div class="wps-table-cell"><a class="wps-statistics-quick-links" href="#" data-from="<?php echo $current_month_start; ?>" data-to="<?php echo $current_month_end; ?>" ><?php esc_html_e( 'Current month', 'wpshop' ); ?></a></div>
		<div class="wps-table-cell"><a class="wps-statistics-quick-links" href="#" data-from="<?php echo current_time( 'Y-m-d' ); ?>" data-to="<?php echo current_time( 'Y-m-d' ); ?>" ><?php esc_html_e( 'Today', 'wpshop' ); ?></a></div>
	</div>
</div>
<center>
	<form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" id="wps_statistics_date_customizer" method="post" >
		<input type="hidden" name="action" value="wps_statistics_custom_date_view" />
		<?php wp_nonce_field( 'wps_statistics_custom_date_view' ); ?>
		<?php esc_html_e( 'Begin date', 'wpshop' ); ?>
		<input type="text" name="wps_statistics_start_date" class="wps_statistics_date" value="<?php echo esc_attr( $date_start ); ?>" />
		<?php esc_html_e( 'End date', 'wpshop' ); ?>
		<input type="text" name="wps_statistics_end_date" class="wps_statistics_date" value="<?php echo esc_attr( $date_end ); ?>" />
		<button class="button button-primary" ><?php esc_html_e( 'View', 'wpshop' ); ?></button>
	</form>
</center>

<div id="wps_stats_chart" style="width:100%; height:300px; position:relative;"></div>

<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php esc_html_e( 'Order count', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php esc_html_e( 'Order total amount', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php esc_html_e( 'Shipping amount', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php esc_html_e( 'Customer subscription', 'wpshop' ); ?></div>
	</div>
	<div class="wps-table-row">
		<?php if ( null !== $order_list ) : ?>
		<div class="wps-table-cell"><?php echo esc_html( count( $order_list ) ); ?></div>
		<div class="wps-table-cell"><?php echo esc_html( number_format( $orders_total_amount, 2, '.', '' ) . ' ' . wpshop_tools::wpshop_get_currency( false ) ); ?></div>
		<div class="wps-table-cell"><?php echo esc_html( number_format( $orders_total_shipping_cost, 2, '.', '' ) . ' ' . wpshop_tools::wpshop_get_currency( false ) ); ?></div>
		<?php else : ?>
		<div class="wps-table-cell"><?php esc_html_e( 'No orders have been placed for the moment', 'wpshop' ); ?></div>
		<div class="wps-table-cell">&nbsp;</div>
		<?php endif; ?>
		<div class="wps-table-cell"><?php echo esc_html( $user_subscription_number->get_total() ); ?></div>
	</div>
</div>
<script type="text/javascript">/* <![CDATA[ */
	var wpsStats = <?php echo json_encode( $stats_translations ); ?>;

	var numberOfSales = [<?php echo $orders_number_for_stats; ?>];
	for (var i = 0; i < numberOfSales.length; ++i) numberOfSales[i][0] += 60 * 60 * 1000;

	var salesAmount = [<?php echo $orders_amount_for_stats; ?>];
	for (var i = 0; i < salesAmount.length; ++i) salesAmount[i][0] += 60 * 60 * 1000;
	var wpsStatsDatas = {"numberOfSales":numberOfSales, "salesAmount":salesAmount };
/* ]]> */
</script>
<script type="text/javascript" src="<?php echo WPS_STATISTICS_URL; ?>/assets/js/wps_statistics.js" ></script>
