<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-customer-quick-add-alert-box wps-alert hidden" ></div>
<form id="create_new_customer_pos_addon" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" >
<?php
	global $wpdb;
	$query = $wpdb->prepare( "SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE name = %s", __( 'Billing address', 'wpshop' ) );
	$attribute_set_id = $wpdb->get_var( $query );
?>
	<input type="hidden" name="action" value="wpspos-customer-quick-add" />
	<?php wp_nonce_field( 'create_customer' ); ?>
	<input type="hidden" name="wps-customer-account-set-id" value="<?php echo $attribute_set_id; ?>" />

	<?php if ( !empty( $customer_attributes ) ) : ?>
		<?php foreach ( $customer_attributes as $customer_attribute_group ) : ?>
	<div class="wps-boxed">
		<span class="wps-h5"><?php echo stripslashes( $customer_attribute_group['name'] ); ?></span>
<?php
		foreach ( $customer_attribute_group[ 'attribut' ] as $attribute ) :
			if( !empty($attribute->code) ) {
				$output = wpshop_attributes::display_attribute( $attribute->code );
				if( empty($output['field_definition']['type']) || $output['field_definition']['type'] != 'hidden' ) {
					echo $output[ 'field' ];
				}
			}
		endforeach;
?>
	</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<button class="wps-bton-first-mini-rounded alignRight wps-bton-loader" id="wps-customer-quick-creation-button" ><?php _e( 'Add customer', 'wpshop'); ?></button>
</form>
<script type="text/javascript" >
	jQuery( document ).ready( function() {
		/**	Trigger event on new product creation form */
		var options = {
			beforeSubmit: function(formData, jqForm, options) {
				jQuery( "#wps-customer-quick-creation-button" ).addClass( "wps-bton-loading" );
				var has_error = false;
				for (var i=0; i < formData.length; i++) {
			        if ( formData[i].required && !formData[i].value ) {
				        has_error = true;
			        }
			    }
			    if ( has_error ) {
		            alert( wpshopConvertAccentTojs( "<?php _e( 'Please fill all fields mark as required', 'wpshop' ); ?>" ) );
		            return false;
			    }
			},
			success: function( responseText, statusText, xhr, $form ) {
				var message_status = '';
				if ( responseText[ 'status' ] ) {
					message_status = 'wps-alert-success';
					$form[0].reset();
					wpspos_set_customer_for_order( responseText[ 'customer_id' ], '<?php echo wp_create_nonce( 'ajax_pos_customer_choice' ); ?>' );

					if ( jQuery( ".wps-pos-customer-letter-choice-" + responseText[ 'letter' ] ).hasClass( "wps-bton-third-rounded" ) ) {
						jQuery( ".wps-pos-customer-letter-choice-" + responseText[ 'letter' ] ).click();
					}
					setTimeout( function(){
						jQuery( "#TB_closeWindowButton" ).click();
					}, '1500');
				}
				else {
					message_status = 'wps-alert-error';
				}
				jQuery('.wps-customer-quick-add-alert-box').html( responseText[ 'output' ] ).addClass( message_status ).show();
				setTimeout(function(){
					jQuery('.wps-customer-quick-add-alert-box').fadeOut( 'slow', function(){
						jQuery('.wps-customer-quick-add-alert-box').removeClass( message_status );
						jQuery('.wps-customer-quick-add-alert-box').html( '' );
						jQuery('.wps-customer-quick-add-alert-box').hide();
					});
	            }, 2500);

				jQuery( "#wps-customer-quick-creation-button" ).removeClass( "wps-bton-loading" );
			},
			dataType: "json",
		};
		jQuery( "#create_new_customer_pos_addon" ).submit( function(){
			jQuery(this).ajaxSubmit( options );
			return false;
		});

	});
</script>
