<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * WPShop Statistics bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

/** */
DEFINE( 'WPS_STATISTICS_VERSION', '1.0.1' );
DEFINE( 'WPS_STATISTICS_DIR', basename(dirname(__FILE__)));
DEFINE( 'WPS_STATISTICS_PATH', dirname( __FILE__ ) );
DEFINE( 'WPS_STATISTICS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', str_replace( "\\", "/", WPS_STATISTICS_PATH ) ) );

/**	Define the templates directories	*/
DEFINE( 'WPS_STATISTICS_TEMPLATES_MAIN_DIR', WPS_STATISTICS_PATH . '/templates/');

include( plugin_dir_path( __FILE__ ).'/controller/wps_statistics_ctr.php' );
include( plugin_dir_path( __FILE__ ).'/model/wps_statisticsmdl.php' );

$wps_statistics_ctr = new wps_statistics_ctr();
