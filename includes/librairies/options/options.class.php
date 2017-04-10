<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Plugin option manager
*
* Define the different method to manage the different options into the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different options into the plugin
* @package wpshop
* @subpackage librairies
*/

/** Stocke les erreurs de saisies */
$options_errors = array();
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_general.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_pages.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_email.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_company.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_payment.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_shipping.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_advanced.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_addons.class.php');

class wpshop_options {

	/**
	 * Declare the different groups/subgroups for wpshop options. Execute a filter in order to accept ption from modules/addons
	 *
	 * @return array A list with all options groups and subgroup to create. All option fields are defined in each module/addons
	 */
	public static function declare_options_groups() {
		$groups = array();

		$groups['wpshop_general_option'] =
			array(	'label' => __('General', 'wpshop'),
					'subgroups' => array(
						'wpshop_general_config' => array('class' => 'wpshop_admin_box_options_general'),
						'wpshop_company_info' => array('class' => 'wpshop_admin_box_options_company'),
					),
			);
		$groups['wpshop_catalog_option'] =
			array(	'label' => __('Catalog', 'wpshop'),
					'subgroups' => array(
						'wpshop_catalog_product_option' => array('class' => ' wpshop_admin_box_options_product'),
						'wpshop_catalog_main_option' => array('class' => ' wpshop_admin_box_options_catalog'),
						'wpshop_catalog_categories_option' => array('class' => ' wpshop_admin_box_options_category'),
					),
			);
		$groups['wpshop_pages_option'] =
			array(	'label' => __('Pages', 'wpshop'),
					'subgroups' => array(
						'wpshop_pages_option' => array('class' => ' wpshop_admin_box_options_pages'),
					),
			);
		$groups['wpshop_display_option'] =
			array(	'label' => __('Display', 'wpshop'),
					'subgroups' => array(
						'wpshop_display_option' => array('class' => ' wpshop_admin_box_options_display'),
						'wpshop_customize_display_option' => array('class' => ' wpshop_admin_box_options_display'),
						'wpshop_admin_display_option' => array('class' => ' wpshop_admin_box_options_admin_display'),
					),
			);
		$groups['wpshop_emails_option'] =
			array(	'label' => __('Emails', 'wpshop'),
					'subgroups' => array(
						'wpshop_emails' => array('class' => ' wpshop_admin_box_options_email'),
						'wpshop_messages' => array('class' => ' wpshop_admin_box_options_message'),
					),
			);

		$wpshop_shop_type = !empty( $_POST['wpshop_shop_type'] ) ? sanitize_text_field( $_POST['wpshop_shop_type'] ) : '';

		/**	Some options are available only when sale mode is active	*/
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($wpshop_shop_type) || (isset($wpshop_shop_type) && ($wpshop_shop_type != 'presentation'))) :
			$groups['wpshop_cart_option'] =
				array(	'label' => __('Cart', 'wpshop'),
						'subgroups' => array(
							'wpshop_cart_info' => array('class' => ' wpshop_admin_box_options_cart'),
						),
				);
			$groups['wpshop_payments_option'] =
				array(	'label' => __('Payments', 'wpshop'),
						'subgroups' => array(
							'wpshop_paymentMethod' => array('class' => ' wpshop_admin_box_options_payment_method'),
							'wpshop_payment_partial_on_command' => array('class' => ' wpshop_admin_box_options_payment_partial'),
						),
				);
			$groups['wpshop_shipping_option'] =
				array(	'label' => __('Shipping', 'wpshop'),
						'subgroups' => array(
							'wpshop_shipping_rules' => array('class' => ' wpshop_admin_box_options_shipping_rules')
						),
				);
		endif;

		$groups['wpshop_addons_option'] =
			array(	'label' => __('Addons', 'wpshop'),
					'subgroups' => array(
						'wpshop_addons_options' => array('class' => ' wpshop_admin_box_options_addons'),
					),
			);

		$groups['wpshop_advanced_options'] =
			array(	'label' => __('Advanced options', 'wpshop'),
					'subgroups' => array(
						'wpshop_extra_options' => array('class' => ' wpshop_admin_box_options_advanced'),
					),
			);

		/**	Allows modules and addons to add options to existing list	*/
		$groups = apply_filters('wpshop_options', $groups);

		return $groups;
	}

	/**
	 * Display the main option page. Read all groups/subgroups and options fields defined in wpshop core and modules/addons
	 */
	public static function option_main_page() {
		global $options_errors;
		$tpl_component = array();

		$options_list = wpshop_options::declare_options_groups();
		ob_start();
		settings_fields('wpshop_options');
		$tpl_component['ADMIN_OPTIONS_FIELDS_FOR_NONCE'] = ob_get_contents();
		ob_end_clean();
		$tpl_component['ADMIN_OPTIONS_TAB_LIST'] = '';
		$tpl_component['ADMIN_OPTIONS_TAB_CONTENT_LIST'] = '';
		if ( !empty($options_list) ) {
			foreach ( $options_list as $group_key => $group_content) {
				$sub_tpl_component = array();
				if ( !empty($group_content['subgroups']) && is_array($group_content['subgroups']) ) {
					$sub_tpl_component['ADMIN_OPTIONS_GROUP_CONTENT'] = '';
					$sub_tpl_component['ADMIN_OPTIONS_TAB_KEY'] = $group_key;
					$sub_tpl_component['ADMIN_OPTIONS_TAB_LABEL'] = ( !empty($group_content['label']) ) ? $group_content['label'] : '';
					$tpl_component['ADMIN_OPTIONS_TAB_LIST'] .= wpshop_display::display_template_element('wpshop_admin_options_group_tab', $sub_tpl_component, array(), 'admin');
					foreach ( $group_content['subgroups'] as $subgroup_key => $subgroup_def) {
						ob_start();
						do_settings_sections( $subgroup_key );
						$sub_tpl_component['ADMIN_OPTIONS_SUBGROUP_CONTENT'] = ob_get_contents();
						ob_end_clean();
						$sub_tpl_component['ADMIN_OPTIONS_SUBGROUP_CLASS'] = $subgroup_def['class'];
						$sub_tpl_component['ADMIN_OPTIONS_GROUP_CONTENT'] .= wpshop_display::display_template_element('wpshop_admin_options_subgroup_container', $sub_tpl_component, array(), 'admin');
					}
					$tpl_component['ADMIN_OPTIONS_TAB_CONTENT_LIST'] .= wpshop_display::display_template_element('wpshop_admin_options_group_container', $sub_tpl_component, array(), 'admin');
				}
			}
		}

		echo wpshop_display::display_template_element('wpshop_admin_options_main_page', $tpl_component, array(), 'admin');
	}


	/**
	*	Declare the different options for the plugin
	*/
	public static function add_options(){
		global $wpshop_display_option;


		/*Catalog - Main	*/
		register_setting('wpshop_options', 'wpshop_catalog_main_option', array('wpshop_options', 'wpshop_options_validate_catalog_main_option'));
			add_settings_section('wpshop_catalog_main_section', '<span class="dashicons dashicons-category"></span>'.__('Catalog', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_catalog_main_option');
				add_settings_field('wpshop_catalog_empty_price_behaviour', __('Empty price', 'wpshop'), array('wpshop_options', 'wpshop_catalog_empty_price_behaviour'), 'wpshop_catalog_main_option', 'wpshop_catalog_main_section');
		/* Catalog - Product */
		register_setting('wpshop_options', 'wpshop_catalog_product_option', array('wpshop_options', 'wpshop_options_validate_catalog_product_option'));
			add_settings_section('wpshop_catalog_product_section', '<span class="dashicons dashicons-archive"></span>'.__('Products', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_catalog_product_option');
				add_settings_field('wpshop_catalog_product_slug', __('Products common rewrite param', 'wpshop'), array('wpshop_options', 'wpshop_catalog_product_slug_field'), 'wpshop_catalog_product_option', 'wpshop_catalog_product_section');
		/* Catalog - Categories */
		register_setting('wpshop_options', 'wpshop_catalog_categories_option', array('wpshop_options', 'wpshop_options_validate_catalog_categories_option'));
		add_settings_section('wpshop_catalog_categories_section', '<span class="dashicons dashicons-portfolio"></span>'.__('Categories', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_catalog_categories_option');
		add_settings_field('wpshop_catalog_categories_slug', __('Categories common rewrite param', 'wpshop'), array('wpshop_options', 'wpshop_catalog_categories_slug_field'), 'wpshop_catalog_categories_option', 'wpshop_catalog_categories_section');
		add_settings_field('wpshop_catalog_no_category_slug', __('Default category slug for unassociated product', 'wpshop'), array('wpshop_options', 'wpshop_catalog_no_category_slug_field'), 'wpshop_catalog_categories_option', 'wpshop_catalog_categories_section');

		/* General option */
		wpshop_general_options::declare_options();

		/* Company */
		wpshop_company_options::declare_options();

		/* Payments */
		$wpshop_shop_type = !empty( $_POST['wpshop_shop_type'] ) ? sanitize_text_field( $_POST['wpshop_shop_type'] ) : '';
		$old_wpshop_shop_type = !empty( $_POST['old_wpshop_shop_type'] ) ? sanitize_text_field( $_POST['old_wpshop_shop_type'] ) : '';
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($wpshop_shop_type) || (isset($wpshop_shop_type) && ($wpshop_shop_type != 'presentation')) && !isset($old_wpshop_shop_type) || (isset($old_wpshop_shop_type) && ($old_wpshop_shop_type != 'presentation'))){
			wpshop_payment_options::declare_options();
		}

		/* Cart */
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($wpshop_shop_type) || (isset($wpshop_shop_type) && ($wpshop_shop_type != 'presentation')) && !isset($old_wpshop_shop_type) || (isset($old_wpshop_shop_type) && ($old_wpshop_shop_type != 'presentation'))){
			register_setting('wpshop_options', 'wpshop_cart_option', array('wpshop_options', 'wpshop_options_validate_cart'));
			add_settings_section('wpshop_cart_info', '<span class="dashicons dashicons-cart"></span>'.__('Cart', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_cart_info');
			add_settings_field('wpshop_cart_product_added_behaviour', __('Action when produt is added succesfully into cart', 'wpshop'), array('wpshop_options', 'wpshop_cart_product_added_behaviour_field'), 'wpshop_cart_info', 'wpshop_cart_info');
			add_settings_field('wpshop_cart_product_added_to_quotation_behaviour', __('Action when produt is added succesfully into a quotation', 'wpshop'), array('wpshop_options', 'wpshop_cart_product_added_to_quotation_behaviour_field'), 'wpshop_cart_info', 'wpshop_cart_info');
			add_settings_field('wpshop_cart_total_item_nb', __('Only a limited number of products in cart', 'wpshop'), array('wpshop_options', 'wpshop_cart_total_item_nb_field'), 'wpshop_cart_info', 'wpshop_cart_info');
			//add_settings_field('wpshop_cart_same_item_nb', __('Number of same item allowed into cart', 'wpshop'), array('wpshop_options', 'wpshop_cart_same_item_nb_field'), 'wpshop_cart_info', 'wpshop_cart_info');
			register_setting('wpshop_options', 'wpshop_catalog_product_option', array('wpshop_options', 'wpshop_catalog_product_variation_option_validate'));
			add_settings_field('wpshop_catalog_product_option', __('Variation product display options for all products', 'wpshop'), array('wpshop_options', 'wpshop_catalog_varition_product_field'), 'wpshop_catalog_product_option', 'wpshop_catalog_product_section');
			do_action('wsphop_options');
		}

		do_action('wsphop_options');

		/* Pages */
		wpshop_page_options::declare_options();

		/* Emails */
		wpshop_email_options::declare_options();

		/* Addons */
		$wpshop_addons_settings = new wpshop_addons_settings();
		$wpshop_addons_settings->declare_options();

		/* Advanced Settings */
		$wpshop_advanced_settings = new wpshop_advanced_settings();
		$wpshop_advanced_settings->declare_options();

		/* Shipping section */
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($wpshop_shop_type) || (isset($wpshop_shop_type) && ($wpshop_shop_type != 'presentation')) && !isset($old_wpshop_shop_type) || (isset($old_wpshop_shop_type) && ($old_wpshop_shop_type != 'presentation'))){
		$wpshop_shipping_options = new wpshop_shipping_options();
			$wpshop_shipping_options->declare_options();
		}
	}

	// Common section description
	public static function plugin_section_text() {
		echo '';
	}

	/* ------------------------------ */
	/* --------- CATALOG INFO ------- */
	/* ------------------------------ */
	public static function wpshop_catalog_empty_price_behaviour() {
		$options = get_option('wpshop_catalog_main_option');
		echo '<input type="checkbox"' . (!empty($options['wpshop_catalog_empty_price_behaviour']) ? ' checked="checked" ' : '') . ' value="yes" name="wpshop_catalog_main_option[wpshop_catalog_empty_price_behaviour]" id="wpshop_catalog_empty_price_behaviour" /> <label for="wpshop_catalog_empty_price_behaviour" >' . __('Hide price and add to cart button when price is empty or equal to 0', 'wpshop') . '</label>';
	}
	public static function wpshop_catalog_product_slug_field(){
		$options = get_option('wpshop_catalog_product_option');
		$catalog_cat_options = get_option('wpshop_catalog_categories_option');
		echo '<input type="checkbox"' . (!empty($options['wpshop_catalog_product_slug_with_category']) ? ' checked="checked" ' : '') . ' value="yes" name="wpshop_catalog_product_option[wpshop_catalog_product_slug_with_category]" id="wpshop_catalog_product_slug_with_category" /> <label for="wpshop_catalog_product_slug_with_category">' . __('Use product category in url', 'wpshop') . '</label><br/>
		<div class="alignleft" >' . site_url('/') . '</div>
		<div class="alignleft wpshop_options_catalog_product_rewrite" ><input type="text" name="wpshop_catalog_product_option[wpshop_catalog_product_slug]" value="' . (!empty($options['wpshop_catalog_product_slug']) ? $options['wpshop_catalog_product_slug'] : WPSHOP_CATALOG_PRODUCT_SLUG) . '" /></div>
		<div class="alignleft wpshop_options_catalog_product_rewrite" ><span class="wpshop_catalog_product_slug_category' . (empty($options['wpshop_catalog_product_slug_with_category']) ? ' disable' : '') . '" >/' . (!empty($catalog_cat_options['wpshop_catalog_categories_slug']) ? $catalog_cat_options['wpshop_catalog_categories_slug'] : WPSHOP_CATALOG_CATEGORIES_SLUG) . '</span></div>
		<div class="alignleft wpshop_options_catalog_product_rewrite" >/' . __('Your_product_slug', 'wpshop') . '</div>
		<div class="alignleft" ><a href="#" title="'.__('This slug will be used in url to describe products page','wpshop').'" class="wpshop_infobulle_marker">?</a></div><br /><br />
		<div><span style="color: red;" class="dashicons dashicons-megaphone"></span> '.__('"/" permit to disable slug of products (<b>but don\'t work with rewrites plugins</b>)','wpshop').'</div>';
	}
	public static function wpshop_catalog_categories_slug_field(){
		$options = get_option('wpshop_catalog_categories_option');
		echo '<input type="text" name="wpshop_catalog_categories_option[wpshop_catalog_categories_slug]" value="' . (!empty($options['wpshop_catalog_categories_slug']) ? $options['wpshop_catalog_categories_slug'] : WPSHOP_CATALOG_CATEGORIES_SLUG) . '" />
		<a href="#" title="'.__('This slug will be used in url to describe catagories page','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	public static function wpshop_catalog_no_category_slug_field(){
		$options = get_option('wpshop_catalog_categories_option');
		echo '<input type="text" name="wpshop_catalog_categories_option[wpshop_catalog_no_category_slug]" value="' . (!empty($options['wpshop_catalog_no_category_slug']) ? $options['wpshop_catalog_no_category_slug'] : WPSHOP_CATALOG_PRODUCT_NO_CATEGORY) . '" />
		<a href="#" title="'.__('This slug will be used for products not being related to any category ','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}

	/* Processing */
	public static function wpshop_options_validate_catalog_product_option($input){
		foreach($input as $option_key => $option_value){
			switch($option_key){
				default:
					$new_input[$option_key] = $option_value;
				break;
			}
		}

		return $new_input;
	}
	public static function wpshop_options_validate_catalog_categories_option($input){
		foreach($input as $option_key => $option_value){
			switch($option_key){
				default:
					$new_input[$option_key] = $option_value;
				break;
			}
		}

		return $new_input;
	}
	public static function wpshop_options_validate_catalog_main_option($input){
		$new_input = $input;
		if ( !empty($input) && is_array( $input ) ) {
			foreach($input as $option_key => $option_value){
				switch($option_key){
					default:
						$new_input[$option_key] = $option_value;
						break;
				}
			}
		}
		flush_rewrite_rules();
		return $new_input;
	}

	public static function wpshop_catalog_varition_product_field () {
		$catalog_product_option = get_option('wpshop_catalog_product_option');
		$output  = '<input type="checkbox" name="wpshop_catalog_product_option[price_display][text_from]" id="wpshop_catalog_product_option_price_display_text_from" ' .( ( !empty($catalog_product_option) && !empty($catalog_product_option['price_display']) && !empty($catalog_product_option['price_display']['text_from']) ) ? 'checked="checked"' : '' ). ' /> ';
		$output .= '<label for="wpshop_catalog_product_option_price_display_text_from">'. __('Display "price from" before basic price of product', 'wpshop').'</label><br/>';
		$output .= '<input type="checkbox" name="wpshop_catalog_product_option[price_display][lower_price]" id="wpshop_catalog_product_option_price_display_lower_price" ' .( ( !empty($catalog_product_option) && !empty($catalog_product_option['price_display']) && !empty($catalog_product_option['price_display']['lower_price']) ) ? 'checked="checked"' : '' ). ' /> ';
		$output .= '<label for="wpshop_catalog_product_option_price_display_lower_price">'. __('Display the lowest price of variation', 'wpshop').'</label>';
		echo $output;
	}

	public static function wpshop_catalog_product_variation_option_validate ($input) {
		return $input;
	}

	/* ------------------------- */
	/* --------- CART ------- */
	/* ------------------------- */
	public static function wpshop_cart_total_item_nb_field() {
		$cart_option = get_option('wpshop_cart_option', array());
		$output = '';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_total_nb_of_item_allowed';
		$input_def['type'] = 'text';
		$input_def['value'] = !empty($cart_option['total_nb_of_item_allowed']) ? $cart_option['total_nb_of_item_allowed'][0] : '';
		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[total_nb_of_item_allowed]') . '<a href="#" title="'.__('This value count all quantities in cart. Example : 2 products A + 3 products B = 5 products','wpshop').'" class="wpshop_infobulle_marker">?</a>';

		echo $output;
	}
	function wpshop_cart_same_item_nb_field() {
		$cart_option = get_option('wpshop_cart_option', 0);
		$output = '';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_nb_of_same_item_allowed';
		$input_def['type'] = 'text';
		$input_def['value'] = $cart_option['total_nb_of_same_item_allowed'][0];
		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[total_nb_of_same_item_allowed]') . '<a href="#" title="'.__('Empty for no restriction','wpshop').'" class="wpshop_infobulle_marker">?</a>';

		echo $output;
	}
	public static function wpshop_cart_product_added_behaviour_field() {
		$cart_option = get_option('wpshop_cart_option', array('dialog_msg'));
		$output = '';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_action_after_product_added_to_cart';
		$input_def['type'] = 'radio';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = $cart_option['product_added_to_cart'];
		$input_def['possible_value'] = array('dialog_msg' => __('Display the dialog allowing to choose between continue shopping or go to cart', 'wpshop'), 'cart_page' => __('Automaticaly send user to cart page', 'wpshop'));
		$input_def['options_label']['original'] = true;
		$input_def['options_label']['container'] = true;
		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[product_added_to_cart]');

		$hide = ( (!empty($cart_option) && !empty($cart_option['product_added_to_cart'][0]) && $cart_option['product_added_to_cart'][0] == 'cart_page') ? 'wpshopHide' : null);
		$output .= '<div id="wpshop_cart_option_animation_cart_type" class="' .$hide. '"><label for="wpshop_cart_option[animation_cart_type]">' .__('Cart animation type', 'wpshop'). '</label>';
		$output .= '<select name="wpshop_cart_option[animation_cart_type]" id="wpshop_cart_option[animation_cart_type]">';
		$output .= '<option value="pop-in" ' .( ( !empty($cart_option['animation_cart_type']) && $cart_option['animation_cart_type'] == 'pop-in') ? 'selected="selected"' : null). '>' .__('Dialog box', 'wpshop'). '</option>';
		$output .= '<option value="animation" ' .( ( !empty($cart_option['animation_cart_type']) && $cart_option['animation_cart_type'] == 'animation') ? 'selected="selected"' : null). '>' .__('Image animation', 'wpshop'). '</option>';
		$output .= '</select></div>';

		echo $output;


	}


	public static function wpshop_cart_product_added_to_quotation_behaviour_field() {
		$cart_option = get_option('wpshop_cart_option', array('dialog_msg'));
		$output = '';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_action_after_product_added_to_quotation';
		$input_def['type'] = 'radio';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = ( !empty($cart_option['product_added_to_quotation']) ? $cart_option['product_added_to_quotation'] : null );
		$input_def['possible_value'] = array('dialog_msg' => __('Display the dialog allowing to choose between continue shopping or go to cart', 'wpshop'), 'cart_page' => __('Automaticaly send user to cart page', 'wpshop'));
		$input_def['options_label']['original'] = true;
		$input_def['options_label']['container'] = true;
		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[product_added_to_quotation]');

		echo $output;
	}
	public static function wpshop_options_validate_cart( $input ) {

		if ( empty( $input ) || empty( $input[ 'display_newsletter' ] ) || empty( $input[ 'display_newsletter' ][ 'partner_subscription' ] ) ) {
			$input[ 'display_newsletter' ][ 'partner_subscription' ] = 'no';
		}

		if ( empty( $input ) || empty( $input[ 'display_newsletter' ] ) || empty( $input[ 'display_newsletter' ][ 'site_subscription' ] ) ) {
			$input[ 'display_newsletter' ][ 'site_subscription' ] = 'no';
		}

		return $input;
	}


}

?>
