<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Shipping options management
*
* Define the different method to manage the different shipping options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different shipping options
* @package wpshop
* @subpackage librairies
*/
class wpshop_shipping_options {
	/**
	*
	*/
	function declare_options(){

		add_settings_section('wpshop_shipping_rules', '<span class="dashicons dashicons-admin-generic"></span>'.__('Shipping general configuration', 'wpshop'), array('wpshop_shipping_options', 'plugin_section_text'), 'wpshop_shipping_rules');
		register_setting('wpshop_options', 'wpshop_shipping_address_choice', array('wpshop_shipping_options', 'wpshop_shipping_address_validator'));
		add_settings_field('wpshop_shipping_address_choice', __('Shipping address choice', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_address_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');

		register_setting('wpshop_options', 'wpshop_shipping_cost_from', array('wpshop_shipping_options', 'wpshop_shipping_cost_from_validator'));
		add_settings_field('wpshop_shipping_cost_from', __('Shipping cost From', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_cost_from_fields'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');


		register_setting('wpshop_options', 'wpshop_limit_country_list', array('wpshop_shipping_options', 'wpshop_limit_country_list_validator'));
		add_settings_field('wpshop_limit_country_list', __('Limit country list', 'wpshop'), array('wpshop_shipping_options', 'wpshop_limit_country_list_fields'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
		
		register_setting('wpshop_options', 'wpshop_country_default_choice', array('wpshop_shipping_options', 'wpshop_country_default_choice_validator'));
		add_settings_field('wpshop_country_default_choice', __('Country default choice in forms', 'wpshop'), array('wpshop_shipping_options', 'wpshop_country_default_choice_fields'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
	}

	// Common section description
	public static function plugin_section_text() {
		echo '';
	}

	public static function wpshop_shipping_address_validator($input){
		$shipping_option = get_option( 'wpshop_shipping_address_choice' );
		if( !empty($shipping_option) && !empty($shipping_option['display_model']) ) {
			$input['display_model'] = $shipping_option['display_model'];
		}

		return $input;
	}

	public static function wpshop_shipping_address_field() {
		global $wpdb;
		$choice = get_option('wpshop_shipping_address_choice', unserialize(WPSHOP_SHOP_CUSTOM_SHIPPING));
		$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_name = "%s" AND post_type = "%s"', WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES);
		$entity_id = $wpdb->get_var($query);

		$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d', $entity_id);
		$content = $wpdb->get_results($query);

		$input_def['name'] = 'wpshop_shipping_address_choice[choice]';
		$input_def['id'] = 'wpshop_shipping_address_choice[choice]';
		$input_def['possible_value'] = $content;
		$input_def['type'] = 'select';
		$input_def['value'] = $choice['choice'];

		$active = !empty($choice['activate']) ? $choice['activate'] : false;
		$display_model = ( !empty($choice['display_model']) ) ? addslashes( serialize( $choice['display_model'] ) ) : '';

		echo '<input type="checkbox" name="wpshop_shipping_address_choice[activate]" id="wpshop_shipping_address_choice[activate]" '.($active ? 'checked="checked"' :null).'/> <label for="wpshop_shipping_address_choice[activate]">'.__('Activate shipping address','wpshop').'</label><br/>
		<div>' .wpshop_form::check_input_type($input_def). '</div>';

	}

	public static function wpshop_shipping_cost_from_fields() {
		$shipping_cost_from_option = get_option('wpshop_shipping_cost_from');
		$output = '<input type="checkbox" id="wpshop_shipping_cost_from" name="wpshop_shipping_cost_from" ' . ( (!empty($shipping_cost_from_option)) ? 'checked="checked"' : '') . ' /> ';
		$output .= '<label for="wpshop_shipping_cost_from">' . __('Display "From" behind Shipping cost in cart while shipping address is undefined', 'wpshop'). '</label>';
		echo $output;
	}

	public static function wpshop_shipping_cost_from_validator( $input ) {


		return $input;
	}

	public static function wpshop_limit_country_list_validator( $input ) {
		return $input;
	}

	public static function wpshop_limit_country_list_fields() {
		$output = '';
		$limit_countries_list = get_option( 'wpshop_limit_country_list' );
		$countries = unserialize(WPSHOP_COUNTRY_LIST);
		if ( !empty ($countries) ) {
			$output .= '<select name="wpshop_limit_country_list[]" class="chosen_select" multiple data-placeholder="Choose a Country">';
			foreach( $countries as $key => $country ) {
				$is_selected = ( !empty($limit_countries_list) && is_array($limit_countries_list) && in_array($key, $limit_countries_list) ) ? true : false;
				$output .= '<option value="' .$key. '" ' . ( ($is_selected) ? 'selected="selected"' : '' ) . '>' .$country. '</option>';
			}
			$output .= '</select>';
		}
		echo  $output;
	}

	public static function wpshop_country_default_choice_fields() {
		$default_country_choice = get_option( 'wpshop_country_default_choice' );
		$output  = '<select name="wpshop_country_default_choice">';
		$countries = unserialize(WPSHOP_COUNTRY_LIST);
		foreach( $countries as $key => $country ) {
			$is_selected = ( !empty($default_country_choice) &&  $key == $default_country_choice ) ? true : false;
			$output .= '<option value="' .$key. '" ' . ( ($is_selected) ? 'selected="selected"' : '' ) . '>' .$country. '</option>';
		}
		$output .= '</select>';
		echo $output;
	}
	
	public static function wpshop_country_default_choice_validator( $input ) {
		return $input;
	}
}































