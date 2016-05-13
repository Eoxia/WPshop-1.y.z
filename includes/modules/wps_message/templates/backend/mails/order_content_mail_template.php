<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<table style="width:600px; border : 1px solid #000000; border-collapse : collapse; margin-top:20px; margin-bottom:20px;">
	<tr bgcolor="#1D7DC1" height="80" valign="middle" align="center" style="color : #FFFFFF;">
		<td width="90"><?php _e('Reference', 'wpshop'); ?> / <?php _e( 'Barcode', 'wpshop' ); ?></td>
		<td width="240"><?php _e('Products', 'wpshop'); ?></td>
		<td width="90"><?php _e('Quantity', 'wpshop'); ?></td>
		<td width="90"><?php _e('Unit price ET', 'wpshop'); ?></td>
		<td width="90"><?php _e('Total HT', 'wpshop'); ?></td>
	</tr>
	<?php
	if ( !empty($orders_infos['order_items']) ) :
		foreach ( $orders_infos['order_items'] as $key=>$item) :
			$item_ref = $item['item_ref'];

			$is_variation = false;
			if( get_post_type( $item['item_id'] ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
				$is_variation = true;
				$parent_def = wpshop_products::get_parent_variation( $item['item_id'] );
				if( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
					$parent_post = $parent_def['parent_post'];
					$item_parent_id = $parent_post->ID;
					$item_name =  $parent_post->post_title;
					$item_barcode = get_post_meta( $item['item_id'], '_barcode', true );
				}
			} else {
				$item_name = $item['item_name'];
				$item_barcode = get_post_meta( $item['item_id'], '_barcode', true );
			}

			if ( !empty($item['item_id']) ) {
				$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($item['item_id'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
				$output_order = array();
				if ( count($product_attribute_order_detail) > 0 && is_array($product_attribute_order_detail) ) {
					foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
						foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
							if ( !empty($attribute_def->code) )
								$output_order[$attribute_def->code] = $position;
						}
					}
				}
				$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $item['item_meta'], $output_order, 'invoice_print', 'common');
				ksort($variation_attribute_ordered['attribute_list']);

				$cart_more_infos = '';
				if( !empty($variation_attribute_ordered['attribute_list']) ) {
					foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
						$cart_more_infos .= '<li>'.$attribute_variation_to_output.'</li>';
					}
				}
				$item_name .= !empty($cart_more_infos) ? '<ul>' .$cart_more_infos. '</ul>' : '';
			}
	?>
		<tr height="40" valign="middle" align="center">
			<td><b><?php echo $item_ref; ?></b><br><?php echo $item_barcode; ?></td>
			<td align="center"><?php echo $item_name; ?></td>
			<td align="center"><?php echo  $item['item_qty']; ?></td>
			<td><?php echo number_format((float)$item['item_pu_ht'], 2, '.', ''). ' '.$currency_code ?></td>
			<td align="center"><?php echo number_format((float)$item['item_total_ht'], 2, '.', ''). ' '.$currency_code; ?></td>
		</tr>
	<?php
		endforeach;
	endif;
	?>
	<!-- Order total -->
	<tr height="40" valign="middle">
		<td colspan="4" align="right"><?php _e('Total ET', 'wpshop'); ?> </td>
		<td align="center"><?php echo number_format((float)$orders_infos['order_total_ht'], 2, '.', '') . ' ' . $currency_code; ?></td>
	</tr>

	<!-- Shippng cost -->
	<tr height="40" valign="middle">
		<td colspan="4" align="right"><?php _e('Shipping cost', 'wpshop'); ?> </td>
		<td align="center"><?php echo number_format((float)$orders_infos['order_shipping_cost'], 2, '.', ''). ' '.$currency_code; ?></td>
	</tr>

	<!-- TVA -->
	<tr height="40" valign="middle">
		<td colspan="4" align="right"><?php _e('Taxes', 'wpshop'); ?> </td>
		<td align="center"><?php echo number_format((float) ( $orders_infos['order_total_ttc'] - ( $orders_infos['order_total_ht'] + ( $orders_infos['order_shipping_cost'] / ( 1 + ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) ) ) ) ), 2, '.', ''). ' '.$currency_code ?></td>
	</tr>
	<?php
	if ( !empty($orders_infos['order_tva']) ) :
		foreach ( $orders_infos['order_tva'] as $rate => $montant ) :
	?>
			<tr height="40" valign="middle">
				<td colspan="4" align="right"><?php _e('Taxes', 'wpshop'); ?> (<?php ( !empty($rate) && $rate == 'VAT_shipping_cost') ? __('on Shipping cost', 'wpshop').' '.WPSHOP_VAT_ON_SHIPPING_COST : $rate;?>%)</td>
				<td align="center"><?php echo number_format((float)$montant, 2, '.', ''). ' '.$currency_code ?></td>
			</tr>
	<?php
		endforeach;
	endif;
	?>
	<!-- Shippng cost -->
	<tr height="40" valign="middle">
		<td colspan="4" align="right"><?php _e('Total ATI', 'wpshop'); ?> </td>
		<td align="center"><?php echo number_format((float)$orders_infos['order_grand_total'], 2, '.', ''). ' '.$currency_code; ?></td>
	</tr>
</table>
