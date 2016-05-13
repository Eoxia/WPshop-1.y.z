<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div>
	<?php 
	echo wpshop_CIC::display_form($_SESSION['order_id']);
	// Empty Cart
	$wps_cart->empty_cart(); 
	?>
</div>
