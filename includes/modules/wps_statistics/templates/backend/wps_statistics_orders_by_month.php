<canvas id="wps_orders_summary" width="" height=""></canvas>
<?php if( !empty($order_recap) ) : ?>
<?php 
$i = 0;
$order_recap = array_slice( $order_recap, 0, 2, true );
$order_recap = array_reverse( $order_recap, true );
?>
<script type="text/javascript">
jQuery( document ).ready( function() {
	var data  = { 
					bezierCurve : false,
					labels : ["<?php _e('January', 'wpshop'); ?>","<?php _e('February', 'wpshop'); ?>","<?php _e('March', 'wpshop'); ?>","<?php _e('April', 'wpshop'); ?>","<?php _e('May', 'wpshop'); ?>","<?php _e('June', 'wpshop'); ?>","<?php _e('July', 'wpshop'); ?>","<?php _e('August', 'wpshop'); ?>" ,"<?php _e('September', 'wpshop'); ?>" ,"<?php _e('October', 'wpshop'); ?>","<?php _e('November', 'wpshop'); ?>","<?php _e('December', 'wpshop'); ?>"],
					datasets : [
								<?php foreach( $order_recap as $y => $year ) : 
										if ( $i < 2 ) :
								?>
											{fillColor : "<?php echo $colors[$i][0]; ?>",pointStrokeColor : "#fff",strokeColor :"<?php echo $colors[$i][1]; ?>", pointColor :"<?php echo $colors[$i][1]; ?>",
												data : [
														<?php for( $j = 1; $j <= 12; $j++) { 	
															if( !empty($year[$j]) ) :
																echo round($year[$j]).','; 
															else :
																echo '0,';
															endif;
														 	} ?>
													]
											},			
								<?php 
											$i++;
										endif;
									endforeach; 
								?>
							   ]
				};
	var LineOrders = new Chart(document.getElementById("wps_orders_summary").getContext("2d")).Line(data);
});
</script>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No order has been made on your shop', 'wpshop'); ?></div>
<?php endif; ?>


