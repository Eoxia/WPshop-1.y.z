<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Payment options management
 *
 * Define the different method to manage the different payment options
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 * @package wpshop
 * @subpackage librairies
 */

/**	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Define the different method to manage the different payment options
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_payment_options {

	/**
	 *
	 */
	public static function declare_options() {

		$options = get_option('wpshop_paymentMethod');
		add_settings_section('wpshop_paymentMethod', '<span class="dashicons dashicons-admin-settings"></span>'.__('Payment method', 'wpshop'), array('wpshop_payment_options', 'plugin_section_text'), 'wpshop_paymentMethod');

		register_setting('wpshop_options', 'wpshop_paymentMethod', array('wpshop_payment_options', 'wpshop_options_validate_default_payment_method'));
		register_setting('wpshop_options', 'wpshop_paymentMethod_options', array('wpshop_payment_options', 'wpshop_options_validate_payment_method_options'));
		register_setting('wpshop_options', 'wpshop_paymentAddress', array('wpshop_payment_options', 'wpshop_options_validate_paymentAddress'));
		register_setting('wpshop_options', 'wpshop_paypalEmail', array('wpshop_payment_options', 'wpshop_options_validate_paypalEmail'));
		register_setting('wpshop_options', 'wpshop_paypalMode', array('wpshop_payment_options', 'wpshop_options_validate_paypalMode'));
		if(WPSHOP_PAYMENT_METHOD_CIC || !empty($options['cic'])) register_setting('wpshop_options', 'wpshop_cmcic_params', array('wpshop_payment_options', 'wpshop_options_validate_cmcic_params'));

		register_setting('wpshop_options', 'wpshop_payment_partial', array('wpshop_payment_options', 'partial_payment_saver'));
		add_settings_section('wpshop_payment_partial_on_command', '<span class="dashicons dashicons-clipboard"></span>'.__('Partial payment', 'wpshop'), array('wpshop_payment_options', 'partial_payment_explanation'), 'wpshop_payment_partial_on_command');
		add_settings_field('wpshop_payment_partial', '', array('wpshop_payment_options', 'partial_payment'), 'wpshop_payment_partial_on_command', 'wpshop_payment_partial_on_command');
		
		register_setting('wpshop_options', 'wpshop_send_invoice', array('wpshop_payment_options', 'allow_send_invoice_saver'));
		add_settings_section('wpshop_send_invoice_section','<span class="dashicons dashicons-email-alt"></span>'. __('Allow WPShop to send invoices', 'wpshop'), array(), 'wpshop_payment_partial_on_command');
		add_settings_field('wpshop_send_invoice', '', array('wpshop_payment_options', 'wpshop_send_invoice'), 'wpshop_payment_partial_on_command', 'wpshop_send_invoice_section');
	}

	// Common section description
	public static function plugin_section_text() {
		echo '';
	}

	function wpshop_company_member_of_a_approved_management_center_field() {
	}

	/* Processing */
	public static function wpshop_options_validate_paymentMethod($input) {
		foreach ($input as $k => $i) {
			if ( $k != 'default_method' && !is_array($i) ) {
				$input[$k] = !empty($input[$k]) && ($input[$k]=='on');
			}
		}
		return $input;
	}
	/* Processing */
	public static function wpshop_options_validate_payment_method_options($input) {
		return $input;
	}
	/* Processing */
	public static function wpshop_options_validate_paymentAddress($input) {
		return $input;
	}
	/* Processing */
	public static function wpshop_options_validate_paypalEmail($input) {
		return $input;
	}
	/* Processing */
	public static function wpshop_options_validate_paypalMode($input) {
		return $input;
	}
	/* Processing */
	public static function wpshop_options_validate_cmcic_params($input) {
		return $input;
	}

	public static function allow_send_invoice_saver( $input ) {
		return $input;
	}

	
	
	/**
	 * Partial payment explanation part
	 */
	public static function partial_payment_explanation() {
		_e('You can define if customer have to pay the complete amount of order or if they just have to pay a part on command and the rest later', 'wpshop');
	}
	/**
	 * Save options for partial payment. For specific treatment on choosen value, do it here
	 *
	 * @param array $input The different input sent through $_POST
	 * @return array The different values to save for current option
	 */
	public static function partial_payment_saver($input) {
		return $input;
	}
	/**
	 * Partial payment configuration area display
	 */
	public static function partial_payment() {
		$output = '';

		$partial_payment_current_config = get_option('wpshop_payment_partial', array('for_all' => array()));

		$partial_for_all_is_activate = false;
		if ( !empty($partial_payment_current_config) && !empty($partial_payment_current_config['for_all']) && !empty($partial_payment_current_config['for_all']['activate']) ) {
			$partial_for_all_is_activate = true;
		}

		$output .= '
<input type="checkbox" name="wpshop_payment_partial[for_all][activate]"' . ($partial_for_all_is_activate ? ' checked="checked"' : '') . ' id="wpshop_payment_partial_on_command_activation_state" /> <label for="wpshop_payment_partial_on_command_activation_state" >' . __('Activate partial command for all order', 'wpshop') . '</label><a href="#" title="'.__('If you want that customer pay a part o f total amount of there order, check this box then fill fields below','wpshop').'" class="wpshop_infobulle_marker">?</a>
<div class="wpshop_partial_payment_config_container' . ($partial_for_all_is_activate ? '' : ' wpshopHide') . '" id="wpshop_partial_payment_config_container" >
	<div class="alignleft" >
		' . __('Value of partial payment', 'wpshop') . '<br/>
		<input type="text" value="' . (!empty($partial_payment_current_config) && !empty($partial_payment_current_config['for_all']) && !empty($partial_payment_current_config['for_all']['value']) ? $partial_payment_current_config['for_all']['value'] : '') . '" name="wpshop_payment_partial[for_all][value]" />
	</div>
	<div class="" >
		' . __('Type of partial payment', 'wpshop') . '<br/>
		<select name="wpshop_payment_partial[for_all][type]" >
			<option value="percentage"' . (!empty($partial_payment_current_config) && !empty($partial_payment_current_config['for_all']) && (empty($partial_payment_current_config['for_all']['type']) || $partial_payment_current_config['for_all']['type'] == 'percentage') ? ' selected="selected"' : '') . ' >' . __('%', 'wpshop') . '</option>
			<option value="amount"' . (!empty($partial_payment_current_config) && !empty($partial_payment_current_config['for_all']) && !empty($partial_payment_current_config['for_all']['type']) && ($partial_payment_current_config['for_all']['type'] == 'amount') ? ' selected="selected"' : '') . ' >' . wpshop_tools::wpshop_get_currency() . '</option>
		</select>
	</div>
</div>';

		echo $output;
	}

	
	public static function wpshop_send_invoice() {
		$send_invoice_option = get_option( 'wpshop_send_invoice' );
		$output = '<input type="checkbox" id="wpshop_send_invoice" name="wpshop_send_invoice" ' . ( ( !empty($send_invoice_option) ) ? 'checked="checked"' : '' ) . '/>';
		$output .= ' <label for="wpshop_send_invoice">' .__('Allow WPshop to send invoice in PDF format when order is completed', 'wpshop' ). '</label>';
		echo $output;
	}
	
	public static function wpshop_options_validate_default_payment_method ($input) {
		return $input;
	}

}