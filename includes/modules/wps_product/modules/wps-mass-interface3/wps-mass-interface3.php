<?php
/**
 * Module file, include dependency & instance controller.
 *
 * @package wps-mass-interface3
 */

DEFINE( 'WPS_PDCT_MASS_VERSION', '3.0' );
DEFINE( 'WPS_PDCT_MASS_URL', plugin_dir_url( __FILE__ ) );
DEFINE( 'WPS_PDCT_MASS_INCLUDE_PATH', dirname( __FILE__ ) . '/include/' );
DEFINE( 'WPS_PDCT_MASS_INCLUDE_URL', WPS_PDCT_MASS_URL . 'include/' );
DEFINE( 'WPS_PDCT_MASS_JS', WPS_PDCT_MASS_INCLUDE_URL . 'js/' );
DEFINE( 'WPS_PDCT_MASS_CSS', WPS_PDCT_MASS_INCLUDE_URL . 'css/' );
DEFINE( 'WPS_PDCT_MASS_CHOSEN_JS', WPS_PDCT_MASS_JS . 'chosen-v1.7.0/' );
DEFINE( 'WPS_PDCT_MASS_CHOSEN_CSS', WPS_PDCT_MASS_CSS . 'chosen-v1.7.0/' );

include_once( WPS_PDCT_MASS_INCLUDE_PATH . 'class-mass-interface3.php' );
new mass_interface3();
