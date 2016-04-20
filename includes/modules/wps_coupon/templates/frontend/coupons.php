<?php if( !$is_from_admin ) : ?>
<span class="wps-h5"><?php _e( 'My coupons', 'wpshop'); ?></span>
<?php endif;  ?>

<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Coupon value', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Validity date', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Coupon code', 'wpshop'); ?></div>
	</div>
	<?php echo $coupons_rows; ?>
</div>

