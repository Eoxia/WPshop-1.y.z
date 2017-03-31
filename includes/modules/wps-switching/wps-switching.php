<?php
/**
 * WpShop Uer switching
 *
 * @author Eoxia dev team <dev@eoxia.com>
 * @version
 * @package includes
 * @subpackage modules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPSHOP_VERSION' ) ) {
	die( __( 'You are not allowed to use this service.', 'wpshop' ) );
}

/** Test if userswithcing module is active */
if ( class_exists( 'user_switching' ) ) {
	add_filter( 'wps_filter_customer_action_metabox', 'wps_display_user_switching_link', 10, 2 );
	add_filter( 'wps_filter_customer_list_actions', 'wps_display_user_switching_link', 10, 2 );
	add_filter( 'wps_filter_customer_in_order', 'wps_display_user_switching_link', 10, 2 );

	/**
	 * Display the link allowing to switch to another user everywhere it is possible
	 *
	 * @param  integer $customer_id The WPShop customer id (equals to post id).
	 * @param  integer $wp_user_id  Corresponding user id.
	 * @param  boolean $need_separator  Add an automatic separator to output.
	 */
	function wps_display_user_switching_link( $customer_id, $wp_user_id, $need_separator = true ) {
		$output = '';

		$user = get_user_by( 'id', $wp_user_id );
		if ( false !== $user ) {
			$output = '<a href="' . esc_url( user_switching::switch_to_url( $user ) ) . '" >' . esc_html( 'Switch to user', 'wpshop' ) . '</a>';
		}

		echo $output;
	}
}
