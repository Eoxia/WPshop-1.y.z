<?php
/**
* Plugin ajax request management.
*
*	Every ajax request will be send to this page wich will return the request result regarding all the parameters
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage includes
*/

/*	Wordpress - Ajax functionnality activation	*/
DEFINE('DOING_AJAX', true);
/*	Wordpress - Specify that we are in wordpress admin	*/
DEFINE('WP_ADMIN', true);
/*	Wordpress - Main bootstrap file that load wordpress basic files	*/
require_once('../../../../wp-load.php');
/*	Wordpress - Admin page that define some needed vars and include file	*/
require_once(ABSPATH . 'wp-admin/includes/admin.php');


/**
*	First thing we define the main directory for our plugin in a super global var
*/
//DEFINE('WPSHOP_PLUGIN_DIR', basename(dirname(__FILE__)));
/**
*	Include the different config for the plugin
*/
require_once(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/config.php' );
/**
*	Include the file which includes the different files used by all the plugin
*/
require_once(WPSHOP_INCLUDES_DIR.'include.php');

/*	Get the different resquest vars to sanitize them before using	*/
$method = isset($_REQUEST['post']) ? wpshop_tools::varSanitizer($_REQUEST['post'], '') : null;
$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action'], '') : null;

/*	Element code define the main element type we are working on	*/
$elementCode = isset($_REQUEST['elementCode']) ? wpshop_tools::varSanitizer($_REQUEST['elementCode']) : null;

/*	Element code define the secondary element type we are working on. For example when working on elementCode:Document elementType:product, we are working on the document for products	*/
$elementType = isset($_REQUEST['elementType']) ? wpshop_tools::varSanitizer($_REQUEST['elementType']) : null;
$elementIdentifier = isset($_REQUEST['elementIdentifier']) ? wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']) : null;


/*	Look at the element type we have to work on	*/
switch ( $elementCode ) {
	// Login
	case 'ajax_login':
		$status = false; $reponse='';
		if($wpshop->validateForm($wpshop_account->login_fields)) {
			// Log the customer
			if($wpshop_account->isRegistered($_REQUEST['account_email'], $_REQUEST['account_password'], true)) {
				$status = true;
			} else $status = false;
		}
		// If there is errors
		if($wpshop->error_count()>0) {
			$reponse = $wpshop->show_messages();
		}
		$reponse = array('status' => $status, 'reponse' => $reponse);
		echo json_encode($reponse);
	break;

	// Register
	case 'ajax_register':
		$status = false; $reponse='';
		if($wpshop->validateForm($wpshop_account->personal_info_fields) && $wpshop->validateForm($wpshop_account->billing_fields)) {
			if(isset($_REQUEST['shiptobilling']) || (!isset($_REQUEST['shiptobilling']) && $wpshop->validateForm($wpshop_account->shipping_fields))) {
				$wpshop_checkout = new wpshop_checkout();
				if ($wpshop_checkout->new_customer_account()) {
					$status = true;
				} else $status = false;
			}
		}
		// If there is errors
		if($wpshop->error_count()>0) {
			$reponse = $wpshop->show_messages();
		}
		$reponse = array('status' => $status, 'reponse' => $reponse);
		echo json_encode($reponse);
	break;

	//	Load user infos
	case 'ajax_order_customer_adress_load':
		$current_order_id = !empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 0;
		$action = !empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'Shipping';
		$current_customer_id = !empty( $_REQUEST['customer_id'] ) ? $_REQUEST['customer_id'] : 0;

		if ( !empty( $current_order_id ) ) {
			$order_postmeta = get_post_meta($current_order_id, '_order_postmeta', true);
			$order_info = get_post_meta($current_order_id, '_order_info', true);
		}

		$current_order_address = (!empty($order_info[strtolower($action)]) ? $order_info[strtolower($action)] : array());
		$current_order_status = (!empty($order_postmeta['order_status']) ? $order_postmeta['order_status'] : '');

		echo wpshop_account::edit_customer_address($action, $current_order_address, $current_customer_id, $current_order_status);
	break;

	case 'ajax_refresh_order':{
		/*	Get order current content	*/
		$order_meta = get_post_meta($elementIdentifier, '_order_postmeta', true);

		switch($action){
			case 'order_product_content':{
				/*	Check if there are product to delete from order	*/
				$listing_to_delete = array();
				if(isset($_REQUEST['product_to_delete']) && ($_REQUEST['product_to_delete'] != '')){
					$listing_to_delete = explode(',', $_REQUEST['product_to_delete']);
				}

				/*	Check product quantity to update	*/
				$listing_to_update = array();
				if(isset($_REQUEST['product_to_update_qty']) && ($_REQUEST['product_to_update_qty'] != '') && (is_array($_REQUEST['product_to_update_qty']))){
					$temp_listing_to_update = $_REQUEST['product_to_update_qty'];
					foreach($temp_listing_to_update as $pdt_id_qty){
						$pdt_infos = explode('_x_', $pdt_id_qty);
						$listing_to_update[$pdt_infos[0]] = $pdt_infos[1];
					}
				}

				$order_items = array();
				if(is_array($order_meta['order_items']) && count($order_meta['order_items']) > 0){
					foreach($order_meta['order_items'] as $order_item_key => $order_item){
						$order_items[$order_item['item_id']]['product_id'] = $order_item['item_id'];
						$order_items[$order_item['item_id']]['product_qty'] = $order_item['item_qty'];

						/*	If current product exists into product list to delete	*/
						if(in_array($order_item['item_id'], $listing_to_delete)){
							unset($order_items[$order_item['item_id']]);
						}

						/*	Check product quantity for update	*/
						if(array_key_exists($order_item['item_id'], $listing_to_update)){
							$order_items[$order_item['item_id']]['product_qty'] = $listing_to_update[$order_item['item_id']];
						}
					}
				}

				$order_custom_infos = '';
				if(isset($_REQUEST['order_shipping_cost']) && ($_REQUEST['order_shipping_cost']>=0)){
					$order_custom_infos['custom_shipping_cost'] = $_REQUEST['order_shipping_cost'];
				}
				$order_meta = array_merge($order_meta, wpshop_cart::calcul_cart_information($order_items, $order_custom_infos));

			}break;
			// Set the shipping price to zero
			case 'set_shipping_to_free':{
				$order_meta['order_old_shipping_cost'] = $order_meta['order_shipping_cost'];
				$order_meta['shipping_is_free'] = true;
				$order_meta['order_grand_total'] = $order_meta['order_grand_total'] - $order_meta['order_shipping_cost'];
				$order_meta['order_shipping_cost'] = 0;
			}break;
			// Unset the shipping price to zero
			case 'unset_shipping_to_free':{
				$order_meta['order_shipping_cost'] = $order_meta['order_old_shipping_cost'];
				$order_meta['order_grand_total'] = $order_meta['order_grand_total'] + $order_meta['order_shipping_cost'];
				unset($order_meta['order_old_shipping_cost']);
				unset($order_meta['shipping_is_free']);
			}break;
		}

		/*	Update order content	*/
		update_post_meta($elementIdentifier, '_order_postmeta', $order_meta);

		echo wpshop_orders::order_content(get_post($elementIdentifier));
	}break;

	//	Load product list
	case 'ajax_load_product_list':{
		$product_per_page = 6;
		$current_order_id = (isset($_POST['order_id']) && ($_POST['order_id'] > 0)) ? $_POST['order_id'] : 0;
		$current_page = (isset($_POST['page']) && ($_POST['page'] > 0)) ? $_POST['page'] : 1;

		if($current_order_id > 0){
			$posts = query_posts(array(
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT
			));
			foreach($posts as $post){
				$post_meta = get_post_meta($post->ID, '_wpshop_product_metadata');
				$post_info = get_post($post->ID, ARRAY_A);
				$post = !empty($post_meta[0]) ? array_merge($post_info, (array)$post_meta[0]) : $post_info;
				$data[] = $post;
			}

			$product_list_for_selection_pagination = '<div class="dialog_listing_pagination_container alignright" >' . paginate_links(array(
				'base' => '#',
				'current' => $current_page,
				'total' => $wp_query->max_num_pages,
				'type' => 'list',
				'prev_next' => false
			)) . '</div>';
			wp_reset_query(); // important

			$product_association_box = '<div id="product_selection_dialog_msg" class="wpshopHide wpshopPageMessage wpshopPageMessage_Updated" >&nbsp;</div><div id="product_listing_container" ><form action="' . WPSHOP_AJAX_FILE_URL . '" id="wpshop_order_selector_product_form" ><input type="hidden" name="list_has_been_modified" id="list_has_been_modified" value="" /><input type="hidden" name="post" value="true" /><input type="hidden" name="order_id" value="' . $current_order_id . '" /><input type="hidden" name="elementCode" value="ajax_add_product_to_order" />' . wpshop_products::custom_product_list() . '</form></div>
<script type="text/javascript" >
wpshop(document).ready(function(){
jQuery(".wpshop_product_cb_dialog").click(function(){
	jQuery("#list_has_been_modified").val("yes");
});

/*	If click on quantity change, check the box	*/
jQuery(".order_product_action_button.qty_change").click(function(){
	jQuery(this).parent("td").parent("tr").children("td:first").children("input").prop("checked", true);
});

jQuery(".dialog_listing_pagination_container ul.page-numbers li a").click(function(){
	go_to_selected_page = true;
	if((jQuery("#list_has_been_modified").val() == "yes") && !confirm(wpshopConvertAccentTojs("' . __('Are you sure you want to change page without saving current selection?', 'wpshop') . '"))){
		go_to_selected_page = false;
	}

	if(go_to_selected_page){
		jQuery("#product_chooser_picture").show();
		jQuery("#product_chooser_container").hide();
		jQuery("#product_chooser_container").load("' . WPSHOP_AJAX_FILE_URL . '",{
			"post":true,
			"elementCode":"ajax_load_product_list",
			"order_id":"' . $current_order_id . '",
			"page": jQuery(this).html()
		});
	}
});

jQuery("#wpshop_order_selector_product_form").ajaxForm({
	target: "#product_selection_dialog_msg",
	beforeSubmit: function(formData, jqForm, options){/*	Check if the form for adding product is not empty before posting	*/
		var selected_product_list = 0;
		jQuery(".wpshop_product_cb_dialog").each(function(){
			if(jQuery(this).is(":checked")){
				selected_product_list+=1;
			}
		});
		if(selected_product_list <= 0){
			alert(wpshopConvertAccentTojs("' . __('You did not selected any product to add to the current order', 'wpshop') . '"));
			return false;
		}
	},
	resetForm: true
});
});
</script>';
		}
		else{
			$product_association_box = __('We are unable to satisfy your request because of the order you asked was not found in your database', 'wpshop');
		}

		echo $product_association_box . '
<script type="text/javascript" >
wpshop(document).ready(function(){
jQuery("#product_chooser_picture").hide();
jQuery("#product_chooser_container").show();
});
</script>';
	}break;
	case 'ajax_add_product_to_order':{
		$current_order_id = (isset($_GET['order_id']) && ($_GET['order_id'] > 0)) ? $_GET['order_id'] : 0;

		/*	Retrieve existing order meta, in order to update and not to overwrite	*/
		$order_meta = get_post_meta($current_order_id, '_order_postmeta', true);

		$order_items = array();
		foreach($_GET['wp_list_product'] as $pid){
			$order_items[$pid]['product_id'] = $pid;
			$order_items[$pid]['product_qty'] = $_GET['wpshop_pdt_qty'][$pid];
		}
		if(isset($order_meta['order_items']) && is_array($order_meta['order_items'])){
			foreach($order_meta['order_items'] as $product_in_order){
				if(!isset($order_items[$product_in_order['item_id']])){
					$order_items[$product_in_order['item_id']]['product_id'] = $product_in_order['item_id'];
					$order_items[$product_in_order['item_id']]['product_qty'] = $product_in_order['item_qty'];
				}
				else{
					$order_items[$product_in_order['item_id']]['product_qty'] += $product_in_order['item_qty'];
				}
			}
		}

		$order_meta = wpshop_cart::calcul_cart_information($order_items);

		/*	Update order content	*/
		update_post_meta($current_order_id, '_order_postmeta', $order_meta);

		echo '
<script type="text/javascript" >
wpshop(document).ready(function(){
jQuery("#product_selection_dialog_msg").html(wpshopConvertAccentTojs("' . __('Order has been updated', 'wpshop') . '"));
jQuery("#product_selection_dialog_msg").show();
setTimeout(function(){
	wpshop("#product_selection_dialog_msg").removeClass("wpshopPageMessage_Updated");
	wpshop("#product_selection_dialog_msg").html("");
}, 7000);
jQuery("#order_product_container").load(WPSHOP_AJAX_FILE_URL,{
	"post":"true",
	"elementCode":"ajax_refresh_order",
	"action":"order_product_content",
	"elementIdentifier":"' . $current_order_id . '"
});
});
</script>';
	}break;

	case 'attribute_set':
		switch ( $elementType ) {
			case 'attributeSetSection':
				switch ( $action ) {
					case 'saveNewAttributeSetSection':
						$attributeSetSectionName = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionName']);
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
						else
							$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_ERROR_ICON . '\' alt=\'action_error\' class=\'wpshopPageMessage_Icon\' />' . __('An error occured while saving new section', 'wpshop');
						echo wpshop_attributes_set::attributeSetDetailsManagement($elementIdentifier) . '<script type="text/javascript" >wpshop(document).ready(function(){	jQuery("#wpshop_new_set_section_add").dialog("close");jQuery("#wpshop_new_set_section_add").children("img").hide(); });wpshopShowMessage("' . $attributeSetSectionCreation_Result . '");hideShowMessage(5000);'.$more_script.'</script>';
					break;

					case 'editAttributeSetSection':
						$attributeSetSectionName = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionName']);
						$attributeSetSectionId = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionId']);
						$attributeSetSectionDefault = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionDefault']);
						$backend_display_type = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionDisplayType']);
						$display_on_frontend = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionDisplayinFrontEnd']);
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

					break;

					case 'deleteAttributeSetSection':
						$attributeSetSectionId = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionId']);
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
					break;
				}
			break;
		}
	break;

	case 'attribute_unit_management':
		switch ( $action ) {
			case 'load_unit_interface':
				echo '
					<div id="wpshop_unit_main_listing_interface">
					<div class="wpshop_full_page_tabs">						
					<ul class="ui-tabs-nav">
					<li id="wpshop_unit_list_tab" class="ui-state-default"><a href="#wpshop_unit_list" >' . __('Unit', 'wpshop') . '</a></li>
					<li id="wpshop_unit_group_list_tab" ><a href="#wpshop_unit_group_list" >' . __('Unit group', 'wpshop') . '</a></li>
					</ul>
					</div>
					<div id="wpshop_unit_list" >' . wpshop_attributes_unit::elementList() . '</div>
					<div id="wpshop_unit_group_list" >' . wpshop_attributes_unit::unit_group_list() . '</div>
					</div>
					<script type="text/javascript" >
					wpshop(document).ready(function(){
					jQuery("#wpshop_unit_main_listing_interface").tabs();
					});
					</script>';
			break;


			case 'load_attribute_units':
				echo wpshop_attributes_unit::elementList();
			break;

			case 'add_attribute_unit':
			case 'edit_attribute_unit':
				$atribute_unit = '';
				if ( $action == 'edit_attribute_unit' ) {
					$atribute_unit = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);
				}
				echo wpshop_attributes_unit::elementEdition($atribute_unit);
			break;
			case 'save_new_attribute_unit':
				$save_output = '';

				$attribute_unit_informations['id'] = '';
				$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['status']);
				$attribute_unit_informations['creation_date'] = date('Y-m-d H:i:s');
				$attribute_unit_informations['name'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['name']);
				$attribute_unit_informations['unit'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['unit']);
				$attribute_unit_informations['group_id'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['group_id']);

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
			break;
			case 'update_attribute_unit':
				$save_output = '';

				$attributeUnitId = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['id']);
				$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['status']);
				$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
				$attribute_unit_informations['name'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['name']);
				$attribute_unit_informations['unit'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['unit']);
				$attribute_unit_informations['group_id'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['group_id']);
				$attribute_unit_informations['is_default_of_group'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['is_default_of_group']);
				
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
			break;
			case 'delete_attribute_unit':
				$unit_id = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);
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
			break;


			case 'load_attribute_unit_groups':
				echo wpshop_attributes_unit::unit_group_list();
			break;
			
			case 'add_attribute_unit_group':
			case 'edit_attribute_unit_group':
				$atribute_unit_group = '';
				if($action == 'edit_attribute_unit_group'){
					$atribute_unit_group = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);
				}
				echo wpshop_attributes_unit::unit_group_edition($atribute_unit_group);
			break;
			
			case 'save_new_attribute_unit_group':
			{
				$save_output = '';

				$attribute_unit_informations['id'] = '';
				$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['status']);
				$attribute_unit_informations['creation_date'] = date('Y-m-d H:i:s');
				$attribute_unit_informations['name'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['name']);

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
			}
			break;
			case 'update_attribute_unit_group':
			{
				$save_output = '';

				$attributeUnitId = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['id']);
				$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['status']);
				$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
				$attribute_unit_informations['name'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['name']);

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
			}
			break;
			case 'delete_attribute_unit_group':
			{
				$unit_id = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);
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
			}
			break;
		}
	break;

	case 'tools':{
		switch($action){
			case 'db_manager':{
				/*	Display a list of operation made for the different version	*/
				$plugin_db_modification_content = '';
				foreach($wpshop_db_table_operation_list as $plugin_db_version => $plugin_db_modification){
					$plugin_db_modification_content .= '
<div class="tools_db_modif_list_version_number" >
' . __('Version', 'evarisk') . '&nbsp;' . $plugin_db_version . '
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
										if(isset($columns->Field) && ($columns->Field == $column_name)){
											$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Field has been created', 'wpshop') . '" title="' . __('Field has been created', 'wpshop') . '" class="db_added_field_check" />';
										}
										else{
											$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Field does not exist', 'wpshop') . '" title="' . __('Field does not exist', 'wpshop') . '" class="db_added_field_check" />';
										}
										$sub_modif .= ' / ';
									}
									$plugin_db_modification_content .= '<li class="added_field" >' . sprintf(__('Added field list for %s', 'wpshop'), $table_name) . '&nbsp;:&nbsp;' .  substr($sub_modif, 0, -2) . '</li>';
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
											}
										}
										$sub_modif .= ' / ';
									}
									$sub_modif = sprintf(__('Change %s for field %s to %s', 'wpshop'), $what_is_changed, $field_infos['field'], $field_infos[$changed_key]) . substr($sub_modif, 0, -2);
									$plugin_db_modification_content .= '<li class="changed_field" >' . sprintf(__('Updated field list for %s', 'wpshop'), $table_name) . '&nbsp;:&nbsp;' . $sub_modif . '</li>';
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
										$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Table has been created', 'wpshop') . '" title="' . __('Table has been created', 'wpshop') . '" class="db_table_check" />';
									}
									$sub_modif .= ' / ';
								}
								$plugin_db_modification_content .= '<li class="added_table" >' . __('Added table list', 'wpshop') . '&nbsp;:&nbsp;' . substr($sub_modif, 0, -2);
							}break;
						}
					}
					$plugin_db_modification_content .= '
</ul>
</div>';
				}
				echo $plugin_db_modification_content;
			}
			break;
		}
	}
	break;

	case 'products_by_criteria':

		// If a filter by attribute is found, recalcul the products that matching it
		if(!empty($_REQUEST['attr'])) {
			$att = explode(':',$_REQUEST['attr']);
			$products_id = wpshop_products::get_products_matching_attribute($att[0],$att[1]);
		}
		$products_id = !empty($products_id) ? $products_id : $_REQUEST['pid'];

		$data = wpshop_products::wpshop_get_product_by_criteria(
			$_REQUEST['criteria'], $_REQUEST['cid'], $products_id, $_REQUEST['display_type'], $_REQUEST['order'], $_REQUEST['page_number'], $_REQUEST['products_per_page']
		);
		if($data[0]) {
			echo json_encode(array(true,$data[1]));
		} else echo json_encode(array(false,__('No product found','wpshop')));
	break;

	case 'bill_order':
		if (!empty($_REQUEST['oid'])):
			$order_id = $_REQUEST['oid'];

			// Get the order from the db
			$order = get_post_meta($order_id, '_order_postmeta', true);
			$order_key = wpshop_orders::get_new_order_reference();
			$order['order_key'] = $order_key;
			update_post_meta($order_id, '_order_postmeta', $order);
			wpshop_orders::order_generate_billing_number($order_id, true);
			echo json_encode(array(true,''));
		endif;
	break;

	case 'duplicate_order':
		$new_order = wpshop_orders::duplicate_order($_REQUEST['pid']);
		echo json_encode(array(true,$new_order));
	break;

	case 'ajax_sendMessage':

		if (!empty($_REQUEST['postid']) && !empty($_REQUEST['title']) && !empty($_REQUEST['message']) && !empty($_REQUEST['recipient'])) {

			$user_info = get_userdata($_REQUEST['recipient']);
			$first_name = $user_info->user_firstname;
			$last_name = $user_info->user_lastname;
			$data=array('customer_first_name'=>$first_name,'customer_last_name'=>$last_name);

			$title = wpshop_tools::customMessage($_REQUEST['title'], $data);
			$message = wpshop_tools::customMessage($_REQUEST['message'], $data);

			if (!empty($user_info->user_email)) {
				wpshop_tools::wpshop_email($user_info->user_email, $title, $message, $save=true, $model_id=$_REQUEST['postid'], $object=array());
				$array = array('result' => true, 'message' => '');
			}
			else $array = array('result' => true, 'message' => __('An error occured','wpshop'));
		}
		else {
			$array = array('result' => false, 'message' => __('An error occured','wpshop'));
		}

		echo json_encode($array);

	break;

	case 'ajax_resendMessage':

		if (!empty($_REQUEST['messageid'])) {

			$ids = explode('-',$_REQUEST['messageid']);
			$postid = $ids[0];
			$date = $ids[1].'-'.$ids[2];
			$arraykey = $ids[3];

			$historic = get_post_meta($postid, 'wpshop_messages_histo_'.$date, true);

			if (isset($historic[$arraykey])) {
				$historic[$arraykey]['mess_dispatch_date'][] = current_time('mysql', 0);
				update_post_meta($postid, 'wpshop_messages_histo_'.$date, $historic);

				$data = $historic[$arraykey];

				wpshop_tools::wpshop_email($data['mess_user_email'], $data['mess_title'], $data['mess_message'], $save=false, $object=array());

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

	break;

	case 'ajax_addPrivateComment':
		$new_comment = wpshop_orders::add_private_comment($_REQUEST['oid'],$_REQUEST['comment'],$_REQUEST['send_email'],$_REQUEST['send_sms']);
		if($new_comment) {
			$content = '<hr /><b>'.__('Date','wpshop').':</b> '.mysql2date('d F Y, H:i:s',current_time('mysql',0), true).'<br /><b>'.__('Message','wpshop').':</b> '.nl2br($_REQUEST['comment']);
			echo json_encode(array(true, $content));
		}
		else echo json_encode(array(false, __('An error occured, impossible to record the comment','wpshop')));
	break;

	case 'related_products':
		$data = wpshop_products::product_list(false, $_REQUEST['search']);
		$array=array();
		foreach ($data as $d) {
			$array[] = array('id' => $d->ID, 'name' => $d->post_title);
		}
		echo json_encode($array);
	break;

	case 'ajax_cartAction':
		global $wpshop_cart;
		switch($_REQUEST['action'])
		{
			case 'setProductQty':

				if (!empty($_REQUEST['pid'])) {

					if (isset($_REQUEST['qty'])) {
						$return = $wpshop_cart->set_product_qty($_REQUEST['pid'],$_REQUEST['qty']);
						echo json_encode(array(true));
					}
					else {
						echo json_encode(array(false, __('Parameters error.','wpshop')));
					}

				}

				break;

			case 'applyCoupon':
				$result = wpshop_coupons::applyCoupon($_REQUEST['coupon_code']);
				if ($result['status']===true) {
					$order = wpshop_cart::calcul_cart_information(array());
					wpshop_cart::store_cart_in_session($order);
					echo json_encode(array(true, ''));
				} else echo json_encode(array(false, $result['message']));
			break;
		}
	break;

	case 'ajax_display_cart':
		global $wpshop_cart;
		$wpshop_cart->display_cart();
	break;

	case 'ajaxUpload':
		if(!is_dir(WPSHOP_UPLOAD_DIR)){
			mkdir(WPSHOP_UPLOAD_DIR, 0755, true);
		}

		$file = $_FILES['wpshop_file'];
		$tmp_name = $file['tmp_name'];
		$name = $file["name"];
		@move_uploaded_file($tmp_name, WPSHOP_UPLOAD_DIR."$name");

		$n = WPSHOP_UPLOAD_URL.'/'.$name;
		$s = $file['size'];
		if (!$n) continue;
		echo $n;

	break;

	case 'reload_mini_cart':
		echo wpshop_cart::mini_cart_content();
	break;
}
