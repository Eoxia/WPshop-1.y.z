<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<table class="table table-striped  wps-pos-element-table" >
	<tr>
		<th><?php _e('Customer infos', 'wps-pos-i18n'); ?></th>
		<th><?php _e('Action', 'wps-pos-i18n'); ?></th>
	</tr>

	<?php foreach ( $customer_list as $customer ) : ?>
		<?php require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/customers', 'customer' ) ); ?>
	<?php endforeach; ?>

	<?php if ( empty( $customer_list ) || ( 1 >= count( $customer_list ) ) ) : ?>
		<?php require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/customers', 'customer', 'not_found' ) ); ?>
	<?php endif; ?>
</table>