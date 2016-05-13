<?php if ( !defined( 'ABSPATH' ) ) exit;
 $order_private_comments = get_post_meta( $oid, '_order_private_comments', true); ?>
<?php if( !empty($order_private_comments) ) : ?>
<?php $order_private_comments = array_reverse($order_private_comments); 
?>
	<?php foreach($order_private_comments as $order_private_comment ) : ?>
	<div class="wps_private_comment">
		<div class="wps_private_comment_avatar">
			<?php $user_id = ( !empty($order_private_comment) && !empty($order_private_comment['author']) ) ? $order_private_comment['author'] : get_current_user_id() ; ?>
			<?php echo get_avatar( $user_id, 30 ); ?>
			<?php $user_data = get_userdata( $user_id ); ?>
		</div>
		<div class="wps_private_comment_author_informations">
			<span class="wps_private_comment_author">
			<?php echo ( !empty($order_private_comment['send_email']) && $order_private_comment['send_email'] === true ) ? '<span class="dashicons dashicons-email-alt"></span>' : ''; ?> 
			<?php printf( __( '%s says on', 'wpshop'), $user_data->user_login ); ?></span> 
			<span class="wps_private_comment_date"><?php echo mysql2date('d F Y H:i:s', $order_private_comment['comment_date'], true); ?></span>
		</div>
		<div class="wps_private_comment_message"><?php echo $order_private_comment['comment']; ?></div>
	</div>
	<?php endforeach; ?>
<?php endif; ?>
