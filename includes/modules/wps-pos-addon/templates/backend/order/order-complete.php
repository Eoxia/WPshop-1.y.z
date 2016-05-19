<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<h2><?php _e('Payments summary', 'wps-pos-i18n'); ?></h2>
<?php if ( !empty( $order_postmeta ) && !empty( $order_postmeta['order_payment'] ) && !empty( $order_postmeta['order_payment']['received'] ) && is_array( $order_postmeta['order_payment']['received'] ) ) : ?>
<table id="wps-pos-order-payment-summary" >
 	<tr>
		<th><?php _e('Date', 'wps-pos-i18n'); ?></th>
		<th><?php _e('Choosen payment method', 'wps-pos-i18n'); ?></th>
		<th><?php _e('Amount', 'wps-pos-i18n'); ?></th>
		<th><?php _e('Invoice', 'wps-pos-i18n'); ?></th>
 	</tr>
 	<?php foreach( $order_postmeta['order_payment']['received'] as $payment_received ) : ?>
    <tr>
		<td><?php echo ( !empty($payment_received['date']) ) ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $payment_received['date'], true ) : ''; ?></td>
		<td><?php echo ( !empty($payment_received['method']) ) ? __( $payment_received['method'], 'wpshop' ) : ''; ?></td>
		<td><?php echo ( !empty($payment_received['received_amount']) ) ?  number_format( $payment_received['received_amount'], 2, '.', '' ) : ''; ?><?php echo wpshop_tools::wpshop_get_currency(); ?></td>
		<td>
			<a class="wps-bton-third-mini-rounded" href="<?php echo admin_url( 'admin-post.php?action=wps_invoice&order_id=' . $order_id . '&mode=pdf' . ( !empty( $payment_received['invoice_ref'] ) ? '&invoice_ref=' . $payment_received['invoice_ref'] : '' ) ); ?>">
			<?php _e( 'Download', 'wpshop' ); ?></a>
			<a class="wps-bton-third-mini-rounded" target="_blank" href="<?php echo admin_url( 'admin-post.php?action=wps_invoice&order_id=' . $order_id . ( !empty( $payment_received['invoice_ref'] ) ? '&invoice_ref=' . $payment_received['invoice_ref'] : '' ) ); ?>" ><?php _e( 'View', 'wpshop' ); ?></a>
		</td>
	</tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ( ( 'money' == $payment_method ) && ( number_format( (float)$received_payment_amount, 2, '.', '') > number_format( (float)$payment_amount, 2, '.', '') ) ) : ?>
<div class="alignright wps-alert wps-alert-info" id="wpspos-back-cash" >
	<?php _e( 'Change due', 'wps-pos-i18n' ); ?> : <span class="wpspos-due-change" ><?php echo ( number_format( (float)$received_payment_amount, 2, '.', '') - number_format( (float)$payment_amount, 2, '.', '') );?></span>
</div>
<?php endif; ?>

<div class="wps-pos-order-completion-button" >
	<a href="<?php echo admin_url( $new_order ? 'admin.php?page=wps-pos&new_order=yes' : 'admin.php?page=wps-pos' ); ?>" class="wps-bton-first-rounded alignright" role="button"><?php echo $new_order ? __('Create a new order', 'wps-pos-i18n') : __('Return to current order', 'wps-pos-i18n'); ?></a>
	<?php if ( !empty( $order_postmeta ) && !empty( $order_postmeta['order_status'] ) && ( 'completed' !=  $order_postmeta['order_status'] ) ) : ?>
		<a title="<?php _e( 'New payment', 'wps-pos-i18n' ); ?>" href="<?php print wp_nonce_url( admin_url( 'admin-ajax.php?action=wpspos-finalize-order&order_id=' . $order_id . '&width=560&height=420' ), 'wps_pos_finalize_order', '_wpnonce' ); ?>" class="thickbox wps-bton-third-rounded alignright" ><?php _e( 'New payment', 'wps-pos-i18n' ); ?></a>
	<?php endif; ?>
</div>
