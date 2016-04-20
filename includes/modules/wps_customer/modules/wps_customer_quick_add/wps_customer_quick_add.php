<?php
/**
 * Plugin Name: WP-Shop - Ajout de client rapide / Customer quick creation
 * Version: 1.0
 * Description: Permet de créer des clients au travers d'une infterface d'ajout rapide / Allows to add a new customer with quick interface
 * Author: Eoxia <dev@eoxia.com>
 * Author URI: http://www.eoxia.com
 */

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