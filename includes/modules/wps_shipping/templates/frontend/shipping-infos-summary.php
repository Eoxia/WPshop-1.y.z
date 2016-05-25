<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-resume-address">
<?php if( !empty($shipping_content) ) : ?>
	<div class="wps-item-resume-address">
		<div class="entry-title"><?php _e( 'Shipping address', 'wpshop')?></div>
		<div class="entry-content">
			<?php echo $shipping_content; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="wps-item-resume-address">
		<div class="entry-title"><?php _e( 'Billing address', 'wpshop')?></div>
		<div class="entry-content">
			<?php echo $billing_content; ?>
		</div>
	</div>
</div>
