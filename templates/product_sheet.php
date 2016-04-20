<?php
/*	Wordpress - Ajax functionnality activation	*/
DEFINE('DOING_AJAX', true);
/*	Wordpress - Main bootstrap file that load wordpress basic files	*/
require_once('../../../../wp-load.php');
/*	Wordpress - Admin page that define some needed vars and include file	*/
require_once(ABSPATH . 'wp-admin/includes/admin.php');

$product_id = ( !empty($_GET['pid']) ) ? intval($_GET['pid']) : null;
$user_id = get_current_user_id();
if( !empty($_GET['pid']) && get_post_type( $product_id ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT && $user_id != 0 && current_user_can( 'manage_options' ) ) {
	$wps_product_administration_ctr = new wps_product_administration_ctr();
	$html_content = $wps_product_administration_ctr->generate_product_sheet_datas( $_GET['pid'] );
	$product_post = get_post( $_GET['pid'] );
	require_once(WPSHOP_LIBRAIRIES_DIR.'HTML2PDF/html2pdf.class.php');
	try {
		$html2pdf = new HTML2PDF('P', 'A4', 'fr');
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->setDefaultFont('Arial');
		$html2pdf->writeHTML($html_content);
		$html2pdf->Output('product-' .$_GET['pid'].'-'.$product_post->post_name.'.pdf', 'D');
	}
	catch (HTML2PDF_exception $e) {
		echo $e;
		exit;
	}
}