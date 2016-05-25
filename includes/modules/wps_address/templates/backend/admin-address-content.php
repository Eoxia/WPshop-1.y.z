<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-boxed summary_shipping_boxed">
	<?php $first = true; ?>
	<div class="wps-h5"><?php echo $title; ?></div>
	<div class="wps-form-group">
		<div class="wps-form">
		<select name="wps_order_address_<?php echo $address_type; ?>">
			<?php foreach( $addresses_datas as $address_id => $address_data ) : ?>
					<option value="<?php echo $address_id; ?>"><?php echo ( !empty($address_data['address_title']) ) ? $address_data['address_title'] : ''; ?></option>
			<?php endforeach; ?>
		</select>
		</div>
	<div>
			<ul>
				<?php foreach( $addresses_datas as $address_id => $address_data ) : ?>
				<li class="<?php echo ( !$first ) ? 'wpshopHide' : ''; ?>">
					<?php echo wps_address::display_an_address( $address_data, '', $address_type ); ?>
				</li>
				<?php $first = false; ?>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

</div>
