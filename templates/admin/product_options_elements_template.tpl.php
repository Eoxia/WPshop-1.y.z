<?php if (!defined('ABSPATH')) {
    exit;
}

/*    Variation box    */
ob_start();
?><div class="wpshop_variation_main_metabox" >
	<input type="hidden" name="wpshop_variation_management" id="wpshop_variation_management" value="<?php echo wp_create_nonce("wpshop_variation_management"); ?>" />
	<div >
		<button class="button-primary wpshop_variation_list_creation_button" type="button" id="wpshop_new_variation_list_button" ><?php _e('Create all combined variation', 'wpshop');?></button>
		<button class="button-secondary wpshop_variation_single_creation_button" type="button" id="wpshop_new_variation_single_button" ><?php _e('Create single variation', 'wpshop');?></button>
		<button class="button-secondary wpshop_variation_parameters alignright" type="button" id="wpshop_variation_parameters_button" ><i class="parameter-icon"></i><?php _e('Parameters', 'wpshop');?></button>
		<a class="button-secondary wpshop_variation_parameters alignright" type="button" href="{WPSHOP_LINK_NEW_INTERFACE}" ><span class="dashicons dashicons-lightbulb"></span><?php _e('New interface', 'wpshop');?></a>&nbsp;
	</div>
	<div class="wpshop_cls wpshop_separator" ></div>
	<div class="wpshop_variations" >{WPSHOP_ADMIN_VARIATION_CONTAINER}</div>
</div>
<div class="wpshop_add_box wpshop_admin_variation_single_dialog" title="<?php _e('Select values for variation creation', 'wpshop');?>" ></div>
<div class="wpshop_add_box wpshop_admin_variation_combined_dialog" title="<?php _e('Choose what attribute to use for variation list creation', 'wpshop');?>" ></div>
<div class="wpshop_add_box wpshop_admin_variation_parameter_dialog" title="<?php _e('Parameters for product options', 'wpshop');?>" ></div><?php
$tpl_element['wpshop_admin_variation_metabox'] = ob_get_contents();
ob_end_clean();

/*    Attribute output for single variation creation    */
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

/*    Attribute output for single variation creation    */
ob_start();
?><li class="wpshop_cls wpshop_admin_variation_available_attribute_main_container wpshop_admin_variation_available_attribute_main_container_{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}" >
	<div class="wpshop_admin_use_attribute_for_single_variation_checkbox_container alignleft" >
		<input{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CHECKBOX_STATE} type="checkbox" name="wpshop_admin_use_attribute_for_single_variation_checkbox[{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}]" id="wpshop_admin_use_attribute_for_single_variation_checkbox_{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}" class="variation_attribute_usable wpshop_admin_use_attribute_for_single_variation_checkbox" value="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_NAME}" />
	</div>
	<div class="wpshop_variation_attribute_container{WPSHOP_ADMIN_VARIATION_ATTRIBUTE_CONTAINER_CLASS}" >
		<label{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_LABEL_STATE} for="wpshop_admin_use_attribute_for_single_variation_checkbox_{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}" >{WPSHOP_ADMIN_VARIATION_NEW_SINGLE_LABEL}</label>{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL_EXPLAINATION}
		<div class="wpshopHide wpshop_attribute_input_for_variation_container wpshop_attribute_input_for_variation_{WPSHOP_ADMIN_ATTRIBUTE_CODE_FOR_VARIATION}" >{WPSHOP_ADMIN_VARIATION_NEW_SINGLE_INPUT}</div>
		</div><div class="wpshop_cls">
	</div>
</li><?php
$tpl_element['wpshop_admin_variation_attribute_line'] = ob_get_contents();
ob_end_clean();

/*    Available attribute list container    */
ob_start();
?><ul class="variation_main_container{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_CONTAINER_CLASS}" >{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_CONTAINER}</ul><?php
$tpl_element['wpshop_admin_attribute_for_variation_list'] = ob_get_contents();
ob_end_clean();

/*    UnAvailable attribute list container    */
ob_start();
?><ul class="wpshop_variation_unusable" ><?php _e('Atributes below are not set for current product and can\'t be used for variation', 'wpshop');?>{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_CONTAINER}</ul><?php
$tpl_element['wpshop_admin_unvailable_attribute_for_variation_list'] = ob_get_contents();
ob_end_clean();

/*    Existing variation list    */
ob_start();
?><div class="variation_existing_main_container{WPSHOP_ADMIN_EXISTING_VARIATIONS_CONTAINER_CLASS}" >{WPSHOP_ADMIN_EXISTING_VARIATIONS_CONTAINER}</div><?php
$tpl_element['wpshop_admin_existing_variation_list'] = ob_get_contents();
ob_end_clean();

/*    Existing variations controller bar    */
ob_start();
?><div class="wpshop_cls wpshop_variation_controller" >
	<div class="wpshop_variation_metabox_col_input">
		<input type="checkbox" id="wpshop_variation_list_selection_controller" name="wpshop_variation_list_selection_controller" class="wpshop_variation_list_selection_controller" />
	</div>
	<div class="wpshop_variation_metabox_col_close">
		<a href="#" class="ui-dialog-titlebar-close ui-corner-all wpshop_admin_variation_mass_delete_button" id="wpshop_admin_variation_mass_delete_button">
			<span class="ui-icon ui-icon-closethick"></span>
		</a>
	</div>
	<!-- <button class=" button-secondary" type="button" id="wpshop_admin_variation_mass_delete_button" ><?php _e('Delete all selected variations', 'wpshop');?></button> -->
	<span><?php _e('Product options definitions', 'wpshop');?></span>
	<div class="wpshop_cls" ></div>
</div><?php
$tpl_element['wpshop_admin_existing_variation_controller'] = ob_get_contents();
ob_end_clean();

/*    Variation item header definition    */
ob_start();
?><span class="wpshop_variation_metabox_data">{WPSHOP_VARIATION_ATTRIBUTE_CODE}<span class="wpshop_variation_metabox_value">{WPSHOP_VARIATION_ATTRIBUTE_CODE_VALUE}</span></span>  <?php
$tpl_element['wpshop_admin_variation_item_def_header'] = ob_get_contents();
ob_end_clean();

/*    Existing variation    */
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
			<div class="wpshop_variation_entry alignleft">{WPSHOP_VARIATION_DETAIL}</div>
			<div class="alignright" >
				<span class="variation_price_resume"><strong><?php _e('Variation price', 'wpshop');?> : </strong> {WPSHOP_VARIATION_DETAIL_PRICE}</span>
				<span class="variation_price_resume"><strong><?php _e('Variation sale price', 'wpshop');?> : </strong>{WPSHOP_VARIATION_DETAIL_SALE_PRICE}</span>
				<br/><span class="variation_price_indication"><strong>{WPSHOP_VARIATION_DETAIL_SALE_PRICE_INDICATION}</strong></span>
			</div>
		</div>
		<div class="wpshop_variation_metabox_col_close" >
			<a href="#" class="ui-dialog-titlebar-close ui-corner-all wpshop_variation_button_delete" id="wpshop_variation_delete_{WPSHOP_VARIATION_IDENTIFIER}" >
				<span class="ui-icon ui-icon-closethick"></span>
			</a>
		</div>
		<div class="wpshop_cls"></div>
	</div>
	{WPSHOP_VARIATION_DEFINITION_CONTENT}
</div><?php
$tpl_element['wpshop_admin_variation_item_def'] = ob_get_contents();
ob_end_clean();

/*    Variation item specific definition    */
ob_start();
?><div class="wpshop_variation_def_details{WPSHOP_ADMIN_VARIATION_SPECIFIC_DEFINITION_CONTAINER_CLASS}" id="wpshop_variation_def_details_{WPSHOP_VARIATION_IDENTIFIER}" >{WPSHOP_VARIATION_DEFINITION}{WPSHOP_VARIATION_IMAGE_CHOICE}<div class="wpshop_cls"></div></div><?php
$tpl_element['wpshop_admin_variation_item_specific_def'] = ob_get_contents();
ob_end_clean();

/*    Variation details    */
ob_start();
?>{WPSHOP_ADMIN_VARIATION_DETAIL}<?php
$tpl_element['wpshop_admin_variation_item_details'] = ob_get_contents();
ob_end_clean();

/*    Variation details line    */
ob_start();
?><div class="wpshop_variation_special_value_container{WPSHOP_ADMIN_VARIATION_DETAIL_DEF_CODE}" >
	<label class="wpshop_variation_special_value_label" for="{WPSHOP_ADMIN_VARIATION_DETAIL_DEF_ID}" >{WPSHOP_ADMIN_VARIATION_DETAIL_DEF_LABEL}</label>{WPSHOP_ADMIN_VARIATION_DETAIL_DEF_INPUT}
</div><?php
$tpl_element['wpshop_admin_variation_item_details_line'] = ob_get_contents();
ob_end_clean();

/*    Variation options    */
ob_start();
?><form action="<?php echo admin_url('admin-ajax.php'); ?>" method="POST" id="wpshop_variation_parameter_form" >
	<input type="hidden" name="current_post_id" id="current_post_id" value="{WPSHOP_ADMIN_VARIATION_PARAMETERS_FORM_HEAD_PRODUCT_ID}" />
	<input type="hidden" name="wpshop_ajax_nonce" id="wpshop_ajax_nonce" value="{WPSHOP_ADMIN_VARIATION_PARAMETERS_FORM_HEAD_NOUNCE}" />
	<input type="hidden" name="action" id="admin_variation_parameters_save" value="admin_variation_parameters_save" />
	<ul class="wpshop_product_variation_options" >
		<li class="wpshop_product_variation_option wpshop_product_variation_priority" >
			<h4><?php _e('In case you create combined variation AND single variation, what is the price to take into cart', 'wpshop')?></h4>
			<ul class="wpshop_product_variation_priority_choices" >
				<li class="wpshop_product_variation_priority_choices_item wpshop_product_variation_priority_choices_item_single" ><input type="radio"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_PRIORITY_SINGLE} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][priority][]" id="wpshop_product_variation_priority_single" value="single"/><label for="wpshop_product_variation_priority_single" ><?php _e('Sum of single variation', 'wpshop');?></label><div class="wpshop_cls"></div></li>
				<li class="wpshop_product_variation_priority_choices_item wpshop_product_variation_priority_choices_item_combined" ><input type="radio"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_PRIORITY_COMBINED} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][priority][]" id="wpshop_product_variation_priority_combined" value="combined"/><label for="wpshop_product_variation_priority_combined" ><?php _e('Combined variations (recommanded if you want to manage stock)', 'wpshop');?></label><div class="wpshop_cls"></div></li>
			</ul>
		</li>
		<li class="wpshop_product_variation_option wpshop_product_variation_option_price_behaviour" >
			<h4><?php _e('Choose the behaviour for the price of your product variation', 'wpshop')?></h4>
			<ul class="wpshop_product_variation_option_price_behaviour_choices" >
				<li class="wpshop_product_variation_option_price_behaviour_choices_item wpshop_product_variation_option_price_behaviour_choices_item_addition" ><input type="radio"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_BEHAVIOUR_ADDITION} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][price_behaviour][]" id="wpshop_product_variation_price_behaviour_addition" value="addition"/><label for="wpshop_product_variation_price_behaviour_addition" ><?php _e('Add the variation prices to the product price', 'wpshop');?></label><div class="wpshop_cls"></div></li>
				<li class="wpshop_product_variation_option_price_behaviour_choices_item wpshop_product_variation_option_price_behaviour_choices_item_replacement" ><input type="radio"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_BEHAVIOUR_REPLACEMENT} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][price_behaviour][]" id="wpshop_product_variation_price_behaviour_replacement" value="replacement"/><label for="wpshop_product_variation_price_behaviour_replacement" ><?php _e('Replace the product price with variation prices', 'wpshop');?></label><div class="wpshop_cls"></div></li>
			</ul>
		</li>
		<li class="wpshop_product_variation_option wpshop_product_variation_option_price_display" >
			<h4><?php _e('Choose variations prices display', 'wpshop')?></h4>
			<ul class="wpshop_product_variation_option_price_display_choices" >
				<li class="wpshop_product_variation_option_price_display_item wpshop_product_variation_option_price_display_item_text_from" ><input type="checkbox"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_PRICE_DISPLAY_TEXT_FROM} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][price_display][text_from]" id="wpshop_product_variation_price_display_from_text" /> <label for="wpshop_product_variation_price_display_from_text" class="alignright" ><?php _e('Display "price from" before basic price of product', 'wpshop');?></label><div class="wpshop_cls"></div></li>
				<li class="wpshop_product_variation_option_price_display_item wpshop_product_variation_option_price_display_item_lower_price" ><input type="checkbox"{WPSHOP_ADMIN_VARIATION_OPTIONS_SELECTED_PRICE_DISPLAY_LOWER_PRICE} name="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION; ?>[options][price_display][lower_price]" id="wpshop_product_variation_price_display_lower_price" /> <label for="wpshop_product_variation_price_display_lower_price" class="alignright" ><?php _e('Display the lowest price of variation', 'wpshop');?></label><div class="wpshop_cls"></div></li>
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
	<h4><?php _e('Choose required attribute', 'wpshop');?></h4>
	<ul class="wpshop_product_variation_option_required_attribute_choices" >
		{WPSHOP_ADMIN_VARIATION_OPTIONS_REQUIRED_ATTRIBUTE}
	</ul>
</li><?php
$tpl_element['wpshop_admin_variation_options_required_attribute_container'] = ob_get_contents();
ob_end_clean();

/*    Available attribute item    */
ob_start();
?><li class="variation_attribute_container{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CONTAINER_CLASS}" ><input{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CHECKBOX_STATE} type="checkbox" class="variation_attribute_usable" name="wpshop_attribute_to_use_for_variation[{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_CODE}]" value="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_NAME}" id="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_ID}" /> <label{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_LABEL_STATE} for="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_ID}" >{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL}</label>{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_LABEL_EXPLAINATION}<div class="wpshop_cls"></div></li><?php
$tpl_element['wpshop_admin_attribute_for_variation_item'] = ob_get_contents();
ob_end_clean();

ob_start();
?><li class="wpshop_cls wpshop_product_variation_option wpshop_product_variation_option_attribute_default_value" >
	<h4><?php _e('Choose default value for attribute in current product', 'wpshop');?></h4>
	<ul class="wpshop_product_variation_option_attribute_default_value_choices" >
		{WPSHOP_ADMIN_VARIATION_OPTIONS_ATTRIBUTE_DEFAULT_VALUE}
	</ul>
</li><?php
$tpl_element['wpshop_admin_variation_options_default_value_container'] = ob_get_contents();
ob_end_clean();

/*    Available attribute item for default value choosen    */
ob_start();
?><li class="variation_attribute_container_default_value{WPSHOP_ADMIN_VARIATIONS_DEF_LIST_ATTRIBUTE_CONTAINER_CLASS}" ><label for="{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_TO_USE_ID}" >{WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_DEFAULT_VALUE_LABEL}</label> {WPSHOP_ADMIN_VARIATIONS_DEF_ATTRIBUTE_DEFAULT_VALUE_INPUT}<div class="wpshop_cls"></div></li><?php
$tpl_element['wpshop_admin_attribute_for_variation_item_for_default'] = ob_get_contents();
ob_end_clean();

/*    Available attribute item for default value choosen    */
ob_start();
?>
<div class="wps_variation_picture_selector"><ul>
<div><h3><?php _e('Choose the picture to link to variation', 'wpshop');?></h3></div>
{WPSHOP_PICTURE_CHOICE_CONTAINER_CONTENT}
</ul>
</div>
<?php
$tpl_element['wpshop_admin_variation_picture_choice_container'] = ob_get_contents();
ob_end_clean();

/*    Available attribute item for default value choosen    */
ob_start();
?>
<li><input type="radio" name="wps_pdt_variations[{WPSHOP_PRODUCT_VARIATION_ID}][wps_attached_picture_id]" value="{WPSHOP_PICTURE_CHOICE_VARIATION_ID}" {WPSHOP_PICTURE_CHOICE_SELECTED} /><br/>{WPSHOP_PICTURE_CHOICE_VARIATION_IMG}</li>
<?php
$tpl_element['wpshop_admin_variation_picture_choice_element'] = ob_get_contents();
ob_end_clean();
