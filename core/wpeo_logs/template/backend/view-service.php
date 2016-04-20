<?php if ( !defined( 'ABSPATH' ) ) exit;
$service_id = (int) $_GET['service_id'];
$sanitize_type = sanitize_text_field( $_GET['type'] );
$sanitize_key = (int) $_GET['key'];

?>

<div class="tablenav bottom">
	<div class="alignleft actions bulkactions">
		<a href="<?php echo admin_url( 'tools.php?page=wpeo-log-page' ); ?>" class="button"><?php _e( 'Back', 'wpeolog-i18n'); ?></a>
	</div>
</div>

<h3><?php _e( 'Archive', 'wpeolog-i18n' ); ?></h3>
<table class="wp-list-table widefat fixed striped posts">
	<thead>
		<tr>
			<th scope="col" id="active" class="manage-column"><?php _e( 'Title', 'wpeolog-i18n' ); ?></th>
			<th scope="col" id="title" class="manage-column"><?php _e( 'Size', 'wpeolog-i18n' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( !empty( $list_archive_file ) ): ?>
			<?php foreach ( $list_archive_file as $key => $archive_file ): ?>
				<tr>
					<td>
						<?php if ( isset( $sanitize_key ) && $sanitize_key == $key ): ?>
							<?php echo substr( $archive_file, 0, -4 ); ?>
						<?php else: ?>
							<a href="<?php echo admin_url( 'tools.php?page=wpeo-log-page&service_id=' . $service_id . '&action=view&type=' .$sanitize_type . '&key=' . $key ); ?>"><?php echo substr( $archive_file, 0, -4 ); ?></a>
						<?php endif; ?>
						<div class="row-actions">
							<span class="trash"><a class="submitdelete" title="<?php _e( 'Move this item to the Trash', 'wpeolog-i18n' ); ?>" href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?file_name=' . $archive_file . '&action=file_to_trash' ), 'to_trash_' . $key ); ?>"><?php _e( 'Trash', 'wpeolog-i18n' ); ?></a> </span>
						</div>
					</td>
					<td><?php echo filesize( $dir_file . $archive_file ); ?>oc</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr><td><?php _e( 'No archive file', 'wpeolog-i18n' ); ?></td></tr>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<th scope="col" id="active" class="manage-column"><?php _e( 'Title', 'wpeolog-i18n' ); ?></th>
			<th scope="col" id="size" class="manage-column"><?php _e( 'Size', 'wpeolog-i18n' ); ?></th>
		</tr>
	</tfoot>
</table>

<br />

<h3><?php _e( 'Data', 'wpeolog-i18n' ); ?></h3>
<table id='wpeo-table-data-csv' class="tablesorter wp-list-table widefat fixed posts">
	<thead>
    	<tr>
      		<th id="author" class="header-order manage-column column-author" style="" scope="col"><?php _e('Author', 'wpeolog-i18n'); ?></th>
	        <th id="severity" class="header-order manage-column column-severity" style="" scope="col"><?php _e('Severity', 'wpeolog-i18n'); ?></th>
	        <th id="object-id" class="header-order manage-column column-object-id" style="" scope="col"><?php _e('Object ID', 'wpeolog-i18n'); ?></th>
	        <th style='width: 50%;' id="message" class="header-order manage-column column-message" style="" scope="col"><?php _e('Message', 'wpeolog-i18n'); ?></th>
	        <th id="date" class="header-order manage-column column-date sortable <?php echo (!empty($order)) ? $order : ""; ?>" style="" scope="col">
            	<span>Date</span>
        	</th>
    	</tr>
  	</thead>

 	<tbody>
 		<?php if ( !empty( $file ) ): ?>
  			<?php foreach ( $file as $key => $value ):
  				$user_email = !empty( $value[1] ) ? get_userdata( $value[1] ) : __( 'Empty', 'wpeolog-i18n' );
  				if ( gettype( $user_email ) == 'object' ) {
					$user_email = $user_email->user_email;
				}

  				?>
    			<tr>
				    <td><?php echo esc_html( $user_email ); ?></td>
				    <td><?php echo !empty( $value[5] ) ? esc_html( $value[5] ) : 0; ?></td>
				    <td><?php echo !empty( $value[3] ) ? esc_html( $value[3] ) : __( 'Empty', 'wpeolog-i18n' ); ?></td>
				    <td><?php echo !(empty($value[4])) ? trim(esc_html( $value[4] ), "\"" ) : __('Empty', 'wpeolog-i18n'); ?></td>
			    	<td><?php echo !(empty($value[0])) ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), esc_html( $value[0] ), true ) : __('Empty', 'wpeolog-i18n'); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
  			<tr>
    			<td><?php _e('Nothing to display, select your log of file', 'wpeolog-i18n'); ?></td>
  			</tr>
		<?php endif; ?>

 	</tbody>
</table>
