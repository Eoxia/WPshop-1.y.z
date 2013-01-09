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

class wpshop_options
{
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

		/* Billing */
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation')) && !isset($_POST['old_wpshop_shop_type']) || (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation'))){
			add_settings_section('wpshop_billing_info', __('Billing settings', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_billing_info');
				register_setting('wpshop_options', 'wpshop_billing_number_figures', array('wpshop_options', 'wpshop_options_validate_billing_number_figures'));
				add_settings_field('wpshop_billing_number_figures', __('Number of figures', 'wpshop'), array('wpshop_options', 'wpshop_billing_number_figures_field'), 'wpshop_billing_info', 'wpshop_billing_info');
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
					<li><a href="#wpshop_billing_option"><?php echo __('Billing', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_payments_option"><?php echo __('Payments', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_shipping_option"><?php echo __('Shipping', 'wpshop'); ?></a></li>
					<?php endif; ?>
					<li><a href="#wpshop_addons_option"><?php echo __('Addons', 'wpshop'); ?></a></li>
					<li class="wpshop_advanced_options <?php echo (current_user_can('wpshop_view_advanced_options') && in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_MODE_ALLOWED_IP)) ? '' : 'wpshopHide' ); ?>" ><a href="#wpshop_advanced_option"><?php echo __('Advanced', 'wpshop'); ?></a></li>
				</ul>

				<form action="options.php" method="post" id="wpshop_option_form" >
					<?php settings_fields('wpshop_options'); ?>

					<div id="wpshop_general_option">
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_general"><?php do_settings_sections('wpshop_general_config'); ?></div>
						<div class="wpshop_admin_box wpshop_admin_box_options wpshop_admin_box_options_company"><?php
							if(WPSHOP_DEBUG_MODE && in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_MODE_ALLOWED_IP))){
								echo '<span class="fill_form_for_test" >Fill the form for test</span>';
							}
							do_settings_sections('wpshop_company_info'); ?></div>
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

