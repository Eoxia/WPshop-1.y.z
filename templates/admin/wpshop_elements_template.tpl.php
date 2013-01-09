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
/*	Taxonomy wysiwyg editor	*/
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

?>