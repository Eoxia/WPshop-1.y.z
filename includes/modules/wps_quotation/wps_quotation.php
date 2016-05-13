<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Define the current version for the plugin. Interresting for clear cache for plugin style and script
 * @var string Plugin current version number
 */
DEFINE( 'WPS_QUOTATION_VERSION', '1.0');

/**	Définition des constantes pour le module / Define constant for module	*/
DEFINE( 'WPS_QUOTATION_DIR', basename(dirname(__FILE__)));
DEFINE( 'WPS_QUOTATION_PATH_TO_MODULE', str_replace( str_replace( "\\", "/", WP_PLUGIN_DIR ), "", str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) ) );
DEFINE( 'WPS_QUOTATION_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPS_QUOTATION_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_QUOTATION_PATH ) );

/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
DEFINE( 'WPS_QUOTATION_TEMPLATES_MAIN_DIR', WPS_QUOTATION_PATH . '/templates/');

load_plugin_textdomain( 'wps_quotation', false, basename(dirname(__FILE__)).'/languages/');

include( plugin_dir_path( __FILE__ ).'/controller/wps_quotation_backend_ctr.php' );

new wps_quotation_backend_ctr();
