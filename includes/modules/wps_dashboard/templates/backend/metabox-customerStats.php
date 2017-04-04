<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e('Number of users', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php $result = count(get_users()); echo $result; ?></div>
		<div class="wps-table-cell"><?php echo ( !empty($result) ) ? '<a href="' . admin_url(). 'admin.php?page=wpshop_dashboard&download_users=users_all" role="button" id="download_all_users_contacts" class="wps-bton-first-rounded">' .__( 'Download the list', 'wpshop' ). '</a>' : ''; ?></div>
	</div>

	<div class="wps-table-header wps-table-row">
		<?php
		$query = $wpdb->prepare( "SELECT COUNT( DISTINCT user_id ) FROM {$wpdb->usermeta} JOIN {$wpdb->posts} ON post_author = user_id AND post_type = %s WHERE meta_key = %s AND ( meta_value LIKE ('%%%s%%') || meta_value LIKE ('%%%s%%') )", WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'user_preferences', 's:16:"newsletters_site";i:1;', 's:16:"newsletters_site";b:1;' );
		$nbcustomers_site = $wpdb->get_var($query);
		?>
		<div class="wps-table-cell"><?php _e('Number of customers who wants to receive shop newsletters', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php echo $nbcustomers_site; ?></div>
		<div class="wps-table-cell"><?php echo ( !empty($nbcustomers_site) ) ? '<a href="' . admin_url(). 'admin.php?page=wpshop_dashboard&download_users=newsletters_site" role="button" id="download_newsletter_contacts" class="wps-bton-first-rounded">' .__( 'Download the list', 'wpshop' ). '</a>' : ''; ?></div>
	</div>

	<div class="wps-table-header wps-table-row">
		<?php
		$query = $wpdb->prepare( "SELECT COUNT( DISTINCT user_id ) FROM {$wpdb->usermeta} JOIN {$wpdb->posts} ON post_author = user_id AND post_type = %s WHERE meta_key = %s AND ( meta_value LIKE ('%%%s%%') || meta_value LIKE ('%%%s%%') )", WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'user_preferences', 's:25:"newsletters_site_partners";i:1;', 's:25:"newsletters_site_partners";b:1;' );
		$nbcustomers_site_partners = $wpdb->get_var($query);
		?>
		<div class="wps-table-cell"><?php _e('Number of customers who wants to receive partners newsletters', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php echo $nbcustomers_site_partners; ?></div>
		<div class="wps-table-cell"><?php echo ( !empty($nbcustomers_site_partners) ) ? '<a href="' . admin_url(). 'admin.php?page=wpshop_dashboard&download_users=newsletters_site_partners" role="button" id="download_newsletter_partners_contacts" class="wps-bton-first-rounded">' .__( 'Download the list', 'wpshop' ). '</a>' : ''; ?></div>
	</div>
</div>
