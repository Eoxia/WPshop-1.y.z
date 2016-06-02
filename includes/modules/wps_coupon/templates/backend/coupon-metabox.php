<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<table class="wpshop_coupon_definition_table" >
	<tr class="wpshop_coupon_definition_table_code_coupon_line" >
		<td class="wpshop_coupon_definition_table_label wpshop_coupon_definition_code_coupon_input_label" ><label for="coupon_code" ><?php _e('Coupon code','wpshop'); ?></label></td>
		<td class="wpshop_coupon_definition_table_input wpshop_coupon_definition_code_coupon_input" ><input type="text" name="coupon_code" id="coupon_code" value="<?php echo $coupon_code; ?>" /></td>
	</tr>
	<tr class="wpshop_coupon_definition_table_code_type_line" >
		<td class="wpshop_coupon_definition_table_label wpshop_coupon_definition_coupon_type_amount_label" ><input type="radio" name="coupon_type" class="wpshop_coupon_type" id="coupon_type_amount" value="amount" <?php echo ( ($wpshop_coupon_discount_type=='amount') || empty($wpshop_coupon_discount_type) ? 'checked="checked"' : null ); ?> /><label for="coupon_type_amount" ><?php _e('Coupon discount amount','wpshop'); ?></label></td>
		<td class="wpshop_coupon_definition_table_input wpshop_coupon_definition_coupon_type_input" rowspan="2" ><input type="text" name="coupon_discount_amount" value="<?php echo $coupon_discount_amount; ?>" /><span class="wpshop_coupon_type_unit wpshop_coupon_type_unit_amount" > <?php echo ( ( (!empty($wpshop_coupon_discount_type) && $wpshop_coupon_discount_type == 'percent' ) ) ? '%' : wpshop_tools::wpshop_get_currency().' '.__('ATI', 'wpshop')); ?></span><span class="wpshopHide wpshop_coupon_type_unit wpshop_coupon_type_unit_percent" > % </span></td>
	</tr>
	<tr class="wpshop_coupon_definition_table_code_type_line" >
		<td class="wpshop_coupon_definition_table_label wpshop_coupon_definition_coupon_type_percent_label" ><input type="radio" name="coupon_type" id="coupon_type_percent" class="wpshop_coupon_type" value="percent" <?php echo ($wpshop_coupon_discount_type=='percent'?'checked="checked"':null); ?> /><label for="coupon_type_percent" ><?php _e('Coupon discount percent','wpshop'); ?></label></td>
	</tr>
	<tr>
		<td>
			<label for="coupon_receiver"><?php _e('Discount receiver', 'wpshop'); ?> : </label>
		</td>
		<td>
			<select name="coupon_receiver[]" id="coupon_receiver"  class="chosen_select" multiple data-placeholder="<?php _e('Choose a customer', 'wpshop'); ?>" >
			<?php
				$args = array(
						'posts_per_page' => -1,
						'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
						'post_status' => 'draft',
				);
				$customers = get_posts( $args );
				if ( !empty($customers) ) :
				 	foreach( $customers as $customer ) :
				 		$name = strtoupper( get_user_meta( $customer->post_author, 'last_name', true ) ).' '.get_user_meta( $customer->post_author, 'first_name', true );
						$user = get_userdata($customer->post_author);
						if ( !empty( $user ) ) :
?>
						<option value="<?php echo $customer->post_author; ?>" <?php echo ( (!empty( $coupon_receiver) && is_array($coupon_receiver) && in_array($customer->post_author, $coupon_receiver)) ? 'selected="selected"' : '' ); ?>><?php echo $name; ?> ( <?php echo $user->user_email; ?> )</option>
<?php
						endif;
					endforeach;
				endif;
				?>
			</select>
		</td>
	</tr>

	<tr>
		<td>
			<label for="wpshop_coupon_usage_limit"><?php _e('Number of usage by user', 'wpshop'); ?></label> :
		</td>
		<td>
			<input type="text" name="coupon_usage_limit" value="<?php echo $coupon_limit_usage; ?>" id="wpshop_coupon_usage_limit" />
			<br/><?php _e('Leave empty if you want a illimited usage', 'wpshop'); ?>
		</td>
	</tr>

	<tr>
		<td>
			<label for="wpshop_coupon_mini_amount"><?php _e('Minimum order amount to use this coupon', 'wpshop'); ?></label> :
		</td>
		<td>
			<input type="text" name="wpshop_coupon_mini_amount" value="<?php echo ( (!empty($wpshop_coupon_minimum_amount) && !empty($wpshop_coupon_minimum_amount['amount']) ) ? $wpshop_coupon_minimum_amount['amount'] : ''); ?>" id="wpshop_coupon_mini_amount" /> <?php echo $default_currency; ?>
			<select name="wpshop_coupon_min_mount_shipping_rule">
			<option value="no_shipping_cost" <?php echo ( (!empty($wpshop_coupon_minimum_amount) && !empty($wpshop_coupon_minimum_amount['shipping_rule']) && $wpshop_coupon_minimum_amount['shipping_rule'] == 'no_shipping_cost') ? 'selected="selected"' : ''); ?>><?php _e('Without shipping cost', 'wpshop'); ?></option>
			<option value="shipping_cost" <?php echo ( (!empty($wpshop_coupon_minimum_amount) && !empty($wpshop_coupon_minimum_amount['shipping_rule']) && $wpshop_coupon_minimum_amount['shipping_rule'] == 'shipping_cost') ? 'selected="selected"' : ''); ?>><?php _e('With shipping cost', 'wpshop'); ?></option>
			</select>
			<br/><?php _e('Leave empty if you want no limitation', 'wpshop'); ?>
		</td>
	</tr>
</table>