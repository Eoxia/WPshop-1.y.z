<?php
if ( !defined( 'ABSPATH' ) ) exit;

DEFINE( 'WPSPDTVARIATION_INTERFACE_VERSION', 1.0 );
DEFINE( 'WPSPDTVARIATION_INTERFACE_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPSPDTVARIATION_INTERFACE_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPSPDTVARIATION_INTERFACE_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPSPDTVARIATION_INTERFACE_PATH ) );

/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
DEFINE( 'WPSPDTVARIATION_INTERFACE_TEMPLATES_MAIN_DIR', WPSPDTVARIATION_INTERFACE_PATH . '/templates/');
DEFINE( 'WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR', WPSPDTVARIATION_INTERFACE_URL . '/assets/');


/** Inclusion des différents composants / Include plugin components */
require_once( WPSPDTVARIATION_INTERFACE_PATH . 'controller/wps_product_variation_interface.ctr.php' );
/**	Instanciation du controlleur principal / Main controller instanciation	*/
$wps_product_variation_interface = new wps_product_variation_interface();

?>