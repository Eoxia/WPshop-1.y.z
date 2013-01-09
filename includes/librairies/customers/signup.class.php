<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Checkout
 * 
 * The WPShop checkout class handles the checkout process, collecting user data and processing the payment.
 *
 * @class 		wpwhop_checkout
 * @package		WPShop
 * @category	Class
 * @author		Eoxia
 */

/* Instantiate the class from the shortcode */
function wpshop_signup_init() {
	global $wpshop_signup;
	$wpshop_signup = new wpshop_signup();
	$wpshop_signup->display_form();
}

/** Signup management */
class wpshop_signup {
	
	var $creating_account = true;
	
	/** Constructor of the class
	* @return void
	*/
	function __construct () {
	}
	
	/** Affiche le formulaire de commande
	* @return void
	*/
	function display_form() {
	
		global $wpshop, $wpshop_account;
			
		$user_id = get_current_user_id();	
			
		if($user_id) {
			wpshop_tools::wpshop_safe_redirect(get_permalink(get_option('wpshop_myaccount_page_id')));
			echo __('Your are already registered','wpshop');
		}
		else {
			echo '<div id="reponseBox"></div>';
			echo '<form  method="post" id="register_form" action="'.WPSHOP_AJAX_FILE_URL.'">';
				echo '<input type="hidden" name="post" value="true" />';
				echo '<input type="hidden" name="elementCode" value="ajax_register" />';
				// Bloc REGISTER
				echo '<div class="col1 wpshopShow" id="register_form_classic">';
				$wpshop_account->display_billing_and_shipping_form_field();
				echo '<input type="submit" name="submitOrderInfos" value="'.__('Take order','wpshop').'"" />';
				echo '</div>';
			echo '</form>';
		}
	}
}