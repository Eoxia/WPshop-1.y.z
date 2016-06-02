<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-<?php echo $address_type; ?>-address" <?php if ( !empty( $first_address_checking ) && !$is_from_admin && ( $address_type == 'billing' ) ) { echo 'style="display: none;"'; } ?>>
	<div class="wps-gridwrapper">
		<div class="wps-grid4x6"><span class="wps-h3"><?php echo $address_title; ?></span></div>
	</div>
	<ul class="wps-itemList wps-address-container" data-nonce="<?php echo wp_create_nonce( 'wps_reload_address_interface' ); ?>" id="wps-address-container-<?php echo $address_type_id; ?>">
<?php if( !empty($box_content) ): ?>
		<?php echo $box_content; ?>
<?php endif; ?>
	</ul>

<?php $hide_add_btn = true;
	if ( empty( $box_content ) && ( $address_type == 'shipping' ) ) :
		$hide_add_btn = false; ?>
	<div class="wps-address-first-address-creation-container" >
		<div id="wps_address_error_container" ></div>
		<form id="wps_address_form_save_first_address" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
			<input type="hidden" name="action" value="wps_save_address" />
			<?php wp_nonce_field( 'wps_save_address' ); ?>
			<input type="hidden" name="wps-address-save-the-first" value="<?php echo $address_type; ?>" />
			<?php echo self::display_form_fields( $address_type_id, '', 'first', '', array(), array(), array(), get_current_user_id() ); ?>

<?php
			/** Check if a billing address is already save **/
	if ( $address_type_id == $shipping_option['choice'] ) :
?>
			<label class="wps-form" >
				<input name="wps-shipping-to-billing" id="wps-shipping_to_billing" checked="checked" type="checkbox" />
				<?php _e( 'Use the same address for billing', 'wpshop' ); ?>
			</label>
<?php endif; ?>

			<button class="wps_submit_address_form wps-bton-first-alignRight-rounded"><?php _e('Save', 'wpshop'); ?></button>
		</form>
	</div>

<?php elseif ( empty( $box_content ) && ( $address_type == 'billing' ) && !$shipping_is_avalaible ) :
	$hide_add_btn = $shipping_is_avalaible; ?>

	<div class="wps-address-first-address-creation-container" >
		<div id="wps_address_error_container" ></div>
		<form id="wps_address_form_save_first_address" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
			<input type="hidden" name="action" value="wps_save_address" />
			<?php wp_nonce_field( 'wps_save_address' ); ?>
			<input type="hidden" name="wps-address-save-the-first" value="<?php echo $address_type; ?>" />
			<?php echo self::display_form_fields( $address_type_id, '', 'first', '', array(), array(), array(), get_current_user_id() ); ?>

<?php
			/** Check if a billing address is already save **/
	if ( $address_type_id != $billing_option['choice'] ) :
?>
			<label class="wps-form" >
				<input name="wps-shipping-to-billing" id="wps-shipping_to_billing" checked="checked" type="checkbox" />
				<?php _e( 'Use the same address for billing', 'wpshop' ); ?>
			</label>
<?php endif; ?>

			<button class="wps_submit_address_form wps-bton-first-alignRight-rounded"><?php _e('Save', 'wpshop'); ?></button>
		</form>
	</div>
<?php endif; ?>

	<?php if( !$is_from_admin && $hide_add_btn ) : ?>
		<button data-nonce="<?php echo wp_create_nonce( 'wps_load_address_form_' . $address_type_id ); ?>" id="wps-add-an-address-<?php echo $address_type_id; ?>" class="wps-bton-third wps-add-an-address<?php echo ( empty( $box_content ) && !$hide_add_btn ? ' hidden' : '' ); ?>" ><i class="wps-icon-plus"></i><?php printf( __('Add a %s', 'wpshop' ), strtolower($address_title) ); ?></button>
	<?php endif; ?>
</div>
