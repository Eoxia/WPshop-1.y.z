<div class="wps-checkout-wrapper">
	<div class="wps-checkout-content">
		<?php echo do_shortcode('[wps_addresses]'); ?>

		<div id="wps-checkout-step-errors"></div>
		<?php if( !empty( $_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type'] == 'quotation' ) : ?>
		<div class="wps"><button class="wps-bton-first-alignRight-rounded" id="wps-checkout-valid-step-three"><?php _e( 'Validate my quotation', 'wpshop' ); ?></button></div>
		<?php else : ?>
		<div class="wps"><button class="wps-bton-first-alignRight-rounded" id="wps-checkout-valid-step-three"><?php _e( 'Order', 'wpshop' ); ?></button></div>
		<?php endif; ?>
	</div>
	<div class="wps-sidebar-resume">
		<input type="hidden" name="action" value="wps-checkout_valid_step_five"/>
		<?php echo do_shortcode( '[wps_resume_cart]' ); ?>
	</div>
</div>
