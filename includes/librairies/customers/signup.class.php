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
		$output = '';

		$user_id = get_current_user_id();

		if($user_id) {
			wpshop_tools::wpshop_safe_redirect(get_permalink(get_option('wpshop_myaccount_page_id')));
			$output .= __('Your are already registered','wpshop');
		}
		else {
			$output .= '<div id="reponseBox"></div>';
			$output .= '<form  method="post" id="register_form" action="' . admin_url('admin-ajax.php') . '">';
				$output .= '<input type="hidden" name="wpshop_ajax_nonce" value="' . wp_create_nonce('wpshop_customer_register') . '" />';
				$output .= '<input type="hidden" name="action" value="wpshop_save_customer_account" />';
				// Bloc REGISTER
				$output .= '<div class="col1 wpshopShow" id="register_form_classic">';
				$wpshop_account->display_account_form();
				$output .= '<input type="submit" name="submitOrderInfos" value="'.__('Create my account','wpshop').'" />';
				$output .= '</div>';
			$output .= '</form>';
		}

		return $output;
	}
}