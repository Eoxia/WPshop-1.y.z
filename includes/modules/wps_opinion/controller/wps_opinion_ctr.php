<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_opinion_ctr {

	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_OPINION_DIR;

	function __construct() {
		$this->template_dir = WPS_OPINION_PATH . WPS_OPINION_DIR . "/templates/";
		add_action( 'admin_init', array( $this, 'declare_options' ) );
		add_shortcode( 'wps_opinion', array( $this, 'display_opinion_customer_interface') );
		add_shortcode( 'wps_star_rate_product', array( $this, 'display_star_rate_in_product') );
		add_shortcode( 'wps_opinion_product', array( $this, 'display_opinion_in_product') );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );

		/** Ajax actions **/
		add_action( 'wp_ajax_wps-opinion-save-form', array( $this, 'wps_opinion_save_form') );
		add_action( 'wp_ajax_nopriv_wps-opinion-save-form', array( $this, 'wps_opinion_save_form') );
		add_action( 'wp_ajax_wps-update-opinion-star-rate', array( $this, 'wps_update_opinion_star_rate') );
		add_action( 'wp_ajax_wps-refresh-add-opinion-list', array( $this, 'wps_refresh_add_opinion_list') );
		add_action( 'wp_ajax_wps_fill_opinion_modal', array( $this, 'wps_fill_opinion_modal') );
	}

	/**
	 * Add JS Files
	 */
	function add_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-form' );
		wp_enqueue_script( 'wps-opinion-js', WPS_OPINION_URL . WPS_OPINION_DIR .'/assets/js/wps_opinion.js', false );
	}

	/**
	 * OPTIONS - Declare add-on configuration panel
	 */
	function declare_options(){
		add_settings_section('wpshop_addons_opinions_options', '<span class="dashicons dashicons-format-chat"></span>'.__('Customer opinions', 'wpshop'), '', 'wpshop_addons_options');
		register_setting('wpshop_options', 'wps_opinion', array( $this, 'validate_options'));
		add_settings_field('wpshop_opinions_field', __( 'Activate possibility to add opinions on products', 'wpshop'), array( $this, 'addons_definition_fields'), 'wpshop_addons_options', 'wpshop_addons_opinions_options');
	}

	/**
	 * Validate opinion option
	 * @param array $input
	 * @return unknown
	 */
	function validate_options( $input ) {
		return $input;
	}

	function addons_definition_fields() {
		$opinion_option = get_option( 'wps_opinion' );
		echo '<input type="checkbox" name="wps_opinion[active]" ' . ( ( !empty($opinion_option) && !empty($opinion_option['active']) ) ? 'checked="ckecked"' : '' ) . '/>';
	}

	/**
	 * Display opinions for an element
	 * @param integer $element_id
	 */
	function display_opinions( $element_id ) {
		if( !empty($element_id) ) {
			$wps_opinion_mdl = new wps_opinion_model();
			$opinions = $wps_opinion_mdl->get_opinions( $element_id );
			if( !empty($opinions) ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPS_OPINION_DIR, $this->template_dir, "frontend", "opinions") );
				$output = ob_get_contents();
				ob_end_clean();
			}
		}
	}

	/**
	 * Display Star rate
	 * @param decimal $rate
	 * @return string
	 */
	function display_stars( $rate ) {
		$output = '';
		$opinion_option = get_option( 'wps_opinion' );
		if( !empty($opinion_option) && !empty($opinion_option['active']) ) {
			if( isset($rate) ) {
				$exploded_rate = explode( '.', $rate );
				for( $i = 1; $i <= 5; $i++ ) {
					if ( $i <= $exploded_rate[0] ) {
						$output .= '<div class="dashicons dashicons-star-filled"></div>';
					}
					else {
						if( ($i == intval( $exploded_rate[0] ) + 1) && !empty($exploded_rate[1]) && intval($exploded_rate[1]) > 0) {
							$output .= '<div class="dashicons dashicons-star-half"></div>';
						}
						else {
							$output .= '<div class="dashicons dashicons-star-empty"></div>';
						}
					}
				}
			}
			else {
				$output = '-';
			}
		}
		return $output;
	}

	/**
	 * Display Opinion interface in administration
	 * @return string
	 */
	function display_opinion_customer_interface() {
		$output = '';
		$customer_id = get_current_user_id();
		$opinion_option = get_option( 'wps_opinion' );
		if( !empty($opinion_option) && !empty($opinion_option['active']) ) {
			if( !empty($customer_id) ) {
				/** Products which wait opinion **/
				//Get all ordered products with no opinion of this customer
				$wps_opinion_mdl = new wps_opinion_model();
				$ordered_products = $wps_opinion_mdl->get_ordered_products( $customer_id, false);
				if( !empty($ordered_products) ) {
					ob_start();
					require( wpshop_tools::get_template_part( WPS_OPINION_DIR, $this->template_dir, "frontend", "waited_opinions") );
					$output .= ob_get_contents();
					ob_end_clean();
				}

				/** Posted opinions **/
				$posted_opinions = $this->wps_customer_posted_opinions( $customer_id );
				ob_start();
				require( wpshop_tools::get_template_part( WPS_OPINION_DIR, $this->template_dir, "frontend", "posted_opinions") );
				$output .= ob_get_contents();
				ob_end_clean();
			}
		}
		return $output;
	}

	/**
	 * Return customer posted opinions
	 * @param int $customer_id
	 * @return array wps_opinion_model
	 */
	function wps_customer_posted_opinions( $customer_id ) {
		$posted_opinions = array();
		$opinion_option = get_option( 'wps_opinion' );
		if( !empty($opinion_option) && !empty($opinion_option['active']) ) {
			if( !empty($customer_id) ) {
				$wps_opinion_mdl = new wps_opinion_model();
				/** Customer opinions **/
				$send_opinions = $wps_opinion_mdl->get_customer_opinions( $customer_id );

				foreach( $send_opinions as $send_opinion ) {
					$rate = get_comment_meta( $send_opinion->comment_ID, '_wps_customer_rate', true );
					$data = array( 'id' => $send_opinion->comment_ID,
								   'opinion_post_ID' => $send_opinion->comment_post_ID,
							       'author_IP' => $send_opinion->comment_author_IP,
							       'author' => $send_opinion->comment_author,
							       'author_email' => $send_opinion->comment_author_email,
							       'opinion_content' => $send_opinion->comment_content,
							       'opinion_date' => $send_opinion->comment_date,
							       'author_id' => $send_opinion->user_id,
							       'opinion_approved' => $send_opinion->comment_approved,
								   'opinion_rate' => $rate
							);
				 	$posted_opinions[] = new wps_opinion_model( $data );
				}
			}
		}
		return $posted_opinions;
	}

	/**
	 * Dispaly rate in product
	 * @param array $args
	 * @return string
	 */
	function display_star_rate_in_product( $args ) {
		$output = '';
		$opinion_option = get_option( 'wps_opinion' );
		if( !empty($opinion_option) && !empty($opinion_option['active']) ) {
			if( !empty($args) && !empty($args['pid']) ) {
				$wps_opinion_mdl = new wps_opinion_model();
				$comments_for_products = $wps_opinion_mdl->get_opinions_for_product( $args['pid'], 'approve' );
				if( !empty($comments_for_products) ) {
					$rate = $this->calcul_rate_average( $comments_for_products );
					$output = $this->display_stars( $rate );
				}
			}
		}
		return $output;
	}

	/**
	 * Calcul star rate average
	 * @param array $comments_for_product
	 * @return decimal
	 */
	function calcul_rate_average( $comments_for_product ) {
		$rate_average = $r = $i = 0;
		if( !empty($comments_for_product) ) {
			foreach( $comments_for_product as $comment_for_product ) {
				if( $comment_for_product->opinion_approved == 1 ) {
					$r += $comment_for_product->opinion_rate;
					$i++;
				}
			}

			$rate_average = ( !empty($i) ) ? number_format( ( $r / $i ), 1, '.', '' ) : 0;
		}
		return $rate_average;
	}

	function display_opinion_in_product( $args ) {
		$output = '';
		$opinion_option = get_option( 'wps_opinion' );
		if( !empty($opinion_option) && !empty($opinion_option['active']) ) {
			if( !empty($args) && !empty($args['pid']) ) {
				$wps_opinion_mdl = new wps_opinion_model();
				$comments_for_product = $wps_opinion_mdl->get_opinions_for_product( $args['pid'], 'approve' );

				if( !empty($comments_for_product) ) {
					ob_start();
					require( wpshop_tools::get_template_part( WPS_OPINION_DIR, $this->template_dir, "frontend", "opinion_in_product") );
					$output .= ob_get_contents();
					ob_end_clean();
				}
				else {
					$output = '<div class="wps-alert-info">' .__( 'No opinion has been posted on this product', 'wps_opinion'). '</div>';
				}
			}
		}
		return $output;
	}

	function wps_update_opinion_star_rate() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_update_opinion_star_rate' ) )
			wp_die();

		$status = false;
		$output = '';
		$rate = !empty( $_POST['rate'] ) ? (int) $_POST['rate'] : 0;

		if( isset( $rate ) ) {
			$output = $this->display_stars( $rate );
			$status = true;
		}
		echo json_encode( array( 'status' => $status, 'response' => $output) );
		wp_die();
	}

	/**
	 * AJAX - Save opinions
	 */
	function wps_opinion_save_form() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_opinion_save_form' ) )
			wp_die();

		$status = false; $response = '';
		$wps_opinion_mdl = new wps_opinion_model();
		$user_id = get_current_user_id();
		// Check if opinion for this product has been posted
		$checking_opinions = $wps_opinion_mdl->get_opinions_for_product( intval($_POST['wps-opinion-product-id']) );
		$comment_exist = false;
		if( !empty($checking_opinions) ) {
			foreach( $checking_opinions as $o ) {
				if( $o->user_id == $user_id ) {
					$comment_exist = true;
				}
			}
		}
		if( !empty($user_id) && !$comment_exist ) {
			$user_data = get_userdata( $user_id );
			$data = array( 'opinion_post_ID' => intval($_POST['wps-opinion-product-id']),
						   'author_IP' => $_SERVER['REMOTE_ADDR'],
						   'author' => get_user_meta( $user_id, 'first_name', true ).' '.get_user_meta( $user_id, 'last_name', true),
						   'author_email' => ( !empty( $user_data->user_email) ) ? $user_data->user_email : '',
						   'opinion_content' => strip_tags( sanitize_text_field ( $_POST['wps-opinion-comment'] ) ),
						   'opinion_date' => current_time( 'mysql', 0 ),
						   'author_id' => $user_id,
						   'opinion_rate' => intval( $_POST['wps-opinion-rate'] )
					);

			$wps_opinion_mdl->Create( $data );
			$wps_opinion_mdl->Save();
			$status = true;
			$response = __( 'Comment has been send and must be approved by an administrator before publishing', 'wpshop' );
		}
		else {
			$response = __( 'You have already post a comment for this product', 'wpshop' );
		}
		echo json_encode( array( 'status' => $status, 'response' => $response) );
		wp_die();
	}

	/**
	 * AJAX - Refresh Opinion list
	 */
	function wps_refresh_add_opinion_list() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_refresh_add_opinion_list' ) )
			wp_die();

		$status = true; $response = '';
		$response = $this->display_opinion_customer_interface();
		echo json_encode( array('status' => $status, 'response' => $response ) );
		wp_die();
	}

	/**
	 * AJAX - Fill the opinion modal
	 */
	function wps_fill_opinion_modal() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_fill_opinion_modal' ) )
			wp_die();

		$status = true; $title = ''; $content = '';
		$title = __( 'Add your opinion', 'wps_opinion');
		ob_start();
		$pid = ( !empty($_POST['pid']) ) ? intval( $_POST['pid'] ) : null;
		require( wpshop_tools::get_template_part( WPS_OPINION_DIR, $this->template_dir, "frontend", "wps-modal-opinion") );
		$content = ob_get_contents();
		ob_end_clean();
		echo json_encode( array( 'status' => $status, 'title' => $title, 'content' => $content ) );
		wp_die();
	}

}
