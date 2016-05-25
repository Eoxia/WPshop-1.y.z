<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Bootstrap file for plugin. Do main includes and create new instance for plugin components
 *
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 */
/** Define */
DEFINE( 'WPS_GUIDED_VERSION', '1.0.0' );
DEFINE( 'WPS_GUIDED_DIR', basename(dirname(__FILE__)));
DEFINE( 'WPS_GUIDED_PATH', dirname( __FILE__ ) );
DEFINE( 'WPS_GUIDED_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', str_replace( "\\", "/", WPS_GUIDED_PATH ) ) );
/**	Define the templates directories	*/
DEFINE( 'WPS_GUIDED_TEMPLATES_MAIN_DIR', WPS_GUIDED_PATH . '/templates/');
/** Translate */
load_plugin_textdomain( 'wps_guided_tour', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
/** Require */
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
require_once( WPS_GUIDED_PATH . '/controller/wpsBubble_ctr.php' );
require_once( WPS_GUIDED_PATH . '/controller/wpsBubbleTemplate_ctr.php' );

new wpsBubble_ctr();
?>
