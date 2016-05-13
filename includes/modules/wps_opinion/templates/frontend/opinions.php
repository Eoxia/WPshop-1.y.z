<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div><span class="wps-h3"><?php _e( 'My opinions', 'wps_opinion'); ?></span></div>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Date', 'wps_opinion'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product', 'wps_opinion'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Opinion', 'wps_opinion'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Rate', 'wps_opinion'); ?></div>
	</div>

	<?php
	 if( !empty($opinions) ) : 
		foreach( $opinions as $opinion ): 
			require( wpshop_tools::get_template_part( WPS_OPINION_DIR, $this->template_dir, "frontend", "opinion") );
		endforeach; 
	else : 
		echo '<div class="wps-alert-info">' .__( 'You have never post opinion', 'wps_opinion'). '</div>';
	endif;  
	 ?>
</div>


