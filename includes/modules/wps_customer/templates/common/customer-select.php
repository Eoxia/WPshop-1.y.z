<?php
/**
 * Affichage de la liste des clients de la boutique / Display customer list
 *
 * @package WPShop
 * @subpackage CUstomers
 *
 * @since 1.4.4.3
 * @version 1.4.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $customers->have_posts() ) : ?>
<select name="user[customer_id]" id="user_customer_id" data-placeholder="<?php esc_html_e( 'Choose a customer', 'wpshop' ); ?>" class="chosen_select" <?php disabled( $disabled ); ?> <?php echo ( $multiple ? ' multiple="multiple"' : '' ); ?> >
	<option value="0" ></option>

	<?php foreach ( $customers->posts as $customer ) :
		if ( 1 !== $customer->post_author ) :
			$user = get_userdata( $customer->post_author );
	?>
	<option value="<?php echo esc_attr( $customer->ID ); ?>" <?php echo ( ! $multiple && ( (int) $selected_user === $customer->ID ) ? ' selected="selected"' : '' ); ?> >
		<?php echo esc_html( $customer->post_title ); ?>
		<?php if ( is_object( $user ) ) :
			echo '<br/>' . esc_html( $user->last_name ); ?> <?php echo esc_html( $user->first_name ); ?> (<?php echo esc_html( $user->user_email ); ?>)
		<?php endif; ?>
	</option>
		<?php endif; ?>
	<?php endforeach; ?>
</select>
<?php else : ?>
<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=wps_load_customer_creation_form_in_admin&width=730&height=690' ), 'wps_load_customer_creation_form_in_admin', '_wpnonce' ) ); ?>" title="<?php esc_html_e( 'Create a customer', 'wpshop' ); ?>" class="page-title-action thickbox" ><?php esc_html_e( 'Please start by creating a new customer', 'wpshop' ); ?></a>
<?php endif; ?>
