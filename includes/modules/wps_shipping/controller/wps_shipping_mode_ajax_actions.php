<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_shipping_mode_ajax_actions {

	function __construct() {
		/** Ajax Actions **/
		add_action('wp_ajax_save_shipping_rule',array( $this, 'wpshop_ajax_save_shipping_rule'));
		add_action('wp_ajax_display_shipping_rules',array( $this, 'wpshop_ajax_display_shipping_rules'));
		add_action('wp_ajax_delete_shipping_rule',array( $this, 'wpshop_ajax_delete_shipping_rule'));
		add_action('wp_ajax_wps_add_new_shipping_mode',array( $this, 'wps_add_new_shipping_mode'));
		add_action('wp_ajax_wps_delete_shipping_mode',array( $this, 'wps_delete_shipping_mode'));
		add_action('wp_ajax_wps_reload_shipping_mode',array( $this, 'wps_reload_shipping_mode'));
		add_action('wp_ajax_wps_calculate_shipping_cost',array( $this, 'wps_calculate_shipping_cost'));
		add_action( 'wp_ajax_wps_load_shipping_methods', array(&$this, 'wps_load_shipping_methods') );
	}

	/**
	 * AJAX - Save custom Rules
	 **/
	function wpshop_ajax_save_shipping_rule(){
		check_ajax_referer( 'wpshop_ajax_save_shipping_rule' );

		global $wpdb;
		$wps_shipping = new wps_shipping();
		$status = false;
		$reponse = array();
		$fees_data = ( !empty($_POST['fees_data']) ) ?  ( $_POST['fees_data'] ) : null;
		$weight_rule = ( !empty($_POST['weight_rule']) ) ? wpshop_tools::varSanitizer( $_POST['weight_rule'] ) : null;
		$shipping_price = ( !empty($_POST['shipping_price']) ) ? wpshop_tools::varSanitizer( $_POST['shipping_price'] ) : 0;
		$selected_country = ( !empty($_POST['selected_country']) ) ? wpshop_tools::varSanitizer( $_POST['selected_country'] ) : null;
		$shipping_rules = $wps_shipping->shipping_fees_string_2_array( stripslashes($fees_data) );

		$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
		$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
		$unity = $wpdb->get_var( $query );

		$weight_rule = ( !empty($unity) && $unity == 'kg' ) ? $weight_rule * 1000 : $weight_rule;
		//Check if this shipping rule (same country and same weight) already exist in the shipping rules definition
		if( !empty($shipping_rules) ) {
			$existing_country = false;
			$tab_key = -1;
			foreach ( $shipping_rules as $key=>$shipping_rule) {
				if ( $shipping_rule['destination'] == $selected_country) {
					$existing_country = true;
					$tab_key = $key;
				}
			}
			if ( $existing_country && $tab_key > -1) {
				$shipping_rules[$tab_key]['fees'][$weight_rule] = $shipping_price;
			}
			else {
				$shipping_rules[] = array( 'destination' => $selected_country, 'rule' => 'weight', 'fees' => array($weight_rule => $shipping_price) );
			}
			$status = true;
		}
		else {
			$shipping_rules = array( '0' => array('destination' => $selected_country, 'rule' => 'weight', 'fees' => array( $weight_rule => $shipping_price)) );
			$status = true;
		}
		$reponse = array('status' => $status, 'reponse' => $wps_shipping->shipping_fees_array_2_string( $shipping_rules ) );
		echo json_encode($reponse);
		die();
	}

	/**
	 * AJAX - Delete Custom shipping Rule
	 */
	function wpshop_ajax_delete_shipping_rule() {
		check_ajax_referer( 'wpshop_ajax_delete_shipping_rule' );

		global $wpdb;
		$wps_shipping = new wps_shipping();
		$fees_data = ( !empty($_POST['fees_data']) ) ? ( $_POST['fees_data'] ) : null;
		$country_and_weight =  ( !empty($_POST['country_and_weight']) ) ? sanitize_text_field( $_POST['country_and_weight'] ) : null;
		$datas = explode("|", $country_and_weight);
		$country = $datas[0];
		$weight = $datas[1];
		$shipping_mode_id = $datas[2];

		$shipping_rules = $wps_shipping->shipping_fees_string_2_array( stripslashes($fees_data) );

		/** Check the default weight unity **/
// 		$weight_unity_id = get_option('wpshop_shop_default_weight_unity');
// 		if ( !empty($weight_unity_id) ) {
// 			$query = $wpdb->prepare('SELECT unit FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id=%d', $weight_unity_id);
// 			$weight_unity = $wpdb->get_var( $query );

// 			if( $weight_unity == 'kg' ) {
// 				$weight = $weight * 1000;
// 			}
// 		}

		if ( array_key_exists($country, $shipping_rules) ) {
			if ( array_key_exists((string)$weight, $shipping_rules[$country]['fees']) ) {
				unset($shipping_rules[$country]['fees'][$weight]);
			}
			if ( empty($shipping_rules[$country]['fees']) ) {
				unset($shipping_rules[$country]);
			}

		}
		foreach ( $shipping_rules as $k=>$shipping_rule ) {
			if ( !isset($shipping_rule['fees']) ) {
				unset($shipping_rules[$k]);
			}
		}

		$status = true;

		if ( !empty( $shipping_rules ) ) {
			$rules = $wps_shipping->shipping_fees_array_2_string( $shipping_rules );
		}
		else {
			$rules = '';
		}

		wp_die( json_encode( array( 'status' => $status, 'reponse' => $rules, ) ) );
	}

	/**
	 * AJAX - Display Created custom shipping rules
	 */
	function wpshop_ajax_display_shipping_rules () {
		check_ajax_referer( 'wpshop_ajax_display_shipping_rules' );

		$status = false;
		$fees_data = ( !empty($_POST['fees_data']) ) ? ( $_POST['fees_data'] ) : null;
		$shipping_mode_id = ( !empty($_POST['shipping_mode_id']) ) ? sanitize_title($_POST['shipping_mode_id']) : null;
		$result = '';
		if( !empty($fees_data) ) {
			$wps_shipping_mode_ctr = new wps_shipping_mode_ctr();
			$result = $wps_shipping_mode_ctr->generate_shipping_rules_table( $fees_data, $shipping_mode_id );
			$status = true;
		}
		else {
			$status = true;
			$result = __('No shipping rules are created', 'wpshop');
		}

		echo json_encode(array('status' => $status, 'reponse' => $result));
		die();
	}



	/**
	 * AJAX - Reload shippig mode interface
	 */
	function wps_reload_shipping_mode() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_reload_shipping_mode' ) )
			wp_die();

		$status = false; $allow_order = true;
		$result = '';
		$address_id = !empty( $_POST['address_id'] ) ? (int) $_POST['address_id'] : 0;
		if ( !empty($address_id) ) {
			$_SESSION['shipping_address'] = $address_id;
		}
		$shipping_address_id = ( !empty($_SESSION['shipping_address']) ) ? $_SESSION['shipping_address'] : '';
		if ( !empty($shipping_address_id) ) {
			//$result = self::generate_shipping_mode_for_an_address();
			$wps_shipping_mode_ctr = new wps_shipping_mode_ctr();
// 			$shipping_modes = $wps_shipping_mode_ctr->generate_shipping_mode_for_an_address( intval($_POST['address_id']) );

			$status = $allow_order = $shipping_modes[0];
			if( empty( $shipping_modes[0]) || $shipping_modes[0] == false ) {
				$status = false;
			}

			$result = $shipping_modes[1];

			if ( $status == false ) {
				$allow_order = false;
				$result = '<div class="error_bloc">' .__('Sorry ! You can\'t order on this shop, because we don\'t ship in your country.', 'wpshop' ). '</div>';
			}

		}
		$response = array('status' => $status, 'response' => $result, 'allow_order' => $allow_order );
		echo json_encode( $response );
		die();
	}

	/**
	 * AJAX - Calculate Shipping cost
	 */
	function wps_calculate_shipping_cost() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_calculate_shipping_cost' ) )
			wp_die();

		$status = false;
		$result = '';
		$chosen_method = !empty($_POST['chosen_method']) ? wpshop_tools::varSanitizer($_POST['chosen_method']) : null;

		if( !empty($chosen_method) ) {
			$_SESSION['shipping_method'] = $chosen_method;
			$wps_cart = new wps_cart();
			$order = $wps_cart->calcul_cart_information( array() );
			$wps_cart->store_cart_in_session($order);

			$status = true;
		}

		$response = array('status' => $status );
		echo json_encode( $response );
		die();
	}

	/**
	 * AJAX - (New checkout Tunnel ) Load available shipping modes
	 */
	function wps_load_shipping_methods() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_load_shipping_methods' ) )
			wp_die();

		$status = true; $response = '';
		$shipping_address_id = ( !empty($_POST['shipping_address']) ) ? intval( $_POST['shipping_address'] ) : null;
		if ( !empty($shipping_address_id) ) {
			// Check if element is an address
			$check_address_type = get_post($shipping_address_id);
			if ( !empty($check_address_type) && $check_address_type->post_author == get_current_user_id() && $check_address_type->post_type == WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS ) {
				// Get address metadatas
				$address_metadata = get_post_meta( $shipping_address_id, '_wpshop_address_metadata', true );
				if( !empty($address_metadata) && !empty($address_metadata['country']) ) {
					$country = $address_metadata['country'];
					$postcode = $address_metadata['postcode'];
					$shipping_methods = get_option( 'wps_shipping_mode' );
					$available_shipping_methods = array();
					if( !empty($shipping_methods) && !empty($shipping_methods['modes']) ) {
						// Check all shipping methods
						foreach( $shipping_methods['modes'] as $shipping_method_id => $shipping_method ){
							if ( empty($shipping_method['limit_destination']) || ( empty($shipping_method['limit_destination']['country']) || ( !empty($shipping_method['limit_destination']['country']) && in_array($country, $shipping_method['limit_destination']['country']) ) ) ) {
								$available_shipping_methods[ $shipping_method_id ] = $shipping_method;
							}
						}
						if( !empty($available_shipping_methods) ) {
							foreach( $available_shipping_methods as $shipping_mode_id => $shipping_mode ) {
								ob_start();
								require( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, WPS_SHIPPING_MODE_PATH . WPS_SHIPPING_MODE_DIR . "/templates/","frontend", "shipping-mode", "element") );
								$response .= ob_get_contents();
								ob_end_clean();

							}
						}
						else {
							$response = '<div class="wps-alert-error">' .__( 'No shipping method available for your shipping address', 'wpshop' ). '</div>';
						}

					}
					else {
						$response = '<div class="wps-alert-info">' .__( 'No shipping method available', 'wpshop' ). '</div>';
					}
				}
			}
		}
		else {
			$response = '<div class="wps-alert-info">' .__( 'Please select a shipping address to choose a shipping method', 'wpshop' ). '</div>';
		}
		echo json_encode( array( 'status' => $status, 'response' => $response) );
		die();
	}


	function wps_add_new_shipping_mode() {
		check_ajax_referer( 'wps_add_new_shipping_mode' );

		$status = false; $reponse = '';

		$k = 'wps_custom_shipping_mode_'.time();
		$shipping_mode = array();
		ob_start();
		require( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, WPS_SHIPPING_MODE_PATH . WPS_SHIPPING_MODE_DIR . "/templates/", "backend", "shipping-mode") );
		$response .= ob_get_contents();
		ob_end_clean();

		$response .= '<script type="text/javascript" >jQuery( document ).ready( function(){
		jQuery("select.chosen_select").chosen( WPSHOP_CHOSEN_ATTRS );
	} );</script>';

		$status = ( !empty($response) ) ? true : false;

		echo json_encode( array( 'status' => $status, 'response' => $response) );
		wp_die();
	}

	function wps_delete_shipping_mode() {
		check_ajax_referer( 'wps_delete_shipping_mode' );
		$shipping_mode = ! empty( $_POST['shipping_mode'] ) ? sanitize_text_field( $_POST['shipping_mode'] ) : null;
		$wps_shipping_mode = get_option( 'wps_shipping_mode' );
		if ( ! empty( $wps_shipping_mode['modes'] ) && array_key_exists( $shipping_mode, $wps_shipping_mode['modes'] ) ) {
			unset( $wps_shipping_mode['modes'][ $shipping_mode ] );
			if ( ! empty( $wps_shipping_mode['default_choice'] ) && $wps_shipping_mode['default_choice'] === $shipping_mode ) {
				$wps_shipping_mode['default_choice'] = 'default_shipping_mode';
			}
		}
		update_option( 'wps_shipping_mode', $wps_shipping_mode );
		wp_send_json_success();
	}
}
