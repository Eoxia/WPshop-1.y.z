<?php namespace eoxia;

if ( !defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap wpeo-logs-wrap">
	<h2>
		<?php _e('Logs', 'digirisk'); ?>
		<a class="add-new-h2" href="<?php echo admin_url( 'admin-post.php?action=add' ); ?>"><?php _e( 'Add New', 'digirisk' ); ?></a>
		<a class="reset-log" href="<?php echo admin_url( 'admin-post.php?action=reset' ); ?>"><?php _e( 'Reset', 'digirisk' ); ?></a>
	</h2>

	<?php if ( !empty( $page_transient ) ): ?>
	  	<div class="<?php echo $page_transient['type']; ?> notice">
	    	<p><?php echo $page_transient['message']; ?></p>
		</div>
	<?php endif; ?>

	<?php
	$action = sanitize_text_field( !empty( $_GET['action'] ) ? $_GET['action'] : '' );
	if ( !empty( $action ) && 'edit' == $action ):
		require ( Config_Util::$init['main']->full_plugin_path . \eoxia\Config_Util::$init['wpeo_log']->path . '/view/list-service-edit.view.php' );
	elseif ( !empty( $action ) && 'view' == $action ):
		require ( Config_Util::$init['main']->full_plugin_path . \eoxia\Config_Util::$init['wpeo_log']->path . '/view/view-service.view.php' );
		require ( Config_Util::$init['main']->full_plugin_path . \eoxia\Config_Util::$init['wpeo_log']->path . '/view/chart.view.php' );
	else:
		require ( Config_Util::$init['main']->full_plugin_path . \eoxia\Config_Util::$init['wpeo_log']->path . '/view/list-service.view.php' );
	endif;
	?>
</div>
