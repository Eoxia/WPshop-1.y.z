<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div>
	<?php 
	wpshop_paypal::display_form($_SESSION['order_id']);
	$wps_cart->empty_cart(); 
	?>
</div>
