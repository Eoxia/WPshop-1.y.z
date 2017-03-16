<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_shipping {
	function __construct() {

	}

	/**
	 * Convert fees (string format) to fees in array format
	 * @param $fees_string : fees string type by the user
	 * @return $data : fees in array format
	 */
	function shipping_fees_string_2_array($fees_string) {
		$data = array();
		if(!empty($fees_string) && !is_array($fees_string) ) {
			if(preg_match_all('#{[^{]+}#', $fees_string, $cont)){
				foreach($cont[0] as $c) {
					preg_match_all('#([a-z]+) ?: ?"(.+)"#', $c, $atts);
					$temp_data = array();
					$country_code = '';
					foreach($atts[1] as $key => $value) {
						$temp_data[$value] =  $atts[2][$key];
						if($value=='destination') {
							$country_code = $atts[2][$key];
						}
						elseif($value=='fees') {
							$fees_data = array();
							$fees = explode(',', $atts[2][$key]);
							foreach($fees as $fee){
								$fee_element = explode(':', $fee);
								$fees_data[trim($fee_element[0])] =  trim($fee_element[1]);
							}
							$number = count($fees_data);

							$fees_data_1 = array();
							preg_match_all('#([0-9]+\.?[0-9]?+) ?: ?([0-9]+\.?[0-9]?+)#', $atts[2][$key], $fees);
							foreach($fees[1] as $_key => $_value) {
								$fees_data_1[$_value] =  $fees[2][$_key];
							}
							$number_1 = count($fees_data_1);
							if ($number == $number_1) {
								$temp_data[$value] =  $fees_data;
							}
							else {
								$temp_data[$value] =  $fees_data_1;
							}
						}
					}
					if(!empty($country_code)) {
						$data[$country_code] = $temp_data;
					}
				}
			}
			return $data;
		}
		return array();
	}

	/**
	 * Convert fees (array format) to fees in string format
	 * @param $fees_array : fees in array format
	 * @return $string : fees in string format
	 */
	function shipping_fees_array_2_string($fees_array) {
		$string = '';
		if(!empty($fees_array)) {
			foreach($fees_array as $d) {
				$string .= '{'."\n";
				foreach($d as $att => $value) {
					$val = '';
					if($att=='fees') {
						foreach($value as $_k=>$_value) $val .= $_k.':'.$_value.', ';
						$val = substr($val,0,-2);
					} else $val = $value;
					$string .= $att.': "'.$val.'",'."\n";
				}
				$string = substr($string,0,-2)."\n";
				$string .= '},'."\n";
			}
			$string = substr($string,0,-2);
			return $string;
		}
		else return false;
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
	function get_shipping_cost( $nb_of_items, $total_cart, $total_shipping_cost, $total_weight, $selected_method = '' ) {
		global $wpdb;
		$shipping_mode_option = get_option( 'wps_shipping_mode' );
		if( !empty($selected_method) || isset( $_SESSION['shipping_address'] ) && empty( $_SESSION['shipping_method'] ) ) {
			if( !empty($selected_method) ) {
				$chosen_shipping_mode = $selected_method;
			} else {
				$ship_mod = new wps_shipping_mode_ctr();
				$shipping_modes = $ship_mod->get_shipping_mode_for_address( $_SESSION['shipping_address'] );
				if( !empty( $shipping_modes ) ) {
					$shipping_modes = $shipping_modes['modes'];
					foreach( $shipping_modes as $key => $shipping_mode ) {
						$chosen_shipping_mode = $key;
						break;
					}
				} else {
					$chosen_shipping_mode = 'default_choice';
				}
			}
		}
		else {
			if( !empty( $_SESSION['shipping_method'] ) ) {
				$chosen_shipping_mode = wpshop_tools::varSanitizer( $_SESSION['shipping_method'] );
			} else {
				$chosen_shipping_mode = 'default_choice';
			}
		}

		$default_weight_unity = get_option( 'wpshop_shop_default_weight_unity' );
		if ( !empty($default_weight_unity) ) {
			$query = $wpdb->prepare('SELECT unit FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id = %d', $default_weight_unity);
			$weight_unity = $wpdb->get_var( $query );

			if ( !empty($weight_unity) && $weight_unity == 'kg' ) {

				$total_weight = $total_weight * 1000;
			}
		}

		if ( ( !empty($_SESSION['shipping_method']) && $_SESSION['shipping_method'] == 'shipping-partners' ) || !empty( $_SESSION['wps-pos-addon']) ) {
			return 0;
		}

		/** Take the selected shipping mode **/
		if( $chosen_shipping_mode == 'default_choice' ) {
			if( !empty( $shipping_mode_option['modes'][$shipping_mode_option['default_choice']]) ) {
				$selected_shipping_mode_config = $shipping_mode_option['modes'][$shipping_mode_option['default_choice']];
			} else {
				$selected_shipping_mode_config = '';
			}
		} else {
			if( !empty( $shipping_mode_option['modes'][$chosen_shipping_mode]) ) {
				$selected_shipping_mode_config = $shipping_mode_option['modes'][$chosen_shipping_mode];
			} else {
				$selected_shipping_mode_config = '';
			}
		}
		$shipping_cost = $total_shipping_cost;




		/** Free Shipping **/
		if ( ( !empty($selected_shipping_mode_config) && !empty($selected_shipping_mode_config['free_shipping']) ) || ( $selected_method == 'is_downloadable_' ) ) {
			$shipping_cost = 0;
		}
		/** Free Shipping From **/
		elseif( !empty($selected_shipping_mode_config) && !empty($selected_shipping_mode_config['free_from']) && $selected_shipping_mode_config['free_from'] >= 0 && $selected_shipping_mode_config['free_from'] <= number_format( $total_cart, 2, '.', '') ) {
			$shipping_cost = 0;
		}
		else {
			/** Check Custom Shipping Cost **/

			if ( !empty($selected_shipping_mode_config['custom_shipping_rules']) && !empty($selected_shipping_mode_config['custom_shipping_rules']['active']) ) {
				$address_infos = '';
				if( !empty( $_SESSION['shipping_address'] ) ) {
					$address_infos = get_post_meta($_SESSION['shipping_address'],'_wpshop_address_metadata', true);
				}
				$country = ( !empty($address_infos['country']) ) ? $address_infos['country'] : '';
				/** Check Active Postcode option **/
				if ( !empty($selected_shipping_mode_config['custom_shipping_rules']['active_cp']) ) {
					$postcode = $address_infos['postcode'];
					if ( array_key_exists($country.'-'.$postcode, $selected_shipping_mode_config['custom_shipping_rules']['fees']) ) {
						$country = $country.'-'.$postcode;
					}
					elseif( array_key_exists($country.'-OTHERS', $selected_shipping_mode_config['custom_shipping_rules']['fees']) ) {
						$country = $country.'-OTHERS';
					}
				}
				$shipping_cost += $this->calculate_custom_shipping_cost($country, array('weight'=>$total_weight,'price'=> $total_cart), $selected_shipping_mode_config['custom_shipping_rules']['fees'], $chosen_shipping_mode);
			}

			/** Min- Max config **/
			if ( !empty($selected_shipping_mode_config['min_max']) && !empty($selected_shipping_mode_config['min_max']['activate']) ) {
				if ( !empty($selected_shipping_mode_config['min_max']['min']) && $shipping_cost < $selected_shipping_mode_config['min_max']['min'] ) {
					$shipping_cost = $selected_shipping_mode_config['min_max']['min'];
				}
				elseif( !empty($selected_shipping_mode_config['min_max']['max']) &&$shipping_cost > $selected_shipping_mode_config['min_max']['max']) {
					$shipping_cost = $selected_shipping_mode_config['min_max']['max'];
				}

			}

		}
		return $shipping_cost;
	}


	/**
	 * Return s custom shipping cost for a cart
	 * @param string $dest Shipping area zone
	 * @param array $data
	 * @param array $fees
	 * @return number|boolean|Ambigous <number, unknown>
	 */
	function calculate_custom_shipping_cost($dest='', $data, $fees, $shipping_method = false) {
		$fees_table = array();
		$key = ''; $price = 0;

		if ( !empty($_SESSION['shipping_partner_id']) ) {
			return 0;
		}

		if($shipping_method === false) {
			$shipping_method = $_SESSION['shipping_method'];
		}

		if(!empty($fees) || !empty($dest) ) {
			$custom_shipping_option = get_option( 'wpshop_custom_shipping', true );
			$shipping_modes = get_option( 'wps_shipping_mode' );

			if ( !empty($shipping_method) ) {
				if ( !empty($shipping_modes) && !empty($shipping_modes['modes']) && !empty($shipping_modes['modes'][ $shipping_method ]) ) {
					$custom_shipping_option = $shipping_modes['modes'][ $shipping_method ]['custom_shipping_rules'];
				}
			}
			$found_active_cp_rule = $found_active_departement_rule = false;
			$shipping_address_def = get_post_meta( $_SESSION['shipping_address'], '_wpshop_address_metadata', true );
			$postcode = '';
			if ( !empty($shipping_address_def) ) {
				$postcode = $shipping_address_def['postcode'];
			}

			/** Search Postcode custom fees **/
			if ( !empty($custom_shipping_option) && !empty($custom_shipping_option['active_cp']) ) {
				$key = $dest.'-'.$postcode;
				if ( array_key_exists($key, $fees) ) {
					foreach ($fees[$key]['fees'] as $k => $shipping_price) {
						if ( $data['weight'] <= $k) {
							break;
						}
					}
					$found_active_cp_rule = true;
				}
			}

			/** Search Department custom fees **/
			if( !empty($custom_shipping_option) && !empty($custom_shipping_option['active_department']) && !$found_active_cp_rule ) {
				$department = substr( $postcode, 0,2 );
				$key = $dest.'-'.$department;
				/** Check if a rule exists **/
				if ( array_key_exists($key, $fees) ) {
					foreach ($fees[$key]['fees'] as $k => $shipping_price) {
						if ( $data['weight'] <= $k) {
							break;
						}
					}
					$found_active_departement_rule = true;
				}
			}

			/** Search general custom fees **/
			if( !$found_active_cp_rule && !$found_active_departement_rule ){
				if ( array_key_exists($dest, $fees) ) {
					$key = $dest;
				}
				elseif( array_key_exists( 'OTHERS', $fees) ) {
					$key = 'OTHERS';
				}
				else {
					return false;
				}
			}

			//Search fees
			if ( !empty($key) ) {
				ksort( $fees[$key]['fees'] );
				foreach ($fees[$key]['fees'] as $k => $shipping_price) {
					if ( $data['weight'] <= $k) {
						$price = $shipping_price;
						break;
					}
					if ($shipping_price === end($fees[$key]['fees'])) {
						$price = $shipping_price;
					}
				}
			}
			else {
				return false;
			}
			return $price;
		}
		return false;
	}

	/**
	 * Return Amount of Shipping Cost for Cart items
	 * @param array $cart_items
	 * @return number
	 */
	function calcul_cart_items_shipping_cost( $cart_items ) {
		$shipping_cost = 0;
		if( !empty($cart_items) ) {
			foreach( $cart_items as $cart_item ) {
				$product_data = get_post_meta( $cart_item['item_id'], '_wpshop_product_metadata', true );

				if ( !empty( $product_data ) && !empty( $product_data['cost_of_postage'] ) ) {
					$shipping_cost += ( $product_data['cost_of_postage'] * $cart_item['item_qty'] );
				}
			}
		}

		return $shipping_cost;
	}

	/**
	 * Return the cart total weight
	 * @param array $cart_items
	 * @return number
	 */
	function calcul_cart_weight( $cart_items ) {
		$cart_weight = 0;
		if ( !empty( $cart_items) ) {
			foreach( $cart_items as $id_item => $cart_item ) {
				$id_item = wpshop_products::get_id_variation($id_item);
				if ( get_post_type($id_item) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
					$product_data = get_post_meta( $id_item, '_wpshop_product_metadata', true );
					if ( !empty($product_data) && !empty($product_data['product_weight']) ) {
						$cart_weight += ( $product_data['product_weight'] * $cart_item['item_qty'] );
					}
					else {
						$parent_def = wpshop_products::get_parent_variation( $id_item );
						if ( !empty($parent_def) && !empty( $parent_def['parent_post_meta']) && !empty($parent_def['parent_post_meta']['product_weight']) ) {
							$cart_weight += ( $parent_def['parent_post_meta']['product_weight'] * $cart_item['item_qty'] );
						}
					}
				}
				else {
					$product_data = get_post_meta( $cart_item['item_id'], '_wpshop_product_metadata', true );
					if ( !empty($product_data) && !empty($product_data['product_weight']) && !empty($cart_item['item_qty'])  ) {
						$cart_weight += ( $product_data['product_weight'] * $cart_item['item_qty'] );
					}
				}
			}
		}
		$cart_weight = apply_filters( 'extra_cart_weight_calcul', $cart_weight, $cart_items );
		return $cart_weight;
	}


}
