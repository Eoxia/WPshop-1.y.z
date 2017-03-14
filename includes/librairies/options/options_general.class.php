<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* General options management
*
* Define the different method to manage the different general options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different general options
* @package wpshop
* @subpackage librairies
*/
class wpshop_general_options {

	/**
	*
	*/
	public static function declare_options(){
		$page = !empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if ( isset($page) && ( substr($page, 0, 13 ) == 'wpshop_option' || $page == 'wps-installer' ) ) {
			wp_enqueue_media();
		}
		add_settings_section('wpshop_general_config','<span class="dashicons dashicons-info"></span>'. __('Shop main configuration', 'wpshop'), array('wpshop_general_options', 'plugin_section_text'), 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_shop_type', array('wpshop_general_options', 'wpshop_options_validate_wpshop_shop_type'));
			add_settings_field('wpshop_shop_type', __('Shop type', 'wpshop'), array('wpshop_general_options', 'wpshop_shop_type'), 'wpshop_general_config', 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_shop_default_currency', array('wpshop_general_options', 'wpshop_options_validate_default_currency'));
			add_settings_field('wpshop_shop_default_currency', __('Currency', 'wpshop'), array('wpshop_general_options', 'wpshop_shop_default_currency_field'), 'wpshop_general_config', 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_shop_price_piloting', array('wpshop_general_options', 'wpshop_options_validate_price_piloting'));
			add_settings_field('wpshop_shop_price_piloting', __('Price piloting for the shop', 'wpshop'), array('wpshop_general_options', 'wpshop_shop_price_piloting_field'), 'wpshop_general_config', 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_shop_default_weight_unity', array('wpshop_general_options', 'wpshop_options_validate_default_weight_unity'));
		add_settings_field('wpshop_shop_default_weight_unity', __('Weight unity', 'wpshop'), array('wpshop_general_options', 'wpshop_default_weight_unity_field'), 'wpshop_general_config', 'wpshop_general_config');


		register_setting('wpshop_options', 'wpshop_ga_account_id', array('wpshop_general_options', 'wpshop_options_validate_ga_account_id'));
		add_settings_field('wpshop_ga_account_id', __('Google Analytics Account ID for e-commerce conversion', 'wpshop'), array('wpshop_general_options', 'wpshop_ga_account_id_field'), 'wpshop_general_config', 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_logo', array('wpshop_general_options', 'wpshop_options_validate_logo'));
		add_settings_field('wpshop_logo', __('The logo for emails and invoices', 'wpshop'), array('wpshop_general_options', 'wpshop_logo_field'), 'wpshop_general_config', 'wpshop_general_config');
	}

	// Common section description
	public static function plugin_section_text() {
		echo '';
	}

	/*	Default currecy for the entire shop	*/
	public static function wpshop_shop_default_currency_field() {
		echo wpshop_attributes_unit::wpshop_shop_currency_list_field() . '<a href="#" title="'.__('This is the currency the shop will use','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}

	public static function wpshop_default_weight_unity_field() {
		global $wpdb;

		$weight_group = get_option('wpshop_shop_weight_group');
		$current_weight = get_option('wpshop_shop_default_weight_unity');

		$weight_options = '';
		if ( !empty ($weight_group) ) {
			$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE group_id = %d', $weight_group);
			$weight_units = $wpdb->get_results($query);
			foreach ( $weight_units as $weight_unit) {
				$weight_options .= '<option value="'.$weight_unit->id.'"'.( ($weight_unit->id == $current_weight) ? ' selected="selected"' : null).'>'.$weight_unit->name.' ('.$weight_unit->unit.')</option>';
			}
		}

		echo '<select name="wpshop_shop_default_weight_unity">'.$weight_options.'</select>
		<a href="#" title="'.__('This is the weight unity the shop will use','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}

	public static function wpshop_options_validate_default_currency($input) {
		return $input;
	}

	/**
	 * Define if the price is piloted by the ATI or TAX FREE
	 */
	public static $wpshop_price_piloting_types = array('HT', 'TTC');
	public static function wpshop_shop_price_piloting_field() {
		$current_piloting = get_option('wpshop_shop_price_piloting', WPSHOP_PRODUCT_PRICE_PILOT);

		$piloting_options = '';
		foreach(self::$wpshop_price_piloting_types as $price_type) {
			$piloting_options .= '<option value="'.$price_type.'"'.(($price_type==$current_piloting) ? ' selected="selected"' : null).'>'.$price_type.'</option>';
		}
		echo '<select name="wpshop_shop_price_piloting">'.$piloting_options.'</select>
		<a href="#" title=\''. __('You can choose if the price you will enter in each product is the "all tax include" price or the "tax free price"','wpshop') .'\' class="wpshop_infobulle_marker">?</a>';
	}


	public static function wpshop_ga_account_id_field() {
		$ga_account_id = get_option('wpshop_ga_account_id');
		echo '<input type="text" name="wpshop_ga_account_id" value="'.$ga_account_id.'" />';
	}

	public static function wpshop_options_validate_ga_account_id ($input) {
		return $input;
	}

	public static function wpshop_options_validate_default_weight_unity ($input) {
		return $input;
	}
	public static function wpshop_options_validate_price_piloting($input) {
		global $wpdb;

		$price_pilot_attribute_code = constant('WPSHOP_PRODUCT_PRICE_'.$input);

		$query = $wpdb->prepare(
"SELECT ATTRIBUTE.code, ATTR_SET_SECTION_DETAILS.id, ATTR_SET_SECTION_DETAILS.attribute_group_id, ATTR_SET_SECTION_DETAILS.position
FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTR_SET_SECTION_DETAILS
		INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE ON (ATTRIBUTE.id = ATTR_SET_SECTION_DETAILS.attribute_id)
WHERE ATTRIBUTE.code = %s OR ATTRIBUTE.code = %s
		AND ATTR_SET_SECTION_DETAILS.status = %s", WPSHOP_PRODUCT_PRICE_HT, WPSHOP_PRODUCT_PRICE_TTC, 'valid');
		$attributes_in_set_section = $wpdb->get_results($query);
		$attributes_order = array();
		foreach ($attributes_in_set_section as $attribute) :
			$attributes_order[$attribute->attribute_group_id][$attribute->code]['position'] = $attribute->position;
			$attributes_order[$attribute->attribute_group_id][$attribute->code]['id'] = $attribute->id;
		endforeach;

		$new_def = array();
		foreach ($attributes_order as $attribute_group_id => $attribute_price_def) :
			if ( ($price_pilot_attribute_code == WPSHOP_PRODUCT_PRICE_TTC) && (!empty($attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position']) && !empty($attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position'])) && ($attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position'] > $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position']) ) {
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'position' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position']), array('id' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['id']));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'position' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position']), array('id' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['id']));

				/** Update entries for quick add and variations */
				$wpdb->query('UPDATE ' .WPSHOP_DBT_ATTRIBUTE.' SET is_used_in_quick_add_form = "yes", is_used_in_variation = "yes" WHERE code = "product_price"');
				$wpdb->query('UPDATE ' .WPSHOP_DBT_ATTRIBUTE.' SET is_used_in_quick_add_form = "yes" WHERE code = "tx_tva"');
				$wpdb->query('UPDATE ' .WPSHOP_DBT_ATTRIBUTE.' SET is_used_in_quick_add_form = "no", is_used_in_variation = "no" WHERE code = "price_ht"');
			}
			elseif ( ($price_pilot_attribute_code == WPSHOP_PRODUCT_PRICE_HT) && (!empty($attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position']) && !empty($attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position'])) && ($attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position'] > $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position']) ) {
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'position' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position']), array('id' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['id']));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'position' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position']), array('id' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['id']));

				/** Update entries for quick add and variations */
				$wpdb->query('UPDATE ' .WPSHOP_DBT_ATTRIBUTE.' SET is_used_in_quick_add_form = "yes", is_used_in_variation = "yes" WHERE code = "price_ht"');
				$wpdb->query('UPDATE ' .WPSHOP_DBT_ATTRIBUTE.' SET is_used_in_quick_add_form = "no" WHERE code = "tx_tva"');
				$wpdb->query('UPDATE ' .WPSHOP_DBT_ATTRIBUTE.' SET is_used_in_quick_add_form = "no", is_used_in_variation = "no" WHERE code = "product_price"');
			}
		endforeach;

		return $input;
	}

	/*	Shop type definition	*/
	public static function wpshop_shop_type() {
		$shop_types = unserialize(WPSHOP_SHOP_TYPES);
		$shop_types_options = '';
		foreach($shop_types as $type) {
			$shop_types_options .= '<option value="'.$type.'"'.(($type==WPSHOP_DEFINED_SHOP_TYPE) ? ' selected="selected"' : null).'>'.__($type, 'wpshop').'</option>';
		}
		echo '<select name="wpshop_shop_type">'.$shop_types_options.'</select><input type="hidden" name="old_wpshop_shop_type" value="'.WPSHOP_DEFINED_SHOP_TYPE.'" />
		<a href="#" title="'.__('Define if you have a shop to sale item or just for item showing','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}

	public static function wpshop_logo_field () {
		$logo_option = get_option('wpshop_logo');

		$output  = '<a href="#" id="wps-add-logo-picture" class="wps-bton-first-mini-rounded">' .__( 'Upload your logo', 'wpshop' ). '</a><br/>';
		$output .= '<img id="wpshop_logo_thumbnail" src="' .( ( !empty($logo_option) ) ? $logo_option : WPSHOP_DEFAULT_CATEGORY_PICTURE ). '" alt="Logo" style="height : 40px; width : auto; border : 5px solid #E8E8E8; margin-top : 8px;" />';
		$output .= '<input type="hidden" name="wpshop_logo" id="wpshop_logo_field" value="' .$logo_option. '" />';
		$output .= '<br/><a href="#" id="wps-delete-shop-logo" '.( empty($logo_option) ? 'class="wpshopHide"' : '' ) .'>' . __( 'Delete this logo', 'wpshop' ) . '</a>';

		echo $output;
	}

	public static function wpshop_options_validate_logo ($input) {
		return $input;
	}

	public static function wpshop_options_validate_wpshop_shop_type($input) {
		global $current_db_version;

		$current_installation_step = get_option( 'wps-installation-current-step', 1 );
		if ( WPSINSTALLER_STEPS_COUNT <= $current_installation_step || ( !empty( $current_db_version ) && !empty( $current_db_version[ 'db_version' ] ) && ( 51 < $current_db_version[ 'db_version' ] ) ) ) {
			$current_db_version['installation_state'] = 'completed';
			update_option('wpshop_db_options', $current_db_version);
			if ( $input == 'sale' ) {
				wpshop_install::wpshop_insert_default_pages( $input );
			}
		}
		return $input;
	}

}
