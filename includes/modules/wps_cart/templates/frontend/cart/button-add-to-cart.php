<?php if ( !defined( 'ABSPATH' ) ) exit; ?>

<div class="wps-product-section">
	<label><?php _e('Quantity', 'wpshop'); ?></label>
	<div class="wps-productQtyForm">
		<?php if ( $use_button ): ?>
			<a class="wps-bton-icon-minus-small wps-cart-reduce-product-qty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" href=""></a>
		<?php endif; ?>
		<span class="wps-form"><input id="wps-cart-product-qty-<?php echo $args['pid']; ?>" class="wpshop_product_qty_input" type="text" value="1" /></span>
		<?php if ( $use_button): ?>
			<a class="wps-bton-icon-plus-small wps-cart-add-product-qty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" href=""></a>
		<?php endif; ?>
	</div>
</div>

<button itemprop="availability" content="in_stock" id="wpshop_add_to_cart_<?php echo $args['pid']; ?>" data-nonce="<?php echo wp_create_nonce( 'ajax_pos_product_variation_selection' ); ?>" class="wpshop_add_to_cart_button wps-bton-first-mini-rounded"><i class="wps-icon-basket"></i><?php echo $button_text; ?></button>
