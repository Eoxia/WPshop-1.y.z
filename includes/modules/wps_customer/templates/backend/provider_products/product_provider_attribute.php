<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-form-group">
	<label><?php _e( $att['field_definition']['label'], 'wpshop' ); ?></label>
	<div class="wps-form"><?php echo str_replace( 'name="wpshop_product_attribute', 'name="wps_provider_product[' .$post->ID. '][' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute]', $att['field_definition']['output'] ); ?></div>
</div>