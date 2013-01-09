<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Define the different method to manage attributes set
*
*	Define the different method and variable used to manage attributes set
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/


/**
* Define the different method to manage attributes set
* @package wpshop
* @subpackage librairies
*/
class wpshop_attributes_set{
	/*	Define the database table used in the current class	*/
	const dbTable = WPSHOP_DBT_ATTRIBUTE_SET;
	/*	Define the url listing slug used in the current class	*/
	const urlSlugListing = WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING;
	/*	Define the url edition slug used in the current class	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING;
	/*	Define the current entity code	*/
	const currentPageCode = 'attribute_set';
	/*	Define the page title	*/
	const pageTitle = 'Attributes groups';
	/*	Define the page title when adding an attribute	*/
	const pageAddingTitle = 'Add an attribute group';
	/*	Define the page title when editing an attribute	*/
	const pageEditingTitle = 'Edit the attribute group "%s" affected to entity %s';

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
			if($action == 'edit'){
				$editedItem = self::getElement($objectInEdition);
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), __($editedItem->name, 'wpshop'), __($editedItem->entity, 'wpshop'));
			}
			elseif($action == 'add')
				$title = __(self::pageAddingTitle, 'wpshop');
		}
		return $title;
	}

	/**
	*	Define the different message and action after an action is send through the element interface
	*/
	function elementAction(){
		global $wpdb, $initialEavData;
		$pageMessage = $actionResult = '';

		/*	Start definition of output message when action is doing on another page	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/****************************************************************************/
		$saveditem = isset($_REQUEST['saveditem']) ? wpshop_tools::varSanitizer($_REQUEST['saveditem']) : '';
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		if(!empty($action) && ($action=='activate') && (!empty($_REQUEST['id']))){
			$query = $wpdb->update(self::getDbTable(), array('status'=>'moderated'), array('id'=>$_REQUEST['id']));
			wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page=' . self::getListingSlug() . "&action=edit&id=" . $_REQUEST['id']));
		}
		if(($action != '') && ($action == 'saveok') && ($saveditem > 0)){
			$editedElement = self::getElement($saveditem);
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), '<span class="bold" >' . $editedElement->name . '</span>');
		}
		elseif(($action != '') && ($action == 'deleteok') && ($saveditem > 0)){
			$editedElement = self::getElement($saveditem, "'deleted'");
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully deleted', 'wpshop'), '<span class="bold" >' . $editedElement->name . '</span>');
		}

		/*	Define the database operation type from action launched by the user	 */
		/*************************			GENERIC				****************************/
		/*************************************************************************/
		$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : ((!empty($_GET['action']) && ($_GET['action']=='delete')) ? $_GET['action'] : '');
		$id = isset($_REQUEST[self::getDbTable()]['id']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['id']) : ((!empty($_GET['id'])) ? $_GET['id'] : '');
		$set_section = !empty($_REQUEST[self::getDbTable()]['set_section']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['set_section']) : '';
		unset($_REQUEST[self::getDbTable()]['set_section']);

		/*	Specific case for the attribute groups	*/
		if(!isset($_REQUEST[self::getDbTable()]['status'])){
			$_REQUEST[self::getDbTable()]['status'] = 'moderated';
		}
		if(!isset($_REQUEST[self::getDbTable()]['default_set'])){
			$_REQUEST[self::getDbTable()]['default_set'] = 'no';
		}

		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue') || ($pageAction == 'delete'))){
			if(current_user_can('wpshop_edit_attribute_set')){
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete'){
					if(current_user_can('wpshop_delete_attribute_set'))
						$_REQUEST[self::getDbTable()]['status'] = 'deleted';
					else
						$actionResult = 'userNotAllowedForActionDelete';
				}
				$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
			}
			else{
				$actionResult = 'userNotAllowedForActionEdit';
			}
		}
		elseif(($pageAction != '') && (($pageAction == 'delete'))){
			if(current_user_can('wpshop_delete_attribute_set')){
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				$_REQUEST[self::getDbTable()]['status'] = 'deleted';
				$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
			}
			else
				$actionResult = 'userNotAllowedForActionDelete';
		}
		elseif(($pageAction != '') && (($pageAction == 'save') || ($pageAction == 'saveandcontinue') || ($pageAction == 'add'))){
			if(current_user_can('wpshop_add_attribute_set')){
				$_REQUEST[self::getDbTable()]['creation_date'] = date('Y-m-d H:i:s');
				$actionResult = wpshop_database::save($_REQUEST[self::getDbTable()], self::getDbTable());
				$id = $wpdb->insert_id;
				if ( empty( $set_section ) ) {
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_GROUP, array('status' => 'valid', 'attribute_set_id' => $id, 'position' => 1, 'creation_date' => current_time('mysql',0), 'code' => 'general',  'default_group' => 'yes', 'name' => __('Main information', 'wpshop')));

					$selected_entity_query = $wpdb->prepare("SELECT post_name FROM " . $wpdb->posts . " WHERE ID = %d", $_REQUEST[self::getDbTable()]['entity_id']);
					if (WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT == $wpdb->get_var($selected_entity_query)) {
						$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_GROUP, array('status' => 'valid', 'attribute_set_id' => $id, 'position' => 1, 'creation_date' => current_time('mysql',0), 'code' => 'prices',  'default_group' => 'no', 'name' => __('Prices', 'wpshop')));
						$price_attribute_set_id = $wpdb->insert_id;
						$price_tab = unserialize(WPSHOP_ATTRIBUTE_PRICES);
						unset($price_tab[array_search(WPSHOP_COST_OF_POSTAGE, $price_tab)]);
						foreach($price_tab as $price_code){
							$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $price_code);
							$attribute_id = $wpdb->get_var($query);
							switch($price_code){
								case WPSHOP_PRODUCT_PRICE_HT:
										$position = ( WPSHOP_PRODUCT_PRICE_PILOT == 'HT' ) ? 1 : 3;
									break;
								case WPSHOP_PRODUCT_PRICE_TAX:
										$position = 2;
									break;
								case WPSHOP_PRODUCT_PRICE_TTC:
										$position = ( WPSHOP_PRODUCT_PRICE_PILOT == 'HT' ) ? 3 : 1;
									break;
								case WPSHOP_PRODUCT_PRICE_TAX_AMOUNT:
										$position = 4;
									break;
							}
							$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status'=>'valid', 'creation_date'=>current_time('mysql', 0), 'entity_type_id'=>$_REQUEST[self::getDbTable()]['entity_id'], 'attribute_set_id'=>$id, 'attribute_group_id'=>$price_attribute_set_id, 'attribute_id'=>$attribute_id, 'position'=>$position));
						}
					}
				}
			}
			else
				$actionResult = 'userNotAllowedForActionAdd';
		}

		/*	When an action is launched and there is a result message	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/************		CHANGE ERROR MESSAGE FOR SPECIFIC CASE					*************/
		/****************************************************************************/
		if($actionResult != ''){
			$elementIdentifierForMessage = __('the attribute group', 'wpshop');
			if(!empty($_REQUEST[self::getDbTable()]['name']))$elementIdentifierForMessage = '<span class="bold" >' . $_REQUEST[self::getDbTable()]['name'] . '</span>';
			if($actionResult == 'error'){/*	CHANGE HERE FOR SPECIFIC CASE	*/
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . sprintf(__('An error occured while saving %s', 'wpshop'), $elementIdentifierForMessage);
			}
			elseif(($actionResult == 'done') || ($actionResult == 'nothingToUpdate')){
				/*****************************************************************************************************************/
				/*************************			CHANGE FOR SPECIFIC ACTION FOR CURRENT ELEMENT				****************************/
				/*****************************************************************************************************************/
				if(!empty($_REQUEST['wpshop_attribute_set_section_order'])){
					$newOrder = str_replace('attribute_group_', '', $_REQUEST['wpshop_attribute_set_section_order']);
					$order = explode(',', $newOrder);
					foreach($order as $position => $set_section_id){
						$_REQUEST['wpshop_attribute_set_section'][$set_section_id]['position']=$position;
					}
				}

				if(isset($_REQUEST['attribute_group_order']) && ($_REQUEST['attribute_group_order'] != '')){
					foreach($_REQUEST['attribute_group_order'] as $groupIdentifier => $newOrder){
						$newOrder = str_replace('attribute_', '', $newOrder);
						$order = explode(',', $newOrder);
						$groupId = str_replace('newOrder', '', $groupIdentifier);
						$i = 1;
						foreach($order as $element){
							if($element != ''){
								if((int)$groupId > 0){
									$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE attribute_id = %d AND status = %s AND attribute_set_id = %d", $element, 'valid', $id);
									$validElement = $wpdb->get_var($query);
									if(!empty($validElement)){
										$query = $wpdb->prepare("UPDATE " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " SET position = %d, attribute_group_id = %d, last_update_date = NOW() WHERE attribute_id = %d AND status = %s AND attribute_set_id = %d", $i, $groupId, $element, 'valid', $id);
									}
									else{
										$query = $wpdb->prepare("INSERT INTO " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " (id, status, creation_date, entity_type_id, attribute_set_id, attribute_group_id, attribute_id, position) VALUES ('', 'valid', NOW(), %d, %d, %d, %d, %d)", $_REQUEST[self::getDbTable()]['entity_id'], $id, $groupId, $element, $i);
									}
									$wpdb->query($query);
								}
								else{
									$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'deleted', 'last_update_date' => current_time('mysql', 0)), array('attribute_id' => $element, 'status' => 'valid', 'attribute_set_id' => $id));
								}
								$i++;
							}
						}
					}
				}
				if(!empty($_REQUEST['wpshop_attribute_set_section'])){
					foreach($_REQUEST['wpshop_attribute_set_section'] as $set_section_id => $set_section_options){
						if(!empty($set_section_options) && is_array($set_section_options)){
							$set_section_options['default_group'] = (!empty($_REQUEST['wpshop_attribute_set_section_is_default_of_set']) && ($_REQUEST['wpshop_attribute_set_section_is_default_of_set'] == $set_section_id)) ? 'yes' : 'no';
							$set_section_options['last_update_date'] = current_time('mysql', 0);
							$set_section_options['display_on_frontend'] = (!empty($set_section_options['display_on_frontend']) && ($set_section_options['display_on_frontend'] == 'yes')) ? 'yes' : 'no';
							$wpdb->update(WPSHOP_DBT_ATTRIBUTE_GROUP, $set_section_options, array('id'=>$set_section_id), array('%s'), array('%d'));
						}
					}
				}

				if ( !empty( $set_section ) ) {
					$parent_attribute_set_detail = self::getAttributeSetDetails($set_section, "'valid'");
					if ( !empty($parent_attribute_set_detail) ) {

						foreach ($parent_attribute_set_detail as $section => $section_detail) {
							$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_GROUP, array('status'=>'valid', 'attribute_set_id' => $id, 'creation_date'=>current_time('mysql', 0), 'code'=>$section_detail['code'], 'name'=>$section_detail['name'], 'default_group'=>$section_detail['is_default_group'], 'backend_display_type'=>$section_detail['backend_display_type'], 'used_in_shop_type'=>$section_detail['used_in_shop_type'], 'display_on_frontend'=>$section_detail['display_on_frontend']));
							$last_group_id = $wpdb->insert_id;
							foreach ($section_detail['attribut'] as $attribute) {
								$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status'=>'valid', 'creation_date'=>current_time('mysql', 0), 'entity_type_id'=>$attribute->entity_id, 'attribute_set_id'=>$id, 'attribute_group_id'=>$last_group_id, 'attribute_id'=>$attribute->id, 'position'=>$attribute->attr_position_in_group));
							}
						}

					}
				}

				/*	If the current group is selected as default group set all others for current entity at no	*/
				if($_REQUEST[self::getDbTable()]['default_set'] == 'yes'){
					$entity_to_take = 0;
					if(isset($_REQUEST['attribute_set_group_id']) && ($_REQUEST['attribute_set_group_id'] != '')){
						$entity_to_take = $_REQUEST['attribute_set_group_id'];
					}
					if(isset($_REQUEST[self::getDbTable()]['entity_id']) && ($_REQUEST[self::getDbTable()]['entity_id'] != '')){
						$entity_to_take = $_REQUEST[self::getDbTable()]['entity_id'];
					}
					if($entity_to_take > 0){
						$query = $wpdb->prepare("UPDATE " . self::getDbTable() . " SET default_set = 'no' WHERE id != %d AND entity_id = %d", $id, $entity_to_take);
						$wpdb->query($query);
					}
				}

				/*************************			GENERIC				****************************/
				/*************************************************************************/
				$pageMessage .= '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), $elementIdentifierForMessage);
				/* if(($pageAction == 'edit') || ($pageAction == 'save'))
					wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page=' . self::getListingSlug() . "&action=saveok&saveditem=" . $id));
				else */if($pageAction == 'add')
					wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page='.self::getListingSlug()."&action=edit&id=".$id));
				elseif($pageAction == 'delete')
					wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page='.self::getListingSlug()."&action=deleteok&saveditem=" . $id));
			}
			elseif(($actionResult == 'userNotAllowedForActionEdit') || ($actionResult == 'userNotAllowedForActionAdd') || ($actionResult == 'userNotAllowedForActionDelete'))
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . __('You are not allowed to do this action', 'wpshop');
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
	    $wpshop_list_table = new wpshop_attributes_set_custom_List_table();
	    //Fetch, prepare, sort, and filter our data...
		$status="'valid', 'moderated'";
		if(!empty($_REQUEST['attribute_groups_status'])){
			switch($_REQUEST['attribute_groups_status']){
				default:
					$status="'".$_REQUEST['attribute_groups_status']."'";
				break;
			}
		}
		$attr_set_list = wpshop_attributes_set::getElement('', $status);
		$i=0;
		foreach($attr_set_list as $attr_set){
			if(!empty($attr_set->id)){
				$attribute_set_list[$i]['id'] = $attr_set->id;
				$attribute_set_list[$i]['name'] = $attr_set->name;
				$attribute_set_list[$i]['status'] = $attr_set->status;
				$attribute_set_list[$i]['entity'] = $attr_set->entity;
				$attribute_set_details = self::getAttributeSetDetails($attr_set->id, "'valid'");

				$attribute_set_list[$i]['content'] = '';
				if(!empty($attribute_set_details)){
					foreach($attribute_set_details as $set_details){
						$attribute_set_list[$i]['content'] .= '<div><a href="'.admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page='.self::getListingSlug()."&action=edit&id=".$attr_set->id.'#attribute_group_'.$set_details['id']).'" >'.__($set_details['name'],'wpshop').'</a>  ';
						$has_att=false;
						foreach($set_details['attribut'] as $set_detail){
							if(!empty($set_detail->frontend_label) && ( $set_detail->code != 'product_attribute_set_id' ) ){
								$attribute_set_list[$i]['content'] .= __($set_detail->frontend_label,'wpshop').', ';
								$has_att=true;
							}
						}
						if(!empty($attribute_set_list[$i]['content'])){
							if($has_att)$attribute_set_list[$i]['content'] = substr($attribute_set_list[$i]['content'],0,-2);
							$attribute_set_list[$i]['content'] .= '</div>';
						}
					}
				}
				$i++;
			}
		}
   		$wpshop_list_table->prepare_items($attribute_set_list);

		ob_start();
?>
    <div class="wrap">
			<?php $wpshop_list_table->views() ?>
			<form id="attributes_set_filter" method="get">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
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
		global $attribute_hidden_field;

		$dbFieldList = wpshop_database::fields_to_input(self::getDbTable());
		$form_more_content = $the_form_content_hidden = $the_form_general_content = '';
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$bloc_list=array();

		$editedItem = '';
		if($itemToEdit != '')
			$editedItem = self::getElement($itemToEdit);

		foreach($dbFieldList as $input_key => $input_def){
			$input_name = $input_def['name'];
			$input_value = $input_def['value'];
			$input_def_id=$input_def['id']='wpshop_' . self::currentPageCode . '_edition_table_field_id_'.$input_name;

			$attributeAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
			$attributeFormValue = isset($_REQUEST[self::getDbTable()][$input_name]) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()][$input_name]) : '';

			/*	Get value by checking current object type	*/
			$currentFieldValue = $input_value;
			if(is_object($editedItem))
				$currentFieldValue = $editedItem->$input_name;
			elseif(($attributeAction != '') && ($attributeFormValue != ''))
				$currentFieldValue = $attributeFormValue;

			/*	Check if the field must be hidden	*/
			if(in_array($input_name, $attribute_hidden_field))
				$input_def['type'] = 'hidden';
			if ($input_name == 'entity_id') {
				$input_def['type'] = 'select';
				$input_def['possible_value'] = wpshop_entities::get_entities_list();

				$input_def['valueToPut'] = 'index';
				if ( is_object($editedItem) || (count($input_def['possible_value'])==1) ) {
					$input_def['type'] = 'hidden';
					$currentFieldValue=(count($input_def['possible_value'])==1)?$input_def['possible_value'][0]->id:$currentFieldValue;
				}
				$input_def['name'] = $input_name;
				$input_def['value'] = $currentFieldValue;

				$i=0;
				foreach($input_def['possible_value'] as $entity_id => $entity_name) {
					if($i <= 0){
						$current_entity_id = $entity_id;
					}
					$i++;
				}

				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
			}
			else {
				if(in_array($input_name, array('status', 'default_set'))){
					$input_def['type'] = 'checkbox';
					switch($input_name){
						case 'status':
							$input_name = __('Use this attribute group', 'wpshop');
							$input_def['possible_value'] = array('valid');
							$input_def_id.='_valid';
							$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using this attribute group', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
						break;
						case 'default_set':
							$input_def['possible_value'] = array('yes');
							$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using this attribute group as default group in selected element', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
							$input_def_id.='_yes';
						break;
					}
				}
				$input_def['value'] = $currentFieldValue;
				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
			}

			if($input_def['type'] != 'hidden'){
				$the_form_general_content .= '
		<tr class="wpshop_' . self::currentPageCode . '_edition_table_line wpshop_' . self::currentPageCode . '_edition_table_line_'.$input_name.'" >
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_label wpshop_' . self::currentPageCode . '_edition_table_field_label_'.$input_name.'" ><label for="'.$input_def_id.'" >' . __($input_name, 'wpshop') . '</label></td>
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_input wpshop_' . self::currentPageCode . '_edition_table_field_input_'.$input_name.'" >' . $the_input . '</td>
		</tr>';
			}
			else{
				$the_form_content_hidden .= '
		' . $the_input;
			}
		}

		if( empty($itemToEdit) ) {
			$the_input = wpshop_attributes_set::get_attribute_set_complete_list($current_entity_id,  self::getDbTable(), self::currentPageCode, false);
			$the_form_general_content .= '
			<tr class="wpshop_' . self::currentPageCode . '_edition_table_line wpshop_' . self::currentPageCode . '_edition_table_line_existing_attribute_set_copy_from" >
				<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_label wpshop_' . self::currentPageCode . '_edition_table_field_label_existing_attribute_set_copy_from" ><label for="'.$input_def_id.'" >' . __('Create the new group from an existing', 'wpshop') . '</label></td>
				<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_input wpshop_' . self::currentPageCode . '_edition_table_field_input_existing_attribute_set_copy_from" >' . $the_input . '</td>
			</tr>';
		}
		$the_form_general_content = '
<table class="wpshop_' . self::currentPageCode . '_edition_table wpshop_' . self::currentPageCode . '_edition_table_main_info" >
'.$the_form_general_content.'
</table>';

		/*	Default content for the current page	*/
		$bloc_list[self::currentPageCode]['main_info']['title']=__('Main informations', 'wpshop');
		$bloc_list[self::currentPageCode]['main_info']['content']=$the_form_general_content;

		if(is_object($editedItem)){
			$bloc_list[self::currentPageCode]['detail']['title']=__('Attribute group section details', 'wpshop');
			$bloc_list[self::currentPageCode]['detail']['content']=self::attributeSetDetailsManagement($itemToEdit);
		}

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="#" >
' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (!empty($_REQUEST['action'])?wpshop_tools::varSanitizer($_REQUEST['action']):'save'), 'hidden') . '
' . wpshop_form::form_input(self::getDbTable() . '_form_has_modification', self::getDbTable() . '_form_has_modification', 'no' , 'hidden') . $the_form_content_hidden . wpshop_display::custom_page_output_builder($bloc_list, WPSHOP_ATTRIBUTE_SET_EDITION_PAGE_LAYOUT) . '
	<div class="wpshop_edition_button wpshop_edition_button_'.self::currentPageCode.'" >';

		if(($action == 'add') && (current_user_can('wpshop_add_attribute_set')))
			$the_form .= '<input type="submit" class="button-primary" id="add" name="add" value="' . __('Add', 'wpshop') . '" />';
		elseif(current_user_can('wpshop_edit_attribute_set'))
			$the_form .= '<input type="submit" class="button-primary" id="save" name="save" value="' . __('Save', 'wpshop') . '" />';

		if(current_user_can('wpshop_delete_attribute_set') && ($action != 'add'))
			$the_form .= '<input type="button" class="button-secondary wpshop_delete_element_button wpshop_delete_element_button_'.self::currentPageCode.'" id="delete" name="delete" value="' . __('Delete', 'wpshop') . '" />';

		$the_form .= '
	</div>
</form>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("'.self::getDbTable().'", "' . __('Are you sure you want to quit this page? You will loose all current modification', 'wpshop') . '", "' . __('Are you sure you want to delete this attributes group?', 'wpshop') . '");

		jQuery("#wpshop_attribute_set_edition_table_field_id_entity_id").change(function(){
			jQuery(".wpshop_attribute_set_edition_table_field_input_existing_attribute_set_copy_from").html(jQuery("#wpshopLoadingPicture").html());

			var data = {
				action: "attribute_set_entity_selection",
				current_entity_id: jQuery(this).val(),
				wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_set_entity_selection") . '"
			};
			jQuery.post(ajaxurl, data, function(response) {
				jQuery(".wpshop_attribute_set_edition_table_field_input_existing_attribute_set_copy_from").html( response );
			}, "json");
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
	function getPageFormButton($element_id = 0){
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$currentPageButton = '';

		return $currentPageButton;
	}

	/**
	*	Get the existing element list into database
	*
	*	@param integer $elementId optionnal The element identifier we want to get. If not specify the entire list will be returned
	*	@param string $elementStatus optionnal The status of element to get into database. Default is set to valid element
	*
	*	@return object $elements A wordpress database object containing the element list
	*/
	function getElement($elementId = '', $elementStatus = "'valid', 'moderated'", $whatToSearch = 'id', $resultList = ''){
		global $wpdb;
		$elements = array();
		$moreQuery = "";

		if($elementId != '')
		{
			switch($whatToSearch)
			{
				case 'entity_code':
					$moreQuery = "
			AND ENTITIES.code = '" . $elementId . "' ";
				break;

				case 'entity_id':
					$moreQuery = "
			AND ATTRIBUTE_SET.entity_id = '" . $elementId . "' ";
				break;

				case 'is_default':
					$moreQuery = "
			AND ATTRIBUTE_SET.default_set = '" . $elementId . "' ";
				break;

				default:
					$moreQuery = "
			AND ATTRIBUTE_SET.id = '" . $elementId . "' ";
				break;
			}
		}

		$query = $wpdb->prepare(
		"SELECT ATTRIBUTE_SET.*, ENTITIES.post_name as entity
		FROM " . self::getDbTable() . " AS ATTRIBUTE_SET
			INNER JOIN " . $wpdb->posts . " AS ENTITIES ON (ENTITIES.ID = ATTRIBUTE_SET.entity_id)
		WHERE ATTRIBUTE_SET.status IN (".$elementStatus.") " . $moreQuery, ''
		);

		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if(($elementId == '') || ($resultList == 'all'))
		{
			$elements = $wpdb->get_results($query);
		}
		else
		{
			$elements = $wpdb->get_row($query);
		}

		return $elements;
	}

	/**
	*	Display inteface allowing to manage the attribute set and group details
	*
	*	@param object $atributeSetId The element's identifier we have to manage the details for
	*
	*	@return string $attributeSetDetailsManagement The html output of management interface
	*/
	function attributeSetDetailsManagement($attributeSetId = ''){
		global $validAttributeList;
		$user_more_script = $add_button = '';


		$attributeSetDetailsManagement = '
<div id="managementContainer" >';
	if(current_user_can('wpshop_add_attribute_group')){
		$dialog_title = __('New attribute set section name', 'wpshop');
		$dialog_identifier = 'wpshop_new_set_section_add';
		$dialog_input_identifier = 'wpshop_new_attribute_set_section_name';
		ob_start();
		include(WPSHOP_TEMPLATES_DIR.'admin/add_new_element_dialog.tpl.php');
		$attributeSetDetailsManagement .= ob_get_contents();
		ob_end_clean();

		$add_button_text = __('Add a section for this group', 'wpshop');
		$add_button_parent_class = 'attribute_set_section_add_new_button';
		$add_button_name = 'wpshop_create_new_set_section_top';
		ob_start();
		include(WPSHOP_TEMPLATES_DIR.'admin/add_new_element_with_dialog.tpl.php');
		$add_button = ob_get_contents();
		ob_end_clean();

		$user_more_script .= '
			jQuery("#'.$dialog_identifier.'").dialog({
				modal: true,
				autoOpen:false,
				show: "blind",
				buttons:{
					"'.__('Add', 'wpshop').'": function(){
						jQuery("#managementContainer").load(WPSHOP_AJAX_FILE_URL,{
							"post": "true",
							"elementCode": "' . self::currentPageCode . '",
							"action": "saveNewAttributeSetSection",
							"elementType": "attributeSetSection",
							"elementIdentifier": "' . $attributeSetId . '",
							"attributeSetSectionName": jQuery("#'.$dialog_input_identifier.'").val()
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
	$attributeSetDetailsManagement .= '
	<input class="newOrder" type="hidden" name="wpshop_attribute_set_section_order" id="wpshop_attribute_set_section_order" value="" />
	<ul class="attribute_set_group_details clear" >' . $add_button;

		/*	Get information about the current attribute set we are editing	*/
		$attributeSetDetails = self::getAttributeSetDetails($attributeSetId);
		if(is_array($attributeSetDetails) && (count($attributeSetDetails) > 0)){
			/*	Build output with the current attribute set details	*/
			foreach($attributeSetDetails as $attributeSetIDGroup => $attributeSetDetailsGroup){
				/*	Check possible action for general code	*/
				$elementActionClass = 'wpshop_attr_set_section_name';
				$edition_area = $edit_button = '';
				if ( current_user_can('wpshop_edit_attribute_group') ) {
					$elementActionClass = 'wpshop_attr_set_section_name_editable';
					$edit_button = '
			<a class="wpshop_attr_tool_box_button wpshop_attr_tool_box_edit wpshop_attr_tool_box_edit_attribute_set_section" id="wpshop_set_section_edit_'.$attributeSetDetailsGroup['id'].'" title="'.__('Edit this section', 'wpshop').'"></a>';

					$tpl_component = array();
					$tpl_component['ADMIN_GROUP_IDENTIFIER'] = str_replace('-', '_', sanitize_title($attributeSetDetailsGroup['id']));
					$tpl_component['ADMIN_GROUP_ID'] = $attributeSetDetailsGroup['id'];
					$tpl_component['ADMIN_GROUP_NAME'] = __($attributeSetDetailsGroup['name'], 'wpshop');
					$tpl_component['ADMIN_GROUP_DISPLAY_TYPE_TAB'] = (!empty($attributeSetDetailsGroup['backend_display_type']) && ($attributeSetDetailsGroup['backend_display_type']=='fixed-tab')?' selected="selected"':'');
					$tpl_component['ADMIN_GROUP_DISPLAY_TYPE_BOX'] = (!empty($attributeSetDetailsGroup['backend_display_type']) && ($attributeSetDetailsGroup['backend_display_type']=='movable-tab')?' selected="selected"':'');
					$tpl_component['ADMIN_GROUP_DISPLAY_ON_FRONTEND'] = (!empty($attributeSetDetailsGroup['display_on_frontend']) && ($attributeSetDetailsGroup['display_on_frontend']=='yes')?' checked="checked"':'');
					$edition_area = wpshop_display::display_template_element('wpshop_admin_attr_set_section_params', $tpl_component, array(), 'admin');
					unset($tpl_component);
				}
//<td rowspan="2" class="wpshop_attribute_set_section_detail_table_default_td" ><input title="'.__('Default section', 'wpshop').'" type="radio" name="wpshop_attribute_set_section_is_default_of_set" '.($is_default?'checked="checked" ':'').'id="wpshop_attribute_set_section_is_default_of_set_'.$attributeSetDetailsGroup['id'].'" value="'.$attributeSetDetailsGroup['id'].'" /></td>
				$is_default = (!empty($attributeSetDetailsGroup['is_default_group']) && ($attributeSetDetailsGroup['is_default_group']=='yes')?true:false);
				$attributeSetDetailsManagement .= '
	<li id="attribute_group_' . $attributeSetIDGroup . '" class="attribute_set_section_container attribute_set_section_container_'.($is_default?'is_default':'normal').'" >
		<table class="wpshpop_attribute_set_section_detail_table" >
			<tr>
				<td id="wpshop_attr_set_section_name_' . $attributeSetDetailsGroup['id'] . '" class="' . $elementActionClass . '" >' . __($attributeSetDetailsGroup['name'], 'wpshop') . '</td>
			</tr>
			<tr>
				<td>
					<input class="newOrder" type="hidden" name="attribute_group_order[newOrder' . $attributeSetIDGroup . ']" id="newOrder' . $attributeSetIDGroup . '" value="" />';

				/*	Add the set section details	*/
				$price_tab = unserialize(WPSHOP_ATTRIBUTE_PRICES);
				unset($price_tab[array_search(WPSHOP_COST_OF_POSTAGE, $price_tab)]);
				$no_delete_button = false;
				if ( is_array($attributeSetDetailsGroup['attribut']) && (count($attributeSetDetailsGroup['attribut']) >= 1) ) {
					$attributeSetDetailsManagement .= '
					<ul id="attribute_group_' . $attributeSetIDGroup . '_details" class="wpshop_attr_set_section_details" >';
					ksort($attributeSetDetailsGroup['attribut']);
					foreach ( $attributeSetDetailsGroup['attribut'] as $attributInGroup ) {
						if ( in_array($attributInGroup->code, $price_tab) ){
							$no_delete_button = true;
						}
						if ( !empty($attributInGroup->id) && ( $attributInGroup->code != 'product_attribute_set_id' ) ) {
							$attributeSetDetailsManagement .= '
						<li class="ui-state-default attribute' . (in_array($attributInGroup->code, $price_tab) ? ' ui-state-disabled' : '') . '" id="attribute_' . $attributInGroup->id . '" >' . __($attributInGroup->frontend_label, 'wpshop')  . '</li>';
						}
					}
					$attributeSetDetailsManagement .= '
					</ul>';
				}

					$attributeSetDetailsManagement .= $edition_area.'
				</td>
			</tr>
		</table>

		<div class="wpshop_admin_toolbox wpshop_attr_set_section_tool_box" >' . $edit_button;
			if ( current_user_can('wpshop_delete_attribute_group') && !$no_delete_button ) {
				$attributeSetDetailsManagement .= '
			<a class="wpshop_attr_tool_box_button wpshop_attr_tool_box_delete wpshop_attr_tool_box_delete_attribute_set_section" id="wpshop_set_section_delete_'.$attributeSetDetailsGroup['id'].'" title="'.__('Delete this section', 'wpshop').'"></a>';
			}
			$attributeSetDetailsManagement .= '
		</div>
	</li>';
			}
		}

		/*	Add the interface for not-affected attribute	*/
		$attributeSetDetailsManagement .= $add_button . '

	</ul>

	<div class="attribute_set_not_affected_attribute" >
		<fieldset>
			<legend id="attributeSetUnaffectedAttributeSection" class="attributeSetSectionName" >' . __('Attribute not affected at this group', 'wpshop') . '</legend>
			<ul id="attribute_group_NotAffectedAttribute_details" class="wpshop_attr_set_section_details" >';

		/*	Get the not affected attribute list	*/
		$notAffectedAttributeList = self::get_not_affected_attribute($attributeSetId, $attributeSetDetailsGroup['entity_id']);
		if(count($notAffectedAttributeList) > 0){
			foreach($notAffectedAttributeList as $notAffectedAttribute){
				if( (is_null($validAttributeList) || !in_array($notAffectedAttribute->id, $validAttributeList)) && ( $notAffectedAttribute->code != 'product_attribute_set_id' ) && ($attributeSetDetailsGroup['entity_id'] == $notAffectedAttribute->entity_id) ){
					$attributeSetDetailsManagement .= '
			<li class="ui-state-default attribute" id="attribute_' . $notAffectedAttribute->id . '" >' . __($notAffectedAttribute->frontend_label, 'wpshop') . '</li>';
				}
			}
		}

		$attributeSetDetailsManagement .= '
			</ul>
			<input class="newOrder" type="hidden" name="attribute_group_order[newOrderNotAffectedAttribute]" id="newOrderNotAffectedAttribute" value="" />
		</fieldset>
	</div>';

		if(current_user_can('wpshop_delete_attribute_group')){
			$user_more_script .= '
			jQuery(".wpshop_attr_tool_box_delete").click(function(){
				if(confirm(wpshopConvertAccentTojs("'.__('Are you sure you want to delete this atribute set section?', 'wpshop').'"))){
					jQuery("#ajax-response").load(WPSHOP_AJAX_FILE_URL,{
						"post": "true",
						"elementCode": "' . self::currentPageCode . '",
						"action": "deleteAttributeSetSection",
						"elementType": "attributeSetSection",
						"elementIdentifier": "' . $attributeSetId . '",
						"attributeSetSectionId": jQuery(this).attr("id").replace("wpshop_set_section_delete_","")
					});
				}
			});';
		}
		$attributeSetDetailsManagement .= '
	<script type="text/javascript" >
		wpshop(document).ready(function(){
			make_list_sortable("' . WPSHOP_DBT_ATTRIBUTE_SET . '");'.$user_more_script.'
		});
	</script>
	<div class="clear"></div>
</div>';

		return $attributeSetDetailsManagement;
	}

	/**
	*	Get the complete details about attributes sets
	*
	*	@param integer $attributeSetId The attribute set identifier we want to get the details for
	*	@param string $attributeSetStatus optionnal The attribute set status. Allows to define if we want all attribute sets or a deleted or valid and so on
	*
	*	@return array $attributeSetDetailsGroups The List of attribute and attribute groups for the given attribute set
	*/
	function getAttributeSetDetails($attributeSetId, $attributeSetStatus = "'valid', 'moderated'"){
		global $wpdb, $validAttributeList;
		$attributeSetDetailsGroups = '';

		$query = $wpdb->prepare(
			"SELECT ATTRIBUTE_GROUP.id AS attr_group_id, ATTRIBUTE_GROUP.backend_display_type AS backend_display_type, ATTRIBUTE_GROUP.used_in_shop_type,
				ATTRIBUTE_GROUP.code AS attr_group_code, ATTRIBUTE_GROUP.position AS attr_group_position, ATTRIBUTE_GROUP.name AS attr_group_name,
				ATTRIBUTE.*, ATTRIBUTE_DETAILS.position AS attr_position_in_group, ATTRIBUTE_GROUP.id as attribute_detail_id, ATTRIBUTE_GROUP.default_group,
				ATTRIBUTE_GROUP.display_on_frontend, ATTRIBUTE_SET.entity_id
			FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
				INNER JOIN " . self::getDbTable() . " AS ATTRIBUTE_SET ON (ATTRIBUTE_SET.id = ATTRIBUTE_GROUP.attribute_set_id)
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_DETAILS ON ((ATTRIBUTE_DETAILS.attribute_group_id = ATTRIBUTE_GROUP.id) AND (ATTRIBUTE_DETAILS.attribute_set_id = ATTRIBUTE_SET.id) AND (ATTRIBUTE_DETAILS.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE ON ((ATTRIBUTE.id = ATTRIBUTE_DETAILS.attribute_id) AND (ATTRIBUTE.status = 'valid') AND (ATTRIBUTE.entity_id = ATTRIBUTE_SET.entity_id))
			WHERE ATTRIBUTE_SET.id = %d
				AND ATTRIBUTE_SET.status IN (" . $attributeSetStatus . ")
				AND ATTRIBUTE_GROUP.status IN (" . $attributeSetStatus . ")
			ORDER BY ATTRIBUTE_GROUP.position, ATTRIBUTE_DETAILS.position",
			$attributeSetId);
		$attributeSetDetails = $wpdb->get_results($query);

		foreach($attributeSetDetails as $attributeGroup){
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['id'] = $attributeGroup->attribute_detail_id;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['code'] = $attributeGroup->attr_group_code;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['name'] = $attributeGroup->attr_group_name;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['is_default_group'] = $attributeGroup->default_group;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['backend_display_type'] = $attributeGroup->backend_display_type;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['used_in_shop_type'] = $attributeGroup->used_in_shop_type;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['display_on_frontend'] = $attributeGroup->display_on_frontend;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['entity_id'] = $attributeGroup->entity_id;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['attribut'][$attributeGroup->attr_position_in_group] = $attributeGroup;
			if ( in_array($attributeGroup->code, unserialize(WPSHOP_ATTRIBUTE_PRICES)) ) {
				$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['prices'][$attributeGroup->code] = $attributeGroup;
			}
			$validAttributeList[] = $attributeGroup->id;
		}

		return $attributeSetDetailsGroups;
	}

	/**
	*	Get the attribute list of attribute not associated to he set we are editing
	*
	*	@param integer $attributeSetId The attribute set identifier we want to get the details for
	*
	*	@return array $attributeSetDetails The List of attribute not affected
	*/
	function get_not_affected_attribute($attributeSetId, $entity_set_id){
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT ATTRIBUTE.*
			FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_DETAILS
				INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE ON ((ATTRIBUTE.id = ATTRIBUTE_DETAILS.attribute_id) AND (ATTRIBUTE.status = 'valid') AND (ATTRIBUTE.entity_id = ATTRIBUTE_DETAILS.entity_type_id))
			WHERE ATTRIBUTE_DETAILS.status = 'deleted'
				AND ATTRIBUTE_DETAILS.attribute_set_id = %d
				AND ATTRIBUTE_DETAILS.entity_type_id = %d
			GROUP BY ATTRIBUTE_DETAILS.attribute_id

		UNION

			SELECT ATTRIBUTE.*
			FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE
			WHERE ATTRIBUTE.status = 'valid'
				AND ATTRIBUTE.id NOT IN (
					SELECT ATTRIBUTE_DETAILS.attribute_id
					FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_DETAILS
					WHERE ATTRIBUTE_DETAILS.status = 'valid'
						AND ATTRIBUTE_DETAILS.attribute_set_id = %d
						AND ATTRIBUTE.entity_id = ATTRIBUTE_DETAILS.entity_type_id
						AND ATTRIBUTE_DETAILS.entity_type_id = %d
				)
			GROUP BY ATTRIBUTE.id", $attributeSetId, $entity_set_id, $attributeSetId, $entity_set_id);
		$attributeSetDetails = $wpdb->get_results($query);

		return $attributeSetDetails;
	}

	/**
	*	Get the existing attribute set for an entity
	*
	*	@param integer $entityId The entity identifier we want to get the entity set list for
	*
	*	@return object $entitySets The entity sets list for the given entity
	*/
	function get_attribute_set_list_for_entity($entityId){
		global $wpdb;
		$entitySetList = '';

		$query = $wpdb->prepare(
			"SELECT id, name,  default_set
			FROM " . self::getDbTable() . "
			WHERE status = 'valid'
				AND entity_id = %d",
			$entityId);
		$entitySetList = $wpdb->get_results($query);

		return $entitySetList;
	}

	/**
	* Traduit le shortcode et affiche un groupe d'attributs
	* @param array $atts : tableau de param�tre du shortcode
	* @return mixed
	**/
	function wpshop_att_group_func($atts) {
		global $wpdb;
		$query = '
		SELECT '.WPSHOP_DBT_ATTRIBUTE.'.frontend_label, '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.value AS value_decimal, '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.value AS value_datetime, '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.value AS value_integer, '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.value AS value_text, '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.value AS value_varchar, '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.unit AS unit
		FROM '.WPSHOP_DBT_ATTRIBUTE_DETAILS.'
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE.' ON '.WPSHOP_DBT_ATTRIBUTE_DETAILS.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_UNIT.' ON (
				'.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.unit_id
			)
		WHERE
			'.WPSHOP_DBT_ATTRIBUTE_DETAILS.'.status="valid"
			AND '.WPSHOP_DBT_ATTRIBUTE.'.status="valid"
			AND '.WPSHOP_DBT_ATTRIBUTE_DETAILS.'.attribute_group_id='.$atts['sid'].'
			AND (
				'.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.entity_id='.$atts['pid'].'
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.entity_id='.$atts['pid'].'
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.entity_id='.$atts['pid'].'
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.entity_id='.$atts['pid'].'
				OR '.WPSHOP_DBT_ATTRIBUTE.'_value_varchar.entity_id='.$atts['pid'].'
			)
		';
		$data = $wpdb->get_results($query);
		foreach($data as $d) {
			echo '<strong>'.__($d->frontend_label, 'wpshop').'</strong> : '.$d->value_decimal.$d->value_datetime.$d->value_integer.$d->value_text.$d->value_varchar.' ('.$d->unit.')<br />';
		}
	}


	/**
	 * Récupération des groupes et/ou sous-groupes d'attributs pour une entité donnée
	 *
	 * @param integer $entity_id Identifiant de l'entité dont on veut récupérer la liste des groupes et/ou sous-groupes
	 * @param string $table Permet de définir quel est l'élément en cours d'édition
	 * @param string $page_code Code de la page courante
	 * @param boolean $complete_tree Si ce paramètre est à vrai alors on affiche les sous-groupes, dans le cas contraire on affiche uniquement les groupes
	 *
	 * @return string Le code html permettant d'afficher la liste des groupes et/ou sous-groupes d'attributs
	 */
	function get_attribute_set_complete_list($entity_id, $table, $page_code, $complete_tree = true){
		$the_input = __('There is no attribute set for this entity', 'wpshop');

		$attr_set_list = wpshop_attributes_set::getElement($entity_id, "'valid'", 'entity_id', 'all');
		if ( !empty($attr_set_list) ) {
			$the_input = '<select name="' . $table . '[set_section]" class="wpshop_' . $page_code . '_set_section" >';
			if (!$complete_tree) {
				$the_input .= '<option value="0">'.__('None', 'wpshop').'</option>';
			}

			foreach ( $attr_set_list as $attr_set_index => $attr_set ) {
				if ( !empty($attr_set->id) ) {
					$attribute_set_details = wpshop_attributes_set::getAttributeSetDetails($attr_set->id, "'valid'");
					if ( !empty($attribute_set_details) ) {

						if ($complete_tree) {
							$the_input .= '<optgroup label="'.__($attr_set->name, 'wpshop').'" >';
							foreach ( $attribute_set_details as $set_details ) {
								$selected = ( ( $attr_set->default_set == 'yes' ) && ( $set_details['is_default_group'] == 'yes' ) ? ' selected="selected"' : '' );
								$the_input .= '<option'.$selected.' value="'.$attr_set->id.'_'.$set_details['id'].'">'.__($set_details['name'],'wpshop').'</option>';
							}
							$the_input .= '</optgroup>';
						}
						else {
							$the_input .= '<option value="'.$attr_set->id.'">'.__($attr_set->name, 'wpshop').'</option>';
						}
					}
				}
			}
			$the_input .= '</select>';
		}

		return $the_input;
	}

}

?>