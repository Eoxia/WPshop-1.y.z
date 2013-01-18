<?php
/*
 * Specific
 *
 *
 * General
 *
 */

$tpl_element = array();

/**
 *
 *
 * Admin button
 *
 *
 */
/*	"Duplicate product" button	*/
ob_start();
?>
<button class="wpshop_product_duplication_button" id="wpshop_product_id_{WPSHOP_PRODUCT_ID}" ><?php _e('Duplicate the product', 'wpshop'); ?></button><span id="wpshop_loading_duplicate_pdt_{WPSHOP_PRODUCT_ID}" class="wpshop_loading_picture" ></span><?php
$tpl_element['wpshop_duplicate_product'] = ob_get_contents();
ob_end_clean();


/**
 *
 *
 *
 *
 *
 *
 */
/*	Taxonomy wysiwyg quick editor	*/
ob_start();
?>
<div class="form-field" >
	<label for="tag_description" ><?php echo _x('Description', 'Taxonomy Description', 'wpshop'); ?></label>
	<div>
		{WPSHOP_ADMIN_TAXONOMY_WYSIWYG}
		<span class="description"><?php _e('The description is not prominent by default, however some themes may show it.', 'wpshop'); ?></span>
	</div>
</div><?php
$tpl_element['wpshop_transform_taxonomy_description_field_into_wysiwyg'] = ob_get_contents();
ob_end_clean();

/*	Taxonomy wysiwyg page editor	*/
ob_start();
?>
<tr class="form-field">
	<th valign="top" scope="row"><label for="tag_description" ><?php echo _x('Description', 'Taxonomy Description', 'wpshop'); ?></label></th>
	<td>
		{WPSHOP_ADMIN_TAXONOMY_WYSIWYG}
		<span class="description"><?php _e('The description is not prominent by default, however some themes may show it.', 'wpshop'); ?></span>
	</td>
</tr><?php
$tpl_element['wpshop_transform_taxonomy_description_field_into_wysiwyg_for_full_page'] = ob_get_contents();
ob_end_clean();

/*	Taxonomy wysiwyg editor	*/
ob_start();
?>
<style type="text/css" >.wp-editor-container .quicktags-toolbar input.ed_button { width: auto; } .html-active .wp-editor-area { border: 0px solid #000000; }</style><?php
$tpl_element['wpshop_taxonomy_wysiwyg_editor_css'] = ob_get_contents();
ob_end_clean();






/**
 *
 *
 *
 *	Attribute management
 *
 *
 */
/*	Edition form for attribute set section
 *
 * {WPSHOP_ADMIN_GROUP_IDENTIFIER}
 * {WPSHOP_ADMIN_GROUP_ID}
 * {WPSHOP_ADMIN_GROUP_NAME}
 * {WPSHOP_ADMIN_GROUP_DISPLAY_TYPE_TAB}
 * {WPSHOP_ADMIN_GROUP_DISPLAY_TYPE_BOX}
 * {WPSHOP_ADMIN_GROUP_DISPLAY_ON_FRONTEND}
 */
ob_start();
?><div class="ui-state-disabled wpshop_att_set_section_edition_container wpshop_att_set_section_edition_container_{WPSHOP_ADMIN_GROUP_IDENTIFIER}" id="wpshop_att_set_section_edition_container_{WPSHOP_ADMIN_GROUP_ID}" >
	<div class="clear" >
		<input type="text" class="wpshop_att_set_section_edition_input wpshop_attribute_set_section_name" name="wpshop_attribute_set_section[{WPSHOP_ADMIN_GROUP_ID}][name]" id="wpshop_attribute_set_section_name_set_{WPSHOP_ADMIN_GROUP_ID}" value="{WPSHOP_ADMIN_GROUP_NAME}" />
		<label for="wpshop_attribute_set_section_name_set_{WPSHOP_ADMIN_GROUP_ID}" ><?php _e('Attribute set section name', 'wpshop'); ?></label>
	</div>
	<div class="clear" >
		<select class="wpshop_att_set_section_edition_input wpshop_attribute_set_section_backend_type" name="wpshop_attribute_set_section[{WPSHOP_ADMIN_GROUP_ID}][backend_display_type]" id="wpshop_attribute_set_section_backend_display_type_set_{WPSHOP_ADMIN_GROUP_ID}" >
			<option value="fixed-tab"{WPSHOP_ADMIN_GROUP_DISPLAY_TYPE_TAB}><?php _e('Tab', 'wpshop'); ?></option>
			<option value="movable-tab"{WPSHOP_ADMIN_GROUP_DISPLAY_TYPE_BOX}><?php _e('Separated box', 'wpshop'); ?></option>
		</select>
		<label for="wpshop_attribute_set_section_backend_display_type_set_{WPSHOP_ADMIN_GROUP_ID}" ><?php _e('Display in admin as', 'wpshop'); ?></label>
	</div>
	<div class="clear" >
		<input type="checkbox" class="wpshop_att_set_section_edition_input wpshop_attribute_set_section_display_on_frontend" name="wpshop_attribute_set_section[{WPSHOP_ADMIN_GROUP_ID}][display_on_frontend]" id="wpshop_attribute_set_section_display_on_frontend_set_{WPSHOP_ADMIN_GROUP_ID}" value="yes"{WPSHOP_ADMIN_GROUP_DISPLAY_ON_FRONTEND} />
		<label for="wpshop_attribute_set_section_display_on_frontend_set_{WPSHOP_ADMIN_GROUP_ID}" ><?php _e('Display on frontend', 'wpshop'); ?></label>
	</div>
</div><?php
$tpl_element['wpshop_admin_attr_set_section_params'] = ob_get_contents();
ob_end_clean();


/*	Attribute display main config choice
 *
 * {WPSHOP_ADMIN_ATTRIBUTE_FD_NAME}
 * {WPSHOP_ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT_CLASS}
 * {WPSHOP_ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT}
 * {WPSHOP_ADMIN_PRODUCT_ATTRIBUTE_FRONTEND_DISPLAY_MAIN_CHOICE_CHECK}
 */
ob_start();
?>
<input type="checkbox" name="{WPSHOP_ADMIN_ATTRIBUTE_FD_NAME}[default_config]" id="wpshop_product_attribute_display_choice" value="yes"{WPSHOP_ADMIN_PRODUCT_ATTRIBUTE_FRONTEND_DISPLAY_MAIN_CHOICE_CHECK} /><label for="wpshop_product_attribute_display_choice" ><?php _e('Use default configuration', 'wpshop'); ?></label>
<div{WPSHOP_ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT_CLASS} id="wpshop_product_attribute_frontend_display_container">
	{WPSHOP_ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT}
</div><?php
$tpl_element['wpshop_admin_attr_set_section_for_front_display_default_choice'] = ob_get_contents();
ob_end_clean();


/*	Attribute display frontend per product
 *
 * {WPSHOP_ADMIN_ATTRIBUTE_SET_SECTION_FD_NAME}
 * {WPSHOP_ADMIN_ATTRIBUTE_SET_SECTION_FD_ID}
 * {WPSHOP_ADMIN_ATTRIBUTE_SET_SECTION_COMPLETE_SHEET_CHECK}
 * {WPSHOP_ADMIN_ATTRIBUTE_SET_SECTION_NAME}
 * {WPSHOP_ADMIN_ATTRIBUTE_SET_SECTION_CONTENT}
 */
ob_start();
?>
<div class="wpshop_admin_set_section_name_front_display" >
	<h2>{WPSHOP_ADMIN_ATTRIBUTE_SET_SECTION_NAME}</h2>{WPSHOP_ADMIN_ATTRIBUTE_SET_SECTION_INPUT_CHECKBOX}
	<ul>{WPSHOP_ADMIN_ATTRIBUTE_SET_SECTION_CONTENT}</ul>
</div><?php
$tpl_element['wpshop_admin_attr_set_section_for_front_display'] = ob_get_contents();
ob_end_clean();

/*	Attribute display frontend per product
 *
 * {WPSHOP_ADMIN_ATTRIBUTE_LABEL}
 * {WPSHOP_ADMIN_ATTRIBUTE_FD_NAME}
 * {WPSHOP_ADMIN_ATTRIBUTE_FD_ID}
 * {WPSHOP_ADMIN_ATTRIBUTE_COMPLETE_SHEET_CHECK}
 * {WPSHOP_ADMIN_ATTRIBUTE_MINI_OUTPUT_CHECK}
 */
ob_start();
?>
<li>
	<strong>{WPSHOP_ADMIN_ATTRIBUTE_LABEL}</strong>
	<ul>
		<li><input type="checkbox" name="{WPSHOP_ADMIN_ATTRIBUTE_FD_NAME}[complete_sheet]" id="{WPSHOP_ADMIN_ATTRIBUTE_FD_ID}_complete_sheet" value="yes"{WPSHOP_ADMIN_ATTRIBUTE_COMPLETE_SHEET_CHECK} /><label for="{WPSHOP_ADMIN_ATTRIBUTE_FD_ID}_complete_sheet" ><?php _e('Display in product page', 'wpshop'); ?></label></li>
		<li><input type="checkbox" name="{WPSHOP_ADMIN_ATTRIBUTE_FD_NAME}[mini_output]" id="{WPSHOP_ADMIN_ATTRIBUTE_FD_ID}_mini_output" value="yes"{WPSHOP_ADMIN_ATTRIBUTE_MINI_OUTPUT_CHECK} /><label for="{WPSHOP_ADMIN_ATTRIBUTE_FD_ID}_mini_output" ><?php _e('Display in product listing', 'wpshop'); ?></label></li>
	</ul>
</li><?php
$tpl_element['wpshop_admin_attr_config_for_front_display'] = ob_get_contents();
ob_end_clean();



/**
 *
 * Variations
 *
 */
/*	Variation box	*/
ob_start();
?><div class="wpshop_variation_main_metabox" >
	<input type="hidden" name="wpshop_variation_management" id="wpshop_variation_management" value="<?php echo wp_create_nonce("wpshop_variation_management"); ?>" />
	<div >
		<button class="button-primary wpshop_variation_list_creation_button" type="button" id="wpshop_new_variation_list_button" /><?php _e('Create all combined variation','wpshop'); ?></button>
		<button class="button-secondary wpshop_variation_single_creation_button" type="button" id="wpshop_new_variation_single_button" /><?php _e('Create single variation','wpshop'); ?></button>
		<button class="button-secondary wpshop_variation_parameters alignright" type="button" id="wpshop_variation_parameters_button" /><i class="parameter-icon"></i><?php _e('Parameters','wpshop'); ?></button>
	</div>
	<div class="clear wpshop_separator" ></div>
	<div class="wpshop_variations" >{WPSHOP_ADMIN_VARIATION_CONTAINER}</div>
</div>
<div class="wpshop_add_box wpshop_admin_variation_single_dialog" title="<?php _e('Select values for variation creation', 'wpshop'); ?>" ></div>
<div class="wpshop_add_box wpshop_admin_variation_combined_dialog" title="<?php _e('Choose what attribute to use for variation list creation', 'wpshop'); ?>" ></div>
<div class="wpshop_add_box wpshop_admin_variation_parameter_dialog" title="<?php _e('Parameters for product options', 'wpshop'); ?>" ></div><?php
$tpl_element['wpshop_admin_variation_metabox'] = ob_get_contents();
ob_end_clean();


/*	Attribute output for single variation creation	*/
ob_start();
?><form action="<?php echo admin_url('admin-ajax.php'); ?>" method="POST" id="wpshop_admin_variation_definition" >
	<input type="hidden" name="wpshop_head_product_id" id="wpshop_head_product_id" value="{WPSHOP_ADMIN_VARIATION_CREATION_FORM_HEAD_PRODUCT_ID}" />
	<input type="hidden" name="wpshop_ajax_nonce" id="wpshop_ajax_nonce" value="{WPSHOP_ADMIN_VARIATION_CREATION_FORM_HEAD_NOUNCE}" />
	<input type="hidden" name="action" id="add_variation_creation_type" value="{WPSHOP_ADMIN_VARIATION_CREATION_FORM_ACTION}" />
	{WPSHOP_ADMIN_VARIATION_SINGLE_CREATION_FORM_CONTENT}
</form>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#wpshop_admin_variation_definition").ajaxForm({
	        beforeSubmit: function(a,f,o) {
	        	animate_container('.wpshop_admin_variation_single_dialog', jQuery(".wpshop_admin_variation_single_dialog"));
	        },
	        success: function(data) {
	        	jQuery(".variation_attribute_usable").each(function() {
		        	jQuery(this).prop("checked", false);
	        	});
	        	jQuery(".variation_attribute_usable_input").each(function() {
		        	jQuery(this).val("");
	        	});
	        	jQuery(".new_variation_specific_values").each(function() {
		        	jQuery(this).val("");
	        	});
	        	jQuery(".wpshop_attribute_input_for_variation_container").each(function() {
		        	jQuery(this).hide();
	        	});
	        	desanimate_container(jQuery(".wpshop_admin_variation_single_dialog"));
	        	jQuery(".wpshop_variations").html(data);
	        },
		});
	});
</script><?php
$tpl_element['wpshop_admin_new_single_variation_form'] = ob_get_contents();
ob_end_clean();


/*	Attribute output for single variation creation	*/
ob_start();
?><li class="clear wpshop_admin_variation_available_attribute_main_container wpshop_admin_variation_available_attribute_main_container_{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}" >
	<div class="wpshop_admin_use_attribute_for_single_variation_checkbox_container alignleft" >
		<input{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CHECKBOX_STATE} type="checkbox" name="wpshop_admin_use_attribute_for_single_variation_checkbox[{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}]" id="wpshop_admin_use_attribute_for_single_variation_checkbox_{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}" class="variation_attribute_usable wpshop_admin_use_attribute_for_single_variation_checkbox" value="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_NAME}" />
	</div>
	<div class="wpshop_variation_attribute_container{WPSHOP_ADMIN_VARIATION_ATTRIBUTE_CONTAINER_CLASS}" >
		<label{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_LABEL_STATE} for="wpshop_admin_use_attribute_for_single_variation_checkbox_{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}" >{WPSHOP_ADMIN_VARIATION_NEW_SINGLE_LABEL}</label>{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL_EXPLAINATION}
		<div class="wpshopHide wpshop_attribute_input_for_variation_container wpshop_attribute_input_for_variation_{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}" >{WPSHOP_ADMIN_VARIATION_NEW_SINGLE_INPUT}</div>
		</div><div class="cls">
	</div>
</li><?php
$tpl_element['wpshop_admin_variation_attribute_line'] = ob_get_contents();
ob_end_clean();

/*	Available attribute list container	*/
ob_start();
?><ul class="variation_main_container{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_CONTAINER_CLASS}" >{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_CONTAINER}</ul><?php
$tpl_element['wpshop_admin_attribute_for_variation_list'] = ob_get_contents();
ob_end_clean();

/*	UnAvailable attribute list container	*/
ob_start();
?><ul class="wpshop_variation_unusable" ><?php _e('Atributes below are not set for current product and can\'t be used for variation', 'wpshop'); ?>{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_CONTAINER}</ul><?php
$tpl_element['wpshop_admin_unvailable_attribute_for_variation_list'] = ob_get_contents();
ob_end_clean();


/*	Existing variation list	*/
ob_start();
?><div class="variation_existing_main_container{WPSHOP_ADMIN_EXISTING_VARIATIONS_CONTAINER_CLASS}" >{WPSHOP_ADMIN_EXISTING_VARIATIONS_CONTAINER}</div><?php
$tpl_element['wpshop_admin_existing_variation_list'] = ob_get_contents();
ob_end_clean();

/*	Existing variations controller bar	*/
ob_start();
?><div class="clear wpshop_variation_controller" >
	<div class="wpshop_variation_metabox_col_input">
		<input type="checkbox" id="wpshop_variation_list_selection_controller" name="wpshop_variation_list_selection_controller" class="wpshop_variation_list_selection_controller" />
	</div>
	<div class="wpshop_variation_metabox_col_close">
		<a href="#" class="ui-dialog-titlebar-close ui-corner-all wpshop_admin_variation_mass_delete_button" id="wpshop_admin_variation_mass_delete_button">
			<span class="ui-icon ui-icon-closethick"></span>
		</a>
	</div>
	<!-- <button class=" button-secondary" type="button" id="wpshop_admin_variation_mass_delete_button" ><?php _e('Delete all selected variations', 'wpshop'); ?></button> -->
	<span><?php _e('Product options definitions', 'wpshop'); ?></span>
	<div class="clear" ></div>
</div><?php
$tpl_element['wpshop_admin_existing_variation_controller'] = ob_get_contents();
ob_end_clean();

/*	Variation item header definition	*/
ob_start();
?><span class="wpshop_variation_metabox_data">{WPSHOP_VARIATION_ATTRIBUTE_CODE}<span class="wpshop_variation_metabox_value">{WPSHOP_VARIATION_ATTRIBUTE_CODE_VALUE}</span></span><?php
$tpl_element['wpshop_admin_variation_item_def_header'] = ob_get_contents();
ob_end_clean();


/*	Existing variation	*/
ob_start();
?>
<div class="wpshop_variation_metabox{WPSHOP_ADMIN_EXISTING_VARIATIONS_CLASS}" id="wpshop_variation_metabox_{WPSHOP_VARIATION_IDENTIFIER}" >
	<div class="wpshop_variation_metabox_row">
		<div class="wpshop_variation_metabox_col_input">
			<input type="checkbox" class="wpshop_variation_mass_select_input" name="wpshop_variation_mass_delete[{WPSHOP_VARIATION_IDENTIFIER}]" id="wpshop_variation_mass_delete_{WPSHOP_VARIATION_IDENTIFIER}" value="{WPSHOP_VARIATION_IDENTIFIER}" />
		</div>
		<!-- <div class="wpshop_variation_metabox_col_id">
			<span class="wpshop_variation_id">{WPSHOP_VARIATION_IDENTIFIER}</span>
		</div> -->
		<div class="wpshop_variation_metabox_col_entry">
			<span class="wpshop_variation_entry">{WPSHOP_VARIATION_DETAIL}</span>
		</div>
		<div class="wpshop_variation_metabox_col_close">
			<a href="#" class="ui-dialog-titlebar-close ui-corner-all wpshop_variation_button_delete" id="wpshop_variation_delete_{WPSHOP_VARIATION_IDENTIFIER}" >
				<span class="ui-icon ui-icon-closethick">close</span>
			</a>
		</div>
		<div class="cls"></div>
	</div>
	{WPSHOP_VARIATION_DEFINITION_CONTENT}
</div><?php
$tpl_element['wpshop_admin_variation_item_def'] = ob_get_contents();
ob_end_clean();


/*	Variation item specific definition	*/
ob_start();
?><div class="wpshop_variation_def_details{WPSHOP_ADMIN_VARIATION_SPECIFIC_DEFINITION_CONTAINER_CLASS}" id="wpshop_variation_def_details_{WPSHOP_VARIATION_IDENTIFIER}" >{WPSHOP_VARIATION_DEFINITION}<div class="clear"></div></div><?php
$tpl_element['wpshop_admin_variation_item_specific_def'] = ob_get_contents();
ob_end_clean();

/*	Variation details	*/
ob_start();
?>{WPSHOP_ADMIN_VARIATION_DETAIL}<?php
$tpl_element['wpshop_admin_variation_item_details'] = ob_get_contents();
ob_end_clean();

/*	Variation details line	*/
ob_start();
?><div class="wpshop_variation_special_value_container{WPSHOP_ADMIN_VARIATION_DETAIL_DEF_CODE}" >
	<label class="wpshop_variation_special_value_label" for="{WPSHOP_ADMIN_VARIATION_DETAIL_DEF_ID}" >{WPSHOP_ADMIN_VARIATION_DETAIL_DEF_LABEL}</label>{WPSHOP_ADMIN_VARIATION_DETAIL_DEF_INPUT}
</div><?php
$tpl_element['wpshop_admin_variation_item_details_line'] = ob_get_contents();
ob_end_clean();


/*	Variation options	*/
ob_start();
?><form action="<?php echo admin_url('admin-ajax.php'); ?>" method="POST" id="wpshop_variation_parameter_form" >
	<input type="hidden" name="current_post_id" id="current_post_id" value="{WPSHOP_ADMIN_VARIATION_PARAMETERS_FORM_HEAD_PRODUCT_ID}" />
	<input type="hidden" name="wpshop_ajax_nonce" id="wpshop_ajax_nonce" value="{WPSHOP_ADMIN_VARIATION_PARAMETERS_FORM_HEAD_NOUNCE}" />
	<input type="hidden" name="action" id="admin_variation_parameters_save" value="admin_variation_parameters_save" />
	<ul class="wpshop_product_variation_options" >
		<li class="wpshop_product_variation_option wpshop_product_variation_priority" >
			<h4><?php _e('In case you create combined variation AND single variation, what is the price to take into cart', 'wpshop') ?></h4>
			<ul class="wpshop_product_variation_priority_choices" >
				<li class="wpshop_product_variation_priority_choices_item wpshop_product_variation_priority_choices_item_single" ><input type="radio"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_PRIORITY_SINGLE} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][priority][]" id="wpshop_product_variation_priority_single" value="single"/><label for="wpshop_product_variation_priority_single" ><?php _e('Sum of single variation', 'wpshop'); ?></label><div class="cls"></div></li>
				<li class="wpshop_product_variation_priority_choices_item wpshop_product_variation_priority_choices_item_combined" ><input type="radio"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_PRIORITY_COMBINED} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][priority][]" id="wpshop_product_variation_priority_combined" value="combined"/><label for="wpshop_product_variation_priority_combined" ><?php _e('Combined variations (recommanded if you want to manage stock)', 'wpshop'); ?></label><div class="cls"></div></li>
			</ul>
		</li>
		<li class="wpshop_product_variation_option wpshop_product_variation_option_price_behaviour" >
			<h4><?php _e('Choose the behaviour for the price of your product variation', 'wpshop') ?></h4>
			<ul class="wpshop_product_variation_option_price_behaviour_choices" >
				<li class="wpshop_product_variation_option_price_behaviour_choices_item wpshop_product_variation_option_price_behaviour_choices_item_addition" ><input type="radio"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_BEHAVIOUR_ADDITION} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][price_behaviour][]" id="wpshop_product_variation_price_behaviour_addition" value="addition"/><label for="wpshop_product_variation_price_behaviour_addition" ><?php _e('Add the variation prices to the product price', 'wpshop'); ?></label><div class="cls"></div></li>
				<li class="wpshop_product_variation_option_price_behaviour_choices_item wpshop_product_variation_option_price_behaviour_choices_item_replacement" ><input type="radio"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_BEHAVIOUR_REPLACEMENT} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][price_behaviour][]" id="wpshop_product_variation_price_behaviour_replacement" value="replacement"/><label for="wpshop_product_variation_price_behaviour_replacement" ><?php _e('Replace the product price with variation prices', 'wpshop'); ?></label><div class="cls"></div></li>
			</ul>
		</li>
		<li class="wpshop_product_variation_option wpshop_product_variation_option_price_display" >
			<h4><?php _e('Choose variations prices display', 'wpshop') ?></h4>
			<ul class="wpshop_product_variation_option_price_display_choices" >
				<li class="wpshop_product_variation_option_price_display_item wpshop_product_variation_option_price_display_item_text_from" ><input type="checkbox"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_PRICE_DISPLAY_TEXT_FROM} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][price_display][text_from]" id="wpshop_product_variation_price_display_from_text" /> <label for="wpshop_product_variation_price_display_from_text" class="alignright" ><?php _e('Display "price from" before basic price of product', 'wpshop'); ?></label><div class="cls"></div></li>
				<li class="wpshop_product_variation_option_price_display_item wpshop_product_variation_option_price_display_item_lower_price" ><input type="checkbox"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_PRICE_DISPLAY_LOWER_PRICE} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][price_display][lower_price]" id="wpshop_product_variation_price_display_lower_price" /> <label for="wpshop_product_variation_price_display_lower_price" class="alignright" ><?php _e('Display the lowest price of variation', 'wpshop'); ?></label><div class="cls"></div></li>
			</ul>
		</li>
		{WPSHOP_ADMIN_MORE_OPTIONS_FOR_VARIATIONS}
	</ul>
</form>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#wpshop_variation_parameter_form").ajaxForm({
	        beforeSubmit: function(a,f,o) {
	        	animate_container('.wpshop_admin_variation_single_dialog', jQuery("#wpshop_variation_parameter_form"));
	        },
	        success: function(data) {
	        	desanimate_container(jQuery("#wpshop_variation_parameter_form"));
	        },
		});
	});
</script><?php
$tpl_element['wpshop_admin_variation_options_container'] = ob_get_contents();
ob_end_clean();

ob_start();
?><li class="wpshop_product_variation_option wpshop_product_variation_option_required_attribute" >
	<h4><?php _e('Choose required attribute', 'wpshop'); ?></h4>
	<ul class="wpshop_product_variation_option_required_attribute_choices" >
		{WPSHOP_ADMIN_VARIATION_OPTIONS_REQUIRED_ATTRIBUTE}
	</ul>
</li><?php
$tpl_element['wpshop_admin_variation_options_required_attribute_container'] = ob_get_contents();
ob_end_clean();

/*	Available attribute item	*/
ob_start();
?><li class="variation_attribute_container{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CONTAINER_CLASS}" ><input{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CHECKBOX_STATE} type="checkbox" class="variation_attribute_usable" name="wpshop_attribute_to_use_for_variation[{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_CODE}]" value="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_NAME}" id="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_ID}" /> <label{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_LABEL_STATE} for="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_ID}" >{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL}</label>{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL_EXPLAINATION}<div class="cls"></div></li><?php
$tpl_element['wpshop_admin_attribute_for_variation_item'] = ob_get_contents();
ob_end_clean();

ob_start();
?><li class="clear wpshop_product_variation_option wpshop_product_variation_option_attribute_default_value" >
	<h4><?php _e('Choose default value for attribute in current product', 'wpshop'); ?></h4>
	<ul class="wpshop_product_variation_option_attribute_default_value_choices" >
		{WPSHOP_ADMIN_VARIATION_OPTIONS_ATTRIBUTE_DEFAULT_VALUE}
	</ul>
</li><?php
$tpl_element['wpshop_admin_variation_options_default_value_container'] = ob_get_contents();
ob_end_clean();

/*	Available attribute item for default value choosen	*/
ob_start();
?><li class="variation_attribute_container_default_value{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CONTAINER_CLASS}" ><label for="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_ID}" >{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_DEFAULT_VALUE_LABEL}</label> {WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_DEFAULT_VALUE_INPUT}<div class="cls"></div></li><?php
$tpl_element['wpshop_admin_attribute_for_variation_item_for_default'] = ob_get_contents();
ob_end_clean();



/**
 *
 *
 * Frontend sorting to preserve from changes
 *
 *
 */
/*	Sorting bloc hidden fields */
/*
 * {WPSHOP_DISPLAY_TYPE}
 * {WPSHOP_ORDER}
 * {WPSHOP_PRODUCT_NUMBER}
 * {WPSHOP_CURRENT_PAGE}
 * {WPSHOP_CATEGORY_ID}
 * {WPSHOP_PRODUCT_ID}
 * {WPSHOP_ATTR}
 */
ob_start();
?>
	<input type="hidden" name="display_type" value="{WPSHOP_DISPLAY_TYPE}" class="hidden_sorting_fields" />
	<input type="hidden" name="order" value="{WPSHOP_ORDER}" class="hidden_sorting_fields" />
	<input type="hidden" name="products_per_page" value="{WPSHOP_PRODUCT_NUMBER}" class="hidden_sorting_fields" />
	<input type="hidden" name="page_number" value="{WPSHOP_CURRENT_PAGE}" />
	<input type="hidden" name="cid" value="{WPSHOP_CATEGORY_ID}" class="hidden_sorting_fields" />
	<input type="hidden" name="pid" value="{WPSHOP_PRODUCT_ID}" class="hidden_sorting_fields" />
	<input type="hidden" name="attr" value="{WPSHOP_ATTR}" class="hidden_sorting_fields" /><?php
$tpl_element['product_listing_sorting_hidden_field'] = ob_get_contents();
ob_end_clean();


/*	Sorting bloc */
/*
 * {WPSHOP_SORTING_HIDDEN_FIELDS}
 * {WPSHOP_SORTING_CRITERIA}
 */
ob_start();
?>
<div class="hidden_sorting_bloc" >
	{WPSHOP_SORTING_HIDDEN_FIELDS}{WPSHOP_SORTING_CRITERIA}
</div><?php
$tpl_element['product_listing_sorting_hidden'] = ob_get_contents();
ob_end_clean();


/*	Sorting bloc hidden fields */
ob_start();
?>
	<input type="hidden" name="sorting_criteria" value="{WPSHOP_CRITERIA_DEFAULT}" class="hidden_sorting_fields" /><?php
$tpl_element['product_listing_sorting_criteria_hidden'] = ob_get_contents();
ob_end_clean();



/*	Selection for chosen select element */
ob_start();
?>
<button class="wpshop_icons_add_new_value_to_option_list wpshop_icons_add_new_value_to_option_list_{WPSHOP_CURRENT_ATTRIBUTE_CODE} button button-small" id="new_value_pict_{WPSHOP_CURRENT_ATTRIBUTE_CODE}" ><?php _e('Add a new value', 'wpshop'); ?></button>
<button id="wpshop_list_chosen_select_all_{WPSHOP_CURRENT_ATTRIBUTE_ID}" class="wpshop_list_chosen_select_all button button-small" ><?php _e('Select all', 'wpshop'); ?></button>
<button id="wpshop_list_chosen_deselect_all_{WPSHOP_CURRENT_ATTRIBUTE_ID}" class="wpshop_list_chosen_deselect_all button button-small" ><?php _e('Deselect all', 'wpshop'); ?></button><?php
$tpl_element['select_list_multiple_bulk_action'] = ob_get_contents();
ob_end_clean();

?>