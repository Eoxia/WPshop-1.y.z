<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Main controller file for product into point of sale management plugin
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */

/**
 * Main controller class for product into point of sale management plugin
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_pos_addon_product {

	/**
	 * Call the different element to instanciate the product module
	 */
	function __construct() {
		/**	Call dashboard metaboxes	*/
		add_action( 'admin_init', array( $this, 'dashboard_metaboxes' ) );

		/**	Point d'accroche AJAX / AJAX listeners	*/
		/**	Vérification du type de produit avant ajout au panier / Check the product type before adding it into cart	*/
		//add_action( 'wap_ajax_wps-pos-product-check-type', array( $this, 'ajax_pos_check_product_type' ) );
		/**	Affiche le formulaire permettant de sélectionner la déclinaison du produit / Display the form allowing to choose product variation	*/
		add_action( 'wp_ajax_wps-pos-product-variation-selection', array( $this, 'ajax_pos_product_variation_selection' ) );
		/**	Lance la recherche de produit / Launch product search	*/
		add_action( 'wp_ajax_wpspos-product-search', array( $this, 'ajax_pos_product_search' ) );
	}

	/**
	 * WP CUSTOM HOOK - Call metaboxes for POS addon dashboard
	 */
	function dashboard_metaboxes() {
		/**	Create metaboxes for upper area	*/

		/**	Create metaboxes for left side	*/
		ob_start();
		require_once( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/products', 'metabox_title', 'list' ) );
		$metabox_title = ob_get_contents();
		ob_end_clean();
		add_meta_box( 'wpspos-dashboard-product-metabox', $metabox_title, array( $this, 'dashboard_product_metabox' ), 'wpspos-dashboard', 'wpspos-dashboard-left' );

		/**	Create metaboxes for right side	*/
	}

	/**
	 * WP CUSTOM METABOX - Display a custom metabox for choosing product
	 */
	function dashboard_product_metabox() {
		global $wpdb;

		/**	Get existing product first letter to only display letter with product as selectable	*/
		$query = $wpdb->prepare( "SELECT GROUP_CONCAT( LEFT( UPPER( post_title ), 1 ) ) AS LETTER FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish' );
		$available_letters = $wpdb->get_var( $query );

		/**	Check the first letter available for product to choose the good one when displaying default interface	*/
		$letters_having_products = array_unique( explode( ',', $available_letters ) );
		sort( $letters_having_products );

		require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/products', 'metabox', 'product' ) );
	}

	/**
	 * PRODUCT - Return the product Table for a letter
	 * @param char $letter
	 * @return string
	 */
	function get_product_table_by_alphabet( $letter ) {
		$output = '';

		if ( !empty($letter) ) {
			$product_list = $this->get_product_list_by_letter( $letter );
			$tpl_component = array();
			if( !empty( $product_list ) ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/products', 'products' ) );
				$output = ob_get_contents();
				ob_end_clean();
			}
		}

		return $output;
	}

	/**
	 * PRODUCT - Return a list of product for a letter
	 *
	 * @param char $letter The letter to get the product list for. Could be "ALL" if all product are requested to be output
	 *
	 * @return array The list of product correspondaing to given alphabet letter
	 */
	function get_product_list_by_letter( $letter ) {
		/** INI SET **/
		@set_time_limit( 120 );

		$product_list = array();
		global $wpdb;
		$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
		if ( !empty( $letter) ) {

			if ( $letter == __('ALL', 'wps-pos-i18n') ) {
				$query = $wpdb->prepare( "
					SELECT P.ID, P.post_title, PM.meta_value
					FROM {$wpdb->posts} AS P
						INNER JOIN {$wpdb->postmeta} AS PM ON ( PM.post_id = P.ID )
					WHERE P.post_type = %s
						AND P.post_status = %s
						AND PM.meta_key = %s
					ORDER BY P.post_title", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish', '_wpshop_product_metadata' );
			}
			else {
				$query = $wpdb->prepare( "
					SELECT P.ID, P.post_title, PM.meta_value
					FROM {$wpdb->posts} AS P
						INNER JOIN {$wpdb->postmeta} AS PM ON ( PM.post_id = P.ID )
					WHERE P.post_type = %s
						AND P.post_status = %s
						AND ( post_title LIKE %s OR post_title LIKE %s )
						AND PM.meta_key = %s
					ORDER BY P.post_title", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish', strtoupper( $letter ).'%', strtolower( $letter).'%', '_wpshop_product_metadata' );
			}
			$products = $wpdb->get_results( $query );

			if ( !empty( $products ) && is_array( $products ) ) {
				foreach ( $products as $product ) {
					$product_post_meta = unserialize( $product->meta_value );
					if ( !empty($product_post_meta) ) {
						//$product_variation_definition = get_post_meta( $product->ID, '_wpshop_variation_defining', true );
						$product_list[] = array(
							'ID' => $product->ID,
							'product_name' => $product->post_title,
							'product_price' => !empty( $product_post_meta ) && !empty( $product_post_meta[ 'product_price' ] ) ? number_format( (float)$product_post_meta['product_price'], 2, '.', '') : '',
							'product_barcode' => !empty( $product_post_meta['barcode'] ) ? $product_post_meta['barcode'] : ''
						);
					}
				}
			}
		}

		return $product_list;
	}

	/**
	 * AJAX - Vérifie si le produit sur le point d'être ajouté à la commande est un produit simple ou un produit composé / Check if the selected produt is a simple one or a combined one
	 */
	function ajax_pos_check_product_type() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'ajax_pos_check_product_type' ) )
			wp_die();

		$product_type = 'simple';

		$product_id = ( !empty($_POST['product_id']) ) ? (int) $_POST['product_id'] : null;
		if ( !empty($product_id) ) {
			$product_post_meta = get_post_meta( $product_id, '_wpshop_variation_defining', true );
			if ( !empty( $product_post_meta ) ) {
				$product_type = 'variations';
			}
		}

		wp_die( json_encode( array( 'product_type' => $product_type, ) ) );
	}

	/**
	 * AJAX - Affiche le formulaire permettant de sélectionner la déclinaison du produit / Display the form allowing to choose product variation
	 */
	function ajax_pos_product_variation_selection() {
		check_ajax_referer( 'ajax_pos_product_variation_selection' );

		/**	Get the product identifier to display variation chooser	*/
		$product_id = !empty( $_GET ) && !empty( $_GET[ 'product_id' ] ) && is_int( (int)$_GET[ 'product_id' ] ) ? (int)$_GET[ 'product_id' ] : null;

		require_once( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/products', 'modal', 'variation' ) );
		wp_die();
	}

	/**
	 * AJAX - Lance la recherche de produit / Launch product search
	 */
	function ajax_pos_product_search() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'ajax_pos_product_search' ) )
			wp_die();

		$term = !empty( $_POST ) && !empty( $_POST[ 'term' ] ) ? sanitize_text_field( $_POST[ 'term' ] ) : null;
		$response = array(
			'status' => false,
			'action' => '',
		);
		if ( !empty( $term ) ) {
			$search_in = !empty( $_POST[ 'search_in' ] ) ? sanitize_text_field( $_POST[ 'search_in' ] ) : '';

			$wps_product_mdl = new wps_product_mdl();
			$results = $wps_product_mdl->get_products_by_title_or_barcode( $term, !empty( $search_in ) && 'only_barcode' == $search_in );

			if ( !empty( $results ) ) {
				if ( 1 < count( $results ) ) {
					foreach ( $results as $product ) {
						$product_post_meta = get_post_meta( $product->ID, '_wpshop_product_metadata', true );
						if ( !empty($product_post_meta) ) {
							$product_list[] = array(
								'ID' => $product->ID,
								'product_name' => $product->post_title,
								'product_price' => number_format( (float)$product_post_meta['product_price'], 2, '.', ''),
								'product_barcode' => !empty( $product_post_meta['barcode'] ) ? $product_post_meta['barcode'] : ''
							);
						}
					}

					ob_start();
					require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/products', 'products' ) );
					$output = ob_get_contents();
					ob_end_clean();
				}
				else {
					$product_post_meta = get_post_meta( $results[ 0 ]->ID, '_wpshop_variation_defining', true );
					if ( !empty( $product_post_meta ) ) {
						$response[ 'action' ] = 'variation_selection';
					}
					else {
						$response[ 'action' ] = 'direct_to_cart';
					}
					$output = $results[ 0 ]->ID;
				}

				/**	Build response to send	*/
				$response[ 'status' ] = true;
				$response[ 'output' ] = $output;
			}
			else {
				ob_start();
				require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/products', 'product', 'not_found' ) );
				$response[ 'output' ] = ob_get_contents();
				ob_end_clean();
			}
		}

		$response['_wpnonce'] = wp_create_nonce( 'ajax_pos_product_variation_selection' );

		wp_die( json_encode( $response ) );
	}

}

?>
