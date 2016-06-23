<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_product_ajax_ctr_01 {
	public function __construct() {
		add_action( 'wp_ajax_checking_products_values', array( &$this, 'ajax_render_inconsistent_product_price' ) );
		add_action( 'wp_ajax_save_products_prices', array( &$this, 'ajax_save_product_price' ) );
	}

	/**
	 * Récupères toutes la listes des produits dont les prix sont incohérent et le nombre
	 * de produit incohérent trouvé. Puis affiches le template product_check_data /
	 * Recovered all the lists of products whose price are inconsistent and count this
	 * list. Then display the template product_check_data
	 */
	public function ajax_render_inconsistent_product_price() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'ajax_render_inconsistent_product_price' ) )
			wp_die();

		$list_product = wps_product_ctr::get_inconsistent_product();

		$inconsistent_product_number = count($list_product);

		require( wpshop_tools::get_template_part( WPS_PRODUCT_DIR, WPS_PRODUCT_TEMPLATES_MAIN_DIR, "backend", "product_check_data") );
		wp_die();
	}

	/**
	 * Récupère le pilotage de prix, le nombre de produit avec un prix incohérent, le type de l'entité et la langue de la boutique.
	 * Parcours la tableau de donnée avec la nouvelle valeur des prix par produit incohérent puis met à jour tout les autres prix de
	 * ce produit. Ensuite renvoie le template avec le nombre de prix incohérent qui on été corrigé et le template de la méthode :
	 * ajax_checking_products_values si des produits incohérents sont toujours présent. / Get the price piloting, the number of
	 * product with an inconsistent price, type of the entity and the language of the shop. Browse the given table with the new
	 * new value pricing of the inconsistent product and updates any other price of this product. Then display the template
	 * of the number of corrected product and the template of the method : ajax_checking_products_values if inconsistent product
	 * already present.
	 *
	 * @param array $_POST['product_price'] List of the new price for the product like array( $id_product => $new_price )
	 * @return JSON Response
	 */
	public function ajax_save_product_price() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'ajax_save_product_price' ) )
			wp_die();

		header( 'Content-Type: application/json' );
		$response = array();

		$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );

		$inconsistent_product_number 	= !empty( $_POST['product_price'] ) ? (int) count( $_POST['product_price'] ) : 0;
		$consistent_product_number 		= 0;

		$entity_type_id	= wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
		$language 		= WPSHOP_CURRENT_LOCALE;
		$icl_language = !empty( $_REQUEST['icl_post_language'] ) ? sanitize_text_field( $_REQUEST['icl_post_language'] ) : '';

		if ( !empty($icl_language) ) {
			$query = $wpdb->prepare("SELECT locale FROM " . $wpdb->prefix . "icl_locale_map WHERE code = %s", $icl_language );
			$language = $wpdb->get_var($query);
		}

		$product_price = !empty( $_POST['product_price'] ) ? (array) $_POST['product_price'] : array();

		if( !empty( $product_price ) ) {
			foreach( $product_price as $product_id => $price ) {
				try {
					if( $price_piloting_option == 'TTC' )
						wpshop_attributes::saveAttributeForEntity( array( 'decimal' => array( 'product_price' => sanitize_text_field( $price ) )), $entity_type_id, (int)$product_id, $language, 'wpshop_products' );
					else
						wpshop_attributes::saveAttributeForEntity( array( 'decimal' => array( 'price_ht' => sanitize_text_field( $price ) )), $entity_type_id, (int)$product_id, $language, 'wpshop_product' );

					wpshop_products::calculate_price( $product_id );
					$consistent_product_number++;

				} catch (Exception $e) {
				}
			}
		}

		$response['template_number'] = __( sprintf( 'Number of processed product : %d/%d', $consistent_product_number, $inconsistent_product_number ), 'wps-product' );

		$list_product = wps_product_ctr::get_inconsistent_product();
		ob_start();
		require( wpshop_tools::get_template_part( WPS_PRODUCT_DIR, WPS_PRODUCT_TEMPLATES_MAIN_DIR, "backend", "product_check_data") );
		$response['template'] = ob_get_clean();

		wp_die( json_encode( $response ) );
	}
}

new wps_product_ajax_ctr_01();
