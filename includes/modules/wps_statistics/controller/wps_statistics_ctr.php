<?php if ( ! defined( 'ABSPATH' ) ) { exit;
}

class wps_statistics_ctr {


	private $wps_stats_mdl;

	function __construct() {

		// End if().
		add_action( 'admin_menu', array( &$this, 'register_stats_menu' ), 250 );
		add_action( 'save_post', array( &$this, 'wps_statistics_save_customer_infos' ), 10, 2 );
		add_action( 'post_submitbox_misc_actions', array( $this, 'wps_statistics_meta_box_content' ) );

		// Add Javascript Files & CSS File in admin
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );

		add_action( 'wp_ajax_wps_statistics_custom_date_view', array( $this, 'wps_statistics_custom_date_view' ) );

		$this->wps_stats_mdl = new wps_statistics_mdl();
	}

	/**
	 * Add Javascript & CSS files
	 */
	function add_scripts( $hook ) {

		global $current_screen;
		if ( ! in_array( $current_screen->post_type, array( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ), true ) && $hook != 'wpshop_shop_order_page_wpshop_statistics' ) {
			return;
		}

		wp_enqueue_script( 'wps_statistics_js_chart', WPSHOP_JS_URL . 'Chart.js' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		 wp_enqueue_script( 'jquery-form' );

		 wp_register_style( 'jquery-ui-wpsstats', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', '', WPSHOP_VERSION );
		wp_enqueue_style( 'jquery-ui-wpsstats' );
	}

	/**
	 * Meta box content to exclude customers of statistics
	 *
	 * @param  WP_Post $post    Définition complète du post actuellement en cours de modification / Current edited post entire definition.
	 */
	function wps_statistics_meta_box_content( $post ) {
		if ( ! empty( $post ) && WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS !== $post->post_type ) {
			return;
		}
		$user_meta = '';
		if ( ! empty( $post ) && ! empty( $post->post_author ) ) {
			$user_meta = get_user_meta( $post->post_author, 'wps_statistics_exclude_customer', true );
		}
		$output = '<span class="misc-pub-section" ><input type="checkbox" name="wps_statistics_exclude_customer" id="wps_statistics_exclude_customer" ' . checked( $user_meta, true, false ) . '/> <label for="wps_statistics_exclude_customer">' . __( 'Exclude this customer from WPShop Statistics', 'wpshop' ) . '</label></span>';

		echo $output;
		// End if().
	}

	/**
	 * Save action to exclude customer of statistics
	 *
	 * @param  integer $post_id L'identifiant du post actuellement en cours de modification / Current edited post identifier.
	 * @param  WP_Post $post    Définition complète du post actuellement en cours de modification / Current edited post entire definition.
	 */
	function wps_statistics_save_customer_infos( $post_id, $post ) {

		$action = ! empty( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
		// WPCS: CSRF ok.
		$wps_statistics_exclude_customer = isset( $_POST['wps_statistics_exclude_customer'] ) && ( 'on' === $_POST['wps_statistics_exclude_customer'] ) ? true : false;
		// WPCS: CSRF ok.
		if ( ( ! empty( $action ) && ( 'autosave' !== $action ) ) && ( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS === get_post_type( $post_id ) ) ) {
			if ( isset( $wps_statistics_exclude_customer ) ) {
				update_user_meta( $post->post_author, 'wps_statistics_exclude_customer', $wps_statistics_exclude_customer );
			}
		}
	}

	/**
	 * Register statistics Menu
	 */
	function register_stats_menu() {

		add_submenu_page( 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER, __( 'Statistics', 'wpshop' ), __( 'Statistics', 'wpshop' ), 'wpshop_view_statistics', 'wpshop_statistics', array( $this, 'wps_display_statistics' ) );
	}

	/**
	 * Display Statistics Interface
	 */
	function wps_display_statistics() {

		$shop_orders = $this->wps_stats_mdl->wps_orders_all( );

		$main_stats_count = 5;

		$ordered_customers = array();
		foreach ( $shop_orders as $order ) {
			 $user_id = $order['order_postmeta']['customer_id'];
			$wps_statistics_exclude_customer = get_user_meta( $user_id, 'wps_statistics_exclude_customer', true );
			$excluded_from_statistics = ( ! empty( $wps_statistics_exclude_customer ) ) ? true : false;

			if ( false === $excluded_from_statistics ) {
				$customer_id = null;
				$args = array(
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
					'author' => $user_id,
					'orderby' => 'post_date',
					'order' => 'ASC',
					'post_status' => 'all',
					'posts_per_page' => 1,
				);
				$customer = new WP_Query( $args );

				if ( ! isset( $ordered_customers[ $user_id ]['count'] ) ) {
					$ordered_customers[ $user_id ]['count'] = 0;
					$ordered_customers[ $user_id ]['total_amount'] = 0;
				}
					$ordered_customers[ $user_id ]['count']++;
					$ordered_customers[ $user_id ]['id'] = $user_id;
					$ordered_customers[ $user_id ]['post_id'] = $customer->post->ID;
					$ordered_customers[ $user_id ]['name'] = ( ! empty( $order['order_info'] ) && ! empty( $order['order_info']['billing'] ) && ! empty( $order['order_info']['billing']['address'] ) && ! empty( $order['order_info']['billing']['address']['address_last_name'] ) && ! empty( $order['order_info']['billing']['address']['address_first_name'] ) ) ? $order['order_info']['billing']['address']['address_first_name'] . ' ' . $order['order_info']['billing']['address']['address_last_name'] : '';
					;
					$ordered_customers[ $user_id ]['total_amount'] += $order['order_postmeta']['order_grand_total'];
			}
		}

		require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, 'backend', 'wps-statistics' ) );
	}

	/**
	 * Get the average duration between
	 *
	 * @param  [type] $order_list [description]
	 *
	 * @return [type]             [description]
	 */
	function get_average_time_between_orders( $order_list ) {

		$time_between_orders = 0;
		$last_date = null;
		foreach ( $order_list as $order ) {
			if ( null !== $last_date ) {
				$last_order = new DateTime( $last_date );
				$current_order = new DateTime( $order['order_postmeta']['order_date'] );
				$time_between_orders += $last_order->getTimestamp() - $current_order->getTimestamp();
			} else {

			}

			$last_date = $order['order_postmeta']['order_date'];
		}

		return round( $time_between_orders / count( $order_list ) );
	}

	/**
	 * Check if the shop is out of bounds for time since last order
	 *
	 * @return boolean The boolean state allowing to know if the shop is out of bounds for time since last order
	 */
	function check_current_time_since_last_order( $list_orders ) {

		$average_check = array(
			'status' => false,
			'last_date' => null,
			'average' => null,
			'duration' => null,
		);
		$current_date = new DateTime( current_time( 'Y-m-d H:i:s', 0 ) );

		if ( ! empty( $list_orders ) ) {
			  $last_order = array_slice( $list_orders, 0, 1 );
			foreach ( $last_order as $order ) {
				$average_check['last_date'] = $order['order_postmeta']['order_date'];
			}
			 $last_order_dateTime = new DateTime( $average_check['last_date'] );

			  $duration_since_last_order = $current_date->getTimestamp() - $last_order_dateTime->getTimestamp();
			$average_check['duration'] = $duration_since_last_order;
			  $average_check['average'] = $this->get_average_time_between_orders( $list_orders );

			  $average_check['status'] = ( $average_check['duration'] > $average_check['average'] ? true : false );
		}

		return $average_check;
	}

	/**
	 * Main Statistics output
	 */
	function wps_display_main_statistics( $shop_orders = null ) {

		global $current_month_offset;

		if ( null === $shop_orders ) {
			$shop_orders = $this->wps_stats_mdl->wps_orders_all();
		}

		$current_month_offset = (int) current_time( 'm' );
		$current_month_offset = isset( $_GET['month'] ) ? (int) $_GET['month'] : $current_month_offset;

		$current_month_start = date( 'Y-m-d 00:00:00', strtotime( 'first day of this month', time() ) );
		$current_month_end = date( 'Y-m-d 23:59:59', strtotime( 'last day of this month', time() ) );

		$last_month_start = date( 'Y-m-d 00:00:00', strtotime( 'first day of last month', time() ) );
		$last_month_end = date( 'Y-m-d 23:59:59', strtotime( 'last day of last month', time() ) );
		$one_month_ago = date( 'Y-m-d 23:59:59', strtotime( '-1 month', time() ) );

		$dates = array(
			__( 'Current month', 'wpshop' ) => array(
				'after' => $current_month_start,
				'before' => $current_month_end,
				'inclusive' => true,
			),
			sprintf( __( 'One month ago (%s)', 'wpshop' ), mysql2date( get_option( 'date_format' ), $one_month_ago, true ) ) => array(
				'after' => $last_month_start,
				'before' => $one_month_ago,
				'inclusive' => true,
			),
			__( 'Last month', 'wpshop' ) => array(
				'after' => $last_month_start,
				'before' => $last_month_end,
				'inclusive' => true,
			),
			__( 'From the beginning', 'wpshop' ) => null,
		);

		require_once( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, 'backend', 'wps_statistics_main' ) );
	}

	/**
	 * Display custom Statistics area. Allows to choose date for stats displaying
	 *
	 * @param array  $shop_orders The list of orders to use for stats.
	 * @param string $start      Optionnal. The start date to get stats for.
	 * @param string $end        Optionnal. The end date to get stats for.
	 */
	function wps_display_custom_statistics( $shop_orders, $start = null, $end = null ) {

		$current_month_start = date( 'Y-m-d', strtotime( 'first day of this month', time() ) );
		$current_month_end = date( 'Y-m-d', strtotime( 'last day of this month', time() ) );

		$last_month_start = date( 'Y-m-d', strtotime( 'first day of last month', time() ) );
		$last_month_end = date( 'Y-m-d', strtotime( 'last day of last month', time() ) );

		$date_start = null !== $start ? $start : $current_month_start;
		$date_end = null !== $end ? $end : $current_month_end;

		$stats_translations = array(
			'numberOfSales' => __( 'Number of sales', 'wpshop' ),
			'sales' => __( 'sales', 'wpshop' ),
			'salesAmount' => __( 'Sales amount', 'wpshop' ),
			'wpshopCurrency' => wpshop_tools::wpshop_get_currency(),
		);

		$orders_total_amount = $orders_total_shipping_cost = $order_count = 0;
		$orders_number_stats = $orders_amount_stats = array();
		if ( ! empty( $shop_orders ) ) {
			foreach ( $shop_orders as $order ) {
				$order_data = $order['order_postmeta'];
				if ( ( $date_start <= $order_data['order_date'] ) && ( $date_end >= $order_data['order_date'] ) ) {
					$orders_total_amount += $order_data['order_grand_total'];
					$order_count++;

					$time = strtotime( date( 'Ymd', strtotime( $order_data['order_date'] ) ) ) . '000';
					if ( ! isset( $orders_number_stats[ $time ] ) ) {
						$orders_number_stats[ $time ] = 0;
					}
					$orders_number_stats[ $time ]++;

					if ( ! isset( $orders_amount_stats[ $time ] ) ) {
						$orders_amount_stats[ $time ] = 0;
					}
					$orders_amount_stats[ $time ] += $order_data['order_grand_total'];

					$orders_total_shipping_cost += $order_data['order_shipping_cost'];
				}
			}
		}

		$orders_number_for_stats = null;
		if ( ! empty( $orders_number_stats ) ) {
			$orders_numbers = array();
			foreach ( $orders_number_stats as $time => $number ) {
				$orders_numbers[] = "[$time, $number]";
			}
			$orders_number_for_stats = implode( ',', $orders_numbers );
		}

		$orders_amount_for_stats = null;
		if ( ! empty( $orders_amount_stats ) ) {
			$orders_amounts = array();
			foreach ( $orders_amount_stats as $time => $amount ) {
				$orders_amounts[] = "[$time, $amount]";
			}
			$orders_amount_for_stats = implode( ',', $orders_amounts );
		}

		$user_subscription_number = new WP_User_Query( array(
			'date_query' => array(
				array(
					'after' => $date_start,
					'before' => $date_end,
					'inclusive' => true,
				),
			),
			'count_total' => true,
		) );

		require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, 'backend', 'wps_statistics_custom' ) );
	}

	/**
	 * Ajax callback - Display custom statistics for given date
	 */
	function wps_statistics_custom_date_view() {

		check_ajax_referer( 'wps_statistics_custom_date_view' );

		$start_date = ! empty( $_POST ) && ! empty( $_POST['wps_statistics_start_date'] ) ? sanitize_text_field( $_POST['wps_statistics_start_date'] ) : date( 'Y-m-d', strtotime( 'first day of this month', time() ) );
		$end_date = ! empty( $_POST ) && ! empty( $_POST['wps_statistics_end_date'] ) ? sanitize_text_field( $_POST['wps_statistics_end_date'] ) : date( 'Y-m-d', strtotime( 'last day of this month', time() ) );

		$order_list = $this->wps_stats_mdl->wps_orders_all( array(
			'date_query' => array(
				array(
					'after' => $date_start,
					'before' => $date_end,
					'inclusive' => true,
				),
			),
		) );

		ob_start();
		$this->wps_display_custom_statistics( $order_list, $start_date, $end_date );
		$output = ob_get_clean();

		wp_die( $output );
	}

}
