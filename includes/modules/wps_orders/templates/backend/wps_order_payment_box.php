<?php if (!defined('ABSPATH')) {
    exit;
}

if (!empty($order_postmeta['order_payment'])):
    $total_amount = (!empty($order_postmeta['order_grand_total'])) ? $order_postmeta['order_grand_total'] : '';
    $waited_amount_sum = $received_amount_sum = $i = 0;
    ?>
										<?php $payment_modes = get_option('wps_payment_mode');?>
										<?php if (!empty($order_postmeta['order_payment']['customer_choice']) && !empty($order_postmeta['order_payment']['customer_choice']['method'])) {
        ;
    }
    ?>
											<div class="wps-alert-info"><strong><?php _e('Payment method customer select', 'wpshop');?> : </strong><br/>
											<?php if (!empty($order_postmeta) && !empty($order_postmeta['order_payment']['customer_choice']['method']) && !empty($payment_modes['mode'][strtolower((string) $order_postmeta['order_payment']['customer_choice']['method'])]['name'])) {
        echo $payment_modes['mode'][strtolower((string) $order_postmeta['order_payment']['customer_choice']['method'])]['name'];
    } else {
        _e('Unknow', 'wpshop');
        if (!empty($order_postmeta['order_payment']['customer_choice']['method'])) {
            echo ' (' . strtolower((string) $order_postmeta['order_payment']['customer_choice']['method']) . ')';
        }
    }?>
											</div>
											<?php
    $payment_method_filter = apply_filters('wps_administration_order_payment_informations', $order->ID);
    if ($order->ID != $payment_method_filter) {
        echo $payment_method_filter;
    }
    ?>

										<?php if (!empty($order_postmeta['order_payment']['received'])): ?>
											<?php ob_start();
    foreach ($order_postmeta['order_payment']['received'] as $index_payment => $received_payment):
        if (empty($received_payment['method']) || $received_payment['method'] == 'quotation') {
            continue;
        }
        if ($i == 0): ?>
																				<div class="wps-boxed">
																					<div class="wps-h2"><?php _e('Received payments', 'wpshop');?></div>
																					<?php endif;
    $i++;
    if (!empty($received_payment['waited_amount'])) {
        $waited_amount_sum += $received_payment['waited_amount'];
    }
    if (!empty($received_payment['received_amount']) && ($received_payment['status'] == 'payment_received')) {
        $received_amount_sum += $received_payment['received_amount'];
    }
    ?>
												<div>
													<div class="wps-h5"><span class="dashicons dashicons-arrow-right"></span><strong><?php echo (!empty($received_payment) && !empty($received_payment['method']) && !empty($payment_modes['mode'][strtolower($received_payment['method'])]) && !empty($payment_modes['mode'][strtolower($received_payment['method'])]['name']) ? $payment_modes['mode'][strtolower($received_payment['method'])]['name'] : sprintf(__('Unknow (%s)', 'wpshop'), strtolower($received_payment['method']))); ?></strong></div>
													<div class="wps-product-section">
														<div><strong><?php _e('Payment date', 'wpshop');?> :</strong> <?php echo (!empty($received_payment) && !empty($received_payment['date'])) ? mysql2date('d F Y H:i', $received_payment['date'], true) : __('Unknow', 'wpshop'); ?></div>
														<div><strong><?php _e('Payment reference', 'wpshop');?> :</strong> <?php echo (!empty($received_payment) && !empty($received_payment['payment_reference'])) ? $received_payment['payment_reference'] : __('Unknow', 'wpshop'); ?></div>
														<div><strong><?php _e('Amount', 'wpshop');?> :</strong> <?php echo (!empty($received_payment) && !empty($received_payment['received_amount'])) ? $received_payment['received_amount'] . ' ' . wpshop_tools::wpshop_get_currency() : __('Unknow', 'wpshop'); ?></div>
														<div><strong><?php _e('Status', 'wpshop');?> :</strong>
															<?php if (!empty($received_payment['status']) && $received_payment['status'] == 'payment_received'): ?>
																<span class="wps-label-vert"><?php _e('Received payment', 'wpshop');?></span>
															<?php elseif ($received_payment['status'] == 'incorrect_amount'): ?>
							<span class="wps-label-orange"><?php _e('Incorrect amount', 'wpshop');?></span>
						<?php elseif ($received_payment['status'] == 'waiting_payment'): ?>
							<span class="wps-label-rouge"><?php _e('Waiting payment', 'wpshop');?></span>
						<?php else: ?>
							<span class="wps-label-rouge"><?php echo $received_payment['status']; ?></span>
						<?php endif;?>
					</div>
				</div>
				<?php if (!empty($received_payment) && !empty($received_payment['invoice_ref'])) {?>
					<div class="wps-product-section"><a href="<?php echo admin_url('admin-post.php?action=wps_invoice&order_id=' . $order->ID . '&invoice_ref=' . $received_payment['invoice_ref'] . '&mode=pdf'); ?>" target="_blank" class="wps-bton-second-mini-rounded" role="button"><i class="dashicons dashicons-download"></i><?php _e('Download invoice', 'wpshop');?></a></div>
					<div class="wps-product-section"><a href="<?php echo admin_url('admin-post.php?action=wps_invoice&order_id=' . $order->ID . '&invoice_ref=' . $received_payment['invoice_ref']); ?>" target="_blank" class="wps-bton-fourth-mini-third" role="button"><i class="dashicons dashicons-welcome-view-site"></i><?php _e('Watch invoice', 'wpshop');?></a></div>
				<?php } elseif (!empty($received_payment) && empty($received_payment['invoice_ref']) && $received_payment['status'] == 'payment_received') {
    ?>
					<input type="hidden" name="order_id" class="wps-regerate-invoice-payment-input<?php $idregen = uniqid();
    echo $idregen;?>" value="<?php echo $order->ID; ?>">
					<input type="hidden" name="index_payment" class="wps-regerate-invoice-payment-input<?php echo $idregen; ?>" value="<?php echo $index_payment; ?>">
					<div class="wps-product-section"><button data-nonce="<?php echo wp_create_nonce('wps_reverify_payment_invoice_ref'); ?>" id="wps-regerate-invoice-payment-btn" class="wps-bton-fourth-mini-third" data-class="<?php echo $idregen; ?>"><i class="dashicons dashicons-controls-repeat"></i><?php _e('Regerate invoice payment', 'wpshop');?></button></div>
				<?php }?>
			</div>
		<?php endforeach;?>
		<?php echo strrev(preg_replace(strrev('/wps-product-section/'), '', strrev(ob_get_clean()), 1)); ?>
		<?php if ($i == 0): ?>
			<div class="wps-alert-info"><?php _e('No received payment for the moment', 'wpshop');?></div>
		<?php else: ?>
			</div>
		<?php endif;?>

		<?php if ((($total_amount - $received_amount_sum) > 0) && ($order_postmeta['order_grand_total'] > 0)): ?>
		<div class="wps-boxed">
			<div class="wps-h5"><?php _e('Add a new payment', 'wpshop');?></div>
			<div class="wps-gridwrapper2-padded">
				<div class="wps-form-group">
					<label><?php _e('Method', 'wpshop');?> :</label>
					<div class="wps-form">
						<select name="wpshop_admin_order_payment_received[method]">
							<?php if (!empty($payment_modes) && !empty($payment_modes['mode'])): ?>
								<?php foreach ($payment_modes['mode'] as $mode_id => $mode): ?>
									<?php if (!empty($mode['active'])): ?>
										<option value="<?php echo $mode_id; ?>"><?php echo $mode['name']; ?></option>
									<?php endif;?>
								<?php endforeach;?>
							<?php endif;?>
						</select>
					</div>
				</div>

				<div class="wps-form-group">
					<label><?php _e('Reference', 'wpshop');?> :</label>
					<div class="wps-form">
						<input type="text" name="wpshop_admin_order_payment_received[payment_reference]" />
					</div>
				</div>

			</div>

			<div class="wps-gridwrapper2-padded">
				<div class="wps-form-group">
					<label><?php _e('Date', 'wpshop');?> :</label>
					<div class="wps-form">
						<input type="text" name="wpshop_admin_order_payment_received[date]" class="wpshop_admin_order_arrived_payment_date" value="" />
					</div>
				</div>

				<div class="wps-form-group">
					<label><?php _e('Amount', 'wpshop');?> (<?php echo wpshop_tools::wpshop_get_currency(); ?>):</label>
					<div class="wps-form">
						<input type="text" name="wpshop_admin_order_payment_received[received_amount]" value="<?php echo $order_postmeta['order_amount_to_pay_now']; ?>" />
					</div>
				</div>

			</div>
			<input type="hidden" value="<?php echo ($waited_amount_sum - $received_amount_sum); ?>" id="wpshop_admin_order_due_amount" />
			<input type="hidden" value="" id="action_triggered_from" name="action_triggered_from" />
			<div><button class="wps-bton-first-mini-rounded" id="wpshop_order_arrived_payment_amount_add_button"><?php _e('Add the payment', 'wpshop');?></button></div>
		</div>

		<script type="text/javascript" >
			wpshop(document).ready(function(){
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker();
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "dateFormat", "yy-mm-dd");
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "changeMonth", true);
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "changeYear", true);
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "yearRange", "-90:+10");
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "navigationAsDateFormat", true);
				jQuery(".wpshop_admin_order_arrived_payment_date").val("<?php echo substr(current_time('mysql', 0), 0, 10); ?>");


				/**	Add an action on order save button	*/
				jQuery("#wpshop_order_arrived_payment_amount_add_button").live("click", function(){
					jQuery("#action_triggered_from").val('add_payment');
					display_message_for_received_payment( false );
				});
			});
		</script>


		<?php endif;?>
		<div class="wps-alert-<?php echo ((($order_postmeta['order_amount_to_pay_now']) <= 0) ? 'success' : 'warning'); ?>"><u><?php _e('Due amount for this order', 'wpshop');?></u> : <span class="alignright"><strong><?php echo $order_postmeta['order_amount_to_pay_now']; ?> <?php echo wpshop_tools::wpshop_get_currency(); ?></strong></span></div>

	<?php endif;?>
<?php else: ?>
	<div class="wps-alert-info"><?php _e('No information available for this order payment', 'wpshop');?></div>
<?php endif;?>
