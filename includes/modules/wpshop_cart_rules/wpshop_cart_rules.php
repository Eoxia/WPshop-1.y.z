<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Cart rules bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wpshop_cart_rules") ) {
	class wpshop_cart_rules {

		function __construct () {
			/**	Add module option to wpshop general options	*/
			add_filter('wpshop_options', array(&$this, 'add_options'), 9);
			add_action('wsphop_options', array(&$this, 'declare_options'));

			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );

			/**	Include the different javascript & style	*/
			//add_action( 'init', array(&$this, 'frontend_js') );
			add_action( 'admin_enqueue_scripts', array(&$this, 'admin_scripts') );

			/** AJAX actions **/
			add_action('wp_ajax_save_cart_rule',array( $this, 'wpshop_ajax_save_cart_rule'));
			add_action('wp_ajax_delete_cart_rule',array( $this, 'wpshop_ajax_delete_cart_rule'));
		}

		/**
		 * Load the different javascript librairies
		 */
		// function frontend_js() {
		// 	/** JS Include **/
		// 	wp_enqueue_script("jquery");
		// }
		/**
		 * Load the different javascript librairies
		 */
		function admin_scripts( $hook ) {
			if ( $hook != 'settings_page_wpshop_option' )
				return;

			/** JS Include **/
			wp_enqueue_script("jquery");
			wp_enqueue_script( 'wpshop_cart_rules', plugins_url('templates/backend/js/wpshop_cart_rules.js', __FILE__) );
			/** CSS Include **/
			wp_register_style( 'wpshop_cart_rules_css', plugins_url('templates/backend/css/wpshop_cart_rules.css', __FILE__) );
			wp_enqueue_style( 'wpshop_cart_rules_css' );
		}

		/** Load the module template **/
		function custom_template_load( $templates ) {
			include('templates/backend/main_elements.tpl.php');

			foreach ( $tpl_element as $template_part => $template_part_content) {
				foreach ( $template_part_content as $template_type => $template_type_content) {
					foreach ( $template_type_content as $template_key => $template) {
						$templates[$template_part][$template_type][$template_key] = $template;
					}
				}
			}
			unset($tpl_element);

			return $templates;
		}

		/** Declare options for this module **/
		function declare_options () {
			if ( WPSHOP_DEFINED_SHOP_TYPE == 'sale' ) {
				$wpshop_shop_type = !empty( $_POST['wpshop_shop_type'] ) ? sanitize_text_field( $_POST['wpshop_shop_type'] ) : '';
				$old_wpshop_shop_type = !empty( $_POST['old_wpshop_shop_type'] ) ? sanitize_text_field( $_POST['old_wpshop_shop_type'] ) : '';

				if ( ( $wpshop_shop_type == '' || $wpshop_shop_type != 'presentation' )
					&& ( $old_wpshop_shop_type == '' || $old_wpshop_shop_type != 'presentation' ) ) {
						add_settings_section('wpshop_cart_rules_option', '<span class="dashicons dashicons-cart"></span>'.__('Cart Rules', 'wpshop'), array(&$this, 'cart_rules_section_text'), 'wpshop_cart_rules_option');
						register_setting('wpshop_options', 'wpshop_cart_rules_option', array(&$this, 'validate_cart_rules_options'));
						add_settings_field('wpshop_cart_rules_option', __('Activate cart rules', 'wpshop'), array(&$this, 'wpshop_cart_rules_field'), 'wpshop_cart_rules_option', 'wpshop_cart_rules_option');

					}
			}
		}

		/** Add a section for option display **/
		function add_options( $option_group ) {
			$option_group['wpshop_cart_option']['subgroups']['wpshop_cart_rules_option']['class'] = ' wpshop_admin_box_options_cart_rules';
			return $option_group;
		}

		function cart_rules_section_text () {

		}

		/** Option cart rules configuuration interface **/
		function wpshop_cart_rules_field () {
			global $wpdb;
			$cart_rules_options = get_option('wpshop_cart_rules_option');
			$cart_rules = ( !empty($cart_rules_options) ) ? $cart_rules_options['rules'] : '';

			$wpshop_customer_groups = get_option('wp_user_roles');

			$tpl_component['CART_RULES_CUSTOMERS_GROUPS'] = '<option value="">' .__('All customers groups' , 'wpshop'). '</option>';
			if ( !empty($wpshop_customer_groups) ) {
				foreach ( $wpshop_customer_groups as $k =>  $wpshop_customer_group) {
					$tpl_component['CART_RULES_CUSTOMERS_GROUPS'] .= '<option value="' .$k. '">' .$wpshop_customer_group['name']. '</option>';
				}
			}

			$tpl_component['CART_RULES_DATA'] = ( !empty($cart_rules) ) ? $cart_rules : '';
			$tpl_component['ACTIVE_CART_RULES'] = ( (!empty($cart_rules_options) && !empty($cart_rules_options['activate']) ) ? 'checked="checked"' : '');

			$tpl_component['ALL_CART_RULES'] = (!empty($cart_rules) ) ? wpshop_cart_rules::display_cart_rules( $cart_rules ) : '';
			$tpl_component['MEDIAS_ICON_URL'] = WPSHOP_MEDIAS_ICON_URL;
			/** Product list **/
			$query = $wpdb->prepare('SELECT ID, post_title, post_type FROM '.$wpdb->posts.' WHERE (post_type = %s OR post_type = %s) AND (post_status = %s OR post_status = %s)', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, 'draft', 'publish');
			$products = $wpdb->get_results($query);
			$tpl_component['PRODUCTS_LIST_FOR_GIFT'] = '';
			if ( !empty($products) ) {
				foreach($products as $product) {
					if ( $product->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
						$parent_product_infos = wpshop_products::get_parent_variation ( $product->ID );
						if ( !empty($parent_product_infos) && !empty($parent_product_infos['parent_post']) ) {
							$parent_post_infos = $parent_product_infos['parent_post'];
							$product_title = $parent_post_infos->post_title;

							$product_options = get_post_meta($product->ID, '_wpshop_variations_attribute_def', true);
							if ( !empty($product_options) && is_array($product_options) ) {
								$option_name = '';
								foreach( $product_options as $k=>$product_option) {
									$query = $wpdb->prepare('SELECT frontend_label FROM '.WPSHOP_DBT_ATTRIBUTE.' WHERE code = %s', $k);
									$option_name .= $wpdb->get_var($query).' ';
									$query = $wpdb->prepare('SELECT label FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE id= %d', $product_option);
									$option_name .=  $wpdb->get_var($query).' ';
								}
								$tpl_component['PRODUCTS_LIST_FOR_GIFT'] .= '<option value="'.$product->ID. '" >'. $product_title .' (' .$option_name. ')</option>';
							}

						}
					}
					else {
						$tpl_component['PRODUCTS_LIST_FOR_GIFT'] .= '<option value="'.$product->ID. '" >' .$product->post_title. '</option>';
					}
				}
			}

			$output = wpshop_display::display_template_element('cart_rules_interface', $tpl_component, array(), 'admin');
			unset($tpl_component);
			echo $output;
		}

		function validate_cart_rules_options ($input) {
			return $input;
		}

		/** Save the cart rule **/
		function wpshop_ajax_save_cart_rule () {
			$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

			if ( !wp_verify_nonce( $_wpnonce, 'wpshop_ajax_save_cart_rule' ) )
				wp_die();

			$cart_limen = ( !empty($_POST['cart_limen']) ) ? wpshop_tools::varSanitizer($_POST['cart_limen']) : null;
			$discount_type = ( !empty($_POST['discount_type']) ) ? wpshop_tools::varSanitizer($_POST['discount_type']) : null;
			$discount_value = ( !empty($_POST['discount_value']) ) ? wpshop_tools::varSanitizer($_POST['discount_value']) : null;
			$customer_groups = wpshop_tools::varSanitizer($_POST['customer_groups']);

			$status = false;
			$response = array();
			$cart_rules = ( !empty($_POST['cart_rules']) ) ? sanitize_text_field( $_POST['cart_rules'] ) : null;

			if ( !empty($cart_limen) && !empty($discount_type) && !empty($discount_value) ) {
				if ( !empty($cart_rules) ) {
					$cart_rules = unserialize(stripslashes($cart_rules));
					$cart_rules[$cart_limen] = array('discount_type' => $discount_type, 'discount_value' => $discount_value, 'customer_group' => $customer_groups);

				}
				else {
					$cart_rules = array();
					$cart_rules[$cart_limen] = array('discount_type' => $discount_type, 'discount_value' => $discount_value, 'customer_group' => $customer_groups);
				}
				$cart_rules = serialize($cart_rules);
				$status = true;
			}

			$display_rules = wpshop_cart_rules::display_cart_rules($cart_rules);
			$reponse = array('status' => $status, 'response' => $cart_rules, 'display_rules' => $display_rules);
			echo json_encode($reponse);
			die();
		}

		/** Save the cart rule **/
		function wpshop_ajax_delete_cart_rule () {
			$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

			if ( !wp_verify_nonce( $_wpnonce, 'wpshop_ajax_delete_cart_rule' ) )
				wp_die();

			$cart_rule_id = ( !empty($_POST['cart_rule_id']) ) ? wpshop_tools::varSanitizer($_POST['cart_rule_id']) : null;

			$status = false;
			$response = array();
			$cart_rules = ( !empty($_POST['cart_rules']) ) ? sanitize_text_field( $_POST['cart_rules'] ) : null;

			$cart_rule_id = str_replace('_', '.', $cart_rule_id);

			if ( !empty($cart_rule_id) ) {
				$cart_rules = unserialize(stripslashes($cart_rules));
				unset($cart_rules[$cart_rule_id]);
				$cart_rules = serialize($cart_rules);
				$status = true;
			}
			$display_rules = wpshop_cart_rules::display_cart_rules($cart_rules);

			$display_rules = wpshop_cart_rules::display_cart_rules($cart_rules);
			$reponse = array('status' => $status, 'response' => $cart_rules, 'display_rules' => $display_rules);
			echo json_encode($reponse);
			die();
		}

		/** Display all cart rules **/
		function display_cart_rules( $rules ) {
			global $wpdb;
			$output = '';
			if ( !empty($rules) ) {
				$tpl_component['MEDIAS_ICON_URL'] = WPSHOP_MEDIAS_ICON_URL;
				$tpl_component['CART_RULES_LINE'] = '';
				foreach( unserialize($rules) as $k => $rule ) {
					$sub_tpl_component['CART_RULE_LINE_CART_LIMEN'] = $k;
					switch ($rule['discount_type']) {
						case 'absolute_discount' :
							$discount_type = __('Absolute discount', 'wpshop');
							$discount_value = $rule['discount_value'].' '.wpshop_tools::wpshop_get_currency();
						break;
						case 'percent_discount' :
							$discount_type = __('Percent discount', 'wpshop');
							$discount_value = $rule['discount_value'].' %';
						break;
						case 'gift_product' :
							$discount_type = __('Product gift', 'wpshop');
							$product = get_post( $rule['discount_value'] );
							if ( $product->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
								$parent_product_infos = wpshop_products::get_parent_variation ( $product->ID );
								if ( !empty($parent_product_infos) && !empty($parent_product_infos['parent_post']) ) {
									$parent_post_infos = $parent_product_infos['parent_post'];
									$product_title = $parent_post_infos->post_title;

									$product_options = get_post_meta($product->ID, '_wpshop_variations_attribute_def', true);
									if ( !empty($product_options) && is_array($product_options) ) {
										$option_name = '';
										foreach( $product_options as $k=>$product_option) {
											$query = $wpdb->prepare('SELECT frontend_label FROM '.WPSHOP_DBT_ATTRIBUTE.' WHERE code = %s', $k);
											$option_name .= $wpdb->get_var($query).' ';
											$query = $wpdb->prepare('SELECT label FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE id= %d', $product_option);
											$option_name .=  $wpdb->get_var($query).' ';
										}
										$discount_value = $product_title .' (' .$option_name. ')';
									}

								}
							}
							else {
								$discount_value = $product->post_title;
							}
						break;
						default :
							$discount_type = '';
							$discount_value = $rule['discount_value'];
						break;
					}
					$sub_tpl_component['CART_RULE_LINE_DISCOUNT_TYPE'] = $discount_type;
					$sub_tpl_component['CART_RULE_LINE_DISCOUNT_VALUE'] = $discount_value;
					$sub_tpl_component['CART_RULE_LINE_CUSTOMER_GROUP'] = (!empty($rule['customer_group']) ) ? $rule['customer_group'] : __('All customers groups', 'wpshop');
					$sub_tpl_component['CART_RULE_ID'] = str_replace('.', '_', $sub_tpl_component['CART_RULE_LINE_CART_LIMEN']);
					$sub_tpl_component['MEDIAS_ICON_URL'] = WPSHOP_MEDIAS_ICON_URL;
					$tpl_component['CART_RULES_LINE'] .= wpshop_display::display_template_element('cart_rules_line', $sub_tpl_component, array(), 'admin');
					unset($sub_tpl_component);
				}
				$output = wpshop_display::display_template_element('cart_rules_display', $tpl_component, array(), 'admin');
			}
			return $output;
		}

		/**
		 * Check if a cart rule exist for a cart amount
		 * @param integer_type $cart_amount
		 * @return array
		 */
		public static function get_cart_rule ($cart_amount) {
			$cart_rule_info = array();
			$cart_rule_exist = false;
			if ( !empty($cart_amount) ) {
				$cart_rules_option = get_option('wpshop_cart_rules_option');
				if ( !empty($cart_rules_option) && !empty($cart_rules_option['activate']) && !empty($cart_rules_option['rules']) ) {
					$cart_rules = unserialize($cart_rules_option['rules']);
					if ( is_array($cart_rules) ) {
						$cart_rule_id = 0;
						foreach( $cart_rules as $k => $cart_rule ) {
							if ( $cart_amount >= $k ) {
								$cart_rule_id = $k;
								if ( $cart_rule_id != 0 ) {
									/** Check if there is a customer group limit **/
									if ( empty($cart_rules[$cart_rule_id]['customer_group']) ) {
										$cart_rule_exist = true;
										$cart_rule_info['discount_type'] = $cart_rules[$cart_rule_id]['discount_type'];
										$cart_rule_info['discount_value'] = $cart_rules[$cart_rule_id]['discount_value'];
									}
									else {
										if ( get_current_user_id() != 0 ) {
											$user_meta = get_user_meta( get_current_user_id(), 'wp_capabilities', true );
											if ( !empty($user_meta)  ) {
												foreach ($user_meta as $k => $user) {
													if ( $k == $cart_rules[$cart_rule_id]['customer_group'] ) {
														$cart_rule_exist = true;
														$cart_rule_info['discount_type'] = $cart_rules[$cart_rule_id]['discount_type'];
														$cart_rule_info['discount_value'] = $cart_rules[$cart_rule_id]['discount_value'];
													}
												}
											}
										}
									}

								}
							}
						}
					}
				}
			}
			return array('cart_rule_exist' => $cart_rule_exist, 'cart_rule_info' => $cart_rule_info);
		}

		function add_gift_product_to_cart ( $cartContent, $order ) {
			global $wpdb;
			if ( !empty($order['cart_rule']) && !empty($order['cart_rule']['discount_value']) && !empty($order['cart_rule']['discount_type']) &&  $order['cart_rule']['discount_type'] == 'gift_product') {
				$product = get_post( $order['cart_rule']['discount_value'] );
				$option_name = '';
				if ( $product->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
					$parent_product_infos = wpshop_products::get_parent_variation ( $product->ID );
					if ( !empty($parent_product_infos) && !empty($parent_product_infos['parent_post']) ) {
						$parent_post_infos = $parent_product_infos['parent_post'];
						$product_title = $parent_post_infos->post_title;

						$product_options = get_post_meta($product->ID, '_wpshop_variations_attribute_def', true);
						if ( !empty($product_options) && is_array($product_options) ) {
							$option_name = '';
							foreach( $product_options as $k=>$product_option) {
								$query = $wpdb->prepare('SELECT frontend_label FROM '.WPSHOP_DBT_ATTRIBUTE.' WHERE code = %s', $k);
								$option_name .= $wpdb->get_var($query).' ';
								$query = $wpdb->prepare('SELECT label FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE id= %d', $product_option);
								$option_name .=  $wpdb->get_var($query).' ';
							}
							$discount_value = $product_title ;
						}

					}
				}
				else {
					$discount_value = $product->post_title;
				}

				$tpl_component['CART_PRODUCT_MORE_INFO'] = $option_name;
				$tpl_component['CART_LINE_ITEM_ID'] = $order['cart_rule']['discount_value'];
				$tpl_component['CART_LINE_ITEM_PUHT'] = number_format(0, 2);
				$tpl_component['CART_LINE_ITEM_DISCOUNT_AMOUNT'] = number_format(0, 2);
				$tpl_component['CART_LINE_ITEM_TPHT'] = number_format(0, 2);
				$tpl_component['CART_LINE_ITEM_TPTTC'] = number_format(0, 2);

				$tpl_component['CART_LINE_ITEM_QTY_'] = 1;
				$tpl_component['CART_LINE_ITEM_REMOVER'] = '';
				$tpl_component['CART_PRODUCT_NAME'] = $discount_value .' ('.__('Gift product', 'wpshop').')';


				$cartContent .= wpshop_display::display_template_element('cart_line', $tpl_component);

			}
			return $cartContent;
		}
	}
}
/**	Instanciate the module utilities if not	*/
if ( class_exists("wpshop_cart_rules") ) {
	$wpshop_cart_rules = new wpshop_cart_rules();
}
