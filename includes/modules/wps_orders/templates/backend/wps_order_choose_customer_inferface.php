<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-gridwrapper3-padded">
	<div>
		<div class="wps-boxed">
			<span class="wps-h5"><?php _e( 'Choose an user', 'wpshop'); ?></span>
			<div class="wps-gridwrapper2-padded">
				<div><?php echo $customer_lists; ?></div>
				<div><a href="#" class="wps-bton-mini-rounded-first" role="button" data-nonce="<?php echo wp_create_nonce( 'wps_order_choose_customer' ); ?>" id="wps_order_choose_customer"><?php _e( 'Choose this customer', 'wpshop')?></a></div>
			</div>
			<input type="hidden" name="wps_customer_id" value="" id="wps_customer_id" />
		</div>
		<!--   <div><?php _e( 'OR', 'wpshop'); ?> <a href="#" class="wps-bton-mini-rounded-first" role="button"><?php _e( 'Create a customer', 'wpshop')?></a></div> -->

	</div>

	<div class="wps_billing_data_container"></div>

	<div class="wps_shipping_data_container"></div>
</div>
