<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_pos_addon_bank_deposit_histo {
	public $name_option = 'bank_deposit_historic';
	public function __construct() {
		/**	Call metaboxes	*/
		add_action( 'admin_init', array( $this, 'metaboxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'vars_js' ), 11 );
		add_option( $this->name_option, array(), '', 'yes' );
	}
	public function metaboxes() {
		add_meta_box( 'wpspos-bank-deposit-histo-metabox', __( 'Historic bank deposit', 'wps-pos-i18n' ), array( $this, 'metabox' ), 'wpspos-bank-deposit', 'wpspos-bank-deposit-right' );
	}
	public function metabox() {
		require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/bank_deposit', 'metabox', 'bank_deposit_histo' ) );
	}
	public function add_historic( $new_value ) {
		$histo = get_option( $this->name_option );
		$histo[] = '';
		end( $histo );
		$id = key( $histo );
		$new_value['id'] = $id;
		$histo[$id] = $new_value;
		update_option( $this->name_option, $histo );
	}
	public function get_historic() {
		return array_reverse( get_option( $this->name_option ) );
	}
	public function row_model( $id, $date, $amount, $payments ) {
		return array( 'id' => $id, 'date' => $date, 'amount' => $amount, 'payments' => $payments );
	}
	public function vars_js() {
		wp_localize_script( 'wpspos-backend-bank-deposit-js', 'historics', $this->get_historic() );
		wp_localize_script( 'wpspos-backend-bank-deposit-js', 'templates_url', admin_url( 'admin-post.php' ) );
	}
	public function save_historic_ajax() {
		$list_payments = !empty( $_POST['payments'] ) ? (array) $_POST['payments'] : array();
		$payments = array();
		foreach( $list_payments as $payment ) {
			if( is_float( $payment ) ) {
				$payments[] = (float) $payment;
			}
		}
		$this->add_historic( $this->row_model( 0, sanitize_text_field( $_POST['date'] ), sanitize_text_field( $_POST['amount'] ), $payments ) );
		echo json_encode( $this->get_historic() );
		wp_die();
	}
}
