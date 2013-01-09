<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Payment options management
*
* Define the different method to manage the different payment options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different payment options
* @package wpshop
* @subpackage librairies
*/
class wpshop_payment_options
{

	/**
	*
	*/
	function declare_options(){
		add_settings_section('wpshop_paymentMethod', __('Payment method', 'wpshop'), array('wpshop_payment_options', 'plugin_section_text'), 'wpshop_paymentMethod');
			register_setting('wpshop_options', 'wpshop_paymentMethod', array('wpshop_payment_options', 'wpshop_options_validate_paymentMethod'));

			add_settings_field('wpshop_payment_paypal', __('Paypal', 'wpshop'), array('wpshop_payment_options', 'wpshop_paypal_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
			add_settings_field('wpshop_company_member_of_a_approved_management_center', '', array('wpshop_payment_options', 'wpshop_company_member_of_a_approved_management_center_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
			add_settings_field('wpshop_payment_checks', __('Checks', 'wpshop'), array('wpshop_payment_options', 'wpshop_checks_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
			$options = get_option('wpshop_paymentMethod');
			if(WPSHOP_PAYMENT_METHOD_CIC || !empty($options['cic'])) add_settings_field('wpshop_payment_cic', __('CIC payment', 'wpshop'), array('wpshop_payment_options', 'wpshop_cic_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');

			register_setting('wpshop_options', 'wpshop_paymentAddress', array('wpshop_payment_options', 'wpshop_options_validate_paymentAddress'));
			register_setting('wpshop_options', 'wpshop_paypalEmail', array('wpshop_payment_options', 'wpshop_options_validate_paypalEmail'));
			register_setting('wpshop_options', 'wpshop_paypalMode', array('wpshop_payment_options', 'wpshop_options_validate_paypalMode'));
			if(WPSHOP_PAYMENT_METHOD_CIC || !empty($options['cic'])) register_setting('wpshop_options', 'wpshop_cmcic_params', array('wpshop_payment_options', 'wpshop_options_validate_cmcic_params'));
			register_setting('wpshop_options', 'wpshop_payment_return_url', array('wpshop_payment_options', 'wpshop_options_validate_return_url'));

		add_settings_section('wpshop_payment_main_info', __('Payment information', 'wpshop'), array('wpshop_payment_options', 'plugin_section_text'), 'wpshop_payment_main_info');
			add_settings_field('wpshop_payment_return', __('Payment return url', 'wpshop'), array('wpshop_payment_options', 'wpshop_payment_return_field'), 'wpshop_payment_main_info', 'wpshop_payment_main_info');
	}

	// Common section description
	function plugin_section_text() {
		echo '';
	}

	/* -------------------------------- */
	/* --------- PAYMENT METHOD ------- */
	/* -------------------------------- */
	function wpshop_paymentByPaypal_field() {
		echo '';
	}
	function wpshop_payment_return_field() {
		$default_url = get_permalink(get_option('wpshop_payment_return_page_id'));
		$url = get_option('wpshop_payment_return_url',$default_url);
		echo '<input name="wpshop_payment_return_url" type="text" value="'.(!empty($url)?$url:$default_url).'" />
		<a href="#" title="'.__('This page is use in order to notify the customer that its order has been recorded or cancelled.','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}

	function wpshop_paypal_field() {
		$options = get_option('wpshop_paymentMethod');
		$paypalEmail = get_option('wpshop_paypalEmail');
		$paypalMode = get_option('wpshop_paypalMode',0);

		echo '
<input type="checkbox" name="wpshop_paymentMethod[paypal]" id="paymentByPaypal" '.(!empty($options['paypal'])?'checked="checked"':null).' />&nbsp;<label for="paymentByPaypal" >'.__('Activate this payment method', 'wpshop').'</label>
<div class="wpshop_payment_method_parameter paymentByPaypal_content" >
	<label class="simple_right">'.__('Business email','wpshop').'</label> <input name="wpshop_paypalEmail" type="text" value="'.$paypalEmail.'" /><br />
	<label class="simple_right">'.__('Mode','wpshop').'</label>
	<select name="wpshop_paypalMode">
		<option value="normal"'.(($paypalMode=='sandbox') ? null : ' selected="selected"').'>'.__('Production mode','wpshop').'</option>
		<option value="sandbox"'.(($paypalMode=='sandbox') ? ' selected="selected"' : null).'>'.__('Sandbox mode','wpshop').'</option>
	</select>
	<a href="#" title="'.__('This checkbox allow to use Paypal in Sandbox mode (test) or production mode (real money)','wpshop').'" class="wpshop_infobulle_marker">?</a>
</div>';
	}
	function wpshop_checks_field() {
		$options = get_option('wpshop_paymentMethod');
		$company_payment = get_option('wpshop_paymentAddress');
		$company = get_option('wpshop_company_info');

		echo '<input name="wpshop_company_info[company_member_of_a_approved_management_center]" id="company_is_member_of_management_center" type="checkbox"'.(!empty($company['company_member_of_a_approved_management_center'])?' checked="checked"':null).' />&nbsp;<label for="company_is_member_of_management_center" >'.__('Member of an approved management center, accepting as such payments by check.', 'wpshop').'</label><a href="#" title="'.__('Is your company member of a approved management center ? Will appear in invocies.','wpshop').'" class="wpshop_infobulle_marker">?</a><br class="clear" />';
		echo '<input type="checkbox" name="wpshop_paymentMethod[checks]" id="paymentByCheck" '.(!empty($options['checks'])?'checked="checked"':null).' />&nbsp;<label for="paymentByCheck" >'.__('Activate this payment method', 'wpshop').'</label><a href="#" title="'.__('Checks will be sent to address you have to type below','wpshop').'" class="wpshop_infobulle_marker">?</a><br />';
		echo '
<div class="wpshop_payment_method_parameter paymentByCheck_content" >
	<label class="simple_right">'.__('Company name', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_name]" type="text" value="'.(!empty($company_payment['company_name'])?$company_payment['company_name']:'').'" /><br />
	<label class="simple_right">'.__('Street', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_street]" type="text" value="'.(!empty($company_payment['company_street'])?$company_payment['company_street']:'').'" /><br />
	<label class="simple_right">'.__('Postcode', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_postcode]" type="text" value="'.(!empty($company_payment['company_postcode'])?$company_payment['company_postcode']:'').'" /><br />
	<label class="simple_right">'.__('City', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_city]" type="text" value="'.(!empty($company_payment['company_city'])?$company_payment['company_city']:'').'" /><br />
	<label class="simple_right">'.__('Country', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_country]" type="text" value="'.(!empty($company_payment['company_country'])?$company_payment['company_country']:'').'" />
</div>';
	}

	function wpshop_cic_field(){
		$options = get_option('wpshop_paymentMethod');
		$cmcic_params = get_option('wpshop_cmcic_params', array());

		echo '
<input type="checkbox" name="wpshop_paymentMethod[cic]" id="paymentByCreditCard_CIC" '.(!empty($options['cic'])?'checked="checked"':null).' /><label for="paymentByCreditCard_CIC" >'.__('Activate this payment method', 'wpshop').'</label>
<div class="wpshop_payment_method_parameter paymentByCreditCard_CIC_content" >
	<label class="simple_right">'.__('Key', 'wpshop').'</label> <input name="wpshop_cmcic_params[cle]" type="text" value="'.$cmcic_params['cle'].'" /><br />
	<label class="simple_right">'.__('TPE', 'wpshop').'</label> <input name="wpshop_cmcic_params[tpe]" type="text" value="'.$cmcic_params['tpe'].'" /><br />
	<label class="simple_right">'.__('Version', 'wpshop').'</label> <input name="wpshop_cmcic_params[version]" type="text" value="'.$cmcic_params['version'].'" /> => 3.0<br />
	<label class="simple_right">'.__('Serveur', 'wpshop').'</label> <input name="wpshop_cmcic_params[serveur]" type="text" value="'.$cmcic_params['serveur'].'" /><br />
	<label class="simple_right">'.__('Company code', 'wpshop').'</label> <input name="wpshop_cmcic_params[codesociete]" type="text" value="'.$cmcic_params['codesociete'].'" /><br />
</div>';
		// <label class="simple_right">'.__('URL success', 'wpshop').'</label> <input name="wpshop_cmcic_params[urlok]" type="text" value="'.$cmcic_params['urlok'].'" /><br />
		// <label class="simple_right">'.__('URL cancel', 'wpshop').'</label> <input name="wpshop_cmcic_params[urlko]" type="text" value="'.$cmcic_params['urlko'].'" />
	}

	function wpshop_company_member_of_a_approved_management_center_field() {
	}

	/* Processing */
	function wpshop_options_validate_paymentMethod($input) {
		$input['paypal'] = !empty($input['paypal']) && ($input['paypal']=='on');
		$input['checks'] = !empty($input['checks']) && ($input['checks']=='on');
		$input['cic'] = !empty($input['cic']) && ($input['cic']=='on');
		if(isset($input['company_member_of_a_approved_management_center']) && $input['company_member_of_a_approved_management_center']=='on') {
			$input['company_member_of_a_approved_management_center'] = 1;
		}
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paymentAddress($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paypalEmail($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paypalMode($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_cmcic_params($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_return_url($input) {
		return $input;
	}

}