<?php
/**
 * Template d'affichage des adresses des clients dans les commandes
 *
 * @package wpshop
 * @subpackage address
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$box_content = wps_address::display_address_interface_content( $address_type_id, $address_title, '', $address_type, $customer_id, true, $order_id );

$url_separator = '?';
if ( strpos( admin_url( 'admin-ajax.php' ), '?' ) ) :
	$url_separator = '&';
endif;
?><div>
	<div class="<?php echo esc_attr( $extra_class ); ?> wps-boxed">
		<span class="wps-h3">
			<?php echo esc_html( $address_title ); ?>
			<a id="wps-add-an-address-<?php echo esc_attr( $address_type_id ); ?>"
				class="add-new-h2 alignright thickbox"
				href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php' ) . $url_separator . 'action=wps_order_load_address_edit_form&address_type=' . $address_type_id . '&customer_id=' . $customer_id . '&width=740&height=690', 'load_adress_edit_form', '_wpnonce' ) ); ?>">
					<i class="wps-icon-plus"></i><?php printf( esc_html( 'Create a %s', 'wpshop' ), strtolower( $address_title ) ); ?>
			</a>
		</span>
		<div style="clear : both;">
			<ul class="wps-itemList wps-address-container" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_reload_address_interface' ) ); ?>" id="wps-address-container-<?php echo esc_attr( $address_type_id ); ?>">
				<?php if ( ! empty( $box_content ) ) : ?>
				<?php echo $box_content; // WPCS: XSS ok. ?>
				<?php else : ?>
					<div class="wps-alert-info"><?php printf( __( 'You do not have create a %s', 'wpshop'), strtolower( $address_title ) ); ?></div>
				<?php endif; ?>
			</ul>
		</div>

	</div>
</div>
