<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier du controleur des metaboxes pour l'administration des clients dans wpshop / Controller file for managing metaboxes into customer administration interface
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Classe du controleur des metaboxes pour l'administration des clients dans wpshop / Controller class for managing metaboxes into customer administration interface
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 */
class wps_customer_metaboxe_controller_01 extends wps_customer_ctr {

	/**
	 * Instanciation de la gestion des metaboxes / Insctanciate metaboxes management
	 */
	function __construct() {
		add_action( 'add_meta_boxes_' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, array( $this, 'add_meta_box' ) );
	}


	/**
	 * Mise en place des metaboxes pour gérer les clients / Create metaboxes for customer managemenr
	 *
	 * @param WP_Post $customer Le client actuel / The current customer
	 */
	function add_meta_box( $customer ) {
		remove_meta_box( 'submitdiv', WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal' );
		add_meta_box( 'submitdiv', __( 'Save' ), array( $this, 'wps_customer_informations_save' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'high' );
		add_meta_box( 'wps_customer_informations', __( 'Customer\'s account informations', 'wpshop' ), array( $this, 'wps_customer_account_informations' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'high' );
		if ( 'auto-draft' !== $customer->post_status ) {
			add_meta_box( 'wps_customer_orders', __( 'Customer\'s orders', 'wpshop' ), array( $this, 'wps_customer_orders_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'low' );
			add_meta_box( 'wps_customer_messages_list', __( 'Customer\'s send messages', 'wpshop' ), array( $this, 'wps_customer_messages_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );
			add_meta_box( 'wps_customer_coupons_list', __( 'Customer\'s coupons list', 'wpshop' ), array( $this, 'wps_customer_coupons_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );
			add_meta_box( 'wps_customer_addresses_list', __( 'Customer\'s addresses', 'wpshop' ), array( $this, 'wps_customer_addresses_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'low' );
		}
	}


	/**
	 * META-BOX CONTENT - Display customer's order list in customer back-office interface
	 */
	function wps_customer_orders_list( $post ) {
		$output = '';
		$output .= '<p><a class="button" href="' . admin_url( 'post-new.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER ) . '&customer_id=' . $post->post_author . '">' . __('Add quotation', 'wpshop') . '</a></p>';
		$wps_orders = new wps_orders_ctr();
		$output .= $wps_orders->display_orders_in_account( $post->post_author);
		echo $output;
	}

	/**
	 * META-BOX CONTENT - Display Customer's addresses in customer back-office interface
	 */
	function wps_customer_addresses_list( $post ) {
		global $wpdb;

		$wps_addresses = new wps_address();
		$output = $wps_addresses->display_addresses_interface( $post->post_author, true );
		echo '<input type="hidden" name="wps_customer_id" id="wps_orders_selected_customer" value="' . $post->post_author . '">';
		echo '<div data-nonce="' . wp_create_nonce( 'reload_addresses_for_customer' ) . '" id="wps_customer_addresses" class="wps-gridwrapper2-padded">' . $output . '</div>';
	}

	/**
	 * META-BOX CONTENT - Display customer's send messages
	 */
	function wps_customer_messages_list( $post ) {
		$wps_messages = new wps_message_ctr();
		$output = $wps_messages->display_message_histo_per_customer( array(),$post->post_author);
		echo $output;
	}

	/**
	 * META-BOX CONTENT - Display wps_customer's coupons list
	 */
	function wps_customer_coupons_list( $post ) {
		$wps_customer = new wps_coupon_ctr();
		$output = $wps_customer->display_coupons( $post->post_author );
		echo $output;
	}

	/**
	 * META-BOX CONTENT - Display Customer's account informations in administration panel
	 */
	function wps_customer_account_informations( $post ) {
		$wps_account = new wps_account_ctr();
		$output = $wps_account->display_account_informations( $post->post_author );
		echo $output;
	}

	function wps_customer_informations_save( $post ) {
		echo '<div class="wps-boxed"><button class="wps-bton-first-rounded" id="wps_signup_button">' . __('Save') . '</button></div>';
		apply_filters( 'wps_filter_customer_action_metabox', $post->ID, wps_customer_ctr::get_author_id_by_customer_id( $post->ID ) );
	}

}

new wps_customer_metaboxe_controller_01();
