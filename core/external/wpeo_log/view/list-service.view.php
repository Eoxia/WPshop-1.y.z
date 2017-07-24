<?php namespace eoxia;

if ( !defined( 'ABSPATH' ) ) exit; ?>

<div class="tablenav top">
 	<div class="tablenav-pages">
		<span class="displaying-num"><?php echo !empty( $array_service ) ? count( $array_service ) : 0; _e( ' item(s)', 'digirisk' ); ?></span>
	</div>
</div>

<table class="wp-list-table widefat fixed striped posts">
	<thead>
		<tr>
			<th scope="col" id="active" class="manage-column"><?php _e( 'Active', 'digirisk' ); ?></th>
			<th scope="col" id="title" class="manage-column"><?php _e( 'Title', 'digirisk' ); ?></th>
			<th scope="col" id="errors" class="manage-column"><?php _e( 'Errors', 'digirisk' ); ?></th>
			<th scope="col" id="warnings" class="manage-column"><?php _e( 'Warnings', 'digirisk' ); ?></th>
			<th scope="col" id="size" class="manage-column"><?php _e( 'Size', 'digirisk' ); ?></th>
			<th scope="col" id="date" class="manage-column"><?php _e( 'Date', 'digirisk' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( !empty( $array_service ) ): ?>
			<?php foreach ( $array_service as $key => $service ): ?>
				<tr>
					<th scope="row"><input disabled type="checkbox" name="service[<?php echo $key; ?>][active]" <?php echo !empty( $service['active'] ) ? 'checked="checked"' : ''; ?> ></th>
					<td>
						<a class="view" title="<?php _e( 'View this service', 'digirisk' ); ?>" href="<?php echo wp_nonce_url( admin_url( 'tools.php?page=wpeo-log-page&service_id=' . $key . '&action=view&type=info' ), 'view_' . $key ); ?>"><?php echo !empty( $service['name'] ) ? $service['name'] : ''; ?></a>
						<div class="row-actions">
							<span class="view"><a class="view" title="<?php _e( 'View this service', 'digirisk' ); ?>" href="<?php echo admin_url( 'tools.php?page=wpeo-log-page&service_id=' . $key . '&action=view&type=info' ); ?>"><?php _e( 'View', 'digirisk' ); ?></a> </span>
							<span class="trash"><a class="submitdelete" title="<?php _e( 'Move this service to the Trash', 'digirisk' ); ?>" href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?service_id=' . $key . '&action=to_trash' ), 'to_trash_' . $key ); ?>"><?php _e( 'Trash', 'digirisk' ); ?></a> </span>
						</div>
					</td>
					<td><a href="<?php echo admin_url( 'tools.php?page=wpeo-log-page&service_id=' . $key . '&action=view&type=error' ); ?>"><?php echo $service['error']['count']; ?></a></td>
					<td><a href="<?php echo admin_url( 'tools.php?page=wpeo-log-page&service_id=' . $key . '&action=view&type=warning' ); ?>"><?php echo $service['warning']['count']; ?></a></td>
					<td>
						<?php
						echo !empty( $service['size'] ) ? size_util::g()->convert_to( $service['size'], $service['format'] , false ) : '';
						echo !empty( $service['format'] ) ? $service['format'] : 'oc'
						?>
					</td>
					<td><?php echo mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $service['created_date'], true ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td><?php _e( 'No services found.', 'digirisk' ); ?></td>
			</tr>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<th scope="col" id="id" class="manage-column"><?php _e( 'Active', 'digirisk' ); ?></th>
			<th scope="col" id="active" class="manage-column"><?php _e( 'Title', 'digirisk' ); ?></th>
			<th scope="col" id="size" class="manage-column"><?php _e( 'Errors', 'digirisk' ); ?></th>
			<th scope="col" id="size-format" class="manage-column"><?php _e( 'Warnings', 'digirisk' ); ?></th>
			<th scope="col" id="number-file" class="manage-column"><?php _e( 'Size', 'digirisk' ); ?></th>
			<th scope="col" id="number-file" class="manage-column"><?php _e( 'Date', 'digirisk' ); ?></th>
		</tr>
	</tfoot>
</table>

<div class="tablenav bottom">
	<div class="alignleft actions bulkactions">
		<a href="<?php echo admin_url( 'tools.php?page=wpeo-log-page&action=edit' ); ?>" class="button button-primary"><?php _e( 'Quick edit', 'digirisk'); ?></a>
	</div>
</div>
