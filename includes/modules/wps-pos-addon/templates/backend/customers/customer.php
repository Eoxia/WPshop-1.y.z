<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
	<tr data-nonce="<?php echo wp_create_nonce( 'ajax_pos_customer_choice' ); ?>" class="wps-pos-addon-customer-line <?php echo ( ( $customer['last_name'] ==  __('Default', 'wps-pos-i18n') ) && ( $customer['first_name'] == __('Customer', 'wps-pos-i18n') ) ? 'info' : '' ); ?>" data-id="<?php echo $customer['ID']; ?>" >
		<td>
			<?php echo $customer['last_name']; ?> <?php echo $customer['first_name']; ?><br/>
			<strong><?php _e('E-mail')?> : </strong><?php echo $customer['email']; ?>
		</td>
		<td>
			<button class="wps-bton-first-rounded wps-pos-addon-choose-customer" type="button" data-type="customer" data-id="<?php echo $customer['ID']; ?>" ><i class="dashicons dashicons-businessman" title="<?php _e( 'Choose this customer', 'wps-pos-i18n' ); ?>" ></i></button>
		</td>
	</tr>
