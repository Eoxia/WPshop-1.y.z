<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Pages options management
 *
 * Define the different method to manage the different emails options
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
class wpshop_page_options {
	/**
	 * Declare options
	 */
	public static function declare_options() {
		add_settings_section('wpshop_pages_option', '<span class="dashicons dashicons-welcome-write-blog"></span>'.__('WPShop pages configuration', 'wpshop'), array('wpshop_page_options', 'plugin_section_text'), 'wpshop_pages_option');

		/**	Get default messages defined into xml files 	*/
		$default_pages =  new SimpleXMLElement( file_get_contents( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/assets/datas/default_pages.xml' ) );
		/**	Read default emails for options creation	*/
		foreach ( $default_pages->xpath( '//pages/page' ) as $page ) {
			if ( ( WPSHOP_DEFINED_SHOP_TYPE == (string)$page->attributes()->shop_type ) || ( 'sale' == WPSHOP_DEFINED_SHOP_TYPE ) ) {
				register_setting( 'wpshop_options', (string)$page->attributes()->code, array( 'wpshop_page_options', 'wpshop_options_validate_wpshop_shop_pages' ) );
				add_settings_field( (string)$page->attributes()->code, __( (string)$page->description, 'wpshop' ), array( 'wpshop_page_options', 'wps_pages_field' ), 'wpshop_pages_option', 'wpshop_pages_option', array( 'code' => (string)$page->attributes()->code, ) );
			}
		}
	}

	/**
	 * Common section description
	 */
	public static function plugin_section_text() {
		printf( __( 'We define default pages content and layout, however you have possibility to %sedit them%s', 'wpshop' ), '<a href="' . admin_url( 'edit.php?post_type=page' ) . '" target="_wps_content_customisation" >', '</a>');
	}

	/**
	 * Shop pages configurations
	 */
	public static function wps_pages_field( $args ) {
		$content = '';

		$current_page_id = get_option( $args['code'], '' );
		$post_list = get_pages();
		if (!empty($post_list)) {
			$content .= '<select name="' . $args['code'] . '" class="chosen_select shop-content-customisation" ><option value="" >' . __('Choose a page to associate', 'wpshop') . '</option>';
			$p=1;
			$error = false;
			foreach ($post_list as $post) {
				$selected = (!empty($current_page_id) && ($current_page_id == $post->ID)) ? ' selected="selected"' : '';
				$content .= '<option'.$selected.' value="' . $post->ID . '" >' . $post->post_title . '</option>';
			}
			$content .= '</select> <a id="wps-page-' . $current_page_id . '" title="' . __( 'Edit current selected page', 'wpshop' ) . '" href="' . admin_url( 'post.php?post=' . $current_page_id . '&action=edit' ) . '" target="_wps_content_customisation" class="shop-content-customisation shop-content-customisation-page dashicons dashicons-edit"></a>';
		}
		wp_reset_query();

		echo $content;
	}

	/**
	 *
	 * @param unknown_type $input
	 * @return unknown
	 */
	public static function wpshop_options_validate_wpshop_shop_pages($input) {
		return $input;
	}

}