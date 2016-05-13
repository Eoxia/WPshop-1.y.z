<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed">
<?php
echo wpshop_tools::create_custom_hook('wpshop_payment_actions');
// Empty the cart
$wps_cart->empty_cart(); 
?>
</div>
