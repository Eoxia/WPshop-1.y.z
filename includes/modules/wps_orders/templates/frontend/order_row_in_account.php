<?php if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wps-table-content wps-table-row">
	<?php $order_status = unserialize(WPSHOP_ORDER_STATUS);?>
	<?php $color_label = array('awaiting_payment' => 'jaune', 'canceled' => 'rouge', 'partially_paid' => 'orange', 'incorrect_amount' => 'orange', 'denied' => 'rouge', 'shipped' => 'bleu', 'payment_refused' => 'rouge', 'completed' => 'vert', 'refunded' => 'rouge', 'pos' => 'bleu');?>
	<?php $currency = wpshop_tools::wpshop_get_currency(false);?>
	<div class="wps-table-cell"><?php echo !empty($order_meta['order_date']) ? mysql2date(get_option('date_format'), $order_meta['order_date'], true) . '<br>' . mysql2date(get_option('time_format'), $order_meta['order_date'], true) : ''; ?></div>
	<div class="wps-table-cell"><?php echo !empty($order_meta['order_key']) ? $order_meta['order_key'] : (!empty($order_meta['order_temporary_key']) ? $order_meta['order_temporary_key'] : ''); ?></div>
	<div class="wps-table-cell"><?php echo !empty($order_meta['order_grand_total']) ? wpshop_tools::formate_number($order_meta['order_grand_total']) . ' ' . $currency : ''; ?></div>
	<div class="wps-table-cell">
		<span class="wps-label-<?php echo $color_label[$order_meta['order_status']]; ?>"><?php _e($order_status[$order_meta['order_status']], 'wpshop');?></span>
	</div>
	<div class="wps-table-cell">
		<?php if (!empty($order_meta['order_trackingLink'])): ?>
			<?php /** Check if http:// it's found in the link */
$url = $order_meta['order_trackingLink'];
if ('http://' != substr($url, 0, 7)) {
    $url = 'http://' . $url;
}

?>
			<a href="<?php echo $url; ?>" target="_blank"><?php echo !empty($order_meta['order_trackingNumber']) ? $order_meta['order_trackingNumber'] : ""; ?></a>
		<?php else: ?>
			<?php _e('No tracking links', 'wpshop');?>
		<?php endif;?>
	</div>
	<?php if (!is_admin()): ?>
		<div class="wps-table-cell wps-customer-order-list-actions">
			<button data-nonce="<?php echo wp_create_nonce('wps_orders_load_details'); ?>" class="wps-bton-third wps-orders-details-opener" id="wps-order-details-opener-<?php echo $order_id; ?>"><?php _e('Order details', 'wpshop');?></button>

			<?php if (!empty($order_meta) && $order_meta['order_status'] && $order_meta['order_status'] != 'canceled' && (float) $order_meta['order_amount_to_pay_now'] != (float) 0): ?>
				<?php if (isset($order_meta['pay_quotation']) && (!empty($order_meta['cart_type']) && $order_meta['cart_type'] == 'quotation') || !empty($order_meta['order_temporary_key'])): ?>
				<a href="<?php echo wpshop_checkout::wps_direct_payment_link_url($order_id); ?>" target="_blank" class="wps-bton-third" role="button"><?php _e('Pay quotation', 'wpshop');?></a>
				<?php else: ?>
				<a href="<?php echo wpshop_checkout::wps_direct_payment_link_url($order_id); ?>" target="_blank" class="wps-bton-third" role="button"><?php _e('Pay order', 'wpshop');?></a>
				<?php endif;?>
			<?php endif;?>

			<?php if (!empty($order_meta) && !empty($order_meta['order_invoice_ref'])): ?>
			<br/><a href="<?php echo admin_url('admin-post.php?action=wps_invoice&order_id=' . $order_id . '&invoice_ref=' . $order_meta['order_invoice_ref'] . '&mode=pdf'); ?>" target="_blank" class="wps-bton-third" role="button"><?php _e('Download invoice', 'wpshop');?></a>
			<?php endif;?>

			<!-- Display delete order -->
			<?php $wpshop_display_delete_order_option = get_option('wpshop_display_option');?>
			<?php if (!empty($wpshop_display_delete_order_option) && !empty($wpshop_display_delete_order_option['wpshop_display_delete_order']) && $wpshop_display_delete_order_option['wpshop_display_delete_order'] && !empty($order_meta) && $order_meta['order_status'] == 'awaiting_payment'): ?>
			<br/><button data-nonce="<?php echo wp_create_nonce('wps_delete_order'); ?>" class="wps-bton-first-mini-rounded wps-orders-delete button-secondary" data-id="<?php echo $order_id; ?>"><?php _e('Delete order', 'wpshop');?></button>
			<?php endif;?>
		</div>
	<?php else: ?>
		<div class="wps-table-cell"><a href="<?php echo admin_url('post.php?post=' . $order_id . '&action=edit'); ?>" target="_blank" role="button" class="wps-bton-first-mini-rounded" ><?php _e('Order details', 'wpshop');?></a></div>
	<?php endif?>
</div>
