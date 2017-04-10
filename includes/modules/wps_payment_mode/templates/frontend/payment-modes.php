<?php if ( ! defined( 'ABSPATH' ) ) { exit;
}
?>
<div><span class="wps-h2"><?php _e( 'Payment mode choice', 'wpshop' ); ?> :</span></div>
<?php if ( ! empty( $payment_modes ) ) : ?>
<?php $count_payment_mode = count( $payment_modes ); ?>
<ul class="wps-itemList" id="wps-shipping-method-list-container" data-nonce="<?php echo wp_create_nonce( 'wps_load_shipping_methods' ); ?>">
	<?php foreach ( $payment_modes as $payment_mode_id => $payment_mode ) : ?>
		<?php if ( $default_choice == $payment_mode_id ) :
			$class = 'wps-activ';
			$checked = 'checked="checked"';
else :
			$checked = $class = '';
endif; ?>
		<li class="<?php echo $class; ?> wps-bloc-loader">
			<label>
				<span><input type="radio" name="wps-payment-method" value="<?php echo $payment_mode_id; ?>" id="<?php echo $payment_mode_id ; ?>" <?php echo $checked; ?> /></span>
				<span class="wps-shipping-method-logo">
					<?php echo ( ! empty( $payment_mode['logo'] ) ? ( (strstr( $payment_mode['logo'], 'http://' ) === false ) ? wp_get_attachment_image( $payment_mode['logo'], 'full' ) : '<img src="' . $payment_mode['logo'] . '" alt="" />' ) : '' ); ?>
				</span>
				<span class="wps-shipping-method-name"><strong><?php _e( $payment_mode['name'], 'wpshop' ); ?></strong></span>
			</label>
			<div class="wps-itemList-content">
				<?php _e( $payment_mode['description'], 'wpshop' ); ?>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
<?php else : ?>
	<div class="wps-alert-info"><?php _e( 'No payment mode available', 'wpshop' ); ?>	</div>
<?php endif; ?>
