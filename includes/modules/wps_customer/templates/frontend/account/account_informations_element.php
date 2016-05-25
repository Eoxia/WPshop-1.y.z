<?php if ( !defined( 'ABSPATH' ) ) exit;

if( isset( $attribute_def->code ) && $attribute_def->code != 'is_provider' ) { ?>
<div class="wps-form-group">
	<?php echo stripslashes( $attribute_def->frontend_label ); ?> : <?php echo ( !empty($attribute_value) ) ? $attribute_value : ''; ?>
</div>
<?php } ?>
