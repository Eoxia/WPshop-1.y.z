<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Emails options management
* 
* Define the different method to manage the different emails options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different emails options
* @package wpshop
* @subpackage librairies
*/
class wpshop_email_options
{

	/**
	*
	*/
	function declare_options(){
		add_settings_section('wpshop_emails', __('Email addresses', 'wpshop'), array('wpshop_email_options', 'plugin_section_text'), 'wpshop_emails');
			register_setting('wpshop_options', 'wpshop_emails', array('wpshop_email_options', 'wpshop_options_validate_emails'));
			add_settings_field('wpshop_noreply_email', __('Mails answers address email', 'wpshop'), array('wpshop_email_options', 'wpshop_noreply_email_field'), 'wpshop_emails', 'wpshop_emails');
			add_settings_field('wpshop_contact_email', __('Contact email', 'wpshop'), array('wpshop_email_options', 'wpshop_contact_email_field'), 'wpshop_emails', 'wpshop_emails');
			
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation')) && !isset($_POST['old_wpshop_shop_type']) || (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation'))){/* Messages */
			add_settings_section('wpshop_messages', __('Messages', 'wpshop'), array('wpshop_email_options', 'plugin_section_text'), 'wpshop_messages');
				// Object
				// register_setting('wpshop_options', 'WPSHOP_SIGNUP_MESSAGE_OBJECT', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_SIGNUP_MESSAGE_OBJECT'));
				// add_settings_field('WPSHOP_SIGNUP_MESSAGE_OBJECT', __('Signup - Object', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_SIGNUP_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
				// Message
				register_setting('wpshop_options', 'WPSHOP_SIGNUP_MESSAGE', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_SIGNUP_MESSAGE'));
				add_settings_field('WPSHOP_SIGNUP_MESSAGE', __('Signup', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_SIGNUP_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');
				
				// Object
				// register_setting('wpshop_options', 'WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT'));
				// add_settings_field('WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT', __('Order confirmation - Object', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
				// Message
				register_setting('wpshop_options', 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_ORDER_CONFIRMATION_MESSAGE'));
				add_settings_field('WPSHOP_ORDER_CONFIRMATION_MESSAGE', __('Order confirmation', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_ORDER_CONFIRMATION_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');
				
				// Object
				// register_setting('wpshop_options', 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'));
				// add_settings_field('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', __('Payment confirmation - Object', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
				// Message
				register_setting('wpshop_options', 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE'));
				add_settings_field('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', __('Payment confirmation', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');
				
				// Object
				// register_setting('wpshop_options', 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'));
				// add_settings_field('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', __('Others payment confirmation - Object', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
				// Message
				register_setting('wpshop_options', 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE'));
				add_settings_field('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', __('Others payment confirmation', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');
				
				// Object
				// register_setting('wpshop_options', 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT'));
				// add_settings_field('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT', __('Shipping confirmation - Object', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
				// Message
				register_setting('wpshop_options', 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', array('wpshop_email_options', 'wpshop_options_validate_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE'));
				add_settings_field('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', __('Shipping confirmation', 'wpshop'), array('wpshop_email_options', 'wpshop_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');
		}
	}

	// Common section description
	function plugin_section_text() {
		echo '';
	}

	/* ------------------------ */
	/* --------- EMAILS ------- */
	/* ------------------------ */
	function wpshop_noreply_email_field() {
		$admin_email = get_bloginfo('admin_email');
		$emails = get_option('wpshop_emails', null);
		$email = empty($emails['noreply_email']) ? $admin_email : $emails['noreply_email'];
		echo '<input name="wpshop_emails[noreply_email]" type="text" value="'.$email.'" />
		<a href="#" title="'.__('This is the no reply email','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_contact_email_field() {
		$admin_email = get_bloginfo('admin_email');
		$emails = get_option('wpshop_emails', null);
		$email = empty($emails['contact_email']) ? $admin_email : $emails['contact_email'];
		echo '<input name="wpshop_emails[contact_email]" type="text" value="'.$email.'" />
		<a href="#" title="'.__('This is the email on which customers can contact you','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_emails($input) {return $input;}
	
	/* -------------------------- */
	/* --------- MESSAGES ------- */
	/* -------------------------- */
	
	/* WPSHOP_SIGNUP_MESSAGE */
	function wpshop_options_validate_WPSHOP_SIGNUP_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_SIGNUP_MESSAGE_field() {
		$message_id = get_option('WPSHOP_SIGNUP_MESSAGE', 0);
		$options = wpshop_messages::getMessageListOption($message_id);
		echo '<select name="WPSHOP_SIGNUP_MESSAGE" class="chosen_select">'.$options.'</textarea><a href="#" title="'.__('This is the content of the signup confirmation message','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_WPSHOP_SIGNUP_MESSAGE($input) {return $input;}
	
	/* WPSHOP_SIGNUP_MESSAGE */
	function wpshop_options_validate_WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_ORDER_CONFIRMATION_MESSAGE_field() {
		$message_id = get_option('WPSHOP_ORDER_CONFIRMATION_MESSAGE', 0);
		$options = wpshop_messages::getMessageListOption($message_id);
		echo '<select name="WPSHOP_ORDER_CONFIRMATION_MESSAGE" class="chosen_select">'.$options.'</textarea><a href="#" title="'.__('This is the content of the order confirmation message','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_WPSHOP_ORDER_CONFIRMATION_MESSAGE($input) {return $input;}
	
	/* WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE */
	function wpshop_options_validate_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_field() {
		$message_id = get_option('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', 0);
		$options = wpshop_messages::getMessageListOption($message_id);
		echo '<select name="WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE" class="chosen_select">'.$options.'</textarea><a href="#" title="'.__('This is the content of the paypal payment confirmation message','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE($input) {return $input;}
	
	/* WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE */
	function wpshop_options_validate_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_field() {
		$message_id = get_option('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', 0);
		$options = wpshop_messages::getMessageListOption($message_id);
		echo '<select name="WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE" class="chosen_select">'.$options.'</textarea><a href="#" title="'.__('This is the content of the others payments confirmation message','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE($input) {return $input;}
	
	/* WPSHOP_SHIPPING_CONFIRMATION_MESSAGE */
	function wpshop_options_validate_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_field() {
		$message_id = get_option('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', 0);
		$options = wpshop_messages::getMessageListOption($message_id);
		echo '<select name="WPSHOP_SHIPPING_CONFIRMATION_MESSAGE" class="chosen_select">'.$options.'</textarea><a href="#" title="'.__('This is the content of the shipping confirmation message','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE($input) {return $input;}

}