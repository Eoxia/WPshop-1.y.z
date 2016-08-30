<?php if ( !defined( 'ABSPATH' ) ) exit;

class wps_barcode_settings {
	public function __construct() {
		add_filter('wpshop_options', array(&$this, 'add_options'), 10);
		add_action( 'admin_init', array( &$this, 'declare_options' ) );
	}

	/**
	 * Add options in group
	 * @param array $option_group Option group
	 * @return array Option group
	 */
	public function add_options($option_group) {
		$option_group['wpshop_barcode_options'] =
		array(	'label' => __('Barcode', 'wps_barcode'),
				'subgroups' => array(
						'wpshop_barcode_options' => array('class' =>
								' wpshop_admin_box_options_barcode'),
						'wpshop_barcode_options_internal' => array('class' =>
								' wpshop_admin_box_options_barcode_internal'),
						'wpshop_barcode_options_normal' => array('class' =>
								' wpshop_admin_box_options_barcode_normal'),
				),
		);

		return $option_group;
	}

	/**
	 * Declare options
	 */
	public function declare_options() {
		register_setting('wpshop_options', 'wps_barcode',
		array( $this, 'validate_options') );

		add_settings_section('wpshop_barcode_type_options',
			'<span class="dashicons dashicons-format-chat"></span>'.
			__('WPShop Barcode configuration', 'wps_barcode'),
			array($this, 'welcome_msg'), 'wpshop_barcode_options');

		add_settings_field('wpshop_barcode_type_field',
			__( 'Type of EAN-13 Barcode', 'wps_barcode'),
			array( $this, 'add_type_field'), 'wpshop_barcode_options',
			'wpshop_barcode_type_options');

		add_settings_field('wpshop_barcode_display_field',
			__('Automatic display barcode', 'wps_barcode'),
			array($this, 'add_display_field'), 'wpshop_barcode_options',
			'wpshop_barcode_type_options');


			add_settings_section('wpshop_barcode_internal_ccode_options',
			'<span class="dashicons dashicons-format-chat"></span>'.
			__('WPShop Barcode internal code configuration', 'wps_barcode'),
			array($this, 'internal_code_section'), 'wpshop_barcode_options_internal');

			add_settings_field('wpshop_barcode_client_field',
				__('Code for indicate client (3 digits)', 'wps_barcode'),
			array($this, 'add_client_field'), 'wpshop_barcode_options_internal',
			'wpshop_barcode_internal_ccode_options');

			add_settings_field('wpshop_barcode_provider_field',
			__('Code for indicate provider (3 digits)', 'wps_barcode'),
			array($this, 'add_provider_field'), 'wpshop_barcode_options_internal',
			'wpshop_barcode_internal_ccode_options');

			add_settings_field('wpshop_barcode_invoice__client_field',
			__('Code for indicate client invoice (3 digits)', 'wps_barcode'),
			array($this, 'add_invoice_client_field'), 'wpshop_barcode_options_internal',
			'wpshop_barcode_internal_ccode_options');

			add_settings_field('wpshop_barcode_do_client_field',
			__('Code for indicate client delivery order (3 digits)', 'wps_barcode'),
			array($this, 'add_do_client_field'), 'wpshop_barcode_options_internal',
			'wpshop_barcode_internal_ccode_options');

			add_settings_field('wpshop_barcode_product_field',
			__('Code for indicate product (3 digits)', 'wps_barcode'),
			array($this, 'add_product_field'), 'wpshop_barcode_options_internal',
			'wpshop_barcode_internal_ccode_options');

			add_settings_field('wpshop_barcode_assets_field',
			__('Code for indicate client assets (3 digits)', 'wps_barcode'),
			array($this, 'add_assets_client_field'), 'wpshop_barcode_options_internal',
			'wpshop_barcode_internal_ccode_options');

			add_settings_field('wpshop_barcode_coupons_field',
			__('Code for indicate coupons (3 digits)', 'wps_barcode'),
			array($this, 'add_coupons_field'), 'wpshop_barcode_options_internal',
			'wpshop_barcode_internal_ccode_options');

			add_settings_field('wpshop_barcode_invoice_provider_field',
			__('Code for indicate provider invoice (3 digits)', 'wps_barcode'),
			array($this, 'add_invoice_provider_field'), 'wpshop_barcode_options_internal',
			'wpshop_barcode_internal_ccode_options');

			add_settings_field('wpshop_barcode_do_provider_field',
			__('Code for indicate provider delivery order (3 digits)', 'wps_barcode'),
			array($this, 'add_do_provider_field'), 'wpshop_barcode_options_internal',
			'wpshop_barcode_internal_ccode_options');
			add_settings_section('wpshop_barcode_normal_code_options',
			'<span class="dashicons dashicons-format-chat"></span>'.
			__('WPShop Barcode normal code configuration', 'wps_barcode'),
			array($this, 'normal_code_section'), 'wpshop_barcode_options_normal');

			add_settings_field('wpshop_barcode_country_code_field',
			__('Indicate your country code', 'wps_barcode'),
			array($this, 'add_country_code_field'), 'wpshop_barcode_options_normal',
			'wpshop_barcode_normal_code_options');

			add_settings_field('wpshop_barcode_enterprise_code_field',
			__('Indicate your enterprise code', 'wps_barcode'),
			array($this, 'add_enterprise_code_field'), 'wpshop_barcode_options_normal',
			'wpshop_barcode_normal_code_options');
	}

	/**
	 * Validate options form
	 * @param array $input Options of form
	 * @return array options validate
	 */
	public function validate_options($input) {
		/*Verifity if exists vars*/
		$internal['generate_barcode'] = isset($input["generate_barcode"]) ? $input["generate_barcode"] : '';

		$internal["internal_client"] = isset($input["internal_client"]) ? $input["internal_client"] : '';

		$internal["internal_provider"] = isset($input["internal_provider"]) ? $input["internal_provider"] : '';

		$internal["internal_invoice_client"] = isset($input["internal_invoice_client"]) ? $input["internal_invoice_client"] : '';

		$internal["internal_do_client"] = isset($input["internal_do_client"]) ? $input["internal_do_client"] : '';

		$internal["internal_product"] = isset($input["internal_product"]) ? $input["internal_product"] : '';

		$internal["internal_assets_client"] = isset($input["internal_assets_client"]) ? $input["internal_assets_client"] : '';

		$internal["internal_coupons"] = isset($input["internal_coupons"]) ? $input["internal_coupons"] : '';

		$internal["internal_invoice_provider"] = isset($input["internal_invoice_provider"]) ? $input["internal_invoice_provider"] : '';

		$internal["internal_do_provider"] = isset($input["internal_do_provider"]) ? $input["internal_do_provider"] : '';

		$internal['type'] = isset($input['type']) ? $input['type'] : '';

		$internal['normal_country_code'] = isset($input['normal_country_code']) ? $input['normal_country_code'] : '';

		$internal['normal_enterprise_code'] = isset($input['normal_enterprise_code']) ? $input['normal_enterprise_code'] : '';

		/*Verifiy if 3 or 2 digits are presents*/
		if ( !empty($internal['internal_client']) || !empty($internal['internal_provider']) || !empty($internal['internal_invoice_client']) || !empty($internal['internal_do_client']) || !empty($internal['internal_product']) || !empty($internal['internal_invoice_provider']) || !empty($internal['internal_do_provider'])  || !empty($internal['normal_country_code']) || !empty($internal['normal_enterprise_code']) ) {
			$internal['internal_client'] = strlen($internal['internal_client']) === 2 ? '0'.$internal['internal_client'] : ( strlen($internal['internal_client']) === 3 ? $internal['internal_client'] : '');

			$internal['internal_provider'] = strlen($internal['internal_provider']) === 2 ? '0'.$internal['internal_provider'] : (strlen($internal['internal_provider']) === 3 ? $internal['internal_provider'] : '');

			$internal['internal_invoice_client'] = strlen($internal['internal_invoice_client']) === 2 ? '0'.$internal['internal_invoice_client'] : (strlen($internal['internal_invoice_client']) === 3 ? $internal['internal_invoice_client'] : '');

			$internal['internal_do_client'] = strlen($internal['internal_do_client']) === 2 ? '0'.$internal['internal_do_client'] : (strlen($internal['internal_do_client']) === 3 ? $internal['internal_do_client'] : '');

			$internal['internal_product'] = strlen($internal['internal_product']) === 2 ? '0'.$internal['internal_product'] : (strlen($internal['internal_product']) === 3 ? $internal['internal_product'] : '');

			$internal['internal_assets_client'] = strlen($internal['internal_assets_client']) === 2 ? '0'.$internal['internal_assets_client'] : (strlen($internal['internal_assets_client']) === 3 ? $internal['internal_assets_client'] : '');

			$internal['internal_coupons'] = strlen($internal['internal_coupons']) === 2 ? '0'.$internal['internal_coupons'] : (strlen($internal['internal_coupons']) === 3 ? $internal['internal_coupons'] : '');

			$internal['internal_invoice_provider'] = strlen($internal['internal_invoice_provider']) === 2 ? '0'.$internal['internal_invoice_provider'] : (strlen($internal['internal_invoice_provider']) === 3 ? $internal['internal_invoice_provider'] : '');

			$internal['internal_do_provider'] = strlen($internal['internal_do_provider']) === 2 ? '0'.$internal['internal_do_provider'] : (strlen($internal['internal_do_provider']) === 3 ? $internal['internal_do_provider'] : '');

			$internal['normal_country_code'] = strlen($internal['normal_country_code']) === 2 ? '0'.$internal['normal_country_code'] : (strlen($internal['normal_country_code']) === 3 ? $internal['normal_country_code'] : '');

			if ( strlen($internal['normal_enterprise_code']) === 1 ) {
				$internal['normal_enterprise_code'] = '000'.$internal['normal_enterprise_code'];
			}
			else if ( strlen($internal['normal_enterprise_code']) === 2 ) {
				$internal['normal_enterprise_code'] = '00'.$internal['normal_enterprise_code'];
			}
			else if ( strlen($internal['normal_enterprise_code']) === 3 ) {
				$internal['normal_enterprise_code'] = '0'.$internal['normal_enterprise_code'];
			}
			else {
				$internal['normal_enterprise_code'] = $internal['normal_enterprise_code'];
			}
		}

		/*Verify if range respected*/
		if ( $internal['internal_client'] !== '') {
			$val = intval($internal['internal_client']);
			if ( $val < 40 || $val > 49) {
				unset($internal['internal_client']);
				unset($input['client']);
			}
		}

		if ( $internal['internal_provider'] !== '') {
			$val = intval($internal['internal_provider']);
			if ( $val < 40 || $val > 49) {
				unset($internal['internal_provider']);
				unset($input['provider']);
			}
		}

		if ( $internal['internal_invoice_client'] !== '') {
			$val = intval($internal['internal_invoice_client']);
			if ( $val < 40 || $val > 49) {
				unset($internal['internal_invoice_client']);
				unset($input['invoice_client']);
			}
		}

		if ( $internal['internal_do_client'] !== '') {
			$val = intval($internal['internal_do_client']);
			if ( $val < 40 || $val > 49) {
				unset($internal['internal_do_client']);
				unset($input['do_client']);
			}
		}

		if ( $internal['internal_product'] !== '') {
			$val = intval($internal['internal_product']);
			if ( $val < 40 || $val > 49) {
				unset($internal['internal_product']);
				unset($input['product']);
			}
		}

		if ( $internal['internal_assets_client'] !== '') {
			$val = intval($internal['internal_assets_client']);
			if ( $val < 50 || $val > 59) {
				unset($internal['internal_assets_client']);
				unset($input['assets_client']);
			}
		}

		if ( $internal['internal_coupons'] !== '') {
			$val = intval($internal['internal_coupons']);
			if ( $val < 50 || $val > 59) {
				unset($internal['internal_coupons']);
				unset($input['coupons']);
			}
		}

		if ( $internal['internal_invoice_provider'] !== '') {
			$val = intval($internal['internal_invoice_provider']);
			if ( $val < 40 || $val > 49) {
				unset($internal['internal_invoice_provider']);
				unset($input['invoice_provider']);
			}
		}

		if ( $internal['internal_do_provider'] !== '') {
			$val = intval($internal['internal_do_provider']);
			if ( $val < 40 || $val > 49) {
				unset($internal['internal_do_provider']);
				unset($input['do_provider']);
			}
		}

		if ( $internal['normal_country_code'] === '' ) {
			unset($internal['normal_country_code']);
			unset($input['normal_country_code']);
		}

		if ( $internal['normal_enterprise_code'] === '' ) {
			unset($internal['normal_enterprise_code']);
			unset($input['normal_enterprise_code']);
		}

		$input = $internal;

		return $input;
	}

	/**
	 * Display message for internal code section
	 */
	public function internal_code_section() {
		_e("Configure your internal codes for automatically generate barcode. The codes are 2 digits, its normal. ".
				"You can put 3 without problems. Please respect the range number following: 040 up to 049.".
				" For coupons, the range is: 050 up to 059.", 'wps_barcode');
	}

	/**
	 * Display message for normal code section
	 */
	public function normal_code_section() {
		_e("Configure normal informations for automatically generate barcode.", 'wps_barcode');
	}

	public function add_display_field() {
		$field = get_option('wps_barcode');

		$checked = ( isset($field['generate_barcode']) && $field['generate_barcode'] === 'on') ? 'checked' : '';

		echo '<input type="checkbox" name="wps_barcode[generate_barcode]" id="barcode_display"'.$checked.'>';
	}

	/**
	 * Add type field in form
	 */
	public function add_type_field() {
		$field = get_option('wps_barcode');
		$selected = '';

		$field['type'] = isset($field['type']) ? $field['type'] : 'internal';

		echo '<select name="wps_barcode[type]" id="barcode_type">';

		if ( !empty($field) && ($field['type'] == 'internal') ) {
			echo '<option value="internal" selected>'.__('Internal', 'wps_barcode').'</option>';
			echo '<option value="normal">'.__('Normal', 'wps_barcode').'</option>';
		}
		else if ( !empty($field) && ($field['type'] == 'normal') ) {
			echo '<option value="internal">'.__('Internal', 'wps_barcode').'</option>';
			echo '<option value="normal" selected>'.__('Normal', 'wps_barcode').'</option>';
		}

		echo '</select>';

		$this->type = $field['type'];
	}

	/**
	 * Display welcome message
	 */
	public function welcome_msg() {
		//include(WPS_BARCODE_TEMPLATES_MAIN_DIR.'backend/welcome.tpl.php');
		require( wpshop_tools::get_template_part(WPS_BARCODE_PATH,
			WPS_BARCODE_TEMPLATES_TPL_DIR, 'backend', 'welcome/welcome') );
	}

	/**
	 * Add field for config internal client code
	 */
	public function add_client_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[internal_client]" '.
				'min=40 max=49 value="'.$field['internal_client'].'" />';
	}

	/**
	 * Add field for config internal provider code
	 */
	public function add_provider_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[internal_provider]" '.
				'min=40 max=49 value="'.$field['internal_provider'].'" />';
	}

	/**
	 * Add field for config internal client invoice code
	 */
	public function add_invoice_client_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[internal_invoice_client]" '.
				'min=40 max=49 value="'.$field['internal_invoice_client'].'" />';
	}

	/**
	 * Add field for config internal client delivery order code
	 */
	public function add_do_client_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[internal_do_client]" '.
				'min=40 max=49 value="'.$field['internal_do_client'].'" />';
	}

	/**
	 * Add field for config internal product code
	 */
	public function add_product_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[internal_product]" '.
				'min=40 max=49 value="'.$field['internal_product'].'" />';
	}

	/**
	 * Add field for config internal assets client code
	 */
	public function add_assets_client_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[internal_assets_client]" '.
				'min=50 max=59 value="'.$field['internal_assets_client'].'" />';
	}

	/**
	 * Add field for config internal coupons code
	 */
	public function add_coupons_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[internal_coupons]" '.
				'min=50 max=59 value="'.$field['internal_coupons'].'" />';
	}

	/**
	 * Add field for config internal provider invoice code
	 */
	public function add_invoice_provider_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[internal_invoice_provider]" '.
				'min=40 max=49 value="'.$field['internal_invoice_provider'].'" />';
	}

	/**
	 * Add field for config internal provider delivery order code
	 */
	public function add_do_provider_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[internal_do_provider]" '.
				'min=40 max=49 value="'.$field['internal_do_provider'].'" />';
	}

	/**
	 * Add field for config normal country code
	 */
	public function add_country_code_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[normal_country_code]" '.
				'value="'.$field['normal_country_code'].'" />';
	}

	/**
	 * Add field for config normal enterprise code
	 */
	public function add_enterprise_code_field() {
		$field = get_option('wps_barcode');

		echo '<input type="number" name="wps_barcode[normal_enterprise_code]" '.
				'value="'.$field['normal_enterprise_code'].'" />';
	}
}

?>
