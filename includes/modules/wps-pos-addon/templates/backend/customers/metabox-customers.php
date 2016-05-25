<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-pos-element-metabox-selection wps-pos-element-metabox-selection-customer" ><input type="text" value="" placeholder="<?php _e( 'Start typing here for customer search', 'wps-pos-i18n' ); ?>" data-nonce="<?php echo wp_create_nonce( 'ajax_pos_customer_search' ); ?>" name="wps-pos-customer-to-choose" class="wps-pos-customer-search" /></div>
<div class="wps-pos-element-listing-container wps-pos-customer-listing wps-bloc-loader" ><?php echo $this->display_customer_list( 'A' ); ?></div>
<div class="wps-pos-alphabet-container" ><?php echo wps_pos_tools::alphabet_letters( 'customer', $available_letters, 'A' ); ?></div>
<script type="text/javascript" >
	jQuery( document ).ready( function(){
		jQuery( "#wps-pos-customer-list-choice" ).chosen();
	});
</script>
