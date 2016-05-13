<?php if ( !defined( 'ABSPATH' ) ) exit;
$iban_infos = get_option( 'wpshop_paymentMethod_options' );
$iban_infos = ( !empty($iban_infos) && !empty($iban_infos['banktransfer']) ) ?$iban_infos['banktransfer'] : array();
?>
<div class="wps-boxed">
	<p><?php _e('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop'); ?></p>
	<p><?php _e('You have to do a bank transfer on account detailled below:', 'wpshop'); ?></p>
	<p><?php _e('Bank name', 'wpshop'); ?> : <?php echo ( ( !empty($iban_infos['bank_name']) ) ? $iban_infos['bank_name'] : ''); ?><br/>
	<?php _e('IBAN', 'wpshop'); ?> : <?php echo ( ( !empty($iban_infos['iban']) ) ? $iban_infos['iban'] : ''); ?><br/>
	<?php _e('BIC/SWIFT', 'wpshop'); ?> : <?php echo ( ( !empty($iban_infos['bic']) ) ? $iban_infos['bic'] : ''); ?><br/>
	<?php _e('Account owner name', 'wpshop'); ?> : <?php echo ( ( !empty($iban_infos['accountowner']) ) ? $iban_infos['accountowner'] : ''); ?></p>
	<p><?php _e('Your order will be shipped upon receipt of funds.', 'wpshop'); ?></p>
</div>
<?php
// Empty Cart
$wps_cart->empty_cart();
?>