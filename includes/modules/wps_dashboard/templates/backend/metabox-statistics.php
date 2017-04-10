<?php $wps_statistics_ctr = new wps_statistics_ctr(); $wps_statistics_ctr->wps_display_main_statistics(); ?>
<?php require_once( wpshop_tools::get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, 'backend', 'metabox', 'customerStats' ) ); ?>

<center><a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=wpshop_statistics' ); ?>"><span class="dashicons dashicons-chart-line"></span><?php esc_html_e( 'View all statistics', 'wpshop' ); ?></a></center>
