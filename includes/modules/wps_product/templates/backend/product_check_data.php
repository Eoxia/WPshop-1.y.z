<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<p><?php _e( 'Number of inconsistent product : ', 'wps_product' ); echo $inconsistent_product_number; ?></p>


<form class="wps-product-check-data-form" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post">
	<?php wp_nonce_field( 'ajax_save_product_price' ); ?>
	<table>
		<tr>
			<th>ID</th>
			<th><?php _e( 'Product name', 'wps_product' ); ?></th>
			<th><?php _e( 'Attribute price', 'wps_product' ); ?></th>
			<th><?php _e( 'Meta : _product_price', 'wps_product' ); ?></th>
			<th><?php _e( 'Meta : _wps_price_infos', 'wps_product' ); ?></th>
			<th><?php _e( 'Meta : _wpshop_displayed_price', 'wps_product' ); ?></th>
			<th><?php _e( 'Meta : _wpshop_product_metadata', 'wps_product' ); ?></th>
			<th><?php _e( 'Fix the value', 'wps_product' ); ?></th>
		</tr>
		<?php
		if( !empty( $list_product ) ):
			foreach( $list_product as $product ):
				?>
				<tr>
				<td><?php echo $product->ID; ?></td>
				<td><?php echo $product->post_title; ?></td>
				<td><?php echo $product->price_attribute; ?></td>
				<td><?php echo $product->price['_product_price']; ?></td>
				<td><?php echo $product->price['_wps_price_infos']; ?></td>
				<td><?php echo $product->price['_wpshop_displayed_price']; ?></td>
				<td><?php echo $product->price['_wpshop_product_metadata']; ?></td>
				<td><input type="text" name="product_price[<?php echo $product->ID; ?>]" value="<?php echo $product->price_attribute; ?>" /></td>
				</tr><?php
			endforeach;
		endif;
		?>
	</table>

	<input class="button-primary wps-product-submit-form" name="Submit" type="submit" value="<?php _e( 'Save changes', 'wps_product'); ?>" />
</form>
