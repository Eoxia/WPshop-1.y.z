<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-form-group">
	<?php 
	foreach( $stored_available_attribute_values as $stored_available_attribute_value ) : ?>
		<input type="radio" class="filter_search_radiobox" id="filter_search_<?php echo $attribute_def->code; ?>_<?php echo $stored_available_attribute_value['option_id'];?>" name="filter_search_<?php echo $attribute_def->code; ?>" /> <label for="filter_search_<?php echo $attribute_def->code; ?>_<?php echo $stored_available_attribute_value['option_id']; ?>"><?php echo $stored_available_attribute_value['option_label']; ?></label>
	<?php endforeach; ?>
</div>
