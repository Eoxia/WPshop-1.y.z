<?php if ( !defined( 'ABSPATH' ) ) exit; ?>
<div class="wpspos-customer-selected" >
	<?php if ( !empty( $customer_infos ) ) : ?>
		<div class="customer_info wps-boxed">
			<span class="wps-h4"><?php _e( 'Account', 'wpshop' ); ?></span>
			<div class="wps-form-group"><?php _e('Customer last name', 'wps-pos-i18n')?> : <?php echo $customer_infos->last_name; ?></div>
			<div class="wps-form-group"><?php _e('Customer first name', 'wps-pos-i18n')?> : <?php echo $customer_infos->first_name; ?></div>
			<div class="wps-form-group"><?php _e('Customer email', 'wps-pos-i18n')?> : <?php echo $customer_infos->user_email; ?></div>
			<input type="hidden" id="wps_pos_selected_customer" value="<?php echo $customer_id; ?>" />
		</div>

		<div class="customer_address wps-boxed">
			<span class="wps-h4"><?php _e( 'Billing address', 'wpshop' ); ?></span>
			<?php
				$billing_option = get_option( 'wpshop_billing_address' );
				$addresses_customer = wps_address::get_addresses_list_no_hidden_attributes( $customer_id );
				if( !empty($addresses_customer[$billing_option['choice']]) ) {
					$billing = reset($addresses_customer[$billing_option['choice']]);
					//echo '<strong>' . $billing['address_title']['value'] . '</strong>' . '' . '<br>';
					echo isset($billing['civility']) ? __( wpshop_attributes::get_attribute_type_select_option_info($billing['civility']['value'], 'label', 'custom') ): '';
					echo ' <strong>' . (isset($billing['address_first_name']) ? $billing['address_first_name']['value'] : '') . ' ' . (isset($billing['address_last_name']) ? $billing['address_last_name']['value'] : '') . '</strong><br>';
					echo (isset($billing['address']) ? $billing['address']['value'] : '') . '<br>';
					echo (isset($billing['city']) ? $billing['city']['value'] . ' ' : '') . (isset($billing['postcode']) ? $billing['postcode']['value'] . ' ' : '') . (isset($billing['country']) ? $billing['country']['value'] : '') . '<br>';
				}
			?>
		</div>

		<div class="order_historic wps-boxed">
			<a href="#" class="toggle-historic dashicons dashicons-arrow-down alignright"></a>
			<span class="wps-h4"><?php _e( 'Historic', 'wpshop' ); ?></span>
			<?php
				foreach( $this->get_orders_customer( 3, $customer_id ) as $order ) {
			?>
			<div class="wps-form-group toggle-historic-group">
				<a href="#" class="lnk_load_order" data-display-nonce="<?php echo wp_create_nonce( 'wps_pos_order_content' ); ?>" data-finish-nonce="<?php echo wp_create_nonce( 'wps_pos_process_checkout' ); ?>" data-oid="<?php echo $order->ID; ?>" data-cid="<?php echo $customer_id; ?>">
					<?php
					if( $order->_order_postmeta['order_key'] ) {
						if( $order->_order_postmeta['order_invoice_ref'] ) {
							$link = $order->_order_postmeta['order_invoice_ref'];
						} else {
							$link = $order->_order_postmeta['order_key'];
						}
					} else {
						$link = __( 'Order summary ', 'wpshop' );
					}
					echo $link;
					?>
				</a>
				<span class="price_order"><?php echo number_format( round($order->_order_postmeta['order_grand_total'], 2), 2, '.', '') . ' ' . wpshop_tools::wpshop_get_currency( false ); ?></span>
				<span class="date_order"><?php echo date("d/m/Y | H:i:s", strtotime( $order->_order_postmeta['order_date'] ) ); ?></span>
				<?php
					if( $order->_order_postmeta['order_invoice_ref'] ) {
					?>
				<span class="invoice_order">
					<a href="" target="_blank" role="button">
						<i class="dashicons dashicons-welcome-view-site"></i>
					</a>
					<a href="<?php echo admin_url( 'admin-post.php?action=wps_invoice&mode=pdf&order_id=' . $order->ID . '&invoice_ref='.$order->_order_postmeta['order_invoice_ref']); ?>" target="_blank" role="button">
						<i class="dashicons dashicons-download"></i>
					</a>
				</span>
					<?php
					}
				?>
			</div>
			<?php
				}
			?>
		</div>

	<?php else : ?>
		<?php _e( 'Nothing was found for selected customer. Please check this customer account before continuing', 'wps-pos-i18n' ); ?>
	<?php endif; ?>
</div>
