<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed">
	<p><?php _e('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop'); ?></p>
</div>
<?php 
// Empty Cart
$wps_cart->empty_cart(); 
?>
