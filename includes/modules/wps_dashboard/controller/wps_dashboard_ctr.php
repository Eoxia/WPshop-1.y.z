<?php
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
}

?>