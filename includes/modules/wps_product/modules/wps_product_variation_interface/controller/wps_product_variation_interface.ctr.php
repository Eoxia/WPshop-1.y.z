<?php
class wps_product_variation_interface {
	private $variations = array();
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 11);
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
}