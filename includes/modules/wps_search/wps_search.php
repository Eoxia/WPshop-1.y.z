<?php
/**
 * Plugin Name: WPShop Search
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: All Search type managment
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WpShop Search bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

/** Template Global vars **/
DEFINE('WPS_SEARCH_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_SEARCH_PATH', str_replace( "\\", "/", str_replace( WPS_SEARCH_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_SEARCH_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_SEARCH_PATH ) );

include( plugin_dir_path( __FILE__ ).'/controller/wps_filter_search.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wpshop_entity_filter.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wps_order_search.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wps_customer_search.php' );

$wps_filter_search = new wps_filter_search();