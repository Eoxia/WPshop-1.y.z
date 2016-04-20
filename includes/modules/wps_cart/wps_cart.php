<?php
/**
 * Plugin Name: WPShop Cart
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Cart Managment
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WpShop Cart bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */


if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

/** Template Global vars **/
DEFINE('WPS_CART_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_CART_PATH', str_replace( "\\", "/", str_replace( WPS_CART_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_CART_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_CART_PATH ) );

DEFINE('WPS_CART_TPL_DIR', WPS_CART_PATH . WPS_CART_DIR . "/templates/" );

// Include Controller
include( plugin_dir_path( __FILE__ ).'controller/wps_cart_ctr.php' );

// Init Controller
$wps_cart = new wps_cart();


