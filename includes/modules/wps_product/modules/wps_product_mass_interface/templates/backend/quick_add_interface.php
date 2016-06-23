<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
	<h2><span class="dashicons dashicons-update" style="font-size : 30px; width : 30px; height : 30px"></span> <?php _e( 'Mass edit products interface', 'wpshop')?><button class="add-new-h2" data-nonce="<?php echo wp_create_nonce( 'wps_mass_interface_new_product_creation' ); ?>" id="wps-mass-interface-button-new-product"><i class="wps-icon-pencil"></i> <?php _e( 'Create a new product', 'wpshop'); ?></button></h2>


<div class="wps-boxed">
	<div class="wps-row wps-gridwrapper3-padded">
		<div>
			<div class="wps-form-group">
			<?php if( !empty($products_attributes_groups) ): ?>
				<?php if ( 1 < count( $products_attributes_groups ) ) : ?>
				<label><?php _e( 'Products Attributes groups', 'wpshop' ); ?> : </label>
				<div class="wps-form">
					<select id="wps_mass_edit_products_default_attributes_set" name="wps_mass_edit_products_default_attributes_set" >
						<?php foreach( $products_attributes_groups as $products_attributes_group ) : ?>
						<option value="<?php echo $products_attributes_group->id; ?>" <?php echo ( (!empty($products_attributes_group->default_set) && $products_attributes_group->default_set == 'yes' ) ? 'selected="selected"' : '' ); ?> ><?php echo $products_attributes_group->name; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php else: ?>
					<input type="hidden" id="wps_mass_edit_products_default_attributes_set"  name="wps_mass_edit_products_default_attributes_set" value="<?php echo $products_attributes_groups[ 0 ]->id; ?>" />
				<?php endif; ?>
			<?php endif; ?>
			</div>

		</div>

		<div>&nbsp;</div>

		<div>
			<div style="width : 100%; margin-top : 20px" class="alignright"><button class="button-primary alignright wps-mass-interface-button-save"><i class="wps-icon-save"></i> <?php _e( 'Save selected products', 'wpshop' ); ?> </button></div>
		</div>

	</div>
	<div class="wps-form wps_mass_products_edit_pagination_container"><?php echo $pagination; ?></div>
</div>
<div style="display : none" class="wps-alert-error"></div>
<div style="display : none" class="wps-alert-success"></div>

<div id="wps_mass_products_edit_tab_container" data-nonce="<?php echo wp_create_nonce( 'wps_mass_edit_change_page' ); ?>">

<?php echo $product_list_interface; ?>

</div>



<div class="wps-boxed">
	<div class="wps-form wps_mass_products_edit_pagination_container"><?php echo $pagination; ?></div>
	<input type="hidden" value="1" id="wps_mass_edit_interface_current_page_id" />
</div>
</div>
