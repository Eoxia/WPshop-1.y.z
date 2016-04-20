<?php if( !empty($comment) ) : ?>
	<div class="wps-boxed"><?php echo $comment; ?></div>
<?php else : ?>
	<div class="wps-alert-info">
		<?php _e('No comment for this order', 'wpshop'); ?>
	</div>
<?php endif; ?>

