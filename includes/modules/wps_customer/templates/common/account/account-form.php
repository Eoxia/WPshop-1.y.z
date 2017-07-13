<?php
/**
 * Affichage des champs du formulaire de modification d'un compte client
 *
 * @package WPShop
 * @subpackage Customers
 *
 * @since 1.4.4.3
 * @version 1.4.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ( ! empty( $signup_form_section ) ) : ?>
	<?php foreach ( $signup_form_section as $signup_section_name => $signup_fields ) : ?>
	<span class="wps-h4"><?php echo esc_html( $signup_section_name, 'wpshop' ); ?></span>
	<div class="customer-account-detail-group" >
	<?php foreach ( $signup_fields as $signup_field ) : ?>
		<?php
		if ( isset( $signup_field->code ) && ( 'is_provider' === $signup_field->code ) ) :
				continue;
		endif;
		$query = $wpdb->prepare( 'SELECT value  FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.strtolower($signup_field->data_type). ' WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d ', $customer_entity_type_id, $signup_field->id, $cid );
		$value = $wpdb->get_var( $query );
		$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $signup_field, $value, array( 'from' => 'frontend', array( 'options' => array( 'original' => true, ), ) ) );
		?>
		<div class="wps-form-group">
			<label for="<?php echo esc_attr( $signup_field->code ); ?>"><?php  _e( stripslashes($signup_field->frontend_label), 'wpshop'); ?> <?php echo ( ( !empty($attribute_output_def['required']) && $attribute_output_def['required'] == 'yes' ) ? '<em>*</em>' : '' ); ?></label>
			<div id="<?php echo esc_attr( $signup_field->code ); ?>" class="wps-form"><?php
				echo ( $attribute_output_def['output'] ); // WPCS: XSS ok.
				echo ( $attribute_output_def['options'] ); // WPCS: XSS ok.
			?></div>
		</div>
		<?php
		/** Check confirmation field **/
		if ( 'yes' === $signup_field->_need_verification  ) :
			$signup_field->code = $signup_field->code . '2';
			$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $signup_field, $value, array( 'from' => 'frontend' ) );
		?>
		<div class="wps-form-group">
			<label for="<?php echo esc_attr( $signup_field->code ); ?>"><?php printf( __('Confirm %s', 'wpshop'), stripslashes( strtolower(__( $signup_field->frontend_label, 'wpshop')) ) ); ?> <?php echo ( ( !empty($attribute_output_def['required']) && $attribute_output_def['required'] == 'yes' ) ? '<em>*</em>' : '' ); ?></label>
			<div id="<?php echo esc_attr( $signup_field->code ); ?>" class="wps-form"><?php
				echo ( $attribute_output_def['output'] );
				echo ( $attribute_output_def['options'] );
			?></div>
		</div>
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>
<?php endif;
$content = '';
ob_start();
do_action( 'signup_extra_fields' );
$content = ob_get_clean();
if ( ! empty( $content ) ) {
	?>
	<div class="wps-form-group">
		<?php echo $content; ?>
	</div>
	<?php
}
