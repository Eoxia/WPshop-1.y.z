<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * WP Shop Credit bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !class_exists('wps_credit') ) {
	class wps_credit {
		function __construct() {
			/** Template Load **/
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );

			/**	Include the different javascript	*/
			add_action( 'admin_enqueue_scripts', array(&$this, 'admin_js') );

			/** Ajax actions **/
			add_action( 'wp_ajax_wps_credit_make_credit', array( &$this, 'wps_credit_make_credit_interface'));
			add_action( 'wp_ajax_wps_make_credit_action', array( &$this, 'wps_make_credit_action') );
			add_action( 'wp_ajax_wps_credit_change_status', array( &$this, 'wps_credit_change_status') );

			// Filter
			add_filter( 'wps_order_saving_admin_extra_action', array( $this, 'wps_credit_actions_on_order_save'), 10, 2 );

			/** Credit slip Page **/
			add_action( 'admin_post_wps_credit_slip', array( $this, 'wps_credit_slip_output' ) );
		}

		/**
		 * Include stylesheets
		 */
		function admin_js() {
			global $current_screen;
		    if ( ! in_array( $current_screen->post_type, array( WPSHOP_NEWTYPE_IDENTIFIER_ORDER ), true ) )
		        return;

			add_thickbox();
			wp_enqueue_script( 'wps_credit', plugins_url('templates/backend/js/wps_credit.js', __FILE__), array( "jquery" ) );
		}

		/** Load module/addon automatically to existing template list
		*
		* @param array $templates The current template definition
		*
		* @return array The template with new elements
		*/
		function custom_template_load( $templates ) {
			include('templates/backend/main_elements.tpl.php');
			$wpshop_display = new wpshop_display();
			$templates = $wpshop_display->add_modules_template_to_internal( $tpl_element, $templates );
			unset($tpl_element);

			return $templates;
		}

		/** Credit Meta Box **/
		public static function wps_credit_meta_box() {
			global $post;
			$output = '';
			if ( !empty($post) && !empty($post->ID) ) {
				$order_meta = get_post_meta( $post->ID, '_order_postmeta', true );
				if( !empty($order_meta) && !empty($order_meta['order_status']) && $order_meta['order_status'] != 'awaiting_payment' ) {
					$output .= '<div id="wps_credit_list_container">';
					$output .= self::display_credit_list($post->ID);
					$output .= '</div>';
				}
				$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wps_credit_make_credit&oid='.$post->ID ), 'wps_credit_make_credit_interface', '_wpnonce' );
				$output .= '<a href="' . $url . '" id="make_credit_button" class="thickbox button">' . __('Make a credit', 'wpshop') . '</a>';
				$output .= '<img src="' .WPSHOP_LOADING_ICON. '" alt="' .__('Loading', 'wpshop'). '" class="wpshopHide" id="change_credit_status_loader" />';
			}
			echo $output;
		}

		/** Create a credit **/
		function create_an_credit( $order_id, $product_list = array(), $credit_statut = 'not_paid', $credit_customer_account = '', $products_list_to_restock = array() ) {
			$status = false;
			if ( !empty($order_id) ) {
				$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
				$order_credits = get_post_meta( $order_id, '_wps_order_credit', true );
				$order_meta = get_post_meta( $order_id, '_order_postmeta', true );

				if ( empty($product_list) ) {
					if ( !empty($order_meta) && !empty($order_meta['order_items']) ) {
						$credit_def = array();
						$credit_def['ref'] = self::generate_credit_slip_number( $order_id );
						$credit_def['credit_status'] = 'not_paid';
						$credit_def['items'] = array();
						$credit_total_amount = 0;
						foreach( $order_meta['order_items'] as $item_id => $item ) {
							if ( !empty($item_id) && !empty($item) ) {
								$credit_def['items'][ $item_id ] = $item;
								$credit_total_amount += $credit_def['items'][ $item_id ]['item_total_ttc'];
							}
						}
						if ( !empty($order_meta['order_shipping_cost']) ) {
							$credit_def['items'][ 'shipping_cost' ][ 'item_qty' ] = 1;
							$credit_def['items'][ 'shipping_cost' ][ 'item_total_ht' ] = $credit_def['items'][ 'shipping_cost' ]['item_pu_ht'] = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? $order_meta['order_shipping_cost'] : $order_meta['order_shipping_cost'] / ( 1 + ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) );
							$credit_def['items'][ 'shipping_cost' ][ 'item_total_ttc' ] = $credit_def['items'][ 'shipping_cost' ]['item_pu_ttc'] = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? $order_meta['order_shipping_cost'] * ( 1 + ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) ) : $order_meta['order_shipping_cost'];
							$credit_def['items'][ 'shipping_cost' ][ 'item_tva_amount' ] = $credit_def['items'][ 'shipping_cost' ]['item_tva_total_amount'] = $credit_def['items'][ 'shipping_cost' ]['item_pu_ttc'] - $credit_def['items'][ 'shipping_cost' ]['item_pu_ht'];
							$credit_def['items'][ 'shipping_cost' ][ 'item_name' ] = __('Shipping cost', 'wpshop');
							$credit_def['items'][ 'shipping_cost' ][ 'item_tva_rate' ] = WPSHOP_VAT_ON_SHIPPING_COST;
						}
					}
					$d = array();
					$d[] = $credit_def;
					update_post_meta( $order_id, '_wps_order_credit', $d );
				}
				else {
					if ( empty($order_credits) ) {
						$order_credits = array();
					}
					$credit_def = array();
					$credit_def['credit_date'] = current_time('mysql', 0);
					$credit_def['ref'] = self::generate_credit_slip_number( $order_id );
					$credit_def['credit_status'] = $credit_statut;
					$credit_def['items'] = array();
					$credit_total_amount = 0;
					foreach( $product_list as $product_id => $product ) {
						if ( !empty($order_meta) && !empty($order_meta['order_items']) && !empty($order_meta['order_items'][ $product_id ]) ) {
							$credit_def['items'][ $product_id ] = $order_meta['order_items'][ $product_id ];
							/** Check Price & Quantity **/
							if ( !empty($product['qty']) && ($product['qty'] != $credit_def['items'][ $product_id ]['item_qty']) ) {
								$credit_def['items'][ $product_id ]['item_qty'] =  $product['qty'];
								$credit_def['items'][ $product_id ]['item_total_ht'] = $credit_def['items'][ $product_id ]['item_pu_ht'] * $credit_def['items'][ $product_id ]['item_qty'];
								$credit_def['items'][ $product_id ]['item_total_ttc'] = ( $credit_def['items'][ $product_id ]['item_pu_ht'] * ( 1 + ($credit_def['items'][ $product_id ]['item_tva_rate'] / 100) ) ) * $credit_def['items'][ $product_id ]['item_qty'];
								$credit_def['items'][ $product_id ]['item_tva_total_amount'] = ( $credit_def['items'][ $product_id ]['item_pu_ht'] * ( $credit_def['items'][ $product_id ]['item_tva_rate'] / 100) ) * $credit_def['items'][ $product_id ]['item_qty'];
							}
							if ( !empty($product['price']) && ($product['price'] != ( ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? $credit_def['items'][ $product_id ]['item_pu_ht'] : $credit_def['items'][ $product_id ]['item_qty'] ) ) ) {
								$credit_def['items'][ $product_id ]['item_pu_ht'] = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? (  $product['price'] / ( 1 + ($credit_def['items'][ $product_id ]['item_tva_rate'] / 100) ) ) : $product['price'];
								$credit_def['items'][ $product_id ]['item_pu_ttc'] = $credit_def['items'][ $product_id ]['item_pu_ht'] * ( 1 + ($credit_def['items'][ $product_id ]['item_tva_rate'] / 100) );
								$credit_def['items'][ $product_id ]['item_tva_amount'] = $credit_def['items'][ $product_id ]['item_pu_ht'] * ( 1 + ($credit_def['items'][ $product_id ]['item_tva_rate'] / 100) );

								/** Total **/
								$credit_def['items'][ $product_id ]['item_total_ht'] = $credit_def['items'][ $product_id ]['item_pu_ht'] * $credit_def['items'][ $product_id ]['item_qty'];
								$credit_def['items'][ $product_id ]['item_total_ttc'] = ( $credit_def['items'][ $product_id ]['item_pu_ht'] * ( 1 + ($credit_def['items'][ $product_id ]['item_tva_rate'] / 100) ) ) * $credit_def['items'][ $product_id ]['item_qty'];
								$credit_def['items'][ $product_id ]['item_tva_total_amount'] = ( $credit_def['items'][ $product_id ]['item_pu_ht'] * ( $credit_def['items'][ $product_id ]['item_tva_rate'] / 100) ) * $credit_def['items'][ $product_id ]['item_qty'];
							}
						}

						/** Shipping Cost Include **/
						if ( $product_id == 'shipping_cost') {
							$credit_def['items'][ $product_id ][ 'item_qty' ] = 1;
							$credit_def['items'][ $product_id ][ 'item_total_ht' ] = $credit_def['items'][ $product_id ]['item_pu_ht'] = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? $product['price'] : $product['price'] / ( 1 + ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) );
							$credit_def['items'][ $product_id ][ 'item_total_ttc' ] = $credit_def['items'][ $product_id ]['item_pu_ttc'] = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? $product['price'] * ( 1 + ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) ) : $product['price'];
							$credit_def['items'][ $product_id ][ 'item_tva_amount' ] = $credit_def['items'][ $product_id ]['item_tva_total_amount'] = $credit_def['items'][ $product_id ]['item_pu_ttc'] - $credit_def['items'][ $product_id ]['item_pu_ht'];
							$credit_def['items'][ $product_id ][ 'item_name' ] = __('Shipping cost', 'wpshop');
							$credit_def['items'][ $product_id ][ 'item_tva_rate' ] = WPSHOP_VAT_ON_SHIPPING_COST;
						}

						$credit_total_amount += $credit_def['items'][ $product_id ]['item_total_ttc'];

						if ( !empty($products_list_to_restock) ) {
							if ( array_key_exists( $product_id, $products_list_to_restock) ) {
								/** Check Post type to know if product is a variation **/
								$product_quantity = $credit_def['items'][ $product_id ][ 'item_qty' ];
								$item_post_type = get_post_type( $product_id );
								if ( $item_post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ){
									$parent_product_def = wpshop_products::get_parent_variation( $product_id );
									$parent_post = $parent_product_def['parent_post'];
									if ( !empty($parent_post) ) {
										$product_id = $parent_post->ID;
									}
								}
								self::restock_product_after_credit( $product_id, $product_quantity );
							}
						}

					}
					$credit_def['total_credit'] = $credit_total_amount;
					$order_credits[] = $credit_def;
					update_post_meta( $order_id, '_wps_order_credit', $order_credits );

					if ( !empty($credit_customer_account) ) {
						$user_metadata = get_user_meta( $order_meta['customer_id'], '_wps_credit_amount', true);
						if ( empty($user_metadata) ) {
							$user_metadata = 0;
						}
						$user_metadata += $credit_def['total_credit'];
						update_user_meta( $order_meta['customer_id'], '_wps_credit_amount', $user_metadata );
					}
				}

				$status = true;
			}
			return $status;
		}

		/**
		 * Generate a new Credit Slip Number
		 * @param int $order_id
		 * @return string
		 */
		function generate_credit_slip_number( $order_id ) {
			/**	Get configuration about the number of figure dor invoice number	*/
			$number_figures = get_option('wpshop_credit_slip_number_figures', false);

			/** If the number doesn't exist, we create a default one */
			if(!$number_figures) {
				update_option('wpshop_credit_slip_number_figures', 5);
				$number_figures = 5;
			}

			/**	Get last invoice number	*/
			$credit_slip_number = get_option('wpshop_credit_slip_current_number', false);

			/** If the counter doesn't exist, we initiate it */
			if (!$credit_slip_number) {
				$credit_slip_number = 1;
			}
			else {
				$credit_slip_number++;
			}
			update_option('wpshop_credit_slip_current_number', $credit_slip_number);
			/**	Create the new invoice number with all parameters viewed above	*/
			$invoice_ref = WPSHOP_CREDIT_SLIP_REFERENCE_PREFIX. ((string)sprintf('%0'.$number_figures.'d', $credit_slip_number));
			return $invoice_ref;
		}

		/** Display Credit List **/
		public static function display_credit_list( $order_id ) {
			if ( !empty($order_id) ) {
				$credit_meta = get_post_meta($order_id, '_wps_order_credit', true );
				$credit_list = '';
				if ( !empty($credit_meta) ) {
					foreach( $credit_meta as $credit ) {
						$tpl_component = array();
						$tpl_component['CREDIT_DATE'] = ( !empty($credit['credit_date']) ) ? $credit['credit_date'] : '';
						$tpl_component['CREDIT_REF'] = $credit['ref'];
						$tpl_component['CREDIT_STATUS_ELEMENTS'] = '<option value="not_paid" ' .( ( !empty($credit['credit_status']) && $credit['credit_status'] == 'not_paid') ? 'selected="selected"' : ''). '>' .__('Not paid', 'wpshop'). '</option>';
						$tpl_component['CREDIT_STATUS_ELEMENTS'] .= '<option value="paid" ' .( ( !empty($credit['credit_status']) && $credit['credit_status'] == 'paid') ? 'selected="selected"' : ''). '>' .__('Paid', 'wpshop'). '</option>';
						$tpl_component['CREDIT_STATUS'] = ( !empty($credit['credit_status']) ) ? $credit['credit_status'] : '';
						$tpl_component['CREDIT_STATUS_ICON'] = ( !empty($credit['credit_status']) && $credit['credit_status'] == 'paid' ) ? 'wpshop_order_payment_received_icon' : 'wpshop_order_incorrect_amount_icon';
						$tpl_component['CREDIT_PDF_LINK'] = '<a href="' .admin_url( 'admin-post.php?action=wps_credit_slip&order_id=' .$order_id. '&credit_ref=' .$credit['ref'] ). '" target="_blank">' .$credit['ref']. '</a>';
						$tpl_component['CREDIT_PDF_LINK'] .= ' | <a href="' .admin_url( 'admin-post.php?action=wps_credit_slip&order_id=' .$order_id. '&credit_ref=' .$credit['ref']. '&mode=pdf' ). '" target="_blank">PDF</a>';
						$credit_list .=  wpshop_display::display_template_element('wps_credit_list_element', $tpl_component, array(), 'admin');
					}
					$output = wpshop_display::display_template_element('wps_credit_list', array( 'WPS_CREDIT_LIST_ELEMENTS' => $credit_list ), array(), 'admin');
				}
				else {
					$output = __('No credit is associated to this order', 'wpshop');
				}
			}
			return $output;
		}

		/** Display Configuration interface to make credit **/
		function wps_credit_make_credit_interface() {
			$_wpnonce = !empty( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';

			if ( !wp_verify_nonce( $_wpnonce, 'wps_credit_make_credit_interface' ) )
				wp_die();

			$order_id = ( !empty($_REQUEST['oid']) ) ? wpshop_tools::varSanitizer($_REQUEST['oid']) : null;
			$tab_lines = '';
			$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
			if ( !empty($order_id) ) {
				$order_meta = get_post_meta( $order_id, '_order_postmeta', true );
				$credit_meta = get_post_meta( $order_id, '_wps_order_credit', true );
				if ( !empty($order_meta) && !empty($order_meta['order_items']) ) {

					$items = self::check_existing_credits( $order_id, $order_meta['order_items'] );
					if ( !empty($items) ) {
						foreach( $items as $item_id => $item  ) {
							$tpl_component['ITEM_ID'] = $item_id;
							$tpl_component['ITEM_NAME'] = $item['item_name'];
							$tpl_component['ITEM_QTY'] = $item['item_qty'];
							$tpl_component['ITEM_PRICE'] = number_format( (( !empty($price_piloting_option) && $price_piloting_option == 'HT') ? $item['item_pu_ht'] : $item['item_pu_ttc']), 2, '.', '' );
							$tab_lines .= wpshop_display::display_template_element('wps_credit_items_table_line', $tpl_component, array(), 'admin');
							unset( $tpl_component );

						}
						$tpl_component['TABLE_LINES'] = $tab_lines;
						$tpl_component['ORDER_ID'] = $order_id;
						$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
						$output = wpshop_display::display_template_element('wps_credit_items_table', $tpl_component, array(), 'admin');

						unset( $tpl_component );
					}
					else {
						$output .= __('All products of this order has been credited', 'wpshop' );
					}
				}
				else {
					$output = __('order informations are not available. You can\'t make a credit.', 'wpshop');
				}
			}
			else {
				$output = __('order ID is not defined. You can\t make a credit.', 'wpshop');
			}
			echo $output;
			die();
		}

		/** Make a credit action Ajax Form **/
		function wps_make_credit_action() {
			$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

			if ( !wp_verify_nonce( $_wpnonce, 'wps_make_credit_action' ) )
				wp_die();

			$status = false; $result = '';
			$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
			$product_list_to_return = array();
			$order_id = ( !empty($_POST['order_id']) ) ? wpshop_tools::varSanitizer( $_POST['order_id'] ) : null;
			$wps_credit_return = !empty( $_POST['wps_credit_return'] ) ? (array) $_POST['wps_credit_return'] : array();
			$wps_credit_item_quantity = !empty( $_POST['wps_credit_item_quantity'] ) ? (array) $_POST['wps_credit_item_quantity'] : array();
			$wps_credit_item_price = !empty( $_POST['wps_credit_item_price'] ) ? (array) $_POST['wps_credit_item_price'] : array();
			if ( !empty($order_id) ) {
				if( !empty($wps_credit_return) ) {
					$order_postmeta = get_post_meta( $order_id, '_order_postmeta', true );
					if ( !empty($order_postmeta) && $order_postmeta['order_items'] ) {
						if ( !empty( $wps_credit_return ) && is_array($wps_credit_return) ) {
							foreach( $wps_credit_return as $item_key => $returned_item ) {
	 							if ( !empty( $wps_credit_item_quantity[$item_key] ) && $wps_credit_item_quantity[$item_key] <= $order_postmeta['order_items'][$item_key]['item_qty'] ) {
	 								if ( !empty( $wps_credit_item_price[$item_key] ) && $wps_credit_item_price[$item_key] <= ( ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? $order_postmeta['order_items'][$item_key]['item_pu_ht'] : $order_postmeta['order_items'][$item_key]['item_pu_ttc'] ) ){
	 									$product_list_to_return[ $item_key ]['qty'] = sanitize_key( $wps_credit_item_quantity[$item_key] );
	 									$product_list_to_return[ $item_key ]['price'] = sanitize_key( $wps_credit_item_price[$item_key] );
	 								}
	 								else {
	 									$result = __( 'You try to return a product more expensive than what was purchased', 'wpshop' );
	 								}
	 							}
	 							else {
	 								$result = __( 'You try to return more quantity than what was purchased', 'wpshop' );
	 							}
							}

							$wps_credit_shipping_cost = !empty( $_POST['wps_credit_shipping_cost'] ) ? sanitize_text_field( $_POST['wps_credit_shipping_cost'] ) : '';
							if( !empty( $wps_credit_shipping_cost ) ) {
								$product_list_to_return['shipping_cost']['price'] = $order_postmeta['order_shipping_cost'];
							}

							/** Check if Returned product is already in a credit **/
							if ( !empty($product_list_to_return) ) {
								/** Check restock Item **/
								$products_list_to_restock = array();
								$wps_credit_restock = !empty( $_POST['wps_credit_restock'] ) ? sanitize_text_field( $_POST['wps_credit_restock'] ) : '';
								if ( !empty($wps_credit_restock) ) {
									$products_list_to_restock = $wps_credit_restock;
								}
								$wps_credit_status = !empty( $_POST['wps_credit_status'] ) ? sanitize_text_field( $_POST['wps_credit_status'] ) : '';
								$wps_add_credit_value = !empty( $_POST['wps_add_credit_value'] ) ? sanitize_text_field( $_POST['wps_add_credit_value'] ) : '';
								$credit_status = $wps_credit_status;
								$add_credit_value = $wps_add_credit_value;
								$status = self::create_an_credit( $order_id, $product_list_to_return, $credit_status, $add_credit_value, $products_list_to_restock );
							}


							if ( $status ) {
								$result = self::display_credit_list( $order_id );
							}
						}
					}
				}
				else {
					$result = __('No product has been selected for credit', 'wpshop');
				}
			}
			$response = array( $status, $result );
			echo json_encode( $response );
			die();
		}

		/**
		 * Restock the product after credit
		 * @param int $product_id
		 * @param int $product_qty
		 */
		function restock_product_after_credit( $product_id, $product_qty ) {
			global $wpdb;
			$stock_attribute_def = wpshop_attributes::getElement('product_stock', '"valid"', 'code');
			$product_type_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			if ( !empty($product_id) && !empty($product_qty) ) {
				$product_postmeta = get_post_meta( $product_id, '_wpshop_product_metadata', true);
				$product_postmeta['product_stock'] = str_replace(',','.', $product_postmeta['product_stock']) + $product_qty;
				update_post_meta( $product_id, '_wpshop_product_metadata', $product_postmeta);
				$wpdb->update( WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $product_postmeta['product_stock']), array('entity_type_id' => $product_type_entity_id, 'attribute_id' => $stock_attribute_def->id, 'entity_id' => $product_id) );
			}
		}


		/** Check all returned products **/
		function check_existing_credits( $order_id, $items ) {
			$credit_meta = get_post_meta( $order_id, '_wps_order_credit', true);
			if ( !empty($credit_meta) ) {
				$count = 0;
				foreach( $items as $item_id => $item ) {
					foreach( $credit_meta as $credit ) {
						if ( !empty($credit['items']) ) {
							if ( array_key_exists( $item_id, $credit['items'] ) ) {
								$count += $credit['items'][ $item_id ]['item_qty'];
							}
						}
					}
					if ( $count > 0 ) {
						if( $count >= $item['item_qty'] ) {
							unset( $items[$item_id] );
						}
						else {
							$items[$item_id]['item_qty'] -= $count;
						}
					}
				}
			}
			return $items;
		}

		/** Credit Slip Generation **/
		static function generate_credit_slip( $order_id, $credit_ref ) {
			$order_meta = get_post_meta( $order_id, '_order_postmeta', true);
			$credit_meta = get_post_meta( $order_id, '_wps_order_credit', true );
			$price_piloting = get_option( 'wpshop_shop_price_piloting' );

			if ( !empty($credit_meta) ) {
				foreach( $credit_meta as $id => $credit_def ) {
					if ( $credit_def['ref'] == $credit_ref ) {
						$credit = $credit_meta[ $id ];
					}
				}
			}


			$credit_date = ( !empty($credit) && !empty($credit['credit_date']) ) ? $credit['credit_date'] : '';
			$logo_options = get_option('wpshop_logo');
			$tpl_component['INVOICE_SUMMARY_MORE'] = '';
			$tpl_component['INVOICE_LOGO'] = ( !empty($logo_options) ) ? '<img src="' .$logo_options .'" alt="" />' : '';
			$tpl_component['INVOICE_ORDER_KEY_INDICATION'] = sprintf( __('Correction on order n. %s', 'wpshop'), $order_meta['order_key'] );
			$tpl_component['INVOICE_ORDER_DATE_INDICATION'] = sprintf( __('Credit slip date %s', 'wpshop'), $credit_date ) ;
			$tpl_component['INVOICE_VALIDATE_TIME'] = '';
			$tpl_component['IBAN_INFOS'] = '';


			$tpl_component['AMOUNT_INFORMATION'] = sprintf( __('Amount are shown in %s', 'wpshop'), wpshop_tools::wpshop_get_currency( true ) );

			/** Header **/
			$tpl_component['INVOICE_TITLE'] = __('Credit slip', 'wpshop' );
			$tpl_component['INVOICE_ORDER_INVOICE_REF'] = $credit_ref;

			$tpl_component['INVOICE_SENDER'] = wpshop_modules_billing::generate_invoice_sender_part();
			$tpl_component['INVOICE_RECEIVER'] = wpshop_modules_billing::generate_receiver_part( $order_id );

			/** Tab **/
			$tpl_component['INVOICE_HEADER'] = wpshop_display::display_template_element('credit_slip_row_header', array(), array(), 'common');

			/** Rows **/
			$tpl_component['INVOICE_ROWS'] = '';
			$total_HT = $total_TTC = 0; $credit_TVA = array();
			if ( !empty($credit['items']) ) {
				foreach( $credit['items'] as $item ) {
					if( __( $item['item_name'], 'wpshop' ) != __( 'Shipping cost', 'wpshop' ) ) {
						if( $price_piloting == 'HT' ) {
							$item['item_total_ht'] = $item['item_total_ttc'];
							$item['item_total_ttc'] = $item['item_total_ht'] * ( 1 + ( $item['item_tva_rate'] / 100 ) );
						} else {
							$item['item_total_ttc'] = $item['item_total_ht'];
							$item['item_total_ht'] = $item['item_total_ttc'] / ( 1 + ( $item['item_tva_rate'] / 100 ) );
						}
						$item['item_tva_total_amount'] = $item['item_total_ttc'] - $item['item_total_ht'];
						$item['item_total_ttc'] = $item['item_total_ttc'] / 100;
						$item['item_total_ht'] = $item['item_total_ht'] / 100;
						$item['item_tva_total_amount'] = $item['item_tva_total_amount'] / 100;
					}
					$sub_tpl_component = array();
					$sub_tpl_component['INVOICE_ROW_ITEM_NAME'] = $item['item_name'];
					$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_HT'] = '-'.number_format( $item['item_total_ht'], 2, '.', '' );
					$sub_tpl_component['INVOICE_ROW_ITEM_TVA_TOTAL_AMOUNT'] = '-'.number_format( $item['item_tva_total_amount'], 2, '.', '' ).' ('.$item['item_tva_rate'].'%)';
					$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_TTC'] = '-'.number_format( $item['item_total_ttc'], 2, '.', '' );

					$total_HT += $item['item_total_ht'];
					$total_TTC += $item['item_total_ttc'];

					if ( empty($credit_TVA[(string)$item['item_tva_rate']]) ) {
						$credit_TVA[$item['item_tva_rate']] = $item['item_tva_total_amount'];
					}
					else {
						$credit_TVA[(string)$item['item_tva_rate']] += $item['item_tva_total_amount'];
					}


					$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('credit_slip_row', $sub_tpl_component, array(), 'common');
					unset( $sub_tpl_component );
				}

			}

			$d = '';
			foreach( $credit_TVA as $tx => $value ) {
				$tva_tpl_component['SUMMARY_ROW_TITLE'] = sprintf( __('Tax amount (%s %s)', 'wpshop'), $tx, '%' );
				$tva_tpl_component['SUMMARY_ROW_VALUE'] = '-'.number_format($value, '2', '.', '').' '.wpshop_tools::wpshop_get_currency( false );
				$d .= wpshop_display::display_template_element('invoice_summary_row', $tva_tpl_component, array(), 'common');
				unset( $tva_tpl_component );
			}
			$sub_tpl_component['CREDIT_SLIP_SUMMARY_TVA'] = $d;


			$sub_tpl_component['INVOICE_SUMMARY_MORE'] = '';

			$sub_tpl_component['CREDIT_SLIP_TOTAL_HT'] = '-'.number_format( $total_HT, '2', '.', '');
			$sub_tpl_component['CREDIT_SLIP_ORDER_GRAND_TOTAL'] = '-'.number_format($total_TTC, '2', '.', '');


			$tpl_component['INVOICE_SUMMARY_PART'] = wpshop_display::display_template_element('credit_slip_summary_part', $sub_tpl_component, array(), 'common');


			$tpl_component['RECEIVED_PAYMENT'] = $tpl_component['INVOICE_TRACKING'] = '';
			$tpl_component['INVOICE_FOOTER'] = wpshop_modules_billing::generate_footer_invoice();



			$output = wpshop_display::display_template_element('invoice_page_content', $tpl_component, array(), 'common');
			unset( $tpl_component );
			return $output;
		}

		function wps_credit_change_status() {
			$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

			if ( !wp_verify_nonce( $_wpnonce, 'wps_credit_change_status' ) )
				wp_die();

			$status = false; $result = '';
			$order_id = ( !empty($_POST['order_id']) ) ? wpshop_tools::varSanitizer( $_POST['order_id'] ): null;
			$credit_ref = ( !empty($_POST['credit_ref']) ) ? wpshop_tools::varSanitizer( $_POST['credit_ref'] ): null;
			$selected_status = ( !empty($_POST['selected_status']) ) ? wpshop_tools::varSanitizer( $_POST['selected_status'] ): null;

			if ( !empty( $order_id) && !empty($credit_ref) ) {
				$credit_meta = get_post_meta( $order_id, '_wps_order_credit', true );
				$id = 0;
				if ( !empty($credit_meta) ) {
					foreach( $credit_meta as $credit_id => $credit_def ) {
						if ( $credit_def['ref'] == $credit_ref ) {
							$id = $credit_id;
						}
					}
					$credit_meta[$id]['credit_status'] = $selected_status;
					update_post_meta( $order_id, '_wps_order_credit', $credit_meta );
					$status = true;
					$result = self::display_credit_list( $order_id );
				}
			}

			$response = array( 'status' => $status, 'response' => $result);
			echo json_encode( $response );
			die();
		}

		/**
		 * Add Credit informations on order
		 * @param array $order_metadata
		 * @param array $posted_datas
		 */
		function wps_credit_actions_on_order_save( $order_metadata, $posted_datas ) {
			if ( ( !empty($posted_datas['markascanceled_order_hidden_indicator']) && wpshop_tools::varSanitizer($posted_datas['markascanceled_order_hidden_indicator']) == 'canceled' ) || ( !empty($posted_datas['markasrefunded_order_hidden_indicator']) && wpshop_tools::varSanitizer($posted_datas['markasrefunded_order_hidden_indicator']) == 'refunded' ) || ( !empty($posted_datas['resendordertocustomer_order_hidden_indicator']) && wpshop_tools::varSanitizer($posted_datas['resendordertocustomer_order_hidden_indicator']) == 'resended' ) ) {
				if( empty($posted_datas['resendordertocustomer_order_hidden_indicator'] )) {
					// Make a credit
					$this->create_an_credit( $posted_datas['post_ID'] );
					if( !empty($posted_datas['markascanceled_order_hidden_indicator']) ) {
						$order_metadata['order_status'] = wpshop_tools::varSanitizer($posted_datas['markascanceled_order_hidden_indicator']);
					} elseif( !empty($posted_datas['markasrefunded_order_hidden_indicator']) ) {
						$order_metadata['order_status'] = wpshop_tools::varSanitizer($posted_datas['markasrefunded_order_hidden_indicator']);
					}
					$order_metadata['order_payment']['refunded_action']['refunded_date'] = current_time('mysql', 0 );
					$order_metadata['order_payment']['refunded_action']['author'] = get_current_user_id();
				} elseif(wpshop_tools::varSanitizer($posted_datas['resendordertocustomer_order_hidden_indicator']) == 'resended' ) {
					$order_id = $posted_datas['post_ID'];
					$order_info = get_post_meta($order_id, '_order_info', true);
					$user_data = get_userdata( $order_metadata['customer_id'] );
					$shipping_mode_option = get_option( 'wps_shipping_mode' );
					$shipping_method = ( !empty($order_metadata['order_payment']['shipping_method']) && !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && is_array($shipping_mode_option['modes']) && array_key_exists($order_metadata['order_payment']['shipping_method'], $shipping_mode_option['modes'])) ? $shipping_mode_option['modes'][$order_metadata['order_payment']['shipping_method']]['name'] : ( (!empty($order_metadata['order_payment']['shipping_method']) ) ? $order_metadata['order_payment']['shipping_method'] : '' );
					$email = ( !empty($user_data) && !empty($user_data->user_email) ) ? $user_data->user_email : '';
					//echo '<pre>'; print_r($email); echo '</pre>'; exit();
					$first_name = (!empty($order_info) && !empty($order_info['billing']) &&  !empty($order_info['billing']['address']['address_first_name']) ? $order_info['billing']['address']['address_first_name'] : '' );
					$last_name = ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']['address_last_name']) ? $order_info['billing']['address']['address_last_name'] : '' );
					$allow_send_invoice = get_option( 'wpshop_send_invoice' );
					$payment_methods = new wpshop_payment();
					$key = count($order_metadata['order_payment']['received']) - 1;
					$invoice_attachment_file = ( !empty($allow_send_invoice) ) ? wpshop_modules_billing::generate_invoice_for_email( $order_id, $order_metadata['order_payment']['received'][$key]['invoice_ref'] ) : '';
					$wps_message = new wps_message_ctr();
					$wps_message->wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order_metadata['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order_metadata['order_date'], 'order_shipping_method' => $shipping_method), array(), $invoice_attachment_file);
				}
			}
			return $order_metadata;
		}

		/**
		 *	Output credit slip
		 */
		function wps_credit_slip_output() {
			$order_id = (!empty($_GET['order_id'])) ? (int) $_GET['order_id'] : null;
			$invoice_ref = (!empty($_GET['credit_ref'])) ? sanitize_text_field($_GET['credit_ref']) : null;
			$mode = (!empty($_GET['mode'])) ? sanitize_text_field($_GET['mode']) : 'html';
			// $is_credit_slip = (!empty($_GET['credit_slip'])) ? wpshop_tools::varSanitizer($_GET['credit_slip']) : null;

			if ( !empty($order_id) ) {
			// 	/**	Order reading	*/
				$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
				$html_content = wps_credit::generate_credit_slip($order_id, $invoice_ref );

				if ( $mode == 'pdf') {
					require_once(WPSHOP_LIBRAIRIES_DIR.'HTML2PDF/html2pdf.class.php');
					try {
						$html_content = wpshop_display::display_template_element('invoice_page_content_css', array(), array(), 'common') . '<page>' . $html_content . '</page>';
						$html2pdf = new HTML2PDF('P', 'A4', 'fr');

						$html2pdf->setDefaultFont('Arial');
						$html2pdf->writeHTML($html_content);

						$html2pdf->Output('order_' .$order_id. '.pdf', 'D');
					}
					catch (HTML2PDF_exception $e) {
						echo $e;
					}
				}
				else {
					$tpl_component['INVOICE_CSS'] =  wpshop_display::display_template_element('invoice_page_content_css', array(), array(), 'common');
					$tpl_component['INVOICE_MAIN_PAGE'] = $html_content;
					$tpl_component['INVOICE_TITLE_PAGE'] = sprintf( __('Credit slip #%s for Order #%s', 'wpshop'), $invoice_ref, $order_postmeta['order_key']);
					echo wpshop_display::display_template_element('invoice_page', $tpl_component, array(), 'common');
				}
			}
			die();
		}

	}
}
if ( class_exists('wps_credit') ) {
	$wps_classic_checkout = new wps_credit();
}
