<?php if ( !empty($addresses) ) : ?>
<ul class="wps-addresses-list" >
	<?php
		foreach ( $addresses as $address_type => $addresses_list_by_type ) :
			foreach ( $addresses_list_by_type as $address_id => $address ) :
	?>
	<li id="wps-address-item-<?php echo $address_id ; ?>" >
	<?php require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "address" ) ); ?>
	</li>
	<?php
			endforeach;
		endforeach;
	?>
</ul>
<?php else: ?>
<span class="wps-addresses-list wps-no-result" ><?php _e( 'No addresses founded', 'wpeo_geoloc' ); ?></span>
<?php endif; ?>