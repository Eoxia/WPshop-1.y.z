<?php if ( !defined( 'ABSPATH' ) ) exit;
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
<div class="wps-boxed">
	<div class="wps-product-section">
	<button class="wpshop_product_duplication_button wps-bton-mini-rounded-second" id="wpshop_product_id_{WPSHOP_PRODUCT_ID}"><i class="dashicons dashicons-admin-page"></i><?php _e('Duplicate the product', 'wpshop'); ?></button>
	<div id="wpshop_loading_duplicate_pdt_{WPSHOP_PRODUCT_ID}"></div>
	</div>
	<div class="wps-product-section"><a href="{WPSHOP_PRINT_PRODUCT_SHEET_LINK}" target="_blank" role="button" class="wps-bton-mini-rounded-second"><i class="dashicons dashicons-format-aside"></i><?php _e('Print the product sheet', 'wpshop'); ?></a></div>
</div>
<?php
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

/*	Taxonomy wysiwyg page editor for js duplicate	*/
ob_start();
?>
<span style="display: none;" id="wpshop_transform_taxonomy_description_field_into_wysiwyg_for_js_duplicate">{WPSHOP_ADMIN_TAXONOMY_WYSIWYG}</span><?php
$tpl_element['wpshop_transform_taxonomy_description_field_into_wysiwyg_for_js_duplicate'] = ob_get_contents();
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
	<div class="wpshop_cls" >
		<input type="text" class="wpshop_att_set_section_edition_input wpshop_attribute_set_section_name" name="wpshop_attribute_set_section[{WPSHOP_ADMIN_GROUP_ID}][name]" id="wpshop_attribute_set_section_name_set_{WPSHOP_ADMIN_GROUP_ID}" value="{WPSHOP_ADMIN_GROUP_NAME}" />
		<label for="wpshop_attribute_set_section_name_set_{WPSHOP_ADMIN_GROUP_ID}" ><?php _e('Attribute set section name', 'wpshop'); ?></label>
	</div>
	<div class="wpshop_cls" >
		<select class="wpshop_att_set_section_edition_input wpshop_attribute_set_section_backend_type" name="wpshop_attribute_set_section[{WPSHOP_ADMIN_GROUP_ID}][backend_display_type]" id="wpshop_attribute_set_section_backend_display_type_set_{WPSHOP_ADMIN_GROUP_ID}" >
			<option value="fixed-tab"{WPSHOP_ADMIN_GROUP_DISPLAY_TYPE_TAB}><?php _e('Tab', 'wpshop'); ?></option>
			<option value="movable-tab"{WPSHOP_ADMIN_GROUP_DISPLAY_TYPE_BOX}><?php _e('Separated box', 'wpshop'); ?></option>
		</select>
		<label for="wpshop_attribute_set_section_backend_display_type_set_{WPSHOP_ADMIN_GROUP_ID}" ><?php _e('Display in admin as', 'wpshop'); ?></label>
	</div>
	<div class="wpshop_cls" >
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

/**
 *
 * Attribute display frontend per product
 *
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
 * Value item of attribute of list type
 *
 */
ob_start();
?>
	<li class="ui-state-default wpshop_cls wpshop_attribute_combo_options_container" id="att_option_div_container_{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_ID}" >
		<table class="wpshop_attr_combo_option_detail_table" >
			<tr class="wpshop_attr_combo_option_detail_table_line wpshop_attr_combo_option_detail_table_title" >
				<td rowspan="2" class="wpshop_attr_combo_option_default_td" ><input title="<?php _e('Default value', 'wpshop'); ?>" type="radio" id="default_value_{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_ID}" name="<?php echo WPSHOP_DBT_ATTRIBUTE; ?>[default_value][default_value]" value="{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_ID}"{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_STATE} /></td>
				<?php if( WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT ): ?>
				<td ><?php _e('Label', 'wpshop'); ?></td>
				<td ><?php _e('Value', 'wpshop'); ?></td>
				<?php else: ?>
				<td ><?php echo '&nbsp;'; ?></td>
				<?php endif; ?>
			</tr>
			<tr class="wpshop_attr_combo_option_detail_table_line wpshop_attr_combo_option_detail_table_values" >
				<td>
				<?php if( current_user_can('wpshop_edit_attributes_select_values') ): ?>
					<input type="text" value="{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_NAME}" name="optionsUpdate[{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_ID}]" id="attribute_option_{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_ID}" />
				<?php else: ?>
					{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_NAME}
				<?php endif; ?>
				</td>
				<?php if( WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT ): ?>
				<td>
					<?php if( current_user_can('wpshop_edit_attributes_select_values') ): ?>
						<input type="text" value="{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_VALUE}" name="optionsUpdateValue[{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_ID}]" id="attribute_option_value{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_ID}" />
					<?php else: ?>
						{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_VALUE}
					<?php endif; ?>
				</td>
				<?php endif; ?>
			</tr>
		</table>
		{WPSHOP_ADMIN_ATTRIBUTE_VALUE_OPTIN_ACTIONS}
	</li><?php
$tpl_element['wpshop_admin_attr_option_value_item'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Element allowing to delete an attribute value
 *
 */
ob_start();
?><div class="wpshop_admin_toolbox wpshop_attr_tool_box" >
	<a class="wpshop_attr_tool_box_button wpshop_attr_tool_box_delete wpshop_attr_combo_option_delete wpshop_attr_combo_option_delete_{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_ID}" id="wpshop_edit_{WPSHOP_ADMIN_ATTRIBUTE_VALUES_OPTION_ID}" title="<?php _e('Delete this value', 'wpshop'); ?>"></a>
</div><?php
$tpl_element['wpshop_admin_attr_option_value_item_deletion'] = ob_get_contents();
ob_end_clean();


/*	Product options for cart	*/
ob_start();
?><ul>
	<li><input{WPSHOP_ADMIN_PRODUCT_OPTION_FOR_CART_AUTOADD_CHECKBOX_STATE} type="checkbox" name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>[options][cart][auto_add]" value="yes" id="wpshop_auto_cart_product" /> <label for="wpshop_auto_cart_product" ><?php _e('Add this product automaticaly to cart', 'wpshop'); ?></label></li>
</ul><?php
$tpl_element['wpshop_admin_product_option_for_cart'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Variations
 *
 */
include_once('product_options_elements_template.tpl.php');

/**
 *
 * Orders
 *
 */
include_once('order_elements_template.tpl.php');

/**
 *
 * Options
 *
 */
include_once('options_elements.tpl.php');

/**
 *
 * Tools
 *
 */
include_once('tools_elements.tpl.php');


/**
 *
 *
 * Frontend sorting to preserve from changes
 *
 *
 */
/*	Sorting bloc hidden fields */
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
ob_start();
?>
<div class="sorting_bloc wpshopHide wps-catalog-sorting" >
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
/**
 *
 * Customer addresses form
 *
 */
ob_start();
?>
<form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" name="billingAndShippingForm" id="create_new_customer_in_admin">
	<input type="hidden" name="action" value="create_new_customer" />
	<?php wp_nonce_field( 'ajax_wpshop_create_new_customer' ); ?>
	<div class="col1 wpshopShow" id="register_form_classic">
		{WPSHOP_CUSTOMER_ADDRESSES_FORM_CONTENT}
		{WPSHOP_CUSTOMER_ADDRESSES_FORM_BUTTONS} <div class="loading_picture_container wpshopHide" id="create_new_customer_loader_creation"><img src="{WPSHOP_LOADING_ICON}" alt="loading..." /></div>
	</div>
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
	var form_options_create_new_customer = {
		dataType:'json',
		beforeSubmit : function(){
				jQuery("#create_new_customer_loader_creation").show();
				jQuery("#create_new_customer_in_admin_reponseBox").html('');
			},
		success: create_customer_in_admin_return
	};
	jQuery('#create_new_customer_in_admin').ajaxForm( form_options_create_new_customer );
	jQuery('input[name=shiptobilling]').click(function(){
		if (jQuery(this).attr('checked')=='checked') {
			jQuery('#create_new_customer_in_admin #shipping_infos_bloc').fadeOut(250);
		}
		else jQuery('#create_new_customer_in_admin #shipping_infos_bloc').fadeIn(250);
	});
});
</script>
<?php
$tpl_element['wpshop_customer_addresses_form_admin'] = ob_get_contents();
ob_end_clean();



/**
 *
 * Category edit interface
 *
 */
ob_start();
?>
<table class="form-table">
<tr class="form-field">
	<th scope="row" valign="top"><label for="wpshop_category_picture"><?php _e('Category\'s thumbnail', 'wpshop'); ?></label></th>
	<td>
		<div class="wpshop_cls" >
		<div class="alignleft wps_category_thumbnail_preview_container" >{WPSHOP_CATEGORY_THUMBNAIL_PREVIEW}</div>
		<div class="category_new_picture_upload" ><?php _e('If you want to change the current picture choose a new file', 'wpshop'); ?><br/>
		{WPSHOP_CATEGORY_DELETE_PICTURE_BUTTON}
		<a href="#" role="button" class="wps-bton-first-mini-rounded" id="add_picture_to_category"><?php _e( 'Add a picture to category', 'wpshop' ); ?></a>
		<input type="hidden" name="wps_category_picture_id" id="wps_category_picture_id" value="{WPSHOP_CATEGORY_PICTURE_ID}" />
		</div>
		</div>
		<div class="wpshop_cls description" ><?php _e('The thumbnail for the category', 'wpshop'); ?></div>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wpshop_category_picture"><?php _e('Integration code', 'wpshop'); ?></label></th>
	<td>
		<div class="wpshop_cls">
			<code>[wpshop_category cid={WPSHOP_CATEGORY_TAG_ID} type="list"]</code> <?php _e('or', 'wpshop'); ?> <code>[wpshop_category cid={WPSHOP_CATEGORY_TAG_ID} type="grid"]</code><br />
			<code>&lt;?php echo do_shortcode('[wpshop_category cid={WPSHOP_CATEGORY_TAG_ID} type="list"]'); ?></code> <?php _e('or', 'wpshop'); ?> <code>&lt;?php echo do_shortcode('[wpshop_category cid={WPSHOP_CATEGORY_TAG_ID} type="grid"]'); ?></code>
		</div>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><?php _e('Filterable attributes for this category', 'wpshop'); ?></th>
	<td class="filterable_attributes_container">
		<ul>{WPSHOP_CATEGORY_FILTERABLE_ATTRIBUTES}</ul>
	</td>
</tr>

</table>
<?php
$tpl_element['wpshop_category_edit_interface_admin'] = ob_get_contents();
ob_end_clean();



/**
 *
 * Category filterable attribute list element
 *
 */
ob_start();
?>
	<li class="wpshop_category_filterable_attribute_element"><input type="checkbox" name="filterable_attribute_for_category[{WPSHOP_CATEGORY_FILTERABLE_ATTRIBUTE_ID}]" value="{WPSHOP_CATEGORY_FILTERABLE_ATTRIBUTE_ID}" id="{WPSHOP_CATEGORY_FILTERABLE_ATTRIBUTE_ID}"  {WPSHOP_CATEGORY_FILTERABLE_ATTRIBUTE_CHECKED} /> <label for="{WPSHOP_CATEGORY_FILTERABLE_ATTRIBUTE_ID}">{WPSHOP_CATEGORY_FILTERABLE_ATTRIBUTE_NAME}</label></li>
<?php
$tpl_element['wpshop_category_filterable_attribute_element'] = ob_get_contents();
ob_end_clean();





ob_start();
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	{WPSHOP_PRODUCTS_JS_ARRAY}
	jQuery( "#wps_order_product_list" ).autocomplete({
		minLength: 0,
		source: products,
		focus: function( event, ui ) {
			jQuery( "#wps_order_product_list" ).val( ui.item.label );
			return false;
		},
		select: function( event, ui ) {
			jQuery( "#wps_order_product_list" ).val( ui.item.label );

			jQuery( "#wps_order_product_list" ).val( ui.item.value );
			return false;
		}
	})
	.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return jQuery( "<li>" )
		.append( "<a>" + item.label + "</a>" )
		.appendTo( ul );
	};
});
</script>
<?php
$tpl_element['wps_orders_products_list_js'] = ob_get_contents();
ob_end_clean();
