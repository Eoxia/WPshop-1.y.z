<?php
class wps_product_variation_interface {
	private $variations = array();
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 11);
	}
	public function add_meta_boxes() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
		$wps_variation_interface = !empty( $_GET['wps_variation_interface'] ) ? filter_var($_GET['wps_variation_interface'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
		if ( wp_verify_nonce( $_wpnonce, 'wps_remove_variation_interface' ) && $wps_variation_interface !== null ) {
			update_option( 'wps_variation_interface_display', $wps_variation_interface );
		}
		if( get_option( 'wps_variation_interface_display', true ) ) {
			remove_meta_box( 'wpshop_wpshop_variations', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal' );
			add_meta_box( 'wpshop_wpshop_variations', __('Product variation', 'wpshop'), array($this, 'meta_box_variation'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default' );
		}
	}
	public function meta_box_variation( $post ) {
		$variations = wpshop_attributes::get_variation_available_attribute( $post->ID );
		$this->variations = array_merge((isset($variations['available']) ? $variations['available'] : array()), (isset($variations['unavailable']) ? $variations['unavailable'] : array()));
		unset($variations);
		if( empty( $this->variations ) ) {
			remove_meta_box( 'wpshop_wpshop_variations', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal' );
			return;
		}
		global $wpdb;
		$ids = array();
		foreach ( $this->variations as $variation ) {
			$ids = array_merge( $ids, $variation['values'] );
		}
		$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE id IN ('" . implode( "', '", $ids ) . "') AND status = %s", 'valid');
		$product = wpshop_products::get_product_data( $post->ID, false, '"publish"' );
		$attribute_list = wpshop_attributes::getElement('yes', "'valid'", "is_used_in_variation", true);
		$attribute_in_variation = array();
		foreach ($attribute_list as $attribute_def) {
			$attribute_in_variation_sub = array();
			$attribute_in_variation_sub['attribute_def'] = $attribute_def;
			$attribute_in_variation_sub['field_definition'] = wpshop_attributes::get_attribute_field_definition( $attribute_def, (!empty($variations_attribute_parameters['variation_dif_values'][$attribute_def->code]) ? $variations_attribute_parameters['variation_dif_values'][$attribute_def->code] : ''), $variations_attribute_parameters );
			$attribute_in_variation[] = $attribute_in_variation_sub;
		}
		wp_enqueue_style( 'wps-product-variation-interface-style', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/css/style-backend.css' );
		wp_enqueue_script( 'wps-product-variation-interface-script', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/js/script-backend.js' );
		wp_enqueue_script( 'wps-product-variation-interface-script-utils', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/js/script-backend-utils.js' );
		wp_localize_script( 'wps-product-variation-interface-script', 'wps_product_variation_interface', array( 'variation' => $this->variations, 'variation_value' => $wpdb->get_results($query), 'product_price' => $product['product_price'], 'tx_tva' => $product['tx_tva'], 'attribute_in_variation' => $attribute_in_variation ) );
		require_once( wpshop_tools::get_template_part( WPSPDTVARIATION_INTERFACE_DIR, WPSPDTVARIATION_INTERFACE_TEMPLATES_MAIN_DIR, "backend", "meta_box_variation" ) );
	}
}