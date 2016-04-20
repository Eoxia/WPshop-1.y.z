<div class="wrap">
	<h2><span class="dashicons dashicons-chart-area" style="font-size : 30px; width : 30px; height : 30px"></span> <?php _e( 'WPShop Statistics', 'wpshop' )?></h2>
	<div class="wps-boxed">
		<form method="post" action="">
		<span class="wps-h5"><?php _e( 'Configure your date statistics results', 'wpshop'); ?></span>
		<div class="wps-gridwrapper3-padded">
			<div>
				<div class="wps-form_group">
					<label><?php _e( 'Begin date', 'wpshop'); ?></label>
					<div class="wps-form"><input type="text" id="wps_statistics_begin_date" name="begin_date" class="date" value="<?php echo ( ( !empty($_POST['begin_date']) )  ? $_POST['begin_date'] : date( 'Y-m-d', strtotime( '1 months ago') ) ); ?>"/></div>
				</div>
			</div>

			<div>
				<div class="wps-form_group">
					<label><?php _e( 'End date', 'wpshop'); ?></label>
					<div class="wps-form"><input type="text" id="wps_statistics_end_date" name="end_date" class="date" value="<?php echo ( ( !empty($_POST['end_date']) )  ? $_POST['end_date'] : date( 'Y-m-d') ); ?>"/></div>
				</div>
			</div>

			<div>
				<div class="wps-form_group">
					<label><br/></label>
					<div class="wps-form">
						<!--  <button class="wps-bton-mini-first-rounded" id="wps_change_statistics_date"><?php _e( 'Reload statistics', 'wpshop'); ?></button> -->
						<input type="submit" class="wps-bton-mini-first-rounded" value="<?php _e( 'Reload statistics', 'wpshop'); ?>" />
					</div>
				</div>
			</div>
		</div>
		</form>
	</div>

	<div class="wps-gridwrapper2-padded metabox-holder wps-statistics-container" >
		<div><?php do_meta_boxes('wpshop_statistics','left_column', ''); ?></div>
		<div><?php do_meta_boxes('wpshop_statistics','right_column', ''); ?></div>
	</div>

</div>
