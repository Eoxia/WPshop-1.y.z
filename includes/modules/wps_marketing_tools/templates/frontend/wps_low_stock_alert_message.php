<?php if ( !defined( 'ABSPATH' ) ) exit;
$extradata = '';
if ( !empty($low_stock_alert_option['based_on_stock']) && $low_stock_alert_option['based_on_stock'] == 'yes' && !empty( $low_stock_alert_option['alert_limit']) ) : ?>
	<?php if ( $product_stock <= $low_stock_alert_option['alert_limit'] ) : 
		$extradata = ', '. sprintf( __('%s products in stock', 'wpshop'), number_format( $product_stock, 0 ) );
	 endif; ?>
<?php endif; ?>
<div class="wps-product-section">
	<img src="<?php echo WPSHOP_MEDIAS_ICON_URL; ?>error.gif" alt="" /> <?php _e('Stock soon exhausted', 'wpshop'); ?><?php echo $extradata; ?>
</div>