<?php
/**
 * Plugin Name: WP Shop Shortcodes
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Shortcodes
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

DEFINE( 'WPS_SHORTCODES_VERSION', '1.0.1' );
DEFINE( 'WPS_SHORTCODES_DIR', basename(dirname(__FILE__)));
DEFINE( 'WPS_SHORTCODES_PATH', dirname( __FILE__ ) );
DEFINE( 'WPS_SHORTCODES_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', str_replace( "\\", "/", WPS_SHORTCODES_PATH ) ) );

/**	Define the templates directories	*/
DEFINE( 'WPS_SHORTCODES_TEMPLATES_MAIN_DIR', WPS_SHORTCODES_PATH . '/templates/');

require_once( WPS_SHORTCODES_PATH . '/controller/wps_shortcodes_ctr.php' );

new wps_shortcodes_ctr();


/*	Instanciate the wysiwyg editor hooks	*/
add_action('init', array('wps_shortcodes_ctr', 'wysiwyg_button'));
add_filter('tiny_mce_version', array('wps_shortcodes_ctr', 'refresh_wysiwyg'));


?>