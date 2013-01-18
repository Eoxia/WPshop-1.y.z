<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Plugin librairies include file.
 *
 * This file will be called in every other file of the plugin and will include every library needed by the plugin to work correctly. If a file is needed in only one script prefer direct inclusion
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage includes
 */

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************/

include_once(WPSHOP_INCLUDES_DIR . 'wpshop_ajax.php');

include_once(WPSHOP_LIBRAIRIES_DIR . 'install.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'init.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'tools.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'permissions.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'options/options.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'notices.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'shortcodes.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'messages.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'dashboard.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'search.class.php');

/* Customers management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'customers/signup.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'customers/account.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'customers/address.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'customers/customer_custom_list_table.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'customers/customer.class.php');
$customer_obj = new wpshop_customer();

/* Groups management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'customers/groups.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'customers/wp_list_custom_groups.class.php');

/* Purchase management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'purchase/cart.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'purchase/checkout.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'purchase/orders.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'purchase/coupons.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'purchase/shipping.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'purchase/wp_list_custom_entities_customers.php');


/* Documentation management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'doc.class.php');

/* Webservice management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'webservice.class.php');

/* Database management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'db/db_structure_definition.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'db/db_data_definition.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'db/database.class.php');

/* Payments management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'payments/payment.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'payments/paypal.class.php');
// If the CIC payment method is active
$wpshop_paymentMethod = get_option('wpshop_paymentMethod');
if(WPSHOP_PAYMENT_METHOD_CIC || !empty($wpshop_paymentMethod['cic'])){
	include_once(WPSHOP_LIBRAIRIES_DIR . 'payments/cic.class.php');
}

/* PDF management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'pdf/fpdf.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'pdf/fpdf_extends.class.php');

/* Display management */
if ( !class_exists('WP_List_Table') ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
include_once(WPSHOP_LIBRAIRIES_DIR . 'display/display.class.php');

include_once(WPSHOP_LIBRAIRIES_DIR . 'display/form.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'display/form_management.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'display/widgets/categories.widget.php');
add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Wpshop_Product_categories");'));
/*	Add needed file to the current theme	*/
add_action('admin_init', array('wpshop_display', 'check_template_file'));

/* Files management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'documents/documents.class.php');
add_action('admin_head', array('wpshop_documents', 'galery_manager_css'));
add_filter('attachment_fields_to_edit', array('wpshop_documents', 'attachment_fields'), 11, 2);
add_filter('gettext', array('wpshop_documents', 'change_picture_translation'), 11, 2);

/* Catalog management */
include_once(WPSHOP_LIBRAIRIES_DIR . 'catalog/products.class.php');
include_once(WPSHOP_LIBRAIRIES_DIR . 'catalog/categories.class.php');
add_filter('manage_edit-' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_columns', array('wpshop_categories', 'category_manage_columns'));
add_filter('manage_' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_custom_column', array('wpshop_categories', 'category_manage_columns_content'), 10, 3);
add_action(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_edit_form_fields', array('wpshop_categories', 'category_edit_fields'));
add_action('created_' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array('wpshop_categories', 'category_fields_saver'), 10, 2);
add_action('edited_' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array('wpshop_categories', 'category_fields_saver'), 10, 2);

/* EAV management */
include(WPSHOP_LIBRAIRIES_DIR . 'eav/wp_list_custom_attributes.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'eav/attributes.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'eav/attributes_unit.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'eav/wp_list_custom_attributes_set.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'eav/attributes_set.class.php');
include(WPSHOP_LIBRAIRIES_DIR . 'eav/entities.class.php');

add_action( 'user_register', array('wpshop_entities', 'create_entity_customer_when_user_is_created') );

