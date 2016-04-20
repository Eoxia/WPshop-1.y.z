<?php if ( !defined( 'ABSPATH' ) ) exit; ?>

<div class="tablenav top">	
 	<div class="tablenav-pages">
		<span class="displaying-num"><?php echo !empty( $array_service ) ? count( $array_service ) : 0; _e( ' item(s)', 'wpeolog-i18n' ); ?></span>
	</div>
</div>


<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
	<input type="hidden" name="action" value="edit_service" />

	<table class="wp-list-table widefat fixed striped posts">
		<thead>
		<tr>			
			<th scope="col" id="id" class="manage-column"><?php _e( 'ID', 'wpeolog-i18n' ); ?></th>
			<th scope="col" id="active" class="manage-column"><?php _e( 'Active', 'wpeolog-i18n' ); ?></th>
			<th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
				<?php _e( 'Name', 'wpeolog-i18n' ); ?></th>			
			<th scope="col" id="size" class="manage-column"><?php _e( 'Size', 'wpeolog-i18n' ); ?></th>
			<th scope="col" id="size-format" class="manage-column"><?php _e( 'File size', 'wpeolog-i18n' ); ?></th>
			<th scope="col" id="rotate" class="manage-column"><?php _e( 'File rotate', 'wpeolog-i18n' ); ?></th>
			<th scope="col" id="number-file" class="manage-column"><?php _e( 'Number file', 'wpeolog-i18n' ); ?></th>
			</tr>
		</thead>

		<tbody id="the-list">
			<?php if ( !empty( $array_service ) ): ?>
				<?php foreach ( $array_service as $key => $service ): ?>
					<tr>
						<td><?php echo $key; ?></td>
						<th scope="row">
							<input type="hidden" name="service[<?php echo $key; ?>][created_date]" value="<?php echo !empty( $service['created_date'] ) ? $service['created_date'] : ''; ?>" />
							<input type="checkbox" name="service[<?php echo $key; ?>][active]" <?php echo !empty( $service['active'] ) ? 'checked="checked"' : ''; ?> />
						</th>
						<td>
							<input type="text" name="service[<?php echo $key; ?>][name]" value="<?php echo !empty( $service['name'] ) ? $service['name'] : ''; ?>" />
							<div class="row-actions">
								<span class="trash"><a class="submitdelete" title="<?php _e( 'Move this item to the Trash', 'wpeolog-i18n' ); ?>" href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?service_id=' . $key . '&action=to_trash' ), 'to_trash_' . $key ); ?>"><?php _e( 'Trash', 'wpeolog-i18n' ); ?></a> </span>
							</div>
						</td>
						<td><input type="text" name="service[<?php echo $key; ?>][size]" value="<?php echo !empty( $service['size'] ) ? $this->convert_to( $service['size'], $service['format'] , false ) : ''; ?>" /></td>
						<td>
							<select name="service[<?php echo $key; ?>][format]">
						    	<?php if ( !empty( $array_size_format ) ): ?>
						    		<?php foreach ( $array_size_format as $key_format => $value ): ?>
						       			<option <?php echo selected( !empty( $service['format'] ) ? $service['format'] : 'oc', $key_format ); ?> value='<?php echo $key_format; ?>'><?php echo $value; ?></option>
						      		<?php endforeach; ?>
						   	 	<?php endif; ?>
							</select>
						</td>
						<td>
							<select name="service[<?php echo $key; ?>][rotate]">
								<?php if ( !empty( $array_file_rotate_dropdown ) ): ?>
									<?php foreach ( $array_file_rotate_dropdown as $key_rotate => $value ): ?>
										<option <?php echo selected( !empty( $service['rotate'] ) ? $service['rotate'] : 'on', $key_rotate ); ?> value='<?php echo $key_rotate; ?>'><?php echo $value; ?></option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</td>
						<td><input type="text" name="service[<?php echo $key; ?>][number]" value="<?php echo !empty( $service['number'] ) ? $service['number'] : 0; ?>" /></td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td><?php _e( 'No services found.', 'wpeolog-i18n' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>

		<tfoot>
			<tr>			
				<th scope="col" id="id" class="manage-column"><?php _e( 'ID', 'wpeolog-i18n' ); ?></th>
				<th scope="col" id="active" class="manage-column"><?php _e( 'Active', 'wpeolog-i18n' ); ?></th>
				<th scope="col" id="title" class="manage-column column-title column-primary sortable desc"><?php _e( 'Name', 'wpeolog-i18n' ); ?></th>			
				<th scope="col" id="size" class="manage-column"><?php _e( 'Size', 'wpeolog-i18n' ); ?></th>
				<th scope="col" id="size-format" class="manage-column"><?php _e( 'File size', 'wpeolog-i18n' ); ?></th>
				<th scope="col" id="rotate" class="manage-column"><?php _e( 'File rotate', 'wpeolog-i18n' ); ?></th>
				<th scope="col" id="number-file" class="manage-column"><?php _e( 'Number file', 'wpeolog-i18n' ); ?></th>
			</tr>
		</tfoot>
	</table>
	
	<div class="tablenav bottom">
		<div class="alignleft actions bulkactions">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'wpeolog-i18n'); ?>" />
			<a href="<?php echo admin_url( 'tools.php?page=wpeo-log-page' ); ?>" class="button"><?php _e( 'Back', 'wpeolog-i18n'); ?></a>
		</div>
	</div>

</form>