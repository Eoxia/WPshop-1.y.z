<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) ) : ?>
<div class="wps-alert-error" id="wps_shipping_config_save_message" style="display : none"><span class="dashicons dashicons-info"></span> <?php _e( 'Process saving, please wait...', 'wpshop'); ?></div>
<div class="wps-alert-info"><span class="dashicons dashicons-lightbulb"></span> <?php printf( __( 'Offer a new shipping service to your customer with <a href="%s" target="_blank">So Colissimo or another shipping mode</a>', 'wpshop'), 'http://shop.eoxia.com/boutique/shop/modules-wpshop/modules-de-livraison/'); ?></div>
<div class="wps-table" id="wps_shipping_mode_list_container">
	<div class="wps-table-header wps-table-row" >
			<div class="wps-table-cell"></div>
			<div class="wps-table-cell"><?php _e( 'Logo', 'wpshop'); ?></div>
			<div class="wps-table-cell"><?php _e( 'Shipping mode Name', 'wpshop'); ?></div>
			<div class="wps-table-cell"><?php _e( 'Configure', 'wpshop'); ?></div>
			<div class="wps-table-cell"><?php _e( 'Activate', 'wpshop'); ?></div>
			<div class="wps-table-cell"><?php _e( 'Default shipping mode', 'wpshop'); ?></div>
			<div class="wps-table-cell"><?php _e( 'Delete', 'wpshop'); ?></div>
	</div>
	<?php foreach( $shipping_mode_option['modes'] as $k => $shipping_mode ) :
		if( $k != 'default_choice' ) :
			require( wpshop_tools::get_template_part( WPS_SHIPPING_MODE_DIR, $this->template_dir, "backend", "shipping-mode") );
		endif;
	endforeach; ?>
</div>
<div><a data-nonce="<?php echo wp_create_nonce( 'wps_add_new_shipping_mode' ); ?>" class="wps-bton-mini-rounded-fourth wps_create_new_shipping_mode"><i class="wps-icon-pencil"></i> <?php _e( 'Add a new shipping mode', 'wpshop'); ?></a></div>
<?php else : ?>
	<div class="wps-alert-info"><?php _e( 'No shipping mode available', 'wpshop'); ?></div>
<?php endif; ?>
