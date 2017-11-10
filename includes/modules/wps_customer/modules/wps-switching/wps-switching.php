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
	die( esc_html( 'You are not allowed to use this service.', 'wpshop' ) );
}

/** Test if userswithcing module is active */
if ( class_exists( 'user_switching' ) ) {

	add_action( 'wps_customer_contacts_list_action', 'wps_display_user_switching_link_in_contacts' );
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
		echo wps_display_user_switching_link_from_user_id( $wp_user_id );  // WPCS: XSS ok.
	}

	/**
	 * Display the link allowing to switch to another user everywhere it is possible
	 *
	 * @param  integer $user_id The user id we want to switch to.
	 */
	function wps_display_user_switching_link_in_contacts( $user_id ) {
		$output = '';

		$user = get_user_by( 'id', $user_id );
		if ( ( false !== $user ) && ( get_current_user_id() !== $user_id ) ) {
			$output = '<a href="' . esc_url( user_switching::switch_to_url( $user ) ) . '" title="' . esc_attr( 'Switch to user', 'wpshop' ) . '" ><i class="dashicons dashicons-randomize" ></i></a>';
		}

		echo $output;
	}

	/**
	 * Construit le lien d'afficahge permettant de changer d'utilisateur à partir de l'identifiant de l'utilisateur
	 *
	 * @param  integer $user_id L'identifiant de l'utilisateur pour lequel on veut prendre la main.
	 *
	 * @return string          Le lien permettant de prendre la main sur le compte utilisateur
	 */
	function wps_display_user_switching_link_from_user_id( $user_id ) {
		$output = '';

		$user = get_user_by( 'id', $user_id );
		if ( ( false !== $user ) && ( get_current_user_id() !== $user_id ) ) {
			$output = '<a href="' . esc_url( user_switching::switch_to_url( $user ) ) . '" >' . esc_html( 'Switch to user', 'wpshop' ) . '</a>';
		}

		return $output;
	}
}
