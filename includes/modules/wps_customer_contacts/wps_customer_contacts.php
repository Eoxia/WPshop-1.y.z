<?php
/**
 * Gestion des contacts pour les clients dans WPShop / WpShop Customer contacts management
 *
 * @author Eoxia dev team <dev@eoxia.com>
 * @version 1.0.0.0
 * @package Customers
 * @subpackage Contacts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPSHOP_VERSION' ) ) {
	die( esc_html( 'You are not allowed to use this service.', 'wpshop' ) );
}

/** Template Global vars */
DEFINE( 'WPS_CUST_CONTACT_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPS_CUST_CONTACT_PATH', str_replace( '\\', '/', plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPS_CUST_CONTACT_URL', str_replace( str_replace( '\\', '/', ABSPATH ), site_url() . '/', WPS_CUST_CONTACT_PATH ) );
DEFINE( 'WPS_CUST_CONTACT_TPL', WPS_CUST_CONTACT_PATH . '/templates/' );

include( plugin_dir_path( __FILE__ ) . 'controller/wps-customer-contact.action.php' );
