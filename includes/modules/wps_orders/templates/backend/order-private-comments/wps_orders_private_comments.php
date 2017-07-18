<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div>
	<div>
		<label for="textarea_order_private_comment"><?php _e( 'Write your notification and send it to your customer', 'wpshop'); ?> :</label>
		<div class="wps-form">
			<textarea id="textarea_order_private_comment" name="order_private_comment"></textarea>
		</div>
		<a data-nonce="<?php echo wp_create_nonce( 'wpshop_add_private_comment_to_order' ); ?>" href="#" class="wps-bton-first-mini-rounded addPrivateComment order_<?php echo $post->ID; ?>"><?php _e('Add the comment','wpshop'); ?></a>
	</div>

	<div id="wps_private_messages_container">
		<?php if( !empty($post) && !empty($post->ID) ) :
		$oid = $post->ID;
		require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "order-private-comments/wps_orders_sended_private_comments") );
		endif; ?>
	</div>
</div>
