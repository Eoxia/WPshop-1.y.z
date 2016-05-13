<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-form-group">
	<label for="filter_search_<?php echo $attribute_def->code; ?>"><?php _e($attribute_def->frontend_label, 'wpshop'); ?></label>
	<div class="wps-form">
		<select id="filter_search_<?php echo $attribute_def->code; ?>" name="filter_search_<?php echo $attribute_def->code; ?>" class="filter_search_element" >
			<option value="all_attribute_values"><?php _e('Display all', 'wpshop'); ?></option>
			<?php echo $list_values; ?>
		</select>
	</div>
</div>
