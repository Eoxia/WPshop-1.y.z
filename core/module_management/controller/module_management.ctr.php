<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier du controleur principal pour la gestion des modules internes dans les extensions wordpress / Main controller file for internal modules management into wordpress plugins
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Classe du controleur principal pour la gestion des modules internes dans les extensions wordpress / Main controller class for internal modules management into wordpress plugins
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */
class eo_module_management {

	/**
	 * Instanciation du gestionnaire de modules /  Instanciate modules manager
	 */
	function __construct() {
		/**	Ajoute une interface aux options pour gérer les modules / Add an interface to plugin options screen in order to manage modules	*/
		add_action( 'admin_init', array( $this, 'declare_options' ), 11 );

		/**	Appel des styles pour l'administration / Call style for administration	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_assets' ) );
	}

	/**
	 * Inclusion des feuilles de styles pour l'administration / Admin css enqueue
	 */
	function admin_assets( $hook ) {
		if ( $hook != 'settings_page_wpshop_option' )
			return;

		wp_register_style( 'eomodmanager-admin-css', EOMODMAN_URL . '/assets/css/backend.css', '', EOMODMAN_VERSION );
		wp_enqueue_style( 'eomodmanager-admin-css' );
	}

	/**
	 * OPTIONS - Déclare les options permettant de gérer les statuts des modules / Declare add-on configuration panel for managing modules status
	 */
	function declare_options() {
		add_settings_section( 'wps_internal_modules', '<i class="dashicons dashicons-admin-plugins"></i>' . __( 'Internal modules management', 'eo-modmanager-i18n' ), '', 'wpshop_addons_options' );
		register_setting( 'wpshop_options', 'wpshop_modules', array( &$this, 'validate_options' ) );

		add_settings_field( 'wpshop_opinions_field', __( 'Internal modules management', 'eo-modmanager-i18n' ), array( &$this, 'module_listing' ), 'wpshop_addons_options', 'wps_internal_modules' );
	}

	/**
	 * OPTIONS -
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	function validate_options( $settings ) {
		if ( is_array( $settings ) ) {
			$module_option = get_option( 'wpshop_modules' );
			$log_error = array();
			foreach ( $settings as $module => $module_state ) {
				if ( !array_key_exists( 'activated', $module_state ) && ( 'on' == $module_state[ 'old_activated' ] ) ) {
					$module_option[ $module ][ 'activated' ] = 'off';
					$module_option[ $module ][ 'date_off' ] = gmdate( "Y-m-d H:i:s", time() );
					$module_option[ $module ][ 'author_off' ] = get_current_user_id();
					$settings[ $module ] = $module_option[ $module ];

					/**	Log module activation	*/
					$user = get_userdata( $module_option[ $folder ][ 'author_on' ] );
					$author = $user->display_name;
					$log_error[ 'message' ] = sprintf( __( 'Activation made on %1$s by %2$s', 'eo-modmanager-i18n' ), mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $settings[ $module ][ 'date_on' ], true ), $author);
				}
				else if ( array_key_exists( 'activated', $module_state ) && ( 'off' == $module_state[ 'old_activated' ] ) ) {
					$module_option[ $module ][ 'activated' ] = 'on';
					$module_option[ $module ][ 'date_on' ] = gmdate( "Y-m-d H:i:s", time() );
					$module_option[ $module ][ 'author_on' ] = get_current_user_id();
					$settings[ $module ] = $module_option[ $module ];

					/**	Log module activation	*/
					$user = get_userdata( $module_option[ $folder ][ 'author_off' ] );
					$author = $user->display_name;
					$log_error[ 'message' ] = sprintf( __( 'Deactivation made on %1$s by %2$s', 'eo-modmanager-i18n' ), mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $settings[ $module ][ 'date_off' ], true ), $author);
				}
				else {
					$settings[ $module ] = $module_option[ $module ];
				}
				unset( $settings[ $module ][ 'old_activated' ] );
				$log_error[ 'object_id' ] = $module;
			}

			wpeologs_ctr::log_datas_in_files( 'wps_addon', $log_error, 0 );

		}

		return $settings;
	}

	/**
	 * OPTIONS - Affiche les modules présents et leur état actuel / Display all modules and they current state
	 */
	function module_listing() {
		/**	Define the directory containing all extra modules for current plugin	*/
		$module_folder = WPSHOP_MODULES_DIR;

		/**	Get	current modules options to know if they are activated or not */
		$module_option = get_option( 'wpshop_modules' );

		/**	Check if the defined directory exists for reading and displaying an input to activate/deactivate the module	*/
		if( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );

			require_once( wpshop_tools::get_template_part( EOMODMAN_DIR, EOMODMAN_TEMPLATES_MAIN_DIR, 'backend', 'settings' ) );
		}
		else {
			_e( 'There is no modules to include into current plugin', 'eo-modmanager-i18n' );
		}

	}

	/**
	 * CORE - Activation des modules "coeur" ne devant pas être désactivés / Activation of "core" modules that does not have to be deactivated
	 */
	public static function core_util() {
		/**	Define the directory containing all "core" modules for current plugin	*/
		$module_folder = WPSHOP_DIR . '/core/';

		/**	Check if the defined directory exists for reading and including the different modules	*/
		if( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );
			foreach ( $parent_folder_content as $folder ) {
				if ( $folder && substr( $folder, 0, 1) != '.' && ( EOMODMAN_DIR != $folder ) ) {
					if ( is_dir( $module_folder . $folder ) && file_exists( $module_folder . $folder . '/' . $folder . '.php') ) {
						$f =  $module_folder . $folder . '/' . $folder . '.php';
						require( $f );
					}
				}
			}
		}
	}

	/**
	 * CORE - Activations des modules complémentaires pour l'extension / Activation of complementary modules for plugin
	 */
	public static function extra_modules() {
		/**	Define the directory containing all extra modules for current plugin	*/
		$module_folder = WPSHOP_MODULES_DIR;

		/**	Get	current modules options to know if they are activated or not */
		$module_option = get_option( 'wpshop_modules' );

		/**	Check if the defined directory exists for reading and including the different modules	*/
		if( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );
			$update_option = false;
			foreach ( $parent_folder_content as $folder ) {
				if ( $folder && substr( $folder, 0, 1) != '.' ) {
					$is_activated = false;
					/**	Check current module state to know if we have to include it or not	*/
					if ( !empty( $module_option ) && array_key_exists( $folder, $module_option ) && ( 'on' == $module_option[ $folder ][ 'activated' ] ) ) {
						$is_activated = true;
					}
					else if ( empty( $module_option ) || ( !empty( $module_option ) && !array_key_exists( $folder, $module_option ) ) ) {
						$module_option[ $folder ] = array(
							'activated' => 'on',
							'date_on' => gmdate( "Y-m-d H:i:s", time() ),
							'author_on' => 'auto',
						);
						$is_activated = true;
						$update_option = true;
					}

					/**	Finaly include module if the state allow it	*/
					if ( $is_activated && is_dir( $module_folder . $folder ) && file_exists( $module_folder . $folder . '/' . $folder . '.php') ) {
						$f =  $module_folder . $folder . '/' . $folder . '.php';
						require( $f );
					}
				}
			}
			/**	Update option only if it is necessary	*/
			if ( $update_option ) {
				update_option( 'wpshop_modules', $module_option );
			}
		}
	}

}
