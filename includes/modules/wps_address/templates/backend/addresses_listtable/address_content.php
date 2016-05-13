<?php if ( !defined( 'ABSPATH' ) ) exit;
if ( !empty( $address_meta ) && !empty( $address_meta[ '_wpshop_address_metadata' ] ) && !empty( $address_meta[ '_wpshop_address_metadata' ][ 0 ] ) ) : ?>
	<?php $address = unserialize( $address_meta[ '_wpshop_address_metadata' ][ 0 ] ); ?>
	<?php echo wps_address::display_an_address( $address, $post_id ); ?>
<?php else:  ?>
	<div class="wps-alert-info"><?php _e( 'This address has no informations', 'wpshop'); ?></div>
<?php endif; ?>