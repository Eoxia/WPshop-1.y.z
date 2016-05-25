<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-table wps-my-message">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Message title', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php _e( 'Send date', 'wpshop' ); ?></div>
	</div>
	<?php if( !empty($messages_data) && is_array($messages_data) ) :?>
	<?php foreach( $messages_data as $meta_id => $message ) : ?>

	<div class="wps-table-content wps-table-row" data-nonce="<?php echo wp_create_nonce( 'get_content_message' ); ?>" data-id="<?php echo $meta_id; ?>" data-date="<?php echo substr($message[0]['mess_dispatch_date'][0], 0, 7); ?>">
		<div class="wps-table-cell">
			<span class="wps-message-title"><?php echo $message[0]['mess_title']; ?></span>
			<?php if( !empty($message[0]['mess_object_id']) ) {
				$order_meta = get_post_meta( $message[0]['mess_object_id'], '_order_postmeta', true );
				$comments = get_post_meta( $message[0]['mess_object_id'], '_order_private_comments', true);
				if(!empty($comments)) {
					foreach ( $comments as $comment ) {
						$user_data = get_userdata( $comment['author'] );
						echo '<br><b>' . $order_meta['order_key'] . '</b> <i>';
						printf( __( '%s says on', 'wpshop'), $user_data->user_login );
						echo ' ' . mysql2date( get_option('links_updated_date_format'), $comment['comment_date'], true ) . '</i> : <br>' . $comment['comment'];
					}
				}
			} ?>
		</div>
		<div class="wps-table-cell">
		<?php if( !empty($message[0]['mess_dispatch_date']) ) : ?>
			<ul>
			<?php foreach( $message[0]['mess_dispatch_date'] as $date ) : ?>
				<li><?php echo mysql2date( get_option('date_format') . ' ' . get_option('time_format') , $date, true ); ?></li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		</div>
	</div>
	<?php endforeach; ?>
	<?php endif;?>
</div>
