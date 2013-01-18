<?php
/**
 * Plugin Name: WP-Shop
 * Plugin URI: http://www.eoxia.com/wpshop-simple-ecommerce-pour-wordpress/
 * Description: With this plugin you will be able to manage the products you want to sell and user would be able to buy this products
 * Version: 1.3.3.5
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * Plugin main file.
 *
 * This file is the main file called by wordpress for our plugin use. It define the basic vars and include the different file needed to use the plugin
 * @author Eoxia <dev@eoxia.com>
 * @version 1.3
 * @package wpshop
 */

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'ABSPATH' ) ) {
	die( 'Access is not allowed by this way' );
}

/*	Allows to refresh css and js file in final user browser	*/
DEFINE('WPSHOP_VERSION', '1.3.3.5');

/*	Allows to avoid problem with theme not supporting thumbnail for post	*/
add_theme_support( 'post-thumbnails' );
add_image_size( 'wpshop-product-galery', 350, 350, true );

/**
 *	First thing we define the main directory for our plugin in a super global var
 */
DEFINE('WPSHOP_PLUGIN_DIR', basename(dirname(__FILE__)));

/*	Include the config file	*/
require(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/config.php');

/*
 * Allow to get errors back when debug mode is set to true
 */
if ( WPSHOP_DEBUG_MODE && in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_MODE_ALLOWED_IP)) ) {
	ini_set('display_errors', true);
	error_reporting(E_ALL);
}

/*	Get the current language to translate the different text in plugin	*/
$locale = get_locale();
if ( defined("ICL_LANGUAGE_CODE") ) {
	$wpml_locale = ICL_LANGUAGE_CODE;
}
if ( !empty($wpml_locale) ) {
	global $wpdb;
	$query = $wpdb->prepare("SELECT locale FROM " . $wpdb->prefix . "icl_locale_map WHERE code = %s", $wpml_locale);
	$local = $wpdb->get_var($query);
	$locale = !empty($local) ? $local : $locale;
}
DEFINE('WPSHOP_CURRENT_LOCALE', $locale);
$moFile = WPSHOP_LANGUAGES_DIR . 'wpshop-' . $locale . '.mo';
if ( !empty($locale) && (is_file($moFile)) ) {
	load_textdomain('wpshop', $moFile);
}

/*	Include the main including file	*/
require(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/include.php');

/*	Check and set (if needed) administrator(s) permissions' each time the plugin is launched. Admin role has all right	*/
wpshop_permissions::set_administrator_role_permission();
wpshop_permissions::wpshop_init_roles();

/*	Call main initialisation function	*/
add_action('init', array('wpshop_init', 'load'));

/*	Call function to create the main left menu	*/
add_action('admin_menu', array('wpshop_init', 'admin_menu'));
add_action('menu_order', array('wpshop_init', 'admin_menu_order'));
add_action('custom_menu_order', array('wpshop_init', 'admin_custom_menu_order'));

/*	Call function for new wordpress element creating [term (product_category) / post (product)]	*/
add_action('init', array('wpshop_init', 'add_new_wp_type'));

/*	Call function allowing to change element front output	*/
add_action('the_content', array('wpshop_display', 'products_page'), 1);
// add_action('archive_template', array('wpshop_categories', 'category_template_switcher'));

/*	On plugin activation create the default parameters to use the ecommerce	*/
register_activation_hook( __FILE__ , array('wpshop_install', 'install_on_activation') );

/*	On plugin deactivation call the function to clean the wordpress installation	*/
register_deactivation_hook( __FILE__ , array('wpshop_install', 'uninstall_wpshop') );

/*	Add the database content	*/
add_action('admin_init', array('wpshop_install', 'update_wpshop'));
if(in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_MODE_ALLOWED_IP)))add_action('admin_init', array('wpshop_install', 'update_wpshop_dev'));

/*	Check if the admin want to ignore configuration	*/
if(isset($_GET['ignore_installation']) && ($_GET['ignore_installation']=='true')){
	$current_db_version = get_option('wpshop_db_options', 0);
	$current_db_version['installation_state'] = 'ignore';
	update_option('wpshop_db_options', $current_db_version);
}

/*	Get current plugin version	*/
$current_db_version = get_option('wpshop_db_options', 0);

/*	Check the db installation state for admin message output	*/
if(empty($current_db_version['installation_state']) || !in_array($current_db_version['installation_state'], array('completed','ignore'))) {
	add_action('admin_notices', array('wpshop_notices', 'install_admin_notice'));
}

/*	Check the configuration state	*/
if(isset($_GET['installation_state']) && !empty($_GET['installation_state']) && ($current_db_version['installation_state']!='completed')){
	$current_db_version['installation_state'] = $_GET['installation_state'];
	update_option('wpshop_db_options', $current_db_version);
}

/*	Do verification for shop who are configured for being sale shop	*/
if(isset($current_db_version['installation_state']) && ($current_db_version['installation_state']=='completed') && (WPSHOP_DEFINED_SHOP_TYPE == 'sale')){
	add_action('admin_notices', array('wpshop_notices','sale_shop_notice'));
}

// Start session
@session_start();

// WP-Shop class instanciation
function classes_init() {
	global $wpshop_cart, $wpshop, $wpshop_account, $wpshop_payment;
	$wpshop_cart = new wpshop_cart();
	$wpshop = new wpshop_form_management();
	$wpshop_account = new wpshop_account();
	$wpshop_payment = new wpshop_payment();
	$wpshop_webservice = new wpshop_webservice();
}
add_action('init', 'classes_init');

/*	Instanciate the wysiwyg editor hooks	*/
add_action('init', array('wpshop_shortcodes', 'wysiwyg_button'));
add_filter('tiny_mce_version', array('wpshop_shortcodes', 'refresh_wysiwyg'));

/*
 * Shortcode management
 */
add_shortcode('wpshop_att_val', array('wpshop_attributes', 'wpshop_att_val_func')); // Attributes
add_shortcode('wpshop_products', array('wpshop_products', 'wpshop_products_func')); // Products list
add_shortcode('wpshop_product', array('wpshop_products', 'wpshop_products_func')); // Products list
add_shortcode('wpshop_product_variation_summary', array('wpshop_products', 'wpshop_product_variations_summary')); // Variation summary
add_shortcode('wpshop_product_variation_value_detail', array('wpshop_products', 'wpshop_product_variation_value_detail')); // Variation value detail
add_shortcode('wpshop_related_products', array('wpshop_products', 'wpshop_related_products_func')); // Products list
add_shortcode('wpshop_category', array('wpshop_categories', 'wpshop_category_func')); // Category
add_shortcode('wpshop_att_group', array('wpshop_attributes_set', 'wpshop_att_group_func')); // Attributes groups
add_shortcode('wpshop_cart', 'wpshop_display_cart'); // Cart
add_shortcode('wpshop_mini_cart', 'wpshop_display_mini_cart'); // Mini cart
add_shortcode('wpshop_checkout', 'wpshop_checkout_init'); // Checkout
add_shortcode('wpshop_signup', 'wpshop_signup_init'); // Signup
add_shortcode('wpshop_myaccount', 'wpshop_account_display_form'); // Customer account
add_shortcode('wpshop_payment_result', array('wpshop_payment', 'wpshop_payment_result')); // Payment result

add_shortcode('wpshop_custom_search', array('wpshop_search', 'wpshop_custom_search_shortcode')); // Custom search
add_shortcode('wpshop_advanced_search', array('wpshop_search', 'wpshop_advanced_search_shortcode')); // Advanced search

add_shortcode('wpshop_variations', array('wpshop_products', 'wpshop_variation'));
add_shortcode('wpshop_entities', array('wpshop_entities', 'wpshop_entities_shortcode'));
add_shortcode('wpshop_attributes', array('wpshop_attributes', 'wpshop_attributes_shortcode'));

/*
 * Add specific messages for wpshop elements management
 */
add_filter('post_updated_messages', array('wpshop_messages', 'update_wp_message_list'));

?>