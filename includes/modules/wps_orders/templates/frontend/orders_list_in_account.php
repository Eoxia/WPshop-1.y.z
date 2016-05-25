<?php if ( !defined( 'ABSPATH' ) ) exit;
if ( !empty($orders) ) :
	$permalink_option = get_option( 'permalink_structure' );
	$account_page_id = get_option('wpshop_myaccount_page_id');
?>


<?php if( !$from_admin ): ?>
<span class="wps-h5"><?php _e( 'My last orders', 'wpshop'); ?></span>
<?php endif; ?>

<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Date', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Reference', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Total', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Status', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Tracking number', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Actions', 'wpshop'); ?></div>
	</div>
	<?php
	foreach( $orders as $order ) {
		$order_id = $order->ID;
		$order_meta = get_post_meta( $order_id, '_order_postmeta', true );
		require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "frontend", "order_row_in_account") );
	} ?>
</div>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No order have been created for the moment', 'wpshop'); ?></div>
<?php endif; ?>


