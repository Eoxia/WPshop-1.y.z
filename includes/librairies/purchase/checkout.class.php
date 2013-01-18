<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Checkout
 *
 * The WPShop checkout class handles the checkout process, collecting user data and processing the payment.
 *
 * @class 		wpwhop_checkout
 * @package		WPShop
 * @category	Class
 * @author		Eoxia
 */

/* Instantiate the class from the shortcode */
function wpshop_checkout_init() {
	global $wpshop_checkout;
	$wpshop_checkout = new wpshop_checkout();
	return $wpshop_checkout->display_form();
}

class wpshop_checkout {

	var $div_register, $div_infos_register, $div_login, $div_infos_login = 'display:block;';
	var $creating_account = true;

	/** Constructor of the class
	* @return void
	*/
	function __construct () {
	}

	/**
	 * Display checkout form
	 *
	 * @return boolean|string
	 */
	function display_form() {
		global $wpshop, $wpshop_account, $wpshop_cart, $civility, $wpshop_signup;
		$output = '';

		/**	In case customer want to cancel order	*/
		if ( !empty($_GET['action']) && ($_GET['action']=='cancel') ) {
			$wpshop_cart->empty_cart();

			 return __('Your order has been succesfully cancelled.', 'wpshop');
		}

		/**	Cart is empty -> Display message*/
		if($wpshop_cart->is_empty() && empty($_POST['order_id'])) :
			$output .= '<p>'.__('Your cart is empty. Select product(s) before checkout.','wpshop').'</p>';
		/**	Cart is not empty -> Check current step	*/
		else :
			/**	Check cart type for current order	*/
			$cart_type = (!empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type']=='quotation') ? 'quotation' : 'cart';

			/**	Check action to launch relative to post nformation	*/
			$form_is_ok = $this->managePost( $cart_type );

			/**	Get available payment method	*/
			$paymentMethod = get_option('wpshop_paymentMethod', array());

			/**	Store order id into Session	*/
			$_SESSION['order_id'] = !empty($_POST['order_id']) ? $_POST['order_id'] : (!empty($_SESSION['order_id']) ? $_SESSION['order_id'] : 0);

			/**	if user ask a quotation	*/
			if ( $form_is_ok && isset($_POST['takeOrder']) && $cart_type=='quotation') {
				$output .= '<p>'.__('Thank you ! Your quotation has been sent. We will respond to you as soon as possible.', 'wpshop').'</p>';

				/**	Empty customer cart	*/
				$wpshop_cart->empty_cart();
			}
			/**	If user want to pay with paypal	*/
			elseif($form_is_ok && !empty($paymentMethod['paypal']) && isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='paypal') {
				wpshop_paypal::display_form($_SESSION['order_id']);

				/**	Empty customer cart	*/
				$wpshop_cart->empty_cart();
			}
			/**	If user want to pay by check	*/
			elseif($form_is_ok && !empty($paymentMethod['checks']) && isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='check') {
				// On recupere les informations de paiements par cheque
				$paymentInfo = get_option('wpshop_paymentAddress', true);
				$tpl_component = array();
				if ( !empty($paymentInfo) ) {
					foreach ( $paymentInfo as $key => $value) {
						$tpl_component['CHECK_CONFIRMATION_MESSAGE_' . strtoupper($key)] = $value;
					}
				}
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_check_confirmation_message', $tpl_component);

				/**	Empty customer cart	*/
				$wpshop_cart->empty_cart();
			}
			/**	If Credit card by CIC is actived And the user selected this payment method	*/
			elseif($form_is_ok && isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='cic') {
				wpshop_CIC::display_form($_SESSION['order_id']);

				/**	Empty customer cart	*/
				$wpshop_cart->empty_cart();
			}
			else {
				$user_id = get_current_user_id();
				if ($user_id) {
					$tpl_component = array();

					/** Display customer addresses */
					$tpl_component['CHECKOUT_CUSTOMER_ADDRESSES_LIST'] = wpshop_account::display_addresses_dashboard();

					/** Display cart content	*/
					$tpl_component['CHECKOUT_SUMMARY_TITLE'] = ($cart_type=='quotation') ? __('Summary of the quotation','wpshop') : __('Summary of the order','wpshop');
					$tpl_component['CHECKOUT_CART_CONTENT'] = $wpshop_cart->display_cart(true);

					$tpl_component['CHECKOUT_TERM_OF_SALES'] = '';
					$option_page_id_terms_of_sale = get_option('wpshop_terms_of_sale_page_id');
					if ( !empty($option_page_id_terms_of_sale) ) {
						$input_def['type'] = 'checkbox';
						$input_def['id'] = $input_def['name'] = 'terms_of_sale';

						$input_def['options']['label']['custom'] = sprintf( __('I have read and I accept %sthe terms of sale%s', 'wpshop'), '<a href="' . get_permalink($option_page_id_terms_of_sale) . '">', '</a>');
						$tpl_component['CHECKOUT_TERM_OF_SALES'] = '<div class="infos_bloc" id="wpshop_terms_acceptation_box" >'.wpshop_form::check_input_type($input_def). '</div>';
					}

					/** Display available payment methods	*/
					$available_payement_method = wpshop_payment::display_payment_methods_choice_form(0, $cart_type);
					$tpl_component['CHECKOUT_PAYMENT_METHODS'] = $available_payement_method[0];

					/**	Display order validation button in case payment methods are available	*/
					$tpl_component['CHECKOUT_PAYMENT_BUTTONS_CONTAINER'] = ' class="wpshop_checkout_button_container" ';
					if(!empty($available_payement_method[1]['paypal']) || !empty($available_payement_method[1]['checks']) || WPSHOP_PAYMENT_METHOD_CIC || !empty($available_payement_method[1]['cic']) || ($cart_type == 'quotation')) {
						$tpl_component['CHECKOUT_PAYMENT_BUTTONS'] = wpshop_display::display_template_element('wpshop_checkout_page_validation_button', array('CHECKOUT_PAGE_VALIDATION_BUTTON_TEXT' => ($cart_type=='quotation') ? __('Ask the quotation', 'wpshop') : __('Order', 'wpshop')));
					}
					else{
						$tpl_component['CHECKOUT_PAYMENT_BUTTONS_CONTAINER'] = str_replace('_container"', '_container wpshop_checkout_button_container_no_method"', $tpl_component['CHECKOUT_PAYMENT_BUTTONS_CONTAINER']);
						$tpl_component['CHECKOUT_PAYMENT_BUTTONS'] = __('It is impossible to order for the moment','wpshop');
					}

					$output .= wpshop_display::display_template_element('wpshop_checkout_page', $tpl_component);
					unset($tpl_component);
				}
				else {
 					$output .= '<div class="infos_bloc" id="infos_register" style="'.$this->div_infos_register.'">'.__('Already registered? <a href="#" class="checkoutForm_login">Please login</a>.','wpshop').'</div>';
 					$output .= '<div class="infos_bloc" id="infos_login" style="'.$this->div_infos_login.'">'.__('Not already registered? <a href="#" class="checkoutForm_login">Please register</a>.','wpshop').'</div>';

					// Bloc LOGIN
					$output .= '<div class="col1" id="login" style="'.$this->div_login.'">';
					$output .= $wpshop_account->display_login_form();
					$output .= '</div>';

					$output .= '<div class="col1" id="register" style="'.$this->div_register.'">';
					wpshop_signup::display_form();
					$output .= '</div>';
				}
			}
		endif;

		return $output;
	}

	/**
	 * Validate an order. When customer validate checkout page this function do treatment for payment method
	 *
	 * @return boolean False if errors occured|True if all is OK
	 */
	function managePost( $cart_type ) {
		global $wpshop;

		/**	If the user validate the checkout page	*/
		if(isset($_POST['takeOrder'])) {
			/** Billing adress if mandatory	*/
			if ( !isset($_POST['billing_address']) ) {
				$wpshop->add_error(__('You must choose a billing address.', 'wpshop'));
			}
			else {
				/**	 If a order_id is given, meaning that the order is already created and the user wants to process to a new payment	*/
				$order_id = !empty($_POST['order_id']) && is_numeric($_POST['order_id']) ? $_POST['order_id'] : 0;

				/**	User ask a quotation for its order	*/
				if ($cart_type=='quotation') {
					$this->process_checkout($paymentMethod='quotation', $order_id);
				}
				/**	Customer want to pay its order with one of available payment method 	*/
				elseif(isset($_POST['modeDePaiement']) && in_array( $_POST['modeDePaiement'], array('paypal', 'check', 'cic') )) {
					$this->process_checkout($_POST['modeDePaiement'], $order_id);
				}
				/**	Customer does not select any payment method for its order and it's not a quotation -> Display a error message to choose a payment method	*/
				else $wpshop->add_error(__('You have to choose a payment method to continue.', 'wpshop'));
			}
		}
		else {
			$this->div_login = $this->div_infos_login = 'display:none';
		}

		/**	Display errors only in case the current cart is not a quotation	*/
		if ( ($cart_type == 'cart') && ($wpshop->error_count() > 0)) {
			echo $wpshop->show_messages();
			return false;
		}

		return true;
	}


	/** Enregistre la commande dans la bdd apr�s que les champs aient �t� valid�, ou que l'utilisateur soit connect�
	 * @param int $user_id=0 : id du client passant commande. Par d�faut 0 pour un nouveau client
	 * @return void
	*/
	function process_checkout($paymentMethod='paypal', $order_id=0) {
		global $wpdb, $wpshop, $wpshop_cart;

		if (is_user_logged_in()) :
			$user_id = get_current_user_id();

			// If the order is already created in the db
			if(!empty($order_id) && is_numeric($order_id)) {
				$order = get_post_meta($order_id, '_order_postmeta', true);
				if(!empty($order)) {
					if($order['customer_id'] == $user_id) {
						$order['payment_method'] = $paymentMethod;
						// On enregistre la commande
						update_post_meta($order_id, '_order_postmeta', $order);
						update_post_meta($order_id, '_wpshop_order_customer_id', $user_id);
						update_post_meta($order_id, '_wpshop_payment_method', $paymentMethod);
					}
					else $wpshop->add_error(__('You don\'t own the order', 'wpshop'));
				}
				else $wpshop->add_error(__('The order doesn\'t exist.', 'wpshop'));
			}
			else
			{
				$order_data = array(
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
					'post_title' => sprintf(__('Order - %s','wpshop'), mysql2date('d M Y\, H:i:s', current_time('mysql', 0), true)),
					'post_status' => 'publish',
					'post_excerpt' => !empty($_POST['order_comments']) ? $_POST['order_comments'] : '',
					'post_author' => $user_id,
					'comment_status' => 'closed'
				);

				// Cart items
				$order_items = array();
				$order_tva = array();

				//$cart = (array)$wpshop_cart->cart;
				$cart = (array)$_SESSION['cart'];

				$download_codes = array();

				// Nouvelle commande
				$order_id = wp_insert_post($order_data);
				$_SESSION['order_id'] = $order_id;

				// Cr�ation des codes de t�l�chargement si il y a des produits t�l�chargeable dans le panier
				foreach($cart['order_items'] as $c) {
					$product = wpshop_products::get_product_data($c['item_id']);
					if(!empty($product['is_downloadable_'])) {
						$download_codes[$c['item_id']] = array('item_id' => $c['item_id'], 'download_code' => uniqid('', true));
					}
				}
				if(!empty($download_codes)) update_user_meta($user_id, '_order_download_codes_'.$order_id, $download_codes);

				// Informations de commande � stocker
				$currency = wpshop_tools::wpshop_get_currency(true);
				$order = array_merge(array(
					'order_key' => NULL,
					'customer_id' => $user_id,
					'order_status' => 'awaiting_payment',
					'order_date' => current_time('mysql', 0),
					'order_payment_date' => null,
					'order_shipping_date' => null,
					'payment_method' => $paymentMethod,
					'order_invoice_ref' => '',
					'order_currency' => $currency
				), $cart);

				// Si c'est un devis
				if ( $paymentMethod == 'quotation' ) {
					$order['order_temporary_key'] = wpshop_orders::get_new_pre_order_reference();
				}
				else {
					$order['order_key'] = wpshop_orders::get_new_order_reference();
				}

				// On enregistre la commande
				update_post_meta($order_id, '_order_postmeta', $order);

				update_post_meta($order_id, '_wpshop_order_customer_id', $order['customer_id']);
				update_post_meta($order_id, '_wpshop_order_shipping_date', $order['order_shipping_date']);
				update_post_meta($order_id, '_wpshop_order_status', $order['order_status']);
				update_post_meta($order_id, '_wpshop_order_payment_date', $order['order_payment_date']);
				update_post_meta($order_id, '_wpshop_payment_method', $order['payment_method']);

				/*	Set custmer information for the order	*/
				wpshop_orders::set_order_customer_addresses($user_id, $order_id, $_POST['shipping_address'], $_POST['billing_address']);

				/*	Notify the customer as the case	*/
				$user_info = get_userdata($user_id);
				$email = $user_info->user_email;
				$first_name = $user_info->user_firstname ;
				$last_name = $user_info->user_lastname;
				// Envoie du message de confirmation de commande au client
				wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', array('customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => current_time('mysql', 0)));
			}

		endif;
	}

}