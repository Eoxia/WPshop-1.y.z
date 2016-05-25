<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( $messages ) : ?>
<?php 
	$formated_message_tab = array();
	foreach( $messages as $message_by_month) : 
		if(!empty($message_by_month)):
			foreach( $message_by_month as $m ) : 
				if( !empty($formated_message_tab[ $m['mess_user_id'] ]) ) :
					$formated_message_tab[ $m['mess_user_id'] ]['mess_dispatch_date'][] = $m['mess_dispatch_date'][0];
				else : 
					$formated_message_tab[ $m['mess_user_id'] ] = array( 'mess_user_email' => $m['mess_user_email'], 
																		 'mess_title' => $m['mess_title'], 
																		 'mess_dispatch_date' => array( $m['mess_dispatch_date'][0] ) 		
																		);
				endif;
				
			endforeach;
		endif;
	endforeach;

	if( !empty($formated_message_tab) ) : 
?>
	<div class="wps-table">
		<div class="wps-table-header wps-table-row">
			<div class="wps-table-cell"><?php _e( 'User ID', 'wpshop'); ?></div>
			<div class="wps-table-cell"><?php _e( 'User e-mail', 'wpshop'); ?></div>
			<div class="wps-table-cell"><?php _e( 'Message title', 'wpshop'); ?></div>
			<div class="wps-table-cell"><?php _e( 'Dispatch dates', 'wpshop'); ?></div>
		</div>

<?php 
		foreach( $formated_message_tab as $user_id => $formated_message ) :
?>
			<div class="wps-table-content wps-table-row">
				<div class="wps-table-cell"><?php echo '#'.$user_id; ?></div>
				<div class="wps-table-cell"><?php echo $formated_message['mess_user_email']; ?></div>
				<div class="wps-table-cell"><?php echo $formated_message['mess_title']; ?></div>
				<div class="wps-table-cell"><ul>
					<?php if( !empty($formated_message['mess_dispatch_date']) ) : 
							foreach( $formated_message['mess_dispatch_date'] as $dispatch_date ) : 
					?>
							<li><?php echo  mysql2date('d F Y H:i:s', $dispatch_date, true); ?></li>
					<?php 
						endforeach;
					endif; ?>
					</ul>
				</div>
			</div>
<?php 
		endforeach;	
?>
</div>
<?php 
	endif;
?>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'This message has never been send', 'wpshop' )?></div>
<?php endif; ?>