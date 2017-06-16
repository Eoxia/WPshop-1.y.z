<?php
/**
 * Affichage du formaulaire client dans le backadmin
 *
 * @package WPShop
 * @subpackage Customers
 *
 * @since 1.4.4.3
 * @version 1.4.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wps-boxed">
	<div id="wps_signup_error_container"></div>
	<?php echo $this->display_account_form_fields( $cid ); // WPCS: XSS ok. ?>
</div>
