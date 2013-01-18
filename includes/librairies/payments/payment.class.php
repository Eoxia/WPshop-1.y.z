<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Wpshop Payment Gateway
 *
 * @class 		wpshop_payment
 * @package		WP-Shop
 * @category	Payment Gateway
 * @author		Eoxia
 */
class wpshop_payment {

	public function __construct() {
		global $wpshop;

		$wpshop_paypal = new wpshop_paypal();
		// If the CIC payment method is active
		$wpshop_paymentMethod = get_option('wpshop_paymentMethod');
		if(WPSHOP_PAYMENT_METHOD_CIC || !empty($wpshop_paymentMethod['cic'])) {
			$wpshop_cic = new wpshop_CIC();
		}

	}

	function get_success_payment_url() {
		$default_url = get_permalink(get_option('wpshop_payment_return_page_id'));
		$url = get_option('wpshop_payment_return_url',$default_url);
		return self::construct_url_parameters($url, 'paymentResult', 'success');
	}

	function get_cancel_payment_url() {
		$default_url = get_permalink(get_option('wpshop_payment_return_page_id'));
		$url = get_option('wpshop_payment_return_url',$default_url);
		return self::construct_url_parameters($url, 'paymentResult', 'cancel');
	}

	function construct_url_parameters($url, $param, $value) {
		$interoguation_marker_pos = strpos($url, '?');
		if($interoguation_marker_pos===false)
			return $url.'?'.$param.'='.$value;
		else return $url.'&'.$param.'='.$value;
	}

	/** Shortcode : Manage payment result */
	function wpshop_payment_result() {

		if(!empty($_GET['paymentResult'])) {
			if($_GET['paymentResult']=='success') {
				echo __('Thank you ! Your payment has been recorded.','wpshop');
			}
			elseif($_GET['paymentResult']=='cancel') {
				echo __('Your payment and your order has been cancelled.','wpshop');
			}
		}
	}

	/**
	 * Display the list of payment methods available
	 *
	 * @param integer $order_id The order id if existing - Useful when user does not finish its order and want to validateit later
	 * @return string The different payment method
	 */
	function display_payment_methods_choice_form($order_id=0, $cart_type = 'cart') {
		$output = '';
		/**	Get available payment method	*/
		$paymentMethod = get_option('wpshop_paymentMethod', array());

		if(!empty($order_id) && is_numeric($order_id)) {
			$output .= '<input type="hidden" name="order_id" value="'.$order_id.'" />';
		}

		if ($cart_type == 'cart') {
			if(!empty($paymentMethod['paypal'])) {
				$tpl_component = array();
				$tpl_component['CHECKOUT_PAYMENT_METHOD_STATE_CLASS'] = ' active';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_INPUT_STATE'] = ' checked="checked"';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_IDENTIFIER'] = 'paypal';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = 'wpshop/medias/paypal.png';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_NAME'] = __('Paypal', 'wpshop');
				$tpl_component['CHECKOUT_PAYMENT_METHOD_EXPLANATION'] = __('<strong>Tips</strong> : If you have a Paypal account, by choosing this payment method, you will be redirected to the secure payment site Paypal to make your payment. Debit your PayPal account, immediate booking products.','wpshop');
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_payment_method_bloc', $tpl_component);
				unset($tpl_component);
			}

			if(!empty($paymentMethod['checks'])) {
				$current_payment_method_state = (!empty($paymentMethod['paypal']) && $paymentMethod['paypal']) ? false : true;
				$tpl_component = array();
				$tpl_component['CHECKOUT_PAYMENT_METHOD_STATE_CLASS'] = !$current_payment_method_state ? '' : ' active';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_INPUT_STATE'] = !$current_payment_method_state ? '' : ' checked="checked"';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_IDENTIFIER'] = 'check';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = 'wpshop/medias/cheque.png';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_NAME'] = __('Check', 'wpshop');
				$tpl_component['CHECKOUT_PAYMENT_METHOD_EXPLANATION'] = __('Reservation of products upon receipt of the check.','wpshop');
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_payment_method_bloc', $tpl_component);
				unset($tpl_component);
			}

			$wpshop_paymentMethod = get_option('wpshop_paymentMethod');
			if(WPSHOP_PAYMENT_METHOD_CIC || !empty($wpshop_paymentMethod['cic'])) {
				$current_payment_method_state = false;
				$tpl_component = array();
				$tpl_component['CHECKOUT_PAYMENT_METHOD_STATE_CLASS'] = !$current_payment_method_state ? '' : ' active';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_INPUT_STATE'] = !$current_payment_method_state ? '' : ' checked="checked"';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_IDENTIFIER'] = 'cic';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = 'wpshop/medias/cic_payment_logo.png';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_NAME'] = __('Credit card', 'wpshop');
				$tpl_component['CHECKOUT_PAYMENT_METHOD_EXPLANATION'] = __('Reservation of products upon confirmation of payment.','wpshop');
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_payment_method_bloc', $tpl_component);
				unset($tpl_component);
			}
		}

		return array( $output, $paymentMethod );
	}

	/**
	* Reduce the stock regarding the order
	*/
	function the_order_payment_is_completed($order_id, $txn_id = null) {
		// Donn�es commande
		$order = get_post_meta($order_id, '_order_postmeta', true);
		$order_info = get_post_meta($order_id, '_order_info', true);

		if(!empty($order) && !empty($order_info) && empty($order['order_invoice_ref'])) {

			// Reduction des stock produits
			if(!empty($order['order_items'])) {
				foreach($order['order_items'] as $o) {
					/*$manage_stock = true; // a laisser
					$manage_stock = wpshop_attributes::get_attribute_option_output($o, 'is_downloadable_', 'manage_stock');
					if($manage_stock) wpshop_products::reduce_product_stock_qty($o['item_id'], $o['item_qty']);*/
					$product = wpshop_products::get_product_data($o['item_id']);
					if (!empty($product) && !empty($product['manage_stock']) && $product['manage_stock']=='yes') {
						wpshop_products::reduce_product_stock_qty($o['item_id'], $o['item_qty']);
					}
				}
			}

			// Generate the billing reference (payment is completed here!!)
			wpshop_orders::order_generate_billing_number($order_id, true);

			$email = (!empty($order_info['billing']['email']) ? $order_info['billing']['email'] : '' );
			$first_name = ( !empty($order_info['billing']['first_name']) ? $order_info['billing']['first_name'] : '' );
			$last_name = ( !empty($order_info['billing']['last_name']) ? $order_info['billing']['last_name'] : '' );

			// Envoie du message de confirmation de paiement au client
			switch($order['payment_method']) {
				case 'check':
					wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']));
				break;

				case 'paypal':
					wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', array('paypal_order_key' => $txn_id, 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']));
				break;

				default:
					wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']));
			}
		}
	}

	/**
	* Get the method through which the data are transferred (POST OR GET)
	*/
	function getMethode(){
		if ($_SERVER["REQUEST_METHOD"] == "GET")
			return $_GET;
		if ($_SERVER["REQUEST_METHOD"] == "POST")
			return $_POST;
		die ('Invalid REQUEST_METHOD (not GET, not POST).');
	}

	/**
	* Save the payment data returned by the payment server
	*/
	function save_payment_return_data($post_id) {
		$data = wpshop_payment::getMethode();

		update_post_meta($post_id, 'wpshop_payment_return_data', $data);
	}

	/**
	* Set order payment status
	*/
	function setOrderPaymentStatus($order_id, $payment_status) {
		// Donn�es commande
		$order = get_post_meta($order_id, '_order_postmeta', true);

		if(!empty($order)) {
			// On stocke la date dans une variable pour r�utilisation
			$order['order_status'] = strtolower($payment_status);
			update_post_meta($order_id, '_wpshop_order_status',strtolower($payment_status));
			$order['order_payment_date'] = date('Y-m-d H:i:s');

			// On met � jour le statut de la commande
			update_post_meta($order_id, '_order_postmeta', $order);
		}
	}

	/**
	* Get payment method
	*/
	function get_payment_method($post_id){

		$order_postmeta = get_post_meta($post_id, '_order_postmeta', true);
		switch($order_postmeta['payment_method']){
			case 'check':
				$pm = __('Check','wpshop');
			break;
			case 'paypal':
				$pm = __('Paypal','wpshop');
			break;
			case 'cic':
				$pm = __('Credit card','wpshop');
			break;
			default:
				$pm = __('Nc','wpshop');
			break;
		}

		return $pm;
	}

	/**
	* Get payment transaction number
	*/
	function get_payment_transaction_number($post_id){

		$order_postmeta = get_post_meta($post_id, '_order_postmeta', true);
		$transaction_indentifier = 0;
		if(!empty($order_postmeta['payment_method'])){
			switch($order_postmeta['payment_method']){
				case 'check':
					$transaction_indentifier = get_post_meta($post_id, '_order_check_number', true);
				break;
				case 'paypal':
					$transaction_indentifier = get_post_meta($post_id, '_order_paypal_txn_id', true);
				break;
				case 'cic':
					$transaction_indentifier = get_post_meta($post_id, '_order_cic_txn_id', true);
				break;
				default:
					$transaction_indentifier = 0;
				break;
			}
		}

		return $transaction_indentifier;
	}

	/**
	* Set payment transaction number
	*/
	function set_payment_transaction_number($post_id){
		$payment_validation = '';
		$display_button = false;

		$order_postmeta = get_post_meta($post_id, '_order_postmeta', true);

		$transaction_indentifier = self::get_payment_transaction_number($post_id);

		$paymentMethod = get_option('wpshop_paymentMethod', array());
		$payment_validation .= '
<div id="order_payment_method_'.$post_id.'" class="clear wpshopHide" >
	<input type="hidden" id="used_method_payment_'.$post_id.'" value="' . (!empty($order_postmeta['payment_method']) ? $order_postmeta['payment_method'] : 'no_method') . '"/>
	<input type="hidden" id="used_method_payment_transaction_id_'.$post_id.'" value="' . (!empty($transaction_indentifier) ? $transaction_indentifier : 0) . '"/>';

		if(!empty($order_postmeta['payment_method'])){
			$payment_validation .= sprintf(__('Selected payment method: %s', 'wpshop'), __($order_postmeta['payment_method'], 'wpshop')) . '<br/>';
		}

		if(!empty($paymentMethod['paypal']) && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="paypal" id="payment_method_paypal" /><label for="payment_method_paypal" >' . __('Paypal', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		if(!empty($paymentMethod['checks']) && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="check" id="payment_method_check" /><label for="payment_method_check" >' . __('Check', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		$wpshop_paymentMethod = get_option('wpshop_paymentMethod');
		if((WPSHOP_PAYMENT_METHOD_CIC || !empty($wpshop_paymentMethod['cic'])) && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="cb" id="payment_method_cb" /><label for="payment_method_cb" >' . __('Credit card', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		if(empty($payment_transaction)){
			$payment_validation .= '<hr/>' . __('Transaction number', 'wpshop') . '&nbsp;:&nbsp;<input type="text" value="" name="payment_method_transaction_number" id="payment_method_transaction_number_'.$post_id.'" />';
			$display_button = true;
		}

		if($display_button){
			$payment_validation .= '
		<br/><br/><a class="button payment_method_validate order_'.$post_id.' clear" >'.__('Validate payment method', 'wpshop').'</a>';
		}

		$payment_validation .= '
</div>';

		return $payment_validation;
	}

}
?>