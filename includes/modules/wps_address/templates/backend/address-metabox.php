<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-address-list-container" data-nonce="<?php echo wp_create_nonce( 'display_addresses_list' ); ?>" >
	<?php require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "addresses" ) ); ?>
</div>
<a href="#" data-nonce="<?php echo wp_create_nonce( 'display_address_adding_form' ); ?>" class="alignright wps-address-icon-black wps-address-icon-add" id="wps-address-add-for-<?php echo $post->ID; ?>" ></a>
<div class="clear" ></div>
