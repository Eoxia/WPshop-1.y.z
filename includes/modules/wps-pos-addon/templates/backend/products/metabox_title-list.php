<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<span class="dashicons dashicons-products"></span>
<?php _e( 'Product selection', 'wps-pos-i18n' ); ?>
<a class="thickbox add-new-h2" title="<?php _e( 'New product creation', 'wps-pos-i18n' ); ?>" href="<?php echo admin_url( 'admin-ajax.php?action=wpspos-product-quick-creation&_wpnonce=' . wp_create_nonce( 'wps-product-quick-nonce' ) . '&width=550&height=600' ); ?>"><?php _e('Create a product', 'wps-pos-i18n'); ?></a>
