<?php
/**
 * Plugin Name: WPShop Products mass administration interface
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: Manage your products from a mass update interface, that allow you to change a lot of information easoer
 * Version: 01.0
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */


if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

DEFINE( 'WPS_PDCT_MASS_VERSION', '2.0' );
DEFINE( 'WPS_PDCT_MASS_DIR', basename(dirname(__FILE__)));
DEFINE( 'WPS_PDCT_MASS_PATH', dirname( __FILE__ ) );
DEFINE( 'WPS_PDCT_MASS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', str_replace( "\\", "/", WPS_PDCT_MASS_PATH ) ) );


/**	Define the templates directories	*/
DEFINE( 'WPS_PDCT_MASS_TEMPLATES_MAIN_DIR', WPS_PDCT_MASS_PATH . '/templates/' );


include( plugin_dir_path( __FILE__ ) . '/controller/wps_product_mass_interface_ctr.php' );
include( plugin_dir_path( __FILE__ ) . '/model/wps_product_mass_interface_mdl.php' );

$wps_product_mass_interface_ctr = new wps_product_mass_interface_ctr();

?>