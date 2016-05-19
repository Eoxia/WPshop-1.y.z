<?php if ( !defined( 'ABSPATH' ) ) exit; ?>
<table class="border">
<tr>
	<td class="width-15"><?php echo !empty($company['company_name']) ? $company['company_name'] : ''; ?></td>
	<td rowspan="4" class="width-70 align-center title bold"><?php _e('Bank deposit', 'wpshop'); ?></td>
	<td class="width-075"><?php _e('SIRET', 'wpshop'); ?>:</td>
	<td class="align-right width-075"><?php echo !empty($company['company_siret']) ? $company['company_siret'] : ''; ?></td>
</tr>
<tr>
	<td class="width-15"><?php echo !empty($company['company_phone']) ? $company['company_phone'] : ''; ?></td>
	<td class="width-075"><?php _e('SIREN', 'wpshop'); ?>:</td>
	<td class="align-right width-075"><?php echo !empty($company['company_siren']) ? $company['company_siren'] : ''; ?></td>
</tr>
<tr>
	<td class="width-15"><?php echo !empty($company['company_street']) ? $company['company_street'] : ''; ?></td>
	<td class="valign-top width-075"><?php _e('Date', 'wpshop'); ?>:</td>
	<td class="align-right width-075">
	<?php if( $from_to ) { ?>
		<span class="force-one-line"><?php echo __( 'From', 'wpshop' ) . ' ' . mysql2date( get_option( 'date_format' ), (string) $fromdate->format('Y-m-d'), true ); ?></span>
		<span class="force-one-line"><?php echo __( 'to', 'wpshop' ) . ' ' . mysql2date( get_option( 'date_format' ), $from_to, true ); ?></span>
	<?php } else { ?>
		<span class="force-one-line"><?php echo mysql2date( get_option( 'date_format' ), (string) $fromdate->format('Y-m-d'), true ); ?></span>
	<?php } ?>
	</td>
</tr>
<tr>
	<td class="width-15"><?php echo ( !empty($company['company_postcode']) ? $company['company_postcode'] : '' ) . ' ' . ( !empty($company['company_country']) ? $company['company_country'] : '' ); ?></td>
	<td class="width-075"><?php _e('Method', 'wpshop' ); ?>:</td>
	<td class="align-right width-075"><?php _e( $method, 'wpshop' ); ?></td>
</tr>
</table>
<br>
<table>
<thead class="border">
	<tr>
		<th class="margin-sides"><?php _e('Invoice', 'wps-pos-i18n'); ?></th>
		<th class="margin-sides"><?php echo ( $from_to ) ? __('Date', 'wps-pos-i18n') : __('Hour', 'wpshop'); ?></th>
		<th class="margin-sides"><?php _e('Order', 'wpshop'); ?></th>
		<th class="width-100"><?php _e('Products', 'wpshop'); ?></th>
		<th class="margin-sides"><?php _e('Amount', 'wps-pos-i18n'); ?></th>
 	</tr>
</thead>
<tbody class="border">
	<?php if( empty( $orders_date[$method] ) ) { $orders_date[$method] = array(); $orders_date[$method]['list'] = array(); $orders_date[$method]['amount_total'] = 0; } ?>
	<?php foreach( $orders_date[$method]['list'] as $payment_received ) : ?>
	<tr>
		<td class="margin-sides"><?php echo ( !empty($payment_received['invoice_ref']) ) ? $payment_received['invoice_ref'] : ''; ?></td>
		<td class="force-one-line margin-sides"><?php echo ( !empty($payment_received['date']) && $payment_received_date = $payment_received['date'] ) ? ( $from_to ) ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $payment_received_date, true ) : mysql2date( get_option( 'time_format' ), $payment_received_date, true ) : ''; ?></td>
		<td class="margin-sides"><?php echo ( !empty($payment_received['order']->_order_postmeta['order_key']) ) ? $payment_received['order']->_order_postmeta['order_key'] : ''; ?></td>
		<td class="max-width align-center force-one-line"><?php
			if( !empty($payment_received['order']->_order_postmeta['order_items']) ) {
				$first = true;
				foreach( $payment_received['order']->_order_postmeta['order_items'] as $item ) {
					if( $first ) {
						$first = !$first;
						echo $item['item_name'];
					} else {
						echo ', ' .$item['item_name'];
					}
				}
			}
		?></td>
		<td class="align-right margin-sides"><?php echo ( !empty($payment_received['received_amount']) ) ?  number_format( $payment_received['received_amount'], 2, '.', '' ) : ''; ?><?php echo wpshop_tools::wpshop_get_currency(); ?></td>
	</tr>
	<?php endforeach; ?>
</tbody>
<tfoot class="border">
	<tr>
		<td colspan="4" class="margin-sides bold"><?php _e('Bank deposit sum', 'wpshop'); ?></td>
		<td class="align-right border margin-sides bold"><?php echo number_format( $orders_date[$method]['amount_total'], 2, '.', '' ) . wpshop_tools::wpshop_get_currency(); ?></td>
	</tr>
</tfoot>
</table>