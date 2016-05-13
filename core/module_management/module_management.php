<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Plugin Name: Gestion des modules internes / Internal module management
 * Description: Permet d'activer ou de désactiver les différents modules internes présents dans le dossier modules / Allow to activate or deactivate internal modules present into modules directory
 * Version: 1.0
 * Author: Eoxia development team <dev@eoxia.com>
 * Author URI: http://www.eoxia.com/
 */

/**
 * Module bootstrap file
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

if ( !defined( 'EOMODMAN_VERSION' ) ) {

	/**
	 * Define the current version for the plugin. Interresting for clear cache for plugin style and script
	 * @var string Plugin current version number
	 */
	DEFINE( 'EOMODMAN_VERSION', '1.0');

	/**	Définition des constantes pour le module / Define constant for module	*/
	DEFINE( 'EOMODMAN_DIR', basename(dirname(__FILE__)));
	DEFINE( 'EOMODMAN_PATH_TO_MODULE', str_replace( str_replace( "\\", "/", WP_PLUGIN_DIR ), "", str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) ) );
	DEFINE( 'EOMODMAN_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
	DEFINE( 'EOMODMAN_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', EOMODMAN_PATH ) );

	/**	Appel des traductions pour le module / Call translation for module	*/
	load_plugin_textdomain( 'eo-modmanager-i18n', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
	DEFINE( 'EOMODMAN_TEMPLATES_MAIN_DIR', EOMODMAN_PATH . '/templates/');

	include( EOMODMAN_PATH . '/controller/module_management.ctr.php' );
	$eo_module_management  = new eo_module_management();

	eo_module_management::core_util();

}
