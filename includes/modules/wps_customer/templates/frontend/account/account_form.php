<?php
/**
 *  Template pour le formulaire d'édition d'un compte client
 *
 * @package WPShop
 * @subpackage Customers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="wps_signup_error_container"></div>
<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wps_account_informations_form">
	<input type="hidden" name="action" value="wps_save_account_informations" />
	<input type="hidden" name="customer_id" value="<?php echo esc_attr( $cid ); ?>" />
	<?php wp_nonce_field( 'wps_save_account_informations' ); ?>

	<?php echo $this->display_account_form_fields( $cid ); // WPCS: XSS ok. ?>

	<?php echo $this->display_commercial_newsletter_form(); // WPCS: XSS ok. ?>
</form>


<button class="wps-bton-first-rounded" id="wps_account_form_button"><?php esc_html_e( 'Save', 'wpshop' ); ?></button>
