<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div>
	<div class="wps-h6"><?php echo $videos_items[ $rand_element ]->title; ?></div>
	<div><center><iframe width="400" height="290" src="<?php echo $videos_items[ $rand_element ]->embed_link; ?>" frameborder="0" allowfullscreen></iframe></center></div>
	<div><?php echo $videos_items[ $rand_element ]->description; ?></div>
</div>