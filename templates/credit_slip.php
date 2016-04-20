<?php
/*	Wordpress - Ajax functionnality activation	*/
DEFINE('DOING_AJAX', true);
/*	Wordpress - Main bootstrap file that load wordpress basic files	*/
require_once('../../../../wp-load.php');
/*	Wordpress - Admin page that define some needed vars and include file	*/
require_once(ABSPATH . 'wp-admin/includes/admin.php');

$order_id = (!empty($_GET['order_id'])) ? wpshop_tools::varSanitizer($_GET['order_id']) : null;
$invoice_ref = (!empty($_GET['credit_ref'])) ? wpshop_tools::varSanitizer($_GET['credit_ref']) : null;
$mode = (!empty($_GET['mode'])) ? wpshop_tools::varSanitizer($_GET['mode']) : 'html';
// $is_credit_slip = (!empty($_GET['credit_slip'])) ? wpshop_tools::varSanitizer($_GET['credit_slip']) : null;

if ( !empty($order_id) ) {
// 	/**	Order reading	*/
	$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
	$html_content = wps_credit::generate_credit_slip($order_id, $invoice_ref );

	if ( $mode == 'pdf') {
		require_once(WPSHOP_LIBRAIRIES_DIR.'HTML2PDF/html2pdf.class.php');
		try {
			$html_content = wpshop_display::display_template_element('invoice_page_content_css', array(), array(), 'common') . '<page>' . $html_content . '</page>';
			$html2pdf = new HTML2PDF('P', 'A4', 'fr');

			$html2pdf->setDefaultFont('Arial');
			$html2pdf->writeHTML($html_content);

			$html2pdf->Output('order_' .$order_id. '.pdf', 'D');
		}
		catch (HTML2PDF_exception $e) {
			echo $e;
			exit;
		}
	}
	else {
		$tpl_component['INVOICE_CSS'] =  wpshop_display::display_template_element('invoice_page_content_css', array(), array(), 'common');
		$tpl_component['INVOICE_MAIN_PAGE'] = $html_content;
		$tpl_component['INVOICE_TITLE_PAGE'] = sprintf( __('Credit slip #%s for Order #%s', 'wpshop'), $invoice_ref, $order_postmeta['order_key']);
		echo wpshop_display::display_template_element('invoice_page', $tpl_component, array(), 'common');
	}
}