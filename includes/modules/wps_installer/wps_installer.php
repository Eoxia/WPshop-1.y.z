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
DEFINE('WPS_INSTALLER_VERSION', '1.0');

/** Template Global vars **/
DEFINE('WPS_INSTALLER_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_INSTALLER_PATH_TO_MODULE', str_replace( str_replace( "\\", "/", WP_PLUGIN_DIR ), "", str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) ) );
DEFINE('WPS_INSTALLER_PATH', str_replace( "\\", "/", str_replace( WPS_INSTALLER_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_INSTALLER_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_INSTALLER_PATH ) );

load_plugin_textdomain( 'wps_installer', false, dirname(plugin_basename(__FILE__)).'/languages/');

include( WPS_INSTALLER_PATH . WPS_INSTALLER_DIR . '/config/config.php' );

include( WPS_INSTALLER_PATH . WPS_INSTALLER_DIR . '/controller/wps_installer_ctr.php' );
include( WPS_INSTALLER_PATH . WPS_INSTALLER_DIR . '/model/wps_installer_model.php' );

$wps_installer_ctr  = new wps_installer_ctr();

?>
