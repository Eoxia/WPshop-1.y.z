<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<span class="wpshop_save_money_message wps-label-vert">
	<?php _e('Saving', 'wpshop'); ?> <?php echo number_format($save_amount,2); ?> <?php echo wpshop_tools::wpshop_get_currency(); ?>
</span>
