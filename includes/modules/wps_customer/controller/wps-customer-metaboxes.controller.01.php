<?php
/**
 * Fichier du controleur des metaboxes pour l'administration des clients dans wpshop / Controller file for managing metaboxes into customer administration interface
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 * @package WPShop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe du controleur des metaboxes pour l'administration des clients dans wpshop / Controller class for managing metaboxes into customer administration interface
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 */
class WPS_Customer_Metaboxes_Controller extends wps_customer_ctr {

	/**
	 * Instanciation de la gestion des metaboxes / Insctanciate metaboxes management
	 */
	function __construct() {
		add_action( 'add_meta_boxes_' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, array( $this, 'add_meta_box_customer' ) );
		add_action( 'add_meta_boxes_' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER, array( $this, 'add_meta_box_order' ) );
	}

	/**
	 * Mise en place des metaboxes pour gérer les clients / Create metaboxes for customer managemenr
	 *
	 * @param WP_Post $customer Le client actuel / The current customer.
	 */
	function add_meta_box_customer( $customer ) {
		// add_meta_box( 'wps_customer_informations', __( 'Customer\'s account informations', 'wpshop' ), array( $this, 'wps_customer_account_informations' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'high' );
		if ( 'auto-draft' !== $customer->post_status ) {
			add_meta_box( 'wps_customer_orders', __( 'Customer\'s orders', 'wpshop' ) . '<a class="page-title-action" href="' . admin_url( 'post-new.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER ) . '&customer_id=' . $customer->ID . '">' . __( 'Add quotation', 'wpshop' ) . '</a>', array( $this, 'wps_customer_orders_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'low' );
			add_meta_box( 'wps_customer_messages_list', __( 'Customer\'s send messages', 'wpshop' ), array( $this, 'wps_customer_messages_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );
			add_meta_box( 'wps_customer_coupons_list', __( 'Customer\'s coupons list', 'wpshop' ), array( $this, 'wps_customer_coupons_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );
			add_meta_box( 'wps_customer_prospect_status', __( 'Customer\'s prospect status', 'wpshop' ), array( $this, 'wps_customer_prospect_status' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );
			add_meta_box( 'wps_customer_addresses_list', __( 'Customer\'s addresses', 'wpshop' ), array( $this, 'wps_customer_addresses_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'low' );
		}
	}

	/**
	 * Mise en place des metaboxes pour gérer les clients dans les commandes / Create metaboxes for customer management in orders
	 *
	 * @param WP_Post $order La commande sur laquelle on se trouve / Currently edited order.
	 */
	function add_meta_box_order( $order ) {
		/**	Box with order customer information	*/
		$metabox_title_button = '';
		$order_metadata = get_post_meta( $order->ID, '_order_postmeta', true );
		if ( empty( $order_metadata ) || ( ! empty( $order_metadata['order_status'] ) && ( 'awaiting_payment' === $order_metadata['order_status'] ) ) ) {
			$metabox_title_button = '<a href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=wps_load_customer_creation_form_in_admin&width=730&height=690' ), 'wps_load_customer_creation_form_in_admin', '_wpnonce' ) . '" title="' . __( 'Create a customer', 'wpshop' ) . '" class="page-title-action thickbox" >' . __( 'Create a customer', 'wpshop' ) . '</a>';
		}
		add_meta_box( 'wpshop_order_customer_information_box', '<span class="dashicons dashicons-businessman"></span> ' . __( 'Customer information', 'wpshop' ) . $metabox_title_button, array( $this, 'display_order_customer_informations_in_administration' ), WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'high' );
	}

	/**
	 * META-BOX CONTENT - Display customer's order list in customer back-office interface
	 *
	 * @param WP_Post $post Current post (customer) we are editing.
	 */
	function wps_customer_orders_list( $post ) {
		$output = '';
		$wps_orders = new wps_orders_ctr();
		$output .= $wps_orders->display_orders_in_account( $post->ID );

		echo $output; // WPCS: XSS ok.
	}

	/**
	 * META-BOX CONTENT - Display Customer's addresses in customer back-office interface
	 *
	 * @param WP_Post $post Current post (customer) we are editing.
	 */
	function wps_customer_addresses_list( $post ) {
		$wps_addresses = new wps_address();
		$output = $wps_addresses->display_addresses_interface( $post->ID, true );
		echo '<input type="hidden" name="wps_customer_id" id="wps_orders_selected_customer" value="' . esc_attr( $post->ID ) . '">';
		echo '<input type="hidden" name="wps_customer_addresses_nonce" id="wps_customer_addresses_nonce" value="' . esc_attr( wp_create_nonce( 'reload_addresses_for_customer' ) ) . '">';
		echo '<div id="wps_customer_addresses" class="wps-gridwrapper2-padded">' . $output . '</div>'; // WPCS: XSS ok.
	}

	/**
	 * META-BOX CONTENT - Display customer's send messages
	 *
	 * @param WP_Post $post Current post (customer) we are editing.
	 */
	function wps_customer_messages_list( $post ) {
		$wps_messages = new wps_message_ctr();
		$output = $wps_messages->display_message_histo_per_customer( array(), $post->ID );

		echo $output; // WPCS: XSS ok.
	}

	/**
	 * META-BOX CONTENT - Display prospect status
	 *
	 * @param WP_Post $post Current post (customer) we are editing.
	 */
	function wps_customer_prospect_status( $post ) {
		$customer_prospect = new WPS_Customer_Prospect();
		$current_status    = (int) get_post_meta( $post->ID, 'fk_stcomm', true );
		$statuses          = $customer_prospect->statuses;

		require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . '/templates/', 'backend', 'prospect_status/prospect_status' ) );
	}

	/**
	 * META-BOX CONTENT - Display wps_customer's coupons list
	 *
	 * @param WP_Post $post Current post (customer) we are editing.
	 */
	function wps_customer_coupons_list( $post ) {
		$wps_vouncher = new wps_coupon_ctr();
		$output = $wps_vouncher->display_coupons( $post->post_author );

		echo $output; // WPCS: XSS ok.
	}

	/**
	 * META-BOX CONTENT - Display Customer's account informations in administration panel
	 *
	 * @param WP_Post $post Current post (customer) we are editing.
	 */
	function wps_customer_account_informations( $post ) {
		$wps_account = new wps_account_ctr();
		$output = $wps_account->display_account_informations( $post->ID );

		echo $output; // WPCS: XSS ok.
	}

	/**
	 * Affichage de la zone client dans l'interface des commandes / Display the Customer informations in order back-office panel
	 *
	 * @param WP_Post $post L'objet WP_Post correspond à la commande / The WP_Post object corresponding to the order.
	 */
	function display_order_customer_informations_in_administration( $post ) {
		// N'afficher la metabox uniquement dans les commandes / Only display metabox in orders.
		if ( ! empty( $post ) && ( WPSHOP_NEWTYPE_IDENTIFIER_ORDER === $post->post_type ) ) {
			$post_id = $post->ID;

			wps_customer_admin::display_customer_informations_in_order( $post_id, ( ! empty( $_REQUEST['customer_id'] ) ? (int) $_REQUEST['customer_id'] : $post->post_parent ) );
		} else {
			esc_html_e( 'The requested order has not been found', 'wpshop' );
		}
	}

}

new WPS_Customer_Metaboxes_Controller();
