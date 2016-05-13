<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell"><?php echo mysql2date( get_option('date_format'), $opinion->opinion_date, true ); ?></div>
		<div class="wps-table-cell"><?php echo $order_meta['order_key']; ?></div>
		<div class="wps-table-cell"><?php echo $opinion->opinion_content; ?></div>
		<div class="wps-table-cell"></div>
</div>
