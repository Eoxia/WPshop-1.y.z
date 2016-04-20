<li class="<?php echo !empty($_GET['account_dashboard_part']) && $_GET['account_dashboard_part'] == 'my-wishlist' ? 'wps-activ' : ''; ?>">
	<a data-target="menu1" href="<?php echo get_permalink($account_page_id).( (!empty($permalink_option) ? '?' : '?' ).'account_dashboard_part=my-wishlist' ); ?>" title="" class="">
		<i class="wps-icon-love"></i>
		<span><?php _e( 'My wishlist', 'wps_wishlist_i18n'); ?></span>
	</a>
</li>
