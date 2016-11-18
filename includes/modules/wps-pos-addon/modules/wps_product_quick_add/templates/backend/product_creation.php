<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<form id="wps_product_quick_add_form" method="post" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" data-nonce="<?php echo wp_create_nonce( 'ajax_pos_product_variation_selection' ); ?>" >
	<input type="hidden" name="action" value="wpspos-product-quick-add">
	<?php wp_nonce_field( 'create_product' ); ?>
	<div class="wps-boxed" style="margin-top:10px;" >
		<span class="wps-h5"><?php _e( 'Product', 'wpshop'); ?></span>

		<div class="wpshop_cls" >
			<div class="wpshop_form_label _product_title_label alignleft" >
				<label for="attribute_product_title" ><?php _e( 'Product name', 'wpshop'); ?> <span class="wpshop_required" >*</span></label>
			</div>
			<div class="wpshop_form_input_element _product_title_input alignleft" >
				<input type="text" name="post_title" id="post_title" value="" class="wpshop_product_attribute_post_title wpshop_attributes_display" required >
			</div>
		</div>

		<div class="wpshop_cls" >
			<div class="wpshop_form_label _description_label alignleft" >
				<label for="attribute_description" ><?php _e( 'Description', 'wpshop'); ?></label>
			</div>
			<div class="wpshop_form_input_element _description_input alignleft" >
				<textarea name="post_content" id="post_content" class="wpshop_product_post_content wpshop_attributes_display" rows="2"></textarea>
			</div>
		</div>
	</div>

<?php
	/**	Get the attribute set list	*/
	$attribute_set_list = wpshop_attributes_set::get_attribute_set_list_for_entity( wpshop_entities::get_entity_identifier_from_code( 'wpshop_product' ) );
	$default_set = 0;
	if ( 1 == count( $attribute_set_list ) ) {
		$default_set = $attribute_set_list[ 0 ]->id;
	}
?>
	<?php /**	Check if attribute set list is not empty in order to display a dropdown for sélection	*/	?>
	<?php if ( !empty( $attribute_set_list ) && ( 1 <= count( $attribute_set_list ) ) ) : ?>
	<div style=" width:80%; margin: 0 auto 10px auto; " >
		<?php _e( 'Product type to create', 'wpshop' ); ?> :
		<select name="wps-product-attribute-set" data-nonce="<?php echo wp_create_nonce( 'attribute_list_reload' ); ?>" >
	<?php foreach( $attribute_set_list as $attribute_set ) : ?>
			<?php $is_default_set = false; ?>
			<?php if ( !empty( $attribute_set->default_set ) && strtolower( __( 'Yes', 'wpshop' ) ) == strtolower( __( $attribute_set->default_set, 'wpshop' ) ) ) : ?>
				<?php $is_default_set = true; ?>
				<?php $default_set = $attribute_set->id; ?>
			<?php endif; ?>
			<option value="<?php echo $attribute_set->id; ?>" <?php selected( ( !empty( $chosen_set ) ? true : $is_default_set ) , true, true ); ?> ><?php echo $attribute_set->name; ?></option>
	<?php endforeach; ?>
		</select>
	</div>
	<?php endif; ?>

	<?php /**	If default set or if there is a selected set get existing attributes list for this set	*/ ?>
	<?php if ( !empty( $default_set ) || !empty( $chosen_set ) ) : ?>
		<?php $this->display_attribute( !empty( $chosen_set ) ? $chosen_set : ( !empty( $default_set ) ? $default_set : 0 ) ); ?>
	<?php endif; ?>

	<button class="wps-bton-first-mini-rounded alignRight wps-bton-loader" id="wps-product-quick-creation-button" ><?php _e( 'Add product', 'wpshop'); ?></button>
</form>
<script type="text/javascript" >
	jQuery( document ).ready( function() {

		/**	When changing attribute set reload attribute list */
		jQuery( "select[name=wps-product-attribute-set]" ).change( function() {
			jQuery( "#wps-product-quick-creation-form-attributes" ).addClass( "wps-bloc-loading" );
			var data = {
				"action": "wps-product-quick-add-reload-attribute-list",
				"_wpnonce": jQuery( this ).data( 'nonce' ),
				"attribute_set" : jQuery( this ).val(),
			};
			jQuery( "#wps-product-quick-creation-form-attributes" ).load( ajaxurl, data, function() {
				jQuery( "#wps-product-quick-creation-form-attributes" ).removeClass( "wps-bloc-loading" );
			});
		});

		/**	Trigger event on form input for pressed key, that should not be the "enter" key	*/
		jQuery( "#wps_product_quick_add_form input, #wps_product_quick_add_form select" ).on( "keypress", function( event ) {
			var code = event.keyCode || event.which;
			  if ( code == 13) {
				  	event.preventDefault();
			    	return false;
			  }
		} );

		/**	Trigger event on new product creation form */
		var options = {
			beforeSubmit: function(formData, jqForm, options) {
				jQuery( "#wps-product-quick-creation-button" ).addClass( "wps-bton-loading" );
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
				if ( responseText[ 'status' ] ) {
					/**	At last remove the loading class	*/
					jQuery( "#wps_cart_container" ).addClass( "wps-bloc-loading" );
					wps_pos_add_simple_product_to_cart( responseText[ 'pid' ], jQuery( $form ).data( 'nonce' ) );
					if ( jQuery( ".wps-pos-product-letter-choice-" + responseText[ 'letter' ].toUpperCase() ).hasClass( "wps-bton-third-rounded" ) ) {
						jQuery( ".wps-pos-product-letter-choice-" + responseText[ 'letter' ].toUpperCase() ).click();
					}
					jQuery( "#TB_closeWindowButton" ).click();
				}

				jQuery( "#wps-product-quick-creation-button" ).removeClass( "wps-bton-loading" );
			},
			dataType: "json",
		};
		jQuery( "#wps_product_quick_add_form" ).ajaxForm( options );

	});
</script>
