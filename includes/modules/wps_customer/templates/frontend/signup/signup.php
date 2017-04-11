<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed" id="wps_signup_form_container">
<span class="wps-h5"><?php _e ('Sign up', 'wpshop'); ?></span>
<div id="wps_signup_error_container"></div>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" id="wps_signup_form">
		<?php if( !empty($args) ) : ?>
			<input type="hidden" name="wps_sign_up_request_from_admin" value="admin" />
		<?php endif; ?>

		<input type="hidden" name="action" value="wps_signup_request" />
		<?php wp_nonce_field( 'wps_save_signup_form' ); ?>

		<?php
		if( !empty($signup_fields) ) :

			$wpshop_billing_address = get_option('wpshop_billing_address');

			foreach( $signup_fields as $signup_field ) :
			if( isset( $signup_field->code ) && $signup_field->code == 'is_provider' ) {
				continue;
			}
			$value = ( !empty($signup_field->frontend_input) && $signup_field->frontend_input != 'password' && !empty($_POST) && !empty($_POST['attribute']) && !empty($_POST['attribute'][$signup_field->data_type]) && !empty( $_POST['attribute'][$signup_field->data_type][$signup_field->code]) ) ? sanitize_text_field( $_POST['attribute'][$signup_field->data_type][$signup_field->code] ) : '';
			$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $signup_field, $value, array( 'from' => 'frontend', ) );
		?>
			<div class="wps-form-group field-<?php echo $signup_field->code; ?>">
				<label for="<?php echo $signup_field->code; ?>"><?php  _e( stripslashes($signup_field->frontend_label), 'wpshop'); ?> <?php echo ( ( !empty($attribute_output_def['required']) && $attribute_output_def['required'] == 'yes' ) ? '<em>*</em>' : '' ); ?></label>
				<div id="<?php echo $signup_field->code; ?>" class="wps-form"><?php echo $attribute_output_def['output']; echo $attribute_output_def['options']; ?></div>
			</div>
		<?php
			/** Check confirmation field **/
			$current_field_key = $signup_field->code;
			if ( $signup_field->_need_verification == 'yes'  ) {
				$signup_field->code = $signup_field->code.'2';
				$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $signup_field, '', array() );
			?>
				<div class="wps-form-group field-<?php echo $signup_field->code; ?>">
					<label for="<?php echo $signup_field->code; ?>"><?php printf( __('Confirm %s', 'wpshop'), stripslashes( strtolower(__( $signup_field->frontend_label, 'wpshop')) ) ); ?> <?php echo ( ( !empty($attribute_output_def['required']) && $attribute_output_def['required'] == 'yes' ) ? '<em>*</em>' : '' ); ?></label>
					<div id="<?php echo $signup_field->code; ?>" class="wps-form"><?php echo $attribute_output_def['output']; echo $attribute_output_def['options']; ?></div>
				</div>
			<?php
			}

			if ( !empty($wpshop_billing_address['integrate_into_register_form']) && ($wpshop_billing_address['integrate_into_register_form'] == 'yes') && !empty($wpshop_billing_address['integrate_into_register_form_after_field']) && ($wpshop_billing_address['integrate_into_register_form_after_field'] == $current_field_key ) ) {
				$current_connected_user = null;
				if ( get_current_user_id() > 0 ) {
					$query = $wpdb->prepare ("SELECT *
						FROM " . $wpdb->posts . "
						WHERE post_type = '" .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. "'
						AND post_parent = %d
						ORDER BY ID
						LIMIT 1", get_current_user_id() );
					$current_connected_user = $wpdb->get_var($query);
				}
				$wps_address = new wps_address();
				echo $wps_address->display_form_fields($wpshop_billing_address['choice'], '', 'true', '', array(), array( 'title' => false, 'address_title' => false, 'field_to_hide' => $wpshop_billing_address[ 'integrate_into_register_form_matching_field' ] ), array(), $current_connected_user);
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
		<?php do_action('signup_extra_content'); ?>
	</div>

	<div class="wps-form-group">
		<button class="wps-bton-first-alignRight-rounded" id="wps_signup_button"><?php _e('Sign up', 'wpshop'); ?></button>
	</div>
</div>
