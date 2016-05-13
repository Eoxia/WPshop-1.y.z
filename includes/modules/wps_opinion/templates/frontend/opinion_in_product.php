<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-table">
<?php
foreach( $comments_for_product as $comment_for_product ) : 
	if( $comment_for_product->opinion_approved == 1 ) : 
?>
	<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell"><?php echo $comment_for_product->author; ?></div>
		<div class="wps-table-cell"><?php echo mysql2date( get_option('date_format'), $comment_for_product->opinion_date, true ); ?></div>
		<div class="wps-table-cell"><?php echo $comment_for_product->opinion_content; ?></div>
		<div class="wps-table-cell"><?php echo wps_opinion_ctr::display_stars( $comment_for_product->opinion_rate ); ?></div>
	</div>
<?php 
	endif;
endforeach;
?>
</div>