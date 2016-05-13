<?php if ( !defined( 'ABSPATH' ) ) exit;
 if(current_user_can('wpshop_edit_options') ) : ?> 
	<?php 
		$value = ($wpshop_display_option[$field_identifier] <= 0 ? WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE : $wpshop_display_option[$field_identifier]);
	?>
	<div id="<?php echo $field_identifier ?>slider" class="slider_variable wpshop_options_slider wpshop_options_slider_display wpshop_options_slider_display_grid_element_number"></div>
	<?php echo wpshop_form::form_input('wpshop_display_option[' . $field_identifier . ']', $field_identifier, $wpshop_display_option[$field_identifier], 'text', ' readonly class="sliderValue" '); ?>

<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery('#<?php echo $field_identifier ?>slider').slider({
			value : <?php echo $value; ?>,
			min: <?php echo WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE ; ?>,
			max: <?php echo WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE ; ?>,
			range: "min",
			step: 1,
			slide: function(event, ui) {
				jQuery("#<?php echo $field_identifier ?>").val(ui.value);
				jQuery("#<?php echo $field_identifier ?>slider a span strong").html(ui.value);
			}
		});
		jQuery("#<?php echo $field_identifier ?>slider a").append("<span><strong><?php echo $value; ?></strong></span>");
		jQuery("#<?php echo $field_identifier ?>").val("<?php echo $value; ?>");
	});
</script>

<?php else :
	$option_field_output = $wpshop_display_option[$field_identifier];
endif; ?>
<a href="#" title="<?php _e('Number of products displayed per line when grid display mode is active','wpshop'); ?>" class="wpshop_infobulle_marker">?</a>
			