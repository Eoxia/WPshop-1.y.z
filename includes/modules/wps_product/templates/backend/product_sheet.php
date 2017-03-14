<?php if ( !defined( 'ABSPATH' ) ) exit;
 global $wpdb; ?>
<page backtop="14mm" backbottom="14mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
	<page_header>
		<table class="page_header">
			<tr><td style="width : 100%"><?php echo $product->post_title; ?></td></tr>
		</table>
	</page_header>
	<table style="width:100%">
		<tr>
			<td class="picture_container" style="width : 30%"><?php echo get_the_post_thumbnail($product_id, 'medium' ); ?></td>
			<td style="width : 60%;" valign="top" align="justify">
				<?php if( !empty($shop_type) && $shop_type == 'sale' && !empty($product_price_data) ) : ?>
				<p><u><?php _e( 'Product price', 'wpshop'); ?></u> :</p>
				<p>
					<?php if( !empty($product_price_data['PRICE_FROM']) ) : ?>
						<?php _e( 'Price from', 'wpshop'); ?>
					<?php endif; ?>
					<?php echo utf8_decode( $product_price_data['PRODUCT_PRICE'] ); ?>
				</p>
				<?php endif; ?>
				<p><u><?php _e( 'Product description', 'wpshop'); ?> :</u></p>
				<p><?php echo nl2br( utf8_decode($product->post_content) ); ?></p>
			</td>
		</tr>
	</table>

	<?php if( !empty($product_atts_def) && !empty($product_atts_def[$product_id]) ) : ?>
		<?php
		$i = 1;
		foreach( $product_atts_def[$product_id] as $group_id => $group_data ) :
		?>
		<table class="content">
			<tr style="padding-top : 10px ;margin-top : 10px; border : 1px dotted #CCCCCC;">
				<td style="width : 100%;">
					<h2><?php echo $i; ?>. <?php echo utf8_decode( $group_id ); ?></h2>
					<ul>
						<?php
						if( !empty($group_data) && !empty($group_data['attributes']) ) :
							foreach( $group_data['attributes'] as $attribute ) :
								if( !empty($attribute) && !empty($attribute['data_type']) && $attribute['data_type'] == 'integer' && !empty($attribute['value']) ) :
									if( $attribute['backend_input'] == 'multiple-select' && is_array($attribute['value']) ) :
										$j = 0;
										foreach( $attribute['value'] as $value ) :
											$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.  ' WHERE id = %d', $value );
											$attribute['value'][$j] = $wpdb->get_var( $query );
											$j++;
										endforeach;
									else :
										$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.  ' WHERE id = %d', $attribute['value'] );
										$attribute['value'] = $wpdb->get_var( $query );
									endif;
								elseif( !empty($attribute) && !empty($attribute['data_type']) && $attribute['data_type'] == 'decimal' ) :
									$attribute['value'] = wpshop_tools::formate_number( $attribute['value'] );
								endif;
								?>
								<li><?php  _e( $attribute['frontend_label'], 'wpshop' ); ?> :
								<?php
								if( $attribute['backend_input'] == 'multiple-select' && is_array($attribute['value']) ) :
									foreach( $attribute['value'] as $v ) :
										echo __( $v, 'wpshop' ).' '.( (!empty($attribute['unit']) ) ? '('.$attribute['unit'].')' : '' ).', ';
									endforeach;
								else :
									echo __( $attribute['value'], 'wpshop' ).' '.__( $attribute['unit'], 'wpshop' );
								endif;
								?></li>
							<?php
							endforeach;
						endif;
						?>
					</ul>
				</td>
			</tr>
		</table>
		<?php
		$i++;
		endforeach;
		?>

	<?php endif; ?>


</page>
