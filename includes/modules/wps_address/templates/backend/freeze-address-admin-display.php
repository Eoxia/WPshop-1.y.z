<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div>
	<div class="wps-boxed summary_shipping_boxed" id="wpshop_order_<?php echo $address_type_indicator; ?>" >
		<div class="wps-h5"><?php _e( ucfirst($address_type_indicator) . ' address', 'wpshop')?></div>
		<?php
		if( !empty($address_content) ) :
			echo $address_content;
		else :
		?>
			<div class="wps-alert-error"><?php _e( 'No billing address informations are found', 'wpshop'); ?></div>
		<?php
		endif;
		?>
	</div>
</div>
