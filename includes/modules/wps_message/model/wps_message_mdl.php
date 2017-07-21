<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_message_mdl {

	function __construct() { }

	/**
	 * Return messages data for a message type or not and for a user or not
	 * @param integer $message_id
	 * @param integer $customer_id
	 * @return array
	 */
	function get_messages_histo( $message_id = '', $customer_id = '' ) {
		global $wpdb;
		$messages_data = array();
		if( empty($customer_id) ) {
			$query = $wpdb->prepare( 'SELECT meta_id, meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key LIKE %s', '_wpshop_messages_histo_' . $message_id . '%' );
		} else {
			$query = $wpdb->prepare( 'SELECT meta_id, meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key LIKE %s AND post_id = %d', '_wpshop_messages_histo_' . $message_id . '%', $customer_id );
		}

		$messages = $wpdb->get_results( $query );

		if( !empty($messages) ) {
			foreach( $messages as $message ) {
				$messages_data[$message->meta_id] = maybe_unserialize( $message->meta_value );
			}
		}
		return $messages_data;
	}
}
