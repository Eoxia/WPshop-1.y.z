<?php if ( !defined( 'ABSPATH' ) ) exit;

class wps_statistics_ctr {
	private $wps_stats_mdl;
	function __construct() {

		// WP Main Actions
		add_action('admin_menu', array(&$this, 'register_stats_menu'), 250);
		add_action( 'save_post', array( &$this, 'wps_statistics_save_customer_infos') );
		add_action('add_meta_boxes', array( &$this, 'add_customer_meta_box'), 1 );

		// Add Javascript Files & CSS File in admin
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );

		// Ajax Actions
		// add_action('wap_ajax_wps_reload_statistics', array( &$this, 'wps_reload_statistics') );
		add_action('wp_ajax_wps_hourly_order_day', array( &$this, 'wps_hourly_order_day') );

		$this->wps_stats_mdl = new wps_statistics_mdl();
	}


	/**
	* Add Javascript & CSS files
	*/
	function add_scripts( $hook ) {
		global $current_screen;
		if( ! in_array( $current_screen->post_type, array( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ), true ) && $hook != 'wpshop_shop_order_page_wpshop_statistics' )
			return;

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-datepicker');
		wp_enqueue_script( 'postbox');
		wp_enqueue_script( 'wps_statistics_js_chart', WPSHOP_JS_URL.'Chart.js' );
		wp_enqueue_script( 'wps_statistics_js', WPS_STATISTICS_URL.'/assets/js/wps_statistics.js' );
		wp_enqueue_script( 'wps_hourlyorders', WPS_STATISTICS_URL.'/assets/js/hourlyorders.js' );
	}

	/**
	 * Add Meta Boxes to exclude customers of WPShop Statistics
	 */
	function add_customer_meta_box() {
		global $post;
		add_meta_box( 'wps_statistics_customer', __( 'Statistics', 'wps_price' ), array( &$this, 'wps_statistics_meta_box_content' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );

	}

	/** Add Statistics Meta Boxes **/
	function add_stats_meta_boxes() {
		$user_stats_order = get_user_meta( get_current_user_id(), 'meta-box-order_boutique_page_wpshop_statistics', true );
		add_meta_box( 'wps-best-sales-statistics',__('Best sales', 'wpshop'), array( $this, 'wps_statistics_best_sales' ), 'wpshop_statistics', 'left_column' );
		add_meta_box( 'wps-most-viewed-statistics',__('Most viewed products', 'wpshop'), array( $this, 'wps_statistics_most_viewed_products' ), 'wpshop_statistics', 'right_column' );
		add_meta_box( 'wps-orders-by-month-statistics',__('Orders', 'wpshop'), array( $this, 'wps_statistics_orders_by_month' ), 'wpshop_statistics', 'left_column' );
		add_meta_box( 'wps-orders-status',__('Orders status', 'wpshop'), array( $this, 'wps_statistics_orders_status' ), 'wpshop_statistics', 'left_column' );
		add_meta_box( 'wps-orders-moment-statistics',__('Orders Hours', 'wpshop'), array( $this, 'wps_statistics_orders_moment' ), 'wpshop_statistics', 'right_column' );
		add_meta_box( 'wps-best-customers',__('Best customers', 'wpshop'), array( $this, 'wps_statistics_best_customers' ), 'wpshop_statistics', 'right_column' );
	}

	/**
	 * Meta box content to exclude customers of statistics
	 */
	function wps_statistics_meta_box_content() {
		global $post;
		$user_meta = '';
		if ( !empty($post) && !empty($post->post_author) ) {
			$user_meta = get_user_meta( $post->post_author, 'wps_statistics_exclude_customer', true );
		}
		$output = '<input type="checkbox" name="wps_statistics_exclude_customer" id="wps_statistics_exclude_customer" ' .( (!empty($user_meta) ) ? 'checked="checked"' : '' ). '/> <label for="wps_statistics_exclude_customer">' .__('Exclude this customer from WPShop Statistics', 'wpshop'). '</label>';
		echo $output;
	}

	/**
	 * Save action to exclude customer of statistics
	 */
	function wps_statistics_save_customer_infos() {
		$action = !empty( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
		$post_type = !empty( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
		$post_id = !empty( $_POST['post_ID'] ) ? (int) $_POST['post_ID'] : 0;
		$wps_statistics_exclude_customer = isset( $_POST['wps_statistics_exclude_customer'] ) ? (int) $_POST['wps_statistics_exclude_customer'] : 0;

		if ( !empty($action) && $action != 'autosave' && !empty($post_type) && $post_type == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ) {
			$customer_def = get_post( $post_id );
			if( isset( $wps_statistics_exclude_customer ) ) {
				update_user_meta( $customer_def->post_author, 'wps_statistics_exclude_customer', $wps_statistics_exclude_customer );
			}
		}
	}

	/**
	 * Register statistics Menu
	 */
	function register_stats_menu() {
		add_submenu_page( 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER, __('Statistics', 'wpshop' ), __('Statistics', 'wpshop'), 'wpshop_view_statistics', 'wpshop_statistics', array($this, 'wps_display_statistics'));
	}

	/**
	 * Display Statistics Interface
	 */
	function wps_display_statistics() {
		$this->add_stats_meta_boxes();
		echo wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		echo wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, "backend", "wps-statistics") );
	}

	/**
	 * Display Best sales Statistics
	 * @param string $begindate
	 * @param string $enddate
	 * @return string
	 */
	function wps_statistics_best_sales() {
		$begin_date =  ( !empty($_POST['begin_date']) ) ? wpshop_tools::varSanitizer($_POST['begin_date']) : date( 'Y-m-d', strtotime( '1 months ago') );
		$end_date = ( !empty($_POST['end_date']) ) ? wpshop_tools::varSanitizer( $_POST['end_date'] ) : date('Y-m-d');
		$products = $this->wps_stats_mdl->wps_best_sales_datas($begin_date, $end_date );
		$colors = array( '#69D2E7', '#E0E4CC', '#F38630', '#64BC43', '#8F33E0', '#F990E6', '#414141', '#E03E3E');
		ob_start();
		require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, "backend", "wps_statistics_best_sales") );
		$output = ob_get_contents();
		ob_end_clean();
		echo $output;
	}

	/** Display most viewed products statistics **/
	function wps_statistics_most_viewed_products() {
		$products = $this->wps_stats_mdl->wps_most_viewed_products_datas();
		$colors = array( '#69D2E7', '#E0E4CC', '#F38630', '#64BC43', '#8F33E0', '#F990E6', '#414141', '#E03E3E');
		ob_start();
		require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, "backend", "wps_statistics_most_viewed_products") );
		$output = ob_get_contents();
		ob_end_clean();
		echo $output;
	}

	/**
	 * Display orders by month statistics
	 */
	function wps_statistics_orders_by_month() {
		$order_recap = $this->wps_stats_mdl->wps_orders_by_month();
		$colors = array( array('#9AE5F4', '#0074A2'), array('#E0E4CC', '#A8AA99'));
		ob_start();
		require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, "backend", "wps_statistics_orders_by_month") );
		$output = ob_get_contents();
		ob_end_clean();
		echo $output;
	}

	/**
	 * Display order status statistics
	 */
	function wps_statistics_orders_status() {
		$begin_date =  ( !empty($_POST['begin_date']) ) ? wpshop_tools::varSanitizer( $_POST['begin_date'] ) : date( 'Y-m-d', strtotime( '1 months ago') );
		$end_date = ( !empty($_POST['end_date']) ) ? wpshop_tools::varSanitizer( $_POST['end_date'] ) : date('Y-m-d');
		$orders_status = $this->wps_stats_mdl->wps_order_status($begin_date, $end_date);
		$colors = array( 'canceled' => '#E0E4CC', 'shipped' => '#69D2E7', 'pos' => '#6993e7', 'completed' => '#64BC43', 'refunded' => '#E03E3E', 'partially_paid' => '#FF9900','awaiting_payment' => '#F4FA58', 'denied' => '#414141', 'incorrect_amount' => '#F38630', 'payment_refused' => '#8F33E0');
		$payment_status = unserialize( WPSHOP_ORDER_STATUS );
		if( !empty($orders_status) ) {
			arsort( $orders_status );
			ob_start();
			require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, "backend", "wps_statistics_orders_status") );
			$output = ob_get_contents();
			ob_end_clean();
			echo $output;
		}
	}

	/**
	 * Display Best customers Statistics
	 */
	function wps_statistics_best_customers() {
		$begin_date =  ( !empty($_POST['begin_date']) ) ? wpshop_tools::varSanitizer( $_POST['begin_date'] ) : date( 'Y-m-d', strtotime( '1 months ago') );
		$end_date = ( !empty($_POST['end_date']) ) ? wpshop_tools::varSanitizer( $_POST['end_date'] ) : date('Y-m-d');
		$customer_recap = $this->wps_stats_mdl->wps_best_customers( $begin_date, $end_date );
		$colors = array( '#69D2E7', '#E0E4CC', '#F38630', '#64BC43', '#8F33E0', '#F990E6', '#414141', '#E03E3E');
		ob_start();
		require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, "backend", "wps_statistics_best_customers") );
		$output = ob_get_contents();
		ob_end_clean();
		echo $output;
	}

	/**
	 * Display Orders moment in the day Statistics
	 */
	function wps_statistics_orders_moment( $args = array( 'choosen_day' => '', 'return' => false ) ) {
		$begin_date =  ( !empty($_POST['begin_date']) ) ? wpshop_tools::varSanitizer( $_POST['begin_date'] ) : date( 'Y-m-d', strtotime( '1 months ago') );
		$end_date = ( !empty($_POST['end_date']) ) ? wpshop_tools::varSanitizer( $_POST['end_date'] ) : date('Y-m-d');
		$datadate = $this->wps_stats_mdl->wps_get_orders_by_hours( $begin_date, $end_date, ( ( !empty($args['choosen_day']) ) ? $args['choosen_day'] : '' ) );
		$days = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		ob_start();
		require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, "backend", "wps_statistics_orders_moment") );
		$output = ob_get_contents();
		ob_end_clean();
		if( ( !empty( $args) ) && !empty( $args['return'] ) && $args['return'] ) {
			return $output;
		}
		else {
			echo $output;
		}
	}

	/**
	 * AJAX - Display Orders moments according the choosen day
	 */
	function wps_hourly_order_day() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_hourly_order_day' ) )
			wp_die();

		$status = false; $response = '';

		$day = ( !empty($_POST['day']) ) ? sanitize_text_field( $_POST['day'] ) : null;
		$width = !empty( $_POST['width'] ) ? (int) $_POST['width'] : 0;
		$height = !empty( $_POST['height'] ) ? (int) $_POST['height'] : 0;

		$response = $this->wps_statistics_orders_moment( array( 'choosen_day' => $day, 'return' => true, 'width' => $width, 'height' => $height ) );
		$status = true;
		echo json_encode( array( 'status' => $status, 'response' => $response ) );
		wp_die();
	}



	function customers_by_month(){
	$canvas_js = '';
	$box_title = __('Monthly customers', 'wpshop');
	$canvas_width = 550;
	$canvas_height = 400;
	$products = $this->wps_stats_mdl->wps_customers_month();
		if ( !empty($customers_recap) ) {
				krsort( $customers_recap );
				$canvas_js .= '<script type="text/javascript">';
				$canvas_js .= 'var data  = { labels : ["' .__('January', 'wpshop'). '","' .__('February', 'wpshop'). '","' .__('March', 'wpshop'). '","' .__('April', 'wpshop'). '","' .__('May', 'wpshop'). '","' .__('June', 'wpshop'). '","' .__('July', 'wpshop'). '","' .__('August', 'wpshop'). '" ,"' .__('September', 'wpshop'). '" ,"' .__('October', 'wpshop'). '","' .__('November', 'wpshop'). '","' .__('December', 'wpshop'). '"],';
				$canvas_js .= 'datasets : [';
				$i = 0;
				$colors = array(array('#E0E4CC', '#A8AA99') , array('#69D2E7', '#4CA3B5'));
				$customers_recap = array_slice( $customers_recap, 0, 2, true );
				$customers_recap = array_reverse( $customers_recap, true );
				foreach( $customers_recap as $y => $year ) {

					if ( $i < 2 ) {
						$canvas_js .= '{fillColor : "' .$colors[$i][0]. '",strokeColor :"' .$colors[$i][1]. '",';
						$canvas_js .= 'data : [';
						for( $j = 1; $j <= 12; $j++) {
							if( !empty($year[$j]) ) {
								$canvas_js .= $year[$j].',';
								if ( $count_users < $year[$j] ) {
									$count_users = $year[$j];
								}
							}
							else {
								$canvas_js .= '0,';
							}
						}
						$canvas_js .= ']';
						$canvas_js .= '},';
						$colors[$i][] = $y;
						$i++;
					}
				}
				$canvas_js .= ']};';
				$canvas_js .= 'var BarCustomers = new Chart(document.getElementById("wps_customers_account_creation").getContext("2d")).Bar(data, {scaleOverride : true, scaleSteps : ' .round( ($count_users / 5) ). ', scaleStepWidth : 5, scaleStartValue : 0});';
				$canvas_js .= '</script>';

				/** Legend **/
				$canvas_js .= '<center><ul class="wps_statistics_legend">';
				foreach( $colors as $color ) {
					if ( !empty($color) && !empty($color[2]) )
					$canvas_js .= '<li style="width : auto; margin-right : 20px;"><div style="background : ' .$color[0]. ';" class="legend_indicator"></div>' .$color[2]. '</li>';
				}
				$canvas_js .= '</ul></center>';
		}
		else {
			$canvas_js = __( 'No customer account has been created on your shop', 'wpshop');
		}
	}


}
