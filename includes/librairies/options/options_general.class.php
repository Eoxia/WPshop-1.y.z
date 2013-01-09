<?php

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
	function declare_options(){

		add_settings_section('wpshop_pages_option', __('WPShop pages configuration', 'wpshop'), array('wpshop_general_options', 'plugin_section_text'), 'wpshop_pages_option');
		$page_list = unserialize(WPSHOP_DEFAULT_PAGES);
		foreach ( $page_list['presentation'] as $page_def) {
			register_setting('wpshop_options', $page_def['page_code'], array('wpshop_general_options', 'wpshop_options_validate_wpshop_shop_pages'));
			add_settings_field($page_def['page_code'], __($page_def['post_title'], 'wpshop'), array('wpshop_general_options', 'wpshop_shop_pages'), 'wpshop_pages_option', 'wpshop_pages_option', array('code' => $page_def['page_code']));
		}

		if(WPSHOP_DEFINED_SHOP_TYPE == 'sale'){
			foreach ( $page_list['sale'] as $page_def ) {
				register_setting('wpshop_options', $page_def['page_code'], array('wpshop_general_options', 'wpshop_options_validate_wpshop_shop_pages'));
				add_settings_field($page_def['page_code'], __($page_def['post_title'], 'wpshop'), array('wpshop_general_options', 'wpshop_shop_pages'), 'wpshop_pages_option', 'wpshop_pages_option', array('code' => $page_def['page_code']));
			}
		}


		add_settings_section('wpshop_general_config', __('Shop main configuration', 'wpshop'), array('wpshop_general_options', 'plugin_section_text'), 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_shop_type', array('wpshop_general_options', 'wpshop_options_validate_wpshop_shop_type'));
			add_settings_field('wpshop_shop_type', __('Shop type', 'wpshop'), array('wpshop_general_options', 'wpshop_shop_type'), 'wpshop_general_config', 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_shop_default_currency', array('wpshop_general_options', 'wpshop_options_validate_default_currency'));
			add_settings_field('wpshop_shop_default_currency', __('Currency', 'wpshop'), array('wpshop_general_options', 'wpshop_shop_default_currency_field'), 'wpshop_general_config', 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_shop_price_piloting', array('wpshop_general_options', 'wpshop_options_validate_price_piloting'));
			add_settings_field('wpshop_shop_price_piloting', __('Price piloting for the shop', 'wpshop'), array('wpshop_general_options', 'wpshop_shop_price_piloting_field'), 'wpshop_general_config', 'wpshop_general_config');
	}

	// Common section description
	function plugin_section_text() {
		echo '';
	}

	/*	Default currecy for the entire shop	*/
	function wpshop_shop_default_currency_field() {
		$wpshop_shop_currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$current_currency = get_option('wpshop_shop_default_currency');

		$currencies_options = '';
		foreach($wpshop_shop_currencies as $k => $v) {
			$currencies_options .= '<option value="'.$k.'"'.(($k==$current_currency) ? ' selected="selected"' : null).'>'.$k.' ('.$v.')</option>';
		}
		echo '<select name="wpshop_shop_default_currency">'.$currencies_options.'</select>
		<a href="#" title="'.__('This is the currency the shop will use','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_default_currency($input) {
		return $input;
	}

	/**
	 * Define if the price is piloted by the ATI or TAX FREE
	 */
	function wpshop_shop_price_piloting_field() {
		$wpshop_price_piloting_types = array('HT', 'TTC');
		$current_piloting = get_option('wpshop_shop_price_piloting', WPSHOP_PRODUCT_PRICE_PILOT);

		$piloting_options = '';
		foreach($wpshop_price_piloting_types as $price_type) {
			$piloting_options .= '<option value="'.$price_type.'"'.(($price_type==$current_piloting) ? ' selected="selected"' : null).'>'.$price_type.'</option>';
		}
		echo '<select name="wpshop_shop_price_piloting">'.$piloting_options.'</select>
		<a href="#" title="'.__('You can choose if the price you will enter in each product is the \'all tax include\' price or the \'tax free price\'','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_price_piloting($input) {
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
			if ( ($price_pilot_attribute_code == WPSHOP_PRODUCT_PRICE_TTC) && ($attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position'] > $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position']) ) {
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'position' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position']), array('id' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['id']));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'position' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position']), array('id' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['id']));
			}
			elseif ( ($price_pilot_attribute_code == WPSHOP_PRODUCT_PRICE_HT) && ($attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position'] > $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position']) ) {
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'position' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['position']), array('id' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['id']));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'position' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_HT]['position']), array('id' => $attribute_price_def[WPSHOP_PRODUCT_PRICE_TTC]['id']));
			}
		endforeach;


		return $input;
	}

	/*	Shop type definition	*/
	function wpshop_shop_type() {
		$shop_types = unserialize(WPSHOP_SHOP_TYPES);
		$shop_types_options = '';
		foreach($shop_types as $type) {
			$shop_types_options .= '<option value="'.$type.'"'.(($type==WPSHOP_DEFINED_SHOP_TYPE) ? ' selected="selected"' : null).'>'.__($type, 'wpshop').'</option>';
		}
		echo '<select name="wpshop_shop_type">'.$shop_types_options.'</select><input type="hidden" name="old_wpshop_shop_type" value="'.WPSHOP_DEFINED_SHOP_TYPE.'" />
		<a href="#" title="'.__('Define if you have a shop to sale item or just for item showing','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}


	function wpshop_options_validate_wpshop_shop_type($input) {
		global $current_db_version;
		$current_db_version['installation_state'] = 'completed';
		update_option('wpshop_db_options', $current_db_version);
		if($input=='sale'){
			wpshop_install::wpshop_insert_default_pages();
		}
		return $input;
	}

	/*	Shop pages configurations	*/
	function wpshop_shop_pages($args) {
		$content = '';

		$current_page_id = get_option($args['code'], '');
		$post_list = get_pages();
		if (!empty($post_list)) {
			$content .= '<select name="' . $args['code'] . '" class="chosen_select" ><option value="" >' . __('Choose a page to associate', 'wpshop') . '</option>';
			$p=1;
			$error = false;
			foreach ($post_list as $post) {
				$selected = (!empty($current_page_id) && ($current_page_id == $post->ID)) ? ' selected="selected"' : '';
				$content .= '<option'.$selected.' value="' . $post->ID . '" >' . $post->post_title . '</option>';
			}
			$content .= '</select>';
		}
		wp_reset_query();

		echo $content;
	}

	/**
	 *
	 * @param unknown_type $input
	 * @return unknown
	 */
	function wpshop_options_validate_wpshop_shop_pages($input) {
		return $input;
	}

}