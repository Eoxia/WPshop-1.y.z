<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-gridwrapper3-padded">
	<div>
		<div class="wps-form-group">
			<label><?php _e( 'Selected part element background', 'wpshop'); ?> :</label>
			<div class="wps-form">
			<input type="text" name="wpshop_customize_display_option[account][activ_element_background]" class="wps-color-picker-field" value="<?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['account']) && !empty($wpshop_customize_display_option['account']['activ_element_background']) )   ? $wpshop_customize_display_option['account']['activ_element_background'] : ''; ?>" />
			<span style="display : inline-block; width : 20px; height : 20px; background : <?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['account']) && !empty($wpshop_customize_display_option['account']['activ_element_background']) )   ? $wpshop_customize_display_option['account']['activ_element_background'] : ''; ?>"></span>
			</div>
		</div>
	</div>
	
	<div>
		<div class="wps-form-group">
			<label><?php _e( 'Information box background color', 'wpshop'); ?> :</label>
			<div class="wps-form">
			<input type="text" name="wpshop_customize_display_option[account][information_box_background]" class="wps-color-picker-field" value="<?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['account']) && !empty($wpshop_customize_display_option['account']['information_box_background']) )   ? $wpshop_customize_display_option['account']['information_box_background'] : ''; ?>" />
			<span style="display : inline-block; width : 20px; height : 20px; background : <?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['account']) && !empty($wpshop_customize_display_option['account']['information_box_background']) )   ? $wpshop_customize_display_option['account']['information_box_background'] : ''; ?>"></span>
			
			</div>
		</div>
	</div>
	
	<div>
		<div class="wps-form-group">
			<label><?php _e( 'Unselected part element background', 'wpshop'); ?> :</label>
			<div class="wps-form">
			<input type="text" name="wpshop_customize_display_option[account][part_background]" class="wps-color-picker-field" value="<?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['account']) && !empty($wpshop_customize_display_option['account']['part_background']) )   ? $wpshop_customize_display_option['account']['part_background'] : ''; ?>"/>
			<span style="display : inline-block; width : 20px; height : 20px; background : <?php echo ( !empty($wpshop_customize_display_option) && !empty($wpshop_customize_display_option['account']) && !empty($wpshop_customize_display_option['account']['part_background']) )   ? $wpshop_customize_display_option['account']['part_background'] : ''; ?>"></span>
			
			</div>
		</div>
	</div>
	
</div>
