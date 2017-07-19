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
	?>
</div>
<?php endforeach; ?>
