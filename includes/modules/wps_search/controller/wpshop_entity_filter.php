<?php if ( !defined( 'ABSPATH' ) ) exit;

/** Check if the plugin version is defined. If not defined script will be stopped here */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

if ( !class_exists("wpshop_entity_filter") ) {
	class wpshop_entity_filter {
		function __construct() {
			add_action('restrict_manage_posts', array(&$this, 'wpshop_entity_filter'));
			add_filter('parse_query', array(&$this, 'wpshop_entity_filter_parse_query'));
		}

		function wpshop_entity_filter() {
			$post_type = !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
			$entity_filter = !empty( $_GET['entity_filter'] ) ? sanitize_text_field( $_GET['entity_filter'] ) : '';

			if (isset($post_type)) {
				if (post_type_exists($post_type) && ($post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)) {
					$filter_possibilities = array();
					$filter_possibilities[''] = __('-- Select Filter --', 'wpshop');
					$filter_possibilities['no_picture'] = __('List products without picture', 'wpshop');
					$filter_possibilities['no_price'] = __('List products without price', 'wpshop');
					$filter_possibilities['no_description'] = __('List products without description', 'wpshop');
					$filter_possibilities['no_barcode_products'] = __('List products without barcode / with barcode not well formated', 'wpshop');
					$filter_possibilities['no_barcode_variations'] = __('List products with options without barcode / with barcode not well formated', 'wpshop');
					echo wpshop_form::form_input_select('entity_filter', 'entity_filter', $filter_possibilities, (!empty($entity_filter) ? $entity_filter : ''), '', 'index');
				}
			}
		}

		function wpshop_entity_filter_parse_query($query) {
			global $pagenow, $wpdb;

			$post_type = !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
			$entity_filter = !empty( $_GET['entity_filter'] ) ? sanitize_text_field( $_GET['entity_filter'] ) : '';

			if ( is_admin() && ($pagenow == 'edit.php') && !empty( $post_type ) && ( $post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) && !empty( $entity_filter ) ) {

				$check = null;
				switch ( $entity_filter ) {
					case 'no_picture':
						$sql_query = $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} pm WHERE meta_key = %s ", '_thumbnail_id');
						$check = 'post__not_in';
					break;
					case 'no_price':
// 						$table_attribute_decimal = $wpdb->prefix . "wpshop__attribute_value_decimal";
// 						$price_attribute = wpshop_attributes::getElement( WPSHOP_PRODUCT_PRICE_TTC, "'valid'", 'code');
// 						$sql_query = $wpdb->prepare("SELECT DISTINCT ID as post_id FROM {$wpdb->posts} WHERE post_type = %s AND ID NOT IN (SELECT entity_id FROM {$table_attribute_decimal} WHERE value > 0 AND attribute_id = %d)", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $price_attribute->id);
// 						$check = 'post__in';
						$query->set( 'meta_query' , array(
							array(
								'key' => '_displayed_price',
								'value' => '',
								'compare' => '=',
							),
						));
					break;
					case 'no_description':
						$sql_query = $wpdb->prepare("SELECT ID as post_id FROM {$wpdb->posts} WHERE post_content = '' AND post_type = %s", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
						$check = 'post__in';
					break;
					case 'no_barcode_variations':
						/***/
						$post_to_get = array();
						$the_query = $wpdb->prepare(
							"
							SELECT post_parent
							FROM {$wpdb->posts}
								INNER JOIN {$wpdb->postmeta} ON (post_id = ID)
							WHERE meta_key = %s
								AND meta_value = ''
								AND post_type = %s
						UNION
							SELECT post_parent
							FROM {$wpdb->posts}
								INNER JOIN {$wpdb->postmeta} ON (post_id = ID)
							WHERE meta_key = %s
								AND meta_value != ''
								AND CHAR_LENGTH( meta_value ) != 13
								AND post_type = %s
						UNION
							SELECT post_parent
							FROM {$wpdb->posts}
							WHERE ID NOT IN (
								SELECT post_id
								FROM {$wpdb->postmeta}
								WHERE meta_key = %s
							) AND post_type = %s"
							, "_barcode", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, "_barcode", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, "_barcode", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION );
						$options_product_without_barcode = $wpdb->get_results( $the_query );
						if ( !empty( $options_product_without_barcode ) ) {
							foreach ( $options_product_without_barcode as $post ) {
								$post_to_get[] = $post->post_parent;
							}
						}
						/**	If there are post that have variations without barcode => add them to the post list to get	*/
						if ( !empty( $post_to_get ) ) {
							$query->query_vars[ 'post__in' ] = $post_to_get;
						}
					break;
					case 'no_barcode_products':
						/**	Check all product that don't have a barcode or having a barcode containing PDCT	*/
						$query->set( 'meta_query' , array(
							'relation' => 'OR',
							array(
								'key' => '_barcode',
								'value' => '',
								'compare' => '=',
							),
							array(
								'key' => '_barcode',
								'value' => 'PDCT',
								'compare' => 'LIKE',
							),
						));
					break;
				}

				if ( !empty( $check ) ) {
					$results = $wpdb->get_results($sql_query);
					$post_id_list = array();
					foreach($results as $item){
						$post_id_list[] = $item->post_id;
					}
					$query->query_vars[$check] = $post_id_list;
				}

				$query->query_vars['post_type'] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
			}
		}
	}
}

if (class_exists("wpshop_entity_filter")) {
	$inst_wpshop_entity_filter = new wpshop_entity_filter();
}

?>
