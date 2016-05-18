<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div id="wps_signup_error_container"></div>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" id="wps_account_informations_form">
		<input type="hidden" name="action" value="wps_save_account_informations" />
		<?php
		wp_nonce_field( 'wps_save_account_informations' );
		
		if( !empty($signup_fields) ) :
			foreach( $signup_fields as $signup_field ) :
			if( isset( $signup_field->code ) && $signup_field->code == 'is_provider' ) {
				continue;
			}
			$query = $wpdb->prepare( 'SELECT value  FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.strtolower($signup_field->data_type). ' WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d ', $customer_entity_type_id, $signup_field->id, $cid );

			$value = $wpdb->get_var( $query );
			$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $signup_field, $value, array( 'from' => 'frontend', array( 'options' => array( 'original' => true, ), ) ) );
		?>
			<div class="wps-form-group">
				<label for="<?php echo $signup_field->code; ?>"><?php  _e( stripslashes($signup_field->frontend_label), 'wpshop'); ?> <?php echo ( ( !empty($attribute_output_def['required']) && $attribute_output_def['required'] == 'yes' ) ? '<em>*</em>' : '' ); ?></label>
				<div id="<?php echo $signup_field->code; ?>" class="wps-form"><?php echo $attribute_output_def['output']; echo $attribute_output_def['options']; ?></div>
			</div>
		<?php
			/** Check confirmation field **/
			if ( $signup_field->_need_verification == 'yes'  ) {
				$signup_field->code = $signup_field->code.'2';
				$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $signup_field, $value, array('from' => 'frontend',) );
			?>
				<div class="wps-form-group">
					<label for="<?php echo $signup_field->code; ?>"><?php printf( __('Confirm %s', 'wpshop'), stripslashes( strtolower(__( $signup_field->frontend_label, 'wpshop')) ) ); ?> <?php echo ( ( !empty($attribute_output_def['required']) && $attribute_output_def['required'] == 'yes' ) ? '<em>*</em>' : '' ); ?></label>
					<div id="<?php echo $signup_field->code; ?>" class="wps-form"><?php echo $attribute_output_def['output']; echo $attribute_output_def['options']; ?></div>
				</div>
			<?php
			}
			endforeach;
		endif;
		?>
		<?php
		$wps_account_ctr = new wps_account_ctr();
		echo $wps_account_ctr->display_commercial_newsletter_form();
		?>
	</form>
	<div class="wps-form-group">
		<?php do_action('signup_extra_fields'); ?>
	</div>

	<button class="wps-bton-first-rounded" id="wps_account_form_button"><?php _e('Save', 'wpshop'); ?></button>
