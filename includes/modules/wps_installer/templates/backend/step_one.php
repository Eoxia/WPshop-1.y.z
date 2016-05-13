<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<h4><?php _e( 'Those informations will be used into invoice and to display your contact informations', 'wpshop'); ?></h4>
<?php
	do_settings_sections( 'wpshop_company_info' );
?>

<table class="form-table" >
	<tr>
		<th><?php _e( 'The logo for emails and invoices', 'wpshop'); ?></th>
		<td><?php wpshop_general_options::wpshop_logo_field(); ?></td>
	</tr>
</table>
