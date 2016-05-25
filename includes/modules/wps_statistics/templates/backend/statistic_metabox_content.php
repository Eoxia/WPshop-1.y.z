<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="postbox">
	<h3 class="hndle"><span><?php echo $box_title; ?></span></h3>
	<div class="inside" id="inside_<?php echo $canvas_id; ?>">
		<center><canvas id="<?php echo $canvas_id; ?>" width="<?php echo $canvas_width; ?>" height="<?php echo $canvas_height; ?>"></canvas></center>
		<?php echo $canvas_js; ?>
	</div>
</div>