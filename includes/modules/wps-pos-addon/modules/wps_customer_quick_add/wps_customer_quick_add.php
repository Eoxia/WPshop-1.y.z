<?php if ( !defined( 'ABSPATH' ) ) exit;

DEFINE( 'WPSPOSCLTQUICK_VERSION', 1.0 );
DEFINE( 'WPSPOSCLTQUICK_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPSPOSCLTQUICK_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPSPOSCLTQUICK_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPSPOSCLTQUICK_PATH ) );

/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
DEFINE( 'WPSPOSCLTQUICK_TEMPLATES_MAIN_DIR', WPSPOSCLTQUICK_PATH . '/templates/');


/** Inclusion des différents composants / Include plugin components */
require_once( WPSPOSCLTQUICK_PATH . 'controller/wps_customer_quick_add.ctr.php' );
/**	Instanciation du controlleur principal / Main controller instanciation	*/
$wpspos_customer_quick_add = new wpspos_customer_quick_add();

?>
