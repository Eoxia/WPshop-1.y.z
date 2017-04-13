<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Plugin initialisation definition file.
*
* This file contains the different methods needed by the plugin on initialisation
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
 *	Define the different plugin initialisation's methods
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_init{

	/**
	 *	This is the function loaded when wordpress load the different plugin
	 */
	public static function load() {
		global $wpdb;

		/**	Load the different template element	*/
		$wpshop_display = new wpshop_display();
		$wpshop_display->load_template();

		/*	Declare the different options for the plugin	*/
		add_action('admin_init', array('wpshop_options', 'add_options'));

		/*	Include head js	*/
		add_action('admin_print_scripts', array('wpshop_init', 'admin_print_js'));

		$page = !empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		$post_type = !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		$action = !empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		$post = !empty( $_GET['post'] ) ? (array) $_GET['post'] : '';
		$taxonomy = !empty( $_GET['taxonomy'] ) ? sanitize_text_field( $_GET['taxonomy'] ) : '';
		if((isset($page) && substr($page, 0, 7) == 'wpshop_') || (isset($page) && $page == 'wps-installer' ) || (isset($post_type) && substr($post_type, 0, 7) == 'wpshop_') || !empty($post) || (isset($page) && $page==WPSHOP_NEWTYPE_IDENTIFIER_GROUP) || (isset($taxonomy) && ($taxonomy == WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES))){
			/*	Include the different javascript	*/
// 			add_action('admin_init', array('wpshop_init', 'admin_js'));
			add_action( 'admin_enqueue_scripts', array('wpshop_init', 'admin_js') );
			add_action('admin_footer', array('wpshop_init', 'admin_js_footer'));

			/*	Include the different css	*/
			//add_action('admin_init', array('wpshop_init', 'admin_css'));
		}
		add_action('admin_init', array('wpshop_init', 'admin_css'));

		/*	Include the different css	*/
		if ( !is_admin() ) {
			add_action('wp_print_styles', array('wpshop_init', 'frontend_css'));
			add_action('wp_print_scripts', array('wpshop_init', 'frontend_js_instruction'));
		}

		if (isset($page,$action) && $page=='wpshop_doc' && $action=='edit') {
			add_action('admin_init', array('wpshop_doc', 'init_wysiwyg'));
		}

		// RICH TEXT EDIT INIT
		add_action('init', array('wpshop_display','wpshop_rich_text_tags'), 9999);
		add_action('init', array('wpshop_display','wps_hide_admin_bar_for_customers'), 9999 );

		/**	Adda custom class to the admin body	*/
		add_filter( 'admin_body_class', array( 'wpshop_init', 'admin_body_class' ) );
		add_filter( 'site_transient_update_plugins', array( 'wpshop_init', 'site_transient_update_plugins' ) );
	}

	/**
	 *	Admin menu creation
	 */
	public static function admin_menu(){
		global $menu;

		/*	Get current plugin version	*/
		$wpshop_shop_type = get_option('wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE);

		$wpshop_catalog_menu_order = 34;

		$menu[ $wpshop_catalog_menu_order-1 ] = array( '', 'read', 'separator-wpshop_dashboard', '', 'wp-menu-separator wpshop_dashboard' );

		/*	Main menu creation	*/
		global $wps_dashboard_ctr;
		add_menu_page(__( 'Dashboard', 'wpshop' ), __( 'Shop', 'wpshop' ), 'wpshop_view_dashboard', WPSHOP_URL_SLUG_DASHBOARD, array( $wps_dashboard_ctr, 'display_dashboard' ), 'dashicons-admin-home', $wpshop_catalog_menu_order);
		add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Dashboard', 'wpshop' ), __('Dashboard', 'wpshop'), 'wpshop_view_dashboard', WPSHOP_URL_SLUG_DASHBOARD, array( $wps_dashboard_ctr, 'display_dashboard' ));

		/*	Add eav model menus	*/
		add_menu_page(__( 'Entities', 'wpshop' ), __( 'Entities', 'wpshop' ), 'wpshop_view_dashboard', WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, array('wpshop_display', 'display_page'), 'dashicons-universal-access-alt', $wpshop_catalog_menu_order + 1);
		add_submenu_page(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, __( 'Attributes', 'wpshop' ), __('Attributes', 'wpshop'), 'wpshop_view_attributes', WPSHOP_URL_SLUG_ATTRIBUTE_LISTING, array('wpshop_display','display_page'));
		add_submenu_page(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, __( 'Attributes groups', 'wpshop' ), __('Attributes groups', 'wpshop'), 'wpshop_view_attribute_set', WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING, array('wpshop_display','display_page'));

		/*	Add messages menus	*/
		//add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __( 'Messages', 'wpshop' ), __( 'Messages', 'wpshop'), 'wpshop_view_messages', 'edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE);
		/*	Add group menu	*/
// 		if( in_array ( long2ip ( ip2long ( $_SERVER["REMOTE_ADDR"] ) ), unserialize( WPSHOP_DEBUG_MODE_ALLOWED_IP ) ) )add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Groups', 'wpshop'), __('Groups', 'wpshop'), 'wpshop_view_groups', WPSHOP_NEWTYPE_IDENTIFIER_GROUP, array('wps_customer_group','display_page'));

		/*	Add a menu for plugin tools	*/
// 		if (WPSHOP_DISPLAY_TOOLS_MENU) {
			add_management_page( __('Wpshop - Tools', 'wpshop' ), __('Wpshop - Tools', 'wpshop' ), 'wpshop_view_tools_menu', WPSHOP_URL_SLUG_TOOLS , array('wpshop_tools', 'main_page'));
// 		}

		/*	Add the options menu	*/
		add_options_page(__('WPShop options', 'wpshop'), __('Shop', 'wpshop'), 'wpshop_view_options', WPSHOP_URL_SLUG_OPTION, array('wpshop_options', 'option_main_page'));

		//echo '<pre>'; print_r($menu); echo '</pre>';
	}

	public static function admin_menu_order($menu_order) {
		// Initialize our custom order array
		$wpshop_menu_order = array();

		// Get the index of our custom separator
		$separator = array_search( 'separator-wpshop_dashboard', $menu_order );
		$product = array_search( 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $menu_order );
		$order = array_search( 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER, $menu_order );
		$customers = array_search( 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $menu_order );
		//$entities = array_search( 'admin.php?page=' . WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, $menu_order );
		$entities = array_search( WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, $menu_order );

		unset( $menu_order[$separator] );
		unset( $menu_order[$product] );
		unset( $menu_order[$order] );
		unset( $menu_order[$customers] );
		unset( $menu_order[$entities] );

		// Loop through menu order and do some rearranging
		foreach ( $menu_order as $index => $item ) :
			if ( 'wpshop_dashboard' == $item ) :
				$wpshop_menu_order[] = 'separator-wpshop_dashboard';
				$wpshop_menu_order[] = $item;
				$wpshop_menu_order[] = 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
				$wpshop_menu_order[] = 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER;
				$wpshop_menu_order[] = 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;
				$wpshop_menu_order[] = WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES;
			elseif ( !in_array( $item, array( 'separator-wpshop_dashboard' ) ) ) :
				$wpshop_menu_order[] = $item;
			endif;
		endforeach;

		// Return order
		return $wpshop_menu_order;
	}

	public static function admin_custom_menu_order() {
		return current_user_can( 'manage_options' );
	}

	/**
	 *	Admin javascript "header script" part definition
	 */
	public static function admin_print_js() {

		/*	Désactivation de l'enregistrement automatique pour certains type de post	*/
		global $post;
		if ( $post && ( (get_post_type($post->ID) === WPSHOP_NEWTYPE_IDENTIFIER_ORDER) ||  (get_post_type($post->ID) === WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE)
				|| (get_post_type($post->ID) === WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES) || (get_post_type($post->ID) === WPSHOP_NEWTYPE_IDENTIFIER_COUPON) ) ) {
			wp_dequeue_script('autosave');
		}

		$entity_to_search = !empty( $_GET['entity_to_search'] ) ? sanitize_text_field( $_GET['entity_to_search'] ) : '';
//	var WPSHOP_AJAX_FILE_URL = "'.WPSHOP_AJAX_FILE_URL.'";
		echo '
<script type="text/javascript">

	var WPSHOP_MEDIAS_ICON_URL = "'.WPSHOP_MEDIAS_ICON_URL.'";
	var WPSHOP_PRODUCT_PRICE_PILOT = "'.WPSHOP_PRODUCT_PRICE_PILOT.'";
	var WPSHOP_PRODUCT_PRICE_HT = "' . WPSHOP_PRODUCT_PRICE_HT . '";
	var WPSHOP_PRODUCT_PRICE_TAX = "' . WPSHOP_PRODUCT_PRICE_TAX . '";
	var WPSHOP_PRODUCT_PRICE_TTC = "' . WPSHOP_PRODUCT_PRICE_TTC . '";
	var WPSHOP_PRODUCT_SPECIAL_PRICE = "' . WPSHOP_PRODUCT_SPECIAL_PRICE . '";
	var WPSHOP_PRODUCT_PRICE_TAX_AMOUNT = "' . WPSHOP_PRODUCT_PRICE_TAX_AMOUNT . '";
	var WPSHOP_ADMIN_URL = "' . admin_url() . '";
	var WPSHOP_NEWTYPE_IDENTIFIER_ORDER = "' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER . '";
	var WPSHOP_NEWTYPE_IDENTIFIER_COUPON = "' . WPSHOP_NEWTYPE_IDENTIFIER_COUPON . '";
	var WPSHOP_NEWTYPE_IDENTIFIER_GROUP = "' . WPSHOP_NEWTYPE_IDENTIFIER_GROUP . '";
	var WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE = "' . WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE . '";
	var WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT = "' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '";
	var WPSHOP_JS_VAR_ADD_PICTURE = "' . __('Add a picture to category', 'wpshop') . '";
	var WPSHOP_JS_VAR_ADD_LOGO = "' . __('Upload your logo', 'wpshop') . '";
	var WPSHOP_NEWOPTION_CREATION_NONCE = "' . wp_create_nonce("wpshop_new_option_for_attribute_creation") . '";

	var WPSHOP_ADD_TEXT = "'.__('Add', 'wpshop').'";
	var WPSHOP_CREATE_TEXT = "'.__('Create', 'wpshop').'";
	var WPSHOP_SAVE_PRODUCT_OPTIONS_PARAMS = "'.__('Save parameters', 'wpshop').'";

	var WPSHOP_NEW_OPTION_IN_LIST_EMPTY = "'.__('You don\'t specify all needed file', 'wpshop').'";
	var WPSHOP_IS_NOT_ALLOWED_SHIPPING_COUNTRY = "'.__('Sorry ! You can\'t order on this shop, because we don\'t ship in your country.', 'wpshop').'";
	var WPSHOP_CONFIRM_BEFORE_GENERATE_INVOICE = "'.__('If you generate the invoice, you will cannot modify this order later. Are you sure to do this action ?', 'wpshop').'";
	var WPSHOP_NEW_OPTION_ALREADY_EXIST_IN_LIST = "'.__('The value you entered already exist in list', 'wpshop').'";
	var WPSHOP_SURE_TO_DELETE_ATTR_OPTION_FROM_LIST = "'.__('Are you sure you want to delete this option from list?', 'wpshop').'";
	var WPSHOP_DEFAULT_VALUE = "'.__('Set as default value', 'wpshop').'";
	var WPSHOP_MSG_INVOICE_QUOTATION = "' . __('Are you sure you want to charge this order? You\'ll be unable to modify the content after this operation', 'wpshop') . '";
	var WPSHOP_MSG_IGNORE_CONFIGURATION = "' . __('If you continue without install the plugin. Your products won\'t be purchasable', 'wpshop') . '";
	var WPSHOP_MSG_CONFIRM_THUMBNAIL_DELETION = "' . __('Are you sure you want to delete this thumbnail?', 'wpshop') . '";
	var WPSHOP_CHOSEN_NO_RESULT = "' . __('No result found for your search', 'wpshop') . '";
	var WPSHOP_CHOSEN_SELECT_FROM_MULTI_LIST = "' . __('Select values from list', 'wpshop') . '";
	var WPSHOP_CHOSEN_SELECT_FROM_LIST = "' . __('Select an Option', 'wpshop') . '";
	var WPSHOP_AJAX_CHOSEN_KEEP_TYPING = "' . __('Keep typing for search launching', 'wpshop') . '";
	var WPSHOP_AJAX_CHOSEN_SEARCHING = "' . __('Searching in progress for', 'wpshop') . '";
	var WPSHOP_MSG_CONFIRM_ADDON_DEACTIVATION = "'.__('Are you sure you want to deactivate this addon?', 'wpshop').'";
	var WPS_DELETE_SHOP_LOGO_MSG = "'.__('Are you sure you want to delete this logo?', 'wpshop').'";
	var WPS_DEFAULT_LOGO = "'.WPSHOP_MEDIAS_IMAGES_URL . 'no_picture.png";

	var WPSHOP_NO_ATTRIBUTES_SELECT_FOR_VARIATION = "'.__('You have to select at least one attribute for creating a new variation', 'wpshop').'";

	var WPSHOP_CHOSEN_ATTRS = {disable_search_threshold: 5, no_results_text: WPSHOP_CHOSEN_NO_RESULT, placeholder_text_single : WPSHOP_CHOSEN_SELECT_FROM_LIST, placeholder_text_multiple : WPSHOP_CHOSEN_SELECT_FROM_MULTI_LIST};

	var WPSHOP_TEMPLATES_URL = "'.WPSHOP_TEMPLATES_URL.'";
	var WPSHOP_BUTTON_DESCRIPTION = "'.__('Insert shortcode into page content', 'wpshop').'";
	var WPSHOP_WYSIWYG_MENU_TITLE_PRODUCT_LISTING = "'.__('Product listing', 'wpshop').'";
	var WPSHOP_WYSIWYG_PRODUCT_LISTING_BY_PID_TITLE = "'.__('By product ID', 'wpshop').'";
	var WPSHOP_WYSIWYG_PRODUCT_LISTING_BY_ATTRIBUTE_TITLE = "'.__('By attribute value', 'wpshop').'";
	var WPSHOP_WYSIWYG_MENU_TITLE_CATEGORIES = "'.__('WPShop categories', 'wpshop').'";
	var WPSHOP_WYSIWYG_MENU_TITLE_ATTRIBUTE_VALUE = "'.__('Attribute value', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_TITLE = "'.__('Custom message content', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_FIRST_NAME = "'.__('Customer first name', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_LAST_NAME = "'.__('Customer last name', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_EMAIL = "'.__('Customer email', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_ID = "'.__('Order identifer', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_PAYPAL_TRANSACTION_ID = "'.__('Paypal transaction ID', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_CONTENT = "'.__('Order content', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_CUSTOMER_PERSONNAL_INFORMATIONS = "'.__('Customer personnal informations', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_ADDRESSES = "'.__('Order addresses', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_BILLING_ORDER_ADDRESS = "'.__('Billing order address', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_SHIPPING_ORDER_ADDRESS = "'.__('Shipping order address', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_SHIPPING_METHOD = "'.__('Shipping method', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_PAYMENT_METHOD = "'.__('order payment_method', 'wpshop').'";
	var WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_COMMENT = "'.__('Order customer comment', 'wpshop').'";
	var WPSHOP_CUSTOM_TAGS_TITLE = "'.__('Wpshop custom tags', 'wpshop').'";
	var WPSHOP_CUSTOM_TAGS_CART = "'.__('Cart', 'wpshop').'";
	var WPSHOP_CUSTOM_TAGS_CART_MINI = "'.__('Cart widget', 'wpshop').'";
	var WPSHOP_CUSTOM_TAGS_CHECKOUT = "'.__('Checkout', 'wpshop').'";
	var WPSHOP_CUSTOM_TAGS_ACCOUNT = "'.__('Customer account', 'wpshop').'";
	var WPSHOP_CUSTOM_TAGS_SHOP = "'.__('Shop', 'wpshop').'";
	var WPSHOP_CUSTOM_TAGS_ADVANCED_SEARCH = "'.__('Advanced search', 'wpshop').'";
	var WPSHOP_CANCEL_ORDER_CONFIRM_MESSAGE = "'.__('Do you want to cancel this order ?', 'wpshop').'";
	var WPSHOP_REFUND_ORDER_CONFIRM_MESSAGE = "'.__('Do you want to refund this order ?', 'wpshop').'";
	var WPSHOP_RESEND_ORDER_CONFIRM_MESSAGE = "'.__('Do you want to resend this order to customer ?', 'wpshop').'";
	var WPSHOP_SEARCH_IN_ORDER_EXPLAIN_MESSAGE = "'.__('You want to search in orders', 'wpshop').'";
	var WPSHOP_SEARCH_IN_ORDER_CHOICE_CUSTOMER = "'.__('a customer', 'wpshop').'";
	var WPSHOP_SEARCH_IN_ORDER_CHOICE_PRODUCT = "'.__('a product', 'wpshop').'";
	var WPSHOP_SEARCH_IN_ORDER_USER_CHOICE = "'.( (!empty($entity_to_search) ) ? $entity_to_search : 'customer' ).'";
	var WPSHOP_DELETE_ADDRESS_CONFIRMATION = "'.__( 'Do you really want to delete this address', 'wpshop' ).'";

	var wps_options_shipping_weight_for_custom_fees = "'.__( 'You must enter a weight', 'wpshop' ).'";
	var wps_options_country_choose_for_custom_fees = "'.__( 'You must a country for custom fees saving', 'wpshop' ).'";
	var wps_options_country_postcode_choose_for_custom_fees = "'.__( 'You must choose a country or write a postcode.', 'wpshop' ).'";
	var wps_an_error_occured = "'.__( 'An error occured', 'wpshop' ).'";
</script>';
	}

	/**
	 *	Admin javascript "footer script" part definition
	 */
	public static function admin_js_footer() {
		global $wp_version;
		ob_start();
		include(WPSHOP_JS_DIR . 'pages/wpshop_product.js');
		$wpshop_product_js = ob_get_contents();
		ob_end_clean();

		echo '<script type="text/javascript">
			var wp_version = "'.$wp_version.'";
			'.$wpshop_product_js.'
		</script>';
	}

	/**
	 *	Admin javascript "file" part definition
	 */
	static function admin_js() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-form');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script('wpshop_main_function_js', WPSHOP_JS_URL . 'main_function.js', '', WPSHOP_VERSION);
		wp_enqueue_script('wpshop_main_js', WPSHOP_JS_URL . 'main.js', '', WPSHOP_VERSION);
		wp_enqueue_script('wpshop_jq_datatable', WPSHOP_JS_URL . 'jquery-libs/jquery.dataTables.min.js', '', WPSHOP_VERSION);
		wp_enqueue_script('wpshop_jquery_chosen',  WPSHOP_JS_URL . 'jquery-libs/chosen.jquery.min.js', '', WPSHOP_VERSION);
		wp_enqueue_script('jquery-effects-core');
		wp_enqueue_script('jquery-effects-highlight');

		$page = !empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		/*	Include specific js file for the current page if existing	*/
		if(isset($page) && is_file(WPSHOP_JS_DIR . 'pages/' . $page . '.js')){
			wp_enqueue_script($page . '_js', WPSHOP_JS_URL . 'pages/' . $page . '.js', '', WPSHOP_VERSION);
		}
		if((isset($page) && ($page == 'wpshop_dashboard'))) {
			wp_enqueue_script($page . '_js', WPSHOP_JS_URL . 'pages/' . WPSHOP_URL_SLUG_OPTION . '.js', '', WPSHOP_VERSION);
			wp_register_style($page . '_css', WPSHOP_CSS_URL . 'pages/' . WPSHOP_URL_SLUG_OPTION . '.css', '', WPSHOP_VERSION);
			wp_enqueue_style($page . '_css');
		}
	}

	/**
	*	Admin javascript "header script" part definition
	*/
	function admin_css_head() {
		ob_start();
		include(WPSHOP_CSS_DIR . 'pages/wpshop_product.css');
		$wpshop_product_css = ob_get_contents();
		ob_end_clean();
?>
<style type="text/css" >
<?php echo $wpshop_product_css; ?>
</style>
<?php
	}

	/**
	 *
	 * @param array $classes
	 * @return string
	 */
	public static function admin_body_class( $classes ) {
		global $post;

		if ( !empty($post->ID) ) {
			$post_type = get_post_type( $post->ID );
			if ( is_admin() && in_array( $post_type, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, WPSHOP_NEWTYPE_IDENTIFIER_ORDER, WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE, WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, WPSHOP_NEWTYPE_IDENTIFIER_COUPON, WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS) ) ) {
				$classes .= ' wpshop-admin-body wpshop-admin-post-type-' . $post_type;
			}
		}

		return $classes;
	}

	/**
	 *	Admin javascript "file" part definition
	 */
	public static function wpshop_css() {
// 		wp_register_style('wpshop_menu_css', WPSHOP_CSS_URL . 'wpshop.css', '', WPSHOP_VERSION);
// 		wp_enqueue_style('wpshop_menu_css');
	}

	/**
	 *	Admin javascript "file" part definition
	 */
	static function admin_css() {
		wp_register_style('wpshop_main_css', WPSHOP_CSS_URL . 'main.css', '', WPSHOP_VERSION);
		wp_enqueue_style('wpshop_main_css');

		// Chosen
		wp_register_style('wpshop_chosen_css', WPSHOP_CSS_URL . 'jquery-libs/chosen.css', '', WPSHOP_VERSION);
		wp_enqueue_style('wpshop_chosen_css');

		/*	Include specific css file for the current page if existing	*/
		$page = !empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if(isset($page) && is_file(WPSHOP_CSS_DIR . 'pages/' . $page . '.css')){
			wp_register_style($page . '_css', WPSHOP_CSS_URL . 'pages/' . $page . '.css', '', WPSHOP_VERSION);
			wp_enqueue_style($page . '_css');
		}

		wp_register_style('wpshop_default_admin_wps_style_css', WPSHOP_TEMPLATES_URL . 'wpshop/css/wps_style_old.css', '', WPSHOP_VERSION);
		wp_enqueue_style('wpshop_default_admin_wps_style_css');
	}

	/**
	 *	Admin css "file" part definition
	 */
	public static function frontend_css() {

		//wp_register_style('wpshop_default_frontend_main_css', WPSHOP_TEMPLATES_URL . 'wpshop/css/frontend_main.css', '', WPSHOP_VERSION);
		//wp_enqueue_style('wpshop_default_frontend_main_css');

		wp_register_style('wpshop_frontend_main_css', wpshop_display::get_template_file('frontend_main.css', WPSHOP_TEMPLATES_URL, 'wpshop/css', 'output', true), '', WPSHOP_VERSION);
		wp_enqueue_style('wpshop_frontend_main_css');

// 		wp_register_style('wpshop_dialog_box_css', wpshop_display::get_template_file('wpshop_dialog_box.css', WPSHOP_TEMPLATES_URL, 'wpshop/css', 'output'), '', WPSHOP_VERSION);
// 		wp_enqueue_style('wpshop_dialog_box_css');
		/*	Include Librairies directly from plugin for librairies not modified	*/

		wp_register_style('wpshop_jquery_fancybox', WPSHOP_CSS_URL . 'jquery-libs/jquery.fancybox-1.3.4.css', '', WPSHOP_VERSION);
		wp_enqueue_style('wpshop_jquery_fancybox');

// 		wp_register_style('wpshop_jquery_ui', WPSHOP_CSS_URL . 'jquery-ui.css', '', WPSHOP_VERSION);
// 		wp_enqueue_style('wpshop_jquery_ui');

// 		wp_register_style('wpshop_jquery_ui_menu', WPSHOP_CSS_URL . 'jquery-libs/jquery-ui-1.10.1.custom.css', '', WPSHOP_VERSION);
// 		wp_enqueue_style('wpshop_jquery_ui_menu');

// 		wp_register_style('wpshop_jquery_ui_menu_2', WPSHOP_CSS_URL . 'jquery-libs/jquery-ui-1.10.1.custom.min.css', '', WPSHOP_VERSION);
// 		wp_enqueue_style('wpshop_jquery_ui_menu_2');

// 		wp_register_style('wpshop_jquery_jqzoom_css', wpshop_display::get_template_file('jquery.jqzoom.css', WPSHOP_TEMPLATES_URL, 'wpshop/css', 'output'), '', WPSHOP_VERSION);
// 		wp_enqueue_style('wpshop_jquery_jqzoom_css');

		wp_register_style('wpshop_default_wps_style_css', WPSHOP_TEMPLATES_URL . 'wpshop/css/wps_style.css', '', WPSHOP_VERSION);
		wp_enqueue_style('wpshop_default_wps_style_css');

		if( file_exists( get_stylesheet_directory().'/wpshop/css/wps_style.css' ) ) {
			wp_deregister_style( 'wpshop_default_wps_style_css' );
			wp_register_style('wps_style_css', wpshop_display::get_template_file('wps_style.css', WPSHOP_TEMPLATES_URL, 'wpshop/css', 'output', true), '', WPSHOP_VERSION);
			wp_enqueue_style('wps_style_css', 11);
		}

		/** OWL CAROUSSEL **/
		wp_register_style('wps_owl_caroussel', wpshop_display::get_template_file('owl.carousel.css', WPSHOP_TEMPLATES_URL, 'wpshop/css', 'output'), '', WPSHOP_VERSION);
		wp_enqueue_style('wps_owl_caroussel');

		wp_register_style('wps_owl_caroussel_transitions', wpshop_display::get_template_file('owl.transitions.css', WPSHOP_TEMPLATES_URL, 'wpshop/css', 'output'), '', WPSHOP_VERSION);
		wp_enqueue_style('wps_owl_caroussel_transitions');

			wp_enqueue_style( 'dashicons' );
	}

	/**
	 *	Frontend javascript caller
	 */
	public static function frontend_js_instruction() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-form');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-effects-core');
		wp_enqueue_script('jquery-effects-highlight');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('wpshop_frontend_main_js', wpshop_display::get_template_file('frontend_main.js', WPSHOP_TEMPLATES_URL, 'wpshop/js', 'output'), '', WPSHOP_VERSION, true);
// 		wp_enqueue_script('wpshop_jquery_jqzoom_core_js', wpshop_display::get_template_file('jquery.jqzoom-core.js', WPSHOP_TEMPLATES_URL, 'wpshop/js', 'output'), '', WPSHOP_VERSION, true);
		wp_enqueue_script('fancyboxmousewheel',WPSHOP_JS_URL . 'fancybox/jquery.mousewheel-3.0.4.pack.js', '', WPSHOP_VERSION, true);
		wp_enqueue_script('fancybox', WPSHOP_JS_URL . 'fancybox/jquery.fancybox-1.3.4.pack.js', '', WPSHOP_VERSION, true);
		wp_enqueue_script('jquery_address', WPSHOP_JS_URL . 'jquery-libs/jquery.address-1.5.min.js', '', WPSHOP_VERSION);
		wp_enqueue_script('wps_api', wpshop_display::get_template_file('wps-api.js', WPSHOP_TEMPLATES_URL, 'wpshop/js', 'output'), '', WPSHOP_VERSION, true);
		wp_enqueue_script('jquery.nouislider.min', wpshop_display::get_template_file('jquery.nouislider.min.js', WPSHOP_TEMPLATES_URL, 'wpshop/js', 'output'), '', WPSHOP_VERSION, true);
		wp_enqueue_script('wps_owl_caroussel', wpshop_display::get_template_file('owl.carousel.min.js', WPSHOP_TEMPLATES_URL, 'wpshop/js', 'output'), '', WPSHOP_VERSION, true);
?>
<script type="text/javascript">
	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	var CURRENT_PAGE_URL = "<?php !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '' ?>";
	var WPSHOP_REQUIRED_FIELD_ERROR_MESSAGE = "<?php _e('Every fields marked as required must be filled', 'wpshop'); ?>";
	var WPSHOP_INVALID_EMAIL_ERROR_MESSAGE = "<?php _e('Email invalid', 'wpshop'); ?>";
	var WPSHOP_UNMATCHABLE_PASSWORD_ERROR_MESSAGE = "<?php _e('Both passwords must match', 'wpshop'); ?>";
	var WPSHOP_CHOSEN_NO_RESULT = "<?php _e('No result found for your search', 'wpshop'); ?>";
	var WPSHOP_CHOSEN_SELECT_FROM_MULTI_LIST = "<?php _e('Select values from list', 'wpshop'); ?>";
	var WPSHOP_CHOSEN_SELECT_FROM_LIST = "<?php _e('Select an Option', 'wpshop'); ?>";
	var WPSHOP_AJAX_CHOSEN_KEEP_TYPING = "<?php _e('Keep typing for search launching', 'wpshop'); ?>";
	var WPSHOP_AJAX_CHOSEN_SEARCHING = "<?php _e('Searching in progress for', 'wpshop'); ?>";
	var WPSHOP_PRODUCT_VARIATION_REQUIRED_MSG = "<div id='wpshop_product_add_to_cart_form_result' class='error_bloc' ><?php _e('Please select all required value', 'wpshop'); ?></div>";
	var WPSHOP_ACCEPT_TERMS_OF_SALE = "<?php _e('You must accept the terms of sale.', 'wpshop'); ?>";
	var WPSHOP_MUST_CHOOSE_SHIPPING_MODE = "<?php _e('You must to choose a shipping mode', 'wpshop'); ?>";
	var WPSHOP_NO_SHIPPING_MODE_AVAILABLE = "<?php _e('You can\'t order because no shipping mode is available.', 'wpshop'); ?>";
	var WPSHOP_LOADER_ICON_JQUERY_ADDRESS = "<img src=\"<?php echo WPSHOP_LOADING_ICON; ?>\" alt=\"Loading...\" />";
	var WPSHOP_CONFIRM_DELETE_ADDRESS = "<?php _e( 'Do you really want to delete this address ?', 'wpshop' ); ?>";
	var wps_speed_slideUpDown = 200;
	var MODAL_URL = '<?php echo WPSHOP_TEMPLATES_URL; ?>wpshop/modal.php';
</script>
<?php
	}

	/**
	 *	Function called on plugin initialisation allowing to declare the new types needed by our plugin
	 *	@see wpshop_products::create_wpshop_products_type();
	 *	@see wpshop_categories::create_product_categories();
	 */
	public static function add_new_wp_type() {
		$wpshop_shop_type = get_option('wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE);

		/*	Creation du type d'element Entité pour wpshop	*/
		wpshop_entities::create_wpshop_entities_type();
		add_action('add_meta_boxes', array('wpshop_entities', 'add_meta_boxes'));
		add_action('save_post', array('wpshop_entities', 'save_entity_type_custom_informations'));
		add_filter('manage_posts_columns', array('wpshop_entities', 'custom_columns_header'), 10, 2);
		add_filter('manage_posts_custom_column', array('wpshop_entities', 'custom_columns_content'), 10, 2);
// 			add_action('quick_edit_custom_box', array('wpshop_attributes', 'quick_edit'), 10, 2);
			add_action('bulk_edit_custom_box', array('wpshop_attributes', 'bulk_edit'), 10, 2);

		/*	Creation des types personnalisé à partir des entités créées	*/
		wpshop_entities::create_wpshop_entities_custom_type();

		/*	Add wpshop product type and add a new meta_bow into product creation/edition interface for regrouping title and editor in order to sort interface	*/
		wpshop_products::create_wpshop_products_type();
		add_filter('hidden_meta_boxes', array('wpshop_products', 'hidden_meta_boxes'), 10, 3);
		add_action('add_meta_boxes', array('wpshop_products', 'add_meta_boxes'));
		//add_action('admin_menu', array('wpshop_products', 'admin_menu'), 10);
		add_filter('post_link', array('wpshop_products', 'set_product_permalink'), 10, 3);
		add_filter('post_type_link', array('wpshop_products', 'set_product_permalink'), 10, 3);
		add_action('pre_get_posts', array('wpshop_products', 'set_product_request_by_name'));
		$product_class = new wpshop_products();
		add_action('save_post', array($product_class, 'save_product_custom_informations'));
		/*	Modification des paramètres de variation quand ils ne sont pas configurés individuellement	*/
		add_filter('pre_update_option_' . 'wpshop_catalog_product_option', array('wpshop_products', 'update_wpshop_catalog_product_option'), 10, 2 );

		/*	Add wpshop product category term	*/
		wpshop_categories::create_product_categories();

		/*	Add wpshop message term	*/


		if ( $wpshop_shop_type == 'sale' ) {
			/*	Add wpshop orders term	*/
			wpshop_orders::create_orders_type();
			add_action('add_meta_boxes', array('wpshop_orders', 'add_meta_boxes'));
			add_action('manage_'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'_posts_custom_column',  array('wpshop_orders', 'orders_custom_columns'), 10, 2);
			add_filter('manage_edit-'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'_columns', array('wpshop_orders', 'orders_edit_columns'));
			add_action('restrict_manage_posts', array('wpshop_orders', 'list_table_filters') );
			add_filter('parse_query', array('wpshop_orders', 'list_table_filter_parse_query') );
		}

		$args = array(
			'public'   => true,
			'_builtin' => false
		);
		$output = 'objects'; // names or objects, note names is the default
		$operator = 'or'; // 'and' or 'or'

		$wp_types_original=get_post_types($args,$output,$operator);
		foreach ($wp_types_original as $type => $type_definition):
			$wp_types[$type] = $type_definition->labels->name;
		endforeach;
		$to_exclude=unserialize(WPSHOP_INTERNAL_TYPES_TO_EXCLUDE);
		if(!empty($to_exclude)):
			foreach($to_exclude as $excluded_type):
				if(isset($wp_types[$excluded_type]))unset($wp_types[$excluded_type]);
			endforeach;
		endif;
		DEFINE('WPSHOP_INTERNAL_TYPES', serialize(array_merge($wp_types, array('users' => __('Users', 'wpshop')))));
	}

	/**
	 * Send mail when new version is available
	 */
	public static function site_transient_update_plugins( $option ) {
		if ( isset( $option->response[ WPSHOP_PLUGIN_NAME ] ) ) {
			global $wpdb;
			$message_mdl = new wps_message_mdl();
			$admin_new_version_message = get_option( 'WPSHOP_NEW_VERSION_ADMIN_MESSAGE', null );
			$shop_admin_email_option = get_option( 'wpshop_emails' );
			$shop_admin_id = $wpdb->get_var( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->users . ' WHERE user_email = %s', $shop_admin_email_option['contact_email'] ) );
			if ( ! is_null( $shop_admin_id ) && ! is_null( $admin_new_version_message ) ) {
				$messages_histo = $message_mdl->get_messages_histo( $admin_new_version_message, $shop_admin_id );
				if ( ! empty( $messages_histo ) ) {
					foreach ( $messages_histo as $messages_histo_group ) {
						foreach ( $messages_histo_group as $messages_histo ) {
							if ( strpos( $messages_histo['mess_title'], $option->response[ WPSHOP_PLUGIN_NAME ]->new_version ) !== false
							|| strpos( $messages_histo['mess_message'], $option->response[ WPSHOP_PLUGIN_NAME ]->new_version ) !== false ) {
								return $option;
							}
						}
					}
				}
				$wps_message = new wps_message_ctr();
				$wps_message->wpshop_prepared_email( $shop_admin_email_option['contact_email'], 'WPSHOP_NEW_VERSION_ADMIN_MESSAGE', array(
					'wpshop_version' => $option->response[ WPSHOP_PLUGIN_NAME ]->new_version,
				) );
			}
		}
		return $option;
	}
}
