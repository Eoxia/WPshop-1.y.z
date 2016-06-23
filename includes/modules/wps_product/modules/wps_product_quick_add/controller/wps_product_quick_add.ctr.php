<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier du controlleur principal du module de création de produit rapide / Controller file for quick product creation
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */

/**
 * Classe du controlleur principal du module de création de produit rapide / Main controller class for quick product creation
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_product_quick_add {

	/**
	 * Instanciation des différents composants pour le module / Main module components for quick produict creation module
	 */
	public function __construct() {
		/**	Affiche un formulaire permettant de créer un produit / Display a form allowing to add a new product	*/
		add_action( 'wp_ajax_wps-product-quick-creation', array( $this, 'product_creation' ) );
		add_action( 'wp_ajax_wps-product-quick-add-reload-attribute-list', array( $this, 'attribute_list_reload' ) );
		add_action( 'wp_ajax_wps-product-quick-add', array( $this, 'create_product' ) );
	}

	/**
	 * DISPLAY - Affiche les champs correspondants aux attributs pour un groupe d'attribut donné / Output attributes inputs for selected attribute set
	 *
	 * @param integer $chosen_set Le groupe d'attribut dont on veut afficher les champs / Attribute set we want display input for
	 */
	function display_attribute( $chosen_set = 0 ) {
		require_once( wpshop_tools::get_template_part( WPSPDTQUICK_DIR, WPSPDTQUICK_TEMPLATES_MAIN_DIR, "backend", "attribute", "list" ) );
	}

	/**
	 * AJAX - Charge le fomulaire d'ajout rapide d'un produit / Load the form for new product quick add
	 */
	function product_creation() {
		$_wpnonce = !empty( $_POST['wps-nonce'] ) ? sanitize_text_field( $_POST['wps-nonce'] ) : '';
		if ( !wp_verify_nonce( $_wpnonce, 'wps-product-quick-nonce' ) )
			wp_die();

		require_once( wpshop_tools::get_template_part( WPSPDTQUICK_DIR, WPSPDTQUICK_TEMPLATES_MAIN_DIR, "backend", "product_creation" ) );
		wp_die( );
	}

	/**
	 * AJAX - Recharge la liste des attributs du groupe sélectionné par l'administratuer pour la création du nouveau produit / Reload attribute list for the selected attribute set, choosen by administrator for new product creation
	 */
	function attribute_list_reload() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'attribute_list_reload' ) )
			wp_die();

		$attribute_set = !empty( $_POST['attribute_set'] ) ? (int) $_POST['attribute_set'] : 0;
		$this->display_attribute( $attribute_set );
		wp_die( );
	}

	/**
	 * AJAX - Création d'un nouveau produit / Create a new product
	 */
	function create_product() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'create_product' ) )
			wp_die();

		global $wpdb;
		$response = array(
			'status' => false,
			'output' => __('Error at product creation!', 'wpshop'),
			'pid' => -1,
		);

		$post_title = ( !empty($_POST['post_title']) ) ? sanitize_text_field( $_POST['post_title'] ) : -1;
		$post_content = ( !empty($_POST['post_content']) ) ? sanitize_text_field( $_POST['post_content'] ) : '';
		$attributes = ( !empty($_POST['attribute']) ) ? (array)$_POST['attribute'] : array();
		$id_attribute_set = ( !empty($_POST['wps-product-attribute-set']) ) ? (int) $_POST['wps-product-attribute-set'] : -1;

		$post_type = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
		$attribute_set_list = wpshop_attributes_set::get_attribute_set_list_for_entity( wpshop_entities::get_entity_identifier_from_code( 'wpshop_product' ) );
		foreach( $attribute_set_list as $attribute_set ) {
			if( $id_attribute_set == $attribute_set->id && $attribute_set->slug == 'free_product' ) {
				$post_type = 'free_product';
			}
		}

		if( ( -1 != $post_title ) && ( -1 != $id_attribute_set ) ) {
			$new_product_id = wp_insert_post( array(
				'post_type' => $post_type,
				'post_status' => 'publish',
				'post_title' => $post_title,
				'post_content' => $post_content,
			) );

			if( !is_wp_error( $new_product_id ) ) {
				update_post_meta( $new_product_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute_set_id', $id_attribute_set );
				$data_to_save['post_ID'] = $data_to_save['product_id'] = intval( $new_product_id );
				$data_to_save['wpshop_product_attribute'] = ( !empty($attributes) ) ? $attributes : array();
				$data_to_save['user_ID'] = get_current_user_id();
				$data_to_save['action'] = 'editpost';
				$response[ 'pid' ] = $new_product_id;
				$response[ 'status' ] = false;
				$response[ 'output' ] = __('Product created partially!', 'wpshop');
				if( !empty( $new_product_id ) && !empty( $data_to_save['user_ID'] ) ) {
					$product_class = new wpshop_products();
					$product_class->save_product_custom_informations( $new_product_id, $data_to_save );
					$response[ 'status' ] = true;
					$response[ 'output' ] = __('Product created successfully.', 'wpshop');
				}

				$wps_quick_creation_hook = do_action( 'wps-new-product-quick-created', $new_product_id );
				if ( !empty( $wps_quick_creation_hook ) ) {
					$response[ '' ] = $wps_quick_creation_hook;
				}
			}
		}

		wp_die( json_encode( $response ) );
	}

}

?>
