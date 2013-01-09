<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Define the different tools for the entire plugin
* 
*	Define the different tools for the entire plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different tools for the entire plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_notices{

	/** Notice the user to install the plugin */
	function install_admin_notice() {
		self::admin_notice_container('<p>' . sprintf(__('Wpshop is now installed. %s','wpshop'), '<p><a href="' . admin_url('options-general.php?page='.WPSHOP_URL_SLUG_OPTION) . '&amp;installation_state=initialized" class="button-primary wpshop-install-button" >' . __('Configure your shop', 'wpshop') . '</a><a href="' . admin_url('admin.php?page='.WPSHOP_URL_SLUG_DASHBOARD.'&amp;ignore_installation=true') . '" class="button-primary wpshop-ignore-install-button" >' . __('Ignore configuration', 'wpshop') . '</a></p>') . '<p>', 'wpshop_install_notice');
	}

	/** Notice the user to install the plugin */
	function sale_shop_notice() {
		$notice = '';
		/* Check that the user has already choose a payment method */
		$paymentMethod = get_option('wpshop_paymentMethod', array());
		if(empty($paymentMethod['paypal']) && empty($paymentMethod['checks']) && empty($paymentMethod['cic'])) {
			$notice .= '<li>' . __('Payment method are missing', 'wpshop') . '&nbsp;<a href="' . admin_url('options-general.php?page='.WPSHOP_URL_SLUG_OPTION.'#wpshop_payments_option') . '" class="button-primary wpshop_missing_parameters_button" >' . __('Choose a payment method', 'wpshop') . '</a></li>';
		}

		/* Check that the user has already choose a payment method */
		$emails = get_option('wpshop_emails', array());
		if(empty($emails)) {
			$notice .= '<li>' . __('Shop emails are misssing', 'wpshop') . '&nbsp;<a href="' . admin_url('options-general.php?page='.WPSHOP_URL_SLUG_OPTION.'#wpshop_emails_option') . '" class="button-primary wpshop_missing_parameters_button" >' . __('Configure shop emails', 'wpshop') . '</a></li>';
		}

		if(!empty($notice)){
			$notice='<p>'.__('You configure your shop to be a sale shop. But some configuration are missing for this type of shop using', 'wpshop').'</p><ul>'.$notice.'</ul>';
			self::admin_notice_container($notice, 'wpshop_shop_sale_type_notice');
		}
	}

	/**		*/
	function admin_notice_container($message, $container_class = ''){
?>
		<div class="updated wpshop_admin_notice <?php echo $container_class; ?>" id="<?php echo $container_class; ?>" >
			<?php echo $message; ?>
		</div>
<?php
	}

}