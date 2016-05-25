<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier du controlleur principal du module de création de client rapide / Controller file for quick customer creation
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */

/**
 * Classe du controlleur principal du module de création de client rapide / Main controller class for quick customer creation
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_customer_quick_add {

	/**
	 * Instanciation des différents composants pour le module / Main module components for quick produict creation module
	 */
	public function __construct() {
		/**	Affiche un formulaire permettant de créer un client / Display a form allowing to add a new customer	*/
		add_action( 'wp_ajax_wps-customer-quick-creation', array( $this, 'customer_creation' ) );
		add_action( 'wp_ajax_wps-customer-quick-add', array( $this, 'create_customer' ) );
	}


	/**
	 * AJAX - Charge le fomulaire d'ajout rapide d'un client / Load the form for new customer quick add
	 */
	function customer_creation() {
		$_wpnonce = sanitize_key( $_REQUEST['wps-nonce'] );
		if ( !wp_verify_nonce( $_wpnonce, 'wps-customer-quick-nonce' ) )
			wp_die();
		global $wpdb;

		$customer_entity_type_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
		$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, get_current_user_id() );
		$cid = $wpdb->get_var( $query );

		$customer_attribute_set = !empty( $_GET ) && !empty( $_GET[ 'customer_set_id' ] ) ? (int)$_GET[ 'customer_set_id' ] : null;

		$customer_attributes = wpshop_attributes_set::getAttributeSetDetails( $customer_attribute_set, "'valid'");

		require_once( wpshop_tools::get_template_part( WPSCLTQUICK_DIR, WPSCLTQUICK_TEMPLATES_MAIN_DIR, "backend", "customer", "creation" ) );
		wp_die( );
	}


	/**
	 * AJAX - Création d'un nouveau client / Create a new customer
	 */
	function create_customer() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'create_customer' ) )
			wp_die();


		global $wpdb;
		$response = array(
			'status' => false,
			'output' => __( 'An error occured while saving customer', 'wpshop' ),
			'customer_id' => -1,
		);

		/**	Check if a attribute set id have been sended in order to check if therer are some check to do on sended input	*/
		$customer_attributes = wpshop_attributes_set::getAttributeSetDetails( (int)$_POST[ 'wps-customer-account-set-id' ], "'valid'");
		$attribute = !empty( $_POST['attribute'] ) ? (array)$_POST['attribute'] : array();
		/**	Read sended values for checking	*/
		$email_founded = false;
		$email_field = $last_name_field = $first_name_field = '';

		foreach ( $attribute as $attribute_type => $attributes ) {
			foreach ( $attributes as $attribute_code => $attribute_value ) {
				$query = $wpdb->prepare( "SELECT frontend_verification FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $attribute_code );
				$current_attribute = $wpdb->get_var( $query );

				if ( 'email' == $current_attribute ) {
					$email_founded = true;
					$email_field = $attribute_code;
					$email_field_type = $attribute_type;
				}
				else if ( strpos( $attribute_code, 'last_name')) {
					$last_name_field = $attribute_code;
					$last_name_field_type = $attribute_type;
				}
				else if ( strpos( $attribute_code, 'first_name')) {
					$first_name_field = $attribute_code;
					$first_name_field_type = $attribute_type;
				}
			}
		}

		/**	Define customer email field value	*/
		$customer_email = sanitize_email( $attribute[ $email_field_type ][ $email_field ] );
		$customer_last_name = !empty( $attribute ) && !empty( $attribute[ $last_name_field_type ] ) && !empty( $attribute[ $last_name_field_type ][ $last_name_field ] ) ? sanitize_text_field( $attribute[ $last_name_field_type ][ $last_name_field ] ) : '';
		$customer_first_name = !empty( $attribute ) && !empty( $attribute[ $first_name_field_type ] ) && !empty( $attribute[ $first_name_field_type ][ $first_name_field ] ) ? sanitize_text_field( $attribute[ $first_name_field_type ][ $first_name_field ] ) : '';

		if ( $email_founded && is_email( $customer_email ) ) {
			/**	Check if current e-mail address does not already exists	*/
			$user_id = username_exists( $customer_email );
			if ( empty( $user_id ) ) {

				/**	Create the user with a random password	*/
				$random_password = wp_generate_password( 12, false );
				$user_id = wp_create_user( $customer_email, $random_password, $customer_email );

				if ( !is_wp_error( $user_id ) ) {
					update_user_meta( $user_id, 'last_name', $customer_last_name );
					update_user_meta( $user_id, 'first_name', $customer_first_name );

					/**	Build a response for customer first letter - Specific action (POS)	*/
					if ( !empty($customer_last_name) ) {
						$field_for_letter = $customer_last_name;
					}
					elseif( !empty($customer_first_name)  ) {
						$field_for_letter = $customer_first_name;
					}
					else {
						$field_for_letter = $customer_email;
					}
					$response[ 'letter' ] = substr( $field_for_letter, 0, 1);

					/**	Build response	*/
					$response[ 'status' ] = true;
					$response[ 'output' ] = __('Customer created succesfully', 'wpshop');

					/** Create customer address from sended data **/
					// $_REQUEST['user']['customer_id'] = (int)$user_id;
					$attribute_to_save = $attribute;
					$wps_customer_account_set_id = !empty( $_POST[ 'wps-customer-account-set-id' ] ) ? (int) $_POST[ 'wps-customer-account-set-id' ] : 0;
					$attribute[ $wps_customer_account_set_id ] = $attribute_to_save;
					wps_address::save_address_infos( $wps_customer_account_set_id );
				}
			}
			else {
				$response[ 'output' ] = __('An account is already created with this e-mail address', 'wpshop');
			}
			$response[ 'customer_id' ] = $user_id;
		}
		else {
			$response[ 'output' ] = __('An email address is required', 'wpshop');
		}

		wp_die( json_encode( $response ) );
	}

}

?>
