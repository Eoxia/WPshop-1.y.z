<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed">
	<div id="wps_renew_password_error_container"></div>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" id="wps_forgot_password_form" >
		<input type="hidden" name="action" value="wps_forgot_password_request" />
		<?php wp_nonce_field( 'wps_forgot_password_request' ); ?>
		<div class="wps-form-group">
			<label for="wps_login_email_address"><?php _e('Email address', 'wpshop');?></label>
			<div id="wps_login_email_address" class="wps-form"><input type="text" name="wps_user_login" id="wps_new_password_request" placeholder="<?php _e('Your email address', 'wpshop');?>" /></div>
		</div>
		<div class="wps-form-group">
			<?php do_action('lostpassword_form'); ?>
		</div>
		<div class="wps-form-group">
			<button class="wps-bton-first-alignRight-rounded" id="wps_send_forgot_password_request"><?php _e('Renew your password', 'wpshop'); ?></button>
		</div>
	</form>
</div>
