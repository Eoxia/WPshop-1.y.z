<?php if ( !defined( 'ABSPATH' ) ) exit;
 echo $customer_post->post_title; ?>

<div class="wps-customer-name-container" >
	<div class="wps-customer-last_name" >
	<?php if ( !empty( $current_user_datas->last_name ) ) : ?>
		<?php echo $current_user_datas->last_name; ?>
	<?php else: ?>
		-
	<?php endif; ?>
	</div>

	<div class="wps-customer-first_name" >
	<?php if ( !empty( $current_user_datas->first_name ) ) : ?>
		<?php echo $current_user_datas->first_name; ?>
	<?php else: ?>
		-
	<?php endif; ?>
	</div>
</div>

<div class="row-actions" >
	<a href="<?php echo admin_url( 'post.php?post=' . $post_id . '&amp;action=edit' ); ?>" ><?php _e( 'View' ); ?></a> |
	<?php if ( current_user_can( 'edit_users' ) ) : ?>
	<a target="_wps_wpuser_edition_page" href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $current_user_id_in_list ) ); ?>" ><?php printf( __( 'View associated user (id: %d)', 'wpshop' ), $current_user_id_in_list ); ?></a>
	<?php apply_filters( 'wps_filter_customer_list_actions', $post_id, $current_user_id_in_list ); ?>
	<?php else: ?>
	<?php printf( __( 'WP-User %d', 'wpshop' ), $current_user_id_in_list); ?>
	<?php endif; ?>
</div>
