<?php
/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Cart
 *
 * The WPShop cart class handles the cart process, collecting products data and calcul total
 *
 * @class 		wpwhop_cart
 * @package		WPShop
 * @category	Class
 * @author		Eoxia
 */

/** Display the cart */
function wpshop_display_cart() {
	global $wpshop_cart;
	echo $wpshop_cart->display_cart();
}
/** Display the mini cart */
function wpshop_display_mini_cart() {
	global $wpshop_cart;
	$wpshop_cart->display_mini_cart();
}

class wpshop_cart {

	/** Constructor of the class */
	function __construct() {
		if(empty($_SESSION['cart'])) {
			$_SESSION['cart'] = $this->load_cart_from_db();
			$_SESSION['coupon'] = 0;
		}
	}

	/** Reload the cart from the database and return it
	 * @return array
	*/
	function load_cart_from_db() {
		$cart = array();
		if(get_current_user_id())
			$cart = self::get_persistent_cart();

		return $cart;
	}

	/**
	 * Store the cart in the user session
	 */
	function store_cart_in_session($cart) {
		$_SESSION['cart'] = $cart;
	}

	/**
	 * Save the persistent cart when updated
	 */
	function get_persistent_cart() {
		if(get_current_user_id())
			$cart = get_user_meta(get_current_user_id(), '_wpshop_persistent_cart', true);
		return empty($cart) ? array() : $cart;
	}

	/**
	 * Save the persistent cart when updated
	 */
	function persistent_cart_update() {
		if(get_current_user_id())
			update_user_meta( get_current_user_id(), '_wpshop_persistent_cart', array(
				'cart' => $_SESSION['cart'],
			));
	}

	/**
	 * Apply a vouncher on a cart
	 */
	function get_coupon_data() {
		global $wpdb;

		if(!empty($_SESSION['cart']['coupon_id'])) {
			$query = $wpdb->prepare('SELECT meta_key, meta_value FROM ' . $wpdb->postmeta . ' WHERE post_id = %d', $_SESSION['cart']['coupon_id']);
			$coupons = $wpdb->get_results($query, ARRAY_A);
			$coupon = array();
			$coupon['coupon_id'] = $_SESSION['cart']['coupon_id'];
			foreach($coupons as $coupon_info){
				$coupon[$coupon_info['meta_key']] = $coupon_info['meta_value'];
			}
			return $coupon;
		}
		return array();
	}

	/**
	*	Store a product list and informations about thus list pricing into an array in order to save a new order
	*
	*	@param array|object $product_list The product's list to make operation on
	*
	*	@return array $cart_infos An array containing the different product infos and the different pricing information about the cart/order
	*/
	function calcul_cart_information($product_list, $custom_order_information = '') {
		$cart_infos = array();

		/*	Amount vars	*/
		$order_total_ht = 0;
		$order_total_ttc = 0;
		$order_tva = array();

		/* Shipping vars */
		$total_weight = 0;
		$nb_of_items = 0;
		$order_shipping_cost_by_article = 0;

		/* Discount vars */
		$order_discount_rate = 0;
		$order_discount_amount = 0;
		$order_items_discount_amount = 0;
		$order_total_discount_amount = 0;

		if ( !empty($product_list) ) {
			foreach ( $product_list as $product_id => $d ) {
				if ( is_array($d) ) {
					$product_id = $d['product_id'];
					$product_qty = $d['product_qty'];
					$product_variation = !empty($d['product_variation']) ? $d['product_variation'] : null;
				}
				else {
					$product_id = $d->product_id;
					$product_qty = $d->product_qty;
					$product_variation = !empty($d->product_variation) ? $d->product_variation : null;
				}
				$head_product_id = $product_id;

				if ( !empty($product_variation) && ( count($product_variation) ==1 ) && ($d['product_variation_type'] != 'single') ) {
					$product_id = $product_variation[0];
				}

				$product = wpshop_products::get_product_data($product_id, true);

				$the_product = array_merge( array(
					'product_id'	=> $product_id,
					'product_qty' 	=> $product_qty
				), $product);

				/*	Add variation to product into cart for storage	*/
				if ( !empty($product_variation) ) {
					$the_product = wpshop_products::get_variation_price_behaviour( $the_product, $product_variation, $head_product_id, array('type' => $d['product_variation_type']) );
				}
				if ( !empty( $d['free_variation'] ) ) {
					$the_product['item_meta']['free_variation'] = $d['free_variation'];
				}

				$cart_items[$product_id] = wpshop_orders::add_product_to_order($the_product);

				/* Shipping var */
				$total_weight += !empty($product['product_weight']) ? $product['product_weight'] * $product_qty : 0;
				$nb_of_items += $product_qty;
				$order_shipping_cost_by_article += !empty($product['cost_of_postage']) ? $product['cost_of_postage'] * $product_qty : 0;

				/* item */
				$order_total_ht += $cart_items[$product_id]['item_total_ht'];//$product[WPSHOP_PRODUCT_PRICE_HT] * $product_qty;
				$order_total_ttc += $cart_items[$product_id]['item_total_ttc'];//$product[WPSHOP_PRODUCT_PRICE_TTC] * $product_qty;

				/* Si le taux n'existe pas, on l'ajoute */
				if ( !empty($product[WPSHOP_PRODUCT_PRICE_TAX]) ) {
					if ( !empty($order_tva[(string)$product[WPSHOP_PRODUCT_PRICE_TAX]]) ) {
					$order_tva[(string)$product[WPSHOP_PRODUCT_PRICE_TAX]] += $cart_items[$product_id]['item_tva_total_amount'];//$product[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT]*$product_qty;
					}
					else{
						$order_tva[(string)$product[WPSHOP_PRODUCT_PRICE_TAX]] = $cart_items[$product_id]['item_tva_total_amount'];//$product[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT]*$product_qty;
					}
				}
			}

			$total_cart_ht_or_ttc_regarding_config = WPSHOP_PRODUCT_PRICE_PILOT=='HT' ? $order_total_ht : $order_total_ttc;
			if (!empty($this)) {
				$cart_infos['order_shipping_cost'] = $this->get_shipping_cost($nb_of_items, $total_cart_ht_or_ttc_regarding_config, $order_shipping_cost_by_article, $total_weight);
			}
			else {
				$cart_infos['order_shipping_cost'] = self::get_shipping_cost($nb_of_items, $total_cart_ht_or_ttc_regarding_config, $order_shipping_cost_by_article, $total_weight);
			}
			if ( isset($custom_order_information['custom_shipping_cost']) && ($custom_order_information['custom_shipping_cost']>=0) ) {
				$cart_infos['order_shipping_cost'] = $custom_order_information['custom_shipping_cost'];
			}
		}
		else {
			$cart_items = $_SESSION['cart']['order_items'];
			$order_total_ht = $_SESSION['cart']['order_total_ht'];
			$order_total_ttc = $_SESSION['cart']['order_total_ttc'];
			$order_tva = $_SESSION['cart']['order_tva'];
			$total_cart_ht_or_ttc_regarding_config = WPSHOP_PRODUCT_PRICE_PILOT=='HT' ? $_SESSION['cart']['order_total_ht'] : $_SESSION['cart']['order_total_ttc'];
			$cart_infos['order_shipping_cost'] = self::get_shipping_cost(count($cart_items), $total_cart_ht_or_ttc_regarding_config, $_SESSION['cart']['order_shipping_cost'], 0);
		}

		$cart_infos['order_items'] = $cart_items;
		$cart_infos['order_total_ht'] = number_format($order_total_ht, 5, '.', '');
		$cart_infos['order_total_ttc'] = number_format($order_total_ttc, 5, '.', '');

		$cart_infos['order_grand_total_before_discount'] = number_format($cart_infos['order_total_ttc'] + $cart_infos['order_shipping_cost'], 5, '.', '');
		$cart_infos['order_grand_total'] = $cart_infos['order_grand_total_before_discount'];

		ksort($order_tva);
		$cart_infos['order_tva'] = array_map('number_format_hack', $order_tva);

		$cart_infos['order_temporary_key'] = NULL;
		$cart_infos['order_old_shipping_cost'] = 0;
		$cart_infos['shipping_is_free'] = false;

		/*	Apply the coupon	*/
		$coupon = self::get_coupon_data();
		$cart_infos['coupon_id'] = !empty($coupon['coupon_id']) ? $coupon['coupon_id'] : 0;
		if (!empty($coupon['wpshop_coupon_discount_value'])) {
			$cart_infos['order_discount_type'] = $coupon['wpshop_coupon_discount_type'];
			$cart_infos['order_discount_value'] = $coupon['wpshop_coupon_discount_value'];

			switch ($coupon['wpshop_coupon_discount_type']) {
				case 'amount':
					$cart_infos['order_discount_amount_total_cart'] = $coupon['wpshop_coupon_discount_value'];
				break;
				case 'percent':
					$cart_infos['order_discount_amount_total_cart'] = $cart_infos['order_grand_total'] * ($coupon['wpshop_coupon_discount_value'] / 100);
				break;
			}
			$cart_infos['order_grand_total'] -= $cart_infos['order_discount_amount_total_cart'];
			$cart_infos['order_discount_amount_total_items'] = 0;
		}

		if (empty($cart_infos['order_items'])) {
			$cart_infos = array();
		}

		if (isset($_SESSION['cart']['cart_type'])) {
			$cart_infos['cart_type'] = $_SESSION['cart']['cart_type'];
		}

		return $cart_infos;
	}

	/**
	 * Delete the persistent cart
	 */
	function persistent_cart_destroy() {
		delete_user_meta( get_current_user_id(), '_wpshop_persistent_cart' );
	}

	/**
	 * Get the shipping cost for the current cart
	 *
	 * @param integer $nb_of_items The number of items in cart
	 * @param float $total_cart The amount of the cart
	 * @param float $total_shipping_cost The amount of the shipping cost calculate from the sum of shipping cost for each product in cart
	 * @param float $total_weight The total weight of all product in cart
	 *
	 * @return number|string The sipping cost for the current cart
	 */
	function get_shipping_cost($nb_of_items, $total_cart, $total_shipping_cost, $total_weight) {
		if ($nb_of_items == 0) {
			return 0;
		}

		$current_user = wp_get_current_user();
		$country = 'FR';
		if ( $current_user->ID !== 0 ) {
			$user_shipping_info = get_user_meta($current_user->ID, 'shipping_info', true);
			if ( !empty( $user_shipping_info ) && !empty($user_shipping_info['country']) ) {
				$country = $user_shipping_info['country'];
			}
		}

		$shipping_cost=false;
		/*
		 * Check if custom shipping fees per weight is active in wpshop options
		 */
		$fees = get_option('wpshop_custom_shipping', unserialize(WPSHOP_SHOP_CUSTOM_SHIPPING));
		if ($fees['active']) {
			$shipping_cost = wpshop_shipping::calculate_shipping_cost($country, array('weight'=>$total_weight,'price'=>$total_cart), $fees['fees']);
		}

		/*
		 * If custom shipping fees is not active or if no rules has been used, get the basic rules
		 */
		if ($shipping_cost === false) {
			$rules = get_option('wpshop_shipping_rules',array());
			$shipping_cost = $total_shipping_cost;

			// Min-Max
			if (!empty($rules['wpshop_shipping_rule_free_shipping'])) {
				$shipping_cost=0;
			}
			elseif ($rules['free_from']>=0 && $total_cart>$rules['free_from']) {
				$shipping_cost=0;
			}
			else {
				if($shipping_cost < $rules['min_max']['min']) $shipping_cost = $rules['min_max']['min'];
				elseif($shipping_cost > $rules['min_max']['max']) $shipping_cost = $rules['min_max']['max'];
			}
		}

		return number_format($shipping_cost, 5, '.', '');
	}

	/**
	 * Check if there is enough stock for asked product if manage stock option is checked
	 *
	 * @param integer $product_id The product we have to check the stock for
	 * @param unknown_type $cart_asked_quantity The quantity the end user want to add to the cart
	 *
	 * @return boolean|string  If there is enough sotck or if the option for managing stock is set to false return OK (true) In the other case return an alert message for the user
	 */
	function check_stock($product_id, $cart_asked_quantity) {
		$product_data = wpshop_products::get_product_data($product_id);
		if(!empty($product_data)) {
			$manage_stock_is_activated = (!empty($product_data['manage_stock']) && ($product_data['manage_stock']=='yes')) ? true : false;
			$the_qty_is_in_stock = !empty($product_data['product_stock']) && $product_data['product_stock'] >= $cart_asked_quantity;

			if (($manage_stock_is_activated && $the_qty_is_in_stock) OR !$manage_stock_is_activated) {
				return true;
			}
			else {
				return __('You cannot add that amount to the cart since there is not enough stock.', 'wpshop');
			}
		}

		return false;
	}

	/**
	 * Change the product quantity into the cart
	 *
	 * @param integer $product_id The product identifier to change quantity for. Allow to check if the product is in cart again/if the roduct has enough stock
	 * @param float $quantity The asked quantity
	 *
	 * @return mixed If an error occured return a alert message. In the other case if the quantity is correctly set return true
	 */
	function set_product_qty($product_id, $quantity) {

		if ( !empty($_SESSION['cart']['order_items'][$product_id]) ) {
			// Check the stock
			$return = self::check_stock($product_id, $quantity);
			if($return !== true) return $return;

			$order_items = array();
			foreach($_SESSION['cart']['order_items'] as $product_in_order){
				$order_items[$product_in_order['item_id']]['product_id'] = $product_in_order['item_id'];
				$order_items[$product_in_order['item_id']]['product_qty'] = $product_in_order['item_qty'];
				if($product_id == $product_in_order['item_id']){
					$order_items[$product_in_order['item_id']]['product_qty'] = $quantity;
				}
			}
			if( $quantity == 0 ){
				unset($order_items[$product_id]);
			}
		}
		else {	/* Product is nomore into cart */
			return __('This product does not exist in the cart.', 'wpshop');
		}

		if (!empty($order_items)) {
			$order = self::calcul_cart_information($order_items);
			self::store_cart_in_session($order);

			/** If the user is already connected we store the cart into database for later order if the user does not finalize the cart	*/
			if (get_current_user_id())
				self::persistent_cart_update();
		}
		else $_SESSION['cart'] = array();

		return 'success';
	}

	/**
	 * Check if the cart is empty or not
	 *
	 * @return boolean The state of the cart
	 */
	function is_empty() {
		$cart = (array)$_SESSION['cart'];

		return empty($cart);
	}

	/**
	 * Empty the current existing cart
	 *
	 * @return void
	 */
	function empty_cart() {
		unset($_SESSION['cart']);
		self::persistent_cart_destroy();

		return;
	}

	/**
	 * Display the cart as a widget. Called with a shortcode
	 *
	 * @return string The "mini" cart content
	 */
	function display_mini_cart() {
		/*
		 * Template parameters
		*/
		$template_part = 'mini_cart_container';
		$tpl_component = array();
		$tpl_component['CART_MINI_CONTENT'] = self::mini_cart_content();
		$mini_cart = wpshop_display::display_template_element($template_part, $tpl_component);

		echo $mini_cart;
	}

	/**
	 * Generate output for the cart widget
	 *
	 * @return string $mini_cart_content The cart content
	 */
	function mini_cart_content() {
		$mini_cart_content = '';

		$cart = (array)$_SESSION['cart'];
		$cpt=0;
		if (!empty($cart['order_items'])) {
			foreach ($cart['order_items'] as $item) {
				$cpt += $item['item_qty'];
			}
		}
		if ( $cpt == 0 ) {
			$mini_cart_content = __('Your cart is empty','wpshop');
		}
		else {
			$cart_link = get_permalink(get_option('wpshop_cart_page_id'));
			$currency = wpshop_tools::wpshop_get_currency();

			/*
			 * Template parameters
			*/
			$template_part = 'mini_cart_content';
			$tpl_component = array();
			$tpl_component['PDT_CPT'] = $cpt;
			$tpl_component['CART_TOTAL_AMOUNT'] = number_format($cart['order_grand_total'],2);

			/*
			 * Build template
			*/
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require_once(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$mini_cart_content = ob_get_contents();
				ob_end_clean();
			}
			else {
				$mini_cart_content = wpshop_display::display_template_element($template_part, $tpl_component);
			}
			unset($tpl_component);
		}

		return $mini_cart_content;
	}

	/** Display the cart content
	* @param boolean $hide_button : cacher le bouton de soumission ou non
	* @return void
	*/
	function display_cart($hide_button=false, $order=array(), $from='') {
		global $wpdb;
		$cart = empty($order) ? $_SESSION['cart'] : $order;

		$cart_type = (!empty($cart['cart_type']) && $cart['cart_type']=='quotation') ? 'quotation' : 'cart';

		// Currency
		$currency = wpshop_tools::wpshop_get_currency();

		$cartContent = $cart_output = '';
		if( !empty($cart['order_items']) ) {
			if(!empty($cart['order_items']['item_id'])){
				$tpl_component = array();
				$tpl_component['CART_LINE_ITEM_ID'] = $cart['order_items']['item_id'];
				$tpl_component['CART_LINE_ITEM_QTY'] = $cart['order_items']['item_qty'];
				$tpl_component['CART_LINE_ITEM_LINK'] = get_permalink($cart['order_items']['item_id']);
				$tpl_component['CART_LINE_ITEM_NAME'] = wpshop_tools::trunk($cart['order_items']['item_name'], 30);
				$tpl_component['CART_LINE_ITEM_PUHT'] = sprintf('%0.2f', $cart['order_items']['item_pu_ht']);
				$tpl_component['CART_LINE_ITEM_TPHT'] = sprintf('%0.2f', $cart['order_items']['item_pu_ht']*$b['item_qty']);
				$tpl_component['CART_LINE_ITEM_TPTTC'] = sprintf('%0.2f', $cart['order_items']['item_pu_ttc']*$b['item_qty']);

				$tpl_component['CART_LINE_ITEM_QTY_'] = empty($cart['order_invoice_ref']) ? wpshop_display::display_template_element('cart_qty_content', $tpl_component) : $cart['order_items']['item_qty'];
				$tpl_component['CART_LINE_ITEM_REMOVER'] = empty($cart['order_invoice_ref']) ? wpshop_display::display_template_element('cart_line_remove', $tpl_component) : '';

				$cartContent .= wpshop_display::display_template_element('cart_line', $tpl_component);
			}
			else{
				$product_list_for_details_replacement = array();
				$product_details_replacement = array();
				foreach($cart['order_items'] as $b) :
					$current_post_type = get_post_type( $b['item_id'] );
					if ( !empty( $current_post_type ) ) {
						$tpl_component = array();
						$tpl_component['CART_LINE_ITEM_ID'] = $b['item_id'];
						$tpl_component['CART_LINE_ITEM_QTY'] = $b['item_qty'];
						$tpl_component['CART_LINE_ITEM_LINK'] = get_permalink($b['item_id']);
						$tpl_component['CART_LINE_ITEM_NAME'] = wpshop_tools::trunk($b['item_name'], 30);
						$tpl_component['CART_LINE_ITEM_PUHT'] = sprintf('%0.2f', $b['item_pu_ht']);
						$tpl_component['CART_LINE_ITEM_TPHT'] = sprintf('%0.2f', $b['item_pu_ht']*$b['item_qty']);
						$tpl_component['CART_LINE_ITEM_TPTTC'] = sprintf('%0.2f', $b['item_pu_ttc']*$b['item_qty']);

						$tpl_component['CART_LINE_ITEM_QTY_'] = empty($cart['order_invoice_ref']) ? wpshop_display::display_template_element('cart_qty_content', $tpl_component) : $b['item_qty'];
						$tpl_component['CART_LINE_ITEM_REMOVER'] = empty($cart['order_invoice_ref']) ? wpshop_display::display_template_element('cart_line_remove', $tpl_component) : '';

						$tpl_component['CART_PRODUCT_MORE_INFO'] = '';
						/**	If current element in cart is a variation => Display variation definition	*/
						if ( get_post_type( $b['item_id'] ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
							foreach ( $b['item_meta']['variation_definition'] as $variation_attribute_code => $variation_attribute_detail ) {
								$variation_tpl_component = array();
								foreach ( $variation_attribute_detail as $info_name => $info_value) {
									$variation_tpl_component['VARIATION_' . strtoupper($info_name)] = $info_value;
								}
								$variation_tpl_component['VARIATION_ID'] = $b['item_id'] . '_' . $variation_attribute_code;
								$variation_tpl_component['VARIATION_ATT_CODE'] = $variation_attribute_code;
								$tpl_component['CART_PRODUCT_MORE_INFO'] .= wpshop_display::display_template_element('cart_variation_detail', $variation_tpl_component);
								unset($variation_tpl_component);
							}
							if ( !empty($b['item_meta']['head_product']) ) {
								$post_id = $b['item_meta']['head_product'][$b['item_id']];
							}
							else {
								$query = $wpdb->prepare("SELECT post_parent FROM " . $wpdb->posts . " WHERE ID = %d", $b['item_id']);
								$post_id = $wpdb->get_var($query);

								$query = $wpdb->prepare("SELECT post_title FROM " . $wpdb->posts . " WHERE ID = %d", $post_id);
								$post_title = $wpdb->get_var($query);
								$tpl_component['CART_LINE_ITEM_NAME'] = wpshop_tools::trunk( $post_title, 30);
							}
							$tpl_component['CART_LINE_ITEM_LINK'] = get_permalink( $post_id );
						}

						/**	Display variations details if exists 	*/
						if ( !empty($b['item_meta']['variations']) ) {
							foreach ( $b['item_meta']['variations'] as $variation_id => $variation_details ) {
								$variation_tpl_component = array();
								foreach ( $variation_details as $info_name => $info_value) {
									if ( $info_name != 'item_meta' ) {
										$variation_tpl_component['VARIATION_DETAIL_' . strtoupper($info_name)] = $info_value;
									}
								}
								foreach ( $variation_details['item_meta']['variation_definition'] as $variation_attribute_code => $variation_attribute_def ) {
									$variation_tpl_component['VARIATION_NAME'] = $variation_attribute_def['NAME'];
									$variation_tpl_component['VARIATION_VALUE'] = $variation_attribute_def['VALUE'];
									$variation_tpl_component['VARIATION_ID'] = $variation_id;
									$variation_tpl_component['VARIATION_ATT_CODE'] = $variation_attribute_code;
								}
								$tpl_component['CART_PRODUCT_MORE_INFO'] .= wpshop_display::display_template_element('cart_variation_detail', $variation_tpl_component);
								unset($variation_tpl_component);
							}
						}

						/**	Display free variation details if exists 	*/
						if ( !empty($b['item_meta']['free_variation']) ) {
							foreach ( $b['item_meta']['free_variation'] as $attribute_code => $free_variation_value ) {
								$free_variation_attribute_def = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
								$variation_tpl_component['VARIATION_NAME'] = $free_variation_attribute_def->frontend_label;
								$value_to_outut = $free_variation_value;
								switch ( $free_variation_attribute_def->data_type ) {
									case 'datetime':
										$value_to_outut = mysql2date('d F Y', $free_variation_value, true);
										break;
								}
								$variation_tpl_component['VARIATION_VALUE'] = $value_to_outut;
								$variation_tpl_component['VARIATION_ID'] = $attribute_code;
								$variation_tpl_component['VARIATION_ATT_CODE'] = $attribute_code;
								$tpl_component['CART_PRODUCT_MORE_INFO'] .= wpshop_display::display_template_element('cart_variation_detail', $variation_tpl_component);
								unset($variation_tpl_component);
							}
						}

						$cartContent .= wpshop_display::display_template_element('cart_line', $tpl_component);

						$product_list_for_details_replacement[] = $b['item_id'];
					}
				endforeach;

				$variation_details = array();
				foreach ( $product_list_for_details_replacement as $product_id) {
					$variation_details['CART_PRODUCT_MORE_INFO_' . $product_id] = '';
					if ( array_key_exists($product_id, $product_details_replacement) ) {
						foreach ( $product_details_replacement[$product_id] as $variation_detail ) {
							$variation_details['CART_PRODUCT_MORE_INFO_' . $product_id] .= $variation_detail;
						}
					}
				}
				$cartContent = wpshop_display::feed_template($cartContent, $variation_details);
			}

			if ( ($from == 'admin') && empty($cart['order_invoice_ref']) ) {
				$cartContent .= '
					<tr>
						<td colspan="2" ><a href="#" id="order_new_product_add_opener" >' . __('Add a product to the current order', 'wpshop') . '</a></td>
						<td colspan="4">&nbsp;</td>
					</tr>';
			}

			if ( !empty($cartContent) ) {
				$tpl_component = array();

				$tva_string = '';
				if ( !empty($cart['order_tva']) ) {
					foreach ( $cart['order_tva'] as $k => $v ) {
						if ( !empty($k) ) {
							$tva_string .= wpshop_display::display_template_element('cart_summary_line_content', array('CART_SUMMARY_LINE_SPECIFIC' => '" id="tax_total_amount_' . str_replace(".","_",$k),'CART_SUMMARY_TITLE' => __('Tax','wpshop') . ' ' . $k . '%', 'CART_SUMMARY_AMOUNT' => number_format($v,2,'.',' '), 'CART_SUMMARY_AMOUNT_CLASS' => ''));
						}
					}
				}

				$tpl_component['CART_OUTPUT'] = wpshop_display::display_template_element('cart_table_def', array('CART_TABLE_COLUMN_DEF' => wpshop_display::display_template_element('cart_table_column_def', array()), 'CART_CONTENT' => $cartContent));
				$tpl_component['CART_PRICE_ET'] = number_format($cart['order_total_ht'], 2);
				$tpl_component['CART_TAXES'] = $tva_string;
				$tpl_component['CART_SHIPPING_COST'] = ( ($from == 'admin') && empty($cart['order_invoice_ref']) ) ? '<input type="text" class="wpshop_order_shipping_cost_custom_admin" value="' . number_format($cart['order_shipping_cost'], 2) . '" />' : number_format($cart['order_shipping_cost'], 2);

				$tpl_component['CART_DISCOUNT_SUMMARY'] = '';
				if(!empty($cart['order_grand_total_before_discount']) && $cart['order_grand_total_before_discount'] != $cart['order_grand_total']){
					$tpl_component['CART_DISCOUNT_SUMMARY'] = wpshop_display::display_template_element('cart_summary_line_content', array('CART_SUMMARY_LINE_SPECIFIC' => '','CART_SUMMARY_TITLE' => __('Total ATI before discount','wpshop'), 'CART_SUMMARY_AMOUNT' => number_format($cart['order_grand_total_before_discount'],2), 'CART_SUMMARY_AMOUNT_CLASS' => ' total_ttc_before_discount'));
					$tpl_component['CART_DISCOUNT_SUMMARY'] .= wpshop_display::display_template_element('cart_summary_line_content', array('CART_SUMMARY_LINE_SPECIFIC' => '','CART_SUMMARY_TITLE' => __('Discount','wpshop'), 'CART_SUMMARY_AMOUNT' => number_format($cart['order_discount_amount_total_cart'],2), 'CART_SUMMARY_AMOUNT_CLASS' => ' discount_amount'));
				}

				$tpl_component['CART_TOTAL_ATI'] = number_format($cart['order_grand_total'], 2);

				$tpl_component['CART_VOUNCHER'] = '';
				$tpl_component['CART_EMPTY_BUTTON'] = '';
				$tpl_component['CART_BUTTONS'] = '';
				if ( $from != 'admin' ) {
					/**	Check if vouncher there are existing vouncher	*/
					$existing_vouncher = query_posts( array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_COUPON, 'post_per_page' => '-1') );
					if ( !empty($existing_vouncher) ) {
						$tpl_component['CART_VOUNCHER'] = wpshop_display::display_template_element('cart_vouncher_part', array());
					}
					wp_reset_query();

					if ( !$hide_button ) {
						/**	Display button to validate cart / button to empty cart	*/
						$tpl_component['CART_BUTTONS'] = wpshop_display::display_template_element('cart_buttons', array('CART_BUTTON_VALIDATE_TEXT' => (($cart_type == 'quotation') ? __('Validate my quotation','wpshop') : __('Validate my cart','wpshop')),'BUTTON_EMPTY_CART_TEXT' => ( $cart_type=='quotation' ) ? __('Empty the quotation','wpshop') : __('Empty the cart','wpshop')));
					}
				}

				$cart_output .= (empty($hide_button) ? '<form action="'.self::get_checkout_url().'" method="post">' : '') . wpshop_display::display_template_element('cart_main_page', $tpl_component) . (empty($hide_button) ? '</form>' : '');
			}
		}
		else if ( ($from == 'admin') && empty($cart['order_invoice_ref']) ) {
			$cart_output .= '<div class="cart"><a href="#" id="order_new_product_add_opener" >' . __('Add a product to the current order', 'wpshop') . '</a></div>';
		}
		else $cart_output .= '<div class="cart">'.__('Your cart is empty.','wpshop').'</div>';

		return $cart_output;
	}

	/**
     * Check if product is in the cart and return cart item key
     * @param int $product_id
     * @return int|null
     */
	function find_product_in_cart($product_id) {
		if(!empty($_SESSION['cart']['order_items'])) {
			foreach ($_SESSION['cart']['order_items'] as $cart_item_key => $cart_item) :
				if ($cart_item['item_id'] == $product_id) :
					return $cart_item_key;
				endif;
			endforeach;
		}
    return NULL;
  }

	/**
	 * Add a product to the cart
	 * @param   string	product_id	contains the id of the product to add to the cart
	 * @param   string	quantity	contains the quantity of the item to add
	 */
	function add_to_cart( $product_list, $quantity, $type='normal', $extra_params=array() ) {
		global $wpdb;

		/** Check if a cart already exist. If there is already a cart that is not the same type (could be a cart or a quotation)	*/
		if(isset($_SESSION['cart']['cart_type']) && $type!=$_SESSION['cart']['cart_type']) return __('You have another element type into your cart. Please finalize it by going to cart page.', 'wpshop');
		else $_SESSION['cart']['cart_type']=$type;

		$order_meta = $_SESSION['cart'];
		$order_items = array();
		foreach ($product_list as $pid => $product_more_content) {
			if ( count($product_list) == 1 ) {
				if ($quantity[$pid] < 1) $quantity[$pid] = 1;
				$product = wpshop_products::get_product_data($pid);

				/** Check if the selected product exist	*/
				if ( $product === false ) return __('This product does not exist', 'wpshop');

				/** Get information about the product price	*/
				$product_price_check = wpshop_products::get_product_price($product, 'check_only');
				if ( $product_price_check !== true ) return $product_price_check;

				/** Get the asked quantity for each product and check if there is enough stock	*/
				$the_quantity = !empty($_SESSION['cart']['order_items'][$pid]) ? $quantity[$pid]+$_SESSION['cart']['order_items'][$pid]['item_qty'] : $quantity[$pid];
				$product_stock = self::check_stock($pid, $the_quantity);
				if ( $product_stock !== true ) {
					return $product_stock;
				}
			}

			$order_items[$pid]['product_id'] = $pid;
			$order_items[$pid]['product_qty'] = $quantity[$pid];

			/** For product with variation	*/
			$order_items[$pid]['product_variation_type'] = !empty( $product_more_content['variation_priority']) ? $product_more_content['variation_priority'] : '';
			$order_items[$pid]['free_variation'] = !empty($product_more_content['free_variation']) ? $product_more_content['free_variation'] : '';
			$order_items[$pid]['product_variation'] = '';
			if ( !empty($product_more_content['variations']) ) {
				foreach ( $product_more_content['variations'] as $variation_id) {
					$order_items[$pid]['product_variation'][] = $variation_id;
				}
			}
		}

		if ( !empty($order_meta['order_items']) && is_array($order_meta['order_items']) ) {
			foreach ($order_meta['order_items'] as $product_in_order) {
				if (empty($order_items[$product_in_order['item_id']])) {
					$order_items[$product_in_order['item_id']]['product_id'] = $product_in_order['item_id'];
					$order_items[$product_in_order['item_id']]['product_qty'] = $product_in_order['item_qty'];
				}
				else {
					$order_items[$product_in_order['item_id']]['product_qty'] += $product_in_order['item_qty'];
				}
			}
		}

		$order = self::calcul_cart_information($order_items, $extra_params);
		self::store_cart_in_session($order);

		/*
		 * Store the cart into database for connected user
		 */
		if ( get_current_user_id() ) {
			self::persistent_cart_update();
		}

		return 'success';
	}

	/**
	 * Gets the url to the checkout page
	 * @return void
	 */
	function get_checkout_url() {
		$checkout_page_id = get_option('wpshop_checkout_page_id');
		if ($checkout_page_id) :
			return get_permalink($checkout_page_id);
		endif;
	}

}

?>