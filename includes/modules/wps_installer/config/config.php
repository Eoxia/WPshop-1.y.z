<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Main plugin configuration file
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 0.1
 * @package wps_installer
 * @subpackage config
 */

/** Define librairies directory */
DEFINE( 'WPSINSTALLER_LIBS_DIR', plugin_dir_path( __FILE__ ) . '/' . WPS_INSTALLER_DIR . '/');

/** Define template directory */
DEFINE( 'WPSINSTALLER_TPL_DIR', WPS_INSTALLER_PATH . WPS_INSTALLER_DIR . '/templates/');
DEFINE( 'WPSINSTALLER_TPL_URL', WPS_INSTALLER_URL . WPS_INSTALLER_DIR . '/templates/');

$installation_step = array( __( 'Your society informations', 'wpshop'), __( 'Shop type', 'wpshop'), __( 'Have fun', 'wpshop') );
DEFINE( 'WPSINSTALLER_STEPS', serialize( $installation_step ) );
DEFINE( 'WPSINSTALLER_STEPS_COUNT', count( $installation_step ) );

?>
