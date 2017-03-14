<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_coupon_ctr {
	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_COUPON_DIR;

	function __construct() {
		$this->template_dir = WPS_COUPON_PATH . WPS_COUPON_DIR . "/templates/";
		add_shortcode( 'wps_coupon', array($this, 'display_coupons') );

		$wpshop_shop_type = get_option('wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE);
		if ( $wpshop_shop_type == 'sale' ) {
			add_action( 'init', array( $this, 'create_coupon_custom_type' ) );
			add_action('add_meta_boxes', array( $this, 'add_meta_boxes') );
			add_action('manage_'.WPSHOP_NEWTYPE_IDENTIFIER_COUPON.'_posts_custom_column',  array( $this, 'wps_coupons_custom_columns'));
			add_filter('manage_edit-'.WPSHOP_NEWTYPE_IDENTIFIER_COUPON.'_columns', array( $this, 'wps_coupons_edit_columns'));
			add_action('save_post', array($this, 'save_coupon_custom_informations'));
		}
	}

	/**
	 * Create Coupon custom post type
	 */
	function create_coupon_custom_type() {
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_COUPON, array(
		'labels' => array(
		'name' 					=> __('Coupons', 'wpshop'),
		'singular_name' 		=> __('coupon', 'wpshop'),
		'add_new' 				=> __('Add coupon', 'wpshop'),
		'add_new_item' 			=> __('Add New coupon', 'wpshop'),
		'edit' 					=> __('Edit', 'wpshop'),
		'edit_item' 			=> __('Edit coupon', 'wpshop'),
		'new_item' 				=> __('New coupon', 'wpshop'),
		'view' 					=> __('View coupon', 'wpshop'),
		'view_item' 			=> __('View coupon', 'wpshop'),
		'search_items' 			=> __('Search coupons', 'wpshop'),
		'not_found' 			=> __('No coupons found', 'wpshop'),
		'not_found_in_trash' 	=> __('No coupons found in trash', 'wpshop'),
		'parent-item-colon' 	=> ''
				),
				'description' 					=> __('This is where store coupons are stored.', 'wpshop'),
				'public' 						=> true,
				'show_ui' 						=> true,
				'capability_type' 				=> 'post',
				'publicly_queryable' 			=> false,
				'exclude_from_search' 			=> true,
				'show_in_menu' 					=> 'edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
				'show_in_admin_bar'				=> false,
				'hierarchical' 					=> false,
				'show_in_nav_menus' 			=> false,
				'rewrite' 						=> false,
				'query_var' 					=> true,
				'supports' 						=> array( 'title', ),
				'has_archive' 					=> false
		));
	}

	/**
	 * Add Meta box to Coupon cutom post type
	 */
	function add_meta_boxes() {
		add_meta_box('wpshop_coupon_main_info', __('Informations', 'wpshop'), array( $this, 'wps_coupon_info_box'), WPSHOP_NEWTYPE_IDENTIFIER_COUPON, 'normal', 'high');
	}

	/**
	 * Coupon custom type Meta box content
	 */
	function wps_coupon_info_box() {
		$output = '';
		$default_currency = wpshop_tools::wpshop_get_currency( false );
		// Coupon Datas
		$metadata = get_post_custom();
		$coupon_code = !empty($metadata['wpshop_coupon_code'][0]) ? $metadata['wpshop_coupon_code'][0] : null;
		$coupon_discount_amount = !empty($metadata['wpshop_coupon_discount_value'][0]) ? $metadata['wpshop_coupon_discount_value'][0] : null;
		$wpshop_coupon_discount_type = !empty($metadata['wpshop_coupon_discount_type'][0]) ? $metadata['wpshop_coupon_discount_type'][0] : null;

		$coupon_receiver = !empty($metadata['wpshop_coupon_individual_use'][0]) ? unserialize($metadata['wpshop_coupon_individual_use'][0]) : array();
		$coupon_limit_usage = !empty($metadata['wpshop_coupon_usage_limit'][0]) ? $metadata['wpshop_coupon_usage_limit'][0] : '';

		$wpshop_coupon_minimum_amount = ( !empty($metadata['wpshop_coupon_minimum_amount'][0]) ) ? $metadata['wpshop_coupon_minimum_amount'][0] : '';
		$wpshop_coupon_minimum_amount = unserialize( $wpshop_coupon_minimum_amount );
		ob_start();
		require( wpshop_tools::get_template_part( WPS_COUPON_DIR, $this->template_dir, "backend", "coupon-metabox") );
		$output = ob_get_contents();
		ob_end_clean();

		echo $output;
	}

	/**
	 * Add custom columns to coupons list in administration
	 */
	function wps_coupons_custom_columns( $column ) {
		global $post;

		$metadata = get_post_custom();
		$wpshop_coupon_discount_type = !empty($metadata['wpshop_coupon_discount_type'][0]) ? $metadata['wpshop_coupon_discount_type'][0] : null;
		switch( $column ){
			case "coupon_code":
				echo $metadata['wpshop_coupon_code'][0];
				break;
			case "coupon_discount_amount":
				$currency = wpshop_tools::wpshop_get_currency( false );
				echo $metadata['wpshop_coupon_discount_value'][0].' '.( (!empty($wpshop_coupon_discount_type) && $wpshop_coupon_discount_type == 'percent') ? '%' : $currency) ;
				break;
		}
	}

	/**
	 * Set the custom colums
	 * @return array
	 */
	function wps_coupons_edit_columns() {
		$columns = array(
				'cb' => '<input type="checkbox" />',
				'title' => __('Name', 'wpshop'),
				'coupon_code' => __('Code', 'wpshop'),
				'coupon_discount_amount' => __('Discount amount', 'wpshop'),
		);
		return $columns;
	}

	/**
	 * Save custom informations on Save post action
	 */
	function save_coupon_custom_informations( $post_id ) {
		if ( wp_is_post_revision( $post_id ) )
			return;

		if( !empty($post_id) && (get_post_type($post_id) == WPSHOP_NEWTYPE_IDENTIFIER_COUPON) ) {
			$wps_coupon_mdl = new wps_coupon_model();

			$data = array(
				'wpshop_coupon_mini_amount' => !empty( $_POST['wpshop_coupon_mini_amount'] ) ? sanitize_text_field( $_POST['wpshop_coupon_mini_amount'] ) : '',
				'wpshop_coupon_min_mount_shipping_rule' => !empty( $_POST['wpshop_coupon_min_mount_shipping_rule'] ) ? sanitize_text_field( $_POST['wpshop_coupon_min_mount_shipping_rule'] ) : '',
				'coupon_code' => !empty( $_POST['coupon_code'] ) ? sanitize_text_field( $_POST['coupon_code'] ) : '',
				'coupon_discount_amount' => !empty( $_POST['coupon_discount_amount'] ) ? sanitize_text_field( $_POST['coupon_discount_amount'] ) : '',
				'wpshop_coupon_discount_type' => !empty( $_POST['coupon_type'] ) ? sanitize_text_field( $_POST['coupon_type'] ) : '',
				'coupon_receiver' => !empty( $_POST['coupon_receiver'] ) ? (array) $_POST['coupon_receiver'] : '',
				'coupon_usage_limit' => !empty( $_POST['coupon_usage_limit'] ) ? sanitize_text_field( $_POST['coupon_usage_limit'] ) : '',
				'post_ID' => $post_id,
			);

			$wps_coupon_mdl->save_coupons_informations( $data );
		}
	}

	/**
	 * APPLY COUPON
	 * @param string $code
	 * @return array
	 */
	function applyCoupon( $code ) {
		global $wpdb, $wpshop_cart;

		/** Default currency **/
		$default_currency = wpshop_tools::wpshop_get_currency( false );

		$coupon_infos = array();

		/** Coupon infos **/
		$query = $wpdb->prepare('
			SELECT META.post_id
			FROM '.$wpdb->prefix.'postmeta META
			LEFT JOIN '.$wpdb->prefix.'posts POSTS ON POSTS.ID = META.post_id
			WHERE
				POSTS.post_type = %s AND
				META.meta_key = "wpshop_coupon_code" AND
				META.meta_value = %s AND
				POSTS.post_status = %s
		', WPSHOP_NEWTYPE_IDENTIFIER_COUPON, $code, 'publish');
		$result = $wpdb->get_row($query);

		if ( !empty($result) ) {
			$coupon_amount = get_post_meta( $result->post_id, 'wpshop_coupon_discount_value', true );

			if ( !empty($coupon_amount) && $coupon_amount > 0) {
				$coupon_usage = get_post_meta( $result->post_id, '_wpshop_coupon_usage', true );
				$coupon_usage_limit  = get_post_meta( $result->post_id, 'wpshop_coupon_usage_limit', true );
				$coupon_individual_usage  = get_post_meta( $result->post_id, 'wpshop_coupon_individual_use', true );

				$coupon_order_amount_mini = get_post_meta( $result->post_id, 'wpshop_coupon_minimum_amount', true);

				$current_user_id = get_current_user_id();
				$individual_usage = $usage_limit = false;


				/** Checking coupon params & logged user **/
				if ( (!empty($coupon_individual_usage) || !empty($coupon_usage_limit) ) && $current_user_id == 0) {
					return array('status' => false, 'message' => __('You must be logged to use this coupon','wpshop'));
				}

				/** Individual use checking **/
				if ( !empty($coupon_individual_usage) ) {

					if ( in_array($current_user_id, $coupon_individual_usage) ) {
						$individual_usage = true;
					}
				}
				else {
					$individual_usage = true;
				}


				/** Checking Usage limitation **/
				if ($individual_usage) {
					if ( !empty($coupon_usage_limit) ) {

						if( ( !empty($coupon_usage) && array_key_exists($current_user_id, $coupon_usage) ) || empty($coupon_usage ) || empty($coupon_usage[$current_user_id]) ) {
							$usage_limit = ( ( !empty($coupon_usage_limit) && $coupon_usage[$current_user_id] < $coupon_usage_limit) || empty($coupon_usage) || empty($coupon_usage[$current_user_id])  ) ? true : false;
						}
						elseif( empty($coupon_usage) ) {
							$usage_limit = true;
						}
					}
					else {
						$usage_limit = true;
					}
				}
				else {
					return array('status' => false, 'message' => __('You are not allowed to use this coupon','wpshop'));
				}


				/** Apply Coupon **/
				if ( $usage_limit ) {
					/** Check orderamount Limit **/
					$order_amount_limit = true;

					if ( !empty($coupon_order_amount_mini) && !empty($coupon_order_amount_mini['amount']) ) {

						if ( !empty($coupon_order_amount_mini) && !empty($coupon_order_amount_mini['shipping_rule']) && $coupon_order_amount_mini['shipping_rule'] == 'shipping_cost' && $_SESSION['cart']['order_grand_total_before_discount'] < $coupon_order_amount_mini['amount'] ) {
							$coupon_infos = array('status' => false, 'message' => __('This coupon is available for an order from ','wpshop').' '.$coupon_order_amount_mini['amount'].' '.$default_currency );
							$order_amount_limit = false;
						}

						elseif(  !empty($coupon_order_amount_mini) && !empty($coupon_order_amount_mini['shipping_rule']) && $coupon_order_amount_mini['shipping_rule'] == 'no_shipping_cost' && $_SESSION['cart']['order_total_ttc'] < $coupon_order_amount_mini['amount'] ) {
							$coupon_infos = array('status' => false, 'message' => __('This coupon is available for an order from ','wpshop').' '.$coupon_order_amount_mini['amount'].' '.$default_currency.' '.__('without shipping cost', 'wpshop') );
							$order_amount_limit = false;
						}

					}
					if ( $order_amount_limit ) {
						$_SESSION['cart']['coupon_id'] = $result->post_id;
						$coupon_infos = array('status' => true, 'message' => '');
					}
				}
				else {
					$coupon_infos = array('status' => false, 'message' => __('You are not allowed to use this coupon','wpshop') );
				}

			}
			else {
				$coupon_infos = array('status' => false, 'message' => __('This coupon is not valid','wpshop'));
			}

		}
		else {
			$coupon_infos = array('status' => false, 'message' => __('This coupon doesn`t exist','wpshop'));
		}
		return $coupon_infos;
	}

	/**
	 * Display coupons list
	 * @param integer $customer_id
	 * @return string
	 */
	function display_coupons( $customer_id = '' ) {
		$is_from_admin = ( !empty($customer_id) ) ? true : false;
		$customer_id = ( !empty($customer_id) ) ? $customer_id : get_current_user_id();
		$coupons_mdl = new wps_coupon_model();
		$coupons = $coupons_mdl->get_coupons();
		$output = $coupons_rows = '';

		if( !empty($coupons) ) {
			foreach( $coupons as $coupon ) {
				$coupon_individual_usage = get_post_meta( $coupon->ID, 'wpshop_coupon_individual_use', true );
				if( empty($coupon_individual_usage) || ( !empty($coupon_individual_usage) && in_array( $customer_id , $coupon_individual_usage) ) ) {
					$coupon_code = get_post_meta( $coupon->ID, 'wpshop_coupon_code', true );
					$coupon_value = get_post_meta( $coupon->ID, 'wpshop_coupon_discount_value', true );
					$discount_type = get_post_meta( $coupon->ID, 'wpshop_coupon_discount_type', true );
					$coupon_date = get_post_meta( $coupon->ID, 'wpshop_coupon_expiry_date', true);
					$coupon_validity_date = ( !empty($coupon_date) ) ? $coupon_date : __( 'No validity date', 'wpshop');
					$coupon_value .= ( !empty($discount_type) && $discount_type == 'amount') ? wpshop_tools::wpshop_get_currency( false ) : '%';
					ob_start();
					require(  wpshop_tools::get_template_part( WPS_COUPON_DIR, $this->template_dir, "frontend", "coupon") );
					$coupons_rows .= ob_get_contents();
					ob_end_clean();
				}
			}
			ob_start();
			require(  wpshop_tools::get_template_part( WPS_COUPON_DIR, $this->template_dir, "frontend", "coupons") );
			$output .= ob_get_contents();
			ob_end_clean();

		}
		else {
			$output = '<div class="wps-alert-info">' .__( 'Sorry ! No available coupon', 'wpshop' ) .'</div>';
		}
		return $output;
	}

	function getCoupons() {
		$wps_coupon_mdl = new wps_coupon_model();
		$result = $wps_coupon_mdl->get_coupons();
		unset($wps_coupon_mdl);
		return $result;
	}
}
