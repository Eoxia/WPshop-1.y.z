<?php
/**
 * Plugin Name: WP-Shop - Interface de caisse / Point Of Sale addon
 * Version: 2.0
 * Description: Gérez vos points de vente dans votre site sous wordpress couplé à WP-Shop / Manage your point of sales within your wordpress administration with WP-Shop help
 * Author: Eoxia <dev@eoxia.com>
 * Author URI: http://www.eoxia.com
 */

if( !class_exists('wps_pos_addon') ) {

	DEFINE( 'WPSPOS_VERSION', 1.0 );
	DEFINE( 'WPSPOS_DIR', basename( dirname( __FILE__ ) ) );
	DEFINE( 'WPSPOS_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
	DEFINE( 'WPSPOS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPSPOS_PATH ) );
	
	/**	Chargement des fichiers de traductions / Load plugin translation	*/
	load_plugin_textdomain( 'wps-pos-i18n', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
	/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
	DEFINE( 'WPSPOS_TEMPLATES_MAIN_DIR', WPSPOS_PATH . '/templates/');
	
	/** Construction du tableau contenant l'alphabet / Build the alphabet letters part	*/
	DEFINE( 'WPSPOS_ALPHABET_LETTERS', serialize( array( __('ALL', 'wps-pos-i18n' ), 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z' ) ) );
	
	/**	Vérification de l'activation de WP-Shop lors de l'installation / Test if WP-Shop is active on current wordpress installation before activate POS addon */
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	/**	register_activation_hook( __FILE__, 'test_wpshop_before_activing' );
	function test_wpshop_before_activing() {
		if ( !is_plugin_active( 'wpshop/wpshop.php' ) ) {
			exit( __( 'WP-Shop is required for using POS addon. Please install and activate WP-Shop', 'wps-pos-i18n' ) );
		}
		else {
			require_once( WPSPOS_PATH . 'controller/wps-pos-addon.ctr.php' );
			wps_pos_addon::action_to_do_on_activation();
		}
	} - (Useless in WPShop, Install case 62) */
	
	/**	Vérification de l'activation de WP-Shop avant l'instanciation de l'interface de caisse / Before instanciate the POS addon check if wpshop is active on current wordpress installation	*/
	if ( is_plugin_active( 'wpshop/wpshop.php' ) ) {
	
		/** Inclusion des différents composants / Include plugin components */
		require_once( WPSPOS_PATH . 'controller/wps-pos-bank-deposit.php' );
		require_once( WPSPOS_PATH . 'controller/wps-pos-bank-deposit-histo.php' );
		require_once( WPSPOS_PATH . 'controller/wps-pos-customer.ctr.php' );
		require_once( WPSPOS_PATH . 'controller/wps-pos-product.ctr.php' );
		require_once( WPSPOS_PATH . 'controller/wps-pos-order.ctr.php' );
		require_once( WPSPOS_PATH . 'controller/wps-pos-addon.ctr.php' );
		/**	Instanciation du controlleur principal / Main controller instanciation	*/
		$wps_pos_addon = new wps_pos_addon();
		/**	Appel automatique des modules présent dans le plugin / Install automatically modules into module directory	*/
		$wps_pos_addon->install_modules();
	
	}

}

?>