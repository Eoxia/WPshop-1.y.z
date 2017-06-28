<?php
/**
 * Formulaire de création/édition d'une adresse / Creation/update form for an address
 *
 * @package WPShop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="wps_address_error_container"></div>
<form id="wps_address_form_save" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
	<input type="hidden" name="action" value="wps_save_address" />
	<input type="hidden" name="post_ID" value="<?php echo esc_attr( $customer_id ); ?>" />
	<?php wp_nonce_field( 'wps_save_address' ); ?>
	<?php echo self::display_form_fields( $address_type_id, $address_id, '', '', array(), array(), array(), $customer_id ); // WPCS: XSS ok. ?>

	<?php
	/** Check if a billing address is already save **/
	if ( $first_address_checking && ( $address_type_id !== (int) $billing_option['choice'] ) ) : ?>
		<div class="wps-form">
			<input name="wps-shipping-to-billing" id="wps-shipping-to-billing" checked="checked" type="checkbox" /> <label for="wps-shipping-to-billing"><?php esc_html_e( 'Use the same address for billing', 'wpshop' ); ?></label>
		</div>
	<?php endif; ?>

	<button id="wps_submit_address_form" class="wps-bton-first-alignRight-rounded" ><?php esc_html_e( 'Save', 'wpshop' ); ?></button>
</form>
