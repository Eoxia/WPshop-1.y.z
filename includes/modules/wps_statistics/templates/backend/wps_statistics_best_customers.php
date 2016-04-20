<?php if ( !empty($customer_recap) ) : ?>
<?php arsort( $customer_recap ); ?>
<canvas id="wps_best_customers" width="" height=""></canvas>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		var pieData = [
						<?php 
						$i = 0;
						foreach( $customer_recap as $customer_id => $customer ): ?>
						<?php if( $i < 8 ) : ?>
						{value:<?php echo round($customer, 2); ?>, color:"<?php echo $colors[$i]; ?>"},	
						<?php endif; ?>
						<?php endforeach; ?>
			       		];
		var best_customers = new Chart(document.getElementById("wps_best_customers").getContext("2d")).Pie(pieData);
	});
</script>
<ul class="wps_statistics_legend">
<?php 
	$i = 0;
	foreach( $customer_recap as $customer_id => $customer ):
		if( $i < 8 ) :
			$user_data = get_userdata( $customer_id );
			$customer_name = ( !empty($user_data) && !empty($user_data->last_name) ) ? strtoupper( $user_data->last_name) : '';
			$customer_name .= ( !empty($user_data) && !empty($user_data->first_name) ) ? ' '.$user_data->first_name : '';
			$customer_email = ( !empty($user_data) && !empty($user_data->user_email) ) ? ' - '.$user_data->user_email : '';
		?>
			<li><div style="background : <?php echo $colors[$i]; ?>;" class="legend_indicator"></div><?php echo $customer_name.' '.$customer_email.' (' .number_format($customer, 2, '.', '').' '.wpshop_tools::wpshop_get_currency( false ); ?>)</li>
		<?php
		endif;
	endforeach;
?>
</ul>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'There is no best customer for the moment', 'wpshop'); ?></div>
<?php endif; ?>
