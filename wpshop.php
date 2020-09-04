<?php
/**
 * Plugin Name: WP-Shop
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: With this plugin you will be able to manage the products you want to sell and user would be able to buy this products
 * Version: 1.6.3
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 * Text Domain: wpshop
 * Domain Path: /languages
 *
 * @package wpshop
 */

/**
 * Plugin main file.
 *
 * This file is the main file called by WordPress for our plugin use. It define the basic vars and include the different file needed to use the plugin
 *
 * @author Eoxia <dev@eoxia.com>
 * @version 1.5.2
 * @package wpshop
 */

ini_set( 'memory_limit', '512M' );

/**    Check if file is include. No direct access possible with file url    */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access is not allowed by this way' );
}

/**    Allows to refresh css and js file in final user browser    */
DEFINE( 'WPSHOP_VERSION', '1.6.3' );

/**    Allows to avoid problem with theme not supporting thumbnail for post    */
add_theme_support( 'post-thumbnails' );
add_image_size( 'wpshop-product-galery', 270, 270, true );
add_image_size( 'wps-categorie-mini-display', 80, 80, true );
add_image_size( 'wps-categorie-display', 480, 340, true );

/**    First thing we define the main directory for our plugin in a super global var    */
DEFINE( 'WPSHOP_PLUGIN_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPSHOP_PLUGIN_NAME', plugin_basename( __FILE__ ) );

/**    Get the current language to translate the different text in plugin    */
$locale = get_locale();
global $wpdb;
if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
	$query = $wpdb->prepare( 'SELECT locale FROM ' . $wpdb->prefix . 'icl_locale_map WHERE code = %s', ICL_LANGUAGE_CODE );
	$local = $wpdb->get_var( $query );
	$locale = ! empty( $local ) ? $local : $locale;
}
DEFINE( 'WPSHOP_CURRENT_LOCALE', $locale );
/**    Load plugin translation    */
load_plugin_textdomain( 'wpshop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**    Include the config file    */
require WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/config.php';

/** Allow to get errors back when debug mode is set to true    */
if ( WPSHOP_DEBUG_MODE && (in_array( long2ip( ip2long( $_SERVER['REMOTE_ADDR'] ) ), unserialize( WPSHOP_DEBUG_MODE_ALLOWED_IP ) )) ) {
	ini_set( 'display_errors', true );
	error_reporting( E_ALL );
}

include_once WPSHOP_LIBRAIRIES_DIR . 'init.class.php';
$current_installation_step = get_option( 'wps-installation-current-step', 1 );

/**    Get current plugin version    */
$current_db_version = get_option( 'wpshop_db_options', 0 );

/**    Call main initialisation function    */
add_action( 'init', array( 'wpshop_init', 'load' ) );

/**    Include the main including file    */
require WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/include.php';

/**    Check and set (if needed) administrator(s) permissions' each time the plugin is launched. Admin role has all right    */
$wpshop_permissions = new wpshop_permissions();
$wpshop_permissions->set_administrator_role_permission();
$wpshop_permissions->wpshop_init_roles();

/**    Call function to create the main left menu    */
// if ( ( WPSINSTALLER_STEPS_COUNT <= $current_installation_step ) || ( !empty( $current_db_version ) && !empty( $current_db_version[ 'db_version' ] ) && ( 51 < $current_db_version[ 'db_version' ] ) ) || ( !empty( $_GET ) && !empty( $_GET[ 'installation_state' ] ) && ( "ignored" == $_GET[ 'installation_state' ] ) ) ) {
add_action( 'admin_menu', array( 'wpshop_init', 'admin_menu' ) );
add_action( 'menu_order', array( 'wpshop_init', 'admin_menu_order' ) );
add_action( 'custom_menu_order', array( 'wpshop_init', 'admin_custom_menu_order' ) );

/*    Call function for new WordPress element creating [term (product_category) / post (product)]    */
add_action( 'init', array( 'wpshop_init', 'add_new_wp_type' ) );

/*    Call function allowing to change element front output    */
add_action( 'the_content', array( 'wpshop_display', 'products_page' ), 1 );
// add_action('archive_template', array('wpshop_categories', 'category_template_switcher'));
// }
/**    On plugin activation create the default parameters to use the ecommerce    */
register_activation_hook( __FILE__, array( 'wpshop_install', 'install_on_activation' ) );

/**    Get current plugin version    */
$current_db_version = get_option( 'wpshop_db_options', 0 );

/**    Add the database content    */
add_action( 'admin_init', array( 'wpshop_install', 'update_wpshop' ) );
if ( (defined( 'WPSINSTALLER_STEPS_COUNT' ) && (WPSINSTALLER_STEPS_COUNT <= $current_installation_step)) || ( ! empty( $current_db_version ) && ! empty( $current_db_version['db_version'] ) && (51 < $current_db_version['db_version'])) || ( ! empty( $current_db_version ) && ! empty( $current_db_version['installation_state'] ) && ('ignore' == $current_db_version['installation_state'])) ) {
	if ( in_array( long2ip( ip2long( $_SERVER['REMOTE_ADDR'] ) ), unserialize( WPSHOP_DEBUG_MODE_ALLOWED_IP ) ) ) {
		add_action( 'admin_init', array( 'wpshop_install', 'update_wpshop_dev' ) );
	}
}
// Start session
if ( session_id() == '' ) {
	 session_start();
}

// WP-Shop class instanciation
function classes_init() {
	global $wpshop_cart, $wpshop, $wpshop_account, $wpshop_payment;
	$wpshop = new wpshop_form_management();
	$wpshop_payment = new wpshop_payment();
}
add_action( 'init', 'classes_init' );

/** Shortcode management */
add_shortcode( 'wpshop_att_val', array( 'wpshop_attributes', 'wpshop_att_val_func' ) ); // Attributes
add_shortcode( 'wpshop_products', array( 'wpshop_products', 'wpshop_products_func' ) ); // Products list
add_shortcode( 'wpshop_product', array( 'wpshop_products', 'wpshop_products_func' ) ); // Products list
add_shortcode( 'wpshop_product_variation_summary', array( 'wpshop_products', 'wpshop_product_variations_summary' ) ); // Variation summary
add_shortcode( 'wpshop_product_variation_value_detail', array( 'wpshop_products', 'wpshop_product_variation_value_detail' ) ); // Variation value detail
add_shortcode( 'wpshop_related_products', array( 'wpshop_products', 'wpshop_related_products_func' ) ); // Products list
add_shortcode( 'wpshop_category', array( 'wpshop_categories', 'wpshop_category_func' ) ); // Category
add_shortcode( 'wpshop_att_group', array( 'wpshop_attributes_set', 'wpshop_att_group_func' ) ); // Attributes groups
add_shortcode( 'wpshop_cart', 'wpshop_display_cart' ); // Cart
// add_shortcode('wpshop_mini_cart', 'wpshop_display_mini_cart'); // Mini cart
// add_shortcode('wpshop_signup', 'wpshop_signup_init'); // Signup
// add_shortcode('wpshop_myaccount', 'wpshop_account_display_form' );
add_shortcode( 'wpshop_payment_result', array( 'wpshop_payment', 'wpshop_payment_result' ) ); // Payment result
add_shortcode( 'wpshop_payment_result_unsuccessfull', array( 'wpshop_payment', 'wpshop_payment_result' ) ); // Payment result

add_shortcode( 'wpshop_variations', array( 'wpshop_products', 'wpshop_variation' ) );
add_shortcode( 'wpshop_entities', array( 'wpshop_entities', 'wpshop_entities_shortcode' ) );
add_shortcode( 'wpshop_attributes', array( 'wpshop_attributes', 'wpshop_attributes_shortcode' ) );
