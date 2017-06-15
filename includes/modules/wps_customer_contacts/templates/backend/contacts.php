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
?><table class="wp-list-table widefat fixed striped users"
	data-dissociate-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_customer_contacts_dissociate' ) ); ?>"
	data-change-default-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_customer_contacts_change_default' ) ); ?>"
	data-default-user-id="<?php echo esc_attr( $default_user ); ?>"  >
	<tbody>
		<?php foreach ( $users as $user_id => $user ) : ?>
			<?php if ( 0 !== $user_id ) : ?>
			<tr data-customer-id="<?php echo esc_attr( $customer->ID ); ?>" data-user-id="<?php echo esc_attr( $user_id ); ?>" >
				<td>#<?php echo esc_html( $user_id ); ?></td>
				<td><?php echo esc_html( $user['last_name'] . ' ' . $user['first_name'] ); ?></td>
				<td><?php echo esc_html( $user['display_name'] ); ?></td>
				<td><?php echo esc_html( $user['user_email'] ); ?></td>

				<td class="wps-customer-contacts-actions" >

				<?php do_action( 'wps_customer_contacts_list_action', $user_id ); ?>

				<?php if ( true === $user['is_default'] ) : ?>
					<i title="<?php esc_attr_e( 'This is the main contact / customer creator', 'wpshop' ); ?>" class="dashicons dashicons-star-filled" ></i>
				<?php else : ?>
					<i title="<?php esc_attr_e( 'Change to default customer', 'wpshop' ); ?>" class="dashicons dashicons-star-empty" ></i>
				<?php endif; ?>

				<!-- <a title="<?php esc_attr_e( 'User account edition', 'wpshop' ); ?>" href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=load_user_account_form&_wpnonce=' . wp_create_nonce( 'load_user_account_form' ) . '&user_id=' . $user_id ) ); ?>" class="thickbox" ><i class="dashicons dashicons-edit" ></i></a> -->

				<?php if ( true === $user['is_default'] ) : ?>
					<i title="<?php esc_attr_e( 'This is the main contact, you can\'t unlink it', 'wpshop' ); ?>" class="dashicons dashicons-lock" ></i>
				<?php else : ?>
					<i title="<?php esc_attr_e( 'Unlink this user', 'wpshop' ); ?>" class="dashicons dashicons-editor-unlink" ></i>
				<?php endif; ?>
				</td>
			</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	</tbody>

	<tfoot>
		<tr>
			<td colspan="5" class="wps-customer-associate-contact-container" >
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
