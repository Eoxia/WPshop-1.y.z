<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<li class="wps-clearfix" id="wps_product_<?php echo $product_key; ?>">
	<div class="wps-cart-item-img">
		<?php if ( !$auto_added_product ) : ?><a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>"><?php endif; ?>
			<?php echo get_the_post_thumbnail($item['item_id'], 'thumbnail' ); ?>
		<?php if ( !$auto_added_product ) : ?></a><?php endif; ?>
	</div>
	<div class="wps-cart-item-content">
		<?php if ( !$auto_added_product && get_post_status( $item_id ) != 'free_product' ) : ?><a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>"><?php endif; ?>
			<?php echo $item_title; ?>
		<?php if ( !$auto_added_product && get_post_status( $item_id ) != 'free_product' ) : ?></a><?php endif; ?>

		<?php echo $variations_indicator; ?>

		<?php if ( !empty( $cart_content ) && !empty( $cart_content[ 'order_status' ] ) && ( 'completed' == $cart_content[ 'order_status' ] ) && ( empty($cart_type) || ( !empty($cart_type) && $cart_type == 'summary' ) ) ) : ?>
			<?php echo $download_link; ?>
		<?php endif; ?>

		<?php echo apply_filters( 'wps_order_item_content_column', '', $item, $oid, $cart_content ); ?>
	</div>

	<?php if( $cart_option == 'simplified_et' ) : ?>
	<div class="wps-cart-item-unit-price">
		<span class="wps-price">
			<?php
				$price_ati_to_display = $item['item_pu_ttc'];
				if ( !empty( $item[ 'item_amount_to_pay_now' ] ) ) {
					$price_ati_to_display = $item[ 'item_amount_to_pay_now' ];
				}
			?>
		 <?php echo wpshop_tools::formate_number( $price_ati_to_display ); ?><span><?php echo $currency; ?></span>
		</span>
	</div>
	<?php endif; ?>

	<?php if( $cart_option == 'full_cart' ) : ?>
	<div class="wps-cart-item-unit-price wps-cart-item-unit-price-et">
		<span class="wps-price"> <?php echo wpshop_tools::formate_number( $item['item_pu_ht'] ); ?><span><?php echo $currency; ?></span></span>
	</div>
	<?php endif; ?>

	<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
	<div class="wps-cart-item-unit-price<?php echo ( $cart_option == 'full_cart' ) ? " wps-cart-item-unit-price-ati" : ""; ?>">
		<span class="wps-price"> <?php echo wpshop_tools::formate_number( $item['item_pu_ttc'] ); ?><span><?php echo $currency; ?></span></span>
	</div>
	<?php endif; ?>

	<div class="wps-cart-item-quantity wps-productQtyForm">
		<?php if ( ( empty($cart_type) || ( !empty($cart_type) ) ) && !$auto_added_product  ) : ?>
			<?php if(  $cart_type != 'summary'  && ( $cart_type != 'admin-panel' || ( $cart_type == 'admin-panel' && ( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) ) ) ) : ?>
				<a href="" class="wps-cart-reduce-product-qty"><i class="wps-icon-minus"></i></a>
				<input type="text" name="french-hens" id="wps-cart-product-qty-<?php echo $product_key; ?>" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" value="<?php echo $item['item_qty']; ?>" class="wps-circlerounded wps-cart-product-qty">
				<a href="" class="wps-cart-add-product-qty"><i class="wps-icon-plus"></i></a>
				<?php else : ?>
					<?php echo $item['item_qty']; ?>
				<?php endif;?>
		<?php elseif ( $auto_added_product ) : ?>
			1
		<?php else : ?>
			<?php echo $item['item_qty']; ?>
		<?php endif; ?>
	</div>

	<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_et' ) : ?>
	<div class="wps-cart-item-price">
    	<span class="wps-price"> <?php echo wpshop_tools::formate_number( $item['item_total_ht'] ); ?><span><?php echo $currency; ?></span></span>
    	<span class="wps-tva"><?php _e( 'ET', 'wpshop'); ?></span>
	</div>
	<?php endif; ?>

	<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
	<div class="wps-cart-item-price">
			<?php
				$price_total_to_display = $item['item_total_ttc'];
				if ( !empty( $item[ 'item_amount_to_pay_now' ] ) ) {
					$price_total_to_display = $item[ 'item_amount_to_pay_now' ] * $item['item_qty'];
				}
			?>
    	<span class="wps-price"> <?php echo wpshop_tools::formate_number( $price_total_to_display ); ?><span><?php echo $currency; ?></span></span>
	</div>
	<?php endif; ?>


	<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) : ?>
	<div class="wps-cart-item-close">
		<?php if( $cart_type != 'admin-panel' || ( $cart_type == 'admin-panel' && ( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) ) ) : ?>
		<?php if ( !$auto_added_product ) : ?><button type="button" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" class="wps-bton-icon wps_cart_delete_product" id="wps-close-<?php echo $product_key; ?>"><i class="wps-icon-close"></i></button><?php endif; ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>

</li>
