<?php if ( !defined( 'ABSPATH' ) ) exit;

class wps_wishlist {
	public function __construct() {
		/** Add filter for add the button "Add to wishlist" in the product sheet */
		//add_filter(  );

		/** Add action wp_enqueue_scripts */
		add_action ( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/** Add action ajax wps-load-modal */
		add_action ( 'wp_ajax_wps-load-modal', array( $this, 'ajax_load_modal' ) );
		add_action ( 'wp_ajax_nopriv_wps-load-modal', array( $this, 'ajax_load_modal' ) );

		/** Add action wps-create-wishlist-and-add-product-to-it */
		add_action ( 'wp_ajax_wps-create-wishlist-and-add-product-to-it', array( $this, 'ajax_create_wishlist_and_add_product' ) );
		add_action ( 'wp_ajax_nopriv_wps-create-wishlist-and-add-product-to-it', array( $this, 'ajax_create_wishlist_and_add_product' ) );

		/** Add action wps-add-to-wishlist */
		add_action ('wp_ajax_wps-add-to-wishlist', array( $this, 'ajax_create_wishlist_and_add_product' ) );
		add_action ('wp_ajax_nopriv_wps-add-to-wishlist', array( $this, 'ajax_create_wishlist_and_add_product' ) );

		/** Add action wps-load-wishlist */
		add_action ('wp_ajax_wps-load-wishlist', array( $this, 'ajax_load_wishlist' ) );
		add_action ('wp_ajax_nopriv_wps-get-login-form', array( $this, 'ajax_get_login_form') );

		/** Filter for display frontend */
		add_filter( 'wps_my_account_extra_part_menu', array( $this, 'add_customer_wishlist_menu' ) );
		add_filter( 'wps_my_account_extra_panel_content', array( $this, 'add_customer_wishlist_content' ), 10, 2 );

		add_filter( 'wps-below-add-to-cart', array( $this, 'filter_button_add_to_wishlist') );
		add_filter( 'the_content', array( $this, 'filter_content' ), 2 );


	}

	public function enqueue_scripts() {
		wp_register_style('wps_wishlist_css', WPS_WISHLIST_URL . 'wps_wishlist/assets/css/wps_wishlist.css', '', '0.0.1');
		wp_enqueue_style('wps_wishlist_css');

		wp_register_script('wps_wishlist_js', WPS_WISHLIST_URL . 'wps_wishlist/assets/js/wps_wishlist.js', array('jquery', 'jquery-effects-shake'), '0.0.1');
		wp_enqueue_script('wps_wishlist_js');
	}

	public function ajax_load_modal() {
		header('Content-Type: application/json');

		$response = array();

		if(get_current_user_id() == 0)
			$response['need_login'] = true;

		$response['title'] = __('My wishlist', 'wps_wishlist_i18n');

		$postID = (int)$_POST['postID'];

		$user_meta = get_user_meta(get_current_user_id(), 'wpshop_user_wishlist', true);

		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_WISHLIST_DIR, WPS_WISHLIST_TEMPLATE_DIR, "frontend", "wishlist-render.tpl" ) );
		$response['content'] = ob_get_clean();

		wp_die(json_encode($response));
	}

	public function ajax_create_wishlist_and_add_product() {
		$user_meta = get_user_meta(get_current_user_id(), 'wpshop_user_wishlist', true);
		$name_wishlist = !empty( $_POST['name_wishlist'] ) ? sanitize_text_field( $_POST['name_wishlist'] ) : '';
		$post_id = !empty( $_POST['id'] ) ? (int) $_POST['id'] : 0;

		if(empty($user_meta[$name_wishlist]) || (!empty($user_meta[$name_wishlist]) && !in_array($post_id, $user_meta[$name_wishlist])))
			$user_meta[$name_wishlist][] = $post_id;

		update_user_meta(get_current_user_id(), 'wpshop_user_wishlist', $user_meta);
	}

	/**
	 * CUSTOM HOOK - Add a menu to WPShop customer account dashboard menu in order to display support
	 *
	 * @param string $content The current content passed through filter definition
	 */
	function add_customer_wishlist_menu( $content ) {
		require_once( wpshop_tools::get_template_part( WPS_WISHLIST_DIR, WPS_WISHLIST_TEMPLATE_DIR, 'frontend/menu', "wishlist-menu.tpl") );
	}

	/**
	 * CUSTOM HOOk - Add the content for support to WPShop customer account dashboard
	 *
	 * @param string $output The current output to filter and to apply modification on
	 * @param string $dashboard_part The current selected part into WPShop customer dashboard
	 *
	 * @return string THe html output to display into customer account dashboard
	 */
	function add_customer_wishlist_content( $output, $dashboard_part ) {
		if ( 'my-wishlist' == $dashboard_part ) {
			/** Get wishlist list */
			$wishlist_list = get_user_meta(get_current_user_id(), 'wpshop_user_wishlist', true);

			/**	Output current customer associated tasks	*/
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_WISHLIST_DIR, WPS_WISHLIST_TEMPLATE_DIR, 'frontend/menu', "wishlist-render.tpl" ) );
			$output = ob_get_contents();
			ob_end_clean();
		}

		return $output;
	}

	function ajax_load_wishlist() {
		$wishlist_list = get_user_meta(get_current_user_id(), 'wpshop_user_wishlist', true);

		$my_wishlist = !empty($wishlist_list[$_POST['name_wishlist']]) ? $wishlist_list[sanitize_text_field($_POST['name_wishlist'])] : null;

		$products = new wpshop_products();

		// Get customer post id
		$post = get_posts(get_current_user_id(), array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS));
		$id_customer = $post[0]->ID;

		$name_wishlist = sanitize_text_field( $_POST['name_wishlist'] );

		$name_user = wp_get_current_user();

		ob_start();
		$current_user = wp_get_current_user();
		$name_user = $current_user->user_login;
		require_once( wpshop_tools::get_template_part( WPS_WISHLIST_DIR, WPS_WISHLIST_TEMPLATE_DIR, 'frontend/menu', 'wishlist-list.tpl'));

		wp_die(ob_get_clean());
	}

	function ajax_get_login_form() {
		$wps_account_ctr = new wps_account_ctr();

		echo $wps_account_ctr->get_login_form();

		wp_die();
	}

	function filter_button_add_to_wishlist() {
		?>
		<button class='wps-add-to-wishlist wps-bton-second' data-id='{WPSHOP_PRODUCT_ID}'><i class="wps-icon-love"></i><?php _e('Add to wishlist', 'wpshop'); ?></button>
		<?php
	}

	/** Add filter - content */
	function filter_content($content) {
		$name_wishlist = !empty( $_GET['name_wishlist'] ) ? sanitize_text_field( $_GET['name_wishlist'] ) : '';
		// If post type customers
		if(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS == get_post_type(get_the_ID()) && !empty($name_wishlist)) {
			$post = get_posts(get_the_ID(), array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS));
			$id_customer = $post[0]->post_author;

			$wishlist_list = get_user_meta($id_customer, 'wpshop_user_wishlist', true);
			$my_wishlist = !empty($wishlist_list[$name_wishlist]) ? $wishlist_list[$name_wishlist] : null;

			$products = new wpshop_products();

			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_WISHLIST_DIR, WPS_WISHLIST_TEMPLATE_DIR, 'frontend/menu', 'wishlist-list.tpl'));

			$content .= ob_get_clean();

			return $content;
		}

			return $content;
	}
}
