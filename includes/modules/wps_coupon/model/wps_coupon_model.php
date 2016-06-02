<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_coupon_model {
	function __construct() {

	}

	/**
	 * Get coupons
	 * @return array
	 */
	function get_coupons() {
		$coupons = get_posts( array( 'post_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_COUPON, 'post_status' => 'publish' ) );
		return $coupons;
	}

	/**
	 * Save coupon custom datas on asave post action
	 * @param array $data
	 */
	function save_coupons_informations( $data ) {
		if( !empty($data) ) {
			$amount_min_limit = array( 'amount' => ( ( !empty($data['wpshop_coupon_mini_amount']) ) ? $data['wpshop_coupon_mini_amount'] : null ), 'shipping_rule' => ( ( !empty($data['wpshop_coupon_min_mount_shipping_rule']) ) ? $data['wpshop_coupon_min_mount_shipping_rule'] : null ) );
			update_post_meta($data['post_ID'], 'wpshop_coupon_code', $data['coupon_code']);
			update_post_meta($data['post_ID'], 'wpshop_coupon_discount_value', floatval( str_replace(',', '.',$data['coupon_discount_amount']) ) );
			update_post_meta($data['post_ID'], 'wpshop_coupon_discount_type', $data['wpshop_coupon_discount_type']);
			update_post_meta($data['post_ID'], 'wpshop_coupon_individual_use', $data['coupon_receiver'] );
			update_post_meta($data['post_ID'], 'wpshop_coupon_product_ids', '');
			update_post_meta($data['post_ID'], 'wpshop_coupon_exclude_product_ids', '');
			update_post_meta($data['post_ID'], 'wpshop_coupon_usage_limit', $data['coupon_usage_limit'] );
			update_post_meta($data['post_ID'], 'wpshop_coupon_start_date', '');
			update_post_meta($data['post_ID'], 'wpshop_coupon_expiry_date', '');
			update_post_meta($data['post_ID'], 'wpshop_coupon_apply_before_tax', '');
			update_post_meta($data['post_ID'], 'wpshop_coupon_free_shipping', '');
			update_post_meta($data['post_ID'], 'wpshop_coupon_product_categories', '');
			update_post_meta($data['post_ID'], 'wpshop_coupon_exclude_product_categories', '');
			update_post_meta($data['post_ID'], 'wpshop_coupon_minimum_amount', $amount_min_limit);
		}
	}

	/**
	 * Get coupon datas
	 * @param integer $coupon_id
	 * @return array
	 */
	function get_coupon_data( $coupon_id ) {
		global $wpdb;
		$coupon_id = ( !empty($coupon_id) ) ? $coupon_id : ( !empty($_SESSION['cart']['coupon_id']) ) ? $_SESSION['cart']['coupon_id'] : null ;
		if( !empty($coupon_id) ) {
			$query = $wpdb->prepare('SELECT meta_key, meta_value FROM ' . $wpdb->postmeta . ' WHERE post_id = %d', $coupon_id );
			$coupons = $wpdb->get_results($query, ARRAY_A);
			$coupon = array();
			$coupon['coupon_id'] = $coupon_id;
			foreach($coupons as $coupon_info){
				$coupon[$coupon_info['meta_key']] = $coupon_info['meta_value'];
			}
			return $coupon;
		}
		return array();
	}

	/** Save an historic of coupon usage */
	function save_coupon_use( $coupon_id ) {
		$coupon_use = get_post_meta( $coupon_id, '_wpshop_coupon_usage', true);
		$user_id = get_current_user_id();

		if ( !empty($coupon_use[$user_id]) ) {
			$coupon_use[$user_id] = $coupon_use[$user_id] + 1;
		}
		else {
			$coupon_use[$user_id] = 1;
		}
		update_post_meta( $coupon_id, '_wpshop_coupon_usage', $coupon_use);
	}
}