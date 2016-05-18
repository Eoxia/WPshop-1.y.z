<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<a href="#" class="wps-bton-mini-first-rounded" id="upload_wps_product_media"><?php _e( 'Download media', 'wpshop'); ?></a>
<input type="hidden" value="post" name="product_media_form" />
<input type="hidden" value="<?php echo $media_id_data; ?>" name="product_media" id="product_media_indicator" />
<div id="selected_media_container" data-nonce="<?php echo wp_create_nonce( 'wp_ajax_display_pictures_in_backend' ); ?>"><?php echo $media; ?></div>
