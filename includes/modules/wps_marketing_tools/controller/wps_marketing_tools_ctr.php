<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_marketing_tools_ctr {
	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_MARKETING_TOOLS_DIR;

	function __construct() {
		// Template loading...
		$this->template_dir = WPS_MARKETING_TOOLS_PATH . WPS_MARKETING_TOOLS_DIR . "/templates/";

		add_action('wsphop_options', array( $this, 'declare_options'), 8);
		add_action('wpshop_free_shipping_cost_alert', array( $this, 'display_free_shipping_cost_alert'));
		add_shortcode('display_save_money_message', array( $this, 'display_save_money_message'));

		add_shortcode( 'wps_low_stock_alert', array($this, 'display_alert_stock_message' ) );
	}

	/**
	 * OPTIONS - Declare options
	 */
	function declare_options () {
		if ( WPSHOP_DEFINED_SHOP_TYPE == 'sale' ) {
			$wpshop_shop_type = !empty( $_POST['wpshop_shop_type'] ) ? sanitize_text_field( $_POST['wpshop_shop_type'] ) : '';
			$old_wpshop_shop_type = !empty( $_POST['old_wpshop_shop_type'] ) ? sanitize_text_field( $_POST['old_wpshop_shop_type'] ) : '';

			if ( ( $wpshop_shop_type == '' || $wpshop_shop_type != 'presentation' )
				&& ( $old_wpshop_shop_type == '' || $old_wpshop_shop_type != 'presentation' ) ) {
					register_setting('wpshop_options', 'wpshop_cart_option', array($this, 'wpshop_options_validate_free_shipping_cost_alert'));
					add_settings_field('wpshop_free_shipping_cost_alert', __('Display a free shipping cost alert in the cart', 'wpshop'), array( $this, 'wpshop_free_shipping_cost_alert_field'), 'wpshop_cart_info', 'wpshop_cart_info');
					// Low stock alert option
					register_setting('wpshop_options', 'wpshop_low_stock_alert_options', array($this, 'wpshop_low_stock_alert_validator'));
					add_settings_field('wpshop_display_low_stock', __('Display Low stock Alert message', 'wpshop'), array($this, 'wpshop_display_low_stock_alert_interface'), 'wpshop_display_option', 'wpshop_display_options_sections');

				}
		}
	}

	/**
	 * OPTIONS - Display Free Shipping alert option field
	 */
	function wpshop_free_shipping_cost_alert_field () {
		$cart_option = get_option('wpshop_cart_option');
		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option[free_shipping_cost_alert]';
		$input_def['type'] = 'checkbox';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = !empty($cart_option['free_shipping_cost_alert']) ? $cart_option['free_shipping_cost_alert'][0] : '';
		$input_def['possible_value'] = 'yes';
		$output = wpshop_form::check_input_type($input_def, 'wpshop_cart_option[free_shipping_cost_alert]') . '<a href="#" title="'.__('Check this box if you want to display an free shipping cost in the mini-cart','wpshop').'" class="wpshop_infobulle_marker">?</a>';

		echo $output;
	}

	/**
	 * OPTIONS - Validate Free shipping alert option
	 * @param unknown_type $input
	 * @return unknown
	 */
	function wpshop_options_validate_free_shipping_cost_alert ($input) {
		return $input;
	}

	/**
	 * OPTIONS - Validate Low stock alert option
	 * @param string $input
	 * @return string
	 */
	function wpshop_low_stock_alert_validator ($input) {
		return $input;
	}

	/**
	 * Display low stock alert options interface
	 */
	function wpshop_display_low_stock_alert_interface() {
		$low_stock_option = get_option('wpshop_low_stock_alert_options');
		$activate_low_stock_alert = ( (!empty($low_stock_option) && !empty($low_stock_option['active']) && $low_stock_option['active'] == 'on') ? 'checked="checked"' : null);
		$based_on_stock = ( !empty($low_stock_option) && !empty($low_stock_option['based_on_stock']) && $low_stock_option['based_on_stock'] == 'yes') ? 'checked="checked"' : null;
		$not_based_on_stock = ( !empty($low_stock_option) && !empty($low_stock_option['based_on_stock']) && $low_stock_option['based_on_stock'] == 'no') ? 'checked="checked"' : null;
		$alert_limit = ( !empty($low_stock_option) && !empty($low_stock_option['alert_limit']) ) ? $low_stock_option['alert_limit'] : '';

		require( wpshop_tools::get_template_part( WPS_MARKETING_TOOLS_DIR, $this->template_dir, "backend", "wps_low_stock_alert_configuration_interface") );
	}

	/**
	 * Display a free Shipping cost alert in cart and shop
	 */
	function display_free_shipping_cost_alert () {
		global $wpdb;

		$output = '';
		$cart = ( !empty($_SESSION['cart']) && is_array($_SESSION['cart']) ) ? $_SESSION['cart'] : null;
		$cart_option = get_option('wpshop_cart_option');
		$price_piloting_option = get_option('wpshop_shop_price_piloting');

		// Get a shipping mode, in order : selected, else default, else first, else shipping_rules.
		$shipping_modes = get_option( 'wps_shipping_mode' );
		if( !empty($shipping_modes) && !empty($shipping_modes['modes']) ) {
			if( !empty($shipping_modes['default_choice']) && !empty( $shipping_modes['modes'][$shipping_modes['default_choice']] ) ) {
				$shipping_rules_option = $shipping_modes['modes'][$shipping_modes['default_choice']];
			} elseif( !empty($shipping_modes['modes']['default_shipping_mode']) ) {
				$shipping_rules_option = $shipping_modes['modes']['default_shipping_mode'];
			} else {
				$shipping_rules_option = reset( $shipping_modes['modes'] );
			}
		} else {
			$shipping_rules_option = get_option( 'wpshop_shipping_rules' );
		}

		if ( !empty($shipping_rules_option) && !empty($shipping_rules_option['free_from']) && $shipping_rules_option['free_from'] > 0 )
			$free_shipping_cost_limit = $shipping_rules_option['free_from'];
		if ( !empty($cart_option) && !empty($cart_option['free_shipping_cost_alert']) ) {
			if ( !empty($cart['order_items']) && !empty($cart['order_grand_total'])) {
				$order_amount = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? number_format((float)$cart['order_total_ht'], 2, '.', '') : number_format((float)$cart['order_total_ttc'], 2, '.', '');
				if ( $order_amount  < $free_shipping_cost_limit ) {
					$free_in = number_format((float)($free_shipping_cost_limit - $order_amount), 2, '.', '');
					$currency = wpshop_tools::wpshop_get_currency();
					$output = sprintf(__('Free shipping cost in %s', 'wpshop'), $free_in. ' ' . $currency);
				}
				else {
					$output = __('Free shipping cost', 'wpshop');
				}
			}
		}
		echo $output;
	}

	/**
	 * Display "You save money" Message
	 * @param string $price_infos
	 * @return string
	 */
	function display_message_you_save_money ( $price_infos ) {
		$output = '';
		/*if ( !empty($price_infos) ) {
			if ( !empty($price_infos) && !empty($price_infos['discount']) && !empty($price_infos['discount']['discount_exist']) ) {
				$tax_piloting_option = get_option('wpshop_shop_price_piloting');
				$save_amount = ( !empty($tax_piloting_option) && $tax_piloting_option == 'HT') ? ( $price_infos['et'] - $price_infos['discount']['discount_et_price'] ) : ( $price_infos['ati'] - $price_infos['discount']['discount_ati_price'] );
				ob_start();
				require( wpshop_tools::get_template_part( WPS_MARKETING_TOOLS_DIR, $this->template_dir, "frontend", "wps_marketing_save_money_message") );
				$message .= ob_get_contents();
				ob_end_clean();
			}
		}*/
		return $output;
	}

	/**
	 * Display Google Analytics code tracker to retrieve our orders in Google Analytics
	 * @param integer $order_id
	 * @return string
	 */
	function display_ecommerce_ga_tracker( $order_id ) {
		global $wpdb;
		$tracker = '';
		if( !empty($order_id) ) {
			$ga_account_id = get_option('wpshop_ga_account_id');
			$order_meta = get_post_meta( $order_id, '_order_postmeta', true);
			$order_info = get_post_meta( $order_id, '_order_info', true);
			if( !empty($ga_account_id) && !empty($order_meta) && !empty($order_info) ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPS_MARKETING_TOOLS_DIR, $this->template_dir, "backend", "wps_ga_order_tracker") );
				$message .= ob_get_contents();
				ob_end_clean();
			}
		}
		return $tracker;
	}

	/**
	 * Display low stoclk alert message
	 * @param string $args
	 * @return string
	 */
	function display_alert_stock_message( $args ) {
		$message = '';
		$post_ID = ( !empty($args) && !empty($args['id']) ) ? $args['id'] : '';
		$low_stock_alert_option  = get_option('wpshop_low_stock_alert_options');

		if ( !empty( $low_stock_alert_option  ) && !empty($low_stock_alert_option['active']) && !empty($post_ID) ) {
			$product = wpshop_products::get_product_data( $post_ID );

			$product_stock = $product['product_stock'];
			$manage_product_stock = (!empty($product['manage_stock']) && ( strtolower(__($product['manage_stock'], 'wpshop')) == strtolower(__('Yes', 'wpshop')) )) ? true : false;

			if ( ( $product_stock > 0 ) && ( empty( $low_stock_alert_option['based_on_stock'] ) || ( ('no' == $low_stock_alert_option['based_on_stock'] ) || ( ( 'yes' == $low_stock_alert_option['based_on_stock'] ) && ( $low_stock_alert_option[ 'alert_limit' ] >= $product_stock ) ) ) ) ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPS_MARKETING_TOOLS_DIR, $this->template_dir, "frontend", "wps_low_stock_alert_message") );
				$message .= ob_get_contents();
				ob_end_clean();
			}

		}
		return $message;
	}
}
