<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Bootstrap file
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */

DEFINE('WPS_EXPORT_DIR', basename(dirname(__FILE__)) );
DEFINE('WPS_EXPORT_PATH', str_replace( "\\", "/", str_replace( WPS_EXPORT_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_EXPORT_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_EXPORT_PATH ) );

load_plugin_textdomain( 'wps_export', false, dirname(plugin_basename( __FILE__ )).'/languages/' );

include( plugin_dir_path( __FILE__ ).'/controller/wps_export_ctr.php' );
include( plugin_dir_path( __FILE__ ).'/model/wps_export_mdl.php' );

$wps_export = new wps_export_ctr();
