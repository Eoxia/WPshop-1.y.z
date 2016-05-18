<?php if ( !defined( 'ABSPATH' ) ) exit;
$tpl_element = array();
/**
 * WPS CREDIT LIST
 */
ob_start();
?>
<ul>
{WPSHOP_WPS_CREDIT_LIST_ELEMENTS}
</ul>
<?php
$tpl_element['admin']['default']['wps_credit_list'] = ob_get_contents();
ob_end_clean();

/**
 * WPS CREDIT LIST ELEMENT
 */
ob_start();
?>
<div class="wps_credit_list_element">
<div class="wps_credit_list_sub_element"><span class="ui-icon {WPSHOP_CREDIT_STATUS_ICON}"></span> <span class="alignleft"><strong>{WPSHOP_CREDIT_REF}</strong></span> <span class="alignright wps_credit_list_element_date">{WPSHOP_CREDIT_DATE}</span></div>
<div class="wps_credit_list_sub_element"><span class="alignleft">{WPSHOP_CREDIT_PDF_LINK}</span>
<span class="alignright">
<select id="credit_status_{WPSHOP_CREDIT_REF}" data-nonce="<?php echo wp_create_nonce( 'wps_credit_change_status' ); ?>" class="wps_credit_change_status">
{WPSHOP_CREDIT_STATUS_ELEMENTS}
</select>
</span>
</div>
</div>
<?php
$tpl_element['admin']['default']['wps_credit_list_element'] = ob_get_contents();
ob_end_clean();


/**
 * WPS CREDIT TABLE LINES
 */
ob_start();
?>
<tr>
	<td>{WPSHOP_ITEM_NAME}</td>
	<td><input type="text" value="{WPSHOP_ITEM_QTY}" name="wps_credit_item_quantity[{WPSHOP_ITEM_ID}]" style="width:60px" /></td>
	<td><input type="text" value="{WPSHOP_ITEM_PRICE}" name="wps_credit_item_price[{WPSHOP_ITEM_ID}]" style="width:60px"/></td>
	<td><input type="checkbox" name="wps_credit_return[{WPSHOP_ITEM_ID}]" /></td>
	<td><input type="checkbox" name="wps_credit_restock[{WPSHOP_ITEM_ID}]" /></td>
</tr>
<?php
$tpl_element['admin']['default']['wps_credit_items_table_line'] = ob_get_contents();
ob_end_clean();

/**
 * WPS CREDIT TABLE
 */
ob_start();
?>
<h2><?php _e('Item list', 'wpshop'); ?></h2>
<form action="<?php echo admin_url('admin-ajax.php'); ?>" id="wps_make_credit_form" method="post">
<input type="hidden" name="action" value="wps_make_credit_action" />
<?php wp_nonce_field( 'wps_make_credit_action' ); ?>
<input type="hidden" name="order_id" value="{WPSHOP_ORDER_ID}" />
<table class="wps_credit_table">
	<tr>
		<th><?php _e('Item name', 'wpshop'); ?></th>
		<th><?php _e('Item quantity', 'wpshop')?></th>
		<th><?php _e('Item price', 'wpshop')?></th>
		<th><?php _e('Return', 'wpshop'); ?></th>
		<th><?php _e('Restock this item', 'wpshop'); ?></th>
	</tr>
	{WPSHOP_TABLE_LINES}
</table>
<p><input type="checkbox" name="wps_credit_shipping_cost" id="wps_credit_shipping_cost" /> <label for="wps_credit_shipping_cost"><?php _e('Add shipping cost to credit', 'wpshop'); ?></label>
<h2><?php _e('Credit statut', 'wpshop'); ?></h2>
<p><label for="wps_credit_status"><?php _e('Credit status', 'wpshop')?> : </label> <select id="wps_credit_status" name="wps_credit_status"><option value="not_paid"><?php _e('Not paid', 'wpshop'); ?></option><option value="paid"><?php _e('Paid', 'wpshop'); ?></option></select></p>
<p><input type="checkbox" id="add_credit_value" name="wps_add_credit_value" /> <label for="add_credit_value"><?php _e('Add credit value to E-Shop Customer account', 'wpshop' ); ?></label></p>
</form>
<p><center><input type="button" id="wps_save_credit_button" value="<?php _e('Make the credit', 'wpshop'); ?>" class="button-primary" /> <img src="{WPSHOP_LOADING_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>" class="wpshopHide" id="save_credit_loader" /></center>
<?php
$tpl_element['admin']['default']['wps_credit_items_table'] = ob_get_contents();
ob_end_clean();
