<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-gridwrapper2-padded">
	<div style="width:100%">
		<div class="wps-form-group">
			<label><?php _e( 'Write your notification and send it to your customer', 'wpshop'); ?> :</label>
			<div class="wps-form"><textarea name="order_private_comment"></textarea></div>
		</div>
		<div class="wps-form-group"><input type="checkbox" name="send_email" id="wps_send_email" /> <label for="wps_send_email"><?php _e('Send an email to customer','wpshop'); ?></label><br/>
		<input type="checkbox" name="copy_to_administrator" id="copy_to_administrator" /> <label for="copy_to_administrator"><?php _e('Send a copy to administrator','wpshop'); ?></label></div>
		<div class="wps-form-group"><a data-nonce="<?php echo wp_create_nonce( 'wpshop_add_private_comment_to_order' ); ?>" href="#" class="wps-bton-first-mini-rounded addPrivateComment order_<?php echo $post->ID; ?>"><?php _e('Add the comment','wpshop'); ?></a></div>
	</div>

	<div id="wps_private_messages_container">
		<?php if( !empty($post) && !empty($post->ID) ) :
		$oid = $post->ID;
		require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "order-private-comments/wps_orders_sended_private_comments") );
		endif; ?>
	</div>
</div>
