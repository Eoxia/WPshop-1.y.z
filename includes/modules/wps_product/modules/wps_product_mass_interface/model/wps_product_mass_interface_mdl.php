<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Model file for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Model class for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_product_mass_interface_mdl extends wps_product_mdl {

	/**
	 * Returns Products with post data and its attributes configuration
	 * @param integer $limit
	 * @param integer $count_products
	 * @return array
	 */
	function get_quick_interface_products( $attribute_set_id, $start_limit = 0, $nb_product_per_page = 20, $order = 'ID', $order_by = 'ASC' ) {
		global $wpdb;

		/*switch( $order ) {
			case ''
		}*/

		$products_data = array();
		// Get products in queried limits
		$query = $wpdb->prepare(
			"SELECT *
			FROM {$wpdb->posts}, {$wpdb->postmeta}
			WHERE post_type = %s
				AND post_status IN ( 'publish', 'draft' )
				AND ID = post_id
				AND meta_key = %s
				AND meta_value = %s
			ORDER BY {$order} DESC
			LIMIT " . $start_limit * $nb_product_per_page . ", " .$nb_product_per_page .""
			, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, '_wpshop_product_attribute_set_id', $attribute_set_id );
		$products = $wpdb->get_results( $query );

		if( !empty($products) ) {
			foreach( $products as $product ) {
				// For each product stock Post Datas and attributes definition
				$tmp = array();
				$tmp['post_datas'] = $product;
				$tmp['attributes_datas'] = $this->get_product_atts_def($product->ID);
				//echo '<pre>'; print_r( $tmp['attributes_datas'] ); exit('</pre>');
				$products_data[] = $tmp;
			}
		}

		return $products_data;
	}


	function get_product_attributes_sets() {
		global $wpdb;
		$product_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		$query = $wpdb->prepare( 'SELECT * FROM '. WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d AND status = %s', $product_entity_id, 'valid' );
		$attributes_groups = $wpdb->get_results( $query );

		return $attributes_groups;
	}


	function get_attributes_quick_add_form() {
		global $wpdb;
		$product_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		$query = $wpdb->prepare( 'SELECT * FROM '. WPSHOP_DBT_ATTRIBUTE . ' WHERE entity_id = %d AND is_used_in_quick_add_form = %s AND status = %s', $product_entity_id, 'yes', 'valid' );
		$attributes = $wpdb->get_results( $query, ARRAY_A );

		return $attributes;
	}

}

?>
