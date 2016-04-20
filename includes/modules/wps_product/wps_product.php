<?php
/**
 * Plugin Name: WPShop Products
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Products
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */


if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

DEFINE( 'WPS_PRODUCT_VERSION', '2.0' );
DEFINE( 'WPS_PRODUCT_DIR', basename(dirname(__FILE__)));
DEFINE( 'WPS_PRODUCT_PATH', dirname( __FILE__ ) );
DEFINE( 'WPS_PRODUCT_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', str_replace( "\\", "/", WPS_PRODUCT_PATH ) ) );

/**	Define the templates directories	*/
DEFINE( 'WPS_PRODUCT_TEMPLATES_MAIN_DIR', WPS_PRODUCT_PATH . '/templates/');

include( plugin_dir_path( __FILE__ ).'/model/wps_product_mdl.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wps_product_ctr.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wps_product_ajax_ctr.01.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wps_product_administration_ctr.php' );

$wps_product = new wps_product_ctr();
$wps_product->install_modules();

$wps_administration_product = new wps_product_administration_ctr();

?>