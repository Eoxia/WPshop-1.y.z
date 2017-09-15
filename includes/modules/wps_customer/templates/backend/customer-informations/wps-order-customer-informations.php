<?php
/**
 * Informations du client dans l'interface des commandes / Customer metabox for orders
 *
 * @package WPShop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<!-- Hidden fields referrer the selected customer -->
<input type="hidden" name="wps_customer_id" id="wps_orders_selected_customer" value="<?php echo esc_attr( $customer_id ); ?>" />
<input type="hidden" name="wps_customer_addresses_nonce" id="wps_customer_addresses_nonce" value="<?php echo esc_attr( wp_create_nonce( 'reload_addresses_for_customer' ) ); ?>" />
<input type="hidden" name="wps_order_selected_address[billing]" id="wps_order_selected_address_billing" value="<?php echo esc_attr( ! empty( $order_infos ) && ! empty( $order_infos['billing'] ) && ! empty( $order_infos['billing']['address_id'] ) ? $order_infos['billing']['address_id'] : '' ); ?>" />
<?php if ( true === $shipping_addresses_activated ) : ?>
	<input type="hidden" name="wps_order_selected_address[shipping]" id="wps_order_selected_address_shipping" value="<?php echo esc_attr( ! empty( $order_infos ) && ! empty( $order_infos['shipping'] ) && ! empty( $order_infos['shipping']['address_id'] ) ? $order_infos['shipping']['address_id'] : '' ); ?>" />
<?php endif; ?>

<div class="wps-gridwrapper<?php echo esc_attr( true === $shipping_addresses_activated ? 3 : 2 ); ?>-padded">
	<div>
		<?php
		// On affiche la liste des clients uniquement si le paiement est encore en attente / Display the customer list only if the order payment status is: awaiting payment.
		if ( true === $order_update_close ) : ?>
		<div class="wps-form" id="wps_customer_list_container" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_order_refresh_customer_list' ) ); ?>">
			<?php echo wps_customer_ctr::customer_select( $customer_id ); // WPCS: XSS ok. ?>
		</div>
		<?php endif; ?>

		<div id="wps_customer_account_informations" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_order_refresh_customer_informations' ) ); ?>">
			<?php if ( ! empty( $customer_datas ) ) : ?>
			<div><?php echo $customer_datas; // WPCS: XSS ok. ?></div>
			<?php else : ?>
				<div class="wps-alert-info"><span class="dashicons dashicons-info"></span><?php esc_html_e( 'Please choose a customer or create one', 'wpshop' ); ?></div>
			<?php endif; ?>
		</div>
	</div>

	<?php echo ( ! empty( $addresses ) ) ? $addresses : ''; // WPCS: XSS ok. ?>
</div>
