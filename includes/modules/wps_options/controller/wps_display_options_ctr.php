<?php if ( ! defined( 'ABSPATH' ) ) { exit;
}
class wps_display_options {


	function __construct() {

		add_action( 'admin_init', array( $this, 'declare_display_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
		add_filter( 'plugin_action_links_' . WPSHOP_PLUGIN_NAME, array( $this, 'plugin_action_links' ) );
		// End if().
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts_frontend' ) );
	}

	function plugin_action_links( $links ) {

		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=' . WPSHOP_URL_SLUG_OPTION ) . '" aria-label="' . esc_attr__( 'View WPShop settings', 'wpshop' ) . '">' . esc_html__( 'Settings' ) . '</a>',
		);
		return array_merge( $action_links, $links );
	}

	function add_scripts( $hook ) {

		if ( $hook != 'settings_page_wpshop_option' ) {
			return;
		}

		wp_enqueue_script( 'jquery' );
		// End if().
		wp_enqueue_script( 'iris' );
		wp_enqueue_script( 'wps_options_display_js',  WPS_OPTIONS_URL . WPS_OPTIONS_DIR . '/assets/backend/js/wps_option_display.js', false );
	}

	function add_scripts_frontend() {

		add_action( 'wp_print_scripts', array( $this, 'create_customizing_css_rules' ) );
	}


	/**
	 * Init and declare WPShop Display Options
	 */
	function declare_display_options() {

		// Frontend display options
		register_setting( 'wpshop_options', 'wpshop_display_option', array( $this, 'display_part_validator' ) );
		add_settings_section( 'wpshop_display_options_sections', '<span class="dashicons dashicons-welcome-view-site"></span>' . __( 'Display options', 'wpshop' ), array( $this, 'frontend_display_part_explanation' ), 'wpshop_display_option' );
		// Add the different field option for frontend
		add_settings_field( 'wpshop_display_cat_sheet_output', __( 'Display type for category page', 'wpshop' ), array( $this, 'wpshop_display_cat_sheet_output' ), 'wpshop_display_option', 'wpshop_display_options_sections' );
		add_settings_field( 'wpshop_display_list_type', __( 'Display type for element list', 'wpshop' ), array( $this, 'wpshop_display_list_type' ), 'wpshop_display_option', 'wpshop_display_options_sections' );
		add_settings_field( 'wpshop_display_grid_element_number', __( 'Number of element by line for grid mode', 'wpshop' ), array( $this, 'wpshop_display_grid_element_number' ), 'wpshop_display_option', 'wpshop_display_options_sections' );
		add_settings_field( 'wpshop_display_element_per_page', __( 'Number of element per page', 'wpshop' ), array( $this, 'wpshop_display_element_per_page' ), 'wpshop_display_option', 'wpshop_display_options_sections' );
		add_settings_field( 'wpshop_display_latest_products_ordered', __( 'Number of element in "latest products ordered" part', 'wpshop' ), array( $this, 'wpshop_display_latest_products_ordered' ), 'wpshop_display_option', 'wpshop_display_options_sections' );
		add_settings_field( 'wpshop_hide_admin_bar', __( 'Hide Wordpress Admin Bar for customers', 'wpshop' ), array( $this, 'wpshop_hide_admin_bar' ), 'wpshop_display_option', 'wpshop_display_options_sections' );
		add_settings_field( 'wpshop_display_delete_order', __( 'Display delete order for customers', 'wpshop' ), array( $this, 'wpshop_display_delete_order' ), 'wpshop_display_option', 'wpshop_display_options_sections' );
		// Customize WPShop display part
		register_setting( 'wpshop_options', 'wpshop_customize_display_option', array( $this, 'customize_color_validator' ) );
		add_settings_section( 'wpshop_customize_wpshop_display_option', '<span class="dashicons dashicons-admin-appearance"></span>' . __( 'Customize your WPShop', 'wpshop' ), array( $this, 'customize_wpshop_colors_explanation' ), 'wpshop_customize_display_option' );
		add_settings_field( 'wpshop_customize_first_button_field', __( 'Change the principal button style', 'wpshop' ), array( $this, 'wps_customize_first_button_style' ), 'wpshop_customize_display_option', 'wpshop_customize_wpshop_display_option' );
		add_settings_field( 'wpshop_customize_second_button_field', __( 'Change the second button style', 'wpshop' ), array( $this, 'wps_customize_second_button_style' ), 'wpshop_customize_display_option', 'wpshop_customize_wpshop_display_option' );
		add_settings_field( 'wpshop_customize_account_field', __( 'Change the customer account elements style', 'wpshop' ), array( $this, 'wps_customize_account_style' ), 'wpshop_customize_display_option', 'wpshop_customize_wpshop_display_option' );
		add_settings_field( 'wpshop_customize_shipping_list_field', __( 'Change The shipping mode choice element style', 'wpshop' ), array( $this, 'wps_customize_shipping_style' ), 'wpshop_customize_display_option', 'wpshop_customize_wpshop_display_option' );
		// Admin (Back-end) display options
		register_setting( 'wpshop_options', 'wpshop_admin_display_option', array( $this, 'admin_part_validator' ) );
		add_settings_section( 'wpshop_admin_display_options_sections', '<span class="dashicons dashicons-desktop"></span>' . __( 'Admin display options', 'wpshop' ), array( $this, 'admin_part_explanation' ), 'wpshop_admin_display_option' );
		add_settings_field( 'wpshop_admin_display_attribute_set_layout', __( 'Attribute set page layout', 'wpshop' ), array( $this, 'wpshop_admin_display_attr_set_layout' ), 'wpshop_admin_display_option', 'wpshop_admin_display_options_sections' );
		add_settings_field( 'wpshop_admin_display_attribute_layout', __( 'Attribute page layout', 'wpshop' ), array( $this, 'wpshop_admin_display_attr_layout' ), 'wpshop_admin_display_option', 'wpshop_admin_display_options_sections' );
		add_settings_field( 'wpshop_admin_display_shortcode_product', __( 'Shortcode display in product page', 'wpshop' ), array( $this, 'wpshop_admin_display_shortcode_in_product_page' ), 'wpshop_admin_display_option', 'wpshop_admin_display_options_sections' );
	}

	/**
	 * ***********************************
	 * ***********************************
	 * FRONTEND GENERALS DISPLAY OPTIONS *
	 * ***********************************
	 * ***********************************
	 */

	/**
	 * VALIDATOR - Frontend part validator
	 *
	 * @param array $input
	 * @return array
	 */
	function display_part_validator( $input ) {

		$newinput['wpshop_display_list_type'] = $input['wpshop_display_list_type'];
		if ( $input['wpshop_display_grid_element_number'] < WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE ) {
			$input['wpshop_display_grid_element_number'] = WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE;
		} elseif ( $input['wpshop_display_grid_element_number'] > WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE ) {
			$input['wpshop_display_grid_element_number'] = WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE;
		}
		$newinput['wpshop_display_grid_element_number'] = $input['wpshop_display_grid_element_number'];
		$newinput['wpshop_display_cat_sheet_output'] = $input['wpshop_display_cat_sheet_output'];
		$newinput['wpshop_display_element_per_page'] = ! empty( $input['wpshop_display_element_per_page'] ) ? $input['wpshop_display_element_per_page'] : '';
		$newinput['latest_products_ordered'] = $input['latest_products_ordered'];
		$newinput['wpshop_hide_admin_bar'] = ! empty( $input['wpshop_hide_admin_bar'] ) ? $input['wpshop_hide_admin_bar'] : '';
		$newinput['wpshop_display_delete_order'] = ! empty( $input['wpshop_display_delete_order'] ) ? $input['wpshop_display_delete_order'] : '';
		return $newinput;
	}

	/**
	 * EXPLANATIONS - Frontend display option explanantion
	 */
	function frontend_display_part_explanation() {

		_e( 'Manage here your frontend display options', 'wpshop' );
	}

	/**
	 * FIELDS - Display Categories output options
	 */
	function wpshop_display_cat_sheet_output() {

		$wpshop_display_option = get_option( 'wpshop_display_option' );
		$field_identifier = 'wpshop_display_cat_sheet_output';
		if ( current_user_can( 'wpshop_edit_options' ) ) {
			$content = array( 'category_description', 'category_subcategory', 'category_subproduct' );
			$option_field_output = '';
			foreach ( $content as $content_definition ) {
				$current_value = (is_array( $wpshop_display_option['wpshop_display_cat_sheet_output'] ) && in_array( $content_definition, $wpshop_display_option['wpshop_display_cat_sheet_output'] )) || ! is_array( $wpshop_display_option['wpshop_display_cat_sheet_output'] ) ? $content_definition : '';
				switch ( $content_definition ) {
					case 'category_description':
						{
							$field_label = __( 'Display product category description', 'wpshop' );
					}
						break;
					case 'category_subcategory':
						{
							$field_label = __( 'Display sub categories listing', 'wpshop' );
					}
						break;
					case 'category_subproduct':
						{
							$field_label = __( 'Display products listing', 'wpshop' );
					}
						break;
					default:
						{
							$field_label = __( 'Nothing defined here', 'wpshop' );
					}
						break;
				}
				$option_field_output .= wpshop_form::form_input_check( 'wpshop_display_option[' . $field_identifier . '][]', $field_identifier . '_' . $content_definition, $content_definition, $current_value, 'checkbox' ) . '<label for="' . $field_identifier . '_' . $content_definition . '" >' . $field_label . '</label><br/>';
			}
		} else {
			$option_field_output = $wpshop_display_option[ $field_identifier ];
		}

		echo $option_field_output;
	}

	/**
	 * FIELDS - Display products displaying type options (List/Grid)
	 */
	function wpshop_display_list_type() {

		$wpshop_display_option = get_option( 'wpshop_display_option' );
		$field_identifier = 'wpshop_display_list_type';
		if ( current_user_can( 'wpshop_edit_options' ) ) {
			$option_field_output = wpshop_form::form_input_select( 'wpshop_display_option[' . $field_identifier . ']', $field_identifier, array(
				'grid' => __( 'Grid', 'wpshop' ),
				'list' => __( 'List', 'wpshop' ),
			), $wpshop_display_option[ $field_identifier ], '', 'index' );
		} else {
			$option_field_output = $wpshop_display_option[ $field_identifier ];
		}

		echo $option_field_output . ' <a href="#" title="' . __( 'Default display mode on shop','wpshop' ) . '" class="wpshop_infobulle_marker">?</a>';
	}

	/**
	 * FILEDS - Display Grid element number options
	 */
	function wpshop_display_grid_element_number() {

		$wpshop_display_option = get_option( 'wpshop_display_option' );
		$field_identifier = 'wpshop_display_grid_element_number';
		require( wpshop_tools::get_template_part( WPS_OPTIONS_DIR, WPS_OPTIONS_TEMPLATE_DIR, 'backend', 'wps_display_options_grid_field' ) );
	}

	/**
	 * FIELDS - Display elements per page option
	 */
	function wpshop_display_element_per_page() {

		$wpshop_display_option = get_option( 'wpshop_display_option' );
		$field_identifier = 'wpshop_display_element_per_page';
		if ( current_user_can( 'wpshop_edit_options' ) ) {
			$option_field_output = wpshop_form::form_input( 'wpshop_display_option[' . $field_identifier . ']', $field_identifier, ! empty( $wpshop_display_option[ $field_identifier ] ) ? $wpshop_display_option[ $field_identifier ] : 20, 'text' );
		} else {
			$option_field_output = $wpshop_display_option[ $field_identifier ];
		}

		echo $option_field_output . ' <a href="#" title="' . __( 'Number of elements per page','wpshop' ) . '" class="wpshop_infobulle_marker">?</a>';
	}

	/**
	 * FIELDS - Display last products ordered count option
	 */
	function wpshop_display_latest_products_ordered() {

		$display_option = get_option( 'wpshop_display_option' );
		$output = '<input type="text" value="' . ( ( ! empty( $display_option ) && ! empty( $display_option['latest_products_ordered'] ) ) ? $display_option['latest_products_ordered'] : '') . '" name="wpshop_display_option[latest_products_ordered]" id="wpshop_display_latest_products_ordered" />';
		echo $output;
	}

	/**
	 * FIELDS - Display WP Admin Bar for customers option
	 */
	function wpshop_hide_admin_bar() {

		$wpshop_hide_admin_bar_option = get_option( 'wpshop_display_option' );
		$output = '<input type="checkbox" name="wpshop_display_option[wpshop_hide_admin_bar]" ' . ( ( ! empty( $wpshop_hide_admin_bar_option ) && ! empty( $wpshop_hide_admin_bar_option['wpshop_hide_admin_bar'] ) ) ? 'checked="checked"' : '') . '/>';
		echo $output;
	}

	/**
	 * FIELDS - Display delete order for customers option
	 */
	public function wpshop_display_delete_order() {

		$wpshop_display_delete_order_option = get_option( 'wpshop_display_option' );
		$output = '<input type="checkbox" name="wpshop_display_option[wpshop_display_delete_order]" ' . ( ( ! empty( $wpshop_display_delete_order_option ) && ! empty( $wpshop_display_delete_order_option['wpshop_display_delete_order'] ) ) ? 'checked="checked"' : '') . '/>';
		echo $output;
	}


	/**
	 * ***********************************
	 * ***********************************
	 * CUSTOMIZER WPSHOP DISPLAY OPTIONS *
	 * ***********************************
	 * ***********************************
	 */

	/**
	 * VALIDATOR - Customize WPShop validator
	 *
	 * @param array $input
	 * @return array
	 */
	function customize_color_validator( $input ) {

		return $input;
	}

	/**
	 * EXPLANATIONS - Display customize WPShop explanantions
	 */
	function customize_wpshop_colors_explanation() {

		_e( 'Here, you can customize your WPShop elements like buttons, customer account parts and selected shipping method colors...', 'wpshop' );
	}

	/**
	 * FIELDS - Display First button customize option
	 */
	function wps_customize_first_button_style() {

		$wpshop_customize_display_option = get_option( 'wpshop_customize_display_option' );
		require( wpshop_tools::get_template_part( WPS_OPTIONS_DIR, WPS_OPTIONS_TEMPLATE_DIR, 'backend', 'wps_display_options_customize_first_button' ) );
	}

	/**
	 * FIELDS - Display Second button customize option
	 */
	function wps_customize_second_button_style() {

		$wpshop_customize_display_option = get_option( 'wpshop_customize_display_option' );
		require( wpshop_tools::get_template_part( WPS_OPTIONS_DIR, WPS_OPTIONS_TEMPLATE_DIR, 'backend', 'wps_display_options_customize_second_button' ) );
	}

	/**
	 * FIELDS - Dsiplay Customer account customize option
	 */
	function wps_customize_account_style() {

		$wpshop_customize_display_option = get_option( 'wpshop_customize_display_option' );
		require( wpshop_tools::get_template_part( WPS_OPTIONS_DIR, WPS_OPTIONS_TEMPLATE_DIR, 'backend', 'wps_display_options_customize_customer_account' ) );
	}

	/**
	 * FIELDS - Dsiplay Customer account customize option
	 */
	function wps_customize_shipping_style() {

		$wpshop_customize_display_option = get_option( 'wpshop_customize_display_option' );
		require( wpshop_tools::get_template_part( WPS_OPTIONS_DIR, WPS_OPTIONS_TEMPLATE_DIR, 'backend', 'wps_display_options_customize_shipping_list' ) );
	}

	/**
	 * Print Custom CSS Rules in administration
	 */
	function create_customizing_css_rules() {

		$wpshop_customize_display_option = get_option( 'wpshop_customize_display_option' );
		require( wpshop_tools::get_template_part( WPS_OPTIONS_DIR, WPS_OPTIONS_TEMPLATE_DIR, 'frontend', 'wps_display_options_customize_css_rules' ) );
	}


	/**
	 * ***********************************
	 * ***********************************
	 * BACK-END GENERALS DISPLAY OPTIONS *
	 * ***********************************
	 * ***********************************
	 */

	/**
	 * VALIDATOR - Admin display option validator
	 *
	 * @param array $input
	 * @return array
	 */
	function admin_part_validator( $input ) {

		return $input;
	}

	/**
	 * EXPLANATIONS - Admin display options explanation
	 */
	function admin_part_explanation() {

		_e( 'You can defined some parameters for admin display', 'wpshop' );
	}

	/**
	 * FIELDS - Display admin option Attributes set layout type
	 */
	function wpshop_admin_display_attr_set_layout() {

		global $attribute_page_layout_types;
		$field_identifier = 'wpshop_admin_attr_set_layout';
		$wpshop_admin_display_option = get_option( 'wpshop_admin_display_option', array() );
		if ( current_user_can( 'wpshop_edit_options' ) ) {
			$option_field_output = wpshop_form::form_input_select( 'wpshop_admin_display_option[' . $field_identifier . ']', $field_identifier, $attribute_page_layout_types, WPSHOP_ATTRIBUTE_SET_EDITION_PAGE_LAYOUT, '', 'index' );
		} else { $option_field_output = $wpshop_admin_display_option[ $field_identifier ];
		}

		echo $option_field_output . ' <a href="#" title="' . __( 'Define if the attribute set edition page is displayed as tab or as separated bloc','wpshop' ) . '" class="wpshop_infobulle_marker">?</a>';
	}

	/**
	 * FIELDS - Display admin option attributes Layout type
	 */
	function wpshop_admin_display_attr_layout() {

		global $attribute_page_layout_types;
		$field_identifier = 'wpshop_admin_attr_layout';
		$wpshop_admin_display_option = get_option( 'wpshop_admin_display_option', array() );
		if ( current_user_can( 'wpshop_edit_options' ) ) {
			$option_field_output = wpshop_form::form_input_select( 'wpshop_admin_display_option[' . $field_identifier . ']', $field_identifier, $attribute_page_layout_types, WPSHOP_ATTRIBUTE_EDITION_PAGE_LAYOUT, '', 'index' );
		} else { $option_field_output = $wpshop_admin_display_option[ $field_identifier ];
		}

		echo $option_field_output . ' <a href="#" title="' . __( 'Define if the attribute edition page is displayed as tab or as separated bloc','wpshop' ) . '" class="wpshop_infobulle_marker">?</a>';
	}

	/**
	 * FIELDS - Display admin options Shortcode layout display
	 */
	function wpshop_admin_display_shortcode_in_product_page() {

		global $product_page_layout_types;
		$field_identifier = 'wpshop_admin_product_shortcode_display';
		$wpshop_admin_display_option = get_option( 'wpshop_admin_display_option', array() );
		if ( current_user_can( 'wpshop_edit_options' ) ) {
			$option_field_output = wpshop_form::form_input_select( 'wpshop_admin_display_option[' . $field_identifier . ']', $field_identifier, $product_page_layout_types, WPSHOP_PRODUCT_SHORTCODE_DISPLAY_TYPE, '', 'index' );
		} else { $option_field_output = $wpshop_admin_display_option[ $field_identifier ];
		}

		echo $option_field_output . ' <a href="#" title="' . __( 'Define how to display the shortcode summary in product edition page','wpshop' ) . '" class="wpshop_infobulle_marker">?</a>';
	}
}
