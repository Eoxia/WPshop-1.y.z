<?php if( !empty($products) ) : ?>
<canvas id="wps-statistics-most-viewed-statistics" width="" height=""></canvas>
<script type="text/javascript">
jQuery( document ).ready( function() {
	var pieData = [
	<?php 
	$i = 0;
	if( !empty($products) ) : 
		foreach( $products as $product ) :
			if ( $i < 8 ) :
				echo '{value:' .$product->meta_value. ', color:"' .$colors[$i]. '"},';
				$i++;
			endif;
		endforeach;
	endif;
	?>  
	];
	var myPie = new Chart(document.getElementById("wps-statistics-most-viewed-statistics").getContext("2d")).Pie(pieData);         	
});
</script>
<!-- Display Legend -->
<ul class="wps_statistics_legend">
	<?php if( !empty($products) ) : 
			$i = 0;
			foreach( $products as $item_id => $product ) :
				if ( $i < 8 ) :
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
					<li><span style="background :<?php echo $colors[$i]; ?>;" class="legend_indicator"></span><span><a href="<?php echo admin_url('post.php?post=' .$product->post_id. '&action=edit'); ?>"><?php echo $product_name; ?></a> (<?php printf( __('%s views', 'wpshop'), $product->meta_value); ?>)</span></li>
				<?php 
					$i++;
				endif;
			endforeach;
		endif;
	?>
</ul>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No product have been seen', 'wpshop'); ?></div>
<?php endif; ?>
