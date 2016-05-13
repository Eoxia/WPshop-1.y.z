<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_pos_addon_bank_deposit {
	public function __construct() {
		/**	Call metaboxes	*/
		add_action( 'admin_init', array( $this, 'metaboxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'vars_js' ), 11 );
	}
	public function metaboxes() {
		add_meta_box( 'wpspos-bank-deposit-metabox', __( 'Create your bank deposit', 'wps-pos-i18n' ), array( $this, 'metabox' ), 'wpspos-bank-deposit', 'wpspos-bank-deposit-left' );
	}
	public function metabox() {
		require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/bank_deposit', 'metabox', 'bank_deposit' ) );
	}
	public function get_payments() {
		$args = array(
				'posts_per_page' 	=> -1,
				'post_type'			=> WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
				'post_status' 		=> 'publish',
				'meta_key'			=> '_order_postmeta',
				'meta_value'		=> serialize( 'received' ) . serialize( array() ),
				'meta_compare'		=> 'NOT LIKE',
		);
		$query = new WP_Query( $args );
		
		$orders = $query->posts;
		$payments = array();
		
		foreach( $orders as $order ) {
			$order->_order_postmeta = get_post_meta( $order->ID, '_order_postmeta', true );
			foreach( $order->_order_postmeta['order_payment']['received'] as $payment_received ) {
				if( isset( $payment_received['status'] ) && $payment_received['status'] == 'payment_received' ) {
					$payments[] = '';
					end( $payments );
					$id = key( $payments );
					$payments[$id] = $this->row_model( $id, isset( $order->_order_postmeta['order_key'] ) ?	$order->_order_postmeta['order_key'] : $order->_order_postmeta['order_temporary_key'], $payment_received['date'], isset( $order->_order_postmeta['cart']['order_items'] ) ? $order->_order_postmeta['cart']['order_items'] : ( isset( $order->_order_postmeta['order_items'] ) ? $order->_order_postmeta['order_items'] : array() ), $payment_received['received_amount'], $payment_received['method'] );
				}
			}
		}
		
		return $payments;
	}
	public function row_model( $id, $order_key, $date, $products, $amount, $method ) {
		$products_simplified = array();
		if( !empty( $products ) && is_array( $products ) ) {
			foreach( $products as $product ) {
				if( isset( $product['item_meta']['variations'] ) ) {
					foreach( $product['item_meta']['variations'] as $key => $value ) {
						$id_variation = $key;
						break;
					}
					$first = true;
					$title = $product['item_name'] . ' - ';
					foreach( $product['item_meta']['variations'][$id_variation]['item_meta']['variation_definition'] as $variation ) {
						if( $first ) {
							$first = false;
						} else {
							$title .= ', ';
						}
						$title .= $variation['NAME'] . ': ' . $variation['VALUE'];
					}
					$products_simplified[] = $title;
				} else {
					$products_simplified[] = $product['item_name'];
				}
			}			
		}
		return array( 'id' => $id, 'order_key' => $order_key, 'date' => $date, 'products' => $products_simplified, 'amount' => $amount, 'method' => $method );
	}
	public function vars_js() {
		wp_localize_script( 'wpspos-backend-bank-deposit-js', 'payments', $this->get_payments() );
	}
}