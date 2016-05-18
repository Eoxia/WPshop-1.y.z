<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<table class="table table-striped wps-pos-element-table">
	<tr>
		<th><?php _e('Product name', 'wps-pos-i18n'); ?></th>
		<th><?php _e('Product price', 'wps-pos-i18n'); ?></th>
		<th><?php _e('Action', 'wps-pos-i18n'); ?></th>
	</tr>
	<tr>
		<td colspan="2" >
			<?php _e( 'No product has been found for current search.', 'wps-pos-i18n' ); ?>
		</td>
		<td>
			<a class="thickbox wps-bton-third-rounded" title="<?php _e( 'New product creation', 'wps-pos-i18n' ); ?>" href="<?php echo admin_url( 'admin-ajax.php?action=wpspos-product-quick-creation&_wpnonce=' . wp_create_nonce( 'wps-product-quick-nonce' ) . '&width=550&height=600' ); ?>"><?php _e('Create a product', 'wps-pos-i18n'); ?></a>
		</td>
	</tr>
</table>
