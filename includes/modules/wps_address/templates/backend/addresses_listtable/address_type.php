<?php if ( !defined( 'ABSPATH' ) ) exit;
 if ( !empty( $address_meta ) && !empty( $address_meta[ '_wpshop_address_attribute_set_id' ] ) && !empty( $address_meta[ '_wpshop_address_attribute_set_id' ][ 0 ] ) ) : ?>
	<?php $address_attribute_set = wpshop_attributes_set::getElement( $address_meta[ '_wpshop_address_attribute_set_id' ][ 0 ], "'valid','deleted'" ); ?>
	<a target="_wps_attribute_set_edition_page" href="<?php echo admin_url( 'admin.php?page=wpshop_attribute_group&action=edit&id=' . $address_attribute_set->id ); ?>" ><?php echo $address_attribute_set->name; ?></a>
<?php else:  ?>
	<div class="wps-alert-info"><?php _e( 'No type setted for address', 'wpshop'); ?></div>
<?php endif; ?>