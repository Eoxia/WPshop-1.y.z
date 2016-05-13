<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<input type="checkbox" name="wpshop_low_stock_alert_options[active]" id="wpshop_low_stock_options_active" <?php echo $activate_low_stock_alert; ?> />
<label for="wpshop_low_stock_options_active"><?php _e('Activate the low-stock alert display', 'wpshop'); ?></label>

<div id="low_stock_alert_configuration">
	<?php _e('Low stock alert is based on real stock ', 'wpshop'); ?> ?<br/>
	<input type="radio" name="wpshop_low_stock_alert_options[based_on_stock]" id="wpshop_low_stock_alert_options_based_on_stock" value="yes" <?php echo $based_on_stock; ?> /><label for="wpshop_low_stock_alert_options_based_on_stock"><?php _e('Yes', 'wpshop'); ?> </label>
	<input type="radio" name="wpshop_low_stock_alert_options[based_on_stock]" id="wpshop_low_stock_alert_options_not_based_on_stock" value="no" <?php echo $not_based_on_stock; ?> /><label for="wpshop_low_stock_alert_options_not_based_on_stock"><?php _e('No', 'wpshop'); ?> </label>
	<div id="low_stock_alert_limit"><label for="stock_alert_limit"><input type="text" id="stock_alert_limit" name="wpshop_low_stock_alert_options[alert_limit]" value="<?php echo $alert_limit; ?>" style="width : 80px" /><?php _e('Number of remaining products to display the alert', 'wpshop'); ?></label></div>
</div>
