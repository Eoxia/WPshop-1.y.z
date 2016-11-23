<?php
/**
 * Controller new interface for variation
 */
class wps_product_variation_interface {
	/**
	 * Array of variations.
	 *
	 * @var array
	 */
	private $variations = null;
	/**
	 * Call hooks.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 11 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_filter( 'wpshop_attribute_output_def', array( $this, 'wpshop_attribute_output_def' ), 10, 2 );
	}
	/**
	 * Call private functions if class is activate.
	 *
	 * @param  string $name      Name of function to call.
	 * @param  array  $arguments  Array of arguments to pass.
	 * @return mixed             Return of real function called.
	 */
	public function __call( $name, $arguments ) {
		if ( method_exists( $this, $name ) ) {
			if ( $this->is_activate() ) {
				return call_user_func_array( array( $this, $name ), $arguments );
			}
		}
	}
	/**
	 * Get if module is active.
	 *
	 * @param  boolean $new_val If set & db is different, save it.
	 * @return boolean          Actual state of module.
	 */
	private function is_activate( $new_val = null ) {
		if ( ! isset( $this->is_active ) ) {
			$this->is_active = (bool) get_option( 'wps_variation_interface_display', false );
		}
		if ( isset( $new_val ) && $this->is_active !== $new_val ) {
			update_option( 'wps_variation_interface_display', $new_val );
		}
		return $this->is_active;
	}
	/**
	 * Get variations of an element.
	 *
	 * @param  array $element Direct array attribute of db.
	 * @return void          It set directly in variable class.
	 */
	private function get_variations( $element ) {
		if ( ! isset( $this->variations ) ) {
			$variations = wpshop_attributes::get_variation_available_attribute( $element );
			$this->variations = (array) array_merge( (isset( $variations['available'] ) ? $variations['available'] : array()), (isset( $variations['unavailable'] ) ? $variations['unavailable'] : array()) );
			unset( $variations );
		}
	}
	/**
	 * Add metabox & remove old one (cqfd).
	 */
	public function add_meta_boxes() {
		$_wpnonce = ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // Input var okay.
		$wps_variation_interface = ! empty( $_GET['wps_variation_interface'] ) ? filter_var( wp_unslash( $_GET['wps_variation_interface'] ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) : null; // Input var okay.
		if ( wp_verify_nonce( $_wpnonce, 'wps_remove_variation_interface' ) && null !== $wps_variation_interface ) {
			$this->is_activate( $wps_variation_interface );
		}
		if ( $this->is_activate() ) {
			remove_meta_box( 'wpshop_wpshop_variations', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal' );
			add_meta_box( 'wpshop_wpshop_variations', __( 'Product variation', 'wpshop' ), array( $this, 'meta_box_variation' ), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default' );
		}
	}
	/**
	 * The metabox.
	 *
	 * @param  object $post Given by WordPress.
	 * @return void       Display directly.
	 */
	private function meta_box_variation( $post ) {
		$this->get_variations( $post->ID );
		if ( empty( $this->variations ) ) {
			remove_meta_box( 'wpshop_wpshop_variations', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal' );
			return;
		}
		global $wpdb;
		$ids = array();
		foreach ( $this->variations as $key => $variation ) {
			$available = wpshop_attributes::get_select_output( $variation['attribute_complete_def'] );
			$this->variations[ $key ]['available'] = array_keys( $available['possible_value'] );
			$ids = array_merge( $ids, array_keys( $available['possible_value'] ) );
		}
		$query = $wpdb->prepare( 'SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE id IN ('" . implode( "', '", $ids ) . "') AND status = %s", 'valid' );
		$product = wpshop_products::get_product_data( $post->ID, false, '"publish"' );
		$is_used_in_variation = wpshop_attributes::getElement( 'yes', "'valid', 'notused'", 'is_used_in_variation', true );
		$attribute_list = array();
		foreach ( $is_used_in_variation as $attribute ) {
			if ( in_array( $attribute->backend_input, array( 'multiple-select', 'select', 'radio', 'checkbox' ), true ) ) {
				$values = wpshop_attributes::get_select_output( $attribute );
				if ( ! empty( $values['possible_value'] ) ) {
					$attribute->possible_value = $values['possible_value'];
				}
			}
			$attribute_list[ $attribute->code ] = $attribute;
		}
		$variation_defining = get_post_meta( $post->ID, '_wpshop_variation_defining', true );
		$variations_saved = wpshop_products::get_variation( $post->ID );
		$price_piloting = get_option( 'wpshop_shop_price_piloting' );
		$product_currency = wpshop_tools::wpshop_get_currency();
		wp_enqueue_style( 'wps-product-variation-interface-style', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/css/style-backend.css' );
		wp_enqueue_script( 'wps-product-variation-interface-script', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/js/script-backend.js' );
		wp_enqueue_script( 'wps-product-variation-interface-script-utils', WPSPDTVARIATION_INTERFACE_ASSETS_MAIN_DIR . '/js/script-backend-utils.js' );
		wp_localize_script( 'wps-product-variation-interface-script', 'wps_product_variation_interface', array( 'variation' => $this->variations, 'nonce_delete' => wp_create_nonce( 'wpshop_variation_management' ), 'variation_value' => $wpdb->get_results( $query ), 'product_price' => $product['product_price'], 'tx_tva' => $product['tx_tva'], 'attribute_in_variation' => $attribute_list, 'variations_saved' => $variations_saved, 'price_piloting' => $price_piloting, 'currency' => $product_currency, 'variation_defining' => $variation_defining ) );
		require_once( wpshop_tools::get_template_part( WPSPDTVARIATION_INTERFACE_DIR, WPSPDTVARIATION_INTERFACE_TEMPLATES_MAIN_DIR, 'backend', 'meta_box_variation' ) );
	}
	/**
	 * Save other values out of old variation interface.
	 *
	 * @param  int    $post_id Id of post.
	 * @param  object $post    WP_Post of WordPress.
	 * @return void
	 */
	private function save_post( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) && WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT !== $post->post_type ) {
			return;
		}
		wpshop_products::variation_parameters_save( $post_id, $_REQUEST['wpshop_variation_defining']['options'] );
	}
	/**
	 * Delete display in attributes products always in metabox.
	 *
	 * @param  array $output  Result of wpshop_attributes::display_attribute() function.
	 * @param  array $element Direct array attribute of db.
	 * @return array          SAme array $output modified.
	 */
	public function wpshop_attribute_output_def( $output, $element ) {
		if ( $this->is_activate() ) {
			$this->get_variations( $element );
			if ( ! empty( $this->variations ) && in_array( $output['field_definition']['name'], array_keys( $this->variations ), true ) ) {
				$output['field'] = '';
			}
		}
		return $output;
	}
}
