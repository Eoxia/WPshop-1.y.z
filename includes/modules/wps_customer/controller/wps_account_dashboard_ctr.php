<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_account_dashboard_ctr {
	function __construct() {
		add_shortcode( 'wps_account_dashboard', array( $this, 'display_account_dashboard') );
		// add_shortcode( 'wps_messages', array( 'wpshop_messages', 'get_histo_messages_per_customer' ) );
		add_shortcode( 'wps_account_last_actions_resume', array($this, 'display_account_last_actions' ) );
	}

	function import_data( $part ) {
		$output = '';

		switch( $part ) {
			case 'account' :
				$output  = '<div id="wps_account_informations_container" data-nonce="' . wp_create_nonce( 'wps_account_reload_informations' ) . '">';
				$output .= do_shortcode('[wps_account_informations]');
				$output .= '</div>';
				$output .= do_shortcode( '[wps_orders_in_customer_account]');
			break;
			case 'address' :
				$output .= do_shortcode( '[wps_addresses]' );
			break;
			case 'order' :
				$output = do_shortcode( '[wps_orders_in_customer_account]' );
			break;
			case  'opinion' :
				$output = do_shortcode( '[wps_opinion]' );
			break;
			case 'wishlist' :
				$output = '<div class="wps-alert-info">' .__( 'This functionnality will be available soon', 'wpshop'). '</div>';
			break;
			case 'coupon' :
				$output = do_shortcode( '[wps_coupon]' );
			break;
			case 'messages' :
				$output = do_shortcode( '[wps_message_histo]' );
			break;
			default :
				$output = do_shortcode('[wps_account_informations]');
			break;

		}

		$output = apply_filters( 'wps_my_account_extra_panel_content', $output, $part );

		if( get_current_user_id() == 0 ) {
			$output = do_shortcode( '[wpshop_login]' );
		}
		return $output;
	}

	/**
	 * Display Account Dashboard
	 */
	function display_account_dashboard() {
		$part = ( !empty($_GET['account_dashboard_part']) ) ? sanitize_title( $_GET['account_dashboard_part'] ) : 'account';
		$content = $this->import_data( $part );
		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . "/templates/", "frontend", "account/account-dashboard") );
		$output = ob_get_contents();
		ob_end_clean();
		echo $output;
	}


	function display_account_last_actions() {
		global $wpdb;
		$output = '';
		$user_id = get_current_user_id();
		if( !empty($user_id) ) {
			$query = $wpdb->prepare( 'SELECT * FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_ORDER, $user_id );
			$orders = $wpdb->get_results( $query );
			if( !empty($orders) ) {
				$orders_list = '';
				foreach( $orders as $order ) {
					$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
					$order_number = ( !empty($order_meta) && !empty($order_meta['order_key']) ) ? $order_meta['order_key'] : '';
					$order_date = ( !empty($order_meta) && !empty($order_meta['order_date']) ) ? mysql2date( get_option('date_format'), $order_meta['order_date'], true ) : '';
					$order_amount = ( !empty($order_meta) && !empty($order_meta['order_key']) ) ? wpshop_tools::formate_number( $order_meta['order_grand_total'] ).' '.wpshop_tools::wpshop_get_currency( false ) : '';
					$order_available_status = unserialize( WPSHOP_ORDER_STATUS );
					$order_status = ( !empty($order_meta) && !empty($order_meta['order_status']) ) ? __( $order_available_status[ $order_meta['order_status'] ], 'wpshop' ) : '';
					ob_start();
					require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . "/templates/", "frontend", "account/account-dashboard-resume-element") );
					$orders_list .= ob_get_contents();
					ob_end_clean();
				}

				ob_start();
				require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . "/templates/","frontend", "account/account-dashboard-resume") );
				$output = ob_get_contents();
				ob_end_clean();
			}
		}
		return $output;
	}
}
