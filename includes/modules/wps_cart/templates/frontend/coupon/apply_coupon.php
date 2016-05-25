<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<!--  <div class="wps-gridwrapper">-->
<?php
$wps_coupon_ctr = new wps_coupon_ctr();
$results = $wps_coupon_ctr->getCoupons();
unset($wps_coupon_ctr);
if( !empty($results) ) :
?>
<div class="wps-coupon">
	<div class="wps-form-group-inline">
		<label for="wps_coupon_code">
			<?php _e( 'Coupon', 'wpshop'); ?>
		</label>
		<div class="wps-form-inliner">
			<input type="text" value="" id="wps_coupon_code" />
			<button id="wps_apply_coupon" data-nonce="<?php echo wp_create_nonce( 'wps_apply_coupon' ); ?>" class="wps-bton-first wpsjs-apply-coupon">
				<?php _e( 'Apply', 'wpshop' ); ?>
			</button>
		</div> <!-- wps-form-inliner -->
	</div> <!-- wps-form-group-inline -->
	<div id="wps_coupon_alert_container"></div>

	<!-- Tableau qui liste les coupons actifs avec possibilité de les supprimer ? -->

<!-- 	<div class="wps-table">
		<div class="wps-table-header">
			<div>Code</div>
			<div>Remise</div>
			<div></div>
		</div>
		<?php foreach ($results as $key => $result) : ?>
		<?php $data = get_post_meta( $result->ID ) ?>
			<div>
				<div>
					<?php echo $data['wpshop_coupon_code'][0]; ?>
				</div>
				<div></div>
				<div><a href="#"><i class="wps-icon-close"></i></a></div>
			</div>
		<?php endforeach; ?>
	</div> -->


</div> <!-- wps-coupon -->
<?php endif; ?>
