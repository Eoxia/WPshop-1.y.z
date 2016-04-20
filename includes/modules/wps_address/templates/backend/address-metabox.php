<div class="wps-address-list-container" >
	<?php require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "addresses" ) ); ?>
</div>
<a href="#" class="alignright wps-address-icon-black wps-address-icon-add" id="wps-address-add-for-<?php echo $post->ID; ?>" ></a>
<div class="clear" ></div>