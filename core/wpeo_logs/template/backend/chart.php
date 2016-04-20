<?php if ( !defined( 'ABSPATH' ) ) exit; ?>

<br /><h3><?php _e( 'Chart', 'wpeolog-i18n' ); ?></h3>

<?php if( empty( $count_error ) && empty( $count_info ) && empty( $count_warning ) ): ?>
	<?php _e( 'No data for chart', 'wpeolog-i18n'); ?>
<?php else: ?>
	<canvas class="alignleft" id="myChart" width="200" height="200"></canvas>
<?php endif; ?>

<script type="text/javascript">
jQuery( document ).ready( function() {
	var data =
	[
		{
	        value: <?php echo $count_error; ?>,
	        color:"#F7464A",
	        highlight: "#FF5A5E",
	        label: "Error"
	    },
	    {
	        value: <?php echo $count_info; ?>,
	        color: "#46BFBD",
	        highlight: "#5AD3D1",
	        label: "Information"
	    },
	    {
	        value: <?php echo $count_warning; ?>,
	        color: "#FDB45C",
	        highlight: "#FFC870",
	        label: "Warning"
	    }
	];

	var myPie = new Chart( document.getElementById( "myChart" ).getContext( "2d" ) ).Pie( data );
});
</script>
