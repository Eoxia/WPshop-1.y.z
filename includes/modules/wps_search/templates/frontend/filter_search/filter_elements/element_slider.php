<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-form-group">
	<label><?php _e($attribute_def->frontend_label, 'wpshop'); ?></label>
	<div class="wps-slider-ui wps-form" id="slider_<?php echo $attribute_def->code; ?>" data-range-min="<?php echo $amount_min; ?>" data-range-max="<?php echo $amount_max; ?>" data-min="<?php echo $amount_min; ?>" data-max="<?php echo $amount_max; ?>"></div>
</div>

