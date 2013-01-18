<?php
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

		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;

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
		$attachement_id = isset($_POST['attachement_id']) ? intval(wpshop_tools::varSanitizer($_POST['attachement_id'])) : null;

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
		$current_post_id = isset($_POST['current_post_id']) ? intval(wpshop_tools::varSanitizer($_POST['current_post_id'])) : null;
		$attachement_type_list = array('reload_box_document' => 'application/pdf', 'reload_box_picture' => 'image/');
		$part_to_reload = isset($_POST['part_to_reload']) ? wpshop_tools::varSanitizer($_POST['part_to_reload']) : null;
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

		$post_ids = ( isset( $_POST[ 'post_ids' ] ) && !empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : array();
		$post_attributes = ( isset( $_POST[ 'attribute' ] ) && !empty( $_POST[ 'attribute' ] ) ) ? $_POST[ 'attribute' ] : array();

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
				/*	Save the attributes values into wpshop eav database	*/
				wpshop_attributes::saveAttributeForEntity($attribute_to_save, wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $post_id, get_locale(), 'bulk');

				/*	Update product price looking for shop parameters	*/
				wpshop_products::calculate_price($post_id);

				/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
				$productMetaDatas = array();
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

		$attributes_for_variation = isset($_POST['wpshop_attribute_to_use_for_variation']) ? ($_POST['wpshop_attribute_to_use_for_variation']) : null;
		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;

		/*
		 * Get the list of values of the attribute to affect to a variation
		 */
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

		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;

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
		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;
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

		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;
		$output = '';

		/*	Display variation options	*/
		$options_tpl_component = array();
		$head_wpshop_variation_definition = get_post_meta( $current_post_id, '_wpshop_variation_defining', true );
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_PRIORITY_SINGLE'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['priority'][0]) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['priority'][0]) && ($head_wpshop_variation_definition['options']['priority'][0] == 'single')) ) ? ' checked="checked"' : '';
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_PRIORITY_COMBINED'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['priority'][0]) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['priority'][0]) && ($head_wpshop_variation_definition['options']['priority'][0] == 'combined')) ) ? ' checked="checked"' : '';
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_BEHAVIOUR_ADDITION'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['price_behaviour'][0]) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['price_behaviour'][0]) && ($head_wpshop_variation_definition['options']['price_behaviour'][0] == 'addition')) ) ? ' checked="checked"' : '';
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_BEHAVIOUR_REPLACEMENT'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['price_behaviour'][0]) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['price_behaviour'][0]) && ($head_wpshop_variation_definition['options']['price_behaviour'][0] == 'replacement')) ) ? ' checked="checked"' : '';

		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_PRICE_DISPLAY_TEXT_FROM'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['price_display']['text_from']) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['price_display']['text_from']) && ($head_wpshop_variation_definition['options']['price_display']['text_from'] == 'on')) ) ? ' checked="checked"' : '';
		$options_tpl_component['ADMIN_VARIATION_OPTIONS_SELECTED_PRICE_DISPLAY_LOWER_PRICE'] = ( empty($head_wpshop_variation_definition['options']) || empty($head_wpshop_variation_definition['options']['price_display']['lower_price']) || (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['price_display']['lower_price']) && ($head_wpshop_variation_definition['options']['price_display']['lower_price'] == 'on')) ) ? ' checked="checked"' : '';

		$options_tpl_component['ADMIN_VARIATION_PARAMETERS_FORM_HEAD_PRODUCT_ID'] = $current_post_id;
		$options_tpl_component['ADMIN_VARIATION_PARAMETERS_FORM_HEAD_NOUNCE'] = wp_create_nonce("wpshop_variation_parameters");

		$options_tpl_component['ADMIN_MORE_OPTIONS_FOR_VARIATIONS'] = '';

		$attribute_list_for_variations = wpshop_attributes::get_variation_available_attribute( $current_post_id );

		$default_value_for_attributes = $required_attributes = '';
		if ( !empty($attribute_list_for_variations['available']) ) {
			$head_wpshop_variation_definition = get_post_meta( $current_post_id, '_wpshop_variation_defining', true );
			foreach ( $attribute_list_for_variations['available'] as $attribute_code => $attribute_definition ) {
				/** Default value for attribute	*/
				$tpl_component = array();
				$tpl_component['ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CONTAINER_CLASS'] = ' variation_attribute_container_default_value_' . $attribute_code;
				$attribute_for_default_value = wpshop_attributes::get_attribute_field_definition($attribute_definition['attribute_complete_def'], ( isset($head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code]) ? $head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code] : null), array('from' => 'frontend', 'field_custom_name_prefix' => 'empty'));
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
					$attribute_for_default_value['possible_value'][0] = __('No default value', 'wpshop');
					foreach( $attribute_for_default_value['possible_value'] as $value_id => $value ){
						if ( !empty($value_id) && !in_array($value_id, $attribute_definition['values']) ) {
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

		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;

		if ( !empty($_POST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION]['options']) ) {
			$variation_post_meta = get_post_meta($current_post_id, '_wpshop_variation_defining', true);
			$variation_post_meta['options'] = $_POST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION]['options'];
			update_post_meta($current_post_id, '_wpshop_variation_defining', $variation_post_meta);
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

		$attributes_for_variation = isset($_POST['variation_attr']) ? ($_POST['variation_attr']) : null;
		$wpshop_admin_use_attribute_for_single_variation_checkbox = isset($_POST['wpshop_admin_use_attribute_for_single_variation_checkbox']) ? ($_POST['wpshop_admin_use_attribute_for_single_variation_checkbox']) : null;
		$variation_specific_definition = isset($_POST['wps_pdt_variations']['new']['attribute']) ? ($_POST['wps_pdt_variations']['new']['attribute']) : null;
		$current_post_id = isset($_POST['wpshop_head_product_id']) ? wpshop_tools::varSanitizer($_POST['wpshop_head_product_id']) : null;

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

		wpshop_attributes::saveAttributeForEntity($variation_specific_definition, wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $new_variation_identifier, get_locale());
		wpshop_products::calculate_price( $new_variation_identifier );

		$output = wpshop_products::display_variation_admin( $current_post_id );

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

		$current_post_id = isset($_POST['current_post_id']) && is_array($_POST['current_post_id']) ? $_POST['current_post_id'] : null;
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
		check_ajax_referer( 'wpshop_variation_management', 'wpshop_ajax_nonce' );

		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;
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
		$order_id = ( isset( $_POST[ 'order_id' ] ) && !empty( $_POST[ 'order_id' ] ) ) ? $_POST[ 'order_id' ] : null;
		$payment_method = ( isset( $_POST[ 'payment_method' ] ) && !empty( $_POST[ 'payment_method' ] ) ) ? $_POST[ 'payment_method' ] : null;
		$transaction_id = ( isset( $_POST[ 'transaction_id' ] ) && !empty( $_POST[ 'transaction_id' ] ) ) ? $_POST[ 'transaction_id' ] : null;

		if ( !empty($order_id) ) {
			if( !empty($payment_method) && !empty($transaction_id) ) {
				/* Update he payment method */
				$order = get_post_meta($order_id, '_order_postmeta', true);
				$order['payment_method'] = $payment_method;
				update_post_meta($order_id, '_order_postmeta', $order);
				update_post_meta($order_id, '_wpshop_payment_method', $order['payment_method']);

				// Update Transaction identifier regarding the payment method
				if ( !empty($transaction_id) ) {
					$transaction_key = '';
					switch($payment_method) {
						case 'check':
							$transaction_key = '_order_check_number';
						break;
					}
					if ( !empty($transaction_key) ) update_post_meta($order_id, $transaction_key, $transaction_id);
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
		$order_id = ( isset( $_POST[ 'order_id' ] ) && !empty( $_POST[ 'order_id' ] ) ) ? $_POST[ 'order_id' ] : null;

		if ( !empty($order_id) ) {
			$result = (array(true, '<h1>'.__('Tracking number','wpshop').'</h1><p>'.__('Enter a tracking number, or leave blank:','wpshop').'</p><input type="hidden" value="'.$order_id.'" name="oid" /><input type="text" name="trackingNumber" /><br /><br /><input type="submit" class="button-primary sendTrackingNumber" value="'.__('Send','wpshop').'" /> <input type="button" class="button-secondary closeAlert" value="'.__('Cancel','wpshop').'" />'));

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

		$order_id = ( isset( $_POST[ 'order_id' ] ) && !empty( $_POST[ 'order_id' ] ) ) ? $_POST[ 'order_id' ] : null;
		$order_state = ( isset( $_POST[ 'order_state' ] ) && !empty( $_POST[ 'order_state' ] ) ) ? $_POST[ 'order_state' ] : null;
		$order_shipped_number = ( isset( $_POST[ 'order_shipped_number' ] ) && !empty( $_POST[ 'order_shipped_number' ] ) ) ? $_POST[ 'order_shipped_number' ] : null;

		if ( !empty($order_id) ) {
			/* Update the oder state */
			$order = get_post_meta($order_id, '_order_postmeta', true);
			$order['order_status'] = $order_state;

			if ( $order_state == 'shipped' ) {
				$order['order_shipping_date'] = current_time('mysql', 0);
				$order['order_trackingNumber'] = $order_shipped_number;
				/* Send a confirmation e-mail */
				wpshop_send_confirmation_shipping_email($order_id);
				update_post_meta($order_id, '_wpshop_order_shipping_date', $order['order_shipping_date']);
			}
			if ( $order_state == 'completed' ) {
				$order['order_payment_date'] = current_time('mysql', 0);
				wpshop_payment::the_order_payment_is_completed($order_id);
				update_post_meta($order_id, '_wpshop_order_payment_date', $order['order_payment_date']);
			}
			update_post_meta($order_id, '_order_postmeta', $order);
			update_post_meta($order_id, '_wpshop_order_status', $order_state);


			$result = array(true, $order_state, __($order_status[$order_state], 'wpshop'));
		}
		else {
		$result = array(false, __('Incorrect order request', 'wpshop'));
		}

		echo json_encode($result);
		die();
	}
	add_action( 'wp_ajax_change_order_state', 'wpshop_ajax_change_order_state' );


	/* Send a confirmation e-mail to the customer */
	function wpshop_send_confirmation_shipping_email($order_id)
	{
		if ( !empty($order_id) ) {
			$order_info = get_post_meta($order_id, '_order_info', true);
			$order = get_post_meta($order_id, '_order_postmeta', true);
			$email = ( !empty($order_info['billing']['email']) ? $order_info['billing']['email'] : '');
			$first_name = (!empty($order_info['billing']['first_name']) ? $order_info['billing']['first_name'] : '');
			$last_name = ( !empty($order_info['billing']['last_name']) ? $order_info['billing']['last_name'] : '');

			wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', array('order_key' => ( !empty($order['order_key']) ? $order['order_key'] : '' ), 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => ( !empty($order['order_date']) ? $order['order_date'] : '' ), 'order_trackingNumber' => ( !empty($order['order_trackingNumber']) ? $order['order_trackingNumber'] : '' )));
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
		$attribute_identifier = isset($_GET['attribute_identifier']) ? wpshop_tools::varSanitizer($_GET['attribute_identifier']) : '0';
		$option_name=(!empty($_REQUEST['attribute_new_label']) ? $_REQUEST['attribute_new_label'] : '');
		$options_value=sanitize_title($option_name);

		/*	Check if given value does not exist before continuing	*/
		$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE (label = %s OR value = %s) AND attribute_id = %d AND status = 'valid'", $option_name, $options_value, $attribute_identifier);
		$existing_values = $wpdb->get_results($query);

		/*	If given value does not exist: display result. If value exist alert a error message	*/
		if( count($existing_values) <= 0 ) {
			ob_start();
			include(WPSHOP_TEMPLATES_DIR.'admin/attribute_option_value.tpl.php');
			$output = ob_get_contents();
			ob_end_clean();

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
				$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('creation_date' => current_time('mysql', 0), 'status' => 'valid', 'attribute_id' => $attribute->id, 'position' => $position, 'label' => str_replace(",", ".", $attribute_options_label), 'value' => $attribute_options_value));
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

		echo json_encode(array($result_status, $result, $attribute_code));
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

		$data_type_to_use = isset($_GET['data_type_to_use']) ? str_replace('_data', '', wpshop_tools::varSanitizer($_GET['data_type_to_use'], '')) : 'custom';
		$current_type = isset($_GET['current_type']) ? wpshop_tools::varSanitizer($_GET['current_type']) : 'short_text';
		$elementIdentifier = isset($_GET['elementIdentifier']) ? intval( wpshop_tools::varSanitizer($_GET['elementIdentifier'])) : null;
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
			$result .= __('Choose the data type to use for this attribute', 'wpshop') . '<a href="#" title="'.sprintf(__('If the type you want to use is not in the list below. You have to create it by using %s menu', 'wpshop'), __('Entities', 'wpshop')).'" class="wpshop_infobulle_marker">?</a><div class="clear wpshop_attribute_select_data_type_internal_list">'.$combo_wp_type.'</div>';
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
				$result_output = '<p class="wpshop_duplicate_attribute_result" ><a href="' . admin_url('edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES . '&page=' . WPSHOP_URL_SLUG_ATTRIBUTE_LISTING . '&action=edit&id=' . $new_attribute_id) . '" >' . __('Edit the new attribute', 'wpshop') . '</a></p>';
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


/* Attributes unit */
	/**
	 * Load comboBox of unit or group of unit
	 */
	function wpshop_ajax_load_attribute_unit_list()
	{
		check_ajax_referer( 'wpshop_load_attribute_unit_list', 'wpshop_ajax_nonce' );

		$current_group = ( isset( $_POST[ 'current_group' ] ) && !empty( $_POST[ 'current_group' ] ) ) ? $_POST[ 'current_group' ] : null;
		$selected_list = ( isset( $_POST[ 'selected_list' ] ) && !empty( $_POST[ 'selected_list' ] ) ) ? $_POST[ 'selected_list' ] : null;

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


/*	Options page	*/
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
				if ( $addons_list[$addon_name][2] == 'per_site') {
					$code .= '-' . substr(hash ( "sha256" , site_url('/') ),  $addons_list[$addon_name][1], 5);
				}
				if ($code == $addon_code) {
					$extra_options = get_option(WPSHOP_ADDONS_OPTION_NAME, array());
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

		$billing_form_fields = wpshop_address::get_addresss_form_fields_by_type ( $current_billing_address );
		$possible_values_for_billing = array('' => __('No corresponding field', 'wpshop'));
		foreach ( $billing_form_fields[$current_billing_address] as $attribute_group_id => $attribute_group_detail) {
			foreach ( $attribute_group_detail['content'] as $attribute_build_code => $attribute_definition) {
				$possible_values_for_billing[$attribute_build_code] = $attribute_definition['label'];
			}
		}

		$account_form_field = $wpshop_account->personal_info_fields;
		$possible_values = array();
		$matching_field = '';
		foreach ( $account_form_field as $attribute_code => $attribute_detail) {
			$possible_values[$attribute_code] = $attribute_detail['label'];

			$input_def['name'] = 'wpshop_billing_address[integrate_into_register_form_matching_field][' . $attribute_code . ']';
			$input_def['id'] = 'wpshop_billing_address_integrate_into_register_form_after_field';
			$input_def['possible_value'] = $possible_values_for_billing;
			$input_def['type'] = 'select';
			$input_def['valueToPut'] = 'index';
			$input_def['value'] = (is_array($wpshop_billing_address['integrate_into_register_form_matching_field']) && array_key_exists($attribute_code, $wpshop_billing_address['integrate_into_register_form_matching_field']) ?$wpshop_billing_address['integrate_into_register_form_matching_field'][$attribute_code] : null);
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
		global $wpshop_cart, $wpdb;
		$product_id = isset($_POST['wpshop_pdt']) ? intval(wpshop_tools::varSanitizer($_POST['wpshop_pdt'])) : null;
		$cart_option = get_option('wpshop_cart_option', array());

		if ( !empty($cart_option['total_nb_of_item_allowed']) && ($cart_option['total_nb_of_item_allowed'][0] == 'yes') ) {
			$wpshop_cart->empty_cart();
		}

		$cart_type_for_adding = 'normal';
		if (!empty($_POST['wpshop_cart_type']) ) {
			switch(wpshop_tools::varSanitizer($_POST['wpshop_cart_type'])){
				case 'cart':
					$wpshop_cart_type = 'normal';
					break;
				case 'quotation':
					$wpshop_cart_type = 'quotation';
					break;
				default:
					$wpshop_cart_type = 'normal';
					break;
			}
		}

		$product_to_add_to_cart[$product_id]['id'] = $product_id;
		if ( !empty( $_POST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION] ) ) {
			$variation_calculator = wpshop_products::get_variation_by_priority( $_POST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION], $product_id );
			if ( !empty($variation_calculator[$product_id]) ) {
				$product_to_add_to_cart[$product_id] = array_merge($product_to_add_to_cart[$product_id], $variation_calculator[$product_id]);
			}
		}

		$return = $wpshop_cart->add_to_cart( $product_to_add_to_cart, array( $product_id => 1 ), $wpshop_cart_type );
		if ( $return == 'success' ) {
			$cart_page_url = get_permalink( get_option('wpshop_cart_page_id') );
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

			echo json_encode(array(true, $succes_message_box, $action_after_add, $cart_page_url));
		}
		else echo json_encode(array(false, $return));

		die();
	}
	add_action('wp_ajax_wpshop_add_product_to_cart', 'ajax_wpshop_add_to_cart');
	add_action('wp_ajax_nopriv_wpshop_add_product_to_cart', 'ajax_wpshop_add_to_cart');

	/**
	 * Set product qty into customer cart
	 */
	function ajax_wpshop_set_qty_for_product_into_cart() {
		global $wpshop_cart, $wpdb;
		$product_id = isset($_POST['product_id']) ? intval(wpshop_tools::varSanitizer($_POST['product_id'])) : null;
		$product_qty = isset($_POST['product_qty']) ? intval(wpshop_tools::varSanitizer($_POST['product_qty'])) : null;

		if (!empty($product_id)) {
			if (isset($product_qty)) {
				if ( $product_qty == 0 ) {
					$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_parent = %d AND post_type = %s", $product_id, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION);
					$variation_of_product = $wpdb->get_results($query);
					if ( !empty($variation_of_product) ) {
						foreach ( $variation_of_product as $p_id) {
							$wpshop_cart->set_product_qty($p_id->ID, $product_qty);
						}
					}
				}
				$return = $wpshop_cart->set_product_qty($product_id, $product_qty);
				echo json_encode(array(true));
			}
			else {
				echo json_encode(array(false, __('Parameters error.','wpshop')));
			}
		}
		die();
	}
	add_action('wp_ajax_wpshop_set_qtyfor_product_into_cart', 'ajax_wpshop_set_qty_for_product_into_cart');
	add_action('wp_ajax_nopriv_wpshop_set_qtyfor_product_into_cart', 'ajax_wpshop_set_qty_for_product_into_cart');

	/**
	 * Refresh product price and mini cart with selected variation
	 */
	function wpshop_ajax_wpshop_variation_selection() {
		global $wpdb;
		$response = '';
		$response_status = false;

		$product_id = isset($_POST['wpshop_pdt']) ? intval(wpshop_tools::varSanitizer($_POST['wpshop_pdt'])) : null;
		$wpshop_variation_selected = isset($_POST['wpshop_variation']) ? $_POST['wpshop_variation'] : null;
		$wpshop_free_variation = isset($_POST['wpshop_free_variation']) ? $_POST['wpshop_free_variation'] : null;
		$wpshop_current_for_display = isset($_POST['wpshop_current_for_display']) ? $_POST['wpshop_current_for_display'] : null;
		$product_qty = isset($_POST['product_qty']) ? $_POST['product_qty'] : 1;

		if ( !empty( $wpshop_variation_selected )  || !empty( $wpshop_free_variation ) ) {
			$variations_selected = array();
			if ( !empty($wpshop_variation_selected) ) {
				foreach ( $wpshop_variation_selected as $selected_variation ) {
					$variation_definition = explode('-_variation_val_-', $selected_variation);
					$variations_selected[$variation_definition[0]] = $variation_definition[1];
				}
			}

			$product_with_variation = wpshop_products::get_variation_by_priority( $variations_selected, $product_id );
			if ( !empty($product_with_variation[$product_id]['variations']) || !empty( $wpshop_free_variation )  ) {
				$head_product_id = $product_id;

				if ( !empty($product_with_variation[$product_id]['variations']) && ( count($product_with_variation[$product_id]['variations']) == 1 ) && ($product_with_variation[$product_id]['variation_priority'] != 'single') ) {
					$product_id = $product_with_variation[$product_id]['variations'][0];
				}

				$product = wpshop_products::get_product_data($product_id, true);

				$the_product = array_merge( array(
					'product_id'	=> $product_id,
					'product_qty' 	=> $product_qty
				), $product);

				/*	Add variation to product into cart for storage	*/
				if ( !empty($product_with_variation[$product_id]['variations']) ) {
					$the_product = wpshop_products::get_variation_price_behaviour( $the_product, $product_with_variation[$head_product_id]['variations'], $head_product_id, array('type' => $product_with_variation[$head_product_id]['variation_priority']) );
				}

				if (  !empty( $wpshop_free_variation )  ) {
					$the_product['item_meta']['free_variation'] = $wpshop_free_variation;
				}

				/*	Build an output for the product ith selected variation	*/
				$response['product_price_output'] = wpshop_products::get_product_price($the_product, 'price_display', 'complete_sheet', true);

				$tpl_component = array();
				foreach ( $the_product as $product_definition_key => $product_definition_value ) {
					if ( $product_definition_key != 'item_meta' ) {
						$tpl_component['PRODUCT_MAIN_INFO_' . strtoupper($product_definition_key)] = $product_definition_value;
						if ( !empty($wpshop_current_for_display) && in_array($product_definition_key, unserialize(WPSHOP_ATTRIBUTE_PRICES)) ) {

							$different_currency = false;
							$change_rate = 1;

							$wpshop_shop_currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
							$currency_group = get_option('wpshop_shop_currency_group');
							$current_currency = get_option('wpshop_shop_default_currency');
							$currency_unit = wpshop_tools::wpshop_get_sigle($current_currency);

							if ( $wpshop_current_for_display != $current_currency) {
								$different_currency = true;
								$query = $wpdb->prepare("SELECT change_rate, unit FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT . " WHERE id = %d", $wpshop_current_for_display);
								$currency_def = $wpdb->get_row($query);

								$change_rate = $currency_def->change_rate;
								$currency_unit = $currency_def->unit;
							}

							$tpl_component['PRODUCT_MAIN_INFO_' . strtoupper($product_definition_key)] = ( !$different_currency || ($change_rate == 1) ) ? $product_definition_value : ($product_definition_value * $change_rate);
							$tpl_component['CURRENCY_CHOOSEN'] = $currency_unit;
						}
					}
					else {
						$tpl_component['PRODUCT_VARIATION_SUMMARY_DETAILS'] = '';

						if ( !empty( $product_definition_value['variation_definition'] ) ) {
							foreach ( $product_definition_value['variation_definition'] as $variation_attribute_code => $variation_attribute_detail ) {
								$variation_tpl_component = array();
								foreach ( $variation_attribute_detail as $info_name => $info_value) {
									$variation_tpl_component['VARIATION_' . strtoupper($info_name)] = stripslashes($info_value);
								}
								$variation_tpl_component['VARIATION_ID'] = $variation_attribute_code;
								$variation_tpl_component['VARIATION_ATT_CODE'] = $variation_attribute_code;
								$tpl_component['PRODUCT_VARIATION_SUMMARY_DETAILS'] .= wpshop_display::display_template_element('cart_variation_detail', $variation_tpl_component);
								unset($variation_tpl_component);
							}
						}
						else {
							if (!empty($product_definition_value['variations'])) {
								foreach ( $product_definition_value['variations'] as $variation_id => $variation_details ) {
									$variation_tpl_component = array();
									foreach ( $variation_details as $info_name => $info_value) {
										if ( $info_name != 'item_meta' ) {
											$variation_tpl_component['VARIATION_DETAIL_' . strtoupper($info_name)] = stripslashes($info_value);
										}
									}
									foreach ( $variation_details['item_meta']['variation_definition'] as $variation_attribute_code => $variation_attribute_def ) {
										$variation_tpl_component['VARIATION_NAME'] = stripslashes($variation_attribute_def['NAME']);
										$variation_tpl_component['VARIATION_VALUE'] = stripslashes($variation_attribute_def['VALUE']);
										$variation_tpl_component['VARIATION_ID'] = $variation_id;
										$variation_tpl_component['VARIATION_ATT_CODE'] = $variation_attribute_code;
									}
									$tpl_component['PRODUCT_VARIATION_SUMMARY_DETAILS'] .= wpshop_display::display_template_element('cart_variation_detail', $variation_tpl_component);
									unset($variation_tpl_component);
								}
							}
						}

						/*	Free Variation part	*/
						if ( !empty($product_definition_value['free_variation']) ) {
							foreach ( $product_definition_value['free_variation'] as $build_variation ) {
								$variation_definition = explode('-_variation_val_-', $build_variation);

								$free_variation_attribute_def = wpshop_attributes::getElement($variation_definition[0], "'valid'", 'code');
								$variation_tpl_component['VARIATION_NAME'] = stripslashes($free_variation_attribute_def->frontend_label);
								$value_to_outut = $variation_definition[1];
								switch ( $free_variation_attribute_def->data_type ) {
									case 'datetime':
										$value_to_outut = mysql2date('d F Y', $variation_definition[1], true);
										break;
								}
								$variation_tpl_component['VARIATION_VALUE'] = stripslashes($value_to_outut);
								$variation_tpl_component['VARIATION_ID'] = $variation_definition[0];
								$variation_tpl_component['VARIATION_ATT_CODE'] = $variation_definition[0];
								if ( !empty($value_to_outut) ) {
									$tpl_component['PRODUCT_VARIATION_SUMMARY_DETAILS'] .= wpshop_display::display_template_element('cart_variation_detail', $variation_tpl_component);
								}
								unset($variation_tpl_component);
							}
						}
					}
				}
				$response['product_output'] = wpshop_display::display_template_element('wpshop_product_configuration_summary_detail', $tpl_component);
			}
			else {
				$response['product_output'] = '';
			}

			if ( $response['product_price_output'] == __('Unknown price', 'wpshop') ) {
				$product = wpshop_products::get_product_data($product_id, true);
				$response['product_price_output'] = wpshop_products::get_product_price($product, 'price_display', 'complete_sheet', true);
			}

			$response_status = true;
		}
		else {
			$response_status = false;
		}

		echo json_encode(array($response_status, $response));
		die();
	}
	add_action('wp_ajax_wpshop_variation_selection', 'wpshop_ajax_wpshop_variation_selection');
	add_action('wp_ajax_nopriv_wpshop_variation_selection', 'wpshop_ajax_wpshop_variation_selection');


	function wpshop_ajax_variation_selection_show_detail_for_value() {
		global $wpdb;

		$display = '';
		$attribute_for_detail = isset($_POST['attribute_for_detail']) ? $_POST['attribute_for_detail'] : null;

		if ( !empty( $attribute_for_detail ) ) {
			$selection = array();
			foreach ( $attribute_for_detail as $selected_variation ) {
				$variation_definition = explode('-_variation_val_-', $selected_variation);
				$attribute_definition = wpshop_attributes::getElement($variation_definition[0], "'valid'", 'code');
				$post_definition = get_post($variation_definition[1]);

				$tpl_component['VARIATION_ATTRIBUTE_NAME_FOR_DETAIL'] = $attribute_definition->frontend_label;
				$tpl_component['VARIATION_VALUE_TITLE_FOR_DETAIL'] = $post_definition->post_title;
				$tpl_component['VARIATION_VALUE_DESC_FOR_DETAIL'] = $post_definition->post_content;
				$tpl_component['VARIATION_VALUE_LINK_FOR_DETAIL'] = get_permalink($variation_definition[1]);

				$display .= wpshop_display::display_template_element('wpshop_product_variation_value_detail_content', $tpl_component);
				unset($tpl_component);
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
		global $wpshop, $wpshop_account;
		$reponse='';
		$status = false;
		$validate = true;

		$user_id = get_current_user_id();
		$current_connected_user = !empty( $user_id ) ? $user_id : null;
		$wpshop_billing_address = get_option('wpshop_billing_address');
		if ( !empty($wpshop_billing_address['integrate_into_register_form']) && ($wpshop_billing_address['integrate_into_register_form'] == 'yes') && isset($_POST['attribute'][$wpshop_billing_address['choice']]) ) {
			if ( !empty($wpshop_billing_address['integrate_into_register_form_matching_field']) ) {
				$address_fields = wpshop_address::get_addresss_form_fields_by_type ( $wpshop_billing_address['choice'] );
				$address_field = $address_fields[$wpshop_billing_address['choice']];
				$temp_aray_for_matching = array_flip($wpshop_billing_address['integrate_into_register_form_matching_field']);
				foreach ( $address_field as $group_id => $group_detail) {
					foreach ( $group_detail['content'] as $attribute_build_code => $attribute_def) {
						if ( in_array($attribute_build_code, $wpshop_billing_address['integrate_into_register_form_matching_field']) && empty( $_POST['attribute'][$wpshop_billing_address['choice']][$attribute_def['data_type']][$attribute_def['name']] ) && !empty(  $_POST['attribute'][$attribute_def['data_type']][$temp_aray_for_matching[$attribute_build_code]] ) ) {
							$_POST['attribute'][$wpshop_billing_address['choice']][$attribute_def['data_type']][$attribute_def['name']] = $_POST['attribute'][$attribute_def['data_type']][$temp_aray_for_matching[$attribute_build_code]];
							if ( $attribute_def['_need_verification'] == 'yes' ) {
								$_POST['attribute'][$wpshop_billing_address['choice']][$attribute_def['data_type']][$attribute_def['name'] . '2'] = $_POST['attribute'][$attribute_def['data_type']][$temp_aray_for_matching[$attribute_build_code] . '2'];
							}
						}
					}
				}
				$_POST['attribute'][$wpshop_billing_address['choice']]['varchar']['address_title'] = !empty( $_POST['attribute'][$wpshop_billing_address['choice']]['varchar']['address_title'] ) ? $_POST['attribute'][$wpshop_billing_address['choice']]['varchar']['address_title'] : __('Billing address', 'wpshop');
			}
			$group = wpshop_address::get_addresss_form_fields_by_type($wpshop_billing_address['choice']);
			$validate = false;
			foreach ( $group as $attribute_sets ) {
				foreach ( $attribute_sets as $attribute_set_field ) {
					$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'][$wpshop_billing_address['choice']], '');
				}
			}
		}
		if( $validate && $wpshop->validateForm($wpshop_account->personal_info_fields) ) {
			$status = $wpshop_account->save_account_form($user_id);

		}
		// If there is errors
		if($wpshop->error_count()>0) {
			$reponse = $wpshop->show_messages();
		}

		$cart_url = !empty($_SESSION['cart']['order_items']) ? get_permalink(get_option('wpshop_checkout_page_id')) : get_permalink(get_option('wpshop_myaccount_page_id'));

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
		$current_customer_id = !empty( $_REQUEST['customer_id'] ) ? $_REQUEST['customer_id'] : 0;
		// Check the attribute set id of Billing Address
		$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE name = "' .__('Billing address', 'wpshop'). '"', '');
		$attribute_set_id = $wpdb->get_var($query);
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
		$id_attribute_set = get_option('wpshop_billing_address', unserialize(WPSHOP_SHOP_CUSTOM_SHIPPING));
		$result = json_encode( array(true, $wpshop_account->display_form_fields( $id_attribute_set['choice'], $address_id ), $current_customer_id) );

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

		$attributes = array();
		/*
		 * Get the attribute to create
		 */
		if ( !empty($_POST['attribute']['new_value_creation']) && is_array( $_POST['attribute']['new_value_creation'] ) ) {
			foreach ( $_POST['attribute']['new_value_creation'] as $attribute_code=>$value) {
				$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = "'.$attribute_code.'"');
				$attribute_def = $wpdb->get_row($query);
				if ( $value != "" ) {
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status'=>'valid', 'creation_date'=>current_time('mysql', 0), 'position'=>1, 'attribute_id'=>$attribute_def->id, 'value'=>$value, 'label'=>$value) );
					$attribute_option_id = $wpdb->insert_id;
					foreach ( $_POST['attribute'] as $attribute=>$val) {
						foreach ($val as $k=>$v) {
							if ( $k == $attribute_code) {
								$_POST['attribute'][$attribute][$k] = $attribute_option_id;
							}
						}
					}
				}
			}
		}
		/*
		 * Store send attribute into a new array for save purpose
		 */
		if ( is_array( $_POST['attribute'] ) ) {
			foreach ( $_POST['attribute'] as $attribute_type => $attribute ) {
				foreach ( $attribute as $attribute_code => $attribute_value ) {
					if ( !isset( $attributes[$attribute_code] ) ) {
						$attributes[$attribute_code] = $attribute_value;
					}
				}
			}
		}

		/*
		 * Save the new entity into database
		 */
		$result = wpshop_entities::create_new_entity( $_POST['entity_type'], $_POST['wp_fields']['post_title'], '', $attributes, array('attribute_set_id' => $_POST['attribute_set_id']) );
		$new_entity_id = $result[1];

		if ( !empty($new_entity_id) ) {
			/*
			 * Make price calculation if entity is a product
			 */
			if ( $_POST['entity_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
				$wpshop_prices_attribute = unserialize(WPSHOP_ATTRIBUTE_PRICES);
				$calculate_price = false;
				foreach( $wpshop_prices_attribute as $attribute_price_code ){
					if ( in_array($attribute_price_code, $attrs) ) {
						$calculate_price = true;
					}
				}
				if ( $calculate_price ) {
					self::calculate_price($new_entity_id);
				}
			}

			/*
			 * Add picture if a file has been send
			 */
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

			echo __('Element has been saved', 'wpshop');
		}
		else {
			echo __('An error occured while adding your element', 'wpshop');
		}

		die();
	}
	add_action('wp_ajax_wpshop_quick_add_entity', 'ajax_wpshop_add_entity');
	add_action('wp_ajax_nopriv_wpshop_quick_add_entity', 'ajax_wpshop_add_entity');

?>