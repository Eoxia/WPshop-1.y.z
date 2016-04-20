<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Advanced settings management
* 
* Define the different method to manage the different advanced settings
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different advanced settings
* @package wpshop
* @subpackage librairies
*/
class wpshop_advanced_settings{

	/**
	*
	*/
	function declare_options(){
		add_settings_section('wpshop_extra_options', '<span class="dashicons dashicons-carrot"></span>'.__('Advanced configurations', 'wpshop'), array('wpshop_advanced_settings', 'plugin_section_text'), 'wpshop_extra_options');
			register_setting('wpshop_options', 'wpshop_extra_options', array('wpshop_advanced_settings', 'validate_options'));
			add_settings_field('wpshop_advanced_settings_field', '', array('wpshop_advanced_settings', 'advanced_settings_field'), 'wpshop_extra_options', 'wpshop_extra_options');
	}

	/*	Explication pour la page courante	*/
	public static function plugin_section_text() {
		echo __('This options screen allows you to add functionnalities to the plugin by adding some parameters we defined', 'wpshop');
	}

	public static function validate_options($input){
		$new_input = array();
		if ( !empty($input['new']) && !empty($input['new']['key']) && !empty($input['new']['value']) ) {
			$new_input[$input['new']['key']] = $input['new']['value'];
		}
		if ( !empty($input['existing']) ) {
			foreach ( $input['existing'] as $extra_params_to_update ) {
				if ( !empty($extra_params_to_update['key']) && !empty($extra_params_to_update['value']) )
					$new_input[$extra_params_to_update['key']] = $extra_params_to_update['value'];
			}
		}

		return $new_input;
	}

	public static function advanced_settings_field($input){
		/*
		WPSHOP_DEBUG_MODE_ALLOWED_IP
		WPSHOP_DEBUG_MODE
		WPSHOP_DEBUG_MODE_ALLOW_DATA_DELETION
		WPSHOP_DISPLAY_TOOLS_MENU

		WPSHOP_INTERNAL_TYPES_TO_EXCLUDE

		WPSHOP_ATTRIBUTE_VALUE_PER_USER

		WPSHOP_PAYMENT_METHOD_CIC
		*/
		$advanced_settings_output = '
	<!--	DEFINE EXTRA PARAMS WPSHOP_DEBUG_MODE_ALLOWED_IP	WPSHOP_DEBUG_MODE	WPSHOP_DEBUG_MODE_ALLOW_DATA_DELETION	WPSHOP_DISPLAY_TOOLS_MENU	WPSHOP_ATTRIBUTE_VALUE_PER_USER	WPSHOP_INTERNAL_TYPES_TO_EXCLUDE	-->
	<ul>';
		$wpshop_advanced_settings = get_option('wpshop_extra_options', array());

		if ( !empty($wpshop_advanced_settings) ) {
			$i=0;

			foreach($wpshop_advanced_settings as $setting_name => $setting_value){
				$advanced_settings_output .= '<li><div class="alignleft wpshop_advanced_options_container" ><p>'.__('Extra parameter key', 'wpshop').'</p>' . wpshop_form::form_input('wpshop_extra_options[existing]['.$i.'][key]', 'wpshop_extra_options_new_key', $setting_name, 'text') . '</div><div class="alignleft wpshop_advanced_options_container" ><p>'.__('Extra parameter value', 'wpshop').'</p>' . wpshop_form::form_input('wpshop_extra_options[existing]['.$i.'][value]', 'wpshop_extra_options_new_value', $setting_value, 'text') . '</div><div class="wpshop_cls" ></div></li>';
				$i++;
			}
		}

		$advanced_settings_output .= '<li><div class="alignleft wpshop_advanced_options_container" ><p>'.__('New extra parameter key', 'wpshop').'</p>' . wpshop_form::form_input('wpshop_extra_options[new][key]', 'wpshop_extra_options_new_key', '', 'text') . '</div><div class="alignleft wpshop_advanced_options_container" ><p>'.__('New extra parameter value', 'wpshop').'</p>' . wpshop_form::form_input('wpshop_extra_options[new][value]', 'wpshop_extra_options_new_value', '', 'text') . '</div><div class="wpshop_cls" ></div></li>
	</ul>';

		echo $advanced_settings_output;
	}

}