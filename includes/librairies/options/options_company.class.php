<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Company options management
* 
* Define the different method to manage the different company options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different company options
* @package wpshop
* @subpackage librairies
*/
class wpshop_company_options
{
	/**
	*
	*/
	function declare_options(){
		add_settings_section('wpshop_company_info', __('Company info', 'wpshop'), array('wpshop_company_options', 'plugin_section_text'), 'wpshop_company_info');
			register_setting('wpshop_options', 'wpshop_company_info', array('wpshop_company_options', 'wpshop_options_validate_company_info'));
			add_settings_field('wpshop_company_legal_statut', __('Legal status', 'wpshop'), array('wpshop_company_options', 'wpshop_company_legal_statut_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_capital', __('Capital', 'wpshop'), array('wpshop_company_options', 'wpshop_company_capital_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_name', __('Company name', 'wpshop'), array('wpshop_company_options', 'wpshop_company_name_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_street', __('Street', 'wpshop'), array('wpshop_company_options', 'wpshop_company_street_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_postcode', __('Postcode', 'wpshop'), array('wpshop_company_options', 'wpshop_company_postcode_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_city', __('City', 'wpshop'), array('wpshop_company_options', 'wpshop_company_city_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_country', __('Country', 'wpshop'), array('wpshop_company_options', 'wpshop_company_country_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_tva_intra', __('TVA Intracommunautaire', 'wpshop'), array('wpshop_company_options', 'wpshop_company_tva_intra_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_telephone', __('Phone', 'wpshop'), array('wpshop_company_options', 'wpshop_company_phone_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_rcs', __('RCS', 'wpshop'), array('wpshop_company_options', 'wpshop_company_rcs_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_siret', __('SIRET', 'wpshop'), array('wpshop_company_options', 'wpshop_company_siret_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_siren', __('SIREN', 'wpshop'), array('wpshop_company_options', 'wpshop_company_siren_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_fax', __('Fax', 'wpshop'), array('wpshop_company_options', 'wpshop_company_fax_field'), 'wpshop_company_info', 'wpshop_company_info');
	}

	/**/
	function plugin_section_text(){
		
	}

	/* ------------------------------ */
	/* --------- COMPANY INFO ------- */
	/* ------------------------------ */
	function wpshop_company_legal_statut_field() {
		$options = get_option('wpshop_company_info');
		
		$legal_status = array(
			'autoentrepreneur' => 'Auto-Entrepreneur',
			'eurl' => 'EURL',
			'sarl' => 'SARL',
			'sa' => 'SA',
			'sas' => 'SAS',
		);
		$select_legal_statut = '<select name="wpshop_company_info[company_legal_statut]">';
		foreach($legal_status as $key=>$value) {
			$selected = $options['company_legal_statut']==$key ? ' selected="selected"' : null;
			$select_legal_statut .= '<option value="'.$key.'"'.$selected.'>'.__($value,'wpshop').'</option>';
		}
		$select_legal_statut .= '</select>';
		$select_legal_statut .= ' <a href="#" title="'.__('Legal status will appear in invoices','wpshop').'" class="wpshop_infobulle_marker">?</a>';
		echo $select_legal_statut;
	}
	function wpshop_company_capital_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_capital]" type="text" value="'.$options['company_capital'].'" /> 
		<a href="#" title="'.__('Capital of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_name_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_name]" type="text" value="'.$options['company_name'].'" />
		<a href="#" title="'.__('Name of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_street_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_street]" type="text" value="'.$options['company_street'].'" />
		<a href="#" title="'.__('Street of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_postcode_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_postcode]" type="text" value="'.$options['company_postcode'].'" />
		<a href="#" title="'.__('Postcode of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_city_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_city]" type="text" value="'.$options['company_city'].'" />
		<a href="#" title="'.__('The city in which your company is based','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_country_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_country]" type="text" value="'.$options['company_country'].'" />
		<a href="#" title="'.__('Country of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_tva_intra_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_tva_intra]" type="text" value="'.$options['company_tva_intra'].'" />
		<a href="#" title="'.__('Intracommunity VAT of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_phone_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_phone]" type="text" value="'.$options['company_phone'].'" />
		<a href="#" title="'.__('Phone number of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_rcs_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_rcs]" type="text" value="'.$options['company_rcs'].'" />
		<a href="#" title="'.__('RCS of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_siret_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_siret]" type="text" value="'.$options['company_siret'].'" />
		<a href="#" title="'.__('SIRET of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_siren_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_siren]" type="text" value="'.$options['company_siren'].'" />
		<a href="#" title="'.__('SIREN of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_company_fax_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_fax]" type="text" value="'.$options['company_fax'].'" />
		<a href="#" title="'.__('Fax number of your company','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}

	/* Processing */
	function wpshop_options_validate_company_info($input) {
		return $input;
	}
	
}