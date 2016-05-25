<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Gestion des addons pour wpshop
*
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Définition des méthodes permettant de gérer les "addons"
*
* @package wpshop
* @subpackage librairies
*/
class wpshop_addons_settings{

	/**
	 * Déclaration des différentes options
	 */
	function declare_options(){
// 		add_settings_section('wpshop_addons_options', __('Wpshop "addons"', 'wpshop'), array('wpshop_advanced_settings', 'plugin_section_text'), 'wpshop_addons_options');
// 		register_setting('wpshop_options', 'wpshop_addons_options', array('wpshop_addons_settings', 'validate_options'));
// 		add_settings_field('wpshop_addons_settings_field', '', array('wpshop_addons_settings', 'addons_definition_fields'), 'wpshop_addons_options', 'wpshop_addons_options');
	}

	/**
	 * Ajout d'un explication pour la page d'option
	 */
	public static function plugin_section_text() {
		_e('This options screen allows you to add additionnal functionnalities', 'wpshop');
	}

	/**
	 *
	 * @param unknown_type $input
	 */
	public static function validate_options($input){

	}

	/**
	 * Définition des champs pour l'activation des addons
	 */
	function addons_definition_fields () {
		$content = '';

		$content .= '<input type="hidden" name="wpshop_ajax_addons_nonce" id="wpshop_ajax_addons_nonce" value="'.wp_create_nonce('wpshop_ajax_activate_addons').'" />';

		$addons_options = get_option('wpshop_addons', array());
		$addons_list = unserialize(WPSHOP_ADDONS_LIST);
		foreach ($addons_list as $addon => $addon_def) {
			$activated_status = false;
			if ( array_key_exists($addon, $addons_options) && ( $addons_options[$addon]['activate'] )) {
				$activated_status = true;
			}
			$activated_string = $activated_status ? __('Activated','wpshop') : __('Desactivated','wpshop');
			$activated_class = unserialize(WPSHOP_ADDONS_STATES_CLASS);
			$content .=  '<strong>' . __($addon_def[0], 'wpshop') . '</strong>: <span class="'.$activated_class[$activated_status].'" id="addon_'.$addon.'_state" >'.$activated_string.'</span>';
			if (!$activated_status) {
				$content .=  ' <input type="text" name="'.$addon.'" id="'.$addon.'" value="" /> <input type="button" name="'.$addon.'_button" id="'.$addon.'_button" class="addons_activating_button button-primary" value="'.__('Activate this addon','wpshop').'" />';
			}
			else {
				$content .= ' <input type="button" name="'.$addon.'_button" id="'.$addon.'_button" class="addons_desactivating_button button-secondary" value="'.__('Desactivate this addon','wpshop').'" />';
			}
			$content .= '<br/>';
		}

		echo $content;
	}

}