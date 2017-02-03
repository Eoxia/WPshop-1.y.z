<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-cart-cartouche">
	<div class="wps-cart-message">
		<?php if( $cart_type != 'admin-panel' ) : ?>
			<?php if ( !empty($cart_type) && $cart_type == 'summary' && !$account_origin ) : ?>
				<?php $url_step_one = get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ); ?>
				<?php printf( __( 'You have forget an item ? <a href="%s">Modify your cart !</a>', 'wpshop'), $url_step_one ); ?>
			<?php else : ?>
				<?php if( !$account_origin ) :
							echo do_shortcode('[wps_apply_coupon]');
						else : ?>
					<button id="<?php echo $oid; ?>" class="wps-bton-first-mini-rounded make_order_again" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_restart_the_order' ); ?>"><?php _e( 'Make this order again', 'wpshop'); ?></button>
				<?php endif; ?>
				<?php if( !empty($tracking) ) : ?>
					<p><br />
					<?php if( !empty($tracking['number']) ) : ?>
						<strong><?php _e('Tracking number','wpshop'); ?> :</strong> <?php _e($tracking['number']); ?><br />
					<?php endif; ?>
					<?php if( !empty($tracking['link']) ) : ?>
						<?php /** Check if http:// it's found in the link */
						$url = $tracking['link'];
						if('http://' != substr($url, 0, 7))
							$url = 'http://' . $url;
						?>
						<a class="wps-bton-fourth-mini-rounded" href="<?php echo $url; ?>" target="_blank"><?php _e('Tracking link','wpshop'); ?></a>
					<?php endif; ?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</div><!-- wps-cart-message -->
	<div class="wps-cart-total">
		<?php $shipping_price_from = get_option( 'wpshop_shipping_cost_from' ); ?>

		<!--	Recap shipping	-->

		<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_et' || $price_piloting == 'HT' ) : ?>
			<p>
				<?php _e( 'Shipping cost ET', 'wpshop'); ?> <?php echo ( ( !empty($shipping_price_from) && empty( $_SESSION['shipping_address'] ) ) ? '<br/><i>('.__( 'From', 'wpshop').')</i>' : '' ); ?>
				<span class="wps-alignRight">
					<?php if( $cart_type != 'admin-panel' ) : ?>
						<strong><?php echo wpshop_tools::formate_number( $shipping_cost_et ); ?></strong> <?php echo $currency; ?>
					<?php else : ?>
						<?php if( ( empty( $cart_content['order_status'] ) || ( $cart_content['order_status'] == 'awaiting_payment' ) ) && $price_piloting == 'HT' ) : ?>
							<input type="text" size="5" value="<?php echo number_format( $shipping_cost_et, 2 ); ?>" id="wps-orders-shipping-cost" class="wps-error" style="text-align : right" />
						<?php else : ?>
							<strong><?php echo wpshop_tools::formate_number( $shipping_cost_et ); ?> <?php echo wpshop_tools::wpshop_get_currency(); ?></strong>
						<?php endif; ?>
					<?php endif; ?>
				</span>
			</p>
		<?php endif; ?>

		<?php if( $cart_option == 'full_cart' ) : ?>
			<p>
				<?php _e( 'VAT on Shipping cost', 'wpshop'); ?>
				<span class="wps-alignRight">
					<strong><?php echo wpshop_tools::formate_number( $shipping_cost_vat ); ?></strong> <?php echo $currency; ?>
				</span>
			</p>
		<?php endif; ?>

		<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' || $price_piloting == 'TTC' ) : ?>
			<p>
				<?php _e( 'Shipping cost', 'wpshop'); ?> <?php echo ( ( !empty($shipping_price_from) && empty( $_SESSION['shipping_address'] ) ) ? '<br/><i>('.__( 'From', 'wpshop').')</i>' : '' ); ?>
				<span class="wps-alignRight">
					<?php if( $cart_type != 'admin-panel' ) : ?>
						<strong><?php echo wpshop_tools::formate_number( $shipping_cost_ati ); ?></strong> <?php echo $currency; ?>
					<?php else : ?>
						<?php if( ( empty( $cart_content['order_status'] ) || ( $cart_content['order_status'] == 'awaiting_payment' ) ) && $price_piloting == 'TTC' ) : ?>
							<input type="text" size="5" value="<?php echo number_format( $shipping_cost_ati, 2 ); ?>" id="wps-orders-shipping-cost" class="wps-error" style="text-align : right" />
						<?php else : ?>
							<strong><?php echo wpshop_tools::formate_number( $shipping_cost_ati ); ?> <?php echo wpshop_tools::wpshop_get_currency(); ?></strong>
						<?php endif; ?>
					<?php endif; ?>
				</span>
			</p>
		<?php endif; ?>

		<?php if( $cart_option == 'full_cart' && !empty($cart_content['order_tva']) ) : ?>
		<?php foreach( $cart_content['order_tva'] as $order_vat_rate => $order_vat_value ) :
				if( $order_vat_rate != 'VAT_shipping_cost') :
					?>
					<p>
						<?php printf( __( 'VAT (%s %%)', 'wpshop'), $order_vat_rate); ?>
						<span class="wps-alignRight">
							<strong><?php echo wpshop_tools::formate_number( $order_vat_value ); ?></strong> <?php echo $currency; ?>
						</span>
					</p>
					<?php
				endif;
		endforeach; ?>
		<?php endif; ?>
			<?php if( !empty($cart_content['order_discount_value']) || ( $cart_type == 'admin-panel' ) ) : ?>
				<p>
					<?php _e( 'Discount on order', 'wpshop'); ?>
					<span class="wps-alignRight">
						<?php if ( ( $cart_type == 'admin-panel' ) && ( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) ) : ?>
							<input type="text" id="wps-orders-discount-value" size="5" style="text-align : right" value="<?php echo ( !empty($cart_content['order_discount_value']) ) ? $cart_content['order_discount_value'] : number_format( 0, 2 ); ?>"/>
						<?php else : ?>
							<?php if( !empty($cart_content['order_discount_value']) ) : ?>
								<?php echo '<strong>' . $cart_content['order_discount_value']; ?>
									<?php if ( !empty($cart_content['order_discount_type']) && $cart_content['order_discount_type'] == 'percent' ) {
										echo '%</strong></span></p>';
										echo '<p>' . __( 'Amount reduction', 'wpshop' ) . '<span class="wps-alignRight">' . $cart_content['order_discount_amount_total_cart'] . ' ' . wpshop_tools::wpshop_get_currency();
									} else {
										echo wpshop_tools::wpshop_get_currency() . '</strong>';
									} ?>
							<?php else : ?>
								0 <?php echo wpshop_tools::wpshop_get_currency(); ?>
							<?php endif; ?>
						<?php endif; ?>
					</span>
				</p>
				<?php if( ( $cart_type == 'admin-panel' ) && ( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) ) : ?>
					<p>
						<?php _e( 'Type of discount on order', 'wpshop'); ?>
						<span class="wps-alignRight">
							<select id="wps-orders-discount-type">
								<option value="percent" <?php echo ( !empty($cart_content) && !empty($cart_content['order_discount_type']) && $cart_content['order_discount_type'] == 'percent' ) ? 'selected="selected"' : ''; ?>>%</option>
								<option value="amount" <?php echo ( !empty($cart_content) && !empty($cart_content['order_discount_type']) && $cart_content['order_discount_type'] == 'amount' ) ? 'selected="selected"' : ''; ?>><?php echo wpshop_tools::wpshop_get_currency(); ?></option>
							</select>
						</span>
					</p>
					<?php if( !empty($cart_content) && !empty($cart_content['order_discount_type']) && $cart_content['order_discount_type'] == 'percent' ) : ?>
						<p>
							<?php _e( 'Amount reduction', 'wpshop' ); ?>
							<span class="wps-alignRight">
								<?php echo $cart_content['order_discount_amount_total_cart'] . ' ' . wpshop_tools::wpshop_get_currency(); ?>
							</span>
						</p>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( !empty( $cart_content['coupon_id']) ) : ?>
			<p><?php _e( 'Total ATI before discount', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $order_totla_before_discount ); ?></strong> <?php echo $currency; ?></span></p>
			<p><?php _e( 'Discount', 'wpshop'); ?> (<?php echo $coupon_title; ?>) <span class="wps-alignRight"><strong><?php echo $coupon_value; ?></strong><?php echo $currency; ?></span></p>
			<?php endif; ?>

			<?php if( !empty($_SESSION['cart']['order_partial_payment']) ) :
				$wps_partial_payment_data = get_option( 'wpshop_payment_partial' );
				if( !empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type'] == 'quotation' ) {
					$partial_payment_informations = $wps_partial_payment_data['for_quotation'];
				} else {
					$partial_payment_informations = $wps_partial_payment_data['for_all'];
				}
				$partial_payment_amount =  $_SESSION['cart']['order_partial_payment'];
			?>
				<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $cart_content['order_grand_total'] ); ?></strong> <?php echo $currency; ?></span></p>
				<p class="wps-hightlight">
				<?php printf(__('Payable now %s','wpshop'), '(' . $partial_payment_informations['value'] . ( ( !empty($partial_payment_informations['type']) && $partial_payment_informations['type'] == 'percentage' ) ? '%': wpshop_tools::wpshop_get_currency( false ) ) . ')'); ?>
				<span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $partial_payment_amount ); ?></strong> <?php echo $currency; ?>
				</span></p>
			<?php elseif ( !empty( $cart_content ) && !empty( $cart_content[ 'order_status'] ) && ( 'partially_paid' == $cart_content[ 'order_status' ] ) && !empty( $cart_content[ 'order_payment' ] ) && !empty( $cart_content[ 'order_payment' ][ 'received' ] ) ) : ?>
				<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $cart_content['order_grand_total'] ); ?></strong> <?php echo $currency; ?></span></p>
				<?php $allready_received_amount = 0; ?>
				<?php foreach ( $cart_content[ 'order_payment' ][ 'received' ] as $payment ) : ?>
					<?php if ( ! empty( $payment[ 'status' ] ) && 'payment_received' == $payment[ 'status' ] ) : ?>
						<?php $allready_received_amount += $payment[ 'received_amount' ]; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<p><?php _e( 'Already paid', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $allready_received_amount ); ?></strong> <?php echo $currency; ?></span></p>
				<p class="wps-hightlight"><?php _e( 'Due amount for this order', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $cart_content['order_grand_total'] - $allready_received_amount ); ?></strong> <?php echo $currency; ?></span></p>
			<?php else : ?>
				<?php if ( isset( $_SESSION[ 'cart' ][ 'order_product_partial_payment' ]) ) : ?>
				<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $_SESSION['cart']['order_amount_to_pay_now'] ); ?></strong> <?php echo $currency; ?></span></p>
				<?php else: ?>
				<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $total_ati ); ?></strong> <?php echo $currency; ?></span></p>
				<?php endif; ?>
			<?php endif; ?>
	</div><!-- wps-cart-total -->
</div><!-- wps-cart-cartouche -->

<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' && $cart_type != 'admin-panel' ) ) : ?>
<div class="wps-checkout-actions">
	<button data-nonce="<?php echo wp_create_nonce( 'wps_empty_cart' ); ?>" class="wps-bton-second emptyCart"><?php _e( 'Empty the cart', 'wpshop' ); ?></button>
	<?php if( !empty( $_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type'] == 'quotation' ) : ?>
		<button class="wps-bton-first" data-nonce="<?php echo wp_create_nonce( 'wps_cart_pass_to_step_two' ); ?>" id="wps-cart-order-action"><?php _e( 'Validate my quotation', 'wpshop' ); ?></button>
	<?php else : ?>
		<button class="wps-bton-first" data-nonce="<?php echo wp_create_nonce( 'wps_cart_pass_to_step_two' ); ?>" id="wps-cart-order-action"><?php _e( 'Order', 'wpshop' ); ?></button>
	<?php endif; ?>
</div>
<div class="wps-cart-notices">
	<span class="wps-mini-cart-free-shipping-alert">
		<?php echo wpshop_tools::create_custom_hook('wpshop_free_shipping_cost_alert'); ?>
	</span>
</div>
<?php endif; ?>

<?php if( !empty($cart_type) && $cart_type == 'admin-panel' && ( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) ) : ?>
	<button class="wps-bton-second-rounded alignRight" data-nonce="<?php echo wp_create_nonce( 'wps_orders_update_cart_informations' ); ?>" id="wps-orders-update-cart-informations"><i class="dashicons dashicons-update"></i><?php _e( 'Update order informations', 'wpshop'); ?></button>
<?php endif; ?>
<?php $wps_payment_mode = get_option('wps_payment_mode'); ?>
<?php if( isset( $wps_payment_mode['mode']['paypal']['active'] ) ): ?>
	<div class="wps-secured-logos">
		<span class="wps-logo-paypal"></span>
		<span class="wps-logo-visa"></span>
		<span class="wps-logo-mastercard"></span>
	</div>
<?php endif; ?>
