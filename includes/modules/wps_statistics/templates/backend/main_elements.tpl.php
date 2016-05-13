<?php if ( !defined( 'ABSPATH' ) ) exit;
$tpl_element = array();

/**
 * BOX STATS
 */
ob_start();
?>
<div id="pageTitleContainer" class="pageTitle">
	<h2><?php _e( 'WPShop Statistics', 'wpshop' ); ?></h2>
</div>
<div class="postbox-container" style="width:100%;">
	<label for="wps_statistics_begin_date"><?php _e( 'Begin date', 'wpshop'); ?> : </label> <input type="text" id="wps_statistics_begin_date" class="date" value="{WPSHOP_STATISTICS_BEGIN_DATE}"/>
	<label for="wps_statistics_end_date"><?php _e( 'End date', 'wpshop'); ?> : </label> <input type="text" id="wps_statistics_end_date" class="date" value="{WPSHOP_STATISTICS_END_DATE}" />
	<button id="wps_change_statistics_date"><?php _e('Reload statistics', 'wpshop'); ?></button> <img src="<?php echo WPSHOP_LOADING_ICON; ?>" alt="<?php _e( 'Loading', 'wpshop' ); ?>" id="wps_statistics_loader"/>
</div>
<div id="wps_statistics_container">
	{WPSHOP_STATISTICS_INTERFACE}
</div>	
<?php
$tpl_element['admin']['default']['wps_statistics_interface'] = ob_get_contents();
ob_end_clean();
	

ob_start();
?>
<div class="postbox-container" style="width:49%;">
{WPSHOP_LEFT_BOXES}
</div>

<div class="postbox-container" style="width:49%; float:right;">
{WPSHOP_RIGHT_BOXES}
</div>
<?php
$tpl_element['admin']['default']['wps_stats'] = ob_get_contents();
ob_end_clean();


ob_start();
?>
<div class="postbox">
	<h3 class="hndle"><span>{WPSHOP_STATISTICS_TITLE}</span></h3>
	<div class="inside" id="inside_{WPSHOP_STATISTICS_CANVAS_ID}">
		<center><canvas id="{WPSHOP_STATISTICS_CANVAS_ID}" width="{WPSHOP_CANVAS_WIDTH}" height="{WPSHOP_CANVAS_HEIGHT}"></canvas></center>
		{WPSHOP_STATISTICS_JS}
	</div>
</div>
<?php
$tpl_element['admin']['default']['wps_postbox'] = ob_get_contents();
ob_end_clean();