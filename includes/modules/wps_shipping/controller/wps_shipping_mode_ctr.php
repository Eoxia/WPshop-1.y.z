<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_shipping_mode_ctr {

	/**
	 * Define the main directory containing the template for the current plugin
	 * @var string
	 */
	public $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_SHIPPING_MODE_DIR;

	function __construct() {
		$this->template_dir = WPS_SHIPPING_MODE_PATH . WPS_SHIPPING_MODE_DIR . "/templates/";

		/** Template Load **/
	//	add_filter( 'wpshop_custom_template', array( $this, 'custom_template_load' ) );

		add_action( 'admin_init', array( $this, 'migrate_default_shipping_mode' ) );

		/**	Add module option to wpshop general options	*/
		add_filter('wpshop_options', array( $this, 'add_options'), 9);
		add_action('wsphop_options', array( $this, 'create_options'), 8);

		// Add files in back-office
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts_in_admin' ) );
		// Add files in front-office
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts') );

		// Available Shortcodes
		add_shortcode( 'wps_shipping_mode', array( &$this, 'display_shipping_mode') );
		add_shortcode( 'wps_shipping_method', array( &$this, 'display_shipping_methods') );
		add_shortcode( 'wps_shipping_summary', array( &$this, 'display_shipping_summary') );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box'), 10, 2 );

		add_shortcode( 'wps_product_shipping_cost', array( $this, 'get_shipping_cost_shortcode' ) );
	}

	function add_meta_box( $post_type, $post ) {
		if ( WPSHOP_NEWTYPE_IDENTIFIER_ORDER == $post_type ) {
			/**	Box for shipping information	*/
			$shipping_option = get_option('wpshop_shipping_address_choice');
			$order_meta = get_post_meta( $post->ID, '_order_postmeta', true );
			if (!in_array( $post->post_status, array( 'auto-draft' ) ) && ( !empty($shipping_option['activate']) && $shipping_option['activate'] && ( is_array( $order_meta ) && empty($order_meta['order_payment']['shipping_method'] ) || $order_meta['order_payment']['shipping_method'] != 'default_shipping_mode_for_pos' ) ) ) {
				add_meta_box(
				'wpshop_order_shipping',
				'<span class="dashicons dashicons-palmtree"></span> '.__('Shipping', 'wpshop'),
				array($this, 'order_shipping_box'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'low'
						);
			}
		}
	}

	/**
	 * Add CSS and JS files in front-office
	 */
	function add_scripts() {
		//CSS files
		wp_register_style( 'wps_shipping_mode_css', WPS_SHIPPING_MODE_URL . WPS_SHIPPING_MODE_DIR .'/assets/frontend/css/wps_shipping_mode.css', false );
		wp_enqueue_style( 'wps_shipping_mode_css' );
		// Javascript Files
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wps_shipping_method_js', WPS_SHIPPING_MODE_URL . WPS_SHIPPING_MODE_DIR .'/assets/frontend/js/shipping_method.js', false );
	}

	/**
	 * Add JS and CSS files in back-office
	 */
	function add_scripts_in_admin( $hook ) {
		global $current_screen;
		if ( ! in_array( $current_screen->post_type, array( WPSHOP_NEWTYPE_IDENTIFIER_ORDER ), true ) && $hook !== 'settings_page_wpshop_option' )
			return;

		add_thickbox();
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
		// Javascript files
		wp_enqueue_script( 'wps_shipping_mode_js', WPS_SHIPPING_MODE_URL . WPS_SHIPPING_MODE_DIR .'/assets/backend/js/wps_shipping_mode.js', false );
		//CSS files
		wp_register_style( 'wps_shipping_mode_css', WPS_SHIPPING_MODE_URL . WPS_SHIPPING_MODE_DIR .'/assets/backend/css/wps_shipping_mode.css', false );
		wp_enqueue_style( 'wps_shipping_mode_css' );
	}


	/** Load module/addon automatically to existing template list
	 *
	 * @param array $templates The current template definition
	 *
	 * @return array The template with new elements
	 */
// 	function custom_template_load( $templates ) {
// 		include($this->template_dir.'wpshop/main_elements.tpl.php');
// 		$wpshop_display = new wpshop_display();
// 		$templates = $wpshop_display->add_modules_template_to_internal( $tpl_element, $templates );
// 		unset($tpl_element);

// 		return $templates;
// 	}

	/**
	 * Declare option groups for the module
	 */
	function add_options( $option_group ) {
		$option_group['wpshop_shipping_option']['subgroups']['wps_shipping_mode']['class'] = ' wpshop_admin_box_options_shipping_mode';
		return $option_group;
	}

	/**
	 * Create Options
	 **/
	function create_options() {
		add_settings_section('wps_shipping_mode', '<span class="dashicons dashicons-admin-site"></span>'.__('Shipping method', 'wpshop'), '', 'wps_shipping_mode');
		register_setting('wpshop_options', 'wps_shipping_mode', array( $this, 'wpshop_options_validate_wps_shipping_mode'));
		add_settings_field('wps_shipping_mode', ''/*__('Shipping Mode', 'wpshop')*/, array( $this, 'display_shipping_mode_in_admin'), 'wps_shipping_mode', 'wps_shipping_mode');
	}

	/**
	 * WPS Shipping mode Option Validator
	 **/
	function wpshop_options_validate_wps_shipping_mode( $input ) {
		$wps_shipping = new wps_shipping();
		if ( !empty($input['modes']) ) {
			foreach( $input['modes'] as $mode => $mode_det ) {
				/** Custom Shipping rules **/
				$input['modes'][$mode]['custom_shipping_rules']['fees'] = $wps_shipping->shipping_fees_string_2_array( $input['modes'][$mode]['custom_shipping_rules']['fees'] );

				/** Shipping Modes Logo Treatment **/
				if ( !empty($_FILES[$mode.'_logo']['name']) && empty($_FILES[$mode.'_logo']['error']) ) {
					$filename = $_FILES[$mode.'_logo'];
					$upload  = wp_handle_upload($filename, array('test_form' => false));
					$wp_filetype = wp_check_filetype(basename($filename['name']), null );
					$wp_upload_dir = wp_upload_dir();
					$attachment = array(
							'guid' => $wp_upload_dir['url'] . '/' . basename( $filename['name'] ),
							'post_mime_type' => $wp_filetype['type'],
							'post_title' => preg_replace(' /\.[^.]+$/', '', basename($filename['name'])),
							'post_content' => '',
							'post_status' => 'inherit'
					);
					$attach_id = wp_insert_attachment( $attachment, $upload['file']);
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
					wp_update_attachment_metadata( $attach_id, $attach_data );

					$input['modes'][$mode]['logo'] = $attach_id;
				}
			}
		}
		return $input;
	}

	/**
	 * Migrate Old Shipping Mode to the new storage system
	 **/
	function migrate_default_shipping_mode() {
		$data = array();
		$shipping_mode_option = get_option( 'wps_shipping_mode' );
		if ( empty($shipping_mode_option) ) {
			$data['modes']['default_shipping_mode']['active'] = 'on';
			$data['modes']['default_shipping_mode']['name'] = __('Home Delivery', 'wpshop');
			$data['modes']['default_shipping_mode']['explanation'] = __('Your purchase will be delivered directly to you at home', 'wpshop');
			/** Check CUstom Shipping **/
			$custom_shipping_option = get_option( 'wpshop_custom_shipping' );
			if ( !empty($custom_shipping_option) ) {
				$data['modes']['default_shipping_mode']['custom_shipping_rules'] = $custom_shipping_option;
			}
			/** Check Country Limit **/
			$limit_destination = get_option( 'wpshop_limit_shipping_destination' );
			if ( !empty($custom_shipping_option) ) {
				$data['modes']['default_shipping_mode']['limit_destination'] = $limit_destination;
			}

			/** Check Others shipping configurations **/
			$wpshop_shipping_rules_option = get_option('wpshop_shipping_rules');
			if ( !empty($wpshop_shipping_rules_option) ){
				if ( !empty($wpshop_shipping_rules_option['min_max']) ) {
					$data['modes']['default_shipping_mode']['min_max'] = $wpshop_shipping_rules_option['min_max'];
				}
				if ( !empty($wpshop_shipping_rules_option['free_from']) ) {
					$data['modes']['default_shipping_mode']['free_from'] = $wpshop_shipping_rules_option['free_from'];
				}
				if ( !empty($wpshop_shipping_rules_option['wpshop_shipping_rule_free_shipping']) ) {
					$data['modes']['default_shipping_mode']['free_shipping'] = $wpshop_shipping_rules_option['wpshop_shipping_rule_free_shipping'];
				}
			}
			$data['default_choice'] = 'default_shipping_mode';

			update_option( 'wps_shipping_mode', $data );
		}
	}

	/**
	 *  Display the Admin Interface for Shipping Mode
	 **/
	function display_shipping_mode_in_admin() {
		$shipping_mode_option = get_option( 'wps_shipping_mode' );
		require_once( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, $this->template_dir, "backend", "shipping-modes") );
	}

	/**
	 * Generate Shipping mode configuration back-office interface
	 * @param string $key
	 * @param array $shipping_mode
	 * @return string
	 */
	function generate_shipping_mode_interface( $k, $shipping_mode ) {
		global $wpdb;
		$tpl_component = array();

		$shipping_mode_option = get_option( 'wps_shipping_mode');
		$default_shipping_mode = !empty( $shipping_mode_option['default_choice'] ) ? $shipping_mode_option['default_choice'] : '';

		$countries = unserialize(WPSHOP_COUNTRY_LIST);

		/** Default Weight Unity **/
		$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
		$query = $wpdb->prepare('SELECT name FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
		$unity = $wpdb->get_var( $query );


		$fees_data = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['fees']) ) ? $shipping_mode['custom_shipping_rules']['fees'] : array();
		if(is_array($fees_data)) {
			$wps_shipping = new wps_shipping();
			$fees_data = $wps_shipping->shipping_fees_array_2_string($fees_data);
		}
		ob_start();
		require( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, $this->template_dir, "backend", "shipping-mode-configuration-interface") );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}


	/**
	 * Generate cutom fees resume table
	 * @param array $fees_data
	 * @param string $key
	 */
	function generate_shipping_rules_table( $fees_data, $shipping_mode_id ){
		global $wpdb;
		$result = '';
		if ( !empty( $fees_data) ) {
			$wps_shipping = new wps_shipping();
			$shipping_rules =$wps_shipping->shipping_fees_string_2_array( stripslashes($fees_data) );
			$result = '';
			$tpl_component ='';
			$tpl_component['CUSTOM_SHIPPING_RULES_LINES'] = '';
			$tpl_component['SHIPPING_MODE_ID'] = $shipping_mode_id;
			$country_list = unserialize(WPSHOP_COUNTRY_LIST);
			$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
			$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
			$unity = $wpdb->get_var( $query );
			ob_start();
			require( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, $this->template_dir, "backend", "shipping-mode-configuration-custom-rules-table") );
			$result = ob_get_contents();
			ob_end_clean();
		}
		return $result;
	}


	/**
	 * ***********************************************
	 * NEW CHECKOUT TUNNEL FUNCTIONS FOR SHIPPING STEP
	 * ***********************************************
	 */

	/**
	 * Display shipping modes
	 * @return string
	 */
	function display_shipping_methods() {
		$output = $shipping_methods = ''; $no_shipping_mode_for_area = false;
		$shipping_modes = get_option( 'wps_shipping_mode' );
		if( !empty($_SESSION['shipping_address']) ) {
			$shipping_modes = $this->get_shipping_mode_for_address( $_SESSION['shipping_address'] );
			if( empty($shipping_modes) ) {
				$no_shipping_mode_for_area = true;
			}
		}
		$shipping_modes = apply_filters( 'wps_filter_shipping_methods', $shipping_modes );
		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, $this->template_dir, "frontend", "shipping-mode", "container") );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Display a shipping summary( Choosen Shipping & billing address, choosen shipping mode )
	 * @return string
	 */
	function display_shipping_summary() {
		$output = '';
		$billing_address_id = ( !empty($_SESSION['billing_address']) ) ? $_SESSION['billing_address'] : null;
		$shipping_address_id = ( !empty($_SESSION['shipping_address']) ) ? $_SESSION['shipping_address'] : null;
		$shipping_mode = ( !empty($_SESSION['shipping_method']) ) ? $_SESSION['shipping_method'] : null;

		if( !empty($billing_address_id)  ) {
			$billing_infos = get_post_meta($billing_address_id, '_wpshop_address_metadata', true);
			$billing_content = wps_address::display_an_address( $billing_infos, $billing_address_id);

			if ( !empty($shipping_address_id) && !empty($shipping_mode) ) {
				$shipping_infos = get_post_meta($shipping_address_id, '_wpshop_address_metadata', true);
				$shipping_content = wps_address::display_an_address( $shipping_infos, $shipping_address_id);

				$shipping_mode_option = get_option( 'wps_shipping_mode' );
				$shipping_mode = ( !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && !empty($shipping_mode_option['modes'][$shipping_mode]) && !empty($shipping_mode_option['modes'][$shipping_mode]['name']) ) ? $shipping_mode_option['modes'][$shipping_mode]['name'] : '';
			}

			ob_start();
			require( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, $this->template_dir, "frontend", "shipping-infos", "summary") );
			$output = ob_get_contents();
			ob_end_clean();
		}


		return $output;
	}

	/**
	 * Return alla availables shipping modes for an address
	 * @param integer $address_id
	 * @return string
	 */
	function get_shipping_mode_for_address( $address_id ) {
		$shipping_modes_to_display = array();
		if( !empty($address_id) ) {
			$shipping_modes = get_option( 'wps_shipping_mode' );
			$address_metadata = /*isset( $postcode ) ? array( 'postcode' => $postcode ) :*/ get_post_meta( $address_id, '_wpshop_address_metadata', true);
			if( !empty( $shipping_modes ) && !empty($shipping_modes['modes']) ){
				foreach( $shipping_modes['modes'] as $k => $shipping_mode ) {
					if ( !empty($shipping_mode) && !empty($shipping_mode['active']) ) {
						/** Check Country Shipping Limitation **/
						if ( empty($shipping_mode['limit_destination']) || ( !empty($shipping_mode['limit_destination']) && empty($shipping_mode['limit_destination']['country']) ) || ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['country']) && in_array($address_metadata['country'], $shipping_mode['limit_destination']['country']) ) ) {
							/** Check Limit Destination By Postcode **/
							$visible = true;
							/** Check Postcode limitation **/
							if ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['postcode']) ) {
								$postcodes = explode(',', $shipping_mode['limit_destination']['postcode'] );
								foreach( $postcodes as $postcode_id => $postcode ) {
									$postcodes[ $postcode_id ] = trim( str_replace( ' ', '', $postcode) );
								}
								if ( !in_array($address_metadata['postcode'], $postcodes) ) {
									$visible = false;
								}
							}
							/** Check Department limitation **/
							$department = isset( $address_metadata['postcode'] ) ? substr( $address_metadata['postcode'], 0, 2 ) : '';
							if ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['department']) ) {
								$departments = explode(',', $shipping_mode['limit_destination']['department'] );
								foreach( $departments as $department_id => $d ) {
									$departments[ $department_id ] = trim( str_replace( ' ', '', $d) );
								}

								if ( !in_array($department, $departments) ) {
									$visible = false;
								}
							}

							if ( $visible ) {
								$shipping_modes_to_display['modes'][$k] = $shipping_mode;
							}
						}
					}
				}
			}
		}
		return $shipping_modes_to_display;
	}


	/**
	 * Display shipping informations in order administration panel
	 * @param object $order : Order post infos
	 */
	function order_shipping_box( $order ) {
		$shipping_mode_option = get_option( 'wps_shipping_mode' );
		$order_postmeta = get_post_meta($order->ID, '_order_postmeta', true);
		$shipping_method_name = ( !empty($order_postmeta['order_payment']['shipping_method']) && !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && is_array($shipping_mode_option['modes']) && array_key_exists($order_postmeta['order_payment']['shipping_method'], $shipping_mode_option['modes'])) ? $shipping_mode_option['modes'][$order_postmeta['order_payment']['shipping_method']]['name'] : ( (!empty($order_postmeta['order_payment']['shipping_method']) ) ? $order_postmeta['order_payment']['shipping_method'] : '' );
		ob_start();
		require( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, $this->template_dir, "backend", "order-shipping-infos") );
		$output = ob_get_contents();
		ob_end_clean();
		echo $output;
	}

	/**
	 * Shortcode to display efficient shipping.
	 * @param  array $atts Attributes : pid, shipping_mode.
	 * @return float Shipping cost for product.
	 */
	function get_shipping_cost_shortcode( $atts ) {
		$shipping_modes = get_option( 'wps_shipping_mode' );
		$atts = shortcode_atts( array(
			'pid' => get_the_ID(),
			'shipping_mode' => '',
		), $atts, 'wps_product_shipping_cost' );
		$atts['pid'] = intval( $atts['pid'] );
		$wps_shipping = new wps_shipping();
		$items = array(
			$atts['pid'] => array(
				'item_id' => $atts['pid'],
				'item_qty' => 1,
			),
		);
		$cart_shipping_cost = $wps_shipping->calcul_cart_items_shipping_cost( $items );
		$cart_weight = $wps_shipping->calcul_cart_weight( $items );
		return floatval( $wps_shipping->get_shipping_cost( 1, 0, $cart_shipping_cost, $cart_weight, $atts['shipping_mode'] ) );
	}

}
