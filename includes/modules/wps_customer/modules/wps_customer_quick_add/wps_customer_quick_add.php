<?php if ( !defined( 'ABSPATH' ) ) exit;

DEFINE( 'WPSCLTQUICK_VERSION', 1.0 );
DEFINE( 'WPSCLTQUICK_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPSCLTQUICK_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPSCLTQUICK_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPSCLTQUICK_PATH ) );

/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
DEFINE( 'WPSCLTQUICK_TEMPLATES_MAIN_DIR', WPSCLTQUICK_PATH . '/templates/');


/** Inclusion des différents composants / Include plugin components */
require_once( WPSCLTQUICK_PATH . 'controller/wps_customer_quick_add.ctr.php' );
/**	Instanciation du controlleur principal / Main controller instanciation	*/
$wps_customer_quick_add = new wps_customer_quick_add();

?>
