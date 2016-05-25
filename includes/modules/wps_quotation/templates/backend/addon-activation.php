<?php if ( !defined( 'ABSPATH' ) ) exit;?>
<hr/>
<div class="wps-quotation-addon-state-message-container" ></div>
<div class="wps-quotation-addon-state-container" >
	<?php printf( __( 'Extend WPShop basic quotation functionnality with %1$sour quotation extra module%2$s', 'wpshop' ), '<a target="shop_eoxia_quotaion_addon" href="' . $quotation_module_def[ 'PluginURI' ] . '" >', '</a>' ); ?><br/>
	<?php _e( 'Already have bought the module? Simply type your code below to activate module', 'wpshop' ); ?>
	<input type="text" name="wps-quotation-check-code-value" id="wps-quotation-check-code-value" value="" placeholder="<?php _e( 'Type your code here', 'wpshop' ); ?>" /><button class="wps-bton-mini-third-rounded wps-bton-loading" data-nonce="<?php echo wp_create_nonce( 'check_code_for_activation' ); ?>" id="wps-quotation-check-code-button" ><?php _e( 'Check code', 'wpshop' ); ?></button>
</div>
