<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap wps-pos-dashboard-wrap">
	<div class="wps-pos-dahboard-header" >
		<h2><?php _e('WP-Shop POS Software', 'wps-pos-i18n'); ?> <a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page=wps-pos&new_order=yes' ); ?>"><?php _e('Create a new order', 'wps-pos-i18n')?></a></h2>
	<!-- 	<div class="wpspos-main-actions-buttons-container" ><?php require_once( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend', 'pos', 'top_button' ) ); ?></div> -->
		<?php require_once( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend', 'tab_pos' ) ); ?>
	</div>

	<div id="wpspos-dashboard-widgetswrap" class="metabox-holder" ><?php
		/**	Create nonce for metabox order saving securisation	*/
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false);
		/**	Create nonce for metabox order saving securisation	*/
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false);

		/**	Call the different fonction to add meta boxes on dashboard	*/
		do_meta_boxes( 'wpspos-dashboard', 'wpspos-dashboard-summary', null );
	?>
		<div class="wpspos-dashboard-contents wpspos-current-step-<?php echo $current_step; ?>" >
	<?php
		do_meta_boxes( 'wpspos-dashboard', 'wpspos-dashboard-left', null );
		do_meta_boxes( 'wpspos-dashboard', 'wpspos-dashboard-right', null );
		do_meta_boxes( 'wpspos-bank-deposit', 'wpspos-bank-deposit-left', null );
		do_meta_boxes( 'wpspos-bank-deposit', 'wpspos-bank-deposit-right', null );
	?>
		</div>
	</div><!-- wpspos-dashboard-widgets-wrap -->
</div><!-- wps-pos-dashboard-wrap -->