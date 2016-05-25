<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WpShop Help bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
DEFINE( 'WPS_HELP_VERSION', '1.0.1' );
DEFINE( 'WPS_HELP_DIR', basename(dirname(__FILE__)));
DEFINE( 'WPS_HELP_PATH', dirname( __FILE__ ) );
DEFINE( 'WPS_HELP_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', str_replace( "\\", "/", WPS_HELP_PATH ) ) );

/**	Define the templates directories	*/
DEFINE( 'WPS_HELP_TEMPLATES_MAIN_DIR', WPS_HELP_PATH . '/templates/');

require_once( WPS_HELP_PATH . '/controller/wps_help_menus_ctr.php' );
require_once( WPS_HELP_PATH . '/controller/wps_help_bubble_ctr.php' );
require_once( WPS_HELP_PATH . '/controller/wps_help_tabs_ctr.php' );

/**	Instanciate task management*/
global $wps_help_menu;
$wps_help_menu = new wps_help_menus_ctr();
new wps_help_bubble_ctr();
global $wps_help_tabs;
$wps_help_tabs = new wps_help_tabs_ctr();

?>
