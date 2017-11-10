<?php
/**
 * Display contact list for a customer.
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

$default_user = 0;
ob_start();
require( wpshop_tools::get_template_part( WPS_CUST_CONTACT_DIR, WPS_CUST_CONTACT_TPL, 'backend', 'contact' ) );
$output = ob_get_clean();

?><table class="wp-list-table widefat fixed striped users"
	data-dissociate-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_customer_contacts_dissociate' ) ); ?>"
	data-change-default-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_customer_contacts_change_default' ) ); ?>"
	data-default-user-id="<?php echo esc_attr( $default_user ); ?>"  >
	<input type="hidden" name="wps_customer_contacts_default_id" value="<?php echo esc_attr( $default_user ); ?>" />

	<thead>
		<tr data-customer-id="<?php echo esc_attr( $customer->ID ); ?>" data-user-id="<?php echo esc_attr( $user_id ); ?>" >
			<td><?php esc_html_e( 'ID', 'wpshop' ); ?></td>
			<td><?php esc_html_e( 'Last name', 'wpshop' ); ?></td>
			<td><?php esc_html_e( 'First name', 'wpshop' ); ?></td>
			<td><?php esc_html_e( 'Display name' ); ?></td>
			<td><?php esc_html_e( 'Email' ); ?></td>
			<td><?php esc_html_e( 'Phone', 'wpshop' ); ?></td>
			<td class="wps-customer-contacts-actions" >&nbsp;</td>
		</tr>
	</thead>

	<tbody>
		<?php echo $output; // WPCS: XSS ok. ?>
	</tbody>

	<tfoot>
		<tr>
			<td colspan="7" class="wps-customer-associate-contact-container" >
				<span class="wps-customer-contact-association-opener" ><i class="dashicons dashicons-plus" ></i></span>
				<input type="text" class="wps-customer-autocomplete-input hidden" placeholder="<?php esc_html_e( 'Start typing for user searching', 'wpshop' ); ?>"
					data-search-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_customer_search' ) ); ?>"
					data-types="users"
					data-customer="<?php echo esc_attr( $customer->ID ); ?>"
					data-associate-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_customer_contacts_associate' ) ); ?>" />
			</td>
		</tr>
	</tfoot>

</table>
<script type="text/javascript" >jQuery( document ).ready( function(){ /** Ecoute les actions sur le champs de recherche des utilisateurs / Listen event on user search input */ wpshopCRM.user_search(); } );</script>
