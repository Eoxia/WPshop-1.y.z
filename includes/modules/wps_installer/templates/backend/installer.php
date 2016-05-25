<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wpshop_full_page_tabs">
	<h1><span class="wps-shop-icon" ></span><?php _e( 'Welcome on wpshop', 'wpshop'); ?></h1>
	<h2><?php _e( 'Before you start to use WPShop you will need to configure some parameters', 'wpshop'); ?></h2>
	<div class="wpshop_admin_box">
		<div class="wps-gridwrapper3-marged">
		<?php
			$step_finished = false;
			foreach( $steps as $step_id => $step) :
				$step_id += 1;
				$step_class = ( $current_step == $step_id ) ? 'wps-checkout-step-current' : ( ( $current_step > $step_id) ? 'wps-checkout-step-finished' : 'wps-checkout-step' ) ;
				$step_finished = ( ( $current_step > $step_id) ? true : false ) ;
				require( wpshop_tools::get_template_part( WPS_INSTALLER_DIR, WPSINSTALLER_TPL_DIR, "backend", "step_indicator" ) );
			endforeach;
		?>
		</div><!-- .wps-gridwrapper4-marged -->

		<div class="wps-installer-step-container wps-installer-step-container-<?php echo $current_step; ?>" >
		<?php if ( $current_step < count( $steps ) ) : ?>
			<form action="<?php echo admin_url( 'admin.php?page=wps-installer&amp;wps-installation-step=' . ( $current_step + 1 ) ); ?>" method="post" id="wps_installer_form" enctype="multipart/form-data" >
				<input type="hidden" name="action" value="wps-installation" />
				<div class="wps-installer-step-content wps-installer-step-content-<?php echo $current_step; ?>" >
					<?php echo $current_step_output; ?>
				</div>

				<button class="button button-primary alignright" ><?php ( 2 == $current_step ) ? _e( 'Create my shop', 'wpshop') : _e( 'Next step', 'wpshop'); ?></button>
				<span class="spinner" ></span>
				<!-- <button class="button button-secondary alignright" ><?php _e( 'Ignore this step', 'wpshop'); ?></button>  -->
			</form><!-- #wps_installer_form -->
		<?php else: ?>
			<?php _e( 'Main informations have been setted up.', 'wpshop'); ?>
		<?php endif; ?>
		</div><!-- .wps-installer-step-container -->
	</div><!-- .wpshop_full_page_tabs -->
</div><!-- .wpshop_admin_box -->