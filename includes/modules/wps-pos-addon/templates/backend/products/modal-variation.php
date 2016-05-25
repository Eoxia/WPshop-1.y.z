<?php if ( !defined( 'ABSPATH' ) ) exit;
 if ( !empty( $product_id ) ) : ?>
	<?php echo wpshop_products::wpshop_variation( $product_id ); ?>
	<div class="wpspos-product-variation-selector" >
		<button class="wps-bton-first-mini-rounded alignRight" id="wpspos-product-with-variation-add-to-cart" ><?php _e( 'Add product', 'wpshop'); ?></button>
	</div>
<?php else : ?>
	<?php _e( 'We are unable to detect the product you want to add to order', 'wps-pos-i18n' ); ?>
<?php endif; ?>