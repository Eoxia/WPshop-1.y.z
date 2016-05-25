<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="<?php echo $step_class; ?>">
	<?php if( $step_finished ) : ?>
		<a href="<?php echo admin_url( 'admin.php?page=wps-installer&amp;wps-installation-step=' . $step_id ); ?>"><i class="wps-circlerounded"><?php echo _e( $step_id, 'wpshop'); ?></i><span><?php echo _e( $step, 'wpshop'); ?></span></a>
	<?php else : ?>
		<i class="wps-circlerounded"><?php _e( $step_id, 'wpshop'); ?></i><span><?php _e( $step, 'wpshop'); ?></span>
	<?php endif ?>
</div>

