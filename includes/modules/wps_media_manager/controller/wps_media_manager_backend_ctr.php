<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_media_manager_backend_ctr {
	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;

	function __construct() {
		$this->template_dir = WPS_MEDIA_MANAGER_PATH . WPS_MEDIA_MANAGER_DIR . "/templates/";

		// Add action to create custom type and custom taxonomy
		add_action( 'add_meta_boxes', array($this, 'add_meta_box') );
		add_action( 'save_post', array($this, 'save_post_actions') );

		// Add Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts_backend' ) );

		// Ajax Actions
		add_action( 'wp_ajax_display_pictures_in_backend', array( $this, 'wp_ajax_display_pictures_in_backend' ) );
	}

	/**
	 * Add JAvascript and CSS files in Backend
	 */
	function add_scripts_backend() {
		global $current_screen;
		if ( ! in_array( $current_screen->post_type, array( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ), true ) )
			return;

		wp_enqueue_script( 'jquery');
		wp_enqueue_script( 'jquery-ui');
		wp_enqueue_script( 'jquery-ui-sortable');
		wp_enqueue_script( 'wps_media_manager_backend', WPS_MEDIA_MANAGER_URL . WPS_MEDIA_MANAGER_DIR .'/assets/js/wps_media_manager_backend.js');

		wp_register_style( 'wps_media_manager_css_backend', WPS_MEDIA_MANAGER_URL . WPS_MEDIA_MANAGER_DIR .'/assets/css/wps_media_manager_backend.css' );
		wp_enqueue_style( 'wps_media_manager_css_backend' );

	}

	/**
	 * Display meta Box in Project Custom Type
	 */
	function add_meta_box() {
		add_meta_box('wps_media_manager', __('Media Manager', 'wpshop' ),array( $this, 'meta_box' ), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal','low');
	}

	/**
	 * Display Pictures Meta Box content
	 */
	function meta_box() {
		global $post;
		$media = '';
		$media_id_data = get_post_meta( $post->ID, '_wps_product_media', true );
		if( !empty($media_id_data) ) {
			$media_id = explode( ',', $media_id_data );
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_MEDIA_MANAGER_DIR, $this->template_dir, "backend", "media_list") );
			$media = ob_get_contents();
			ob_end_clean();
		}
		require_once( wpshop_tools::get_template_part( WPS_MEDIA_MANAGER_DIR, $this->template_dir, "backend", "meta_box") );
	}

	/**
	 * AJAX - Display pictures in backend panel
	 */
	function wp_ajax_display_pictures_in_backend() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wp_ajax_display_pictures_in_backend' ) )
			wp_die();

		$status = true; $response = '';
		$media_indicator = !empty($_POST['media_id']) ? (int)$_POST['media_id'] : null;
		if( !empty($media_indicator) ) {
			$media_id = explode( ',', $media_indicator );
			if( !empty($media_id) ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPS_MEDIA_MANAGER_DIR, $this->template_dir, "backend", "media_list") );
				$response = ob_get_contents();
				ob_end_clean();
			}
		}
		echo json_encode( array( 'status' => $status, 'response' => $response ) );
		wp_die();
	}

	// @TODO: NONCE et vérifier product_media
	function save_post_actions() {
		$post_type = !empty( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
		$product_media_form = !empty( $_POST['product_media_form'] ) ? sanitize_text_field( $_POST['product_media_form'] ) : '';
		$action = !empty( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
		$product_media = !empty( $_POST['product_media'] ) ? sanitize_text_field( $_POST['product_media'] ) : '';
		$post_ID = !empty( $_POST['post_ID'] ) ? (int) $_POST['post_ID'] : 0;

		if ( !empty($post_type) && !empty($product_media_form) && $product_media_form == 'post' && $post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT && !empty( $action ) && $action != 'autosave' ) {
			update_post_meta( $post_ID, '_wps_product_media', $product_media );
		}
	}

}
