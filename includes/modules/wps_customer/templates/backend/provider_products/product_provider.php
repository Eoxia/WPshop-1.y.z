<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="product" data-id="<?php echo $post->ID; ?>">
	<h1 data-id="<?php echo $post->ID; ?>"><a href="#"><?php echo $post->post_title; ?></a></h1><a data-id="<?php echo $post->ID; ?>" class="delete_provider_btn" href="#">delete</a><input type="hidden" data-id="<?php echo $post->ID; ?>" class="special_provider" name="wps_provider_product[<?php echo $post->ID; ?>][special_provider]" value="update">
	<div class="values" data-id="<?php echo $post->ID; ?>">
		<div class="wps-form-group">
			<label><?php _e( 'Product title', 'wpshop'); ?></label>
			<div class="wps-form"><input type="text" name="wps_provider_product[<?php echo $post->ID; ?>][post_title]"  value="<?php echo $post->post_title; ?>" /></div>
		</div>
		<div class="wps-form-group">
			<label><?php _e( 'Product description', 'wpshop'); ?> :</label>
			<div class="wps-form"><textarea id="wps_product_description_<?php echo $post->ID; ?>" name="wps_provider_product[<?php echo $post->ID; ?>][post_content]"><?php echo nl2br( $post->post_content );?></textarea></div>
		</div>
		<?php echo $attributes_display; ?>
	</div>
</div>