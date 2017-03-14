<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($products) ) : ?>
	<?php $tab_def = array(); ?>
	<?php
		foreach( $quick_add_form_attributes as $id_att => $att ) {
			$tab_def[$id_att]['name'] = $att['frontend_label'];
		}
	?>

	<form method="post" id="wps_mass_edit_product_form" action="<?php echo admin_url( 'admin-ajax.php' ); ?>">
		<input type="hidden" name="action" value="wps_mass_edit_product_save_action" />
    <?php wp_nonce_field( 'wps_save_product_quick_interface' ); ?>
		<table class="wp-list-table widefat wps-product-mass-interface-table">
			<thead>
				<tr>
					<th class="check-column manage-column column-cb check-column">
						<input type="checkbox" class="wps-save-product-checkbox" name="wps_product_quick_save_checkbox_column" />
					</th>
					<th><?php _e( 'Picture', 'wpshop'); ?></th>
					<th class="title"><?php _e( 'Title', 'wpshop'); ?></th>
					<th class="description"><?php _e( 'Description', 'wpshop'); ?></th>
					<?php /*<th width="80"><?php _e( 'Files', 'wpshop'); ?></th>*/ ?>
					<?php if( !empty($tab_def) ) : ?>
					<?php foreach( $tab_def as $col ) : ?>
						<th ><?php echo $col['name']; ?></th>
					<?php endforeach; ?>
					<?php endif; ?>
				</tr>
			</thead>


			<?php
				$count_product = 1;
				foreach( $products as $product ) :
					$product_attribute_set_id = get_post_meta( $product['post_datas']->ID, '_wpshop_product_attribute_set_id', true );
					$class = ($count_product % 2) ? 'alternate' : '';
			?>

			<?php require( wpshop_tools::get_template_part( WPS_PDCT_MASS_DIR, WPS_PDCT_MASS_TEMPLATES_MAIN_DIR, "backend", "quick_add_interface", "product_line" ) ); ?>

			<?php
				$count_product++;
				endforeach;
			?>
			<tfoot>
				<tr>
					<th class="check-column manage-column column-cb check-column">
						<input type="checkbox" class="wps-save-product-checkbox" name="wps_product_quick_save_checkbox_column" />
					</th>
					<th><?php _e( 'Picture', 'wpshop'); ?></th>
					<th><?php _e( 'Title', 'wpshop'); ?></th>
					<th><?php _e( 'Description', 'wpshop'); ?></th>
					<?php /*<th width="80"><?php _e( 'Files', 'wpshop'); ?></th>*/ ?>
					<?php if( !empty($tab_def) ) : ?>
					<?php foreach( $tab_def as $col ) : ?>
						<th><?php echo $col['name']; ?></th>
					<?php endforeach; ?>
					<?php endif; ?>
				</tr>
			</tfoot>
		</table>
	</form>
	<script type="text/javascript" >jQuery( document ).ready( function(){
		jQuery("select.chosen_select").chosen( WPSHOP_CHOSEN_ATTRS );
	} );</script>
<?php else: ?>
	<div class="wps-alert-info"><?php _e( 'You don\'t have any product for the moment', 'wpshop' ); ?></div>
<?php endif; ?>
