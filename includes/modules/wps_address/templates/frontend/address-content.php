<?php
$i = 0;
if ( !empty($list_addresses) ) :
	foreach( $list_addresses as $address_id => $address ) :
	$name_to_display  = '';
	$name_to_display .= ( !empty($address['address_last_name']) ) ? $address['address_last_name'].' ': '';
	$name_to_display .= ( !empty($address['address_first_name']) ) ? $address['address_first_name'].' - ': '';
	$name_to_display .= ( !empty($address['address']) ) ? $address['address'].' ': '';
	$name_to_display .= ( !empty($address['postcode']) ) ? $address['postcode'].' ': '';
	$name_to_display .= ( !empty($address['city']) ) ? $address['city'].' ': '';

	$class = $checked = '';

	$selected_address = ( !empty($_SESSION[ $type.'_address' ] ) ) ? $_SESSION[ $type.'_address' ] : '';

	if( !empty($selected_address) && $address_id == $selected_address ) {
		$class = 'wps-activ';
		$checked = 'checked="checked"';
	}
	else {
		if( $i == 0 && empty($selected_address) ) {
			$checked = 'checked="checked"';
			$class = 'wps-activ';
		}
		else {
			$checked = $class = '';
		}
	}

?>
		<li class="<?php echo $class; ?> wps-bloc-loader">
			<span><input type="radio" class="wps_select_address" value="<?php echo $address_id; ?>" name="<?php echo $type; ?>_address_id" id="wps_select_address_<?php echo $address_id; ?>" <?php echo $checked; ?> /></span>
			<span><strong><?php echo ( ( !empty($address['address_title']) ) ? $address['address_title'] : '' ); ?></strong></span>
			<span><?php echo $name_to_display; ?></span>
			<?php //if( !$is_from_admin ) : ?>
			<span class="wps-itemList-tools">
				<a href="" title="<?php _e( 'Edit this address', 'wpshop' ); ?>" class="wps-address-edit-address" id="wps-address-edit-address-<?php echo $address_id; ?>" data-address_type="<?php echo $address_type_id; ?>" data-nonce="<?php echo wp_create_nonce( 'wps_load_address_form_' . $address_type_id ); ?>" ><i class="wps-icon-pencil"></i></a>
				<a href="" title="<?php _e( 'Delete this address', 'wpshop' ); ?>" class="wps-address-delete-address" id="wps-address-delete-address-<?php echo $address_id; ?>-<?php echo $address_type_id; ?>" data-nonce="<?php echo wp_create_nonce( 'wps_delete_an_address' ); ?>" ><i class="wps-icon-trash"></i></a>
			</span>
			<?php //endif; ?>
			<div class="wps-itemList-content">
				<?php echo wps_address::display_an_address( $address, $address_id ); ?>
			</div>
		</li>
<?php
		$i++;
	endforeach;
endif; ?>