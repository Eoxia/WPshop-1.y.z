<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Main plugin configuration file
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 0.1
 * @package wps_installer
 * @subpackage config
 */

/** Check if the plugin version is defined. If not defined script will be stopped here	*/
if ( !defined( 'WPS_DASHBOARD_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wps_installer') );
}

/** Define librairies directory */
DEFINE( 'WPSDASHBOARD_LIBS_DIR', plugin_dir_path( __FILE__ ) . '/' . WPS_DASHBOARD_DIR . '/');

/** Define template directory */
DEFINE( 'WPSDASHBOARD_TPL_DIR', WPS_DASHBOARD_PATH . WPS_DASHBOARD_DIR . '/templates/');
DEFINE( 'WPSDASHBOARD_TPL_URL', WPS_DASHBOARD_URL . WPS_DASHBOARD_DIR . '/templates/');


?>