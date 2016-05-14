<form action="<?php echo admin_url( 'admin-ajax.php' ) ?>" method="POST" id="wps-address-type-chooser-form" >
	<input type="hidden" name="action" value="wps-address-edition-form-load" />
	<?php wp_nonce_field( 'wps_address_edition_form_load' ); ?>
	<input type="hidden" name="element_id" value="0" />
	<input type="hidden" name="post_id" value="<?php echo $post_ID; ?>" />
	<select name="wpeogeo-address-type-chosen-for-creation" >
	<?php foreach ( $attached_addresses as $address_type_id ) : ?>
		<?php
			$query = $wpdb->prepare("SELECT name FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE id = %d", $address_type_id);
		?>
		<option value="<?php echo $address_type_id; ?>" ><?php echo $wpdb->get_var( $query ); ?></option>
	<?php endforeach; ?>
	</select>
	<button ><?php _e( 'Continue', 'wpeo_geoloc' ); ?></button>
</form>
<script type="text/javascript" >
jq_wpeogeoloc( document ).ready( function(){
		jQuery( "#wps-address-type-chooser-form" ).ajaxForm({
			beforeSubmit: function( formData, jqForm, options ){
				jqForm.closest( "div.inside" ).children( ".wps-address-list-container" ).append( '<div id="wps-overlay" class="wps-overlay-background" ></div><div id="wps-overlay-load" style="top: 45%;" ><img src="' + thickboxL10n.loadingAnimation + '" /></div>' ).css( "height", "100px" );
			},
			success: function( responseText, statusText, xhr, $form ){
				$form.closest( ".wps-address-list-container" ).css( "height", "" );
				$form.closest( "div.wps-address-list-container" ).html( responseText );
			},
		});
	});
</script>
