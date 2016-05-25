<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<table class="form-table" >
	<tr>
		<th><?php _e( 'Shop type', 'wpshop' ); ?></th>
		<td><?php wpshop_general_options::wpshop_shop_type(); ?></td>
	</tr>
	<tr>
		<th><?php _e( 'Price piloting for the shop', 'wpshop' ); ?></th>
		<td><?php wpshop_general_options::wpshop_shop_price_piloting_field(); ?></td>
	</tr>
	<tr>
		<td colspan="2" ><label><input type="checkbox" name="wps-installer-data-insertion" value="yes" checked="checked" disabled="disabled" /><?php _e( 'Create necessary datas for wpshop (emails, pages)', 'wpshop'); ?></label></td>
	</tr>
	<!--
	<tr>
		<td colspan="2" ><label><input type="checkbox" name="wps-installer-data-insertion" value="yes" /><?php _e( 'Create sample datas (products)', 'wpshop'); ?></label></td>
	</tr>
	 -->
</table>