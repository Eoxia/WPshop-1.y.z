<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<h3><?php _e( 'Order summary', 'wps-pos-i18n' ); ?></h3>
<ul class="wpspos-order-content-summary" >
	<li><span class="wpspos-order-summary-title"><?php _e( 'Number of product', 'wps-pos-i18n' ); ?></span><span class="wpspos-order-summary-content" ><?php echo count( $order_items ); ?></span></li>
	<li><span class="wpspos-order-summary-title"><?php _e( 'Amount to pay', 'wps-pos-i18n' ); ?></span><span class="wpspos-order-summary-content" ><?php echo wpshop_tools::formate_number( $cart_content['order_amount_to_pay_now'] ); ?> <?php echo wpshop_tools::wpshop_get_currency(); ?></span></li>
</ul>

<h3><?php _e( 'Order payment', 'wps-pos-i18n' ); ?></h3>
<form class="wpspos-order-payment-form" action="<?php echo admin_url( "admin-ajax.php" ); ?>" method="post" >
	<input type="hidden" name="action" value="wpspos-finish-order" />
	<?php wp_nonce_field( 'wps_pos_process_checkout', '_wpnonce' ); ?>
	<input type="hidden" name="wps-pos-total-order-amount" value="<?php echo $cart_content['order_amount_to_pay_now']; ?>" />
	<input type="hidden" name="order_id" value="<?php echo $current_order_id; ?>" />
	<ul class="wpspos-order-payment-method" >
		<li>
			<label>
				<?php _e('Check', 'wps-pos-i18n'); ?><br/>
				<input type="radio" name="wpspos-payment-method" id="wpshop_pos_addon_payment_method_input_check" value="check" />
			</label>
		</li>
		<li>
			<label>
				<?php _e('Credit card', 'wps-pos-i18n'); ?><br/>
				<input type="radio" name="wpspos-payment-method" id="wpshop_pos_addon_payment_method_input_credit_card" value="credit_cart" />
			</label>
		</li>
		<li>
			<label>
				<?php _e('Money', 'wps-pos-i18n'); ?><br/>
				<input type="radio" name="wpspos-payment-method" id="wpshop_pos_addon_payment_method_input_money" value="money" />
			</label>
		</li>
	</ul>
	<div class="wpspos-payment-container" >
		<label class="wpspos-full-amount-payment wpshopHide" >
			<input type="checkbox" value="true" name="wpspos-payment-is-complete" id="wpspos-customer-paid-full-amount" checked="checked" />
			<?php _e('Customer paid the full amount', 'wps-pos-i18n'); ?>
		</label>
		<div id="wpspos-received-amount-container" >
			<?php _e( 'Given Amount', 'wps-pos-i18n' )?> : <input type="text" name="wpspos-order-received-amount" value="" class="wpspos-order-received-amount" placeholder="<?php echo $cart_content['order_amount_to_pay_now']; ?>" /> <?php echo wpshop_tools::wpshop_get_currency(); ?>
		</div>
		<div class="wpshopHide alignright wps-alert wps-alert-info" id="wpspos-back-cash" >
			<?php _e( 'Change due', 'wps-pos-i18n' ); ?> : <span class="wpspos-due-change" ></span>
		</div>
	</div>
	<div class="wpspos-order-cash-area" >
		<div class="alignright" >
			<button class="wps-bton-second-rounded" id="wpspos-cancel-order-cash" ><?php _e( 'Cancel', 'wps-pos-i18n' ); ?></button>
			<button class="wps-bton-first-rounded wps-bton-loader" id="wpspos-order-cash" ><?php _e( 'Cash', 'wps-pos-i18n' ); ?></button>
		</div>
	</div>
</form>
<script type="text/javascript" >
	jQuery( document ).ready( function(){
		jQuery( "#wpspos-order-cash" ).click( function(){
			jQuery( ".wpspos-order-payment-form" ).ajaxSubmit({
				beforeSubmit: function( formData, jqForm, options ){
					var payment_method_found = false;
					var payment_method_chosen = deposit_amount = total_amount = '';
					for ( var i=0; i < formData.length; i++ ) {
						if ( 'wpspos-payment-method' == formData[i].name ) {
							payment_method_found = true;
							payment_method_chosen = formData[i].value;
						}

						if ( 'wpspos-order-received-amount' == formData[i].name ) {
							deposit_amount = formData[i].value;
						}
						if ( 'wps-pos-total-order-amount' == formData[i].name ) {
							total_amount = formData[i].value;
						}
				    }

				    if ( !payment_method_found ) {
					    alert( "<?php _e( 'Please select a payment method', 'wps-pos-i18n' ); ?>" );
					    return false;
				    }
				    else if ( ( parseFloat( deposit_amount ) > parseFloat( total_amount ) ) && ( "money" != payment_method_chosen ) ) {
					    alert( "<?php _e( 'Deposit amount exceeds order amount', 'wps-pos-i18n' ); ?>" );
				    	return false;
				    }

				    jQuery( "#wpspos-order-cash" ).addClass( "wps-bton-loading" );
				},
				success: function(responseText, statusText, xhr, $form){
				    jQuery( "#wpspos-order-cash" ).removeClass( "wps-bton-loading" );

				    if ( true == responseText[ 'status' ] ) {
					    jQuery( ".wpspos-order-final-step-container" ).html( responseText[ 'output' ] );
					    jQuery( "#wps-pos-order-content-alert" ).html( responseText[ 'message' ] );
				    	jQuery( "#wps-pos-order-content-alert" ).show().addClass( "wps-alert-success" );

					    jQuery( "#wps_cart_container input[type=text]" ).each( function(){
							jQuery( this ).prop( "readonly", true );
					    });
						jQuery( ".wpspos-dashboard-contents" ).removeClass( "wpspos-current-step-2" );
						jQuery( ".wpspos-dashboard-contents" ).addClass( "wpspos-current-step-3" );
				    }
				    else {
					    jQuery( "#wps-pos-order-content-alert" ).html( responseText[ 'message' ] );
				    	jQuery( "#wps-pos-order-content-alert" ).show().addClass( "wps-alert-error" );
				    }

					/**	Close finalisation thickbox	*/
					jQuery( "#TB_closeWindowButton" ).click();
				},
				dataType: 'json',
			});

			return false;
		});
	} );
</script>