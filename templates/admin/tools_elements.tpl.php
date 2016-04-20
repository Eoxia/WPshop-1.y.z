<?php

/**	Tools main page	*/
ob_start();
echo wpshop_display::displayPageHeader(__('Outils pour WP-Shop', 'wpshop'), '', __('WPShop tools', 'wpshop'), __('WPShop tools', 'wpshop'), false, '', '', 'wpshop-tools');
?><div id="wpshop_configurations_container" class="wpshop_cls" >
	<div id="tools_tabs" class="wpshop_tabs wpshop_full_page_tabs wpshop_tools_tabs" >
		<ul>
			<li class="loading_pic_on_select" ><a href="<?php echo admin_url('admin-ajax.php'); ?>?action=wpshop_tool_db_check" title="wpshop_tools_tab_container" ><?php _e('Database structure check', 'wpshop'); ?></a></li>
			<li class="loading_pic_on_select" ><a href="<?php echo admin_url('admin-ajax.php'); ?>?action=wpshop_tool_default_datas_check" title="wpshop_tools_tab_container" ><?php _e('Default data check', 'wpshop'); ?></a></li>
			<li class="loading_pic_on_select" ><a href="<?php echo admin_url('admin-ajax.php'); ?>?action=wps_mass_action" title="wpshop_tools_tab_container" class="wps_mass_action" ><?php _e('Mass action', 'wpshop'); ?></a></li>
			<li class="loading_pic_on_select" ><a href="<?php echo admin_url('admin-ajax.php'); ?>?action=checking_products_values" title="wpshop_tools_tab_container" class="checking_products_values" ><?php _e('Checking product values', 'wpshop'); ?></a></li>
		</ul>
		<div id="wpshop_tools_tab_container" ></div>
	</div>
</div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#wpshop_tools_tab_container").html(jQuery("#wpshopLoadingPicture").html());
		jQuery("#tools_tabs").tabs( {
			load: function( event, ui ) {
				if( ui.panel.attr('id') == 'ui-id-8' && wps_product ) {
					wps_product.product_check_data();
				}
			}
		} );
		jQuery(".loading_pic_on_select a").click(function(){
			jQuery("#wpshop_tools_tab_container").html(jQuery("#wpshopLoadingPicture").html());
		});

		jQuery(".wpshop_repair_db_version").live("click", function(){
			jQuery(this).after(jQuery("#wpshopLoadingPicture").html());
			var data = {
				action: "wpshop_ajax_db_repair_tool",
				version_id: jQuery(this).attr("id").replace("wpshop_repair_db_version_", ""),
			};
			jQuery.post(ajaxurl, data, function(response){
				if (response) {
					jQuery("#wpshop_tools_tab_container").load("<?php echo admin_url('admin-ajax.php') ?>", {
						"action": "wpshop_tool_db_check",
					});
				}
				else {
					alert(wpshopConvertAccentTojs("<?php _e('An error occured while attempting to repair database', 'wpshop'); ?>"));
				}
			}, 'json');
		});

		jQuery(".wpshop_repair_default_data_cpt").live("click", function(){
			jQuery(this).after(jQuery("#wpshopLoadingPicture").html());
			var data = {
				action: "wpshop_ajax_repair_default_datas",
				type: "<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES; ?>",
				identifier: jQuery(this).attr("id").replace("wpshop_repair_default_data_wpshop_cpt_", ""),
			};
			jQuery.post(ajaxurl, data, function(response){
				if (response[0]) {
					jQuery("#" + response[1]).html(response[2]);
				}
				else {
					alert(wpshopConvertAccentTojs("<?php _e('An error occured while attempting to repair default custom post type', 'wpshop'); ?>"));
				}
			}, 'json');
		});
		jQuery(".wpshop_repair_default_data_attributes").live("click", function(){
			jQuery(this).after(jQuery("#wpshopLoadingPicture").html());
			var data = {
				action: "wpshop_ajax_repair_default_datas",
				type: "<?php echo WPSHOP_DBT_ATTRIBUTE; ?>",
				identifier: jQuery(this).attr("id").replace("wpshop_repair_default_data_wpshop_cpt_", ""),
			};
			jQuery.post(ajaxurl, data, function(response){
				if (response[0]) {
					jQuery("#" + response[1]).html(response[2]);
				}
				else {
					alert(wpshopConvertAccentTojs("<?php _e('An error occured while attempting to repair default attributes', 'wpshop'); ?>"));
				}
			}, 'json');
		});
		jQuery(".wpshop_translate_default_data_attributes").live("click", function(){
			jQuery(this).after(jQuery("#wpshopLoadingPicture").html());
			var data = {
				action: "wpshop_ajax_translate_default_datas",
				type: "<?php echo WPSHOP_DBT_ATTRIBUTE; ?>",
				identifier: jQuery(this).attr("id").replace("wpshop_translate_default_data_wpshop_cpt_", ""),
			};
			jQuery.post(ajaxurl, data, function(response){
				if (response['status']) {
					alert(wpshopConvertAccentTojs("<?php _e('Default attributes translation has been updated', 'wpshop'); ?>"));
				}
				else {
					alert(wpshopConvertAccentTojs("<?php _e('An error occured while attempting to repair default attributes', 'wpshop'); ?>"));
				}
			}, 'json');
		});
	});
</script><?php
echo wpshop_display::displayPageFooter(false);
$tpl_element['wpshop_admin_tools_main_page'] = ob_get_contents();
ob_end_clean();


ob_start();
?><div id="wps_tools_mas_action_message_copy_betwwen_attributes" ></div><form method="post" id="wps_tools_mass_update_form" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" ><input type="hidden" name="action" value="wps_mass_action_update_attribute" /><?php _e('Copy the different values of a given attribute into another attribute for products', 'wpshop'); ?><br/><?php echo sprintf( __('Copy values from %s to %s', 'wpshop'), '{WPSHOP_ATTRIBUTE_LIST_FROM}', '{WPSHOP_ATTRIBUTE_LIST_TO}'); ?>
<select name="wps_entity_to_transfert" >
	<option value="wpshop_product" ><?php _e( 'Products', 'wpshop' ); ?></option>
	<option value="wps_pdt_variations" ><?php _e( 'Products variations', 'wpshop' ); ?></option>
</select>
<input type="submit" value="<?php _e('Update values', 'wpshop'); ?>" /></form>
<hr/>

<form method="post" id="wps_tools_mass_action_change_variation_option" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" >
	<input type="hidden" name="action" value="wps_mass_action_change_variation_option" />
	<?php _e('Change option for product variation attribute\'s', 'wpshop'); ?>{WPSHOP_USED_FOR_VARIATION_ATTRIBUTE_LIST}
	<div id="wps_mass_action_change_variation_option_container"></div>
	<input id="wps_mass_action_change_variation_option_submit_button" type="submit" value="<?php _e('Update options', 'wpshop'); ?>" />
</form>
<script type="text/javascript" >
	jQuery(document).ready(function(){
		jQuery("#wps_tools_mass_update_form").ajaxForm({
			dataType: 'json',
			success: function( response ) {
				jQuery("#wps_tools_mass_update_form").resetForm();
				jQuery("#wps_tools_mas_action_message_copy_betwwen_attributes").html( response['error'] );
			}
		});

		jQuery("#attribute_id").change(function(){
			jQuery.post(ajaxurl, {action:"wps_tools_mass_action_load_possible_options_for_variations_attributes", attribute_id: jQuery(this).val(), }, function ( response ) {
				jQuery("#wps_mass_action_change_variation_option_container").html( response );
				jQuery("#wps_mass_action_change_variation_option_submit_button").show();
			});
		});
		jQuery("#wps_tools_mass_action_change_variation_option").ajaxForm({
			success: function( response ) {
				jQuery("#wps_tools_mass_action_change_variation_option").resetForm();
				jQuery("#wps_tools_mas_action_message_copy_betwwen_attributes").html( response['error'] );
			}
		});
	});
</script><?php
$tpl_element['wps_admin_tools_mass_action_main_page'] = ob_get_contents();
ob_end_clean();


ob_start();
?><ul>
	{WPSHOP_TOOLS_CUSTOM_POST_TYPE_LIST}
</ul><?php
$tpl_element['wpshop_admin_tools_default_datas_check_main'] = ob_get_contents();
ob_end_clean();


ob_start();
?><li class="wpshop_tools_default_custom_post_type_main_container{WPSHOP_TOOLS_CUSTOM_POST_TYPE_CONTAINER_CLASS}" id="{WPSHOP_CUSTOM_POST_TYPE_NAME}" >
	{WPSHOP_TOOLS_CUSTOM_POST_TYPE_CONTAINER}
</li><?php
$tpl_element['wpshop_admin_tools_default_datas_check_main_element'] = ob_get_contents();
ob_end_clean();


ob_start();
?><h2>{WPSHOP_CUSTOM_POST_TYPE_IDENTIFIER}</h2>
<ul class="wpshop_tools_default_datas_repair_attribute_container" >
	{WPSHOP_CUSTOM_POST_TYPE_DEFAULT_ATTRIBUTES}
</ul><?php
$tpl_element['wpshop_admin_tools_default_datas_check_main_element_content_no_error'] = ob_get_contents();
ob_end_clean();

ob_start();
?><h2>{WPSHOP_CUSTOM_POST_TYPE_IDENTIFIER}</h2>
<button id="wpshop_repair_default_data_{WPSHOP_CUSTOM_POST_TYPE_NAME}" class="wpshop_repair_default_data_cpt" ><?php _e('Re-create this type of element', 'wpshop'); ?></button><?php
$tpl_element['wpshop_admin_tools_default_datas_check_main_element_content_error'] = ob_get_contents();
ob_end_clean();


ob_start();
?><li><h3 class="wpshop_default_datas_state no_error" ><?php _e('Attributes that are OK', 'wpshop'); ?></h3><button id="wpshop_translate_default_data_{WPSHOP_CUSTOM_POST_TYPE_NAME}" class="wpshop_translate_default_data_attributes" ><?php _e('Overwrite attribute name translation', 'wpshop'); ?></button><br/>{WPSHOP_CUSTOM_POST_TYPE_DEFAULT_ATTRIBUTES_LIST}</li><?php
$tpl_element['wpshop_admin_tools_default_datas_check_main_element_content_attributes_no_error'] = ob_get_contents();
ob_end_clean();

ob_start();
?><li><h3 class="wpshop_default_datas_state error" ><?php _e('Attributes needing attention', 'wpshop'); ?></h3><button id="wpshop_repair_default_data_{WPSHOP_CUSTOM_POST_TYPE_NAME}" class="wpshop_repair_default_data_attributes" ><?php _e('Repair missing attributes', 'wpshop'); ?></button><br/>{WPSHOP_CUSTOM_POST_TYPE_DEFAULT_ATTRIBUTES_LIST}</li><?php
$tpl_element['wpshop_admin_tools_default_datas_check_main_element_content_attributes_error'] = ob_get_contents();
ob_end_clean();
