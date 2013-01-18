<div class="ui-state-disabled wpshop_att_set_section_edition_container wpshop_att_set_section_edition_container_<?php echo str_replace('-', '_', sanitize_title($attributeSetDetailsGroup['id'])); ?>" id="wpshop_att_set_section_edition_container_<?php echo $attributeSetDetailsGroup['id']; ?>" >
	<div class="clear" >
		<input type="text" class="wpshop_att_set_section_edition_input wpshop_attribute_set_section_name" name="wpshop_attribute_set_section[<?php echo $attributeSetDetailsGroup['id']; ?>][name]" id="wpshop_attribute_set_section_name_set_<?php echo $attributeSetDetailsGroup['id']; ?>" value="<?php echo $area_name; ?>" />
		<label for="wpshop_attribute_set_section_name_set_<?php echo $attributeSetDetailsGroup['id']; ?>" ><?php echo __('Attribute set section name', 'wpshop'); ?></label>
	</div>
	<div class="clear" >
		<select class="wpshop_att_set_section_edition_input wpshop_attribute_set_section_backend_type" name="wpshop_attribute_set_section[<?php echo $attributeSetDetailsGroup['id']; ?>][backend_display_type]" id="wpshop_attribute_set_section_backend_display_type_set_<?php echo $attributeSetDetailsGroup['id']; ?>" >
			<option value="fixed-tab"<?php echo (!empty($attributeSetDetailsGroup['backend_display_type']) && ($attributeSetDetailsGroup['backend_display_type']=='fixed-tab')?' selected="selected"':''); ?>><?php echo __('Tab', 'wpshop'); ?></option>
			<option value="movable-tab"<?php echo (!empty($attributeSetDetailsGroup['backend_display_type']) && ($attributeSetDetailsGroup['backend_display_type']=='movable-tab')?' selected="selected"':''); ?>><?php echo __('Separated box', 'wpshop'); ?></option>
		</select>
		<label for="wpshop_attribute_set_section_backend_display_type_set_<?php echo $attributeSetDetailsGroup['id']; ?>" ><?php echo __('Display in admin as', 'wpshop'); ?></label>
	</div>
</div>