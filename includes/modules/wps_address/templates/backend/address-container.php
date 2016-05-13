<?php if ( !defined( 'ABSPATH' ) ) exit;
$box_content = wps_address::display_address_interface_content($address_type_id, $address_title, '', $address_type, $customer_id, true, $order_id );
?>

<div>
	<div class="<?php echo $extra_class; ?> wps-boxed">
		<span class="wps-h3"><?php echo $address_title; ?><a id="wps-add-an-address-<?php echo $address_type_id; ?>" class="add-new-h2 alignright thickbox" href="<?php echo admin_url( 'admin-ajax.php'); ?>?action=wps_order_load_address_edit_form&address_type=<?php echo $address_type_id; ?>&customer_id=<?php echo $customer_id; ?>&width=740&height=690"><i class="wps-icon-plus"></i><?php printf( __('Create a %s', 'wpshop' ), strtolower($address_title) ); ?></a>
		</span>
		<div style="clear : both;">
			<ul class="wps-itemList wps-address-container" id="wps-address-container-<?php echo $address_type_id; ?>">
				<?php if( !empty($box_content) ): ?>
				<?php echo $box_content; ?>
				<?php else : ?>
					<div class="wps-alert-info"><?php printf( __( 'You do not have create a %s', 'wpshop'), strtolower( $address_title ) ); ?></div>
				<?php endif; ?>
			</ul>
		</div>

	</div>
</div>


