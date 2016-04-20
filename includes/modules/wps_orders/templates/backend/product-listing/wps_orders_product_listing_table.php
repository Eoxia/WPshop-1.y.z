<?php $order_post_meta = !empty($post) ? get_post_meta( $post->ID, '_wpshop_order_status', true ) : ""; ?>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Picture', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product reference', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product name', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Price', 'wpshop'); ?></div>

		<?php if ( 'completed' != $order_post_meta ) : ?>
			<div class="wps-table-cell"><?php _e( 'Quantity', 'wpshop'); ?></div>
			<div class="wps-table-cell"><?php _e( 'Add to order', 'wpshop'); ?></div>
		<?php endif; ?>
	</div>
	<?php if( !empty($products) ) : ?>
	<?php foreach ( $products as $product ) :
	$pid = $product->ID;
	?>
	<?php $product_metadata = get_post_meta( $product->ID, '_wpshop_product_metadata', true ); ?>
	<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell  wps-cart-item-img"><?php echo get_the_post_thumbnail( $product->ID, 'thumbnail' ); ?></div>
		<div class="wps-table-cell"><?php echo ( !empty( $product_metadata) && $product_metadata['product_reference']) ? $product_metadata['product_reference'] : ''; ?></div>
		<div class="wps-table-cell"><?php echo $product->post_title; ?></div>
		<div class="wps-table-cell">
		<?php
			$product = wpshop_products::get_product_data($product->ID);
			echo wpshop_prices::get_product_price($product, 'price_display', array('mini_output', 'grid') );
		?>
		</div>
		<?php if ( 'completed' != $order_post_meta ) : ?>
			<div class="wps-table-cell">
				<a class="wps-bton-icon-minus-small wps-cart-reduce-product-qty" href=""></a>
				<input id="wps-cart-product-qty-<?php echo $pid; ?>" class="wps-cart-product-qty" type="text" value="1" name="french-hens" size="3" style="text-align : center">
				<a class="wps-bton-icon-plus-small wps-cart-add-product-qty" href=""></a>
			</div>
			<div class="wps-table-cell">
				<a href="#" class="wps-bton-first-mini-rounded wps-order-add-product" id="wps-order-add-product-<?php echo $pid; ?>"><i class="wps-icon-basket"></i> <?php _e( 'Add to order', 'wpshop'); ?></a>
			</div>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
	<?php else :
	$letter_display = ( strtoupper( $current_letter ) != 'ALL' ) ? $current_letter : __('ALL', 'wpshop' ); ?>
	<div class="wps-alert-info"><?php printf( __( 'No products corresponds to the letter <strong>"%s"</strong> search', 'wpshop'), strtoupper( $letter_display ) ); ?></div>
	<?php endif; ?>
</div>
