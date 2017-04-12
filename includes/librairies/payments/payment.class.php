<?php if ( !defined( 'ABSPATH' ) ) exit;

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
		$wpshop_paymentMethod = get_option( 'wps_payment_mode' );
		if(WPSHOP_PAYMENT_METHOD_CIC && ( !empty($wpshop_paymentMethod['mode']) && !empty($wpshop_paymentMethod['mode']['cic']) && !empty($wpshop_paymentMethod['mode']['cic']['active']) ) ) {
			$wpshop_cic = new wpshop_CIC();
		}
		wpshop_tools::create_custom_hook ('wpshop_bankserver_reponse');

	}

	public static function get_success_payment_url() {
		$url = get_permalink( wpshop_tools::get_page_id( get_option('wpshop_payment_return_page_id') ) );
		return self::construct_url_parameters($url, 'paymentResult', 'success');
	}

	public static function get_cancel_payment_url() {
		$url = get_permalink( wpshop_tools::get_page_id( get_option('wpshop_payment_return_nok_page_id') ) );
		return $url;
	}

	public static function construct_url_parameters($url, $param, $value) {
		$interoguation_marker_pos = strpos($url, '?');
		if($interoguation_marker_pos===false)
			return $url.'?'.$param.'='.$value;
		else return $url.'&'.$param.'='.$value;
	}

	/**
	 * Shortcode : Manage payment result
	 */
	public static function wpshop_payment_result() {
		global $wpdb;
		$user_ID = get_current_user_id();
		$query = $wpdb->prepare('SELECT MAX(ID) FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_ORDER, $user_ID);
		$order_post_id = $wpdb->get_var( $query );
		if ( !empty($order_post_id) ) {
			$order_postmeta = get_post_meta($order_post_id , '_wpshop_order_status', true);
			if ( !empty($order_postmeta) ) {
				switch ( $order_postmeta ) {
					case 'awaiting_payment':
						echo __('We wait your payment.','wpshop');
					break;
					case 'completed':
						echo __('Thank you ! Your payment has been recorded.','wpshop');
					break;
					case 'partially_paid':
						echo __('Thank you ! Your first payment has been recorded.','wpshop');
					break;
					default:
						echo __('Your payment and your order has been cancelled.','wpshop');
					break;
				}
			}
		}
// 		if(!empty($_GET['paymentResult'])) {
// 			if($_GET['paymentResult']=='success') {
// 				echo __('Thank you ! Your payment has been recorded.','wpshop');
// 			}
// 			elseif($_GET['paymentResult']=='cancel') {
// 				echo __('Your payment and your order has been cancelled.','wpshop');
// 			}
// 		}
	}

	/**
	 * Display the list of payment methods available
	 *
	 * @param integer $order_id The order id if existing - Useful when user does not finish its order and want to validateit later
	 * @return string The different payment method
	 */
	function __display_payment_methods_choice_form($order_id=0, $cart_type = 'cart') {
		$output = '';
		/**	Get available payment method	*/
		$paymentMethod = get_option('wpshop_paymentMethod', array());

		if(!empty($order_id) && is_numeric($order_id)) {
			$output .= '<input type="hidden" name="order_id" value="'.$order_id.'" />';
		}

		if ($cart_type == 'cart') {
			$payment_methods = array();
			if(!empty($paymentMethod['paypal'])) {
				$payment_methods['paypal'] = array('payment_method_name' => __('CB with Paypal', 'wpshop'),
														'payment_method_icon' => WPSHOP_TEMPLATES_URL . 'wpshop/medias/paypal.png',
														'payment_method_explanation' => __('<strong>Tips</strong> : If you have a Paypal account, by choosing this payment method, you will be redirected to the secure payment site Paypal to make your payment. Debit your PayPal account, immediate booking products.','wpshop')
								 );
			}
			if(!empty($paymentMethod['checks'])) {
				$payment_methods['check'] = array('payment_method_name' => __('Check', 'wpshop'),
						'payment_method_icon' => WPSHOP_TEMPLATES_URL . 'wpshop/medias/cheque.png',
						'payment_method_explanation' => __('Reservation of products upon receipt of the check.','wpshop')
				);
			}
			if(!empty($paymentMethod['banktransfer'])) {
				$payment_methods['banktransfer'] = array('payment_method_name' => __('Bank transfer', 'wpshop'),
						'payment_method_icon' => WPSHOP_TEMPLATES_URL . 'wpshop/medias/cheque.png',
						'payment_method_explanation' =>__('Reservation of product receipt of payment.','wpshop')
				);
			}
			if(WPSHOP_PAYMENT_METHOD_CIC || !empty($paymentMethod['cic'])) {
				$payment_methods['cic'] = array('payment_method_name' =>__('Credit card', 'wpshop'),
						'payment_method_icon' => WPSHOP_TEMPLATES_URL . 'wpshop/medias/cic_payment_logo.jpg',
						'payment_method_explanation' =>__('Reservation of products upon confirmation of payment.','wpshop')
				);
			}
			$payment_methods = apply_filters('wpshop_payment_method', $payment_methods);

			$payment_method_table = array();

			if ( !empty( $paymentMethod['display_position'] ) ) {
				$position_determinated = false;
				foreach ( $paymentMethod['display_position'] as $key => $position) {
					if ( $position != null) {
						$position_determinated = true;
					}
				}
				if ( $position_determinated ) {
					for ( $i = 1; $i < count( $paymentMethod['display_position'] ) + 1; $i++) {
						foreach ( $paymentMethod['display_position'] as $key => $position ) {
							if ( $position == $i  && !empty($paymentMethod[$key])) {
								if ($key == 'checks') {
									$key = 'check';
								}
								$payment_method_table[$key] = $payment_methods[$key];
							}
						}
					}
					$payment_methods = $payment_method_table;
				}
			}
			if (!empty($payment_methods) ) {

				foreach( $payment_methods as  $payment_method_identifier =>  $payment_method_def ) {
					$tpl_component = array();
					$checked = $active = '';
					$payment_identifier_for_test = $payment_method_identifier;
					if ($payment_method_identifier == 'check') {
						$payment_identifier_for_test = 'checks';
					}
					if ( !empty($paymentMethod['default_method']) && $paymentMethod['default_method'] == $payment_identifier_for_test) {
						$checked = ' checked="checked"';
						$active = ' active';

					}
					$tpl_component['CHECKOUT_PAYMENT_METHOD_STATE_CLASS'] = $active;
					$tpl_component['CHECKOUT_PAYMENT_METHOD_INPUT_STATE'] = $checked;
					$tpl_component['CHECKOUT_PAYMENT_METHOD_IDENTIFIER'] = $payment_method_identifier;
					if ( !empty($payment_mode['logo']) && (int)$payment_mode['logo'] != 0 ) {
						$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = ( !empty($payment_method_def['payment_method_icon']) ) ? wp_get_attachment_image( $payment_method_def['payment_method_icon'], 'thumbnail', false, array('class' => 'wps_shipping_mode_logo') ) : '';
					}
					else {
						$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = ( !empty($payment_method_def['payment_method_icon']) ) ? '<img src="' .$payment_method_def['payment_method_icon']. '" alt="" />' : '';
					}
					//$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = $payment_method_def['payment_method_icon'];
					$tpl_component['CHECKOUT_PAYMENT_METHOD_NAME'] = $payment_method_def['payment_method_name'];
					$tpl_component['CHECKOUT_PAYMENT_METHOD_EXPLANATION'] = $payment_method_def['payment_method_explanation'];
					$output .= wpshop_display::display_template_element('wpshop_checkout_page_payment_method_bloc', $tpl_component, array('type' => 'payment_method', 'id' => $payment_method_identifier));
					unset($tpl_component);
				}
			}
		}

		return array( $output, $paymentMethod['mode'] );
	}


	public static function display_payment_methods_choice_form($order_id=0, $cart_type = 'cart') {
		$payment_option = get_option( 'wps_payment_mode' );
		$output = '';
		if(!empty($order_id) && is_numeric($order_id)) {
			$output .= '<input type="hidden" name="order_id" value="'.$order_id.'" />';
		}
		if( $cart_type == 'cart' ) {
			if ( !empty($payment_option) && !empty($payment_option['mode']) ) {
				foreach( $payment_option['mode'] as $payment_id => $payment_config ) {
					if( !empty($payment_config['active']) ) {
						$tpl_component['CHECKOUT_PAYMENT_METHOD_STATE_CLASS'] = ( ( !empty($payment_option['default_choice']) && $payment_option['default_choice'] == $payment_id ) ? ' active' : '');
						$tpl_component['CHECKOUT_PAYMENT_METHOD_INPUT_STATE'] = ( ( !empty($payment_option['default_choice']) && $payment_option['default_choice'] == $payment_id ) ? 'checked="checked"' : '');
						$tpl_component['CHECKOUT_PAYMENT_METHOD_IDENTIFIER'] = $payment_id;

						if ( !empty($payment_config['logo']) && (int)$payment_config['logo'] != 0 ) {
							$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = ( !empty($payment_config['logo']) ) ? wp_get_attachment_image( $payment_config['logo'], 'thumbnail', false ) : '';
						}
						else {
							$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = ( !empty($payment_config['logo']) ) ? '<img src="' .$payment_config['logo']. '" alt="' .$payment_config['name']. '" />' : '';
						}
						$tpl_component['CHECKOUT_PAYMENT_METHOD_NAME'] = ( !empty($payment_config['name']) ) ? $payment_config['name'] : '';
						$tpl_component['CHECKOUT_PAYMENT_METHOD_EXPLANATION'] = ( !empty($payment_config['description']) ) ? $payment_config['description'] : '';
						$output .= wpshop_display::display_template_element('wpshop_checkout_page_payment_method_bloc', $tpl_component, array('type' => 'payment_method', 'id' => $payment_id));
						unset($tpl_component);
					}
				}
			}
		}
		return array($output, $payment_option);
	}



	/**
	* Reduce the stock regarding the order
	*/
	function the_order_payment_is_completed($order_id, $txn_id = null) {
		// Donnees commandes
		$order = get_post_meta($order_id, '_order_postmeta', true);
		$order_info = get_post_meta($order_id, '_order_info', true);

		$wps_message = new wps_message_ctr();

		if(!empty($order) && !empty($order_info) && empty($order['order_invoice_ref'])) {
			$email = (!empty($order_info['billing']['address']['address_user_email']) ? $order_info['billing']['address']['address_user_email'] : '' );
			$first_name = ( !empty($order_info['billing']['address']['address_first_name']) ? $order_info['billing']['address']['address_first_name'] : '' );
			$last_name = ( !empty($order_info['billing']['address']['address_last_name']) ? $order_info['billing']['address']['address_last_name'] : '' );

			// Envoie du message de confirmation de paiement au client
			switch($order['payment_method']) {
				case 'check':
					$wps_message->wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']));
				break;

				case 'paypal':
					$wps_message->wpshop_prepared_email($email, 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', array('paypal_order_key' => $txn_id, 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']));
				break;

				default:
					$wps_message->wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']));
				break;
			}
		}
	}


	function setOrderPaymentStatus( $order_id, $payment_status ) {
		/**	Get order main information	*/
		$order = get_post_meta($order_id, '_order_postmeta', true);
		$send_email = false;

		if ( !empty($order) ) {
			/**	Change order status to given status	*/
			$order['order_status'] = strtolower($payment_status);
			/**	Put order status into a single meta, allowing to use it easily later	*/
			update_post_meta($order_id, '_wpshop_order_status', $order['order_status']);

			/**	In case the set status is completed, make specific treatment: add the completed date	*/
			if ( $payment_status == 'completed' ) {
				/**	Read order items list, if not empty and check if each item is set to manage stock or not */
				if (!empty($order['order_items'])) {
					foreach ($order['order_items'] as $o) {
						$product = wpshop_products::get_product_data( $o['item_id'] );
						if (!empty($product) && !empty($product['manage_stock']) && __($product['manage_stock'], 'wpshop') == __('Yes', 'wpshop') ) {
							wpshop_products::reduce_product_stock_qty($o['item_id'], $o['item_qty']);
						}
					}
				}

				/** Add information about the order completed date */
				update_post_meta($order_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER . '_completed_date', current_time('mysql', 0));

				/**	Set a variable to know when send an email to the customer	*/
				$send_email = true;
			}

			/**	Send email to customer when specific case need it	*/
			if ( $send_email ) {
				/**	Get information about customer that make the order	*/
				$order_info = get_post_meta($order_id, '_order_info', true);
				$mail_tpl_component = array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']);
			}

			/**	Update order with new informations	*/
			update_post_meta($order_id, '_order_postmeta', $order);
		}
	}

	/**
	* Get payment method
	*/
	function get_payment_method($post_id){
		$pm = __('Nc','wpshop');
		$order_postmeta = get_post_meta($post_id, '_order_postmeta', true);
		if ( !empty($order_postmeta['payment_method']) ) {
			switch($order_postmeta['payment_method']){
				case 'check':
					$pm = __('Check','wpshop');
				break;
				case 'paypal':
					$pm = __('Paypal','wpshop');
				break;
				case 'banktransfer':
					$pm = __('Bank transfer','wpshop');
				break;
				case 'cic':
					$pm = __('Credit card','wpshop');
				break;
				default:
					$pm = __('Nc','wpshop');
				break;
			}
		}
		return $pm;
	}

	/**
	* Set payment transaction number
	*/
	function display_payment_receiver_interface($post_id) {
		$payment_validation = '';
		$display_button = false;

		$order_postmeta = get_post_meta($post_id, '_order_postmeta', true);

		$transaction_indentifier = self::get_payment_transaction_number($post_id);

		$paymentMethod = $paymentMethod['mode'];
		$payment_validation .= '
<div id="order_payment_method_'.$post_id.'" class="wpshop_cls wpshopHide" >
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

		$wpshop_paymentMethod = get_option( 'wps_payment_mode' );
		if((WPSHOP_PAYMENT_METHOD_CIC || (!empty($wpshop_paymentMethod['mode']) && !empty($wpshop_paymentMethod['mode']['cic'])) ) && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="cb" id="payment_method_cb" /><label for="payment_method_cb" >' . __('Credit card', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		if(empty($payment_transaction)){
			$payment_validation .= '<hr/>' . __('Transaction number', 'wpshop') . '&nbsp;:&nbsp;<input type="text" value="" name="payment_method_transaction_number" id="payment_method_transaction_number_'.$post_id.'" />';
			$display_button = true;
		}

		if($display_button){
			$payment_validation .= '
		<br/><br/><a class="button payment_method_validate order_'.$post_id.' wpshop_clear" >'.__('Validate payment method', 'wpshop').'</a>';
		}

		$payment_validation .= '
</div>';

		return $payment_validation;
	}

	/**
	 * Allows to inform customer that he would pay a partial amount on this order
	 *
	 * @param float $current_order_total The current order total to pay before partial amount calcul
	 * @return array The amount to pay / A html output with amount to pay and different information
	 */
	function partial_payment_calcul( $current_order_total, $for = 'for_all' ) {
		$output = '';
		$tpl_component = array();

		/**	Get current configuration	*/
		$partial_payment_configuration = get_option('wpshop_payment_partial', array($for => array(), 'for_quotation' => array()));
		if ( !empty($partial_payment_configuration[$for]) && (!empty($partial_payment_configuration[$for]['activate'])) && ($partial_payment_configuration[$for]['activate'] == 'on') ) {
			$amount_of_partial_payment = 0;
			if ( !empty($partial_payment_configuration[$for]['value']) && !empty($partial_payment_configuration[$for]['activate']) ) {
				$amount_of_partial_payment = $partial_payment_configuration[$for]['value'];
			}

			$partial_amount_to_pay = 0;
			$type_of_partial_payment = null;
			if (!empty($partial_payment_configuration[$for]) && !empty($partial_payment_configuration[$for]['type']) ) {
				switch ($partial_payment_configuration[$for]['type']) {
					case 'percentage':
						$type_of_partial_payment = '%';
						$partial_amount_to_pay = (($current_order_total * $amount_of_partial_payment) / 100);
					break;
					case 'amount':
						$type_of_partial_payment = wpshop_tools::wpshop_get_currency();
						$partial_amount_to_pay = ($current_order_total - $amount_of_partial_payment);
					break;
					default:
						$type_of_partial_payment = wpshop_tools::wpshop_get_currency();
						$partial_amount_to_pay = ($current_order_total - $amount_of_partial_payment);
					break;
				}
			}
			$output['amount_of_partial_payment'] = $amount_of_partial_payment;
			$output['type_of_partial_payment'] = $type_of_partial_payment;
			$output['amount_to_pay'] = $partial_amount_to_pay;

			$tpl_component['CURRENT_ORDER_TOTAL_AMOUNT'] = $current_order_total;
			$tpl_component['PARTIAL_PAYMENT_CONFIG_AMOUNT'] = !empty($amount_of_partial_payment) ? $amount_of_partial_payment : '';
			$tpl_component['PARTIAL_PAYMENT_CONFIG_TYPE'] = !empty($type_of_partial_payment) ? $type_of_partial_payment : '';
			$tpl_component['PARTIAL_PAYMENT_AMOUNT'] = $partial_amount_to_pay;

			$output['display'] = wpshop_display::display_template_element('wpshop_partial_payment_display', $tpl_component);
			unset($tpl_component);
		}

		return $output;
	}

	/**
	 * Return the new transaction reference for an order payment
	 * @since 1.3.3.7
	 *
	 * @param integer $order_id The order identifer we want to get payment reference for
	 *
	 * @return mixed The payment reference for current order
	 */
	function get_payment_transaction_number($order_id, $payment_index = 0) {
		$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
		$transaction_indentifier = '';

		if (!empty($order_meta['order_payment']['received']) && !empty($order_meta['order_payment']['received'][$payment_index]) && !empty($order_meta['order_payment']['received'][$payment_index]['payment_reference'])) {
			$transaction_indentifier = $order_meta['order_payment']['received'][$payment_index]['payment_reference'];
		}

		return $transaction_indentifier;
	}

	/**
	 * Set the transaction identifier for a given order
	 *
	 * @param integer $order_id
	 * @param mixed $transaction_number The identifier of transaction. Used for all the payment method
	 */
	function set_payment_transaction_number($order_id, $transaction_number) {
		$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);

		if ( !empty($order_postmeta['order_payment']['received']) ) {
			if (count($order_postmeta['order_payment']['received']) == 1) {
				$order_postmeta['order_payment']['received'][0]['payment_reference'] = $transaction_number;
			}
		}

		update_post_meta($order_id, '_order_postmeta', $order_postmeta);
	}

	/**
	 * Save the payment data returned by the payment server
	 *
	 * @param integer $order_id
	 */
	function save_payment_return_data( $order_id ) {
		$data = wpshop_tools::getMethode();

		$current_payment_return = get_post_meta( $order_id, '_wpshop_payment_return_data', true);
		$current_payment_return[] = $data;

		update_post_meta($order_id, '_wpshop_payment_return_data', $current_payment_return);
	}

	/**
	 * Add a new payment to a given order
	 *
	 * @param array $order_meta The complete order meta informations
	 * @param integer $payment_index The payment to add/update data for
	 * @param array $params : infos sended by the bank, array structure : ('method', 'waited amount', 'status', 'author', 'payment reference', 'date', 'received amount')
	 * @return array The order new meta informations
	 */
	public static function add_new_payment_to_order( $order_id, $order_meta, $payment_index, $params, $bank_response ) {

		$order_meta['order_payment']['received'][$payment_index]['method'] = ( !empty($params['method']) ) ? $params['method'] : null;
		$order_meta['order_payment']['received'][$payment_index]['waited_amount'] = ( !empty($params['waited_amount']) ) ? $params['waited_amount'] : null;
		$order_meta['order_payment']['received'][$payment_index]['status'] = ( !empty($params['status']) ) ? $params['status'] : null;
		$order_meta['order_payment']['received'][$payment_index]['author'] = ( !empty($params['author']) ) ? $params['author'] : get_current_user_id();
		$order_meta['order_payment']['received'][$payment_index]['payment_reference'] =( !empty($params['payment_reference']) ) ? $params['payment_reference'] : null;
		$order_meta['order_payment']['received'][$payment_index]['date'] = ( !empty($params['date']) ) ? $params['date'] : null;
		$order_meta['order_payment']['received'][$payment_index]['received_amount'] = ( !empty($params['received_amount']) ) ? $params['received_amount'] : null;
		$order_meta['order_payment']['received'][$payment_index]['comment'] = '';

		$order_info = get_post_meta($order_id, '_order_info', true);
		if(!empty($order_meta) && !empty($order_info) && ($bank_response == 'completed')) {

			/**	Generate an invoice number for the current payment. Check if the payment is complete or not	*/
			if ( empty($order_meta['order_invoice_ref']) ) {
				$order_meta['order_payment']['received'][$payment_index]['invoice_ref'] = wpshop_modules_billing::generate_invoice_number( $order_id );
			}
		}

		return $order_meta['order_payment']['received'][$payment_index];
	}

	/**
	 * Return the array id of the last waited paylent for an payment method
	 * @param integer $oid
	 * @param string $payment_method
	 * @return integer $key : array id of [order_payment][received] in the order postmeta
	 */
	public static function get_order_waiting_payment_array_id ( $oid, $payment_method ) {
		$key = 0;
		$order_meta = get_post_meta( $oid, '_order_postmeta', true);
		if ( !empty($order_meta) ) {
			$key = count( $order_meta['order_payment']['received'] );
			foreach ( $order_meta['order_payment']['received'] as $k => $payment_test) {
				if ( !array_key_exists('received_amount', $payment_test) /* && $order_meta['order_payment']['received'][$k]['method'] == $payment_method */ ) {
					$key = $k;
				}
			}
		}
		return $key;
	}


	/**
	 * Update th receive payment part in order postmeta and return "Complete" if the shop have received the total amount of the order
	 * @param int $order_id
	 * @param array $params_array
	 * @return string
	 */
	public static function check_order_payment_total_amount($order_id, $params_array, $bank_response, $order_meta = array(), $save_metadata = true ) {
		global $wpshop_payment; global $wpdb;
		$order_meta = ( !empty($order_meta) ) ? $order_meta : get_post_meta( $order_id, '_order_postmeta', true);

		$wps_message = new wps_message_ctr();
		if ( !empty($order_meta) ) {
			$order_info = get_post_meta($order_id, '_order_info', true);
			$user_data = get_userdata( $order_meta['customer_id'] );
			$email = ( !empty($user_data) && !empty($user_data->user_email) ) ? $user_data->user_email : '';
// 			$email = ( !empty($order_info) &&  !empty($order_info['billing']) && !empty($order_info['billing']['address']['address_user_email']) ) ? $order_info['billing']['address']['address_user_email'] : '' ;
			$first_name = (!empty($order_info) && !empty($order_info['billing']) &&  !empty($order_info['billing']['address']['address_first_name']) ? $order_info['billing']['address']['address_first_name'] : '' );
			$last_name = ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']['address_last_name']) ? $order_info['billing']['address']['address_last_name'] : '' );

			$key = self::get_order_waiting_payment_array_id( $order_id, $params_array['method']);
			$order_grand_total = $order_meta['order_grand_total'];
			$total_received = ( ( !empty($params_array['status']) && ( $params_array['status'] == 'payment_received') && ($bank_response == 'completed') && !empty($params_array['received_amount']) ) ? $params_array['received_amount'] : 0 );
			foreach ( $order_meta['order_payment']['received'] as $received ) {
				$total_received += ( ( !empty($received['status']) && ( $received['status'] == 'payment_received') && ($bank_response == 'completed') && !empty($received['received_amount']) ) ? $received['received_amount'] : 0 );
			}
			$order_meta['order_amount_to_pay_now'] = $order_grand_total - $total_received;
			$order_meta['order_payment']['received'][$key] = self::add_new_payment_to_order( $order_id, $order_meta, $key, $params_array, $bank_response );

			if ($bank_response == 'completed') {

				if ( number_format((float)$total_received, 2, '.', '') >= number_format((float)$order_grand_total,2, '.', '') ) {
					$payment_status = 'completed';

					$order_meta['order_invoice_ref'] = ( empty ($order_meta['order_invoice_ref'] ) && !empty($order_meta['order_payment']['received'][$key]) && !empty($order_meta['order_payment']['received'][$key]['invoice_ref']) ) ? $order_meta['order_payment']['received'][$key]['invoice_ref'] : ( empty($order_meta['order_invoice_ref']) ? null : $order_meta['order_invoice_ref'] ) ;
					$order_meta['order_invoice_date'] = current_time('mysql', 0);

					if (!empty($order_meta['order_items'])) {
						foreach ($order_meta['order_items'] as $item_id => $o) {
							$pid = $o['item_id'];
							if (strpos($item_id,'__') !== false) {
								$product_data_id = explode( '__', $item_id );
								$pid = ( !empty($product_data_id) && !empty($product_data_id[1]) ) ? $product_data_id[1] : $pid;
							}
							$product = wpshop_products::get_product_data( $pid );
							if ( get_post_type( $pid ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
								$parent_def = wpshop_products::get_parent_variation ( $pid );
								$parent_post = $parent_def['parent_post'];
								$product = wpshop_products::get_product_data( $parent_post->ID );
							}

							if (!empty($product) && !empty($product['manage_stock']) && strtolower( __($product['manage_stock'], 'wpshop') ) == strtolower( __('Yes', 'wpshop') ) ) {
								wpshop_products::reduce_product_stock_qty($product['product_id'], $o['item_qty'], $pid );
							}
						}
					}

					/** Add information about the order completed date */
					update_post_meta($order_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER . '_completed_date', current_time('mysql', 0));

					/** Check if the order content a downloadable product **/
					if ( ! empty( $order_meta['order_items'] ) ) {
						foreach ( $order_meta['order_items'] as $key_value => $item ) {
							$link = wps_download_file_ctr::get_product_download_link( $order_id, $item );
							if ( false !== $link ) {
								$link = '<a href="' . $link . '" target="_blank">' . __( 'Download the product', 'wpshop' ) . '</a>';
								$wps_message->wpshop_prepared_email( $email, 'WPSHOP_DOWNLOADABLE_FILE_IS_AVAILABLE', array( 'order_key' => $order_meta['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order_meta['order_date'], 'download_product_link' => $link ), array() );
							}
						}
					}

					// Send confirmation e-mail to administrator
					if ( empty($_SESSION['wps-pos-addon']) ) {
						$email_option = get_option('wpshop_emails');
						if(  !empty($email_option) && !empty($email_option['send_confirmation_order_message']) ){
							wpshop_checkout::send_order_email_to_administrator( $order_id, $user_data );
						}
					}

					// POS Status
					if( !empty($order_meta['order_payment']) && !empty($order_meta['order_payment']['shipping_method']) && $order_meta['order_payment']['shipping_method'] == 'default_shipping_mode_for_pos' ) {
						$payment_status = 'pos';
					}
				}
				else {
					$payment_status = 'partially_paid';
				}

				$order_meta['order_status'] = $payment_status;
				update_post_meta( $order_id, '_order_postmeta', $order_meta);
				$save_metadata = false;

				$allow_send_invoice = get_option( 'wpshop_send_invoice' );
				$invoice_attachment_file = ( !empty($allow_send_invoice) ) ? wpshop_modules_billing::generate_invoice_for_email( $order_id, empty( $order_meta['order_payment']['received'][$key]['invoice_ref'] ) ? $order_meta['order_invoice_ref'] : $order_meta['order_payment']['received'][$key]['invoice_ref'] ) : '';

				$email_option = get_option( 'wpshop_emails' );

				$shipping_mode_option = get_option( 'wps_shipping_mode' );
				$shipping_method = ( !empty($order_meta['order_payment']['shipping_method']) && !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && is_array($shipping_mode_option['modes']) && array_key_exists($order_meta['order_payment']['shipping_method'], $shipping_mode_option['modes'])) ? $shipping_mode_option['modes'][$order_meta['order_payment']['shipping_method']]['name'] : ( (!empty($order_meta['order_payment']['shipping_method']) ) ? $order_meta['order_payment']['shipping_method'] : '' );

				$payment_method_option = get_option( 'wps_payment_mode' );
				$order_payment_method = ( !empty($payment_method_option) && !empty($payment_method_option['mode'])  && !empty($order_meta['order_payment']['customer_choice']['method'])  && !empty($payment_method_option['mode'][$order_meta['order_payment']['customer_choice']['method']])  ) ? $payment_method_option['mode'][$order_meta['order_payment']['customer_choice']['method']]['name'] : $order_meta['order_payment']['customer_choice']['method'];

				if ( !empty( $email_option ) && !empty( $email_option['send_confirmation_order_message'] ) && $payment_status == 'completed'
						&& ( !isset( $params_array[ 'send_received_payment_email' ] ) || ( true == $params_array[ 'send_received_payment_email' ] ) ) ) {
					$wps_message->wpshop_prepared_email($email, 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', array('order_id' => $order_id,'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'customer_email' => $email, 'order_key' => ( ( !empty($order_meta['order_key']) ) ? $order_meta['order_key'] : ''),'order_date' => current_time('mysql', 0),  'order_payment_method' => $order_payment_method, 'order_content' => '', 'order_addresses' => '', 'order_customer_comments' => '', 'order_billing_address' => '', 'order_shipping_address' => '',  'order_shipping_method' => $shipping_method ) );
				}

				if ( !isset( $params_array[ 'send_received_payment_email' ] ) || ( true == $params_array[ 'send_received_payment_email' ] ) ) {
					$wps_message->wpshop_prepared_email( $email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order_meta['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order_meta['order_date'], 'order_shipping_method' => $shipping_method), array(), $invoice_attachment_file);
				}
			}
			else {
				$payment_status = $bank_response;
			}

			$order_meta['order_status'] = $payment_status;
			if( !$save_metadata ) {
				return 	$order_meta;
			}
			else {
				update_post_meta( $order_id, '_order_postmeta', $order_meta);
			}
			update_post_meta( $order_id, '_wpshop_order_status', $payment_status);
		}
	}

	/**
	 * Return the transaction of an order payment transaction.
	 *
	 * @deprecated deprecated since version 1.3.3.7
	 *
	 * @param integer $order_id The order identifier we want to get the old transaction reference for
	 * @return integer
	 */
	public static function get_payment_transaction_number_old_way($order_id){
		$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
		$transaction_indentifier = 0;
		if(!empty($order_postmeta['payment_method'])){
			switch($order_postmeta['payment_method']){
				case 'check':
					$transaction_indentifier = get_post_meta($order_id, '_order_check_number', true);
					break;
				case 'paypal':
					$transaction_indentifier = get_post_meta($order_id, '_order_paypal_txn_id', true);
					break;
				case 'cic':
					$transaction_indentifier = get_post_meta($order_id, '_order_cic_txn_id', true);
					break;
				default:
					$transaction_indentifier = 0;
					break;
			}
		}

		return $transaction_indentifier;
	}

	public static function reverify_payment_invoice_ref( $order_id, $index_payment ) {
		$status = false;
		$order_meta = get_post_meta( $order_id, '_order_postmeta', true );
		if( !empty( $order_meta ) && !empty( $order_meta['order_payment'] ) && !empty( $order_meta['order_payment']['received'] ) && !empty( $order_meta['order_payment']['received'][$index_payment] ) && empty( $order_meta['order_payment']['received'][$index_payment]['invoice_ref'] ) ) {
			$order_invoice = $invoice_ref = false;

			end( $order_meta['order_payment']['received'] );
			$last_payment = key( $order_meta['order_payment']['received'] );
			if( $last_payment == $index_payment ) {
				$payments = 0;
				foreach( $order_meta['order_payment']['received'] as $payment ) {
					$payments += ( $payment['status'] == 'payment_received' ) ? $payment['received_amount'] : 0;
				}
				if( $order_meta['order_grand_total'] <= $payments ) {
					if( $order_meta['order_status'] == 'partially_paid' ) {
						$order_status_meta = get_post_meta( $order_id, '_wpshop_order_status', true );
						$order_meta['order_status'] = 'completed';
						$status = (bool)update_post_meta( $order_id, '_wpshop_order_status', 'completed' );
					}
					if( !empty( $order_meta['order_invoice_ref'] ) ) {
						$invoice_ref = $order_meta['order_invoice_ref'];
						$status = true;
					} else {
						$status = $order_invoice = true;
					}
				}
			} else {
				$status = true;
			}

			if( $status ) {
				if( empty( $invoice_ref ) ) {
					$invoice_ref = wpshop_modules_billing::generate_invoice_number( $order_id );
					if( $order_invoice ) {
						$order_meta['order_invoice_ref'] = $invoice_ref;
					}
				}

				$order_meta['order_payment']['received'][$index_payment]['invoice_ref'] = $invoice_ref;
				$status = (bool)update_post_meta( $order_id, '_order_postmeta', $order_meta );
			}
		}
		return $status;
	}
}

?>
