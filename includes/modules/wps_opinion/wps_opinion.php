<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Bootstrap file
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */

DEFINE('WPS_OPINION_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_OPINION_PATH', str_replace( "\\", "/", str_replace( WPS_OPINION_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_OPINION_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_OPINION_PATH ) );
load_plugin_textdomain( 'wps_opinion', false, dirname(plugin_basename( __FILE__ )).'/languages/' );

include( plugin_dir_path( __FILE__ ).'/config/config.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wps_opinion_ctr.php' );
include( plugin_dir_path( __FILE__ ).'/model/wps_opinion_model.php' );

$wps_opinion = new wps_opinion_ctr();
