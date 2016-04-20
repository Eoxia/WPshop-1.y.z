<?php
/**
 * Plugin Name: WP-Shop - Ajout de produit rapide / Product quick creation
 * Version: 1.0
 * Description: Permet de créer des produits au travers d'une infterface d'ajout rapide / Allows to add a new product with quick interface
 * Author: Eoxia <dev@eoxia.com>
 * Author URI: http://www.eoxia.com
 */

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