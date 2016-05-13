<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div>
	<a href="<?php echo ( ( !empty($data->link) ) ? $data->link : '#' ); ?>" title="<?php echo ( ($data->title) ? $data->title : '' ); ?>">
		<?php if( !empty($data->id) ) : ?>
		<?php echo get_the_post_thumbnail( $data->id, 'medium' ); ?>
		<?php echo $data->post_content; ?>
		<?php endif; ?>
	</a>
</div>