<?php if ( !defined( 'ABSPATH' ) ) exit;

DEFINE( 'WPSPOSPDTQUICK_VERSION', 1.0 );
DEFINE( 'WPSPOSPDTQUICK_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPSPOSPDTQUICK_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPSPOSPDTQUICK_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPSPOSPDTQUICK_PATH ) );

/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
DEFINE( 'WPSPOSPDTQUICK_TEMPLATES_MAIN_DIR', WPSPOSPDTQUICK_PATH . '/templates/');


/** Inclusion des différents composants / Include plugin components */
require_once( WPSPOSPDTQUICK_PATH . 'controller/wps_product_quick_add.ctr.php' );
/**	Instanciation du controlleur principal / Main controller instanciation	*/
$wpspos_product_quick_add = new wpspos_product_quick_add();

?>
