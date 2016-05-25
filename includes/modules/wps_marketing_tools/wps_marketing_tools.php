<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * @author ALLEGRE Jérôme - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 */

/** Template Global vars **/
DEFINE('WPS_MARKETING_TOOLS_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_MARKETING_TOOLS_PATH', str_replace( "\\", "/", str_replace( WPS_MARKETING_TOOLS_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_MARKETING_TOOLS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_MARKETING_TOOLS_PATH ) );



include( plugin_dir_path( __FILE__ ).'/controller/wps_marketing_tools_ctr.php' );
// include( plugin_dir_path( __FILE__ ).'/model/wps_message_mdl.php' );

$wps_marketing_tools = new wps_marketing_tools_ctr();
