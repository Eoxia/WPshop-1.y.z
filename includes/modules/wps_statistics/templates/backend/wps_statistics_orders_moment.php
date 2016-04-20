<?php if( !empty($datadate) ) : 
krsort($datadate);
$tmp_array = array();
foreach( $datadate as $day_name => $day_data ) {
	foreach( $day_data as $hour => $d ) {
		if( empty($tmp_array[$hour]) ) {
			$tmp_array[$hour] = $day_data[$hour];
		}
		else {
			$tmp_array[$hour] += $day_data[$hour];
		}
	}
}

$tmp_value = 0;
foreach ($tmp_array as $values){
	if ($values > $tmp_value)
		$tmp_value = $values;
}

if ($tmp_value / 2 >= 25)
	$scaleStepWidth = $tmp_value / 2;
else
	$scaleStepWidth = $tmp_value;
?>
<canvas id="wps_hourly_orders_canvas" width="<?php echo ( !empty($args['width']) ) ? $args['width'] : ''; ?>" height="<?php echo ( !empty($args['height']) ) ? $args['height'] : ''; ?>"></canvas>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		var data = {labels: [
							<?php 
							for( $i = 0; $i <= 23; $i++ ) { 
								echo '"'.( ($i < 10 ) ? '0' : '' ).$i.'",';
							} ?>
							],
					datasets: 
						[{label : "<?php _e( 'Datas', 'wpshop' ); ?>",
						  fillColor: "#9AE5F4",
						  strokeColor: "#0074A2",
						  pointColor: "#0074A2",
						  pointStrokeColor: "#FFFFFF",
						  pointHighlightFill: "#0074A2",
						  pointHighlightStroke: "#0074A2",
						  data : [
							<?php 
							for( $i = 0; $i <= 23; $i++ ) { 
								echo ( !empty($tmp_array[$i]) ) ? $tmp_array[$i].',' : '0,';
							}
								?>
								]		
						}],	
					};

		var LineOrders = new Chart(document.getElementById("wps_hourly_orders_canvas").getContext("2d")).Line(data, {scaleOverride : true, scaleSteps : <?php echo $tmp_value; ?>,  scaleStepWidth : <?php echo $scaleStepWidth; ?>, scaleStartValue : 0 });
});
</script>


<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No orders have been created', 'wpshop' ); ?></div>
<?php endif; ?>
<div class="wps-form-group">
<label><?php _e( 'Choose the day', 'wpshop'); ?></label>
<div class="wps-form">
<select id="wps-statistics-orders-moment-selectbox">
	<option value=""><?php _e( 'All days', 'wpshop' ); ?></option>
	<?php foreach( $days as $day ) : ?>
	<option value="<?php echo $day; ?>"<?php echo ( !empty($args['choosen_day']) && $args['choosen_day'] == $day ) ? ' selected' : ''; ?>><?php _e( $day, 'wpshop'); ?></option>
	<?php endforeach; ?>
</select>
</div>
</div>