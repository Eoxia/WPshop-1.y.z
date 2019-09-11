<?php if ( !defined( 'ABSPATH' ) ) exit;
$company_infos = get_option( 'wpshop_company_info' );
$amount = ( !empty($_SESSION['cart']['order_amount_to_pay_now']) ) ? wpshop_tools::formate_number( $_SESSION['cart']['order_amount_to_pay_now'] ) : 0;
?>
<div class="wps-boxed">
	<p><?php _e('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop'); ?></p>
	<p><?php echo sprintf(__('You have to send the check with an amount of %s to about "%s" to the adress :', 'wpshop'), $amount.' '.wpshop_tools::wpshop_get_currency( false ), ( ( !empty($company_infos['company_name']) ) ? $company_infos['company_name'] : '') ); ?></p>
	<p><?php echo ( ( !empty($company_infos['company_name']) ) ? $company_infos['company_name'] : ''); ?><br/>
	<?php echo ( ( !empty($company_infos['company_street']) ) ? $company_infos['company_street'] : ''); ?><br/>
	<?php echo ( ( !empty($company_infos['company_postcode']) ) ? $company_infos['company_postcode'] : ''); ?> <?php echo ( ( !empty($company_infos['company_city']) ) ? $company_infos['company_city'] : ''); ?> <br/>
	<?php echo ( ( !empty($company_infos['company_country']) ) ? $company_infos['company_country'] : ''); ?></p>
	<p><?php _e('Your order will be shipped upon receipt of the check.', 'wpshop'); ?></p>
</div>

<?php
$wps_cart->empty_cart();
?>
