<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery( 'canvas' ).attr( 'width', parseInt( jQuery( '.meta-box-sortables' ).width() ) );
		jQuery( 'canvas' ).attr( 'height', ( parseInt( jQuery( '.meta-box-sortables' ).width() ) * 0.5 ) );
	});
</script>
<?php if( !empty($products) ) : ?>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery( 'canvas' ).attr( 'width', parseInt( jQuery( '.meta-box-sortables' ).width() ) );
		jQuery( 'canvas' ).attr( 'height', ( parseInt( jQuery( '.meta-box-sortables' ).width() ) * 0.5 ) );
	});
</script>
<canvas id="wps-statistics-best-sales" width="" height=""></canvas>
<script type="text/javascript">
jQuery( document ).ready( function() {
	var pieData = [
	<?php 
	$i = 0;
	if( !empty($products) ) : 
		foreach( $products as $product ) :
			if ( $i < 8 ) :
				echo '{value:' .$product. ', color:"' .$colors[$i]. '"},';
				$i++;
			endif;
		endforeach;
	endif;
	?>  
	];
	var myPie = new Chart(document.getElementById("wps-statistics-best-sales").getContext("2d")).Pie(pieData);         	
});
</script>
<!-- Display Legend -->
<ul class="wps_statistics_legend">
	<?php if( !empty($products) ) : 
			$i = 0;
			foreach( $products as $item_id => $product ) :
				if ( $i < 8 ) :
					$product_type = get_post_type( $item_id );
					if ( $product_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) :
						$product_name = get_the_title( $item_id );
					else : 
						$parent_def = wpshop_products::get_parent_variation( $item_id );
						if ( !empty($parent_def) && !empty($parent_def['parent_post']) ) :
							$parent_post = $parent_def['parent_post'];
							$product_name = $parent_post->post_title;
						endif;
					endif;
				?>	
					<li><span style="background :<?php echo $colors[$i]; ?>;" class="legend_indicator"></span><span><a href="<?php echo admin_url('post.php?post=' . $item_id. '&action=edit'); ?>"><?php echo ( !empty($product_name) ) ? $product_name : __( 'Deleted product', 'wpshop') ; ?></a> (<?php printf( __('%s items', 'wpshop'), $product); ?>)</span></li>
				<?php 
					$i++;
				endif;
			endforeach;
		endif;
	?>
</ul>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No order have been created', 'wpshop'); ?></div>
<?php endif; ?>