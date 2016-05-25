<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<table style="width:290px; border : 1px solid #A4A4A4; float : left; margin-right : 5px; margin-left : 5px; margin-bottom:20px;">
	<tr bgcolor="#1D7DC1" height="50" valign="middle" align="center" style="color : #FFFFFF">
		<td>
			<?php echo $address_type_title; ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $civility; ?> <?php echo $customer_last_name; ?> <?php echo $customer_firtsname; ?><br/>
			<?php echo $customer_company; ?><br/>
			<?php echo $customer_address; ?><br/>
			<?php echo $customer_zip_code; ?> <?php echo $customer_city; ?><br/>
			<?php echo $customer_state; ?><br/>
			<?php echo $customer_country; ?><br/>
			<?php echo $customer_phone; ?>
		</td>
	</tr>
</table>
