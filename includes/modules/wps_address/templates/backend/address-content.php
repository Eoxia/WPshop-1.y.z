<?php if ( !defined( 'ABSPATH' ) ) exit;
global $post_id;
$post_id = ( !empty($order_id) ) ? $order_id : $post_id;
$order_infos = get_post_meta( $post_id, '_order_info', true );
$order_meta = get_post_meta( $post_id, '_order_postmeta', true );
$i = 0;

if ( !empty($list_addresses) ) :
	foreach( $list_addresses as $address_id => $address ) :
	$adress_post = get_post( $address_id );
	$name_to_display  = $customer_name = '';
	$customer_name  = ( !empty($address['address_last_name']) ) ? $address['address_last_name'].' ': '';
	$customer_name .= ( !empty($address['address_first_name']) ) ? $address['address_first_name'].' - ': '';
	$name_to_display .= ( !empty($address['address']) ) ? $address['address'].' ': '';
	$name_to_display .= ( !empty($address['postcode']) ) ? $address['postcode'].' ': '';
	$name_to_display .= ( !empty($address['city']) ) ? $address['city'].' ': '';
	$name_to_display .= ( !empty($address['country']) ) ? $address['country'].' ': '';

	$class = $checked = $selected_address = '';
	if( !empty( $order_infos ) && !empty( $order_infos[$type]) && !empty($order_infos[$type]['address_id']) ) {
		if( $adress_post->post_author == $customer_id && $customer_id == $order_meta['customer_id'] ) {
			$selected_address = $order_infos[$type]['address_id'];

		}
	}

	if( !empty($selected_address) && $address_id == $selected_address ) {
		$class = 'wps-activ';
		$checked = 'checked="checked"';
	}
	else {
		if( $i == 0 && empty($selected_address) ) {
			$checked = 'checked="checked"';
			$class = 'wps-activ';
		}
	}

	$url_separator = '?';
	if ( strpos( admin_url( 'admin-ajax.php' ), '?' ) ) :
		$url_separator = '&';
	endif;
?>
		<li class="<?php echo $class; ?> wps-bloc-loader wps_address_li">
			<span><input type="radio" class="wps_select_address" value="<?php echo $address_id; ?>" name="<?php echo $type; ?>_address_id" id="wps_select_address_<?php echo $address_id; ?>" <?php echo $checked; ?> /></span>
			<span><strong><?php echo ( ( !empty($customer_name) ) ? $customer_name : '' ); ?></strong></span>
			<span><?php echo $name_to_display; ?></span>
			<?php //if( !$is_from_admin ) : ?>
			<span class="wps-itemList-tools">
				<!-- wps_delete_an_address -->
				<a href="<?php print wp_nonce_url( admin_url( 'admin-ajax.php' ) . $url_separator . 'action=wps_order_load_address_edit_form&address_id=' . $address_id . '&address_type=' . $address_type_id . '&customer_id=' . $customer_id . '&width=740&height=690', 'load_adress_edit_form', '_wpnonce' ); ?>" title="<?php _e( 'Edit this address', 'wpshop' ); ?>" class="wps-address-edit-address  thickbox" id="wps-address-edit-address-<?php echo $address_id; ?>"><i class="wps-icon-pencil"></i></a>
				<a href="" data-nonce="<?php echo wp_create_nonce( 'delete_address_in_order_panel_' . $address_id . '-' . $address_type_id ); ?>" title="<?php _e( 'Delete this address', 'wpshop' ); ?>" class="wps-address-delete-address" id="wps-address-delete-address-<?php echo $address_id; ?>-<?php echo $address_type_id; ?>"><i class="wps-icon-trash"></i></a>
			</span>
			<?php //endif; ?>
		</li>
		<li class="wps_address_li_content"<?php echo ($checked) ? ' style="display: list-item;"' : '';?>>
			<?php echo wps_address::display_an_address( $address, $address_id ); ?>
		</li>
<?php
		$i++;
	endforeach;
endif; ?>
