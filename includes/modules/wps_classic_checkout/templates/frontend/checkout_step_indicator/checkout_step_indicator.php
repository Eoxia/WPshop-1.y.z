<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-gridwrapper6-marged wps-checkout-steps -wps-screen">
<?php
	$step_finished = false;
	foreach( $steps as $step_id => $step) :
		$step_id += 1;

		$real_step_to_display = $default_step;
		if ( ( true === $no_cart ) && ( 3 == $real_step_to_display ) ) {
			$real_step_to_display = 2;
		}
		if ( ( true === $no_shipping ) && ( 4 < $real_step_to_display ) ) {
			if ( true === $no_cart ) {
				$real_step_to_display -= 2;
			}
			else {
				$real_step_to_display -= 1;
			}
		}
		$step_class = ( $real_step_to_display == $step_id ) ? 'wps-checkout-step --wps-current' : ( ( $real_step_to_display > $step_id) ? 'wps-checkout-step --wps-finished' : 'wps-checkout-step' ) ;
		$step_finished = ( ( $real_step_to_display > $step_id ) ? true : false ) ;
		require( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir, "frontend", "checkout_step_indicator/checkout_step_indicator_step") );
	endforeach;
?>
</div>

<div class="wps-checkout-steps --wps-touch">
	<span class="wps-h3"><?php echo $steps[$real_step_to_display-1]; ?></span>
	<span class="wps-checkout-steps-caption"><?php _e('Etape', 'wpshop'); ?> <?php echo $real_step_to_display; ?>/<?php echo count($steps); ?></span>
</div>