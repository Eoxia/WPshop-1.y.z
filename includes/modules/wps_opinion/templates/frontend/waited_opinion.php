<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell wps-cart-item-img"><a href="<?php echo get_permalink($ordered_product); ?>"><?php echo get_the_post_thumbnail( $ordered_product, 'thumbnail', array( 'class' => 'wps-circlerounded') ); ?></a></div>
		<div class="wps-table-cell"><a href="<?php echo get_permalink($ordered_product); ?>" target="_blank"><?php echo get_the_title( $ordered_product ); ?></a></div>
		<div class="wps-table-cell"><button class="wps-bton-first-mini-rounded wps-add-opinion-opener" data-nonce="<?php echo wp_create_nonce( 'wps_fill_opinion_modal' ); ?>" id="wps-add-opinion-<?php echo $ordered_product; ?>"><?php _e( 'Add your opinion', 'wps_opinion'); ?></button></div>
</div>
