<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_orders_in_back_office {

	function __construct() {
		// Template loading
		$this->template_dir = WPS_ORDERS_PATH . WPS_ORDERS_DIR . "/templates/";
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes') );

		// Ajax actions
		add_action('wp_ajax_wpshop_add_private_comment_to_order', array( $this, 'wpshop_add_private_comment_to_order' ) );
		add_action('wp_ajax_wps_order_refresh_product_listing', array( $this, 'refresh_product_list' ) );
		add_action('wp_ajax_wps_add_product_to_order_admin', array( $this, 'wps_add_product_to_order_admin' ) );
		add_action('wp_ajax_wps_refresh_cart_order', array( $this, 'refresh_cart_order' ) );
		add_action('wp_ajax_wps_refresh_payments_order', array( $this, 'refresh_payments_order' ) );
		add_action('wp_ajax_wps_update_product_qty_in_admin', array( $this, 'wps_update_product_qty_in_admin' ) );
		add_action('wp_ajax_wps_order_load_product_variations', array( $this, 'wps_order_load_product_variations' ) );
		add_action('wp_ajax_wps-orders-update-cart-informations', array( $this, 'wps_orders_update_cart_informations' ) );
		add_action('wp_ajax_wps_reverify_payment_invoice_ref', array( $this, 'wps_reverify_payment_invoice_ref' ) );

		// WP General actions
		add_action( 'admin_enqueue_scripts', array( $this, 'wps_orders_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wps_orders_scripts_texts' ), 20 );
		add_action( 'save_post', array( $this, 'save_order_custom_informations' ), 10, 2 );

		// WP Filters
		//add_filter( 'wps_order_saving_admin_extra_action', array( $this, 'wps_notif_user_on_order_saving'), 100, 2 );
	}

	/**
	 * Add scripts
	 */
	function wps_orders_scripts() {
		global $current_screen;
		if( ! in_array( $current_screen->post_type, array( WPSHOP_NEWTYPE_IDENTIFIER_ORDER ), true ) )
			return;

		wp_enqueue_style( 'wps_orders_backend', WPS_ORDERS_URL . WPS_ORDERS_DIR . '/assets/backend/css/wps_orders.backend.css' );
		wp_enqueue_script( 'wps_orders_backend', WPS_ORDERS_URL . WPS_ORDERS_DIR . '/assets/backend/js/wps_orders.js' );
	}

	function wps_orders_scripts_texts() {
		wp_localize_script( 'wps_orders_backend', 'message_confirm_reload', __( 'Are you sure you want to refresh your browser?', 'wpshop' ) );
		wp_localize_script( 'wps_orders_backend', 'message_error_reverify_payment_invoice_ref', __( 'Error detected.', 'wpshop' ) );
	}
	/**
	 * Add meta boxes
	 */
	function add_meta_boxes() {
		global $post;
		$order_meta = get_post_meta( $post->ID, '_order_postmeta', true );
		/** Box  Order Payments **/
		if( !in_array( $post->post_status, array( 'auto-draft' ) ) ) {
			add_meta_box('wpshop_order_payment', '<span class="dashicons dashicons-money"></span> '.__('Order payment', 'wpshop'),array($this, 'display_order_payments_box'),WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'low');
		}
		/**	Box for customer order comment */
		if ( ! empty( $post->post_excerpt ) ) {
			add_meta_box('wpshop_order_customer_comment', '<span class="dashicons dashicons-format-status"></span> '.__('Order customer comment', 'wpshop'),array( $this, 'order_customer_comment_box'),WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'low');
		}
		/** Historic sales **/
		add_meta_box('wpshop_product_order_historic', __('Sales informations', 'wpshop'), array( $this, 'meta_box_product_sale_informations'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'low');
		/**	Box with the complete order content	*/
		$payment_started = false;
		if ( ! empty( $order_meta['order_payment'] ) && ! empty( $order_meta['order_payment']['received'] ) ) {
			foreach ( $order_meta['order_payment']['received'] as $received ) {
				if ( empty( $received['received_amount'] ) ) {
					continue;
				}
				$payment_started = true;
				break;
			}
		}
		if ( ! $payment_started ) {
			add_meta_box( 'wpshop_product_list', '<span class="dashicons dashicons-archive"></span> ' . __( 'Product List', 'wpshop' ), array( $this, 'wps_products_listing_for_quotation' ), WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low' );
		}
		/**	Box with the complete order content	*/
		add_meta_box( 'wpshop_order_content', '<span class="dashicons dashicons-cart"></span> '.__('Order content', 'wpshop'), array( $this, 'meta_box_order_content'), WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low');
		/** Box Private order comments **/
		if( !in_array( $post->post_status, array( 'auto-draft' ) ) ) {
			add_meta_box('wpshop_order_private_comments', '<span class="dashicons dashicons-format-chat"></span> '.__('Comments', 'wpshop'), array( $this, 'meta_box_private_comment'), WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low');
		}
	}

	/**
	 * METABOX CONTENT - Display Customer comments on order in administration panel
	 * @param object $order
	 */
	function order_customer_comment_box( $order ) {
		if ( ! empty($order) && ! empty($order->ID) ) {
			require_once( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "customer_comment_on_order_box") );
		}
	}

	/**
	 * METABOX CONTENT - Display an order historic of product in administration product panel
	 */
	function meta_box_product_sale_informations () {
 		global $post;
 		$product_id = $post->ID;
 		$variations = wpshop_products::get_variation( $product_id );
 		$order_status = unserialize( WPSHOP_ORDER_STATUS );
 		$color_label = array( 'awaiting_payment' => 'jaune', 'canceled' => 'rouge', 'partially_paid' => 'orange', 'incorrect_amount' => 'orange', 'denied' => 'rouge', 'shipped' => 'bleu', 'payment_refused' => 'rouge', 'completed' => 'vert', 'refunded' => 'rouge', 'pos' => 'bleu');
 		// Get datas
 		$sales_informations = array();
 		/** Query **/
 		$data_to_compare = '"item_id";s:' .strlen($product_id). ':"' .$product_id. '";';
 		$query_args = array( 'posts_per_page' => 10, 'paged' => absint( isset( $_GET['paged_sales'] ) ? $_GET['paged_sales'] : 1 ), 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'meta_query' => array( array('key' => '_order_postmeta', 'value' => $data_to_compare, 'compare' => 'LIKE') ) );
 		$orders = new WP_Query( $query_args );
 		if ( ! empty($orders) && ! empty($orders->posts) ) {
 			foreach( $orders->posts as $order ) {
 				$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
 				$order_info = get_post_meta( $order->ID, '_order_info', true );
 				$sales_informations[] = array(
 						'order_key' => ( ! empty($order_meta) && ! empty($order_meta['order_key']) ) ? $order_meta['order_key'] : '',
 						'order_date' => ( ! empty($order_meta) && ! empty($order_meta['order_date']) ) ? $order_meta['order_date'] : '',
 						'customer_firstname' => ( ! empty($order_info) && ! empty($order_info['billing']) && ! empty($order_info['billing']['address']) && ! empty($order_info['billing']['address']['address_first_name']) ) ? $order_info['billing']['address']['address_first_name'] : '',
 						'customer_name' => ( ! empty($order_info) && ! empty($order_info['billing']) && ! empty($order_info['billing']['address']) && ! empty($order_info['billing']['address']['address_last_name']) ) ? $order_info['billing']['address']['address_last_name'] : '',
 						'customer_email' => ( ! empty($order_info) && ! empty($order_info['billing']) && ! empty($order_info['billing']['address']) && ! empty($order_info['billing']['address']['address_user_email']) ) ? $order_info['billing']['address']['address_user_email'] : '',
 						'order_id' => $order->ID,
 						'order_status' => $order_meta['order_status']
 				);
 			}
 		}
 		// Display results
 		require_once( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "product_order_historic") );
 	}

	/**
	 * METABOX CONTENT - Payments Box in Orders panel
	 * @param string $order
	 */
	function display_order_payments_box( $order ) {
		$order_status = unserialize(WPSHOP_ORDER_STATUS);
		$order_postmeta = get_post_meta($order->ID, '_order_postmeta', true);
		require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "wps_order_payment_box") );
	}

	/**
	 * METABOX CONTENT - Display an lsiting of products to make quotation in backend
	 */
	function wps_products_listing_for_quotation( $post ) {
		$letters = array( 'ALL', 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$current_letter = sanitize_title( $letters[0] );
		$wps_product_mdl = new wps_product_mdl();
		$products = $wps_product_mdl->get_products_by_letter( $current_letter );
		require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "product-listing/wps_orders_product_listing") );
	}

	/**
	 * METABOX CONTENT - Display Private comments Meta box in order administration panel
	 */
	function meta_box_private_comment() {
		global $post;
		require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "order-private-comments/wps_orders_private_comments") );
	}

	/**
	 * METABOX CONTENT - Display Order content in back-office Panel
	 */
	function meta_box_order_content() {
		global $post_id;
		unset( $_SESSION['cart'] );
		echo do_shortcode( '[wps_cart oid="' .$post_id. '" cart_type="admin-panel"]');
	}


	/**
	 * Enregistrement d'informations complémentaires pour les commandes / Save custom informations for orders
	 *
	 * @version 1.4.4.3
	 *
	 * @param integer $post_id The current saved post id / L'identifiant post qui vient d'être sauvegardé.
	 * @param WP_Post $post The entire post currently saved / Le post qui vient d'être sauvegardé.
	 */
	public function save_order_custom_informations( $post_id, $post ) {
		if ( ( WPSHOP_NEWTYPE_IDENTIFIER_ORDER !== $post->post_type ) || ( 'auto-draft' === $post->post_status ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$user_id = get_current_user_id();
		$customer_id = ( ! empty( $_REQUEST['wps_customer_id'] ) ) ? (int) $_REQUEST['wps_customer_id'] : wps_customer_ctr::get_customer_id_by_author_id( $user_id );

		// Récupération des informations actuelles de la commande / Get order's current information.
		$order_meta = get_post_meta( $post_id, '_order_postmeta', true );

		if ( empty( $order_meta ) ) {
			$order_meta = array();
		}


		// Affectation du client de la commande / Affect customer to order.
		remove_action( 'save_post', array( $this, 'save_order_custom_informations' ) );
		wp_update_post( array( 'ID' => $post_id, 'post_parent' => $customer_id ) );
		if ( empty( $order_meta['customer_id'] ) ) {
			update_post_meta( $post_id, '_wpshop_order_customer_id', $user_id );
			$order_meta['customer_id'] = $user_id;
		}

		if ( empty( $order_meta['order_key'] ) ) {
			$order_meta['order_key'] = ! empty( $order_meta['order_key'] ) ? $order_meta['order_key'] : ( ! empty( $order_meta['order_status'] ) && ( $order_meta['order_status'] != 'awaiting_payment' ) ? wpshop_orders::get_new_order_reference() : '');
			$order_meta['order_temporary_key'] = ( isset( $order_meta['order_temporary_key'] ) && ( $order_meta['order_temporary_key'] != '') ) ? $order_meta['order_temporary_key'] : wpshop_orders::get_new_pre_order_reference();
		}
		$order_meta['order_status'] = (isset($order_meta['order_status']) && ($order_meta['order_status'] != '')) ? $order_meta['order_status'] : 'awaiting_payment';
		$order_meta['order_date'] = (isset($order_meta['order_date']) && ($order_meta['order_date'] != '')) ? $order_meta['order_date'] : current_time('mysql', 0);
		$order_meta['order_currency'] = wpshop_tools::wpshop_get_currency( true );

		$billing_adress_id = ( ! empty( $_REQUEST['wps_order_selected_address'] ) && ! empty( $_REQUEST['wps_order_selected_address']['billing'] ) ) ? (int) $_REQUEST['wps_order_selected_address']['billing'] : 0;

		if ( ! empty( $billing_adress_id ) ) {
			$order_informations = get_post_meta( $post_id, '_order_info', true );
			$order_informations = ( ! empty( $order_informations ) ) ? $order_informations : array();
			$billing_address_option = get_option( 'wpshop_billing_address' );
			$billing_address_option = ( ! empty( $billing_address_option ) && ! empty( $billing_address_option['choice'] ) ) ? $billing_address_option['choice'] : '';

			// Billing datas.
			$order_informations['billing'] = array(
				'id' => $billing_address_option,
				'address_id' => $billing_adress_id,
				'address' => get_post_meta( $billing_adress_id, '_wpshop_address_metadata', true ),
			);
			// Shipping datas.
			$shipping_adress_id = ( ! empty( $_REQUEST['wps_order_selected_address'] ) && ! empty( $_REQUEST['wps_order_selected_address']['shipping'] ) ) ? (int) $_REQUEST['wps_order_selected_address']['shipping'] : 0;

			if ( ! empty( $shipping_adress_id ) ) {
				$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );
				$shipping_address_option = ( ! empty( $shipping_address_option ) && ! empty( $shipping_address_option['choice'] ) ) ? $shipping_address_option['choice'] : '';
				$order_informations['shipping'] = array(
					'id' => $shipping_address_option,
					'address_id' => $shipping_adress_id,
					'address' => get_post_meta( $shipping_adress_id, '_wpshop_address_metadata', true ),
				);
			}
			update_post_meta( $post_id, '_order_info', $order_informations );
		}

		$wpshop_admin_order_payment_received = ! empty( $_REQUEST['wpshop_admin_order_payment_received'] ) ? (array) $_REQUEST['wpshop_admin_order_payment_received'] : array();
		$wpshop_admin_order_payment_received['method'] = ! empty( $wpshop_admin_order_payment_received['method'] ) ? sanitize_text_field( $wpshop_admin_order_payment_received['method'] ) : '';
		$wpshop_admin_order_payment_received['payment_reference'] = ! empty( $wpshop_admin_order_payment_received['payment_reference'] ) ? sanitize_text_field( $wpshop_admin_order_payment_received['payment_reference'] ) : '';
		$wpshop_admin_order_payment_received['date'] = ! empty( $wpshop_admin_order_payment_received['date'] ) ? sanitize_text_field( $wpshop_admin_order_payment_received['date'] ) : current_time( 'mysql', 0 );
		$wpshop_admin_order_payment_received['received_amount'] = ! empty( $wpshop_admin_order_payment_received['received_amount'] ) ? str_replace( ',', '.', $wpshop_admin_order_payment_received['received_amount'] ) : 0;
		$action_triggered_from = ! empty( $_REQUEST['action_triggered_from'] ) ? sanitize_text_field( $_REQUEST['action_triggered_from'] ) : '';
		$wshop_admin_order_payment_reference = ! empty( $_REQUEST['wpshop_admin_order_payment_reference'] ) ? sanitize_text_field( $_REQUEST['wpshop_admin_order_payment_reference'] ) : '';

		// Ajout des informations de paiements / Add payment informations.
		if ( ! empty( $wpshop_admin_order_payment_received ) && ! empty( $wpshop_admin_order_payment_received['method'] )
				&& ! empty( $wpshop_admin_order_payment_received['date'] ) && ! empty( $wpshop_admin_order_payment_received['received_amount'] ) &&
				( ( 'add_payment' === $action_triggered_from ) || ! empty( $wshop_admin_order_payment_reference ) ) ) {
			// Enregistrement des informations de paiement dans la commande / Save order payment information.
			$params_array = array(
				'method' 						=> $wpshop_admin_order_payment_received['method'],
				'waited_amount' 		=> $wpshop_admin_order_payment_received['received_amount'],
				'status' 						=> 'payment_received',
				'author' 						=> $user_id,
				'payment_reference' => $wpshop_admin_order_payment_received['payment_reference'],
				'date' 							=> $wpshop_admin_order_payment_received['date'],
				'received_amount' 	=> $wpshop_admin_order_payment_received['received_amount'],
			);
			$order_meta = wpshop_payment::check_order_payment_total_amount( $post_id, $params_array, 'completed', $order_meta, false );
		}

		// Round final amount.
		$order_meta['order_grand_total'] = number_format( round( $order_meta['order_grand_total'], 2 ), 2, '.', '' );
		$order_meta['order_total_ttc'] = number_format( round( $order_meta['order_total_ttc'], 2 ), 2, '.', '' );
		$order_meta['order_amount_to_pay_now'] = number_format( round( $order_meta['order_amount_to_pay_now'], 2 ), 2, '.', '' );

		// Payment Pre-Fill.
		if ( empty( $order_meta['order_payment'] ) ) {
			$order_meta['order_payment']['customer_choice']['method'] = '';
			$order_meta['order_payment']['received'][] = array('waited_amount' => ( ! empty($order_meta) && ! empty($order_meta['order_grand_total']) ) ? number_format($order_meta['order_grand_total'],2,'.', '') : 0 );
		}

		$data['post_ID'] = ! empty( $_REQUEST['post_ID'] ) ? (int) $_REQUEST['post_ID'] : 0;
		$data['markascanceled_order_hidden_indicator'] = ! empty( $_REQUEST['markascanceled_order_hidden_indicator' ] ) ? sanitize_text_field( $_REQUEST['markascanceled_order_hidden_indicator'] ) : '';
		$data['resendordertocustomer_order_hidden_indicator'] = ! empty( $_REQUEST['resendordertocustomer_order_hidden_indicator' ] ) ? sanitize_text_field( $_REQUEST['resendordertocustomer_order_hidden_indicator'] ) : '';
		$data['action_triggered_from'] = ! empty( $_REQUEST['action_triggered_from' ] ) ? sanitize_text_field( $_REQUEST['action_triggered_from'] ) : '';
		$data['notif_the_customer'] = ! empty( $_REQUEST['notif_the_customer' ] ) ? sanitize_text_field( $_REQUEST['notif_the_customer'] ) : '';

		// Apply a filter to make credit, notificate the customer and generate billing actions
		$order_meta = apply_filters( 'wps_order_saving_admin_extra_action', $order_meta, $data );

		// Save Shipping informations & Order status.
		if ( isset( $order_meta['order_shipping_date'] ) ) {
			update_post_meta( $post_id, '_wpshop_order_shipping_date', $order_meta['order_shipping_date'] );
		}
		update_post_meta( $post_id, '_wpshop_order_status', $order_meta['order_status'] );

		// Save Metadata
		update_post_meta( $post_id, '_order_postmeta', $order_meta );
	}

	/**
	 * DEPRECATED
	 * Notificate customer on order saving action
	 * @param array $order_metadata
	 * @param array $posted_datas
	 * @return array
	 */
	function wps_notif_user_on_order_saving( $order_metadata, $posted_datas ) {
		if( ! empty($posted_datas['notif_the_customer']) && $posted_datas['notif_the_customer']=='on' ) {
			$wps_message = new wps_message_ctr();
			/*	Get order current content	*/
			$user = get_post_meta($posted_datas['post_ID'], '_order_info', true);
			$email = get_userdata($posted_datas['user_ID'])->data->user_email;
			//$email = $user['billing']['address']['address_user_email'];
			$first_name = $user['billing']['address']['address_first_name'];
			$last_name = $user['billing']['address']['address_last_name'];

			$object = array('object_type'=>'order','object_id'=>(int)$_REQUEST['post_ID']);
			/* Envoie du message de confirmation de commande au client	*/
			if ( empty( $order_metadata['order_key'] ) ) {
				$wps_message->wpshop_prepared_email($email,
						'WPSHOP_QUOTATION_UPDATE_MESSAGE',
						array(  'order_id' => $object['object_id'],
							    'customer_first_name' => $first_name,
								'customer_last_name' => $last_name,
								'order_date' => current_time('mysql', 0),
								'order_content' => '',
								'order_addresses' => '',
								'order_billing_address' => '',
								'order_shipping_address' => ''
								)
						);
			}
			else {
				$wps_message->wpshop_prepared_email(
						$email,
						'WPSHOP_ORDER_UPDATE_MESSAGE',
						array(  'customer_first_name' => $first_name,
								'customer_last_name' => $last_name,
								'order_key' => $order_metadata['order_key'],
								'order_billing_address' => '',
								'order_shipping_address' => '',
								'order_addresses' => '',
								'order_addresses' => '',
								'order_billing_address' => '',
								'order_shipping_address' => '' ),
						$object);
			}
		}
		return $order_metadata;
	}

	/**
	 * AJAX - Add a private comment to order
	 */
	function wpshop_add_private_comment_to_order() {
		$_wpnonce = ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wpshop_add_private_comment_to_order' ) )
			wp_die();

		$status = false; $result = '';
		$order_id = ( ! empty($_POST['oid']) ) ? intval($_POST['oid']) : null;
		$comment = ( ! empty($_POST['comment']) ) ? wpshop_tools::varSanitizer($_POST['comment']) : null;
		$send_email = ( ! empty($_POST['send_email']) ) ? wpshop_tools::varSanitizer($_POST['send_email']) : null;
		$copy_to_administrator = ( ! empty($_POST['copy_to_administrator']) ) ? wpshop_tools::varSanitizer($_POST['copy_to_administrator']) : null;

		if ( ! empty($comment) && ! empty($order_id) ) {
			$wps_back_office_orders_mdl = new wps_back_office_orders_mdl();
			$new_comment = $wps_back_office_orders_mdl->add_private_comment($order_id, $comment, $send_email, false, $copy_to_administrator );
			if($new_comment) {
				$order_private_comment = get_post_meta( $order_id, '_order_private_comments', true );
				$oid = $order_id;
				ob_start();
				require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "order-private-comments/wps_orders_sended_private_comments") );
				$result = ob_get_contents();
				ob_end_clean();
				$status = true;
			}
		}
		else {
			$result = __('An error was occured', 'wpshop');
		}

		$response = array( 'status' => $status, 'response' => $result );
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * AJAX - Refresh product listing in order back-office
	 */
	function refresh_product_list() {
		$_wpnonce = ! empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
		$letter = ( ! empty($_POST['letter']) ) ? sanitize_title( $_POST['letter'] ) : '';
		$research = ! empty( $_POST['research'] ) ? sanitize_text_field( $_POST['research'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'refresh_product_list_'.strtolower($letter) ) )
			wp_die();

		$status = false; $response = '';
		$oid = ! empty( $_POST['oid'] ) ? (int) $_POST['oid'] : 0;

		if( ! empty($oid) ) {
			$post = get_post( $oid );
		}
		if( ! empty($letter) ) {
			$current_letter = $letter;
			$wps_product_mdl = new wps_product_mdl();
			if( ! empty( $research ) ) {
				$products = $wps_product_mdl->get_products_by_title_or_barcode( $research, strlen( $research ) >= 8 && ctype_digit( $research ) );
			} else {
				$products = $wps_product_mdl->get_products_by_letter( $letter );
			}
			ob_start();
			require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "product-listing/wps_orders_product_listing_table") );
			$response = ob_get_contents();
			ob_end_clean();
			$status = true;
		}
		echo json_encode( array( 'status' => $status, 'response' => $response ) );
		wp_die();
	}

	/**
	 * AJAX - Add product to order in back-office panel
	 */
	function wps_add_product_to_order_admin() {
		check_ajax_referer( 'wps_add_product_to_order_admin' );

		$status = false; $response = ''; $product_have_variations = false;
		// Sended vars
		$product_id = ( ! empty($_POST['pid']) ) ? intval( $_POST['pid']) : null;
		$order_id = ( ! empty($_POST['oid']) ) ? intval( $_POST['oid']) : null;
		$product_qty = ( ! empty($_POST['qty']) ) ? intval( $_POST['qty']) : 1;

		if( ! empty($order_id) && ! empty($product_id) ) {
			$wps_orders = new wps_orders_ctr();
			$product_datas = wpshop_products::get_product_data($product_id, false, '"publish", "free_product"');
			// Check if product have variations
			$have_variations_checking = wpshop_products::get_variation( $product_id );
			if( ! empty($have_variations_checking) ) {
				$product_have_variations = true;
			}
			else {
				// Get Metadatas
				$order_metadata = get_post_meta( $order_id, '_order_postmeta', true );
				// Calcul cart informations
				$wps_cart = new wps_cart();
				$order_metadata = $wps_cart->calcul_cart_information( array( $product_id => array( 'product_id' => $product_id, 'product_qty' => $product_qty ) ), '', $order_metadata, true, false );
				// Update Metadatas
				update_post_meta( $order_id, '_order_postmeta', $order_metadata );
				$status = true;
			}
			$status = true;
		}

		$_wpnonce = wp_create_nonce( 'wps_order_load_product_variations' );

		echo json_encode( array( '_wpnonce' => $_wpnonce, 'status' => $status, 'response' => $response, 'variations_exist' => $product_have_variations ) );
		wp_die();
	}

	/**
	 * AJAX - Refresh cart in administration
	 */
	function refresh_cart_order() {
		check_ajax_referer( 'wps_reload_cart' );

		$status = false; $response = '';
		$order_id = ( ! empty($_POST['order_id']) ) ? intval($_POST['order_id']) : null;
		if( ! empty($order_id) ) {
			$response = do_shortcode( '[wps_cart oid="' .$order_id. '" cart_type="admin-panel"]');
			$status = true;
		}
		echo json_encode( array( 'status' => $status, 'response' => $response ) );
		wp_die();
	}

	/**
	 * AJAX - Refresh cart in administration
	 */
	function refresh_payments_order() {
		check_ajax_referer( 'wps_reload_cart' );
		$status = false; $response = '';
		$order_id = ( ! empty($_POST['order_id']) ) ? intval($_POST['order_id']) : null;
		if( ! empty($order_id) ) {
			ob_start();
			echo $this->display_order_payments_box( get_post( $order_id ) );
			$response = ob_get_contents();
			ob_end_clean();
			$status = true;
		}
		echo json_encode( array( 'status' => $status, 'response' => $response ) );
		wp_die();
	}

	/**
	 * AJAX - Update product Quantity in Back-office Panel
	 */
	function wps_update_product_qty_in_admin() {
		check_ajax_referer( 'wps_reload_cart' );

		$status = false; $response = '';
		$product_id = ( ! empty($_POST['product_id']) ) ? wpshop_tools::varSanitizer( $_POST['product_id'] ) : null;
		$order_id = ( ! empty($_POST['order_id']) ) ? intval( $_POST['order_id'] ) : null;
		$product_qty = ( ! empty($_POST['qty']) ) ? intval( $_POST['qty'] ) : 0;

		if( ! empty($product_id) && ! empty($order_id) ) {
			// Get Metadatas.
			$order_metadata = get_post_meta( $order_id, '_order_postmeta', true );
			// Calcul cart informations.
			$wps_cart = new wps_cart();
			$order_metadata = $wps_cart->calcul_cart_information( array( $product_id => array( 'product_id' => $product_id, 'product_qty' => $product_qty ) ), '', $order_metadata, true, false );
			// Update Metadatas.
			update_post_meta( $order_id, '_order_postmeta', $order_metadata );
			$status = true;
		}

		echo json_encode( array( 'status' => $status ) );
		wp_die();
	}

	/**
	 * AJAX - Load Product Variations in ThickBox on Add product to order action
	 */
	function wps_order_load_product_variations() {
		$_wpnonce = ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_order_load_product_variations' ) )
			wp_die();

		$product_id = ( ! empty($_GET['pid']) ) ? intval( $_GET['pid']) : null;
		$order_id = ( ! empty($_GET['oid']) ) ? intval( $_GET['oid']) : null;
		$qty = ( ! empty($_GET['qty']) ) ? intval( $_GET['qty']) : 1;
		echo '<div class="wps-boxed"><span class="wps-h5">'.__( 'Select your variations', 'wpshop' ).'</span>'.wpshop_products::wpshop_variation($product_id, true, $order_id, $qty ).'<a href="#" class="wps-bton-first-mini-rounded alignRight wps-orders-add_variation_product"><i class="wps-icon-basket"></i> ' .__( 'Add to cart', 'wpshop' ). '</a>'.'</div>';
		wp_die();
	}

	/**
	 * AJAX - Update cart informations
	 */
	function wps_orders_update_cart_informations() {
		$_wpnonce = ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_orders_update_cart_informations' ) )
			wp_die();

		$status = false;
		$order_id = ( ! empty($_POST['order_id']) ) ? intval($_POST['order_id']) : '';
		$shipping_cost = isset($_POST['shipping_cost']) ? wpshop_tools::varSanitizer($_POST['shipping_cost']) : '';
		$discount_value = isset($_POST['discount_amount']) ? wpshop_tools::varSanitizer($_POST['discount_amount']) : '';
		$discount_type = isset($_POST['discount_type']) ? wpshop_tools::varSanitizer($_POST['discount_type']) : '';


		if( ! empty($order_id) ) {
			$order_meta = get_post_meta( $order_id, '_order_postmeta', true );
			$order_meta['order_shipping_cost'] = $shipping_cost;

			//Add discounts if exists
			if( isset($discount_value) && ! empty($discount_type) ) {
				$order_meta['order_discount_type'] = $discount_type;
				$order_meta['order_discount_value'] = $discount_value;
			}
			$wps_cart = new wps_cart();
			$order_meta = $wps_cart->calcul_cart_information( array(), '', $order_meta, true );
			update_post_meta( $order_id, '_order_postmeta', $order_meta );
			$status = true;
		}

		echo json_encode( array( 'status' => $status) );
		wp_die();
	}

	function wps_reverify_payment_invoice_ref() {
		$_wpnonce = ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_reverify_payment_invoice_ref' ) )
			wp_die();

		$status = true;
		$inputs = ! empty( $_POST['inputs'] ) ? (array) $_POST['inputs'] : false;

		if (!$inputs)
			$status = false;

		if( $status ) {
			$inputs_clone = $inputs;
			$inputs = array();
			foreach( $inputs_clone as $var ) {
				if( isset( $var['key'] ) && isset( $var['value'] ) ) {
					$inputs[$var['key']] = $var['value'];
				}
			}

			( isset( $inputs['order_id'] ) ) ? $order_id = $inputs['order_id'] : $status = $order_id = false;
			( isset( $inputs['index_payment'] ) ) ? $index_payment = $inputs['index_payment'] : $status = $index_payment = false;
		}

		if( $status ) {
			$status = wpshop_payment::reverify_payment_invoice_ref( $order_id, $index_payment );
		}

		echo json_encode( array( 'status' => $status ) );
		wp_die();
	}

}
