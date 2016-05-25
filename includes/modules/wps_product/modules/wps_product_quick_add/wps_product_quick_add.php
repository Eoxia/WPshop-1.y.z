<?php if ( !defined( 'ABSPATH' ) ) exit;

DEFINE( 'WPSPDTQUICK_VERSION', 1.0 );
DEFINE( 'WPSPDTQUICK_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPSPDTQUICK_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPSPDTQUICK_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPSPDTQUICK_PATH ) );

/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
DEFINE( 'WPSPDTQUICK_TEMPLATES_MAIN_DIR', WPSPDTQUICK_PATH . '/templates/');


/** Inclusion des différents composants / Include plugin components */
require_once( WPSPDTQUICK_PATH . 'controller/wps_product_quick_add.ctr.php' );
/**	Instanciation du controlleur principal / Main controller instanciation	*/
$wps_product_quick_add = new wps_product_quick_add();

?>
