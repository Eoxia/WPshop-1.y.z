<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
	<div id="wps-product-quick-creation-form-attributes" class="wps-bloc-loader" >
		<?php $attribute_list = wpshop_attributes_set::getAttributeSetDetails( $chosen_set ); ?>
		<?php if ( !empty( $attribute_list ) ) : ?>
			<?php foreach( $attribute_list as $attribute_set ) : ?>
			<?php
				$attributes = '';
				foreach($attribute_set['attribut'] as $attribute_key => $attribute) {
					if( !empty( $attribute_key ) && ( $attribute->status == 'valid' ) && ( 'yes' == $attribute->is_used_in_quick_add_form ) ) {
						$output = wpshop_attributes::display_attribute( $attribute->code );
						$attributes .= $output['field'];
					}
				}
			?>
			<?php if ( !empty( $attributes ) ) : ?>
	<div class="wps-boxed">
		<span class="wps-h5"><?php echo $attribute_set['name']; ?></span>
		<?php echo $attributes; ?>
	</div>
			<?php endif; ?>

			<?php endforeach; ?>
		<?php endif; ?>
	</div>