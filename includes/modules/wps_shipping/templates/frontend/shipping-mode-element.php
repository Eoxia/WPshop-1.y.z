<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty( $shipping_mode['active'] ) ) : ?>
	<?php
	$checked = $class = '';

	$selected_shipping_method = ( !empty($_SESSION['shipping_method']) ) ? $_SESSION['shipping_method'] : '';

	$count = count( $shipping_modes );
	$shipping_modes = get_option( 'wps_shipping_mode' );
	if( !empty($selected_shipping_method) && $shipping_mode_id == $selected_shipping_method ) {
		$class = 'wps-activ';
		$checked = 'checked="checked"';
	}
	else {
		if( empty($selected_shipping_method) && ( !empty($shipping_modes) && !empty($shipping_modes['default_choice']) && $shipping_mode_id == $shipping_modes['default_choice'] )  ) {
			$checked = 'checked="checked"';
			$class = 'wps-activ';
		}
		else if( $count == 0 && empty($selected_shipping_method) ) {
			$checked = 'checked="checked"';
			$class = 'wps-activ';
		}
		else {
			$checked = $class = '';
		}
	}

	$shipping_cost_are_free = false;
	$free_shipping_cost_alert = '';
	$currency = wpshop_tools::wpshop_get_currency();
	$cart_items = ( !empty($_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items'])  ) ? $_SESSION['cart']['order_items'] : '';
	$price_piloting = get_option( 'wpshop_shop_price_piloting' );
	if( !empty($cart_items) ) {
		$wps_shipping = new wps_shipping();
		$cart_weight = $wps_shipping->calcul_cart_weight( $cart_items );
		$total_cart_ht_or_ttc_regarding_config = ( !empty($price_piloting) && $price_piloting == 'HT' )  ? $_SESSION['cart']['order_total_ht'] : $_SESSION['cart']['order_total_ttc'];
		$total_shipping_cost_for_products = $wps_shipping->calcul_cart_items_shipping_cost( $cart_items );
		$shipping_cost = $wps_shipping->get_shipping_cost( count( $cart_items ), $total_cart_ht_or_ttc_regarding_config, $total_shipping_cost_for_products, $cart_weight, $shipping_mode_id ).' '.$currency;
	}

	if (  !empty($shipping_mode['free_from']) ) {
		$order_amount = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? number_format((float)$_SESSION['cart']['order_total_ht'], 2, '.', '') : number_format((float)$_SESSION['cart']['order_total_ttc'], 2, '.', '');
		if ( $order_amount  < $shipping_mode['free_from'] ) {
			$free_in = ($shipping_mode['free_from'] - $order_amount);
			$shipping_cost .= ' ' . sprintf(__('Free in %s', 'wpshop'), $free_in. ' ' . $currency);
		}
		else {
			$shipping_cost = '<span class="wps-badge-vert">'.__('Free shipping cost', 'wpshop').'</span>';
			$shipping_cost_are_free = true;
		}
	}

	?>



	<li class="<?php echo $class; ?> wps-bloc-loader">
			<span><input type="radio" name="wps-shipping-method" data-nonce="<?php echo wp_create_nonce( 'wps_calculate_shipping_cost' ); ?>" value="<?php echo $shipping_mode_id; ?>" id="<?php echo $shipping_mode_id ; ?>" <?php echo $checked; ?> /> <?php apply_filters( 'wps-extra-fields-'.$shipping_mode_id, '' ); ?></span>

			<?php echo ( !empty($shipping_mode['logo']) ? '<span class="wps-shipping-method-logo">'.wp_get_attachment_image( $shipping_mode['logo'], 'thumbnail' ).'</span>': '' ); ?>

			<span class="wps-shipping-method-name"><strong><?php _e( $shipping_mode['name'], 'wpshop' ); ?></strong></span>
			<span class="alignright">
				<?php if ( false === $shipping_cost_are_free ) : _e('Cost of postage ', 'wpshop'); endif; ?>
				<?php echo $shipping_cost; ?>
				<?php if ( false === $shipping_cost_are_free ) : echo $price_piloting; endif; ?>
			</span>

			<div class="wps-shipping-method-explanation"><?php if( !empty( $shipping_mode['explanation'] ) ) :
				_e( $shipping_mode['explanation'], 'wpshop' );
			endif; ?></div>
			<div id="container_<?php echo $shipping_mode_id ; ?>" >
			<?php
				$result_filter = apply_filters('wps_shipping_mode_additional_content', $shipping_mode_id);
				if($result_filter != $shipping_mode_id) {
					echo $result_filter;
				}
			?></div>

	</li>

<?php
	$i++;
	endif;
?>
