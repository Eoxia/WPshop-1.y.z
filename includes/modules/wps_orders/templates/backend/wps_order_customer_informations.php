<?php if ( !defined( 'ABSPATH' ) ) exit;
if( !empty($order_metadata) ) : 
?>
<div class="wps-gridwrapper3-padded">
	<div>
		<?php echo $customer_datas; ?>
	</div>


	<div>
		<div class="wps-boxed summary_shipping_boxed">
			<div class="wps-h5"><?php _e( 'Billing address', 'wpshop')?></div>
			<?php 
			if( !empty($billing_address_content) ) : 
				echo $billing_address_content; 
			else : 
			?>
				<div class="wps-alert-error"><?php _e( 'No billing address informations are found', 'wpshop'); ?></div>
			<?php 
			endif;
			?>
		</div>
	</div>
	<div>
		<div class="wps-boxed summary_shipping_boxed">
			<div class="wps-h5"><?php _e( 'Shipping address', 'wpshop')?></div>
			<?php 
			if( !empty($shipping_address_content) ) : 
				echo $shipping_address_content; 
			else : 
			?>
				<div class="wps-alert-error"><?php _e( 'No shipping address informations are found', 'wpshop'); ?></div>
			<?php 
			endif;
			?>
		</div>
	</div>
	
</div>

<?php 
else :
?>
<div class="wps-alert-info"><?php _e( 'Please choose a customer or create one', 'wpshop'); ?></div>
<?php 
endif;
?>


