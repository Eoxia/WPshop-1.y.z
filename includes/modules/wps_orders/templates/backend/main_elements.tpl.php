<?php if ( !defined( 'ABSPATH' ) ) exit;
$tpl_element = array();
/**
 * WPS ORDERS CHOOSE CUSTOMERS INTERFACE 
 */
ob_start();
?>
<button id="wps_orders_create_customer" class="button-primary"><?php _e('Create a customer', 'wpshop') ; ?></button>  
<?php _e('OR', 'wpshop'); ?> 
{WPSHOP_CUSTOMERS_LIST}
<?php
$tpl_element['admin']['default']['wps_orders_choose_customer_interface'] = ob_get_contents();
ob_end_clean();


/**
 * WPS ORDERS LETTER
 */
ob_start();
?>
<img src="{WPSHOP_LOADING_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>" class="wpshopHide" id="wps_products_list_change_loader" /><br/>
<div id="wps_orders_products_list_for_quotation_container">
	<table>
		<tr>
			<th></th>
			<th><?php _e('Product ID', 'wpshop'); ?></th>
			<th><?php _e('Product reference', 'wpshop'); ?></th>
			<th><?php _e('Product name', 'wpshop'); ?></th>
			<th><?php _e('Product price', 'wpshop'); ?></th>
			<th><?php _e('Quantity', 'wpshop'); ?></th>
			<th></th>
		</tr>
		{WPSHOP_PRODUCTS_LIST}
	</table>
</div>
<?php
$tpl_element['admin']['default']['wps_orders_products_list_for_quotation'] = ob_get_contents();
ob_end_clean();


/**
 * WPS ORDERS LETTER
 */
ob_start();
?>
	<tr>
		<td class="wps_orders_product_id">{WPSHOP_PRODUCT_PICTURE}</td>
		<td class="wps_orders_product_id">#{WPSHOP_PRODUCT_ID}</td>
		<td class="wps_orders_product_reference">{WPSHOP_PRODUCT_REFERENCE}</td>
		<td>{WPSHOP_PRODUCT_NAME}</td>
		<td class="wps_orders_product_price">{WPSHOP_PRODUCT_PRICE}</td>
		<td class="wps_orders_product_qty"><input type="text" id="add_product_to_order_qty_{WPSHOP_PRODUCT_ID}" value="1" size="8" /></td>
		<td class="wps_orders_product_add_to_cart"><input type="button" class="button-primary add_product_to_order_quotation" id="add_product_to_cart_{WPSHOP_PRODUCT_ID}" value="<?php _e('Add product to order', 'wpshop'); ?>" /> <img src="{WPSHOP_LOADING_ICON}" class="wpshopHide add_to_cart_loader" id="add_to_cart_loader_{WPSHOP_PRODUCT_ID}" alt="<?php _e('Loading', 'wpshop')?>" /></td>
	</tr>
<?php
$tpl_element['admin']['default']['wps_orders_products_list_for_quotation_table_line'] = ob_get_contents();
ob_end_clean();