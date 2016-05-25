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
class wpspos_customer_quick_add {

	/**
	 * Instanciation des différents composants pour le module / Main module components for quick produict creation module
	 */
	public function __construct() {
		/**	Affiche un formulaire permettant de créer un client / Display a form allowing to add a new customer	*/
		add_action( 'wp_ajax_wpspos-customer-quick-creation', array( $this, 'customer_creation' ) );
		add_action( 'wp_ajax_wpspos-customer-quick-add', array( $this, 'create_customer' ) );
	}


	/**
	 * AJAX - Charge le fomulaire d'ajout rapide d'un client / Load the form for new customer quick add
	 */
	function customer_creation() {
		$_wpnonce = !empty( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps-customer-quick-nonce' ) )
			wp_die();

		// check_ajax_referer( 'wps-customer-quick-nonce', 'wps-nonce' );
		global $wpdb;

		$customer_entity_type_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
		$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, get_current_user_id() );
		$cid = $wpdb->get_var( $query );

		$customer_attribute_set = !empty( $_GET ) && !empty( $_GET[ 'customer_set_id' ] ) ? (int)$_GET[ 'customer_set_id' ] : null;

		$customer_attributes = wpshop_attributes_set::getAttributeSetDetails( $customer_attribute_set, "'valid'");

		require_once( wpshop_tools::get_template_part( WPSPOSCLTQUICK_DIR, WPSPOSCLTQUICK_TEMPLATES_MAIN_DIR, "backend", "customer", "creation" ) );
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

		/**	Read sended values for checking	*/
		$email_founded = false;
		$user = array();
		$email_field = $last_name_field = $first_name_field = '';


		$data = array(
			'attribute' => !empty( $_POST['attribute'] ) ? (array)$_POST['attribute'] : array(),
		);

		$quick_add_customer = wps_customer_ctr::quick_add_customer( $data );
		switch( $quick_add_customer ) {
			case 1:
				$response[ 'output' ] = __('An email address is required', 'wpshop');
				break;
			case 2:
				$response[ 'output' ] = __('An account is already created with this e-mail address', 'wpshop');
				break;
			case is_array($quick_add_customer):
				/**	Build a response for customer first letter - Specific action (POS)	*/
				if ( !empty($quick_add_customer['varchar']['last_name']) ) {
					$field_for_letter = $quick_add_customer['varchar']['last_name'];
				}
				elseif( !empty($quick_add_customer['varchar']['first_name']) ) {
					$field_for_letter = $quick_add_customer['varchar']['first_name'];
				}
				else {
					$field_for_letter = $quick_add_customer['varchar']['user_email'];
				}
				$response[ 'letter' ] = substr( $field_for_letter, 0, 1);

				/**	Build response	*/
				$response[ 'status' ] = true;
				$response[ 'output' ] = __('Customer created succesfully', 'wpshop');
				$response[ 'customer_id' ] = $quick_add_customer['integer']['ID'];

				/** Create customer address from sended data **/
				// $_REQUEST['user']['customer_id'] = (int)$quick_add_customer['integer']['ID'];
				$attribute_to_save = $data['attribute'];
				$customer_id = !empty( $_POST[ 'wps-customer-account-set-id' ] ) ? (int) $_POST[ 'wps-customer-account-set-id' ] : 0;
				// unset( $_POST['attribute'] );
				// $_POST['attribute'][ (int)$_POST[ 'wps-customer-account-set-id' ] ] = $attribute_to_save;
				wps_address::save_address_infos( $customer_id );
				break;
		}

		wp_die( json_encode( $response ) );
	}

}

?>
