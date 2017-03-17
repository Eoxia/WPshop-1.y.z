<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Main controller file for wpshop help
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Main controller class for wpshop help
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_help_bubble_ctr {

	/**
	 * CORE - Instanciate wpshop help
	 */
	function __construct() {
		/**	Call style & javascript for administration	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ) );

		/**	Add the scripts into admin footer for future use	*/
		add_action( 'admin_print_footer_scripts', array( &$this, 'wps_dashboard_help') );

		/** Ajax actions **/
		add_action( 'wp_ajax_close_wps_help_window', array( &$this, 'wps_ajax_close_wps_help_window' ) );
	}

	/**
	 * Include stylesheets
	 */
	function admin_scripts() {
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
	}

	/**
	 * AJAX - Save into current user meta the different help that have to be closed next time the user will be logged in
	 */
	function wps_ajax_close_wps_help_window() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_ajax_close_wps_help_window' ) )
			wp_die();

		$status = false;
		$result = '';
		$pointer_id = !empty( $_POST['pointer_id']) ? wpshop_tools::varSanitizer( $_POST['pointer_id'] ) : '';
		if ( !empty($pointer_id) ) {
			$seen_help_windows = get_user_meta( get_current_user_id(), '_wps_closed_help', true);
			$seen_help_windows[ $pointer_id ] = true;

			update_user_meta( get_current_user_id(), '_wps_closed_help', $seen_help_windows);
			$status = true;
		}

		$response = array( 'status' => $status, 'response' => $result );
		wp_die( json_encode( $response ) );
	}

	/**
	 * WORDPRESS HOOK - FOOTER SCRIPTS - Create the help messages for wpshop. only create them into required pages by loking at current screen
	 */
	function wps_dashboard_help() {
		/**	Initialise help messages	*/
		$help_cases = null;

		/**	Get current screen in order to load messages only in required pages	*/
		$current_screen = get_current_screen();
		$current_screen->id = 'disable';
		switch ( $current_screen->id ) {
			case 'toplevel_page_wpshop_dashboard':
				$help_cases[ 'download_newsletter_contacts' ]		= array( 'edge' => 'left', 'at' => 'left bottom', 'my' => 'left top', 'pointer_id' => '#download_newsletter_contacts' );
			break;

			case 'wpshop_product':
				$help_cases[ 'product_page_categories' ] 			= array( 'edge' => 'right',  'at' => '', 'my' => '', 'pointer_id' => '#wpshop_product_categorydiv');
				$help_cases[ 'product_datas_configuration' ] 		= array( 'edge' => 'bottom', 'at' => 'center top', 'my' => 'bottom right', 'pointer_id' => '#wpshop_product_fixed_tab');
				$help_cases[ 'product_display_configuration' ] 		= array( 'edge' => 'bottom', 'at' => 'right bottom', 'my' => 'bottom', 'pointer_id' => '.wpshop_product_data_display_tab' );
				$help_cases[ 'product_variations' ] 				= array( 'edge' => 'bottom', 'at' => 'right bottom', 'my' => 'bottom', 'pointer_id' => '#wpshop_new_variation_list_button' );
				$help_cases[ 'product_variations_configuration' ] 	= array( 'edge' => 'bottom', 'at' => 'right bottom', 'my' => 'bottom', 'pointer_id' => '#wpshop_variation_parameters_button' );
				$help_cases[ 'add_product_automaticly_to_cart' ] 	= array( 'edge' => 'right',  'at' => '', 'my' => '', 'pointer_id' => '#wpshop_product_options' );
			break;

			case 'edit-wpshop_product_category':
				$help_cases[ 'category_filterable_attributes' ] 	= array( 'edge' => 'bottom', 'at' => 'top', 'my' => 'bottom', 'pointer_id' => '.filterable_attributes_container' );
				$help_cases[ 'category_picture' ] 					= array( 'edge' => 'bottom', 'at' => '', 'my' => 'bottom', 'pointer_id' => '.category_new_picture_upload' );
			break;

			case WPSHOP_NEWTYPE_IDENTIFIER_ORDER:
				$help_cases[ 'order_customer_comment' ] 			= array( 'edge' => 'right', 'at' => '', 'my' => '', 'pointer_id' => '#wpshop_order_customer_comment' );
				$help_cases[ 'order_notification_message' ] 		= array( 'edge' => 'bottom', 'at' => '', 'my' => '', 'pointer_id' => '#wpshop_order_private_comments' );
				$help_cases[ 'order_shipping_box' ] 				= array( 'edge' => 'right', 'at' => '', 'my' => '', 'pointer_id' => '#wpshop_order_shipping' );
			break;

			case 'wpshop_shop_message':
				$help_cases[ 'message_historic' ] 					= array( 'edge' => 'bottom', 'at' => '', 'my' => '', 'pointer_id' => '#wpshop_message_histo' );
			break;

			case 'settings_page_wpshop_option':
				$help_cases[ 'options_payment_part' ] 				= array ('edge' => 'right', 'at' => '', 'my' => '', 'pointer_id' => '#wps_payment_mode_list_container' );
			break;
		}

		if ( !empty( $help_cases ) ) {
			/** Get help data seen by user **/
			$closed_help_window = get_user_meta( get_current_user_id(), '_wps_closed_help', true);

			/**	Read the different help cases	*/
			foreach( $help_cases as $help_id => $help_case ) {
				if ( empty( $closed_help_window ) || ( !empty( $closed_help_window ) && !array_key_exists( $help_id, $closed_help_window ) ) ){
					switch( $help_id ) {
						case 'download_newsletter_contacts' :
							$pointer_content  = '<h3>' .__( 'Customers information download', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can download emails of customers who accept to receive your commercials offers or your partners commercials offers by newsletter', 'wpshop'). '</p>';
							break;

						case 'product_page_categories' :
							$pointer_content  = '<h3>' .__( 'WPShop Categories', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can classify your products by category.', 'wpshop'). '<br/></p>';
							$pointer_content .= '<p><a href="' .admin_url('edit-tags.php?taxonomy=wpshop_product_category&post_type=wpshop_product'). '" class="button-primary" target="_blank">' .__('Create my WPShop categories', 'wpshop' ). '</a></p>';
							break;

						case 'product_datas_configuration' :
							$pointer_content  = '<h3>' .__( 'Product configurations', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can configure your product (Price, weight, reference and all attributes you want to create and affect to products', 'wpshop'). '</p>';
							break;

						case 'product_display_configuration' :
							$pointer_content  = '<h3>' .__( 'Product display configurations', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can manage what elements you want to display on your product page', 'wpshop' ). '</p>';
							break;

						case 'product_variations' :
							$pointer_content  = '<h3>' .__( 'Product variations', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can generate your product variations.', 'wpshop'). '</p><br/>';
							$pointer_content .= '<p><a href="http://www.wpshop.fr/documentations/configurer-un-produit-avec-des-options/" class="button-primary" target="_blank">' .__('Read the tutorial', 'wpshop').'</a></p>';
							break;

						case 'product_variations_configuration' :
							$pointer_content  = '<h3>' .__( 'Variations configuration', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can manage your product variations configurations (Display "Price from", variation price priority...).', 'wpshop'). '</p>';
							break;

						case 'add_product_automaticly_to_cart' :
							$pointer_content  = '<h3>' .__('Add product to cart', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'If you check this checkbox, this produc will be add automaticly to cart. This functionnality is helpful if you want to bill fees for example.', 'wpshop' ). '</p>';
							break;

						case 'category_filterable_attributes' :
							$pointer_content  = '<h3>' .__('Filterable search', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can add a filter search to your WPShop, here will be display all available attributes for a filter search in this category', 'wpshop' ). '</p><br/>';
							$pointer_content .= '<p><a href="http://www.wpshop.fr/documentations/la-recherche-par-filtre/" class="button-primary" target="_blank">' .__('Read the filter search tutorial', 'wpshop').'</a></p>';
							break;

						case 'category_picture' :
							$pointer_content  = '<h3>' .__('Category image', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can add a picture to illustrate your category', 'wpshop' ). '</p>';
							break;

						case 'order_customer_comment' :
							$pointer_content  = '<h3>' .__('Order customer comment', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here is displayed the customer comment wrote during the order', 'wpshop' ). '</p>';
							break;

						case 'message_historic' :
							$pointer_content  = '<h3>' .__('Message Historic', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'This is this message historic. You can check here if an automatic e-mail was send to a customer', 'wpshop' ). '</p>';
							break;

						case 'order_notification_message' :
							$pointer_content  = '<h3>' .__('Order notification', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can add a private comment to the order or send an comment to customer about this order', 'wpshop' ). '</p>';
							break;

						case 'order_shipping_box' :
							$pointer_content  = '<h3>' .__('Shipping actions', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can manage your shipping actions, download the shipping slip.', 'wpshop' ). '</p>';
							break;

						case 'options_payment_part' :
							$pointer_content  = '<h3>' .__('Payment configuration', 'wpshop'). '</h3><p>' .__( 'You can manage your payment methods (Change name, add description, add a logo and apply configurations ). You can add others payment methods', 'wpshop' ). '</p><br/><p><a href="http://shop.eoxia.com/boutique/shop/modules-wpshop/modules-de-paiement/" class="button-primary" target="_blank">' .__('See available payment methods', 'wpshop').'</a></p>';
							break;

						default :
							$pointer_content = '';
							break;
					}

					require( wpshop_tools::get_template_part( WPS_HELP_DIR, WPS_HELP_TEMPLATES_MAIN_DIR, 'backend', 'help', 'container_bubble' ) );
				}
			}
		}
	}

}
