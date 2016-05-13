<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="fullSize">
	<div id="model_histo_row" class="fullSize marginTop10p">
		<span class="width-100p inline"><?php _e( 'Number:', 'wps-pos-i18n' ); ?> %id%</span>
		<span class="width-130p inline bold">%date%</span>
		<span class="historow inline textRight paddLR20">
			%amount% <?php echo wpshop_tools::wpshop_get_currency(); ?>
		</span>
		<input type="hidden" class="date_histo" value="%date%">
		<input type="hidden" class="amount_histo" value="%amount%">
		<input type="hidden" class="payments_dl" value='%payments%'>
		<button class="wps-bton-third-rounded width-40p height-40p inline download_histo"><i class="dashicons dashicons-download"></i></button>
	</div>
	<div id="model_histo_no_results" class="fullSize marginTop10p" style="display: none;">
		<span class="inline"><?php _e( 'No result', 'wps-pos-i18n' ); ?></span>
	</div>
</div>