<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wpshop_product_variation wpshop_product_variation_metabox" id="wpshop_product_variation_metabox_<?php echo $variation_id; ?>" >
	<h3 class="hndle" >
		<span class="wpshop_variation_header_info" ><?php echo $variation_id; ?></span>
		<?php echo $variation_list; ?>
		<input type="button" class="button-secondary alignright product_variation_button product_variation_button_delete" id="wpshop_variation_delete_<?php echo $variation_id; ?>" value="<?php _e('Delete variation', 'wpshop'); ?>" />
		<div class="wpshop_cls" ></div>
	</h3>
	<div class="wpshop_variation_def" ></div>
</div>