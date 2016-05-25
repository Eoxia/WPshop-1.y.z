<?php if ( !defined( 'ABSPATH' ) ) exit;

class wps_wishlist_settings {
	public static function get_option() {
		$result = get_option('wpshop_catalog_product_option');
		return ( !empty($result) && !empty($result['wps_wishlist_display']) ) ? $result['wps_wishlist_display'] : null;
	}
	public function __construct() {
		add_action('wsphop_options', array($this, 'declare_options'));
	}
	
	public function declare_options() {
		register_setting('wpshop_options', 'wpshop_catalog_product_option');
		add_settings_field('wpshop_catalog_product_option_wishlist', __('Activate wishlist', 'wps_wishlist_i18n'), array($this, 'display_option'), 'wpshop_catalog_product_option', 'wpshop_catalog_product_section');
	}
	
	function display_option() {
		$wps_wishlist_display = self::get_option();
		require_once( wpshop_tools::get_template_part( WPS_WISHLIST_DIR, WPS_WISHLIST_TEMPLATE_DIR, 'backend/settings', "wishlist_option" ) );
	}
}