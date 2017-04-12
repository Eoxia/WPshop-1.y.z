<?php if ( ! defined( 'ABSPATH' ) ) { exit;
}

// End if().
if ( ! defined( 'WPSHOP_VERSION' ) ) {
	die( __( 'Access is not allowed by this way', 'wpshop' ) );
}

/**
 * Emails options management
 *
 * Define the different method to manage the different emails options
 *
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 * @package wpshop
 * @subpackage librairies
 */

/**
 * Define the different method to manage the different emails options
 *
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_email_options {



	/**
	 *
	 */
	public static function declare_options() {

		add_settings_section( 'wpshop_emails', '<span class="dashicons dashicons-email"></span>' . __( 'Email addresses', 'wpshop' ), array( 'wpshop_email_options', 'plugin_section_text' ), 'wpshop_emails' );
		register_setting( 'wpshop_options', 'wpshop_emails', array( 'wpshop_email_options', 'wpshop_options_validate_emails' ) );
		add_settings_field( 'wpshop_noreply_email', __( 'Mails answers address email', 'wpshop' ), array( 'wpshop_email_options', 'wpshop_noreply_email_field' ), 'wpshop_emails', 'wpshop_emails' );
		add_settings_field( 'wpshop_contact_email', __( 'Contact email', 'wpshop' ), array( 'wpshop_email_options', 'wpshop_contact_email_field' ), 'wpshop_emails', 'wpshop_emails' );
		add_settings_field( 'wpshop_send_confirmation_order_email', '', array( 'wpshop_email_options', 'wpshop_send_confirmation_order_message_field' ), 'wpshop_emails', 'wpshop_emails' );
		/** Define the settings section for message	*/
		add_settings_section( 'wpshop_messages', '<span class="dashicons dashicons-email-alt"></span>' . __( 'Messages', 'wpshop' ), array( 'wpshop_email_options', 'plugin_section_text' ), 'wpshop_messages' );
		/**	Get default messages defined into xml files 	*/
		$xml_default_emails = file_get_contents( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/assets/datas/default_emails.xml' );
		$default_emails = new SimpleXMLElement( $xml_default_emails );
		/**	Read default emails for options creation	*/
		foreach ( $default_emails->xpath( '//emails/email' ) as $email ) {
			if ( ( WPSHOP_DEFINED_SHOP_TYPE == (string) $email->attributes()->shop_type ) || ( 'sale' == WPSHOP_DEFINED_SHOP_TYPE ) ) {
				register_setting( 'wpshop_options', (string) $email->attributes()->code, array( 'wpshop_email_options', 'wps_options_validate_emails' ) );
				add_settings_field( (string) $email->attributes()->code, __( (string) $email->description, 'wpshop' ), array( 'wpshop_email_options', 'wps_options_emails_field' ), 'wpshop_messages', 'wpshop_messages', array(
					'code' => (string) $email->attributes()->code,
				) );
			}
		}
	}

	/**
	 * Common section description
	 */
	public static function plugin_section_text() {

		// End if().
	}

	/* ------------------------ */
	/* --------- EMAILS ------- */
	/* ------------------------ */
	public static function wpshop_noreply_email_field() {

		$admin_email = get_bloginfo( 'admin_email' );
		$emails = get_option( 'wpshop_emails', null );
		$email = empty( $emails['noreply_email'] ) ? $admin_email : $emails['noreply_email'];
		echo '<input name="wpshop_emails[noreply_email]" type="text" value="' . $email . '" />
		<a href="#" title="' . __( 'This is the no reply email','wpshop' ) . '" class="wpshop_infobulle_marker">?</a>';
	}
	public static function wpshop_contact_email_field() {

		$admin_email = get_bloginfo( 'admin_email' );
		$emails = get_option( 'wpshop_emails', null );
		$email = empty( $emails['contact_email'] ) ? $admin_email : $emails['contact_email'];
		echo '<input name="wpshop_emails[contact_email]" type="text" value="' . $email . '" />
		<a href="#" title="' . __( 'This is the email on which customers can contact you','wpshop' ) . '" class="wpshop_infobulle_marker">?</a>';
	}
	public static function wpshop_options_validate_emails( $input ) {
		return $input;
	}

	public static function wpshop_send_confirmation_order_message_field() {

		$email_option = get_option( 'wpshop_emails' );
		$output = '<input type="checkbox" name="wpshop_emails[send_confirmation_order_message]" id="wpshop_emails_send_confirmation_order_message" ' . ( ( ! empty( $email_option ) && ! empty( $email_option['send_confirmation_order_message'] ) ) ? 'checked="checked"' : '') . '/> ';
		$output .= '<label for="wpshop_emails_send_confirmation_order_message">' . __( 'Send confirmation order message when order is totally paid', 'wpshop' ) . '</label>';
		echo $output;
	}

	function wpshop_send_confirmation_order_message_validate( $input ) {

		return $input;
	}

	/* -------------------------- */
	/* --------- MESSAGES ------- */
	/* -------------------------- */
	/**
	 *
	 * @param unknown_type $input
	 * @return unknown
	 */
	public static function wps_options_validate_emails( $input ) {

		return $input;
	}

	/**
	 *
	 * @param unknown_type $args
	 */
	public static function wps_options_emails_field( $args ) {

		$content = '';
		$current_message_id = get_option( $args['code'], '' );
		/*
		$wps_message = new wps_message_ctr();
		$options = $wps_message->getMessageListOption( $current_message_id );

		if ( !empty( $options ) ) {
			$content .= '<select name="' . $args['code'] . '" class="chosen_select" >';
			$content .= $options;
			$content .= '</select> <a id="wps-email-' . $current_message_id . '" title="' . __( 'Edit current selected message', 'wpshop' ) . '" href="' . admin_url( 'post.php?post=' . $current_message_id . '&action=edit' ) . '" target="_wps_content_customisation" class="shop-content-customisation shop-content-customisation-email dashicons dashicons-edit"></a>';
		}*/

		echo $content . '<input type="hidden" name="' . $args['code'] . '" value="' . $current_message_id . '"><a id="wps-email-' . $current_message_id . '" title="' . __( 'Edit current selected message', 'wpshop' ) . '" href="' . admin_url( 'post.php?post=' . $current_message_id . '&action=edit' ) . '" target="_wps_content_customisation" class="shop-content-customisation shop-content-customisation-email dashicons dashicons-edit"></a>';
	}

}
