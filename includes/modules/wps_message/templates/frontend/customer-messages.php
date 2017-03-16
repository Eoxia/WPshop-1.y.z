<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($messages_histo) && is_array($messages_histo) ) :?>
	<div class="wps-table wps-my-message">
		<div class="wps-table-header wps-table-row">
			<div class="wps-table-cell"><?php _e( 'Message title', 'wpshop' ); ?></div>
			<div class="wps-table-cell"><?php _e( 'Send date', 'wpshop' ); ?></div>
		</div>
		<?php $page_message_histo = isset( $_GET['page_message_histo'] ) ? (int) $_GET['page_message_histo'] : 1;
		$nb_pages_messages_histo = ceil( count( $messages_histo ) / wps_message_ctr::$mails_display );
		$messages_histo = array_slice( $messages_histo, ( $page_message_histo - 1 ) * wps_message_ctr::$mails_display, wps_message_ctr::$mails_display );
		reset( $messages_histo );
		while( $messages = current( $messages_histo ) ) :
			$first_send_date = key( $messages_histo );
			reset( $messages );
			while( $message = current( $messages ) ) :
				$key = key( $messages ); ?>
		<div class="wps-table-content wps-table-row" data-date="<?php echo substr($first_send_date, 0, 7); ?>" >
			<div class="wps-table-cell wps-message-title-container">
				<?php $message_special_id = rand(); ?>
				<span class="wps-message-title"><a title="<?php echo $message['title']; ?>" href="#TB_inline?width=600&height=550&inlineId=wps-customer-message-<?php echo $message_special_id; ?>" class="thickbox" ><?php echo $message['title']; ?></a></span>
				<div id="wps-customer-message-<?php echo $message_special_id; ?>" style="display:none;" ><?php echo $message['message']; ?></div>
			</div>
			<div class="wps-table-cell">
			<?php if( !empty($message['dates']) ) : ?>
				<ul>
				<?php foreach( $message['dates'] as $date ) : ?>
					<li><?php echo mysql2date( get_option('date_format') . ' ' . get_option('time_format') , $date, true ); ?></li>
				<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			</div>
		</div>
		<?php	next( $messages );
			endwhile;
			next( $messages_histo );
		endwhile; ?>
	</div>
	<?php if( $nb_pages_messages_histo != 1 ) {
		for( $i = 1; $i <= $nb_pages_messages_histo; $i++ ) { ?>
		<?php echo '<' . ( ( $page_message_histo == $i ) ? 'span' : 'a href="' . add_query_arg( array( 'page_message_histo' => $i ) ) . '"' ) . ' class="page-numbers">' . $i . '</' . ( ( $page_message_histo == $i ) ? 'span' : 'a' ) . '>'; ?>
	<?php }
	} ?>
<?php else: ?>
	<div class="wps-alert-info">
		<?php _e( 'No email was sent.', 'wpshop' ); ?>
	</div>
<?php endif; ?>
