<?php if ( !defined( 'ABSPATH' ) ) exit;
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
	public static function sale_shop_notice() {
		$plug_version = substr( WPSHOP_VERSION, 0, 5 );


		$notice_display_user_option = get_user_meta( get_current_user_id(), '_wps_hide_notice_messages_indicator', true);

		$notice = '';
		$messages_to_hide = '';
		/* Check that the user has already choose a payment method */
		$paymentMethod = get_option( 'wps_payment_mode' );
		$no_payment_mode_configurated = true;
		if ( !empty($paymentMethod ) && !empty($paymentMethod['mode']) ) {
			foreach( $paymentMethod['mode'] as $k => $pm ) {
				if ( !empty($pm['active'] ) ) {
					$no_payment_mode_configurated = false;
				}
			}
		}

		if( $no_payment_mode_configurated ) {
			$notice .= '<li>' . __('Payment method are missing', 'wpshop') . '&nbsp;<a href="' . admin_url('options-general.php?page='.WPSHOP_URL_SLUG_OPTION.'#wpshop_payments_option') . '" class="button-primary wpshop_missing_parameters_button" >' . __('Choose a payment method', 'wpshop') . '</a></li>';
		}

		/* Check that the user has already choose a payment method */
		$emails = get_option('wpshop_emails', array());
		if(empty($emails)) {
			$notice .= '<li>' . __('Shop emails are misssing', 'wpshop') . '&nbsp;<a href="' . admin_url('options-general.php?page='.WPSHOP_URL_SLUG_OPTION.'#wpshop_emails_option') . '" class="button-primary wpshop_missing_parameters_button" >' . __('Configure shop emails', 'wpshop') . '</a></li>';
		}


		/*$current_theme_option = get_option( 'current_theme' );
		if ( !empty($cuurent_theme_option) && $cuurent_theme_option == 'SlickShop mini' && ( empty($notice_display_user_option) || !array_key_exists('SLICKSHOP', $notice_display_user_option) ) )  {
			$notice .= '<li>' .__('Some changes on templates files have been made on WPSHOP 1.3.6.3. You must download Slickshop on <a href="http://www.wpshop.fr/myaccount/">your account on WPSHOP.FR</a>', 'wpshop').'</li>';
			$messages_to_hide .= 'SLICKSHOP,';
		}*/

		$install = !empty( $_GET[ 'install' ] ) ? sanitize_text_field( $_GET[ 'install' ] ) : '';

		if(!empty($notice) && ( empty( $install ) ) ) {
			$notice='<p>'.__('You configure your shop to be a sale shop. But some configuration are missing for this type of shop using', 'wpshop').'</p><ul>'.$notice.'</ul>';
			if ( !empty($messages_to_hide) ) {
				$notice .= '<button data-nonce="' . wp_create_nonce( 'wps_hide_notice_messages' ) . '" class="wps_hide_notice_message button-secondary" id="wps_hide_notice_message">' .__('Hide this message', 'wpshop'). '</button>';
				$notice .= '<input type="hidden" id="hide_messages_indicator" value="' .$messages_to_hide. '"/>';
			}
			self::admin_notice_container($notice, 'wpshop_shop_sale_type_notice');
		}
	}

	/**		*/
	public static function admin_notice_container($message, $container_class = ''){
		?>
		<div class="updated wpshop_admin_notice <?php echo $container_class; ?>" id="<?php echo $container_class; ?>" >
			<h3><?php _e('Configure my shop', 'wpshop') ?></h3>
			<?php echo $message; ?>
		</div>
		<?php
	}
}
