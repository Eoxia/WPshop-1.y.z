<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap wps_statistics">
	<h2><span class="dashicons dashicons-chart-area" style="font-size : 30px; width : 30px; height : 30px"></span> <?php _e( 'WPShop Statistics', 'wpshop' )?></h2>

	<div class="wps-boxed">
		<div class="wps-gridwrapper2-padded">
			<div>
				<div>
					<div>
						<span class="wps-h5"><?php _e( 'Sales statistics', 'wpshop'); ?></span>
						<?php $this->wps_display_main_statistics(); ?>
					</div>
				</div>
				<div>
					<div>
						<span class="wps-h5"><?php _e( 'Orders infos', 'wpshop'); ?></span>
						<?php require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, 'backend', 'wps_statistics_average') ); ?>
					</div>
				</div>
			</div>
			<div>
				<div>
					<span class="wps-h5"><?php _e( 'Custom statistics', 'wpshop'); ?></span>
					<div id="wps_statistics_custom_container" class="wps-bloc-loader" ><?php $this->wps_display_custom_statistics(); ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="wps-boxed">
		<div class="wps-gridwrapper3-padded">
			<div>
				<span class="wps-h5"><?php _e( 'Last orders', 'wpshop'); ?></span>
				<?php require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, 'backend', 'wps_statistics_last_orders') ); ?>
			</div>
			<div>
				<span class="wps-h5"><?php _e( 'Most buyed products', 'wpshop'); ?></span>
				<?php require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, 'backend', 'wps_statistics_best_sales') ); ?>
			</div>
			<div>
				<span class="wps-h5"><?php _e( 'Most viewed products', 'wpshop'); ?></span>
				<?php require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, 'backend', 'wps_statistics_most_viewed_products') ); ?>
			</div>
		</div>
		<div class="wps-gridwrapper3-padded">
			<div>
				<span class="wps-h5"><?php _e( 'Best customers per amount', 'wpshop'); ?></span>
				<?php require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, 'backend', 'wps_statistics_best_customers_amount') ); ?>
			</div>
			<div>
				<span class="wps-h5"><?php _e( 'Best customers per count', 'wpshop'); ?></span>
				<?php require( wpshop_tools::get_template_part( WPS_STATISTICS_DIR, WPS_STATISTICS_TEMPLATES_MAIN_DIR, 'backend', 'wps_statistics_best_customers_count') ); ?>
			</div>
		</div>
	</div>

</div>
