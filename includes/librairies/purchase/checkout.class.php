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


class wpshop_checkout {

	var $div_register, $div_infos_register, $div_login, $div_infos_login = 'display:block;';
	var $creating_account = true;

	/** Constructor of the class
	* @return void
	*/
	function __construct () {
	}


	public static function process_checkout($paymentMethod='paypal', $order_id = 0, $customer_id = 0, $customer_billing_address_id = 0, $customer_shipping_address_id = 0) {

		global $wpdb, $wpshop, $wpshop_cart;
		$wps_message = new wps_message_ctr();
		$shipping_address_option = get_option('wpshop_shipping_address_choice');

		if (is_user_logged_in()) :
			$user_id = get_current_user_id();

		if ( $customer_id != 0 ) {
			$user_id = $customer_id;
		}

			// If the order is already created in the db
			if(!empty($order_id) && is_numeric($order_id)) {
				$order = get_post_meta($order_id, '_order_postmeta', true);

				if(!empty($order)) {
					if($order['customer_id'] == $user_id) {
						$order['payment_method'] = $paymentMethod;
						$_SESSION['order_id'] = wpshop_tools::varSanitizer( $order_id );
						// Store cart in session
						//wpshop_cart::store_cart_in_session($order);
						// Add a payment
						$order['order_payment']['received'][] = array( 'method' => $paymentMethod, 'waited_amount' => $order['order_amount_to_pay_now'], 'status' => 'waiting_payment', 'author' => get_current_user_id() );

						// On enregistre la commande
						update_post_meta($order_id, '_order_postmeta', $order);
						update_post_meta($order_id, '_wpshop_order_customer_id', $user_id);
					}
					else $wpshop->add_error(__('You don\'t own the order', 'wpshop'));
				}
				else $wpshop->add_error(__('The order doesn\'t exist.', 'wpshop'));
			}
			else{
				$order_data = array(
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
					'post_title' => sprintf(__('Order - %s','wpshop'), mysql2date('d M Y\, H:i:s', current_time('mysql', 0), true)),
					'post_status' => 'publish',
					'post_excerpt' => !empty($_POST['wps-customer-comment']) ? $_POST['wps-customer-comment'] : '',
					'post_author' => $user_id,
					'comment_status' => 'closed'
				);

				// Cart items
				$order_items = array();
				$order_tva = array();

				//$cart = (array)$wpshop_cart->cart;
				if ( !empty($_SESSION['cart']) && !empty( $_SESSION['cart']['shipping_method']) ) {
					$_SESSION['cart']['shipping_method'] = __('Standard shipping method', 'wpshop');
				}
				$cart = (array)$_SESSION['cart'];

				$download_codes = array();

				// Nouvelle commande
				$order_id = wp_insert_post($order_data);
				$_SESSION['order_id'] = $order_id;

				// Cr�ation des codes de t�l�chargement si il y a des produits t�l�chargeable dans le panier
				if ( !empty( $cart['order_items']  ) ) {
				foreach($cart['order_items'] as $c) {
					$product = wpshop_products::get_product_data($c['item_id']);
					/** Check if it's a variation and check the parent product **/
					if ( get_post_type( $c['item_id'] ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
						$parent_def = wpshop_products::get_parent_variation( $c['item_id'] );
						if ( !empty($parent_def) && !empty($parent_def['parent_post_meta']) && !empty($parent_def['parent_post_meta']['is_downloadable_']) ) {
							$product['is_downloadable_'] = $parent_def['parent_post_meta']['is_downloadable_'];
						}
					}
					if(!empty($product['is_downloadable_'])) {
						$download_codes[$c['item_id']] = array('item_id' => $c['item_id'], 'download_code' => uniqid('', true));
					}

				}
				}
				if(!empty($download_codes)) update_user_meta($user_id, '_order_download_codes_'.$order_id, $download_codes);

				// Informations de commande � stocker
				$currency = wpshop_tools::wpshop_get_currency(true);
				$order = array_merge(array(
					'order_key' 			=> NULL,
					'customer_id' 			=> $user_id,
					'order_status' 			=> 'awaiting_payment',
					'order_date' 			=> current_time('mysql', 0),
					'order_shipping_date' 	=> null,
					'order_invoice_ref'		=> '',
					'order_currency' 		=> $currency,
					'order_payment' 		=> array(
					'customer_choice' 		=> array('method' => $paymentMethod),
					'received'				=> array('0' => array('method' => $paymentMethod, 'waited_amount' => $cart['order_amount_to_pay_now'], 'status' =>  'waiting_payment', 'author' => $user_id)),
					'shipping_method'       => ( ( !empty( $_SESSION['shipping_method']) ) ? wpshop_tools::varSanitizer( $_SESSION['shipping_method']) : __('Standard shipping method', 'wpshop') )
					),
				), $cart);

				// Si c'est un devis
				if ( $paymentMethod == 'quotation' ) {
					$order['order_temporary_key'] = wpshop_orders::get_new_pre_order_reference();
				}
				else {
					$order['order_key'] = wpshop_orders::get_new_order_reference();
				}

				//Round final amount
				$order['order_grand_total'] = number_format( round($order['order_grand_total'], 2), 2, '.', '');
				$order['order_total_ttc'] = number_format( round($order['order_total_ttc'], 2), 2, '.', '');
				$order['order_amount_to_pay_now'] = number_format( round($order['order_amount_to_pay_now'], 2), 2, '.', '');

				/** On enregistre la commande	*/
				update_post_meta($order_id, '_order_postmeta', $order);
				update_post_meta($order_id, '_wpshop_order_customer_id', $order['customer_id']);
				update_post_meta($order_id, '_wpshop_order_shipping_date', $order['order_shipping_date']);
				update_post_meta($order_id, '_wpshop_order_status', $order['order_status']);


				do_action( 'wps_order_extra_save', $order_id );


				//Add an action to extra actions on order save
				$args = array( 'order_id' => $order_id, 'posted_data' => $_REQUEST);
				wpshop_tools::create_custom_hook( 'wps_order_extra_save_action', $args );

				/**	Set custmer information for the order	*/
				$shipping_address =  ( !empty($shipping_address_option) && !empty($shipping_address_option['activate']) ) ? ( ( !empty($_SESSION['shipping_address']) ) ? wpshop_tools::varSanitizer($_SESSION['shipping_address']) : $customer_shipping_address_id ) : '';
				$billing_address =  ( !empty($_SESSION['billing_address']) ) ? wpshop_tools::varSanitizer($_SESSION['billing_address']) : $customer_billing_address_id;


				if ( !empty( $billing_address) ) {
					wpshop_orders::set_order_customer_addresses($user_id, $order_id, $shipping_address, $billing_address);
				}

				if ( !empty($_SESSION['shipping_address_to_save']) ) {
					$order_infos_postmeta = get_post_meta($order_id, '_order_info', true);
					$order_infos_postmeta['shipping']['address'] = $_SESSION['shipping_address_to_save'];
					$order_infos_postmeta['shipping']['address_id'] = '';
					update_post_meta($order_id, '_order_info', $order_infos_postmeta);
					unset( $_SESSION['shipping_address_to_save'] );
				}


				/** Save Coupon use **/
				if ( !empty($_SESSION['cart']['coupon_id']) ) {
					$wps_coupon_mdl = new wps_coupon_model();
					$wps_coupon_mdl->save_coupon_use( $_SESSION['cart']['coupon_id'] );
				}

				/**	Notify the customer as the case	*/
				$user_info = get_userdata($user_id);
				$email = $user_info->user_email;
				$first_name = $user_info->user_firstname ;
				$last_name = $user_info->user_lastname;

				// Envoie du message de confirmation de commande au client
				$order_meta = get_post_meta( $order_id, '_order_postmeta', true);

				$shipping_mode_option = get_option( 'wps_shipping_mode' );
				$shipping_method = ( !empty($order_meta['order_payment']['shipping_method']) && !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && is_array($shipping_mode_option['modes']) && array_key_exists($order_meta['order_payment']['shipping_method'], $shipping_mode_option['modes'])) ? $shipping_mode_option['modes'][$order_meta['order_payment']['shipping_method']]['name'] : ( (!empty($order_meta['order_payment']['shipping_method']) ) ? $order_meta['order_payment']['shipping_method'] : '' );

				if ( !empty($order_meta) && !empty($order_meta['cart_type']) && $order_meta['cart_type'] == 'quotation' && empty($order_meta['order_key']) ) {
					$wps_message->wpshop_prepared_email($email, 'WPSHOP_QUOTATION_CONFIRMATION_MESSAGE', array('order_id' => $order_id,'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'customer_email' => $email, 'order_date' => current_time('mysql', 0), 'order_content' => '', 'order_addresses' => '', 'order_customer_comments' => '', 'order_billing_address' => '', 'order_shipping_address' => '', 'order_shipping_method' => $shipping_method, 'order_personnal_informations' => '') );
				}
				else {
					$email_option = get_option( 'wpshop_emails' );
					if ( empty($email_option['send_confirmation_order_message']) ) {
						$payment_method_option = get_option( 'wps_payment_mode' );
						$order_payment_method = ( !empty($payment_method_option) && !empty($payment_method_option['mode']) && !empty($order_meta['order_payment']['customer_choice']['method']) && !empty($payment_method_option['mode'][$order_meta['order_payment']['customer_choice']['method']])  ) ? $payment_method_option['mode'][$order_meta['order_payment']['customer_choice']['method']]['name'] : $order_meta['order_payment']['customer_choice']['method'];
	
						$wps_message->wpshop_prepared_email($email, 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', array('order_id' => $order_id,'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'customer_email' => $email, 'order_key' => ( ( !empty($order_meta['order_key']) ) ? $order_meta['order_key'] : ''),'order_date' => current_time('mysql', 0),  'order_payment_method' => $order_payment_method, 'order_content' => '', 'order_addresses' => '', 'order_customer_comments' => '', 'order_billing_address' => '', 'order_shipping_address' => '',  'order_shipping_method' => $shipping_method, 'order_personnal_informations' => '' ) );
					}
				}

				if ( empty($_SESSION['wps-pos-addon']) ) {
					$email_option = get_option('wpshop_emails');
					if( empty($email_option) || ( !empty($email_option) && empty($email_option['send_confirmation_order_message']) ) ){
						self::send_order_email_to_administrator( $order_id, $user_info );
					}
				}


				/** IF Order amount is 0, Finish the Order **/
				if ( $cart['order_amount_to_pay_now'] == 0 ) {
					$order_meta = get_post_meta($order_id, '_order_postmeta', true);
					$payment_status = 'completed';
					$params_array = array (
						'method' =>'free',
						'waited_amount' => $order_meta['order_amount_to_pay_now'],
						'status' =>  'payment_received',
						'author' => $order_meta['customer_id'],
						'payment_reference' => 'FREE_ORDER',
						'date' => current_time('mysql', 0),
						'received_amount' => $order_meta['order_amount_to_pay_now']
					);
					wpshop_payment::check_order_payment_total_amount($order_id, $params_array, $payment_status);
				}
				apply_filters( 'wpshop_finish_order_extra_actions', $order_id);
			}
		endif;
		return $order_id;
	}

	public static function send_order_email_to_administrator ( $order_id, $customer_infos = ''  ) {
		if ( !empty($order_id) ) {
			$wps_message = new wps_message_ctr();
			$order_infos = get_post_meta($order_id, '_order_postmeta', true);
			//Send email to administrator(s)
			$shop_admin_email_option = get_option('wpshop_emails');
			$shop_admin_email = $shop_admin_email_option['contact_email'];
			$order_tmp_key = '';

			$shipping_mode_option = get_option( 'wps_shipping_mode' );
			$shipping_method = ( !empty($order_infos['order_payment']['shipping_method']) && !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && is_array($shipping_mode_option['modes']) && array_key_exists($order_infos['order_payment']['shipping_method'], $shipping_mode_option['modes'])) ? $shipping_mode_option['modes'][$order_infos['order_payment']['shipping_method']]['name'] : ( (!empty($order_infos['order_payment']['shipping_method']) ) ? $order_infos['order_payment']['shipping_method'] : '' );


			if( !empty( $order_infos ) && !empty($order_infos['cart_type']) && $order_infos['cart_type'] == 'normal' && !empty($order_infos['order_key']) ){
				$message_type = 'WPSHOP_NEW_ORDER_ADMIN_MESSAGE';
			}
			else {
				$message_type = 'WPSHOP_NEW_QUOTATION_ADMIN_MESSAGE';
				$order_tmp_key = $order_infos['order_temporary_key'];
			}

			$payment_method_option = get_option( 'wps_payment_mode' );
			$order_payment_method = ( !empty($payment_method_option) && !empty($payment_method_option['mode']) && !empty($order_infos['order_payment']['customer_choice']['method']) && !empty($payment_method_option['mode'][$order_infos['order_payment']['customer_choice']['method']])  ) ? $payment_method_option['mode'][$order_infos['order_payment']['customer_choice']['method']]['name'] : $order_infos['order_payment']['customer_choice']['method'];

			$data_to_send = array('order_id' => $order_id, 'order_key' => $order_infos['order_key'], 'customer_email' => ( !empty($customer_infos) && !empty($customer_infos->user_email) ) ? $customer_infos->user_email : '' , 'customer_last_name' => ( !empty($customer_infos) && !empty($customer_infos->user_lastname) ) ? $customer_infos->user_lastname : '', 'customer_first_name' => ( !empty($customer_infos) && !empty($customer_infos->user_firstname) ) ? $customer_infos->user_firstname : '', 'order_date' => $order_infos['order_date'], 'order_payment_method' => $order_payment_method, 'order_temporary_key' => $order_tmp_key, 'order_content' => '', 'order_addresses' => '', 'order_customer_comments' => '', 'order_billing_address' => '', 'order_shipping_address' => '','order_shipping_method' => $shipping_method, 'order_personnal_informations' => '' );

			$wps_message->wpshop_prepared_email( $shop_admin_email, $message_type, $data_to_send, array('object_type' => 'order', 'object_id' => $order_id));
		}
	}

	public static function direct_payment_link( $token, $order_id, $login ) {

		global $wpdb;
		if( !empty($token) && !empty($order_id) && !empty($login) ) {
			/** Verify informations **/
			$query = $wpdb->prepare( 'SELECT * FROM ' .$wpdb->users. ' WHERE user_login = %s AND user_activation_key = %s', $login, $token);
			$user_infos = $wpdb->get_row( $query );
			if( !empty($user_infos) ) {
				/** Connect the user **/
				$secure_cookie = is_ssl() ? true : false;
				wp_set_auth_cookie($user_infos->ID, true, $secure_cookie);

				/** Add order to SESSION **/
				$order_meta = get_post_meta($order_id, '_order_postmeta', true);
				$_SESSION['cart'] = array();
				$_SESSION['cart']['order_items'] = array();
				if ( !empty($order_meta) && !empty( $order_meta['order_items']) ) {
					$wpshop_cart_type = 'cart';
					foreach( $order_meta['order_items'] as $item ) {;
						$_SESSION['cart']['order_items'][$item['item_id']] = $item;
					}
					$wps_cart_ctr = new wps_cart();
					$order = $wps_cart_ctr->calcul_cart_information( array() );
					$wps_cart_ctr->store_cart_in_session( $order );
				}
				$_SESSION['order_id'] = $order_id;
				$wpdb->update($wpdb->users, array('user_activation_key' => ''), array('user_login' => $login) );
				wpshop_tools::wpshop_safe_redirect( get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ) );
			}
			else {
				wpshop_tools::wpshop_safe_redirect( get_permalink( wpshop_tools::get_page_id( get_option('wpshop_myaccount_page_id') ) ) );
			}

		}
	}

}