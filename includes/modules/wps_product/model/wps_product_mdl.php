<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Main controller file for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Main controller class for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_product_mdl {

	/**
	 * Get product product Attributes definition
	 *
	 * @param integer $product_id
	 *
	 * @return array
	 */
	function get_product_atts_def( $product_id ) {
		$product_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		$element_atribute_list = wpshop_attributes::getElementWithAttributeAndValue( $product_entity_id, $product_id, WPSHOP_CURRENT_LOCALE, '', 'frontend' );

		$one_product = get_post_meta($product_id, WPSHOP_PRODUCT_FRONT_DISPLAY_CONF, true);
		if(!empty($one_product)) {
			$array1 = (!empty($one_product['attribute_set_section'])) ? $one_product['attribute_set_section'] : array();
			$array2 = $element_atribute_list[$product_id];
			unset($element_atribute_list);
			foreach($array2 as $key => $attribute_set_section) {
				foreach($array1 as $code1 => $value){
					if($code1 == $attribute_set_section['code']) {
						$element_atribute_list[$product_id][$key] = $attribute_set_section;
					}
				}
			}
		}

		return $element_atribute_list;
	}

	/**
	 * Return Products which name start by querying letter
	 * @param integer $letter
	 * @return array
	 */
	function get_products_by_letter( $letter = 'a' ) {
		global $wpdb;
		if ( $letter == 'all' ) {
			$query = $wpdb->prepare( 'SELECT ID, post_title FROM ' .$wpdb->posts. ' WHERE post_status = %s AND post_type = %s ORDER BY post_title ASC', 'publish', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		}
		else {
			$query = $wpdb->prepare( 'SELECT ID, post_title FROM ' .$wpdb->posts. ' WHERE post_status = %s AND post_type = %s AND post_title LIKE %s ORDER BY post_title ASC', 'publish', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $letter.'%');
		}
		$products = $wpdb->get_results( $query );
		return $products;
	}

	/**
	 * Return Products by a search
	 */
	function get_products_by_title_or_barcode( $search, $only_barcode = false ) {
		global $wpdb;
		$more_query = "";
		$query_args = array();
		$query_args[] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
		$query_args[] = $search;
		if ( !( (bool) $only_barcode ) ) {
			$more_query = " OR P.post_title LIKE %s";
			$query_args[] = '%' . $search . '%';
		}
		$query = $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->posts} AS P
				LEFT JOIN {$wpdb->postmeta} AS PM ON ( PM.post_id = P.ID )
			WHERE P.post_type = %s
				AND ( ( PM.meta_key = '_barcode' AND PM.meta_value = %s ) " . $more_query . " )
			GROUP BY P.ID", $query_args );
		return $wpdb->get_results( $query );
	}
}
