<?php if ( !defined( 'ABSPATH' ) ) exit;

if( !class_exists('wps_pos_addon') ) {

	DEFINE( 'WPSPOS_VERSION', 1.0 );
	DEFINE( 'WPSPOS_DIR', basename( dirname( __FILE__ ) ) );
	DEFINE( 'WPSPOS_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
	DEFINE( 'WPSPOS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPSPOS_PATH ) );

	/**	Chargement des fichiers de traductions / Load plugin translation	*/
	load_plugin_textdomain( 'wps-pos-i18n', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
	DEFINE( 'WPSPOS_TEMPLATES_MAIN_DIR', WPSPOS_PATH . '/templates/');

	/** Construction du tableau contenant l'alphabet / Build the alphabet letters part	*/
	DEFINE( 'WPSPOS_ALPHABET_LETTERS', serialize( array( __('ALL', 'wps-pos-i18n' ), 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z' ) ) );

	/**	Instanciation du controlleur principal / Main controller instanciation	*/
	require_once( WPSPOS_PATH . 'controller/wps-pos-addon.ctr.php' );
	$wps_pos_addon = new wps_pos_addon();
	/**	Appel automatique des modules présent dans le plugin / Install automatically modules into module directory	*/
	$wps_pos_addon->install_modules();
}
