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
class wpshop_orders {

	/**
	*	Call wordpress function that declare a new term type in order to define the product as wordpress term (taxonomy)
	*/
	function create_orders_type() {
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_ORDER, array(
			'labels' => array(
				'name' 					=> __('Orders', 'wpshop'),
				'singular_name' 		=> __('Order', 'wpshop'),
				'add_new' 				=> __('Add quotation', 'wpshop'),
				'add_new_item' 			=> __('Add new quotation', 'wpshop'),
				'edit' 					=> __('Edit', 'wpshop'),
				'edit_item' 			=> __('Edit Order', 'wpshop'),
				'new_item' 				=> __('New quotation', 'wpshop'),
				'view' 					=> __('View Order', 'wpshop'),
				'view_item' 			=> __('View Order', 'wpshop'),
				'search_items' 			=> __('Search Orders', 'wpshop'),
				'not_found' 			=> __('No Orders found', 'wpshop'),
				'not_found_in_trash' 	=> __('No Orders found in trash', 'wpshop'),
				'parent' 				=> __('Parent Orders', 'wpshop')
			),
			'description' 			=> __('This is where store orders are stored.', 'wpshop'),
			'public' 				=> true,
			'show_ui' 				=> true,
			'capability_type' 		=> 'post',
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_in_menu' 			=> true,
			'hierarchical' 			=> false,
			'show_in_nav_menus' 	=> false,
			'rewrite' 				=> false,
			'query_var' 			=> true,
			'supports' 				=> array('title'),
			'has_archive' 			=> false
		));
	}

	/**
	*	Create the different bow for the product management page looking for the attribute set to create the different boxes
	*/
	function add_meta_boxes() {
		// Ajout de la box Information principale
		add_meta_box(
			'wpshop_order_main_infos',
			__('Main information', 'wpshop'),
			array('wpshop_orders', 'order_main_infos_box'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'high'
		);

		// Ajout de la box info
		add_meta_box(
			'wpshop_order_customer_information_box',
			__('Customer information', 'wpshop'),
			array('wpshop_orders', 'order_customer_information'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low'
		);

		// Ajout de la box contenu de la commande
		add_meta_box(
			'wpshop_order_content',
			__('Order content', 'wpshop'),
			array('wpshop_orders', 'order_content'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low'
		);

		// Ajout de la box "messagerie"
		add_meta_box(
			'wpshop_order_private_comments',
			__('Comments', 'wpshop'),
			array('wpshop_orders', 'order_private_comments'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low'
		);

		// Ajout de la box action
	/* 	add_meta_box(
			'wpshop_order_action',
			__('Order\'s action', 'wpshop'),
			array('wpshop_orders', 'order_actions'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'high'
		); */

		// Ajout de la box action
		add_meta_box(
			'wpshop_order_status',
			__('Payment status', 'wpshop'),
			array('wpshop_orders', 'order_status_box'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'high'
		);

		// Ajout de la box notification
		add_meta_box(
			'wpshop_order_notification',
			__('Order notifications', 'wpshop'),
			array('wpshop_orders', 'order_notification_box'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'high'
		);
	}


	/** Print the content of the order
	*
	*/
	function order_content($post){
		$order_content = '';

		$order = get_post_meta($post->ID, '_order_postmeta', true);

		$order_content .= '<div id="product_chooser_dialog" title="' . __('Choose a new product to add to the current order', 'wpshop') . '" class="wpshopHide" ><div class="loading_picture_container" id="product_chooser_picture" ><img src="' . WPSHOP_LOADING_ICON . '" alt="loading..." /></div><div id="product_chooser_container" class="wpshopHide" >&nbsp;</div></div>
<div id="order_product_container" class="order_product_container clear" >';
		if($order){/*	Read the order content if the order has product	*/
			$order_content .= '<input type="hidden" value="" name="order_products_to_delete" id="order_products_to_delete" />' . wpshop_cart::display_cart(true, $order, 'admin') . '
	<div id="order_refresh_button_container" class="wpshop_clear_block" ><button class="button-primary alignright wpshopHide" id="wpshop_admin_order_recalculate" >' . __('Refresh order informations', 'wpshop') . '</button></div>';
		}
		elseif(!isset($order['order_invoice_ref']) || ($order['order_invoice_ref'] == "")){
			$order_content .= '
	<a href="#" id="order_new_product_add_opener" >' . __('Add a product to the current order', 'wpshop') . '</a>';
		}
		$order_content .= '
		<div class="clear" ></div>
</div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery(".wpshop_order_product_listing_line.order_product_qty input").keyup(function(){
			jQuery("#wpshop_admin_order_recalculate").show();
		});
		jQuery(".wpshop_order_product_listing_line.order_product_qty input").blur(function(){
			if((jQuery(this).val() == 0) || (jQuery(this).val() == "")){
				jQuery(this).closest("tr").children("td:last").children("a").click();
			}
			jQuery("#wpshop_admin_order_recalculate").show();
		});
		jQuery(".remove").click(function(){
			if(confirm(wpshopConvertAccentTojs("' . __('Are you sure that you want to delete this product from the order?', 'wpshop') . '"))){
				var product_id_to_delete = jQuery(this).closest("tr").attr("id").replace("product_", "");
				jQuery("#order_products_to_delete").val(jQuery("#order_products_to_delete").val().replace(product_id_to_delete + ",", "") + product_id_to_delete + ",");
				jQuery(this).closest("tr").remove();
				jQuery("#wpshop_admin_order_recalculate").show();
			}
		});
		jQuery("#wpshop_admin_order_recalculate").click( function(){
			jQuery("#order_refresh_button_container").html(jQuery(".loading_picture_container").html());
			jQuery("#order_refresh_button_container img").addClass("alignright");
			update_order_product_content("' . $post->ID . '", jQuery("#order_products_to_delete").val());
		});

		jQuery("#product_chooser_dialog").dialog({
			width:800,
			height:600,
			modal:true,
			autoOpen:false,
			resizable: false,
			dialogClass: "wpshop_uidialog_box",
			close:function(){
				jQuery("#product_chooser_picture").show();
				jQuery("#product_chooser_container").hide();
			},
			buttons:{
				"assign-product-to-order" : {
					text : "' . __('Add selected product to order', 'wpshop') . '",
					click: function(){
						jQuery("#wpshop_order_selector_product_form").submit();
					},
					class: "button-primary",
				}
			}
		});

		jQuery("#order_new_product_add_opener").click( function(){
			if(jQuery("#wpshop_admin_order_recalculate").is(":visible")){
				update_order_product_content("' . $post->ID . '", jQuery("#order_products_to_delete").val());
			}
			jQuery("#product_chooser_container").load("' . WPSHOP_AJAX_FILE_URL . '",{
				"post":true,
				"elementCode":"ajax_load_product_list",
				"order_id":"' . $post->ID . '"
			});
			jQuery("#product_chooser_dialog").dialog("open");
		});

		jQuery("#free_shipping_for_order").click(function(){
			jQuery("#order_refresh_button_container").html(jQuery(".loading_picture_container").html());
			jQuery("#order_refresh_button_container img").addClass("alignright");
			if(!jQuery(this).is(":checked")){
				jQuery("#order_product_container").load(WPSHOP_AJAX_FILE_URL,{
					"post":"true",
					"elementCode":"ajax_refresh_order",
					"action":"unset_shipping_to_free",
					"elementIdentifier":"' . $post->ID . '"
				});
			}
			else{
				jQuery("#order_product_container").load(WPSHOP_AJAX_FILE_URL,{
					"post":"true",
					"elementCode":"ajax_refresh_order",
					"action":"set_shipping_to_free",
					"elementIdentifier":"' . $post->ID . '"
				});
			}
		});
	});
</script>';

		echo $order_content;
	}


	/**	Print box containing the user associated to the current order
	*
	*/
	function order_customer_information($post, $params){
		global $customer_obj; global $wpshop_account;
		$user_order_box_content = '';

		$order_postmeta = get_post_meta($post->ID, '_order_postmeta', true);
		$order_info = get_post_meta($post->ID, '_order_info', true);

		$billing = !empty($order_info['billing']) ? $order_info['billing'] : '';
		$shipping = !empty($order_info['shipping']) ? $order_info['shipping'] : '';

		$choosen_billing_address = get_option('wpshop_billing_address');
		$billing_address = !empty($billing['id']) ? $billing['id'] : $choosen_billing_address['choice'];
		$shipping_option = get_option('wpshop_shipping_address_choice');
		$shipping_address = !empty($shipping['id']) ? $shipping['id'] : $shipping_option['choice'];

		$user_id = 0;
		if ( !empty( $order_postmeta['customer_id'] ) ) {
			$user_id = $order_postmeta['customer_id'];
			$user_info = get_userdata($order_postmeta['customer_id']);
			if ( !$billing || !empty( $params['force_changing'] ) ) {
				$billing = $user_info->billing_info;
			}
			if ( !$shipping || !empty($params['force_changing'] ) ) {
				$shipping = $user_info->shipping_info;
			}
		}
		echo '<input type="hidden" name="input_wpshop_order_customer_adress_load" id="input_wpshop_order_customer_adress_load" value="' . wp_create_nonce("wpshop_order_customer_adress_load") . '" />';
		echo '<div class="wpshop_order_customer_container wpshop_order_customer_container_user_information wpshop_order_customer_container_user_information_chooser" id="wpshop_order_customer_chooser">
			<p><label>'.__('Customer','wpshop').'</label></p>
				' . $customer_obj->custom_user_list(array('name'=>'user[customer_id]', 'id'=>'wpshop_order_user_customer_id'), (!empty($order_postmeta['customer_id']) ? $order_postmeta['customer_id'] : ''), false, ( empty($order_postmeta['order_status']) || (!empty($order_postmeta['order_status']) && in_array( $order_postmeta['order_status'], array('awaiting_payment', '')) ) ? false : true ) ) . '
		</div>';
		echo '<input type="hidden" name="wpshop_customer_id" id="wpshop_customer_id" value="0" />';
		echo '<div class="wpshop_order_customer_container wpshop_order_customer_container_user_information">';
		echo '<div id="customer_address_form">' .$wpshop_account->display_form_fields($billing_address, $user_id, '', '', (!empty($billing['address']) ? $billing['address'] : '')). '</div>';
		if ($shipping_option['activate']) { echo '<p><label><input type="checkbox" name="shiptobilling" checked="checked" /> '.__('Use as shipping information','wpshop').'</label></p>'; }
		echo '</div>';

		if ($shipping_option['activate']) {
			$display = 'display:none;';
			echo '<div id="shipping_infos_bloc" class="wpshop_order_customer_container wpshop_order_customer_container_user_information" style="'.$display.'">';
			echo $wpshop_account->display_form_fields($shipping_address['choice'], $user_id, '', '', $shipping['address']);
			echo '</div>';
		}

		echo '<div class="clear"></div>';
	}


	/* Prints the box content */
	function order_main_infos_box($post) {
		$order_main_infos_box_content = '';
		$order = get_post_meta($post->ID, '_order_postmeta', true);

		if(!empty($order['order_date'])){
			$order_main_infos_box_content .=  __('Order date','wpshop').': <strong>'.mysql2date('d F Y H:i:s', $order['order_date'], true).'</strong><br />';
		}
		if(empty($order['order_date']) || (empty($order['order_key']) && empty($order['order_temporary_key']) && empty($order['order_invoice_ref']))){
			$order_main_infos_box_content .=  __('Temporary quotation reference','wpshop').': <strong>'.self::get_new_pre_order_reference(false).'</strong><br />';
		}
		else{
			if(!empty($order['order_key'])){
				$order_main_infos_box_content .=  __('Order reference','wpshop').': <strong>'.$order['order_key'].'</strong><br />';
			}
			if(!empty($order['order_temporary_key'])){
				$order_main_infos_box_content .=  __('Pre-order reference','wpshop').': <strong>'.$order['order_temporary_key'].'</strong><br />';
			}
			if(!empty($order['order_invoice_ref'])){
				$order_main_infos_box_content .=  __('Invoice number','wpshop').': <strong>'.$order['order_invoice_ref'].'</strong><br />';
			}
		}

		$order_main_infos_box_content .= '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		if(jQuery("#title").val() == ""){
			jQuery("#title").val((wpshopConvertAccentTojs("' . sprintf(__('Order - %s', 'wpshop'), mysql2date('d M Y\, H:i:s', current_time('mysql', 0), true)) . '")));
		}
	});
</script>';

		echo $order_main_infos_box_content;
	}


	/* Prints the box content */
	function order_status_box($post){
		global $order_status;
		$order_status_box_content = '';
		if(!empty($_GET['download_invoice'])) {
			$pdf = new wpshop_export_pdf();
			$pdf->invoice_export($_GET['download_invoice']);
		}
		$order_postmeta = get_post_meta($post->ID, '_order_postmeta', true);

		if(empty($order_postmeta['order_status'])){
			$order_status_box_content .= __('No information available for this order for the moment', 'wpshop');
		}
		else{
			$payment_method = '';
			if(!empty($order_postmeta['payment_method'])){
				$payment_method = '<p>'.sprintf(__('Payment method %s', 'wpshop'), __($order_postmeta['payment_method'], 'wpshop'));

				switch($order_postmeta['payment_method']){
					case 'check':
						$check_nb = get_post_meta($post->ID, '_order_check_number', true);
						if(!empty($check_nb))$payment_method .= '<br/>' . sprintf(__('Check number: %s', 'wpshop'), $check_nb);
					break;
					case 'paypal':
						$paypal_txn = get_post_meta($post->ID, '_order_paypal_txn_id', true);
						if(!empty($paypal_txn))$payment_method .= '<br/>' . sprintf(__('Transaction identifier: %s', 'wpshop'), $paypal_txn);
					break;
				}

				$payment_method .= '</p>';
			}
			else{
				$payment_method = '<p>'.__('No payment method selected for the moment', 'wpshop') . '</p>';
			}

			$order_status_box_content .= '<div class="column-order_status">' .
			sprintf('<mark class="%s" id="order_status_'.$post->ID.'">%s</mark>', sanitize_title(strtolower($order_postmeta['order_status'])), __($order_status[strtolower($order_postmeta['order_status'])], 'wpshop')) . '</div>';

			// Marquer comme envoy�
			switch($order_postmeta['order_status']){
				case 'awaiting_payment':{
					$order_status_box_content .= '<p><a class="button markAsCompleted order_'.$post->ID.'">'.__('Payment received', 'wpshop').'</a></p>' . wpshop_payment::set_payment_transaction_number($post->ID) . ' ';
					/* Button for cancel an order */
					$order_status_box_content .= '<p><a class="button markAsCanceled order_'.$post->ID.'">'.__('Cancel this order', 'wpshop').'</a></p>';
				}break;
				case 'canceled' : {

				}break;
				case 'completed':
				case 'shipped':
// 					$invoice_url = home_url().'/myaccount?action=order&oid='.$post->ID.'&download_invoice='.$post->ID;
					$invoice_url = admin_url('post.php?' . $_SERVER['QUERY_STRING']) . '&download_invoice='.$post->ID;
					$order_status_box_content .= __('Order payment date','wpshop').': '.(empty($order_postmeta['order_payment_date'])?__('Unknow','wpshop'):'<strong>'.mysql2date('d F Y H:i:s', $order_postmeta['order_payment_date'], true).'</strong>').'<br />' . $payment_method . '
							<a href="'.$invoice_url.'" target="wpshop_invoice_downloader" >'.__('Download the invoice','wpshop').'</a><br /><br class="clear" />';

					if($order_postmeta['order_status'] === 'shipped'){
						$order_status_box_content .= __('Order shipping date','wpshop').': '.(empty($order_postmeta['order_shipping_date'])?__('Unknow','wpshop'):'<strong>'.mysql2date('d F Y H:i:s', $order_postmeta['order_shipping_date'],true).'</strong>').'<br />';
						if(!empty($order_postmeta['order_trackingNumber']))$order_status_box_content .= __('Tracking number','wpshop').': '.$order_postmeta['order_trackingNumber'].'<br /><br />';

					}
					else{
						$order_status_box_content .= '<p><a class="button markAsShipped order_'.$post->ID.'">'.__('Mark as shipped', 'wpshop').'</a></p>';
					}
				break;
			}

			if((!empty($order_postmeta['order_temporary_key']) && empty($order_postmeta['order_invoice_ref']) && $order_postmeta['order_status'] != 'canceled') || (($order_postmeta['order_status'] == 'completed') && empty($order_postmeta['order_invoice_ref']))) {
				$order_status_box_content .= '<br/><input type="hidden" name="oid" value="'.$post->ID.'" /><br/><a class="button alignright" href="#" id="bill_order">'.__('Charge this order', 'wpshop').'</a><br class="clear" />';
			}
		}
		$order_status_box_content .= '<input type="hidden" name="input_wpshop_change_order_state" id="input_wpshop_change_order_state" value="' . wp_create_nonce("wpshop_change_order_state") . '" />';
		$order_status_box_content .= '<input type="hidden" name="input_wpshop_dialog_inform_shipping_number" id="input_wpshop_dialog_inform_shipping_number" value="' . wp_create_nonce("wpshop_dialog_inform_shipping_number") . '" />';
		$order_status_box_content .= '<input type="hidden" name="input_wpshop_validate_payment_method" id="input_wpshop_validate_payment_method" value="' . wp_create_nonce("wpshop_validate_payment_method") . '" />';

		echo $order_status_box_content;
	}


	/** Generate the billing reference regarding the order $order_id
	 * @return void
	*/
	function order_generate_billing_number($order_id, $force_invoicing = false){
		global $wpdb;

		// Get the order from the db
		$order = get_post_meta($order_id, '_order_postmeta', true);

		// If the payment is completed
		if(($order['order_status']=='completed') || $force_invoicing) {

			// If the reference hasn't been generated yet
			if(empty($order['order_invoice_ref'])) {

				$number_figures = get_option('wpshop_billing_number_figures', false);
				/* If the number doesn't exist, we create a default one */
				if(!$number_figures) {
					$number_figures = 5;
					update_option('wpshop_billing_number_figures', $number_figures);
				}

				$billing_current_number = get_option('wpshop_billing_current_number', false);
				/* If the counter doesn't exist, we initiate it */
				if(!$billing_current_number) { $billing_current_number = 1; }
				else { $billing_current_number++; }
				update_option('wpshop_billing_current_number', $billing_current_number);

				$invoice_ref = WPSHOP_BILLING_REFERENCE_PREFIX.((string)sprintf('%0'.$number_figures.'d', $billing_current_number));
				$order['order_invoice_ref'] = $invoice_ref;
				update_post_meta($order_id, '_order_postmeta', $order);
			}
		}
	}


	function order_actions($post){
		$output = '';
		$order = get_post_meta($post->ID, '_order_postmeta', true);

		if ( !empty( $order['order_date'] ) && ( !empty( $order['order_key'] ) || !empty( $order['order_temporary_key'] ) || !empty( $order['order_invoice_ref'] ) ) ) {
			/*	Display possibility to duplicate an order	*/
			$output .=  '<a class="button" href="#" id="duplicate_the_order">'.__('Duplicate the order', 'wpshop').'</a><br />';
		}

		$output .=  '<input type="submit" value="' . __('Save order', 'wpshop') . '" name="save" class="button-primary" />';

		echo $output . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		// DUPLICATE AN ORDER
		jQuery("a#duplicate_the_order").click(function(){
			var _this = jQuery(this);
			jQuery(this).attr("class", "button");
			// Display loading...
			jQuery(this).addClass("loading");

			jQuery.getJSON(WPSHOP_AJAX_FILE_URL, {post:"true", elementCode:"duplicate_order", pid:jQuery("#post_ID").val()},
				function(data){
					jQuery(this).removeClass("loading");
					if(data[0]){
						jQuery(this).addClass("success");
						jQuery(this).after("<a href=\'' . admin_url('post.php?post=" + data[1] + "&action=edit') . '\' >' . __('View created order', 'wpshop') . '</a>");
					}
					else{
						jQuery(this).addClass("error");
					}
				}
			);

			return false;
		});
	});
</script>';
	}

	/**
	* Ajax save the order data
	*/
	function save_order_custom_informations(){
		global $wpshop_account; global $wpdb;

		if (!empty($_REQUEST['post_ID']) && (get_post_type($_REQUEST['post_ID']) == WPSHOP_NEWTYPE_IDENTIFIER_ORDER) && empty($_POST['edit_other_thing'])){
			/*	Get order current content	*/
			$order_meta = get_post_meta($_REQUEST['post_ID'], '_order_postmeta', true);
			// If the customer notification is checked
			if(!empty($_REQUEST['notif_the_customer']) && $_REQUEST['notif_the_customer']=='on') {
				/*	Get order current content	*/
				$user = get_post_meta($_REQUEST['post_ID'], '_order_info', true);
				$email = $user['billing']['email'];
				$first_name = $user['billing']['first_name'];
				$last_name = $user['billing']['last_name'];

				$object = array('object_type'=>'order','object_id'=>$_REQUEST['post_ID']);
				/* Envoie du message de confirmation de commande au client	*/
				wpshop_tools::wpshop_prepared_email(
					$email,
					'WPSHOP_ORDER_UPDATE_MESSAGE',
					array('customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_key' => $order_meta['order_key']),
					$object
				);
			}
			// SENDSMS NOTIFICATION IS CHECKED
			if(!empty($_REQUEST['notif_the_customer_sendsms']) && $_REQUEST['notif_the_customer_sendsms']=='on') {
				// Get order current content
				$user = get_post_meta($_REQUEST['post_ID'], '_order_info', true);
				$email = $user['billing']['email'];
				$first_name = $user['billing']['first_name'];
				$last_name = $user['billing']['last_name'];
				$phone = !empty($user['billing']['phone']) ? $user['billing']['phone'] : $user['shipping']['phone'];

				$message = wpshop_tools::customMessage(
					WPSHOP_ORDER_UPDATE_MESSAGE,
					array('customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_key' => $order_meta['order_key'])
				);
				$userList = array();
				$userList[]['from'][] = 'wpshop_list';
				$userList[]['tel'] = $phone;

				// Send the message
				sendsms_message::sendSMS($message, $userList);
			}

			/* Save the billing and Shipping address */
			$update_order_billing_and_shipping_infos = false;
			$order_info = array();

			if ( !empty( $_REQUEST['attribute']) ) {
				if (isset($_REQUEST['shiptobilling']) && $_REQUEST['shiptobilling'] == "on") {
					$wpshop_account->same_billing_and_shipping_address($_REQUEST['billing_address'], $_REQUEST['shipping_address']);
				}
				// If the customer doesn't exist in the database, we create him
				if ( isset($_REQUEST['wpshop_customer_id']) && $_REQUEST['wpshop_customer_id'] == 0 ) {
					$default_billing_address_set_id = get_option('wpshop_billing_address');
					$username = $_REQUEST['attribute'][$default_billing_address_set_id['choice']]['varchar']['address_last_name']."_".$_REQUEST['attribute'][$default_billing_address_set_id['choice']]['varchar']['address_first_name']."_".$_REQUEST['attribute'][$default_billing_address_set_id['choice']]['varchar']['postcode'];
					$password = wp_generate_password( $length=12, $include_standard_special_chars=false );
					$email = $_REQUEST['attribute'][$default_billing_address_set_id['choice']]['varchar']['address_user_email'];

					if ( !username_exists( $user_name ) && email_exists($email) == false ) {
						$user_id = wp_create_user( $username, $password, $email );
					}
					else {
						$user = get_user_by('email', $email);
						$user_id = $user->ID;
					}
					$_REQUEST['user']['customer_id'] = $user_id;
				}
				else {
					$user_id = $_REQUEST['wpshop_customer_id'];
				}

				$billing_set_infos = get_option('wpshop_billing_address');
				$shipping_set_infos = get_option('wpshop_shipping_address_choice');


				foreach ( $_REQUEST['attribute'] as $address_attribute_set_id => $address_detail_per_type ) {
					$stored_address = array();
					foreach ( $address_detail_per_type as $address_detail ) {
						$stored_address = array_merge($stored_address, $address_detail);
					}
					if ( $address_attribute_set_id == $billing_set_infos['choice'] ) {
						$adress_type = 'billing';
					}
					else if ( $address_attribute_set_id == $shipping_set_infos['choice'] ) {
						$adress_type = 'shipping';
					}
					if ( $adress_type == 'billing' ) {
						$order_info[$adress_type]['id'] = $billing_set_infos['choice'];
					}
					else {
						$order_info[$adress_type]['id'] = $shipping_set_infos['choice'];
					}
					$order_info[$adress_type]['address'] = $stored_address;
				$update_order_billing_and_shipping_infos = true;

					$billing_info = get_user_meta($user_id, $adress_type . '_info', true);
					if ( empty( $billing_info ) ) {
						update_user_meta($user_id, $adress_type . '_info', $stored_address);
				}
			}
			}
			if($update_order_billing_and_shipping_infos){
				update_post_meta($_REQUEST['post_ID'], '_order_info', $order_info);

				if ( !empty($_POST['billing_address']) ) {
					$wpshop_account->treat_forms_infos( $_REQUEST['billing_address'] );
			}
				if( !empty($_POST['shipping_address']) ) {
					$wpshop_account->treat_forms_infos( $_REQUEST['shipping_address'] );
				}
			}

			if(empty($order_meta['customer_id']) ){
				$order_meta['customer_id'] = $user_id;
			}

			/*	Complete information about the order	*/
			if ( empty($order_meta['order_key']) ) {
				$order_meta['order_key'] = !empty($order_meta['order_key']) ? $order_meta['order_key'] : (!empty($order_meta['order_status']) && ($order_meta['order_status']!='awaiting_payment') ? wpshop_orders::get_new_order_reference() : '');
				$order_meta['order_temporary_key'] = (isset($order_meta['order_temporary_key']) && ($order_meta['order_temporary_key'] != '')) ? $order_meta['order_temporary_key'] : wpshop_orders::get_new_pre_order_reference();
			}
			$order_meta['order_status'] = (isset($order_meta['order_status']) && ($order_meta['order_status'] != '')) ? $order_meta['order_status'] : 'awaiting_payment';
			$order_meta['order_date'] = (isset($order_meta['order_date']) && ($order_meta['order_date'] != '')) ? $order_meta['order_date'] : current_time('mysql', 0);
			$order_meta['order_currency'] = wpshop_tools::wpshop_get_currency(true);/*	Update order content	*/

			/*	Set order information into post meta	*/
			update_post_meta($_REQUEST['post_ID'], '_order_postmeta', $order_meta);
		/* Update the others wpshop order post_meta */
		update_post_meta($_REQUEST['post_ID'], '_wpshop_order_customer_id', $order_meta['customer_id']);
		update_post_meta($_REQUEST['post_ID'], '_wpshop_order_shipping_date', $order_meta['order_shipping_date']);
		update_post_meta($_REQUEST['post_ID'], '_wpshop_order_status', $order_meta['order_status']);
		update_post_meta($_REQUEST['post_ID'], '_wpshop_order_payment_date', $order_meta['order_payment_date']);
		update_post_meta($_REQUEST['post_ID'], '_wpshop_payment_method', $order_meta['payment_method']);
		}
	}


	/** Renvoi une nouvelle r�f�rence unique pour une commande
	* @return int
	*/
	function get_new_order_reference(){
		$number_figures = get_option('wpshop_order_number_figures', false);
		/* If the number doesn't exist, we create a default one */
		if(!$number_figures){
			$number_figures = 5;
			update_option('wpshop_order_number_figures', $number_figures);
		}

		$order_current_number = get_option('wpshop_order_current_number', false);
		/* If the counter doesn't exist, we initiate it */
		if(!$order_current_number) { $order_current_number = 1; }
		else { $order_current_number++; }
		update_option('wpshop_order_current_number', $order_current_number);

		$order_ref = (string)sprintf('%0'.$number_figures.'d', $order_current_number);
		return WPSHOP_ORDER_REFERENCE_PREFIX.$order_ref;
	}

	/** Renvoi une nouvelle r�f�rence unique pour un devis
	* @return int
	*/
	function get_new_pre_order_reference($save = true){
		$number_figures = get_option('wpshop_order_number_figures', false);
		/* If the number doesn't exist, we create a default one */
		if(!$number_figures){
			$number_figures = 5;
			update_option('wpshop_order_number_figures', $number_figures);
		}

		$order_current_number = get_option('wpshop_preorder_current_number', false);
		/* If the counter doesn't exist, we initiate it */
		if(!$order_current_number) { $order_current_number = 1; }
		else { $order_current_number++; }
		if($save){
			update_option('wpshop_preorder_current_number', $order_current_number);
		}

		$order_ref = (string)sprintf('%0'.$number_figures.'d', $order_current_number);
		return WPSHOP_PREORDER_REFERENCE_PREFIX.$order_ref;
	}


	/**
	*	Build an array with the different items to add to an order
	*
	*	@param array $products The item list to add to the order
	*
	*	@return array $item_list The item to add to order
	*/
	function add_product_to_order($product){

		/*	Read selected product list for adding to order	*/
		$pu_ht = !empty($product[WPSHOP_PRODUCT_PRICE_HT]) ? $product[WPSHOP_PRODUCT_PRICE_HT] : null;
		$pu_ttc = !empty($product[WPSHOP_PRODUCT_PRICE_TTC]) ? $product[WPSHOP_PRODUCT_PRICE_TTC] : null;
		$pu_tva = !empty($product[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT]) ? $product[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] : null;
		$total_ht = $pu_ht*$product['product_qty'];
		$tva_total_amount = $pu_tva*$product['product_qty'];
		$total_ttc = $pu_ttc*$product['product_qty'];
		$tva = !empty($product[WPSHOP_PRODUCT_PRICE_TAX]) ? $product[WPSHOP_PRODUCT_PRICE_TAX] : null;

		$item_discount_type = $item_discount_value = $item_discount_amount = 0;
		/*
		 * Check if there is a specila price to apply
		 */
		if ( !empty( $product[WPSHOP_PRODUCT_SPECIAL_PRICE] ) ) {
			$item_discount_type = 'amount';
			$item_discount_value = 'original_price';
			$item_discount_amount = $pu_ttc;
			$pu_ttc = $product[WPSHOP_PRODUCT_SPECIAL_PRICE];
			$total_ttc = $pu_ttc*$product['product_qty'];
		}

		$item = array(
			'item_id' => $product['product_id'],
			'item_ref' => !empty($product['product_reference']) ? $product['product_reference'] : null,
			'item_name' => !empty($product['product_name']) ? $product['product_name'] : 'wpshop_product_' . $product['product_id'],
			'item_qty' => $product['product_qty'],
			'item_pu_ht' => number_format($pu_ht, 5, '.', ''),
			'item_pu_ttc' => number_format($pu_ttc, 5, '.', ''),
			'item_ecotaxe_ht' => number_format(0, 5, '.', ''),
			'item_ecotaxe_tva' => 19.6,
			'item_ecotaxe_ttc' => number_format(0, 5, '.', ''),
			'item_discount_type' => $item_discount_type,
			'item_discount_value' => $item_discount_value,
			'item_discount_amount' => number_format($item_discount_amount, 5, '.', ''),
			'item_tva_rate' => $tva,
			'item_tva_amount' => number_format($pu_tva, 5, '.', ''),
			'item_total_ht' => number_format($total_ht, 5, '.', ''),
			'item_tva_total_amount' => number_format($tva_total_amount, 5, '.', ''),
			'item_total_ttc' => number_format($total_ttc, 5, '.', ''),
			'item_meta' => !empty($product['item_meta']) ? $product['item_meta'] : array()
		);

		$array_not_to_do = array(WPSHOP_PRODUCT_PRICE_HT,WPSHOP_PRODUCT_PRICE_TTC,WPSHOP_PRODUCT_PRICE_TAX_AMOUNT,'product_qty',WPSHOP_PRODUCT_PRICE_TAX,'product_id','product_reference','product_name','variations');

		if(!empty($product['item_meta'])) {
			foreach($product['item_meta'] as $key=>$value) {
				if( !isset($item['item_'.$key]) && !in_array($key, $array_not_to_do) && !empty($product[$key]) ) {
					$item['item_'.$key] = $product[$key];
				}
			}
		}

		return $item;
	}

	/**
	* Give to admin user possibility to duplicate an order
	*/
	function duplicate_order($pid) {
		global $wpdb;

		// Get the product post info
		$query_posts = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'posts WHERE ID='.$pid, '');
		$data_posts = $wpdb->get_row($query_posts,ARRAY_A);
		$data_posts['ID'] = NULL;
		$data_posts['post_date'] = current_time('mysql', 0);
		$data_posts['post_date_gmt'] = current_time('mysql', 0);
		$data_posts['post_modified'] = current_time('mysql', 0);
		$data_posts['post_modified_gmt'] = current_time('mysql', 0);
		$data_posts['guid'] = NULL;

		// Get others features like thumbnails
		$query_posts_more = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_parent='.$pid.' AND post_type="attachment"', '');
		$data_posts_more = $wpdb->get_results($query_posts_more,ARRAY_A);

		// Postmeta
		$order_content_meta = get_post_meta($pid,'_order_postmeta', true);
		$order_content_meta['order_status'] = NULL;
		$order_content_meta['order_key'] = NULL;
		$order_content_meta['order_payment_date'] = NULL;
		$order_content_meta['order_shipping_date'] = NULL;
		$order_content_meta['payment_method'] = NULL;
		$order_content_meta['order_invoice_ref'] = NULL;
		$order_content_meta['order_temporary_key'] = NULL;
		$order_content_meta['order_old_shipping_cost'] = '0';
		$order_content_meta['shipping_is_free'] = false;
		$order_user_meta = get_post_meta($pid,'_order_info', true);

		$wpdb->insert($wpdb->prefix.'posts', $data_posts);
		$new_pid = $wpdb->insert_id;

		// Update the post_name to avoid duplicated product name
		$post_name = $data_posts['post_name'].$new_pid;
		$wpdb->update($wpdb->posts, array('post_name'=>$post_name), array('ID'=>$new_pid));

		// Replace the old product id by the new one
		foreach($data_posts_more as $k=>$v){
			$data_posts_more[$k]['ID'] = NULL;
			$data_posts_more[$k]['post_parent'] = $new_pid;
			$data_posts_more[$k]['post_date'] = current_time('mysql', 0);
			$data_posts_more[$k]['post_date_gmt'] = current_time('mysql', 0);
			$data_posts_more[$k]['post_modified'] = current_time('mysql', 0);
			$data_posts_more[$k]['post_modified_gmt'] = current_time('mysql', 0);
			$wpdb->insert($wpdb->prefix.'posts', $data_posts_more[$k]);
		}

		update_post_meta($new_pid, '_order_postmeta', $order_content_meta);
		update_post_meta($new_pid, '_order_info', $order_user_meta);


		update_post_meta($new_pid, '_wpshop_order_customer_id', $order_content_meta['customer_id']);
		update_post_meta($new_pid, '_wpshop_order_shipping_date', $order_content_meta['order_shipping_date']);
		update_post_meta($new_pid, '_wpshop_order_status', $order_content_meta['order_status']);
		update_post_meta($new_pid, '_wpshop_order_payment_date', $order_content_meta['order_payment_date']);
		update_post_meta($new_pid, '_wpshop_payment_method', $order_content_meta['payment_method']);


		return $new_pid;
	}

	/**
	*	Add information about user to the selected order
	*
	*	@param int $user_id The user identifier to get information for and to add to order meta informations
	*	@param int $order_id The order identifier to update meta information for
	*
	*	@return void
	*/
	function set_order_customer_addresses($user_id, $order_id, $shipping_address_id='', $billing_address_id=''){
		// On r�cup�re les infos de facturation et de livraison
		$shipping_info['id'] = get_post_meta($shipping_address_id, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, true);
		$shipping_info['address'] = get_post_meta($shipping_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);

		$billing_info['id'] = get_post_meta($billing_address_id, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, true);
		$billing_info['address'] = get_post_meta($billing_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);

		$order_info = array('billing' => $billing_info, 'shipping' => $shipping_info);
		// On enregistre l'adresse de facturation et de livraison
		update_post_meta($order_id, '_order_info', $order_info);
	}


	/** Set the custom colums
	 * @return array
	*/
	function orders_edit_columns($columns){
	  $columns = array(
		'cb' => '<input type="checkbox" />',
		'order_status' => __('Status', 'wpshop'),
		'order_type' => __('Order type', 'wpshop'),
		'order_billing' => __('Billing', 'wpshop'),
		'order_shipping' => __('Shipping', 'wpshop'),
		'order_total' => __('Order total', 'wpshop'),
		'date' => __('Date', 'wpshop'),
		'order_actions' => __('Actions', 'wpshop')
	  );

	  return $columns;
	}

	/** Give the content by column
	 * @return array
	*/
	function orders_custom_columns($column, $post_id) {
		if ( get_post_type( $post_id ) == WPSHOP_NEWTYPE_IDENTIFIER_ORDER ) {
			global $civility, $order_status;

			$metadata = get_post_custom();

			$order_postmeta = isset($metadata['_order_postmeta'][0])?unserialize($metadata['_order_postmeta'][0]):'';
			$order_info = isset($metadata['_order_info'][0])?unserialize($metadata['_order_info'][0]):'';

			$addresses = get_post_meta($post_id,'_order_info', true);
			if ( !empty($addresses) ) {
				$billing = $addresses['billing']['address'];
				$shipping =  $addresses['shipping']['address'];

				switch($column){
					case "order_status":
						echo !empty($order_postmeta['order_status']) ? sprintf('<mark class="%s" id="order_status_'.$post_id.'">%s</mark>', sanitize_title(strtolower($order_postmeta['order_status'])), __($order_status[strtolower($order_postmeta['order_status'])], 'wpshop')) : __('Unknown Status', 'wpshop');
					break;

					case "order_billing":
						if (!empty($billing)) {
							echo (!empty($billing['civility']) ? __(wpshop_attributes::get_attribute_type_select_option_info($billing['civility'], 'label', 'custom'), 'wpshop') : null).' <strong>'.(!empty($billing['address_first_name']) ? $billing['address_first_name'] : null).' '.(!empty($billing['address_last_name']) ? $billing['address_last_name'] : null).'</strong>';
							echo empty($billing['company'])?'<br />':', <i>'.$billing['company'].'</i><br />';
							echo (!empty($billing['address']) ? $billing['address'] : null).'<br />';
							echo (!empty($billing['postcode']) ? $billing['postcode'] : null).' '.(!empty($billing['city']) ? $billing['city'] : null).', '.(!empty($billing['country']) ? $billing['country'] : null);
						}
						else {
							echo __('No information available for user billing', 'wpshop');
						}
					break;

					case "order_type":
							echo '<a href="'.admin_url('post.php?post='.$post_id.'&action=edit').'">'.(!empty($order_postmeta['order_temporary_key']) ? __('Quotation','wpshop') :  __('Basic order','wpshop')).'</a>';
						break;

					case "order_shipping":
						if(!empty($shipping)){
								echo '<strong>'.(!empty($shipping['address_first_name']) ? $shipping['address_first_name'] : null).' '.(!empty($shipping['address_last_name']) ? $shipping['address_last_name'] : null).'</strong>';
							echo empty($shipping['company'])?'<br />':', <i>'.$shipping['company'].'</i><br />';
							echo (!empty($shipping['address']) ? $shipping['address'] : null).'<br />';
							echo (!empty($shipping['postcode']) ? $shipping['postcode'] : null).' '.(!empty($shipping['city']) ? $shipping['city'] : null).', '.(!empty($shipping['country']) ? $shipping['country'] : null);
						}
						else{
							echo __('No information available for user shipping', 'wpshop');
						}
					break;

					case "order_total":
						$currency = !empty($order_postmeta['order_currency']) ?$order_postmeta['order_currency'] : get_option('wpshop_shop_default_currency');
						echo !empty($order_postmeta['order_grand_total']) ? number_format($order_postmeta['order_grand_total'],2,'.', ' ').' '.  wpshop_tools::wpshop_get_sigle($currency) : 'NaN';
					break;

					case "order_actions":
						$buttons = '<p>';
						// Marquer comme envoy�
						if (!empty($order_postmeta['order_status']) && ($order_postmeta['order_status'] == 'completed')) {
								$buttons .= '<a class="button markAsShipped order_'.$post_id.'">'.__('Mark as shipped', 'wpshop').'</a> ';
						}
						else if (!empty($order_postmeta['order_status']) && ($order_postmeta['order_status'] == 'awaiting_payment' )) {
								$buttons .= '<a class="button markAsCompleted order_'.$post_id.' alignleft" >'.__('Payment received', 'wpshop').'</a>' . wpshop_payment::set_payment_transaction_number($post_id) . ' ';
						}

						// Voir la commande
							$buttons .= '<a class="button alignright" href="'.admin_url('post.php?post='.$post_id.'&action=edit').'">'.__('View', 'wpshop').'</a>';
						$buttons .= '</p>';
						$buttons .= '<input type="hidden" name="input_wpshop_change_order_state" id="input_wpshop_change_order_state" value="' . wp_create_nonce("wpshop_change_order_state") . '" />';
						$buttons .= '<input type="hidden" name="input_wpshop_dialog_inform_shipping_number" id="input_wpshop_dialog_inform_shipping_number" value="' . wp_create_nonce("wpshop_dialog_inform_shipping_number") . '" />';
						$buttons .= '<input type="hidden" name="input_wpshop_validate_payment_method" id="input_wpshop_validate_payment_method" value="' . wp_create_nonce("wpshop_validate_payment_method") . '" />';

						echo $buttons;
					break;
				}
			}
		}
	}


	/** Prints the box content */
	function add_private_comment($oid, $comment, $send_email, $send_sms) {

		$order_private_comments = get_post_meta($oid, '_order_private_comments', true);
		$order_private_comments = !empty($order_private_comments) ? $order_private_comments : array();

		/*	Get order current content	*/
		$order_meta = get_post_meta($oid, '_order_postmeta', true);

		// Send email is checked
		if($send_email) {
			// Get order current content
			$user = get_post_meta($oid, '_order_info', true);
			$email = isset($user['billing']['email'])?$user['billing']['email']:'';
			$first_name = isset($user['billing']['first_name'])?$user['billing']['first_name']:'';
			$last_name = isset($user['billing']['last_name'])?$user['billing']['last_name']:'';

			$object = array('object_type'=>'order','object_id'=>$oid);
			/* Envoie du message de confirmation de commande au client	*/
			wpshop_tools::wpshop_prepared_email(
				$email,
				'WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE',
				array('customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_key' => $order_meta['order_key'], 'message' => $comment),
				$object
			);
		}
		// Send sms is checked
		/*if($send_sms) {
			// Get order current content
			$user = get_post_meta($oid, '_order_info', true);
			$email = $user['billing']['email'];
			$first_name = $user['billing']['first_name'];
			$last_name = $user['billing']['last_name'];
			$phone = !empty($user['billing']['phone']) ? $user['billing']['phone'] : $user['shipping']['phone'];

			$message = wpshop_tools::customMessage(
				WPSHOP_ORDER_UPDATE_MESSAGE,
				array('customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_key' => $order_meta['order_key'])
			);
			$userList = array();
			$userList[]['from'][] = 'wpshop_list';
			$userList[]['tel'] = $phone;

			// Send the message
			sendsms_message::sendSMS($message, $userList);
		}*/

		$order_private_comments[] = array(
			'comment_date' => current_time('mysql',0),
			'send_email' => $send_email,
			'send_sms' => $send_sms,
			'comment' => $comment
		);

		if(is_array($order_private_comments)) {
			update_post_meta($oid, '_order_private_comments', $order_private_comments);
			return true;
		}
		else return false;
	}

	/** Orders comments */
	function order_private_comments($post){
		$content = '<textarea name="order_private_comment" style="width:100%"></textarea><br />';
		$content .= '<label><input type="checkbox" name="send_email" /> '.__('Send an email to customer','wpshop').'</label><br />';
		//$content .= '<label><input type="checkbox" name="send_sms" /> '.__('Send a SMS to customer','wpshop').'</label><br />';
		//$content .= '<label><input type="checkbox" name="allow_visibility" /> '.__('Visible from the customer account','wpshop').'</label><br />';
		$content .= '<br /><a class="button addPrivateComment order_'.$post->ID.'">'.__('Add the comment','wpshop').'</a>';

		$order_private_comments = get_post_meta($post->ID, '_order_private_comments', true);

		if ( !empty( $order_private_comments ) ) {
			$order_private_comments = array_reverse($order_private_comments);
			$content .= '<br /><br /><div id="comments_container">';
			foreach ( $order_private_comments as $o ) {
				$content .= '<hr /><b>'.__('Date','wpshop').':</b> '.mysql2date('d F Y, H:i:s',$o['comment_date'], true).'<br /><b>'.__('Message','wpshop').':</b> '.nl2br($o['comment']);
			}
			$content .= '</div>';
		}

		echo $content;
	}

	/** Prints the box content */
	function order_notification_box($post){
		$notifs = self::get_notification_by_object(array('object_type'=>'order','object_id'=>$post->ID));

		echo '<label><input type="checkbox" name="notif_the_customer" /> '.__('Send a notification to the customer', 'wpshop').'</label>';
		/*if(wpshop_tools::is_sendsms_actived()) {
			echo '<br /><label><input type="checkbox" name="notif_the_customer_sendsms" /> '.__('Send a SMS to the customer', 'wpshop').'</label>';
		}*/

		if(!empty($notifs)) echo '<hr />';
		foreach($notifs as $n) {
			echo '<span class="right"><a href="admin.php?page='.WPSHOP_URL_SLUG_MESSAGES.'&mid='.$n['mess_id'].'">Voir</a></span>Le '.mysql2date('d F Y\, H:i', $n['mess_creation_date'], true).'<br />';
		}
	}

	/**
	* Return an array list of all the notifications regarding the object (ex of object : order, id=458)
	*/
	function get_notification_by_object($object) {
		global $wpdb;

		$data = array();
		if(!empty($object['object_type']) && !empty($object['object_id'])) {
			$prepare = $wpdb->prepare('SELECT * FROM '.WPSHOP_DBT_MESSAGES.' WHERE mess_object_type=%s AND mess_object_id=%d', $object['object_type'], $object['object_id']);
			$data = $wpdb->get_results($prepare, ARRAY_A);
		}

		return $data;
	}

	/**
	 * Display orders list for a given customer
	 *
	 * @param object $post The current element being edited (i.e a customer)
	 * @param array $metaboxArgs Extras arguments
	 */
	function display_orders_for_customer($post, $metaboxArgs) {
		global $wpdb;
		global $order_status;

		$query = $wpdb->prepare(
				"SELECT *
				FROM ".$wpdb->posts." AS posts
					INNER JOIN ".$wpdb->postmeta." AS metas ON (metas.post_id = posts.ID)
				WHERE post_type = %s
					AND post_status = %s
					AND meta_key = %s
					AND meta_value = %s
				ORDER BY post_date DESC",
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'publish', '_wpshop_order_customer_id', $post->post_author);
		$orders_id = $wpdb->get_results($query);
		/* Use the wpshop_customer_entities_custom_List_table to display the table */
		$wpshop_list_table = new wpshop_customer_entities_custom_List_table();
		$attribute_set_list = array();
		$i=0;
		foreach ($orders_id as $o_id) {

			$query  = $wpdb->prepare('SELECT meta_value, post_id FROM '.$wpdb->postmeta.' WHERE post_id = '.$o_id->ID.'', '');
			$infos = $wpdb->get_results($query);
			if (!empty($infos)) {
				$o = get_post_meta($o_id->ID, '_order_postmeta', true);
				$currency = wpshop_tools::wpshop_get_sigle($o['order_currency']);

				$attribute_set_list[$i]['date'] = $o['order_date'];
				if( empty($o['order_key']) ) {
					$attribute_set_list[$i]['order_number'] = $o['order_temporary_key'];
				}
				else {
					$attribute_set_list[$i]['order_number'] = $o['order_key'];
				}

				$attribute_set_list[$i]['total'] = number_format($o['order_grand_total'], 2, '.', '').' '.$currency;
				$attribute_set_list[$i]['status'] = '<span class="wpshop_orders_status-'.$o['order_status'].'">'.__($order_status[$o['order_status']], 'wpshop').'</span>';
				$attribute_set_list[$i]['action'] = $o_id->ID;
				$i++;
			}

		}

		$wpshop_list_table->prepare_items($attribute_set_list);
		$wpshop_list_table->views();
		$wpshop_list_table->display();
	}

}
