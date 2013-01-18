	<li class="ui-state-default clear wpshop_attribute_combo_options_container" id="att_option_div_container_<?php echo $option_id; ?>" >
		<table class="wpshop_attr_combo_option_detail_table" >
			<tr class="wpshop_attr_combo_option_detail_table_line wpshop_attr_combo_option_detail_table_title" >
				<td rowspan="2" class="wpshop_attr_combo_option_default_td" ><input title="<?php _e('Default value', 'wpshop'); ?>" type="radio" id="default_value_<?php echo $option_id; ?>" name="<?php echo WPSHOP_DBT_ATTRIBUTE; ?>[default_value]" value="<?php echo $option_id; ?>"<?php echo (!empty($option_id) && !empty($option_default_value) && ($option_id == $option_default_value) ? ' checked="checked"' : ''); ?>/></td>
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
					<input type="text" value="<?php echo stripslashes($option_name); ?>" name="optionsUpdate[<?php echo $option_id; ?>]" id="attribute_option_<?php echo $option_id; ?>" />
				<?php else: ?>
					<?php echo $option_name; ?>
				<?php endif; ?>
				</td>
				<?php if( WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT ): ?><td><?php if( current_user_can('wpshop_edit_attributes_select_values') ): ?><input type="text" value="<?php echo str_replace(".", ",", $options_value); ?>" name="optionsUpdateValue[<?php echo $option_id; ?>]" id="attribute_option_value<?php echo $option_id; ?>" /><?php else: echo str_replace(".", ",", $options_value); endif; ?></td><?php endif; ?>
			</tr>
		</table>
	<?php if( current_user_can('wpshop_delete_attributes_select_values') ): ?>
		<div class="wpshop_admin_toolbox wpshop_attr_tool_box" >
			<?php if ( current_user_can('wpshop_edit_attributes') && ($option_value_id <= 0) ) : ?>
				<a class="wpshop_attr_tool_box_button wpshop_attr_tool_box_delete wpshop_attr_combo_option_delete wpshop_attr_combo_option_delete_<?php echo $option_id; ?>" id="wpshop_edit_<?php echo $option_id; ?>" title="<?php _e('Delete this value', 'wpshop'); ?>"></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	</li>