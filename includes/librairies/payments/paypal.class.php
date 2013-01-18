<?php

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
		global $wpshop;

		if(!empty($_GET['paymentListener']) && $_GET['paymentListener']=='paypal') {

			// read the post from PayPal system and add 'cmd'
			$req = 'cmd=_notify-validate';
			foreach ($_POST as $key => $value) {
				$value = urlencode(stripslashes($value));
				$req .= "&$key=$value";
			}

			// post back to PayPal system to validate
			$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

			// If testing on Sandbox use:
			$paypalMode = get_option('wpshop_paypalMode', null);
			if($paypalMode == 'sandbox') {
				$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
			}
			else {
				$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
			}

			/* Variables */
			$customer_id = $_POST['custom']; // id client
			$shipping = $_POST['mc_shipping']; // frais de livraison
			$business = $_POST['business']; // compte pro
			$order_id = (int)$_POST['invoice']; // num de facture
			$receiver_email = $_POST['receiver_email'];
			$amount_paid = $_POST['mc_gross']; // total (hors frais livraison)
			$txn_id = $_POST['txn_id']; // num�ro de transaction
			$payment_status = $_POST['payment_status']; // status du paiement
			$payer_email = $_POST['payer_email']; // email du client
			$txn_type = $_POST['txn_type'];

			$notify_email = get_option('wpshop_paypalEmail', null); // email address to which debug emails are sent to

			if (!$fp) echo 'HTTP ERROR!';
			else {
				fputs ($fp, $header.$req);
				while (!feof($fp)) {
					$res = fgets ($fp, 1024);

					if (strcmp ($res, "VERIFIED") == 0) {

						$paypalBusinessEmail = get_option('wpshop_paypalEmail', null);

						// On v�rifie que le paiement est envoy� � la bonne adresse email
						if ($receiver_email == $paypalBusinessEmail) {

							// On cherche � r�cup�rer l'id de la transaction
							$paypal_txn_id = get_post_meta($order_id, '_order_paypal_txn_id', true);

							// Si la transaction est unique
							if (empty($paypal_txn_id)) {

								wpshop_payment::save_payment_return_data($order_id);

								// On enregistre l'id unique de la transaction
								update_post_meta($order_id, '_order_paypal_txn_id', $txn_id);
								// Donn�es commande
								$order = get_post_meta($order_id, '_order_postmeta', true);
								// On parse les montant afin de pouvoir les comparer correctement
								$amount2pay = floatval($order['order_total']);
								$amount_paid = floatval($amount_paid);

								/*	Check the payment status	*/
								if ( $payment_status == 'Completed' ) {
									wpshop_payment::the_order_payment_is_completed($order_id, $txn_id);
								}

								/*	Check if the paid amount is equal to the order amount	*/
								// if ($amount_paid == $amount2pay ) {
								if ($amount_paid == sprintf('%0.2f', $amount2pay) ) {
									wpshop_payment::setOrderPaymentStatus($order_id, strtolower($payment_status));
								}
								else wpshop_payment::setOrderPaymentStatus($order_id, 'incorrect_amount');

							}
							else {
								@mail($notify_email, 'VERIFIED DUPLICATED TRANSACTION', 'VERIFIED DUPLICATED TRANSACTION');
							}
						}
						exit;
					}
					// if the IPN POST was 'INVALID'...do this
					elseif (strcmp ($res, "INVALID") == 0) {
						@mail($notify_email, "INVALID IPN", "$res\n $req");
					}
				}
				fclose ($fp);
			}
		}
	}

	/**
	* Display the paypal form in order to redirect correctly to paypal
	*/
	function display_form($oid) {

		$order = get_post_meta($oid, '_order_postmeta', true);

		// If the order exist
		if(!empty($order)) {

			$paypalBusinessEmail = get_option('wpshop_paypalEmail', null);

			// Si l'email Paypal n'est pas vide
			if(!empty($paypalBusinessEmail)) {

				$paypalMode = get_option('wpshop_paypalMode', null);
				if($paypalMode == 'sandbox') $paypal = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				else $paypal = 'https://www.paypal.com/cgi-bin/webscr';

				$return_url = get_permalink(get_option('wpshop_myaccount_page_id')); // Url de retour apr�s paiement
				$currency = wpshop_tools::wpshop_get_currency( true ); // Informations de commande � stocker

				echo '<script type="text/javascript">jQuery(document).ready(function(){ jQuery("#paypalForm").submit(); });</script>';
				echo '<div class="paypalPaymentLoading"><span>' . __('Redirecting to paypal. Please wait', 'wpshop') . '</span></div>';
				echo '
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

				$i=0;
				foreach ($order['order_items'] as $c) :
					$i++;
					echo '
						<input id="item_number_'.$i.'" name="item_number_'.$i.'" type="hidden" value="'.$c['item_id'].'" />
						<input id="item_name_'.$i.'" name="item_name_'.$i.'" type="hidden" value="'.$c['item_name'].'" />
						<input id="quantity_'.$i.'" name="quantity_'.$i.'" type="hidden" value="'.$c['item_qty'].'" />
						<input id="amount_'.$i.'" name="amount_'.$i.'" type="hidden" value="'.sprintf('%0.2f', $c['item_pu_ttc']).'" />
					';
				endforeach;

				/*
					<input id="shipping_1" name="shipping_1" type="hidden" value="' . $order['order_shipping_cost'] . '" />
				*/

				echo '
						<input id="item_number_'.($i+1).'" name="item_number_'.($i+1).'" type="hidden" value="wps_cart_shipping_cost" />
						<input id="item_name_'.($i+1).'" name="item_name_'.($i+1).'" type="hidden" value="' . __('Shipping cost', 'wpshop') . '" />
						<input id="quantity_'.($i+1).'" name="quantity_'.($i+1).'" type="hidden" value="1" />
						<input id="amount_'.($i+1).'" name="amount_'.($i+1).'" type="hidden" value="'.sprintf('%0.2f', $order['order_shipping_cost']).'" />

						<noscript><input type="submit" value="' . __('Checkout', 'wpshop') . '" /></noscript>
					</form>
				';
			}
		}
	}
}

?>