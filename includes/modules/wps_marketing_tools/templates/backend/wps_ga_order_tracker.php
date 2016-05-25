<?php if ( !defined( 'ABSPATH' ) ) exit;
 $company_infos = get_option( 'wpshop_company_info' ); ?>
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push( ['_setAccount', '<?php echo $ga_account_id; ?>'] );
	_gaq.push( ['_trackPageview'] );
	_gaq.push(['_addTrans',
	       	'<?php echo $order_id; ?>',
	       	'<?php echo ( !empty($company_infos) && !empty($company_infos['company_name']) ? $company_infos['company_name'] : ''  ) ; ?>',
	       	'<?php echo number_format($order_meta['order_grand_total'], 2, '.', ''); ?>', '<?php echo number_format($total_tva, 2, '.', ''); ?>',
	       	'<?php echo ( ( !empty($order_meta['order_shipping_cost']) ) ? $order_meta['order_shipping_cost'] : 0); ?>',
	       	'<?php echo ( ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['city']) ) ? $order_info['billing']['address']['city'] : ''); ?>',
	       	'<?php echo ( ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['state']) ) ? $order_info['billing']['address']['state'] : ''); ?>',
	       	'<?php echo ( ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['country']) ) ? $order_info['billing']['address']['country'] : ''); ?>']);

	<?php if ( !empty( $order_meta['order_items'] ) && is_array( $order_meta['order_items'] ) ) : ?>
		<?php foreach( $order_meta['order_items'] as $item ) :
				 /** Variation **/
				$variation = '';
				$variation_definition = get_post_meta( $item['item_id'], '_wpshop_variations_attribute_def', true);
				if ( !empty($variation_definition) && is_array($variation_definition) ) :
					foreach( $variation_definition as $k => $value ) :
						$attribute_def = wpshop_attributes::getElement( $k, '"valid"', 'code' );
						if ( !empty($attribute_def) ) :
							$variation .= $attribute_def->frontend_label.' : ';
							if ( $attribute_def->data_type_to_use == 'custom' ) :
								$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $value );
								$variation .= $wpdb->get_var( $query );

							else :
								$variation .= get_the_title( $value );
							endif;
						endif;
					endforeach;
				endif;
				$item_meta = get_post_meta($item['item_id'], '_wpshop_product_metadata', true);
		?>
		_gaq.push(['_addItem',
		   		'<?php echo  $order_id; ?>',
		   		'<?php echo ( (!empty($item_meta) && !empty($item_meta['barcode']) ) ? $item_meta['barcode'] : ''); ?>',
		   		'<?php echo $item['item_name']; ?>',
		   		'<?php echo $variation; ?>',
		   		'<?php echo $item['item_pu_ttc']; ?>',
		   		'<?php echo $item['item_qty']; ?>'
		   		]
   		);
		<?php endforeach; ?>
	<?php endif; ?>

	_gaq.push( ['_trackTrans'] );
	(function() {
		var ga = document.createElement('script');
		ga.type = 'text/javascript';
		ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(ga, s);})
	();
</script>
