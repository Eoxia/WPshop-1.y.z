<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Products management method file
*
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class wpshop_coupons
{
	/**
	*	Call wordpress function that declare a new term type in coupon to define the product as wordpress term (taxonomy)
	*/
	function create_coupons_type()
	{
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_COUPON, array(
			'labels' => array(
					'name' 					=> __('Coupons', 'wpshop'),
					'singular_name' 		=> __('coupon', 'wpshop'),
					'add_new' 				=> __('Add coupon', 'wpshop'),
					'add_new_item' 			=> __('Add New coupon', 'wpshop'),
					'edit' 					=> __('Edit', 'wpshop'),
					'edit_item' 			=> __('Edit coupon', 'wpshop'),
					'new_item' 				=> __('New coupon', 'wpshop'),
					'view' 					=> __('View coupon', 'wpshop'),
					'view_item' 			=> __('View coupon', 'wpshop'),
					'search_items' 			=> __('Search coupons', 'wpshop'),
					'not_found' 			=> __('No coupons found', 'wpshop'),
					'not_found_in_trash' 	=> __('No coupons found in trash', 'wpshop'),
					'parent-item-colon' 	=> ''
				),
			'description' 					=> __('This is where store coupons are stored.', 'wpshop'),
			'public' 						=> true,
			'show_ui' 						=> true,
			'capability_type' 				=> 'post',
			'publicly_queryable' 			=> false,
			'exclude_from_search' 			=> true,
			'show_in_menu' 					=> 'edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
			'hierarchical' 					=> false,
			'show_in_nav_menus' 			=> false,
			'rewrite' 						=> false,
			'query_var' 					=> true,
			'supports' 						=> array('title','editor'),
			'has_archive' 					=> false
		));

	}

	/**
	*	Create the different bow for the product management page looking for the attribute set to create the different boxes
	*/
	function add_meta_boxes() {
		// Ajout de la box info
		add_meta_box(
			'wpshop_coupon_main_info',
			__('Informations', 'wpshop'),
			array('wpshop_coupons', 'coupon_info_box'),
			 WPSHOP_NEWTYPE_IDENTIFIER_COUPON, 'normal', 'high'
		);
	}

	/* Prints the box content */
	function coupon_info_box($post, $params)
	{
		$metadata = get_post_custom();
		$coupon_code = !empty($metadata['wpshop_coupon_code'][0]) ? $metadata['wpshop_coupon_code'][0] : null;
		$coupon_discount_amount = !empty($metadata['wpshop_coupon_discount_value'][0]) ? $metadata['wpshop_coupon_discount_value'][0] : null;
		$wpshop_coupon_discount_type = !empty($metadata['wpshop_coupon_discount_type'][0]) ? $metadata['wpshop_coupon_discount_type'][0] : null;

		$string = '
<table class="wpshop_coupon_definition_table" >
	<tr class="wpshop_coupon_definition_table_code_coupon_line" >
		<td class="wpshop_coupon_definition_table_label wpshop_coupon_definition_code_coupon_input_label" ><label for="coupon_code" >'.__('Coupon code','wpshop').'</label></td>
		<td class="wpshop_coupon_definition_table_input wpshop_coupon_definition_code_coupon_input" ><input type="text" name="coupon_code" id="coupon_code" value="'.$coupon_code.'" /></td>
	</tr>
	<tr class="wpshop_coupon_definition_table_code_type_line" >
		<td class="wpshop_coupon_definition_table_label wpshop_coupon_definition_coupon_type_amount_label" ><input type="radio" name="coupon_type" class="wpshop_coupon_type" id="coupon_type_amount" value="amount" '.(($wpshop_coupon_discount_type=='amount') || empty($wpshop_coupon_discount_type) ?'checked="checked"':null).' /><label for="coupon_type_amount" >'.__('Coupon discount amount','wpshop').'</label></td>
		<td class="wpshop_coupon_definition_table_input wpshop_coupon_definition_coupon_type_input" rowspan="2" ><input type="text" name="coupon_discount_amount" value="'.$coupon_discount_amount.'" /><span class="wpshop_coupon_type_unit wpshop_coupon_type_unit_amount" > '.wpshop_tools::wpshop_get_currency().' '.__('ATI', 'wpshop').' </span><span class="wpshopHide wpshop_coupon_type_unit wpshop_coupon_type_unit_percent" > % </span></td>
	</tr>
	<tr class="wpshop_coupon_definition_table_code_type_line" >
		<td class="wpshop_coupon_definition_table_label wpshop_coupon_definition_coupon_type_percent_label" ><input type="radio" name="coupon_type" id="coupon_type_percent" class="wpshop_coupon_type" value="percent" '.($wpshop_coupon_discount_type=='percent'?'checked="checked"':null).' /><label for="coupon_type_percent" >'.__('Coupon discount amount','wpshop').'</label></td>
	</tr>
</table>';

		echo $string;
	}

	/** Set the custom colums
	 * @return array
	*/
	function coupons_edit_columns($columns)
	{
	  $columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Name', 'wpshop'),
			'coupon_code' => __('Code', 'wpshop'),
			'coupon_discount_amount' => __('Discount amount', 'wpshop'),
			//'coupon_start_date' => __('Start date', 'wpshop'),
			//'coupon_end_date' => __('Expiration date', 'wpshop')
	  );

	  return $columns;
	}

	/** Give the content by column
	 * @return array
	*/
	function coupons_custom_columns($column)
	{
		global $post;

		$metadata = get_post_custom();

		switch($column){
			case "coupon_code":
				echo $metadata['wpshop_coupon_code'][0];
			break;
			case "coupon_discount_amount":
				$currency = wpshop_tools::wpshop_get_currency();
				echo $metadata['wpshop_coupon_discount_value'][0].' '.$currency;
			break;
		}
	}

	/**
	*
	*/
	function save_coupon_custom_informations()
	{
		if( !empty($_REQUEST['post_ID']) && (get_post_type($_REQUEST['post_ID']) == WPSHOP_NEWTYPE_IDENTIFIER_COUPON) )
		{
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_code', $_REQUEST['coupon_code']);
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_discount_value', intval($_REQUEST['coupon_discount_amount']));
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_discount_type', $_REQUEST['coupon_type']);
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_individual_use', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_product_ids', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_exclude_product_ids', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_usage_limit', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_start_date', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_expiry_date', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_apply_before_tax', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_free_shipping', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_product_categories', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_exclude_product_categories', '');
			update_post_meta($_REQUEST['post_ID'], 'wpshop_coupon_minimum_amount', '');
		}
	}

	/**
	* Save the persistent cart when updated
	*/
	function get_coupon_data()
	{
		global $wpdb;
		if(!empty($_SESSION['cart']['coupon_id'])) {
			$query = $wpdb->prepare('SELECT meta_key, meta_value FROM ' . $wpdb->postmeta . ' WHERE post_id = %d', $_SESSION['cart']['coupon_id']);
			$coupons = $wpdb->get_results($query, ARRAY_A);
			$coupon = array();
			$coupon['coupon_id'] = $_SESSION['cart']['coupon_id'];
			foreach($coupons as $coupon_info){
				$coupon[$coupon_info['meta_key']] = $coupon_info['meta_value'];
			}
			return $coupon;
		}
		return array();
	}

	/**
	*
	*/
	function applyCoupon($code)
	{
		global $wpdb, $wpshop_cart;
		$coupon_infos = array();

		$query = $wpdb->prepare('
			SELECT META.post_id
			FROM '.$wpdb->prefix.'postmeta META
			LEFT JOIN '.$wpdb->prefix.'posts POSTS ON POSTS.ID = META.post_id
			WHERE
				POSTS.post_type = %s AND
				META.meta_key = "wpshop_coupon_code" AND
				META.meta_value = %s
		', WPSHOP_NEWTYPE_IDENTIFIER_COUPON, $code);
		$result = $wpdb->get_row($query);

		if(!empty($result)) {

			if(!empty($_SESSION['cart']['order_items'])) {
				$_SESSION['cart']['coupon_id'] = $result->post_id;

				$coupon_infos = array('status' => true, 'message' => '');

			} else $coupon_infos = array('status' => false, 'message' => __('Coupon not applicable','wpshop'));

		} else $coupon_infos = array('status' => false, 'message' => __('Incorrect coupon','wpshop'));

		return $coupon_infos;
	}

}