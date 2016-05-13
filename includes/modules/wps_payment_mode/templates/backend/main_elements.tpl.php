<?php if ( !defined( 'ABSPATH' ) ) exit;
$tpl_element = array();
/**
 * WPS PAYMENT MODE INTERFACE
 */
ob_start();
?>
<ul id="wps_payment_mode_list_container">
{WPSHOP_INTERFACES}
</ul>
<?php
$tpl_element['admin']['default']['wps_payment_mode_interface'] = ob_get_contents();
ob_end_clean();


/**
* WPS SHIPPING MODE EACH INTERFACE
*/
ob_start();
?>
<li class="wps_shipping_mode_container" id="container_{WPSHOP_PAYMENT_MODE_ID}">

<div class="shipping_mode_titre">
<label for="wps_payment_mode_configuration_{WPSHOP_PAYMENT_MODE_ID}_name"><?php _e('Name', 'wpshop'); ?></label> : <input type="text" name="wps_payment_mode[mode][{WPSHOP_PAYMENT_MODE_ID}][name]" id="wps_payment_mode_configuration_{WPSHOP_PAYMENT_MODE_ID}_name" value="{WPSHOP_PAYMENT_MODE_NAME}" /><br/>
<label for="{WPSHOP_PAYMENT_MODE_ID}_logo"><?php _e('Logo', 'wpshop'); ?></label> :<input type="file" id="{WPSHOP_PAYMENT_MODE_ID}_logo" name="{WPSHOP_PAYMENT_MODE_ID}_logo" /><input type="hidden" name="wps_payment_mode[mode][{WPSHOP_PAYMENT_MODE_ID}][logo]" value="{WPSHOP_PAYMENT_MODE_LOGO_POST_ID}" /><br/>
{WPSHOP_PAYMENT_MODE_THUMBNAIL}
</div>


<div class="shipping_mode_little_configuration">
<label for="activate_shipping_mode_{WPSHOP_PAYMENT_MODE_ID}"><?php _e('Activate', 'wpshop')?></label> <input type="checkbox" name="wps_payment_mode[mode][{WPSHOP_PAYMENT_MODE_ID}][active]" class="shipping_mode_is_active" id="activate_shipping_mode_{WPSHOP_PAYMENT_MODE_ID}" {WPSHOP_PAYMENT_MODE_ACTIVE} />
<br/>
<label for="{WPSHOP_PAYMENT_MODE_ID}_default"><?php _e('Default payment mode', 'wpshop'); ?></label> <input type="radio" name="wps_payment_mode[default_choice]" value="{WPSHOP_PAYMENT_MODE_ID}" id="{WPSHOP_PAYMENT_MODE_ID}_default" {WPSHOP_DEFAULT_PAYMENT_MODE_ACTIVE} />
<br/>
	<div id="{WPSHOP_PAYMENT_MODE_ID}_configuration_interface" style="display:none;" >
		 <div class="wps-boxed">
			 <div class="wps-form-group">
				 <label><?php _e('Displayed description on front', 'wpshop'); ?></label>
				 <div class="wps-form">
				 	<textarea name="wps_payment_mode[mode][{WPSHOP_PAYMENT_MODE_ID}][description]" style="width : 100%">{WPSHOP_PAYMENT_DESCRIPTION}</textarea>
				 </div>
			 </div>
		 </div>
	     {WPSHOP_PAYMENT_MODE_CONFIGURATION_INTERFACE}
	     <!-- <div><center><a href="#" role="button" class="wps-bton-first-rounded wps_save_payment_mode_configuration"><?php _e( 'Save', 'wpshop'); ?></a></center><br/></div>  -->
	</div>
	<a href="#TB_inline?width=600&amp;height=400&amp;inlineId={WPSHOP_PAYMENT_MODE_ID}_configuration_interface" class="thickbox button-secondary" title="<?php _e('Configure the payment mode', 'wpshop'); ?>" ><?php _e('Configure the payment mode', 'wpshop'); ?></a>
</div>

</li>
<?php
$tpl_element['admin']['default']['wps_payment_mode_each_interface'] = ob_get_contents();
ob_end_clean();