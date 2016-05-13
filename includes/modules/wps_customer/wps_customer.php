<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * WpShop Customer Account bootstrap file
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
	DEFINE('WPS_ACCOUNT_DIR', basename(dirname(__FILE__)));
	DEFINE('WPS_ACCOUNT_PATH', str_replace( "\\", "/", str_replace( WPS_ACCOUNT_DIR, "", dirname( __FILE__ ) ) ) );
	DEFINE('WPS_ACCOUNT_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_ACCOUNT_PATH ) );

	include( plugin_dir_path( __FILE__ ).'controller/wps_customer_ctr.php' );
	include( plugin_dir_path( __FILE__ ).'controller/wps_customer_metaboxes.controller.01.php' );
	include( plugin_dir_path( __FILE__ ).'controller/wps_account_ctr.php' );
	include( plugin_dir_path( __FILE__ ).'controller/wps_account_dashboard_ctr.php' );
	include( plugin_dir_path( __FILE__ ).'model/wps_customer_mdl.php' );
	include( plugin_dir_path( __FILE__ ).'controller/wps_customer_group.php' );
// 	include( plugin_dir_path( __FILE__ ).'controller/customer_custom_list_table.class.php' );
// 	include( plugin_dir_path( __FILE__ ).'controller/wp_list_custom_groups.class.php' );
	include( plugin_dir_path( __FILE__ ).'controller/wp_list_custom_entities_customers.php' );
	include( plugin_dir_path( __FILE__ ).'controller/wps_provider_ctr.php' );

	$wps_customer = new wps_customer_ctr();
	$wps_account = new wps_account_ctr();
	$wps_account_dashboard = new wps_account_dashboard_ctr();
	$wps_provider = new wps_provider_ctr();
	// Add customer admin
	if( is_admin() ) {
		include( plugin_dir_path( __FILE__ ).'controller/wps_customer_admin_ctr.php' );
		$wps_customer_admin = new wps_customer_admin();
		$wps_customer_admin->install_modules();
	}
?>
