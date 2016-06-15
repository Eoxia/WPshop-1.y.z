<?php if ( !defined( 'ABSPATH' ) ) exit;
$tpl_element = array();

/**
 * WPSHOP ADDRESS FIELD
 */
ob_start();
?>
<div class="wps-form-group field-{WPSHOP_CUSTOMER_FORM_INPUT_NAME}">
	<label {WPSHOP_CUSTOMER_FORM_INPUT_LABEL_OPTIONS}>{WPSHOP_CUSTOMER_FORM_INPUT_LABEL}</label>
	<div class="wps-form">
	{WPSHOP_CUSTOMER_FORM_INPUT_FIELD}
	</div>
</div>
<?php
$tpl_element['wpshop']['default']['wps_address_field'] = ob_get_contents();
ob_end_clean();