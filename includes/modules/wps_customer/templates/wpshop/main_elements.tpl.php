<?php if ( !defined( 'ABSPATH' ) ) exit;
$tpl_element = array();

/**
 * ACCOUNT FORM ELEMENT
*/
ob_start();
?>
<div class="wps-form-group">
	<label for="{WPSHOP_ACCOUNT_FORM_ELEMENT_ID}">{WPSHOP_ACCOUNT_FORM_ELEMENT_LABEL}</label> <span class="wps_required">{WPSHOP_ACCOUNT_FORM_REQUIRED_ELEMENT}</span>
	<div class="wps-form">{WPSHOP_ACCOUNT_FORM_ELEMENT_INPUT}</div>
</div>
<?php
$tpl_element['wpshop']['default']['wps_account_form_element'] = ob_get_contents();
ob_end_clean();




