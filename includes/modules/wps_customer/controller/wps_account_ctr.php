<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_account_ctr {

	private $redirect = false;

	private $redirect_url;

	function __construct() {
		/** Shortcodes **/
		// Sign up Display Shortcode
		add_shortcode( 'wps_signup', array( &$this, 'display_signup' ) );
		// Log in Form Display Shortcode
		add_shortcode( 'wpshop_login', array( &$this, 'get_login_form'));
		//Log in first step
		add_shortcode( 'wps_first_login', array( &$this, 'get_login_first_step'));
		// Forgot password Form
		add_shortcode( 'wps_forgot_password', array( &$this, 'get_forgot_password_form'));
		// Renew password form
		add_shortcode( 'wps_renew_password', array( &$this, 'get_renew_password_form'));
		//Account informations
		add_shortcode( 'wps_account_informations', array($this, 'shortcode_callback_display_account_informations') );
		//Account form
		add_shortcode( 'wps_account_informations_form', array($this, 'account_informations_form') );

		/** Ajax Actions **/
		// add_action('wap_ajax_wps_display_connexion_form', array(&$this, 'wps_ajax_get_login_form_interface') );
		// add_action('wap_ajax_nopriv_wps_display_connexion_form', array(&$this, 'wps_ajax_get_login_form_interface') );

		add_action('wp_ajax_wps_login_request', array(&$this, 'control_login_form_request') );
		add_action('wp_ajax_nopriv_wps_login_request', array(&$this, 'control_login_form_request') );

		add_action('wp_ajax_wps_forgot_password_request', array(&$this, 'wps_forgot_password_request') );
		add_action('wp_ajax_nopriv_wps_forgot_password_request', array(&$this, 'wps_forgot_password_request') );

		add_action('wp_ajax_wps_forgot_password_renew', array(&$this, 'wps_forgot_password_renew') );
		add_action('wp_ajax_nopriv_wps_forgot_password_renew', array(&$this, 'wps_forgot_password_renew') );

		add_action('wp_ajax_wps_signup_request', array(&$this, 'wps_save_signup_form') );
		add_action('wp_ajax_nopriv_wps_signup_request', array(&$this, 'wps_save_signup_form_nopriv') );

		add_action('wp_ajax_wps_login_first_request', array(&$this, 'wps_login_first_request') );
		add_action('wp_ajax_nopriv_wps_login_first_request', array(&$this, 'wps_login_first_request') );

		add_action( 'wp_ajax_wps_save_account_informations', array($this, 'wps_save_account_informations') );

		add_action( 'wp_ajax_wps_account_reload_informations', array($this, 'wps_account_reload_informations') );

		add_action( 'wp_ajax_wps_fill_forgot_password_modal', array($this, 'wps_fill_forgot_password_modal') );
		add_action( 'wp_ajax_nopriv_wps_fill_forgot_password_modal', array($this, 'wps_fill_forgot_password_modal') );

		add_action( 'wp_ajax_wps_fill_account_informations_modal', array($this, 'wps_fill_account_informations_modal') );
		add_action( 'wp_ajax_nopriv_wps_fill_account_informations_modal', array($this, 'wps_fill_account_informations_modal') );

		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts') );
	}

	/**
	 * Add scripts
	 */
	function add_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'wps_forgot_password_js', WPS_ACCOUNT_URL.'wps_customer/assets/frontend/js/wps_forgot_password.js' );
		wp_enqueue_script( 'wps_login_js', WPS_ACCOUNT_URL.'wps_customer/assets/frontend/js/wps_login.js' );
		wp_enqueue_script( 'wps_signup_js', WPS_ACCOUNT_URL.'wps_customer/assets/frontend/js/wps_signup.js' );
		wp_enqueue_script( 'wps_account_js', WPS_ACCOUNT_URL.'wps_customer/assets/frontend/js/wps_account.js' );
		wp_enqueue_style( 'wps_account_css', WPS_ACCOUNT_URL.'wps_customer/assets/frontend/css/frontend.css' );
	}

	/** LOG IN - Display log in Form **/
	function get_login_form( $force_login = false ) {
		$args = array();
		if ( true === is_user_logged_in() ) {
			return __( 'You are already logged', 'wpshop');
		} else {
			$action = !empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
			$key = !empty( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
			$login = !empty( $_GET['login'] ) ? sanitize_text_field( $_GET['login'] ) : 0;
			if ( !empty($action) && $action == 'retrieve_password' && !empty($key) && !empty($login) && !$force_login ) {
				$output = self::get_renew_password_form();
			}
			else {
				ob_start();
				require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL,  "frontend", "login/login-form") );
				$output = ob_get_contents();
				ob_end_clean();
				if ( !$force_login ) {
					$output .= do_shortcode( '[wps_signup]' );
				}
			}
			return $output;
		}
	}

	/** LOG IN - AJAX - Action to connect **/
	function control_login_form_request() {

		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'control_login_form_request' ) )
			wp_die();

		$result = '';
		$status = false;
		$origin = sanitize_text_field( $_POST['wps-checking-origin'] );
		$wps_login_user_login = !empty( $_POST['wps_login_user_login'] ) ? sanitize_text_field( $_POST['wps_login_user_login' ] ) : '';
		$wps_login_password = !empty( $_POST['wps_login_password'] ) ? sanitize_text_field( $_POST['wps_login_password' ] ) : '';
		$page_account_id = wpshop_tools::get_page_id( get_option( 'wpshop_myaccount_page_id') );
		if ( !empty($wps_login_user_login) && !empty($wps_login_password) ) {
			$creds = array();
			// Test if an user exist with this login
			$user_checking = get_user_by( 'login', $wps_login_user_login );
			if( !empty($user_checking) ) {
				$creds['user_login'] = $wps_login_user_login;
			}
			else {
				if ( is_email($wps_login_user_login) ) {
					$user_checking = get_user_by( 'email', $wps_login_user_login );
					$creds['user_login'] = $user_checking->user_login;
				}
			}
			$creds['user_password'] = wpshop_tools::varSanitizer( $_POST['wps_login_password'] );
			$creds['remember'] =  !empty( $_POST['wps_login_remember_me'] ) ? (int) $_POST['wps_login_remember_me'] : false;
			$user = wp_signon( $creds, false );
			if ( is_wp_error($user) ) {
				$result = '<div class="wps-alert-error">' .__('Connexion error', 'wpshop'). '</div>';
			}
			else {
				$permalink_option = get_option( 'permalink_structure' );
				$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
				if( $origin == $page_account_id ) {
					$result = get_permalink( $page_account_id );
				}
				else {
					$result = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=3';
				}
				$status = true;
			}
		}
		else {
			$result = '<div class="wps-alert-error">' .__('E-Mail and Password are required', 'wpshop'). '</div>';
		}

		echo json_encode( array( $status, $result) );
		die();
	}

	/**
	 * LOG IN - AJAX - Display log in Form in Ajax
	 */
	function wps_ajax_get_login_form_interface() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_ajax_get_login_form_interface' ) )
			wp_die();

		$response = array( 'status' => true, 'response' => self::get_login_form() );
		echo json_encode( $response );
		die();
	}

	/** LOG IN - Display first login step **/
	function get_login_first_step() {
		$output = '';
		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, "frontend", "login/login-form", "first") );
		$output .= ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * LOG IN - First Step log in request
	 */
	function wps_login_first_request() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_login_first_request' ) )
			wp_die();

		$status = false; $login_action = false; $response = '';
		$user_email = ( !empty($_POST['email_address']) ) ? wpshop_tools::varSanitizer( $_POST['email_address'] ) : null;
		if ( !empty($user_email) ) {
			$status = true;
			/** Check if a user exist with it's email **/
			$checking_user = get_user_by( 'login', $user_email);
			if ( !empty($checking_user) ) {
				$login_action = true;
				$user_firstname = get_user_meta( $checking_user->ID, 'first_name', true );
				$response = $user_firstname;
			}
			else {
				$checking_user = get_user_by( 'email', $user_email);
				if ( !empty( $checking_user ) ) {
					$login_action = true;
					$user_firstname = get_user_meta( $checking_user->ID, 'first_name', true );
					$response = $user_firstname;
				}
			}

			if( !$login_action && is_email($user_email)  ) {
				$response = $user_email;
			}
		}
		else {
			$response = '<div class="wps-alert-error">' .__( 'An e-mail address is required', 'wpshop' ). '</div>';
		}
		echo json_encode( array( 'status'=> $status, 'response' => $response, 'login_action' => $login_action) );
		die();
	}

	/**
	 * FORGOT PASSWORD - Display the forgot Password Form
	 */
	function get_forgot_password_form() {
		$output = '';
		if ( get_current_user_id() == 0 ) {
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL,  "frontend", "forgot-password/forgot-password") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	/**
	 * FORGOT PASSWORD - AJAX - Fill the forgot password modal
	 */
	function wps_fill_forgot_password_modal() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_fill_forgot_password_modal' ) )
			wp_die();

		$status = false; $title = $content = '';
		$title = __( 'Forgot password', 'wpshop' );
		$content = do_shortcode('[wps_forgot_password]');
		$status = true;
		echo json_encode( array('status' => $status, 'title' => $title, 'content' => $content) );
		wp_die();
	}

	/**
	 * FORGOT PASSWORD- AJAX - Forgot Password Request
	 */
	function wps_forgot_password_request() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_forgot_password_request' ) )
			wp_die();

		global $wpdb;
		$status = false; $result = '';
		$user_login = ( !empty( $_POST['wps_user_login']) ) ? wpshop_tools::varSanitizer($_POST['wps_user_login']) : null;
		if ( !empty($user_login) ) {
			$existing_user = false;
			$key_for_update = 'user_login';
			$exist_user = get_user_by('login', $user_login);
			if( !empty($exist_user) ) {
				$existing_user = true;
			}
			else {
				$exist_user = get_user_by('email', $user_login);
				$key_for_update = 'user_email';
				if ( !empty($exist_user) ) {
					$existing_user = true;
				}
			}

			if ( $existing_user ) {
				$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE $key_for_update = %s", $user_login));
				if ( empty($key) ) {
					$key = wp_generate_password(20, false);
					$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
				}
				$this->send_forgot_password_email($key, $user_login, $exist_user);
				$result = '<div class="wps-alert-info">' .__('An e-mail with an password renew link has been sent to you', 'wpshop'). '</div>';
				$status = true;
			}
			else {
				$result = '<div class="wps-alert-error">' .__('No customer account corresponds to this email', 'wpshop'). '</div>';
			}
		}
		else {
			$result = '<div class="wps-alert-error">' .__('Please fill the required field', 'wpshop'). '</div>';
		}
		$response = array( $status, $result );
		echo json_encode( $response );
		die();
	}

	/**
	 * FORGOT PASSWORD - Send Forgot Password Email Initialisation
	 * @param string $key
	 * @param string $user_login
	 */
	function send_forgot_password_email($key, $user_login, $exist_user){
		$user_data = $exist_user->data;
		$email = $user_data->user_email;
		$wps_message = new wps_message_ctr();
		$first_name = get_user_meta( $user_data->ID, 'first_name', true );
		$last_name = get_user_meta( $user_data->ID, 'last_name', true );
		$permalink_option = get_option( 'permalink_structure' );
		$link = '<a href="' .get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ).( (!empty($permalink_option)) ? '?' : '&').'order_step=2&action=retrieve_password&key=' .$key. '&login=' .rawurlencode($user_login). '">' .get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ). '&action=retrieve_password&key=' .$key. '&login=' .rawurlencode($user_login). '</a>';
		if( !empty($key) && !empty( $user_login ) ) {
			$wps_message->wpshop_prepared_email($email,
			'WPSHOP_FORGOT_PASSWORD_MESSAGE',
			array( 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'forgot_password_link' => $link)
			);
		}
	}

	/** FORGOT PASSWORD - AJAX - Make renew password action **/
	function wps_forgot_password_renew() {
		check_ajax_referer( 'wps_forgot_password_renew' );

		global $wpdb;
		$status = false; $result = $form = '';
		$password = ( !empty( $_POST['pass1']) ) ? wpshop_tools::varSanitizer( $_POST['pass1'] ) : null;
		$confirm_password = ( !empty( $_POST['pass2']) ) ? wpshop_tools::varSanitizer( $_POST['pass2'] ) : null;
		$activation_key = ( !empty( $_POST['activation_key']) ) ?  wpshop_tools::varSanitizer( $_POST['activation_key'] ) : null;
		$login = ( !empty( $_POST['user_login']) ) ?  wpshop_tools::varSanitizer( $_POST['user_login'] ) : null;
		if ( !empty($password) && !empty($confirm_password) && $confirm_password == $password ) {
			if ( !empty($activation_key) && !empty($login) ) {
				$existing_user = false;
				$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $activation_key, $login ) );
				if( !empty($user) ) {
					$existing_user = true;
				}
				else {
					$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_email = %s", $activation_key, $login ) );
					if( !empty($user) ) {
						$existing_user = true;
					}
				}

				if ( $existing_user ){
					wp_set_password($password, $user->ID);
					wp_password_change_notification($user);
					$status = true;
					$result = '<div class="wps-alert-success">' .__('Your password has been updated', 'wpshop'). '. <a href="#" id="display_connexion_form"> ' .__('Connect you', 'wpshop').' !</a></div>';
					$form = self::get_login_form( true );
				}
				else {
					$result = '<div class=" wps-alert-error">' .__('Invalid activation key', 'wpshop'). '</div>';
				}
			}
			else {
				$result = '<div class=" wps-alert-error">' .__('Invalid activation key', 'wpshop'). '</div>';
			}
		}
		else {
			$result = '<div class="wps-alert-error">' .__('Password and confirmation password are differents', 'wpshop'). '</div>';
		}

		$response = array( $status, $result, $form );
		echo json_encode( $response);
		die();
	}

	/**
	 * FORGOT PASSWORD - Display renew password interface
	 * @return string
	 */
	function get_renew_password_form() {
		if ( get_current_user_id() == 0 ) {
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, "frontend", "forgot-password/password-renew") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	/** FORGOT PASSWORD - AJAX - Get Forgot Password form **/
	function wps_ajax_get_forgot_password_form() {
		echo json_encode( array(self::get_forgot_password_form() ) );
		die();
	}

	/**
	 * SIGN UP - Display Sign up form
	 * @return string
	 */
	function display_signup( $args = array() ) {
		global $wpdb;
		$output = '';
		if ( get_current_user_id() == 0 || !empty($args) ) {
			$fields_to_output = $signup_fields = array();

			$password_attribute = $signup_form_attributes =  array();

			$entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );

			$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_SET.' WHERE entity_id = %d', $entity_id);
			$customer_entity_id = $wpdb->get_var( $query );
			$attributes_set = wpshop_attributes_set::getElement($customer_entity_id);
			$account_attributes = wpshop_attributes_set::getAttributeSetDetails( ( !empty($attributes_set->id) ) ? $attributes_set->id : '', "'valid'");
			$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_GROUP.' WHERE attribute_set_id = %d AND status = %s', $attributes_set->id, 'valid' );
			$customer_attributes_sections = $wpdb->get_results( $query );
			foreach( $customer_attributes_sections as $k => $customer_attributes_section ) {
				foreach( $account_attributes[$customer_attributes_section->id]['attribut'] as $attribute ) {
					$signup_fields[] = $attribute;
				}
			}
			ob_start();
			require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, "frontend", "signup/signup") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	function wps_save_signup_form_nopriv() {
		$this->wps_save_signup_form( true );
	}

	/**
	 * SIGN UP - Save sign up form
	 */
	function wps_save_signup_form( $connect = false ) {
		check_ajax_referer( 'wps_save_signup_form' );

		global $wpdb, $wpshop;
		$user_id = ( !empty( $_POST['wps_sign_up_request_from_admin'] ) ) ? (int) $_POST['wps_sign_up_request_from_admin'] : get_current_user_id();
		$wps_message = new wps_message_ctr();
		$status = $account_creation = false; $result = '';
		$exclude_user_meta = array( 'user_email', 'user_pass' );
		$attribute = !empty( $_POST['attribute'] ) ? (array) $_POST['attribute'] : array();
		$element_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
		if ( !empty( $element_id) ){
			$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d', $element_id );
			$attribute_set_id = $wpdb->get_var( $query );
			if ( !empty($attribute_set_id) ){
				$group = wps_address::get_addresss_form_fields_by_type( $attribute_set_id );
				foreach ( $group as $attribute_sets ) {
					foreach ( $attribute_sets as $attribute_set_field ) {
						if( !empty($user_id) ) {
							foreach( $attribute_set_field['content'] as $attribute_code => $att_def ) {
								if( $attribute_code != 'account_user_email' ) {
									$attribute_set_field['content'][$attribute_code]['required'] = 'no';
								}
							}
						}
						$validate = $wpshop->validateForm($attribute_set_field['content'], $attribute );
					}
					if ( empty($wpshop->errors) ) {
						$user_name = !empty($attribute['varchar']['user_login']) ? sanitize_text_field( $attribute['varchar']['user_login'] ) : sanitize_email( $attribute['varchar']['user_email'] );
						$user_pass = ( !empty($attribute['varchar']['user_pass']) && !empty($_POST['wps_signup_account_creation']) ) ? sanitize_text_field( $attribute['varchar']['user_pass'] ) : wp_generate_password( 12, false );

						if ( $user_id == 0  ) {
							$user_id = wp_create_user($user_name, $user_pass, sanitize_email( $attribute['varchar']['user_email'] ) );
							if ( !is_object( $user_id) ) {
								$account_creation = true;
								/** Update newsletter user preferences **/
								$newsletter_preferences = array();
								$newsletters_site = !empty( $_POST['newsletters_site'] ) ? (bool) $_POST['newsletters_site'] : false;
								if( $newsletters_site ) {
									$newsletter_preferences['newsletters_site'] = 1;
								}
								$newsletters_site_partners = !empty( $_POST['newsletters_site_partners'] ) ? (bool) $_POST['newsletters_site_partners'] : false;
								if( $newsletters_site_partners ) {
									$newsletter_preferences['newsletters_site_partners'] = 1;
								}

								update_user_meta( $user_id, 'user_preferences', $newsletter_preferences);
							}
						}

						$customer_entity_request = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $user_id);
						$customer_post_ID = $wpdb->get_var( $customer_entity_request );

						if( !empty( $attribute ) ) {
							$user_info = $address_forms = $all_addresses_form = array();
							$billing_option = get_option( 'wpshop_billing_address' );
							foreach( $attribute as $type => $attributes ) {
								if( !empty( $billing_option['integrate_into_register_form'] ) && $billing_option['integrate_into_register_form'] == 'yes' && ctype_digit( (string) $type ) ) {
									//wps_address::save_address_infos( (int) $type );
									foreach( $attributes as $sub_type => $sub_attributes ) {
										if( !is_array( $sub_attributes ) ) {
											$address_forms[$type][$sub_type] = sanitize_text_field( $sub_attributes );
											continue;
										}
										foreach( $sub_attributes as $sub_meta => $sub_attribute_value ) {
											$address_forms[$type][$sub_type][$sub_meta] = sanitize_text_field( $sub_attribute_value );
										}
									}
								} else {
									foreach( $attributes as $meta => $attribute_value ) {
										$user_info[$meta] = sanitize_text_field( $attribute_value );
										if( !empty( $billing_option['integrate_into_register_form'] ) && $billing_option['integrate_into_register_form'] == 'yes' && isset( $billing_option['integrate_into_register_form_matching_field'], $billing_option['integrate_into_register_form_matching_field'][$meta] ) ) {
											$all_addresses_form[$type][$meta] = $user_info[$meta];
										}
									}
								}
							}
							wps_customer_ctr::save_customer_synchronize( $customer_post_ID, $user_id, $user_info );
							foreach( $address_forms as $type_of_form => $address_form ) {
								$address_form = array_merge_recursive( $all_addresses_form, $address_form );
								wps_address::save_address_infos( (int) $type_of_form, 0, array( 'type_of_form' => (int) $type_of_form, 'attribute' => array( $type_of_form => $address_form ) ), $customer_post_ID );
							}
						}

						if ( !empty( $_SESSION ) && !empty( $_SESSION[ 'cart' ] ) ) {
							$permalink_option = get_option( 'permalink_structure' );
							$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ));
							$result = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=3';
						}
						else {
							$account_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_myaccount_page_id' ));
							$result = get_permalink( $account_page_id  );
						}
						$status = true;

						if ( $account_creation && !empty( $user_id ) && $connect ) {
							$secure_cookie = is_ssl() ? true : false;
							wp_set_auth_cookie( $user_id, true, $secure_cookie );
						}
						$wps_message->wpshop_prepared_email( sanitize_email($attribute['varchar']['user_email']), 'WPSHOP_SIGNUP_MESSAGE', array('customer_first_name' => ( !empty($attribute['varchar']['first_name']) ) ? sanitize_text_field( $attribute['varchar']['first_name'] ) : '', 'customer_last_name' => ( !empty($attribute['varchar']['last_name']) ) ? sanitize_text_field( $attribute['varchar']['last_name'] ) : '', 'customer_user_email' => ( !empty($attribute['varchar']['user_email']) ) ? sanitize_email( $attribute['varchar']['user_email'] ) : '') );

					} else {
						$result = '<div class="wps-alert-error">' .__('Some errors have been detected', 'wpshop') . ' : <ul>';
						foreach(  $wpshop->errors as $error ){
							$result .= '<li>' .$error. '</li>';
						}
						$result .= '</div>';
					}
				}

			}
		}

		wp_die( json_encode( array( $status, $result, $user_id, $customer_post_ID ) ) );
	}

	/** SIGN UP - Display the commercial & newsletter form
	 * @return void
	 */
	function display_commercial_newsletter_form() {
		$output = '';
		$user_preferences = get_user_meta( get_current_user_id(), 'user_preferences', true );
		$wpshop_cart_option = get_option( 'wpshop_cart_option' );
		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL,  "frontend", "signup/signup", "newsletter") );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Affichage du shortcode générant le compte client
	 *
	 * @version 1.4.4.3
	 *
	 * @param  array $args Les arguments passés au shortcode.
	 */
	function shortcode_callback_display_account_informations( $args ) {
		$customer_id = ! empty( $args ) && ! empty( $args['cid'] ) ? (int) $args['cid'] : wps_customer_ctr::get_customer_id_by_author_id( get_current_user_id() );
		return $this->display_account_informations( $customer_id );
	}

	/**
	 * Affiche les champs du formualire d'édition des informations d'un compte client
	 *
	 * @param  integer $cid L'identifiant du client dont il faut afficher le formulaire d'édition.
	 *
	 * @return string     L'afficahge HTML des champs
	 */
	function display_account_form_fields( $cid ) {
		global $wpdb;

		$customer_entity_type_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
		$fields_to_output = $signup_form_section = $password_attribute = $signup_form_attributes = array();

		$query = $wpdb->prepare( 'SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d', $customer_entity_type_id );
		$customer_entity_id = $wpdb->get_var( $query );
		$attributes_set = wpshop_attributes_set::getElement( $customer_entity_id );
		$account_attributes = wpshop_attributes_set::getAttributeSetDetails( ( ! empty( $attributes_set->id ) ) ? $attributes_set->id : '', "'valid'" );
		$query = $wpdb->prepare( 'SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_GROUP . ' WHERE attribute_set_id = %d', $attributes_set->id );
		$customer_attributes_sections = $wpdb->get_results( $query );
		foreach ( $customer_attributes_sections as $k => $customer_attributes_section ) {
			$signup_form_section[ $customer_attributes_section->name ] = array();
			if ( ! empty( $account_attributes[ $customer_attributes_section->id ] ) ) {
				foreach ( $account_attributes[ $customer_attributes_section->id ]['attribut'] as $attribute ) {
					$signup_form_section[ $customer_attributes_section->name ][] = $attribute;
				}
			}
		}

		ob_start();
		require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, 'common/account', 'account', 'form' ) );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * ACCOUNT - Display Account informations
	 *
	 * @return string
	 */
	function display_account_informations( $customer_id = '', $force_edition_form = false, $customer_link = false ) {
		global $wpdb;
		$output = $attributes_sections_tpl = $attribute_details = '';
		$customer_id = ( ! empty( $customer_id ) ) ? $customer_id : get_current_user_id();
		if ( 0 !== $customer_id ) {
			$screen = get_current_screen();
			$customer_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
			$cid = $customer_id;

			if ( ( is_admin() && isset( $screen ) && is_object( $screen ) && ( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS == $screen->post_type ) ) || $force_edition_form ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, 'backend/customer-informations', 'form' ) );
				$output = ob_get_contents();
				ob_end_clean();
			} else {
				if ( ! empty( $customer_entity_id ) ) {
					$query = $wpdb->prepare( 'SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d AND status = %s AND default_set = %s', $customer_entity_id, 'valid', 'yes' );
					$attributes_sets = $wpdb->get_results( $query );
					foreach( $attributes_sets as $attributes_set ) {
						if( !empty($attributes_set->id) ) {
							$query = $wpdb->prepare( 'SELECT * FROM '. WPSHOP_DBT_ATTRIBUTE_GROUP. ' WHERE attribute_set_id = %d AND status = %s', $attributes_set->id, 'valid');
							$attributes_sections = $wpdb->get_results( $query );

							if( !empty($attributes_sections) ) {
								foreach( $attributes_sections as $attributes_section ) {
									$query = $wpdb->prepare( 'SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE status = %s AND entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d', 'valid', $customer_entity_id, $attributes_set->id, $attributes_section->id);
									$attributes_details = $wpdb->get_results( $query );
									$attribute_details = '';
									foreach( $attributes_details as $attributes_detail ) {
										$query = $wpdb->prepare( 'SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE id = %d AND status = %s', $attributes_detail->attribute_id, 'valid' );
										$attribute_def = $wpdb->get_row( $query );

										$query = $wpdb->prepare( 'SELECT value  FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.strtolower($attribute_def->data_type). ' WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d ', $customer_entity_id, $attribute_def->id, $cid );
										$attribute_value = $wpdb->get_var( $query );

										/**	Check attribute type for specific type display	*/
										if ( "datetime" == $attribute_def->data_type ) {
											$attribute_value = mysql2date( get_option( 'date_format' ) . ( ( substr( $attribute_value, -9 ) != ' 00:00:00' ) ? ' ' . get_option( 'time_format' ) : '' ), $attribute_value, true);
										}

										/**	Check attribute input type in order to get specific value	*/
										if ( in_array( $attribute_def->backend_input, array( 'multiple-select', 'select', 'radio', 'checkbox' ) ) ) {
											if ( $attribute_def->data_type_to_use == 'custom' ) {
												$query = $wpdb->prepare("SELECT label FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d AND status = 'valid' AND id = %d", $attribute_def->id, $attribute_value );
												$attribute_value = $wpdb->get_var( $query );
											} elseif ( $attribute_def->data_type_to_use == 'internal' ) {
												$associated_post = get_post( $atribute_value );
												$attribute_value = $associated_post->post_title;
											}
										}

										if( !empty( $attribute_def ) ) {
											if( $attribute_def->frontend_input != 'password' ) {
												ob_start();
												require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, "frontend", "account/account_informations_element") );
												$attribute_details .= ob_get_contents();
												ob_end_clean();
											}
										}
									}

									ob_start();
									require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, "frontend", "account/account_informations_group_element") );
									$attributes_sections_tpl .= ob_get_contents();
									ob_end_clean();
								}
							}
						}
					}
				}

				ob_start();
				require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, 'frontend', 'account/account_informations' ) );
				$output = ob_get_contents();
				ob_end_clean();
			}
		}
		return $output;
	}


	/**
	 * ACCOUNT - AJAX - Fill account informations modal
	 */
	function wps_fill_account_informations_modal() {
		check_ajax_referer( 'wps_fill_account_informations_modal' );

		$customer_id_from_cookie = ! empty( $_COOKIE ) && ! empty( $_COOKIE['wps_current_connected_customer'] ) ? (int) $_COOKIE['wps_current_connected_customer'] : wps_customer_ctr::get_customer_id_by_author_id( get_current_user_id() );
		$customer_id = ! empty( $_POST ) && ! empty( $_POST['customer_id'] ) && is_int( (int) $_POST['customer_id'] ) ? (int) $_POST['customer_id'] : $customer_id_from_cookie;

		$title = __( 'Edit your account informations', 'wpshop' );
		$content = $this->account_informations_form( $customer_id );

		wp_die( wp_json_encode( array( 'status' => true, 'title' => $title, 'content' => $content ) ) );
	}

	/**
	 * ACCOUNT - Edit account informations data
	 *
	 * @param integer $cid L'identifiant du client pour lequel on veut modifier les informations.
	 */
	function account_informations_form( $cid = 0 ) {
		global $wpdb;
		$output = '';

		if ( 0 !== $cid ) {
			ob_start();
			require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, 'frontend', 'account/account_form' ) );
			$output = ob_get_contents();
			ob_end_clean();
		}

		return $output;
	}

	function save_account_informations( $cid, $args, $admin = true ) {
		global $wpdb, $wpshop;

		$exclude_user_meta = array( 'user_email', 'user_pass' );
		$wps_entities = new wpshop_entities();
		$element_id = $wps_entities->get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );

		$user_id = wps_customer_ctr::get_author_id_by_customer_id( $cid );

		$user_name = ! empty( $args['attribute']['varchar']['user_login'] ) ? $args['attribute']['varchar']['user_login'] : $args['attribute']['varchar']['user_email'];
		$user_pass = ! empty( $args['attribute']['varchar']['user_pass'] ) ? $args['attribute']['varchar']['user_pass'] : '';

		$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d', $element_id );
		$attribute_set_id = $wpdb->get_var( $query );
		if ( !empty($attribute_set_id) ) {
			$group  = wps_address::get_addresss_form_fields_by_type( $attribute_set_id );
			//Save data in attribute tables, ckeck first if exist to know if Insert or Update
			wpshop_attributes::saveAttributeForEntity( $args['attribute'], $element_id, $cid );
			foreach ( $group as $attribute_sets ) {
				foreach ( $attribute_sets as $attribute_set_field ) {
					if ( $admin ) {
						$validate = $wpshop->validateForm( $attribute_set_field['content'], $args['attribute'] );
					}

					if ( empty($wpshop->errors) || !$admin ) {
						$wpshop_attributes = new wpshop_attributes();
						foreach( $attribute_set_field['content'] as $attribute ) {
							$attribute_def = wpshop_attributes::getElement( $attribute['name'], "'valid'", 'code');
							if ( !in_array( $attribute['name'], $exclude_user_meta ) ) {
								update_user_meta( $user_id, $attribute['name'], wpshop_tools::varSanitizer( $args['attribute'][$attribute['data_type']][$attribute['name']])  );
							} else {
								wp_update_user( array( 'ID' => $user_id, $attribute['name'] => wpshop_tools::varSanitizer( $args['attribute'][$attribute['data_type']][$attribute['name']]) ) );
							}
						}
						/** Update newsletter user preferences **/
						$newsletter_preferences = array();
						if( !empty($args['newsletters_site']) ) {
							$newsletter_preferences['newsletters_site'] = 1;
						}
						if( !empty($args['newsletters_site_partners']) ) {
							$newsletter_preferences['newsletters_site_partners'] = 1;
						}
						update_user_meta( $user_id, 'user_preferences', $newsletter_preferences);
					} else {
						return $wpshop->errors;
					}
				}
			}
		}
	}

	/**
	 * ACCOUNT - Save account informations
	 */
	function wps_save_account_informations() {
		check_ajax_referer( 'wps_save_account_informations' );

		$status = true;
		$response = '';

		$customer_id_from_cookie = ! empty( $_COOKIE ) && ! empty( $_COOKIE['wps_current_connected_customer'] ) ? (int) $_COOKIE['wps_current_connected_customer'] : wps_customer_ctr::get_customer_id_by_author_id( get_current_user_id() );
		$customer_id = ! empty( $_POST ) && ! empty( $_POST['customer_id'] ) && is_int( (int) $_POST['customer_id'] ) ? (int) $_POST['customer_id'] : $customer_id_from_cookie;

		$errors = $this->save_account_informations( $customer_id, $_POST );
		if ( ! empty( $errors ) ) {
			$response = '<div class="wps-alert-error">' . __( 'Some errors have been detected', 'wpshop' ) . ' : <ul>';
			foreach ( $errors as $error ) {
				$response .= '<li>' . $error . '</li>';
			}
			$response .= '</div>';
		} else {
			$status = true;
			$response = $customer_id;
		}

		wp_die( wp_json_encode( array( 'status' => $status, 'response' => $response ) ) );
	}

	/**
	 * ACCOUNT - AJAX - Reload account informations data
	 */
	function wps_account_reload_informations() {
		// check_ajax_referer( 'wps_account_reload_informations' );

		$customer_id_from_cookie = ! empty( $_COOKIE ) && ! empty( $_COOKIE['wps_current_connected_customer'] ) ? (int) $_COOKIE['wps_current_connected_customer'] : wps_customer_ctr::get_customer_id_by_author_id( get_current_user_id() );
		$customer_id = ! empty( $_POST ) && ! empty( $_POST['customer_id'] ) && is_int( (int) $_POST['customer_id'] ) ? (int) $_POST['customer_id'] : $customer_id_from_cookie;

		$status = false;
		$response = $this->display_account_informations( $customer_id );
		if ( ! empty( $response ) ) {
			$status = true;
		}

		wp_die( wp_json_encode( array( 'status' => $status, 'response' => $response ) ) );
	}

}
