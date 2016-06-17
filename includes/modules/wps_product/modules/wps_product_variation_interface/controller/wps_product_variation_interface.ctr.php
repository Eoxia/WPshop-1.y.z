<?php
class wps_product_variation_interface {
	private $variations = array();
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 11);
		add_action( 'wp_ajax_add_empty_variation', array( $this, 'add_empty_variation' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
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
		$variation_defining = get_post_meta( $post->ID, '_wpshop_variation_defining', true);
		$variations_saved = wpshop_products::get_variation( $post->ID );
		$price_piloting = get_option( 'wpshop_shop_price_piloting' );
		$productCurrency = wpshop_tools::wpshop_get_currency();
		wp_enqueue_style( 'wps-product-variation-interface-style', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/css/style-backend.css' );
		wp_enqueue_script( 'wps-product-variation-interface-script', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/js/script-backend.js' );
		wp_enqueue_script( 'wps-product-variation-interface-script-utils', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/js/script-backend-utils.js' );
		wp_localize_script( 'wps-product-variation-interface-script', 'wps_product_variation_interface', array( 'variation' => $this->variations, 'variation_value' => $wpdb->get_results($query), 'product_price' => $product['product_price'], 'tx_tva' => $product['tx_tva'], 'attribute_in_variation' => $attribute_list, 'variations_saved' => $variations_saved, 'price_piloting' => $price_piloting, 'currency' => $productCurrency, 'variation_defining' => $variation_defining ) );
		require_once( wpshop_tools::get_template_part( WPSPDTVARIATION_INTERFACE_DIR, WPSPDTVARIATION_INTERFACE_TEMPLATES_MAIN_DIR, "backend", "meta_box_variation" ) );
	}
	public function save_post( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) && WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT != $post->post_type ) {
			return;
		}
		$_wpshop_variation_defining = array_merge( ( $_POST['wpshop_variation_defining'] ) ? $_POST['wpshop_variation_defining'] : array(), get_post_meta( $post_id, '_wpshop_variation_defining', true) );
		update_post_meta( $post_id, '_wpshop_variation_defining', $_wpshop_variation_defining );
	}
	// AJAX
	public function add_empty_variation() {
		check_ajax_referer( 'wps_add_empty_variation_variation_interface' );
		$attributes_for_variation = isset($_POST['variation_attr']) ? (array) $_POST['variation_attr'] : null;
		$current_post_id = isset($_POST['post_id']) ? sanitize_key($_POST['post_id']) : null;
		echo json_encode( array( 'ID' => wpshop_products::creation_variation_callback( array( 0 => $attributes_for_variation ), $current_post_id ) ) );
		wp_die();
	}
}