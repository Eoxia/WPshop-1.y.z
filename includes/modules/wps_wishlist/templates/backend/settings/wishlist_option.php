<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<input type="checkbox" id="wpshop_catalog_product_option_wishlist" name="wpshop_catalog_product_option[wps_wishlist_display]" <?php echo ( !empty($wps_wishlist_display) ) ? 'checked="checked"' : ''; ?> />
<a class="wpshop_infobulle_marker" title="<?php echo __('Activate the possibility to have a wishlist', 'wps_wishlist_i18n'); ?>" href="#">?</a>