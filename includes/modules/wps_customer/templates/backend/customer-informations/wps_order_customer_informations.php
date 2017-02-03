<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-gridwrapper2-padded">

	<div>
		<?php if( empty($order_metadata) || empty($order_metadata['customer_id']) || $order_metadata['order_status'] == 'awaiting_payment' ) : ?>
		<div class="wps-boxed">
			<div class="wps-h5"><?php _e( 'Customer Managment', 'wpshop'); ?><a href="<?php print wp_nonce_url( admin_url( 'admin-ajax.php?action=wps_load_customer_creation_form_in_admin&width=730&height=690' ), 'wps_load_customer_creation_form_in_admin', '_wpnonce' ); ?>" title="<?php _e( 'Create a customer', 'wpshop'); ?>" class="add-new-h2 alignright thickbox"><i class="wps-icon-plus"></i> <?php _e( 'Create a customer', 'wpshop'); ?></a></div>

			<div class="wps-gridwrapper2-padded" style="clear : both; ">
				<div>
					<div class="wps-form-group">
						<label><?php _e( 'Search and choose a customer', 'wpshop'); ?> :</label>
						<div class="wps-form" id="wps_customer_list_container" data-nonce="<?php wp_create_nonce( 'wps_order_refresh_customer_list' ); ?>">
							<?php echo $customer_lists; ?>
						</div>
					</div>
				</div>
			</div>


		</div>
		<?php endif; ?>
	</div>

	<div id="wps_customer_account_informations" data-nonce="<?php echo wp_create_nonce( 'wps_order_refresh_customer_informations' ); ?>">
		<?php if( !empty($customer_datas) ) : ?>
		<div>
			<?php echo $customer_datas; ?>
		</div>
		<?php else : ?>
			<div class="wps-alert-info"><span class="dashicons dashicons-info"></span> <?php _e( 'Please choose a customer or create one', 'wpshop'); ?></div>
		<?php endif; ?>
	</div>

</div>
<!-- Hidden field referrer the selected customer -->
<input type="hidden" name="wps_customer_id" id="wps_orders_selected_customer" value="<?php echo ( !empty($order_metadata) && !empty($order_metadata['customer_id']) ) ? $order_metadata['customer_id'] : ''; ?>" />

<div id="wps_customer_addresses" data-nonce="<?php echo wp_create_nonce( 'reload_addresses_for_customer' ); ?>" class="wps-gridwrapper2-padded">
	<?php echo ( !empty($addresses) ) ? $addresses : ''; ?>
</div>
<input type="hidden" name="wps_order_selected_address[billing]" id="wps_order_selected_address_billing" value="<?php echo ( !empty($order_infos) && !empty($order_infos['billing']) && !empty($order_infos['billing']['address_id']) ) ? $order_infos['billing']['address_id'] : '' ; ?>" />
<?php if( !empty( $shipping_address_option ) && !empty($shipping_address_option['activate']) ) : ?>
	<input type="hidden" name="wps_order_selected_address[shipping]" id="wps_order_selected_address_shipping" value="<?php echo ( !empty($order_infos) && !empty($order_infos['shipping']) && !empty($order_infos['shipping']['address_id']) ) ? $order_infos['shipping']['address_id'] : '' ; ?>" />
<?php endif; ?>
