<?php
/**
 * Affichage du nom du client dans la liste
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

$wps_customers_contacts = new WPS_Customers_Contacts();
$contacts = $wps_customers_contacts->get_customer_contact_list( $customer_post );

?><a href="<?php echo admin_url( 'post.php?post=' . $post_id . '&amp;action=edit' ); ?>" ><?php echo $customer_post->post_title; ?></a>
<br/>
<?php foreach ( $contacts as $user_id => $user ) : ?>
<?php $current_user_datas = get_userdata( $user_id ); ?>
<div class="wps-customer-name-container" >
	<div class="alignleft" >
		<i class="dashicons dashicons-businessman" ></i><a target="_wps_wpuser_edition_page" href="<?php echo esc_url( get_edit_user_link( $user_id ) ); ?>" ><?php echo esc_html( $current_user_datas->user_email ); ?></a>
		<?php
		$contact_names = '';
		if ( ! empty( $current_user_datas->last_name ) ) {
			$contact_names .= strtoupper( $current_user_datas->last_name );
		}
		if ( ! empty( $current_user_datas->first_name ) ) {
			$contact_names .= empty( $contact_names ) ? '' : ' ';
			$contact_names .= ucfirst( strtolower( $current_user_datas->first_name ) );
		}
		$contact_names = empty( $contact_names ) ? $contact_names : ' - ' . $contact_names;
		echo esc_html( $contact_names );
	?></div>
	<div class="wps-customer-contact-list-actions alignleft hidden" >
		&nbsp;&nbsp;-
		<?php do_action( 'wps_customer_contacts_list_action', $user_id ); ?>
		<a title="<?php esc_attr_e( 'User account edition', 'wpshop' ); ?>" href="<?php echo esc_url( get_edit_user_link( $user_id ) ); ?>" target="wps_contact_edition" ><i class="dashicons dashicons-edit" ></i></a>
	</div>
</div>
<?php endforeach; ?>
