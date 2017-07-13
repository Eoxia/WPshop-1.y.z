<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="changelog" >

	<div class="feature-section changelog three-col">
		<div class="col">
			<img src="<?php echo WPS_INSTALLER_URL . WPS_INSTALLER_DIR . '/assets/medias/paiement.jpg'; ?>" alt="<?php _e( 'Payment methods', 'wpshop'); ?>" title="<?php _e( 'Payment methods', 'wpshop'); ?>" />
			<h3><?php _e( 'Payment methods', 'wpshop'); ?></h3>
			<p><?php printf( __( 'When installing WPShop you will have included by default, checks and paypal payment gateway. However there are more %spayment method%s developped for WPShop. If you are interested by a non existant payment method. You can contact us on our %sforum%s', 'wpshop'), '<a href="http://www.wpshop.fr/boutique/extensions/paiement/" target="_wps_about_extra">', '</a>', '<a href="http://forums.eoxia.com/" target="_eoxia_form">', '</a>' ); ?></p>
			<a href="<?php echo admin_url( 'options-general.php?page=wpshop_option#wpshop_payments_option' ); ?>" class="button button-large button-primary"><?php _e( 'Manage payment methods', 'wpshop'); ?></a>
		</div>
		<div class="col">
			<img src="<?php echo WPS_INSTALLER_URL . WPS_INSTALLER_DIR . '/assets/medias/livraison.jpg'; ?>" alt="<?php _e( 'Shipping methods', 'wpshop'); ?>" title="<?php _e( 'Shipping methods', 'wpshop'); ?>" />
			<h3><?php _e( 'Shipping methods', 'wpshop'); ?></h3>
			<p><?php printf( __( 'By default you have one shipping method available into WPShop. You can create as much as you want using %ssettings interface%s. You will also find on our %swebsite%s additionnals shipping methods. You can contact us on our %sforum%s', 'wpshop'), '<a href="' . admin_url( 'options-general.php?page=wpshop_option#wpshop_shipping_option' ) . '" target="_wps_settings_interface" >', '</a>', '<a href="http://www.wpshop.fr/shop-theme/" target="_wps_about_extra" >', '</a>',  '<a href="http://forums.eoxia.com/" target="_eoxia_form">', '</a>' ); ?></p>
			<a href="<?php echo admin_url( 'options-general.php?page=wpshop_option#wpshop_shipping_option' ); ?>" class="button button-large button-primary"><?php _e( 'Manage shipping methods', 'wpshop'); ?></a>
		</div>
		<div class="col last-feature">
			<img src="<?php echo WPS_INSTALLER_URL . WPS_INSTALLER_DIR . '/assets/medias/produit.jpg'; ?>" alt="<?php _e( 'Create products', 'wpshop'); ?>" title="<?php _e( 'Create products', 'wpshop'); ?>" />
			<h3><?php _e( 'Create products', 'wpshop'); ?></h3>
			<p><?php _e( 'With WPshop plugin you can now create your product catalog on WordPress and market them. A catalog and clear product information, easy to create and fully customizable through shortcodes.', 'wpshop'); ?></p>
			<a href="<?php echo admin_url( 'edit.php?post_type=wpshop_product' ); ?>" class="button button-large button-primary wpshop-about-btn-create-products"><?php _e( 'Create products', 'wpshop'); ?></a>
		</div>
	</div>

	<div class="feature-section col three-col">
		<div class="col">
			<img src="<?php echo WPS_INSTALLER_URL . WPS_INSTALLER_DIR . '/assets/medias/commande.jpg'; ?>" alt="<?php _e( 'Manage orders', 'wpshop'); ?>" title="<?php _e( 'Manage orders', 'wpshop'); ?>" />
			<h3><?php _e( 'Manage orders', 'wpshop'); ?></h3>
			<p><?php _e( 'You can manage all your orders and stay in touch with your customers. The dashboard controls will allow you to quickly view your most visited pages , static of your orders ...', 'wpshop'); ?></p>
			<a href="<?php echo admin_url( 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER ); ?>" class="button button-large button-primary"><?php _e( 'Manage orders', 'wpshop'); ?></a>
		</div>
		<div class="col">
			<img src="<?php echo WPS_INSTALLER_URL . WPS_INSTALLER_DIR . '/assets/medias/reglage-boutique.jpg'; ?>" alt="<?php _e( 'Configure my shop', 'wpshop'); ?>" title="<?php _e( 'Configure my shop', 'wpshop'); ?>" />
			<h3><?php _e( 'Configure my shop', 'wpshop'); ?></h3>
			<p><?php printf( __( 'We update WPShop in order to improve speed, accessibility and mobile usage. Go on %smain settings interface%s in order to configure your new shop, payment gateways or shipping methods', 'wpshop'), '<a href="' . admin_url( 'options-general.php?page=wpshop_option' ) . '" target="_wps_settings_interface" >', '</a>' ); ?></p>
			<a href="<?php echo admin_url( 'options-general.php?page=wpshop_option' ); ?>" class="button button-large button-primary"><?php _e( 'Configure my shop', 'wpshop'); ?></a>
		</div>
		<div class="col last-feature">
			<img src="<?php echo WPS_INSTALLER_URL . WPS_INSTALLER_DIR . '/assets/medias/contenu.jpg'; ?>" alt="<?php _e( 'Full content customization', 'wpshop'); ?>" title="<?php _e( 'Full content customization', 'wpshop'); ?>" />
			<h3><?php _e( 'Full content customization', 'wpshop'); ?></h3>
			<p><?php printf( __( 'You can design all you %stransactionnal emails%s, change the different %spage layout%s (as cart page, checkout page, and so on)', 'wpshop'), '<a href="' . admin_url( 'edit.php?post_type=wpshop_shop_message' ) . '" target="_wps_settings_interface" >', '</a>', '<a href="' . admin_url( 'edit.php?post_type=page' ) . '" target="_wps_settings_interface" >', '</a>' ); ?></p>
		</div>
	</div>

	<h2 class="about-headline-callout"><?php _e( 'Extend default WPShop functionnalities', 'wpshop'); ?></h2>
	<div class="feature-section col two-col">
		<div class="col">
			<h3><?php _e( 'External addons', 'wpshop'); ?></h3>
			<p><?php printf( __( 'Need a theme? or a payment gateway? or a shipping addons? Anythong else? Check %sour website%s in order to find the addons for WPShop that you need. If you don\'t find it please contact us through the forum with the link below.', 'wpshop'), '<a href="http://www.wpshop.fr/shop-theme/" >', '</a>' ); ?></p>
		</div>
		<div class="col last-feature">
			<h3><?php _e( 'Custom hooks', 'wpshop'); ?></h3>
			<p><?php printf( __( 'We inserted some custom hook through WPShop code. That will give you some possibilities to add functionnalities when it is already planned.', 'wpshop') ); ?></p>
		</div>
	</div>

	<?php // <h3 class="about-headline-callout"><?php printf( __( 'A question ? A comment ? A need ? Join us on %sWPShop forum%s', 'wpshop'), '<a href="http://forums.eoxia.com/login" taget="_wpshop_forums" >', '</a>' ); </h3> ?>

	<hr>
	<div class="return-to-dashboard"><a href="<?php echo admin_url( 'admin.php?page=wpshop_dashboard' ); ?>"><?php _e( 'Go to your shop dashboard', 'wpshop'); ?></a></div>
</div>
