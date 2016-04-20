<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell"><?php echo mysql2date( get_option('date_format'), $posted_opinion->opinion_date, true ); ?></div>
		<div class="wps-table-cell">
			<?php 
			$product = get_post( $posted_opinion->opinion_post_ID );
			if( !empty($product) && !empty($product->post_type) ) : 
				if( $product->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) : 
					$product = get_post( $product->post_parent );
				endif;
			endif;
			echo $product->post_title;
			?>
		</div>
		<div class="wps-table-cell"><?php echo $posted_opinion->opinion_content; ?></div>
		<div class="wps-table-cell"><?php echo wps_opinion_ctr::display_stars( $posted_opinion->opinion_rate ); ?></div>
</div>