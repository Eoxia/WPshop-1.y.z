<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
* Ajax request management file
*
* @author Eoxia <dev@eoxia.com>
* @version 1.3.2.3
* @package wpshop
* @subpackage includes
*/

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/*	Products	*/
	/**
	 * Duplicate a product
	 */
	function ajax_duplicate_product() {

		check_ajax_referer( 'wpshop_product_duplication', 'wpshop_ajax_nonce' );

		$current_post_id = isset($_POST['current_post_id']) ? sanitize_key($_POST['current_post_id']) : null;

		$result = wpshop_entities::duplicate_entity_element($current_post_id);

		echo json_encode($result);
		die();
	}
	add_action('wp_ajax_duplicate_product', 'ajax_duplicate_product');

	/**
	 * Delete an attachmant from a product
	 */
	function ajax_delete_product_thumbnail() {
		check_ajax_referer( 'wpshop_delete_product_thumbnail', 'wpshop_ajax_nonce' );

		$bool = false;
		$attachement_id = isset($_POST['attachement_id']) ? (int) $_POST['attachement_id'] : null;

		if ( !empty($attachement_id) ) {
			$deletion_result = wp_delete_attachment($attachement_id, false);
			$bool = !empty($deletion_result);
		}

		echo json_encode(array($bool, $attachement_id));
		die();
	}
	add_action('wp_ajax_delete_product_thumbnail', 'ajax_delete_product_thumbnail');
	/**
	 * Reload attachment container
	 */
	function ajax_reload_attachment_boxes() {
		check_ajax_referer( 'wpshop_reload_product_attachment_part', 'wpshop_ajax_nonce' );

		$bool = false;
		$current_post_id = isset($_POST['current_post_id']) ? (int) $_POST['current_post_id'] : null;
		$attachement_type_list = array('reload_box_document' => 'application/pdf', 'reload_box_picture' => 'image/');
		$part_to_reload = isset($_POST['part_to_reload']) ? sanitize_text_field( $_POST['part_to_reload']) : null;
		$attachement_type = $attachement_type_list[$part_to_reload];

		echo json_encode(array(wpshop_products::product_attachement_by_type($current_post_id, $attachement_type, 'media-upload.php?post_id=' . $current_post_id . '&amp;tab=library&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=566'), $part_to_reload));
		die();
	}
	add_action('wp_ajax_reload_product_attachment', 'ajax_reload_attachment_boxes');

	/**
	 * Save information for product when bulk edit
	 */
	function ajax_product_bulk_edit_save() {
		global $wpdb;
		check_ajax_referer( 'product_bulk_edit_save', 'wpshop_ajax_nonce' );

		$post_ids = ( isset( $_POST[ 'post_ids' ] ) && !empty( $_POST[ 'post_ids' ] ) ) ? (array) $_POST[ 'post_ids' ] : array();
		$post_attributes = ( isset( $_POST[ 'attribute' ] ) && !empty( $_POST[ 'attribute' ] ) ) ? (array) $_POST[ 'attribute' ] : array();

		if ( !empty( $post_ids ) && is_array( $post_ids ) && !empty( $post_attributes ) && is_array( $post_attributes ) ) {
			$attribute_to_save = array();
			foreach ( $post_attributes as $attribute ) {
				$attribute_component = explode('_-val-_', $attribute);
				$attribute_definition = explode('[', $attribute_component[0]);
				$attribute_data_type = substr($attribute_definition[1], 0, -1);
				$attribute_code = substr($attribute_definition[2], 0, -1);

				if ( !empty($attribute_component[1]) ) {
					$attribute_to_save[$attribute_data_type][$attribute_code] = $attribute_component[1];
				}
			}

			foreach ( $post_ids as $post_id ) {
				$query = $wpdb->prepare("SELECT locale FROM " . $wpdb->prefix . "icl_locale_map WHERE code = (SELECT language_code FROM " . $wpdb->prefix . "icl_translations WHERE element_id = %d )", $post_id);
				$lang_wpml = $wpdb->get_var($query);
				$lang = !empty($lang_wpml) ? $lang_wpml : WPSHOP_CURRENT_LOCALE;
				/*	Save the attributes values into wpshop eav database	*/
				wpshop_attributes::saveAttributeForEntity($attribute_to_save, wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $post_id, $lang, 'bulk');

				/*	Update product price looking for shop parameters	*/
				wpshop_products::calculate_price($post_id);

				/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
				$productMetaDatas = get_post_meta($post_id, '_wpshop_product_metadata', true);
				if ( !empty($productMetaDatas) ) {
					$attributes_list = wpshop_attributes::get_attribute_list_for_item(wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT), $post_id);
					if ( !empty($attributes_list)) {
						foreach ($attributes_list as $attribute) {
							$value_key = 'attribute_value_'.$attribute->data_type;
							$productMetaDatas[$attribute->code] = $attribute->$value_key;
						}
					}
				}
				foreach($attribute_to_save as $attributeType => $attributeValues){
					foreach($attributeValues as $attributeCode => $attributeValue){
						if ( $attributeCode == 'product_attribute_set_id' ) {
							/*	Update the attribute set id for the current product	*/
							update_post_meta($post_id, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, $attributeValue);
						}
						$productMetaDatas[$attributeCode] = $attributeValue;
					}
				}
				update_post_meta($post_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);
				$parent_product_metadata = get_post_meta($post_id, '_wpshop_product_metadata', true);


				/* If the product have some variations */
				$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE post_parent = %d AND post_type = %s', $post_id, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION);
				$product_variations = $wpdb->get_results($query);
				if ( !empty($product_variations) ) {
					foreach( $product_variations as $product_variation ) {
						$variation_post_meta = get_post_meta($product_variation->ID, '_wpshop_product_metadata', true);
						$common_attributes = unserialize(WPSHOP_COMMON_ATTRIBUTES_PARENT_VARIATION_PRODUCT);
						if ( !empty($common_attributes) ) {
							if ( !empty($parent_product_metadata) ) {
								foreach( $parent_product_metadata as $key => $value ) {
									if ( in_array($key, $common_attributes) ) {
										$variation_post_meta[$key] = $value;
										update_post_meta($product_variation->ID, '_'.$key, $value);
									}
								}
								update_post_meta($product_variation->ID, '_wpshop_product_metadata', $variation_post_meta);
							}
						}
					}
				}
			}



		}

		die();
	}
	add_action( 'wp_ajax_product_bulk_edit_save', 'ajax_product_bulk_edit_save' );
/*	Products	*/

/*	Variations	*/
	/**
	 * Variation list creation
	 */
	function ajax_add_new_variation_list() {
		check_ajax_referer( 'wpshop_variation_management', 'wpshop_ajax_nonce' );
		global $wpdb;

		$attributes_for_variation = isset($_POST['wpshop_attribute_to_use_for_variation']) ? (array) $_POST['wpshop_attribute_to_use_for_variation'] : null;
		$current_post_id = isset($_POST['current_post_id']) ? sanitize_key($_POST['current_post_id']) : null;

		/** Get the list of values of the attribute to affect to a variation	*/
		$var = array();

		foreach ( $attributes_for_variation as $attribute_code ) {
			$query = $wpdb->prepare("SELECT data_type_to_use FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $attribute_code);
			$var[$attribute_code] = wpshop_attributes::get_affected_value_for_list( $attribute_code, $current_post_id, $wpdb->get_var($query));
		}

		$possible_variations = wpshop_tools::search_all_possibilities( $var );

		wpshop_products::creation_variation_callback( $possible_variations, $current_post_id );

		$output = wpshop_products::display_variation_admin( $current_post_id );

		echo $output;
		die();
	}
	add_action('wp_ajax_add_new_variation_list', 'ajax_add_new_variation_list');

	/**
	 * Variation uniq item creation
	 */
	function ajax_new_single_variation_definition() {
		check_ajax_referer( 'wpshop_variation_management', 'wpshop_ajax_nonce' );
		$output = '';

		$current_post_id = isset($_POST['current_post_id']) ? sanitize_key($_POST['current_post_id']) : null;

		/*	Get the list of values of the attribute to affect to a variation	*/
		$attribute_for_variation = wpshop_attributes::get_variation_available_attribute_display( $current_post_id, 'single' );
		$output = $attribute_for_variation[0];

		/**	Display specific element for variation	*/
		$tpl_component['ADMIN_VARIATION_SPECIFIC_DEFINITION_CONTAINER_CLASS'] = '';
		$tpl_component['VARIATION_IDENTIFIER'] = 'new';
		$tpl_component['VARIATION_DEFINITION'] = wpshop_attributes::get_variation_attribute( array('input_class' => ' new_variation_specific_values', 'field_name' => wpshop_products::current_page_variation_code . '[' . $tpl_component['VARIATION_IDENTIFIER'] . ']','page_code' => wpshop_products::current_page_variation_code, 'field_id' => wpshop_products::current_page_variation_code . '_' . $tpl_component['VARIATION_IDENTIFIER'], 'variation_dif_values' => '') );
		$output .= wpshop_display::display_template_element('wpshop_admin_variation_item_specific_def', $tpl_component, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $current_post_id, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION => $tpl_component['VARIATION_IDENTIFIER']), 'admin');

		$tpl_component = array();
		$tpl_component['ADMIN_VARIATION_SINGLE_CREATION_FORM_CONTENT'] = $output;
		$tpl_component['ADMIN_VARIATION_CREATION_FORM_HEAD_PRODUCT_ID'] = $current_post_id;
		$tpl_component['ADMIN_VARIATION_CREATION_FORM_HEAD_NOUNCE'] = wp_create_nonce("wpshop_variation_management");
		$tpl_component['ADMIN_VARIATION_CREATION_FORM_ACTION'] = 'add_new_single_variation';
		echo wpshop_display::display_template_element('wpshop_admin_new_single_variation_form', $tpl_component, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $current_post_id, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION => ''), 'admin');

		die();
	}
	add_action('wp_ajax_new_single_variation_definition', 'ajax_new_single_variation_definition');

	/*
	 * Combined variation list creation
	 */
	function ajax_new_combined_variation_list_definition() {
		check_ajax_referer( 'wpshop_variation_management', 'wpshop_ajax_nonce' );
		$current_post_id = isset($_POST['current_post_id']) ? sanitize_key($_POST['current_post_id']) : null;
		$output = '';

		$attribute_for_variation = wpshop_attributes::get_variation_available_attribute_display( $current_post_id );
		$output = $attribute_for_variation[0];

		echo $output;
		die();
	}
	add_action('wp_ajax_new_combined_variation_list_definition', 'ajax_new_combined_variation_list_definition');

	/*
	 * Product variaitons parameters
	 */
	function wpshop_ajax_admin_variation_parameters() {
		check_ajax_referer( 'wpshop_variation_management', 'wpshop_ajax_nonce' );

		$current_post_id = isset($_POST['current_post_id']) ? sanitize_key($_POST['current_post_id']) : null;
		$output = '';

		/*	Display variation options	*/
		$options_tpl_component = array();
		$head_wpshop_variation_definition = get_post_meta( $current_post_id, '_wpshop_variation_defining', true );
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_PRIORITY_SINGLE'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['priority'][0]) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['priority'][0]) && ($head_wpshop_variation_definition['options']['priority'][0] == 'single')) ) ? ' checked="checked"' : '';
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_PRIORITY_COMBINED'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['priority'][0]) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['priority'][0]) && ($head_wpshop_variation_definition['options']['priority'][0] == 'combined')) ) ? ' checked="checked"' : '';
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_BEHAVIOUR_ADDITION'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['price_behaviour'][0]) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['price_behaviour'][0]) && ($head_wpshop_variation_definition['options']['price_behaviour'][0] == 'addition')) ) ? ' checked="checked"' : '';
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_BEHAVIOUR_REPLACEMENT'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['price_behaviour'][0]) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['price_behaviour'][0]) && ($head_wpshop_variation_definition['options']['price_behaviour'][0] == 'replacement')) ) ? ' checked="checked"' : '';

		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_PRICE_DISPLAY_TEXT_FROM'] = ( ( empty($head_wpshop_variation_definition['options']) ) || ( (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['price_display']['text_from']) && ($head_wpshop_variation_definition['options']['price_display']['text_from'] == 'on')) ) ) ? ' checked="checked"' : '';
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_PRICE_DISPLAY_LOWER_PRICE'] = ( (empty($head_wpshop_variation_definition['options']) ) || ((!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['price_display']['lower_price']) && ($head_wpshop_variation_definition['options']['price_display']['lower_price'] == 'on')) ) ) ? ' checked="checked"' : '';

		$options_tpl_component['ADMIN_VARIATION_PARAMETERS_FORM_HEAD_PRODUCT_ID'] = $current_post_id;
		$options_tpl_component['ADMIN_VARIATION_PARAMETERS_FORM_HEAD_NOUNCE'] = wp_create_nonce("wpshop_variation_parameters");

		$options_tpl_component['ADMIN_MORE_OPTIONS_FOR_VARIATIONS'] = '';

		$attribute_list_for_variations = wpshop_attributes::get_variation_available_attribute( $current_post_id );

		$default_value_for_attributes = $required_attributes = '';

		$attribute_user_defined = wpshop_attributes::get_attribute_user_defined( array('entity_type_id' => get_post_type($current_post_id)) );
		if ( !empty($attribute_user_defined) ) {
			foreach ( $attribute_user_defined as $attribute_def ) {
				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_LABEL_STATE'] = '';
				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL_EXPLAINATION'] = '';

				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_CODE'] = $attribute_def->code;
				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_NAME'] = $attribute_def->code;
				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL'] = __( $attribute_def->frontend_label, 'wpshop' );
				$tpl_component['ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CONTAINER_CLASS'] = '';

				$tpl_component['ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CHECKBOX_STATE'] = ( (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['required_attributes']) && ( in_array( $attribute_def->code, $head_wpshop_variation_definition['options']['required_attributes']) )) ) ? ' checked="checked"' : '';

				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_ID'] = 'required_' . $attribute_def->code;

				$required_attributes .= str_replace('wpshop_attribute_to_use_for_variation', 'wps_pdt_variations[options][required_attributes]', str_replace('variation_attribute_usable', 'variation_attribute_required', wpshop_display::display_template_element('wpshop_admin_attribute_for_variation_item', $tpl_component, array(), 'admin')));
			}
		}

		if ( !empty($attribute_list_for_variations['available']) ) {
			$head_wpshop_variation_definition = get_post_meta( $current_post_id, '_wpshop_variation_defining', true );
			foreach ( $attribute_list_for_variations['available'] as $attribute_code => $attribute_definition ) {
				/** Default value for attribute	*/
				$tpl_component = array();
				$tpl_component['ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CONTAINER_CLASS'] = ' variation_attribute_container_default_value_' . $attribute_code;

				$attribute_for_default_value = wpshop_attributes::get_attribute_field_definition($attribute_definition['attribute_complete_def'], (is_array($head_wpshop_variation_definition) && isset($head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code]) ? $head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code] : 'none'), array('from' => 'frontend', 'field_custom_name_prefix' => 'empty'));
				switch ( $attribute_for_default_value['type'] ) {
					case 'select':
					case 'multiple-select':
					case 'radio':
					case 'checkbox':
						$attribute_for_default_value['type'] = 'select';
						break;
					default:
						$attribute_for_default_value['type'] = 'text';
						break;
				}

				if ( !empty($attribute_for_default_value['possible_value']) ) {
					$attribute_for_default_value['possible_value']['none'] = __('No default value', 'wpshop');
					foreach( $attribute_for_default_value['possible_value'] as $value_id => $value ){
						if ( !empty($value_id) && ($value_id != 'none') && !in_array($value_id, $attribute_definition['values']) ) {
							unset($attribute_for_default_value['possible_value'][$value_id]);
						}
					}
					ksort($attribute_for_default_value['possible_value']);

					$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_ID'] = $attribute_for_default_value['id'];
					$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_DEFAULT_VALUE_LABEL'] = sprintf( __('Default value for %s', 'wpshop'), $attribute_for_default_value['label'] );
					$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_DEFAULT_VALUE_INPUT'] = wpshop_form::check_input_type($attribute_for_default_value, 'wps_pdt_variations[options][attributes_default_value]');
					$default_value_for_attributes .= wpshop_display::display_template_element('wpshop_admin_attribute_for_variation_item_for_default', $tpl_component, array(), 'admin');
				}

				/** Required attribute for variations	*/
				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_LABEL_STATE'] = '';
				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL_EXPLAINATION'] = '';

				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_CODE'] = $attribute_code;
				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_NAME'] = $attribute_code;
				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL'] = __( $attribute_definition['label'], 'wpshop' );
				$tpl_component['ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CONTAINER_CLASS'] = '';

				$tpl_component['ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CHECKBOX_STATE'] = ( (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['required_attributes']) && ( in_array( $attribute_code, $head_wpshop_variation_definition['options']['required_attributes']) )) ) ? ' checked="checked"' : '';

				$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_ID'] = 'required_' . $attribute_code;
				$required_attributes .= str_replace('wpshop_attribute_to_use_for_variation', 'wps_pdt_variations[options][required_attributes]', str_replace('variation_attribute_usable', 'variation_attribute_required', wpshop_display::display_template_element('wpshop_admin_attribute_for_variation_item', $tpl_component, array(), 'admin')));
			}

			$options_tpl_component['ADMIN_MORE_OPTIONS_FOR_VARIATIONS'] .= !empty($required_attributes) ? wpshop_display::display_template_element('wpshop_admin_variation_options_required_attribute_container', array('ADMIN_VARIATION_OPTIONS_REQUIRED_ATTRIBUTE' => $required_attributes), array(), 'admin') : '';
			$options_tpl_component['ADMIN_MORE_OPTIONS_FOR_VARIATIONS'] .= !empty($default_value_for_attributes) ? wpshop_display::display_template_element('wpshop_admin_variation_options_default_value_container', array('ADMIN_VARIATION_OPTIONS_ATTRIBUTE_DEFAULT_VALUE' => $default_value_for_attributes), array(), 'admin') : '';
		}

		$output .= wpshop_display::display_template_element('wpshop_admin_variation_options_container', $options_tpl_component, array(), 'admin');
		unset($options_tpl_component);


		echo $output;
		die();
	}
	add_action('wp_ajax_admin_variation_parameters', 'wpshop_ajax_admin_variation_parameters');

	/*
	 * Save product variation paramters
	 */
	function wpshop_ajax_admin_variation_parameters_save() {
		check_ajax_referer( 'wpshop_variation_parameters', 'wpshop_ajax_nonce' );

		$current_post_id = isset($_POST['current_post_id']) ? sanitize_key($_POST['current_post_id']) : null;
		$options = !empty( $_POST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION]['options'] ) ? (array)$_POST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION]['options'] : array();

		if ( !empty($options) ) {
			wpshop_products::variation_parameters_save( $post_id, $options );
		}

		die();
	}
	add_action('wp_ajax_admin_variation_parameters_save', 'wpshop_ajax_admin_variation_parameters_save');

	/**
	 * Variation uniq item creation
	 */
	function ajax_add_new_single_variation() {
		check_ajax_referer( 'wpshop_variation_management', 'wpshop_ajax_nonce' );
		$output = '';

		$attributes_for_variation = isset($_POST['variation_attr']) ? (array) $_POST['variation_attr'] : null;
		$wpshop_admin_use_attribute_for_single_variation_checkbox = isset($_POST['wpshop_admin_use_attribute_for_single_variation_checkbox']) ? (array) $_POST['wpshop_admin_use_attribute_for_single_variation_checkbox'] : null;
		$variation_specific_definition = isset($_POST['wps_pdt_variations']['new']['attribute']) ? (array) $_POST['wps_pdt_variations']['new']['attribute'] : null;
		$current_post_id = isset($_POST['wpshop_head_product_id']) ? sanitize_key($_POST['wpshop_head_product_id']) : null;
		$display = isset( $_POST['data'] ) ? (bool) $_POST['data'] : false;

		$attribute_to_use_for_creation = array();
		foreach ( $attributes_for_variation as $attribute_code => $attribute_value) {
			if ( array_key_exists($attribute_code, $wpshop_admin_use_attribute_for_single_variation_checkbox) ) {
				$attribute_to_use_for_creation[0][$attribute_code] = $attributes_for_variation[$attribute_code];
				$attr_def = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
				$variation_specific_definition[$attr_def->data_type][$attribute_code] = $attributes_for_variation[$attribute_code];
			}
		}
		$new_variation_identifier = wpshop_products::creation_variation_callback( $attribute_to_use_for_creation, $current_post_id );

		/*	Save variation specific element	*/
		foreach ( unserialize(WPSHOP_ATTRIBUTE_PRICES) as $price_attribute_code) {
			$head_product_price_attribute_value = wpshop_attributes::get_attribute_value_content($price_attribute_code, $current_post_id, wpshop_products::currentPageCode);
			$price_attr_def = wpshop_attributes::getElement($price_attribute_code, "'valid'", 'code');
			if ( !empty($price_attr_def) && !empty($price_attr_def->data_type) && (empty($variation_specific_definition[$price_attr_def->data_type]) || !array_key_exists($price_attribute_code, $variation_specific_definition[$price_attr_def->data_type]))) {
				$variation_specific_definition[$price_attr_def->data_type][$price_attribute_code] = !empty($head_product_price_attribute_value->value) ? $head_product_price_attribute_value->value : 1;
			}
		}

		wpshop_attributes::saveAttributeForEntity($variation_specific_definition, wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $new_variation_identifier, WPSHOP_CURRENT_LOCALE);
		wpshop_products::calculate_price( $new_variation_identifier );

		if( $display ) {
			$output = json_encode( array( 'ID' => $new_variation_identifier ) );
		} else {
			$output = wpshop_products::display_variation_admin( $current_post_id );
		}

		echo $output;
		die();
	}
	add_action('wp_ajax_add_new_single_variation', 'ajax_add_new_single_variation');

	/**
	 * Delete a variation
	*/
	function ajax_delete_variation() {
		check_ajax_referer( 'wpshop_variation_management', 'wpshop_ajax_nonce' );
		$result = false;
		$list_to_remove = '';

		$current_post_id = isset($_POST['current_post_id']) && is_array($_POST['current_post_id']) ? (array) $_POST['current_post_id'] : null;
		foreach ( $current_post_id as $variation_id) {
			$result = wp_delete_post($variation_id, false);
			if ( $result ) {
				$list_to_remove[] = $variation_id;
			}
		}

		echo json_encode($list_to_remove);
		die();
	}
	add_action('wp_ajax_delete_variation', 'ajax_delete_variation');

	/**
	 * Delete a variation defintion into head product
	*/
	function ajax_wpshop_delete_head_product_variation_def() {
		//check_ajax_referer( 'wpshop_variation_management', 'wpshop_ajax_nonce' );

		$current_post_id = isset($_POST['current_post_id']) ? sanitize_key($_POST['current_post_id']) : null;
		$current_variation_def = get_post_meta($current_post_id, '_wpshop_variation_defining', true);
		unset($current_variation_def['attributes']);
		update_post_meta($current_post_id, '_wpshop_variation_defining', $current_variation_def);
		die();
	}
	add_action('wp_ajax_wpshop_delete_head_product_variation_def', 'ajax_wpshop_delete_head_product_variation_def');
/*	Variations	*/

/*	Orders	*/
	/* Validate the payment transaction number */
	function wpshop_ajax_validate_payment_method() {
		check_ajax_referer( 'wpshop_validate_payment_method', 'wpshop_ajax_nonce' );
		$order_id = ( isset( $_POST[ 'order_id' ] ) && !empty( $_POST[ 'order_id' ] ) ) ? (int) $_POST[ 'order_id' ] : null;
		$payment_method = ( isset( $_POST[ 'payment_method' ] ) && !empty( $_POST[ 'payment_method' ] ) ) ? sanitize_text_field( $_POST[ 'payment_method' ] ) : null;
		$transaction_id = ( isset( $_POST[ 'transaction_id' ] ) && !empty( $_POST[ 'transaction_id' ] ) ) ? (int) $_POST[ 'transaction_id' ] : null;

		if ( !empty($order_id) ) {
			if( !empty($payment_method) && !empty($transaction_id) ) {
				/* Update he payment method */
				$order = get_post_meta($order_id, '_order_postmeta', true);
				$order['payment_method'] = $payment_method;
				update_post_meta($order_id, '_order_postmeta', $order);

				// Update Transaction identifier regarding the payment method
				if ( !empty($transaction_id) ) {
// 					$transaction_key = '';
// 					switch($payment_method) {
// 						case 'check':
// 							$transaction_key = '_order_check_number';
// 						break;
// 					}
// 					if ( !empty($transaction_key) ) update_post_meta($order_id, $transaction_key, $transaction_id);
					wpshop_payment::set_payment_transaction_number($order_id, $transaction_id);
				}
				$result = json_encode(array(true,''));
			}
			else {
				$result = json_encode(array(false,__('Choose a payment method and/or type a transaction number', 'wpshop')));
			}
		}
		else {
			$result = json_encode(array(false,__('Bad order identifier', 'wpshop')));
		}
		echo json_encode($result);
		die();
	}
	add_action( 'wp_ajax_validate_payment_method', 'wpshop_ajax_validate_payment_method' );


	/* Display a dialog box to inform a shipping tracking number */
	function wpshop_ajax_dialog_inform_shipping_number() {
		check_ajax_referer( 'wpshop_dialog_inform_shipping_number', 'wpshop_ajax_nonce' );
		$order_id = ( isset( $_POST[ 'order_id' ] ) && !empty( $_POST[ 'order_id' ] ) ) ? (int)$_POST[ 'order_id' ] : null;

		if ( !empty($order_id) ) {
			$result = (array(true, '<h1>'.__('Tracking number','wpshop').'</h1><p>'.__('Enter a tracking number, or leave blank:','wpshop').'</p><input type="hidden" value="'.$order_id.'" name="oid" /><input type="text" name="trackingNumber" /><br /><br /><p>'.__('Enter a tracking link, or leave blank: (http included)','wpshop').'</p><input type="text" name="trackingLink" /><br /><br /><input type="submit" data-nonce="' . esc_attr( wp_create_nonce( 'wpshop_change_order_state' ) ) . '" class="button-primary sendTrackingNumber" value="'.__('Send','wpshop').'" /> <input type="button" class="button-secondary closeAlert" value="'.__('Cancel','wpshop').'" />'));

		}
		else {
			$result = json_encode(array(false, __('Order reference error', 'wpshop')));
		}
		echo json_encode($result);
		die();
	}
	add_action( 'wp_ajax_dialog_inform_shipping_number', 'wpshop_ajax_dialog_inform_shipping_number' );

	function wpshop_ajax_change_order_state() {
		global $order_status;
		check_ajax_referer( 'wpshop_change_order_state', 'wpshop_ajax_nonce' );

		$order_id = ( isset( $_POST[ 'order_id' ] ) && !empty( $_POST[ 'order_id' ] ) ) ? (int)$_POST[ 'order_id' ] : null;
		$order_state = ( isset( $_POST[ 'order_state' ] ) && !empty( $_POST[ 'order_state' ] ) ) ? sanitize_text_field( $_POST[ 'order_state' ] ) : null;
		$order_shipped_number = ( isset( $_POST[ 'order_shipped_number' ] ) && !empty( $_POST[ 'order_shipped_number' ] ) ) ? sanitize_text_field( $_POST[ 'order_shipped_number' ] ) : null;
		$order_shipped_link = ( isset( $_POST[ 'order_shipped_link' ] ) && !empty( $_POST[ 'order_shipped_link' ] ) ) ? sanitize_text_field( $_POST[ 'order_shipped_link' ] ) : null;

		if ( !empty($order_id) ) {
			/* Update the oder state */
			$order = get_post_meta($order_id, '_order_postmeta', true);
			$order['order_status'] = $order_state;

			if ( $order_state == 'shipped' ) {
				$order['order_shipping_date'] = current_time('mysql', 0);
				$order['order_trackingNumber'] = $order_shipped_number;
				$order['order_trackingLink'] = $order_shipped_link;
				update_post_meta($order_id, '_wpshop_order_shipping_date', $order['order_shipping_date']);
				update_post_meta($order_id, '_order_postmeta', $order);
				wpshop_send_confirmation_shipping_email($order_id);

				$output_payment_box_class = 'wpshop_order_status_shipped';
				$output_payment_box_content = __('Shipped', 'wpshop');

				$output_shipping_box  = '<li><strong>'.__('Order shipping date','wpshop').' :</strong>'.$order['order_shipping_date'].'</li>';
				$output_shipping_box .= '<li><strong>'.__('Tracking number','wpshop').' :</strong> '.$order['order_trackingNumber'].'</li>';
				$output_shipping_box .= '<li><strong>'.__('Tracking link','wpshop').' :</strong> '.$order['order_trackingLink'].'</li>';

				$result = array( true, $order_state, $output_shipping_box, $output_payment_box_class, $output_payment_box_content );
			}
			else {
				wpshop_payment::setOrderPaymentStatus($order_id, $order_state);

				$result = array(true, $order_state, __($order_status[$order_state], 'wpshop'));
			}
			update_post_meta($order_id, '_order_postmeta', $order);
			update_post_meta($order_id, '_wpshop_order_status', $order_state);
		}
		else {
			$result = array(false, __('Incorrect order request', 'wpshop'));
		}

		echo json_encode($result);
		die();
	}
	add_action( 'wp_ajax_change_order_state', 'wpshop_ajax_change_order_state' );


	/* Send a confirmation e-mail to the customer */
	function wpshop_send_confirmation_shipping_email($order_id){
		$wps_message = new wps_message_ctr();
		if ( !empty($order_id) ) {
			$order_info = get_post_meta($order_id, '_order_info', true);
			$order = get_post_meta($order_id, '_order_postmeta', true);
			$email = ( !empty($order_info['billing']['address']['address_user_email']) ? $order_info['billing']['address']['address_user_email'] : '');
			$first_name = (!empty($order_info['billing']['address']['address_first_name']) ? $order_info['billing']['address']['address_first_name'] : '');
			$last_name = ( !empty($order_info['billing']['address']['address_last_name']) ? $order_info['billing']['address']['address_last_name'] : '');

			if( empty($email) && !empty($order['customer_id']) ) {
				$user_data = get_userdata( $order['customer_id'] );
				$email = $user_data->user_email;
			}

			$shipping_mode_option = get_option( 'wps_shipping_mode' );
			$shipping_method = ( !empty($order['order_payment']['shipping_method']) && !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && is_array($shipping_mode_option['modes']) && array_key_exists($order['order_payment']['shipping_method'], $shipping_mode_option['modes'])) ? $shipping_mode_option['modes'][$order['order_payment']['shipping_method']]['name'] : ( (!empty($order_meta['order_payment']['shipping_method']) ) ? $order['order_payment']['shipping_method'] : '' );


			$wps_message->wpshop_prepared_email (
				$email,
				'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE',
				array(
					'order_id' => $order_id,
					'order_key' => ( !empty($order['order_key']) ? $order['order_key'] : '' ),
					'customer_first_name' => $first_name,
					'customer_last_name' => $last_name,
					'order_date' => ( !empty($order['order_date']) ? $order['order_date'] : '' ),
					'order_trackingNumber' => ( !empty($order['order_trackingNumber']) ? $order['order_trackingNumber'] : '' ),
					'order_trackingLink' => ( !empty($order['order_trackingLink']) ? $order['order_trackingLink'] : '' ),
					'order_addresses' => '',
					'order_billing_address' => '',
					'order_shipping_address' => '',
					'order_content' => '',
					'order_shipping_method' => $shipping_method
				)
			);
		}
	}


/*	Attribute value	*/
	/**
	 * Add a new value for attribute from select type
	 *
	 * @return string The html output for the new value
	 */
	function ajax_new_option_for_select_callback() {
		check_ajax_referer( 'wpshop_new_option_for_attribute_creation', 'wpshop_ajax_nonce' );
		global $wpdb;

		$option_id=$option_default_value=$option_value_id=$options_value='';
		$attribute_identifier = isset($_GET['attribute_identifier']) ? (int) $_GET['attribute_identifier'] : '0';
		$option_name = (!empty($_REQUEST['attribute_new_label']) ? sanitize_text_field( $_REQUEST['attribute_new_label'] ) : '');
		$options_value = sanitize_title($option_name);

		/*	Check if given value does not exist before continuing	*/
		$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE (label = %s OR value = %s) AND attribute_id = %d AND status = 'valid'", $option_name, $options_value, $attribute_identifier);
		$existing_values = $wpdb->get_results($query);

		/*	If given value does not exist: display result. If value exist alert a error message	*/
		if( empty($existing_values) ) {
			$tpl_component = array();
			$tpl_component['ADMIN_ATTRIBUTE_VALUES_OPTION_ID'] = $option_id;
			$tpl_component['ADMIN_ATTRIBUTE_VALUES_OPTION_NAME'] = stripslashes($option_name);
			$tpl_component['ADMIN_ATTRIBUTE_VALUES_OPTION_DEFAULT_VALUE'] = $option_default_value;
			$tpl_component['ADMIN_ATTRIBUTE_VALUES_OPTION_VALUE'] = str_replace(".", ",", stripslashes($options_value));
			$tpl_component['ADMIN_ATTRIBUTE_VALUES_OPTION_STATE'] = '';
			$tpl_component['ADMIN_ATTRIBUTE_VALUE_OPTIN_ACTIONS'] = '';
			if ( current_user_can('wpshop_delete_attributes_select_values') && ($option_id >= 0) ) :
				$tpl_component['ADMIN_ATTRIBUTE_VALUE_OPTIN_ACTIONS'] .= wpshop_display::display_template_element('wpshop_admin_attr_option_value_item_deletion', $tpl_component, array('type' => WPSHOP_DBT_ATTRIBUTE, 'id' => $attribute_identifier), 'admin');
			endif;
			$output = wpshop_display::display_template_element('wpshop_admin_attr_option_value_item', $tpl_component, array('type' => WPSHOP_DBT_ATTRIBUTE, 'id' => $attribute_identifier), 'admin');
			unset($tpl_component);

			echo json_encode(array(true, str_replace('optionsUpdate', 'options', $output)));
		}
		else {
			echo json_encode(array(false, __('The value you entered already exist', 'wpshop')));
		}
		die();
	}
	add_action('wp_ajax_new_option_for_select', 'ajax_new_option_for_select_callback');

	/**
	 * Add a new value to an attribute from select type directly from an entity element edition interface
	 */
	function ajax_new_option_for_select_from_product_edition_callback() {
		check_ajax_referer( 'wpshop_new_option_for_attribute_creation', 'wpshop_ajax_nonce' );

		global $wpdb;
		$result = '';

		$attribute_selected_values = isset($_POST['attribute_selected_values']) ? (array)$_POST['attribute_selected_values'] : array();
		$item_in_edition = isset($_POST['item_in_edition']) ? intval(wpshop_tools::varSanitizer($_POST['item_in_edition'])) : '0';
		$attribute_code = isset($_POST['attribute_code']) ? wpshop_tools::varSanitizer($_POST['attribute_code']) : '0';
		$attribute_place_display = isset($_POST['attribute_place_display']) ? wpshop_tools::varSanitizer($_POST['attribute_place_display']) : 'backend';
		$current_page_code = isset($_POST['attribute_page_code']) ? wpshop_tools::varSanitizer($_POST['attribute_page_code']) : wpshop_products::currentPageCode;

		$attribute = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
		$type = $attribute->data_type_to_use;

		$attribute_options_label = isset($_POST['attribute_new_label']) ? wpshop_tools::varSanitizer($_POST['attribute_new_label']) : null;
		$attribute_options_value = sanitize_title($attribute_options_label);

		if ( $type == 'internal' ) {
			/**	Check if the given value does not exist	*/
			$query = $wpdb->prepare("SELECT * FROM " . $wpdb->posts . " WHERE post_title = %s AND post_status = 'publish'", $attribute_options_label);
			$existing_values = $wpdb->get_results($query);

			/**	If the value does not exist, we create it and output, in case it exists alert an error message	*/
			if ( count($existing_values) <= 0 ) {
				$result_status = true;
				/**	Create the new value as an entity into post database	*/
				$new_post = array(
					'post_title' 	=> $attribute_options_label,
					'post_name' 	=> $attribute_options_value,
					'post_status' 	=> 'publish',
					'post_type' 	=> $attribute->default_value
				);
				$new_option_id = wp_insert_post($new_post);
				$input_def['valueToPut'] = 'index';
			}
			else {
				$result_status = false;
				$result = __('This value already exist for this attribute', 'wpshop');
			}
		}
		else {
			/**	Check if the given value does not exist	*/
			$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE (label = %s OR value = %s) AND attribute_id = %d AND status = 'valid'", str_replace(",", ".", $attribute_options_label), $attribute_options_value, $attribute->id);
			$existing_values = $wpdb->get_results($query);

			/**	If the value does not exist, we create it and output, in case it exists alert an error message	*/
			if( count($existing_values) <= 0 ) {
				$result_status = true;
				$position = 1;
				/**	Get the last value position for adding the new at the end	*/
				$query = $wpdb->prepare("SELECT position FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d", $attribute->id);
				$position = $wpdb->get_var($query);

				/**	Add the new value into database	*/
				$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('creation_date' => current_time('mysql', 0), 'status' => 'valid', 'attribute_id' => $attribute->id, 'position' => $position, 'label' => str_replace(",", ".", stripslashes($attribute_options_label)), 'value' => stripslashes($attribute_options_value)));
				$new_option_id = $wpdb->insert_id;
			}
			else {
				$result_status = false;
				$result = __('This value already exist for this attribute', 'wpshop');
			}
		}

		if ($result_status) {
			$tmp_selection_for_output = array();
			foreach ( $attribute_selected_values as $value ) {
				$tmp_selection_for_output[]['value'] = $value;
			}
			$tmp_selection_for_output[]['value'] = $new_option_id;
			foreach ( $tmp_selection_for_output as $tmp_value ) {
				$selection_for_output[] = (object)$tmp_value;
			}
			$attribute_selected_values[] = $new_option_id;
			$input = wpshop_attributes::get_attribute_field_definition( $attribute, $selection_for_output, array('page_code' => $current_page_code, 'from' => $attribute_place_display) );
			$result = $input['output'] . $input['options'];
		}

		echo json_encode(array($result_status, $result, $attribute_code, $new_option_id));
		die();
	}
	add_action('wp_ajax_new_option_for_select_from_product_edition', 'ajax_new_option_for_select_from_product_edition_callback');

	/**
	 * Delete a value for a select list attribute
	 */
	function ajax_delete_option_for_select_callback() {
		check_ajax_referer( 'wpshop_new_option_for_attribute_deletion', 'wpshop_ajax_nonce' );

		$attribute_value_id = isset($_POST['attribute_value_id']) ? wpshop_tools::varSanitizer($_POST['attribute_value_id']) : '0';

		$result_status = false;
		$result = __('An error occured while deleting selected value', 'wpshop');
		if (!empty($attribute_value_id)) :
		$action_result = wpshop_database::update(array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), $attribute_value_id, WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS);
		if ($action_result == 'done') :
		$result_status = true;
		$result = "#att_option_div_container_" . $attribute_value_id;
		endif;
		endif;

		echo json_encode(array($result_status, $result));
		die();
	}
	add_action('wp_ajax_delete_option_for_select', 'ajax_delete_option_for_select_callback');

/*	Attributes	*/
	/**
	 * Display the field for the selected attribute type
	 */
	function ajax_attribute_output_type_callback() {
		check_ajax_referer( 'wpshop_attribute_output_type_selection', 'wpshop_ajax_nonce' );

		$data_type_to_use = isset($_POST['data_type_to_use']) ? str_replace('_data', '', wpshop_tools::varSanitizer($_POST['data_type_to_use'], '')) : 'custom';
		$current_type = isset($_POST['current_type']) ? wpshop_tools::varSanitizer($_POST['current_type']) : 'short_text';
		$elementIdentifier = isset($_POST['elementIdentifier']) ? intval( wpshop_tools::varSanitizer($_POST['elementIdentifier'])) : null;
		$the_input = __('An error occured while getting field type', 'wpshop');
		$input_def = array();
		$input_def['name'] = 'default_value';
		$input_def['id'] = 'wpshop_attributes_edition_table_field_id_default_value';
		$input_label = __('Default value', 'wpshop');

		switch($current_type){
			case 'short_text':
			case 'float_field':
				$input_def['type'] = 'text';
				$input_def['value'] = '';
				$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
				break;
			case 'select':
			case 'multiple-select':
			case 'radio':
			case 'checkbox':
				$input_label=__('Options list for attribute', 'wpshop');
				$the_input = wpshop_attributes::get_select_options_list($elementIdentifier, $data_type_to_use);
				break;
			case 'date_field':
				$input_label=__('Date field configuration', 'wpshop');

				$the_input = wpshop_attributes::attribute_type_date_config( array() );
				break;
			case 'textarea':
				$input_def['type'] = 'textarea';
				$input_def['value'] = '';
				$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
				break;
		}

		echo json_encode(array($the_input, $input_label));
		die();
	}
	add_action('wp_ajax_attribute_output_type', 'ajax_attribute_output_type_callback');

	/**
	 * Get the attribute set list when creating a new attribute for direct affectation
	 */
	function ajax_attribute_entity_set_selection_callback() {
		check_ajax_referer( 'wpshop_attribute_entity_set_selection', 'wpshop_ajax_nonce' );

		$current_entity_id = isset($_POST['current_entity_id']) ? intval(wpshop_tools::varSanitizer($_POST['current_entity_id'])) : null;

		$the_input = wpshop_attributes_set::get_attribute_set_complete_list($current_entity_id,  wpshop_attributes::getDbTable(), wpshop_attributes::currentPageCode);

		echo json_encode($the_input);
		die();
	}
	add_action('wp_ajax_attribute_entity_set_selection', 'ajax_attribute_entity_set_selection_callback');
	/**
	 * Get the attribute set list when creating a new attribute for direct affectation
	 */
	function ajax_attribute_set_entity_selection_callback() {
		check_ajax_referer( 'wpshop_attribute_set_entity_selection', 'wpshop_ajax_nonce' );

		$current_entity_id = isset($_POST['current_entity_id']) ? intval(wpshop_tools::varSanitizer($_POST['current_entity_id'])) : null;

		$the_input = wpshop_attributes_set::get_attribute_set_complete_list($current_entity_id,  wpshop_attributes_set::getDbTable(), wpshop_attributes::currentPageCode, false);

		echo json_encode($the_input);
		die();
	}
	add_action('wp_ajax_attribute_set_entity_selection', 'ajax_attribute_set_entity_selection_callback');

	/**
	 * Dialog box allowing to change attribute data type from custom to internal
	 */
	function ajax_attribute_select_data_type_callback() {
		check_ajax_referer( 'wpshop_attribute_change_select_data_type', 'wpshop_ajax_nonce' );
		$result = '';

		$current_attribute = isset($_POST['current_attribute']) ? intval(wpshop_tools::varSanitizer($_POST['current_attribute'])) : null;
		$attribute = wpshop_attributes::getElement($current_attribute);

		$types_toggled = unserialize(WPSHOP_ATTR_SELECT_TYPE_TOGGLED);
		$result .= '<p class="wpshop_change_select_data_type_change wpshop_change_select_data_type_change_current_attribute" >' . sprintf(__('Selected attribute %s', 'wpshop'), $attribute->frontend_label) . '</p>';
		$result .= '<p class="wpshop_change_select_data_type_change wpshop_change_select_data_type_change_types" >' . sprintf(__('Actual data type is %s. After current operation: %s', 'wpshop'), __($attribute->data_type_to_use.'_data', 'wpshop'), __($types_toggled[$attribute->data_type_to_use], 'wpshop')) . '</p>';

		if ( $attribute->data_type_to_use == 'custom' ) {
			$sub_output='';
			$wp_types = unserialize(WPSHOP_INTERNAL_TYPES);
			unset($input_def);$input_def=array();
			$input_def['label'] = __('Type of data for list', 'wpshop');
			$input_def['type'] = 'select';
			$input_def['name'] = 'internal_data';
			$input_def['valueToPut'] = 'index';
			$input_def['possible_value'] = $wp_types;
			$input_def['value'] = !empty($attribute_select_options[0]->default_value) ? $attribute_select_options[0]->default_value : null;
			$combo_wp_type = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
			$result .= __('Choose the data type to use for this attribute', 'wpshop') . '<a href="#" title="'.sprintf(__('If the type you want to use is not in the list below. You have to create it by using %s menu', 'wpshop'), __('Entities', 'wpshop')).'" class="wpshop_infobulle_marker">?</a><div class="wpshop_cls wpshop_attribute_select_data_type_internal_list">'.$combo_wp_type.'</div>';
			$result .= '<input type="hidden" value="no" name="delete_items_of_entity" id="delete_items_of_entity" /><input type="hidden" value="no" name="delete_entity" id="delete_entity" />';
		}
		else {
			$result .= '<input type="hidden" value="' . $attribute->default_value . '" name="internal_data" id="internal_data" />';

			unset($input_def);
			$input_def['label'] = __('Delete existing items when transfer is complete', 'wpshop');
			$input_def['name'] = 'delete_items_of_entity';
			$input_def['option'] = ' class="wpshop_attribute_change_select_data_type_deletion_input wpshop_attribute_change_select_data_type_deletion_input_item" ';
			$input_def['type'] = 'checkbox';
			$input_def['possible_value'] = 'yes';
			$result .= '<p class="cursor" >' . wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE) . ' <label for="' . $input_def['name'] . '">' . $input_def['label'] . '</label></p>';

			$input_def['label'] = __('Delete entity type when transfer is complete', 'wpshop');
			$input_def['name'] = 'delete_entity';
			$input_def['option'] = ' class="wpshop_attribute_change_select_data_type_deletion_input wpshop_attribute_change_select_data_type_deletion_input_entity" ';
			$result .= '<p>' . wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE) . ' <label for="' . $input_def['name'] . '">' . $input_def['label'] . '</label></p>';

			$result .= '<div class="wpshop_attribute_change_data_type_alert wpshopHide" >' . __('Be careful by checking boxes above, you will destroy element. This operation could not be reversed later', 'wpshop') . '</div>';
		}

		$result .= '<input type="hidden" value="' . str_replace('_data', '', $types_toggled[$attribute->data_type_to_use]) . '" name="wpshop_attribute_change_data_type_new_type" id="wpshop_attribute_change_data_type_new_type" />';

		echo json_encode($result);
		die();
	}
	add_action('wp_ajax_attribute_select_data_type', 'ajax_attribute_select_data_type_callback');
	/**
	 * Change datatype for attribute of select list type.
	 */
	function ajax_attribute_select_data_type_change_callback() {
		global $wpdb;
		check_ajax_referer( 'wpshop_attribute_change_select_data_type_change', 'wpshop_ajax_nonce' );
		$result = '';

		$current_attribute = isset($_POST['attribute_id']) ? intval(wpshop_tools::varSanitizer($_POST['attribute_id'])) : null;
		$data_type = isset($_POST['data_type']) ? wpshop_tools::varSanitizer($_POST['data_type']) : null;
		$internal_data_type = isset($_POST['internal_data']) ? wpshop_tools::varSanitizer($_POST['internal_data']) : null;
		$delete_items_of_entity = isset($_POST['delete_items_of_entity']) ? wpshop_tools::varSanitizer($_POST['delete_items_of_entity']) : false;
		$delete_entity = isset($_POST['delete_entity']) ? wpshop_tools::varSanitizer($_POST['delete_entity']) : false;


		if ( $data_type == 'internal' ) {
			$options_list = wpshop_attributes::get_select_option_list_($current_attribute);
			if(!empty($options_list)){
				foreach($options_list as $option){
					/*	Creat the new entity	*/
					$new_post = array(
							'post_title' 	=> $option->name,
							'post_name' 	=> $option->value,
							'post_status' 	=> 'publish',
							'post_type' 	=> $internal_data_type
					);
					$new_option_id = wp_insert_post($new_post);
					if(!empty($new_option_id)){
						$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status'=>'deleted'), array('attribute_id'=>$current_attribute));
					}
				}
			}
		}
		else {
			$post_list = query_posts(array('post_type' => $internal_data_type));
			if (!empty($post_list)) {
				$p=1;
				$error = false;
				foreach ($post_list as $post) {
					$last_insert = $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status'=>'valid', 'creation_date'=>current_time('mysql',0), 'attribute_id'=>$current_attribute, 'position'=>$p, 'value'=>$post->post_name, 'label'=>$post->post_title));
					if(is_int($last_insert) && $delete_items_of_entity){
						wp_delete_post($post->ID, true);
					}
					else{
						$error = true;
					}
					$p++;
				}
				if(!$error && $delete_entity){
					$post = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type=%s AND post_name=%s", WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, $internal_data_type);
					wp_delete_post($wpdb->get_var($post), true);
				}
			}
			wp_reset_query();
		}

		/*	Update attribute datatype	*/
		$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('data_type_to_use' => $data_type, 'default_value' => $internal_data_type), array('id' => $current_attribute));

		$result = wpshop_attributes::get_select_options_list($current_attribute, $editedItem->$data_type);

		echo json_encode($result);
		die();
	}
	add_action('wp_ajax_attribute_select_data_type_change', 'ajax_attribute_select_data_type_change_callback');
	/**
	 * Duplicate an existing attribute from an entity to another
	 */
	function ajax_wpshop_duplicate_attribute_callback (){
		check_ajax_referer( 'wpshop_duplicate_attribute', 'wpshop_ajax_nonce' );
		global $wpdb;

		$result = '';

		$current_attribute = isset($_POST['attribute_id']) ? intval(wpshop_tools::varSanitizer($_POST['attribute_id'])) : null;
		$new_entity = isset($_POST['entity']) ? intval(wpshop_tools::varSanitizer($_POST['entity'])) : null;

		/*	Get attribute definition	*/
		$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE id = %d", $current_attribute);
		$attribute_def = $wpdb->get_row($query, ARRAY_A);
		/*	Change information from old attribute to the new */
		$attribute_def['id'] = '';
		$attribute_def['creation_date'] = current_time('mysql', 0);
		$attribute_def['entity_id'] = $new_entity;
		$attribute_def['code'] = $attribute_def['code'] . '-' . $new_entity;

		/*	Check if the attribute to duplicate does not exist for the selected entity	*/
		$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $attribute_def['code']);
		$check_existing_attribute = $wpdb->get_var($query);
		if ( empty($check_existing_attribute) ) {
			/*	Save new attribut for the selected entity	*/
			$new_attribute = $wpdb->insert(WPSHOP_DBT_ATTRIBUTE, $attribute_def);
			$new_attribute_id = $wpdb->insert_id;

			if ($new_attribute) {
				if ( in_array($attribute_def['backend_input'], array('select', 'multiple-select', 'radio', 'checkbox')) && ($attribute_def['data_type_to_use'] == 'custom') ) {
					$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d", $current_attribute);
					$attribute_options_list = $wpdb->get_results($query, ARRAY_A);
					foreach ( $attribute_options_list as $option ) {
						$option['id'] = '';
						$option['creation_date'] = current_time('mysql', 0);
						$option['attribute_id'] = $new_attribute_id;
						$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, $option);
					}
				}
				$result = true;
				$result_output = '<p class="wpshop_duplicate_attribute_result" ><a href="' . admin_url('admin.php?page=' . WPSHOP_URL_SLUG_ATTRIBUTE_LISTING . '&action=edit&id=' . $new_attribute_id) . '" >' . __('Edit the new attribute', 'wpshop') . '</a></p>';
			}
			else {
				$result = false;
				$result_output = __('An error occured while duplicating attribute', 'wpshop');
			}
		}
		else {
			$result = false;
			$result_output = __('This attribute has already been duplicate to this entity', 'wpshop');
		}


		echo json_encode(array($result, $result_output));
		die();
	}
	add_action('wp_ajax_wpshop_duplicate_attribute', 'ajax_wpshop_duplicate_attribute_callback');


/** Attributes unit */
	/**
	 * Load comboBox of unit or group of unit
	 */
	function wpshop_ajax_load_attribute_unit_list(){
		check_ajax_referer( 'wpshop_load_attribute_unit_list', 'wpshop_ajax_nonce' );
		$response = '';

		$current_group = ( isset( $_POST[ 'current_group' ] ) && !empty( $_POST[ 'current_group' ] ) ) ? sanitize_text_field( $_POST[ 'current_group' ] ) : null;
		$selected_list = ( isset( $_POST[ 'selected_list' ] ) && !empty( $_POST[ 'selected_list' ] ) ) ? sanitize_text_field( $_POST[ 'selected_list' ] ) : null;

		$group = wpshop_tools::varSanitizer($current_group);
		$selected_list = wpshop_tools::varSanitizer($selected_list);

		if ( !empty($group) && !empty($selected_list)) {
			/* Test if we want display the group unit list OR the unit list */
			if ( $selected_list == 'group unit' ) {
				$list = wpshop_attributes_unit::get_unit_group();
			}
			else {
				$list = wpshop_attributes_unit::get_unit_list_for_group($group);
			}

			foreach( $list as $unit ) {
				$response .= '<option value="' . $unit->id . '" '. ( ($current_group == $unit->id && $selected_list == 'group unit') ? 'selected="selected"' : '' ).'>' . $unit->name . '</option>';
			}
			$result = array(true, $response);
		}
		else {
			$result = array(false, __('Incorrect order request', 'wpshop'));
		}

		echo json_encode($result);
		die();
	}
	add_action('wp_ajax_load_attribute_unit_list', 'wpshop_ajax_load_attribute_unit_list');


/**	Tools page	*/
	function wpshop_ajax_db_check_tool() {
		check_ajax_referer( 'wpshop_ajax_db_check_tool', '_wpnonce' );

		global $wpdb, $wpshop_db_table_operation_list, $wpshop_db_table, $wpshop_update_way;
		$current_db_version = get_option('wpshop_db_options', 0);

		/*	Display a list of operation made for the different version	*/
		$plugin_db_modification_content = '';
		$error_nb = 0; $error_list = array();
		$warning_nb = 0; $warning_list = array();
		foreach ($wpshop_db_table_operation_list as $plugin_db_version => $plugin_db_modification) {
			$plugin_db_modification_content .= '
<div class="tools_db_modif_list_version_number" id="wpshop_plugin_v_' . $plugin_db_version . '" >
	' . __('Version', 'wpshop') . '&nbsp;' . $plugin_db_version . '
</div>
<div class="tools_db_modif_list_version_details" >
	<ul>';
			foreach($plugin_db_modification as $modif_name => $modif_list){
				switch($modif_name){
					case 'FIELD_ADD':{
						foreach($modif_list as $table_name => $field_list){
							$sub_modif = '  ';
							foreach($field_list as $column_name){
								$query = $wpdb->prepare("SHOW COLUMNS FROM " .$table_name . " WHERE Field = %s", $column_name);
								$columns = $wpdb->get_row($query);
								$sub_modif .= $column_name;
								if( !empty($columns->Field) && ($columns->Field == $column_name) ){
									$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Field has been created', 'wpshop') . '" title="' . __('Field has been created', 'wpshop') . '" class="db_added_field_check" />';
								}
								else{
									$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Field does not exist', 'wpshop') . '" title="' . __('Field does not exist', 'wpshop') . '" class="db_added_field_check" />';
									$error_nb++;
									if ( !empty($error_list[$plugin_db_version]) ) {
										$error_list[$plugin_db_version] += 1;
									}
									else {
										$error_list[$plugin_db_version] = 1;
									}
								}
								$sub_modif .= ' / ';
							}
							$plugin_db_modification_content .= '<li class="added_field" >' . sprintf(__('Added field list for %s', 'wpshop'), $table_name) . '&nbsp;:&nbsp;' .  substr($sub_modif, 0, -2) . '</li>';
						}
					}break;
					case 'FIELD_DROP':{
						foreach($modif_list as $table_name => $field_list){
							$sub_modif = '  ';
							foreach($field_list as $column_name){
								$query = $wpdb->prepare("SHOW COLUMNS FROM " .$table_name . " WHERE Field = %s", $column_name);
								$columns = $wpdb->get_row($query);
								$sub_modif .= $column_name;
								if(empty($columns) || ($columns->Field != $column_name)){
									$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Field has been deleted', 'wpshop') . '" title="' . __('Field has been deleted', 'wpshop') . '" class="db_deleted_field_check" />';
								}
								else{
									$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Field exists', 'wpshop') . '" title="' . __('Field exists', 'wpshop') . '" class="db_deleted_field_check" />';
									$error_nb++;
									$error_list[$plugin_db_version] += 1;
								}
								$sub_modif .= ' / ';
							}
							$plugin_db_modification_content .= '<li class="deleted_field" >' . sprintf(__('Fields list deleted for the %s table', 'wpshop'), $table_name) . '&nbsp;:&nbsp;' .  substr($sub_modif, 0, -2) . '</li>';
						}
					}break;
					case 'FIELD_CHANGE':{
						foreach($modif_list as $table_name => $field_list){
							$sub_modif = '  ';
							foreach($field_list as $field_infos){
								$query = $wpdb->prepare("SHOW COLUMNS FROM " .$table_name . " WHERE Field = %s", $field_infos['field']);
								$columns = $wpdb->get_row($query);
								$what_is_changed = '';
								if(isset($field_infos['type'])){
									$what_is_changed = __('field type', 'wpshop');
									$changed_key = 'type';
									if($columns->Type == $field_infos['type']){
										$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Field has been created', 'wpshop') . '" title="' . __('Field has been created', 'wpshop') . '" class="db_added_field_check" />';
									}
									else{
										$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Field does not exist', 'wpshop') . '" title="' . __('Field does not exist', 'wpshop') . '" class="db_added_field_check" />';
										$error_nb++;
										$error_list[$plugin_db_version] += 1;
									}
									$sub_modif .= sprintf(__('Change %s for field %s to %s', 'wpshop'), $what_is_changed, $field_infos['field'], $field_infos[$changed_key]);
								}
								if(isset($field_infos['original_name'])){
									$what_is_changed = __('field name', 'wpshop');
									$changed_key = 'original_name';
									if($columns->Field == $field_infos['field']){
										$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Field has been created', 'wpshop') . '" title="' . __('Field has been created', 'wpshop') . '" class="db_added_field_check" />';
									}
									else{
										$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Field does not exist', 'wpshop') . '" title="' . __('Field does not exist', 'wpshop') . '" class="db_added_field_check" />';
										$error_nb++;
										$error_list[$plugin_db_version] += 1;
									}
									$sub_modif .= sprintf(__('Change %s for field %s to %s', 'wpshop'), $what_is_changed, $field_infos[$changed_key], $field_infos['field']);
								}
								$sub_modif .= ' / ';
							}
							$sub_modif = substr($sub_modif, 0, -2);
							$plugin_db_modification_content .= '<li class="changed_field" >' . sprintf(__('Updated field list for %s', 'wpshop'), $table_name) . '&nbsp;:&nbsp;' . $sub_modif . '</li>';
						}
					}break;

					case 'DROP_INDEX':{
						foreach($modif_list as $table_name => $field_list){
							$sub_modif = '   ';
							foreach($field_list as $column_name){
								$query = $wpdb->prepare("SHOW INDEX FROM " .$table_name . " WHERE Column_name = %s", $column_name);
								$columns = $wpdb->get_row($query);
								$sub_modif .= $column_name;
								if((empty($columns)) || ($columns->Column_name != $column_name)){
									$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Index has been deleted', 'wpshop') . '" title="' . __('Index has been deleted', 'wpshop') . '" class="db_deleted_index_check" />';
								}
								else{
									$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Index does not exists', 'wpshop') . '" title="' . __('Index does not exists', 'wpshop') . '" class="db_deleted_index_check" />';
									$error_nb++;
									$error_list[$plugin_db_version] += 1;
								}
								$sub_modif .= ' / ';
							}
							$plugin_db_modification_content .= '<li class="deleted_index" >' . sprintf(__('Deleted indexes for %s table', 'wpshop'), $table_name) . '&nbsp;:&nbsp;' .  substr($sub_modif, 0, -3) . '</li>';
						}
					}break;
					case 'ADD_INDEX':{
						foreach($modif_list as $table_name => $field_list){
							$sub_modif = '   ';
							foreach($field_list as $column_name){
								$query = $wpdb->prepare("SHOW INDEX FROM " . $table_name . " WHERE Column_name = %s OR Key_name = %s", $column_name, $column_name);
								$columns = $wpdb->get_row($query);
								$sub_modif .= $column_name;
								if(($columns->Column_name == $column_name) || ($columns->Key_name == $column_name)){
									$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Index has been created', 'wpshop') . '" title="' . __('Index has been created', 'wpshop') . '" class="db_added_index_check" />';
								}
								else{
									$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Index does not exist', 'wpshop') . '" title="' . __('Index does not exist', 'wpshop') . '" class="db_added_index_check" />';
									$error_nb++;
									$error_list[$plugin_db_version] += 1;
								}
								$sub_modif .= ' / ';
							}
							$plugin_db_modification_content .= '<li class="added_index" >' . sprintf(__('Added indexes for %s table', 'wpshop'), $table_name) . '&nbsp;:&nbsp;' .  substr($sub_modif, 0, -3) . '</li>';
						}
					}break;

					case 'ADD_TABLE':{
						$sub_modif = '  ';
						foreach($modif_list as $table_name){
							$sub_modif .= $table_name;
							$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table_name);
							$table_exists = $wpdb->query($query);
							if($table_exists == 1){
								$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Table has been created', 'wpshop') . '" title="' . __('Table has been created', 'wpshop') . '" class="db_table_check" />';
							}
							else{
								$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Table has not been created', 'wpshop') . '" title="' . __('Table has not been created', 'wpshop') . '" class="db_table_check" />';
								$error_nb++;
								if ( !empty($error_list[$plugin_db_version]) ) {
									$error_list[$plugin_db_version] += 1;
								}
								else {
									$error_list[$plugin_db_version] = 1;
								}
							}
							$sub_modif .= ' / ';
						}
						$plugin_db_modification_content .= '<li class="added_table" >' . __('Added table list', 'wpshop') . '&nbsp;:&nbsp;' . substr($sub_modif, 0, -2);
					}break;
					case 'TABLE_RENAME':{
						$sub_modif = '  ';
						foreach($modif_list as $table){
							$sub_modif .= sprintf(__('%s has been renammed %', 'wpshop'), $table['old_name'], $table['name']);
							$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table['name']);
							$table_exists = $wpdb->query($query);
							$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table['old_name']);
							$old_table_exists = $wpdb->query($query);
							if(($table_exists == 1) && ($old_table_exists == 1)){
								$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Both database table are still present', 'wpshop') . '" title="' . __('Both database table are still present', 'wpshop') . '" class="db_rename_table_check" />';
								$error_nb++;
								if ( !empty($error_list[$plugin_db_version]) ) {
									$error_list[$plugin_db_version] += 1;
								}
								else {
									$error_list[$plugin_db_version] = 1;
								}
							}
							elseif($table_exists == 1){
								$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Table has been renamed', 'wpshop') . '" title="' . __('Table has been renamed', 'wpshop') . '" class="db_rename_table_check" />';
							}
							else{
								$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Table has not been renamed', 'wpshop') . '" title="' . __('Table has not been renamed', 'wpshop') . '" class="db_rename_table_check" />';
								$error_nb++;
								if ( !empty($error_list[$plugin_db_version]) ) {
									$error_list[$plugin_db_version] += 1;
								}
								else {
									$error_list[$plugin_db_version] = 1;
								}
							}
							$sub_modif .= ' / ';
						}
						$plugin_db_modification_content .= '<li class="renamed_table" >' . __('Renamed table list', 'wpshop') . '&nbsp;:&nbsp;' . substr($sub_modif, 0, -2);
					}break;
					case 'TABLE_RENAME_FOR_DELETION':{
						$sub_modif = '  ';
						foreach($modif_list as $table){
							$sub_modif .= sprintf(__('%s has been renammed %', 'wpshop'), $table['old_name'], $table['name']);
							$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table['name']);
							$table_delete_exists = $wpdb->query($query);
							$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table['old_name']);
							$old_table_exists = $wpdb->query($query);
							if(($table_delete_exists == 1) || ($old_table_exists == 1)){
								if($old_table_exists == 1){
									$deleted_table_result = '<img src="' . admin_url('images/no.png') . '" alt="' . __('Table has not been renamed', 'wpshop') . '" title="' . __('Table has not been renamed', 'wpshop') . '" class="db_deleted_table_check" />';
									$error_nb++;
									if ( !empty($error_list[$plugin_db_version]) ) {
										$error_list[$plugin_db_version] += 1;
									}
									else {
										$error_list[$plugin_db_version] = 1;
									}
								}
								else{
									$deleted_table_result = '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'warning_vs.gif" alt="' . __('Table has not been deleted', 'wpshop') . '" title="' . __('Table has not been renamed', 'wpshop') . '" class="db_deleted_table_check" />';
									$warning_nb++;
									if ( !empty($warning_list[$plugin_db_version]) ) {
										$warning_list[$plugin_db_version] += 1;
									}
									else {
										$warning_list[$plugin_db_version] = 1;
									}
								}
								$sub_modif .= $deleted_table_result;
							}
							else{
								$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Table has been deleted', 'wpshop') . '" title="' . __('Table has been deleted', 'wpshop') . '" class="db_deleted_table_check" />';
							}
							$sub_modif .= ' / ';
						}
						$plugin_db_modification_content .= '<li class="renamed_table" >' . __('Renammed table for deletion', 'wpshop') . '&nbsp;:&nbsp;' . substr($sub_modif, 0, -2);
					}break;
				}
			}
			$plugin_db_modification_content .= '
	</ul>
</div>';
		}

		$db_table_field_error = '';
		foreach($wpshop_db_table as $table_name => $table_definition){
			if(!empty($table_definition)){
				$table_line = explode("
", $table_definition);

				$sub_db_table_field_error = '  ';
				foreach($table_line as $table_definition_line){
					$def_line = trim($table_definition_line);
					if(substr($def_line, 0, 1) == "`"){
						$line_element = explode(" ", $def_line);
						$field_name = str_replace("`", "", $line_element[0]);
						$query = $wpdb->prepare("SHOW COLUMNS FROM " .$table_name . " WHERE Field = %s", $field_name);
						$columns = $wpdb->get_row($query);
						if ( !empty($columns->Field) && ($columns->Field != $field_name)) {
							$sub_db_table_field_error .= $field_name . ', '/*  . ' : <img src="' . admin_url('images/no.png') . '" alt="' . __('Field does not exist', 'wpshop') . '" title="' . __('Field does not exist', 'wpshop') . '" class="db_added_field_check" />' */;
							$error_nb++;
						}
					}
				}
				$sub_db_table_field_error = trim(substr($sub_db_table_field_error, 0, -2));
				if(!empty($sub_db_table_field_error)){
					$db_table_field_error .= sprintf(__('Following fields of %s don\'t exists: %s', 'wpshop'), '<span class="bold" >' . $table_name . '</span>', $sub_db_table_field_error) . '<br/>';
				}
			}
		}
		if(!empty($db_table_field_error)){
			$db_table_field_error = '<hr class="clear" />' . $db_table_field_error . '<hr/>';
		}

		/*	Start display	*/
		$plugin_install_error = '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Wpshop install is ok', 'wpshop') . '" title="' . __('Wpshop install is ok', 'wpshop') . '" />&nbsp;' . __('There is no error in your wpshop installation. Please find details below', 'wpshop') . '<hr/>';
		if($error_nb > 0){
			$plugin_install_error = '<img src="' . admin_url('images/no.png') . '" alt="' . __('Error in wpshop install', 'wpshop') . '" title="' . __('Error in wpshop install', 'wpshop') . '" />&nbsp;' . __('There are ne or more errors into your wpshop installation. Please find details below', 'wpshop') . '<br/>
							<ul>';
			foreach($error_list as $version => $element_nb){
				$plugin_install_error .= '<li>' . sprintf(__('There are %d errors into %s version', 'wpshop'), $element_nb, '<a href="#wpshop_plugin_v_' . $version . '" >' . $version . '</a>') . ' - <button id="wpshop_repair_db_version_' . $version  . '" data-nonce="' . wp_create_nonce( 'wpshop_ajax_db_repair_tool ' ) .'" class="wpshop_repair_db_version" >' . __('Repair', 'wpshop') . '</button></li>';
			}
			$plugin_install_error .= '
							</ul>';
		}
		if($warning_nb > 0){
			$plugin_install_error .= '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'warning_vs.gif" alt="' . __('Warning in wpshop install', 'wpshop') . '" title="' . __('Warning in wpshop install', 'wpshop') . '" />&nbsp;' . __('Some element need your attention. They have no consequences on wpshop operation. Please find details below', 'wpshop') . '<br/>';
			foreach($warning_list as $version => $element_nb){
				$plugin_install_error .= '&nbsp;&nbsp;' . sprintf(__('There are %d warning into %s version', 'wpshop'), $element_nb, '<a href="#wpshop_plugin_v_' . $version . '" >' . $version . '</a>') . ' - ';
			}
			$plugin_install_error = substr($plugin_install_error, 0, -3) . '<hr/>';
		}

		$max_number = 0;
		foreach($wpshop_update_way as $number => $operation){
			if($number > $max_number){
				$max_number = $number;
			}
		}
		echo $plugin_install_error . sprintf(__('Theoretical version of the database : %d - Real version : %d', 'wpshop'), $max_number, $current_db_version['db_version']) . $db_table_field_error . $plugin_db_modification_content;
		die();
	}
	add_action('wp_ajax_wpshop_tool_db_check', 'wpshop_ajax_db_check_tool');

	function wpshop_tool_default_datas_check() {
		check_ajax_referer( 'wpshop_tool_default_datas_check' );

		$output_ok = $output_error = '';

		/**	Get defined default datas type	*/
		$default_custom_post_type = unserialize( WPSHOP_DEFAULT_CUSTOM_TYPES );

		/**	Read the default data saved to check	*/
		if ( !empty($default_custom_post_type) ) {
			foreach ( $default_custom_post_type as $type ) {
				$has_error = false;
				$file_uri = WPSHOP_TEMPLATES_DIR . 'default_datas/' . $type . '.csv';
				if ( is_file( $file_uri ) ) {
					unset($tpl_component);
					$tpl_component = array();
					$tpl_component['CUSTOM_POST_TYPE_NAME'] = 'wpshop_cpt_' . $type;

					/**	Launch check on custom post type	*/
					$check_cpt = wpshop_entities::check_default_custom_post_type( $type, $tpl_component );
					$has_error = $check_cpt[0];
					$tpl_component['TOOLS_CUSTOM_POST_TYPE_CONTAINER'] = $check_cpt[1];
					$tpl_component = array_merge( $tpl_component, $check_cpt[2] );

					if ( $has_error ) {
						$output_error .= wpshop_display::display_template_element('wpshop_admin_tools_default_datas_check_main_element', $tpl_component, array(), 'admin');
					}
					else {
						$output_ok .= wpshop_display::display_template_element('wpshop_admin_tools_default_datas_check_main_element', $tpl_component, array(), 'admin');
					}
				}
			}
		}

		echo wpshop_display::display_template_element('wpshop_admin_tools_default_datas_check_main', array('TOOLS_CUSTOM_POST_TYPE_LIST' => $output_error . $output_ok), array(), 'admin');
		die();
	}
	add_action('wp_ajax_wpshop_tool_default_datas_check', 'wpshop_tool_default_datas_check');

	function wpshop_ajax_repair_default_datas() {
		check_ajax_referer( 'wpshop_ajax_repair_default_datas' );

		global $wpdb;
		$output = '';
		$container = '';
		$result = '';

		$selected_type = ( isset( $_POST['type'] ) && !empty( $_POST['type'] ) ) ? sanitize_text_field( $_POST['type'] ) : null;
		$identifier = ( isset( $_POST['identifier'] ) && !empty( $_POST['identifier'] ) ) ? sanitize_text_field( $_POST['identifier'] ) : null;

		switch ( $selected_type ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES:
				$result = wpshop_entities::create_cpt_from_csv_file( $identifier );
			break;
			case WPSHOP_DBT_ATTRIBUTE:
				$result = wpshop_entities::create_cpt_attributes_from_csv_file( $identifier );
			break;
		}

		echo json_encode( $result );
		die();
	}
	add_action('wp_ajax_wpshop_ajax_repair_default_datas', 'wpshop_ajax_repair_default_datas');

	function wpshop_ajax_translate_default_datas() {
		check_ajax_referer( 'wpshop_ajax_translate_default_datas' );
		global $wpdb;
		$result = array('status' => true);

		$selected_type = ( isset( $_POST['type'] ) && !empty( $_POST['type'] ) ) ? sanitize_text_field( $_POST['type'] ) : null;
		$identifier = ( isset( $_POST['identifier'] ) && !empty( $_POST['identifier'] ) ) ? sanitize_text_field( $_POST['identifier'] ) : null;

		$entity_id = wpshop_entities::get_entity_identifier_from_code( $identifier );
		$query = $wpdb->prepare("SELECT id, frontend_label FROM " . $selected_type . " WHERE entity_id = %d", $entity_id);
		$attribute_list = $wpdb->get_results( $query );
		if ( !empty($attribute_list) ) {
			foreach ( $attribute_list as $attribute) {
				$update_result = $wpdb->update( $selected_type, array('frontend_label' => __($attribute->frontend_label, 'wpshop')), array('id' => $attribute->id) );
				if ( $update_result === false ) {
					$result['status'] = false;
				}
			}
		}

		echo json_encode( $result );
		die();
	}
	add_action('wp_ajax_wpshop_ajax_translate_default_datas', 'wpshop_ajax_translate_default_datas');

	function wpshop_ajax_db_repair_tool() {
		check_ajax_referer( 'wpshop_ajax_db_repair_tool' );

		$version_id = isset($_POST['version_id']) ? wpshop_tools::varSanitizer($_POST['version_id']) : null;

		echo wpshop_install::alter_db_structure_on_update( $version_id );

		die();
	}
	add_action('wp_ajax_wpshop_ajax_db_repair_tool', 'wpshop_ajax_db_repair_tool');

	function wps_mass_action_main_interface() {
		check_ajax_referer( 'wps_mass_action_main_interface' );
		$tpl_component = array();

		/**	Copy an attribute content to another	*/
		$attribute_list = wpshop_attributes::getElement(wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ), "'valid'", 'entity_id', true);
		$possible_values = array('' => __('Choose an attribute', 'wpshop'));
		$possible_values_for_variation = array('' => __('Choose an attribute', 'wpshop'));
		foreach ( $attribute_list as $attribute ) {
			$possible_values[$attribute->id] = $attribute->frontend_label;
			if ( $attribute->is_used_for_variation == 'yes' ) {
				$possible_values_for_variation[$attribute->id] = $attribute->frontend_label;
			}
		}
		$input_def['possible_value'] = $possible_values;
		$input_def['type'] = 'select';
		$input_def['valueToPut'] = 'index';

		$input_def['name'] = 'wps_update_att_values[from]';
		$input_def['id'] = 'wps_update_att_values_from';
		$tpl_component['ATTRIBUTE_LIST_FROM'] = wpshop_form::check_input_type($input_def);
		$input_def['name'] = 'wps_update_att_values[to]';
		$input_def['id'] = 'wps_update_att_values_to';
		$tpl_component['ATTRIBUTE_LIST_TO'] = wpshop_form::check_input_type($input_def);


		$input_def['possible_value'] = $possible_values_for_variation;
		$input_def['type'] = 'select';
		$input_def['valueToPut'] = 'index';
		$input_def['name'] = 'attribute_id';
		$input_def['id'] = 'attribute_id';
		$tpl_component['USED_FOR_VARIATION_ATTRIBUTE_LIST'] = wpshop_form::check_input_type($input_def);

		$output = wpshop_display::display_template_element('wps_admin_tools_mass_action_main_page', $tpl_component, array(), 'admin');

		echo $output;
		die();
	}
	add_action('wp_ajax_wps_mass_action', 'wps_mass_action_main_interface');

	function wps_mass_action_update_attribute_value() {
		check_ajax_referer( 'wps_mass_action_update_attribute_value' );
		global $wpdb;
		$response = array();

		$from = !empty( $_POST['wps_update_att_values']['from'] ) ? sanitize_text_field( $_POST['wps_update_att_values']['from'] ) : '';
		$to = !empty( $_POST['wps_update_att_values']['to'] ) ? sanitize_text_field( $_POST['wps_update_att_values']['to'] ) : '';
		$entity_to_transfert = !empty( $_POST['wps_entity_to_transfert'] ) ? sanitize_text_field( $_POST['wps_entity_to_transfert'] ) : '';

		if ( $from != $to ) {
			$from_attribute = wpshop_attributes::getElement($from, "'valid'");
			$to_attribute = wpshop_attributes::getElement($to, "'valid'");
			if ( $from_attribute->data_type == $to_attribute->data_type ) {

				/**	Manage specific case	*/
				$query_more_args = array();
				switch( $to_attribute->code ){
					case "barcode":
							$more_query = "
						AND VAL.value NOT LIKE %s";

							$query_more_args[] = 'PDCT%';
						break;
				}
				/**	Get all value for selected attributes	*/
				$query = $wpdb->prepare(
					"SELECT P.ID, VAL.value
					FROM {$wpdb->posts} AS P
						INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $from_attribute->data_type . " AS VAL ON ( VAL.entity_id = P.ID )
					WHERE VAL.attribute_id = %d
						AND P.post_type = %s
						AND VAL.value != ''" . $more_query,
					array_merge( array( $from, $entity_to_transfert ), $query_more_args )
				);
				$element_list_to_update = $wpdb->get_results( $query );

				if ( !empty($element_list_to_update) ) {
					$has_error = false;
					$error_count = 0;
					foreach ( $element_list_to_update as $element ) {

						/**	Historicize the old value of recevier attribute	*/
						if (  $to_attribute->is_historisable == 'yes' ) {
							$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $to_attribute->data_type . " WHERE attribute_id = %d AND entity_id = %d", $to_attribute->id, $element->ID);
							$attribute_histos = $wpdb->get_results( $query );
							if ( !empty( $attribute_histos ) ) {
								foreach ( $attribute_histos as $attribute_histo ) {
									if ( !empty( $attribute_histo->value ) ) {
										$attribute_histo_content['status'] = 'valid';
										$attribute_histo_content['creation_date'] = current_time('mysql', 0);
										$attribute_histo_content['creation_date_value'] = $attribute_histo->creation_date_value;
										$attribute_histo_content['original_value_id'] = $attribute_histo->value_id;
										$attribute_histo_content['entity_type_id'] = $attribute_histo->entity_type_id;
										$attribute_histo_content['attribute_id'] = $attribute_histo->attribute_id;
										$attribute_histo_content['entity_id'] = $attribute_histo->entity_id;
										$attribute_histo_content['unit_id'] = $attribute_histo->unit_id;
										$attribute_histo_content['language'] = $attribute_histo->language;
										$attribute_histo_content['value'] = $attribute_histo->value;
										$attribute_histo_content['value_type'] = WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType;
										$last_histo = $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, $attribute_histo_content);
										if ( $last_histo === false ) {
											$has_error = true;
											$error_count++;
										}
										else {
											$wpdb->delete( WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType, array( 'value_id' => $attribute_histo->value_id ) );
										}
									}
								}
							}
						}

						/**		*/
						$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $to_attribute->data_type . " WHERE attribute_id = %d AND entity_id = %d", $from_attribute->id, $element->ID);
						$attribute_to_save = $wpdb->get_row( $query, ARRAY_A );
						unset($attribute_to_save['value_id']);
						$attribute_to_save['attribute_id'] = $to_attribute->id;
						$attribute_to_save['creation_date_value'] = current_time('mysql', 0);
						$attribute_to_save['user_id'] = get_current_user_id();
						$new_value = $wpdb->insert( WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $to_attribute->data_type, $attribute_to_save );
						if ( $new_value === false ) {
							$has_error = true;
							$error_count++;
						}
						else {
							/**	Save new atribute values in product metadata	*/
							$current_product_metadata = get_post_meta( $element->ID, '_wpshop_product_metadata', true);
							$current_product_metadata[$to_attribute->code] = $element->value;
							update_post_meta( $element->ID, '_wpshop_product_metadata', $current_product_metadata );

							/**	Save a single meta for attribute in case it will be used in specific case where meta needs to be alone	*/
							if ( ( ($to_attribute->is_used_for_sort_by == 'yes') || ($to_attribute->is_searchable == 'yes'))  || ( $to_attribute->is_filterable == 'yes') && !empty($element->value) ) :
								update_post_meta( $element->ID, '_' . $to_attribute->code, $element->value );
							endif;
						}
					}

					if ( !$has_error ) {
						$response['status'] = true;
						$response['error'] = __('Transfert between attribute is done', 'wpshop');
					}
					else {
						$response['status'] = false;
						$response['error'] = sprinttf( __('There are %d error that occured while copying value through attributes', 'wpshop'), $error_count);
					}
				}
				else {
					$response['status'] = false;
					$response['error'] = __('There are no element corresponding to attribute choosen to copy from', 'wpshop');
				}
			}
			else {
				$response['status'] = false;
				$response['error'] = __('Both attribute must have same data type to be updated', 'wpshop');
			}
		}
		else {
			$response['status'] = false;
			$response['error'] = __('You have to choose attributes in order to update values', 'wpshop');
		}

		echo json_encode( $response );
		die();
	}
	add_action('wp_ajax_wps_mass_action_update_attribute', 'wps_mass_action_update_attribute_value');

	function wps_tools_mass_action_load_possible_options_for_variations_attributes() {
		// @TODO attribute_id est introuvable
		check_ajax_referer( 'wps_tools_mass_action_load_possible_options_for_variations_attributes' );
		$output = '';
		$attribute_id = !empty( $_POST['attribute_id'] ) ? (int) $_POST['attribute_id'] : 0;
		$attribute = wpshop_attributes::getElement( $attribute_id, "'valid'" );

		/**	Define new default value	*/
		$attribute_possible_values_output = wpshop_attributes::get_select_output( $attribute, array() );
		$attribute_possible_values_output['possible_value']['no_changes'] = __('No changes', 'wpshop');
		$attribute_possible_values_output['possible_value']['none'] = __('No default value', 'wpshop');
		ksort($attribute_possible_values_output['possible_value']);
		$input_def['possible_value'] = $attribute_possible_values_output['possible_value'];
		$input_def['type'] = 'select';
		$input_def['valueToPut'] = 'index';
		$input_def['name'] = 'wps_update_att_for_variation_options_values';
		$input_def['id'] = 'wps_update_att_for_variation_options_values';
		$output .= __( 'Default value to affect for this attribute to all products', 'wpshop' ) . ' ' . wpshop_form::check_input_type( $input_def );

		/**	Define if attribute is required for adding product to cart	*/
		$input_def = array();
		$input_def['possible_value'] = array('no_changes' => __('No changes', 'wpshop'), 'no' => __('No', 'wpshop'), 'yes' => __('Yes', 'wpshop'));
		$input_def['type'] = 'select';
		$input_def['valueToPut'] = 'index';
		$input_def['name'] = 'wps_update_att_for_variation_required_state';
		$input_def['id'] = 'wps_update_att_for_variation_required_state';
		$output .= '<br/>' . __( 'Put this attribute as required for all products', 'wpshop' ) . ' ' . wpshop_form::check_input_type( $input_def );


		$input_def = array();
		$input_def['possible_value'] = array('no_changes' => __('No changes', 'wpshop'), 'no' => __('No', 'wpshop'), 'yes' => __('Yes', 'wpshop'));
		$input_def['type'] = 'select';
		$input_def['valueToPut'] = 'index';
		$input_def['name'] = 'wps_update_att_for_variation[price_display][text_from]';
		$input_def['id'] = 'wps_update_att_for_variation_price_display_text_from';
		$output .= '<br/>' . __( 'Display "Price from" text before price for all products', 'wpshop' ) . ' ' . wpshop_form::check_input_type( $input_def );

		$input_def = array();
		$input_def['possible_value'] = array('no_changes' => __('No changes', 'wpshop'), 'no' => __('No', 'wpshop'), 'yes' => __('Yes', 'wpshop'));
		$input_def['type'] = 'select';
		$input_def['valueToPut'] = 'index';
		$input_def['name'] = 'wps_update_att_for_variation[price_display][lower_price]';
		$input_def['id'] = 'wps_update_att_for_variation_price_display_lower_price';
		$output .= '<br/>' . __( 'Display lower price for all products', 'wpshop' ) . ' ' . wpshop_form::check_input_type( $input_def );

		$input_def = array();
		$input_def['possible_value'] = array('no_changes' => __('No changes', 'wpshop'), 'replacement' => __('Replace product price with option price', 'wpshop'), 'addition' => __('Add option price to product price', 'wpshop'));
		$input_def['type'] = 'select';
		$input_def['valueToPut'] = 'index';
		$input_def['name'] = 'wps_update_att_for_variation[price_behaviour]';
		$input_def['id'] = 'wps_update_att_for_variation_price_behaviour';
		$output .= '<br/>' . __( 'Price calculation behaviour', 'wpshop' ) . ' ' . wpshop_form::check_input_type( $input_def );

		$input_def = array();
		$input_def['possible_value'] = array('no_changes' => __('No changes', 'wpshop'), 'single' => __('Priority to single options', 'wpshop'), 'combined' => __('Priority to combined options', 'wpshop'));
		$input_def['type'] = 'select';
		$input_def['valueToPut'] = 'index';
		$input_def['name'] = 'wps_update_att_for_variation[priority]';
		$input_def['id'] = 'wps_update_att_for_variation_priority';
		$output .= '<br/>' . __( 'Choose priority combination for calculating options', 'wpshop' ) . ' ' . wpshop_form::check_input_type( $input_def );

		echo $output;
		die();
	}
	add_action('wp_ajax_wps_tools_mass_action_load_possible_options_for_variations_attributes', 'wps_tools_mass_action_load_possible_options_for_variations_attributes');

	function wps_mass_action_change_variation_option() {
		check_ajax_referer( 'wps_mass_action_change_variation_option' );
		global $wpdb;

		$attribute_id = !empty( $_POST['attribute_id'] ) ? (int) $_POST['attribute_id'] : 0;
		$attribute = wpshop_attributes::getElement( $attribute_id, "'valid'" );
		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE ('%%%s%%')", '_wpshop_variation_defining', $attribute->code );
		$meta_to_update = $wpdb->get_results( $query );
		$has_error = false;
		if ( !empty($meta_to_update) ) {
			foreach ( $meta_to_update as $meta_def ) {
				$meta_value = unserialize( $meta_def->meta_value );
				if ( !empty($meta_value) && !empty( $meta_value['attributes'] ) && in_array( $attribute->code, $meta_value['attributes']) ) {

					$update_att_for_variation_options_values = !empty( $_POST['wps_update_att_for_variation_options_values'] ) ? sanitize_text_field( $_POST['wps_update_att_for_variation_options_values'] ) : '';
					if ( $update_att_for_variation_options_values != 'no_changes' ) {
						$meta_value['options']['attributes_default_value'][$attribute->code] = $update_att_for_variation_options_values;
					}

					$wps_update_att_for_variation_required_state = !empty( $_POST['wps_update_att_for_variation_required_state'] ) ? sanitize_text_field( $_POST['wps_update_att_for_variation_required_state'] ) : '';
					if ( !empty($wps_update_att_for_variation_required_state) && ($wps_update_att_for_variation_required_state != 'no_changes') ) {
						if ( $wps_update_att_for_variation_required_state == 'yes') {
							$meta_value['options']['required_attributes'][$attribute->code] = $attribute->code;
						}
						else if ( !empty($meta_value['options']['required_attributes']) && !empty($meta_value['options']['required_attributes'][$attribute->code]) ) {
							unset($meta_value['options']['required_attributes'][$attribute->code]);
						}
					}

					$update_att_for_variation = !empty( $_POST['wps_update_att_for_variation'] ) ? (array) $_POST['wps_update_att_for_variation'] : array();
					$text_from = !empty( $update_att_for_variation['text_from'] ) ? sanitize_text_field( $update_att_for_variation['text_from'] ) : '';
					if ( !empty( $update_att_for_variation ) ) {
						if ( !empty($text_from) && ($text_from != 'no_changes') ) {
							if ( $text_from == 'yes' ) {
								$meta_value['options']['price_display']['text_from'] = 'on';
							}
							else if( !empty($meta_value['options']['price_display']['text_from']) ) {
								unset($meta_value['options']['price_display']['text_from']);
							}
						}

						$lower_price = !empty( $update_att_for_variation['lower_price'] ) ? sanitize_text_field( $update_att_for_variation['lower_price'] ) : '';
						if ( !empty($lower_price) && ($lower_price != 'no_changes') ) {
							if ( $lower_price == 'yes' ) {
								$meta_value['options']['price_display']['lower_price'] = 'on';
							}
							else if( !empty($meta_value['options']['price_display']['lower_price']) ) {
								unset($meta_value['options']['price_display']['lower_price']);
							}
						}

						$price_behaviour = !empty( $update_att_for_variation['price_behaviour'] ) ? sanitize_text_field( $update_att_for_variation['price_behaviour'] ) : '';
						if ( !empty($price_behaviour) && ($price_behaviour != 'no_changes') ) {
							$meta_value['options']['price_behaviour'][0] = $price_behaviour;
						}

						$priority = !empty( $update_att_for_variation['priority'] ) ? sanitize_text_field( $update_att_for_variation['priority'] ) : '';
						if ( !empty($priority) && ($priority != 'no_changes') ) {
							$meta_value['options']['priority'][0] = $priority;
						}
					}

					$meta_save = update_meta( $meta_def->meta_id, '_wpshop_variation_defining', $meta_value);
					if ( $meta_save === false ) {
						$has_error = true;
					}

				}
			}
		}

		echo json_encode( array('status' => $has_error, 'error' => (!$has_error ? __('Product variation parameters have been updated', 'wpshop') : __('An error occured while changing products variations options parameters'))) );
		die();
	}
	add_action('wp_ajax_wps_mass_action_change_variation_option', 'wps_mass_action_change_variation_option');

/**	Options page	*/
	/**
	 * Addons activate
	 * @todo Activate linked attribute if defined
	 */
	function ajax_activate_addons() {
		global $wpdb;
		check_ajax_referer( 'wpshop_ajax_activate_addons', 'wpshop_ajax_nonce' );

		$addon_name = isset($_POST['addon']) ? wpshop_tools::varSanitizer($_POST['addon']) : null;
		$addon_code = isset($_POST['code']) ? wpshop_tools::varSanitizer($_POST['code']) : null;
		$state = false;

		if (!empty($addon_name) && !empty($addon_code)) {
			$addons_list = (unserialize(WPSHOP_ADDONS_LIST));
			if (in_array($addon_name, array_keys($addons_list))) {
				$plug = get_plugin_data( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/wpshop.php' );
				$code_part = array();
				$code_part[] = substr(hash ( "sha256" , $addons_list[$addon_name][0] ),  $addons_list[$addon_name][1], 5);
				$code_part[] = substr(hash ( "sha256" , $plug['Name'] ), WPSHOP_ADDONS_KEY_IS, 5);
				$code_part[] = substr(hash ( "sha256" , 'addons' ), WPSHOP_ADDONS_KEY_IS, 5);
				$code = $code_part[1] . '-' . $code_part[2] . '-' . $code_part[0];

				$current_web_site = site_url('/');

				if ( $addons_list[$addon_name][2] == 'per_site') {
					$code .= '-' . substr(hash ( "sha256" , $current_web_site ),  $addons_list[$addon_name][1], 5);
				}

				if ( !empty($addons_list[$addon_name][4]) && $addons_list[$addon_name][4] == 'WPSHOP_NEW_QUOTATION_ADMIN_MESSAGE') {
					$admin_new_quotation_message = get_option( 'WPSHOP_NEW_QUOTATION_ADMIN_MESSAGE' );
					if ( empty($admin_new_quotation_message) ) {
						wps_message_ctr::createMessage( 'WPSHOP_NEW_QUOTATION_ADMIN_MESSAGE' );
					}
					$admin_new_quotation_confirm_message = get_option( 'WPSHOP_QUOTATION_CONFIRMATION_MESSAGE' );
					if ( empty($admin_new_quotation_confirm_message) ) {
						wps_message_ctr::createMessage( 'WPSHOP_QUOTATION_CONFIRMATION_MESSAGE' );
					}
				}

				if ($code == $addon_code) {
					$extra_options = get_option(WPSHOP_ADDONS_OPTION_NAME, array() );
					$extra_options[$addon_name]['activate'] = true;
					$extra_options[$addon_name]['activation_date'] = current_time('mysql', 0);
					$extra_options[$addon_name]['activation_code'] = $addon_code;
					if ( update_option(WPSHOP_ADDONS_OPTION_NAME, $extra_options) ) {
						$result = array(true, __('The addon has been activated successfully', 'wpshop'), __('Activated','wpshop'));
						if( !empty($addons_list[$addon_name][3]) ) {
							$activate_attribute_for_addon = $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid'), array('code' => $addons_list[$addon_name][3]));
						}
						$state = true;
					}
					else {
						$result = array(false, __('An error occured','wpshop'), __('Desactivated','wpshop'));
					}
				}
				else {
					$result = array(false, __('The activating code is invalid', 'wpshop'), __('Desactivated','wpshop'));
				}
			}
			else {
				$result = array(false, __('The addon to activate is invalid', 'wpshop'), __('Desactivated','wpshop'));
			}
		}
		else {
			$result = array(false, __('An error occured','wpshop'), __('Desactivated','wpshop'));
		}
		$activated_class = unserialize(WPSHOP_ADDONS_STATES_CLASS);

		echo json_encode(array_merge($result, array($addon_name, $activated_class[$state])));
		die();
	}
	add_action('wp_ajax_activate_wpshop_addons', 'ajax_activate_addons');

	/**
	 * Addons desactivate
	 */
	function ajax_desactivate_wpshop_addons() {
		check_ajax_referer( 'wpshop_ajax_activate_addons', 'wpshop_ajax_nonce' );

		$addon_name = isset($_POST['addon']) ? wpshop_tools::varSanitizer($_POST['addon']) : null;
		$state = true;

		if ( !empty($addon_name) ) {
			$addons_list = array_keys(unserialize(WPSHOP_ADDONS_LIST));
			if (in_array($addon_name, $addons_list)) {
				$extra_options = get_option(WPSHOP_ADDONS_OPTION_NAME, array());
				$extra_options[$addon_name]['activate'] = false;
				$extra_options[$addon_name]['deactivation_date'] = current_time('mysql', 0);
				if ( update_option(WPSHOP_ADDONS_OPTION_NAME, $extra_options) ) {
					$result = array(true, __('The addon has been desactivated successfully', 'wpshop'), __('Desactivated','wpshop'));
					$state = false;
				}
				else {
					$result = array(false, __('An error occured','wpshop'), __('Activated','wpshop'));
				}
			}
			else {
				$result = array(false, __('The addon to desactivate is invalid', 'wpshop'), __('Activated','wpshop'));
			}
		}
		$activated_class = unserialize(WPSHOP_ADDONS_STATES_CLASS);

		echo json_encode(array_merge($result, array($addon_name, $activated_class[$state])));
		die();
	}
	add_action('wp_ajax_desactivate_wpshop_addons', 'ajax_desactivate_wpshop_addons');

	/**
	 * Display opttions for including user address into account form
	 */
	function ajax_integrate_billing_into_register() {
		check_ajax_referer( 'wpshop_ajax_integrate_billin_into_register', 'wpshop_ajax_nonce' );
		global $wpshop_account;
		$wpshop_billing_address = get_option('wpshop_billing_address');
		$current_billing_address = isset($_POST['current_billing_address']) ? intval(wpshop_tools::varSanitizer($_POST['current_billing_address'])) : null;
		$selected_field = isset($_POST['selected_field']) ? wpshop_tools::varSanitizer($_POST['selected_field']) : null;
		$attribute = !empty( $_POST['attribute'] ) ? (array) $_POST['attribute'] : array();
		$billing_form_fields = wps_address::get_addresss_form_fields_by_type ( $current_billing_address );
		$possible_values_for_billing = array('' => __('No corresponding field', 'wpshop'));
		foreach ( $billing_form_fields[$current_billing_address] as $attribute_group_id => $attribute_group_detail) {
			foreach ( $attribute_group_detail['content'] as $attribute_build_code => $attribute_definition) {
				$possible_values_for_billing[$attribute_build_code] = $attribute_definition['label'];
			}
		}
		$attributes_set = wpshop_attributes_set::getElement( 'yes', "'valid'", 'is_default', '', wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS));
		/*	Get the attribute set details in order to build the product interface	*/
		$productAttributeSetDetails = wpshop_attributes_set::getAttributeSetDetails( ( !empty($attributes_set->id) ) ? $attributes_set->id : '', "'valid'");
		if(!empty($productAttributeSetDetails)){
			foreach($productAttributeSetDetails as $productAttributeSetDetail){
				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute) {
						if(!empty($attribute->id)) {
							$submitOrderInfos = !empty( $_POST['submitOrderInfos'] ) ? (int) $_POST['submitOrderInfos'] : 0;
							if( !empty($submitOrderInfos) ) {
								$value = $attribute[$attribute->data_type][$attribute->code];
							}
							else {
								$value = '';
								if ( $attribute->code != 'user_pass') {
									$code = $attribute->code;
									$value = $user->$code;
								}
							}
							$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute, $value, array() );
							$account_form_field[$attribute->code] = $attribute_output_def;
						}
					}
				}
			}
		}

		$possible_values = array();
		$matching_field = '';
		foreach ( $account_form_field as $attribute_code => $attribute_detail) {
			$possible_values[$attribute_code] = $attribute_detail['label'];

			$input_def['name'] = 'wpshop_billing_address[integrate_into_register_form_matching_field][' . $attribute_code . ']';
			$input_def['id'] = 'wpshop_billing_address_integrate_into_register_form_after_field';
			$input_def['possible_value'] = $possible_values_for_billing;
			$input_def['type'] = 'select';
			$input_def['valueToPut'] = 'index';
			$input_def['value'] = (!empty($wpshop_billing_address['integrate_into_register_form_matching_field']) && array_key_exists($attribute_code, $wpshop_billing_address['integrate_into_register_form_matching_field']) ? $wpshop_billing_address['integrate_into_register_form_matching_field'][$attribute_code] : null);
			$matching_field .= '<div>' . $attribute_detail['label'] . ' : ' . wpshop_form::check_input_type($input_def) . '</div>';
		}

		$input_def['name'] = 'wpshop_billing_address[integrate_into_register_form_after_field]';
		$input_def['id'] = 'wpshop_billing_address_integrate_into_register_form_after_field';
		$input_def['possible_value'] = $possible_values;
		$input_def['type'] = 'select';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = $selected_field;
		$output = '<div>' . wpshop_form::check_input_type($input_def) . '</div>';

		$output .= '<div><div>' . __('If some fields are twice, you can hide them into billing address by matching them with account field below. Left fields are account form, right fields are for billing address', 'wpshop') . '</div>' . $matching_field . '</div>';

		echo $output;
		die();
	}
	add_action('wp_ajax_integrate_billing_into_register', 'ajax_integrate_billing_into_register');


	/**
	 * Search element in database for shortcode insertion interface
	 */
	function ajax_wpshop_element_search() {
		check_ajax_referer( 'wpshop_element_search', 'wpshop_ajax_nonce' );

		$wpshop_element_searched = isset($_REQUEST['wpshop_element_searched']) ? wpshop_tools::varSanitizer($_REQUEST['wpshop_element_searched']) : null;
		$wpshop_element_type = isset($_REQUEST['wpshop_element_type']) ? wpshop_tools::varSanitizer($_REQUEST['wpshop_element_type']) : null;
		$wpshop_format_result = isset($_REQUEST['wpshop_format_result']) ? (bool)wpshop_tools::varSanitizer($_REQUEST['wpshop_format_result']) : true;

		switch ( $wpshop_element_type ) {
			case 'product':
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
				$data = wpshop_products::product_list($wpshop_format_result, $wpshop_element_searched);
				break;
			case 'categories':
				$data = wpshop_categories::product_list_cats($wpshop_format_result, $wpshop_element_searched);
				break;
			case WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS:
				$wps_provider_ctr = new wps_provider_ctr();
				$data = $wps_provider_ctr->read( array( 's' => $wpshop_element_searched ) );
				break;
		}

		if ( $wpshop_format_result ) {
			$data = empty($data) ? __('No match', 'wpshop') : $data;
		}
		else {
			if ( !empty($data) ) {
				$temp_data = $data;
				unset( $data );
				foreach ( $temp_data as $post) {
					$data[$post->ID] = $post->ID . ' - ' . $post->post_title;
				}
			}
			else {
				$data = array();
			}
		}


		echo json_encode($data);
		die();
	}
	add_action('wp_ajax_wpshop_element_search', 'ajax_wpshop_element_search');


/*	Frontend	*/
	/**
	 * Add product to the end user cart
	 */
	function ajax_wpshop_add_to_cart() {
		check_ajax_referer( 'ajax_pos_product_variation_selection' );

		global $wpdb;
		$wpshop_cart = new wps_cart();

		$product_id = isset($_POST['wpshop_pdt']) ? intval(wpshop_tools::varSanitizer($_POST['wpshop_pdt'])) : null;
		$product_qty = isset($_POST['wpshop_pdt_qty']) ? intval(wpshop_tools::varSanitizer($_POST['wpshop_pdt_qty'])) : 1;
		$cart_option = get_option('wpshop_cart_option', array() );
		$wpshop_variation_selected = !empty($_POST['wps_pdt_variations']) ? (array)$_POST['wps_pdt_variations'] : array();
		$from_administration =  ( !empty($_POST['from_admin']) ) ? (int)$_POST['from_admin'] : false;
		$order_id =  ( !empty($_POST['wps_orders_order_id']) ) ? wpshop_tools::varSanitizer( $_POST['wps_orders_order_id'] ) : null;


		// Check Cart Animation
		$cart_animation_choice = ( !empty($cart_option) && !empty($cart_option['animation_cart_type']) ? $cart_option['animation_cart_type'] : null);
		if ( !empty($cart_option['total_nb_of_item_allowed']) && ((int) $cart_option['total_nb_of_item_allowed'][0] == 1) ) {
			$wpshop_cart->empty_cart();
		}

		// Prepare Product to be added to cart
		$formatted_product = $wpshop_cart->prepare_product_to_add_to_cart( $product_id, $product_qty, $wpshop_variation_selected );
		$product_to_add_to_cart = $formatted_product[0];
		$new_pid = $formatted_product[1];

		// Check cart Type
		$cart_type_for_adding = 'normal';
		$wpshop_cart_type = !empty( $_POST['wpshop_cart_type'] ) ? sanitize_text_field( $_POST['wpshop_cart_type'] ) : '';
		if (!empty($wpshop_cart_type) ) {
			switch(wpshop_tools::varSanitizer($wpshop_cart_type)){
				case 'quotation':
					$wpshop_cart_type = 'quotation';
					break;
				default:
					$wpshop_cart_type = 'normal';
					break;
			}
		}

		// Check Product image
		$product = get_post( $product_id );
		$product_img = '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="no picture" />';
		if ( !empty($product_id) ) {
			if ( $product->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
				$parent_def = wpshop_products::get_parent_variation( $product_id );
				$parent_post = ( !empty($parent_def['parent_post']) ) ? $parent_def['parent_post'] : array();
				$product_title = ( !empty($parent_post) && !empty($parent_post->post_title) ) ? $parent_post->post_title : '';
				$product_description = ( !empty($parent_post) && !empty($parent_post->post_content) ) ? $parent_post->post_content : '';
				$product_img =  ( !empty($parent_post->ID) ) ? get_the_post_thumbnail( $parent_post->ID, 'thumbnail') : '';
			}
			else {
				$product_title = $product->post_title;
				$product_description = $product->post_content;
				$product_img =  get_the_post_thumbnail( $product_id, 'thumbnail');
			}
		}

		$wps_orders_from_admin = !empty( $_POST['wps_orders_from_admin'] ) ? (int) $_POST['wps_orders_from_admin'] : 0;
		if ( !empty($wps_orders_from_admin) && $wps_orders_from_admin) {
			$order_meta = get_post_meta($order_id, '_order_postmeta', true);
			$return = $wpshop_cart->add_to_cart( $product_to_add_to_cart, array( $new_pid => $product_qty ), $wpshop_cart_type, array(), true, $order_meta, $order_id );

			echo json_encode( array(true) );
			die();
		}
		else {
			$return = $wpshop_cart->add_to_cart( $product_to_add_to_cart, array( $new_pid => $product_qty ), $wpshop_cart_type );
		}

		if ( $return == 'success' ) {
			$cart_page_url = apply_filters( 'wps_cart_page_url', get_permalink( wpshop_tools::get_page_id( get_option( 'wpshop_cart_page_id' ) ) ) );
			/** Template parameters	*/
			$template_part = 'product_added_to_cart_message';

			/** Build template	*/
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require_once(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$succes_message_box = ob_get_contents();
				ob_end_clean();
			}
			else {
				$succes_message_box = wpshop_display::display_template_element($template_part, array('PRODUCT_ID' => $product_id));
			}
			unset($tpl_component);

			$action_after_add = (($cart_option['product_added_to_cart'][0] == 'cart_page') ? true : false);
			if ($wpshop_cart_type == 'quotation') {
				$action_after_add = (($cart_option['product_added_to_quotation'][0] == 'cart_page') ? true : false);
			}

			/** Product Price **/
			$product_price = '';
			if ( !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) && !empty($product_to_add_to_cart) ) {
				$idp = '';

				if ( !empty($product_to_add_to_cart[$new_pid]['variations']) && count($product_to_add_to_cart[$new_pid]['variations']) < 2 ) {
					$idp = $product_to_add_to_cart[$new_pid]['variations'][0];
				}
				else {
					if( strstr( $new_pid, '__') ) {
						$idp = $new_pid;
					}
					else {
						$idp = $product_to_add_to_cart[$new_pid]['id'];
					}
				}

				if( !empty($idp) ) {
					$default_currency = wpshop_tools::wpshop_get_currency( false );
					$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );

					$real_price = null;
					if ( !empty( $_SESSION[ 'cart' ] ) && !empty( $_SESSION[ 'cart' ][ 'order_items' ] ) && !empty( $_SESSION[ 'cart' ][ 'order_items' ][$new_pid] ) && !empty( $_SESSION[ 'cart' ][ 'order_items' ][$new_pid]['item_amount_to_pay_now'] ) ) {
						$real_price = ( $_SESSION['cart']['order_items'][$new_pid]['item_amount_to_pay_now'] * $product_qty );
					}

					$pr = empty( $real_price ) ? ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? ( $_SESSION['cart']['order_items'][$new_pid]['item_pu_ht']  * $product_qty ) : ( $_SESSION['cart']['order_items'][$new_pid]['item_pu_ttc'] * $product_qty ) : $real_price;
					$product_price = wpshop_tools::formate_number( $pr ).$default_currency;
				}
			}
			/** Check if there are linked products **/
			$related_products = get_post_meta( $product_id, '_wpshop_product_related_products', true);
			$tpl_component = array();
			$linked_products = '';
			if ( !empty($related_products) ) {
				// $linked_products = '<h2>'.__('Linked products', 'wpshop').'</h2>';
				$linked_products .= '<div class="modal_product_related">' .do_shortcode( '[wpshop_related_products pid="' .$product_id. '" sorting="no"]' ).'</div>';
			}
			else {
				$linked_products = '';
			}

			$message_confirmation = sprintf( __('%s has been add to the cart', 'wpshop'), $product->post_title );

			$modal_content = wpshop_display::display_template_element('wps_new_add_to_cart_confirmation_modal', array( 'RELATED_PRODUCTS' => $linked_products , 'PRODUCT_PICTURE' => $product_img, 'PRODUCT_TITLE' => $product_title, 'PRODUCT_PRICE' => $product_price, 'PRODUCT_DESCRIPTION' => $product_description) );
			$modal_footer_content = wpshop_display::display_template_element('wps_new_add_to_cart_confirmation_modal_footer', array( 'LINK_CART_PAGE' => wpshop_tools::get_page_id( get_permalink( get_option('wpshop_cart_page_id') ) ) ) );

			$response = array( true, $succes_message_box, $action_after_add, $cart_page_url, $product_id, array($cart_animation_choice, $message_confirmation), array($product_img, $product_title, $linked_products, $product_price), $modal_content, $modal_footer_content );
		}
		else {
			$response = array( false, $return );
		}

		wp_die( json_encode( $response ) );
	}
 	add_action('wp_ajax_wpshop_add_product_to_cart', 'ajax_wpshop_add_to_cart');
	add_action('wp_ajax_nopriv_wpshop_add_product_to_cart', 'ajax_wpshop_add_to_cart');


	/**
	 * Set product qty into customer cart
	 */
	function ajax_wpshop_set_qty_for_product_into_cart() {
		check_ajax_referer( 'ajax_wpshop_set_qty_for_product_into_cart' );
		$response = array();
		$wpshop_cart = new wps_cart();
		$product_id = isset($_POST['product_id']) ? wpshop_tools::varSanitizer($_POST['product_id']) : null;
		$product_qty = isset($_POST['product_qty']) ? intval(wpshop_tools::varSanitizer($_POST['product_qty'])) : null;

		$pid = $product_id;
		if (strpos($product_id,'__') !== false) {
			//$pid = $_SESSION['cart']['order_items'][$product_id]['item_id'];
			foreach( $_SESSION['cart']['order_items'][$product_id]['item_meta']['variations'] as $variation ) {
				$pid = $variation['product_id'];
				break;
			}
		}

		$global_discount = !empty( $_POST['global_discount'] ) ? (int) $_POST['global_discount'] : 0;
		if ( !empty($global_discount) ) {
			$_SESSION['cart']['pos_global_discount'] = $global_discount;
		}

		if (!empty($product_id)) {
			if (isset($product_qty)) {
				if ( $product_qty == 0 ) {
					$variation_of_product = query_posts( array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, 'post_parent' => $pid, 'posts_per_page' => -1) );
					if ( !empty($variation_of_product) ) {
						foreach ( $variation_of_product as $p_id) {
							$wpshop_cart->set_product_qty($p_id->ID, $product_qty);
						}
					}
				}
				$return = $wpshop_cart->set_product_qty( $product_id, $product_qty, $pid );
				$response[] = $return;
			}
			else {
				$response[] = false;
				$response[] = __('Parameters error.','wpshop');
			}
		}
		wp_die( json_encode( $response ) );
	}
	add_action('wp_ajax_wpshop_set_qtyfor_product_into_cart', 'ajax_wpshop_set_qty_for_product_into_cart');
	add_action('wp_ajax_nopriv_wpshop_set_qtyfor_product_into_cart', 'ajax_wpshop_set_qty_for_product_into_cart');

	/**
	 * Display cart after doing an action on it
	 */
	function ajax_wpshop_display_cart() {
		check_ajax_referer( 'ajax_wpshop_display_cart' );
		ini_set('display_errors', true);
		error_reporting(E_ALL);
		$wps_cart_ctr = new wps_cart();
		$display_button = isset($_POST['display_button']) ? (bool)wpshop_tools::varSanitizer($_POST['display_button']) : null;
		echo $wps_cart_ctr->display_cart($display_button, array(), 'ajax_request');
		die();
	}
	add_action('wp_ajax_wpshop_display_cart', 'ajax_wpshop_display_cart');
	add_action('wp_ajax_nopriv_wpshop_display_cart', 'ajax_wpshop_display_cart');

	/**
	 * Display mini cart widgte after doing an action on it
	 */
	function ajax_wpshop_reload_mini_cart(){
		check_ajax_referer( 'ajax_wpshop_display_cart' );
		echo wpshop_cart::mini_cart_content();
		die();
	}
	add_action('wp_ajax_wpshop_reload_mini_cart', 'ajax_wpshop_reload_mini_cart');
	add_action('wp_ajax_nopriv_wpshop_reload_mini_cart', 'ajax_wpshop_reload_mini_cart');

	/**
	 * Refresh Price in complete product sheet and Cart summary display
	 */
	function wpshop_ajax_wpshop_variation_selection() {
		check_ajax_referer( 'wpshop_ajax_wpshop_variation_selection' );
		$product_id = isset($_POST['wpshop_pdt']) ? intval(wpshop_tools::varSanitizer($_POST['wpshop_pdt'])) : null;
		$wpshop_variation_selected = isset($_POST['wpshop_variation']) ? (array)$_POST['wpshop_variation'] : null;
		$wpshop_free_variation = isset($_POST['wpshop_free_variation']) ? (array)$_POST['wpshop_free_variation'] : null;
		$wpshop_current_for_display = isset($_POST['wpshop_current_for_display']) ? sanitize_text_field( $_POST['wpshop_current_for_display'] ) : null;
		$product_qty = isset($_POST['product_qty']) ? (int)$_POST['product_qty'] : 1;

		$product_variation_summary = wpshop_products::wpshop_ajax_wpshop_variation_selection( $product_id, $wpshop_variation_selected, $wpshop_free_variation, $wpshop_current_for_display, $product_qty );

		wp_die( json_encode( $product_variation_summary ) );
	}
	add_action('wp_ajax_wpshop_variation_selection', 'wpshop_ajax_wpshop_variation_selection');
	add_action('wp_ajax_nopriv_wpshop_variation_selection', 'wpshop_ajax_wpshop_variation_selection');



	function wpshop_ajax_variation_selection_show_detail_for_value() {
		global $wpdb;

		check_ajax_referer( 'wpshop_ajax_wpshop_variation_selection' );

		$display = '';
		$attribute_for_detail = isset($_POST['attribute_for_detail']) ? (array)$_POST['attribute_for_detail'] : array();

		if ( !empty( $attribute_for_detail ) ) {
			$selection = array();
			foreach ( $attribute_for_detail as $selected_variation ) {
				$variation_definition = explode('-_variation_val_-', $selected_variation);
				$attribute_definition = wpshop_attributes::getElement($variation_definition[0], "'valid'", 'code');
				$post_definition = get_post($variation_definition[1]);

				if ( !empty($post_definition) ) {
					$post_content = ( !empty($post_definition) && !empty($post_definition->post_content) ) ? $post_definition->post_content : '';
					if ( empty($post_content) && !empty($post_definition->post_parent) ) {
						$post_parent_definition = get_post($post_definition->post_parent);
						if ( !empty($post_parent_definition) ) {
							$post_content = $post_parent_definition->post_content;
						}
					}

					$tpl_component['VARIATION_ATTRIBUTE_NAME_FOR_DETAIL'] = $attribute_definition->frontend_label;
					$tpl_component['VARIATION_VALUE_TITLE_FOR_DETAIL'] = $post_definition->post_title;
					$tpl_component['VARIATION_VALUE_DESC_FOR_DETAIL'] = $post_content;
					$tpl_component['VARIATION_VALUE_LINK_FOR_DETAIL'] = get_permalink($variation_definition[1]);

					$display .= wpshop_display::display_template_element('wpshop_product_variation_value_detail_content', $tpl_component);
					unset($tpl_component);
				}
			}
		}

		echo $display;
		die();
	}
	add_action('wp_ajax_wpshop_ajax_variation_selection_show_detail_for_value', 'wpshop_ajax_variation_selection_show_detail_for_value');
	add_action('wp_ajax_nopriv_wpshop_ajax_variation_selection_show_detail_for_value', 'wpshop_ajax_variation_selection_show_detail_for_value');


	/**
	 * Save customer account informations
	 */
	function wpshop_ajax_save_customer_account() {
		check_ajax_referer( 'wpshop_customer_register', 'wpshop_ajax_nonce' );
		global $wpshop, $wpshop_account, $wpdb;
		$reponse='';
		$status = false;
		$validate = true;

		$attribute = !empty( $_POST['attribute'] ) ? (array)$_POST['attribute'] : array();

		$user_id = get_current_user_id();
		$current_connected_user = !empty( $user_id ) ? $user_id : null;
		$wpshop_billing_address = get_option('wpshop_billing_address');
		if ( !empty($wpshop_billing_address['integrate_into_register_form']) && ($wpshop_billing_address['integrate_into_register_form'] == 'yes') && isset($attribute[$wpshop_billing_address['choice']]) ) {
			if ( !empty($wpshop_billing_address['integrate_into_register_form_matching_field']) ) {
				$address_fields = wps_address::get_addresss_form_fields_by_type ( $wpshop_billing_address['choice'] );
				$address_field = $address_fields[$wpshop_billing_address['choice']];
				$temp_aray_for_matching = array_flip($wpshop_billing_address['integrate_into_register_form_matching_field']);
				foreach ( $address_field as $group_id => $group_detail) {
					foreach ( $group_detail['content'] as $attribute_build_code => $attribute_def) {
						if ( in_array($attribute_build_code, $wpshop_billing_address['integrate_into_register_form_matching_field']) && empty( $attribute[$wpshop_billing_address['choice']][$attribute_def['data_type']][$attribute_def['name']] ) && !empty(  $attribute[$attribute_def['data_type']][$temp_aray_for_matching[$attribute_build_code]] ) ) {
							$attribute[$wpshop_billing_address['choice']][$attribute_def['data_type']][$attribute_def['name']] = $attribute[$attribute_def['data_type']][$temp_aray_for_matching[$attribute_build_code]];
							if ( $attribute_def['_need_verification'] == 'yes' && !empty($attribute[$wpshop_billing_address['choice']][$attribute_def['data_type']][$attribute_def['name'] . '2']) ) {
								$attribute[$wpshop_billing_address['choice']][$attribute_def['data_type']][$attribute_def['name'] . '2'] = $attribute[$attribute_def['data_type']][$temp_aray_for_matching[$attribute_build_code] . '2'];
							}
						}
					}
				}
				$attribute[$wpshop_billing_address['choice']]['varchar']['address_title'] = !empty( $attribute[$wpshop_billing_address['choice']]['varchar']['address_title'] ) ? $attribute[$wpshop_billing_address['choice']]['varchar']['address_title'] : __('Billing address', 'wpshop');
			}
			$group = wps_address::get_addresss_form_fields_by_type($wpshop_billing_address['choice']);
			$validate = false;
			foreach ( $group as $attribute_sets ) {
				foreach ( $attribute_sets as $attribute_set_field ) {
					$validate = $wpshop->validateForm($attribute_set_field['content'], $attribute[$wpshop_billing_address['choice']], '');
				}
			}
		}
		$cart_url = !empty($_SESSION['cart']['order_items']) ? get_permalink(wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') )) : get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id')));

		$account_form_type = !empty( $_POST['account_form_type'] ) ? sanitize_text_field( $_POST['account_form_type'] ) : '';
		$validate_personal_form_infos = ( !empty( $account_form_type ) && $account_form_type == 'partial' ) ? $wpshop->validateForm($wpshop_account->partial_personal_infos_fields, array(), '', true) : $wpshop->validateForm($wpshop_account->personal_info_fields);
		if( $validate && $validate_personal_form_infos ) {
			$account_creation_result = $wpshop_account->save_account_form($user_id,  ( ( !empty($account_form_type) && $account_form_type == 'partial' ) ? 'partial' : 'complete') );
			$status = $account_creation_result[0];
			$user_id = $account_creation_result[1];
			$is_partial_account_creation  = $account_creation_result[2];
			if ( $is_partial_account_creation == 'partial' ) {
				$permalink_option = get_option('permalink_structure');
				if ( !empty($permalink_option) ) {
					$cart_url = get_permalink( wpshop_tools::get_page_id( get_option('wpshop_signup_page_id') ) ).'?complete_sign_up=ok';
				}
				else {
					$cart_url = get_permalink( wpshop_tools::get_page_id(get_option('wpshop_signup_page_id') ) ).'&complete_sign_up=ok';
				}
			}
			else {
				if ( !empty($_SESSION['cart']) ) {
					$cart_url = get_permalink( wpshop_tools::get_page_id(get_option('wpshop_checkout_page_id')) );
				}
				else {
					$cart_url = get_permalink( wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id')) );
				}
			}
			// check if the customer have already register an address
			$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE post_author = %d AND post_type = %s', $user_id, WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS);
			$exist_address = $wpdb->get_results($query);
		}
		// If there is errors
		if($wpshop->error_count()>0) {
			$reponse = $wpshop->show_messages();
		}



		$reponse = array('status' => $status, 'reponse' => $reponse, 'url' => $cart_url);

		echo json_encode($reponse);
		die();
	}
	add_action('wp_ajax_wpshop_save_customer_account', 'wpshop_ajax_save_customer_account');
	add_action('wp_ajax_nopriv_wpshop_save_customer_account', 'wpshop_ajax_save_customer_account');

	function wpshop_ajax_order_customer_adress_load() {
		global $wpshop_account;
		global $wpdb;
		check_ajax_referer( 'wpshop_order_customer_adress_load', 'wpshop_ajax_nonce' );
		$current_customer_id = !empty( $_REQUEST['customer_id'] ) ? (int)$_REQUEST['customer_id'] : 0;
		$order_id = !empty( $_REQUEST['order_id'] ) ? (int)$_REQUEST['order_id'] : 0;

		$order_postmeta = get_post_meta ($order_id, '_order_postmeta', true);
		$order_infos_postmeta = get_post_meta ($order_id, '_order_info', true);

		if ( !empty($order_postmeta) && !empty($order_postmeta['order_status']) && in_array($order_postmeta['order_status'], array('completed', 'shipped', 'refunded')) ) {
			/** Billing address display **/
			$tpl_component['ADDRESS_COMBOBOX'] = '';
			$tpl_component['ADDRESS_BUTTONS'] = '';
			$tpl_component['CUSTOMER_ADDRESS_TYPE_TITLE'] = __('Billing address', 'wpshop');
			$tpl_component['ADDRESS_TYPE'] = 'billing_address';
			$address_fields = wps_address::get_addresss_form_fields_by_type($order_infos_postmeta['billing']['id']);
			$tpl_component['CUSTOMER_ADDRESS_CONTENT'] = wpshop_account::display_an_address( $address_fields, $order_infos_postmeta['billing']['address'] );
			$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = wpshop_display::display_template_element('display_address_container', $tpl_component);
			$retour =  wpshop_display::display_template_element('display_addresses_by_type_container', $tpl_component);
			unset( $tpl_component );

			/** Shipping address display **/
			$retour .= '<div id="shipping_infos_bloc" class="wpshop_order_customer_container wpshop_order_customer_container_user_information">';
			$tpl_component['ADDRESS_COMBOBOX'] = '';
			$tpl_component['ADDRESS_BUTTONS'] = '';
			$tpl_component['CUSTOMER_ADDRESS_TYPE_TITLE'] = __('Shipping address', 'wpshop');
			$tpl_component['ADDRESS_TYPE'] = 'shipping_address';
			$address_fields = wps_address::get_addresss_form_fields_by_type($order_infos_postmeta['shipping']['id']);
			$tpl_component['CUSTOMER_ADDRESS_CONTENT'] = wpshop_account::display_an_address( $address_fields, $order_infos_postmeta['shipping']['address']);
			$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = wpshop_display::display_template_element('display_address_container', $tpl_component);
			$retour .=  wpshop_display::display_template_element('display_addresses_by_type_container', $tpl_component);
			unset( $tpl_component );
			$retour .= '</div>';
			$retour .= '<div class="wpshop_cls"></div>';
			$result = json_encode( array(true, $retour) );

		}

 		elseif ( !empty($order_postmeta) && !empty($order_postmeta['order_status']) && in_array($order_postmeta['order_status'], array('awaiting_payment', 'partially_paid'))) {
			$order_id = !empty( $_REQUEST['order_id'] ) ? (int) $_REQUEST['order_id'] : 0;
 			$order_info_postmeta = get_post_meta($order_id, '_order_info', true);

 			$billing_id_attribute_set = get_option('wpshop_billing_address');
 			$shipping_id_attribute_set = get_option('wpshop_shipping_address_choice');

 			$order_billing_address = ( !empty($order_info_postmeta) && !empty($order_info_postmeta['billing']) && !empty($order_info_postmeta['billing']['address']) ) ? $order_info_postmeta['billing']['address'] : array();
 			$order_shipping_address = ( !empty($order_info_postmeta) && !empty($order_info_postmeta['shipping']) && !empty($order_info_postmeta['shipping']['address']) ) ? $order_info_postmeta['shipping']['address'] : array();

 			$billing_form = $wpshop_account->display_form_fields( $billing_id_attribute_set['choice'], '', '', '', array(), array(), $order_billing_address );
 			if ( !empty($shipping_id_attribute_set) && !empty($shipping_id_attribute_set['activate']) ) {
 				$shipping_form = $wpshop_account->display_form_fields( $shipping_id_attribute_set['choice'], '', '', '', array(), array(), $order_shipping_address );
 			}

 			$result = json_encode( array(true, $billing_form, $shipping_form, $current_customer_id) );
 		}
		else {
				// Check the attribute set id of Billing Address
				$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE name = "' .__('Billing address', 'wpshop'). '"', '');
				$attribute_set_id = $wpdb->get_var($query);
				$billing_id_attribute_set = get_option('wpshop_billing_address');
				//Check the billing address id of the customer
				$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE post_author = ' .$current_customer_id. ' AND post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. '"', '');
				$post_addresses = $wpdb->get_results($query);
				$address_id = '';
				foreach ( $post_addresses as $post_address ) {
					$address_type = get_post_meta($post_address->ID, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY,true);
					if ( $address_type == $attribute_set_id ) {
						$address_id = $post_address->ID;
					}
				}
				$shipping_id_attribute_set = get_option('wpshop_shipping_address_choice');
				$shipping_form = '';
				if ( !empty($shipping_id_attribute_set) && !empty($shipping_id_attribute_set['activate']) ) {
					// Check the attribute set id of Shipping Address
					$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE name = "' .__('Shipping address', 'wpshop'). '"', '');
					$attribute_set_id = $wpdb->get_var($query);
					//Check the billing address id of the customer
					$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE post_author = ' .$current_customer_id. ' AND post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. '"', '');
					$post_addresses = $wpdb->get_results($query);
					$shipping_address_id = '';
					foreach ( $post_addresses as $post_address ) {
						$address_type = get_post_meta($post_address->ID, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY,true);
						if ( $address_type == $attribute_set_id ) {
							$shipping_address_id = $post_address->ID;
						}
					}
					$shipping_form = $wpshop_account->display_form_fields( $shipping_id_attribute_set['choice'], $shipping_address_id );
				}
				$result = json_encode( array(true, $wpshop_account->display_form_fields( $billing_id_attribute_set['choice'], $address_id ), $shipping_form, $current_customer_id) );
		}
		echo $result;
		die();
	}
	add_action('wp_ajax_order_customer_adress_load', 'wpshop_ajax_order_customer_adress_load');

	/**
	 * Add new entity element from anywhere
	 */
	function ajax_wpshop_add_entity() {
		global $wpdb;
		check_ajax_referer( 'wpshop_add_new_entity_ajax_nonce', 'wpshop_ajax_nonce' );

		$attribute = !empty( $_POST['attribute'] ) ? (array)$_POST['attribute'] : array();
		$attributes = array();
		/** Get the attribute to create	*/
		$attribute_to_reload = null;
		if ( !empty($attribute['new_value_creation']) && is_array( $attribute['new_value_creation'] ) ) {
			foreach ( $attribute['new_value_creation'] as $attribute_code=>$value) {
				$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = %s', $attribute_code);
				$attribute_def = $wpdb->get_row($query);
				if ( $value != "" ) {
					if ( $attribute_def->data_type_to_use == 'internal' ) {
						$attribute_default_value = unserialize($attribute_def->default_value);
						if ( $attribute_default_value['default_value'] == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ) {
							$user_id = wp_create_user( sanitize_user( $value ), wp_generate_password( 12, false ) );
							$query = $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type = %s AND post_author = %d", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $user_id );
							$attribute_option_id = $wpdb->get_var( $query );
						}
						else {
							$entity_args = array(
								'post_type' 	 => $attribute_default_value['default_value'],
								'post_title'  	 => $value,
								'post_author' 	 => (function_exists('is_user_logged_in') && is_user_logged_in() ? get_current_user_id() : 'NaN'),
								'comment_status' => 'closed'
							);
							$attribute_option_id = wp_insert_post($entity_args);
						}
					}
					else {
						$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status'=>'valid', 'creation_date'=>current_time('mysql', 0), 'position' => 1, 'attribute_id'=>$attribute_def->id, 'value'=>$value, 'label'=>$value) );
						$attribute_option_id = $wpdb->insert_id;
					}

					foreach ( $attribute as $attribute => $val) {
						foreach ($val as $k=>$v) {
							if ( $k == $attribute_code) {
								$attribute[$attribute][$k] = $attribute_option_id;
							}
						}
					}
				}
			}
		}
		/** Store send attribute into a new array for save purpose	*/
		if ( is_array( $attribute ) ) {
			foreach ( $attribute as $attribute_type => $attribute ) {
				foreach ( $attribute as $attribute_code => $attribute_value ) {
					if ( !isset( $attributes[$attribute_code] ) ) {
						$attributes[$attribute_code] = $attribute_value;
					}
				}
			}
		}

		$post_title = !empty( $_POST['wp_fields']['post_title'] ) ? sanitize_text_field( $_POST['wp_fields']['post_title'] ) : '';
		$entity_type = !empty( $_POST['entity_type'] ) ? sanitize_text_field( $_POST['entity_type'] ) : '';
		$attribute_set_id = !empty( $_POST['attribute_set_id'] ) ? (int) $_POST['attribute_set_id'] : 0;
		$type_of_form = !empty( $_POST['type_of_form'] ) ? sanitize_text_field( $_POST['type_of_form'] ) : '';

		/** Save the new entity into database */
		$result = wpshop_entities::create_new_entity( $entity_type, $post_title, '', $attributes, array('attribute_set_id' => $attribute_set_id) );
		$new_entity_id = $result[1];

		if ( !empty($new_entity_id) ) {
			/**	Save address for current entity	*/
			if ( !empty( $type_of_form ) && !empty( $attribute[$type_of_form] ) ) {
				global $wpshop_account;
				$result = wps_address::wps_address( $type_of_form );
				update_post_meta ($new_entity_id, '_wpshop_attached_address', $result['current_id']);
			}

			/** Make price calculation if entity is a product	*/
			if ( $entity_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
				$wpshop_prices_attribute = unserialize(WPSHOP_ATTRIBUTE_PRICES);
				$calculate_price = false;
				foreach( $wpshop_prices_attribute as $attribute_price_code ){
					if ( array_key_exists($attribute_price_code, $attributes) ) {
						$calculate_price = true;
					}
				}
				if ( $calculate_price ) {
					wpshop_products::calculate_price($new_entity_id);
				}
			}

			/** Add picture if a file has been send	*/
			if ( !empty($_FILES) ) {
				$wp_upload_dir = wp_upload_dir();
				$final_dir = $wp_upload_dir['path'] . '/';
				if ( !is_dir($final_dir) ) {
					mkdir($final_dir, 0755, true);
				}

				foreach ( $_FILES as $file ) {
					$tmp_name = $file['tmp_name']['post_thumbnail'];
					$name = $file['name']['post_thumbnail'];

					$filename = $final_dir . $name;
					@move_uploaded_file($tmp_name, $filename);

					$wp_filetype = wp_check_filetype(basename($filename), null );
					$attachment = array(
						'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $filename ),
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => preg_replace( '/\.[^.]+$/', '', basename($filename) ),
						'post_content' => '',
						'post_status' => 'inherit'
					);
					$attach_id = wp_insert_attachment( $attachment, $filename, $new_entity_id );
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id, $attach_data );
					add_post_meta($new_entity_id, '_thumbnail_id', $attach_id, true);
				}
			}

			echo json_encode( array( true,  __('Element has been saved', 'wpshop'), $attribute_to_reload, $new_entity_id) );
		}
		else {
			echo json_encode( array(false, __('An error occured while adding your element', 'wpshop')) );
		}

		die();
	}
	add_action('wp_ajax_wpshop_quick_add_entity', 'ajax_wpshop_add_entity');
// 	add_action('wp_ajax_nopriv_wpshop_quick_add_entity', 'ajax_wpshop_add_entity');

	function ajax_wpshop_reload_attribute_for_quick_add() {
		check_ajax_referer( 'ajax_wpshop_reload_attribute_for_quick_add' );
		$output = '';
		$attribute_to_reload = !empty( $_POST['attribute_to_reload'] ) ? (array) $_POST['attribute_to_reload'] : array();
		if ( !empty($attribute_to_reload) ) {
			foreach ( $attribute_to_reload as $attribute_code ) {
				$attr_field = wpshop_attributes::display_attribute( $attribute_code, 'frontend' );
				$output[$attribute_code]['result'] = $attr_field['field_definition']['output'] . $attr_field['field_definition']['options'];
			}
		}
		echo json_encode( array($output) );
		die();
	}
	add_action('wp_ajax_reload_attribute_for_quick_add', 'ajax_wpshop_reload_attribute_for_quick_add');

	function ajax_wpshop_change_address() {
		check_ajax_referer( 'ajax_wpshop_change_address' );
		$address_id = ( !empty($_POST['address_id']) ? (int) $_POST['address_id'] : null);
		$address_type = ( !empty($_POST['address_type']) ? sanitize_text_field($_POST['address_type']) : null);
		$is_allowed_destination  = true;
		if ( !empty($address_id) && !empty($address_type) ) {
			//Check if it's an allowed address for shipping
			$checkout_payment_button = '';
			$cart_type = (!empty($_SESSION['cart']['cart_type']) && sanitize_text_field($_SESSION['cart']['cart_type'])=='quotation') ? 'quotation' : 'cart';
			$is_allowed_destination = true;//wpshop_shipping_configuration::is_allowed_country ( $address_id );
			if ( $is_allowed_destination ) {
				$available_payement_method = wpshop_payment::display_payment_methods_choice_form(0, $cart_type);
				//if(!empty($available_payement_method[1]['paypal']) || !empty($available_payement_method[1]['banktransfer']) || !empty($available_payement_method[1]['checks']) || WPSHOP_PAYMENT_METHOD_CIC || !empty($available_payement_method[1]['cic']) || ($cart_type == 'quotation')) {
				if ( !empty($available_payement_method[0]) ) {
					if ( $cart_type=='quotation' ) {
						$checkout_payment_button = wpshop_display::display_template_element('wpshop_checkout_page_quotation_validation_button', array() );
					}
					else {
						$checkout_payment_button = wpshop_display::display_template_element('wpshop_checkout_page_validation_button', array() );
					}
				}
			}
			else {
				$checkout_payment_button = wpshop_display::display_template_element('wpshop_checkout_page_impossible_to_order', array());
			}
			if( $address_type == 'billing_address') {
				$billing_option = get_option( 'wpshop_billing_address' );
				$address_option = $billing_option['choice'];
			}
			else {
				$shipping_address_option = get_option('wpshop_shipping_address_choice');
				$address_option = $shipping_address_option['choice'];
			}
			$add = wps_address::get_addresss_form_fields_by_type($address_option);
			$address_infos = get_post_meta($address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
			$retour = wpshop_account::display_an_address ( $add, $address_infos, $address_id);

			$_SESSION[$address_type] = $address_id;

			$edit_link = '<a href="' .get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))) . (strpos(get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))), '?')===false ? '?' : '&') . 'action=editAddress&amp;id='.$address_id.'" title="' .__('Edit', 'wpshop'). '">' .__('Edit', 'wpshop'). '</a>';
			$result = json_encode( array(true, $retour, $edit_link, $is_allowed_destination, $checkout_payment_button) );
		}
		else {
			$result = json_encode( array(false, 'missing_informations') );
		}
		echo $result;

		die();
	}
	add_action('wp_ajax_change_address', 'ajax_wpshop_change_address');
	add_action('wp_ajax_nopriv_change_address', 'ajax_wpshop_change_address');

	function ajax_wpshop_load_create_new_customer_interface() {
		check_ajax_referer( 'ajax_wpshop_load_create_new_customer_interface' );
		$billing_address_option = get_option('wpshop_billing_address');
		$shipping_address_option = get_option('wpshop_shipping_address_choice');

		$tpl_component = array();
		$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
		if ( !empty($billing_address) ) {
			echo wpshop_account::get_addresses_by_type( $billing_address, __('Billing address', 'wpshop'), array('only_display' => 'yes'));
		}
		$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] = wpshop_account::display_form_fields($billing_address_option['choice'], '', 'first');

		if ( $shipping_address_option['activate'] ) {
			$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] .= '<p class="formField"><label><input type="checkbox" name="shiptobilling" id="shiptobilling_checkbox" checked="checked" /> '.__('Use as shipping information','wpshop').'</label></p><br/>';
			$display = 'display:none;';
			$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] .= '<div id="shipping_infos_bloc" style="'.$display.'">';
			$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] .= wpshop_account::display_form_fields($shipping_address_option['choice'], '', 'first');
			$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] .= '</div><br/>';
		}

		$tpl_component['CUSTOMER_ADDRESSES_FORM_BUTTONS'] = '<p class="formField"><input type="submit" name="submitbillingAndShippingInfo" id="submitbillingAndShippingInfo" value="' . __('Save','wpshop') . '" /></p>';
		$output = wpshop_display::display_template_element('wpshop_customer_addresses_form_admin', $tpl_component, array(), 'admin');
		unset($tpl_component);
		$result = json_encode(array(true, $output));
		echo $result;
		die();
	}
	add_action('wp_ajax_load_create_new_customer_interface', 'ajax_wpshop_load_create_new_customer_interface');

	function ajax_wpshop_create_new_customer() {
		check_ajax_referer( 'ajax_wpshop_create_new_customer' );
		$result = '';
		$billing_address = !empty($_REQUEST['billing_address']) ? sanitize_key($_REQUEST['billing_address']) : '';
		$shipping_address = !empty($_REQUEST['shipping_address']) ? sanitize_key($_REQUEST['shipping_address']) : '';
		$shiptobilling = !empty($_REQUEST['shiptobilling']) ? sanitize_key($_REQUEST['shiptobilling']) : null;
		$username = !empty($_POST['attribute'][$billing_address]['varchar']['address_user_email']) ? sanitize_text_field($_POST['attribute'][$billing_address]['varchar']['address_user_email']) : null;
		if ( $username != null ) {
			/** Crerate the new customer user account */
			$password = wp_generate_password( $length=12, $include_standard_special_chars=false );
			$email = $username;
			if ( !empty($username) && !username_exists($username) && !empty($email) && !email_exists($email) ) {
				$user_id = wp_create_user( $username, $password, $email );
				// @TODO : REQUEST
				// $_REQUEST['user']['customer_id'] = $user_id;
				/** Save addresses */
				$billing_set_infos = get_option('wpshop_billing_address');
				$shipping_set_infos = get_option('wpshop_shipping_address_choice');
				/** If it's same addresses for Shipping and Billing */
				if (isset($shiptobilling) && $shiptobilling == "on") {
					wpshop_account::same_billing_and_shipping_address($billing_address, $shipping_address);
				}

				if ( !empty($billing_address) ) {
					wps_address::save_address_infos( $billing_address );
				}
				if( !empty($shipping_address ) ) {
					wps_address::save_address_infos( $shipping_address);
				}
				$result = json_encode( array(true, __('Customer created', 'wpshop'), $user_id) );
			}
			else {
				$result = json_encode( array(false, __('A customer account is already created with this email address', 'wpshop')) );
			}

		}
		else {
			$result = json_encode( array(false, __('An email address is required', 'wpshop')) );
		}
		echo $result;
		die();
	}
	add_action('wp_ajax_create_new_customer', 'ajax_wpshop_create_new_customer');

	/**
	 * Send a message to customer
	 */
	function ajax_wpshop_send_message_by_type () {
		check_ajax_referer( 'ajax_wpshop_send_message_by_type' );
		global $wpdb;
		$result = array();
		$message_type_id = ( !empty( $_POST['message_type_id'])) ? (int) $_POST['message_type_id'] : null;
		$customer_id = ( !empty( $_POST['customer_user_id'])) ? (int) $_POST['customer_user_id'] : null;
		$model_name = ( !empty( $_POST['message_model_name'])) ? sanitize_text_field($_POST['message_model_name']) : null;
		$order_id = ( !empty( $_POST['order_id'])) ? (int) $_POST['order_id'] : null;
		if ( !empty($customer_id) && !empty($message_type_id) && !empty($model_name)) {
			$order_post_meta = get_post_meta($order_id, '_order_postmeta', true);
			$receiver_infos = get_userdata( $customer_id );
			$email = $receiver_infos->user_email;
			$first_name = get_user_meta($customer_id, 'first_name', true);
			$last_name = get_user_meta($customer_id, 'last_name', true);
			$wps_message = new wps_message_ctr();
			$wps_message->wpshop_prepared_email( $email, $model_name, array('order_id' => $order_id, 'order_key' => ( ( !empty($order_post_meta) && !empty($order_post_meta['order_key']) ) ? $order_post_meta['order_key'] : '' ), 'order_date' => ( ( !empty($order_post_meta) && !empty($order_post_meta['order_date']) ) ? $order_post_meta['order_date'] : '' ),'customer_first_name' => $first_name, 'customer_last_name' => $last_name) );
			$result = array('status' => true, 'response' => $wps_message->get_historic_message_by_type($message_type_id) );
		}
		else {
			$result = array('status' => false, 'response' => __('An error occured', 'wpshop') );
		}
		echo json_encode($result);
		die();
	}
	add_action('wp_ajax_send_message_by_type', 'ajax_wpshop_send_message_by_type');


	function ajax_wpshop_upload_downloadable_file_action() {
		check_ajax_referer( 'ajax_wpshop_upload_downloadable_file_action' );
		$result = '';
		$element_identifier = ( !empty( $_POST ) && !empty( $_POST[ 'element_identifier' ] ) && is_int( (int)$_POST[ 'element_identifier' ] ) ) ? (int)$_POST[ 'element_identifier' ] : 0;
		$wpshop_uploaded_file = ( !empty( $_FILES ) && !empty( $_FILES[ 'wpshop_file' ] ) && is_array( (array)$_FILES[ 'wpshop_file' ] ) ) ? (array)$_FILES[ 'wpshop_file' ] : array();

		if ( !empty( $wpshop_uploaded_file ) && !empty( $element_identifier ) ) {
			if(!is_dir(WPSHOP_UPLOAD_DIR)){
				mkdir(WPSHOP_UPLOAD_DIR, 0755, true);
			}
			$file = $wpshop_uploaded_file;
			$tmp_name = $file['tmp_name'];
			$name = sanitize_file_name( current_time( 'mysql', 0 ).'__'.$file["name"] );
			move_uploaded_file($tmp_name, WPSHOP_UPLOAD_DIR.$name);

			$n = WPSHOP_UPLOAD_URL.'/'.$name;
			update_post_meta( $element_identifier, 'attribute_option_is_downloadable_', array('file_url' => $n));
			$result = '<a href="' .$n. '" target="_blank" download>' .$name. '</a>';
		}
		else {
			$result = '';
		}
		echo $result;
		die();
	}
	add_action('wp_ajax_upload_downloadable_file_action', 'ajax_wpshop_upload_downloadable_file_action');

	function ajax_wpshop_fill_the_downloadable_dialog() {
		$product_id = ( !empty( $_POST ) && !empty( $_POST[ 'product_identifier' ] ) ) ? (int)$_POST[ 'product_identifier' ] : 0;
		check_ajax_referer( "ajax_wpshop_fill_the_downloadable_dialog".$product_id );
		ajax_wpshop_fill_the_downloadable_dialog_exec($product_id);
	}
	function ajax_wpshop_fill_the_downloadable_dialog_unnonced() {
		$product_id = ( !empty( $_POST ) && !empty( $_POST[ 'product_identifier' ] ) ) ? (int)$_POST[ 'product_identifier' ] : 0;
		ajax_wpshop_fill_the_downloadable_dialog_exec($product_id);
	}
	function ajax_wpshop_fill_the_downloadable_dialog_exec( $product_id ) {
		$output  = '<form method="post" action="' .admin_url('admin-ajax.php') .'" id="upload_downloadable_file" enctype="multipart/form-data" >';
		$output .= '<p class="formField"><label for="wpshop_file">' .__('Choose your file to send', 'wpshop'). '</label><input type="file" name="wpshop_file" /></p>';
		$output .= '<input type="hidden" name="action" value="upload_downloadable_file_action" />';
		$output .= '<input type="hidden" name="element_identifier" id="element_identifier" value="' .esc_attr( $product_id). '" />';
		$output .= wp_nonce_field( 'ajax_wpshop_upload_downloadable_file_action', '_wpnonce', true, false );
		$output .= '<p class="formField"><a id="send_downloadable_file_button" class="wps-bton-first-mini-rounded">' .__('Send your file', 'wpshop'). '</a></p>';
		$output .= '</form>';
		$output .='<script type="text/javascript">jQuery("#upload_downloadable_file").ajaxForm({
		beforeSubmit : function() { },success: function(response) {
		jQuery(".is_downloadable_statut_'.$product_id.'").html( response );
		jQuery(".send_downloadable_file_dialog").dialog("close");
		}});</script>';
		$output = apply_filters( 'wpshop_download_file_dialog', $output, $product_id );

		$response = array( 'status' => true, 'response' => $output);
		echo json_encode( $response );
		die();
	}
	add_action('wp_ajax_fill_the_downloadable_dialog', 'ajax_wpshop_fill_the_downloadable_dialog');
	add_action('wp_ajax_fill_the_downloadable_dialog_unnonced', 'ajax_wpshop_fill_the_downloadable_dialog_unnonced');

	function ajax_wpshop_show_downloadable_interface_in_admin() {
		$post_id = !empty( $_POST ) && !empty( $_POST[ 'post_id' ] ) && is_int( (int)$_POST[ 'post_id' ] ) ? (int)$_POST[ 'post_id' ] : 0;
		check_ajax_referer( 'ajax_wpshop_show_downloadable_interface_in_admin'.$post_id );
		global $wpdb;
		$status = false;
		$selected_value = ( !empty($_POST['selected_value']) ) ? sanitize_text_field( $_POST['selected_value'] ) : '';
		$query = $wpdb->prepare( 'SELECT label FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE id = %d', $selected_value);
		$value = $wpdb->get_var( $query );

		if ( !empty($value) && __( $value, 'wpshop') == __('Yes', 'wpshop') ) {
			$status = true;
		}
		$response = array( 'status' => $status );
		echo json_encode( $response );
		die();
	}
	add_action( 'wp_ajax_show_downloadable_interface_in_admin', 'ajax_wpshop_show_downloadable_interface_in_admin');

	function ajax_wpshop_restart_the_order() {
		check_ajax_referer( 'ajax_wpshop_restart_the_order' );

		global $wpshop_cart, $wpdb;
		$status = $add_to_cart_checking = $manage_stock_checking_bool = false;
		$add_to_cart_checking_message = '';
		$result = __('Error, you cannot restart this order', 'wpshop');
		$order_id = ( !empty($_POST['order_id']) ) ? (int) $_POST['order_id'] : null;
		$is_make_order_again = ( !empty($_POST['make_order_again']) ) ? sanitize_text_field( $_POST['make_order_again'] ) : null;
		if( !empty( $order_id ) ) {
			$order_meta = get_post_meta($order_id, '_order_postmeta', true);
			$_SESSION['cart'] = array();
			$_SESSION['cart']['order_items'] = array();
			$wpshop_cart_type = $order_meta['cart_type'];

			if ( !empty($order_meta) && !empty( $order_meta['order_items']) ) {
				$wpshop_cart_type = $order_meta['cart_type'];
				foreach( $order_meta['order_items'] as $item_key => $item ) {
					$item_meta = get_post_meta( $item['item_id'], '_wpshop_product_metadata', true );
					$stock =  $item_meta['product_stock'];
					$qty = $item['item_qty'];
					$item_option = get_post_meta( $item['item_id'], '_wpshop_product_options', true );
					if( !empty($item_meta['manage_stock']) ) {
						$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $item_meta['manage_stock']);
						$manage_stock_checking = $wpdb->get_var( $query );
						if( !empty($manage_stock_checking) && strtolower( __( $manage_stock_checking, 'wpshop') ) == strtolower( __( 'Yes', 'wpshop') )  ) {
							$manage_stock_checking_bool = true;
						}
					}
					else {
						if( get_post_type($item['item_id']) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
							$parent_product = wpshop_products::get_parent_variation( $item['item_id'] );
							if( !empty($parent_product) && !empty($parent_product['parent_post_meta']) ) {
								$parent_metadata = $parent_product['parent_post_meta'];
								if( !empty($parent_product['parent_post_meta']['manage_stock']) ) {
									$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $parent_product['parent_post_meta']['manage_stock']);
									$manage_stock_checking = $wpdb->get_var( $query );
									if( !empty($manage_stock_checking) && strtolower( __( $manage_stock_checking, 'wpshop') ) == strtolower( __( 'Yes', 'wpshop') )  ) {
										$manage_stock_checking_bool = true;
										$stock = $parent_product['parent_post_meta']['product_stock'];
									}
								}
							}
						}
					}




					/** Checking stock **/
					if ( empty($item_meta['manage_stock']) || ( !empty($item_meta['manage_stock']) && !$manage_stock_checking_bool )|| ( !empty($item_meta['manage_stock']) && $manage_stock_checking_bool && $stock >= $qty ) ) {
						$_SESSION['cart']['order_items'][ $item_key ] = $item;
					}
					else {
						$add_to_cart_checking = true;
						$add_to_cart_checking_message = __('Some products cannot be added to cart because they are out of stock', 'wpshop');
					}
				}

				$wps_cart_ctr = new wps_cart();
				$order = $wps_cart_ctr->calcul_cart_information( array() );
				$order['cart_type'] = $wpshop_cart_type;
				$wps_cart_ctr->store_cart_in_session( $order );
			}

			if ( empty($is_make_order_again) ) {
				$_SESSION['order_id'] = $order_id;
			}
			$status = true;

			$result = get_permalink( get_option('wpshop_cart_page_id') );
		}

		$response = array( 'status' => $status, 'response' => $result, 'add_to_cart_checking' => $add_to_cart_checking, 'add_to_cart_checking_message' => $add_to_cart_checking_message);
		echo json_encode( $response );
		die();
	}
	add_action( 'wp_ajax_restart_the_order', 'ajax_wpshop_restart_the_order');



	function wps_hide_notice_messages() {
		check_ajax_referer( 'wps_hide_notice_messages' );

		$status = false;
		$indicator = !empty($_POST['indicator'] ) ? sanitize_text_field($_POST['indicator']) : null;
		if ( !empty($indicator) ) {
			$user_id = get_current_user_id();
			$hide_notice_meta = get_user_meta( $user_id, '_wps_hide_notice_messages_indicator', true);
			$hide_notice_meta = !empty($hide_notice_meta) ? $hide_notice_meta : array();
			$indicators = explode(',', $indicator);
			if ( !empty($indicators) && is_array($indicators) ) {
				foreach( $indicators as $i ) {
					if ( !empty($i) ) {
						$hide_notice_meta[$i] = true;
					}
				}
			}
			update_user_meta($user_id, '_wps_hide_notice_messages_indicator', $hide_notice_meta);
			$status = true;
		}
		$response = array('status' => $status);
		echo json_encode( $response );
		die();
	}
	add_action('wp_ajax_wps_hide_notice_messages', 'wps_hide_notice_messages');



	function wps_update_products_prices() {
		check_ajax_referer( 'wps_update_products_prices' );
		$action = wpshop_prices::mass_update_prices();
		$response = array( 'status' => $action[0], 'response' => $action[1] );
		echo json_encode( $response );
		die();
	}
	add_action( 'wp_ajax_update_products_prices', 'wps_update_products_prices' );

	/** Quotation is payable by customer **/
	function wps_quotation_is_payable_by_customer() {
		check_ajax_referer( 'wps_quotation_is_payable_by_customer' );
		global $wpdb;
		$status = false;
		$response = '';
		$order_id = ( !empty($_POST['order_id']) ) ? intval( $_POST['order_id'] ) : null;
		if( isset($order_id) ) {
			$order_metadata = get_post_meta( $order_id, '_order_postmeta', true );
			if( isset( $order_metadata['pay_quotation'] ) ) {
				unset( $order_metadata['pay_quotation'] );
			} else {
				$order_metadata['pay_quotation'] = 'on';
			}
			update_post_meta( $order_id, '_order_postmeta', $order_metadata );

			$response = wpshop_orders::display_customer_pay_quotation( isset( $order_metadata['pay_quotation'] ), $order_id );
			$status = true;
		} else {
			$response = __( 'An error was occured, no Order ID defined', 'wpshop' );
		}
		echo json_encode( array( 'status' => $status, 'response' => $response) );
		die();
	}
	add_action( 'wp_ajax_wps_quotation_is_payable_by_customer', 'wps_quotation_is_payable_by_customer' );

	/** Send a direct payment Link **/
	function wps_send_direct_payment_link() {
		check_ajax_referer( 'wps_send_direct_payment_link' );
		global $wpdb;
		$status = false; $response = '';
		$order_id = ( !empty($_POST['order_id']) ) ? (int) $_POST['order_id'] : null;
		if( !empty($order_id) ) {
			/** Get the customer **/
			$order_metadata = get_post_meta( $order_id, '_order_postmeta', true );
			if( !empty($order_metadata) && !empty($order_metadata['customer_id']) && !empty($order_metadata['order_status']) && $order_metadata['order_status'] == 'awaiting_payment' ) {
				$user_infos = get_userdata( $order_metadata['customer_id'] );

				$first_name =  get_user_meta($user_infos->ID, 'first_name', true);
				$last_name =  get_user_meta($user_infos->ID, 'last_name', true);


				/** Create an activation key **/
				$token = wps_orders_ctr::wps_token_order_customer( $order_id );
				/*$token = wp_generate_password(20, false);
				$wpdb->update($wpdb->users, array('user_activation_key' => $token), array('user_login' => $user_infos->user_login) );*/

				$permalink_option = get_option( 'permalink_structure' );
				$link = '<a href="' . wpshop_checkout::wps_direct_payment_link_url( $order_id ) . '">' .__( 'Click here to pay your order', 'wpshop' ). '</a>';

				/** Send message **/
				$wps_message = new wps_message_ctr();
				$wps_message->wpshop_prepared_email($user_infos->user_email,
				'WPSHOP_DIRECT_PAYMENT_LINK_MESSAGE',
				array( 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'direct_payment_link' => $link, 'order_content' => '' )
				);

				$response = __( 'Direct payment link has been send', 'wpshop' );
				$status = true;
			}
			else {
				$response = __( 'An error was occured', 'wpshop' );
			}
		}
		else {
			$response = __( 'An error was occured, no Order ID defined', 'wpshop' );
		}
		echo json_encode( array( 'status' => $status, 'response' => $response) );
		die();
	}
	add_action( 'wp_ajax_wps_send_direct_payment_link', 'wps_send_direct_payment_link' );

	function wps_mass_action_product() {
		check_ajax_referer( 'wps_mass_action_product' );
		$mass_actions_tools_page = '';
		$default_attributes = array( 'product_stock', 'barcode', 'product_price', 'special_price', );

		/**	Copy an attribute content to another	*/
		$attribute_list = wpshop_attributes::getElement(wpshop_entities::get_entity_identifier_from_code( WPSHOP_PRODUCT ), "'valid'", 'entity_id', true);
		if ( !empty( $attribute_list ) ) {
			$mass_actions_tools_page .= '
			<form action="' . admin_url( "admin-ajax.php" ) . '" method="POST" id="wps_mass_action_on_entity_form" >
				<input type="hidden" value="wps_mass_action_on_entity_launch" name="action" />
				<!--<div>' . __( 'Choose attribute to display into list', 'wpshop' ) . '
					<ul>';
			foreach ( $attribute_list as $attribute ) {
				$mass_actions_tools_page .= '
						<li style="display:inline-block; width:10%;" ><input type="checkbox"' . checked( in_array( $attribute->code, $default_attributes ), true, false ) . ' name="wps_tools_mass_action_on_element[]" value="' . $attribute->id . '" />' . __( $attribute->frontend_label, 'wpshop' ) . '</li>';
			}
			$mass_actions_tools_page .= '
					</ul>
					<button>' . __( 'Voir la liste des produits', 'wpshop' ) . '</button> -->
				</div>
			</form>
			<div id="wps_entity_list" >' . wps_mass_action_on_entity_launch() . '</div>
			<script type="text/javascript" >
				jQuery( document ).ready( function(){
					jQuery( "#wps_mass_action_on_entity_form" ).ajaxForm( function( response ){
						jQuery( "#wps_entity_list" ).html( response );
					});
				});
			</script>';
		}

		wp_die( $mass_actions_tools_page );
	}
	add_action( 'wp_ajax_wps_mass_action_product', 'wps_mass_action_product' );

	function wps_put_history_back() {
		check_ajax_referer( 'wps_put_history_back' );

		global $wpdb;
		$upload_dir = wp_upload_dir();
		$log_dir = $upload_dir[ 'basedir' ] . '/wps_repair/';
		wp_mkdir_p( $log_dir );
		$value_to_take = !empty($_POST[ 'value_to_take' ]) ? (array) $_POST[ 'value_to_take' ] : array();
		foreach ( $value_to_take as $product_id => $product_element ) {
			foreach( $product_element as $attribute_id => $attribute_value_to_take ){
				$query = $wpdb->prepare( "SELECT value, value_type FROM wp_wpshop__attribute_value__histo WHERE value_id = %d", $attribute_value_to_take );
				$the_new_value = $wpdb->get_row( $query );
				if ( 6 == $attribute_id ) {
					$query = $wpdb->prepare( "SELECT OPT.value, HISTO.value AS rate_id FROM wp_wpshop__attribute_value_options AS OPT INNER JOIN wp_wpshop__attribute_value__histo AS HISTO ON ( ( HISTO.attribute_id = OPT.attribute_id ) AND ( HISTO.value = OPT.id ) ) WHERE HISTO.attribute_id = 29 AND HISTO.entity_id = %d ORDER BY HISTO.creation_date_value DESC LIMIT 1", $product_id );
					$the_tax = $wpdb->get_row( $query );
					$pttc = number_format( $the_new_value->value, 5, ".", " " );
					$ht = number_format( $pttc / ( 1 + ( $the_tax->value / 100 ) ), 5, ".", " " );
					$tax_amount = number_format( $pttc - $ht, 5, ".", " " );
					$wpdb->delete( 'wp_wpshop__attribute_value_decimal', array( "entity_id" => $product_id, "attribute_id" => 6 ) );
					$wpdb->delete( 'wp_wpshop__attribute_value_decimal', array( "entity_id" => $product_id, "attribute_id" => 28 ) );
					$wpdb->delete( 'wp_wpshop__attribute_value_integer', array( "entity_id" => $product_id, "attribute_id" => 29 ) );
					$wpdb->delete( 'wp_wpshop__attribute_value_decimal', array( "entity_id" => $product_id, "attribute_id" => 30 ) );

					$common_datas = array(
						'value_id' => null,
						'entity_type_id' => 5,
						'entity_id' => $product_id,
						'unit_id' => null,
						'user_id' => 1,
						'creation_date_value' => current_time( "mysql", 0 ),
						'language' => 'fr_FR',
					);

					$content_to_write =  "
" . mysql2date( "d/m/Y H:i", current_time( 'mysql', 0 ), true ) . ' - ' . $product_id . ' - ' . serialize( $common_datas ) . ' - ' . serialize( array( 'ttc' => $pttc,  'ht' => $ht,  'tax_rate_id' => $the_tax->rate_id,  'tax_amount' => $tax_amount,  ) ). '';
					$fp = fopen( $log_dir . 'correction_prix.txt', 'a' );
					fwrite( $fp, $content_to_write );
					fclose( $fp );

					$wpdb->insert( 'wp_wpshop__attribute_value_decimal', array_merge( $common_datas, array( 'attribute_id' => 6, 'value' => $pttc ) ) );
					$wpdb->insert( 'wp_wpshop__attribute_value_decimal', array_merge( $common_datas, array( 'attribute_id' => 28, 'value' => $ht ) ) );
					$wpdb->insert( 'wp_wpshop__attribute_value_integer', array_merge( $common_datas, array( 'attribute_id' => 29, 'value' => $the_tax->rate_id ) ) );
					$wpdb->insert( 'wp_wpshop__attribute_value_decimal', array_merge( $common_datas, array( 'attribute_id' => 30, 'value' => $tax_amount ) ) );
				}
				else {
					$common_datas = array(
						'value_id' => null,
						'entity_type_id' => 5,
						'entity_id' => $product_id,
						'unit_id' => null,
						'user_id' => 1,
						'creation_date_value' => current_time( "mysql", 0 ),
						'language' => 'fr_FR',
					);
					$wpdb->delete( $the_new_value->value_type, array( "entity_id" => $product_id, "attribute_id" => $attribute_id ) );
					$wpdb->insert( $the_new_value->value_type, array_merge( $common_datas, array( 'attribute_id' => $attribute_id, 'value' => trim( $the_new_value->value ) ) ) );

					$content_to_write =  "
" . mysql2date( "d/m/Y H:i", current_time( 'mysql', 0 ), true ) . ' - ' . $product_id . ' - ' . serialize( $common_datas ) . ' - ' . serialize( array( 'attribute_id' => $attribute_id, 'value' => trim( $the_new_value->value ),  ) ). '';
					$fp = fopen( $log_dir . 'correction_attribut.txt', 'a' );
					fwrite( $fp, $content_to_write );
					fclose( $fp );
				}
			}
		}
		wp_die();
	}
	add_action( 'wp_ajax_wps_put_history_back', 'wps_put_history_back' );

	function wps_mass_action_on_entity_launch() {
		global $wpdb;
		$response = '';
		$element_to_output = array();

		$attributes_to_test = array( 'decimal' => '6,36', 'varchar' => '12', 'integer' => '85', 'text' => '120, 118' );

		$decimal_attribute = $attributes_to_test['decimal']; //,30,28
		$query = $wpdb->prepare( "
				SELECT P.ID, P.post_title,
					D.value_id, H.value_id, D.attribute_id, D.entity_id, H.creation_date, D.creation_date_value, D.value AS current_value, H.value AS last_history_value
				FROM {$wpdb->posts} AS P
					INNER JOIN wp_wpshop__attribute_value_decimal AS D ON ( D.entity_id = P.ID )
					INNER JOIN wp_wpshop__attribute_value__histo AS H ON (( H.entity_type_id = D.entity_type_id ) AND ( H.attribute_id = D.attribute_id ) AND ( H.entity_id = D.entity_id ))
				WHERE ( (P.post_type = %s ) OR (P.post_type = %s ) )
					AND post_status = %s
					AND D.attribute_id IN ( " . $decimal_attribute . " )
					AND D.value = 0.00000
					AND H.value != 0.00000
					AND H.value_type = 'wp_wpshop__attribute_value_decimal'

				ORDER BY H.entity_id ASC, H.value_id DESC",
			array( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, "publish", )
		);
		 $list_of_element = $wpdb->get_results( $query );
		 if ( !empty( $list_of_element ) ) {
			foreach ( $list_of_element as $element ) {
				$element_to_output[ $element->ID ][ 'title' ] = $element->post_title;
				$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'current' ] = $element->current_value;
			 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value_id' ] = $element->value_id;
			 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value_date' ] = $element->creation_date;
			 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value' ] = $element->last_history_value;
			}
		}

		$attribute_list = $attributes_to_test['varchar'];
		$query = $wpdb->prepare( "
			SELECT P.ID, P.post_title,
				D.value_id, H.value_id, D.attribute_id, D.entity_id, H.creation_date, D.creation_date_value, D.value AS current_value, H.value AS last_history_value
			FROM {$wpdb->posts} AS P
			INNER JOIN wp_wpshop__attribute_value_varchar AS D ON ( D.entity_id = P.ID )
			INNER JOIN wp_wpshop__attribute_value__histo AS H ON (( H.entity_type_id = D.entity_type_id ) AND ( H.attribute_id = D.attribute_id ) AND ( H.entity_id = D.entity_id ))
			WHERE ( (P.post_type = %s ) OR (P.post_type = %s ) )
				AND post_status = %s
				AND D.attribute_id IN ( " . $attribute_list . " )
				AND ( D.value = '' OR D.value LIKE 'PDCT%%' )
				AND H.value != ''
				AND H.value_type = 'wp_wpshop__attribute_value_varchar'
			ORDER BY H.creation_date",
			array( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, "publish", )
		);
		$list_of_element = $wpdb->get_results( $query );
		if ( !empty( $list_of_element ) ) {
			foreach ( $list_of_element as $element ) {
			 	$element_to_output[ $element->ID ][ 'title' ] = $element->post_title;
			 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'current' ] = $element->current_value;
			 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value_id' ] = $element->value_id;
			 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value_date' ] = $element->creation_date;
			 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value' ] = $element->last_history_value;
			}
		}


		$attribute_list = $attributes_to_test['text'];
		$query = $wpdb->prepare( "
				SELECT P.ID, P.post_title,
				D.value_id, H.value_id, D.attribute_id, D.entity_id, H.creation_date, D.creation_date_value, D.value AS current_value, H.value AS last_history_value
				FROM {$wpdb->posts} AS P
				INNER JOIN wp_wpshop__attribute_value_text AS D ON ( D.entity_id = P.ID )
				INNER JOIN wp_wpshop__attribute_value__histo AS H ON (( H.entity_type_id = D.entity_type_id ) AND ( H.attribute_id = D.attribute_id ) AND ( H.entity_id = D.entity_id ))
				WHERE ( (P.post_type = %s ) OR (P.post_type = %s ) )
				AND post_status = %s
				AND D.attribute_id IN ( " . $attribute_list . " )
				AND ( D.value = '' )
				AND H.value != ''
				AND H.value_type = 'wp_wpshop__attribute_value_text'
			ORDER BY H.creation_date",
			array( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, "publish", )
		);
		$list_of_element = $wpdb->get_results( $query );
		if ( !empty( $list_of_element ) ) {
		foreach ( $list_of_element as $element ) {
		 	$element_to_output[ $element->ID ][ 'title' ] = $element->post_title;
		 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'current' ] = $element->current_value;
		 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value_id' ] = $element->value_id;
		 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value_date' ] = $element->creation_date;
		 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value' ] = $element->last_history_value;
		 	}
		}

		$attribute_list = $attributes_to_test['integer'];
		$query = $wpdb->prepare( "
				SELECT P.ID, P.post_title,
				D.value_id, H.value_id, D.attribute_id, D.entity_id, H.creation_date, D.creation_date_value, D.value AS current_value, H.value AS last_history_value
				FROM {$wpdb->posts} AS P
				INNER JOIN wp_wpshop__attribute_value_integer AS D ON ( D.entity_id = P.ID )
				INNER JOIN wp_wpshop__attribute_value__histo AS H ON (( H.entity_type_id = D.entity_type_id ) AND ( H.attribute_id = D.attribute_id ) AND ( H.entity_id = D.entity_id ))
				WHERE ( (P.post_type = %s ) OR (P.post_type = %s ) )
				AND post_status = %s
				AND D.attribute_id IN ( " . $attribute_list . " )
				AND ( D.value = '' OR D.value LIKE 'PDCT%%' )
				AND H.value != ''
				AND H.value_type = 'wp_wpshop__attribute_value_integer'
			ORDER BY H.creation_date",
			array( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, "publish", )
		);
							$list_of_element = $wpdb->get_results( $query );
							if ( !empty( $list_of_element ) ) {
							foreach ( $list_of_element as $element ) {
					 	$element_to_output[ $element->ID ][ 'title' ] = $element->post_title;
					 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'current' ] = $element->current_value;
					 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value_id' ] = $element->value_id;
					 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value_date' ] = $element->creation_date;
					 	$element_to_output[ $element->ID ][ 'content' ][ $element->attribute_id ][ $element->creation_date ][ 'histo' ][ 'value' ] = $element->last_history_value;
					 	}
							}

		$array_done = array();
		if ( !empty( $element_to_output ) ) {
			$lines = '';
			$done_header = array();


			/** Formate informations **/
			$formatted_datas = array();
			$controller_rows_array = array();
			foreach( $attributes_to_test as $attribute_to_test ) {
				$atts = explode( ',', $attribute_to_test );
				foreach( $atts as $att ) {
					$controller_rows_array[ $att ] = false;
				}
			}

			foreach(  $element_to_output as $element_id => $element_definition ) {
				$formatted_datas[$element_id]['title'] = $element_definition['title'];
				$formatted_datas[$element_id]['content'] = array();

				foreach( $attributes_to_test as $attribute_to_test ) {
					$atts = explode( ',', $attribute_to_test );
					foreach( $atts as $att ) {
						if( !empty($element_definition['content'][$att]) ) {
							$formatted_datas[$element_id]['content'][$att] = $element_definition['content'][$att];
							$controller_rows_array[ $att ] = true;
						}
						else {
							$formatted_datas[$element_id]['content'][$att] = array();
						}
					}
				}
			}

			foreach ( $formatted_datas as $element_id => $element_definition ) {
				if ( !in_array( $element_id, $array_done ) ) {
					$lines .= '<tr  style="border:1px solid black;"><td  style="border:1px solid black;" >#' . $element_id . ' - ' . $element_definition[ 'title' ] . '</td>';
					ksort( $element_definition[ 'content' ] );
					foreach ( $element_definition[ 'content' ] as $atribute_id => $value_on_date ) {
						if( $controller_rows_array[$atribute_id] ) {
							$current_attribute = wpshop_attributes::getElement( $atribute_id );
							if ( !in_array( $atribute_id, $done_header ) ) {
								$more_header .= '<td style="border:1px solid black;" >' . __( $current_attribute->frontend_label, 'wpshop' ) . '</td>';
								$done_header[] = $atribute_id;
							}
							$last_value = 'XXXXX';
							$counter_for_line = 0;
							$content_line = '';
							foreach ( $value_on_date as $date => $value ) {
								if ( $value[ 'histo' ][ 'value' ] != $last_value ) {
									$current_val = $value[ 'current' ];
									$old_val = $value[ 'histo' ][ 'value' ];
									// Test if attribute data Type is integer
									if ( $current_attribute->data_type == 'integer' ) {
										if( $current_attribute->data_type_to_use == 'internal' ) {
											$current_val = get_the_title($current_val);
											$old_val = get_the_title($old_val);
										}
										else {
											$query = $wpdb->prepare( 'SELECT label FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE id=%d', $old_val);
											$old_val = $wpdb->get_var( $query );
											$query = $wpdb->prepare( 'SELECT label FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE id=%d', $current_val);
											$current_val = $wpdb->get_var( $query );
										}
									}
									$content_line .= '<label ><input type="radio" name="value_to_take[' . $element_id . '][' . $atribute_id . ']" value="' . $value[ 'histo' ][ 'value_id' ] . '"' . checked( ( 0 == $counter_for_line ? true : false), true, false ) . '/>' . __( 'Current value', 'wpshop' ) . ' : ' .$current_val. ' / ' . __( 'Last value', 'wpshop' ) . ' : ' .$old_val. '</label><br/>';
									$last_value = $value[ 'histo' ][ 'value' ];
									$counter_for_line++;
								}
							}
							$lines .= '<td style="border:1px solid black;" >';
								$lines .= $content_line;
							$lines .= '</td>';
						}
					}
					$lines .= '</tr>';
					$array_done[ $element_id ];
				}
			}

			$response = '
				<form action="' . admin_url( 'admin-ajax.php' ) . '" method="POST" id="wps_put_histo_back" >
					<input type="text" name="action" value="wps_put_history_back" />
					' . wp_nonce_field( 'wps_put_history_back' ) . '
					<table style="border-collapse: collapse;border:1px solid black;" cellpadding="0" cellspacing="0" >
						<tr style="border:1px solid black;"><td style="border:1px solid black;" >' . __( 'Product', 'wpshop' ) . '</td>'.$more_header.'</tr>
						' . $lines . '
					</table>
				</form><script type="text/javascript" >
				jQuery( document ).ready( function(){
					jQuery( "#wps_put_histo_back" ).ajaxForm( function( response ){

					});
				});
			</script>';
		}

		return $response;
	}


	function wps_delete_picture_category() {
		check_ajax_referer( 'wps_delete_picture_category' );
		$status = false; $response = '';
		$cat_id = ( !empty($_POST['cat_id']) ) ? (int) $_POST['cat_id'] : null;
		if( !empty($cat_id) ) {
			// Get Category option
			$category_option = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $cat_id );
			if( !empty($category_option) && !empty($category_option['wpshop_category_picture']) ) {
				unset( $category_option['wpshop_category_picture'] );
				update_option( WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $cat_id, $category_option );
				$response = '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail_preview"/>';
				$status = true;
			}
			else {
				$response = __( 'Category options are not defined', 'wpshop');
			}
		}
		else {
			$response = __( 'Category ID is not defined', 'wpshop');
		}
		echo json_encode( array( 'status' => $status, 'response' => $response) );
		wp_die();
	}
	add_action( 'wp_ajax_wps_delete_picture_category', 'wps_delete_picture_category' );

	/* EAV */

	/* ATTRIBUTES SET */

	function saveNewAttributeSetSection() {
		check_ajax_referer( 'saveNewAttributeSetSection' );
		global $wpdb;
		$attributeSetSectionName = sanitize_text_field($_REQUEST['attributeSetSectionName']);
		$elementIdentifier = (int)$_REQUEST['elementIdentifier'];
		$attributeSetInfos = array();
		$attributeSetInfos['id'] = '';
		$attributeSetInfos['status'] = 'valid';
		$attributeSetInfos['attribute_set_id'] = $elementIdentifier;
		$query = $wpdb->prepare("SELECT MAX(position) + 1 AS LAST_GROUP_POSITION FROM ". WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id = %s", $elementIdentifier);
		$attributeSetInfos['position'] = $wpdb->get_var($query);
		$attributeSetInfos['creation_date'] = date('Y-m-d H:i:s');
		$attributeSetInfos['code'] = wpshop_tools::slugify($attributeSetSectionName, array('noAccent', 'noSpaces', 'lowerCase'));
		$attributeSetInfos['name'] = $attributeSetSectionName;

		$more_script='';
		$attributeSetSectionCreation = wpshop_database::save($attributeSetInfos, WPSHOP_DBT_ATTRIBUTE_GROUP);
		if ( $attributeSetSectionCreation == 'done' ) {
			$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_SUCCES_ICON . '\' alt=\'action_success\' class=\'wpshopPageMessage_Icon\' />' . __('New section has been created successfully', 'wpshop');
			$more_script = 'wpshop_go_to("#attribute_group_'.$wpdb->insert_id.'")';
		}
		else {
			$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_ERROR_ICON . '\' alt=\'action_error\' class=\'wpshopPageMessage_Icon\' />' . __('An error occured while saving new section', 'wpshop');
		}

		$script = '<script type="text/javascript" >wpshop(document).ready(function(){	jQuery("#wpshop_new_set_section_add").dialog("close");jQuery("#wpshop_new_set_section_add").children("img").hide(); });wpshopShowMessage("' . $attributeSetSectionCreation_Result . '");hideShowMessage(5000);'.$more_script.'</script>';
		wp_die( wpshop_attributes_set::attributeSetDetailsManagement($elementIdentifier) . $script );
	}
	add_action( 'wp_ajax_wps_attribute_set_section_new', 'saveNewAttributeSetSection' );

	function editAttributeSetSection() {
		check_ajax_referer( 'editAttributeSetSection' );
		$attributeSetSectionName = sanitize_title($_REQUEST['attributeSetSectionName']);
		$attributeSetSectionId = sanitize_key($_REQUEST['attributeSetSectionId']);
		$attributeSetSectionDefault = sanitize_text_field($_REQUEST['attributeSetSectionDefault']);
		$backend_display_type = sanitize_text_field($_REQUEST['attributeSetSectionDisplayType']);
		$display_on_frontend = sanitize_text_field($_REQUEST['attributeSetSectionDisplayinFrontEnd']);
		$backend_display_type = in_array($backend_display_type, array('movable-tab','fixed-tab')) ? $backend_display_type : 'fixed-tab';

		if($attributeSetSectionDefault == 'yes'){
			$wpdb->update(WPSHOP_DBT_ATTRIBUTE_GROUP, array('last_update_date' => current_time('mysql', 0), 'default_group' => 'no'), array('attribute_set_id' => $elementIdentifier));
		}
		$attributeSetInfos = array();
		$attributeSetInfos['last_update_date'] = current_time('mysql', 0);
		$attributeSetInfos['name'] = $attributeSetSectionName;
		$attributeSetInfos['default_group'] = $attributeSetSectionDefault;
		$attributeSetInfos['backend_display_type'] = $backend_display_type;
		$attributeSetInfos['display_on_frontend'] = $display_on_frontend;
		$attributeSetSectionCreation = wpshop_database::update($attributeSetInfos, $attributeSetSectionId, WPSHOP_DBT_ATTRIBUTE_GROUP);
		if ( $attributeSetSectionCreation == 'done' )
			$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_SUCCES_ICON . '\' alt=\'action_success\' class=\'wpshopPageMessage_Icon\' />' . __('The section has been updated successfully', 'wpshop');
		else
			$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_ERROR_ICON . '\' alt=\'action_error\' class=\'wpshopPageMessage_Icon\' />' . __('An error occured while updating the section', 'wpshop');
		echo wpshop_attributes_set::attributeSetDetailsManagement($elementIdentifier) . '<script type="text/javascript" >wpshopShowMessage("' . $attributeSetSectionCreation_Result . '");hideShowMessage(5000);</script>';
		wp_die();
	}
	add_action( 'wp_ajax_wps_attribute_set_section_edit', 'editAttributeSetSection' );

	function deleteAttributeSetSection() {
		check_ajax_referer( 'deleteAttributeSetSection' );
		$attributeSetSectionId = (int) $_REQUEST['attributeSetSectionId'];
		$attributeSetInfos = array();
		$attributeSetInfos['status'] = 'deleted';
		$attributeSetInfos['last_update_date'] = current_time('mysql', 0);

		$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'deleted', 'last_update_date' => current_time('mysql', 0)), array('attribute_group_id' => $attributeSetSectionId));
		$attributeSetSectionCreation = wpshop_database::update($attributeSetInfos, $attributeSetSectionId, WPSHOP_DBT_ATTRIBUTE_GROUP);

		$more_script = '';
		if ( $attributeSetSectionCreation == 'done' ) {
			$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_SUCCES_ICON . '\' alt=\'action_success\' class=\'wpshopPageMessage_Icon\' />' . __('The section has been successfully been deleted', 'wpshop');
			$more_script.='jQuery("#attribute_group_'.$attributeSetSectionId.'").remove();';
		}
		else
			$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_ERROR_ICON . '\' alt=\'action_error\' class=\'wpshopPageMessage_Icon\' />' . __('An error occured while deleting the section', 'wpshop');
		echo '<script type="text/javascript" >wpshopShowMessage("' . $attributeSetSectionCreation_Result . '");hideShowMessage(5000);'.$more_script.'</script>';
		wp_die();
	}
	add_action( 'wp_ajax_wps_attribute_set_section_delete', 'deleteAttributeSetSection' );

	/* ATTRIBUTES UNIT */

	function load_unit_interface() {
		check_ajax_referer( 'load_unit_interface' );
		?>
		<div id="wpshop_unit_main_listing_interface">
			<div class="wpshop_full_page_tabs">
				<ul class="ui-tabs-nav">
					<li id="wpshop_unit_list_tab" class="ui-state-default"><a href="#wpshop_unit_list" ><?php _e('Unit', 'wpshop'); ?></a></li>
					<li id="wpshop_unit_group_list_tab" ><a href="#wpshop_unit_group_list" ><?php _e('Unit group', 'wpshop'); ?></a></li>
				</ul>
			</div>
			<div id="wpshop_unit_list" ><?php echo wpshop_attributes_unit::elementList(); ?></div>
			<div id="wpshop_unit_group_list" ><?php echo wpshop_attributes_unit::unit_group_list(); ?></div>
		</div>
		<script type="text/javascript" >
			wpshop(document).ready(function(){ jQuery("#wpshop_unit_main_listing_interface").tabs(); });
		</script>
		<?php

		wp_die();
	}
	add_action( 'wp_ajax_wps_attribute_unit_interface', 'load_unit_interface' );

	function load_attribute_units() {
		check_ajax_referer( 'load_attribute_units' );
		wp_die( wpshop_attributes_unit::elementList() );
	}
	add_action( 'wp_ajax_wps_attribute_unit_load', 'load_attribute_units' );

	function add_attribute_unit() {
		check_ajax_referer( 'add_attribute_unit' );
		wp_die( wpshop_attributes_unit::elementEdition( '' ) );
	}
	add_action( 'wp_ajax_wps_attribute_unit_add', 'add_attribute_unit' );

	function edit_attribute_unit() {
		check_ajax_referer( 'edit_attribute_unit_' . ( isset( $_REQUEST['elementIdentifier'] ) ? (int) $_REQUEST['elementIdentifier'] : '' ) );
		wp_die( wpshop_attributes_unit::elementEdition( isset( $_REQUEST['elementIdentifier'] ) ? (int) $_REQUEST['elementIdentifier'] : '' ) );
	}
	add_action( 'wp_ajax_wps_attribute_unit_edit', 'edit_attribute_unit' );

	function save_new_attribute_unit() {
		check_ajax_referer( 'save_new_attribute_unit' );
		$save_output = '';

		$attribute_unit_informations['id'] = '';
		$attribute_unit_informations['status'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['status']);
		$attribute_unit_informations['creation_date'] = date('Y-m-d H:i:s');
		$attribute_unit_informations['name'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['name']);
		$attribute_unit_informations['unit'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['unit']);
		$attribute_unit_informations['group_id'] = sanitize_key($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['group_id']);
		$attribute_unit_informations['is_default_of_group'] = sanitize_key($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['is_default_of_group']);
		$attribute_unit_informations['change_rate'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['change_rate']);
		$attribute_unit_informations['code_iso'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['code_iso']);

		$save_unit_result = wpshop_database::save($attribute_unit_informations, WPSHOP_DBT_ATTRIBUTE_UNIT);
		if($save_unit_result == 'done'){
			$save_output = wpshop_attributes_unit::elementList();
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The new unit has been saved succesfully', 'wpshop');
		}
		else{
			$save_output = wpshop_attributes_unit::elementEdition();
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during new unit saving', 'wpshop');
		}

		echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . $save_output;
		wp_die();
	}
	add_action( 'wp_ajax_wps_attribute_unit_new', 'save_new_attribute_unit' );

	function update_attribute_unit() {
		check_ajax_referer( 'update_attribute_unit' );
		$save_output = '';

		$attributeUnitId = sanitize_key($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['id']);
		$attribute_unit_informations['status'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['status']);
		$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
		$attribute_unit_informations['name'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['name']);
		$attribute_unit_informations['unit'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['unit']);
		$attribute_unit_informations['group_id'] = sanitize_key($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['group_id']);
		$attribute_unit_informations['is_default_of_group'] = sanitize_key($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['is_default_of_group']);
		$attribute_unit_informations['change_rate'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['change_rate']);
		$attribute_unit_informations['code_iso'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['code_iso']);

		if ( $attribute_unit_informations['is_default_of_group'] == 'yes' ) {
			$wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array(
				'is_default_of_group' => 'no'
			), array(
				'group_id' => $attribute_unit_informations['group_id']
			));
		}

		$save_unit_result =  wpshop_database::update($attribute_unit_informations, $attributeUnitId, WPSHOP_DBT_ATTRIBUTE_UNIT);
		if ( $save_unit_result == 'done' ) {
			$save_output = wpshop_attributes_unit::elementList();
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The unit has been saved succesfully', 'wpshop');
		}
		else {
			$save_output = wpshop_attributes_unit::elementEdition($attributeUnitId);
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during unit saving', 'wpshop');
		}

		echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . $save_output;
		wp_die();
	}
	add_action( 'wp_ajax_wps_attribute_unit_update', 'update_attribute_unit' );

	function delete_attribute_unit() {
		check_ajax_referer( 'delete_attribute_unit_' . ( isset( $_REQUEST['elementIdentifier'] ) ? (int) $_REQUEST['elementIdentifier'] : '' ) );
		$unit_id = sanitize_key($_REQUEST['elementIdentifier']);
		$save_output = '';

		$attribute_unit_informations['status'] = 'deleted';
		$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
		$save_unit_result = wpshop_database::update($attribute_unit_informations, $unit_id, WPSHOP_DBT_ATTRIBUTE_UNIT);
		if ( $save_unit_result == 'done' ) {
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action succesful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The unit has been deleted succesfully', 'wpshop');
		}
		else {
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during unit deletion', 'wpshop');
		}

		echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . wpshop_attributes_unit::elementList();
		wp_die();
	}
	add_action( 'wp_ajax_wps_attribute_unit_delete', 'delete_attribute_unit' );

	/* ATTRIBUTES UNIT GROUP */

	function load_attribute_unit_groups() {
		check_ajax_referer( 'load_attribute_unit_groups' );
		wp_die( wpshop_attributes_unit::unit_group_list() );
	}
	add_action( 'wp_ajax_wps_attribute_group_unit_load', 'load_attribute_unit_groups' );

	function add_attribute_unit_group() {
		check_ajax_referer( 'add_attribute_unit_group' );
		wp_die( wpshop_attributes_unit::unit_group_edition( '' ) );
	}
	add_action( 'wp_ajax_wps_attribute_group_unit_add', 'add_attribute_unit_group' );

	function edit_attribute_unit_group() {
		check_ajax_referer( 'edit_attribute_unit_group_' . ( isset( $_REQUEST['elementIdentifier'] ) ? $_REQUEST['elementIdentifier'] : '' ) );
		wp_die( wpshop_attributes_unit::unit_group_edition( isset( $_REQUEST['elementIdentifier'] ) ? (int) $_REQUEST['elementIdentifier'] : '' ) );
	}
	add_action( 'wp_ajax_wps_attribute_group_unit_edit', 'edit_attribute_unit_group' );

	function save_new_attribute_unit_group() {
		check_ajax_referer( 'save_new_attribute_unit_group' );
		$save_output = '';

		$attribute_unit_informations['id'] = '';
		$attribute_unit_informations['status'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['status']);
		$attribute_unit_informations['creation_date'] = date('Y-m-d H:i:s');
		$attribute_unit_informations['name'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['name']);

		$save_unit_result = wpshop_database::save($attribute_unit_informations, WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP);
		if($save_unit_result == 'done'){
			$save_output = wpshop_attributes_unit::unit_group_list();
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The new unit group has been saved succesfully', 'wpshop');
		}
		else{
			$save_output = wpshop_attributes_unit::unit_group_edition();
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during new unit group saving', 'wpshop');
		}

		echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . $save_output;
		wp_die();
	}
	add_action( 'wp_ajax_wps_attribute_group_unit_new', 'save_new_attribute_unit_group' );

	function update_attribute_unit_group() {
		check_ajax_referer( 'update_attribute_unit_group' );
		$save_output = '';

		$attributeUnitId = sanitize_key($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['id']);
		$attribute_unit_informations['status'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['status']);
		$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
		$attribute_unit_informations['name'] = sanitize_text_field($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['name']);

		$save_unit_result =  wpshop_database::update($attribute_unit_informations, $attributeUnitId, WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP);
		if($save_unit_result == 'done'){
			$save_output = wpshop_attributes_unit::unit_group_list();
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The unit group has been saved succesfully', 'wpshop');
		}
		else{
			$save_output = wpshop_attributes_unit::unit_group_edition($attributeUnitId);
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during unit group saving', 'wpshop');
		}

		echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . $save_output;
		wp_die();
	}
	add_action( 'wp_ajax_wps_attribute_group_unit_update', 'update_attribute_unit_group' );

	function delete_attribute_unit_group() {
		check_ajax_referer( 'delete_attribute_unit_group_' . ( isset( $_REQUEST['elementIdentifier'] ) ? (int) $_REQUEST['elementIdentifier'] : '' ) );
		$unit_id = sanitize_key($_REQUEST['elementIdentifier']);
		$save_output = '';

		$attribute_unit_informations['status'] = 'deleted';
		$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
		$save_unit_result = wpshop_database::update($attribute_unit_informations, $unit_id, WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP);
		if($save_unit_result == 'done'){
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action succesful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The unit group has been deleted succesfully', 'wpshop');
		}
		else{
			$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during unit group deletion', 'wpshop');
		}

		echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . wpshop_attributes_unit::unit_group_list();
		wp_die();
	}
	add_action( 'wp_ajax_wps_attribute_group_unit_delete', 'delete_attribute_unit_group' );

	function products_by_criteria() {
		check_ajax_referer( 'products_by_criteria' );
		$attr = !empty($_REQUEST['attr']) ? sanitize_text_field($_REQUEST['attr']) : '';
		// If a filter by attribute is found, recalcul the products that matching it
		if(!empty($attr)) {
			$att = explode(':',$attr);
			$products_id = wpshop_products::get_products_matching_attribute($att[0],$att[1]);
		}
		$products_id = !empty($products_id) ? $products_id : sanitize_text_field( $_REQUEST['pid'] );
		$page_number = !empty( $_REQUEST ) && !empty( $_REQUEST[ 'page_number' ] ) && is_int( (int) $_REQUEST['page_number'] ) ? (int) $_REQUEST['page_number'] : 0;

		$criteria = !empty( $_REQUEST ) && !empty( $_REQUEST[ 'criteria' ] ) ? sanitize_text_field( $_REQUEST[ 'criteria' ] ) : null;
		$cid = !empty( $_REQUEST ) && !empty( $_REQUEST[ 'cid' ] ) && is_int( (int)$_REQUEST[ 'cid' ] ) ? (int)$_REQUEST[ 'cid' ] : null;
		$display_type = !empty( $_REQUEST ) && !empty( $_REQUEST[ 'display_type' ] ) ? sanitize_text_field( $_REQUEST[ 'display_type' ] ) : null;
		$order = !empty( $_REQUEST ) && !empty( $_REQUEST[ 'order' ] ) ? sanitize_text_field( $_REQUEST[ 'order' ] ) : null;
		$products_per_page = !empty( $_REQUEST ) && !empty( $_REQUEST[ 'products_per_page' ] ) && is_int( (int)$_REQUEST[ 'products_per_page' ] ) ? (int)$_REQUEST[ 'products_per_page' ] : 0;

		$data = wpshop_products::wpshop_get_product_by_criteria( $criteria, $cid, $products_id, $display_type, $order, $page_number, $products_per_page );
		if($data[0]) {
			echo json_encode(array(true, do_shortcode($data[1])));
		} else echo json_encode(array(false,__('No product found','wpshop')));
		wp_die();
	}
	add_action( 'wp_ajax_wps_products_by_criteria', 'products_by_criteria' );
	add_action( 'wp_ajax_nopriv_wps_products_by_criteria', 'products_by_criteria' );

	function ajax_sendMessage() {
		check_ajax_referer( 'sendMessage' );
		$post_id = !empty($_REQUEST['postid']) ? (int) $_REQUEST['postid'] : null;
		$title = !empty($_REQUEST['title']) ? sanitize_title($_REQUEST['title']) : '';
		$message = !empty($_REQUEST['message']) ? sanitize_text_field($_REQUEST['message']) : '';
		$recipient = !empty($_REQUEST['recipient']) ? (array) $_REQUEST['recipient'] : array();
		if ( isset($post_id) && isset($title) && isset($message) && !empty($recipient) ) {
			$user_info = get_userdata($recipient);
			$first_name = $user_info->user_firstname;
			$last_name = $user_info->user_lastname;
			$data = array('customer_first_name'=>$first_name,'customer_last_name'=>$last_name);

			$wps_message_ctr = new wps_message_ctr();


			$title = $wps_message_ctr->customMessage($title, $data);
			$message = $wps_message_ctr->customMessage($message, $data);

			if (!empty($user_info->user_email)) {
				$wps_message_ctr->wpshop_email($user_info->user_email, $title, $message, $save=true, $model_id=$post_id, $object=array());
				$array = array('result' => true, 'message' => '');
			}
			else $array = array('result' => true, 'message' => __('An error occured','wpshop'));
		}
		else {
			$array = array('result' => false, 'message' => __('An error occured','wpshop'));
		}

		echo json_encode($array);
		wp_die();
	}
	add_action( 'wp_ajax_wps_ajax_send_message', 'ajax_sendMessage' );

	function ajax_resendMessage() {
		check_ajax_referer( 'resendMessage' );
		$message_id = !empty($_REQUEST['messageid']) ? (array) $_REQUEST['messageid'] : array();
		if (isset($message_id)) {
			$ids = explode('-',$message_id);
			$postid = $ids[0];
			$date = $ids[1].'-'.$ids[2];
			$arraykey = $ids[3];

			$historic = get_post_meta($postid, 'wpshop_messages_histo_'.$date, true);

			if (isset($historic[$arraykey])) {
				$historic[$arraykey]['mess_dispatch_date'][] = current_time('mysql', 0);
				update_post_meta($postid, 'wpshop_messages_histo_'.$date, $historic);

				$data = $historic[$arraykey];
				$wps_message_ctr = new wps_message_ctr();
				$wps_message_ctr->wpshop_email($data['mess_user_email'], $data['mess_title'], $data['mess_message'], $save=false, $object=array());

				$array = array('result' => true, 'message' => '');
			}
			else {
				$array = array('result' => false, 'message' => __('An error occured','wpshop'));
			}
		}
		else {
			$array = array('result' => false, 'message' => __('An error occured','wpshop'));
		}

		echo json_encode($array);
		wp_die();
	}
	add_action( 'wp_ajax_wps_ajax_resend_message', 'ajax_resendMessage' );

	function related_products() {
		check_ajax_referer( 'related_products' );
		$search = sanitize_text_field($_REQUEST['search']);
		$data = wpshop_products::product_list(false, $search);
		$array=array();
		foreach ($data as $d) {
			$array[] = array('id' => $d->ID, 'name' => $d->post_title);
		}
		echo json_encode($array);
		wp_die();
	}
	add_action( 'wp_ajax_wps_related_products', 'related_products' );

	function applyCoupon() {
		check_ajax_referer( 'applyCoupon' );
		$wps_coupon_ctr = new wps_coupon_ctr();
		$coupon_code = sanitize_text_field($_REQUEST['coupon_code']);
		$result = $wps_coupon_ctr->applyCoupon($coupon_code);
		if ($result['status']===true) {
			$wps_cart_ctr = new wps_cart();
			$order = $wps_cart_ctr->calcul_cart_information(array());
			$wps_cart_ctr->store_cart_in_session($order);
			echo json_encode(array(true, ''));
		} else echo json_encode(array(false, $result['message']));
		wp_die();
	}
	add_action( 'wp_ajax_wps_cart_action_apply_coupon', 'applyCoupon' );

?>
