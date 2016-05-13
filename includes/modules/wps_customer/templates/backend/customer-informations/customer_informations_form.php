<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed">
<div id="wps_signup_error_container"></div>
	<?php
	if( !empty($signup_fields) ) :
		foreach( $signup_fields as $fields_section_name => $fields_section ) : ?>
		<span class="wps-h4"><?php _e( $fields_section_name, 'wpshop' ); ?></span>
		<div><?php
			foreach( $fields_section as $signup_field ) :
			$query = $wpdb->prepare( 'SELECT value  FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.strtolower($signup_field->data_type). ' WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d ', $customer_entity_type_id, $signup_field->id, $cid );

			$value = $wpdb->get_var( $query );
			$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $signup_field, $value, array( 'from' => 'frontend', array( 'options' => array( 'original' => true, ), ) ) );
		?>
			<div class="wps-form-group">
				<label for="<?php echo $signup_field->code; ?>"><?php  _e( stripslashes($signup_field->frontend_label), 'wpshop'); ?><?php //echo ( ( !empty($attribute_output_def['required']) && $attribute_output_def['required'] == 'yes' ) ? ' <em>*</em>' : '' ); ?></label>
				<div id="<?php echo $signup_field->code; ?>" class="wps-form"><?php echo $attribute_output_def['output']; echo $attribute_output_def['options']; ?></div>
			</div>
		<?php
			endforeach; ?>
		</div><?php
		endforeach;
	endif;
	?>
	<div class="wps-form-group">
		<?php do_action('signup_extra_fields'); ?>
	</div>
</div>
