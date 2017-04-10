<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wpshopMainWrap" >
	<div id="wpshopLoadingPicture" class="wpshopHide" ><img src="<?php echo WPSHOP_LOADING_ICON; ?>" alt="loading picture" class="wpshopPageMessage_Icon" /></div>
	<div id="wpshopMessage" class="fade below-h2 wpshopPageMessage <?php echo esc_attr( ! empty( $actionInformationMessage ) ? 'wpshopPageMessage_Updated' : '' ); ?>" ><?php ! empty( $actionInformationMessage ) ? _e( $actionInformationMessage, 'wpshop' ) : ''; ?></div>

	<div class="pageTitle" id="pageTitleContainer" >
		<h2 ><?php esc_html_e( 'Shop dashboard', 'wpshop' ); ?></h2>
	</div>
	<div id="champsCaches" class="wpshop_cls wpshopHide" ></div>
	<div id="wpshop_dashboard">
		<div class="wpshop_cls wps-gridwrapper2-padded metabox-holder wps-statistics-container" id="dashboard-widgets" >
			<?php apply_filters( 'wps_dashboard_notice', '' ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', true ); ?>
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', true ); ?>

			<?php do_meta_boxes( 'wpshop_dashboard', 'left_column', '' ); ?>
			<?php do_meta_boxes( 'wpshop_dashboard', 'right_column', '' ); ?>

		</div>
		<div class="wpshop_cls wpshopHide" id="ajax-response"></div>
		<span class="infobulle"></span>
	</div>
</div>
