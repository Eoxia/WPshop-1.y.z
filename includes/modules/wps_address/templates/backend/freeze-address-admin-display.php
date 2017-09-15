<?php
/**
 * Affichage d'une adresse en lecture seule  dans les commandes / Display an read only address in orders
 *
 * @package WPShop
 * @subpackage Addresses
 *
 * @version 1.4.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div id="wps_customer_addresses" >
	<div class="wps-boxed summary_shipping_boxed" id="wpshop_order_<?php echo esc_attr( $address_type ); ?>" >
		<div class="wps-h5"><?php esc_html_e( ucfirst( $address_type ) . ' address', 'wpshop' ); ?></div>
		<?php if ( ! empty( $address_informations ) && ! empty( $order_infos[ $address_type ]['id'] ) ) : ?>
			<?php echo wps_address::display_an_address( $address_informations, '', $order_infos[ $address_type ]['id'] ); // WPCS: XSS ok. ?>
		<?php else : ?>
			<div class="wps-alert-error"><?php esc_html_e( 'Nothing setted for this address', 'wpshop' ); ?></div>
		<?php endif; ?>
	</div>
</div>
