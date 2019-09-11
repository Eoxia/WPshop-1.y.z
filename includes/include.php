<?php if ( ! defined( 'ABSPATH' ) ) { exit;
}

// End if().
if ( ! defined( 'WPSHOP_VERSION' ) ) {
	die( __( 'Access is not allowed by this way', 'wpshop' ) );
}

/**
 * Plugin librairies include file.
 *
 * This file will be called in every other file of the plugin and will include every library needed by the plugin to work correctly. If a file is needed in only one script prefer direct inclusion
 *
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage includes
 */

// End if().
include_once( WPSHOP_DIR . '/core/module_management/module_management.php' );
include_once( WPSHOP_INCLUDES_DIR . 'wpshop_ajax.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'install.class.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'permissions.class.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'options/options.class.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'notices.class.php' );

include_once( WPSHOP_LIBRAIRIES_DIR . 'parsedown/parsedown.php' );

/* Purchase management */
include_once( WPSHOP_LIBRAIRIES_DIR . 'purchase/checkout.class.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'purchase/orders.class.php' );
/* Database management */
include_once( WPSHOP_LIBRAIRIES_DIR . 'db/db_structure_definition.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'db/db_data_definition.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'db/database.class.php' );
/* Payments management */
include_once( WPSHOP_LIBRAIRIES_DIR . 'payments/payment.class.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'payments/paypal.class.php' );
// If the CIC payment method is active
$wpshop_paymentMethod = get_option( 'wps_payment_mode' );
if ( WPSHOP_PAYMENT_METHOD_CIC && ! empty( $wpshop_paymentMethod ) && ! empty( $wpshop_paymentMethod['mode'] ) && ! empty( $wpshop_paymentMethod['mode']['cic'] ) ) {
	include_once( WPSHOP_LIBRAIRIES_DIR . 'payments/cic.class.php' );
}

// End if().
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
include_once( WPSHOP_LIBRAIRIES_DIR . 'display/display.class.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'display/form.class.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'display/form_management.class.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'display/widgets/categories.widget.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'display/widgets/products.widget.php' );
// End if().
add_action( 'admin_init', array( 'wpshop_display', 'check_template_file' ) );
/* Files management */
include_once( WPSHOP_LIBRAIRIES_DIR . 'documents/documents.class.php' );
add_action( 'admin_head', array( 'wpshop_documents', 'galery_manager_css' ) );
add_filter( 'attachment_fields_to_edit', array( 'wpshop_documents', 'attachment_fields' ), 11, 2 );
add_filter( 'gettext', array( 'wpshop_documents', 'change_picture_translation' ), 11, 2 );
/* Catalog management */
include_once( WPSHOP_LIBRAIRIES_DIR . 'catalog/products.class.php' );
include_once( WPSHOP_LIBRAIRIES_DIR . 'catalog/categories.class.php' );
add_filter( 'manage_edit-' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_columns', array( 'wpshop_categories', 'category_manage_columns' ) );
add_filter( 'manage_' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_custom_column', array( 'wpshop_categories', 'category_manage_columns_content' ), 10, 3 );
add_action( WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_edit_form_fields', array( 'wpshop_categories', 'category_edit_fields' ) );
add_action( 'created_' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array( 'wpshop_categories', 'category_fields_saver' ), 10, 2 );
add_action( 'edited_' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array( 'wpshop_categories', 'category_fields_saver' ), 10, 2 );
/* EAV management */
include( WPSHOP_LIBRAIRIES_DIR . 'eav/wp_list_custom_attributes.class.php' );
include( WPSHOP_LIBRAIRIES_DIR . 'eav/attributes.class.php' );
include( WPSHOP_LIBRAIRIES_DIR . 'eav/attributes_unit.class.php' );
include( WPSHOP_LIBRAIRIES_DIR . 'eav/wp_list_custom_attributes_set.class.php' );
include( WPSHOP_LIBRAIRIES_DIR . 'eav/attributes_set.class.php' );
include( WPSHOP_LIBRAIRIES_DIR . 'eav/entities.class.php' );
/* Modules management */
eo_module_management::extra_modules();
