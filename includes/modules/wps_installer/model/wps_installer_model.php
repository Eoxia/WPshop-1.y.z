<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Module controller class definition file
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 0.1
 * @package wps_installer
 * @subpackage model
 */

/**
 * Module controller class definition
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 0.1
 * @package wps_installer
 * @subpackage model
 */
class wps_installer_model {

	/**
	 * Module instanciation
	 */
	function __construct() { }

	/**
	 * Save informations sended by the user. Step by tep
	 *
	 * @param integer $the_step_to_save The current step to save
	 */
	function save_step( $the_step_to_save ) {

		switch ( $the_step_to_save ) {
			case 1:
				/**	Save company informations	*/
				$wpshop_company_info = !empty( $_POST['wpshop_company_info'] ) ? sanitize_text_field( $_POST['wpshop_company_info'] ) : '';
				if ( !empty( $wpshop_company_info ) ) {
					update_option( 'wpshop_company_info', $wpshop_company_info );
				}

				/**	Save the company logo in case a file is sended	*/
				$shop_logo = !empty( $_POST['wpshop_logo'] ) ? sanitize_text_field( $_POST['wpshop_logo'] ) : '';
				if ( !empty( $shop_logo ) ) {
					update_option( 'wpshop_logo', $shop_logo );
				}
				break;

			case 2:
				/**	Save shop type	*/
				$wpshop_shop_type = !empty( $_POST['wpshop_shop_type'] ) ? sanitize_text_field( $_POST[ 'wpshop_shop_type' ] ) : '';
				if ( !empty( $wpshop_shop_type ) ) {
					update_option( 'wpshop_shop_type', $wpshop_shop_type );
				}

				/**	Save shop price piloting	*/
				$wpshop_shop_price_piloting = !empty( $_POST[ 'wpshop_shop_price_piloting' ] ) ? sanitize_text_field( $_POST[ 'wpshop_shop_price_piloting' ] ) : '';
				if ( !empty( $wpshop_shop_price_piloting ) ) {
					update_option( 'wpshop_shop_price_piloting', $wpshop_shop_price_piloting );
				}

				/**	Insert default pages	*/
				//add_action( 'init', array( 'wpshop_install', 'wpshop_insert_default_pages' ) );
				//add_action( 'init', array( 'wps_message_ctr', 'create_default_message' ) );

				/**	In case the user want to insert default datas	*/
				$wps_installer_data_insertion = !empty( $_POST[ 'wps-installer-data-insertion' ] ) ? sanitize_text_field( $_POST[ 'wps-installer-data-insertion' ] ) : '';
				if ( !empty( $wps_installer_data_insertion ) && ( 'yes' == $wps_installer_data_insertion ) ) {
					/**	Insert sample datas */
					add_action( 'init', array( 'wpshop_install', 'import_sample_datas' ) );
				}

				$wps_current_db_version = get_option( 'wpshop_db_options', 0 );
				$wps_current_db_version[ 'installation_state' ] = 'completed';
				$wps_current_db_version[ 'installation_date' ] = current_time( 'mysql', 0 );
				update_option( 'wpshop_db_options', $wps_current_db_version );
				break;
		}

		if ( !empty( $the_step_to_save ) ) {
			update_option( 'wps-installation-current-step', ( WPSINSTALLER_STEPS_COUNT != $the_step_to_save ? $the_step_to_save + 1 : WPSINSTALLER_STEPS_COUNT ) );
		}
	}

	/**
	 * Save the current state of installation process
	 *
	 * @param string $setted_state A parameter setted for saving the state of installation ( in progress, completed, ignored )
	 * @param array $current_db_version The current value for database version option
	 */
	function installer_state_saver( $setted_state, $current_db_version ) {
		$current_db_version['installation_state'] = $setted_state;
		$current_db_version[ 'installation_date' ] = current_time( 'mysql', 0 );
		update_option('wpshop_db_options', $current_db_version);

		if ( "ignored" == $current_db_version['installation_state'] ) {
			/**	Create the different pages	*/
			if ( 2 >= get_option( 'wps-installation-current-step' ) ) {
				update_option( 'wpshop_shop_price_piloting', 'TTC' );
				update_option( 'wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE );

				/**	Insert default pages	*/
				//add_action( 'init', array( 'wpshop_install', 'wpshop_insert_default_pages' ) );
				/**	Insert default emails	*/
				//add_action( 'init', array( 'wps_message_ctr', 'create_default_message' ) );
			}

			/**	Save the crrent step to 4 in order to consider that installation is done	*/
			update_option( 'wps-installation-current-step', WPSINSTALLER_STEPS_COUNT );
		}

		return $current_db_version;
	}

}

?>
