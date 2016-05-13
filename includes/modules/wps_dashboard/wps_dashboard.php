<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Module bootstrap file
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Define the current version for the plugin. Interresting for clear cache for plugin style and script
 * @var string Plugin current version number
 */
DEFINE('WPS_DASHBOARD_VERSION', '1.0');

/** Template Global vars **/
DEFINE('WPS_DASHBOARD_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_DASHBOARD_PATH_TO_MODULE', str_replace( str_replace( "\\", "/", WP_PLUGIN_DIR ), "", str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) ) );
DEFINE('WPS_DASHBOARD_PATH', str_replace( "\\", "/", str_replace( WPS_DASHBOARD_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_DASHBOARD_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_DASHBOARD_PATH ) );

include( WPS_DASHBOARD_PATH . WPS_DASHBOARD_DIR . '/config/config.php' );

include( WPS_DASHBOARD_PATH . WPS_DASHBOARD_DIR . '/controller/wps_dashboard_ctr.php' );

global $wps_dashboard_ctr;
$wps_dashboard_ctr = new wps_dashboard_ctr();

?>
