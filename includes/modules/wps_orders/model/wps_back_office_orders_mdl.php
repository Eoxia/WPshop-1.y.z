<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_back_office_orders_mdl {
	function __construct() {

	}

	/** Add a pricate comment to order **/
	function add_private_comment($oid, $comment, $send_email = '', $send_sms = false, $copy_to_administrator = '') {
		// Check informations
		$order_private_comments = get_post_meta($oid, '_order_private_comments', true);
		$order_private_comments = !empty($order_private_comments) ? $order_private_comments : array();
		$order_meta = get_post_meta($oid, '_order_postmeta', true);

		// Send email is checked
		if( !empty($send_email) ) {
			// New object wps_message_ctr
			$wps_message = new wps_message_ctr();

			// Get order current content
			$user = get_post_meta($oid, '_order_info', true);

			$email = isset($user['billing']['address']['address_user_email']) ? $user['billing']['address']['address_user_email'] :'';

			/** Si pas d'email trouvé, utilises l'adresse email par défault du client */
			if(empty($email)) {
				$customer_id = get_post_meta( $oid, '_wpshop_order_customer_id', true );
				$user_info = get_userdata($customer_id);
				$email = $user_info->user_email;
			}
			$first_name = isset($user['billing']['address']['address_first_name'])?$user['billing']['address']['address_first_name']:'';
			$last_name = isset($user['billing']['address']['address_last_name'])?$user['billing']['address']['address_last_name']:'';

			$object = array('object_type'=>'order','object_id'=>$oid);
			/* Envoie du message de confirmation de commande au client	*/
			$wps_message->wpshop_prepared_email(
					$email,
					'WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE',
					array('order_id' => $oid, 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_key' => $order_meta['order_key'], 'message' => $comment, 'order_addresses' => '', 'order_billing_address' => '', 'order_shipping_address' => ''),
					$object
			);
			// Copy to Administrator
			if ( !empty($copy_to_administrator) ) {
				$email = get_option( 'wpshop_emails' );
				$email = $email['contact_email'];
				$wps_message->wpshop_prepared_email(
						$email,
						'WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE',
						array( 'order_id' => $oid, 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_key' => $order_meta['order_key'], 'message' => $comment, 'order_addresses' => '', 'order_billing_address' => '', 'order_shipping_address' => ''),
						$object
				);
			}
		}

		// Private comment informations
		$order_private_comments[] = array(
				'comment_date' => current_time('mysql',0),
				'send_email' => $send_email,
				'send_sms' => $send_sms,
				'comment' => $comment,
				'author' => get_current_user_id()
		);
		// Save it
		if(is_array($order_private_comments)) {
			update_post_meta($oid, '_order_private_comments', $order_private_comments);
			return true;
		}
		else return false;
	}
}
