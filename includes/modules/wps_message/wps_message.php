<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * @author ALLEGRE Jérôme - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 */

/** Template Global vars **/
DEFINE('WPS_MESSAGE_VERSION', '0.1');
DEFINE('WPS_MESSAGE_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_MESSAGE_PATH', str_replace( "\\", "/", str_replace( WPS_MESSAGE_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_MESSAGE_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_MESSAGE_PATH ) );

/** Require */
require_once( plugin_dir_path( __FILE__ ).'/controller/wps_message_ctr.php' );
require_once( plugin_dir_path( __FILE__ ).'/model/wps_message_mdl.php' );

$wps_message = new wps_message_ctr();
