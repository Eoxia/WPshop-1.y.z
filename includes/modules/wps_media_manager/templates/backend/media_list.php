<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<ul id="product_media_list">
<?php if( $media_id ) : ?>
	<?php foreach( $media_id as $id ) : ?>
		<?php if( !empty($id) ) : ?>
			<li id="media_<?php echo $id; ?>" class="attachment">
				<div id="attachment-preview-border" class="attachment-preview">
					<div class="thumbnail">
						<?php if( wp_attachment_is_image( $id ) ) { ?>
							<div class="centered">
								<?php echo wp_get_attachment_image( $id ); ?>
							</div>
							<?php } else { ?>
							<div class="centered">
								<?php echo wp_get_attachment_image( $id, 'thumbnail', 1 ); ?>
							</div>
							<div class="filename">
								<div><?php echo basename(get_attached_file( $id )); ?></div>
							</div>
						<?php } ?>
					</div>
				</div>
				<a href="#"><span class="wps-icon-trash delete-picture"></span></a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
</ul>
