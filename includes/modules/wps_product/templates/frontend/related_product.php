<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div id="product_related">
	<?php if ( empty( $atts ) || empty( $atts[ 'with_title' ] ) || ( 'yes' == $atts[ 'with_title' ] )  ) : ?><h3><?php _e( 'Related products', 'wpshop'); ?></h3><?php endif; ?>
	<?php echo $related_product_output; ?>
</div>