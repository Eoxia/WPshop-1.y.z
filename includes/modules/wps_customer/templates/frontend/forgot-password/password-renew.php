<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div id="wps_renew_password_error_container_true"></div>
<div class="wps-boxed" id="wps_password_renew">
	<div id="wps_renew_password_error_container"></div>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" id="wps_forgot_password_form_renew" >

		<input type="hidden" name="activation_key" value="<?php echo sanitize_text_field($_GET['key']); ?>" />
		<input type="hidden" name="user_login" value="<?php echo sanitize_text_field($_GET['login']); ?>" />
		<input type="hidden" name="action" value="wps_forgot_password_renew" />
		<?php echo wp_nonce_field( 'wps_forgot_password_renew' ); ?>

		<div class="wps-form-group">
			<label for="wps_login_email_address"><?php _e('New password', 'wpshop');?></label>
			<div id="wps_login_email_address" class="wps-form"><input type="password" name="pass1" id="wps_new_password_request" placeholder="<?php _e('New password', 'wpshop');?>" /></div>
		</div>
		<div class="wps-form-group">
			<label for="wps_login_email_address"><?php _e('Confirm new password', 'wpshop');?></label>
			<div id="wps_login_email_address" class="wps-form"><input type="password" name="pass2" id="wps_new_password_request" placeholder="<?php _e('Confirm new password', 'wpshop');?>" /></div>
		</div>
		<div class="wps-form-group">

		</div>
		<div class="wps-form-group">
			<button class="wps-bton-first-alignRight-rounded" id="wps_send_forgot_password_renew"><?php _e('Renew your password', 'wpshop'); ?></button>
		</div>
	</form>
</div>
