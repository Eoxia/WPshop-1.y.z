<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Define the different method to manage attributes
 *
 *	Define the different method and variable used to manage attributes
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 * @package wpshop
 * @subpackage librairies
 */

/**
 * Define the different method to manage attributes
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_attributes{
	/*	Define the database table used in the current class	*/
	const dbTable = WPSHOP_DBT_ATTRIBUTE;
	/*	Define the url listing slug used in the current class	*/
	const urlSlugListing = WPSHOP_URL_SLUG_ATTRIBUTE_LISTING;
	/*	Define the url edition slug used in the current class	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_ATTRIBUTE_LISTING;
	/*	Define the current entity code	*/
	const currentPageCode = 'attributes';
	/*	Define the page title	*/
	const pageContentTitle = 'Attributes';
	/*	Define the page title when adding an attribute	*/
	const pageAddingTitle = 'Add an attribute';
	/*	Define the page title when editing an attribute	*/
	const pageEditingTitle = 'Attribute "%s" edit';
	/*	Define the page title when editing an attribute	*/
	const pageTitle = 'Attributes list';

	/*	Define the path to page main icon	*/
	public $pageIcon = '';
	/*	Define the message to output after an action	*/
	public $pageMessage = '';

	/**
	 *	Get the url listing slug of the current class
	 *
	 *	@return string The table of the class
	 */
	function setMessage($message){
		$this->pageMessage = $message;
	}
	/**
	 *	Get the url listing slug of the current class
	 *
	 *	@return string The table of the class
	 */
	function getListingSlug(){
		return self::urlSlugListing;
	}
	/**
	 *	Get the url edition slug of the current class
	 *
	 *	@return string The table of the class
	 */
	function getEditionSlug(){
		return self::urlSlugEdition;
	}
	/**
	 *	Get the database table of the current class
	 *
	 *	@return string The table of the class
	 */
	function getDbTable(){
		return self::dbTable;
	}
	/**
	 *	Define the title of the page
	 *
	 *	@return string $title The title of the page looking at the environnement
	 */
	function pageTitle(){
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : '';
		$objectInEdition = isset($_REQUEST['id']) ? wpshop_tools::varSanitizer($_REQUEST['id']) : '';

		$title = __(self::pageTitle, 'wpshop' );
		if($action != ''){
			if(($action == 'edit') || ($action == 'delete')){
				$editedItem = self::getElement($objectInEdition);
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), str_replace("\\", "", $editedItem->frontend_label));
			}
			elseif($action == 'add')
				$title = __(self::pageAddingTitle, 'wpshop');
		}
		elseif((self::getEditionSlug() != self::getListingSlug()) && ($_GET['page'] == self::getEditionSlug()))
			$title = __(self::pageAddingTitle, 'wpshop');

		return $title;
	}

	/**
	 *	Define the different message and action after an action is send through the element interface
	 */
	function elementAction(){
		global $wpdb, $initialEavData;

		$pageMessage = $actionResult = '';
		$attribute_undeletable = unserialize(WPSHOP_ATTRIBUTE_UNDELETABLE);

		/*	Start definition of output message when action is doing on another page	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/****************************************************************************/
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$saveditem = isset($_REQUEST['saveditem']) ? wpshop_tools::varSanitizer($_REQUEST['saveditem']) : '';
		$set_section = !empty($_REQUEST[self::getDbTable()]['set_section']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['set_section']) : '';
		if ( !empty($_REQUEST[self::getDbTable()]['set_section']) ) unset($_REQUEST[self::getDbTable()]['set_section']);
		if(!empty($action) && ($action=='activate') && (!empty($_REQUEST['id']))){
			$query = $wpdb->update(self::getDbTable(), array('status'=>'moderated'), array('id'=>$_REQUEST['id']));
			wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page=' . self::getListingSlug() . "&action=edit&id=" . $_REQUEST['id']));
		}
		if(($action != '') && ($action == 'saveok') && ($saveditem > 0)){
			$editedElement = self::getElement($saveditem);
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}
		elseif(($action != '') && ($action == 'deleteok') && ($saveditem > 0)){
			$editedElement = self::getElement($saveditem, "'deleted'");
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully deleted', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}

		if(!isset($_REQUEST[self::getDbTable()]['status'])){
			$_REQUEST[self::getDbTable()]['status'] = 'moderated';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_historisable'])){
			$_REQUEST[self::getDbTable()]['is_historisable'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_intrinsic'])){
			$_REQUEST[self::getDbTable()]['is_intrinsic'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_requiring_unit'])){
			$_REQUEST[self::getDbTable()]['is_requiring_unit'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_visible_in_front'])){
			$_REQUEST[self::getDbTable()]['is_visible_in_front'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_visible_in_front_listing'])){
			$_REQUEST[self::getDbTable()]['is_visible_in_front_listing'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_used_for_sort_by'])){
			$_REQUEST[self::getDbTable()]['is_used_for_sort_by'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_visible_in_advanced_search'])){
			$_REQUEST[self::getDbTable()]['is_visible_in_advanced_search'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_searchable'])){
			$_REQUEST[self::getDbTable()]['is_searchable'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_used_for_variation'])){
			$_REQUEST[self::getDbTable()]['is_used_for_variation'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_used_in_variation'])){
			$_REQUEST[self::getDbTable()]['is_used_in_variation'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_user_defined'])){
			$_REQUEST[self::getDbTable()]['is_user_defined'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['_display_informations_about_value'])){
			$_REQUEST[self::getDbTable()]['_display_informations_about_value'] = 'no';
		}

		/*	Check frontend input and data type	*/
		if (!empty($_REQUEST[self::getDbTable()]['frontend_input'])) {
			switch ($_REQUEST[self::getDbTable()]['frontend_input']) {
				case 'short_text':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'text';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) ) $_REQUEST[self::getDbTable()]['backend_input'] = 'text';
						$_REQUEST[self::getDbTable()]['data_type'] = 'varchar';
					break;
				case 'date_field':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'text';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) ) $_REQUEST[self::getDbTable()]['backend_input'] = 'text';
						$_REQUEST[self::getDbTable()]['data_type'] = 'datetime';
					break;
				case 'float_field':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'text';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) ) $_REQUEST[self::getDbTable()]['backend_input'] = 'text';
						$_REQUEST[self::getDbTable()]['data_type'] = 'decimal';
					break;
				case 'hidden_field':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'hidden';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) ) $_REQUEST[self::getDbTable()]['backend_input'] = 'text';
						$_REQUEST[self::getDbTable()]['data_type'] = 'varchar';
					break;
				case 'pass_field':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'password';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) ) $_REQUEST[self::getDbTable()]['backend_input'] = 'text';
						$_REQUEST[self::getDbTable()]['data_type'] = 'varchar';
					break;

				case 'select':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'select';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) || empty($_REQUEST['id']) )
							$_REQUEST[self::getDbTable()]['backend_input'] = 'multiple-select';
						$_REQUEST[self::getDbTable()]['data_type'] = 'integer';
					break;
				case 'multiple-select':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'multiple-select';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) || empty($_REQUEST['id']) )
							$_REQUEST[self::getDbTable()]['backend_input'] = 'multiple-select';
						$_REQUEST[self::getDbTable()]['data_type'] = 'integer';
					break;
				case 'radio':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'radio';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) || empty($_REQUEST['id']) )
							$_REQUEST[self::getDbTable()]['backend_input'] = 'multiple-select';
						$_REQUEST[self::getDbTable()]['data_type'] = 'integer';
					break;
				case 'checkbox':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'checkbox';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) || empty($_REQUEST['id']) )
							$_REQUEST[self::getDbTable()]['backend_input'] = 'multiple-select';
						$_REQUEST[self::getDbTable()]['data_type'] = 'integer';
					break;

				case 'textarea':
						$_REQUEST[self::getDbTable()]['frontend_input'] = 'textarea';
						if ( empty($_REQUEST[self::getDbTable()]['backend_input']) || empty($_REQUEST['id']) )
							$_REQUEST[self::getDbTable()]['backend_input'] = 'textarea';
						$_REQUEST[self::getDbTable()]['data_type'] = 'text';
					break;
			}
		}
		else {
			$_REQUEST[self::getDbTable()]['frontend_input'] = 'text';
			if ( empty($_REQUEST[self::getDbTable()]['backend_input']) ) $_REQUEST[self::getDbTable()]['backend_input'] = 'text';
			$_REQUEST[self::getDbTable()]['data_type'] = 'varchar';
		}

		/*	Check if the checkbox for ajax activation is checked for data update	*/
		// if(!isset($_REQUEST[self::getDbTable()]['use_ajax_for_filling_field']) || empty($_REQUEST[self::getDbTable()]['use_ajax_for_filling_field'])){
			// $_REQUEST[self::getDbTable()]['use_ajax_for_filling_field']='no';
		// }
		$_REQUEST[self::getDbTable()]['use_ajax_for_filling_field'] = 'yes';

		/*	Define the database operation type from action launched by the user	 */
		$_REQUEST[self::getDbTable()]['default_value'] = isset($_REQUEST[self::getDbTable()]['default_value']) ? str_replace('"', "'", $_REQUEST[self::getDbTable()]['default_value']) : '';
		if ( $_REQUEST[self::getDbTable()]['data_type'] == 'datetime' ) {
			$date_default_value_trasform_into_config = array('default_value' => $_REQUEST[self::getDbTable()]['default_value'], 'field_options' => $_POST[self::getDbTable() . '_options']);
			$_REQUEST[self::getDbTable()]['default_value'] = serialize( $date_default_value_trasform_into_config );
		}
		/*****************************		GENERIC				**************************/
		/*************************************************************************/
		$pageAction = (!empty($_REQUEST[self::getDbTable()]['frontend_label']) && isset($_REQUEST[self::getDbTable() . '_action'])) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : ((!empty($_GET['action']) && ($_GET['action']=='delete')) ? $_GET['action'] : '');
		$id = isset($_REQUEST[self::getDbTable()]['id']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['id']) : ((!empty($_GET['id'])) ? $_GET['id'] : '');
		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue'))){
			if(current_user_can('wpshop_edit_attributes')){
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete'){
					$attribute_code = $_REQUEST[self::getDbTable()]['code'];
					if(!isset($_REQUEST[self::getDbTable()]['code']) || ($_REQUEST[self::getDbTable()]['code'] == '')){
						$attribute = self::getElement($id, "'valid', 'moderated', 'notused'", 'id');
						$attribute_code = $attribute->code;
					}
					if(!in_array($attribute_code, $attribute_undeletable)){
						if(current_user_can('wpshop_delete_attributes')){
							$_REQUEST[self::getDbTable()]['status'] = 'deleted';
						}
						else{
							$actionResult = 'userNotAllowedForActionDelete';
						}
					}
					else{
						$actionResult = 'unDeletableAtribute';
					}
				}
				$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
			}
			else{
				$actionResult = 'userNotAllowedForActionEdit';
			}
		}
		elseif(($pageAction != '') && (($pageAction == 'delete'))){
			$attribute_code = '';
			if (empty($_REQUEST[self::getDbTable()]['code'])) {
				$attribute = self::getElement($id, "'valid', 'moderated', 'notused', 'deleted'", 'id');
				$attribute_code = $attribute->code;
			}
			if (!in_array($attribute_code, $attribute_undeletable)) {
				if(current_user_can('wpshop_delete_attributes')){
					$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
					$_REQUEST[self::getDbTable()]['status'] = 'deleted';
					$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
				}
				else
					$actionResult = 'userNotAllowedForActionDelete';
			}
			else
				$actionResult = 'unDeletableAtribute';
		}
		elseif(($pageAction != '') && (($pageAction == 'save') || ($pageAction == 'saveandcontinue') || ($pageAction == 'add'))){
			if(current_user_can('wpshop_add_attributes')){
				$_REQUEST[self::getDbTable()]['creation_date'] = date('Y-m-d H:i:s');
				if(trim($_REQUEST[self::getDbTable()]['code']) == ''){
					$_REQUEST[self::getDbTable()]['code'] = $_REQUEST[self::getDbTable()]['frontend_label'];
				}
				$_REQUEST[self::getDbTable()]['code'] = wpshop_tools::slugify(str_replace("\'", "_", str_replace('\"', "_", $_REQUEST[self::getDbTable()]['code'])), array('noAccent', 'noSpaces', 'lowerCase', 'noPunctuation'));
				$code_exists = self::getElement($_REQUEST[self::getDbTable()]['code'], "'valid', 'moderated', 'deleted'", 'code');
				if((is_object($code_exists) || is_array($code_exists)) && (count($code_exists) > 0)){
					$_REQUEST[self::getDbTable()]['code'] = $_REQUEST[self::getDbTable()]['code'] . '_' . (count($code_exists) + rand());
				}
				$actionResult = wpshop_database::save($_REQUEST[self::getDbTable()], self::getDbTable());
				$id = $wpdb->insert_id;
			}
			else{
				$actionResult = 'userNotAllowedForActionAdd';
			}
		}

		/*	When an action is launched and there is a result message	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/************		CHANGE ERROR MESSAGE FOR SPECIFIC CASE					*************/
		/****************************************************************************/
		if($actionResult != ''){
			$elementIdentifierForMessage = __('the attribute', 'wpshop');
			if(!empty($_REQUEST[self::getDbTable()]['name']))$elementIdentifierForMessage = '<span class="bold" >' . $_REQUEST[self::getDbTable()]['frontend_label'] . '</span>';
			if ($actionResult == 'error') {/*	CHANGE HERE FOR SPECIFIC CASE	*/
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . sprintf(__('An error occured while saving %s', 'wpshop'), $elementIdentifierForMessage, ' -> ' . $wpdb->last_error);
			}
			else if (($actionResult == 'done') || ($actionResult == 'nothingToUpdate')) {/*	CHANGE HERE FOR SPECIFIC CASE	*/
				/*****************************************************************************************************************/
				/*************************			CHANGE FOR SPECIFIC ACTION FOR CURRENT ELEMENT				****************************/
				/*****************************************************************************************************************/
				/*	Add the different option for the attribute that are set to combo box for frontend input	*/
				$done_options_value = array();
				$default_value = $_REQUEST[self::getDbTable()]['default_value'];
				$i = 1;
				if ( !empty($_REQUEST['optionsUpdate']) ) {
					/**
					 *	Check if there is an attribute code into sended request or if we have to get the code from database (Bug fix)
					 */
					if (empty($_REQUEST[self::getDbTable()]['code'])) {
						$attribute = self::getElement($id, "'valid', 'moderated', 'notused'", 'id');
						$attribute_code = $attribute->code;
					}
					else {
						$attribute_code = $_REQUEST[self::getDbTable()]['code'];
					}

					foreach ($_REQUEST['optionsUpdate'] as $option_key => $option_label){
						$option_value = !empty($_REQUEST['optionsUpdateValue'][$option_key]) ? str_replace(",", ".", $_REQUEST['optionsUpdateValue'][$option_key]) : '';

						if ( empty($option_value) || !in_array($option_value, $done_options_value) ) {
							/*	Update an existing value only if the value does not exist into existing list	*/
							$label = (($option_label != '') ? $option_label : str_replace(",", ".", $option_value));
							$value = str_replace(",", ".", $option_value);
							if( !WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT ) {
								$label = $option_label;
								$value = sanitize_title($label);
							}
							$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('last_update_date' => current_time('mysql', 0), 'position' => $i, 'label' => $label, 'value' => $value), array('id' => $option_key));
							$done_options_value[] = str_replace(",", ".", $option_value);

							/*	Check if this value is used for price calculation and make update on the different product using this value	*/
							if($attribute_code == WPSHOP_PRODUCT_PRICE_TAX){
								$query = $wpdb->prepare("SELECT entity_id FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " WHERE attribute_id = %d AND value = %d", $id, $option_key);
								$entity_liste_using_this_option_value = $wpdb->get_results($query);

								$query = $wpdb->prepare("
										SELECT
										(SELECT data_type
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_HT_TYPE,
										(SELECT data_type
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_TTC_TYPE,
										(SELECT data_type
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_TYPE,
										(SELECT id
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_HT_ID,
										(SELECT id
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_TTC_ID,
										(SELECT id
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_ID
										", WPSHOP_PRODUCT_PRICE_HT, WPSHOP_PRODUCT_PRICE_TTC, WPSHOP_PRODUCT_PRICE_TAX_AMOUNT, WPSHOP_PRODUCT_PRICE_HT, WPSHOP_PRODUCT_PRICE_TTC, WPSHOP_PRODUCT_PRICE_TAX_AMOUNT);
								$attribute_types = $wpdb->get_row($query);

								if(is_array($entity_liste_using_this_option_value) && (count($entity_liste_using_this_option_value) > 0)){
									foreach($entity_liste_using_this_option_value as $entity){
										$query = $wpdb->prepare("
												SELECT
												(SELECT value
												FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_HT_TYPE . "
												WHERE attribute_id = %d
												AND entity_id = %d) AS PRICE_HT,
												(SELECT value
												FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TTC_TYPE . "
												WHERE attribute_id = %d
												AND entity_id = %d) AS PRICE_TTC,
												(SELECT value
												FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_TYPE . "
												WHERE attribute_id = %d
												AND entity_id = %d) AS PRICE_TAX_AMOUNT", $attribute_types->WPSHOP_PRODUCT_PRICE_HT_ID, $entity->entity_id, $attribute_types->WPSHOP_PRODUCT_PRICE_TTC_ID, $entity->entity_id, $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_ID, $entity->entity_id);
										$product_price_info = $wpdb->get_row($query);

										$ht_amount = $ttc_amount = $tax_amount = 0;
										$tax_rate = 1 + (str_replace(",", ".", $option_value) / 100);
										$ht_amount = str_replace(',', '.', $product_price_info->PRICE_HT);
										$ttc_amount = str_replace(',', '.', $product_price_info->PRICE_TTC);
										if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
											$ttc_amount = $ht_amount * $tax_rate;
											$tax_amount = $ttc_amount - $ht_amount;
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TTC_TYPE, array('value' => $ttc_amount), array('entity_id' => $entity->entity_id, 'attribute_id' => $attribute_types->WPSHOP_PRODUCT_PRICE_TTC_ID));
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_TYPE, array('value' => $tax_amount), array('entity_id' => $entity->entity_id, 'attribute_id' => $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_ID));
										}
										if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
											$ht_amount = $ttc_amount / $tax_rate;
											$tax_amount = $ttc_amount - $ht_amount;
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_HT_TYPE, array('value' => $ht_amount), array('entity_id' => $entity->entity_id, 'attribute_id' => $attribute_types->WPSHOP_PRODUCT_PRICE_HT_ID));
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_TYPE, array('value' => $tax_amount), array('entity_id' => $entity->entity_id, 'attribute_id' => $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_ID));
										}
									}
								}
							}
						}

						if($default_value == $option_key) {
							/*	Update an existing a only if the value does not exist into existing list	*/
							$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $option_key), array('id' => $id));
							$done_options_value[] = str_replace(",", ".", $option_value);
						}
						$i++;
					}
				}
				if ( !empty($_REQUEST['options']) ) {
					foreach ( $_REQUEST['options'] as $option_key => $option_label ) {
						$option_value = !empty($_REQUEST['optionsValue'][$option_key]) ? str_replace(",", ".", $_REQUEST['optionsValue'][$option_key]) : sanitize_title($option_label);

						/*	Check what value to use for the new values	*/
						$label = (!empty($option_label) ? $option_label : str_replace(",", ".", $option_value));
						if( !WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT && empty($option_value) ) {
							$label = $option_label;
							$option_value = sanitize_title($label);
						}

						// If the optionsUpdateValue is empty, set it a empty array to avoid error calling the in_array() function
						$_REQUEST['optionsUpdateValue'] = !empty($_REQUEST['optionsUpdateValue']) ? $_REQUEST['optionsUpdateValue'] : array();

						if (!in_array($option_value, $done_options_value) && !in_array($option_value, $_REQUEST['optionsUpdateValue']) ) {

							$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('creation_date' => current_time('mysql', 0), 'status' => 'valid', 'attribute_id' => $id, 'position' => $i, 'label' => $label, 'value' => $option_value));
							$done_options_value[] = str_replace(",", ".", $option_value);
							$last_insert_id = $wpdb->insert_id;

							if (empty($default_value)) {
								/*	Update an existing a only if the value does not exist into existing list	*/
								$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $last_insert_id), array('id' => $id));
								$done_options_value[] = str_replace(",", ".", $option_value);
							}

						}
						$i++;
					}
				}

				// If the is_used_for_sort_by is mark as yes, we have to get out some attributes and save it separately
				if( (!empty($_REQUEST[self::getDbTable()]['is_used_for_sort_by']) && ($_REQUEST[self::getDbTable()]['is_used_for_sort_by'] == 'yes')) || (!empty($_REQUEST[self::getDbTable()]['is_searchable']) && ($_REQUEST[self::getDbTable()]['is_searchable'] == 'yes')) ){
					$data = query_posts(array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
					$attribute_code = $_REQUEST[self::getDbTable()]['code'];
					if(!isset($_REQUEST[self::getDbTable()]['code']) || ($_REQUEST[self::getDbTable()]['code'] == '')){
						$attribute = self::getElement($id, "'valid', 'moderated', 'notused'", 'id');
						$attribute_code = $attribute->code;
					}
					foreach($data as $post){
						$query = $wpdb->prepare("SELECT value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $_REQUEST[self::getDbTable()]['data_type'] . " WHERE attribute_id = %d AND entity_type_id = %d AND entity_id = %d", $id, $_REQUEST[self::getDbTable()]['entity_id'], $post->ID);
						$value = $wpdb->get_var($query);
						if(!empty($postmeta[$attribute_code])) {
							update_post_meta($post->ID, '_' . $attribute_code, $value/* $postmeta[$attribute_code] */);
						}
					}
					wp_reset_query();
				}

				if ( $pageAction != 'delete' ) {/*	Add the new attribute in the additionnal informations attribute group	*/
					if ( !empty($set_section) ) {
						$choosen_set_section = explode('_', $set_section);
						$set_id = $choosen_set_section[0];
						$group_id = $choosen_set_section[1];
					}
					else{
						$attribute_current_attribute_set = 0;
						$query = $wpdb->prepare("
								SELECT id
								FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_SET_DETAILS
								WHERE ATTRIBUTE_SET_DETAILS.status = 'valid'
								AND ATTRIBUTE_SET_DETAILS.attribute_id = %d
								AND ATTRIBUTE_SET_DETAILS.entity_type_id = %d", $id, $_REQUEST[self::getDbTable()]['entity_id']);
						$attribute_current_attribute_set = $wpdb->get_var($query);

						if($attribute_current_attribute_set <= 0){
							$query = $wpdb->prepare(
									"SELECT
									(SELECT ATTRIBUTE_SET.id
									FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTRIBUTE_SET
									WHERE ATTRIBUTE_SET.entity_id = %d
									AND ATTRIBUTE_SET.default_set = 'yes' ) AS attribute_set_id,
									(SELECT ATTRIBUTE_GROUP.id
									FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
									INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTRIBUTE_SET ON ((ATTRIBUTE_SET.id = ATTRIBUTE_GROUP.attribute_set_id) AND (ATTRIBUTE_SET.entity_id = %d))
									WHERE ATTRIBUTE_GROUP.default_group = 'yes') AS attribute_group_id"
									, $_REQUEST[self::getDbTable()]['entity_id']
									, $_REQUEST[self::getDbTable()]['entity_id']
									, $_REQUEST[self::getDbTable()]['entity_id']
							);
							$wpshop_default_group = $wpdb->get_row($query);

							$set_id = $wpshop_default_group->attribute_set_id;
							$group_id = $wpshop_default_group->attribute_group_id;
						}
					}

					if(!empty($set_id) && !empty($group_id)){
						$query = $wpdb->prepare(
								"SELECT (MAX(position) + 1) AS position
								FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . "
								WHERE attribute_set_id = %s
								AND attribute_group_id = %s
								AND entity_type_id = %s ",
								$set_id,
								$group_id,
								$_REQUEST[self::getDbTable()]['entity_id']
						);
						$wpshopAttributePosition = $wpdb->get_var($query);
						if($wpshopAttributePosition == 0)$wpshopAttributePosition = 1;
						$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $_REQUEST[self::getDbTable()]['entity_id'], 'attribute_set_id' => $set_id, 'attribute_group_id' => $group_id, 'attribute_id' => $id, 'position' => $wpshopAttributePosition));
					}
				}

				/*************************			GENERIC				****************************/
				/*************************************************************************/
				$pageMessage .= '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), $elementIdentifierForMessage);
				/* if(($pageAction == 'edit') || ($pageAction == 'save')){
					wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page=' . self::getListingSlug() . "&action=saveok&saveditem=" . $id));
				}
				else */
				if ( $pageAction == 'add' )
					wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page=' . self::getListingSlug() . "&action=edit&id=" . $id));
				elseif ( $pageAction == 'delete' )
					wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page=' . self::getListingSlug() . "&action=deleteok&saveditem=" . $id));
			}
			elseif(($actionResult == 'userNotAllowedForActionEdit') || ($actionResult == 'userNotAllowedForActionAdd') || ($actionResult == 'userNotAllowedForActionDelete')){
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . __('You are not allowed to do this action', 'wpshop');
			}
			elseif(($actionResult == 'unDeletableAtribute')){
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . __('This attribute could not be deleted due to configuration', 'wpshop');
			}

			if(empty($_REQUEST[self::getDbTable()]['frontend_label']) && ($pageAction!='delete')){
				$pageMessage .= __('Please enter an label for the attribut', 'wpshop');
			}
		}

		self::setMessage($pageMessage);
	}

	/**
	 *	Return the list page content, containing the table that present the item list
	 *
	 *	@return string $listItemOutput The html code that output the item list
	 */
	function elementList() {
		//Create an instance of our package class...
		$wpshop_list_table = new wpshop_attributes_custom_List_table();
		//Fetch, prepare, sort, and filter our data...
		$status="'valid'";
		if(!empty($_REQUEST['attribute_status'])){
			switch($_REQUEST['attribute_status']){
				case 'unactive':
					$status="'moderated', 'notused'";
					if(empty($_REQUEST['orderby']) && empty($_REQUEST['order'])){
						$_REQUEST['orderby']='status';
						$_REQUEST['order']='asc';
					}
					break;
				default:
					$status="'".$_REQUEST['attribute_status']."'";
					break;
			}
		}
		$attr_set_list = self::getElement('', $status);
		$i=0;
		$attribute_set_list=array();
		foreach($attr_set_list as $attr_set){
			if(!empty($attr_set->id) && ($attr_set->code != 'product_attribute_set_id') ){
				$attribute_set_list[$i]['id'] = $attr_set->id;
				$attribute_set_list[$i]['name'] = $attr_set->frontend_label;
				$attribute_set_list[$i]['status'] = $attr_set->status;
				$attribute_set_list[$i]['entity'] = $attr_set->entity;
				$attribute_set_list[$i]['code'] = $attr_set->code;
				$i++;
			}
		}
		$wpshop_list_table->prepare_items($attribute_set_list);

		ob_start();
		?>
<div class="wrap">
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<?php $wpshop_list_table->views() ?>
	<form id="attributes_filter" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page"
			value="<?php echo $_REQUEST['page']; ?>" />
		<!-- Now we can render the completed list table -->
		<?php $wpshop_list_table->display() ?>
	</form>
</div>
<?php
$element_output = ob_get_contents();
ob_end_clean();

		return $element_output;
	}

	/**
	 *	Return the page content to add a new item
	 *
	 *	@return string The html code that output the interface for adding a nem item
	 */
	function elementEdition($itemToEdit = '') {
		global $attribute_displayed_field, $attribute_options_group;
		$dbFieldList = wpshop_database::fields_to_input(self::getDbTable());

		$editedItem = '';
		if($itemToEdit != '')
			$editedItem = self::getElement($itemToEdit);

		$the_form_content_hidden = $the_form_general_content = '';
		$the_form_option_content_list = array();
		foreach($dbFieldList as $input_key => $input_def){
			if(!isset($attribute_displayed_field) || !is_array($attribute_displayed_field) || in_array($input_def['name'], $attribute_displayed_field)){
				$input_def['label'] = $input_def['name'];
				$input_def_id=$input_def['id']='wpshop_' . self::currentPageCode . '_edition_table_field_id_'.$input_def['label'];

				$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
				$requestFormValue = isset($_REQUEST[self::currentPageCode][$input_def['label']]) ? wpshop_tools::varSanitizer($_REQUEST[self::currentPageCode][$input_def['label']]) : '';
				$currentFieldValue = $input_def['value'];
				if(is_object($editedItem))
					$currentFieldValue = $editedItem->$input_def['label'];
				elseif(($pageAction != '') && ($requestFormValue != ''))
					$currentFieldValue = $requestFormValue;

				if($input_def['label'] == 'status'){
					if(in_array('notused', $input_def['possible_value'])){
						$key = array_keys($input_def['possible_value'], 'notused');
						unset($input_def['possible_value'][$key[0]]);
					}
					if(in_array('dbl', $input_def['possible_value'])){
						$key = array_keys($input_def['possible_value'], 'dbl');
						unset($input_def['possible_value'][$key[0]]);
					}

					$input_def['type'] = 'checkbox';
					$input_def['label'] = __('Use this attribute', 'wpshop');
					$input_def['possible_value'] = array('valid');
					$input_def_id.='_valid';
					$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using this attribute', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
				}

				if ( (substr($input_def['label'], 0, 3) == 'is_') || ( $input_def['label'] == '_display_informations_about_value') ) {
					$input_def['type'] = 'checkbox';
					$input_def['possible_value'] = 'yes';
				}
				switch($input_def['label']){
					case 'is_requiring_unit':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using unit with this attribute', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'is_visible_in_front':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for displaying this attribute in shop', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'is_visible_in_front_listing':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for displaying this attribute in product listing in shop', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'is_used_for_sort_by':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for displaying this attribute into sortbar', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'is_searchable':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for including values of this attribute as search parameter', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'is_visible_in_advanced_search':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using in advanced search form', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'frontend_css_class':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Separate with a space each CSS Class', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'backend_css_class':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Separate with a space each CSS Class', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'is_historisable':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box if you want to save the different value this attribute, each time it is modified', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'is_intrinsic':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box if this attribute is intrinsic for a product', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'is_used_for_variation':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box if this attribute is used for variation. It means that the user would be able to choose a value in frontend', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
					case 'is_used_in_variation':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box if you want to use this attribute for variation definition', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
						if ( !empty($editedItem) && ($editedItem->is_used_for_variation == 'yes') ) {
							$input_def['option'] = 'disabled="disabled"';
						}
					break;
					case 'is_user_defined':
						$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box if you want your customer to choose a value for this attribute into frontend product', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
					break;
				}

				$input_def['value'] = $currentFieldValue;
				if($input_def['label'] == 'code')
					$input_def['type'] = 'hidden';
				elseif($input_def['label'] == 'entity_id'){
					$input_def['possible_value'] = wpshop_entities::get_entities_list();
					$input_def['valueToPut'] = 'index';
					$input_def['type'] = 'select';

					$i=0;
					foreach($input_def['possible_value'] as $entity_id => $entity_name) {
						if($i <= 0){
							$current_entity_id = $entity_id;
						}
						$i++;
					}
				}
				elseif($input_def['label'] == '_unit_group_id'){
					$input_def['possible_value'] = wpshop_attributes_unit::get_unit_group();
					$input_def['type'] = 'select';
				}
				elseif($input_def['label'] == '_default_unit'){
					$unit_group_list = wpshop_attributes_unit::get_unit_group();
					$input_def['possible_value'] = wpshop_attributes_unit::get_unit_list_for_group(!empty($editedItem->_unit_group_id)?$editedItem->_unit_group_id:(!empty($unit_group_list)?$unit_group_list[0]->id:''));
					$input_def['type'] = 'select';
				}
				elseif ($input_def['label'] == 'backend_input') {
					if ( !is_object($editedItem) ) {
						$input_def['type'] = 'hidden';
					}
					else {
						$new_possible_value = array();
						switch ( $editedItem->data_type) {
							case 'integer':
								$new_possible_value[__('Checkbox', 'wpshop')] = 'checkbox';
								$new_possible_value[__('Radio button', 'wpshop')] = 'radio';
								$new_possible_value[__('select', 'wpshop')] = 'select';
								$new_possible_value[__('multiple-select', 'wpshop')] = 'multiple-select';
								break;
							case 'varchar':
								switch ( $input_def['value'] ) {
									case 'hidden':
										$new_possible_value[__('Hidden field', 'wpshop')] = 'hidden_field';
										break;
									case 'password':
										$new_possible_value[__('Password field', 'wpshop')] = 'pass_field';
										break;
									default:
										$new_possible_value[__('Text field', 'wpshop')] = 'short_text';
										break;
								}
								break;
							case 'text':
								$new_possible_value[__('Textarea field', 'wpshop')] = 'textarea';
								break;
							case 'decimal':
								$new_possible_value[__('Number field', 'wpshop')] = 'float_field';
								break;
							case 'datetime':
								$new_possible_value[__('Date field', 'wpshop')] = 'date_field';
								break;
						}
						$input_def['possible_value'] = $new_possible_value;
					}
				}
				elseif ($input_def['label'] == 'frontend_input') {
					$new_possible_value = array();

					if ( is_object($editedItem) ) {
						switch ( $editedItem->data_type) {
							case 'integer':
								$new_possible_value[__('Checkbox', 'wpshop')] = 'checkbox';
								$new_possible_value[__('Radio button', 'wpshop')] = 'radio';
								$new_possible_value[__('select', 'wpshop')] = 'select';
								$new_possible_value[__('multiple-select', 'wpshop')] = 'multiple-select';
								break;
							case 'varchar':
									switch ( $input_def['value'] ) {
										case 'hidden':
											$new_possible_value[__('Hidden field', 'wpshop')] = 'hidden_field';
										break;
										case 'password':
											$new_possible_value[__('Password field', 'wpshop')] = 'pass_field';
										break;
										default:
											$new_possible_value[__('Text field', 'wpshop')] = 'short_text';
										break;
									}
								break;
							case 'text':
									$new_possible_value[__('Textarea field', 'wpshop')] = 'textarea';
								break;
							case 'decimal':
									$new_possible_value[__('Number field', 'wpshop')] = 'float_field';
								break;
							case 'datetime':
									$new_possible_value[__('Date field', 'wpshop')] = 'date_field';
								break;
						}
					}
					else {
						$new_possible_value[__('Text field', 'wpshop')] = 'short_text';
						$new_possible_value[__('Number field', 'wpshop')] = 'float_field';
						$new_possible_value[__('Date field', 'wpshop')] = 'date_field';
						$new_possible_value[__('Textarea field', 'wpshop')] = 'textarea';
						$new_possible_value[__('Password field', 'wpshop')] = 'pass_field';
						$new_possible_value[__('Hidden field', 'wpshop')] = 'hidden_field';
						$new_possible_value[__('Checkbox', 'wpshop')] = 'checkbox';
						$new_possible_value[__('Radio button', 'wpshop')] = 'radio';
						$new_possible_value[__('select', 'wpshop')] = 'select';
						$new_possible_value[__('multiple-select', 'wpshop')] = 'multiple-select';
					}

					$input_def['possible_value'] = $new_possible_value;

					if ( !empty($editedItem->frontend_input) ) {
						switch ( $editedItem->frontend_input ) {
							case 'text':
								switch ( $editedItem->data_type ) {
									case 'varchar':
										$input_def['value'] = 'short_text';
									break;
									case 'decimal':
										$input_def['value'] = 'float_field';
									break;
									case 'datetime':
										$input_def['value'] = 'date_field';
									break;
									case 'hidden':
										$input_def['value'] = 'hidden_field';
									break;
									case 'password':
										$input_def['value'] = 'pass_field';
									break;
								}
							break;
							default:
								$input_def['value'] = $editedItem->frontend_input;
							break;
						}
					}
				}

				if(is_object($editedItem) && (($input_def['label'] == 'code') || ($input_def['label'] == 'data_type') || ($input_def['label'] == 'entity_id'))){
					// $input_def['type'] = 'hidden';
					$input_def['option'] = ' disabled="disabled" ';
					$the_form_content_hidden .= '<input type="hidden" name="' . self::getDbTable() . '[' . $input_def['name'] . ']" value="' . $input_def['value'] . '" />';
					$input_def['label'] = $input_def['name'];
					$input_def['name'] = $input_def['name'] . '_already_defined';
				}

				$input_def['value'] = str_replace("\\", "", $input_def['value']);

				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
				if ( $input_def['label'] == 'default_value' ) {
					if ( !empty($editedItem->frontend_input) ) {
						switch ( $editedItem->frontend_input ) {
							case 'text':
								$input_def['type'] = 'text';
								switch ( $editedItem->data_type ) {
									case 'datetime':
										$the_input = wpshop_attributes::attribute_type_date_config( unserialize($input_def['value']) );

										$input_def['label'] = __('Date field configuration','wpshop');
									break;
									default:
										$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
									break;
								}
							break;
							case 'hidden':
									$the_input = '';
								break;
							case 'password':
									$the_input = '';
								break;
							case 'select':
							case 'multiple-select':
							case 'radio':
							case 'checkbox':
								$input_def['label'] = __('Options list for attribute','wpshop') . '
<div class="alignright wpshop_change_select_data_type" >
	+' . __('Change data type for this attribute', 'wpshop') . '
</div>';
								$the_input = wpshop_attributes::get_select_options_list($itemToEdit, $editedItem->data_type_to_use);

								break;
							case 'textarea':
								$input_def['type'] = 'textarea';
								$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
							break;
						}
					}
					else {
						$input_def['type']='text';
						$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
					}
				}
				if( $input_def['label'] == '_unit_group_id') {
					$the_input .= '<div id="wpshop_loader_input_group_unit"></div>';
					$the_input .= '<a class="button-primary" href="#wpshop_unit_group_list" id="wpshop_attribute_group_unit_manager_opener">'.__('Manage group unit', 'wpshop').'</a></div>';
				}

				if( $input_def['label'] == '_default_unit') {
					$the_input .= '<div id="wpshop_loader_input_unit"></div>';
					$the_input .= '<a class="button-primary" href="#wpshop_unit_list" id="wpshop_attribute_unit_manager_opener">'.__('Manage units', 'wpshop').'</a></div>';
					$the_input .= '<input type="hidden" name="input_wpshop_load_attribute_unit_list" id="input_wpshop_load_attribute_unit_list" value="' . wp_create_nonce("wpshop_load_attribute_unit_list") . '" />';
					$the_input .= '<div id="wpshop_attribute_unit_manager" title="' . __('Unit management', 'wpshop') . '" class="wpshopHide" ><div class="loading_picture_container" id="product_chooser_picture" ><img src="' . WPSHOP_LOADING_ICON . '" alt="loading..." /></div></div>';
				}


				if($input_def['type'] != 'hidden'){
					if ( ($input_def['label'] == 'entity_id') && is_object($editedItem) ) {
						$the_input .= '<br/><span class="wpshop_duplicate_attribute" >' . __('Duplicate this attribute to another entity', 'wpshop') . '</span>';
					}
					$input = '
		<tr class="wpshop_' . self::currentPageCode . '_edition_table_line wpshop_' . self::currentPageCode . '_edition_table_line_'.$input_def['name'].'" >
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_label wpshop_' . self::currentPageCode . '_edition_table_field_label_'.$input_def['name'].'" ><label for="'.$input_def_id.'" >' . __($input_def['label'], 'wpshop') . '</label></td>
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_input wpshop_' . self::currentPageCode . '_edition_table_field_input_'.$input_def['name'].'" >' . $the_input . '</td>
		</tr>';
					if ( (substr($input_def['label'], 0, 3) == 'is_') || (substr($input_def['label'], 0, 1) == '_') || in_array($input_def['label'], unserialize( WPSHOP_ATTRIBUTE_DEF_COLUMN_INTO_OPTIONS )) )
						$the_form_option_content_list[$input_def['label']] = $input;
					else {
						$the_form_general_content .= $input;
						if ( ($input_def['label'] == 'frontend_input') && !is_object($editedItem) ) {

							$the_input = wpshop_attributes_set::get_attribute_set_complete_list($current_entity_id,  self::getDbTable(), self::currentPageCode);

							$input = '
		<tr class="wpshop_' . self::currentPageCode . '_edition_table_line wpshop_' . self::currentPageCode . '_edition_table_line_set_section" >
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_label wpshop_' . self::currentPageCode . '_edition_table_field_label_set_section" ><label for="'.self::currentPageCode.'_set_section" >' . __('Affect this new attribute to the set section', 'wpshop') . '</label></td>
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_input wpshop_' . self::currentPageCode . '_edition_table_field_input_set_section" >' . $the_input . '</td>
		</tr>';
							$the_form_general_content .= $input;
						}
					}
				}
				else{
					$the_form_content_hidden .= '
				' . $the_input;
				}
			}
		}

		$section_legend = '';
		$section_page_code = self::currentPageCode;
		$section_content = $the_form_general_content;
		ob_start();
		include(WPSHOP_TEMPLATES_DIR.'admin/admin_box_section.tpl.php');
		$the_form_general_content = ob_get_contents();
		ob_end_clean();

		if (!empty($the_form_option_content_list)) {
			$the_form_option_content_section='';
			foreach ($attribute_options_group as $group_name => $group_content) {
				$section_content = '';
				foreach ($group_content as $group_code) {
					if (array_key_exists($group_code, $the_form_option_content_list)) {
						$section_content .= $the_form_option_content_list[$group_code];
						unset($the_form_option_content_list[$group_code]);
					}
				}
				$section_legend = __($group_name,'wpshop');
				$section_page_code = self::currentPageCode;

				ob_start();
				include(WPSHOP_TEMPLATES_DIR.'admin/admin_box_section.tpl.php');
				$the_form_option_content_section .= ob_get_contents();
				ob_end_clean();
			}

			/*	Check there are other attributes to display not in defined group	*/
			if (!empty($the_form_option_content_list)) {
				$section_legend = __('General options','wpshop');
				$section_content = implode('', $the_form_option_content_list);
				$section_page_code = self::currentPageCode;

				ob_start();
				include(WPSHOP_TEMPLATES_DIR.'admin/admin_box_section.tpl.php');
				$the_form_option_content = ob_get_contents();
				ob_end_clean();

				$the_form_option_content .= $the_form_option_content_section;
			}
		}

		/*	Default content for the current page	*/
		$bloc_list[self::currentPageCode]['main_info']['title']=__('Main informations', 'wpshop');
		$bloc_list[self::currentPageCode]['main_info']['content']=$the_form_general_content;

		$bloc_list[self::currentPageCode]['options']['title']=__('Options', 'wpshop');
		$bloc_list[self::currentPageCode]['options']['content']=$the_form_option_content;

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="#" >
	' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (isset($_REQUEST['action']) && ($_REQUEST['action'] != '') ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'save') , 'hidden') . '
	' . wpshop_form::form_input(self::currentPageCode . '_form_has_modification', self::currentPageCode . '_form_has_modification', 'no' , 'hidden') . $the_form_content_hidden . wpshop_display::custom_page_output_builder($bloc_list, WPSHOP_ATTRIBUTE_EDITION_PAGE_LAYOUT) . '
</form>
<div title="' . __('Change data type for selected attribute', 'wpshop') . '" id="wpshop_dialog_change_select_data_type" ><div id="wpshop_dialog_change_select_data_type_container" ></div></div>';
		$input_def['possible_value'] = wpshop_entities::get_entities_list();
		unset($input_def['possible_value'][$current_entity_id]);
		$input_def['valueToPut'] = 'index';
		$input_def['type'] = 'select';
		$input_def['name'] = 'wpshop_entity_to_duplicate_to';
		$input_def['id'] = 'wpshop_entity_to_duplicate_to';
		$the_form .= '
<div title="' . __('Duplicate attribute to another entity', 'wpshop') . '" id="wpshop_dialog_duplicate_attribute" >
	' . __('Choose an entity to copy the selected attribute to', 'wpshop') . '
	' . wpshop_form::check_input_type($input_def) . '
</div>';

		$the_form .= '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("'.self::getDbTable().'", "' . __('Are you sure you want to quit this page? You will loose all current modification', 'wpshop') . '", "' . __('Are you sure you want to delete this attributes group?', 'wpshop') . '");

		jQuery("#wpshop_dialog_duplicate_attribute").dialog({
			autoOpen: false,
			width: 500,
			height: 100,
			modal: true,
			dialogClass: "wpshop_uidialog_box",
			resizable: false,
			buttons:{
				"'.__('Duplicate', 'wpshop').'": function(){
					var data = {
						action: "wpshop_duplicate_attribute",
						wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_duplicate_attribute") . '",
						attribute_id: jQuery("#wpshop_attributes_edition_table_field_id_id").val(),
						entity: jQuery("#wpshop_entity_to_duplicate_to").val()
					};
					jQuery.post(ajaxurl, data, function(response) {
						if (response[0]) {
							jQuery("#wpshop_dialog_duplicate_attribute").append(response[1]);
						}
						else {
							alert(response[1]);
						}
					}, "json");
				},
				"'.__('Cancel', 'wpshop').'": function(){
					jQuery(this).dialog("close");
					jQuery(".wpshop_duplicate_attribute_result").remove();
				}
			}
		});
		jQuery(".wpshop_duplicate_attribute").live("click", function(){
			jQuery("#wpshop_dialog_duplicate_attribute").dialog("open");
		});

		jQuery("#wpshop_dialog_change_select_data_type").dialog({
			autoOpen: false,
			width: 800,
			height: 200,
			modal: true,
			dialogClass: "wpshop_uidialog_box",
			resizable: false,
			buttons:{
				"'.__('Change type', 'wpshop').'": function(){
					var delete_entity = false;
					if(jQuery("#delete_entity").is(":checked")){
						var delete_entity = true;
					}
					var delete_items_of_entity = false;
					if(jQuery("#delete_items_of_entity").is(":checked")){
						var delete_items_of_entity = true;
					}
					var data = {
						action: "attribute_select_data_type_change",
						wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_change_select_data_type_change") . '",
						attribute_id: jQuery("#wpshop_attributes_edition_table_field_id_id").val(),
						internal_data: jQuery("#internal_data").val(),
						data_type: jQuery("#wpshop_attribute_change_data_type_new_type").val(),
						delete_entity: delete_entity,
						delete_items_of_entity: delete_items_of_entity
					};
					jQuery.post(ajaxurl, data, function(response) {
						jQuery(".wpshop_attributes_edition_table_field_input_default_value").html( response );
						jQuery("#wpshop_dialog_change_select_data_type").dialog("close");
					}, "json");
				},
				"'.__('Cancel', 'wpshop').'": function(){
					jQuery(this).dialog("close");
				}
			}
		});

		jQuery(".wpshop_attribute_change_select_data_type_deletion_input").live("click",function() {
			var display = false;
			if (jQuery(".wpshop_attribute_change_select_data_type_deletion_input_item").is(":checked") ) {
				display = true;
			}
			if (jQuery(".wpshop_attribute_change_select_data_type_deletion_input_entity").is(":checked") ) {
				display = true;
			}
			if (display) {
				jQuery(".wpshop_attribute_change_data_type_alert").show();
			}
			else {
				jQuery(".wpshop_attribute_change_data_type_alert").hide();
			}
		});

		jQuery(".wpshop_change_select_data_type").live("click",function(){
			jQuery("#wpshop_dialog_change_select_data_type_container").html(jQuery("#wpshopLoadingPicture").html());
			jQuery("#wpshop_dialog_change_select_data_type").dialog("open");

			var data = {
				action: "attribute_select_data_type",
				current_attribute: jQuery("#wpshop_attributes_edition_table_field_id_id").val(),
				wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_change_select_data_type") . '"
			};
			jQuery.post(ajaxurl, data, function(response) {
				jQuery("#wpshop_dialog_change_select_data_type_container").html( response );
			}, "json");

		});
		jQuery("#wpshop_attributes_edition_table_field_id__unit_group_id").change(function(){
			change_unit_list();
		});';

		if ( !is_object($editedItem) )  {
			$the_form .= '
		jQuery("#wpshop_attributes_edition_table_field_id_frontend_input").change(function(){
			jQuery(".wpshop_attributes_edition_table_field_input_default_value").html(jQuery("#wpshopLoadingPicture").html());

			var data = {
				action: "attribute_output_type",
				current_type: jQuery(this).val(),
				wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_output_type_selection") . '"
			};
			jQuery.getJSON(ajaxurl, data, function(response) {
				jQuery(".wpshop_attributes_edition_table_field_input_default_value").html((response[0]));
				jQuery(".wpshop_attributes_edition_table_field_label_default_value label").html((response[1]));
			});

// 			var data = {
// 				action: "attribute_frontend_input_type",
// 				current_type: jQuery(this).val(),
// 				wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_frontend_input_type") . '"
// 			};
// 			jQuery.getJSON(ajaxurl, data, function(response) {
// 				jQuery("#wpshop_attributes_edition_table_field_id_frontend_input").html(response);
// 			});

		});';
		}

		$the_form .= '
			jQuery("#wpshop_attributes_edition_table_field_id_entity_id").change(function(){
			jQuery(".wpshop_attributes_edition_table_field_input_set_section").html(jQuery("#wpshopLoadingPicture").html());

			var data = {
				action: "attribute_entity_set_selection",
				current_entity_id: jQuery(this).val(),
				wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_entity_set_selection") . '"
			};
			jQuery.post(ajaxurl, data, function(response) {
				jQuery(".wpshop_attributes_edition_table_field_input_set_section").html( response );
			}, "json");
		});


		jQuery("#wpshop_attributes_edition_table_field_id_is_used_for_variation").click(function(){
			if ( jQuery(this).is(":checked") ) {
				jQuery("#wpshop_attributes_edition_table_field_id_is_used_in_variation").prop("checked", false);
				jQuery("#wpshop_attributes_edition_table_field_id_is_used_in_variation").prop("disabled", true);
			}
			else {
				jQuery("#wpshop_attributes_edition_table_field_id_is_used_in_variation").prop("disabled", false);
			}
		});
	});
	function change_unit_list(){
		var data = {
					action: "load_attribute_unit_list",
					wpshop_ajax_nonce: jQuery("#input_wpshop_load_attribute_unit_list").val(),
					current_group: jQuery("#wpshop_attributes_edition_table_field_id__unit_group_id").val(),
					selected_list:"unit"
				};
			//Response, update the combo box
				jQuery.post(ajaxurl, data, function(response) {
					if ( response[0] ) {
						jQuery("#wpshop_attributes_edition_table_field_id__default_unit").html(response[1]);
					}
					else {
						alert( response[1] );
					}
				}, "json");

	}
</script>';

		return $the_form;
	}
	/**
	 *	Return the different button to save the item currently being added or edited
	 *
	 *	@return string $currentPageButton The html output code with the different button to add to the interface
	 */
	function getPageFormButton($element_id = 0){
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$currentPageButton = '';

		 //$currentPageButton .= '<h2 class="cancelButton alignleft" ><a href="' . admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&amp;page=' . self::getListingSlug()) . '" class="button add-new-h2" >' . __('Back', 'wpshop') . '</a></h2>';

		if(($action == 'add') && (current_user_can('wpshop_add_attributes')))
			$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Add', 'wpshop') . '" />';

		elseif(current_user_can('wpshop_edit_attributes'))
		$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Save', 'wpshop') . '" />';

		$attribute_undeletable = unserialize(WPSHOP_ATTRIBUTE_UNDELETABLE);
		$attribute = self::getElement($element_id, "'valid', 'moderated', 'notused'", 'id');
		$attribute_code = !empty($attribute->code)?$attribute->code:'';
		if(current_user_can('wpshop_delete_attributes') && ($action != 'add') && !in_array($attribute_code, $attribute_undeletable))
			$currentPageButton .= '<input type="button" class="button-secondary wpshop_delete_element_button wpshop_delete_element_button_'.self::currentPageCode.'" id="delete" name="delete" value="' . __('Delete', 'wpshop') . '" />';

		return $currentPageButton;
	}

	/**
	 *	Get the existing attribute list into database
	 *
	 *	@param integer $element_id optionnal The attribute identifier we want to get. If not specify the entire list will be returned
	 *	@param string $element_status optionnal The status of element to get into database. Default is set to valid element
	 *	@param mixed $field_to_search optionnal The field we want to check the row identifier into. Default is to set id
	 *
	 *	@return object $element_list A wordpress database object containing the attribute list
	 */
	function getElement($element_id = '', $element_status = "'valid', 'moderated', 'notused'", $field_to_search = 'id', $list = false){
		global $wpdb;
		$element_list = array();
		$moreQuery = "";

		if($element_id != ''){
			$moreQuery .= "
					AND CURRENT_ELEMENT." . $field_to_search . " = '" . $element_id . "' ";
		}
		if(!empty($_REQUEST['orderby']) && !empty($_REQUEST['order'])){
			$moreQuery .= "
					ORDER BY " . $_REQUEST['orderby'] . "  " . $_REQUEST['order'];
		}

		$query = $wpdb->prepare(
				"SELECT CURRENT_ELEMENT.*, ENTITIES.post_name as entity
				FROM " . self::getDbTable() . " AS CURRENT_ELEMENT
				INNER JOIN " . $wpdb->posts . " AS ENTITIES ON (ENTITIES.ID = CURRENT_ELEMENT.entity_id)
				WHERE CURRENT_ELEMENT.status IN (".$element_status.") " . $moreQuery, ''
		);

		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if(($element_id == '') || $list){
			$element_list = $wpdb->get_results($query);
		}
		else{
			$element_list = $wpdb->get_row($query);
		}

		return $element_list;
	}

	/**
	 *	Save the different value for attribute of a given entity type and entity
	 *
	 *	@param array $attributeToSet The list of attribute with each value to set
	 *	@param integer $entityTypeId The entity type identifier (products/categories/...)
	 *	@param integer $entityId The entity identifier we want to save attribute for (The specific product/category/...)
	 *	@param string $language The language to set the value for into database
	 *
	 */
	function saveAttributeForEntity($attributeToSet, $entityTypeId, $entityId, $language = WPSHOP_CURRENT_LOCALE, $from = '') {
		global $wpdb;
		/* Recuperation de l'identifiant de l'utilisateur connecte */
		$user_id = function_exists('is_user_logged_in') && is_user_logged_in() ? get_current_user_id() : '0';
		$sent_attribute_list = array();

		if ( !empty($attributeToSet) ) {
			foreach ($attributeToSet as $attributeType => $attributeTypeDetails) {

				/* Preparation des parametres permettant de supprimer les bonnes valeurs des attributs suivant la configuration de la boutique et de la methode de mise a jour */
				$delete_current_attribute_values_params = array(
						'entity_id' => $entityId,
						'entity_type_id' => $entityTypeId
					);
				if ( WPSHOP_ATTRIBUTE_VALUE_PER_USER ) {
					$delete_current_attribute_values_params['user_id'] = $user_id;
				}

				if(!empty($attributeTypeDetails) && is_array($attributeTypeDetails)) {

					foreach($attributeTypeDetails as $attribute_code => $attributeValue) {

						if ( $attributeType == 'decimal' ) {
							$attributeValue = str_replace(',', '.', $attributeValue);
						}
						if ( ($attributeType == 'integer') && !is_array($attributeValue) ) {
							$attributeValue = (int)$attributeValue;
						}
						$more_query_params_values = array();
						if($attribute_code != 'unit') {

							$unit_id = 0;
							if(isset($attributeTypeDetails['unit'][$attribute_code])){
								$unit_id = $attributeTypeDetails['unit'][$attribute_code];
							}

							$currentAttribute = self::getElement($attribute_code, "'valid'", 'code');
							$sent_attribute_list[] = $currentAttribute->id;

							/*	Enregistrement de la valeur actuelle de l'attribut dans la table d'historique si l'option historique est activee sur l'attribut courant	*/
							if ($currentAttribute->is_historisable == 'yes') {
								$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType . " WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d", $entityTypeId, $currentAttribute->id, $entityId);
								$attribute_histo = $wpdb->get_results($query);
								if(!empty($attribute_histo)){
									$attribute_histo_content['status'] = 'valid';
									$attribute_histo_content['creation_date'] = current_time('mysql', 0);
									$attribute_histo_content['creation_date_value'] = $attribute_histo[0]->creation_date_value;
									$attribute_histo_content['original_value_id'] = $attribute_histo[0]->value_id;
									$attribute_histo_content['entity_type_id'] = $attribute_histo[0]->entity_type_id;
									$attribute_histo_content['attribute_id'] = $attribute_histo[0]->attribute_id;
									$attribute_histo_content['entity_id'] = $attribute_histo[0]->entity_id;
									$attribute_histo_content['unit_id'] = $attribute_histo[0]->unit_id;
									$attribute_histo_content['language'] = $attribute_histo[0]->language;
									$attribute_histo_content['value'] = $attribute_histo[0]->value;
									$attribute_histo_content['value_type'] = WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType;
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, $attribute_histo_content);
								}
							}
							$attributeValue = str_replace("\\", "", $attributeValue);

							if ( empty($from) || (!empty($attributeValue)) ) {
								$wpdb->delete(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType, array_merge($delete_current_attribute_values_params, array('attribute_id' => $currentAttribute->id)));

								/*	Insertion de la nouvelle valeur de l'attribut dans la base	*/
								$query_params = array(
									'value_id' => '',
									'entity_type_id' => $entityTypeId,
									'attribute_id' => $currentAttribute->id,
									'entity_id' => $entityId,
									'unit_id' => $unit_id,
									'language' => $language,
									'user_id' => $user_id,
									'creation_date_value' => current_time('mysql', 0)
								);
								/*	Si l'attribut courant est contenu dans un tableau (exemple: select multiple) on lit tout le tableau et on enregistre chaque valeur separement	*/
								if(is_array($attributeValue)){
									foreach($attributeValue as $a){
										$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType, array_merge($query_params, array('value' => $a)));
									}
								}
								else{
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType, array_merge($query_params, array('value' => $attributeValue)));
								}

								/*	Dans le cas ou l'attribut courant est utilise dans l'interface permettant de trier les produits (option de l'attribut) on defini une meta specifique	*/
								if ( ( ($currentAttribute->is_used_for_sort_by == 'yes') || ($currentAttribute->is_searchable == 'yes'))  && !empty($attributeValue)) :
									update_post_meta($entityId, '_'.$attribute_code, $attributeValue);
								endif;

								/*	Enregistrement de toutes les valeurs des attributs dans une meta du produit	*/
								if (!empty($_POST['attribute_option'][$attribute_code])) {
									$value = self::get_attribute_type_select_option_info($attributeTypeDetails[$attribute_code], 'value');
									if (strtolower($value) == 'yes') :
										update_post_meta($entityId, 'attribute_option_'.$attribute_code, $_POST['attribute_option'][$attribute_code]);
									else :
										delete_post_meta($entityId, 'attribute_option_'.$attribute_code);
									endif;
								}
							}
						}
					}

					if ( empty($from) ) {
						$query = $wpdb->prepare("SELECT value_id FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType . " WHERE attribute_id NOT IN ('" . implode("', '", $sent_attribute_list) . "') AND entity_id = %d AND entity_type_id = %d", $entityId, $entityTypeId);
						$attr_to_delete = $wpdb->get_results($query);
						if(!empty($attr_to_delete)){
							foreach ($attr_to_delete as $value) {
								$wpdb->delete(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType, array_merge($delete_current_attribute_values_params, array('value_id' => $value->value_id)));
							}
						}
					}
				}
			}
		}
	}

	/**
	 *	Return the value for a given attribute of a given entity type and a given entity
	 *
	 *	@param string $attributeType The extension of the database table to get the attribute value in
	 *	@param integer $attributeId The attribute identifier we want to get the value for
	 *	@param integer $entityTypeId The entity type identifier we want to get the attribute value for (example: product = 1)
	 	*	@param integer $entityId The entity id we want the attribute value for
	 *
	 *	@return object $attributeValue A wordpress database object containing the value of the attribute for the selected entity
	 */
	function getAttributeValueForEntityInSet($attributeType, $attributeId, $entityTypeId, $entityId, $atribute_params = array()) {
		global $wpdb;
		$attributeValue = '';

		$query_params = "";
		$query_params_values = array($attributeId, $entityTypeId, $entityId);
		if(WPSHOP_ATTRIBUTE_VALUE_PER_USER && (isset($atribute_params['intrinsic']) && ($atribute_params['intrinsic'] != 'yes'))){
			$query_params = "
				AND ATTR_VAL.user_id = %d";
			$query_params_values[] = get_current_user_id();
		}
		$query = $wpdb->prepare(
			"SELECT ATTR_VAL.value, ATTR_VAL.unit_id, ATTR_VAL.user_id
			FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType . " AS ATTR_VAL
			WHERE ATTR_VAL.attribute_id = %d
				AND ATTR_VAL.entity_type_id = %d
				AND ATTR_VAL.entity_id = %d" . $query_params . "
			ORDER BY ATTR_VAL.creation_date_value ASC",
			$query_params_values
		);
		$attributeValue = $wpdb->get_results($query);

		if ( ( (count($attributeValue) <= 1 ) && !empty($attributeValue[0]) ) && ( empty($atribute_params['frontend_input']) || ($atribute_params['frontend_input'] != 'multiple-select') ) ) {
			$attributeValue = $attributeValue[0];
		}

// 		else {
// 			$entity_meta = get_post_meta($entityId, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
// 			if ( !empty($entity_meta) ) {
// 				$query = $wpdb->prepare("SELECT code FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE id = %d AND entity_id = %d ", $attributeId, $entityTypeId);
// 				$attribute_code = $wpdb->get_var($query);
// 				$attributeValue = !empty($entity_meta[$attribute_code]) ? $entity_meta[$attribute_code] : null;
// 			}
// 		}
// 		if(!WPSHOP_ATTRIBUTE_VALUE_PER_USER && (count($attributeValue) > 1)){
// 			$attributeValue = $attributeValue[0];
// 		}

		return $attributeValue;
	}

	/**
	 *	Get the existing element list into database
	 *
	 *	@param integer $elementId optionnal The element identifier we want to get. If not specify the entire list will be returned
	 *	@param string $elementStatus optionnal The status of element to get into database. Default is set to valid element
	 *
	 *	@return object $elements A wordpress database object containing the element list
	 */
	function getElementWithAttributeAndValue($entityId, $elementId, $language, $keyForArray = '', $outputType = ''){
		$elements = array();
		$elementsWithAttributeAndValues = self::get_attribute_list_for_item($entityId, $elementId, $language);

		foreach ( $elementsWithAttributeAndValues as $elementDefinition ) {
			$arrayKey = $elementDefinition->attribute_id;
			if ( $keyForArray == 'code' ) {
				$arrayKey = $elementDefinition->attribute_code;
			}
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['code'] = $elementDefinition->attribute_set_section_code;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['display_on_frontend'] = $elementDefinition->display_on_frontend;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['code'] = $elementDefinition->code;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['data_type'] = $elementDefinition->data_type;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['backend_table'] = $elementDefinition->backend_table;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['backend_input'] = $elementDefinition->backend_input;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['frontend_input'] = $elementDefinition->frontend_input;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['frontend_label'] = $elementDefinition->frontend_label;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['attribute_code'] = $elementDefinition->attribute_code;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['data_type_to_use'] = $elementDefinition->data_type_to_use;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['is_visible_in_front'] = $elementDefinition->is_visible_in_front;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['is_visible_in_front_listing'] = $elementDefinition->is_visible_in_front_listing;
			$attributeValueField = 'attribute_value_' . $elementDefinition->data_type;

			// Manage the value differently if it is an array or not
			if ( !empty($elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value']) ) {
				if (is_array($elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'])) {
					$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'][] = $elementDefinition->$attributeValueField;
				}
				else {
					$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'] = array($elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'],$elementDefinition->$attributeValueField);
				}
			}
			else {
				$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'] = $elementDefinition->$attributeValueField;
			}

			if ( $elementDefinition->backend_input == 'multiple-select' ) {
				$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['select_value'] = self::get_attribute_type_select_option_info($attributeValueField, 'value');
			}

			$attributeUnitField = 'attribute_unit_' . $elementDefinition->data_type;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['unit'] = $elementDefinition->$attributeUnitField;
		}

		return $elements;
	}

	function get_attribute_list_for_item($entityId, $elementId, $language = WPSHOP_CURRENT_LOCALE, $defined_entity_type = '') {
		global $wpdb;
		$elementsWithAttributeAndValues = array();
		$moreQuery = "";

		$entity_type = empty($defined_entity_type) ? get_post_type( $elementId ) : $defined_entity_type;

		$query = $wpdb->prepare(
				"SELECT POST_META.*,
					ATTR.code, ATTR.id as attribute_id, ATTR.data_type, ATTR.backend_table, ATTR.backend_input, ATTR.frontend_input, ATTR.frontend_label, ATTR.code AS attribute_code, ATTR.is_recordable_in_cart_meta, ATTR.default_value as default_value, ATTR.data_type_to_use, ATTR.is_visible_in_front, ATTR.is_visible_in_front_listing,
					ATTR_VALUE_VARCHAR.value AS attribute_value_varchar, ATTR_UNIT_VARCHAR.unit AS attribute_unit_varchar,
					ATTR_VALUE_DECIMAL.value AS attribute_value_decimal, ATTR_UNIT_DECIMAL.unit AS attribute_unit_decimal,
					ATTR_VALUE_TEXT.value AS attribute_value_text, ATTR_UNIT_TEXT.unit AS attribute_unit_text,
					ATTR_VALUE_INTEGER.value AS attribute_value_integer, ATTR_UNIT_INTEGER.unit AS attribute_unit_integer,
					ATTR_VALUE_DATETIME.value AS attribute_value_datetime, ATTR_UNIT_DATETIME.unit AS attribute_unit_datetime,
					ATTRIBUTE_GROUP.code AS attribute_set_section_code, ATTRIBUTE_GROUP.name AS attribute_set_section_name, ATTRIBUTE_GROUP.display_on_frontend
				FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS EAD ON (EAD.attribute_id = ATTR.id)
					INNER JOIN " . $wpdb->postmeta . " AS POST_META ON ((POST_META.post_id = %d) AND (POST_META.meta_key = '_" . $entity_type . "_attribute_set_id') AND (POST_META.meta_value = EAD.attribute_set_id))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP  ON (ATTRIBUTE_GROUP.id = EAD.attribute_group_id)
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR . " AS ATTR_VALUE_VARCHAR ON ((ATTR_VALUE_VARCHAR.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_VARCHAR.attribute_id = ATTR.id) AND (ATTR_VALUE_VARCHAR.entity_id = %d) AND (ATTR_VALUE_VARCHAR.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_VARCHAR ON ((ATTR_UNIT_VARCHAR.id = ATTR_VALUE_VARCHAR.unit_id) AND (ATTR_UNIT_VARCHAR.status = 'valid'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATTR_VALUE_DECIMAL ON ((ATTR_VALUE_DECIMAL.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_DECIMAL.attribute_id = ATTR.id) AND (ATTR_VALUE_DECIMAL.entity_id = %d) AND (ATTR_VALUE_DECIMAL.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_DECIMAL ON ((ATTR_UNIT_DECIMAL.id = ATTR_VALUE_DECIMAL.unit_id) AND (ATTR_UNIT_DECIMAL.status = 'valid'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT . " AS ATTR_VALUE_TEXT ON ((ATTR_VALUE_TEXT.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_TEXT.attribute_id = ATTR.id) AND (ATTR_VALUE_TEXT.entity_id = %d) AND (ATTR_VALUE_TEXT.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_TEXT ON ((ATTR_UNIT_TEXT.id = ATTR_VALUE_TEXT.unit_id) AND (ATTR_UNIT_TEXT.status = 'valid'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATTR_VALUE_INTEGER ON ((ATTR_VALUE_INTEGER.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_INTEGER.attribute_id = ATTR.id) AND (ATTR_VALUE_INTEGER.entity_id = %d) AND (ATTR_VALUE_INTEGER.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_INTEGER ON ((ATTR_UNIT_INTEGER.id = ATTR_VALUE_INTEGER.unit_id) AND (ATTR_UNIT_INTEGER.status = 'valid'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME . " AS ATTR_VALUE_DATETIME ON ((ATTR_VALUE_DATETIME.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_DATETIME.attribute_id = ATTR.id) AND (ATTR_VALUE_DATETIME.entity_id = %d) AND (ATTR_VALUE_DATETIME.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_DATETIME ON ((ATTR_UNIT_DATETIME.id = ATTR_VALUE_DATETIME.unit_id) AND (ATTR_UNIT_DATETIME.status = 'valid'))
				WHERE
					ATTR.status = 'valid'
					AND EAD.status = 'valid'
					AND ATTRIBUTE_GROUP.status = 'valid'
					AND EAD.entity_type_id = '" . $entityId . "' " . $moreQuery . "
			ORDER BY ATTRIBUTE_GROUP.position",
		$elementId, $elementId, $elementId, $elementId, $elementId, $elementId);

		$elementsWithAttributeAndValues = $wpdb->get_results($query);

		return $elementsWithAttributeAndValues;
	}

	/**
	 * Check if an attribute or an attribute set section have to be displayed on the product output un frontend
	 *
	 * @param string $attribute_main_config The main configuration for display for the attribute
	 * @param array $attribute_custom_config The custom config defined into product page
	 * @param string $attribute_or_set Define if we check for an attribute or for an attribute set section
	 * @param string $attribute_code The code of element to check the display for
	 * @param string $output_type The current output type
	 *
	 * @return boolean The result to know if the element has to be displayed on frontend
	 */
	function check_attribute_display( $attribute_main_config, $attribute_custom_config, $attribute_or_set, $attribute_code, $output_type) {
		if ( $attribute_main_config === 'yes' ) {
			$attribute_output = true;
			if ( (in_array($attribute_or_set, array('attribute', 'attribute_set_section')) && empty($attribute_custom_config[$attribute_or_set])) || empty($attribute_custom_config) ) {
				$attribute_output = true;
			}
			else if ( empty($attribute_custom_config[$attribute_or_set][$attribute_code]) || empty($attribute_custom_config[$attribute_or_set][$attribute_code][$output_type]) || (!empty($attribute_custom_config[$attribute_or_set][$attribute_code][$output_type]) && ( $attribute_custom_config[$attribute_or_set][$attribute_code][$output_type] == 'no')) )  {
				$attribute_output = false;
			}
		}
		elseif ( $attribute_main_config === 'no' ) {
			$attribute_output = false;
			if ( empty($attribute_custom_config[$attribute_or_set]) || empty($attribute_custom_config[$attribute_or_set][$attribute_code]) ) {
				$attribute_output = false;
			}
			else if ( !empty($attribute_custom_config[$attribute_or_set][$attribute_code][$output_type]) && ( $attribute_custom_config[$attribute_or_set][$attribute_code][$output_type] == 'yes') )  {
				$attribute_output = true;
			}
		}

		return $attribute_output;
	}

	/**
	 * Traduit le shortcode et affiche la valeur d'un attribut donnï¿½
	 * @param array $atts : tableau de paramï¿½tre du shortcode
	 * @return mixed
	 **/
	function wpshop_att_val_func($atts) {
		global $wpdb;
		global $wp_query;

		$attribute = self::getElement($atts['attid']);
		if(empty($atts['pid'])) $atts['pid'] = $wp_query->posts[0]->ID;

		$attribute_main_config = ( empty($atts['output_type']) || ($atts['output_type'] == 'complete_sheet') ) ? $attribute->is_visible_in_front : $attribute->is_visible_in_front_listing;
		$output_type = ( empty($atts['output_type']) || ($atts['output_type'] == 'complete_sheet') ) ? 'complete_sheet' : 'mini_output';
		$product_attribute_custom_config = get_post_meta($atts['pid'], WPSHOP_PRODUCT_FRONT_DISPLAY_CONF, true);
		$display_attribute_value = wpshop_attributes::check_attribute_display( $attribute_main_config, $product_attribute_custom_config, 'attribute', $attribute->code, $output_type);

		if ( !empty( $attribute->data_type ) && $display_attribute_value ) {
			$query = $wpdb->prepare("SELECT value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute->data_type . " WHERE entity_id=%d AND attribute_id=%d", $atts['pid'], $atts['attid']);
			$data = $wpdb->get_results($query);
			return $data[0]->value;
		}

		return null;
	}

	/**
	 * Build the output for an attribute field
	 *
	 * @param object $attribute The complete definition for an attribute
	 * @param string $attribute_value Optionnal The current value for the attribute
	 * @param array $specific_argument Optionnal The different parameters used for filter output
	 * @return array The definition for the field used to display an attribute
	 */
	function get_attribute_field_definition( $attribute, $attribute_value = '', $specific_argument = array() ) {
		$wpshop_price_attributes = unserialize(WPSHOP_ATTRIBUTE_PRICES);
		$wpshop_weight_attributes = unserialize(WPSHOP_ATTRIBUTE_WEIGHT);
		$input_def = array();
		$input_def['option'] = $input_def['field_container_class'] = '';
		$attributeInputDomain = (!empty($specific_argument['field_custom_name_prefix']) || (!empty($specific_argument['field_custom_name_prefix']) && ($specific_argument['field_custom_name_prefix'] == 'empty')) ) ? $specific_argument['field_custom_name_prefix'] : ((!empty($specific_argument['page_code']) ? $specific_argument['page_code'] . '_' : '' ) . 'attribute[' . $attribute->data_type . ']');
		$input_def['input_domain'] = $attributeInputDomain;
		$input_def['id'] = (!empty($specific_argument) && !empty($specific_argument['field_id']) ? $specific_argument['field_id'] . '_' : '') . 'attribute_' . $attribute->id;
		$input_def['intrinsec'] = $attribute->is_intrinsic;
		$input_def['name'] = $attribute->code;
		$input_def['type'] = wpshop_tools::defineFieldType($attribute->data_type, $attribute->frontend_input);
		$input_def['label'] = $attribute->frontend_label;
		$input_def['value'] = stripslashes($attribute->default_value);
		$input_def['is_unique'] = $attribute->is_unique;
		$input_def['_need_verification'] = $attribute->_need_verification;
		$input_def['required'] = $attribute->is_required;
		$input_def['frontend_verification'] = $attribute->frontend_verification;
		$input_def['data_type'] = $attribute->data_type;

		if ( !empty($attribute_value) && !is_object($attribute_value) ) {
			$input_def['value'] = ($attribute_value);
		}
		else if ( !empty($attribute_value->value) ) {
			$input_def['value'] = stripslashes($attribute_value->value);
		}
		else if ( !empty($specific_argument['element_identifier']) && empty($attribute_value) && (get_post_status($specific_argument['element_identifier']) != 'auto-draft') ) {
			$input_def['value'] = '';
		}

		$input_def['options'] = '';
		$input_more_class = !empty($specific_argument['input_class']) ? $specific_argument['input_class'] : '';
		if ($attribute->data_type == 'datetime') {
			$date_config = unserialize( $attribute->default_value );
			if ((($date_config['default_value'] == '') || ($date_config['default_value'] == 'date_of_current_day')) && ($date_config['default_value'] == 'date_of_current_day')) {
				$input_def['value'] = date('Y-m-d');
			}
			else {
				$input_def['value'] = !empty($attribute_value) && is_string($attribute_value) ? $attribute_value : '';
			}
			$input_more_class .= ' wpshop_input_datetime ';
			$field_script = '<script type="text/javascript" ><!--//--><![CDATA[//><!--
	jQuery(document).ready(function(){
		wpshop("#' . $input_def['id'] . '").datepicker();
		wpshop("#' . $input_def['id'] . '").datepicker("option", "dateFormat", "yy-mm-dd");
		wpshop("#' . $input_def['id'] . '").datepicker("option", "changeMonth", true);
		wpshop("#' . $input_def['id'] . '").datepicker("option", "changeYear", true);
		wpshop("#' . $input_def['id'] . '").datepicker("option", "yearRange", "-90:+10");
		wpshop("#' . $input_def['id'] . '").datepicker("option", "navigationAsDateFormat", true);
		wpshop("#' . $input_def['id'] . '").val("' . str_replace(" 00:00:00", "", $input_def['value']) . '");';

			$script_options = $script_options_params = array();
			if ( !empty($date_config['field_options']['attribute_type_date_options_day_to_show']) ) {
				$day_to_show_list = '    ';
				foreach ( $date_config['field_options']['attribute_type_date_options_day_to_show'] as $day_to_show ) {
					$day_to_show_list .= '(date.getDay() == ' . $day_to_show . ') || ';
				}
				$script_options[] = '( ' . substr($day_to_show_list, 0, -4) . ' )';
			}

			if ( !empty($date_config['field_options']['attribute_type_date_options_available_date_type'][0]) ) {
				if ( !empty($date_config['field_options']['attribute_type_date_options_available_date']) ) {
					$available_date = ' ';
					foreach ( $date_config['field_options']['attribute_type_date_options_available_date'] as $avalaible_date_list ) {
						if ( !empty($avalaible_date_list) ) {
							$available_date .= '"' . $avalaible_date_list . '",';
						}
					}
					$script_options_params[] = 'var dates = [' . substr($available_date, 0, -1) . ']';
					$script_options[] = '(jQuery.inArray(dmy, dates) ' . ($date_config['field_options']['attribute_type_date_options_available_date_type'][0] == 'available' ? '!=' : '==') . ' -1)';
				}
			}

			if ( !empty( $script_options ) ) {
				$field_script .= '
		wpshop("#' . $input_def['id'] . '").datepicker("option", "beforeShowDay", function(date){
			' . implode(' ', $script_options_params) . ';
			var Y = date.getFullYear();
			var M = (date.getMonth()+1);
			if( M < 10) {
				M = "0" + M;
			}
			var D = date.getDate();
			if( D < 10) {
				D = "0" + D;
			}
			dmy = Y + "-" + M + "-" + D;
			if ( ' . implode(' && ', $script_options) . ' ) {
				return [true, ""];
		  	}
			else {
		   		return [false,""];
		  	}
		});';
			}

			$field_script .= '
	});
//--><!]]></script>';
			$input_def['options'] .= $field_script;
		}
		if ( in_array($attribute->backend_input, array('multiple-select', 'select', 'radio', 'checkbox'))) {
			$input_more_class .= (!empty($specific_argument['no_chosen']) ? '' : ' chosen_select ' );
			$input_def['type'] = ((!empty($specific_argument['from']) && ($specific_argument['from'] == 'frontend')) || (!is_admin() && empty($specific_argument['from'])) ? $attribute->frontend_input : $attribute->backend_input);
			$input_def['valueToPut'] = 'index';

			$select_display = self::get_select_output($attribute, $specific_argument);
			$input_def['options'] .= $select_display['more_input'];
			$input_def['possible_value'] = $select_display['possible_value'];
			$input_def['options'] .= '<input type="hidden" value="' . str_replace("\\", "", $input_def['value']) . '" name="wpshop_product_attribute_' . $attribute->code . '_current_value" id="wpshop_product_attribute_' . $attribute->code . '_current_value" />';
			if ( in_array($attribute->backend_input, array('multiple-select', 'checkbox')) ) {
				$input_def['options'] .= wpshop_display::display_template_element('select_list_multiple_bulk_action', array( 'CURRENT_ATTRIBUTE_ID' => $input_def['id'], 'CURRENT_ATTRIBUTE_CODE' => $attribute->code), array(), 'admin');
			}
		}
		$input_def['label_pointer'] = 'for="' . $input_def['id'] . '"';
		if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox')){
			$input_def['label_pointer'] = '';
		}

		/*
		 * Specifc treatment for price attributes
		 */
		if((WPSHOP_PRODUCT_PRICE_PILOT == 'HT') && ($attribute->code == WPSHOP_PRODUCT_PRICE_TTC) ){
			$input_def['option'] .= ' readonly="readonly" ';
			$input_more_class .= ' wpshop_prices_readonly';
		}
		elseif((WPSHOP_PRODUCT_PRICE_PILOT == 'TTC') && ($attribute->code == WPSHOP_PRODUCT_PRICE_HT) ){
			$input_def['option'] .= ' readonly="readonly" ';
			$input_more_class .= ' wpshop_prices_readonly';
		}
		if ($attribute->code == WPSHOP_PRODUCT_PRICE_TAX_AMOUNT) {
			$input_def['option'] .= ' readonly="readonly" ';
			$input_more_class .= ' wpshop_prices_readonly';
		}

		$input_def['label'] = str_replace("\\", "", $input_def['label']);
// 		$input_def['value'] = str_replace("\\", "", $input_def['value']);
		$input_def['option'] .= ' class="wpshop_product_attribute_' . $attribute->code . $input_more_class . ' ' . (( is_admin() ) ? $attribute->backend_css_class : $attribute->frontend_css_class) . '" ';
		$input_def['title'] = !empty($attribute->frontend_help_message) ? ' title="' . $attribute->frontend_help_message . '" ' : '';

		if (($attribute->is_intrinsic == 'yes') && ((!empty($input_def['value'])) || ($input_def['value'] > 0))) {
			$input_def['option'] .= ' readonly="readonly" ';
		}

		/*
		 * Add the unit to the attribute if attribute configuration is set to yes
		 */
		if ($attribute->is_requiring_unit == 'yes') {
			if ( in_array($attribute->code, $wpshop_price_attributes) ) {
				$input_def['options'] .= '&nbsp;<span class="attribute_currency" id="attribute_currency_' . $attribute->id . '" >' . wpshop_tools::wpshop_get_currency() . '</span>';
			}
			elseif ( in_array($attribute->code, $wpshop_weight_attributes) ) {
				$input_def['options'] .= '&nbsp;<span class="attribute_weight" id="attribute_weight_' . $attribute->id . '" >' . __('Kilogram', 'wpshop') . '</span>';
			}
			else {
				unset($unit_input_def);
				$unit_input_def['possible_value'] = wpshop_attributes_unit::get_unit_list_for_group($attribute->_unit_group_id);
				$unit_input_def['type'] = 'select';
				$unit_input_def['option'] = ' class="wpshop_attribute_unit_input chosen_select" ';
				$unit_input_def['id'] = $specific_argument['page_code'] . '_' . $specific_argument['element_identifier'] . '_unit_attribute_' . $attribute->id;
				$unit_input_def['name'] = $attribute->code;
				$unit_input_def['value'] = (!empty($attribute_value->unit_id) ? $attribute_value->unit_id : '');
				if($unit_input_def['value'] == ''){
					if ( $attribute->_default_unit > 0 ) {
						$unit_input_def['value'] = $attribute->_default_unit;
					}
					else {
						$unit_input_def['value'] = wpshop_attributes_unit::get_default_unit_for_group($attribute->_unit_group_id);
					}
				}
				$input_def['options'] .= wpshop_form::check_input_type($unit_input_def, $attributeInputDomain . '[unit]');
			}
		}

		/*
		 * Add indication on postage cost tax
		 */
		if ( $attribute->code == WPSHOP_COST_OF_POSTAGE ) {
			$input_def['options'] .= ' <span class="attribute_currency" >' . __('ATI', 'wpshop') . '</span>';
		}

		/*
		 * Create the field output
		 */
		if ( is_admin() && ($attribute->data_type == 'datetime') && ($attribute->is_user_defined == 'yes') && (empty($specific_argument['from']) || ($specific_argument['from'] != 'frontend')) ) {
			$input_def['output'] = sprintf(__('You select this field to be defined by final customer into frontend part. To change this behaviour you have to change attribute option "%s"', 'wpshop'),__('is_user_defined', 'wpshop'));
			$input_def['options'] = '';
			$input_def['label_pointer'] = '';
			$input_def['option'] = substr( $input_def['option'], 0 , -2 ) . ' wpshop_attributes_is_user_defined_admin_field "';
			$input_def['field_container_class'] .= 'wpshop_attributes_is_user_defined_admin_container';
		}
		else {
			$input_def['output'] = wpshop_form::check_input_type($input_def, $attributeInputDomain);
		}

		return $input_def;
	}

	/**
	 *
	 * @param array $attribute_list
	 * @param string $output_from
	 * @return string The output for
	 */
	function display_attribute( $attribute_code, $output_from = 'admin', $output_specs = array() ) {
		$output = '';
		/*	Get the page code	*/
		$currentPageCode = !empty($output_specs['page_code']) ? $output_specs['page_code'] : '';
		$element_identifier = !empty($output_specs['element_identifier']) ? $output_specs['element_identifier'] : '';

		/*	Get attribute definition	*/
		$attribute_def = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
		/*	Get attribute input definition	*/
		$current_value = (!empty($output_specs['current_value']) ? $output_specs['current_value'] : '');
		$input = wpshop_attributes::get_attribute_field_definition( $attribute_def, $current_value, array_merge($output_specs, array('input_class' => ' wpshop_attributes_display', 'from' => $output_from)) );

		/*	Create default output	*/
		$input_to_display = $input['output'] . $input['options'];

		/*	Check if current field is linked to an addon, and if the addon is activated	*/
		$addons_list = unserialize(WPSHOP_ADDONS_LIST);
		foreach ( $addons_list as $addon_code => $addon_def ) {
			if ( in_array($attribute_code, $addon_def) ) {
				if ( constant($addon_code) === false ) {
					$input_to_display = '<a href="' . admin_url('options-general.php?page=wpshop_option#wpshop_addons_option') . '" >' . __("This addon isn't activated, click to activate",'wpshop') . '</a>';
				}
			}
		}

		/*	Check the prices attribute because output for this attributes is customized	*/
		$price_tab = unserialize(WPSHOP_ATTRIBUTE_PRICES);
		unset($price_tab[array_search(WPSHOP_COST_OF_POSTAGE, $price_tab)]);

		$output['field'] = '
<div class="clear" >
	<div class="wpshop_form_label ' . $currentPageCode . '_' . $input['name'] . '_label ' . (in_array($attribute_def->code, $price_tab) ? $currentPageCode . '_prices_label ' : '') . ' alignleft" >
		<label ' . $input['label_pointer'] . ' >' . __($input['label'], 'wpshop') . ($attribute_def->is_required == 'yes' ? ' <span class="wpshop_required" >*</span>' : '') . '</label>
	</div>
	<div class="wpshop_form_input_element ' . $currentPageCode . '_' . $input['name'] . '_input ' . (in_array($attribute_def->code, $price_tab) ? $currentPageCode . '_prices_input ' : '') . ' ' . $input['field_container_class'] . ' alignleft" >
		' . $input_to_display . '
	</div>';

		/*
		 * Display attribute option if applicable
		 */
		if ( $output_from == 'admin') {
			$attribute_option_display = $attribute_def->backend_input=='select' && strtolower(self::get_attribute_type_select_option_info($input['value'], 'value'))=='yes' ? '' : ' wpshopHide';

			$output['field'] .= '
	<div class="attribute_option_'.$attribute_def->code.''.$attribute_option_display.'" >'.self::get_attribute_option_fields($element_identifier, $attribute_def->code).'</div>';
		}

		$output['field'] .= '
</div>';
		$output['field_definition'] = $input;

		return $output;
	}

	/**
	 * Manage display for the output when user uses a shortcode for attributes display
	 * @param array $shorcode_args The list of argument passed through the shortcode
	 */
	function wpshop_attributes_shortcode( $shorcode_args ) {
		$output = '';
		/*
		 * Read the attribute list
		*/
		foreach ( explode(', ', $shorcode_args['attributes']) as $attribute_code ) {
			$attribute_output_def = wpshop_attributes::display_attribute( $attribute_code, $shorcode_args['from'] );
			$output .= $attribute_output_def['field'];
		}

		return $output;
	}

	/**
	 *
	 * @param unknown_type $attributeSetId
	 * @param unknown_type $currentPageCode
	 * @param unknown_type $itemToEdit
	 * @param unknown_type $outputType
	 * @return Ambigous <multitype:, string>
	 */
	function entities_attribute_box($attributeSetId, $currentPageCode, $itemToEdit, $outputType = 'box') {
		$box = $box['box'] = $box['boxContent'] = $box['generalTabContent'] = array();

		/*	Get the attribute set details in order to build the product interface	*/
		$productAttributeSetDetails = wpshop_attributes_set::getAttributeSetDetails($attributeSetId, "'valid'");
		$attribute_specification = array('page_code' => $currentPageCode, 'element_identifier' => $itemToEdit, 'field_id' => $currentPageCode . '_' . $itemToEdit . '_');

		if ( count($productAttributeSetDetails) > 0 ) {
			/*	Read the attribute list in order to output	*/
			$shortcodes_attr = '';
			$shortcodes_to_display = false;
			$attribute_set_id_is_present = false;
			foreach($productAttributeSetDetails as $productAttributeSetDetail){
				$shortcodes = $currentTabContent = '';
				$output_nb = 0;
				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute){
						if ( !empty($attribute->id) ) {
							if ( $attribute->code == 'product_attribute_set_id' ) {
								$attribute_set_id_is_present = true;
							}

							/*
							 * Generic part for attribute field output
							 */
							$value = wpshop_attributes::getAttributeValueForEntityInSet($attribute->data_type, $attribute->id, wpshop_entities::get_entity_identifier_from_code($currentPageCode), $itemToEdit, array('intrinsic' => $attribute->is_intrinsic, 'backend_input' => $attribute->backend_input));
							$attribute_specification['current_value'] = $value;
							$attribute_output_def = wpshop_attributes::display_attribute( $attribute->code, 'admin', $attribute_specification);

							/*	Manage specific field as the attribute_set_id in product form	*/
							if ( $attribute_output_def['field_definition']['name'] == 'product_attribute_set_id' ) {
								$product_attribute_set = get_post_meta($itemToEdit, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
								$attribute_output_def['field_definition']['value'] = !empty($product_attribute_set) ? $product_attribute_set : $attributeSetId;
								$attribute_output_def['field_definition']['type'] = 'hidden';
							}

							if ( $attribute_output_def['field_definition']['type'] != 'hidden' ) {
								$currentTabContent .= $attribute_output_def['field'];

								$shortcode_code_def=array();
								$shortcode_code_def['attribute_'.str_replace('-', '_', sanitize_title($attribute_output_def['field_definition']['label']))]['main_code'] = 'wpshop_att_val';
								$shortcode_code_def['attribute_'.str_replace('-', '_', sanitize_title($attribute_output_def['field_definition']['label']))]['attrs_exemple']['type'] = $attribute->data_type;
								$shortcode_code_def['attribute_'.str_replace('-', '_', sanitize_title($attribute_output_def['field_definition']['label']))]['attrs_exemple']['attid'] = $attribute->id;
								$shortcode_code_def['attribute_'.str_replace('-', '_', sanitize_title($attribute_output_def['field_definition']['label']))]['attrs_exemple']['pid'] = $itemToEdit;
								ob_start();
								wpshop_shortcodes::output_shortcode('attribute_'.str_replace('-', '_', sanitize_title($attribute_output_def['field_definition']['label'])), $shortcode_code_def, 'wpshop_product_shortcode_display wpshop_product_attribute_shortcode_display wpshop_product_attribute_shortcode_display_'.str_replace('-', '_', sanitize_title($attribute_output_def['field_definition']['label'])).' clear');
								$shortcodes .= '<li class="clear" >'.sprintf(__('Insertion code for the attribute %s for this product', 'wpshop'), '<span>'.__($attribute_output_def['field_definition']['label'], 'wpshop').'</span>').ob_get_contents().'</li>';
								ob_end_clean();
							}
							else {
								$currentTabContent .=  wpshop_form::check_input_type($attribute_output_def['field_definition'], $attribute_output_def['field_definition']['input_domain']);
							}
							$output_nb++;
						}
					}

					$shortcode_code['attributes_set']['main_code'] = 'wpshop_att_group';
					$shortcode_code['attributes_set']['attrs_exemple']['pid'] = $itemToEdit;
					$shortcode_code['attributes_set']['attrs_exemple']['sid'] = $productAttributeSetDetail['id'];
					ob_start();
					wpshop_shortcodes::output_shortcode('attributes_set', $shortcode_code, 'wpshop_product_shortcode_display wpshop_product_attribute_group_shortcode_display wpshop_product_attribute_group_shortcode_display_'.str_replace('-', '_', sanitize_title($productAttributeSetDetail['name'])).' clear');
					$attribute_group_display = sprintf(__('Insertion code for attribute group %s for this product', 'wpshop'), '<span>'.$productAttributeSetDetail['name'].'</span>').ob_get_contents().'<ul class="" >'.$shortcodes.'</ul>';
					ob_end_clean();

					if( WPSHOP_PRODUCT_SHORTCODE_DISPLAY_TYPE == 'each-box' )
						$currentTabContent .= '<div class="clear" ><strong>'.__('Shortcodes','wpshop').'</strong> - <a href="#" class="show-hide-shortcodes">' . __('Display', 'wpshop') . '</a><div class="wpshop_product_shortcode_display wpshop_product_shortcode_display_container wpshopHide" >' . $attribute_group_display . '</div></div>';
					else
						$shortcodes_attr .= $attribute_group_display;

					if ( $output_nb <= 0 ) {
						$currentTabContent = __('Nothing avaiblable here. You can go in attribute management interface in order to add content here.', 'wpshop');
					}
				}

				if ($output_nb > 0) {
					$shortcodes_to_display = true;
					if ( $outputType == 'box' ) {
						$box['box'][$productAttributeSetDetail['code']] = $productAttributeSetDetail['name'];
						$box['box'][$productAttributeSetDetail['code'].'_backend_display_type'] = $productAttributeSetDetail['backend_display_type'];
						$box['boxContent'][$productAttributeSetDetail['code']] = '
			<div id="wpshop_' . $currentPageCode . '_' . wpshop_tools::slugify($productAttributeSetDetail['code'], array('noAccent')) . '_form" >' . $currentTabContent . '
							</div><div class="clear" ></div>';
					}
					else if ( $outputType == 'column' ) {
						$currentTabContent = str_replace('wpshop_form_input_element', 'wpshop_form_input_column', $currentTabContent);
						$currentTabContent = str_replace('wpshop_form_label', 'wpshop_form_label_column', $currentTabContent);

						$box['columnTitle'][$productAttributeSetDetail['code']] = __($productAttributeSetDetail['name'], 'wpshop');
						$box['columnContent'][$productAttributeSetDetail['code']] = $currentTabContent;
					}
				}
			}

			if( !$attribute_set_id_is_present ) {
				/*	Get attribute definition	*/
				$attribute_def = wpshop_attributes::getElement('product_attribute_set_id', "'valid'", 'code');
				/*	Get attribute input definition	*/
				$input = wpshop_attributes::get_attribute_field_definition( $attribute_def, $attributeSetId, array_merge($attribute_specification, array('input_class' => ' wpshop_attributes_display', 'from' => 'admin')) );
				$input['type'] = 'hidden';

				$box['boxMore'] = wpshop_form::check_input_type($input, $input['input_domain']);
			}

			/*	Ajout de la boite permettant d'ajouter des valeurs aux attributs de type liste deroulante a la volee	*/
			$dialog_title = __('New value for attribute', 'wpshop');
			$dialog_identifier = 'wpshop_new_attribute_option_value_add';
			$dialog_input_identifier = 'wpshop_new_attribute_option_value';
			ob_start();
			include(WPSHOP_TEMPLATES_DIR.'admin/add_new_element_dialog.tpl.php');
			$box['boxMore'] .= ob_get_contents();
			ob_end_clean();
			$box['boxMore'] .= '<input type="hidden" name="wpshop_attribute_type_select_code" value="" id="wpshop_attribute_type_select_code" />';

			if ( $shortcodes_to_display ) {
				switch ( WPSHOP_PRODUCT_SHORTCODE_DISPLAY_TYPE ) {
					case 'fixed-tab':
					case 'movable-tab':
						if ($outputType == 'box') {
							$box['box']['shortcode'] = __('Product Shortcodes', 'wpshop');
							$box['boxContent']['shortcode'] = $shortcodes_attr;
							$box['box']['shortcode_backend_display_type'] = WPSHOP_PRODUCT_SHORTCODE_DISPLAY_TYPE;
						}
						else{
							$box['columnTitle']['shortcode'] = __('Product Shortcodes', 'wpshop');
							$box['columnContent']['shortcode'] = $shortcodes_attr;
						}
						break;
				}
			}
		}

		return $box;
	}

	/**
	 * Generate the list of element to put into a combobox
	 *
	 * @param object $attribute Complete definition of attribute to generate output for
	 * @return array The output for the combobox
	 */
	function get_select_output($attribute, $provenance = array()) {
		global $wpdb;
		$ouput = array();
		$ouput['more_input'] = '';

		if ( $attribute->data_type_to_use == 'custom') {
			$query = $wpdb->prepare("SELECT id, label, value, '' as name FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d AND status = 'valid' ORDER BY position", $attribute->id);
			$attribute_select_options = $wpdb->get_results($query);

			/*	Read existing element list for creating the possible values	*/
			foreach ($attribute_select_options as $index => $option) :
				$attribute_select_options_list[$option->id] = $option->label;

				$ouput['more_input'] .= '<input type="hidden" value="' . (WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT ? str_replace("\\", "", $option->value) : str_replace("\\", "", $option->label)) . '" name="wpshop_product_attribute_' . $attribute->code . '_value_' . $option->id . '" id="wpshop_product_attribute_' . $attribute->code . '_value_' . $option->id . '" />';
			endforeach;
		}
		elseif ( $attribute->data_type_to_use == 'internal')  {
			switch ($attribute->default_value) {
				case 'users':
					$users = get_users('orderby=nicename');
					foreach($users as $user){
						$attribute_select_options_list[$user->ID] = $user->display_name;
					}
				break;
				default:
					wp_reset_query();
					$wpshop_attr_custom_post_query = new WP_Query(array(
						'post_type' => $attribute->default_value,
						'posts_per_page' => -1,
					));

					if($wpshop_attr_custom_post_query->have_posts()):
						foreach($wpshop_attr_custom_post_query->posts as $post){
							$attribute_select_options_list[$post->ID] = $post->post_title;
						}
					endif;
					wp_reset_query();
				break;
			}
		}

		/*	There is no value existing for this value	*/
		if (empty($attribute_select_options_list)) :
			$ouput['more_input'].=__('Nothing found for this field', 'wpshop');
		else:
			/*	Add a default value to the combobox list	*/
// 			if ( !in_array($attribute->frontend_input, array('radio', 'checkbox')) ) $ouput['possible_value'][] = __('Choose...', 'wpshop');
			foreach ( $attribute_select_options_list as $option_key => $option_value ) {
				$ouput['possible_value'][$option_key] = stripslashes($option_value);
			}
		endif;

		/*	Add a extra element to create a new element into list	*/
		if ( is_admin() && (empty($provenance['from']) || ($provenance['from'] != 'frontend')) ) {
			/**	$ouput['more_input'] .= '<img src="'.WPSHOP_MEDIAS_ICON_URL.'add.png" id="new_value_pict_' . $attribute->code . '" alt="'.__('Add a new value for this attribute', 'wpshop').'" title="'.__('Add a new value for this attribute', 'wpshop').'" class="wpshop_icons wpshop_icons_add_new_value_to_option_list wpshop_icons_add_new_value_to_option_list_'.$attribute->code.'" />';	*/
		}
		else {
			$tpl_component = array();
			$tpl_component['NEW_ELEMENT_CREATION_FIELD'] = 'attribute[new_value_creation][' . $attribute->code . ']';
			$ouput['more_input'] .= wpshop_display::display_template_element('quick_entity_specific_field_new_element', $tpl_component);
		}

		return $ouput;
	}

	function get_affected_value_for_list( $attribute_code, $element_id, $attribute_data_type ) {
		global $wpdb;
		$affected_value = array();

		if ( $attribute_data_type == 'custom' ) {
			$query = $wpdb->prepare("
SELECT ATT_SELECT_OPTIONS_VALUE.id AS chosen_val, ATT_SELECT_OPTIONS_VALUE.value
FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATT
	INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATT_INT_VAL ON ( (ATT_INT_VAL.attribute_id = ATT.id) AND (ATT_INT_VAL.entity_id = %d) )
	INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " AS ATT_SELECT_OPTIONS_VALUE ON ( (ATT_SELECT_OPTIONS_VALUE.attribute_id = ATT.id) AND (ATT_SELECT_OPTIONS_VALUE.id = ATT_INT_VAL.value) )
WHERE ATT.code = %s
	AND ATT_SELECT_OPTIONS_VALUE.status = 'valid'
GROUP BY ATT.id, chosen_val", $element_id, $attribute_code);
		}
		else {
			$query = $wpdb->prepare("
SELECT P.ID AS chosen_val, P.post_title
FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATT
	INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATT_INT_VAL ON ( (ATT_INT_VAL.attribute_id = ATT.id) AND (ATT_INT_VAL.entity_id = %d) )
	INNER JOIN " . $wpdb->posts . " AS P ON ( P.id = ATT_INT_VAL.value )
WHERE ATT.code = %s
	AND P.post_status = 'publish'
GROUP BY ATT.id, chosen_val", $element_id, $attribute_code);
		}

		$attribute_values_for_variations = $wpdb->get_results($query);
		foreach ( $attribute_values_for_variations as $attribute_def ) {
			$affected_value[] = $attribute_def->chosen_val;
		}

		return $affected_value;
	}

	function get_attribute_option_output($item, $attr_code, $attr_option, $additionnal_params = '') {
		switch($attr_code){
			case 'is_downloadable_':
				$option = get_post_meta($item['item_id'], 'attribute_option_'.$attr_code, true);
				switch($attr_option){
					case 'file_url':
						if(in_array($additionnal_params['order_status'], array('completed', 'shipped')) && (!empty($item['item_'.$attr_code]) && (strtolower($item['item_'.$attr_code])=='yes'))) {
							$file_url = isset($option[$attr_option]) ? $option[$attr_option] : false;
							return $file_url;
						}
						return false;
						break;
				}
				break;
		}
	}

	function get_attribute_option_fields($postid, $code) {

		switch($code){
			case 'is_downloadable_':
				$data = get_post_meta($postid, 'attribute_option_'.$code, true);
				$data['file_url'] = !empty($data['file_url'])?$data['file_url']:__('No file selected', 'wpshop');

				$fields = '<div class="wpshop_form_label alignleft">&nbsp;</div>
						<div class="wpshop_form_input_element alignleft"><br /><br />
						<form></form>
						<form action="'.WPSHOP_AJAX_FILE_URL.'" method="post" enctype="multipart/form-data" id="wpshop_uploadForm">
						<input type="file" name="wpshop_file" style="width:auto;" />
						<input type="hidden" name="post" value="true" />
						<input type="hidden" name="elementCode" value="ajaxUpload" />
						<input type="submit" value="'.__('Upload File','wpshop').'" class="button" /> <img src="' . WPSHOP_LOADING_ICON . '" alt="loading..." class="wpshop_loading" style="display:none;" />
						</form>
						<div class="statut">'.basename($data['file_url']).'</div>
						</div>';

				$fields .= '<div class="wpshop_form_label alignleft"><label>'.__('File url','wpshop').'</label></div>
						<div class="wpshop_form_input_element alignleft">
						<input type="hidden" name="attribute_option[is_downloadable_][file_url]" value="'.$data['file_url'].'" /><br /><br />
						</div>';

				$fields .= '<div class="wpshop_form_label alignleft">&nbsp;</div>
						<div class="wpshop_form_input_element alignleft">
						<input type="checkbox" name="attribute_option[is_downloadable_][allow_presale]" value="true" '.(!empty($data['allow_presale'])?'checked="checked"':null).' />
						<label>'.__('Allow pre-sale','wpshop').'</label><br /><br />
						</div>';

				return $fields;
				break;

			default:
				return '';
				break;
		}

	}

	/**
	 *	Return content informations about a given attribute
	 *
	 *	@param string $attribute_code The code of attribute to get (Not the id because if another system is using eav model it could have some conflict)
	 *	@param integer $entity_id The current entity we want to have the attribute value for
	 *	@param string $entity_type The current entity type code we want to have the attribute value for
	 *
	 *	@return object $attribute_value_content The attribute content
	 */
	function get_attribute_value_content($attribute_code, $entity_id, $entity_type) {
		$attribute_value_content = '';

		$atributes = self::getElement($attribute_code, "'valid'", 'code');
		if ( !empty($atributes) ) {
			$attribute_value_content = self::getAttributeValueForEntityInSet($atributes->data_type, $atributes->id,  wpshop_entities::get_entity_identifier_from_code($entity_type), $entity_id);
		}

		return $attribute_value_content;
	}

	function attribute_type_date_config( $value ) {
		$date_config_output = '';

		$input_def['name'] = 'default_value';
		$input_def['type'] = 'checkbox';
		$input_def['possible_value'] = 'date_of_current_day';
		$input_def['value'] = !empty($value['default_value']) ? $value['default_value'] : '';
		$input_def['options']['label']['custom'] = __('Use the date of the day as default value', 'wpshop') . ' <a href="#" title="'.__('Check this box for using date of the day as value when editing a product', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
		$date_config_output .= wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_attributes_edition_table_field_attribute_type_date_options_day_to_show';
		$input_def['type'] = 'checkbox';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = !empty($value['field_options']['attribute_type_date_options_day_to_show']) ? $value['field_options']['attribute_type_date_options_day_to_show'] : '';
		$input_def['possible_value'] = array('1' => __('Monday', 'wpshop'), '2' => __('Tuesday', 'wpshop'), '3' => __('Wednesday', 'wpshop'), '4' => __('Thursday', 'wpshop'), '5' => __('Friday', 'wpshop'), '6' => __('Saturday', 'wpshop'), '0' => __('Sunday', 'wpshop'));
		$input_def['options']['label']['original'] = true;
		$date_config_output .= '<div>' . __('Choose available days in date picker', 'wpshop') . '<a href="#" title="'.__('This option allows you to define the available day in final datepicker', 'wpshop').'" class="wpshop_infobulle_marker">?</a>' . '<br/>' . wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE . '_options[attribute_type_date_options_day_to_show]') . '</div>';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['type'] = 'text';
		$date_config_output .= '
<div id="wpshop_attribute_date_empy_field" class="wpshopHide" ><br/>' . wpshop_form::check_input_type(array_merge($input_def, array('value' => '', 'id' => 'wpshop_attributes_edition_table_field_attribute_type_date_options_available_date_new_input')), WPSHOP_DBT_ATTRIBUTE . '_options[attribute_type_date_options_available_date]') .'</div>
<div id="wpshop_avalaible_date_list_container" >' . __('Choose available date in date picker', 'wpshop') . '<a href="#" title="'.__('This option allows you to define the available date in final datepicker', 'wpshop').'" class="wpshop_infobulle_marker">?</a><br/>';

		$available_type_input_def = array();
		$available_type_input_def['name'] = '';
		$available_type_input_def['id'] = 'wpshop_attributes_edition_table_field_attribute_type_date_options_available_date_type';
		$available_type_input_def['type'] = 'radio';
		$available_type_input_def['valueToPut'] = 'index';
		$available_type_input_def['value'] = !empty($value['field_options']['attribute_type_date_options_available_date_type']) ? $value['field_options']['attribute_type_date_options_available_date_type'] : array('available');
		$available_type_input_def['possible_value'] = array('' => __('No restriction', 'wpshop'), 'available' => __('Date below are available', 'wpshop'), 'unavailable' => __('Date below are unvailable', 'wpshop'));
		$available_type_input_def['options']['label']['original'] = true;
		$date_config_output .= wpshop_form::check_input_type($available_type_input_def, WPSHOP_DBT_ATTRIBUTE . '_options[attribute_type_date_options_available_date_type]') . '<br/>';

		$existing = 0;
		if ( !empty($value['field_options']['attribute_type_date_options_available_date']) ) {
			foreach ( $value['field_options']['attribute_type_date_options_available_date'] as $index => $value ) {
				if ( !empty($value) ) {
					$input_def['value'] = $value;
					$input_def['id'] = 'wpshop_attributes_edition_table_field_attribute_type_date_options_available_date_' . $index;
					$date_config_output .= wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE . '_options[attribute_type_date_options_available_date]') . '<br/>';
					$existing++;
				}
			}
		}
		$input_def['value'] = '';
		$input_def['id'] = 'wpshop_attributes_edition_table_field_attribute_type_date_options_available_date';
		$date_config_output .= wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE . '_options[attribute_type_date_options_available_date]');
		$date_config_output .= '
	<img class="wpshop_icons wpshop_icons_add_new_value_to_option_list wpshop_icons_add_new_value_to_available_date_list" title="' . __('Add a new date', 'wpshop') . '" alt="' . __('Add a new date', 'wpshop') . '" src="' . WPSHOP_MEDIAS_ICON_URL . 'add.png" >
</div>';

		return $date_config_output;
	}

	/**
	 * Met a jour un ou plusieurs attributes concernant un produit
	 * @param integer $entityId Id du produit
	 * @param array $values Valeurs d'attributs
	 * @return array
	 */
	function setAttributesValuesForItem($entityId, $values=array(), $defaultValueForOthers=false, $from = 'webservice') {
		$message='';
		$attribute_available = array();
		$attribute_final = array();
		$entity_type = get_post_type($entityId);
		$data = self::get_attribute_list_for_item( wpshop_entities::get_entity_identifier_from_code($entity_type), $entityId, WPSHOP_CURRENT_LOCALE);
		foreach($data as $d) $attribute_available[$d->attribute_code] = array('attribute_id' => $d->attribute_id, 'data_type' => $d->data_type);

		// Creation d'un array "propre" et valide pour la fonction self::saveAttributeForEntity
		foreach ( $values as $key => $value ) {
			if ( in_array( $key, array_keys( $attribute_available ) ) ) {
				$attribute_final[$attribute_available[$key]['data_type']][$key] = $value;
			}
			else $message .= sprintf(__('Impossible to set "%s" attribute', 'wpshop'), $key)."\n";
		}

		// Pour les autres attributs non donnÃ© on leur affecte leur valeur par dÃ©faut
		if ($defaultValueForOthers) {
			$codes = array_keys($values);
			foreach ($data as $d) {
				if (!in_array($d->attribute_code, $codes)) {
					$attribute_final[$d->data_type][$d->attribute_code] = $d->default_value;
				}
			}
		}

		/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
		$productMetaDatas = array();
		foreach ($attribute_final as $attributeType => $attributeValues) {
			foreach ($attributeValues as $attributeCode => $attributeValue) {
				$productMetaDatas[$attributeCode] = $attributeValue;
			}
		}

		switch ( $entity_type ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
					$meta_key = WPSHOP_PRODUCT_ATTRIBUTE_META_KEY;
				break;
			default:
					$meta_key = '_' . $entity_type . '_metadata';
				break;
		}

		$current = get_post_meta($entityId, $meta_key, true);
		$current = empty($current) ? array() : $current;
		$productMetaDatas = array_merge($current, $productMetaDatas);
		update_post_meta($entityId, $meta_key, $productMetaDatas);

		if (!empty($attribute_final)) {
			self::saveAttributeForEntity($attribute_final, wpshop_entities::get_entity_identifier_from_code($entity_type), $entityId, get_locale(), $from);
		}

		return array('status' => empty($message), 'message' => $message);
	}


	/**
	 * Recupere les informations concernant une option donnees dans la liste d'un attribut de type liste deroulante
	 *
	 * @param integer $option_id L'identifiant de l'option dont on veut recuperer les informations
	 * @param string $field optionnal Le champs correspondant a l'information que l'on souhaite recuperer
	 * @return string $info L'information que l'on souhaite
	 */
	function get_attribute_type_select_option_info ($option_id, $field = 'label', $attribute_data_type = 'custom', $only_value = false) {
		global $wpdb;

		switch ( $attribute_data_type ) {
			case 'internal':
					$entity_infos = get_post($option_id);
					if ( !$only_value ) {
						/*
						 * Template parameters
						 */
						$template_part = 'product_attribute_value_internal';
						$tpl_component = array();
						$tpl_component['ATTRIBUTE_VALUE_POST_LINK'] = get_permalink($option_id);
						$tpl_component['ATTRIBUTE_VALUE_POST_TITLE'] = $entity_infos->post_title;

						/*
						 * Build template
						 */
						$info = wpshop_display::display_template_element($template_part, $tpl_component);
						unset($tpl_component);
					}
					else {
						$info = $entity_infos->post_title;
					}
				break;

			default:
					$query = $wpdb->prepare("SELECT " . $field . " FROM ".WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS." WHERE id=%d LIMIT 1", $option_id);
					$info = $wpdb->get_var($query);
				break;
		}

		return $info;
	}
	function get_select_option_list_ ($attribute_id){
		global $wpdb;
		$query = $wpdb->prepare("
			SELECT ATTRIBUTE_COMBO_OPTION.id, ATTRIBUTE_COMBO_OPTION.label as name, ATTRIBUTE_COMBO_OPTION.value , ATTRIBUTE_VALUE_INTEGER.value_id
			, ATT.default_value, ATT.data_type_to_use, ATT.use_ajax_for_filling_field
			FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATT
				INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " AS ATTRIBUTE_COMBO_OPTION ON ((ATTRIBUTE_COMBO_OPTION.attribute_id = ATT.id) AND (ATTRIBUTE_COMBO_OPTION.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATTRIBUTE_VALUE_INTEGER ON ((ATTRIBUTE_VALUE_INTEGER.attribute_id = ATTRIBUTE_COMBO_OPTION.attribute_id) AND (ATTRIBUTE_VALUE_INTEGER.value = ATTRIBUTE_COMBO_OPTION.id))
			WHERE ATT.id = %d
				AND ATT.status = 'valid'
			GROUP BY ATTRIBUTE_COMBO_OPTION.value
			ORDER BY ATTRIBUTE_COMBO_OPTION.position", $attribute_id);
		return $wpdb->get_results($query);
	}
	/**
	 * Recupere la liste des options pour les attributs de type liste deroulante suivant le type de donnees choisi (personnalise ou interne a wordpress)
	 *
	 * @param integer $attribute_id L'identifiant de l'attribut pour lequel on souhaite recuperer la liste des options
	 * @param string $data_type optionnal Le type de donnees choisi pour cet attribut (custom | internal)
	 * @return string Le resultat sous forme de code html pour la liste des options
	 */
	function get_select_options_list($attribute_id, $data_type='custom') {
		global $wpdb;
		$output = '';

		$attribute_select_options = self::get_select_option_list_($attribute_id);

		/*	Add possibily to choose datat type to use with list	*/
		if(empty($attribute_id) || (!empty($attribute_select_options) && empty($attribute_select_options[0]->data_type_to_use))){
			unset($input_def);$input_def=array();
			$input_def['label'] = __('Type of data for list', 'wpshop');
			$input_def['type'] = 'radio';
			$input_def['name'] = 'data_type_to_use';
			$input_def['valueToPut'] = 'index';
			$input_def['possible_value'] = unserialize(WPSHOP_ATTR_SELECT_TYPE);
			$input_def['option'] = 'class="clear wpshop_attr_combo_data_type"';
			$input_def['value'] = $data_type.'_data';
			$input_def['options']['label']['original'] = true;
			$output = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
		}

		if(!empty($attribute_id) || !empty($data_type)){
			if((($data_type == 'custom') && empty($attribute_select_options)) || (!empty($attribute_select_options) && !empty($attribute_select_options[0]->data_type_to_use) && ($attribute_select_options[0]->data_type_to_use == 'custom'))){
				$sub_output = '';
				if ( count($attribute_select_options) > 0 ) {
					$sub_output .= '
					<li class="wpshop_attribute_combo_options_container ui-state-disabled" >
						<input type="radio" name="' . WPSHOP_DBT_ATTRIBUTE . '[default_value]" value="" id="default_value_empty" ' . (empty($attribute_select_options[0]->default_value) ? 'checked ' : '') . '/> <label for="default_value_empty">' . __('No default value', 'wpshop') . '</label>
					</li>';
					foreach ($attribute_select_options as $options) {
						$option_id=$options->id;
						$option_default_value=$options->default_value;
						$option_value_id=$options->value_id;
						$option_name=$options->name;
						$options_value=$options->value;
						ob_start();
						include(WPSHOP_TEMPLATES_DIR.'admin/attribute_option_value.tpl.php');
						$sub_output .= ob_get_contents();
						ob_end_clean();
					}
				}
				$add_button = $add_dialog_box = $user_more_script = '';
				if( current_user_can('wpshop_add_attributes_select_values') ) {

					$dialog_title = __('New value for attribute', 'wpshop');
					$dialog_identifier = 'wpshop_new_attribute_option_value_add';
					$dialog_input_identifier = 'wpshop_new_attribute_option_value';
					ob_start();
					include(WPSHOP_TEMPLATES_DIR.'admin/add_new_element_dialog.tpl.php');
					$add_dialog_box = ob_get_contents();
					ob_end_clean();

					$add_button_text = __('Add a value for this attribute', 'wpshop');
					$add_button_parent_class = 'wpshop_attribute_option_value_add';
					$add_button_name = 'wpshop_add_option_to_select';
					ob_start();
					include(WPSHOP_TEMPLATES_DIR.'admin/add_new_element_with_dialog.tpl.php');
					$add_button = ob_get_contents();
					ob_end_clean();

					$user_more_script = '
			jQuery("#'.$dialog_identifier.'").dialog({
				modal: true,
				dialogClass: "wpshop_uidialog_box",
				autoOpen:false,
				show: "blind",
				resizable: false,
				buttons:{
					"'.__('Add', 'wpshop').'": function(){
						var data = {
							action: "new_option_for_select",
							wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_new_option_for_attribute_creation") . '",
							attribute_new_label: jQuery("#'.$dialog_input_identifier.'").val(),
							attribute_identifier: "' . $attribute_id . '"
						};
						jQuery.getJSON(ajaxurl, data, function(response) {
							if( response[0] ) {
								jQuery("#sortable_attribute li:last-child").before(response[1]);
								jQuery("#wpshop_new_attribute_option_value_add").dialog("close");
							}
							else {
								alert(response[1]);
							}
							jQuery("#wpshop_new_attribute_option_value_add").children("img").hide();
						});

						jQuery(this).children("img").show();
					},
					"'.__('Cancel', 'wpshop').'": function(){
						jQuery(this).dialog("close");
					}
				},
				close:function(){
					jQuery("#'.$dialog_input_identifier.'").val("");
				}
			});
			jQuery(".'.$add_button_parent_class.' input").click(function(){
				jQuery("#'.$dialog_identifier.'").dialog("open");
			});';

				}
				$output .= $add_dialog_box . '
	<ul id="sortable_attribute" class="clear" >'.(count($attribute_select_options)>5 ? $add_button : '').$sub_output.$add_button.'
	</ul>
	<input type="hidden" value="' . wp_create_nonce("wpshop_new_option_for_attribute_deletion") . '" name="wpshop_new_option_for_attribute_deletion_nonce" id="wpshop_new_option_for_attribute_deletion_nonce" />
	<script type="text/javascript" >
		wpshop(document).ready(function() {
			jQuery("#sortable_attribute").sortable({
				revert: true,
				items: "li:not(.ui-state-disabled)"
			});
			' . $user_more_script . '
			jQuery(".wpshop_attr_combo_data_type").live("click", function(){
				if(jQuery(this).is(":checked")){
					jQuery(".wpshop_attributes_edition_table_field_input_default_value").html(jQuery("#wpshopLoadingPicture").html());
					var data = {
						action: "attribute_output_type",
						current_type: jQuery("#wpshop_attributes_edition_table_field_id_backend_input").val(),
						elementIdentifier: "'.$attribute_id.'",
						data_type_to_use: jQuery(this).val(),
						wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_output_type_selection") . '"
					};
					jQuery.getJSON(ajaxurl, data, function(response) {
						jQuery(".wpshop_attributes_edition_table_field_input_default_value").html(response[0]);
						jQuery(".wpshop_attributes_edition_table_field_label_default_value label").html(response[1]);
						jQuery("#wpshop_attributes_edition_table_field_id_frontend_input").html();
					});
				}
			});

		});
	</script>';
			}
			elseif((($data_type == 'internal') && empty($attribute_select_options)) || (!empty($attribute_select_options) && !empty($attribute_select_options[0]->data_type_to_use) && ($attribute_select_options[0]->data_type_to_use == 'internal'))){
				$sub_output='';
				$wp_types = unserialize(WPSHOP_INTERNAL_TYPES);
				unset($input_def);$input_def=array();
				$input_def['label'] = __('Type of data for list', 'wpshop');
				$input_def['type'] = 'select';
				$input_def['name'] = 'default_value';
				$input_def['valueToPut'] = 'index';
				$input_def['possible_value'] = $wp_types;
				$input_def['value'] = !empty($attribute_select_options[0]->default_value) ? $attribute_select_options[0]->default_value : null;
				$combo_wp_type = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
				$output .= '<div class="clear">'.$combo_wp_type.'</div>';
			}
		}

		return $output;
	}

	/**
	 * Get the attribute list affected to an entity in order to generate a shortcode
	 *
	 * @param integer $entity_id The entity identifier for retrieving attribute list
	 * @param string $list_for The type of shortcode we want to generate
	 * @param string $current_post_type The post type of current edited element
	 *
	 * @return string The html output
	 */
	function get_attribute_list($entity_id = 0, $list_for = 'product_by_attribute', $current_post_type = '') {
		global $wpdb;
		$output = '';

		/*	If no entity is specified, take product as default entity	*/
		$entity_id = empty($entity_id) ? wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) : $entity_id;

		/*	Get attribute list for the selected enttiy	*/
		$attribute_list_for_entity = self::getElement($entity_id, "'valid'", 'entity_id', true);

		/*	Read the list	*/
		if ( !empty ($attribute_list_for_entity ) ) {
			foreach ( $attribute_list_for_entity as $attribute) {
				switch ($list_for) {
					case 'attribute_value':
							$checkbox_state = ' ';
							$attribute_possible_values = '
							<div class="wpshop_shortcode_element_attribute_value_product_list wpshop_shortcode_element_attribute_value_product_list_' . $attribute->id . '_container hidden" >
								<select id="wpshop_shortcode_element_attribute_value_product_list_' . $attribute->id . '" class="wpshop_shortcode_element_attribute_value_product_list" >';

							global $post;
							$posts = get_posts( array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'numberposts' => -1) );
							foreach( $posts as $post ) :
								setup_postdata($post);
								$attribute_possible_values .= '<option value="' . get_the_ID() . '" >' . get_the_ID() . ' - ' . get_the_title() . '</option>';
							endforeach;

							$attribute_possible_values .= '
								</select>
							</div>';
						break;

					default:
						$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute->data_type . " WHERE entity_type_id=%d AND attribute_id=%d GROUP BY value", $entity_id, $attribute->id);
						$attribute_values = $wpdb->get_results($query);

						$checkbox_state = 'disabled ';
						$attribute_possible_values = '';
						if ( !empty($attribute_values) ) {
							$checkbox_state = '';
							$attribute_possible_values = '
							<div class="wpshop_shortcode_element_product_listing_per_attribute_value wpshop_shortcode_element_prodcut_listing_per_attribute_value_' . $attribute->code . '_container" >
								<select id="wpshop_attribute_value_for_shortcode_generation_' . $attribute->id . '" class="wpshop_shortcode_element_prodcut_listing_per_attribute_value hidden" >';

								if ( ($attribute->data_type == 'integer') && ( ($attribute->backend_input == 'select') || ($attribute->backend_input == 'multiple-select') ) ) {
									$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id=%d ORDER BY position", $attribute->id);
									$possible_values = $wpdb->get_results($query);
									$already_selected_values = array();
									foreach ($attribute_values as $attribute_value) {
										if ( !empty($attribute_value->value) ) {
											$already_selected_values[] = $attribute_value->value;
										}
									}
									foreach ($possible_values as $value) {
										if ( in_array( $value->id, $already_selected_values ) ) {
											$attribute_possible_values .= '
									<option value="' . $value->value . '" >' . $value->label . '</option>';
										}
									}
								}
								else {
									foreach ($attribute_values as $attribute_value) {
										if ( !empty($attribute_value->value) ) {
											$attribute_possible_values .= '
									<option value="' . $attribute_value->value . '" >' . $attribute_value->value . '</option>';
										}
									}
								}
								$attribute_possible_values .= '
								</select>
							</div>';
						}
						break;
				}
				$output .= '
				<li class="wpshop_shortcode_element_container wpshop_shortcode_element_container_attributes" >
					<input type="checkbox" name="' . $attribute->code . '" class="wpshop_shortcode_element wpshop_shortcode_element_attribute wpshop_shortcode_element_attribute_' . $attribute->id . '" value="' . (($list_for == 'product_by_attribute') ? $attribute->code : $attribute->id) . '" id="wpshop_attribute_' . $attribute->id . '" ' . $checkbox_state . '> <label for="wpshop_attribute_' . $attribute->id . '" >' . __($attribute->frontend_label, 'wpshop') . '</label>' . $attribute_possible_values . '
				</li>';
			}
		}

		return $output;
	}

	/**
	 * Retrieve the attribute list into an attribute set section from a given attribute code
	 *
	 * @param string $attribute_code The attribute code that allows to define the attribute set section to get attribute list for
	 *
	 * @return object The attribute list as a wordpress database object
	 */
	function get_attribute_list_in_same_set_section( $attribute_code ) {
		global $wpdb;

		$attribute_def = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');

		/*
		 * Get the entire list of attribute in price set section for display
		 */
		$query = $wpdb->prepare( "SELECT entity_type_id, attribute_set_id, attribute_group_id FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE attribute_id = %d AND status = 'valid'", $attribute_def->id);
		$attribute_attribution_def = $wpdb->get_row($query);
		$query = $wpdb->prepare( "
							SELECT ATTR.code, is_visible_in_front_listing, is_visible_in_front
							FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS SET_SECTION_DETAIL
								INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR ON (ATTR.id = SET_SECTION_DETAIL.attribute_id)
							WHERE entity_type_id = %d
								AND attribute_set_id = %d
								AND attribute_group_id = %d",
				$attribute_attribution_def->entity_type_id, $attribute_attribution_def->attribute_set_id, $attribute_attribution_def->attribute_group_id );
		$atribute_list = $wpdb->get_results($query);

		return $atribute_list;
	}

	/**
	 * Get all attribute available for current
	 * @param unknown_type $current_entity_id
	 * @return Ambigous <multitype:, multitype:NULL >
	 */
	function get_variation_available_attribute( $current_entity_id ) {
		global $wpdb;
		$final_list = array();

		/**	Get all attributes defined as usable into product variation for the product type and group	*/
		$query = $wpdb->prepare(
				"SELECT ATT.*, ENTITY_META.meta_value
				FROM " . self::getDbTable() . " AS ATT
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS. " AS ATT_DETAILS ON ((ATT_DETAILS.attribute_id = ATT.id) AND (ATT_DETAILS.entity_type_id = %d) AND (ATT_DETAILS.status = 'valid'))
					INNER JOIN " . $wpdb->postmeta . " AS ENTITY_META ON ((ENTITY_META.meta_key = %s) AND (ENTITY_META.meta_value = ATT_DETAILS.attribute_set_id))
				WHERE ATT.status IN ('valid')
					AND ATT.is_used_for_variation = %s
					AND ENTITY_META.post_id = %d
				GROUP BY ENTITY_META.post_id, ATT.code", wpshop_entities::get_entity_identifier_from_code(get_post_type($current_entity_id)), WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, 'yes', $current_entity_id
		);
		$attribute_list = $wpdb->get_results($query);
		foreach ($attribute_list as $attribute) {
			if ( !in_array($attribute->code, unserialize(WPSHOP_VARIATION_ATTRIBUTE_TO_HIDE)) && in_array($attribute->backend_input, array('select', 'multiple-select')) ) {
				$attribute_values_for_variations = wpshop_attributes::get_affected_value_for_list( $attribute->code, $current_entity_id, $attribute->data_type_to_use );

				if ( empty($attribute_values_for_variations) ) {
					$final_list['unavailable'][$attribute->code]['label'] = $attribute->frontend_label;
					$final_list['unavailable'][$attribute->code]['values'] = array();
					$final_list['unavailable'][$attribute->code]['attribute_complete_def'] = $attribute;
				}
				else {
					$final_list['available'][$attribute->code]['label'] = $attribute->frontend_label;
					$final_list['available'][$attribute->code]['values'] = $attribute_values_for_variations;
					$final_list['available'][$attribute->code]['attribute_complete_def'] = $attribute;
				}
			}
		}

		return $final_list;
	}

	/**
	 *
	 * @param integer $current_entity_id The current element edited
	 * @return Ambigous <string, string, mixed>
	 */
	function get_variation_available_attribute_display( $current_entity_id, $variation_type = 'multiple' ) {
		$attribute_list = wpshop_attributes::get_variation_available_attribute($current_entity_id);

		$attribute_defined_as_available_for_variation = '';
		if ( !empty($attribute_list) ) {
			foreach ($attribute_list as $list_type => $attribute_list_by_type) {
				$sub_attribute_list = '';
				foreach ($attribute_list_by_type as $attribute_code => $attribute_def) {
					$tpl_component = array();
					if ( $list_type == 'available' ) {
						$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_LABEL_STATE'] = '';
						$tpl_component['ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CHECKBOX_STATE'] = '';
						$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL_EXPLAINATION'] = '';
					}
					else {
						$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_LABEL_STATE'] = ' class="wpshop_unavailable_label_variation_definition" ';
						$tpl_component['ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CHECKBOX_STATE'] = ' disabled="disabled"';
						$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL_EXPLAINATION'] = '';
					}

					$tpl_component['ADMIN_ATTRIBUTE_CODE_FOR_VARIATION'] = $attribute_code;
					$tpl_component['ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_NAME'] = $attribute_code;
					$tpl_component['ADMIN_VARIATION_ATTRIBUTE_CONTAINER_CLASS'] = ' wpshop_attribute_for_variation_' . $attribute_code;
					$tpl_component['ADMIN_VARIATION_NEW_SINGLE_LABEL'] = __( $attribute_def['label'], 'wpshop' );
					$tpl_component['ADMIN_VARIATION_NEW_SINGLE_INPUT'] = '';
					if ( $variation_type == 'single' ) {
						$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute_def['attribute_complete_def'], $attribute_def['values'], array('from' => 'frontend', 'field_custom_name_prefix' => 'variation_attr', 'input_class' => ' variation_attribute_usable_input') );
						if ( !empty($attribute_output_def['possible_value']) ) {
							foreach( $attribute_output_def['possible_value'] as $value_id => $value ){
								if ( !in_array($value_id, $attribute_def['values']) ) {
									unset($attribute_output_def['possible_value'][$value_id]);
								}
							}
						}
						$tpl_component['ADMIN_VARIATION_NEW_SINGLE_INPUT'] = wpshop_form::check_input_type($attribute_output_def, $attribute_output_def['input_domain']);
					}
					$sub_attribute_list .= wpshop_display::display_template_element('wpshop_admin_variation_attribute_line', $tpl_component, array(), 'admin');
					unset($tpl_component);
				}

				$attribute_defined_as_available_for_variation .= wpshop_display::display_template_element((($list_type == 'available') ? 'wpshop_admin_attribute_for_variation_list' : 'wpshop_admin_unvailable_attribute_for_variation_list'), array('ADMIN_VARIATIONS_DEF_LIST_CONTAINER_CLASS' => '', 'ADMIN_VARIATIONS_DEF_LIST_CONTAINER' => $sub_attribute_list), array(), 'admin');
			}
		}

		return array($attribute_defined_as_available_for_variation, $attribute_list['available'], $attribute_list['unavailable']);
	}

	/**
	 * Get attribute defined as product option specific attribute
	 *
	 * @param array $variations_attribute_parameters Allows to give some parameters for customize list
	 * @return string The output for all specific attribute in each product with option
	 */
	function get_variation_attribute( $variations_attribute_parameters ) {
		$output = '';

		$attribute_list = wpshop_attributes::getElement('yes', "'valid'", "is_used_in_variation", true);
		if ( !empty( $attribute_list ) ) {
			$tpl_component = array();
			$tpl_component['ADMIN_VARIATION_DETAIL'] = '';
			foreach ( $attribute_list as $attribute_def ) {
				$variations_attribute_parameters['field_custom_name_prefix'] = $variations_attribute_parameters['field_name'] . '[attribute][' . $attribute_def->data_type . ']';
				$attribute_output_def = wpshop_attributes::get_attribute_field_definition($attribute_def, (!empty($variations_attribute_parameters['variation_dif_values'][$attribute_def->code]) ? $variations_attribute_parameters['variation_dif_values'][$attribute_def->code] : ''), $variations_attribute_parameters);

				$field_output = $attribute_output_def['output'];

				/*	Build array for output complete customization	*/
				$tpl_component['ADMIN_VARIATION_DETAIL_LABEL_' . strtoupper($attribute_def->code)] = $attribute_output_def['label'];
				$tpl_component['ADMIN_VARIATION_DETAIL_INPUT_' . strtoupper($attribute_def->code)] = $field_output;
				$sub_tpl_component = array();
				$sub_tpl_component['ADMIN_VARIATION_DETAIL_DEF_CODE'] = ' wpshop_variation_special_value_container_' . $attribute_output_def['name'];
				$sub_tpl_component['ADMIN_VARIATION_DETAIL_DEF_ID'] = $attribute_output_def['id'];
				$sub_tpl_component['ADMIN_VARIATION_DETAIL_DEF_LABEL'] = $attribute_output_def['label'];
				$sub_tpl_component['ADMIN_VARIATION_DETAIL_DEF_INPUT'] = $field_output;
				$tpl_component['ADMIN_VARIATION_DETAIL'] .= wpshop_display::display_template_element('wpshop_admin_variation_item_details_line', $sub_tpl_component, array(), 'admin');;
				unset($sub_tpl_component);
			}
			$output .= wpshop_display::display_template_element('wpshop_admin_variation_item_details', $tpl_component, array(), 'admin');
		}

		return $output;
	}

	function get_attribute_user_defined( $use_defined_parameters ) {
		global $wpdb;
		$attribute_user_defined_list = array();

		$query = $wpdb->prepare(
				"SELECT ATT.*, ENTITY.post_name as entity
				FROM " . self::getDbTable() . " AS ATT
					INNER JOIN " . $wpdb->posts . " AS ENTITY ON (ENTITY.ID = ATT.entity_id)
					INNER JOIN " . $wpdb->postmeta . " AS ENTITY_META ON ((ENTITY_META.post_id = ENTITY_ID))
				WHERE ATT.status IN ('valid')
					AND ATT.is_user_defined = %s
					AND ATT.is_used_for_variation = %s
					AND ENTITY.post_name = %s", 'yes', 'no', $use_defined_parameters['entity_type_id']
		);
		$attribute_user_defined_list = $wpdb->get_results($query);

		return $attribute_user_defined_list;
	}

	/**
	 * Define the different field available for bulk edition for entities. Attributes to display are defined by checking box in attribute option
	 *
	 * @param string $column_name The column name for output type definition
	 * @param string $post_type The current
	 *
	 */
	function quick_edit( $column_name, $entity ) {
		switch ( $entity ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
				$attribute_def = wpshop_attributes::getElement($column_name, "'valid'", 'code');
				if ( !empty($attribute_def) ) {
					$input_def = self::get_attribute_field_definition( $attribute_def, '', array('input_class' => ' wpshop_bulk_and_quick_edit_input') );
					$input = wpshop_form::check_input_type($input_def, $input_def['input_domain']);
?>
	<div class="wpshop_bulk_and_quick_edit_column_container wpshop_bulk_and_quick_edit_column_<?php echo $column_name; ?>_container">
		<span class="wpshop_bulk_and_quick_edit_column_label wpshop_bulk_and_quick_edit_column_<?php echo $column_name; ?>_label"><?php _e($attribute_def->frontend_label, 'wpshop'); ?></span>
		<?php echo str_replace('chosen_select', '', str_replace('alignleft', '', $input)); ?>
	</div>
<?php
			}
			break;
		}
	}
	/**
	 * Define the different field available for bulk edition for entities. Attributes to display are defined by checking box in attribute option
	 *
	 * @param string $column_name The column name for output type definition
	 * @param string $post_type The current
	 *
	 */
	function bulk_edit( $column_name, $entity ) {
		switch ( $entity ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
				$attribute_def = wpshop_attributes::getElement($column_name, "'valid'", 'code');
				if ( !empty($attribute_def) ) {
					$input_def = self::get_attribute_field_definition( $attribute_def, '', array('input_class' => ' wpshop_bulk_and_quick_edit_input') );
					$input = wpshop_form::check_input_type($input_def, $input_def['input_domain']);
?>
	<div class="wpshop_bulk_and_quick_edit_column_container wpshop_bulk_and_quick_edit_column_<?php echo $column_name; ?>_container">
		<span class="wpshop_bulk_and_quick_edit_column_label wpshop_bulk_and_quick_edit_column_<?php echo $column_name; ?>_label"><?php _e($attribute_def->frontend_label, 'wpshop'); ?></span>
		<?php echo str_replace('chosen_select', '', str_replace('alignleft', '', $input)); ?>
		<!-- <input class="wpshop_bulk_and_quick_edit_input wpshop_bulk_and_quick_edit_input_data_type_<?php echo $attribute_def->data_type; ?> wpshop_bulk_and_quick_edit_input_data_code_<?php echo $attribute_def->code; ?>" type="text" name="<?php echo $entity; ?>_-code-_<?php echo $attribute_def->code; ?>" value="" />  -->
	</div>
<?php
			}
			break;
		}
	}

}

?>