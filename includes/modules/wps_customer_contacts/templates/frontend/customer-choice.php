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
?><?php esc_html_e( 'Customer account', 'wpshop' ); ?><select>
<?php foreach ( $customers as $customer ) : ?>
	<option value="<?php echo esc_attr( $customer->ID ); ?>" ><?php echo esc_html( $customer->post_title ); ?></option>
<?php endforeach; ?>
</select>
