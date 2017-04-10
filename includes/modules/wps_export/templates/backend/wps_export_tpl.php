<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<!-- BOX EXPORTS -->
<div class="wps-row">
	<div class="wps-form-group">
		<label for="wps_export_list"><?php _e('Select file to download', 'wps_export'); ?></label>
		<div class="wps-form">
			<select id="wps_export_list">
				<option selected disabled><?php _e('Select file', 'wps_export'); ?></option>
				<option value='users_date'><?php _e('List of customers registered between 2 dates', 'wps_export'); ?></option>
				<option value='users_orders'><?php _e('List of customers who ordered to at least', 'wps_export'); ?></option>
				<option value='orders_date'><?php _e('List of orders between 2 dates', 'wps_export'); ?></option>
			</select>
		</div>
	</div>
</div>
<div class="wps-row" style="display:none" id="wps_export_minp_group">
	<div id="wps_export_minp_form" class="wps-form-group">
		<label for="wps_export_minp"><?php _e('Price minimum', 'wps_export'); ?></label>
		<div class="wps-form">
			<input type="text" id="wps_export_minp">
		</div>
		<label for="wps_export_minp_forder"><?php _e('Free orders', 'wps_export'); ?></label>
		<div class="wps-form">
			<input type="checkbox" id="wps_export_minp_forder" value="free_order">
		</div>
	</div>
</div>
<div class="wps-row wps-gridwrapper2-padded" style="display:none" id="wps_export_dates_group">
	<div id="wps_export_bdte_form" class="wps-form-group">
		<label for="wps_export_bdte"><?php _e('Begin date', 'wps_export'); ?></label>
		<div class="wps-form">
			<input type="text" id="wps_export_bdte" class="datepicker">
		</div>
	</div>
	<div id="wps_export_edte_form" class="wps-form-group">
		<label for="wps_export_edte"><?php _e('End date', 'wps_export'); ?></label>
		<div class="wps-form">
			<input type="text" id="wps_export_edte" class="datepicker">
		</div>
	</div>
</div>
<a id="wps_export_download_btn" class="wps-bton-first-mini-rounded"><?php _e('Download', 'wps_export'); ?></a>
<div class="wpshop_cls"></div>
