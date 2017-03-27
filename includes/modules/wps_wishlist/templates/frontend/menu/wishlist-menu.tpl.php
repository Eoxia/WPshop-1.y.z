<?php if ( !defined( 'ABSPATH' ) ) exit;
$account_dashboard_part = !empty( $_GET['account_dashboard_part'] ) ? sanitize_text_field( $_GET['account_dashboard_part'] ) : '';
?>
<li class="<?php echo !empty($account_dashboard_part) && $account_dashboard_part == 'my-wishlist' ? 'wps-activ' : ''; ?>">
	<a data-target="menu1" href="<?php echo get_permalink($account_page_id).( (!empty($permalink_option) ? '?' : '?' ).'account_dashboard_part=my-wishlist' ); ?>" title="" class="">
		<i class="wps-icon-love"></i>
		<span><?php _e( 'Wishlist', 'wps_wishlist_i18n'); ?></span>
	</a>
</li>
