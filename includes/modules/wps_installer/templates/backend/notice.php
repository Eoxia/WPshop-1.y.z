<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="updated wpshop_admin_notice wpshop_install_notice" id="wpshop_install_notice" >
	<h3><?php
		if ( 1 != $this->current_installation_step ) {
			_e( 'You start to configure your shop, but there are still missing parameters', 'wpshop');
		}
		else {
			_e( 'Your shop is now installed. You will need some configuration before using it completely', 'wpshop');
		}
	?></h3>
	<a href="<?php echo admin_url( 'admin.php?page=wps-installer&amp;installation_state=initialized' ); ?>" class="button-primary wpshop-install-button" ><?php ( 1 != $this->current_installation_step ) ? _e( 'Continue configuration', 'wpshop') : _e( 'Configure your shop', 'wpshop'); ?></a>
	<a href="<?php echo admin_url( 'admin.php?page=wpshop_about&amp;installation_state=ignored' ); ?>" ><?php _e( 'Install without settings', 'wpshop'); ?></a>
</div>
