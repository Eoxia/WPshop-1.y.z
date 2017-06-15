<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_orders_mdl {

	function __construct() { }

	function get_customer_orders( $customer_id ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s AND post_status NOT IN ( %s ) AND post_status != %s ORDER BY ID DESC", $customer_id, WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'auto-draft', 'trash' );
		$orders = $wpdb->get_results( $query );

		return $orders;
	}
}
