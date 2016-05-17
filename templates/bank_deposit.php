<?php if ( !defined( 'ABSPATH' ) ) exit;

	/*	Wordpress - Ajax functionnality activation	*/
	DEFINE('DOING_AJAX', true);
	/*	Wordpress - Main bootstrap file that load wordpress basic files	*/
	require_once('../../../../wp-load.php');
	/*	Wordpress - Admin page that define some needed vars and include file	*/
	require_once(ABSPATH . 'wp-admin/includes/admin.php');

	$fromdate = empty( $fromdate ) ? date( 'Y-m-d' ) : sanitize_text_field( $_GET['fromdate'] );
	$method = empty( $_GET['method'] ) ? 'all' : sanitize_text_field( $_GET['method'] );
	$mode = !empty( $_GET['mode'] ) ? sanitize_text_field( $_GET['mode'] ) : '';

	$valid_dates = array();
	$valid_dates['relation'] = 'OR';

	$from_to = !empty( $_GET['todate'] ) : sanitize_text_field( $_GET['todate'] ) : date( 'Y-m-d' );

	$fromdate = DateTime::createFromFormat( 'Y-m-d', $fromdate );
	$todate = DateTime::createFromFormat( 'Y-m-d', ($from_to) ? $from_to : $fromdate );
	$datePeriod = new DatePeriod( $fromdate, new DateInterval('P1D'), $todate->modify('+1 day') );

	foreach($datePeriod as $date) {
		$valid_dates[] = array(
			'key'			=> '_order_postmeta',
			'value' 		=> serialize( 'date' ) . 's:19:"' . $date->format( 'Y-m-d' ),
			'compare' 		=> 'LIKE',
		);
	}

	$args = array(
		'posts_per_page' 	=> -1,
		'post_type'			=> WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
		'post_status' 		=> 'publish',
		'meta_query' 		=> array(
			'relation' 			=> 'AND',
			array(
					'key'			=> '_order_postmeta',
					'value' 		=> serialize( 'order_status' ) . serialize( 'pos' ),
					'compare' 		=> 'LIKE',
			),
			$valid_dates,
		),
	);
	if( $method != 'all' ) {
		$args['meta_query'][] = array(
				'key'			=> '_order_postmeta',
				'value' 		=> serialize( 'method' ) . serialize( $method ),
				'compare' 		=> 'LIKE',
		);
	}
	$query = new WP_Query( $args );

	$orders = $query->posts;
	$orders_date = array();

	foreach( $orders as $order ) {
		$order->_order_postmeta = get_post_meta( $order->ID, '_order_postmeta', true );
		foreach( $order->_order_postmeta['order_payment']['received'] as $payment_received ) {
			if( $payment_received['status'] == 'payment_received' ) {
				$payment_received['order'] = $order;
				if( !isset( $orders_date[$payment_received['method']] ) ) { $orders_date[$payment_received['method']]['amount_total'] = 0; }
				@$orders_date[$payment_received['method']]['amount_total'] += $payment_received['received_amount'];
				@$orders_date[$payment_received['method']]['list'][] = $payment_received;
				@$orders_date['all']['amount_total'] += $payment_received['received_amount'];
				@$orders_date['all']['list'][] = $payment_received;
			}
		}
	}

	$company = get_option('wpshop_company_info', array());

	ob_start(); ?>
		<style>
			table {
				width: 100%;
				border-collapse: collapse;
			}
			.border {
				border: 1px solid #000;
			}
			.title {
				font-size: 20px;
			}
			.bold {
				font-weight: bold;
			}
			.width-100 {
				width: 100%;
			}
			.width-70 {
				width: 70%;
			}
			.width-15 {
				width: 15%;
			}
			.width-075 {
				width: 7.5%;
			}
			.margin-sides {
				padding: 0 1% 0 1%;
			}
			.force-one-line {
				white-space: nowrap;
 				overflow: hidden;
			}
			.valign-middle {
				vertical-align: middle;
			}
			.valign-top {
				vertical-align: top;
			}
			.valign-bottom {
				vertical-align: bottom;
			}
			.align-center {
				text-align: center;
			}
			.align-left {
				text-align: left;
			}
			.align-right {
				text-align: right;
			}
		</style>
	<?php
	$content_css = ob_get_contents();
	ob_end_clean();
	ob_start(); ?>
		<table class="border">
			<tr>
				<td class="width-15"><?php echo $company['company_name']; ?></td>
				<td rowspan="4" class="width-70 align-center title bold"><?php _e('Bank deposit', 'wpshop'); ?></td>
				<td class="width-075"><?php _e('SIRET', 'wpshop'); ?>:</td>
				<td class="align-right width-075"><?php echo $company['company_siret']; ?></td>
			</tr>
			<tr>
				<td class="width-15"><?php echo $company['company_phone']; ?></td>
				<td class="width-075"><?php _e('SIREN', 'wpshop'); ?>:</td>
				<td class="align-right width-075"><?php echo $company['company_siren']; ?></td>
			</tr>
			<tr>
				<td class="width-15"><?php echo $company['company_street']; ?></td>
				<td class="valign-top width-075"><?php _e('Date', 'wpshop'); ?>:</td>
				<td class="align-right width-075">
				<?php if( $from_to ) { ?>
					<span class="force-one-line"><?php echo __( 'From', 'wpshop' ) . ' ' . mysql2date( get_option( 'date_format' ), $fromdate, true ); ?></span>
					<span class="force-one-line"><?php echo __( 'to', 'wpshop' ) . ' ' . mysql2date( get_option( 'date_format' ), $from_to, true ); ?></span>
				<?php } else { ?>
					<span class="force-one-line"><?php echo mysql2date( get_option( 'date_format' ), $fromdate, true ); ?></span>
				<?php } ?>
				</td>
			</tr>
			<tr>
				<td class="width-15"><?php echo $company['company_postcode'] . ' ' . $company['company_country']; ?></td>
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
					<td class="force-one-line margin-sides"><?php echo ( !empty($payment_received['date']) ) ? ( $from_to ) ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $payment_received['date'], true ) : mysql2date( get_option( 'time_format' ), $payment_received['date'], true ) : ''; ?></td>
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
	<?php
	$content = ob_get_contents();
	ob_end_clean();

	if ( !empty( $mode ) && $mode == 'pdf') {
		require_once(WPSHOP_LIBRAIRIES_DIR.'HTML2PDF/html2pdf.class.php');
		try {
			$html_content = $content_css . '<page>' . $content . '</page>';
			$html2pdf = new HTML2PDF('P', 'A4', 'fr');

			$html2pdf->setDefaultFont('Arial');
			$html2pdf->writeHTML($html_content);

			$html2pdf->Output( __('Bank deposit', 'wpshop') . ' - ' . mysql2date( get_option( 'date_format' ), $fromdate ) . '.pdf', 'D');
		}
		catch (HTML2PDF_exception $e) {
			echo $e;
			exit;
		}
	} else { ?>
<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  dir="ltr" lang="en-US">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  dir="ltr" lang="en-US">
<!--<![endif]-->
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php _e('Bank deposit', 'wpshop'); ?> - <?php echo mysql2date( get_option( 'date_format' ), $fromdate ); ?></title>
		<?php echo $content_css; ?>
	</head>
	<body>
		<?php echo $content; ?>
	</body>
</html>
	<?php
	}
