<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-alert-info"><span class="dashicons dashicons-lightbulb"></span> <?php printf( __( 'Boost your WPShop with a Credit card payment solution, <a href="%s" target="_blank">click here to select your payment solution adapted to your Bank</a>', 'wpshop'), 'http://shop.eoxia.com/boutique/shop/modules-wpshop/modules-de-paiement/'); ?></div>
<?php if( !empty($payment_option) && !empty($payment_option['mode']) ) : ?>
<div class="wps-alert-error" id="wps_payment_config_save_message" style="display : none"><span class="dashicons dashicons-info"></span> <?php _e( 'Process saving, please wait...', 'wpshop'); ?></div>
<div class="wps-table" id="wps_payment_mode_list_container">
	<div class="wps-table-header wps-table-row" >
		<div class="wps-table-cell"></div>
		<div class="wps-table-cell"><?php _e( 'Logo', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Payment mode Name', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Configure', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Activate', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Default payment mode', 'wpshop'); ?></div>
	</div>
	<?php foreach( $payment_option['mode'] as $k => $payment_mode ) :
			if( $k != 'default_shipping_mode' ) :
	?>

	<div class="wps-table-content wps-table-row wps_payment_mode_container">
		<div class="wps-table-cell wps-cart-item-img" id="wps_payment_mode_logo_container_<?php echo $k; ?>">
			<?php echo ( !empty($payment_mode['logo']) ? ( (strstr($payment_mode['logo'], 'http://') === FALSE ) ? wp_get_attachment_image( $payment_mode['logo'], 'thumbnail') : '<img src="' .$payment_mode['logo']. '" alt="" />' ) : '' ); ?>
		</div>
		<div class="wps-table-cell">
			<a class="wps-bton-first-mini-rounded add_logo_to_payment_mode" id="add_logo_to_payment_mode_<?php echo $k; ?>" href="#" style="display : inline-block"><?php _e( 'Add a logo', 'wpshop'); ?></a>
			<input type="hidden" name="wps_payment_mode[mode][<?php echo $k; ?>][logo]" id="wps_payment_mode_logo_<?php echo $k; ?>" value="<?php echo ( !empty($payment_mode['logo']) ) ? $payment_mode['logo'] : ''; ?>" />
		</div>
		<div class="wps-table-cell">
		<input type="text" name="wps_payment_mode[mode][<?php echo $k; ?>][name]" value="<?php echo ( !empty($payment_mode['name']) ) ? $payment_mode['name'] : ''; ?>" />
		</div>
		<div class="wps-table-cell"><a href="#TB_inline?width=780&amp;height=700&amp;inlineId=<?php echo $k; ?>_configuration_interface" class="thickbox wps-bton-first-mini-rounded" title="<?php _e('Configure the payment mode', 'wpshop'); ?>" ><?php _e( 'Configure', 'wpshop'); ?></a></div>
		<div class="wps-table-cell">
			<input type="checkbox" id="wps_payment_active_<?php echo $k; ?>" class="wps_payment_active" name="wps_payment_mode[mode][<?php echo $k; ?>][active]"  <?php echo ( (!empty($payment_mode) && !empty($payment_mode['active']) ) ? 'checked="checked"' : '' ); ?> />
		</div>
		<div class="wps-table-cell">
			<input type="radio" id="wps_payment_active_<?php echo $k; ?>_radio_default" name="wps_payment_mode[default_choice]" value="<?php echo $k; ?>" <?php echo ( !empty( $payment_option['default_choice'] ) && $payment_option['default_choice'] == $k ) ? 'checked="checked"' : ''; echo ( (!empty($payment_mode) && !empty($payment_mode['active']) ) ? '' : 'disabled="disabled"' ); ?> />
		</div>


	<div id="<?php echo $k; ?>_configuration_interface" style="display : none">
		 <div class="wps-boxed">
			 <div class="wps-form-group">
				 <label><?php _e('Displayed description on front', 'wpshop'); ?></label>
				 <div class="wps-form">
				 	<textarea name="wps_payment_mode[mode][<?php echo $k; ?>][description]" style="width : 100%; height: 120px;"><?php echo ( !empty($payment_mode['description']) ) ? trim($payment_mode['description']) : ''; ?></textarea>
				 </div>
			 </div>
		 </div>
	     <?php  echo apply_filters('wps_payment_mode_interface_'.$k, ''); ?>
	     <div><center><a href="#" role="button" class="wps-bton-first-rounded wps_save_payment_mode_configuration"><?php _e( 'Save', 'wpshop'); ?></a></center><br/></div>
	</div>

	</div>
	<?php
		endif;
	endforeach; ?>
</div>

<?php else : ?>
	<div class="wps-alert-info"><?php _e( 'No payment mode available', 'wpshop'); ?></div>
<?php endif; ?>
