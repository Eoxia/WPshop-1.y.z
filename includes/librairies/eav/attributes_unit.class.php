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
class wpshop_attributes_unit
{
	/**
	*	Define the database table used in the current class
	*/
	const dbTable = WPSHOP_DBT_ATTRIBUTE_UNIT;
	/**
	*	Define the url listing slug used in the current class
	*/
	const urlSlugListing = WPSHOP_URL_SLUG_ATTRIBUTE_LISTING;
	/**
	*	Define the url edition slug used in the current class
	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_ATTRIBUTE_LISTING;
	/**
	*	Define the current entity code
	*/
	const currentPageCode = 'attributes_unit';
	/**
	*	Define the page title
	*/
	const pageContentTitle = 'Attributes unit';
	/**
	*	Define the page title when adding an attribute
	*/
	const pageAddingTitle = 'Add an unit';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageEditingTitle = 'Unit "%s" edit';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageTitle = 'Attributes unit list';

	/**
	*	Define the path to page main icon
	*/
	public $pageIcon = '';
	/**
	*	Define the message to output after an action
	*/
	public $pageMessage = '';

	/**
	*	Get the url listing slug of the current class
	*
	*	@return string The table of the class
	*/
	function setMessage($message)
	{
		$this->pageMessage = $message;
	}
	/**
	*	Get the url listing slug of the current class
	*
	*	@return string The table of the class
	*/
	function getListingSlug()
	{
		return self::urlSlugListing;
	}
	/**
	*	Get the url edition slug of the current class
	*
	*	@return string The table of the class
	*/
	function getEditionSlug()
	{
		return self::urlSlugEdition;
	}
	/**
	*	Get the database table of the current class
	*
	*	@return string The table of the class
	*/
	function getDbTable()
	{
		return self::dbTable;
	}

	/**
	*	Define the title of the page
	*
	*	@return string $title The title of the page looking at the environnement
	*/
	function pageTitle()
	{
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : '';
		$objectInEdition = isset($_REQUEST['id']) ? wpshop_tools::varSanitizer($_REQUEST['id']) : '';

		$title = __(self::pageTitle, 'wpshop' );
		if($action != '')
		{
			if(($action == 'edit') || ($action == 'delete'))
			{
				$editedItem = self::getElement($objectInEdition);
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), str_replace("\\", "", $editedItem->frontend_label) . '&nbsp;(' . $editedItem->code . ')');
			}
			elseif($action == 'add')
			{
				$title = __(self::pageAddingTitle, 'wpshop');
			}
		}
		elseif((self::getEditionSlug() != self::getListingSlug()) && ($_GET['page'] == self::getEditionSlug()))
		{
			$title = __(self::pageAddingTitle, 'wpshop');
		}
		return $title;
	}

	/**
	*	Define the different message and action after an action is send through the element interface
	*/
	function elementAction()
	{
		global $wpdb, $initialEavData;

		$pageMessage = $actionResult = '';

		/*	Start definition of output message when action is doing on another page	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/****************************************************************************/
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$saveditem = isset($_REQUEST['saveditem']) ? wpshop_tools::varSanitizer($_REQUEST['saveditem']) : '';
		if(($action != '') && ($action == 'saveok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem);
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}
		elseif(($action != '') && ($action == 'deleteok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem, "'deleted'");
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully deleted', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}

		/*	Define the database operation type from action launched by the user	 */
		$_REQUEST[self::getDbTable()]['default_value'] = str_replace('"', "'", $_REQUEST[self::getDbTable()]['default_value']);
		/*************************		GENERIC				**************************/
		/*************************************************************************/
		$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
		$id = isset($_REQUEST[self::getDbTable()]['id']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['id']) : '';
		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue'))){
			if(current_user_can('wpshop_edit_attributes'))
			{
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete')
				{
					if(current_user_can('wpshop_delete_attributes'))
					{
						$_REQUEST[self::getDbTable()]['status'] = 'deleted';
					}
					else
					{
						$actionResult = 'userNotAllowedForActionDelete';
					}
				}
				$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
			}
			else
			{
				$actionResult = 'userNotAllowedForActionEdit';
			}
		}
		elseif(($pageAction != '') && (($pageAction == 'delete'))){
			if(current_user_can('wpshop_delete_attributes'))
			{
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				$_REQUEST[self::getDbTable()]['status'] = 'deleted';
				$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
			}
			else
			{
				$actionResult = 'userNotAllowedForActionDelete';
			}
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
					$_REQUEST[self::getDbTable()]['code'] = $_REQUEST[self::getDbTable()]['code'] . '_' . (count($code_exists) + 1);
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
			$elementIdentifierForMessage = '<span class="bold" >' . $_REQUEST[self::getDbTable()]['frontend_label'] . '</span>';
			if ($actionResult == 'error') {
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . sprintf(__('An error occured while saving %s', 'wpshop'), $elementIdentifierForMessage);
				if(WPSHOP_DEBUG_MODE)
				{
					$pageMessage .= '<br/>' . $wpdb->last_error;
				}
			}
			elseif(($actionResult == 'done') || ($actionResult == 'nothingToUpdate'))
			{/*	CHANGE HERE FOR SPECIFIC CASE	*/
				/*****************************************************************************************************************/
				/*************************			CHANGE FOR SPECIFIC ACTION FOR CURRENT ELEMENT				******************/
				/*****************************************************************************************************************/

				/***********************************************************************************/
				/*************************			GENERIC				****************************/
				/***********************************************************************************/
				$pageMessage .= '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), $elementIdentifierForMessage);
				if(($pageAction == 'edit') || ($pageAction == 'save'))
				{
					wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page=' . self::getListingSlug() . "&action=saveok&saveditem=" . $id));
				}
				elseif($pageAction == 'add')
				{
					wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page=' . self::getListingSlug() . "&action=edit&id=" . $id));
				}
				elseif($pageAction == 'delete')
				{
					wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page=' . self::getListingSlug() . "&action=deleteok&saveditem=" . $id));
				}
			}
			elseif(($actionResult == 'userNotAllowedForActionEdit') || ($actionResult == 'userNotAllowedForActionAdd') || ($actionResult == 'userNotAllowedForActionDelete'))
			{
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . __('You are not allowed to do this action', 'wpshop');
			}
		}

		self::setMessage($pageMessage);
	}

	/**
	*	Return the list page content, containing the table that present the item list
	*
	*	@return string $listItemOutput The html code that output the item list
	*/
	function elementList()
	{
		$listItemOutput = '';

		/*	Start the table definition	*/
		$tableId = self::getDbTable() . '_list';
		$tableSummary = __('Existing attributes listing', 'wpshop');
		$tableTitles = array();
		$tableTitles[] = __('Attribute unit name', 'wpshop');
		$tableTitles[] = __('Attribute unit', 'wpshop');
		$tableTitles[] = __('Attribute unit group name', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_label_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_code_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_group_column';

		$line = 0;
		$elementList = self::getElement();
		if(is_array($elementList) && (count($elementList) > 0)){
			foreach($elementList as $element)
			{
				$tableRowsId[$line] = self::getDbTable() . '_' . $element->id;

				$elementLabel = __($element->name, 'wpshop');
				$subRowActions = '';
				$attributeSlugUrl = self::getListingSlug();
				if(current_user_can('wpshop_add_attributes_unit'))
				{
					$attributeSlugUrl = self::getEditionSlug();
				}
				if(current_user_can('wpshop_edit_attributes_unit'))
				{
					$editAction = admin_url('admin.php?page=' . $attributeSlugUrl . '&amp;action=edit&amp;id=' . $element->id);
					$subRowActions .= '
		<a href="#" id="edit_attribute_unit_' . $element->id . '" class="edit_attribute_unit" >' . __('Edit', 'wpshop') . '</a>';
				}
				elseif(current_user_can('wpshop_view_attributes_unit'))
				{
					$editAction = admin_url('admin.php?page=' . $attributeSlugUrl . '&amp;action=edit&amp;id=' . $element->id);
				}
				if(current_user_can('wpshop_delete_attributes_unit'))
				{
					if($subRowActions != '')
					{
						$subRowActions .= '&nbsp;|&nbsp;';
					}
					$subRowActions .= '
		<a href="#" id="delete_attribute_unit_' . $element->id . '" class="delete_attribute_unit" >' . __('Delete', 'wpshop') . '</a>';
				}

				$rowActions = '
	<div id="rowAction' . $element->id . '" class="wpshopRowAction" >' . $subRowActions . '
	</div>';

				unset($tableRowValue);
				$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => str_replace('\\', '', $elementLabel) . $rowActions);
				$tableRowValue[] = array('class' => self::currentPageCode . '_code_cell', 'value' => __($element->unit, 'wpshop'));
				$tableRowValue[] = array('class' => self::currentPageCode . '_group_cell', 'value' => __($element->group_name, 'wpshop'));
				$tableRows[] = $tableRowValue;

				$line++;
			}
		}
		else{
			unset($tableRowValue);
			$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => __('No element to ouput here', 'wpshop'));
			$tableRowValue[] = array('class' => self::currentPageCode . '_name_cell', 'value' => '');
			$tableRowValue[] = array('class' => self::currentPageCode . '_code_cell', 'value' => '');
			$tableRows[] = $tableRowValue;
		}
		if(current_user_can('wpshop_add_attributes_unit')){
			$listItemOutput .= '
<input type="button" value="' . __('Add an unit', 'wpshop') . '" class="button-secondary alignleft" name="add_attribute_unit" id="add_attribute_unit" />';
		}
		$listItemOutput .= wpshop_display::getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, true) . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $tableId . '").dataTable();
		jQuery("#wpshop_unit_group_list_tab").show();';
		if(current_user_can('wpshop_delete_attributes_unit')){
			$listItemOutput .= '
		wpshop(".delete_attribute_unit").click(function(){
			if(confirm(wpshopConvertAccentTojs("' . __('Are you sure you want to delete this unit', 'wpshop')  .' ?"))){
				wpshop("#wpshop_unit_list").load(WPSHOP_AJAX_FILE_URL,{
					"post": "true",
					"elementCode": "attribute_unit_management",
					"action": "delete_attribute_unit",
					"elementIdentifier": wpshop(this).attr("id").replace("delete_attribute_unit_", "")
				});
			}
		});';
		}
		if(current_user_can('wpshop_edit_attributes_unit')){
			$listItemOutput .= '
		jQuery(".edit_attribute_unit").click(function(){
			jQuery("#wpshop_unit_list").load(WPSHOP_AJAX_FILE_URL,{
				"post": "true",
				"elementCode": "attribute_unit_management",
				"action": "edit_attribute_unit",
				"elementIdentifier": wpshop(this).attr("id").replace("edit_attribute_unit_", "")
			});
		});';
		}
		if(current_user_can('wpshop_add_attributes_unit')){
			$listItemOutput .= '
		jQuery("#add_attribute_unit").click(function(){
			jQuery("#wpshop_unit_list").load(WPSHOP_AJAX_FILE_URL,{
				"post": "true",
				"elementCode": "attribute_unit_management",
				"action": "add_attribute_unit"
			});
		});';
		}
		$listItemOutput .= '
	});
</script>';

		return $listItemOutput;
	}
	/**
	*	Return the page content to add a new item
	*
	*	@return string The html code that output the interface for adding a nem item
	*/
	function elementEdition($itemToEdit = ''){
		global $attribute_displayed_field; global $wpdb;
		$dbFieldList = wpshop_database::fields_to_input(self::getDbTable());

		$editedItem = '';
		$_REQUEST['action'] = 'save_new_attribute_unit';
		if($itemToEdit != ''){
			$editedItem = self::getElement($itemToEdit);
			$_REQUEST['action'] = 'update_attribute_unit';
		}
		$query = $wpdb->prepare('SELECT unit FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id = ' .get_option('wpshop_shop_default_currency'). '', '');
 		$default_unit = $wpdb->get_var($query);

		$the_form_content_hidden = $the_form_general_content = $the_form_option_content = '';
		foreach($dbFieldList as $input_key => $input_def){
			$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
			$requestFormValue = isset($_REQUEST[self::currentPageCode][$input_def['name']]) ? wpshop_tools::varSanitizer($_REQUEST[self::currentPageCode][$input_def['name']]) : '';
			$currentFieldValue = $input_def['value'];
			if(is_object($editedItem)){
				$currentFieldValue = $editedItem->$input_def['name'];
			}
			elseif(($pageAction != '') && ($requestFormValue != '')){
				$currentFieldValue = $requestFormValue;
			}

			$input_def['value'] = $currentFieldValue;
			if($input_def['name'] == 'group_id'){
				$attribute_unit_group_list = self::get_unit_group();
				$input_def['possible_value'] = $attribute_unit_group_list;
				$input_def['type'] = 'select';
			}

			$input_def['value'] = str_replace("\\", "", $input_def['value']);
			$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());


			if($input_def['type'] != 'hidden'){
				$label = 'for="' . $input_def['name'] . '"';
				if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox')){
					$label = '';
				}

				$the_form_general_content .= '
				<div class="clear" >
					<div class="wpshop_form_label wpshop_' . self::currentPageCode . '_' . $input_def['name'] . '_label alignleft" >
						<label ' . $label . ' >' . __($input_def['name'], 'wpshop') . '</label>
					</div>
					<div class="wpshop_form_input wpshop_' . self::currentPageCode . '_' . $input_def['name'] . '_input alignleft" >
						' . $the_input . ' ' .( ($input_def['name'] == 'change_rate') ? $default_unit : ''). '
					</div>
				</div>';


			}
			else{
				$the_form_content_hidden .= '
	' . $the_input;
			}
		}

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="' . WPSHOP_AJAX_FILE_URL . '" >
' . wpshop_form::form_input('action', 'action', $_REQUEST['action'], 'hidden') . '
' . wpshop_form::form_input('post', 'post', 'true' , 'hidden') . '
' . wpshop_form::form_input('elementCode', 'elementCode', 'attribute_unit_management' , 'hidden') . '
' . wpshop_form::form_input(self::currentPageCode . '_form_has_modification', self::currentPageCode . '_form_has_modification', 'no' , 'hidden') . '
	' . $the_form_content_hidden .'' . $the_form_general_content . '
	<input type="button" value="' . __('Back', 'wpshop') . '" class="button-primary alignright" name="cancel_unit_edition" id="cancel_unit_edition" />
	<input type="submit" value="' . __('Save', 'wpshop') . '" class="button-primary alignright" name="save_new_unit" id="save_new_unit" />
</form>
<div class="wpshopHide" ><div id="default_value_content_default" >&nbsp;</div><div id="default_value_content_datetime" ><input type="checkbox" name="wp_wpshop__attribute[default_value]" value="date_of_current_day" />' . __('Date of the day', 'wpshop') . '</div></div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("' . self::getDbTable() . '", "' . __('Are you sure you want to quit this page? You will loose all current modification', 'wpshop') . '", "' . __('Are you sure you want to delete this attribute?', 'wpshop') . '");

		jQuery("#wpshop_unit_group_list_tab").hide();

		jQuery("#cancel_unit_edition").click(function(){
			jQuery("#wpshop_unit_list").load(WPSHOP_AJAX_FILE_URL, {
				"post": "true",
				"elementCode": "attribute_unit_management",
				"action": "load_attribute_units"
			});
		});

		jQuery("#' . self::getDbTable() . '_form").ajaxForm({
			target: "#wpshop_unit_list"
		});
	});
</script>';

		return $the_form;
	}
	/**
	*	Return the different button to save the item currently being added or edited
	*
	*	@return string $currentPageButton The html output code with the different button to add to the interface
	*/
	function getPageFormButton($element_id = 0)
	{
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$currentPageButton = '';

		if($action == 'add')
		{
			if(current_user_can('wpshop_add_attributes'))
			{
				$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Add', 'wpshop') . '" />';
			}
		}
		elseif(current_user_can('wpshop_edit_attributes'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Save', 'wpshop') . '" /><input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Save and continue edit', 'wpshop') . '" />';
		}
		if(current_user_can('wpshop_delete_attributes') && ($action != 'add'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="delete" name="delete" value="' . __('Delete', 'wpshop') . '" />';
		}

		$currentPageButton .= '<h2 class="cancelButton" ><a href="' . admin_url('admin.php?page=' . self::getListingSlug()) . '" class="button add-new-h2" >' . __('Back', 'wpshop') . '</a></h2>';

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
	function getElement($element_id = '', $element_status = "'valid', 'moderated'", $field_to_search = 'id'){
		global $wpdb;
		$element_list = array();
		$moreQuery = "";

		if($element_id != ''){
			$moreQuery = "
			AND CURRENT_ELEMENT." . $field_to_search . " = '" . $element_id . "' ";
		}

		$query = $wpdb->prepare(
		"SELECT CURRENT_ELEMENT.*, UNIT_GROUP.name as group_name
		FROM " . self::getDbTable() . " AS CURRENT_ELEMENT
			LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . " AS UNIT_GROUP ON (UNIT_GROUP.id = CURRENT_ELEMENT.group_id)
		WHERE CURRENT_ELEMENT.status IN (".$element_status.") " . $moreQuery, ''
		);

		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if($element_id == ''){
			$element_list = $wpdb->get_results($query);
		}
		else{
			$element_list = $wpdb->get_row($query);
		}

		return $element_list;
	}

	/**
	*
	*/
	function get_unit_list_for_group($group_id){
		global $wpdb;
		$unit_list_for_group = '';

		$query = $wpdb->prepare("
			SELECT 0 as id, %s AS name FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT . " WHERE status = 'valid' AND group_id = %d GROUP BY id
				UNION
			SELECT id, GROUP_CONCAT(name, \" (\", unit, \")\") AS name FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT . " WHERE status = 'valid' AND group_id = %d GROUP BY id", __('No unit', 'wpshop'), $group_id, $group_id);
		$unit_list_for_group = $wpdb->get_results($query);

		return $unit_list_for_group;
	}
	/**
	*
	*/
	function get_default_unit_for_group($group_id){
		global $wpdb;
		$default_unit_for_group = '';

		$query = $wpdb->prepare("
			SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT . " WHERE status = 'valid' AND is_default_of_group = 'yes' AND group_id = %d GROUP BY id", $group_id);
		$default_unit_for_group = $wpdb->get_var($query);

		return $default_unit_for_group;
	}

	/**
	*	Get the unit group existing list in database
	*
	*	@return object $attribute_unit_group_list The list of existing unit group
	*/
	function get_unit_group($element_id = '', $element_status = "'valid', 'moderated'", $field_to_search = 'id'){
		global $wpdb;
		$element_list = array();
		$moreQuery = "";

		if($element_id != ''){
			$moreQuery = "
			AND CURRENT_ELEMENT." . $field_to_search . " = '" . $element_id . "' ";
		}

		$query = $wpdb->prepare(
		"SELECT CURRENT_ELEMENT.*
		FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . " AS CURRENT_ELEMENT
		WHERE CURRENT_ELEMENT.status IN (".$element_status.") " . $moreQuery, ''
		);

		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if($element_id == '')
			$element_list = $wpdb->get_results($query);
		else
			$element_list = $wpdb->get_row($query);

		return $element_list;

		// $query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . " WHERE status != 'deleted'");
		// $attribute_unit_group_list = $wpdb->get_results($query);

		// return $attribute_unit_group_list;
	}
	/**
	*	Return the list page content, containing the table that present the item list
	*
	*	@return string $listItemOutput The html code that output the item list
	*/
	function unit_group_list(){
		$listItemOutput = '';

		/*	Start the table definition	*/
		$tableId = self::getDbTable() . '_group_list';
		$tableSummary = __('Existing attributes listing', 'wpshop');
		$tableTitles = array();
		$tableTitles[] = __('Attribute unit group name', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_label_column';

		$line = 0;
		$elementList = self::get_unit_group();
		if(is_array($elementList) && (count($elementList) > 0)){
			foreach($elementList as $element)
			{
				$tableRowsId[$line] = self::getDbTable() . '_' . $element->id;

				$elementLabel = __($element->name, 'wpshop');
				$subRowActions = '';
				$attributeSlugUrl = self::getListingSlug();
				if(current_user_can('wpshop_add_attributes_unit_group'))
				{
					$attributeSlugUrl = self::getEditionSlug();
				}
				if(current_user_can('wpshop_edit_attributes_unit_group'))
				{
					$editAction = admin_url('admin.php?page=' . $attributeSlugUrl . '&amp;action=edit&amp;id=' . $element->id);
					$subRowActions .= '
		<a href="#" id="edit_attribute_unit_group_' . $element->id . '" class="edit_attribute_unit_group" >' . __('Edit', 'wpshop') . '</a>';
				}
				elseif(current_user_can('wpshop_view_attributes_unit_group'))
				{
					$editAction = admin_url('admin.php?page=' . $attributeSlugUrl . '&amp;action=edit&amp;id=' . $element->id);
				}
				if(current_user_can('wpshop_delete_attributes_unit_group'))
				{
					if($subRowActions != '')
					{
						$subRowActions .= '&nbsp;|&nbsp;';
					}
					$subRowActions .= '
		<a href="#" id="delete_attribute_unit_group_' . $element->id . '" class="delete_attribute_unit_group" >' . __('Delete', 'wpshop') . '</a>';
				}

				$rowActions = '
	<div id="rowAction' . $element->id . '" class="wpshopRowAction" >' . $subRowActions . '
	</div>';

				unset($tableRowValue);
				$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => str_replace('\\', '', $elementLabel) . $rowActions);
				$tableRows[] = $tableRowValue;

				$line++;
			}
		}
		else{
			unset($tableRowValue);
			$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => __('No element to ouput here', 'wpshop'));
			$tableRows[] = $tableRowValue;
		}
		if(current_user_can('wpshop_add_attributes_unit_group')){
			$listItemOutput .= '
<input type="button" value="' . __('Add an unit group', 'wpshop') . '" class="button-secondary alignleft" name="add_attribute_unit_group" id="add_attribute_unit_group" />';
		}
		$listItemOutput .= wpshop_display::getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, true) . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $tableId . '").dataTable();
		jQuery("#wpshop_unit_list_tab").show();';
		if(current_user_can('wpshop_delete_attributes_unit_group')){
			$listItemOutput .= '
		wpshop(".delete_attribute_unit_group").click(function(){
			if(confirm(wpshopConvertAccentTojs("' . __('Are you sure you want to delete this unit group', 'wpshop')  .' ?"))){
				wpshop("#wpshop_unit_group_list").load(WPSHOP_AJAX_FILE_URL, {
					"post": "true",
					"elementCode": "attribute_unit_management",
					"action": "delete_attribute_unit_group",
					"elementIdentifier": wpshop(this).attr("id").replace("delete_attribute_unit_group_", "")
				});
			}
		});';
		}
		if(current_user_can('wpshop_edit_attributes_unit_group')){
			$listItemOutput .= '
		wpshop(".edit_attribute_unit_group").click(function(){
			wpshop("#wpshop_unit_group_list").load(WPSHOP_AJAX_FILE_URL, {
				"post": "true",
				"elementCode": "attribute_unit_management",
				"action": "edit_attribute_unit_group",
				"elementIdentifier": wpshop(this).attr("id").replace("edit_attribute_unit_group_", "")
			});
		});';
		}
		if(current_user_can('wpshop_add_attributes_unit_group')){
			$listItemOutput .= '
		wpshop("#add_attribute_unit_group").click(function(){
			wpshop("#wpshop_unit_group_list").load(WPSHOP_AJAX_FILE_URL, {
				"post": "true",
				"elementCode": "attribute_unit_management",
				"action": "add_attribute_unit_group"
			});
		});';
		}
		$listItemOutput .= '
	});
</script>';

		return $listItemOutput;
	}
	/**
	*	Return the page content to add a new item
	*
	*	@return string The html code that output the interface for adding a nem item
	*/
	function unit_group_edition($itemToEdit = ''){
		global $attribute_displayed_field;
		$dbFieldList = wpshop_database::fields_to_input(WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP);

		$editedItem = '';
		$_REQUEST['action'] = 'save_new_attribute_unit_group';
		if($itemToEdit != ''){
			$editedItem = self::get_unit_group($itemToEdit);
			$_REQUEST['action'] = 'update_attribute_unit_group';
		}

		$the_form_content_hidden = $the_form_general_content = $the_form_option_content = '';
		foreach($dbFieldList as $input_key => $input_def){
			$pageAction = isset($_REQUEST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . '_action']) : '';
			$requestFormValue = isset($_REQUEST[self::currentPageCode][$input_def['name']]) ? wpshop_tools::varSanitizer($_REQUEST[self::currentPageCode][$input_def['name']]) : '';
			$currentFieldValue = $input_def['value'];
			if(is_object($editedItem)){
				$currentFieldValue = $editedItem->$input_def['name'];
			}
			elseif(($pageAction != '') && ($requestFormValue != '')){
				$currentFieldValue = $requestFormValue;
			}

			$input_def['value'] = $currentFieldValue;

			$input_def['value'] = __(str_replace("\\", "", $input_def['value']), 'wpshop');
			$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP);

			if($input_def['type'] != 'hidden'){
				$label = 'for="' . $input_def['name'] . '"';
				if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox')){
					$label = '';
				}
				$input = '
	<div class="clear" >
		<div class="wpshop_form_label wpshop_' . self::currentPageCode . '_' . $input_def['name'] . '_label alignleft" >
			<label ' . $label . ' >' . __($input_def['name'], 'wpshop') . '</label>
		</div>
		<div class="wpshop_form_input wpshop_' . self::currentPageCode . '_' . $input_def['name'] . '_input alignleft" >
			' . $the_input . '
		</div>
	</div>';
				if(substr($input_def['name'], 0, 3) == 'is_'){
					$the_form_option_content .= $input;
				}
				else{
					$the_form_general_content .= $input;
				}
			}
			else{
				$the_form_content_hidden .= '
	' . $the_input;
			}
		}

		$the_form = '
<form name="' . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . '_form" id="' . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . '_form" method="post" action="' . WPSHOP_AJAX_FILE_URL . '" >
' . wpshop_form::form_input('action', 'action', $_REQUEST['action'], 'hidden') . '
' . wpshop_form::form_input('post', 'post', 'true' , 'hidden') . '
' . wpshop_form::form_input('elementCode', 'elementCode', 'attribute_unit_management' , 'hidden') . '
' . wpshop_form::form_input(self::currentPageCode . '_form_has_modification', self::currentPageCode . '_form_has_modification', 'no' , 'hidden') . '
	' . $the_form_content_hidden .'' . $the_form_general_content . '
	<input type="button" value="' . __('Retour', 'wpshop') . '" class="button-primary alignright" name="cancel_unit_group_edition" id="cancel_unit_group_edition" />
	<input type="submit" value="' . __('Save', 'wpshop') . '" class="button-primary alignright" name="save_new_unit_group" id="save_new_unit_group" />
</form>
<div class="wpshopHide" ><div id="default_value_content_default" >&nbsp;</div><div id="default_value_content_datetime" ><input type="checkbox" name="wp_wpshop__attribute[default_value]" value="date_of_current_day" />' . __('Date of the day', 'wpshop') . '</div></div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("' . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . '", "' . __('Are you sure you want to quit this page? You will loose all current modification', 'wpshop') . '", "' . __('Are you sure you want to delete this unit group?', 'wpshop') . '");

		jQuery("#wpshop_unit_list_tab").hide();

		jQuery("#cancel_unit_group_edition").click(function(){
			jQuery("#wpshop_unit_group_list").load(WPSHOP_AJAX_FILE_URL, {
				"post": "true",
				"elementCode": "attribute_unit_management",
				"action": "load_attribute_unit_groups"
			});
		});

		jQuery("#' . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . '_form").ajaxForm({
			target: "#wpshop_unit_group_list"
		});
	});
</script>';

		return $the_form;
	}


	/*	Default currecy for the entire shop	*/
	function wpshop_shop_currency_list_field() {
		global $wpdb;
		$wpshop_shop_currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$currency_group = get_option('wpshop_shop_currency_group');
		$current_currency = get_option('wpshop_shop_default_currency');

		$currencies_options = '';
		if ( !empty ($currency_group) ) {
			$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE group_id = ' . $currency_group . '', '');
			$currencies = $wpdb->get_results($query);
			foreach ( $currencies as $currency) {
				$currencies_options .= '<option value="'.$currency->id.'"'.(($currency->id == $current_currency) ? ' selected="selected"' : null).'>'.$currency->name.' ('.$currency->unit.')</option>';
			}
		}
		else {
			foreach($wpshop_shop_currencies as $k => $v) {
				$currencies_options .= '<option value="'.$k.'"'.(($k==$current_currency) ? ' selected="selected"' : null).'>'.$k.' ('.$v.')</option>';
			}
		}
		return '<select name="wpshop_shop_default_currency" class="wpshop_currency_field" >'.$currencies_options.'</select>';
	}


}