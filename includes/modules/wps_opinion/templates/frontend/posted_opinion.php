<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell"><?php echo mysql2date( get_option('date_format'), $posted_opinion->opinion_date, true ); ?></div>
		<div class="wps-table-cell"><a href="<?php echo get_permalink($posted_opinion->opinion_post_ID); ?>" target="_blank"><?php echo get_the_post_thumbnail( $posted_opinion->opinion_post_ID, 'thumbnail', array( 'class' => 'wps-circlerounded') ); ?></a></div>
		<div class="wps-table-cell">
			<a href="<?php echo get_permalink($posted_opinion->opinion_post_ID); ?>" target="_blank">
			<?php
			$product = get_post( $posted_opinion->opinion_post_ID );
			if( !empty($product) && !empty($product->post_type) ) :
				if( $product->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) :
					$product = get_post( $product->post_parent );
				endif;
			endif;
			echo $product->post_title;
			?>
			</a>
		</div>
		<div class="wps-table-cell"><?php echo $posted_opinion->opinion_content; ?></div>
		<div class="wps-table-cell"><?php echo wps_opinion_ctr::display_stars( $posted_opinion->opinion_rate ); ?></div>
</div>
