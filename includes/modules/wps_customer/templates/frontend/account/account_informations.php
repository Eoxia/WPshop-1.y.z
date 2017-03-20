<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed">
	<?php echo $attributes_sections_tpl; ?>
	<?php if( !$is_from_admin ) : ?>
	<div><button data-nonce="<?php echo wp_create_nonce( 'wps_fill_account_informations_modal' ); ?>" class="wps-link wps-alignright" id="wps_modal_account_informations_opener"><i class="wps-icon-pencil"></i><?php _e( 'Edit your account informations', 'wpshop')?></button></div>
	<?php elseif( $customer_link ) : ?>
		<div><a href="<?php echo get_edit_post_link( $cid ); ?>"><?php esc_html_e( 'See customer', 'wpshop' ); ?></a></div>
		<?php apply_filters( 'wps_filter_customer_in_order', $cid, wps_customer_ctr::get_author_id_by_customer_id( $cid ) ); ?>
	<?php endif; ?>
</div>
