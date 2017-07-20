<?php if ( !defined( 'ABSPATH' ) ) exit;
if($orders) :
	$payment_status = unserialize( WPSHOP_ORDER_STATUS );
?>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Date', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Customer', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Total', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Status', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Details', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Invoice', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Shipping Slip', 'wpshop'); ?></div>
	</div>
	<?php foreach( $orders as $order ) :
		$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
		$order_info = get_post_meta( $order->ID, '_order_info', true );
		if ( !empty($order_meta) ) :
	?>
			<div class="wps-table-content wps-table-row">
				<div class="wps-table-cell"><?php echo ( !empty($order_meta['order_date']) ) ? mysql2date('d F Y', $order_meta['order_date'], true) : ''; ?></div>
				<div class="wps-table-cell"><?php echo ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['address_last_name']) && !empty($order_info['billing']['address']['address_first_name']) ) ? $order_info['billing']['address']['address_first_name'].' '.$order_info['billing']['address']['address_last_name'] : ''; ?></div>
				<div class="wps-table-cell"><?php echo ( !empty($order_meta['order_grand_total']) ) ? number_format( $order_meta['order_grand_total'], 2, '.', '' ).' '.wpshop_tools::wpshop_get_currency( false ) : ''; ?></div>
				<div class="wps-table-cell wps_dashboard_<?php echo $order_meta['order_status']; ?>"><?php _e($payment_status[ $order_meta['order_status'] ], 'wpshop' ) ?></div>
				<div class="wps-table-cell"><a href="<?php echo admin_url('/post.php?post=' .$order->ID. '&action=edit'); ?>" role="button" class="wps-bton-first-mini-rounded"><?php _e( 'See', 'wpshop' ); ?></a></div>
				<div class="wps-table-cell">
					<?php
					$invoice_ref = '';
					if ( !empty($order_meta['order_invoice_ref']) ) {
						$invoice_ref = $order_meta['order_invoice_ref'];
					}
					if ( !empty($invoice_ref) ) {
						if( !empty($order_meta) && !empty($order_meta['order_payment']) && !empty($order_meta['order_payment']['received']) && !empty($order_meta['order_payment']['received'][ count($order_meta['order_payment']['received']) - 1 ]['invoice_ref']) ) {
							$invoice_ref = $order_meta['order_payment']['received'][ count($order_meta['order_payment']['received']) - 1 ]['invoice_ref'];
						}
					}
					if ( ( $order_meta['order_status'] == 'partially_paid' || $order_meta['order_status'] == 'completed' || $order_meta['order_status'] == 'shipped' ) && !empty($invoice_ref) ) : ?>
						<a href="<?php echo admin_url( 'admin-post.php?action=wps_invoice&order_id='.$order->ID.'&mode=pdf' ); ?>" role="button" class="wps-bton-second-mini-rounded"><?php _e( 'Download', 'wpshop' ); ?></a>
					<?php endif; ?>
				</div>
				<div class="wps-table-cell">
				<?php if ( $order_meta['order_status'] == 'shipped' ) : ?>
					<a href="<?php echo admin_url( 'admin-post.php?action=wps_invoice&order_id='.$order->ID.'&bon_colisage=ok&mode=pdf'); ?>" role="button" class="wps-bton-third-mini-rounded"><?php _e( 'Download', 'wpshop' ); ?></a>
				<?php endif; ?>
				</div>

			</div>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No orders has been created on your shop', 'wpshop'); ?></div>
<?php endif; ?>
