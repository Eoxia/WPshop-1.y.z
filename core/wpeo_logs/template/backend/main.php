<?php if ( !defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap wpeo-logs-wrap">
	<h2><?php _e('Logs', 'wpeolog-i18n'); ?> <a class="add-new-h2" href="<?php  echo admin_url( 'admin-post.php?action=add' ); ?>"><?php _e( 'Add New', 'wpeolog-i18n' ); ?></a></h2>

	<?php if ( !empty( $page_transient ) ): ?>
	  	<div class="<?php echo $page_transient['type']; ?> notice">
	    	<p><?php echo $page_transient['message']; ?></p>
		</div>
	<?php endif; ?>

	<?php
	$action = sanitize_text_field( $_GET['action'] );
	if ( !empty( $action ) && 'edit' == $action ):
		require_once( wpeo_template_01::get_template_part( WPEO_LOGS_DIR, WPEO_LOGS_TEMPLATES_MAIN_DIR, 'backend', 'list', 'service-edit' ) );
	elseif ( !empty( $action ) && 'view' == $action ):
		require_once( wpeo_template_01::get_template_part( WPEO_LOGS_DIR, WPEO_LOGS_TEMPLATES_MAIN_DIR, 'backend', 'view', 'service' ) );
		require_once( wpeo_template_01::get_template_part( WPEO_LOGS_DIR, WPEO_LOGS_TEMPLATES_MAIN_DIR, 'backend', 'chart' ) );
	else:
		require_once( wpeo_template_01::get_template_part( WPEO_LOGS_DIR, WPEO_LOGS_TEMPLATES_MAIN_DIR, 'backend', 'list', 'service' ) );
	endif;
	?>
</div>
