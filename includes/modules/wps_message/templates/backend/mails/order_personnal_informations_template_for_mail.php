<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<table style="width:600px; border : 1px solid #A4A4A4; clear : both;">
	<tr>
		<td width="600" valign="middle" align="left" bgcolor="#1D7DC1" height="40" style="color : #FFFFFF">
			<?php _e( 'Customer personnal informations', 'wpshop'); ?>
		</td>
	</tr>
<tr>
	<td width="600">
		<ul>
			<?php foreach( $attributes_sets as $attributes_set ) :
					$query = $wpdb->prepare( 'SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_GROUP. ' WHERE attribute_set_id = %d AND status = %s', $attributes_set->id, 'valid');
					$attributes_groups = $wpdb->get_results( $query );
					if( !empty($attributes_groups) ) :
						foreach( $attributes_groups as $attribute_group ) :
							$query = $wpdb->prepare( 'SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d AND status = %s ORDER BY position', $customer_entity, $attributes_set->id, $attribute_group->id, 'valid' );
							$attribute_ids = $wpdb->get_results( $query );
							if( !empty($attribute_ids) ) :
								foreach( $attribute_ids as $attribute_id ) :
									$query = $wpdb->prepare( 'SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE. ' WHERE id = %d AND status = %s', $attribute_id->attribute_id, 'valid' );
									$attribute_def = $wpdb->get_row( $query );
									if( !empty($attribute_def) ) :
										$user_data = get_userdata($user_id);
										$user_attribute_meta = get_user_meta( $user_id, $attribute_def->code, true );

										if( in_array( $attribute_def->frontend_input, array( 'checkbox', 'radio', 'select') ) ) :
											$query = $wpdb->prepare( 'SELECT label FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $user_attribute_meta);
											$value = $wpdb->get_var( $query );
										else :
											$value = $user_attribute_meta;
										endif;

										/**	Specific case for datetime*/
										switch ( strtolower( $attribute_def->data_type ) ) :
											case 'datetime':
													$value = mysql2date( ( 10 == strlen( $value ) || ( 10 < strlen( $value ) && "00:00:00" == substr( $value, -8 ) ) ? get_option( 'date_format' ) : get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ), $value, true );
												break;
										endswitch;

										/**	Specific case for user email	*/
										if ( 'user_email' == $attribute_def->code ) :
											$value = $user_data->user_email;
										endif;

										if( $attribute_def->code != 'user_pass' ) :
										?>
											<li><strong><?php echo $attribute_def->frontend_label; ?> : </strong><?php echo $value; ?></li>
										<?php
										endif;
									endif;
								endforeach;
							endif;
						endforeach;
					endif;
			endforeach; ?>
		</ul>
	</td>
</tr>
</table>
<div style="clear:both; width : 100%; height : 15px; display : block;"></div>
