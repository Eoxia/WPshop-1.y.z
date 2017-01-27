<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * WPShop Payment Mode bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !class_exists("wps_payment_mode") ) {

	/** Template Global vars **/
	DEFINE('WPS_PAYMENT_MODE_DIR', basename(dirname(__FILE__)));
	DEFINE('WPS_PAYMENT_MODE_PATH', str_replace( "\\", "/", str_replace( WPS_PAYMENT_MODE_DIR, "", dirname( __FILE__ ) ) ) );
	DEFINE('WPS_PAYMENT_MODE_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_PAYMENT_MODE_PATH ) );


	class wps_payment_mode {
		/**
		 * Define the main directory containing the template for the current plugin
		 * @var string
		 */
		private $template_dir;
		/**
		 * Define the directory name for the module in order to check into frontend
		 * @var string
		 */
		private $plugin_dirname = WPS_PAYMENT_MODE_DIR;

		function __construct() {
			$this->template_dir = WPS_PAYMENT_MODE_PATH . WPS_PAYMENT_MODE_DIR . "/templates/";

			/** Checking Payment Mode Option **/
			$payment_option = get_option( 'wps_payment_mode' );
			if ( empty($payment_option) ) {
				self::migrate_payment_modes();
			}

			/** Check if SystemPay is registred in Payment Main Option **/
			$payment_option = get_option( 'wps_payment_mode' );
			if ( !empty($payment_option) && !empty($payment_option['mode']) && !array_key_exists('checks', $payment_option['mode']) ) {
				$payment_option['mode']['checks']['name'] = __('Checks', 'wpshop');
				$payment_option['mode']['checks']['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cheque.png';
				$payment_option['mode']['checks']['description'] = __('Reservation of products upon receipt of the check.', 'wpshop');
				update_option( 'wps_payment_mode', $payment_option );
			}

			if ( !empty($payment_option) && !empty($payment_option['mode']) && !array_key_exists('banktransfer', $payment_option['mode']) ) {
				$payment_modes['mode']['banktransfer']['name'] = __('Banktransfer', 'wpshop');
				$payment_modes['mode']['banktransfer']['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cheque.png';
				$payment_modes['mode']['banktransfer']['description'] = __('Reservation of products upon confirmation of payment.', 'wpshop');
				update_option( 'wps_payment_mode', $payment_option );
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'add_script') );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts') );

			/** Create Options **/
			add_action('wsphop_options', array(&$this, 'create_options') );

			add_filter( 'wps_payment_mode_interface_checks', array( &$this, 'display_interface_check') );
			add_filter( 'wps_payment_mode_interface_banktransfer', array( &$this, 'display_admin_interface_banktransfer') );
			add_filter( 'wps_payment_mode_interface_cic', array( 'wpshop_CIC', 'display_admin_part') );

			add_shortcode( 'wps_payment', array( &$this, 'display_payment_modes' ));

		}

		function add_script() {
			wp_enqueue_script( 'jquery');
			wp_enqueue_script( 'wps_payment_mode', plugins_url('assets/frontend/js/wps_payment_mode.js', __FILE__) );
		}

		function add_admin_scripts($hook) {
			if( $hook != 'settings_page_wpshop_option' )
				return;

			add_thickbox();
			wp_enqueue_script( 'jquery');
			wp_enqueue_script('jquery-ui');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script( 'wps_payment_mode_js', plugins_url('assets/backend/js/wps_payment_mode.js', __FILE__) );
		}


		/**
		 * Create the options
		 */
		function create_options() {
			register_setting('wpshop_options', 'wps_payment_mode', array(&$this, 'wps_validate_payment_option'));
			add_settings_field('wps_payment_mode', ''/*__('Payment Modes', 'wpshop')*/, array(&$this, 'display_payment_modes_in_admin'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
		}


		/**
		 * Options Validator
		 * @param array $input
		 * @return array
		 */
		function wps_validate_payment_option( $input ) {
			if( is_array($input) ) {
				foreach( $input['mode'] as $mode_key => $mode_config ) {
					if ( !empty($_FILES[$mode_key.'_logo']['name']) && empty($_FILES[$mode_key.'_logo']['error']) ) {
						$filename = $_FILES[$mode_key.'_logo'];
						$upload  = wp_handle_upload($filename, array('test_form' => false));
						$wp_filetype = wp_check_filetype(basename($filename['name']), null );
						$wp_upload_dir = wp_upload_dir();
						$attachment = array(
								'guid' => $wp_upload_dir['url'] . '/' . basename( $filename['name'] ),
								'post_mime_type' => $wp_filetype['type'],
								'post_title' => preg_replace(' /\.[^.]+$/', '', basename($filename['name'])),
								'post_content' => '',
								'post_status' => 'inherit'
						);
						$attach_id = wp_insert_attachment( $attachment, $upload['file']);
						require_once(ABSPATH . 'wp-admin/includes/image.php');
						$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
						wp_update_attachment_metadata( $attach_id, $attach_data );

						$input['mode'][$mode_key]['logo'] = $attach_id;
					}
				}
			}
			return $input;
		}

		/**
		 * Display Payment Mode in Admin
		 */
		function display_payment_modes_in_admin() {
			$output = '';
			$payment_option = get_option( 'wps_payment_mode' );

			require_once( wpshop_tools::get_template_part( WPS_PAYMENT_MODE_DIR, $this->template_dir, "backend", "payment-modes") );

		}


		public static function migrate_payment_modes() {
			$payment_modes = array();
			$payment_option = get_option( 'wpshop_paymentMethod' );
			$methods = array();
			$methods['display_position']['paypal'] = ( !empty($payment_option) && !empty($payment_option['paypal']) ) ? 'on' : '';
			$methods['display_position']['checks'] = ( !empty($payment_option) && !empty($payment_option['checks']) ) ? 'on' : '';
			$methods['display_position']['banktransfer'] = ( !empty($payment_option) && !empty($payment_option['banktransfer']) ) ? 'on' : '';
			$methods['default_method'] = ( !empty($payment_option['default_method']) ) ? $payment_option['default_method'] : 'checks';

			if ( !empty($payment_option['display_position']) ) {
				$methods['display_position'] = array_merge( $methods['display_position'], $payment_option['display_position'] );
				foreach( $methods['display_position'] as $k => $v ) {
					if ( !empty($payment_option[$k]) ) {
						$methods['display_position'][$k] = $payment_option[$k];
					}
				}
			}

			if ( !empty($methods) && !empty($methods['display_position']) ) {
				foreach( $methods['display_position'] as $key => $value ) {
						$payment_modes['mode'][$key]['active'] = ( !empty($methods['display_position'][ $key ]) && $methods['display_position'][ $key ] == 'on' ) ? $methods['display_position'][ $key ] : '';
						switch( $key ) {
							case 'paypal' :
								$payment_modes['mode'][$key]['name'] = __('Paypal', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/paypal.png';
								$payment_modes['mode'][$key]['description'] = __('Tips : If you have a Paypal account, by choosing this payment method, you will be redirected to the secure payment site Paypal to make your payment. Debit your PayPal account, immediate booking products.', 'wpshop');
							break;
							case 'banktransfer' :
								$payment_modes['mode'][$key]['name'] = __('Banktransfer', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cheque.png';
								$payment_modes['mode'][$key]['description'] = __('Reservation of products upon confirmation of payment.', 'wpshop');
							break;
							case 'checks' :
								$payment_modes['mode'][$key]['name'] = __('Checks', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cheque.png';
								$payment_modes['mode'][$key]['description'] = __('Reservation of products upon receipt of the check.', 'wpshop');
								$payment_modes['mode'][$key]['active'] = 'on';
							break;
							case 'systempay' :
								$payment_modes['mode'][$key]['name'] = __('Systempay', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = plugins_url().'/wpshop_systemPay/img/systemPay.png';
								$payment_modes['mode'][$key]['description'] = __('SystemPay - Banque Populaire', 'wpshop_systemPay');
							break;
							case 'cic' :
								$payment_modes['mode'][$key]['name'] = __('CIC', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cic_payment_logo.jpg';
								$payment_modes['mode'][$key]['description'] = __('Reservation of products upon confirmation of payment.', 'wpshop');
							break;
						}
				}

				if ( $methods['default_method'] ) {
					$payment_modes['default_choice'] = $methods['default_method'];
				}
				update_option( 'wps_payment_mode', $payment_modes);
			}

		}

		function display_interface_check() {
			$output = '';
			$company_payment = get_option('wpshop_paymentAddress');
			$company = get_option('wpshop_company_info');
// 			$output .= '<div class="wps-boxed"><div class="wps-form-group"><label>'.__('Company name', 'wpshop').'</label><div class="wps-form"><input name="wpshop_paymentAddress[company_name]" type="text" value="'.(!empty($company_payment['company_name'])?$company_payment['company_name']:'').'" /></div></div>';
// 			$output .= '<div class="wps-form-group"><label>'.__('Street', 'wpshop').'</label><div class="wps-form"><input name="wpshop_paymentAddress[company_street]" type="text" value="'.(!empty($company_payment['company_street'])?$company_payment['company_street']:'').'" /></div></div>';
// 			$output .= '<div class="wps-form-group"><label>'.__('Postcode', 'wpshop').'</label><div class="wps-form"><input name="wpshop_paymentAddress[company_postcode]" type="text" value="'.(!empty($company_payment['company_postcode'])?$company_payment['company_postcode']:'').'" /></div></div>';
// 			$output .= '<div class="wps-form-group"><label>'.__('City', 'wpshop').'</label><div class="wps-form"><input name="wpshop_paymentAddress[company_city]" type="text" value="'.(!empty($company_payment['company_city'])?$company_payment['company_city']:'').'" /></div></div>';
// 			$output .= '<div class="wps-form-group"><label>'.__('Country', 'wpshop').'</label><div class="wps-form"><input name="wpshop_paymentAddress[company_country]" type="text" value="'.(!empty($company_payment['company_country'])?$company_payment['company_country']:'').'" /></div></div></div>';
			return $output;
		}

		function display_admin_interface_banktransfer() {

			$wpshop_paymentMethod_options = get_option('wpshop_paymentMethod_options');
			$output  = '<div class="wps-boxed">';
			$output .= '<div class="wps-form-group"><label>'.__('Bank name', 'wpshop').'</label><div class="wps-form"><input name="wpshop_paymentMethod_options[banktransfer][bank_name]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['bank_name'])?$wpshop_paymentMethod_options['banktransfer']['bank_name']:'').'" /></div></div>';
			$output .= '<div class="wps-form-group"><label>'.__('IBAN', 'wpshop').'</label><div class="wps-form"><input name="wpshop_paymentMethod_options[banktransfer][iban]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['iban'])?$wpshop_paymentMethod_options['banktransfer']['iban']:'').'" /></div></div>';
			$output .= '<div class="wps-form-group"><label>'.__('BIC/SWIFT', 'wpshop').'</label><div class="wps-form"><input name="wpshop_paymentMethod_options[banktransfer][bic]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['bic'])?$wpshop_paymentMethod_options['banktransfer']['bic']:'').'" /></div></div>';
			$output .= '<div class="wps-form-group"><label>'.__('Account owner name', 'wpshop').'</label><div class="wps-form"><input name="wpshop_paymentMethod_options[banktransfer][accountowner]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['accountowner'])?$wpshop_paymentMethod_options['banktransfer']['accountowner']:'').'" /></div></div>';
			$output .= '</div>';
			return $output;
		}

		function display_payment_modes() {
			$output = '';
			$payment_modes = get_option( 'wps_payment_mode' );

			if ( !empty($payment_modes) && !empty($payment_modes['mode']) ) {
				$default_choice = ( !empty($payment_modes['default_choice']) ) ? $payment_modes['default_choice'] : '';
				$payment_modes = $payment_modes['mode'];
				$tmp_array = array();
				foreach( $payment_modes as $payment_mode_id => $payment_mode ) {
					if( !empty($payment_mode['active']) ) {
						$tmp_array[ $payment_mode_id ] = $payment_mode;
					}
				}
				$payment_modes = apply_filters( 'wps-paymentmode-filter', $tmp_array );

				ob_start();
				require_once( wpshop_tools::get_template_part( WPS_PAYMENT_MODE_DIR, $this->template_dir, "frontend", "payment-modes") );
				$output = ob_get_contents();
				ob_end_clean();
			}

			return $output;
		}


	}
}

/**	Instanciate the module utilities if not	*/
if ( class_exists("wps_payment_mode") ) {
	$wps_shipping_mode = new wps_payment_mode();
}
