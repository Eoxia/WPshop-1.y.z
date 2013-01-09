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
	 * Variation element dialog box content
	 */
	function ajax_add_new_variation() {
		check_ajax_referer( 'wpshop_variation_creation', 'wpshop_ajax_nonce' );

		$attributes_for_variation = isset($_POST['checkboxes']) ? ($_POST['checkboxes']) : null;
		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;

		$variation_id = wpshop_products::create_variation($current_post_id, $attributes_for_variation);

		$output = wpshop_products::display_variation_admin($current_post_id);

		echo $output;
		die();
	}
	add_action('wp_ajax_add_new_variation', 'ajax_add_new_variation');

	/**
	 * Duplicate an existing variation
	 */
	function ajax_duplicate_variation() {
		check_ajax_referer( 'wpshop_variation_duplication', 'wpshop_ajax_nonce' );

		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;
		$attributes_for_variation = get_post_meta($current_post_id, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION);

		$variation_id = wpshop_products::create_variation($current_post_id, $attributes_for_variation);

		$output = wpshop_products::display_variation_admin($current_post_id);

		echo $output;
		die();
	}
	add_action('wp_ajax_duplicate_variation', 'ajax_duplicate_variation');

	/**
	 * Deleta a variation
	 */
	function ajax_delete_variation() {
		check_ajax_referer( 'wpshop_delete_variation', 'wpshop_ajax_nonce' );

		$result = true;

		$current_post_id = isset($_POST['current_post_id']) ? intval(wpshop_tools::varSanitizer($_POST['current_post_id'])) : null;
		$result = wp_delete_post($current_post_id, false);

		echo json_encode(array($result, $current_post_id));
		die();
	}
	add_action('wp_ajax_delete_variation', 'ajax_delete_variation');

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


/*	Orders	*/
	/* Validate the payment transaction number */
	function wpshop_ajax_validate_payment_method()
	{
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
	function wpshop_ajax_dialog_inform_shipping_number()
	{
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
		$order_info = get_post_meta($order_id, '_order_info', true);
		$email = $order_info['billing']['email'];
		$first_name = $order_info['billing']['first_name'];
		$last_name = $order_info['billing']['last_name'];

		wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date'], 'order_trackingNumber' => $order['order_trackingNumber']));
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

		$item_in_edition = isset($_POST['item_in_edition']) ? intval(wpshop_tools::varSanitizer($_POST['item_in_edition'])) : '0';
		$attribute_code = isset($_POST['attribute_code']) ? wpshop_tools::varSanitizer($_POST['attribute_code']) : '0';
		$attribute_place_display = isset($_POST['attribute_place_display']) ? wpshop_tools::varSanitizer($_POST['attribute_place_display']) : 'backend';
		$current_page_code = isset($_POST['attribute_page_code']) ? wpshop_tools::varSanitizer($_POST['attribute_page_code']) : wpshop_products::currentPageCode;

		/*	Check the type of data for the selected attribute (custom or internal)	*/
		$type = 'custom';
		$real_attr_code = str_replace('custom_', '', $attribute_code);
		if (substr($attribute_code, 0, 9) == 'internal_') {
			$type = 'internal';
			$real_attr_code = str_replace('internal_', '', $attribute_code);
		}
		$attribute = wpshop_attributes::getElement($real_attr_code, "'valid'", 'code');
		$attribute_options_label = isset($_POST['attribute_new_label']) ? wpshop_tools::varSanitizer($_POST['attribute_new_label']) : null;
		$attribute_options_value = sanitize_title($attribute_options_label);

		if ($type == 'custom') {
			/*	Check if the given value does not exist	*/
			$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE (label = %s OR value = %s) AND attribute_id = %d AND status = 'valid'", str_replace(",", ".", $attribute_options_label), $attribute_options_value, $attribute->id);
			$existing_values = $wpdb->get_results($query);

			/*	If the value does not exist, we create it and output, in case it exists alert an error message	*/
			if( count($existing_values) <= 0 ) {
				$result_status = true;
				$position = 1;
				/*	Get the last value position for adding the new at the end	*/
				$query = $wpdb->prepare("SELECT position FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d", $attribute->id);
				$position = $wpdb->get_var($query);

				/*	Add the new value into database	*/
				$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('creation_date' => current_time('mysql', 0), 'status' => 'valid', 'attribute_id' => $attribute->id, 'position' => $position, 'label' => str_replace(",", ".", $attribute_options_label), 'value' => $attribute_options_value));
				$new_option_id = $wpdb->insert_id;
			}
			else {
				$result_status = false;
				$result = __('This value already exist for this attribute', 'wpshop');
			}
		}
		else {
			/*	Check if the given value does not exist	*/
			$query = $wpdb->prepare("SELECT * FROM " . $wpdb->posts . " WHERE post_title = %s AND post_status = 'publish'", $attribute_options_label);
			$existing_values = $wpdb->get_results($query);

			/*	If the value does not exist, we create it and output, in case it exists alert an error message	*/
			if ( count($existing_values) <= 0 ) {
				$result_status = true;
				/*	Create the new value as an entity into post database	*/
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

		if ($result_status) {
			$input = wpshop_attributes::get_attribute_field_definition( $attribute, $new_option_id, array('page_code' => $current_page_code, 'from' => $attribute_place_display) );
			$result = $input['output'] . $input['options'];
		}

		echo json_encode(array($result_status, $result, $real_attr_code));
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
		$input_label=__('Default value', 'wpshop');

		switch($current_type){
			case 'short_text':
			case 'float_field':
				$input_def['type'] = 'text';
				$input_def['value'] = '';
				$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
				break;
			case 'select':
			case 'multiple-select':
				$input_label=__('Options list for attribute', 'wpshop');
				$the_input = wpshop_attributes::get_select_options_list($elementIdentifier, $data_type_to_use);
				break;
			case 'date_field':
				$input_label=__('Use the date of the day as default value', 'wpshop');
				$input_def['type'] = 'checkbox';
				$input_def['possible_value'] = 'date_of_current_day';
				$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using date of the day as value when editing a product', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
				$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
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
				if (($attribute_def['backend_input'] == 'select') || ($attribute_def['backend_input'] == 'multiple-select') && ($attribute_def['data_type_to_use'] == 'custom') ) {
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



/*	Frontend	*/
	/**
	 * Add product to the end user cart
	 */
	function ajax_wpshop_add_to_cart() {
		global $wpshop_cart;
		$product_id = isset($_POST['wpshop_pdt']) ? intval(wpshop_tools::varSanitizer($_POST['wpshop_pdt'])) : null;

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

		$return = $wpshop_cart->add_to_cart( array($product_id), array($product_id=>1), $wpshop_cart_type );
		if ($return == 'success') {
			$cart_page_url = get_permalink( get_option('wpshop_cart_page_id') );
			if ($wpshop_cart_type == 'normal') {
				/*
				 * Template parameters
				*/
				$template_part = 'product_added_to_cart_message';

				/*
				 * Build template
				*/
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

				echo json_encode(array(true, $succes_message_box));
			}
			else {
				echo json_encode(array(true, $cart_page_url));
			}
		}
		else echo json_encode(array(false, $return));

		die();
	}
	add_action('wp_ajax_wpshop_add_product_to_cart', 'ajax_wpshop_add_to_cart');
	add_action('wp_ajax_nopriv_wpshop_add_product_to_cart', 'ajax_wpshop_add_to_cart');


	/**
	 * Add new entity element from anywhere
	 */
	function ajax_wpshop_add_entity() {
		check_ajax_referer( 'wpshop_add_new_entity_ajax_nonce', 'wpshop_ajax_nonce' );

		/*
		 * Store send attribute into a new array for save purpose
		 */
		$attributes = array();
		if ( is_array( $_POST['attribute'] ) ) {
			foreach ( $_POST['attribute'] as $attribute_type => $attribute ) {
				foreach ( $attribute as $attribute_code => $attribute_value ) {
					$attributes[$attribute_code] = $attribute_value;
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