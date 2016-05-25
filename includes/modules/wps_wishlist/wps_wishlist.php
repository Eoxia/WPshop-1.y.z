<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * WpShop Cart bootstrap file
 * @author Jimmy LATOUR- Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */


if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

/** Template Global vars **/
DEFINE('WPS_WISHLIST_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_WISHLIST_PATH', str_replace( "\\", "/", str_replace( WPS_WISHLIST_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_WISHLIST_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_WISHLIST_PATH ) );
DEFINE( 'WPS_WISHLIST_TEMPLATE_DIR', WPS_WISHLIST_PATH . WPS_WISHLIST_DIR . '/templates/');
load_plugin_textdomain( 'wps_wishlist_i18n', false, dirname(plugin_basename( __FILE__ )).'/languages/' );

// Include Controller
include( plugin_dir_path( __FILE__ ).'controller/wps_wishlist_ctr.php' );
include( plugin_dir_path( __FILE__ ).'controller/wps_wishlist_settings_ctr.php' );

// Init Controller
new wps_wishlist_settings();
$wpshop_catalog_product_option = wps_wishlist_settings::get_option();
if( !empty($wpshop_catalog_product_option) ) {
	$wps_wishlist = new wps_wishlist();
}
