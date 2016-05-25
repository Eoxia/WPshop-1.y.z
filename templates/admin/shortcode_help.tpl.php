<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wpshop_shortcode_definition_container<?php echo ( !empty($more_class_shortcode_helper) ? ' '.$more_class_shortcode_helper : '' ); ?>" >
	<?php if ( !empty($shortcode_main_title) ) : ?><p><?php _e($shortcode_main_title); ?><code>[<?php _e($shorcode_main_code); ?><?php _e($shorcode_attributes_def); ?>]</code></p><?php endif; ?>
	<p><?php if ( !empty($shortcode_main_title) ) : _e('Basic example', 'wpshop'); endif; ?> <code>[<?php _e($shorcode_main_code); ?><?php _e($shorcode_attributes_exemple); ?>]</code></p>
	<p><?php if ( !empty($shortcode_main_title) ) : _e('PHP example', 'wpshop'); endif; ?> <code>&lt;?php echo do_shortcode('[<?php _e($shorcode_main_code); ?><?php _e($shorcode_attributes_exemple); ?>]'); ?&gt;</code></p>
</div>h