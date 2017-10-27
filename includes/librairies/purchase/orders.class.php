<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Products management method file
 *
 * This file contains the different methods for products management
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage librairies
 */

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * This file contains the different methods for products management
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_orders {

	/**
	 * Create a new custom post type in wordpress for current element
	 */
	public static function create_orders_type( ) {
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
			'show_in_admin_bar'   	=> false,
			'rewrite' 				=> false,
			'query_var' 			=> true,
			'supports' 				=> array(''),
			'has_archive' 			=> false,
			'menu_icon'				=> 'dashicons-cart'
		));
	}

	/**
	 *	Call the different boxes in edition page
	 */
	public static function add_meta_boxes( ) {
		global $post;

		/**	Add action button	*/
		add_meta_box(
			'wpshop_order_actions',
			'<span class="dashicons dashicons-info"></span> '.__('Actions on order', 'wpshop'),
			array('wpshop_orders', 'order_actions'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'high'
		);

		if ( !in_array( $post->post_status, array( 'auto-draft' ) ) ) {
			add_meta_box('wpshop_credit_actions', __('Credit on order', 'wpshop'), array('wps_credit', 'wps_credit_meta_box'), WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'low');
		}



		/**	Box	containing listing of customer notification */
// 		$notifs = self::get_notification_by_object( array('object_type' => 'order', 'object_id' => $post->ID) );
// 		if ( !empty($notifs) ) {
// 			add_meta_box(
// 				'wpshop_order_customer_notification',
// 				__('Customer Notification', 'wpshop'),
// 				array('wpshop_orders', 'wpshop_order_customer_notification'),
// 					WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'low'
// 			);
// 		}
	}



	/**
	 * Define the box for actions on order
	 *
	 * @param object $order The current order being edited
	 */
	public static function order_actions( $order ) {
		$output = '';

		$order_status = unserialize(WPSHOP_ORDER_STATUS);
		$order_postmeta = get_post_meta($order->ID, '_order_postmeta', true);

		$tpl_component = array();

		$delete_button = wpshop_display::display_template_element('wpshop_admin_order_action_del_button', array('ADMIN_ORDER_DELETE_LINK' => esc_url( get_delete_post_link($order->ID) ) , 'ADMIN_ORDER_DELETE_TEXT' => (!EMPTY_TRASH_DAYS ? __('Delete Permanently', 'wpshop') :  __('Move to Trash', 'wpshop'))), array(), 'admin');
		$tpl_component['ADMIN_ORDER_DELETE_ORDER'] = current_user_can( "delete_post", $order->ID ) ? $delete_button : '';

		/**	Add an action list	*/
		$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] = '';

		/**	Display main information about the order	*/
		$order_main_info = '';
		if(!empty($order_postmeta['order_date'])){
			$order_main_info .=  '<div class="wps-product-section"><span class="dashicons dashicons-calendar-alt"></span> <strong>'.__('Order date','wpshop').' : </strong><br/>'.mysql2date('d F Y H:i:s', $order_postmeta['order_date'], true).'</div>';
		}
		$order_main_info .= '<div class="wps-product-section">';
		if(empty($order_postmeta['order_date']) || (empty($order_postmeta['order_key']) && empty($order_postmeta['order_temporary_key']) && empty($order_postmeta['order_invoice_ref']))){
			$order_main_info .=  '<span class="dashicons dashicons-arrow-right"></span> <strong>'.__('Temporary quotation reference','wpshop').': </strong>'.self::get_new_pre_order_reference(false).'<br/>';
		}
		else{
			if(!empty($order_postmeta['order_key'])){
				$order_main_info .=  '<span class="dashicons dashicons-arrow-right"></span> <strong>'.__('Order reference','wpshop').' : </strong>'.$order_postmeta['order_key'].'<br/>';
			}
			if(!empty($order_postmeta['order_temporary_key'])){
				$order_main_info .=  '<span class="dashicons dashicons-arrow-right"></span> <strong>'.__('Pre-order reference','wpshop').': </strong>'.$order_postmeta['order_temporary_key'].'<br/>';
				$post_ID = !empty( $_GET['post'] ) ? (int) $_GET['post'] : 0;
				$order_main_info .= '<a href="' .admin_url( 'admin-post.php?action=wps_invoice&order_id=' . $post_ID . '&mode=pdf' ) . '">' .__('Download the quotation', 'wpshop'). '</a><br />';
			}
			if(!empty($order_postmeta['order_invoice_ref'])){
				$sub_tpl_component = array();
				$order_invoice_download = '<a href="' . admin_url( 'admin-post.php?action=wps_invoice&order_id=' . $order->ID . '&invoice_ref=' . $order_postmeta['order_invoice_ref'] . '&mode=pdf' ) . '">' . __('Download invoice', 'wpshop') . '</a><br />';
				$order_main_info .= '<span class="dashicons dashicons-arrow-right"></span> <strong>'. __('Invoice number','wpshop').': </strong>'.$order_postmeta['order_invoice_ref'].'<br/>' . $order_invoice_download . '';
			}
			else {
				$order_main_info .= wpshop_display::display_template_element('wpshop_admin_order_generate_invoice_button', array(), array(), 'admin');
			}
		}
		$order_main_info .= '</div>';


		/*Add the current order status in display**/
			$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= ( !empty($order_postmeta['order_status']) ) ? (sprintf('<span class="order_status_' . $order->ID . ' wpshop_order_status_container wpshop_order_status_%1$s ">%2$s</span>', sanitize_title(strtolower($order_postmeta['order_status'])), __($order_status[strtolower($order_postmeta['order_status'])], 'wpshop')) ) : '';

			$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= $order_main_info;

		/**	Add a box allowing to notify the customer on order update	*/
		/**
		 *
		 * To check because notification is not really send
		 *
		 */
		/*if ( !empty($order->post_author) ) {
			$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= '
			<div class="wps-product-section wpshop_order_notify_customer_on_update_container" >
				<input type="checkbox" name="notif_the_customer" id="wpshop_order_notif_the_customer_on_update" /> <label for="wpshop_order_notif_the_customer_on_update" >'.__('Send a notification to the customer', 'wpshop').'</label>
				<!-- <br/><input type="checkbox" name="notif_the_customer_sendsms" id="wpshop_order_notif_the_customer_sendsms_on_update" /> <label for="wpshop_order_nnotif_the_customer_sendsms_on_update" >'.__('Send a SMS to the customer', 'wpshop').'</label> -->
			</div>';
		}*/

		if( ( ( !empty($order_postmeta['cart_type']) && $order_postmeta['cart_type'] == 'quotation' ) || !empty( $order_postmeta['order_temporary_key'] ) ) && in_array( $order_postmeta['order_status'], array( 'awaiting_payment', 'partially_paid' ) ) && (float) $order_postmeta['order_amount_to_pay_now'] != (float) 0 ) {
			$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= '<div class="wps-product-section">' . self::display_customer_pay_quotation( isset( $order_postmeta['pay_quotation'] ), $order->ID ) . '</div>';
		}

		/**
		 * Only for quotations
		 */
		/*if( ( ( !empty($order_postmeta['cart_type']) && $order_postmeta['cart_type'] == 'quotation' ) || !empty( $order_postmeta['order_temporary_key'] ) ) && $order_postmeta['order_status'] != 'canceled' && (float) $order_postmeta['order_amount_to_pay_now'] != (float) 0 ) {
			$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= '<div class="wps-product-section"><input type="text" value="' . wpshop_checkout::wps_direct_payment_link_url( $order->ID ) . '"/></div>';
		}*/

		/*Add the button regarding the order status**/
		if ( !empty($order_postmeta['order_status']) ) {
			if( $order_postmeta['order_status'] == 'awaiting_payment' ) {
				$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= '<div class="wps-product-section"><button class="wps-bton-second-mini-rounded markAsCanceled order_'.$order->ID.'" >'.__('Cancel this order', 'wpshop').'</button><input type="hidden" id="markascanceled_order_hidden_indicator" name="markascanceled_order_hidden_indicator" /></div>';
			}
			$credit_meta = get_post_meta( $order->ID, '_wps_order_credit', true );

			$total_received = (float) 0;
			if ( ! empty( $order_postmeta['order_payment'] ) && ! empty( $order_postmeta['order_payment']['received'] ) ) {
				foreach( $order_postmeta['order_payment']['received'] as $received ) {
					$total_received += (float) isset( $received['received_amount'] ) ? $received['received_amount'] : 0;
				}
			}

			if ( empty($credit_meta) && (float) 0 !== $total_received ) {
				if( $order_postmeta['order_status'] == 'refunded') {
					$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= '<div class="wps-product-section wps_markAsRefunded_container">' .__('Credit Slip number', 'wpshop'). ' : <strong>'. ( (!empty($order_postmeta) && !empty($order_postmeta['order_payment']) && !empty($order_postmeta['order_payment']['refunded_action']) && !empty($order_postmeta['order_payment']['refunded_action']['credit_slip_ref']) ) ? '<a href="' .admin_url( 'admin-post.php?action=wps_invoice&order_id=' .$order->ID. '&amp;invoice_ref=' .$order_postmeta['order_payment']['refunded_action']['credit_slip_ref'].'&credit_slip=ok' ). '" target="_blank">'.$order_postmeta['order_payment']['refunded_action']['credit_slip_ref'].'</a>' : '') .'</strong></div>';
				}
				else {
					$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= '<div class="wps-product-section wps_markAsRefunded_container" ><button class="wps-bton-second-mini-rounded markAsRefunded order_' .$order->ID. '">' .__('Refund this order', 'wpshop'). '</button><input type="hidden" id="markasrefunded_order_hidden_indicator" name="markasrefunded_order_hidden_indicator" /></div>';
				}
			}
		}
		$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] = strrev( preg_replace( strrev( '/wps-product-section/' ), '', strrev( $tpl_component['ADMIN_ORDER_ACTIONS_LIST'] ), 1 ) );
		echo wpshop_display::display_template_element( 'wpshop_admin_order_action_box', $tpl_component, array( 'type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'id' => $order->ID ), 'admin' );
	}



	function order_container_in_admin() {
		global $post;
		$output  = '<div id="wps_order_content_container">';
		$output .= self::order_content( $post );
		$output .= '</div>';
		echo $output;
	}


	/**
	 * Display the order content: the list of element put into order
	 *
	 * @param order $post The complete order content
	 */
	function order_content( $post ) {
		$order_content = '';

		$order = get_post_meta($post->ID, '_order_postmeta', true);

		$order_content .= '<div id="order_product_container" class="order_product_container wpshop_cls" >';
		if($order){/*	Read the order content if the order has product	*/
			$order_content .= '<input type="hidden" value="" name="order_products_to_delete" id="order_products_to_delete" />' . wpshop_cart::display_cart(true, $order, 'admin');
			if (empty($order['order_invoice_ref'])) {
				$order_content .= '<div id="order_refresh_button_container" class="wpshop_clear_block" ><input type="button" class="button-primary alignright wpshopHide" id="wpshop_admin_order_recalculate" value="' . __('Refresh order informations', 'wpshop') . '" /></div>';
			}
		}
		$order_content .= '<div class="wpshop_cls" ></div></div>';

		return $order_content;
	}





	/** Generate the billing reference regarding the order $order_id
	 * @return void
	*/
	function order_generate_billing_number($order_id, $force_invoicing = false){
		global $wpdb, $wpshop_modules_billing;

		// Get the order from the db
		$order = get_post_meta($order_id, '_order_postmeta', true);

		// If the payment is completed
		if(($order['order_status']=='completed') || $force_invoicing) {

			// If the reference hasn't been generated yet
			if(empty($order['order_invoice_ref'])) {
				$order['order_invoice_ref'] = $wpshop_modules_billing->generate_invoice_number( $order_id );

				update_post_meta($order_id, '_order_postmeta', $order);
			}
		}
	}


	/** Renvoi une nouvelle r�f�rence unique pour une commande
	* @return int
	*/
	public static function get_new_order_reference(){
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
	public static function get_new_pre_order_reference($save = true){
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
	 *	Add information about user to the selected order
	 *
	 *	@param int $user_id The user identifier to get information for and to add to order meta informations
	 *	@param int $order_id The order identifier to update meta information for
	 *
	 *	@return void
	 */
	public static function set_order_customer_addresses($user_id, $order_id, $shipping_address_id='', $billing_address_id=''){
		/**	Get order informations	*/
		$billing_info['id'] = get_post_meta($billing_address_id, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, true);
		$billing_info['address'] = get_post_meta($billing_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
		$billing_info['address_id'] = ( !empty($_SESSION['billing_address']) ) ? intval( $_SESSION['billing_address'] ) : '';
		if ( !empty($_SESSION['shipping_partner_id']) ) {
			$partner_address_id = get_post_meta( $_SESSION['shipping_partner_id'], '_wpshop_attached_address', true);
			if (!empty($partner_address_id)) {
				foreach( $partner_address_id as $address_id ) {
					$shipping_info['id'] = get_post_meta($address_id, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, true);
					$shipping_info['address'] = get_post_meta( $address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
				}
			}
		}
		else {
			$shipping_info['id'] = get_post_meta($shipping_address_id, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, true);
			$shipping_info['address'] = get_post_meta($shipping_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
			$shipping_info['address_id'] = ( !empty($_SESSION['shipping_address']) ) ? intval( $_SESSION['shipping_address'] ) : '';
		}

		$order_info = array('billing' => $billing_info, 'shipping' => $shipping_info);

		/**	Update order info metadata with new shipping	*/
		update_post_meta($order_id, '_order_info', $order_info);
	}


	/**
	 * Set the custom colums
	 * @return array
	 */
	static function orders_edit_columns($columns){
		$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );

	  $columns = array(
			'cb' => '<input type="checkbox" />',
			'order_identifier' => __('Identifiers', 'wpshop'),
			'order_status' => __('Status', 'wpshop'),
			'order_type' => __('Order type', 'wpshop'),
			'order_billing' => __('Billing', 'wpshop'),
		);
		if ( ( ! empty( $shipping_address_option ) && ! empty( $shipping_address_option['activate'] ) ) ) {
			$columns ['order_shipping'] = __('Shipping', 'wpshop');
		}
		$columns ['order_total'] = __('Order total', 'wpshop');
		$columns ['date'] = __('Date', 'wpshop');
		//$columns ['order_actions'] = __('Actions', 'wpshop');

	  return $columns;
	}

	/** Give the content by column
	 * @return array
	*/
	public static function orders_custom_columns($column, $post_id) {
		if ( get_post_type( $post_id ) == WPSHOP_NEWTYPE_IDENTIFIER_ORDER ) {
			global $civility, $order_status;

			$metadata = get_post_custom();

			$order_postmeta = isset($metadata['_order_postmeta'][0])?unserialize($metadata['_order_postmeta'][0]):'';
			$addresses = get_post_meta($post_id,'_order_info', true);

			switch($column){
				case "order_identifier":
					if( !empty( $order_postmeta['order_key'] ) ) {
						echo '<b>' . $order_postmeta['order_key'] . '</b><br>';
					}
					if( !empty( $order_postmeta['order_invoice_ref'] ) ) {
						echo '<i>' . $order_postmeta['order_invoice_ref'] . '</i>';
					} elseif( !empty($order_postmeta['order_temporary_key'] ) ) {
						echo '<b>' . $order_postmeta['order_temporary_key'] . '</b>';
					}
				break;

				case "order_status":
					echo !empty($order_postmeta['order_status']) ? sprintf('<mark class="%s" id="order_status_'.$post_id.'">%s</mark>', sanitize_title(strtolower($order_postmeta['order_status'])), __($order_status[strtolower($order_postmeta['order_status'])], 'wpshop')) : __('Unknown Status', 'wpshop');

					do_action( 'wps_order_status', $post_id, $order_postmeta );
				break;

				case "order_billing":
					if ( !empty($addresses['billing']) && !empty($addresses['billing']['address']) && is_array($addresses['billing']['address']) ) {
						$billing = $addresses['billing']['address'];
					}
					else if ( !empty($addresses['billing']) ) {
						$billing = $addresses['billing'];
					}
					if ( !empty($billing) ) {
						echo (!empty($billing['civility']) ? __(wpshop_attributes::get_attribute_type_select_option_info($billing['civility'], 'label', 'custom'), 'wpshop') : null).' <strong>'.(!empty($billing['address_first_name']) ? $billing['address_first_name'] : null).' '.(!empty($billing['address_last_name']) ? $billing['address_last_name'] : null).'</strong>';
						echo empty($billing['company']) ?'<br />' : ', <i>'.$billing['company'].'</i><br />';
						echo !empty($billing['address']) ? $billing['address'] : null;
						echo !empty($billing['postcode']) ? '<br />' . $billing['postcode'] . ' ' : null;
						echo !empty($billing['city']) ? $billing['city'] . ', ' : null;
						echo !empty($billing['country']) ? $billing['country'] : null;
						echo (!empty($billing['phone']) ? '<br /><b>' . $billing['phone'] . '</b>' : '');
					}
					else {
						echo __('No information available for user billing', 'wpshop');
					}
				break;

				case "order_shipping":
					if ( !empty($addresses['shipping']) && !empty($addresses['shipping']['address']) && is_array($addresses['shipping']['address']) ) {
						$shipping = $addresses['shipping']['address'];
					}
					else if ( !empty($addresses['shipping']) ) {
						$shipping = $addresses['shipping'];
					}
					if ( !empty($shipping) ) {
						echo '<strong>'.(!empty($shipping['address_first_name']) ? $shipping['address_first_name'] : null).' '.(!empty($shipping['address_last_name']) ? $shipping['address_last_name'] : null).'</strong>';
						echo empty($shipping['company'])?'<br />':', <i>'.$shipping['company'].'</i><br />';
						echo (!empty($shipping['address']) ? $shipping['address'] : null);
						echo !empty($billing['postcode']) ? '<br />' . $billing['postcode'] . ' ' : null;
						echo !empty($billing['city']) ? $billing['city'] . ', ' : null;
						echo !empty($billing['country']) ? $billing['country'] : null;
					}
					else{
						echo __('No information available for user shipping', 'wpshop');
					}
				break;

				case "order_type":
						echo '<a href="'.admin_url('post.php?post='.$post_id.'&action=edit').'">'.(!empty($order_postmeta['order_temporary_key']) ? __('Quotation','wpshop') :  __('Basic order','wpshop')).'</a>';
						$buttons = '<p class="row-actions">';
						// Voir la commande
						$buttons .= '<a class="button button-small" href="'.admin_url('post.php?post='.$post_id.'&action=edit').'">'.__('View', 'wpshop').'</a>';
						// Marquer comme envoy�
						if (!empty($order_postmeta['order_status']) && ($order_postmeta['order_status'] == 'completed')) {
							$buttons .= '<a data-id="' . $post_id . '" class="wps-bton-second-mini-rounded markAsShipped order_'.$post_id.' wps-bton-loader" data-nonce="' . wp_create_nonce("wpshop_dialog_inform_shipping_number") . '">'.__('Mark as shipped', 'wpshop').'</a> ';
						}
						else if (!empty($order_postmeta['order_status']) && ($order_postmeta['order_status'] == 'awaiting_payment' )) {
							//		$buttons .= '<a class="button markAsCompleted order_'.$post_id.' alignleft" >'.__('Payment received', 'wpshop').'</a>' . wpshop_payment::display_payment_receiver_interface($post_id) . ' ';
						}
						$buttons .= '</p>';
						/*$buttons .= '<input type="hidden" name="input_wpshop_change_order_state" id="input_wpshop_change_order_state" value="' . wp_create_nonce("wpshop_change_order_state") . '" />';
						$buttons .= '<input type="hidden" name="input_wpshop_dialog_inform_shipping_number" id="input_wpshop_dialog_inform_shipping_number" value="' . wp_create_nonce("wpshop_dialog_inform_shipping_number") . '" />';
						$buttons .= '<input type="hidden" name="input_wpshop_validate_payment_method" id="input_wpshop_validate_payment_method" value="' . wp_create_nonce("wpshop_validate_payment_method") . '" />';*/

						echo $buttons;
					break;

				case 'order_total':
					echo esc_html( isset( $order_postmeta['order_grand_total'] ) ? number_format( $order_postmeta['order_grand_total'], 2, '.', '' ) . ' ' . wpshop_tools::wpshop_get_currency() : '-' );
					// Dans le cas ou la commande n'est pas complétement payée on affiche le montant restant.
					if ( 'partially_paid' === $order_postmeta['order_status'] && ! empty( $order_postmeta['order_amount_to_pay_now'] ) ) {
						echo wp_kses( '<br/>' . sprintf( __( 'Due amount %s', 'wpshop' ), number_format( $order_postmeta['order_amount_to_pay_now'], 2, '.', '' ) . ' ' . wpshop_tools::wpshop_get_currency() ), array(
							'br' => array(),
						) );
					}
				break;

				/*case "order_actions":
					$buttons = '<p class="row-actions">';
					// Marquer comme envoy�
					if (!empty($order_postmeta['order_status']) && ($order_postmeta['order_status'] == 'completed')) {
							$buttons .= '<a class="button markAsShipped order_'.$post_id.'">'.__('Mark as shipped', 'wpshop').'</a> ';
					}
					else if (!empty($order_postmeta['order_status']) && ($order_postmeta['order_status'] == 'awaiting_payment' )) {
					//		$buttons .= '<a class="button markAsCompleted order_'.$post_id.' alignleft" >'.__('Payment received', 'wpshop').'</a>' . wpshop_payment::display_payment_receiver_interface($post_id) . ' ';
					}

					// Voir la commande
					$buttons .= '<a class="button alignright" href="'.admin_url('post.php?post='.$post_id.'&action=edit').'">'.__('View', 'wpshop').'</a>';
					$buttons .= '</p>';
					$buttons .= '<input type="hidden" name="input_wpshop_change_order_state" id="input_wpshop_change_order_state" value="' . wp_create_nonce("wpshop_change_order_state") . '" />';
					$buttons .= '<input type="hidden" name="input_wpshop_dialog_inform_shipping_number" id="input_wpshop_dialog_inform_shipping_number" value="' . wp_create_nonce("wpshop_dialog_inform_shipping_number") . '" />';
					$buttons .= '<input type="hidden" name="input_wpshop_validate_payment_method" id="input_wpshop_validate_payment_method" value="' . wp_create_nonce("wpshop_validate_payment_method") . '" />';

					echo $buttons;
				break;*/
			}

		}
	}

	public static function list_table_filters() {
		$post_type = !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		$entity_filter = !empty( $_GET['entity_filter'] ) ? sanitize_text_field( $_GET['entity_filter'] ) : '';
		$entity_filter_btpf = !empty( $_GET['entity_filter_btpf'] ) ? sanitize_text_field( $_GET['entity_filter_btpf'] ) : '';
		$entity_filter_btps = !empty( $_GET['entity_filter_btps'] ) ? sanitize_text_field( $_GET['entity_filter_btps'] ) : '';

		if (isset($post_type)) {
			if (post_type_exists($post_type) && ($post_type == WPSHOP_NEWTYPE_IDENTIFIER_ORDER)) {
				$filter_possibilities = array();
				$filter_possibilities['all'] = __('-- Select Filter --', 'wpshop');
				$filter_possibilities['only_orders'] = __('List orders only', 'wpshop');
				$filter_possibilities['quotations'] = __('List quotations only', 'wpshop');
				$filter_possibilities['free_orders'] = __('List orders free', 'wpshop');
				echo wpshop_form::form_input_select('entity_filter', 'entity_filter', $filter_possibilities, $entity_filter, '', 'index');
				$min = $entity_filter_btpf;
				$max = $entity_filter_btps;
				echo ' <label for="entity_filter_btpf">'.__('Between two prices', 'wpshop').'</label> ';
				echo wpshop_form::form_input('entity_filter_btpf', 'entity_filter_btpf', $min, 'text', 'placeholder="'.__('Minimum price', 'wpshop').'"', null);
				echo wpshop_form::form_input('entity_filter_btps', 'entity_filter_btps', $max, 'text', 'placeholder="'.__('Maximum price', 'wpshop').'"', null);
			}
		}
	}

	public static function list_table_filter_parse_query($query) {
		global $pagenow, $wpdb;
		$post_type = !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		$entity_filter = !empty( $_GET['entity_filter'] ) ? sanitize_text_field( $_GET['entity_filter'] ) : '';
		$entity_filter_btpf = !empty( $_GET['entity_filter_btpf'] ) ? sanitize_text_field( $_GET['entity_filter_btpf'] ) : '';
		$entity_filter_btps = !empty( $_GET['entity_filter_btps'] ) ? sanitize_text_field( $_GET['entity_filter_btps'] ) : '';

		if ( is_admin() && ($pagenow == 'edit.php') && !empty( $post_type ) && ( $post_type == WPSHOP_NEWTYPE_IDENTIFIER_ORDER ) && !empty( $entity_filter ) ) {
			$check = null;
			switch ( $entity_filter ) {
				case 'all':
					$sql_query = $wpdb->prepare(
						"SELECT ID
						FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status != %s",
					WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
					'auto-draft');
					$check = 'post__in';
					break;
				case 'only_orders':
					$sql_query = $wpdb->prepare(
						"SELECT ID
						FROM {$wpdb->posts}
						INNER JOIN {$wpdb->postmeta}
						ON post_id = ID
						AND meta_key = %s
						AND meta_value NOT LIKE %s
						AND meta_value NOT LIKE %s
						WHERE post_type = %s
						AND post_status != %s",
					'_order_postmeta',
					'%s:9:"cart_type";s:9:"quotation";%',
					'%s:17:"order_grand_total";d:0;%',
					WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
					'auto-draft');
					$check = 'post__in';
					break;
				case 'quotations':
					$sql_query = $wpdb->prepare(
						"SELECT ID
						FROM {$wpdb->posts}
						INNER JOIN {$wpdb->postmeta}
						ON post_id = ID
						AND meta_key = %s
						AND meta_value LIKE %s
						WHERE post_type = %s
						AND post_status != %s",
					'_order_postmeta',
					'%s:9:"cart_type";s:9:"quotation";%',
					WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
					'auto-draft');
					$check = 'post__in';
					break;
				case 'free_orders':
					$sql_query = $wpdb->prepare(
							"SELECT ID
							FROM {$wpdb->posts}
							INNER JOIN {$wpdb->postmeta}
							ON post_id = ID
							AND meta_key = %s
							AND meta_value LIKE %s
							WHERE post_type = %s
							AND post_status != %s",
						'_order_postmeta',
						'%s:17:"order_grand_total";d:0;%',
						WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
						'auto-draft');
						$check = 'post__in';
						$no_btp = 'yes';
						break;
			}

			if ( !empty( $check ) ) {
				if( !empty($no_btp) && $no_btp == 'yes' ) {
					$min = 'minimum';
					$max = 'maximum';
				} else {
					$min = ( !empty($_GET['entity_filter_btpf']) && is_numeric($_GET['entity_filter_btpf']) ) ? sanitize_text_field( $_GET['entity_filter_btpf'] ) : 'minimum';
					$max = ( !empty($_GET['entity_filter_btps']) && is_numeric($_GET['entity_filter_btps']) ) ? sanitize_text_field( $_GET['entity_filter_btps'] ) : 'maximum';
				}
				$results = $wpdb->get_results($sql_query);
				$post_id_list = array();
				$i = 0;
				foreach($results as $item){
					$meta_value = get_post_meta($item->ID, '_order_postmeta');
					$price = ( !empty( $meta_value[0]['order_grand_total'] ) ) ? $meta_value[0]['order_grand_total'] : '';
					if( $price >= $min || $min == 'minimum' ) {
						if( $price <= $max || $max == 'maximum' ) {
							$post_id_list[] = $item->ID;
						}
					}
				}
				if( empty($post_id_list) ) {
					$post_id_list[] = 'no_result';
				}
				$query->query_vars[$check] = $post_id_list;
			}
			$query->query_vars['post_type'] = WPSHOP_NEWTYPE_IDENTIFIER_ORDER;
		}
	}



	function latest_products_ordered ( $orders ) {
		global $wpdb;
		$product_id = $output = '';
		$products = array();
		$display_option = get_option('wpshop_display_option');
		if ( !empty($orders) && !empty($display_option) && !empty($display_option['latest_products_ordered']) ) {
			foreach( $orders as $order ) {
				$order_content = get_post_meta( $order->ID, '_order_postmeta', true );
				if ( !empty($order_content) && !empty( $order_content['order_items']) ) {

					foreach( $order_content['order_items'] as $item ) {
						if ( count( $products) >= $display_option['latest_products_ordered'] ) {
							continue;
						}
						$product_id = $item['item_id'];
						if ( !empty( $item) && !empty($item['item_meta']) && !empty($item['item_meta']['variation_definition']) ) {
							$parent_def = wpshop_products::get_parent_variation( $item['item_id'] );
							if ( !empty( $parent_def ) ) {
								$parent_post = $parent_def['parent_post'];
								$product_id = $parent_post->ID;
							}
						}

						if ( !in_array($product_id, $products) ) {
							$products[] = $product_id;
						}
					}
				}
			}
			if ( !empty($products) ) {
				$products_id = implode(",", $products);
				$output = wpshop_display::display_template_element('latest_products_ordered', array('LATEST_PRODUCTS_ORDERED' => do_shortcode('[wpshop_products pid="' .$products_id. '"]')) );
			}
		}
		return $output;
	}

	function get_order_list_for_customer( $customer_id ) {
		global $wpdb;
		$output = '';

		if( !empty($customer_id) ) {
			 $query = $wpdb->prepare( 'SELECT *
							 		   FROM ' .$wpdb->posts. '
							 		   WHERE post_author = %d
							 		   AND post_type = %s', $customer_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
			 $orders = $wpdb->get_results( $query );

			 foreach( $orders as $order ) {

			 }
		}

		return $output;
	}
	static function display_customer_pay_quotation( $state, $oid ) {
		$label = ( ( $state ) ? __('Invalid quotation', 'wpshop') : __('Valid quotation', 'wpshop') );
		$btn = '<p><a role="button" data-nonce="' . wp_create_nonce( 'wps_quotation_is_payable_by_customer' ) . '" class="wps-bton-' . ( ( $state ) ? 'third' : 'second' ) . '-mini-rounded quotation_is_payable_by_customer" href="#" >'.$label.'</a></p>';
		if( $state ) {
			//$btn .= '<a target="_blank" href="' . admin_url( 'admin-ajax.php?action=wps_checkout_quotation&order_id=' . $oid . '&is_link=link' ) . '">' . __( 'Pay link', 'wpshop' ) . '</a>';
			$btn .= '<span><input id="wps_direct_link_url" type="text" value="' . wpshop_checkout::wps_direct_payment_link_url( $oid ) . '"/><a class="button" data-copy-target="#wps_direct_link_url" title="' . __( 'Copy', 'wpshop' ) . '"><span class="dashicons dashicons-clipboard"></span></a><a data-nonce="' . wp_create_nonce( 'wps_send_direct_payment_link' ) . '" role="button" class="button" href="#" title="' . __( 'Send by mail', 'wpshop' ) . '"><span class="dashicons dashicons-email"></span></a></span><span>' . sprintf( __( 'Link is valid until %s', 'wpshop' ), mysql2date( get_option( 'date_format' ), date_format( date_create( date('Y-m') . ' + 2month - 1day' ), 'Y-m-d H:i:s' ), true ) ) . '</span>';
		}
		return $btn;
	}
}
