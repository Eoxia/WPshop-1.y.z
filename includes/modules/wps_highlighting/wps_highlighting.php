<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Bootstrap file
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */

DEFINE('WPS_HIGHLIGHTING_DIR', basename(dirname(__FILE__)) );
DEFINE('WPS_HIGHLIGHTING_PATH', str_replace( "\\", "/", str_replace( WPS_HIGHLIGHTING_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_HIGHLIGHTING_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_HIGHLIGHTING_PATH ) );

load_plugin_textdomain( 'wps_highlighting', false, dirname(plugin_basename( __FILE__ )).'/languages/' );

include( plugin_dir_path( __FILE__ ).'/config/config.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wps_highlighting_ctr.php' );
include( plugin_dir_path( __FILE__ ).'/model/wps_highlighting_model.php' );

$wps_highlight = new wps_highlighting_ctr();
