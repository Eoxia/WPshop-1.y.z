<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<!-- <div id="welcome-panel" class="welcome-panel wps-welcome-panel" >
	<input id="wps_installer_welcome_close_nonce" type="hidden" value="<?php echo wp_create_nonce("wps-installer-welcome-panel-close"); ?>" name="wps_installer_welcome_close_nonce">
	<a class="welcome-panel-close wps-welcome-panel-close" href="<?php echo admin_url( 'admin.php?page=wpshop_dashboard?welcome=0' ); ?>"><?php _e( 'Close', 'wpshop'); ?></a>

	<div class="welcome-panel-content">
		<h3><?php _e( 'Welcome to WPShop', 'wpshop'); ?></h3>
		<p class="about-description"><?php _e( 'You will find some useful links in order to start using WPShop', 'wpshop'); ?></p>

		<div class="welcome-panel-column-container">
			<div class="welcome-panel-column">
				<h4><?php _e( 'Have fun with WPShop !', 'wpshop'); ?></h4>
				<a href="<?php echo admin_url( 'options-general.php?page=wpshop_option' ); ?>" class="button button-primary button-hero"><?php _e( 'Configure your shop', 'wpshop'); ?></a>
			</div>
			<div class="welcome-panel-column">
				<h4><?php _e( 'Next step', 'wpshop'); ?></h4>
				<ul>
					<?php if ( 0 >= $nb_products ) : ?>
						<li><div class="dashicons dashicons-welcome-write-blog"></div><a href="<?php echo admin_url( 'post-new.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ); ?>" ><?php _e( 'Create your first product', 'wpshop');?></a></li>
					<?php endif; ?>

					<?php if ( ( "sale" == $shop_type ) && $no_payment_mode_configurated ) : ?>
						<li><div class="dashicons dashicons-admin-generic"></div><a href="<?php echo admin_url('options-general.php?page='.WPSHOP_URL_SLUG_OPTION.'#wpshop_payments_option'); ?>" ><?php _e( 'Configure a payment method', 'wpshop');?></a></li>
					<?php endif; ?>

					<li><div class="dashicons dashicons-email-alt"></div><a href="<?php echo admin_url('edit.php?post_type=wpshop_shop_message'); ?>" ><?php _e( 'Configure emails', 'wpshop');?></a></li>
				</ul>
			</div>
			<div class="welcome-panel-column">
				<h4><?php _e( 'Go further', 'wpshop'); ?></h4>
				<ul>
					<li><div class="dashicons dashicons-welcome-view-site"></div><a href="<?php echo get_permalink( get_option( 'wpshop_product_page_id' ) ); ?>" ><?php _e( 'View your shop', 'wpshop');?></a></li>
					<li><div class="dashicons dashicons-book-alt"></div><?php printf( __( 'More about WPShop %s or %s', 'wpshop'), sprintf( __( 'with %sonline documentation%s', 'wpshop'), '<a target="_blank" href="http://www.wpshop.fr/documentations/presentation-wpshop/" >', '</a>'), sprintf( __( 'on %sthe forum%s', 'wpshop'), '<a target="_blank" href="http://forums.eoxia.com/forum/wpshop" >', '</a>') ); ?></li>
					<li><div class="dashicons dashicons-admin-plugins"></div><?php printf( __( 'Extend WPShop functionnalities with %saddons%s', 'wpshop'), '<a target="_blank" href="http://www.wpshop.fr/shop-theme/" >', '</a>'); ?></li>
				</ul>
			</div>
		</div>
	</div>
</div> -->
