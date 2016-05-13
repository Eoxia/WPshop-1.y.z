<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Plugin database start content definition file.
 *
 * This file contains the different definitions for the database content.
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage librairies-db
 */

$wpshop_eav_content = array();
$wpshop_eav_content_update = array();
$wpshop_db_content_add = array();
$wpshop_db_content_update = array();
$wpshop_db_options_add = array();
$wpshop_db_options_update = array();
$wpshop_db_delete= array();
$wpshop_db_version = 0;

{/*	Version 0	*/
	$wpshop_db_version = 0;

	/*	Default entities	*/
	$wpshop_eav_content[$wpshop_db_version]['entities'][] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
	$wpshop_db_options_add[$wpshop_db_version]['wpshop_db_options']['installation_state'] = 'install_init';

	/*	Unit	*/
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'mm', 'name' => __('Millimeters', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'm', 'name' => __('Meters', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'l', 'name' => __('Liters', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'oz', 'name' => __('Ounce', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'g', 'name' => __('Gram', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'kg', 'name' => __('Kilogram', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => '&euro;', 'name' => __('euro', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => '$', 'name' => __('dollar', 'wpshop'));
}

{/*	Version 1	*/
	$wpshop_db_version = 1;

	/*	Add unit groups	*/
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('length', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('capacity', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('weight', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('currency', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('various', 'wpshop'));

	/*	Update unit with group identifier	*/
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 1), 'where' => array('unit' => 'm'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 1, 'is_default_of_group' => 'yes'), 'where' => array('unit' => 'mm'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 2), 'where' => array('unit' => 'oz'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 2, 'is_default_of_group' => 'yes'), 'where' => array('unit' => 'l'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 3), 'where' => array('unit' => 'g'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 3, 'is_default_of_group' => 'yes'), 'where' => array('unit' => 'kg'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 4), 'where' => array('unit' => '$'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 4, 'is_default_of_group' => 'yes'), 'where' => array('unit' => '&euro;'));
}

{/*	Version 3	*/
	$wpshop_db_version = 3;

	$wpshop_db_options_add[$wpshop_db_version]['permalink_structure'] = '/%postname%';
	$wpshop_db_options_add[$wpshop_db_version]['wpshop_paymentMethod'] = array('paypal' => false, 'checks' => false);
}

{/*	Version 7	*/
	$wpshop_db_version = 7;

	$wpshop_eav_content[$wpshop_db_version]['attributes'][] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;

	/*	Default attribute group	*/
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['default'][] = array('code' => 'important_datas', 'name' => __('Product', 'wpshop'), 'details' => array( 'product_attribute_set_id', 'barcode', 'product_price', 'tx_tva', 'manage_stock', 'product_stock', 'product_weight'), 'backend_display_type' => 'movable-tab' );
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['default'][] = array('code' => 'general', 'name' => __('Main information', 'wpshop'), 'details' => array('product_reference', 'cost_of_postage',));
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['default'][] = array('code' => 'feature', 'name' => __('Feature', 'wpshop'), 'details' => array('product_height', 'product_width'));
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['default'][] = array('code' => 'additionnal_informations', 'name' => __('Additionnal informations', 'wpshop'), 'details' => array());

	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['default'][] = array( 'name' => __('Prices', 'wpshop'), 'code' => 'prices', 'details' => array( 'price_ht', 'tva', ));

	/*	Update attribute with default unit	*/
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_unit_group_id' => 1, '_default_unit' => 2), 'where' => array('code' => 'product_height'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_unit_group_id' => 1, '_default_unit' => 2), 'where' => array('code' => 'product_width'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_unit_group_id' => 3, '_default_unit' => 6), 'where' => array('code' => 'product_weight'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_unit_group_id' => 4, '_default_unit' => 7), 'where' => array('code' => 'product_price'));

	/*	Prices	*/
	$wpshop_eav_content[$wpshop_db_version]['attributes'][] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
	$wpshop_eav_content_update[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['default'][] = array('code' => 'additionnal_informations', 'details' => array());

	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['chemical_product'][] = array('status' => 'deleted', 'name' => __('Chemical Product', 'wpshop'), 'code' => 'chemical_product', 'details' => array('chemical_product', 'pictogram', 'risk_phrases', 'safety_advices', 'security_datas', 'last_update_date', 'consumption', 'frequency', 'cmr'));

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'valid'), 'where' => array('status' => ''));
}

{/*	Version 9	*/
	$wpshop_db_version = 9;

	/*	Make a correction on attribute that have been created with bad attribute set group	*/
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_DETAILS][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), 'where' => array('entity_id' => '0'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_DETAILS][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), 'where' => array('attribute_set_id' => '0'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_DETAILS][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), 'where' => array('attribute_group_id' => '0'));

	/*	Add a default attribute set for new attribute set creation	*/
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_SET][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'default_set' => 'yes'), 'where' => array('id' => '1'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'default_group' => 'yes'), 'where' => array('code' => 'additionnal_informations'));
}

{/*	Version 10	*/
	$wpshop_db_version = 10;

	$wpshop_db_options_add[$wpshop_db_version]['wpshop_shop_default_currency'] = WPSHOP_SHOP_DEFAULT_CURRENCY;
}

{/*	Version 11	*/
	$wpshop_db_version = 11;

	$wpshop_db_options_add[$wpshop_db_version]['wpshop_shipping_rules'] = unserialize(WPSHOP_SHOP_SHIPPING_RULES);
}

{/*	Version 12	*/
	$wpshop_db_version = 12;

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_default_unit' => 0), 'where' => array('code' => 'product_price'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_default_unit' => 0), 'where' => array('code' => 'price_ht'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_default_unit' => 0), 'where' => array('code' => 'tva'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_default_unit' => 0), 'where' => array('code' => 'cost_of_postage'));

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'is_used_for_sort_by' => 'yes'), 'where' => array('code' => 'product_price'));

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'moderated', 'backend_input' => 'select'), 'where' => array('code' => 'declare_new'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'moderated'), 'where' => array('code' => 'set_new_from'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'moderated'), 'where' => array('code' => 'set_new_to'));

	$wpshop_eav_content[$wpshop_db_version]['attributes'][] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;

	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['default'][] = array('code' => 'product_highlight', 'name' => __('Product highlight', 'wpshop'), 'details' => array('declare_new', 'set_new_from', 'set_new_to', 'highlight_product', 'highlight_from', 'highlight_to'));
}

{/*	Version 13	*/
	$wpshop_db_version = 13;

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'is_used_for_sort_by' => 'yes'), 'where' => array('code' => 'product_stock'));
}

{/*	Version 14	*/
	$wpshop_db_version = 14;

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'backend_input' => 'select'), 'where' => array('code' => 'declare_new'));
}

{/*	Version 19 - Version 1.3.1.8	*/
	$wpshop_db_version = 19;

	/*	Add custom shipping fees	*/
	$wpshop_db_options_add[$wpshop_db_version]['wpshop_custom_shipping'] = unserialize(WPSHOP_SHOP_CUSTOM_SHIPPING);

	/*	Add shop type	*/
	$current_db_version = get_option('wpshop_db_options', 0);
	if(!empty($current_db_version) && $current_db_version['db_version'] >= $wpshop_db_version){
		$wpshop_db_options_add[$wpshop_db_version]['wpshop_shop_type'] = 'sale';
		$wpshop_db_options_update[$wpshop_db_version]['wpshop_db_options']['installation_state'] = 'completed';
	}

	/*	Add attributes for product: downloadable product / ask quotation for product	*/
	$wpshop_eav_content[$wpshop_db_version]['attributes'][] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;

	/*	Affect attribute to a set section	*/
	$wpshop_eav_content_update[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['default'][] = array('code' => 'additionnal_informations', 'details' => array('is_downloadable_', 'quotation_allowed'));

	/*	Change url rewriting for categories	*/
	$wpshop_db_options_update[$wpshop_db_version]['wpshop_catalog_categories_option']['wpshop_catalog_categories_slug'] = 'wpshop-category';

	/*	Update attributes set sections for sale/presentation shop */
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'used_in_shop_type' => 'sale'), 'where' => array('code' => 'prices'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'used_in_shop_type' => 'sale'), 'where' => array('code' => 'inventory'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'used_in_shop_type' => 'sale'), 'where' => array('code' => 'shipping'));

	/*	Delete useless option	*/
	$wpshop_db_delete[$wpshop_db_version][] = $wpdb->prepare("DELETE FROM ".$wpdb->options." WHERE option_name=%s", 'wpshop_shop_currencies');
}

{/*	Version 20 - Version 1.3.1.9	*/
	$wpshop_db_version = 20;

	/*	Update attributes set sections for display in frontend parameter */
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'display_on_frontend' => 'no'), 'where' => array('code' => 'prices'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'display_on_frontend' => 'no'), 'where' => array('code' => 'inventory'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'display_on_frontend' => 'no'), 'where' => array('code' => 'shipping'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'display_on_frontend' => 'no'), 'where' => array('code' => 'product_highlight'));

	$wpshop_eav_content[$wpshop_db_version]['attributes'][] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
}

{/*	Version 22 - Version 1.3.2.4	*/
	$wpshop_db_version = 22;

	$wpshop_eav_content[$wpshop_db_version]['entities'][] = WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;
}

{/*	Version 25 - Version 1.3.2.6	*/
	$wpshop_db_version = 25;

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'is_used_in_admin_listing_column' => 'no'), 'where' => array('code' => 'product_price'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'is_used_in_admin_listing_column' => 'no'), 'where' => array('code' => 'product_stock'));
}

{/*	Version 27 - Version 1.3.2.8	*/
	$wpshop_db_version = 27;

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'display_on_frontend' => 'yes'), 'where' => array('display_on_frontend' => ''));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'is_visible_in_front' => 'yes'), 'where' => array('code' => WPSHOP_PRODUCT_PRICE_TTC));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'is_visible_in_front_listing' => 'no'), 'where' => array('is_visible_in_front' => 'no'));
}

{/*	Version 29 - Version 1.3.3.4	*/
	$wpshop_db_version = 29;

	$wpshop_eav_content[$wpshop_db_version]['entities'][] = WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS;
	$wpshop_eav_content[$wpshop_db_version]['attributes'][] = WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS;

	$wpshop_eav_content[$wpshop_db_version]['attributes'][] = WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;

	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS][__('Billing address', 'wpshop')][] = array('code' => 'billing_address', 'name' => __('Billing address', 'wpshop'), 'details' => array( 'address_title', 'civility', 'address_last_name', 'address_first_name', 'company', 'tva_intra', 'address_user_email', 'address', 'postcode', 'city', 'country', 'state', 'phone', 'longitude', 'latitude'));
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS][__('Shipping address', 'wpshop')][] = array('code' => 'shipping_address', 'name' => __('Shipping address', 'wpshop'), 'details' => array('address_title', 'civility', 'address_last_name', 'address_first_name', 'company', 'address', 'postcode', 'city', 'country', 'state', 'longitude', 'latitude'));

	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS][__('Account', 'wpshop')][] = array('code' => 'account', 'name' => __('Account', 'wpshop'), 'details' => array( 'user_login', 'user_pass', 'last_name', 'first_name', 'user_email' ));
}

{/*	Version 40 - Version 1.3.5.4	*/
	$wpshop_db_version = 40;

	$wpshop_eav_content[$wpshop_db_version]['attributes'][] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;

	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('puissance', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'watt', 'name' => __('watt', 'wpshop'));

	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'cm', 'name' => __('centimeters', 'wpshop'));

	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('duration', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'day', 'name' => __('Day(s)', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'week', 'name' => __('Week(s)', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'year', 'name' => __('Year(s)', 'wpshop'));
}

{/*	Version 60 - Version 1.3.9.8	*/
	$wpshop_db_version = 60;

	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['free_product'][] = array( 'name' => __('Prices', 'wpshop'), 'code' => 'prices', 'details' => array('price_ht', 'product_price', 'tx_tva', 'tva'));
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups'][WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['free_product'][] = array('code' => 'general', 'name' => __('Main information', 'wpshop'), 'details' => array('product_reference', 'barcode'));
}

{/*	Version 65 - Version 1.4.1.6	*/
	$wpshop_db_version = 60;

	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'group_id' => 4, 'unit' => '&yen;', 'name' => __('Yuan', 'wpshop'));
}

{/*	Version dev	- Call for every plugin db version	*/
	$wpshop_db_version = 'dev';
}
