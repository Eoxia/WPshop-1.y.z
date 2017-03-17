<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed">
<span class="wps-h5"><span class="dashicons dashicons-search"></span> <input type="text" placeholder="<?php _e( 'Search products by letter', 'wpshop' ); ?>" id="search_product_by_title_or_barcode" size="50"><a href="#" data-nonce="<?php echo wp_create_nonce( 'refresh_product_list_all' ); ?>" class="wps-bton-second-mini-rounded search_product_by_title_or_barcode"><?php _e('Search', 'wpshop'); ?></a></span>
<?php echo apply_filters( 'wps-filter-free-product-bton-tpl', $post->ID );
foreach( $letters as $letter ) : ?>
<a href="#" data-nonce="<?php echo wp_create_nonce( 'refresh_product_list_'.strtolower($letter) ); ?>" class="wps-bton-second-mini-rounded <?php echo ( strtoupper( $current_letter ) == strtoupper( $letter ) ) ? 'third' : ''; ?> search_product_by_letter" id="<?php echo $letter; ?>"><?php echo ( $letter != 'ALL' ) ? $letter : __('ALL', 'wpshop' ); ?></a>
<?php endforeach; ?>
</div>

<div id="wps_orders_product_listing_table">
<?php require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "product-listing/wps_orders_product_listing_table") ); ?>
</div>
