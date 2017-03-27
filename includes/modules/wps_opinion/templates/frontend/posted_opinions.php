<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($posted_opinions) ) : ?>
<span class="wps-h5"><?php _e( 'My opinions', 'wpshop'); ?></span>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Date', 'wps_opinion'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Picture', 'wps_opinion'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product', 'wps_opinion'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Opinion', 'wps_opinion'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Rate', 'wps_opinion'); ?></div>
	</div>

	<?php
	if( !empty($posted_opinions) ) :
		foreach( $posted_opinions as $posted_opinion ) :
			require( wpshop_tools::get_template_part( WPS_OPINION_DIR, $this->template_dir,"frontend", "posted_opinion") );
		endforeach;
	endif;
	?>
</div>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'You never posted an opinion on a product', 'wpshop'); ?></div>
<?php endif; ?>
