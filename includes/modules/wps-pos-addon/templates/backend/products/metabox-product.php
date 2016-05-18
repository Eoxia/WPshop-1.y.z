<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-pos-element-metabox-selection wps-pos-element-metabox-selection-product" >
	<input type="text" value="" data-nonce="<?php echo wp_create_nonce( 'ajax_pos_product_search' ); ?>" placeholder="<?php _e( 'Start typing here for product search', 'wps-pos-i18n' ); ?>" name="wps-pos-product-to-choose" class="wps-pos-product-search" />
	<button class="wps-bton-first-rounded" id="wpspos-product-search" ><i class="dashicons dashicons-search" ></i></button>

	<label style="margin-left: 50px;" >
		<?php
			$only_barcode = '';
			$option = 'wps_pos_options';
			$values = get_option( $option );
			if( empty( $values['only_barcode'] ) || $values['only_barcode'] == 'checked' ) {
				$only_barcode = 'checked="checked"';
			}
		?>
		<input data-nonce="<?php echo wp_create_nonce( 'ajax_save_config_barcode_only' ); ?>" type="checkbox" value="only_barcode" name="wps-pos-search-in" <?php echo $only_barcode; ?> />
		<?php _e( 'Search only in barcode', 'wps-pos-i18n' ); ?>
	</label>
</div>
<div class="wps-pos-product-selection-alert-box wps-alert hidden" ></div>
<div class="wps-pos-element-listing-container wps-pos-product-listing wps-bloc-loader" ><?php echo $this->get_product_table_by_alphabet( $letters_having_products[ 0 ] ); ?></div>
<div class="wps-pos-alphabet-container" ><?php echo wps_pos_tools::alphabet_letters( 'product', $available_letters, $letters_having_products[ 0 ] ); ?></div>
