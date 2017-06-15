<?php if ( ! defined( 'ABSPATH' ) ) { exit;
}
 $order_post_meta = ! empty( $post ) ? get_post_meta( $post->ID, '_wpshop_order_status', true ) : ''; ?>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Picture', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product reference', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product name', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php _e( 'Price', 'wpshop' ); ?></div>

		<?php if ( 'completed' != $order_post_meta ) : ?>
			<div class="wps-table-cell"><?php _e( 'Quantity', 'wpshop' ); ?></div>
			<div class="wps-table-cell"><?php _e( 'Add to order', 'wpshop' ); ?></div>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $products ) ) :
		$total_products = count( $products );
		$i = 0;
		$elements_per_page = 20;
		$paged = absint( isset( $_REQUEST['paged_order'] ) ? $_REQUEST['paged_order'] : 1 );
		$paginate_links = paginate_links( array(
			'base' => '%_%',
			'format' => '?paged_order=%#%',
			'current' => $paged,
			'total' => ceil( $total_products / $elements_per_page ),
			'type' => 'list',
			'prev_next' => true,
		) );
		foreach ( $products as $product ) :
			if ( ( $elements_per_page * ( $paged - 1 ) ) > $i ) {
				$i++;
				continue;
			} elseif ( ( $elements_per_page * $paged ) <= $i ) {
				break;
			}
			$i++;
			$pid = $product->ID;
	?>
	<?php $product_metadata = get_post_meta( $product->ID, '_wpshop_product_metadata', true ); ?>
	<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell  wps-cart-item-img"><?php echo get_the_post_thumbnail( $product->ID, 'thumbnail' ); ?></div>
		<div class="wps-table-cell"><?php echo ( ! empty( $product_metadata ) && $product_metadata['product_reference']) ? $product_metadata['product_reference'] : ''; ?></div>
		<div class="wps-table-cell"><?php echo $product->post_title; ?></div>
		<div class="wps-table-cell">
		<?php
		  $product = wpshop_products::get_product_data( $product->ID );
		  echo wpshop_prices::get_product_price( $product, 'price_display', array( 'mini_output', 'grid' ) );
		?>
		</div>
		<?php if ( 'completed' != $order_post_meta ) : ?>
			<div class="wps-table-cell">
				<a class="wps-bton-icon-minus-small wps-cart-reduce-product-qty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" href=""></a>
				<input id="wps-cart-product-qty-<?php echo $pid; ?>" class="wps-cart-product-qty" type="text" value="1" name="french-hens" size="3" style="text-align : center">
				<a class="wps-bton-icon-plus-small wps-cart-add-product-qty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" href=""></a>
			</div>
			<div class="wps-table-cell">
				<a href="#" data-nonce="<?php echo wp_create_nonce( 'wps_add_product_to_order_admin' ); ?>" class="wps-bton-first-mini-rounded wps-order-add-product" id="wps-order-add-product-<?php echo $pid; ?>"><i class="wps-icon-basket"></i> <?php _e( 'Add to order', 'wpshop' ); ?></a>
			</div>
		<?php endif; ?>
	 </div>
		<?php endforeach;
  else :
		if ( ! empty( $research ) ) : ?>
		   <div class="wps-alert-info"><?php printf( __( 'No products corresponds to the search <strong>"%s"</strong>', 'wpshop' ), $research ); ?></div>
		<?php else :
		   $letter_display = ( strtoupper( $current_letter ) != 'ALL' ) ? $current_letter : __( 'ALL', 'wpshop' ); ?>
			<div class="wps-alert-info"><?php printf( __( 'No products corresponds to the letter <strong>"%s"</strong> search', 'wpshop' ), strtoupper( $letter_display ) ); ?></div>
	<?php endif;
	   endif; ?>
</div>
<?php
if ( ! empty( $products ) && $total_products > $elements_per_page ) :
	echo $paginate_links;
	?>
	<input type="hidden" name="last_query[oid]" value="<?php echo $post->ID; ?>">
	<input type="hidden" name="last_query[letter]" value="<?php echo strtoupper( $current_letter ); ?>">
	<input type="hidden" name="last_query[research]" value="<?php echo isset( $research ) ? $research : ''; ?>">
	<input type="hidden" name="last_query[_wpnonce]" value="<?php echo wp_create_nonce( 'refresh_product_list_' . strtolower( $current_letter ) ); ?>">
	<?php
endif;
?>
