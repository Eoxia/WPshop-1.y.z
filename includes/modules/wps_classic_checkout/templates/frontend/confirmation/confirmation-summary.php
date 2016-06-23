<?php if ( !defined( 'ABSPATH' ) ) exit;
$order_meta = get_post_meta($_SESSION['order_id'], '_order_postmeta', true);
if( !empty( $order_meta ) ) {
	?>
	<div style="text-align: center;">
		<script>
			jQuery( document ).on( 'click', '.wps-orders-details-opener', function() {
				var order_id = jQuery( this ).attr( 'data-order-id' );
				jQuery( this ).addClass( 'wps-bton-loading' );
				var data = {
					action: "wps_orders_load_details",
					_wpnonce: jQuery( this ).data( 'nonce' ),
					order_id : order_id
				};
				jQuery.post(ajaxurl, data, function(response) {
						if( response['status'] ) {
							fill_the_modal( response['title'], response['content'], '' );
							jQuery( '#wps-order-details-opener-' + order_id ).removeClass( 'wps-bton-loading' );
						}
						else {
							jQuery( '#wps-order-details-opener-' + order_id ).removeClass( 'wps-bton-loading' );
						}

				}, 'json');
			});
		</script>
		<a class="wps-bton-first" role="button" href="<?php echo get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))); ?>"><?php echo __('My account', 'wpshop'); ?></a>
		<button data-nonce="<?php echo wp_create_nonce( 'wps_orders_load_details' ); ?>" class="wps-bton-second wps-orders-details-opener" id="wps-order-details-opener-<?php echo $_SESSION['order_id']; ?>"><?php _e( 'Order details', 'wpshop' ); ?></button>
		<?php if ( !empty( $order_meta ) && !empty( $order_meta[ 'order_invoice_ref' ] ) ) : ?>
			<a href="<?php echo admin_url( 'admin-post.php?action=wps_invoice&order_id='.$_SESSION['order_id'].'&invoice_ref='.$order_meta[ 'order_invoice_ref' ].'&mode=pdf' ); ?>" target="_blank" class="wps-bton-third" role="button"><?php _e( 'Download invoice', 'wpshop' ); ?></a>
		<?php endif; ?>
	</div>
<?php } ?>
