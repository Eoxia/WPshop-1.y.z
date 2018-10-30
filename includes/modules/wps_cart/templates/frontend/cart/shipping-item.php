<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<li class="wps-clearfix">
	<div class="wps-cart-item-img">
	</div>

	<div class="wps-cart-item-content">
		<?php esc_html_e( 'Shipping cost ET', 'wpshop' ); ?>
	</div>

	<div class="wps-cart-item-unit-price wps-cart-item-unit-price-et">
		<span class="wps-price"><?php echo wpshop_tools::formate_number( $shipping_cost_et ); ?>€</span>
		<span class="wps-tva"><?php _e( 'ET', 'wpshop'); ?></span>
	</div>


	<div class="wps-cart-item-quantity wps-productQtyForm">1</div>

	<div class="wps-cart-item-price">
		<span class="wps-price"><?php echo wpshop_tools::formate_number( $shipping_cost_et ); ?>€</span>
		<span class="wps-tva"><?php _e( 'ET', 'wpshop'); ?></span>
	</div>

	<div class="wps-cart-item-close"></div>
</li>
