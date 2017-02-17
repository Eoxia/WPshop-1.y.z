<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-table-content wps-table-row wps_shipping_mode_container">
		<div class="wps-table-cell wps-cart-item-img">
			<div id="wps_shipping_mode_logo_container_<?php echo $k; ?>">
				<?php echo ( !empty($shipping_mode['logo']) ? ( (strstr($shipping_mode['logo'], 'http://') === FALSE ) ? wp_get_attachment_image( $shipping_mode['logo'], 'thumbnail') : '<img src="' .$shipping_mode['logo']. '" alt="" />' ) : '' ); ?>
			</div>
		</div>
		<div class="wps-table-cell">
			<a class="wps-bton-first-mini-rounded add_logo_to_shipping_mode" id="add_logo_to_shipping_mode_<?php echo $k; ?>" href="#"><?php _e( 'Add a logo', 'wpshop'); ?></a>
			<input type="hidden" name="wps_shipping_mode[modes][<?php echo $k; ?>][logo]"  id="wps_shipping_mode_logo_<?php echo $k; ?>" value="<?php echo ( !empty($shipping_mode['logo']) ) ? $shipping_mode['logo'] : ''; ?>" />
		</div>
		<div class="wps-table-cell">
			<input type="text" name="wps_shipping_mode[modes][<?php echo $k; ?>][name]" id="wps_shipping_mode_configuration_<?php echo $k; ?>_name" value="<?php echo ( !empty($shipping_mode['name']) ) ? $shipping_mode['name'] : ''; ?>" />
		</div>
		<div class="wps-table-cell"><a href="#TB_inline?width=780&amp;height=700&amp;inlineId=<?php echo $k; ?>_shipping_configuration_interface" class="thickbox wps-bton-first-mini-rounded" title="<?php _e('Configure the shipping mode', 'wpshop'); ?>" ><?php _e( 'Configure', 'wpshop'); ?></a></div>
		<div class="wps-table-cell"><input type="checkbox" id="wps_shipping_mode_<?php echo $k; ?>" class="wps_shipping_mode_active" name="wps_shipping_mode[modes][<?php echo $k; ?>][active]" <?php echo ( (!empty($shipping_mode) && !empty($shipping_mode['active']) ) ? 'checked="checked"' : '' ); ?> /></div>
		<?php $shipping_mode_option = get_option( 'wps_shipping_mode' ); ?>
		<div class="wps-table-cell"><input type="radio" id="wps_shipping_mode_<?php echo $k; ?>_radio_default" name="wps_shipping_mode[default_choice]" value="<?php echo $k; ?>" <?php echo ( !empty( $shipping_mode_option['default_choice'] ) && $shipping_mode_option['default_choice'] == $k ) ? 'checked="checked"' : ''; echo ( (!empty($shipping_mode) && !empty($shipping_mode['active']) ) ? '' : 'disabled="disabled"' ); ?> /></div>
		<div class="wps-table-cell"><?php if( !is_bool( strpos( $k, 'wps_custom_shipping_mode_' ) ) ) : ?>
			<a href="" class="wps_delete_shipping_mode" data-nonce="<?php echo wp_create_nonce( 'wps_delete_shipping_mode' ); ?>"><span class="dashicons dashicons-trash"></span></a>
		<?php endif; ?></div>
		<!-- Configuration interface -->
		<div id="<?php echo $k; ?>_shipping_configuration_interface" style="display : none">
			<?php
			$wps_shipping_mode_ctr = new wps_shipping_mode_ctr();
			echo $wps_shipping_mode_ctr->generate_shipping_mode_interface( $k, $shipping_mode ); ?>
		</div>
</div>
