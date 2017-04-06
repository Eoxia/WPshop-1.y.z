<?php if ( !defined( 'ABSPATH' ) ) exit;
	$products = $this->wps_stats_mdl->wps_most_viewed_products_datas( $main_stats_count );
	if ( !empty( $products ) ) :
?>
<div class="wps-table">
	<?php
		foreach( $products as $item_id => $product ) :
			$product_type = get_post_type( $product->post_id );
			if ( $product_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) :
				$product_name = get_the_title( $product->post_id );
			else :
				$parent_def = wpshop_products::get_parent_variation( $product->post_id );
				if ( !empty($parent_def) && !empty($parent_def['parent_post']) ) :
					$parent_post = $parent_def['parent_post'];
					$product_name = $parent_post->post_title;
				endif;
			endif;
	?>
	<div class="wps-table wps-table-row">
		<div class="wps-table-cell textleft" ><a href="<?php echo admin_url('post.php?post=' .$product->post_id. '&action=edit'); ?>"><?php echo $product_name; ?></a></div>
		<div class="wps-table-cell" ><?php printf( __('%s views', 'wpshop'), $product->meta_value); ?></div>
	</div>
	<?php endforeach; ?>
</div>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No product have been seen', 'wpshop'); ?></div>
<?php endif; ?>
