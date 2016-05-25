<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Bootstrap file
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */

DEFINE('WPS_COUPON_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_COUPON_PATH', str_replace( "\\", "/", str_replace( WPS_COUPON_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_COUPON_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_COUPON_PATH ) );


include( plugin_dir_path( __FILE__ ).'/controller/wps_coupon_ctr.php' );
include( plugin_dir_path( __FILE__ ).'/model/wps_coupon_model.php' );
$wps_coupon = new wps_coupon_ctr();
