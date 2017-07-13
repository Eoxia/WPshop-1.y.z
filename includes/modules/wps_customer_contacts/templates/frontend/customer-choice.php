<?php
/**
 * Affichage de la liste des comptes clients disponible pour l'utiilsateur actuellement connecté / Display customer list for selection into current connected user
 *
 * @package WPShop
 * @subpackage CRM
 *
 * @since 3.0.0.0
 * @version 3.0.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$customer_id_from_cookie = ! empty( $_COOKIE ) && ! empty( $_COOKIE['wps_current_connected_customer'] ) ? (int) $_COOKIE['wps_current_connected_customer'] : wps_customer_ctr::get_customer_id_by_author_id( get_current_user_id() );

?><?php esc_html_e( 'Customer account', 'wpshop' ); ?><select id="wps-customer-contacts-user-selection" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wps-customer-switch-to' ) ); ?>" >
<?php foreach ( $customers as $customer ) : ?>
	<option value="<?php echo esc_attr( $customer->ID ); ?>" <?php selected( $customer->ID, $customer_id_from_cookie, true ); ?>><?php echo esc_html( $customer->post_title ); ?></option>
<?php endforeach; ?>
</select>
