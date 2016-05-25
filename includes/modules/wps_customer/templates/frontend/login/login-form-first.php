<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed" id="wps_login_form_first_step">
	<span class="wps-h5"><?php _e ('Log in', 'wpshop'); ?> / <?php _e( 'Sign up', 'wpshop'); ?></span>
	<div id="wps_login_first_error_container" class="wps-login-first-error"></div>
	<div class="wps-form-group">
		<label for="wps_login_first_email_address">
			<?php _e('Email address', 'wpshop');?>
		</label>
		<span class="wps-help-inline"></span>
		<div class="wps-form">
			<input type="text" name="wps_login_user_login" id="wps_login_first_email_address" placeholder="<?php _e('Your email address', 'wpshop');?>" />
		</div>
	</div>
	<div class="wps-form-group">
		<button class="wps-bton-first-alignRight-rounded" data-nonce="<?php echo wp_create_nonce( 'wps_login_first_request' ); ?>" id="wps_first_login_button">
			<?php _e('Continue', 'wpshop'); ?>
		</button>
	</div>
</div>
