<?php

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
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_display.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_email.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_company.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_payment.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_shipping.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_advanced.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'options/options_addons.class.php');

class wpshop_options {
	/**
	*	Declare the different options for the plugin
	*/
	function add_options(){
		global $wpshop_display_option;

		/* Display	*/
		wpshop_display_options::declare_options();

		/* Catalog - Product */
		register_setting('wpshop_options', 'wpshop_catalog_product_option', array('wpshop_options', 'wpshop_options_validate_catalog_product_option'));
			add_settings_section('wpshop_catalog_product_section', __('Products', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_catalog_product_option');
				add_settings_field('wpshop_catalog_product_slug', __('Products common rewrite param', 'wpshop'), array('wpshop_options', 'wpshop_catalog_product_slug_field'), 'wpshop_catalog_product_option', 'wpshop_catalog_product_section');
		/* Catalog - Categories */
		register_setting('wpshop_options', 'wpshop_catalog_categories_option', array('wpshop_options', 'wpshop_options_validate_catalog_categories_option'));
			add_settings_section('wpshop_catalog_categories_section', __('Categories', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_catalog_categories_option');
				add_settings_field('wpshop_catalog_categories_slug', __('Categories common rewrite param', 'wpshop'), array('wpshop_options', 'wpshop_catalog_categories_slug_field'), 'wpshop_catalog_categories_option', 'wpshop_catalog_categories_section');
				add_settings_field('wpshop_catalog_no_category_slug', __('Default category slug for unassociated product', 'wpshop'), array('wpshop_options', 'wpshop_catalog_no_category_slug_field'), 'wpshop_catalog_categories_option', 'wpshop_catalog_categories_section');

		/* General option */
		wpshop_general_options::declare_options();

		/* Company */
		wpshop_company_options::declare_options();

		/* Payments */
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation')) && !isset($_POST['old_wpshop_shop_type']) || (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation'))){
			wpshop_payment_options::declare_options();
		}

		/* Cart */
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation')) && !isset($_POST['old_wpshop_shop_type']) || (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation'))){
			register_setting('wpshop_options', 'wpshop_cart_option', array('wpshop_options', 'wpshop_options_validate_cart'));
				add_settings_section('wpshop_cart_info', __('Cart', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_cart_info');
					add_settings_field('wpshop_cart_product_added_behaviour', __('Action when produt is added succesfully into cart', 'wpshop'), array('wpshop_options', 'wpshop_cart_product_added_behaviour_field'), 'wpshop_cart_info', 'wpshop_cart_info');
					add_settings_field('wpshop_cart_product_added_to_quotation_behaviour', __('Action when produt is added succesfully into a quotation', 'wpshop'), array('wpshop_options', 'wpshop_cart_product_added_to_quotation_behaviour_field'), 'wpshop_cart_info', 'wpshop_cart_info');
					add_settings_field('wpshop_cart_total_item_nb', __('Allow only one product into cart', 'wpshop'), array('wpshop_options', 'wpshop_cart_total_item_nb_field'), 'wpshop_cart_info', 'wpshop_cart_info');
// 					add_settings_field('wpshop_cart_same_item_nb', __('Number of same item allowed into cart', 'wpshop'), array('wpshop_options', 'wpshop_cart_same_item_nb_field'), 'wpshop_cart_info', 'wpshop_cart_info');
		}

		/* Billing */
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation')) && !isset($_POST['old_wpshop_shop_type']) || (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation'))){
			register_setting('wpshop_options', 'wpshop_billing_number_figures', array('wpshop_options', 'wpshop_options_validate_billing_number_figures'));
				add_settings_section('wpshop_billing_info', __('Billing settings', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_billing_info');
					add_settings_field('wpshop_billing_number_figures', __('Number of figures', 'wpshop'), array('wpshop_options', 'wpshop_billing_number_figures_field'), 'wpshop_billing_info', 'wpshop_billing_info');

			register_setting('wpshop_options', 'wpshop_billing_address', array('wpshop_options', 'wpshop_billing_address_validator'));
				add_settings_field('wpshop_billing_address_choice', __('Billing address choice', 'wpshop'), array('wpshop_options', 'wpshop_billing_address_choice_field'), 'wpshop_billing_info', 'wpshop_billing_info');
				add_settings_field('wpshop_billing_address_include_into_register', '', array('wpshop_options', 'wpshop_billing_address_include_into_register_field'), 'wpshop_billing_info', 'wpshop_billing_info');
		}

		/* Emails */
		wpshop_email_options::declare_options();

		/* Addons */
		wpshop_addons_settings::declare_options();

		/* Advanced Settings */
		wpshop_advanced_settings::declare_options();

		/* Shipping section */
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation')) && !isset($_POST['old_wpshop_shop_type']) || (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation'))){
			wpshop_shipping_options::declare_options();
		}

		flush_rewrite_rules();
	}

	// Common section description
	function plugin_section_text() {
		echo '';
	}

	/* ------------------------------ */
	/* --------- CATALOG INFO ------- */
	/* ------------------------------ */
	function wpshop_catalog_product_slug_field(){
		$options = get_option('wpshop_catalog_product_option');
		echo '<input type="text" name="wpshop_catalog_product_option[wpshop_catalog_product_slug]" value="' . (!empty($options['wpshop_catalog_product_slug']) ? $options['wpshop_catalog_product_slug'] : WPSHOP_CATALOG_PRODUCT_SLUG) . '" />
		<a href="#" title="'.__('This slug will be used in url to describe products page','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_catalog_categories_slug_field(){
		$options = get_option('wpshop_catalog_categories_option');
		echo '<input type="text" name="wpshop_catalog_categories_option[wpshop_catalog_categories_slug]" value="' . (!empty($options['wpshop_catalog_categories_slug']) ? $options['wpshop_catalog_categories_slug'] : WPSHOP_CATALOG_CATEGORIES_SLUG) . '" />
		<a href="#" title="'.__('This slug will be used in url to describe catagories page','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_catalog_no_category_slug_field(){
		$options = get_option('wpshop_catalog_categories_option');
		echo '<input type="text" name="wpshop_catalog_categories_option[wpshop_catalog_no_category_slug]" value="' . (!empty($options['wpshop_catalog_no_category_slug']) ? $options['wpshop_catalog_no_category_slug'] : WPSHOP_CATALOG_PRODUCT_NO_CATEGORY) . '" />
		<a href="#" title="'.__('This slug will be used for products not being related to any category ','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	/* Processing */
	function wpshop_options_validate_catalog_product_option($input){
		foreach($input as $option_key => $option_value){
			switch($option_key){
				default:
					$new_input[$option_key] = $option_value;
				break;
			}
		}

		return $new_input;
	}
	function wpshop_options_validate_catalog_categories_option($input){
		foreach($input as $option_key => $option_value){
			switch($option_key){
				default:
					$new_input[$option_key] = $option_value;
				break;
			}
		}

		return $new_input;
	}


	/* ------------------------- */
	/* --------- CART ------- */
	/* ------------------------- */
	function wpshop_cart_total_item_nb_field() {
		$cart_option = get_option('wpshop_cart_option', array());
		$output = '';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_total_nb_of_item_allowed';
		$input_def['type'] = 'checkbox';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = !empty($cart_option) ? $cart_option['total_nb_of_item_allowed'][0] : '';
		$input_def['possible_value'] = 'yes';
		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[total_nb_of_item_allowed]') . '<a href="#" title="'.__('Check this box if you want to allow the user to add only one product into cart','wpshop').'" class="wpshop_infobulle_marker">?</a>';

// 		$input_def = array();
// 		$input_def['name'] = '';
// 		$input_def['id'] = 'wpshop_cart_option_total_nb_of_item_allowed';
// 		$input_def['type'] = 'text';
// 		$input_def['value'] = $cart_option['total_nb_of_item_allowed'][0];
// 		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[total_nb_of_item_allowed]') . '<a href="#" title="'.__('Empty for no restriction','wpshop').'" class="wpshop_infobulle_marker">?</a>';

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
	function wpshop_cart_product_added_behaviour_field() {
		$cart_option = get_option('wpshop_cart_option', array('dialog_msg'));
		$output = '';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_action_after_product_added_to_cart';
		$input_def['type'] = 'radio';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = $cart_option['product_added_to_cart'];
		$input_def['possible_value'] = array('dialog_msg' => __('Display the dialog allowing to choose between continue shopping or go to cart', 'wpshop'), 'cart_page' => __('Automaticaly send user to cart page', 'wpshop'));
		$input_def['options']['label']['original'] = true;
		$input_def['options']['label']['container'] = true;
		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[product_added_to_cart]');

		echo $output;
	}
	function wpshop_cart_product_added_to_quotation_behaviour_field() {
		$cart_option = get_option('wpshop_cart_option', array('dialog_msg'));
		$output = '';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_action_after_product_added_to_quotation';
		$input_def['type'] = 'radio';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = $cart_option['product_added_to_quotation'];
		$input_def['possible_value'] = array('dialog_msg' => __('Display the dialog allowing to choose between continue shopping or go to cart', 'wpshop'), 'cart_page' => __('Automaticaly send user to cart page', 'wpshop'));
		$input_def['options']['label']['original'] = true;
		$input_def['options']['label']['container'] = true;
		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[product_added_to_quotation]');

		echo $output;
	}
	function wpshop_options_validate_cart( $input ) {
		return $input;
	}


	/* ------------------------- */
	/* --------- BILLING ------- */
	/* ------------------------- */
	function wpshop_billing_number_figures_field() {
		$wpshop_billing_number_figures = get_option('wpshop_billing_number_figures');
		$readonly = !empty($wpshop_billing_number_figures) ? 'readonly="readonly"': null;
		if(empty($wpshop_billing_number_figures)) $wpshop_billing_number_figures=5;

		echo '<input name="wpshop_billing_number_figures" type="text" value="'.$wpshop_billing_number_figures.'" '.$readonly.' />
		<a href="#" title="'.__('Number of figures to make appear on invoices','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_billing_number_figures($input) {return $input;}
	function wpshop_billing_address_validator($input){
		return $input;
	}
	function wpshop_billing_address_choice_field() {
		global $wpdb;
		$output = '';

		$wpshop_billing_address = get_option('wpshop_billing_address');

		$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_name = "' .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. '" AND post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES. '"', '');
		$entity_id = $wpdb->get_var($query);

		$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = ' .$entity_id. '', '');
		$content = $wpdb->get_results($query);

		/*	Field for billing address type choice	*/
		$input_def['name'] = 'wpshop_billing_address[choice]';
		$input_def['id'] = 'wpshop_billing_address_choice';
		$input_def['possible_value'] = $content;
		$input_def['type'] = 'select';
		$input_def['value'] = $wpshop_billing_address['choice'];
		$output .= '<div>' .wpshop_form::check_input_type($input_def). '</div>';

		/*	Field for integrate billign form into register form	*/
		$input_def = array();
		$input_def['name'] = 'wpshop_billing_address[integrate_into_register_form]';
		$input_def['id'] = 'wpshop_billing_address_integrate_into_register_form';
		$input_def['possible_value'] = array( 'yes' => __('Integrate billing form into register form', 'wpshop') );
		$input_def['valueToPut'] = 'index';
		$input_def['options']['label']['original'] = true;
		$input_def['type'] = 'checkbox';
		$input_def['value'] = array( $wpshop_billing_address['integrate_into_register_form'] );
		$output .= '
<div class="wpshop_include_billing_form_into_register_container" >
	' .wpshop_form::check_input_type($input_def). '
	<input type="hidden" name="wpshop_ajax_integrate_billin_into_register" id="wpshop_ajax_integrate_billin_into_register" value="' . wp_create_nonce('wpshop_ajax_integrate_billin_into_register') . '" />
	<input type="hidden" name="wpshop_include_billing_form_into_register_where_value" id="wpshop_include_billing_form_into_register_where_value" value="' . $wpshop_billing_address['integrate_into_register_form_after_field'] . '" />
	<div class="wpshop_include_billing_form_into_register_where" ></div>
</div>';


		echo $output;
	}
	function wpshop_billing_address_include_into_register_field() {

	}


	/**
	*
	*/
	function option_main_page(){
		global $options_errors;
		/* <div class="wrap"> */
?>
			<div id="icon-options-general" class="icon32"></div>
			<h2><?php echo __('WP-Shop options', 'wpshop'); ?></h2>

			<div id="options-tabs" class="wpshop_tabs wpshop_full_page_tabs wpshop_options_tabs" >
				<ul>
					<li><a href="#wpshop_general_option"><?php echo __('General', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_catalog_option"><?php echo __('Catalog', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_pages_option"><?php echo __('Pages', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_display_option"><?php echo __('Display', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_emails_option"><?php echo __('Emails', 'wpshop'); ?></a></li>
					<?php if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation'))) : ?>
					<li><a href="#wpshop_cart_option"><?php echo __('Cart', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_billing_option"><?php echo __('Billing', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_payments_option"><?php echo __('Payments', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_shipping_option"><?php echo __('Shipping', 'wpshop'); ?></a></li>
					<?php endif; ?>
					<li><a href="#wpshop_addons_option"><?php echo __('Addons', 'wpshop'); ?></a></li>
					<li class="wpshop_advanced_options <?php echo current_user_can('wpshop_view_advanced_options') ? '' : 'wpshopHide' ; ?>" ><a href="#wpshop_advanced_option"><?php echo __('Advanced', 'wpshop'); ?></a></li>
				</ul>

				<form action="options.php" method="post" id="wpshop_option_form" >
					<?php settings_fields('wpshop_options'); ?>

					<div id="wpshop_general_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_general"><?php do_settings_sections('wpshop_general_config'); ?></div>
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_company"><?php do_settings_sections('wpshop_company_info'); ?></div>
					</div>

					<div id="wpshop_catalog_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_product"><?php do_settings_sections('wpshop_catalog_product_option'); ?></div>
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_category"><?php do_settings_sections('wpshop_catalog_categories_option'); ?></div>
					</div>

					<div id="wpshop_pages_option">
							<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_pages"><?php  do_settings_sections('wpshop_pages_option'); ?></div>
					</div>

					<div id="wpshop_display_option">
							<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_display"><?php  do_settings_sections('wpshop_display_option'); ?></div>
							<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_admin_display"><?php  do_settings_sections('wpshop_admin_display_option'); ?></div>
					</div>

					<div id="wpshop_emails_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_email"><?php do_settings_sections('wpshop_emails'); ?></div>
						<?php if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation'))) : ?>
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_message"><?php do_settings_sections('wpshop_messages'); ?></div>
						<?php endif; ?>
					</div>

					<?php if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation'))) : ?>
					<div id="wpshop_cart_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_cart"><?php do_settings_sections('wpshop_cart_info'); ?></div>
					</div>
					<?php endif; ?>

					<?php if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation'))) : ?>
					<div id="wpshop_billing_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_billing"><?php do_settings_sections('wpshop_billing_info'); ?></div>
					</div>
					<?php endif; ?>

					<?php if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation'))) : ?>
					<div id="wpshop_payments_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_payment_main"><?php do_settings_sections('wpshop_payment_main_info'); ?></div>
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_payment_method"><?php do_settings_sections('wpshop_paymentMethod'); ?></div>
					</div>
					<?php endif; ?>

					<?php if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation'))) : ?>
					<div id="wpshop_shipping_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_shipping_rules"><?php do_settings_sections('wpshop_shipping_rules'); ?></div>
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_shipping_mode"><?php do_settings_sections('wpshop_shipping_mode'); ?></div>
					</div>
					<?php endif; ?>

					<div id="wpshop_addons_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_addons"><?php do_settings_sections('wpshop_addons_options'); ?></div>
					</div>

					<?php if ( current_user_can('wpshop_view_advanced_options') ): ?>
					<div id="wpshop_advanced_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_advanced_settings"><?php do_settings_sections('wpshop_extra_options'); ?></div>
					</div>
					<?php endif; ?>

					<?php if(current_user_can('wpshop_edit_options')): ?>
						<p class="submit">
							<input class="button-primary" name="Submit" type="submit" value="<?php echo __('Save Changes','wpshop'); ?>" />
						</p>
					<?php endif; ?>

				</form>
		</div>

		<span class="infobulle"></span>
<?php
	}

}

