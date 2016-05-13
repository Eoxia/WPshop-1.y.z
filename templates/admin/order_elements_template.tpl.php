<?php if ( !defined( 'ABSPATH' ) ) exit;

/**	Order actions box	*/
ob_start();
?>
<input type="hidden" name="input_wpshop_change_order_state" id="input_wpshop_change_order_state" value="<?php echo wp_create_nonce("wpshop_change_order_state"); ?>" />
<input type="hidden" name="input_wpshop_dialog_inform_shipping_number" id="input_wpshop_dialog_inform_shipping_number" value="<?php echo wp_create_nonce("wpshop_dialog_inform_shipping_number"); ?>" />
<input type="hidden" name="input_wpshop_validate_payment_method" id="input_wpshop_validate_payment_method" value="<?php echo wp_create_nonce("wpshop_validate_payment_method"); ?>" />
<div class="wps-boxed wpshop_orders_actions_list">
	<div class="wps-product-section">
		{WPSHOP_ADMIN_ORDER_ACTIONS_LIST}
	</div>
	<div class="wpshop_orders_actions_main">
		{WPSHOP_ADMIN_ORDER_DELETE_ORDER}
		<input type="submit" value="<?php _e('Save order', 'wpshop'); ?>" name="save" class="wps-bton-first-mini-rounded wpshop_order_save_button" id="wpshop_order_save_button" />
		<img id="ajax-loading-wphop-order" class="alignright wpshopHide ajax-loading-wphop-order" alt="" src="<?php echo admin_url('images/wpspin_light.gif'); ?>">
	</div>
</div>

<script type="text/javascript" >
	wpshop(document).ready(function(){
		if(jQuery("#title").val() == ""){
			jQuery("#title").val((wpshopConvertAccentTojs("<?php echo sprintf(__('Order - %s', 'wpshop'), mysql2date('d M Y\, H:i:s', current_time('mysql', 0), true)); ?>")));
		}

		jQuery("#wpshop_order_save_button").live('click', function(){
			jQuery('#ajax-loading-wphop-order').show();
			display_message_for_received_payment( true );
		});

		
	});

	/**
	 * Output a message to the user if he received a new payment
	 */
	function display_message_for_received_payment( from_general_button ) {

		var form_is_complete = true;

		if( jQuery('#wpshop_admin_order_payment_received_date').val() == "" && jQuery('#wpshop_admin_order_payment_received_amount').val() == "" ){
				form_is_complete = false;
		}

		if( !form_is_complete && !from_general_button ){
			jQuery("#ajax-loading-wphop-order").hide();
			return false;
		}

		if ( form_is_complete ) {
			/**	Get the current due amount to display a message to current admin	*/
			var current_due_amount = jQuery("#wpshop_admin_order_due_amount").val();
			var received_amount = jQuery("#wpshop_admin_order_payment_received_amount").val();

			var message_to_display = "<?php _e('Adding this payment will result of the billing of this order.\r\nAre you sure you want to continue?', 'wpshop'); ?>";
			if (current_due_amount == received_amount) {
				message_to_display = "<?php _e('It seems you received complete payment for this order.\rThis', 'wpshop'); ?>";
			}
		}
	}
</script><?php
$tpl_element['wpshop_admin_order_action_box'] = ob_get_contents();
ob_end_clean();

ob_start();
?><a class="submitdelete deletion" href="{WPSHOP_ADMIN_ORDER_DELETE_LINK}"><span class="dashicons dashicons-trash"></span></a><?php
$tpl_element['wpshop_admin_order_action_del_button'] = ob_get_contents();
ob_end_clean();

ob_start();
?>
<p><?php _e('Sended', 'wpshop'); ?> : <br/>
{WPSHOP_UPDATE_ORDER_MESSAGE_DATE}</p>
<p><?php _e('Message', 'wpshop'); ?> : <br/>{WPSHOP_UPDATE_ORDER_MESSAGE}</p>
<hr/>
<?php
$tpl_element['wpshop_admin_order_customer_notification_item'] = ob_get_contents();
ob_end_clean();




