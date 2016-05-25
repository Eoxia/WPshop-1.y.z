<?php if ( !defined( 'ABSPATH' ) ) exit;
 if ( !$force_login ) : ?>
<?php echo do_shortcode( '[wps_first_login]' ); ?>

<?php endif; ?>
<?php global $post; ?>
<div class="wps-boxed" id="wps_login_form_container">
	<span class="wps-h5"><?php _e ('Log in', 'wpshop'); ?></span>
	<div class="wp-form-group wps-welcome_back_message" id="welcome_back_message">
		<span class="welcome_back_message_hello_message">
			<?php _e( 'Howdy', 'wpshop')?>
		</span> <strong id="user_firstname"></strong>, <br/><?php _e( 'we are happy to see you again', 'wpshop'); ?> !</div>
	<div id="wps_login_error_container"></div>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" id="wps_login_form">
		<?php echo apply_filters( 'login_form_top', '', $args ); ?>
		<input type="hidden" name="action" value="wps_login_request" />
    <?php wp_nonce_field( 'control_login_form_request' ); ?>
		<input type="hidden" name="wps-checking-origin" value="<?php echo $post->ID; ?>" />
		<div class="wps-form-group">
			<label for="wps_login_email_address"><?php _e('Email address', 'wpshop');?></label>
			<span class="wps-help-inline wps-help-inline-title"></span>
			<div id="wps_login_email_address" class="wps-form"><input type="text" name="wps_login_user_login" id="wps_login_email" placeholder="<?php _e('Your email address', 'wpshop');?>" /></div>
		</div>

		<div class="wps-form-group">
			<label for="wps_login_password"><?php _e('Password', 'wpshop');?></label>
			<div id="wps_login_password" class="wps-form"><input type="password" name="wps_login_password" id="wps_login_password" placeholder="<?php _e('Your password', 'wpshop');?>" /></div>
		</div>
		<div class="wps-form-group">
			<?php echo apply_filters( 'login_form_middle', '', $args ); ?>
			<?php do_action( 'login_form' ); ?>
		</div>
		<div class="wps-form-group wps-password-reminder">
			<a href="#" data-nonce="<?php echo wp_create_nonce( 'wps_fill_forgot_password_modal' ); ?>" class="wps-modal-forgot-password-opener wps-password-reminder-link"><?php _e( 'Forgotten password', 'wpshop'); ?> ?</a> <button class="wps-bton-first-alignRight-rounded" id="wps_login_button"><?php _e('Connexion', 'wpshop'); ?></button>
			<div class="wps-password-reminder-content">

			</div>
		</div>
		<?php echo apply_filters( 'login_form_bottom', '', $args ); ?>
	</form>
</div>
