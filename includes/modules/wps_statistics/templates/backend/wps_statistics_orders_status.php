<?php if( !empty($orders_status) ) : ?>
<canvas id="wps_orders_status" width="" height=""></canvas>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		var pieData2 = [
<?php foreach( $orders_status as $status => $count ) : ?>
						{value: <?php echo $count; ?>, color:"<?php echo $colors[strtolower($status)]; ?>"},
<?php endforeach; ?>	
						];	
		var pie_order_status = new Chart(document.getElementById("wps_orders_status").getContext("2d")).Pie(pieData2);        		
	});
</script>
<ul class="wps_statistics_legend">
	<?php foreach( $orders_status as $status => $count ) : ?>
		<li><span style="background : <?php echo $colors[strtolower($status)]; ?>;" class="legend_indicator"></span><span><?php _e($payment_status[ strtolower($status) ], 'wpshop' ); ?> (<?php echo $count; ?>)</span></li>
	<?php endforeach; ?>	
</ul>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No order have been created', 'wpshop' ); ?></div>
<?php endif; ?>
