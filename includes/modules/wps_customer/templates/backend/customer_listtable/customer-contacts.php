<?php
/**
 * Affichage des contacts pour un client
 *
 * @package WPShop
 * @subpackage Customer
 *
 * @since 3.0.0.0
 * @version 3.0.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$WPS_Customers_Contacts = new WPS_Customers_Contacts();
$contacts = $WPS_Customers_Contacts->get_customer_contact_list( $customer_post );

?>
<?php foreach ( $contacts as $user_id => $user ) : ?>
<?php $current_user_datas = get_userdata( $user_id ); ?>
<div class="wps-customer-name-container" >
	<a target="_wps_wpuser_edition_page" href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $user_id ) ); ?>" ><?php echo esc_html( $current_user_datas->user_email ); ?></a>
</div>

<div class="row-actions" >
	<?php if ( current_user_can( 'edit_users' ) ) : ?>
	<?php //apply_filters( 'wps_filter_customer_list_actions', $post_id, $user_id ); ?>
	<?php else : ?>
	<?php printf( __( 'WP-User %d', 'wpshop' ), $user_id ); ?>
	<?php endif; ?>
</div>
<?php endforeach; ?>
