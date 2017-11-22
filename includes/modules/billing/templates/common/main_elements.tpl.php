<?php if ( !defined( 'ABSPATH' ) ) exit;
 ob_start();?><!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  dir="ltr" lang="en-US">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  dir="ltr" lang="en-US">
<!--<![endif]-->
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>{WPSHOP_INVOICE_TITLE_PAGE}</title>
		{WPSHOP_INVOICE_CSS}
	</head>
	<body >
		{WPSHOP_INVOICE_MAIN_PAGE}
	</body>
</html><?php
$tpl_element['common']['default']['invoice_page'] = ob_get_contents();
ob_end_clean();


ob_start();
?><style type="text/css" >
	.invoice_main_title_container {
		width: 100%;
	}
	.invoice_main_title span {
		font-size: 40px;
	}
	.invoice_main_title_container td {
		width: 50%;
		text-align: right;
	}
	.invoice_logo {
		text-align : left !important;
	}
	.invoice_logo img{
		max-height : 120px;
		height : auto;
	}
	.invoice_part_main_container {
		width: 100%;
		margin-bottom: 30px;
	}
	.invoice_sender_container, .invoice_receiver_container {
		width: 40%;
		padding: 10px;
	}
	.invoice_part_main_container .invoice_emtpy_cell {
		width: 20%;
	}
	.invoice_sender_container {
		background-color: #F1F1F1;
	}
	.invoice_receiver_container {
		border: 1px solid #000000;
	}

	.invoice_lines  {
		border-collapse: collapse;
		border: 1px solid #CCCCCC;
		width: 100%;
	}
	.invoice_lines th, .invoice_lines td {
		font-size : 11px;
		border: 1px solid #CCCCCC;
	}
	.invoice_lines th {
		padding: 2px;
	}
	.invoice_lines td {
		padding: 2px 0px;
	}

	.wpshop_alignright {
		text-align: right;
	}
	.wpshop_aligncenter {
		text-align: center;
	}
	.wpshop_cart_variation_details_line {
		clear: both;
		margin: 6px 10px;
		word-wrap: break-word;
	}
	.invoice_line_ref {
		width: 30mm;
		word-wrap: break-word;
		-webkit-hyphens: auto;
		-moz-hyphens: auto;
		-ms-hyphens: auto;
		-o-hyphens: auto;
		hyphens: auto;
	}
	.invoice_line_product_name {
		width: 55mm;
	}
	.wpshop_invoice_summaries_container {
		margin: 30px 0px;
		width: 100%;
		border-collapse: collapse;
	}
	.wpshop_invoice_summaries_container_infos {
		width: 60%;
	}
	.wpshop_invoice_summaries_container_totals {
		width: 40%;
	}
	.invoice_summary {
		width: 100%;
	}

	.invoice_summary_row_title {
		width: 80%;
		padding-left: 45%;
		text-align: left;
	}
	.invoice_summary_row_amount {
		width: 20%;
		text-align: right;
	}
	.wpshop_invoice_received_payment_container {
		width: 100%;
		float: right;
		margin: 40px 0px;
	}
	.wpshop_invoice_received_payment_container_infos {
		width: 30%;
	}
	.received_payment_list {
		width: 100%;
	}
	.received_payment_list_header th {
		background-color: #CCCCCC;
	}
	.received_payment_list_row td {
		text-align: center;
	}
	.footer {
		margin-top : 20px;
		height : 40px;
		width : 100%;
		float : left;
		text-align : center;
		position : relative;
	}
	.iban_infos {
		float : left;
		width : 100%;
		text-align : left;
		height : auto;
	}
</style><?php
$tpl_element['common']['default']['invoice_page_content_css'] = ob_get_contents();
ob_end_clean();

ob_start();
?><style type="text/css" >
	.invoice_summary_row_title {
		width: 60%;
		text-align: left;
	}
	.invoice_summary_row_amount {
		width: 40%;
		text-align: right;
	}
	.wpshop_invoice_received_payment_container {
		width: 100%;
		float: right;
		margin: 40px -250px;
	}
	.received_payment_list_row {
		text-align: center;
		word-wrap: break-word;
	}
		</style><?php
$tpl_element['common']['default']['invoice_print_page_content_css'] = ob_get_contents();
ob_end_clean();



ob_start();
?>

		<table class="invoice_main_title_container" >
			<tbody>
				<tr>
					<td class="invoice_logo">{WPSHOP_INVOICE_LOGO}</td>
					<td class="invoice_main_title" >
						<span>{WPSHOP_INVOICE_TITLE}&nbsp;&nbsp;{WPSHOP_INVOICE_ORDER_INVOICE_REF}</span><br/>
						{WPSHOP_INVOICE_ORDER_KEY_INDICATION} {WPSHOP_INVOICE_VALIDATE_TIME}
					</td>
				</tr>
				<tr>
					<td></td>
					<td style="text-align: right;" >
						{WPSHOP_INVOICE_ORDER_DATE_INDICATION}<br/>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="invoice_part_main_container" >
			<tbody>
				<tr>
					<td class="invoice_sender_title" ><?php _e('Sender', 'wpshop'); ?></td>
					<td class="invoice_emtpy_cell" ></td>
					<td class="invoice_receiver_title" ><?php _e('Customer', 'wpshop'); ?></td>
				</tr>
				<tr>
					<td class="invoice_sender_container" valign="top">
						{WPSHOP_INVOICE_SENDER}
					</td>
					<td class="invoice_emtpy_cell" ></td>
					<td class="invoice_receiver_container" valign="top">
						{WPSHOP_INVOICE_RECEIVER}
					</td>
				</tr>
				<tr>
					<td colspan="3">
						{WPSHOP_INVOICE_TRACKING}
					</td>
				</tr>
			</tbody>
		</table>
		<h4 style="text-align: right; width: 100%; margin: 30px 0px 12pt;">{WPSHOP_AMOUNT_INFORMATION}</h4>
		<table class="invoice_lines" >
			<thead>
				{WPSHOP_INVOICE_HEADER}
			</thead>
			<tbody>
				{WPSHOP_INVOICE_ROWS}
			</tbody>
		</table>
		{WPSHOP_INVOICE_SUMMARY_PART}
		<table class="iban_infos">
			<tr><td>
				{WPSHOP_IBAN_INFOS}
			</td></tr>
		</table>
		{WPSHOP_RECEIVED_PAYMENT}
		{WPSHOP_INVOICE_FOOTER}
		<?php
$tpl_element['common']['default']['invoice_page_content'] = ob_get_contents();
ob_end_clean();









ob_start();
?>		<table class="wpshop_invoice_received_payment_container" >
			<tbody>
				<tr>
				<td class="wpshop_invoice_received_payment_container_list wpshop_invoice_summaries_container_received_payment" >
					<?php _e('Received payment', 'wpshop'); ?>
					<table class="received_payment_list" >
						<thead>
							<tr class="received_payment_list_header" >
								<th><?php _e('Date', 'wpshop'); ?></th>
								<th><?php _e('Invoice ref.', 'wpshop'); ?></th>
								<th><?php _e('Method', 'wpshop'); ?></th>
								<th><?php _e('Amount', 'wpshop'); ?></th>
								<th><?php _e('Ref.', 'wpshop'); ?></th>
							</tr>
						</thead>
						<tbody>
							{WPSHOP_ORDER_RECEIVED_PAYMENT_ROWS}
						</tbody>
					</table>
				</td>
			</tr></tbody>
		</table>

		<?php
$tpl_element['common']['default']['received_payment'] = ob_get_contents();
ob_end_clean();









ob_start();
?><tr class="received_payment_list_row" >
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_DATE}</td>
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_INVOICE_REF}</td>
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_METHOD}</td>
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_RECEIVED_AMOUNT}</td>
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_PAYMENT_REFERENCE}</td>
</tr><?php
$tpl_element['common']['default']['received_payment_row'] = ob_get_contents();
ob_end_clean();










ob_start();
?><tr>
	<th><?php _e('Ref.', 'wpshop'); ?></th>
	<th><?php _e('Name', 'wpshop'); ?></th>
	<th><?php _e('Qty', 'wpshop'); ?></th>
	<th><?php _e('U.P ET', 'wpshop'); ?></th>
	<th><?php _e('Discount', 'wpshop'); ?></th>
	<th><?php _e('Total ET', 'wpshop'); ?></th>
	<th><?php _e('Taxes amount', 'wpshop'); ?></th>
	<th><?php _e('Total ATI', 'wpshop'); ?></th>
</tr><?php
$tpl_element['common']['default']['invoice_row_header'] = ob_get_contents();
ob_end_clean();




ob_start();
?><tr>
	<th><?php _e('Ref.', 'wpshop'); ?></th>
	<th><?php _e('Name', 'wpshop'); ?></th>
	<th><?php _e('Qty', 'wpshop'); ?></th>
	<th><?php _e('U.P ET', 'wpshop'); ?></th>
	<th><?php _e('Unit Discount', 'wpshop'); ?></th>
	<th><?php _e('Global Discount', 'wpshop'); ?></th>
	<th><?php _e('Discounted Total ET', 'wpshop'); ?></th>
	<th><?php _e('Taxes', 'wpshop'); ?></th>
	<th><?php _e('Total ATI', 'wpshop'); ?></th>
</tr><?php
$tpl_element['common']['default']['invoice_row_header_with_discount'] = ob_get_contents();
ob_end_clean();




ob_start();
?><tr>
	<th><?php _e('Name', 'wpshop'); ?></th>
	<th><?php _e('Total ET', 'wpshop'); ?></th>
	<th><?php _e('Taxes amount', 'wpshop'); ?></th>
	<th><?php _e('Total ATI', 'wpshop'); ?></th>
</tr><?php
$tpl_element['common']['default']['credit_slip_row_header'] = ob_get_contents();
ob_end_clean();




ob_start();
?><tr>
	<td class="invoice_line_product_name" >{WPSHOP_INVOICE_ROW_ITEM_NAME}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TOTAL_HT}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TVA_TOTAL_AMOUNT}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TOTAL_TTC}</td>
</tr><?php
$tpl_element['common']['default']['credit_slip_row'] = ob_get_contents();
ob_end_clean();



ob_start();
?><tr>
	<th><?php _e('Reference', 'wpshop'); ?></th>
	<th><?php _e('Name', 'wpshop'); ?></th>
	<th><?php _e('Qty', 'wpshop'); ?></th>
</tr><?php
$tpl_element['common']['default']['bon_colisage_row_header'] = ob_get_contents();
ob_end_clean();







ob_start();
?><tr>
	<td class="invoice_line_ref" >{WPSHOP_INVOICE_ROW_ITEM_REF}</td>
	<td class="invoice_line_product_name" >
		{WPSHOP_INVOICE_ROW_ITEM_NAME}
		{WPSHOP_INVOICE_ROW_ITEM_DETAIL}
	</td>
	<td class="wpshop_aligncenter" >{WPSHOP_INVOICE_ROW_ITEM_QTY}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_PU_HT}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_DISCOUNT_AMOUNT}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TOTAL_HT}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TVA_TOTAL_AMOUNT} ({WPSHOP_INVOICE_ROW_ITEM_TVA_RATE}%)</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TOTAL_TTC}</td>
</tr><?php
$tpl_element['common']['default']['invoice_row'] = ob_get_contents();
ob_end_clean();


ob_start();
?><tr>
	<td class="invoice_line_ref" >{WPSHOP_INVOICE_ROW_ITEM_REF}</td>
	<td class="invoice_line_product_name" >
		{WPSHOP_INVOICE_ROW_ITEM_NAME}
		{WPSHOP_INVOICE_ROW_ITEM_DETAIL}
	</td>
	<td class="wpshop_aligncenter" >{WPSHOP_INVOICE_ROW_ITEM_QTY}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_PU_HT}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_UNIT_DISCOUNT_AMOUNT} ({WPSHOP_INVOICE_ROW_ITEM_UNIT_DISCOUNT_VALUE}%)</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_GLOBAL_DISCOUNT_AMOUNT} ({WPSHOP_INVOICE_ROW_ITEM_GLOBAL_DISCOUNT_VALUE}%)</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_DISCOUNTED_HT_TOTAL}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TVA_TOTAL_AMOUNT} ({WPSHOP_INVOICE_ROW_ITEM_TVA_RATE}%)</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TOTAL_TTC}</td>
</tr><?php
$tpl_element['common']['default']['invoice_row_with_discount'] = ob_get_contents();
ob_end_clean();


ob_start();
?><tr>
	<td class="invoice_line_ref" >{WPSHOP_INVOICE_ROW_ITEM_REF}</td>
	<td class="invoice_line_product_name" >
		{WPSHOP_INVOICE_ROW_ITEM_NAME}
		{WPSHOP_INVOICE_ROW_ITEM_DETAIL}
	</td>
	<td class="invoice_line_product_name" >{WPSHOP_INVOICE_ROW_ITEM_QTY}</td>

</tr><?php
$tpl_element['common']['default']['bon_colisage_row'] = ob_get_contents();
ob_end_clean();

/*	Product variation detail in cart					Panier detail des variations */
ob_start();
?><span class="wpshop_cart_variation_details_line" >{WPSHOP_VARIATION_NAME} : {WPSHOP_VARIATION_VALUE}</span><br/><?php
$tpl_element['common']['default']['cart_variation_detail'] = ob_get_contents();
ob_end_clean();

ob_start();
?><br/>{WPSHOP_CART_PRODUCT_MORE_INFO}<?php
$tpl_element['common']['default']['invoice_row_item_detail'] = ob_get_contents();
ob_end_clean();


ob_start();
?><tr>
	<td class="invoice_summary_row_title" >{WPSHOP_SUMMARY_ROW_TITLE}</td>
	<td class="invoice_summary_row_amount" >{WPSHOP_SUMMARY_ROW_VALUE}</td>
</tr><?php
$tpl_element['common']['default']['invoice_summary_row'] = ob_get_contents();
ob_end_clean();


ob_start();
?>
<strong>{WPSHOP_COMPANY_LEGAL_STATUT} {WPSHOP_COMPANY_NAME}</strong><br/>
{WPSHOP_COMPANY_STREET}<br/>
{WPSHOP_COMPANY_POSTCODE} {WPSHOP_COMPANY_CITY}<br/>
{WPSHOP_COMPANY_COUNTRY}<br/><br/>

<?php _e('Phone', 'wpshop'); ?> : {WPSHOP_COMPANY_PHONE}<br/>
<?php _e('Fax', 'wpshop'); ?> : {WPSHOP_COMPANY_FAX}<br/>
<?php _e('Email', 'wpshop'); ?> : {WPSHOP_COMPANY_EMAIL}<br/>
<?php _e('Website', 'wpshop'); ?> : {WPSHOP_COMPANY_WEBSITE}<br/>

<?php
$tpl_element['common']['default']['invoice_sender_formatted_address'] = ob_get_contents();
ob_end_clean();



ob_start();
?>
{WPSHOP_CIVILITY} {WPSHOP_ADDRESS_LAST_NAME} {WPSHOP_ADDRESS_FIRST_NAME}<br/>
{WPSHOP_COMPANY}<br/>
{WPSHOP_ADDRESS}<br/>
{WPSHOP_POSTCODE} {WPSHOP_CITY}<br/>
{WPSHOP_STATE}<br/>
{WPSHOP_COUNTRY}<br/><br/>
{WPSHOP_PHONE}<br/>
{WPSHOP_ADDRESS_USER_EMAIL}

<?php
$tpl_element['common']['default']['invoice_receiver_formatted_address'] = ob_get_contents();
ob_end_clean();

ob_start();
?>
<table class="footer">
<tr>
<td>
<strong>{WPSHOP_COMPANY_LEGAL_STATUT} {WPSHOP_COMPANY_NAME}</strong>, {WPSHOP_COMPANY_STREET} {WPSHOP_COMPANY_POSTCODE} {WPSHOP_COMPANY_CITY} {WPSHOP_COMPANY_COUNTRY}<br/>
<?php _e('Phone', 'wpshop'); ?> : {WPSHOP_COMPANY_PHONE} / <?php _e('Fax', 'wpshop'); ?> : {WPSHOP_COMPANY_FAX} / <?php _e('Email', 'wpshop'); ?> : {WPSHOP_COMPANY_EMAIL} / <?php _e('Website', 'wpshop'); ?> : {WPSHOP_COMPANY_WEBSITE}<br/>
{WPSHOP_COMPANY_LEGAL_STATUT} {WPSHOP_COMPANY_CAPITAL} {WPSHOP_COMPANY_SIRET}<br/>{WPSHOP_COMPANY_TVA_INTRA} {WPSHOP_COMPANY_SIREN} {WPSHOP_COMPANY_RCS}
</td>
</tr>
</table>
<?php
$tpl_element['common']['default']['invoice_footer'] = ob_get_contents();
ob_end_clean();

ob_start();
?>
<table class="wpshop_invoice_summaries_container" >
<tbody>
<tr>
<td class="wpshop_invoice_summaries_container_infos" ></td>
<td class="wpshop_invoice_summaries_container_totals" >
<table class="invoice_summary" >
<tbody>
<tr>
<td class="invoice_summary_row_title" ><?php _e('Order grand total ET', 'wpshop'); ?></td>
	<td class="invoice_summary_row_amount" >{WPSHOP_INVOICE_ORDER_TOTAL_HT} {WPSHOP_CURRENCY}</td>
</tr>
{WPSHOP_INVOICE_SUMMARY_TOTAL_DISCOUNTED}
{WPSHOP_INVOICE_SUMMARY_TAXES}
<tr class="wpshop_invoice_grand_total" >
	<td class="invoice_summary_row_title" ><?php _e('Shipping cost', 'wpshop'); ?> {WPSHOP_PRICE_PILOTING}</td>
	<td class="invoice_summary_row_amount" >{WPSHOP_INVOICE_ORDER_SHIPPING_COST} {WPSHOP_CURRENCY}</td>
</tr>
<tr class="wpshop_invoice_grand_total" >
	<td class="invoice_summary_row_title" ><?php _e('Shipping cost taxes', 'wpshop'); ?></td>
	<td class="invoice_summary_row_amount" >{WPSHOP_INVOICE_ORDER_SHIPPING_COST_TAXES} {WPSHOP_CURRENCY}</td>
</tr>
{WPSHOP_INVOICE_ORDER_DISCOUNT}
<tr class="wpshop_invoice_grand_total" >
	<td class="invoice_summary_row_title" ><?php _e('Order grand total ATI', 'wpshop'); ?></td>
							<td class="invoice_summary_row_amount" >{WPSHOP_INVOICE_ORDER_GRAND_TOTAL} {WPSHOP_CURRENCY}</td>
						</tr>
						{WPSHOP_INVOICE_SUMMARY_MORE}
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<?php
$tpl_element['common']['default']['invoice_summary_part'] = ob_get_contents();
ob_end_clean();



ob_start();
?>
<table class="wpshop_invoice_summaries_container" >
<tbody>
<tr>
<td class="wpshop_invoice_summaries_container_infos" ></td>
<td class="wpshop_invoice_summaries_container_totals" >
<table class="invoice_summary" >
<tbody>
		<tr>
			<td class="invoice_summary_row_title" ><?php _e('Order grand total ET', 'wpshop'); ?></td>
			<td class="invoice_summary_row_amount" >{WPSHOP_CREDIT_SLIP_TOTAL_HT} {WPSHOP_CURRENCY}</td>
		</tr>

		{WPSHOP_CREDIT_SLIP_SUMMARY_TVA}

		<tr class="wpshop_invoice_grand_total" >
			<td class="invoice_summary_row_title" ><?php _e('Order grand total ATI', 'wpshop'); ?></td>
			<td class="invoice_summary_row_amount" >{WPSHOP_CREDIT_SLIP_ORDER_GRAND_TOTAL} {WPSHOP_CURRENCY}</td>
		</tr>
		{WPSHOP_INVOICE_SUMMARY_MORE}
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<?php
$tpl_element['common']['default']['credit_slip_summary_part'] = ob_get_contents();
ob_end_clean();


/** DISOUNT PART **/
ob_start();
?>
<tr class="wpshop_invoice_grand_total" >
	<td class="invoice_summary_row_title" ><?php _e('Total ATI before discount', 'wpshop'); ?></td>
	<td class="invoice_summary_row_amount" >{WPSHOP_TOTAL_BEFORE_DISCOUNT} {WPSHOP_CURRENCY}</td>
</tr>
<tr class="wpshop_invoice_grand_total" >
	<td class="invoice_summary_row_title" ><?php _e('Discount', 'wpshop'); ?></td>
	<td class="invoice_summary_row_amount" >-{WPSHOP_DISCOUNT_VALUE} {WPSHOP_CURRENCY}</td>
</tr>
<?php
$tpl_element['common']['default']['invoice_discount_part'] = ob_get_contents();
ob_end_clean();
