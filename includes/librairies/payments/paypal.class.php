<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * PayPal Standard Payment Gateway
 *
 * Provides a PayPal Standard Payment Gateway.
 *
 * @class 		wpshop_paypal
 * @package		WP-Shop
 * @category	Payment Gateways
 * @author		Eoxia
 */
class wpshop_paypal {

	public function __construct() {
		add_filter( 'wps_payment_mode_interface_paypal', array( &$this, 'display_admin_part') );

		/** Check if SystemPay is registred in Payment Main Option **/
		$payment_option = get_option( 'wps_payment_mode' );
		if ( !empty($payment_option) && !empty($payment_option['mode']) && !array_key_exists('paypal', $payment_option['mode']) ) {
			$payment_option['mode']['paypal']['name'] = __('Paypal', 'wpshop');
			$payment_option['mode']['paypal']['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/paypal.png';
			$payment_option['mode']['paypal']['description'] = __('<strong>Tips</strong> : If you have a Paypal account, by choosing this payment method, you will be redirected to the secure payment site Paypal to make your payment. Debit your PayPal account, immediate booking products.', 'wpshop');
			update_option( 'wps_payment_mode', $payment_option );
		}

		$payment_listener = !empty( $_GET['paymentListener'] ) ? sanitize_text_field( $_GET['paymentListener'] ) : '';

		if(!empty($payment_listener) && $payment_listener=='paypal') {
			$payment_status = 'denied';
			// read the post from PayPal system and add 'cmd'
			$req = 'cmd=_notify-validate';
			// @TODO : REQUEST
			$post = !empty($_POST) ? (array) $_POST : array();
			foreach ( $post as $key => $value) {
				$value = urlencode(stripslashes($value));
				$req .= "&$key=$value";
			}

			// If testing on Sandbox use:
			$paypalMode = get_option('wpshop_paypalMode', null);
			if($paypalMode == 'sandbox') {
				$fp = fsockopen ('ssl://sandbox.paypal.com', 443, $errno, $errstr, 30);
				$host = "www.sandbox.paypal.com";
			}
			else {
				$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
				$host = "www.paypal.com";
			}

			// post back to PayPal system to validate
			$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Host: " . $host . "\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

			/* Variables */
			$customer_id = $_POST['custom']; // id client
			$shipping = $_POST['mc_shipping']; // frais de livraison
			$business = $_POST['business']; // compte pro
			$order_id = (int)$_POST['invoice']; // num de facture
			$receiver_email = sanitize_text_field( $_POST['receiver_email'] );
			$amount_paid = $_POST['mc_gross']; // total (hors frais livraison)
			$txn_id = $_POST['txn_id']; // num�ro de transaction
			$payment_status = $_POST['payment_status']; // status du paiement
			$payer_email = $_POST['payer_email']; // email du client
			$txn_type = sanitize_text_field( $_POST['txn_type'] );

			// @TODO : REQUEST
			$post = !empty($_POST) ? (array) $_POST : array();
			if ( !empty($post) ) {
				foreach ( $post as $key => $value) {
					if ( substr($key, 0, 9) == 'item_name' ) {
						//$_POST[$key] = htmlentities($value);
					}
				}
			}

			/**	Save paypal return data automatically	*/
			wpshop_payment::save_payment_return_data( $order_id );

			$notify_email = get_option('wpshop_paypalEmail', null); // email address to which debug emails are sent to

			if (!$fp){
				echo 'HTTP ERROR!';
			}
			else {
				fputs ($fp, $header.$req);
				while (!feof($fp)) {
					$res = fgets ($fp, 1024);
					if (strcmp ($res, "VERIFIED") == 0) {
						$paypalBusinessEmail = get_option('wpshop_paypalEmail', null);

						/**	Check if payment has been send to good paypal account	*/
						if ($receiver_email == $paypalBusinessEmail) {
							/**	Get the payment transaction identifier	*/
							$paypal_txn_id = wpshop_payment::get_payment_transaction_number( $order_id,  wpshop_payment::get_order_waiting_payment_array_id( $order_id, 'paypal'));

							/**	If no transaction reference has been saved for this order	*/
							if ( empty($paypal_txn_id) ) {
								/**	Set the payment reference for the order	*/
								wpshop_payment::set_payment_transaction_number($order_id, $txn_id);

								/**	Get order content	*/
								$order = get_post_meta($order_id, '_order_postmeta', true);

								/**	Check the different amount : Order total / Paypal paid amount	*/
// 								$amount2pay = floatval($order['order_grand_total']);
								$amount2pay = number_format(floatval($order['order_amount_to_pay_now']), 2, '.', '');
								$amount_paid = number_format(floatval($amount_paid), 2, '.', '');

								/*	Check if the paid amount is equal to the order amount	*/
								if ( $amount_paid == $amount2pay ) {
									$payment_status = 'completed';
								}
								else {
									$payment_status = 'incorrect_amount';
								}

							}
							else {
								@mail($notify_email, 'VERIFIED DUPLICATED TRANSACTION', 'VERIFIED DUPLICATED TRANSACTION');
								$payment_status = 'completed';
							}
						}
					}
					// if the IPN POST was 'INVALID'...do this
					elseif (strcmp ($res, "INVALID") == 0) {
						@mail($notify_email, "INVALID IPN", "$res\n $req");
						$payment_status = 'payment_refused';
					}
				}
				fclose($fp);
			}

			$mc_gross = !empty( $_POST['mc_gross'] ) ? (float)$_POST['mc_gross'] : 0;

			$params_array = array('method' => 'paypal',
					'waited_amount' => number_format((float)$order['order_amount_to_pay_now'], 2, '.', ''),
					'status' => ( ( number_format((float)$order['order_amount_to_pay_now'], 2, '.', '') == number_format($mc_gross, 2, '.', '') ) ? 'payment_received' : 'incorrect_amount' ),
					'author' => $order['customer_id'],
					'payment_reference' => $txn_id,
					'date' => current_time('mysql', 0),
					'received_amount' => number_format($mc_gross, 2, '.', '') );
			wpshop_payment::check_order_payment_total_amount($order_id, $params_array, $payment_status);

		}


	}

	/**
	* Display the paypal form in order to redirect correctly to paypal
	*/
	public static function display_form($oid) {
		global $wpdb;
		$order = get_post_meta($oid, '_order_postmeta', true);

		// If the order exist
		if(!empty($order)) {

			$paypalBusinessEmail = get_option('wpshop_paypalEmail', null);

			// Si l'email Paypal n'est pas vide
			if(!empty($paypalBusinessEmail)) {

				$paypalMode = get_option('wpshop_paypalMode', null);
				if($paypalMode == 'sandbox') $paypal = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				else $paypal = 'https://www.paypal.com/cgi-bin/webscr';

				$current_currency = get_option('wpshop_shop_default_currency');
				$query = $wpdb->prepare('SELECT code_iso FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id =%d ', $current_currency );
				$currency = $wpdb->get_var($query);

				$output  = '<script type="text/javascript">jQuery(document).ready(function(){ jQuery("#paypalForm").submit(); });</script>';
				$output .= '<div class="paypalPaymentLoading"><span>' . __('Redirecting to paypal. Please wait', 'wpshop') . '</span></div>';
				$output .= '
						<form action="'.$paypal.'" id="paypalForm" method="post">
						<input id="cmd" name="cmd" type="hidden" value="_cart" />
						<input id="upload" name="upload" type="hidden" value="1" />
						<input id="charset" name="charset" type="hidden" value="utf-8" />
						<input id="no_shipping" name="no_shipping" type="hidden" value="1" />
						<input id="no_note" name="no_note" type="hidden" value="0" />
						<input id="rm" name="rm" type="hidden" value="0" />

						<input id="custom" name="custom" type="hidden" value="'.$order['customer_id'].'" />
						<input id="invoice" name="invoice" type="hidden" value="'.$oid.'" /> <!-- Invoice number -->
						<input id="business" name="business" type="hidden" value="'.$paypalBusinessEmail.'" /> <!-- Paypal business account -->
						<input id="cbt" name="cbt" type="hidden" value="' . __('Back to shop', 'wpshop') . '" />
						<input id="lc" name="lc" type="hidden" value="FR" />
						<input id="currency_code" name="currency_code" type="hidden" value="'.$currency.'" />

						<input id="return" name="return" type="hidden" value="'.wpshop_payment::get_success_payment_url().'" />
						<input id="cancel_return" name="cancel_return" type="hidden" value="'.wpshop_payment::get_cancel_payment_url().'" />
						<input id="notify_url" name="notify_url" type="hidden" value="'.wpshop_payment::construct_url_parameters(trailingslashit(home_url()), 'paymentListener', 'paypal').'" />
				';

				$i=1;
				if ( !empty( $order['order_partial_payment']) && !empty($order['order_partial_payment']['amount_of_partial_payment']) ) {
					$output .=	'
									<input id="item_number_'.$i.'" name="item_number_'.$i.'" type="hidden" value="' .$oid. '_partial_payment" />
									<input id="item_name_'.$i.'" name="item_name_'.$i.'" type="hidden" value="'.__('Partial payment', 'wpshop').' (' .__('Order number', 'wpshop'). ' : ' .$order['order_key']. ')" />
									<input id="quantity_'.$i.'" name="quantity_'.$i.'" type="hidden" value="1" />
									<input id="amount_'.$i.'" name="amount_'.$i.'" type="hidden" value="'.number_format($order['order_amount_to_pay_now'], 2, '.', '').'" />
									';
				}
				else {

					$output .=	'
									<input id="item_number_'.$i.'" name="item_number_'.$i.'" type="hidden" value="' .$order['order_key']. '" />
									<input id="item_name_'.$i.'" name="item_name_'.$i.'" type="hidden" value="' .__('Current order', 'wpshop'). ' : ' .$order['order_key']. '" />
									<input id="quantity_'.$i.'" name="quantity_'.$i.'" type="hidden" value="1" />
									<input id="amount_'.$i.'" name="amount_'.$i.'" type="hidden" value="'.number_format($order['order_amount_to_pay_now'], 2, '.', '').'" />
									';

				}



				$output .=	'<noscript><input type="submit" value="' . __('Checkout', 'wpshop') . '" /></noscript></form>';
			}
		}

		echo !empty($output) ? $output : '';
	}


	function display_admin_part() {
		$paypalEmail = get_option('wpshop_paypalEmail');
		$paypalMode = get_option('wpshop_paypalMode',0);
		$output  = '<div class="wps-boxed">';
		$output .= '<div class="wps-form-group"><label>'.__('Business email','wpshop').'</label><div class="wps-form"><input name="wpshop_paypalEmail" type="text" value="'.$paypalEmail.'" /></div></div>';
		$output .= '<label class="simple_right">'.__('Mode','wpshop').'</label>';
		$output .= '<div class="wps-form"><select name="wpshop_paypalMode">';
		$output .= '<option value="normal"'.(($paypalMode=='sandbox') ? null : ' selected="selected"').'>'.__('Production mode','wpshop').'</option>';
		$output .= '<option value="sandbox"'.(($paypalMode=='sandbox') ? ' selected="selected"' : null).'>'.__('Sandbox mode','wpshop').'</option>';
		$output .= '</select>';
		$output .= '</div>';
		$output .= '<a href="#" title="'.__('This checkbox allow to use Paypal in Sandbox mode (test) or production mode (real money)','wpshop').'" class="wpshop_infobulle_marker">?</a>';
		$output .= '</div>';
		return $output;
	}
}

?>
