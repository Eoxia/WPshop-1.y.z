<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WPSHOP Prices bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wpshop_prices") ) {
	class wpshop_prices {

		function __construct() {
			add_action('wsphop_options', array('wpshop_prices', 'declare_options'));
		}

		public static function declare_options () {
			register_setting('wpshop_options', 'wpshop_catalog_product_option', array('wpshop_prices', 'wpshop_options_validate_prices'));
			add_settings_field('wpshop_catalog_product_option_discount', __('Activate the discount on products', 'wpshop'), array('wpshop_prices', 'wpshop_activate_discount_prices_field'), 'wpshop_catalog_product_option', 'wpshop_catalog_product_section');
		}

		public static function wpshop_options_validate_prices($input) {
			global $wpdb;

			/** Price attribute Def **/
			$price_piloting_option = get_option('wpshop_shop_price_piloting');
			$price_attribute_def = wpshop_attributes::getElement( ( (!empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? 'price_ht' : 'product_price' ), "'valid'", 'code');

			/** Discount attributes **/
			$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = %s OR code = %s OR  code = %s OR code = %s OR code = %s', 'discount_amount', 'discount_rate', 'special_price', 'special_from', 'special_to' );
			$discount_attributes = $wpdb->get_results($query);

			/** Check if discount is actived **/
			if ( !empty( $input) && !empty($input['discount']) ) {
				/** Activate the attributes **/
				if ( !empty($discount_attributes) ) {
					foreach ( $discount_attributes as $discount_attribute ) {
						$update = $wpdb->prepare('UPDATE ' .WPSHOP_DBT_ATTRIBUTE. ' SET status = "valid" WHERE code = %s', $discount_attribute->code);
						$wpdb->query($update);
					}
				}

				/** Affect discount attributes to All Attributes Set section where there is a Price attribute **/
				$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_id = %d', $price_attribute_def->id);
				$attributes_sections = $wpdb->get_results( $query );
				if ( !empty($attributes_sections) ) {
					foreach ( $attributes_sections as $attributes_section ) {
						/** Check the Max Position for the Attribute section */
						$query = $wpdb->prepare('SELECT MAX(position) AS max_position FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_set_id = %d AND attribute_group_id = %d AND entity_type_id = %s', $attributes_section->attribute_set_id, $attributes_section->attribute_group_id, $attributes_section->entity_type_id);
						$max_position = $wpdb->get_var( $query );
						$max_position = ( !empty($max_position) ) ? $max_position : 0;
						/** Affect the discount attributes **/
						foreach ( $discount_attributes as $discount_attribute) {
							$query = $wpdb->prepare(' SELECT COUNT(*) AS count_attributes_affect FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_set_id = %d AND attribute_group_id = %d AND entity_type_id = %s AND attribute_id = %d', $attributes_section->attribute_set_id, $attributes_section->attribute_group_id, $attributes_section->entity_type_id, $discount_attribute->id);
							$count_attribute_affectation = $wpdb->get_row( $query );
							if ( !empty($count_attribute_affectation) && ( empty($count_attribute_affectation->count_attributes_affect) || $count_attribute_affectation->count_attributes_affect == 0 ) ) {
								$result = $wpdb->insert( WPSHOP_DBT_ATTRIBUTE_DETAILS, array( 'status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $attributes_section->entity_type_id, 'attribute_set_id' => $attributes_section->attribute_set_id, 'attribute_group_id' => $attributes_section->attribute_group_id, 'attribute_id' => $discount_attribute->id, 'position' => $max_position) );
								$max_position += 1;
							}
						}
					}
				}
			}
			else {
				/** Desactivate Discount Attributes **/
				if ( !empty($discount_attributes) ) {
					foreach ( $discount_attributes as $discount_attribute ) {
						$update = $wpdb->prepare('UPDATE ' .WPSHOP_DBT_ATTRIBUTE. ' SET status = "notused" WHERE code = %s', $discount_attribute->code);
						$wpdb->query($update);
					}
				}

				/** Delete the Price attribute set section affectation **/
				$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_id = %d', $price_attribute_def->id);
				$attributes_sections = $wpdb->get_results( $query );
				if ( !empty($attributes_sections) ) {
					foreach ( $attributes_sections as $attributes_section ) {
						/** Affect the discount attributes **/
						foreach ( $discount_attributes as $discount_attribute) {
							$wpdb->delete( WPSHOP_DBT_ATTRIBUTE_DETAILS, array( 'entity_type_id' => $attributes_section->entity_type_id, 'attribute_set_id' => $attributes_section->attribute_set_id, 'attribute_group_id' => $attributes_section->attribute_group_id, 'attribute_id' => $discount_attribute->id ) );
						}
					}
				}
			}

			return $input;
		}

		public static function wpshop_activate_discount_prices_field() {
			$product_discount_option = get_option('wpshop_catalog_product_option');

			$output  = '<input type="checkbox" id="wpshop_catalog_product_option_discount" name="wpshop_catalog_product_option[discount]" ' .( (!empty($product_discount_option) && !empty($product_discount_option['discount'])) ? 'checked="checked"' : '' ). ' />';
			$output .= '<a class="wpshop_infobulle_marker" title="' .__('Activate the possibility to create discount on products', 'wpshop'). '" href="#">?</a>';
			echo $output;
		}

		public static function check_product_price( $product, $cart = false ) {
			$price_infos = array();
			$wpshop_price_piloting_option = get_option('wpshop_shop_price_piloting');
			if ( !empty($product) ) {
				if ( $cart ) {
					$discount_config = self::check_discount_for_product($product['product_id'] );
					if ( !empty($discount_config) ) {
						$product['price_ttc_before_discount'] = $product['product_price'];
						$product['price_ht_before_discount'] = $product['price_ht'];

						if ( !empty($discount_config['type']) && !empty($discount_config['value']) && $discount_config['type'] == 'special_price' ) {
							$product['price_ht'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $discount_config['value'] : $discount_config['value'] / (1 + $product['tx_tva'] /100);
							$product['product_price'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $discount_config['value'] * (1 + $product['tx_tva'] /100) : $discount_config['value'];
							$product['tva'] = $product['price_ht'] * ( $product['tx_tva'] / 100);
						}
						elseif( !empty($discount_config['type']) && !empty($discount_config['value']) && $discount_config['type'] == 'discount_amount' ) {
							$product['price_ht'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? ( $product['price_ht_before_discount'] - $discount_config['value'] ) : ( ( $product['price_ttc_before_discount'] - $discount_config['value'] ) / (1 + $product['tx_tva'] /100) ) ;
							$product['product_price'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? $product['price_ht'] * (1 + $product['tx_tva'] /100) : $product['price_ttc_before_discount'] - $discount_config['value'];
							$product['tva'] =  $product['price_ht'] * ( $product['tx_tva'] / 100);

						}
						elseif(!empty($discount_config['type']) && !empty($discount_config['value']) && $discount_config['type'] == 'discount_rate') {
							$product['price_ht'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? ( $product['price_ht_before_discount'] * ( 1 -  $discount_config['value'] / 100) ) : ( ( $product['price_ttc_before_discount']  * ( 1 - ( $discount_config['value'] / 100 ) ) ) / (1 + $product['tx_tva'] /100) ) ;
							$product['product_price'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? $product['price_ht'] * ( 1 + $product['tx_tva'] /100) : $product['price_ttc_before_discount'] * ( 1 - ( $discount_config['value'] / 100 ) );
							$product['tva'] =  $product['price_ht'] * ( $product['tx_tva'] / 100);

						}


						$price_infos['ati'] = ( !empty($product['price_ttc_before_discount'] )) ? number_format((float)$product['price_ttc_before_discount'], 2, '.', '') : number_format((float)$product['product_price'], 2, '.', '');
						$price_infos['et'] = ( !empty($product['price_ht_before_discount'] ) ) ? number_format((float)$product['price_ht_before_discount'], 2, '.', '') : number_format((float)$product['price_ht'], 2, '.', '');
						$price_infos['tva'] = $price_infos['et'] * ( $product['tx_tva'] / 100);

						$price_infos['discount']['discount_exist'] = ( !empty($product['price_ttc_before_discount'])  && !empty($product['price_ht_before_discount'] ) ) ? true : false;
						$price_infos['discount']['discount_ati_price'] = ( !empty($product['price_ttc_before_discount']) ) ? number_format((float)$product['product_price'], 2, '.', '') : 0;
						$price_infos['discount']['discount_et_price'] = ( !empty($product['price_ht_before_discount']) ) ? number_format( (float)$product['price_ht'], 2, '.', '' ) : 0;
						$price_infos['discount']['discount_tva'] = ( !empty($product['price_ttc_before_discount']) && !empty($product['price_ht_before_discount']) ) ? number_format( ($price_infos['discount']['discount_ati_price'] - $price_infos['discount']['discount_et_price']), 2, '.', '') : 0;
					}
					else {
						$price_infos['ati'] = ( !empty($product['product_price'] )) ? number_format((float)$product['product_price'], 5, '.', '') : 0;
						$price_infos['et'] = ( !empty($product['price_ht'] ) ) ? number_format((float)$product['price_ht'], 5, '.', '') : 0;
						$price_infos['tva'] =  ( !empty($product['tva'] ) ) ? $product['tva'] : 0;
					}


				}
				else {
					if ( !empty( $product['price_ttc_before_discount']) && !empty( $product['price_ht_before_discount'] ) ) {
						$price_infos['discount']['discount_exist'] = ( !empty($product['price_ttc_before_discount'])  && !empty($product['price_ht_before_discount'] ) ) ? true : false;
						$price_infos['discount']['discount_ati_price'] = ( !empty($product['product_price']) ) ? number_format((float)$product['product_price'], 2, '.', '') : 0;
						$price_infos['discount']['discount_et_price'] = ( !empty($product['price_ht']) ) ? number_format( (float)$product['price_ht'], 2, '.', '' ) : 0;
						$price_infos['discount']['discount_tva'] = ( !empty($product['tva']) ) ? $product['tva'] : 0;

						$price_infos['ati'] = ( !empty($product['price_ttc_before_discount'] )) ? number_format((float)$product['price_ttc_before_discount'], 5, '.', '') : 0;
						$price_infos['et'] = ( !empty($product['price_ht_before_discount']) ) ? number_format((float)$product['price_ht_before_discount'], 5, '.', '') : 0;
						$price_infos['tva'] =  $price_infos['ati'] - $price_infos['et'];
					}
					else {
						$price_infos['ati'] = ( !empty($product['product_price'] )) ? number_format((float)$product['product_price'], 5, '.', '') : 0;
						$price_infos['et'] = ( !empty($product['price_ht'] ) ) ? number_format((float)$product['price_ht'], 5, '.', '') : 0;
						$price_infos['tva'] =  ( !empty($product['tva'] ) ) ? $product['tva'] : 0;
					}
				}
			}
			return $price_infos;
		}

		public static function get_product_price($product, $return_type, $output_type = '', $only_price = false, $price_checking_done = false) {
			$wpshop_price_piloting_option = get_option('wpshop_shop_price_piloting');
// 			$wpshop_price_piloting_option = 'TTC';
 			/** Price for Mini-output **/
			if( !empty($product['product_id']) && !$price_checking_done ) {

				/** Checking if it's a product with variation **/
				$variation_option_checking = get_post_meta( $product['product_id'], '_wpshop_variation_defining', true );

				if( !empty($variation_option_checking) ) {
					$variations_exists = wpshop_products::get_variation( $product['product_id'], 'publish' );
				}

				if( !empty($variation_option_checking) && !empty($variations_exists) ) {
					if ( !empty( $variation_option_checking['attributes']) ) {
						foreach( $variation_option_checking['attributes'] as $attribute ) {
							$selected_variation[$attribute] = 0;
							if( !empty( $variation_option_checking['options'] ) && !empty( $variation_option_checking['options']['attributes_default_value'] ) && array_key_exists( $attribute, $variation_option_checking['options']['attributes_default_value']) ){
								if ( $variation_option_checking['options']['attributes_default_value'][$attribute] != 'none') {
									$selected_variation[$attribute] = $variation_option_checking['options']['attributes_default_value'][$attribute];
								}
							}
						}
					}
					if ( !empty($selected_variation) ) {
						$product_with_variation = wpshop_products::get_variation_by_priority( $selected_variation, $product['product_id'] );
					}
					if( empty($product_with_variation[$product['product_id']]['variations']) ) {
						$product_with_variation[$product['product_id']]['variations'] = array();
					}
					if ( !empty($product_with_variation[$product['product_id']]['variation_priority']) ) {
						$product =  wpshop_products::get_variation_price_behaviour( $product, $product_with_variation[$product['product_id']]['variations'], $product['product_id'], array('type' => $product_with_variation[$product['product_id']]['variation_priority'], 'text_from' => !empty($product_with_variation['text_from']) ? 'on' : '' ) );
					}
				}
				else {
					/** It's Simple product Check Discounts for products **/
					$discount_config = self::check_discount_for_product( $product['product_id'] );
					if ( !empty($discount_config) ) {
						if ( !empty($discount_config['type']) && !empty($discount_config['value']) && $discount_config['type'] == 'special_price' ) {
							$product['price_ttc_before_discount'] = $product['product_price'];
							$product['price_ht_before_discount'] = $product['price_ht'];

							$product['price_ht'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $discount_config['value'] : $discount_config['value'] / (1 + $product['tx_tva'] /100);
							$product['product_price'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $discount_config['value'] * (1 + $product['tx_tva'] /100) : $discount_config['value'];
							$product['tva'] = $product['price_ht'] * ( $product['tx_tva'] / 100);
						}
						elseif( !empty($discount_config['type']) && !empty($discount_config['value']) && $discount_config['type'] == 'discount_amount' ) {
							$product['price_ttc_before_discount'] = $product['product_price'];
							$product['price_ht_before_discount'] = $product['price_ht'];

							$product['price_ht'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? ( $product['price_ht_before_discount'] - $discount_config['value'] ) : ( ( $product['price_ttc_before_discount'] - $discount_config['value'] ) / (1 + $product['tx_tva'] /100) ) ;
							$product['product_price'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? $product['price_ht'] * (1 + $product['tx_tva'] /100) : $product['price_ttc_before_discount'] - $discount_config['value'];
							$product['tva'] =  $product['price_ht'] * ( $product['tx_tva'] / 100);

						}
						elseif(!empty($discount_config['type']) && !empty($discount_config['value']) && $discount_config['type'] == 'discount_rate') {
							$product['price_ttc_before_discount'] = $product['product_price'];
							$product['price_ht_before_discount'] = $product['price_ht'];


							$product['price_ht'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? ( $product['price_ht_before_discount'] * ( 1 - $discount_config['value'] / 100) ) : ( ( $product['price_ttc_before_discount']  * ( 1 - ( $discount_config['value'] / 100 ) ) ) / (1 + $product['tx_tva'] /100) ) ;
							$product['product_price'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? $product['price_ht'] * ( 1 + $product['tx_tva'] /100) : $product['price_ttc_before_discount'] * ( 1 - ( $discount_config['value'] / 100 ) );
							$product['tva'] =  $product['price_ht'] * ( $product['tx_tva'] / 100);

						}
					}
				}

			}

			$price_infos = self::check_product_price( $product );
			$productCurrency = '<span class="wps-currency">'.wpshop_tools::wpshop_get_currency().'</span>';

			$wps_marketing_tools = new wps_marketing_tools_ctr();

			if ( !empty($price_infos) ) {
				if ( $return_type == 'check_only' ) {
					/** Check if the product price has been set	*/
					if( isset($price_infos['ati']) && $price_infos['ati'] === '') return __('This product cannot be purchased - the price is not yet announced', 'wpshop');
					/** Check if the product price is coherent (not less than 0)	*/
					if( isset($price_infos['ati']) && $price_infos['ati'] < 0) return __('This product cannot be purchased - its price is negative', 'wpshop');

					return true;
				}
				elseif( $return_type == 'just_price_infos' ) {
					$tpl_component = array();

					$price = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $price_infos['et'] : $price_infos['ati'];
					$exploded_price = explode('.', number_format($price,2, '.', ''));
					$price = '<span class="wps-absolute-price">'.$exploded_price[0].'</span><span class="wpshop_price_centimes_display">,'.( (!empty($exploded_price[1]) ) ? $exploded_price[1] : '').'</span>';

					$tpl_component['TAX_PILOTING'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT')  ? __('ET', 'wpshop') : '';

					$tpl_component['CROSSED_OUT_PRICE'] = '';
					$tpl_component['MESSAGE_SAVE_MONEY'] = '';



					if( !empty($price_infos['discount']['discount_exist']) ) {
						$crossed_out_price = ( (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? number_format($price_infos['et'], 2) : number_format($price_infos['ati'], 2) ).' '. $productCurrency;
						$tpl_component['CROSSED_OUT_PRICE'] = $crossed_out_price;
						if(!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') {
							$exploded_discount_price = explode('.', number_format($price_infos['discount']['discount_et_price'],2, '.', ''));
						}
						else {
							$exploded_discount_price = explode('.', number_format($price_infos['discount']['discount_ati_price'],2, '.', ''));
						}

						$discount_price = $exploded_discount_price[0].'<span class="wpshop_price_centimes_display">,'.( (!empty($exploded_discount_price[1]) ) ? $exploded_discount_price[1] : '').'</span>';
						$tpl_component['PRODUCT_PRICE'] = '<span class="wps-absolute-price">'.$discount_price.'</span> '.$productCurrency;

						$tpl_component['MESSAGE_SAVE_MONEY'] = $wps_marketing_tools->display_message_you_save_money( $price_infos );
					}
					else {
						$tpl_component['PRODUCT_PRICE'] = $price.' '.$productCurrency;
					}

					$post_type = get_post_type( $product['product_id'] );
					if (  $post_type ==  WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
						$price_display_attribute = get_post_meta( $product['product_id'], '_wpshop_variation_defining', true );
					}
					elseif( $post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
						$parent_def = wpshop_products::get_parent_variation ( $product['product_id'] );
						if( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
							$parent_post = $parent_def['parent_post'];
							$price_display_attribute = get_post_meta( $parent_post->ID, '_wpshop_variation_defining', true );
						}
					}
					$price_display_option = get_option( 'wpshop_catalog_product_option' );
					$tpl_component['PRICE_FROM'] = ( ( !empty($price_display_attribute) && empty($price_display_attribute['options'] ) && !empty($price_display_option) && !empty($price_display_option['price_display']) && !empty($price_display_option['price_display']['text_from'])  ) || ( ( !empty($price_display_attribute) && (!empty($price_display_attribute['options'] ) && (!empty($price_display_attribute['options']['price_display']) && !empty($price_display_attribute['options']['price_display']['text_from']) ) ) ) ) ) ? 'on' : '';

					return $tpl_component;
				}
				else if ( $return_type == 'price_display' ) {
					$tpl_component = array();
					$price = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $price_infos['et'] : $price_infos['ati'];

					$price_data = $price_infos;

					$exploded_price = explode('.', number_format($price,2, '.', ''));
					$price = '<span class="wps-absolute-price">'.$exploded_price[0].'</span><span class="wpshop_price_centimes_display">.'.( (!empty($exploded_price[1]) ) ? $exploded_price[1] : '').'</span>';

					$tpl_component['TAX_PILOTING'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT')  ? __('ET', 'wpshop') : '';

					$tpl_component['CROSSED_OUT_PRICE'] = '';
					$tpl_component['MESSAGE_SAVE_MONEY'] = '';

					if( !empty($price_infos['discount']['discount_exist']) ) {
						$text_from = false;
						/** Get variation defining **/
						$post_type = get_post_type( $product['product_id'] );
						if( $post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
							$parent_def = wpshop_products::get_parent_variation ( $product['product_id'] );
							if( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
								$parent_post = $parent_def['parent_post'];
								$price_display_attribute = get_post_meta( $parent_post->ID, '_wpshop_variation_defining', true );
							}
						}
						else {
							$price_display_attribute = get_post_meta( $product['product_id'], '_wpshop_variation_defining', true );
						}
						$text_from = ( ( !empty($price_display_attribute) && empty($price_display_attribute['options'] ) && !empty($price_display_option) && !empty($price_display_option['price_display']) && !empty($price_display_option['price_display']['text_from'])  ) || ( ( !empty($price_display_attribute) && (!empty($price_display_attribute['options'] ) && (!empty($price_display_attribute['options']['price_display']) && !empty($price_display_attribute['options']['price_display']['text_from']) ) ) ) ) && !empty($product['text_from']) ) ? true : false;


						$exploded_price = explode('.', number_format($price_infos['discount']['discount_et_price'],2, '.', ''));
						$price_infos['discount']['discount_et_price'] = '<span class="wps-absolute-price">'.$exploded_price[0].'</span><span class="wpshop_price_centimes_display">.'.( (!empty($exploded_price[1]) ) ? $exploded_price[1] : '').'</span>';

						$exploded_price = explode('.', number_format($price_infos['discount']['discount_ati_price'],2, '.', ''));
						$price_infos['discount']['discount_ati_price'] = '<span class="wps-absolute-price">'.$exploded_price[0].'</span><span class="wpshop_price_centimes_display">.'.( (!empty($exploded_price[1]) ) ? $exploded_price[1] : '').'</span>';


						$crossed_out_price = ( (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? number_format($price_infos['et'], 2) : number_format($price_infos['ati'], 2) ).' '. $productCurrency;
						$tpl_component['CROSSED_OUT_PRICE'] = str_replace( '.', ',', ( ( $text_from ) ? __('Price from', 'wpshop') . ' ' : '' ). wpshop_display::display_template_element('product_price_template_crossed_out_price', array('CROSSED_OUT_PRICE_VALUE' => $crossed_out_price)) );
						$tpl_component['PRODUCT_PRICE'] = (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? '<span class="wps-absolute-price">'. $price_infos['discount']['discount_et_price'].'</span> '.$productCurrency : '<span class="wps-absolute-price">'.$price_infos['discount']['discount_ati_price'].'</span> '.$productCurrency;
						$tpl_component['MESSAGE_SAVE_MONEY'] = $wps_marketing_tools->display_message_you_save_money( $price_data );

					}
					else {
						if( get_post_type($product['product_id']) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
							$parent_def = wpshop_products::get_parent_variation( $product['product_id'] );
							$pid = $parent_def['parent_post'];
							$pid = $pid->ID;
						}
						else {
							$pid = $product['product_id'];
						}

						$text_from = false;
						/** Get variation defining **/
						$post_type = get_post_type( $pid );
						if( $post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
							$parent_def = wpshop_products::get_parent_variation ( $pid );
							if( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
								$parent_post = $parent_def['parent_post'];
								$price_display_attribute = get_post_meta( $parent_post->ID, '_wpshop_variation_defining', true );
							}
						}
						else {
							$price_display_attribute = get_post_meta( $pid, '_wpshop_variation_defining', true );
						}

						$price_display_attribute = get_post_meta( $pid, '_wpshop_variation_defining', true );

						$text_from = ( ( !empty($price_display_attribute) && empty($price_display_attribute['options'] ) && !empty($price_display_option) && !empty($price_display_option['price_display']) && !empty($price_display_option['price_display']['text_from'])  ) || ( ( !empty($price_display_attribute) && (!empty($price_display_attribute['options'] ) && (!empty($price_display_attribute['options']['price_display']) && !empty($price_display_attribute['options']['price_display']['text_from']) ) ) ) ) ) ? true : false;

						$tpl_component['PRODUCT_PRICE']  = ( $text_from && !empty($product['text_from']) ) ? __('Price from', 'wpshop') . ' ' : '';
						$tpl_component['PRODUCT_PRICE'] .= $price.' '.$productCurrency;
					}


					// Replace . by ,
					$tpl_component['PRODUCT_PRICE'] = str_replace( '.',',', $tpl_component['PRODUCT_PRICE'] );

					if ( $output_type == 'complete_sheet' ) {
						$price_tpl = wpshop_display::display_template_element('product_price_template_complete_sheet', $tpl_component );
					}
					elseif ( $output_type == 'mini_output' || in_array('mini_output', $output_type ) ) {
						$price_tpl = wpshop_display::display_template_element('product_price_template_mini_output', $tpl_component );
					}
					return $price_tpl;
				}

			}

			return false;
		}

		/**
		 * Récupère le taux de TVA du produit. Si il n'est pas trouvé retourne le taux
		 * par défaut. Si ces deux cas sont vides log et arrêtes le script. / Get the
		 * Product VAT rate. If it is not found return the default rate. If these
		 * two cases are empty, log and stop the script.
		 *
		 * @param integer $product_id L'id du produit / The product ID
		 * @return stdClass ( value, id ) L'id de l'attribut et le taux de TVA /
		 * Attribute ID and the VAT rate
		 */
		public static function get_rate_vat( $product_id ) {
			global $wpdb;

			/**
			 * Cette requête récupère la valeur du taux de TVA / This query retrieves
			 * the value of the VAT rate
			 */
			$query = "
					SELECT ATTR_VAL_OPTIONS.value, ATTR_VAL_OPTIONS.id
					FROM " . WPSHOP_DBT_ATTRIBUTE . " as ATTR
						INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " as ATTR_VAL_OPTIONS ON ATTR_VAL_OPTIONS.attribute_id = ATTR.id
						INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " as ATTR_VAL_INT ON ( ATTR_VAL_INT.attribute_id = ATTR.id AND ATTR_VAL_OPTIONS.id=ATTR_VAL_INT.value )
					WHERE ATTR.code=%s AND ATTR_VAL_INT.entity_id=%d";

			$request = $wpdb->prepare( $query, array( 'tx_tva',  $product_id ) );
			$rate_vat = $wpdb->get_row( $request );

			/**
			 * Vérifie ensuite si elle est vide, si elle est vide met la valeur par
			 * défaut / Then checks if it is empty , if empty the value put the
			 * default value
			 */
			if( empty( $rate_vat ) ) {
				wpeologs_ctr::log_datas_in_files( 'wps_product', array(
					'object_id' 	=> $product_id,
					'message' 		=> __( 'Use the default VAT rate', 'wpshop' ) ), 0
				);

				$query = "
						SELECT ATTR_VAL_OPTIONS.value, ATTR_VAL_OPTIONS.id
						FROM " . WPSHOP_DBT_ATTRIBUTE . " as ATTR
							INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " as ATTR_VAL_OPTIONS ON ATTR_VAL_OPTIONS.attribute_id = ATTR.id
						WHERE ATTR.code=%s AND ATTR_VAL_OPTIONS.id=ATTR.default_value";

				$request = $wpdb->prepare( $query, array( 'tx_tva' ) );
				$rate_vat = $wpdb->get_row( $request );
			}

			/**
			 * Si c'est toujours vide, cela signifie qu'aucun taux de tva à été trouvé
			 * dans ce cas la on utilise le log / If it is still empty , it means that no
			 * VAT rate found in this case the log is used
			 */
			if( empty( $rate_vat ) ) {
				wpeologs_ctr::log_datas_in_files( 'wps_product', array(
					'object_id' 	=> $product_id,
					'message' 		=> __( 'No VAT rate in the product and no default VAT rate found', 'wpshop' ) ), 2
				);
			}

			return $rate_vat;
		}

		/**
		 * Check if isset Required attributes
		 */
		public static function check_required_attributes( $product_id ) {
			$required_attributes_list = array();
			$variation_option = get_post_meta( $product_id, '_wpshop_variation_defining', true);
			if ( !empty($variation_option) && !empty($variation_option['attributes']) ) {
				if( !empty($variation_option['options']) && !empty($variation_option['options']['required_attributes']) ) {
					foreach( $variation_option['options']['required_attributes'] as $required_attribute ) {
						$required_attributes_list[ $required_attribute ] = $required_attribute;
					}
				}
				/** Check the attribute configuration **/
				foreach ( $variation_option['attributes'] as $variation ) {
					$attribute_def = wpshop_attributes::getElement( $variation, '"valid"', 'code' );
					if ( is_object($attribute_def) && !empty($attribute_def->is_required) && $attribute_def->is_required == 'yes' ) {
						$required_attributes_list[$attribute_def->code] = $attribute_def->code;
					}
				}
			}



			return $required_attributes_list;
		}

		/** Check the Product lower price **/
		public static function check_product_lower_price ( $product_id ) {
			global $wpdb;
			$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
			$lower_price_product = $min_price = 0;
			$variations = wpshop_products::get_variation( $product_id, 'publish' );
			$single_variations = $lower_price_product_combinaison = array();
			$first = true;

			/** Check variations type **/
			$query = $wpdb->prepare( 'SELECT DISTINCT( SUBSTRING( m.meta_value, 3,1 ) ) AS attributes_count FROM ' .$wpdb->postmeta .' AS m INNER JOIN ' .$wpdb->posts. ' AS P ON ((P.ID = m.post_id) AND (P.post_parent = %d)) WHERE meta_key LIKE %s', $product_id, '_wpshop_variations_attribute_def' );
			$variation_type = $wpdb->get_results( $query );


			/** Check which type of variation contains the product **/
			$contains_simple_variation = false;
			if ( !empty($variation_type) ) {
				foreach( $variation_type as $k => $value ) {
					if( $value->attributes_count == '1' ) {
						$contains_simple_variation = true;
					}
				}
			}

			if ( $contains_simple_variation ) {

				if( !empty($variations) ) {
					$attributes = get_post_meta( $product_id, '_wpshop_variation_defining', true);
					if ( !empty($attributes) && !empty($attributes['attributes']) ) {
						/** Construct an array with all cheaper attributes **/
						foreach( $attributes['attributes'] as $key=>$attribute ) {
							$min_price = 0;
							$first = true;
							foreach( $variations as $k => $variation) {
								if ( !empty($variation['variation_def']) && count($variation['variation_def']) == 1 ) {
									if( array_key_exists($attribute, $variation['variation_def'] ) ) {
										$variation_price = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? $variation['variation_dif']['price_ht'] : !empty( $variation['variation_dif']['product_price'] ) ? $variation['variation_dif']['product_price'] : 0;

										/** Check Discount **/
										$variation_price = ( !empty( $variation['variation_dif']['special_price'] ) && $variation['variation_dif']['special_price'] > 0 ) ? $variation['variation_dif']['special_price'] : $variation_price;
										if( empty($variation['variation_dif']['special_price']) && !empty($variation['variation_dif']['discount_rate']) && $variation['variation_dif']['discount_rate'] > 0) {
											$query = $wpdb->prepare( 'SELECT value FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $variation['variation_dif']['tx_tva'] );
											$tx_tva = $wpdb->get_var( $query );
											$variation_price =  $variation['variation_dif']['price_ht'] / ( 1 + ($variation['variation_dif']['discount_rate'] / 100 ) );
											$variation_price = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? $variation_price : $variation_price * 1 + ($tx_tva / 100);
										}
										elseif( empty($variation['variation_dif']['special_price']) && !empty($variation['variation_dif']['discount_amount']) && $variation['variation_dif']['discount_amount'] > 0 ) {
											$query = $wpdb->prepare( 'SELECT value FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $variation['variation_dif']['tx_tva'] );
											$tx_tva = $wpdb->get_var( $query );
											$variation_price =  $variation['variation_dif']['price_ht'] - $variation['variation_dif']['discount_amount'];
											$variation_price = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? $variation_price : $variation_price * 1 + ($tx_tva / 100);
										}

										/** Check the Min-price **/
										if( $first|| $min_price >= $variation_price ) {
											$first = false;
											$min_price = $variation_price;
											$lower_price_product_combinaison['variations'][$attribute] = $k;
											$lower_price_product_combinaison['variation_priority'] = 'single';
										}
									}
								}
							}
						}
					}
				}
			}
			else {
				if ( !empty($product_id) ) {
					if( !empty($variations) ) {

						foreach( $variations as $variation_id => $variation) {
							if ( !empty($variation['variation_dif']) && !empty($variation['variation_def']) && count($variation['variation_def']) > 1) {
								$variation_price = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? ( ( !empty($variation['variation_dif']['price_ht']) ) ? $variation['variation_dif']['price_ht'] : 0) : ( ( !empty($variation['variation_dif']['product_price']) ) ? $variation['variation_dif']['product_price'] : 0);
								/** Check the Min-price **/
								if( $min_price >= $variation_price || $first ) {
									$min_price = $variation_price;
									$var_id = $variation_id;
								}
							}
							$first  = false;
						}

						if ( !empty($var_id) ) {
							$lower_price_product_combinaison['variations'][] = $var_id;
							$lower_price_product_combinaison['variation_priority'] = 'combined';
						}
					}
				}
			}
			return $lower_price_product_combinaison;
		}

		/**
		 * Calcul discounted price if discount exist
		 * @param array $product
		 * @param array $discount_config
		 * @return array
		 */
		public static function calcul_discounted_price( $product, $discount_config ) {
			$wpshop_price_piloting_option = get_option( 'wpshop_shop_price_piloting');
			if( !empty($discount_config) ) {

				if ( !empty($discount_config['type']) && !empty($discount_config['value']) && $discount_config['type'] == 'special_price' ) {
					$product['price_ht'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $discount_config['value'] : $discount_config['value'] / (1 + $product['tx_tva'] /100);
					$product['product_price'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $discount_config['value'] * (1 + $product['tx_tva'] /100) : $discount_config['value'];
					$product['tva'] = $product['product_price'] - $product['price_ht'];
				}
				elseif( !empty($discount_config['type']) && !empty($discount_config['value']) && $discount_config['type'] == 'discount_amount' ) {
					$product['price_ht'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? ( $product['price_ht'] - $discount_config['value'] ) : ( ( $product['product_price'] - $discount_config['value'] ) / (1 + $product['tx_tva'] /100) ) ;
					$product['product_price'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? $product['price_ht'] * (1 + $product['tx_tva'] /100) : $product['product_price'] - $discount_config['value'];
					$product['tva'] =  $product['product_price'] - $product['price_ht'];

				}
				elseif(!empty($discount_config['type']) && !empty($discount_config['value']) && $discount_config['type'] == 'discount_rate') {
					$product['price_ht'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? ( $product['price_ht'] * ( 1 -  $discount_config['value'] / 100) ) : ( ( $product['product_price']  * ( 1 - ( $discount_config['value'] / 100 ) ) ) / (1 + $product['tx_tva'] /100) ) ;
					$product['product_price'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT' ) ? $product['price_ht'] * ( 1 + $product['tx_tva'] /100) : $product['product_price'] * ( 1 - ( $discount_config['value'] / 100 ) );
					$product['tva'] =   $product['product_price'] - $product['price_ht'];
				}
			}
			return $product;
		}


		/** Check Discount for Product **/
		public static function check_discount_for_product( $product_id, $head_product_id = 0 ) {
			$discount_config = array();
			$time_def = array('0000-00-00 00:00:00', '0000-00-00');

			if( !empty($product_id) ) {
				if ( !empty($head_product_id) ) {
					$product_meta = get_post_meta( $head_product_id, '_wpshop_product_metadata', true );
					$product_discount_date_from = ( !empty($product_meta['special_from']) ) ? $product_meta['special_from'] : 0;
					$product_discount_date_to = ( !empty($product_meta['special_to']) ) ? $product_meta['special_to'] : 0;

					$product_meta = get_post_meta( $product_id, '_wpshop_product_metadata', true );
				}
				else {
					$product_meta = get_post_meta( $product_id, '_wpshop_product_metadata', true );
					$product_discount_date_from = ( !empty($product_meta['special_from']) ) ? $product_meta['special_from'] : 0;
					$product_discount_date_to = ( !empty($product_meta['special_to']) ) ? $product_meta['special_to'] : 0;
				}

				$current_date = date('Y-m-d');

				if ( !empty( $product_meta ) ) {

					if( ( empty($product_discount_date_from) && empty($product_discount_date_to) ) || ( in_array($product_discount_date_from, $time_def)  && in_array( $product_discount_date_to, $time_def) ) || (strtotime($product_discount_date_from) < strtotime($current_date) && strtotime($current_date) < strtotime($product_discount_date_to) ) ) {
						/** Special Price **/
						if ( !empty($product_meta['special_price']) && $product_meta['special_price'] > 0 ) {
							$discount_config['type'] = 'special_price';
							$discount_config['value'] = $product_meta['special_price'];
						}
						elseif( !empty($product_meta['discount_amount']) && $product_meta['discount_amount'] > 0) {
							$discount_config['type'] = 'discount_amount';
							$discount_config['value'] = $product_meta['discount_amount'];
						}
						elseif( !empty($product_meta['discount_rate']) && $product_meta['discount_rate'] > 0 ) {
							$discount_config['type'] = 'discount_rate';
							$discount_config['value'] = $product_meta['discount_rate'];
						}
					}
				}
			}
			return $discount_config;
		}

		/**
		 * Check the parent product price
		 */
		function check_parent_product_price( $product ) {
			$price_infos = array();
			if( !empty($product) ) {
				$price_infos['ati'] = $product['product_price'];
				$price_infos['et'] = $product['price_ht'];
				$price_infos['tva'] = $product['tva'];
				$price_infos['fork_price'] = array( 'have_fork_price' => false, 'min_product_price' => '', 'max_product_price' => '');
			}
			return $price_infos;
		}

		/** Recalculate prices in mass **/
		function mass_update_prices() {
			global $wpdb;
			$status = false; $result = '';
			@ini_set('max_execution_time', '500');
			$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
			$output_type_option = get_option( 'wpshop_display_option' );
			$output_type = $output_type_option['wpshop_display_list_type'];

			/** Get tx_tva attribute_id **/
			$query = $wpdb->prepare( 'SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = %s', 'tx_tva' );
			$tx_tva_attribute_id = $wpdb->get_var( $query );

			/** Product entity Definition **/
			$product_entity = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );

			/** Attributes def **/
			$tva_attribute_def = wpshop_attributes::getElement('tva',"'valid'", 'code');
			$product_price_attribute_def = wpshop_attributes::getElement('product_price',"'valid'", 'code');
			$price_ht_attribute_def = wpshop_attributes::getElement('price_ht',"'valid'", 'code');

			if ( !empty($tx_tva_attribute_id) ) {
				$query = $wpdb->prepare( 'SELECT id, value FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE attribute_id = %d', $tx_tva_attribute_id, 'valid' );
				$tx_tva = $wpdb->get_results( $query );

				$tva_array = array();
				/** Transform array to easy teatment **/
				foreach( $tx_tva as $t ) {
					$tva_array[ $t->id ] = $t->value;
				}

				if ( !empty($tx_tva) ) {

					$count_products = wp_count_posts(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);

					for( $i = 0; $i <= $count_products->publish; $i+= 100 ) {
					$query = $wpdb->prepare( 'SELECT * FROM '. $wpdb->posts .' WHERE post_type = %s AND post_status = %s ORDER BY ID DESC LIMIT '.$i.', 150', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish' );
					$products = $wpdb->get_results( $query );

						if( !empty($products) ){
	 						foreach( $products as $product ) {
								$product_data = get_post_meta( $product->ID, '_wpshop_product_metadata', true);
								if ( !empty($product_data) ) {
									if ( !empty($product_data['tx_tva']) && array_key_exists( $product_data['tx_tva'], $tva_array) ) {

										if ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) {
											/** Update post meta **/
											$product_data['price_ht'] = (float)str_replace( ',', '.', $product_data['price_ht'] );
											$product_data['product_price'] = $product_data['price_ht'] * ( 1 + ($tva_array[ $product_data['tx_tva'] ] / 100) );
											$product_data['tva'] = $product_data['price_ht'] * ( ($tva_array[ $product_data['tx_tva'] ] / 100) );
											update_post_meta( $product->ID, '_wpshop_product_metadata', $product_data);


											/** Update attributes values **/
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => (float)$product_data['price_ht'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $price_ht_attribute_def->id, 'entity_id' => $product->ID) );
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => (float)$product_data['product_price'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $product_price_attribute_def->id, 'entity_id' => $product->ID) );
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => (float)$product_data['tva'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $tva_attribute_def->id, 'entity_id' => $product->ID) );

											/** Update Display price meta **/
											$p = wpshop_products::get_product_data($product->ID);
											$price = wpshop_prices::get_product_price($p, 'just_price_infos', array('mini_output', $output_type) );
											update_post_meta( $product->ID, '_wps_price_infos', $price );
										}
										else {
											/** Update post meta **/
											$product_data['product_price'] = (float)str_replace( ',', '.', $product_data['product_price'] );
											$product_data['price_ht'] = $product_data['product_price'] / ( 1 + ($tva_array[ $product_data['tx_tva'] ] / 100) );
											$product_data['tva'] = $product_data['price_ht'] * ( ($tva_array[ $product_data['tx_tva'] ] / 100) );
											update_post_meta( $product->ID, '_wpshop_product_metadata', $product_data);

											/** Update attributes values **/
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_data['price_ht'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $price_ht_attribute_def->id, 'entity_id' => $product->ID) );
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_data['tva'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $tva_attribute_def->id, 'entity_id' => $product->ID) );
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_data['product_price'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $product_price_attribute_def->id, 'entity_id' => $product->ID) );


											/** Update Display price meta **/
											$p = wpshop_products::get_product_data($product->ID);
											$price = wpshop_prices::get_product_price($p, 'just_price_infos', array('mini_output', $output_type) );
											update_post_meta( $product->ID, '_wps_price_infos', $price );
										}
									}
								}
							}

						}
					}
					unset( $products );

					$count_variations = wp_count_posts(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION);
					for( $i = 0; $i <= $count_variations->publish; $i+= 100 ) {
						$query = $wpdb->prepare( 'SELECT * FROM '. $wpdb->posts .' WHERE post_type = %s AND post_status = %s ORDER BY ID DESC LIMIT '.$i.', 100', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, 'publish' );
						$product_variations = $wpdb->get_results( $query );
						/** Update Products Variations **/
						if ( !empty($product_variations) ) {
							foreach( $product_variations as $product_variation ) {
								$product_data = get_post_meta( $product_variation->ID, '_wpshop_product_metadata', true);
								if ( !empty($product_data) ) {
									if ( !empty($product_data['tx_tva']) && array_key_exists( $product_data['tx_tva'], $tva_array) ) {
										if ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) {
											/** Update post meta **/
											$product_data['price_ht'] = (float)str_replace( ',', '.', $product_data['price_ht'] );
											$product_data['product_price'] = $product_data['price_ht'] * ( 1 + ($tva_array[ $product_data['tx_tva'] ] / 100) );
											$product_data['tva'] = $product_data['price_ht'] * ( ($tva_array[ $product_data['tx_tva'] ] / 100) );
											update_post_meta( $product_variation->ID, '_wpshop_product_metadata', $product_data);

											/** Update attributes values **/
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_data['price_ht'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $price_ht_attribute_def->id, 'entity_id' => $product_variation->ID) );
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_data['product_price'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $product_price_attribute_def->id, 'entity_id' => $product_variation->ID) );
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_data['tva'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $tva_attribute_def->id, 'entity_id' => $product_variation->ID) );
										}
										else {
											/** Update post meta **/
											$product_data['product_price'] = (float)str_replace( ',', '.', $product_data['product_price'] );
											$product_data['price_ht'] = $product_data['product_price'] / ( 1 + ($tva_array[ $product_data['tx_tva'] ] / 100) );
											$product_data['tva'] = $product_data['price_ht'] * ( ($tva_array[ $product_data['tx_tva'] ] / 100) );
											update_post_meta( $product_variation->ID, '_wpshop_product_metadata', $product_data);

											/** Update attributes values **/
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_data['price_ht'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $price_ht_attribute_def->id, 'entity_id' => $product_variation->ID) );
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_data['tva'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $tva_attribute_def->id, 'entity_id' => $product_variation->ID) );
											$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_data['product_price'] ), array('entity_type_id' => $product_entity, 'attribute_id' => $product_price_attribute_def->id, 'entity_id' => $product_variation->ID) );
										}
									}
								}
							}
						}

					}

					$result = __('Prices updated', 'wpshop');
					$status = true;
				}
				else {
					$result = __('No VAT rates was found', 'wpshop');
				}
			}
			else {
				$result = __( 'VAT rate attribute was not found', 'wpshop' );
			}
			return array( $status, $result );
		}

	}

}
/**	Instanciate the module utilities if not	*/
if ( class_exists("wpshop_prices") ) {
	$wpshop_prices = new wpshop_prices();
}
