<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-gridwrapper3-padded">
	<div>
		<div class="wps-form-group">
			<label><?php _e( 'Shipping mode list element background', 'wpshop'); ?> :</label>
			<div class="wps-form">
			<input type="text" name="wpshop_customize_display_option[shipping][background]" class="wps-color-picker-field" value="<?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['second']) && !empty($wpshop_customize_display_option['shipping']['background']) )   ? $wpshop_customize_display_option['shipping']['background'] : ''; ?>" /></div>
			<span style="display : inline-block; width : 20px; height : 20px; background : <?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['second']) && !empty($wpshop_customize_display_option['shipping']['background']) )   ? $wpshop_customize_display_option['shipping']['background'] : ''; ?>" /></span>
		</div>
	</div>
	
	<div>
		<div class="wps-form-group">
			<label><?php _e( 'Selected shipping mode list element background', 'wpshop'); ?> :</label>
			<div class="wps-form">
			<input type="text" name="wpshop_customize_display_option[shipping][active_element]" class="wps-color-picker-field" value="<?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['shipping']) && !empty($wpshop_customize_display_option['shipping']['active_element']) )   ? $wpshop_customize_display_option['shipping']['active_element'] : ''; ?>" /></div>
			<span style="display : inline-block; width : 20px; height : 20px; background : <?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['shipping']) && !empty($wpshop_customize_display_option['shipping']['active_element']) )   ? $wpshop_customize_display_option['shipping']['active_element'] : ''; ?>" /></span>
		</div>
	</div>
	
	<div>
		<div class="wps-form-group">
			<label><?php _e( 'Shipping mode list element text color', 'wpshop'); ?> :</label>
			<div class="wps-form">
			<input type="text" name="wpshop_customize_display_option[shipping][text]" class="wps-color-picker-field" value="<?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['shipping']) && !empty($wpshop_customize_display_option['shipping']['text']) )   ? $wpshop_customize_display_option['shipping']['text'] : ''; ?>"/></div>
			<span style="display : inline-block; width : 20px; height : 20px; background :<?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['shipping']) && !empty($wpshop_customize_display_option['shipping']['text']) )   ? $wpshop_customize_display_option['shipping']['text'] : ''; ?>" /></span>
		</div>
	</div>
	
</div>