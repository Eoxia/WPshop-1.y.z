<?php
/**
 * Display a contact item for a customer
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
?>
<?php foreach ( $users as $user_id => $user ) : ?>
	<?php if ( 0 !== $user_id ) : ?>
		<?php if ( true === $user['is_default'] ) : ?>
			<?php $default_user = $user_id; ?>
		<?php endif; ?>
	<tr data-customer-id="<?php echo esc_attr( $customer->ID ); ?>" data-user-id="<?php echo esc_attr( $user_id ); ?>" >
		<td>#<?php echo esc_html( $user_id ); ?></td>
		<td><?php echo empty( $user['last_name'] ) ? '&mdash;' : esc_html( $user['last_name'] ); ?></td>
		<td><?php echo empty( $user['first_name'] ) ? '&mdash;' : esc_html( $user['first_name'] ); ?></td>
		<td><?php echo esc_html( $user['display_name'] ); ?></td>
		<td><?php echo esc_html( $user['user_email'] ); ?></td>
		<td><?php echo ( isset( $user['metas']['wps_phone'] ) && ! empty( $user['metas']['wps_phone'] ) ? esc_html( implode( ',', $user['metas']['wps_phone'] ) ) : '&mdash;' ); ?></td>

		<td class="wps-customer-contacts-actions" >
			<?php do_action( 'wps_customer_contacts_list_action', $user_id ); ?>
			<a title="<?php esc_attr_e( 'User account edition', 'wpshop' ); ?>" href="<?php echo esc_url( get_edit_user_link( $user_id ) ); ?>" target="wps_contact_edition" ><i class="dashicons dashicons-edit" ></i></a>

		<?php if ( true === $user['is_default'] ) : ?>
			<i title="<?php esc_attr_e( 'This is the main contact / customer creator', 'wpshop' ); ?>" class="dashicons dashicons-star-filled" ></i>
			<i title="<?php esc_attr_e( 'This is the main contact, you can\'t unlink it', 'wpshop' ); ?>" class="dashicons dashicons-lock" ></i>
		<?php else : ?>
			<i title="<?php esc_attr_e( 'Change to default customer', 'wpshop' ); ?>" class="dashicons dashicons-star-empty" ></i>
			<i title="<?php esc_attr_e( 'Unlink this user', 'wpshop' ); ?>" class="dashicons dashicons-editor-unlink" ></i>
		<?php endif; ?>

		</td>
	</tr>
	<?php endif; ?>
<?php endforeach; ?>
