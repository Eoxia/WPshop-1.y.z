<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_cart {

	function __construct() {
		/** WPShop Cart Shortcode **/
		add_shortcode( 'wps_cart', array( &$this, 'display_cart' ) );
		add_shortcode( 'wpshop_cart', array( &$this, 'display_cart' ) );
		/** WPShop Mini Cart Shortcode **/
		add_shortcode( 'wps_mini_cart', array( &$this, 'display_mini_cart' ) );
		add_shortcode( 'wpshop_mini_cart', array( &$this, 'display_mini_cart' ) );
		/** WPShop Resume Cart Shorcode **/
		add_shortcode( 'wps_resume_cart', array( &$this, 'display_resume_cart' ) );
		add_shortcode( 'wpshop_resume_cart', array( &$this, 'display_resume_cart' ) );
		/** Apply Coupon Interface **/
		add_shortcode( 'wps_apply_coupon', array( &$this, 'display_apply_coupon_interface' ) );
		add_shortcode( 'wpshop_apply_coupon', array( &$this, 'display_apply_coupon_interface' ) );
		/** NUmeration Cart **/
		add_shortcode( 'wps-numeration-cart', array( &$this, 'display_wps_numeration_cart' ) );
		add_shortcode( 'wpshop-numeration-cart', array( &$this, 'display_wps_numeration_cart' ) );
		/** Button add to cart */
		add_shortcode( 'wpshop_button_add_to_cart', array( &$this, 'display_button_add_to_cart' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
		add_action( 'init', array( $this, 'load_cart_from_db' ) );

		/** Ajax Actions **/
		add_action( 'wp_ajax_wps_reload_cart', array( $this, 'wps_reload_cart' ) );
		add_action( 'wp_ajax_nopriv_wps_reload_cart', array( $this, 'wps_reload_cart' ) );

		add_action( 'wp_ajax_wps_reload_mini_cart', array( &$this, 'wps_reload_mini_cart' ) );
		add_action( 'wp_ajax_nopriv_wps_reload_mini_cart', array( &$this, 'wps_reload_mini_cart' ) );

		add_action( 'wp_ajax_wps_reload_summary_cart', array( &$this, 'wps_reload_summary_cart' ) );
		add_action( 'wp_ajax_nopriv_wps_reload_summary_cart', array( &$this, 'wps_reload_summary_cart' ) );

		add_action( 'wp_ajax_wps_apply_coupon', array( &$this, 'wps_apply_coupon' ) );
		add_action( 'wp_ajax_nopriv_wps_apply_coupon', array( &$this, 'wps_apply_coupon' ) );

		add_action( 'wp_ajax_wps_cart_pass_to_step_two', array( &$this, 'wps_cart_pass_to_step_two' ) );
		add_action( 'wp_ajax_nopriv_wps_cart_pass_to_step_two', array( &$this, 'wps_cart_pass_to_step_two' ) );

		add_action( 'wp_ajax_wps_empty_cart', array( &$this, 'wps_empty_cart' ) );
		add_action( 'wp_ajax_nopriv_wps_empty_cart', array( &$this, 'wps_empty_cart' ) );

		add_action( 'wsphop_options', array(&$this, 'declare_options' ), 8);
	}

	/**
	 * Add Scripts
	 */
	function add_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'wps_cart_js',  WPS_CART_URL . WPS_CART_DIR.'/assets/frontend/js/wps_cart.js' );
	}

	/**
	 * Declare Cart Options
	 */
	public static function declare_options () {
		if ( WPSHOP_DEFINED_SHOP_TYPE == 'sale' ) {
			$wpshop_shop_type = ! empty( $_POST['wpshop_shop_type'] ) ? sanitize_text_field( $_POST['wpshop_shop_type'] ) : '';
			$old_wpshop_shop_type = ! empty( $_POST['old_wpshop_shop_type'] ) ? sanitize_text_field( $_POST['old_wpshop_shop_type'] ) : '';

			if ( ( $wpshop_shop_type == '' || $wpshop_shop_type != 'presentation' )
				&& ( $old_wpshop_shop_type == '' || $old_wpshop_shop_type != 'presentation' ) ) {
					/**	Add module option to wpshop general options	*/
					register_setting('wpshop_options', 'wpshop_cart_option', array('wps_cart', 'wpshop_options_validate_cart_type'));
					add_settings_field('wpshop_cart_type', __('Which type of cart do you want to display', 'wpshop'), array('wps_cart', 'wpshop_cart_type_field'), 'wpshop_cart_info', 'wpshop_cart_info');
				}
		}
	}

	/**
	 * Validate Options Cart
	 * @param unknown_type $input
	 * @return unknown
	 */
	public static function wpshop_options_validate_cart_type( $input ) {
		return $input;
	}

	/**
	 * Cart Options Fields
	 */
	public static function wpshop_cart_type_field() {
		$cart_option = get_option( 'wpshop_cart_option' );

		$output  = '<select name="wpshop_cart_option[cart_type]">';
		$output .= '<option value="simplified_ati" ' .( ( ! empty($cart_option) && ! empty($cart_option['cart_type']) && $cart_option['cart_type'] == 'simplified_ati' ) ? 'selected="selected"' : ''). ' >' .__( 'Simplified cart ATI', 'wpshop'). '</option>';
		$output .= '<option value="simplified_et" ' .( ( ! empty($cart_option) && ! empty($cart_option['cart_type']) && $cart_option['cart_type'] == 'simplified_et' ) ? 'selected="selected"' : ''). ' >' .__( 'Simplified cart ET', 'wpshop'). '</option>';
		$output .= '<option value="full_cart" ' .( ( ! empty($cart_option) && ! empty($cart_option['cart_type']) && $cart_option['cart_type'] == 'full_cart' ) ? 'selected="selected"' : ''). ' >' .__( 'Full cart', 'wpshop'). '</option>';
		$output .= '</select>';

		echo $output;
	}

	/** Display Cart **/
	function display_cart( $args ) {
		$cart_type = ( ! empty($args) && ! empty($args['cart_type']) ) ?  $args['cart_type']: '';
		$oid =  ( ! empty($args) && ! empty($args['oid']) ) ?  $args['oid'] : '';
		$output  = '<div id="wps_cart_container" data-nonce="' . wp_create_nonce( 'wps_reload_cart' ) . '" class="wps-bloc-loader wps-cart-wrapper">';
		$output .= self::cart_content($cart_type, $oid);
		$output .= '</div>';

		return $output;
	}

	/** Cart Content **/
	public static function cart_content( $cart_type = '', $oid = '' ) {
		global $wpdb;
		$output = '';
		$account_origin = false;
		$cart_option = get_option( 'wpshop_cart_option' );
		$cart_option = ( ! empty($cart_option) && ! empty($cart_option['cart_type']) ) ? $cart_option['cart_type'] : 'simplified_ati';

		$price_piloting  = get_option( 'wpshop_shop_price_piloting' );

		$coupon_title = $coupon_value = '';
		$cart_content = ( ! empty($_SESSION) && ! empty($_SESSION['cart']) ) ? $_SESSION['cart'] : array();
		if( ! empty($oid) ) {
			$account_origin = true;
			$cart_content = get_post_meta( $oid, '_order_postmeta', true);
		}
		$currency = wpshop_tools::wpshop_get_currency( false );

		if ( ! empty($cart_content) ) {
			$cart_items = ( ! empty($cart_content['order_items']) ) ? $cart_content['order_items'] : array();

			if ( ! empty($cart_content['coupon_id']) ) {
				$coupon_title = get_the_title( $cart_content['coupon_id']);
				$coupon_value = wpshop_tools::formate_number( $cart_content['order_discount_amount_total_cart'] );
			}

			if ( ! empty($cart_items) ) {
				/** Total values **/
				$shipping_cost_et = ( ! empty($cart_content['order_shipping_cost']) ) ? ( (! empty($price_piloting) && $price_piloting != 'HT') ? ( $cart_content['order_shipping_cost'] / ( 1 + ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) ) ) : $cart_content['order_shipping_cost'] ) : 0;
				$shipping_cost_vat = ( ! empty( $shipping_cost_et) ) ? ( $shipping_cost_et * ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) ) : 0;
				$shipping_cost_ati = ( ! empty($cart_content['order_shipping_cost']) ) ? ( (! empty($price_piloting) && $price_piloting != 'HT') ? $cart_content['order_shipping_cost'] : $cart_content['order_shipping_cost'] + $shipping_cost_vat ) : 0;
				$total_et = ( ! empty( $cart_content['order_total_ht']) ) ? $cart_content['order_total_ht'] : 0;
				$order_totla_before_discount = ( ! empty($cart_content['order_grand_total_before_discount']) ) ? $cart_content['order_grand_total_before_discount'] : 0;
				$order_amount_to_pay_now = wpshop_tools::formate_number( $cart_content['order_amount_to_pay_now'] );
				$total_ati = ( ! empty( $order_amount_to_pay_now ) && ! empty($oid) && $order_amount_to_pay_now > 0 ) ? $cart_content['order_amount_to_pay_now'] : ( (! empty($cart_content['order_grand_total']) ) ? $cart_content['order_grand_total'] : 0 );
				$total_ht  = $cart_content['order_total_ht'] + $cart_content['order_shipping_cost'];
				unset($tracking);
				if( ! empty($cart_content['order_trackingNumber']) ) {
					$tracking['number'] = $cart_content['order_trackingNumber'];
				}
				if( ! empty($cart_content['order_trackingLink']) ) {
					$tracking['link'] = $cart_content['order_trackingLink'];
				}
				ob_start();
				require( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, "frontend", "cart/cart") );
				$output = ob_get_contents();
				ob_end_clean();
			}
			else {
				return '<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';;
			}
		}
		else {
			return '<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';;
		}
		return $output;
	}

	/** Display mini cart **/
	function display_mini_cart( $args ) {
		$price_piloting = get_option( 'wpshop_shop_price_piloting' );
		
		$total_cart_item = 0;
		$cart_content = ( ! empty($_SESSION) && ! empty($_SESSION['cart']) ) ? $_SESSION['cart'] : array();
		$type = ( ! empty($args) && ! empty($args['type']) ) ? $args['type'] : '';


		if ( ! empty($cart_content) ) {
			$cart_items = ( ! empty($cart_content['order_items']) ) ? $cart_content['order_items'] : array();
			/** Count items **/
			$total_cart_item = self::total_cart_items( $cart_items );
			$mini_cart_body = self::mini_cart_content( $type );
		}
		else {
			$mini_cart_body = self::mini_cart_content();
		}
		ob_start();
		if( ! empty($type) && $type == 'fixed' ) {
			require(wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, "frontend", "mini-cart/fixed-mini-cart") );
		}
		else {
			require( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, "frontend", "mini-cart/mini-cart") );
		}

		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/** Mini cart Content **/
	public static function mini_cart_content( $type = '') {
		$price_piloting  = get_option( 'wpshop_shop_price_piloting' );
		
		$currency = wpshop_tools::wpshop_get_currency( false );
		$cart_content = ( ! empty($_SESSION) && ! empty($_SESSION['cart']) ) ? $_SESSION['cart'] : array();
		$output = '';
		if ( ! empty($cart_content) ) {
			$cart_items = ( ! empty($cart_content['order_items']) ) ? $cart_content['order_items'] : array();
			if ( ! empty($cart_items) ) {
				if ( ! empty($cart_content['coupon_id']) ) {
					$coupon_title = get_the_title( $cart_content['coupon_id']);
					$coupon_value = wpshop_tools::formate_number( $cart_content['order_discount_amount_total_cart'] );
				}
				$order_total_before_discount = ( ! empty($cart_content['order_grand_total_before_discount']) ) ? $cart_content['order_grand_total_before_discount'] : 0;
				$shipping_cost_ati = ( ! empty($cart_content['order_shipping_cost']) ) ? $cart_content['order_shipping_cost'] : 0;
				$total_ati  = $total_cart = ( ! empty($cart_content['order_amount_to_pay_now']) ) ? $cart_content['order_amount_to_pay_now'] : 0;
				$total_ht  = $cart_content['order_total_ht'] + $cart_content['order_shipping_cost'];

				ob_start();
				if( ! empty($type) && $type == 'fixed' ) {
					require( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, "frontend", "mini-cart/fixed-mini-cart", "content") );
				}
				else {
					require( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, "frontend", "mini-cart/mini-cart", "content") );
				}
				$output = ob_get_contents();
				ob_end_clean();
			}
			else {
				$output = '<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';
			}
		}
		else {
			$output = '<div class="wps-alert-info">' . __( 'Your cart is empty', 'wpshop' ).'</div>';
		}
		return $output;
	}

	/** Resume Cart **/
	function display_resume_cart() {
		$cart_summary_content = self::resume_cart_content();
		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, "frontend", "resume-cart/resume-cart") );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/** Resume cart Content **/
	public static function resume_cart_content() {
		$price_piloting  = get_option( 'wpshop_shop_price_piloting' );
		
		$output = '';
		$currency = wpshop_tools::wpshop_get_currency( false );
		$cart_content = ( ! empty($_SESSION) && ! empty($_SESSION['cart']) ) ? $_SESSION['cart'] : array();
		if ( ! empty($cart_content) ) {
			$cart_items = ( ! empty($cart_content['order_items']) ) ? $cart_content['order_items'] : array();
			if ( ! empty($cart_items) ) {
				if ( ! empty($cart_content['coupon_id']) ) {
					$coupon_title = get_the_title( $cart_content['coupon_id']);
					$coupon_value = wpshop_tools::formate_number( $cart_content['order_discount_amount_total_cart'] );
				}
				$order_total_before_discount = ( ! empty($cart_content['order_grand_total_before_discount']) ) ? $cart_content['order_grand_total_before_discount'] : 0;
				$shipping_cost_ati = ( ! empty($cart_content['order_shipping_cost']) ) ? $cart_content['order_shipping_cost'] : 0;
				$total_ati  = $total_cart = ( ! empty($cart_content['order_amount_to_pay_now']) ) ? $cart_content['order_amount_to_pay_now'] : 0;
				$total_ht  = $cart_content['order_total_ht'] + $cart_content['order_shipping_cost'];
				ob_start();
				require_once( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, "frontend", "resume-cart/resume-cart", "content") );
				$output = ob_get_contents();
				ob_end_clean();
			}
			else {
				$resume_cart_body = '<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';
			}
		}
		else {
			$resume_cart_body ='<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';
		}
		return $output;
	}

	/**
	 * Count total items in cart
	 * @param array cart
	 * @return int total items
	 */
	public static function total_cart_items( $cart_items ) {
		$count = 0;
		if( ! empty($cart_items) && is_array( $cart_items )) {
			foreach( $cart_items as $cart_item ) {
				$count += $cart_item['item_qty'];
			}
		}
		return $count;
	}

	/**
	 * Reload Persistent cart for logged user and if a persistent cart exists
	 */
	function load_cart_from_db() {
		if(empty($_SESSION['cart']) && get_current_user_id() ) {
			$cart = $this->get_persistent_cart();
			$_SESSION['cart'] = ! empty( $cart ) && ! empty( $cart['cart'] ) ? $cart['cart'] : ( ! empty( $cart ) ? $cart : array() );
			$_SESSION['coupon'] = 0;
		}
	}

	/**
	 * Return Logged user persistent cart
	 * @return array()
	 */
	function get_persistent_cart() {
		if(get_current_user_id())
			$cart = get_user_meta(get_current_user_id(), '_wpshop_persistent_cart', true);
		return empty($cart) ? array() : $cart;
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
	function persistent_cart_update() {
		if(get_current_user_id())
			update_user_meta( get_current_user_id(), '_wpshop_persistent_cart', array(
					'cart' => $_SESSION['cart'],
			));
	}

	/**
	 * Delete the persistent cart
	 */
	function persistent_cart_destroy() {
		delete_user_meta( get_current_user_id(), '_wpshop_persistent_cart' );
	}

	/**
	 * Empty the current existing cart
	 *
	 */
	function empty_cart() {
		unset($_SESSION['cart']);
		$this->persistent_cart_destroy();
	}

	/**
	 * Change the product quantity into the cart
	 *
	 * @param integer $product_id The product identifier to change quantity for. Allow to check if the product is in cart again/if the roduct has enough stock
	 * @param float $quantity The asked quantity
	 *
	 * @return mixed If an error occured return a alert message. In the other case if the quantity is correctly set return true
	 */
	function set_product_qty($product_id, $quantity, $combined_variation_id = '', $cart = array(), $from_admin = '', $order_id = '' ) {
		// Init Cart var
		$cart = ( ! empty($cart) ) ? $cart : $_SESSION['cart'];
		$wpshop_cart_type = ( ! empty($cart) && ! empty($cart['cart_type']) ) ? $cart['cart_type'] : 'normal';
		$parent_product_id = $product_id;
		$selected_variations = array();

		// Test if Product exists
		if( ! empty($product_id) && ! empty($cart['order_items']) && ! empty( $cart['order_items'][ $product_id ] ) ) {
			// Test if is composed product ID
			$pid = $product_id;
			if (strpos($pid,'__') !== false) {
				$product_data_id = explode( '__', $pid );
				$pid = ( ! empty( $product_data_id ) && ! empty( $product_data_id[1] ) ) ? $product_data_id[1] : $cart['order_items'][ $product_id ]['item_id'];
			}

			// Checking stock
			$wps_product_ctr = new wps_product_ctr();
			$return = $wps_product_ctr->check_stock($pid, $quantity, $combined_variation_id );
			if( $return !== true) {
				return $return;
			}

			// Check Variations to construct product to add to cart
			if( ! empty($product_data_id) || get_post_type($product_id) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
				// Check Parent ID
				if( get_post_type($cart['order_items'][ $product_id ]['item_id']) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
					$parent_product_id = $cart['order_items'][ $product_id ]['item_id'];
				}
				else {
					$parent_data = wpshop_products::get_parent_variation( $cart['order_items'][ $product_id ]['item_id'] );
					$parent_post = $parent_data['parent_post'];
					$parent_product_id = $parent_post->ID;
				}
				if( ! empty($product_data_id) ) {
					unset( $product_data_id[0] );
					if( ! empty($product_data_id) ) {
						foreach( $product_data_id as $i ) {
							$mtdt = get_post_meta( $i, '_wpshop_variations_attribute_def', true );
							if( ! empty($mtdt) && is_array($mtdt) ) {
								$selected_variations = array_merge( $selected_variations, $mtdt );
							}
						}
					}
				}
				else {
					$selected_variations = get_post_meta( $product_id, '_wpshop_variations_attribute_def', true );
				}
			}

			$formatted_product = $this->prepare_product_to_add_to_cart( $parent_product_id, $quantity, $selected_variations );
			$product_to_add_to_cart = $formatted_product[0];
			$new_pid = $product_id;
			$return = $this->add_to_cart( $product_to_add_to_cart, array( $new_pid => $quantity ), $wpshop_cart_type, array(), $from_admin, $cart, $order_id );
			return $return;
		}
		else {
			return __('This product does not exist in the cart.', 'wpshop');
		}
	}

	/**
	 * Add a product to the cart
	 *
	 * @param string product_id	contains the id of the product to add to the cart.
	 * @param string quantity	contains the quantity of the item to add.
	 */
	function add_to_cart( $product_list, $quantity, $type = 'normal', $extra_params = array(), $from_admin = '', $order_meta = '', $order_id = '' ) {
		global $wpdb;

		$count_items = 0;

		/** Check if a cart already exist. If there is already a cart that is not the same type (could be a cart or a quotation)	*/
		if ( empty( $from_admin ) ) {
			if ( isset( $_SESSION['cart']['cart_type'] ) && $type !== $_SESSION['cart']['cart_type'] ) {
				return __( 'You have another element type into your cart. Please finalize it by going to cart page.', 'wpshop' );
			} else {
				$_SESSION['cart']['cart_type'] = $type;
			}
			$order_meta = $_SESSION['cart'];
		}

		$order_items = array();

		foreach ( $product_list as $pid => $product_more_content ) {
			if ( 1 === count( $product_list ) ) {
				if ( ! isset( $quantity[ $pid ] ) ) {
					$quantity[ $pid ] = 1;
				}
				$product = wpshop_products::get_product_data( $product_more_content['id'], false, '"publish", "free_product"' );
				/** Check if the selected product exist	*/
				if ( false === $product ) {
					return __( 'This product does not exist', 'wpshop' );
				}

				/** Get information about the product price	*/
				$product_price_check = wpshop_prices::get_product_price( $product, 'check_only' );
				if ( true !== $product_price_check ) {
					return $product_price_check;
				}

				$the_quantity = 1;
				/** Get the asked quantity for each product and check if there is enough stock	*/
				$the_quantity = $quantity[ $pid ];

				$variation_id = 0;
				if ( ! empty( $product_more_content ) && ! empty( $product_more_content['variations'] ) && ! empty( $product_more_content['variations'][0] ) && ! empty( $product_more_content['defined_variation_priority'] ) && ( 'combined' === $product_more_content['defined_variation_priority'] ) ) {
					$variation_id = $product_more_content['variations'][0];
				}
				//$quantity_to_check = ( ! empty($_SESSION) && ! empty($_SESSION['cart']) && ! empty($_SESSION['cart']['order_items']) && ! empty($_SESSION['cart']['order_items'][ $pid ]) && ! empty($_SESSION['cart']['order_items'][ $pid ]['item_qty'])  ) ? $_SESSION['cart']['order_items'][ $pid ]['item_qty'] : $the_quantity;

				$wps_product_ctr = new wps_product_ctr();
				$product_stock = $wps_product_ctr->check_stock( $product_more_content['id'], $the_quantity, $variation_id );
				if ( true !== $product_stock ) {
					return $product_stock;
				}
				$count_items += (int) $product_more_content['product_qty'];
			}

			$order_items[ $pid ]['product_id'] = $product_more_content['id'];
			$order_items[ $pid ]['product_qty'] = $the_quantity;

			/** For product with variation	*/
			$order_items[ $pid ]['product_variation_type'] = ! empty( $product_more_content['variation_priority'] ) ? $product_more_content['variation_priority'] : '';
			$order_items[ $pid ]['free_variation'] = ! empty( $product_more_content['free_variation'] ) ? $product_more_content['free_variation'] : '';
			$order_items[ $pid ]['product_variation'] = '';
			if ( ! empty( $product_more_content['variations'] ) ) {
				foreach ( $product_more_content['variations'] as $variation_id ) {
					$order_items[ $pid ]['product_variation'][] = $variation_id;
				}
			}
		}

		$current_cart = ( ! empty( $order_meta )) ? $order_meta : array();

		$current_cart_order_items = isset( $current_cart['order_items'] ) ? $current_cart['order_items'] : array();

		foreach ( $current_cart_order_items as $item_id => $item ) {
			if ( ! array_key_exists( $item_id, $order_items ) ) {
				$count_items += $item['item_qty'];
			}
		}
		$wpshop_cart_option = get_option( 'wpshop_cart_option' );
		if ( ! empty( $wpshop_cart_option ) && ! empty( $wpshop_cart_option['total_nb_of_item_allowed'] ) && ! empty( $wpshop_cart_option['total_nb_of_item_allowed'][0] ) && (int) $count_items > (int) $wpshop_cart_option['total_nb_of_item_allowed'][0] ) {
			return __( 'No more products can be added to your cart.', 'wpshop' );
		}

		$order = $this->calcul_cart_information( $order_items, $extra_params, $current_cart );

		if ( empty( $from_admin ) ) {
			self::store_cart_in_session( $order );
			/** Store the cart into database for connected user */
			if ( get_current_user_id() ) {
				$this->persistent_cart_update();
			}
		} else {
			update_post_meta( $order_id, '_order_postmeta', $order );
		}
		return 'success';
	}

	function prepare_product_to_add_to_cart( $product_id, $product_qty, $wpshop_variation_selected = array() ) {
		$product_price = '';
		$product_data = wpshop_products::get_product_data($product_id);

		// Free vars
		if ( ! empty($wpshop_variation_selected['free']) ){
			$free_variations = $wpshop_variation_selected['free'];
			unset($wpshop_variation_selected['free']);
		}

		// If product have many variations
		if ( count($wpshop_variation_selected ) > 1 ) {
			if ( ! empty($wpshop_variation_selected) ) {
				$product_with_variation = wpshop_products::get_variation_by_priority( $wpshop_variation_selected, $product_id, true );
			}

			if ( ! empty($product_with_variation[$product_id]['variations']) ) {
				$product = $product_data;
				$has_variation = true;
				$head_product_id = $product_id;

				if ( ! empty($product_with_variation[$product_id]['variations']) && ( count($product_with_variation[$product_id]['variations']) == 1 ) && ($product_with_variation[$product_id]['variation_priority'] != 'single') ) {
					$product_id = $product_with_variation[$product_id]['variations'][0];
				}
				$product = wpshop_products::get_product_data($product_id, true);

				$the_product = array_merge( array(
						'product_id'	=> $product_id,
						'product_qty' 	=> $product_qty
				), $product);

				/*	Add variation to product into cart for storage	*/
				if ( ! empty($product_with_variation[$head_product_id]['variations']) ) {
					$the_product = wpshop_products::get_variation_price_behaviour( $the_product, $product_with_variation[$head_product_id]['variations'], $head_product_id, array('type' => $product_with_variation[$head_product_id]['variation_priority']) );
				}

				$product_data = $the_product;
			}
		}

		$product_to_add_to_cart = array( $product_id => array( 'id' => $product_id, 'product_qty' => $product_qty ) );

		if ( ! empty( $wpshop_variation_selected ) ) {
			$variation_calculator = wpshop_products::get_variation_by_priority($wpshop_variation_selected, $product_id, true );
			if ( ! empty($variation_calculator[$product_id]) ) {
				$product_to_add_to_cart[$product_id] = array_merge($product_to_add_to_cart[$product_id], $variation_calculator[$product_id]);
			}
		}

		$new_pid = $product_id;
		//Create custom ID on single variations Product
		if( ! empty($product_to_add_to_cart[$product_id]['variations']) && count( $product_to_add_to_cart[$product_id]['variations'] ) && ! empty( $product_to_add_to_cart[$product_id]['variation_priority'] ) && $product_to_add_to_cart[$product_id]['variation_priority'] == 'single' ) {
			$tmp_obj = $product_to_add_to_cart[$product_id];
			unset( $product_to_add_to_cart[$product_id] );
			$key = $product_id;
			foreach( $tmp_obj['variations'] as $variation_key) {
				$key.= '__'. $variation_key;
			}
			$product_to_add_to_cart[$key] = $tmp_obj;
			$new_pid = $key;
		}
		// Add free variations
		if( ! empty($free_variations) ) {
			$product_to_add_to_cart[$new_pid]['free_variation'] = $free_variations;
		}
		return array( $product_to_add_to_cart, $new_pid );
	}

	/**
	 * Add product if is queried and recalcule cart informations
	 *
	 * @param  array   $product_list             A list of roduct to add to curent cart.
	 * @param  string  $custom_order_information NOT USED!!!
	 * @param  array   $current_cart             [Current cart for current user.
	 * @param  boolean $from_admin               Is the cart builded from admin or from frontend.
	 *
	 * @return array                             The cart content.
	 */
	function calcul_cart_information( $product_list, $custom_order_information = '', $current_cart = array(), $from_admin = false ) {
		// Price piloting option.
		$price_piloting = get_option( 'wpshop_shop_price_piloting' );

		// Init vars.
		$cart_infos = ( ! empty( $current_cart ) ) ? $current_cart : ( ( ! empty( $_SESSION ) && ! empty( $_SESSION['cart'] ) && ! $from_admin ) ? $_SESSION['cart'] : array() );
		$cart_items = ( ! empty( $current_cart ) && ! empty( $current_cart['order_items'] ) ) ? $current_cart['order_items'] : array();
		$cart_items = ( ! empty( $_SESSION ) && ! empty( $_SESSION['cart'] ) && ! empty( $_SESSION['cart']['order_items'] ) && ! $from_admin ) ? $_SESSION['cart']['order_items'] : $cart_items;
		$order_total_ht = $order_total_ttc = $total_vat = 0;
		$order_tva = array();
		$total_weight = $nb_of_items = $order_shipping_cost_by_article = 0;
		$order_discount_rate = $order_discount_amount = $order_items_discount_amount = $order_total_discount_amount = 0;
		$cart_infos['order_amount_to_pay_now'] = 0;

		// If Product list is not empty, add products to order.
		if ( ! empty( $product_list ) ) {
			foreach ( $product_list as $product_id => $d ) {
				$product_key = $product_id;
				if ( isset( $d['product_qty'] ) ) {
					// Formate datas.
					$product_id = $head_product_id = $d['product_id'];
					$product_qty = $d['product_qty'];
					$product_variation = ! empty( $d['product_variation'] ) ? $d['product_variation'] : null;

					// If product is a single variation product.
					if ( ! empty( $product_variation ) && ( 1 === count( $product_variation ) ) ) {
						$product_id = $product_variation[0];
					}

					// Construct final product.
					$default_status = array( '"publish"', '"free_product"' );
					if ( true === $from_admin ) {
						$default_status[] = '"private"';
					}
					$product = wpshop_products::get_product_data( $d['product_id'], true, implode( ', ', $default_status ) );
					$the_product = array_merge( array(
						'product_id'	=> $d['product_id'],
						'product_qty' => $product_qty,
					), $product );

					// Add variation to product into cart for storage.
					if ( ! empty( $product_variation ) ) {
						$the_product = wpshop_products::get_variation_price_behaviour( $the_product, $product_variation, $head_product_id, array( 'type' => $d['product_variation_type'] ) );
					}

					// Free Variations Checking.
					if ( ! empty( $d['free_variation'] ) ) {
						$the_product['item_meta']['free_variation'] = $d['free_variation'];
						$head_product_id = $the_product['product_id'];
					}

					// If product is a variation, we check parent product general.
					if ( get_post_type( $the_product['product_id'] ) === (string) WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
						$parent_def = wpshop_products::get_parent_variation( $the_product['product_id'] );
						if ( ! empty( $parent_def ) && ! empty( $parent_def['parent_post'] ) ) {
							$variation_def = get_post_meta( $parent_def['parent_post']->ID, '_wpshop_variation_defining', true );
							$parent_meta = $parent_def['parent_post_meta'];
							if ( ! empty( $variation_def ) && ! empty( $variation_def['options'] ) && ! empty( $variation_def['options']['priority'] ) && in_array( 'combined', $variation_def['options']['priority'], true )
									&& ! empty( $variation_def['options']['price_behaviour'] ) && in_array( 'addition', $variation_def['options']['price_behaviour'], true ) && ! empty( $variation_def['attributes'] ) && count( $variation_def['attributes'] ) > 1 ) {
								$the_product['product_price'] += number_format( str_replace( ',', '.', $parent_meta['product_price'] ), 2, '.', '' );
								$the_product['price_ht'] += number_format( str_replace( ',', '.',$parent_meta['price_ht'] ) , 2, '.', '' );
								$the_product['tva'] += number_format( str_replace( ',', '.', $parent_meta['tva'] ) , 2, '.', '' );
							}
						}
					}

					// Delete product if its qty is equals to zero, else add this product to order.
					if ( empty( $d['product_qty'] ) ) {
						unset( $cart_items[ $product_key ] );
						unset( $cart_infos['order_items'][ $product_key ] );
					} else {
						$wps_orders = new wps_orders_ctr();
						$cart_items[ $product_key ] = $wps_orders->add_product_to_order( $the_product );
					}
				}
			}
		}

		// Add automaticaly Add-to-cart Products.
		$cart_items = $this->add_automaticaly_product_to_cart( $cart_items );

		// Calcul Cart Informations.
		$cart_has_special_product = false;
		if ( ! empty( $cart_items ) && is_array( $cart_items ) ) {
			foreach ( $cart_items as $item_id => $item ) {

				if ( ! empty( $item['item_amount_to_pay_now'] ) ) {
					$cart_has_special_product = true;
					$cart_infos['order_amount_to_pay_now'] += ( $item['item_amount_to_pay_now'] * $item['item_qty'] );
				} else {
					$cart_infos['order_amount_to_pay_now'] += $item['item_total_ttc'];
				}

				$order_total_ht += $item['item_total_ht'];
				$order_total_ttc += $item['item_total_ttc'];
				// VAT.
				if ( ! empty( $order_tva[ $item['item_tva_rate'] ] ) ) {
					$order_tva[ $item['item_tva_rate'] ] += $item['item_tva_total_amount'];
				} else {
					$order_tva[ $item['item_tva_rate'] ] = $item['item_tva_total_amount'];
				}
			}
		} else {
			return array();
		}

		// Apply informations to cart.
		$cart_infos['order_items'] = $cart_items;
		$cart_infos['order_total_ht'] = $order_total_ht;
		$cart_infos['order_total_ttc'] = $order_total_ttc;

		// Calcul Shipping cost.
		if ( ! $from_admin && empty( $cart_infos['order_shipping_cost_fixe'] ) && empty( $_SESSION['wps-pos-addon'] ) ) {
			$wps_shipping = new wps_shipping();
			$total_cart_ht_or_ttc_regarding_config = ( ! empty( $price_piloting ) && ( 'HT' === $price_piloting ) ) ? $cart_infos['order_total_ht'] : $cart_infos['order_total_ttc'];
			$cart_weight = $wps_shipping->calcul_cart_weight( $cart_infos['order_items'] );
			$total_shipping_cost_for_products = $wps_shipping->calcul_cart_items_shipping_cost( $cart_infos['order_items'] );
			foreach ( $cart_infos['order_items'] as $item ) {
				if ( ! empty( $item['item_is_downloadable_'] ) && strtolower( __( $item['item_is_downloadable_'], 'wpshop' ) ) === __( 'yes', 'wpshop' ) ) {
					$is_downloadable_products = 'is_downloadable_';
				} else {
					$is_downloadable_products = '';
					break;
				}
			}

			$cart_infos['order_shipping_cost'] = $wps_shipping->get_shipping_cost( count( $cart_infos['order_items'] ), $total_cart_ht_or_ttc_regarding_config, $total_shipping_cost_for_products, $cart_weight, $is_downloadable_products );
			$cart_infos['order_amount_to_pay_now'] += $cart_infos['order_shipping_cost'];
		}

		// If Price piloting is ET, calcul VAT on Shipping cost.
		if ( ! empty( $price_piloting ) && ( 'HT' === (string) $price_piloting ) ) {
			$shipping_cost_vat = ( ! empty( $cart_infos['order_shipping_cost'] ) ) ? ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) * number_format( $cart_infos['order_shipping_cost'], 2, '.', '' ) : 0;
			$order_tva['VAT_shipping_cost'] = $shipping_cost_vat;
			$cart_infos['order_amount_to_pay_now'] += $shipping_cost_vat;
		}

		// Calcul VAT Total.
		if ( ! empty( $order_tva ) ) {
			foreach ( $order_tva as $vat_rate => $vat_value ) {
				$total_vat += $vat_value;
			}
		}

		// Recap totals.
		$cart_infos['order_total_ttc'] = ( $cart_infos['order_total_ht'] + ( ! empty( $cart_infos ) && ! empty( $cart_infos['order_shipping_cost'] )  ? $cart_infos['order_shipping_cost'] : 0 ) + $total_vat );
		$cart_infos['order_grand_total_before_discount'] = $cart_infos['order_grand_total'] = $cart_infos['order_total_ttc'];

		// Total to pay now.
		$total_received = 0;
		if ( ! empty( $cart_infos['order_payment']['received'] ) ) {
			foreach ( $cart_infos['order_payment']['received'] as $received ) {
				$total_received += ( ( ! empty( $received['status'] ) && ( 'payment_received' === $received['status'] ) && ! empty( $received['received_amount'] ) ) ? $received['received_amount'] : 0 );
			}
		}
		$cart_infos['order_amount_to_pay_now'] = $cart_infos['order_amount_to_pay_now'] - $total_received;

		// Apply cart rules.
		$cart_rule = wpshop_cart_rules::get_cart_rule( $cart_infos['order_grand_total'] );
		if ( $cart_rule['cart_rule_exist'] ) {
			if ( ! empty( $cart_rule['cart_rule_info']['discount_type'] ) ) {
				if ( 'absolute_discount' == $cart_rule['cart_rule_info']['discount_type'] ) {
					$cart_infos['order_discount_type'] = 'amount';
				}
				if ( 'percent_discount' == $cart_rule['cart_rule_info']['discount_type'] ) {
					$cart_infos['order_discount_type'] = 'percent';
				}
			}
			$cart_infos['order_discount_value'] = $cart_rule['cart_rule_info']['discount_value'];
		}

		// Apply coupons.
		if ( ! empty( $_SESSION['cart'] ) && ! $from_admin ) {
			if ( ! empty( $_SESSION['cart']['coupon_id'] ) ) {
				$wps_coupon_mdl = new wps_coupon_model();
				$coupon = $wps_coupon_mdl->get_coupon_data( $_SESSION['cart']['coupon_id'] );
				if ( ! empty( $coupon ) && ! empty( $coupon['wpshop_coupon_code'] ) ) {
					$wps_coupon = new wps_coupon_ctr();
					$coupon_checking = $wps_coupon->applyCoupon( $coupon['wpshop_coupon_code'] );
					// If Coupon conditions are Ok.
					if ( ! empty( $coupon_checking ) && ! empty( $coupon_checking['status'] ) && ( true == $coupon_checking['status'] ) ) {
						$cart_infos['order_discount_type'] = $coupon['wpshop_coupon_discount_type'];
						$cart_infos['order_discount_value'] = $coupon['wpshop_coupon_discount_value'];
					}
				}
			}
		}

		// Checking Discounts.
		if ( ! empty( $cart_infos['order_discount_type'] ) && isset( $cart_infos['order_discount_value'] ) ) {
			// Calcul discount on Order.
			switch ( $cart_infos['order_discount_type'] ) {
				case 'amount':
					$cart_infos['order_discount_amount_total_cart'] = number_format( str_replace( ',', '.', $cart_infos['order_discount_value'] ), 2, '.', '' );
				break;
				case 'percent':
					$cart_infos['order_discount_amount_total_cart'] = number_format( $cart_infos['order_grand_total'], 2, '.', '' ) * ( number_format( str_replace( ',', '.', $cart_infos['order_discount_value'] ), 2, '.', '' ) / 100);
				break;
			}
			if ( number_format( $cart_infos['order_discount_amount_total_cart'], 2, '.', '' ) > number_format( $cart_infos['order_grand_total'], 2, '.', '' ) ) {
				$cart_infos['order_grand_total'] = 0;
			} else {
				$cart_infos['order_grand_total'] -= number_format( $cart_infos['order_discount_amount_total_cart'], 2, '.', '' );
			}
			$cart_infos['order_amount_to_pay_now'] = number_format( $cart_infos['order_grand_total'] - $total_received, 2, '.', '' );
		}

		// Apply Partial Payments.
		$wpshop_payment = new wpshop_payment();
		if ( ! empty( $cart_infos['cart_type'] ) && ( 'quotation' === $cart_infos['cart_type'] ) ) {
			$partial_payment = $wpshop_payment->partial_payment_calcul( $cart_infos['order_grand_total'], 'for_quotation' );
		} else {
			$partial_payment = $wpshop_payment->partial_payment_calcul( $cart_infos['order_grand_total'] );
		}
		if ( ! empty( $partial_payment['amount_to_pay'] ) && ( empty( $cart_infos['order_status'] ) || ( 'awaiting_payment' === $cart_infos['order_status'] ) ) && empty( $product_partial_payment_amount ) ) {
			unset( $partial_payment['display'] );
			$cart_infos['order_partial_payment'] = number_format( str_replace( ',', '.', $partial_payment['amount_to_pay'] ), 2, '.', '' );
			$cart_infos['order_amount_to_pay_now'] = number_format( str_replace( ',', '.', $partial_payment['amount_to_pay'] ), 2, '.', '' );
		} elseif ( ! empty( $cart_has_special_product ) ) { // Apply Partial Payments : Subscription part.
			$cart_infos['order_amount_to_pay_now'] = number_format( str_replace( ',', '.', $cart_infos['order_amount_to_pay_now'] ), 2, '.', '' );
			$cart_infos['order_product_partial_payment'] = 'subscription';
		}

		// Cart Type.
		if ( isset( $_SESSION['cart']['cart_type'] ) ) {
			$cart_infos['cart_type'] = $_SESSION['cart']['cart_type'];
		}

		// Apply Extra actions on cart infos.
		$cart_infos = apply_filters( 'wps_extra_calcul_in_cart', $cart_infos, $_SESSION );

		return $cart_infos;
	}

	/**
	 * Add automaticaly products to cart if option is defined
	 * @param array $cart_items
	 * @return array
	 */
	function add_automaticaly_product_to_cart( $cart_items ) {
		global $wpdb;
		// Recovery all products with options
		$query = $wpdb->prepare("SELECT post_id, meta_value FROM " . $wpdb->postmeta . " WHERE meta_key = %s ", '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_options');
		$post_list_with_options = $wpdb->get_results($query);
		$wps_orders = new wps_orders_ctr();
		if ( ! empty($post_list_with_options) && ! empty($cart_items) ) {
			foreach ( $post_list_with_options as $product_info) {
				$product_meta = unserialize($product_info->meta_value);
				if ( ! empty($product_meta['cart']) && ! empty($product_meta['cart']['auto_add']) && ($product_meta['cart']['auto_add'] == 'yes') && empty($cart_items[$product_info->post_id]) ) {
					$product = wpshop_products::get_product_data($product_info->post_id, true, '"draft", "publish"');
					$the_product = array_merge( array(
							'product_id'	=> $product_info->post_id,
							'product_qty' 	=> 1
					), $product);
					$cart_items[$product_info->post_id] = $wps_orders->add_product_to_order($the_product);
				}
			}
		}
		return $cart_items;
	}


	/** Ajax action to reload cart **/
	public static function wps_reload_cart() {
		check_ajax_referer( 'wps_reload_cart' );

		$wps_cart = new wps_cart();
		$result = $wps_cart->cart_content();

		wp_die( json_encode( array( 'response' => $result) ) );
	}


	/** Ajax action to reload mini cart */
	public function wps_reload_mini_cart() {
		$_wpnonce = ! empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_reload_mini_cart' ) )
			wp_die();

		$wps_cart = new wps_cart();
		$result = $wps_cart->mini_cart_content( sanitize_title( $_POST['type']) );
		$count_items = ( ! empty($_SESSION) && ! empty($_SESSION['cart']) && ! empty($_SESSION['cart']['order_items'])  ) ? $wps_cart->total_cart_items( $_SESSION['cart']['order_items'] ) : 0;
		$free_shipping_alert = wpshop_tools::create_custom_hook('wpshop_free_shipping_cost_alert');

		echo json_encode( array( 'response' => $result, 'count_items' => $count_items, 'free_shipping_alert' => $free_shipping_alert) );
		die();
	}


	/**
	 * Display the number of products in cart
	 * @return string
	 */
	function display_wps_numeration_cart() {
		$cart_items = ( ! empty($_SESSION) && ! empty($_SESSION['cart']) && ! empty($_SESSION['cart']['order_items']) ) ? $_SESSION['cart']['order_items'] : array();
		$total_cart_item = self::total_cart_items( $cart_items );

		ob_start();
		require(wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, "frontend", "cart/numeration-cart") );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	public function display_button_add_to_cart( $args ) {
		$button_text = __( 'Add to cart', 'wpshop' );
		$use_button = true;
		$output = __( 'No product has been found.', 'wpshop' );

		if ( ! empty( $args ) && ! empty( $args['pid'] ) ) {

			/** The user send the text in argument */
			if ( ! empty( $args['text' ] ) ) {
				$button_text = $args['text'];
			}

			/** The user send use input */
			if ( ! empty( $args['use_button'] ) && 'false' == $args['use_button'] ) {
				$use_button = false;
			}

			ob_start();
			require( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, 'frontend/cart', 'button', 'add-to-cart' ) );
			$output = ob_get_clean();
		}

		return $output;
	}

	/** Ajax action to reload summary cart */
	public static function wps_reload_summary_cart() {
		check_ajax_referer( 'wps_reload_summary_cart' );

		$wps_cart = new wps_cart();
		$result = $wps_cart->resume_cart_content();

		wp_die( json_encode( array( 'response' => $result, ) ) );
	}


	/** Display Apply Coupon Interface **/
	function display_apply_coupon_interface() {
		$output = '';
		if ( ! empty( $_SESSION) && ! empty($_SESSION['cart']) && ! empty($_SESSION['cart']['order_items']) ) {
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_CART_DIR, WPS_CART_TPL_DIR, "frontend", "coupon/apply_coupon") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}


	/** AJAX - action to apply coupon **/
	function wps_apply_coupon() {
		$_wpnonce = ! empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_apply_coupon' ) )
			wp_die();

		$status = false; $response = '';
		$coupon = ( ! empty($_POST['coupon_code']) ) ? wpshop_tools::varSanitizer( $_POST['coupon_code']) : null;
		if( ! empty($coupon) ) {
			$wps_coupon_ctr = new wps_coupon_ctr();
			$result = $wps_coupon_ctr->applyCoupon($coupon);
			if ($result['status']===true) {
				$order = $this->calcul_cart_information(array());
				$this->store_cart_in_session($order);
				$status = true;
				$response = '<div class="wps-alert-success">' .__( 'The coupon has been applied', 'wpshop' ). '</div>';
			}
			else {
				$response = '<div class="wps-alert-error">' .$result['message']. '</div>';
			}
		}
		else {
			$response = '<div class="wps-alert-error">'.__( 'A coupon code is required', 'wpshop'). '</div>';
		}
		echo json_encode( array( 'status' => $status, 'response' => $response ) );
		die();
	}


	/**
	 * AJAX - Pass to step two in the Checkout tunnel
	 */
	public static function wps_cart_pass_to_step_two() {
		$_wpnonce = ! empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_cart_pass_to_step_two' ) )
			wp_die();

		$status = false; $response = '';
		$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
		if( ! empty($checkout_page_id) ) {
			$permalink_option = get_option( 'permalink_structure' );
			$step = ( get_current_user_id() != 0 ) ?  3 : 2;
			$response = get_permalink( $checkout_page_id  ).( ( ! empty($permalink_option) ) ? '?' : '&').'order_step='.$step;
			$response = apply_filters('wps_extra_signup_actions', $response);
			$status = true;
		}
		else {
			$response = '<div class="wps-alert-error">' .__( 'An error was occured, please retry later or contact the website administrator', 'wpshop' ). '</div>';
		}
		echo json_encode( array( 'status' => $status, 'response' => $response));
		die();
	}


	/**
	 * AJAX - Empty the cart
	 */
	function wps_empty_cart() {
		$_wpnonce = ! empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_empty_cart' ) )
			wp_die();


		$this->empty_cart();
		echo json_encode( array( 'status' => true) );
		die();
	}
}
