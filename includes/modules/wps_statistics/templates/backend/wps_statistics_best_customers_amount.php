<?php
/**
 * Display the best customers list
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

usort( $ordered_customers, function( $a, $b ) {
	if ($a['total_amount'] == $b['total_amount']) {
    return 0;
  }
  return ($a['total_amount'] > $b['total_amount']) ? -1 : 1;
} );
$best_customers = array_slice( $ordered_customers, 0, 5 );
if ( !empty( $best_customers ) ) :
?>
<div class="wps-table">
	<div class="wps-table-header">
		<div class="wps-table-cell" ><?php esc_html_e( 'Customer name', 'wpshop' ); ?></div>
		<div class="wps-table-cell" ><?php esc_html_e( 'Order count', 'wpshop' ); ?></div>
		<div class="wps-table-cell" ><?php esc_html_e( 'Order average amount', 'wpshop' ); ?></div>
		<div class="wps-table-cell" ><?php esc_html_e( 'Order total amount', 'wpshop' ); ?></div>
	</div>
<?php foreach( $best_customers as $customer ) : ?>
	<div class="wps-table-row">
		<div class="wps-table-cell textleft" ><a href="<?php echo admin_url('post.php?post=' . $customer['post_id']. '&action=edit'); ?>"><?php echo $customer['name']; ?></a></div>
		<div class="wps-table-cell" ><?php echo esc_html( $customer['count'] ); ?></div>
		<div class="wps-table-cell" ><?php echo esc_html( number_format( $customer['total_amount'] / $customer['count'], 2, '.', '' ).' '.wpshop_tools::wpshop_get_currency( false ) ); ?></div>
		<div class="wps-table-cell" ><?php echo esc_html( number_format( $customer['total_amount'], 2, '.', '' ).' '.wpshop_tools::wpshop_get_currency( false ) ); ?></div>
	</div>
<?php endforeach; ?>
</div>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No orders have been placed for the moment', 'wpshop'); ?></div>
<?php endif; ?>
