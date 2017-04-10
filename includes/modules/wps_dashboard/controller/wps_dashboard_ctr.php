<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * File for installer control class definition
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 *
 */

/**
 * Class for installer control
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 *
 */
class wps_dashboard_ctr {

	/**
	 * Instanciate the module controller
	 */
	function __construct() {
// 		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts'), '', '', true );
	}

	function add_scripts() {
		add_action( 'admin_print_scripts', array($this, 'admin_print_script') );
	}

	function admin_print_script() {
		echo "<div id=\"fb-root\"></div>
			<script type=\"text/javascript\">(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = \"//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.0\";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>'";
	}

	/**
	 * DISPLAY - Display wpshop dashboard
	 */
	function display_dashboard() {
		global $order_status, $wpdb;

		$this->add_dashboard_metaboxes();
		require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, "backend", "dashboard" ) );
	}

	function wpshop_dashboard_orders() {
		$output = '';
		$orders = get_posts( array( 'posts_per_page' => 10, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'post_status' => 'publish', 'orderby' => 'post_date', 'order' => 'DESC') );
		// Display orders
		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, "backend", "wps_orders_on_dashboard" ) );
		$output = ob_get_contents();
		ob_end_clean();


		return $output;
	}

	function wpshop_rss_feed() {
		$output = '';
		include_once( ABSPATH . WPINC . '/feed.php' );

		$rss = fetch_feed( 'http://www.wpshop.fr/feed/' );
		if( ! is_wp_error( $rss ) ){
			$maxitems = $rss->get_item_quantity( 4 );
			$rss_items = $rss->get_items( 0, $maxitems );
		}
		else {
			$output .= '<p>' . __('WPShop News cannot be loaded', 'wpshop') . '</p>';
		}

		if ( $maxitems == 0 ) {
			$output .= '<p>' . __('No WPShop new has been found', 'wpshop') . '</p>';
		}
		else {
			$output .= '<ul class="recent-orders">';
			foreach ( $rss_items as $item ) {
				$output .= '<li><a href="' .$item->get_permalink() . '" title="' .$item->get_title(). '" target="_blank">' .$item->get_title(). '</a><br/>';
				$output .= $item->get_content();
				$output .= '</li>';
			}
			$output .= '</ul>';
		}
		echo $output;
	}

	function wpshop_rss_tutorial_videos() {
		$ini_get_checking = ini_get( 'allow_url_fopen' );

		if ( $ini_get_checking != 0 ) {
			$content = @file_get_contents( 'http://www.wpshop.fr/rss_video.xml' );
			$videos_rss = ( $content !== false ) ? new SimpleXmlElement( $content ) : null;
			if ( !empty($videos_rss) && !empty($videos_rss->channel) ) {
				$videos_items = array();
				foreach( $videos_rss->channel->item as $i => $item ) {
					$videos_items[] = $item;
				}
				$rand_element = array_rand( $videos_items );

				ob_start();
				require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, "backend", "dashboard", "videos" ) );
				$output = ob_get_contents();
				ob_end_clean();
			}
			else {
				$output = __('No tutorial videos can be loaded', 'wpshop' );
			}
		}
		else {
			$output = __( 'Your servor doesn\'t allow to open external files', 'wpshop');
		}

		echo $output;
	}

	function wpshop_dashboard_get_changelog() {
		$readme_file = fopen( WPSHOP_DIR.'/readme.txt', 'r' );
		if ( $readme_file ) {
			$txt = file_get_contents( WPSHOP_DIR.'/readme.txt' );
			$pre_change_log = explode( '== Changelog ==', $txt );
			$versions = explode( '= Version', $pre_change_log[1] );

			echo $versions[1];
		}
	}

	/**
	 * Add custom metaboxes to WPShop dashboard
	 */
	function add_dashboard_metaboxes() {
		add_meta_box( 'wps-right-now', '<i class="dashicons dashicons-info"></i>' . esc_html( 'Right Now', 'wpshop' ), array( $this, 'wps_dashboard_right_now' ), 'wpshop_dashboard', 'left_column' );
		add_meta_box( 'wps-dashboard-quick-links', '<i class="dashicons dashicons-performance"></i>' . esc_html( 'Quick Links', 'wpshop' ), array( $this, 'wps_dashboard_quick_links' ), 'wpshop_dashboard', 'left_column' );
		// add_meta_box( 'wps-dashboard-customer-stats', '<i class="dashicons dashicons-chart-pie"></i>' . esc_html( 'Customers stats', 'wpshop' ), array( $this, 'wps_dashboard_customer_stats' ), 'wpshop_dashboard', 'left_column' );
		add_meta_box( 'wps-dashboard-export', '<i class="dashicons dashicons-download"></i>' . esc_html( 'CSV export', 'wpshop' ), array( $this, 'wps_dashboard_export' ), 'wpshop_dashboard', 'left_column' );
		add_meta_box( 'wps-dashboard-orders', '<i class="dashicons dashicons-cart"></i>' . esc_html( 'Recent Orders', 'wpshop' ), array( $this, 'wps_dashboard_orders' ), 'wpshop_dashboard', 'left_column' );

		add_meta_box( 'wps-dashboard-statistics', '<i class="dashicons dashicons-chart-area"></i>' . esc_html( 'Statistics', 'wpshop' ), array( $this, 'wps_dashboard_statistics' ), 'wpshop_dashboard', 'right_column' );

		add_meta_box( 'wps-dashboard-infos', '<i class="dashicons dashicons-heart"></i>' . esc_html( 'WPShop : WordPress e-commerce', 'wpshop' ), array( $this, 'wps_dashboard_infos' ), 'wpshop_dashboard', 'right_column' );
		add_meta_box( 'wps-dashboard-feed', '<i class="dashicons dashicons-format-status"></i>' . esc_html( 'WPShop News', 'wpshop' ), array( $this, 'wps_dashboard_feed' ), 'wpshop_dashboard', 'right_column' );
	}

	/**
	 * Display metabox with main shop summary
	 */
	function wps_dashboard_right_now() {
		global $wpdb;
		require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, 'backend', 'metabox', 'right_now' ) );
	}

	/**
	 * Display metabox with quick links
	 */
	function wps_dashboard_quick_links() {
		require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, 'backend', 'metabox', 'quicklinks' ) );
	}

	/**
	 * Display metabox with quick links
	 */
	function wps_dashboard_customer_stats() {
		global $wpdb;
	}

	/**
	 * Display metabox with shop main statistics
	 */
	function wps_dashboard_statistics() {
		global $wpdb;

		require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, 'backend', 'metabox', 'statistics' ) );
	}

	/**
	 * Display metabox with shop main statistics
	 */
	function wps_dashboard_infos() {
		global $wpdb;
		require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, 'backend', 'metabox', 'infos' ) );
	}

	/**
	 * Display metabox with shop main statistics
	 */
	function wps_dashboard_feed() {
		require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, 'backend', 'metabox', 'feed' ) );
	}

	/**
	 * Display metabox with recent orders list
	 */
	function wps_dashboard_orders() {
		require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, 'backend', 'metabox', 'orders' ) );
	}

	/**
	 * Display metabox for export
	 */
	function wps_dashboard_export() {
		if ( class_exists( 'wps_export_ctr' ) ) {
			$wps_export = new wps_export_ctr();
			$wps_export->wps_export_tpl();
		}
	}

}
