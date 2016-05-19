<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_address_admin {
	function __construct() {
		/**	Include the different javascript	*/
		add_action( 'admin_init', array(&$this, 'admin_js') );

		// Ajax Actions
		add_action( 'wp_ajax_wps_order_load_address_edit_form', array( $this, 'load_address_form_action') );
		add_action( 'wp_ajax_reload_order_addresses_for_customer', array( $this, 'reload_addresses_for_customer') );
		add_action( 'wp_ajax_delete_address_in_order_panel', array( $this, 'delete_address_in_order_panel') );
	}

	/**
	 * Include stylesheets
	 */
	function admin_js() {
		add_thickbox();
	}

	/**
	 * Display address in customer informations panel
	 * @param integer $customer_id
	 * @param integre $order_id
	 * @param integer $address_type
	 * @return string
	 */
	function display_customer_address_in_order( $customer_id, $order_id, $address_type ) {
		$output = '';
		if( !empty($customer_id) && !empty($order_id) && !empty($address_type) ) {
			$order_infos = get_post_meta( $order_id, '_order_info', true );
			$order_metadata = get_post_meta( $order_id, '_order_postmeta', true );

			$billing_address_option = get_option( 'wpshop_billing_address' );
			$address_type_indicator = ( !empty($billing_address_option) && !empty($billing_address_option['choice']) && $billing_address_option['choice'] == $address_type ) ? 'billing' : 'shipping';

			// Freeze Address display
			$address_informations = ( !empty($order_infos) && is_array($order_infos) && !empty($order_infos[$address_type_indicator]) && !empty($order_infos[$address_type_indicator]['address']) ) ? $order_infos[$address_type_indicator]['address'] : '';
			$address_content = ( !empty($address_informations) && !empty($order_infos[$address_type_indicator]['id']) ) ? wps_address::display_an_address( $address_informations, '', $order_infos[$address_type_indicator]['id'] ) : '';
			ob_start();
			require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "freeze-address-admin-display") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	/**
	 * Load address edit form in thicbox
	 */
	function load_address_form_action() {
		$address_type_id = ( !empty($_GET['address_type']) ) ? intval( $_GET['address_type'] ) : null;
		$address_id = ( !empty($_GET['address_id']) ) ? intval( $_GET['address_id'] ) : null;
		$customer_id = ( !empty($_GET['customer_id']) ) ? intval( $_GET['customer_id'] ) : null;
		$_wpnonce = ( !empty( $_REQUEST['_wpnonce'] ) ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'load_adress_edit_form' ) )
			wp_die();

		$wps_address = new wps_address();
		$form = $wps_address->loading_address_form( $address_type_id, $address_id, $customer_id );

		wp_die( $form[0] );
	}

	/**
	 * Reload address panel
	 */
	function reload_addresses_for_customer() {
		$_wpnonce = ( !empty( $_REQUEST['_wpnonce'] ) ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'reload_addresses_for_customer' ) )
			wp_die();

		$status = false; $response = '';
		$customer_id = ( !empty($_POST['customer_id']) ) ? intval($_POST['customer_id']) : '';
		$order_id = ( !empty($_POST['order_id']) ) ? intval($_POST['order_id']) : '';
		if( !empty($customer_id) ) {
			$wps_address = new wps_address();
			$response = $wps_address->display_addresses_interface( $customer_id, true, $order_id );
			$status = true;
		}

		wp_die( json_encode( array( 'status' => $status, 'response' => $response ) ) );
	}

	/**
	 * Delete address in order
	 */
	function delete_address_in_order_panel() {
		$status = false;
		$address_datas = ( !empty($_POST['address_id']) ) ? wpshop_tools::varSanitizer($_POST['address_id']): null;
		$_wpnonce = ( !empty( $_REQUEST['_wpnonce'] ) ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'delete_address_in_order_panel_' . $address_datas ) )
			wp_die();


		if( !empty($address_datas) ) {
			$address_datas = explode( '-', $address_datas );
			if( !empty($address_datas) && !empty($address_datas[0]) ) {
				wp_delete_post( $address_datas[0], true );
				delete_post_meta( $address_datas[0], '_wpshop_address_attribute_set_id' );
				delete_post_meta( $address_datas[0], '_wpshop_address_metadata' );
				$status = true;
			}
		}

		wp_die( json_encode( array( 'status' => $status ) ) );
	}

}
