<?php
class wps_product_variation_interface {
	private $variations = array();
	public function __construct() {
		if( get_option( 'wps_variation_interface_display', true ) ) {
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 11);
		}
		add_action( 'wp_ajax_wps-remove-variation-interface', array( $this, 'wps_remove_variation_interface' ) );
		add_action( 'wp_ajax_wps-display-variation-interface', array( $this, 'wps_display_variation_interface' ) );
	}
	public function add_meta_boxes() {
		remove_meta_box( 'wpshop_wpshop_variations', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal' );
		add_meta_box( 'wpshop_wpshop_variations', __('Product variation', 'wpshop'), array($this, 'meta_box_variation'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default' );
	}
	public function meta_box_variation( $post ) {
		$variations = wpshop_attributes::get_variation_available_attribute( $post->ID );
		$this->variations = array_merge((isset($variations['available']) ? $variations['available'] : array()), (isset($variations['unavailable']) ? $variations['unavailable'] : array()));
		unset($variations);
		if( empty( $this->variations ) ) {
			remove_meta_box( 'wpshop_wpshop_variations', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal' );
			return;
		}
		wp_enqueue_style( 'wps-product-variation-interface-style', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/css/style-backend.css' );
		wp_enqueue_script( 'wps-product-variation-interface-script', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/js/script-backend.js' );
		wp_enqueue_script( 'wps-product-variation-interface-script-utils', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/js/script-backend-utils.js' );
		wp_localize_script( 'wps-product-variation-interface-script', 'wps_product_variation_interface', array( 'variation' => $this->variations ) );
		require_once( wpshop_tools::get_template_part( WPSPDTVARIATION_INTERFACE_DIR, WPSPDTVARIATION_INTERFACE_TEMPLATES_MAIN_DIR, "backend", "meta_box_variation" ) );
	}
	public function wps_remove_variation_interface() {
		$_wponce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_remove_variation_interface' ) )
			wp_die();

		update_option( 'wps_variation_interface_display', false );
	}
	public function wps_display_variation_interface() {
		$_wponce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_display_variation_interface' ) )
			wp_die();

		update_option( 'wps_variation_interface_display', true );
	}
}