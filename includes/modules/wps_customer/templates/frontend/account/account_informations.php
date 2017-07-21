<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed">
	<?php echo $attributes_sections_tpl; ?>
	<?php if ( $customer_link ) : ?>
		<div><a href="<?php echo get_edit_post_link( $cid ); ?>"><?php esc_html_e( 'See customer', 'wpshop' ); ?></a></div>
		<?php apply_filters( 'wps_filter_customer_in_order', $cid, wps_customer_ctr::get_author_id_by_customer_id( $cid ) ); ?>
	<?php elseif ( ! is_admin() || strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) ) : ?>
		<div><button data-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_fill_account_informations_modal' ) ); ?>" data-customer-id="<?php echo esc_attr( $cid ); ?>" class="wps-link wps-alignright" id="wps_modal_account_informations_opener"><i class="wps-icon-pencil"></i><?php esc_html_e( 'Edit your account informations', 'wpshop' ); ?></button></div>
	<?php endif; ?>
</div>
