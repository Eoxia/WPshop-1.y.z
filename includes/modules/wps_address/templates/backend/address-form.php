<?php if ( !defined( 'ABSPATH' ) ) exit; if ( empty($address_id) ) : ?>
<div class="wps-address-item-header wps-address-creation-header"><?php _e( 'New address', 'wpeo_geoloc' ); ?></div>
<div class="wps-address-item-content">
<?php endif; ?>
	<form data-nonce="<?php echo wp_create_nonce( 'wps_address_display_an_address' ); ?>" action="<?php echo admin_url( "admin-ajax.php" ); ?>" method="POST" class="wps-address-form" >
		<input type="hidden" value="wps-address-save-address" name="action" />
		<input type="hidden" value="<?php echo $post_ID; ?>" name="post_ID" />
		<?php echo self::display_form_fields( $address_type_id, $address_id ); ?>
		<button ><?php _e( 'Save address', 'wpeo_geoloc' ); ?></button>
		<button data-nonce="<?php echo wp_create_nonce( 'wps_address_display_an_address' ); ?>" type="reset" ><?php _e( 'Cancel', 'wpeo_geoloc' ); ?></button>
	</form>
<?php if ( empty($address_id) ) : ?>
</div>
<?php endif; ?>

<script type="text/javascript" >
	jq_wpeogeoloc( document ).ready( function(){

		/**		*/
		jQuery( ".wps-address-form" ).ajaxForm({
			dataType: "json",
			beforeSubmit: function( formData, jqForm, options ){
				jqForm.closest( "div.wps-address-item-content" ).append( '<div id="wps-overlay" class="wps-overlay-background" ></div><div id="wps-overlay-load" ><img src="' + thickboxL10n.loadingAnimation + '" /></div>' );
			},
			success: function( responseText, statusText, xhr, $form ){
				if ( responseText[ 0 ] ) {
					if ( 0 != <?php echo $address_id; ?> ) {
						$form.closest( "div.wps-address-item-content" ).children( "#wps-overlay-load" ).html( '<div class="wps-alert wps-alert-success" ><?php _e( 'Address saved', 'wpeo_geoloc' ); ?></div>' );
						var data = {
							action: "wps-address-display-an-address",
							_wpnonce: $form.data( "nonce" ),
							address_id: $form.closest( "li" ).attr( "id" ).replace( "wps-address-item-", "" ),
						};
						setTimeout(function(){
							$form.closest( "li" ).load( ajaxurl, data );
						}, "1000");
					}
					else {
						$form.closest( "div.wps-address-item-content" ).children( "#wps-overlay-load" ).html( '<div class="wps-alert wps-alert-success" ><?php _e( 'Address saved', 'wpeo_geoloc' ); ?></div>' );
						wps_address_load_addresses_list( <?php echo $post_ID; ?> );
					}
	        	}
				else {
					$form.before( responseText[ 1 ] );
					$form.closest( "div.wps-address-item-content" ).children( "#wps-overlay" ).remove();
					$form.closest( "div.wps-address-item-content" ).children( "#wps-overlay-load" ).remove();
				}
				setTimeout( function(){
					jQuery( ".wpscrm-society-associated-element-list-container.wpscrm-society-associated-addresses legend a.wpscrm-button-icon-add" ).show();
				}, "1000");
	       	},
		});

		/**	Add listener when user click on form cancel button	*/
		jQuery( ".wps-address-form button[type=reset]" ).click(function( e ){
			e.preventDefault();
			if ( confirm( wps_address_convert_html_accent( "<?php _e( 'Are you sure you want to cancel?', 'wpeo_geoloc' ); ?>" ) ) ) {
				if ( 0 != <?php echo $address_id; ?> ) {
					jQuery( this ).closest( "div.wps-address-list-container" ).append( '<div id="wps-overlay" class="wps-overlay-background" ></div><div id="wps-overlay-load" ><img src="' + thickboxL10n.loadingAnimation + '" /></div>' );
					var data = {
						action: "wps-address-display-an-address",
						_wpnonce: jQuery( this ).data( "nonce" ),
						address_id: <?php echo $address_id; ?>,
					};
					jQuery( "#wps-address-item-<?php echo $address_id; ?>" ).load( ajaxurl, data, function() {
						jQuery( "#wps-overlay" ).remove();
						jQuery( "#wps-overlay-load" ).remove();
					} );
				}
				else {
					jQuery( this ).closest( "div.wps-address-list-container" ).append( '<div id="wps-overlay" class="wps-overlay-background" ></div><div id="wps-overlay-load" ><img src="' + thickboxL10n.loadingAnimation + '" /></div>' );
					wps_address_load_addresses_list( <?php echo $post_ID; ?> );
				}

				setTimeout( function(){
					jQuery( "a.wps-address-icon-add" ).show();
				}, "1000");
			}
		});

/*		jQuery( ".wps-form input.wpshop_product_attribute_address" ).css( "width", "85%");
		jQuery( ".wps-form input.wpshop_product_attribute_address" ).after( '<a class="wpscrm-button-icon-white wpscrm-button-icon-geoloc wpscrm-geolocalise-current-position-for-address" href="#"></a>' );
		jQuery( document ).on( "click", ".wpscrm-geolocalise-current-position-for-address", function ( e ){
			e.preventDefault();
	    	get_address_from_current_position( ".wps-form input.wpshop_product_attribute_address", ".wps-form input.wpshop_product_attribute_city", ".wps-form input.wpshop_product_attribute_postcode" );
		});*/
	});
</script>
