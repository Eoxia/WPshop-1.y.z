<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * File for installer control class definition
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 *
 */

/**
 * Class for installer control
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 *
 */
class wps_installer_ctr {

	/**	Get the current step of installation	*/
	private $current_installation_step;

	/**
	 * Instanciate the module controller
	 */
	function __construct() {

		/**	Call administration style definition & scripts	*/
		add_action( 'admin_init', array( &$this, 'admin_scripts' ) );

		$current_step = ( !empty( $_GET['wps-installation-step'] ) ) ? sanitize_title( $_GET['wps-installation-step'] ) : $this->current_installation_step;

		/**	Instanciate datas saver components */
		$wps_installer_model = new wps_installer_model();

		$action = !empty( $_POST[ 'action' ] ) ? sanitize_text_field( $_POST[ 'action' ] ) : '';
		/**	Call datas saver	*/
		if ( !empty( $current_step ) && !empty( $action ) && ( "wps-installation" == $action ) ) {
			$step_to_save = $current_step - 1;
			$wps_installer_model->save_step( $step_to_save );
			if ( WPSINSTALLER_STEPS_COUNT == $current_step ) {
				add_action( 'init', array( &$this, 'go_to_wpshop_about' ) );
			}
		}

		/**	Set the current installatino step	*/
		$this->current_installation_step = get_option( 'wps-installation-current-step', 1 );

		/**	Get current version for wpshop plugin	*/
		$wps_current_db_version = get_option( 'wpshop_db_options', 0 );

		/**	Check the configuration state	*/
		$installation_state = !empty( $_GET[ 'installation_state' ] ) ? sanitize_text_field( $_GET[ 'installation_state' ] ) : '';
		if ( isset( $installation_state ) && !empty( $installation_state ) && !empty( $wps_current_db_version )
		&& (empty( $wps_current_db_version[ 'installation_state' ] ) || ( $wps_current_db_version[ 'installation_state' ] != 'completed' ) ) ) {
			$wps_current_db_version = $wps_installer_model->installer_state_saver( $installation_state, $wps_current_db_version );
		}

		/**	Do verification for shop who are configured for being sale shop	*/
		$current_page = strstr( $_SERVER[ "REQUEST_URI" ], 'wps-about');
		if ( isset( $wps_current_db_version['installation_state'] ) && ( $wps_current_db_version[ 'installation_state' ] == 'completed' ) && ( WPSHOP_DEFINED_SHOP_TYPE == 'sale' ) && empty( $current_page ) ) {
			add_action( 'admin_notices', array( 'wpshop_notices' , 'sale_shop_notice' ) );
		}

		/**	Create an administration menu	*/
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );

		/**	In case that configuration have not been done and that instalation is not asked to be ignored	*/
		if ( empty( $wps_current_db_version ) || empty( $wps_current_db_version[ 'installation_state' ] ) || ( !empty( $wps_current_db_version[ 'installation_state' ] ) && !in_array( $wps_current_db_version[ 'installation_state' ], array( 'completed', 'ignored' ) ) ) ) {
			/*	Check the db installation state for admin message output	*/
			$current_page = strstr( $_SERVER[ "REQUEST_URI" ], 'wps-installer');
			if( ( WPSINSTALLER_STEPS_COUNT > $this->current_installation_step ) && ( empty( $current_page ) ) ) {
				add_action( 'admin_notices', array( &$this, 'install_admin_notice' ) );
			}
		}

		/**	Hook wpshop dashboard in order to display the notice banner with quick links - wordpress like	*/
		add_filter( 'wps_dashboard_notice', array( $this, 'wps_dashboard_notice' ), 10, 1 );

		/**	Hook ajax action when clicking on hide welcome banner on wpshop dashboard	*/
		add_action( 'wp_ajax_wps-hide-welcome-panel', array( $this, 'wps_hide_welcome_panel' ) );
	}


	/**
	 * Enqueue style definition
	 * Enqueue scripts definition
	 */
	function admin_scripts($hook_suffix) {
		if ( 'wps-installer.php' !== $hook_suffix )
        	return;

		wp_register_style( 'wps_installer_style', WPS_INSTALLER_URL . WPS_INSTALLER_DIR . '/assets/css/backend-styles.css', '', WPS_INSTALLER_VERSION );
		wp_enqueue_style( 'wps_installer_style' );
		wp_enqueue_script( 'wps-installer-admin-scripts', WPS_INSTALLER_URL . WPS_INSTALLER_DIR . '/assets/js/backend-scripts.js', array( 'jquery' ), WPS_INSTALLER_VERSION );
	}

	/**
	 * Call the menu for displaying installer interface
	 */
	function admin_menu() {
		/**	Get current version for wpshop plugin	*/
		$wps_current_db_version = get_option( 'wpshop_db_options', 0 );

		add_menu_page( __( 'Install WPShop', 'wpshop' ), __( 'WPShop - install', 'wpshop' ), 'manage_options', 'wps-installer', array( &$this, 'installer_main_page' ) );
		remove_menu_page( 'wps-installer' );
	}

	/**
	 * Display the installer interface
	 */
	function installer_main_page() {
		$current_step = ( !empty( $_GET['wps-installation-step'] ) ) ? wpshop_tools::varSanitizer( $_GET['wps-installation-step'] ) : $this->current_installation_step;
		$steps = unserialize( WPSINSTALLER_STEPS );

		/**	Get the defined shop type in order to display the different element to	*/
		$wps_shop_type = get_option( 'wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE );

		/** Check the current step to display */
		$current_step_output = '';
		$the_step_file = '';
		switch( $current_step ) {
			case 2:
				$the_step_file = 'step_two';
				break;

			default:
				$the_step_file = 'step_one';
				break;
		}

		/**	Create display for current step	*/
		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_INSTALLER_DIR, WPSINSTALLER_TPL_DIR, "backend", $the_step_file ) );
		$current_step_output = ob_get_contents();
		ob_end_clean();

		require_once( wpshop_tools::get_template_part( WPS_INSTALLER_DIR, WPSINSTALLER_TPL_DIR, "backend", "installer" ) );
	}

	/**
	 * Create a notice for admin user when plugin is activated and not yet configured
	 */
	function install_admin_notice() {
		require_once( wpshop_tools::get_template_part( WPS_INSTALLER_DIR, WPSINSTALLER_TPL_DIR, "backend", "notice" ) );
	}

	/**
	 * Redirect the user automatically to the wpshop about page
	 */
	function go_to_wpshop_about() {
		wp_redirect( admin_url( 'admin.php?page=wpshop_about' ) );
		exit();
	}

	/**
	 * DISPLAY - Output the about page for wpshop
	 */
	function wps_about_page() {
		require_once( wpshop_tools::get_template_part( WPS_INSTALLER_DIR, WPSINSTALLER_TPL_DIR, "backend", "about" ) );
	}

	/**
	 * DISPLAY - Display a banner on wpshop dashboard
	 */
	function wps_dashboard_notice() {
		$user_pref = get_user_meta( get_current_user_id(), '_wps_hide_notice_messages_indicator', true );

		if ( empty( $user_pref ) || empty( $user_pref[ 'welcome-banner' ] ) ) {
			/**	Get current shop type	*/
			$shop_type = get_option( 'wpshop_shop_type' );

			/**	Get the current number of product created	*/
			$nb_products = 0;
			$created_products = wp_count_posts( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
			if ( !empty( $created_products ) ) {
				foreach ( $created_products as $created_product_type => $created_product_nb) {
					/**	Don't count product that are automatically created and not accessible through amin interface	*/
					if ( !in_array( $created_product_type, array( 'auto-draft', 'inherit' ) ) ) {
						$nb_products += $created_product_nb;
					}
				}
			}

			/**	Get configuration about payment method 	*/
			$no_payment_mode_configurated = true;
			if ( !empty($paymentMethod ) && !empty($paymentMethod['mode']) ) {
				foreach( $paymentMethod['mode'] as $k => $pm ) {
					if ( !empty($pm['active'] ) ) {
						$no_payment_mode_configurated = false;
					}
				}
			}

			/**	Get configuration about emails 	*/
			$emails = get_option('wpshop_emails', array() );

			require_once( wpshop_tools::get_template_part( WPS_INSTALLER_DIR, WPSINSTALLER_TPL_DIR, "backend", "welcome" ) );
		}
	}

	/**
	 * AJAX - Launch ajax action allowing to hide welcome panel for current user
	 */
	function wps_hide_welcome_panel() {
		$wpshop_ajax_nonce = !empty( $_REQUEST['wpshop_ajax_nonce'] ) ? sanitize_text_field( $_REQUEST['wpshop_ajax_nonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps-installer-welcome-panel-close' ) )
			wp_die();

		$user_pref = get_user_meta( get_current_user_id(), '_wps_hide_notice_messages_indicator', true );
		$user_pref[ 'welcome-banner' ] = true;

		$response = array(
			'status' => false,
		);

		$user_pref = update_user_meta( get_current_user_id(), '_wps_hide_notice_messages_indicator', $user_pref );
		if ( false !== $user_pref ) {
			$response[ 'status' ] = true;
		}

		wp_die( json_encode( $response ) );
	}
}

?>
