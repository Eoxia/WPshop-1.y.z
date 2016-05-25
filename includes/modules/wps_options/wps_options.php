<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * @author ALLEGRE Jérôme - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 */

/** Template Global vars **/
DEFINE('WPS_OPTIONS_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_OPTIONS_PATH', str_replace( "\\", "/", str_replace( WPS_OPTIONS_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_OPTIONS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_OPTIONS_PATH ) );
DEFINE('WPS_OPTIONS_TEMPLATE_DIR',WPS_OPTIONS_PATH . WPS_OPTIONS_DIR . "/templates/" );

include( plugin_dir_path( __FILE__ ).'/controller/wps_display_options_ctr.php' );

$wps_dispaly_options = new wps_display_options();
