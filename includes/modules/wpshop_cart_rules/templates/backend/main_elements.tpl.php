<?php if ( !defined( 'ABSPATH' ) ) exit;
$tpl_element = array();

/**
 * CART RULES INTERFACE
 */
ob_start();
?>
<input type="checkbox" name="wpshop_cart_rules_option[activate]" id="wpshop_cart_rules_option_activate" {WPSHOP_ACTIVE_CART_RULES} />


<div id="wpshop_cart_rules_interface">
<p><?php _e('Cart limen', 'wpshop'); ?> : <input type="text" class="shipping_rules_configuration_input" name="wpshop_cart_rules_option_cart_limen" id="wpshop_cart_rules_option_cart_limen" /> {WPSHOP_CURRENCY} <a href="#" title="<?php _e('From which amount you want to apply a cart rule ?', 'wpshop'); ?>" class="wpshop_infobulle_marker">?</a></p>

<p><?php _e('Customers group', 'wpshop'); ?> : <select id="wpshop_cart_rules_option_customer_group" class="chosen_select" name="wpshop_cart_rules_option_customer_group" >{WPSHOP_CART_RULES_CUSTOMERS_GROUPS}</select></p>


<h3><?php _e('Discount type', 'wpshop'); ?></h3>
<div class="cart_rules_discount_interface_container"><input type="radio" name="wpshop_cart_rules_option_rules_discount_type" id="wpshop_cart_rules_option_discount_absolute" value="absolute_discount" class="wpshop_cart_rules_option_discount_choice" /> <label for="wpshop_cart_rules_option_discount_absolute"><?php _e('Absolute discount', 'wpshop'); ?></label></div>
<div class="cart_rules_discount_interface_container"><input type="radio" name="wpshop_cart_rules_option_rules_discount_type" id="wpshop_cart_rules_option_discount_percent" value="percent_discount" class="wpshop_cart_rules_option_discount_choice" /> <label for="wpshop_cart_rules_option_discount_percent"><?php _e('Percent discount', 'wpshop'); ?></label></div>
<div class="cart_rules_discount_interface_container"><input type="radio" name="wpshop_cart_rules_option_rules_discount_type" id="wpshop_cart_rules_option_gift_product" value="gift_product" class="wpshop_cart_rules_option_discount_choice" /> <label for="wpshop_cart_rules_option_gift_product"><?php _e('Gift product', 'wpshop'); ?></label></div>
<div class="wpshop_cls"></div>
<div class="cart_rules_discount_interface_container">
	<div class="cart_rules_discount_interface" id="interface_wpshop_cart_rules_option_discount_absolute">
		<input type="text" name="absolute_discount_value" id="absolute_discount_value" class="shipping_rules_configuration_input" /> {WPSHOP_CURRENCY}
	</div>
</div>
<div class="cart_rules_discount_interface_container">
	<div class="cart_rules_discount_interface" id="interface_wpshop_cart_rules_option_discount_percent">
		<input type="text" name="percent_discount_value" id="percent_discount_value" class="shipping_rules_configuration_input" /> %
	</div>
</div>
<div class="cart_rules_discount_interface_container">
	<div class="cart_rules_discount_interface" id="interface_wpshop_cart_rules_option_gift_product">
		<select name="gift_product_value" id="gift_product_value" class="chosen_select" data-placeholder="<?php _e('Select a product', 'wpshop'); ?>">
			{WPSHOP_PRODUCTS_LIST_FOR_GIFT}
		</select>
	</div>
</div>
<div class="wpshop_cls"></div>
<div class="cart_rules_discount_interface_container"><a id="save_cart_rule" data-nonce="<?php echo wp_create_nonce( 'wpshop_ajax_save_cart_rule' ); ?> " class="button-primary"><?php _e('Add the cart rule', 'wpshop')?></a></div>
<div class="wpshop_cls"></div>

<h3><?php _e('Cart rules already created', 'wpshop'); ?></h3>
<textarea id="wpshop_cart_rules_data" name="wpshop_cart_rules_option[rules]" class="wpshopHide">{WPSHOP_CART_RULES_DATA}</textarea>

<div id="display_all_rules">{WPSHOP_ALL_CART_RULES}</div>
</div>
<?php
$tpl_element['admin']['default']['cart_rules_interface'] = ob_get_contents();
ob_end_clean();



/**
 * CART RULES DISPLAY LINE
 */
ob_start();
?>
<tr>
	<td>{WPSHOP_CART_RULE_LINE_CART_LIMEN} {WPSHOP_CURRENCY}</td>
	<td>{WPSHOP_CART_RULE_LINE_DISCOUNT_TYPE}</td>
	<td>{WPSHOP_CART_RULE_LINE_CUSTOMER_GROUP}</td>
	<td>{WPSHOP_CART_RULE_LINE_DISCOUNT_VALUE}</td>
	<td id="{WPSHOP_CART_RULE_ID}" data-nonce="<?php echo wp_create_nonce( 'wpshop_ajax_delete_cart_rule' ); ?>" class="cart_line_delete_rule" ><img src="{WPSHOP_MEDIAS_ICON_URL}delete.png" alt="<?php _e('Delete', 'wpshop_shipping_configuration'); ?>" /></td>
<?php
$tpl_element['admin']['default']['cart_rules_line'] = ob_get_contents();
ob_end_clean();



/**
 * CART RULES DISPLAY
 */
ob_start();
?>
<table>
	<tr>
		<th><?php _e('Cart Limen', 'wpshop'); ?></th>
		<th><?php _e('Discount type', 'wpshop'); ?></th>
		<th><?php _e('Customers group', 'wpshop'); ?></th>
		<th><?php _e('Discount value', 'wpshop'); ?></th>
		<th></th>
	</tr>
	{WPSHOP_CART_RULES_LINE}
</table>
<div class="wpshop_cls"></div>
<div class="cart_rules_container"><img src="{WPSHOP_MEDIAS_ICON_URL}error.gif" alt="" /> <i><?php _e('Don\'t forget to click on "Save Changes" button to save your cart rules.', 'wpshop'); ?></i><br/></div>
<?php
$tpl_element['admin']['default']['cart_rules_display'] = ob_get_contents();
ob_end_clean();
?>
