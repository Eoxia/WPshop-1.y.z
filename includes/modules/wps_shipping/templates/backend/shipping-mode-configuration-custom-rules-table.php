<?php if ( !defined( 'ABSPATH' ) ) exit;
$wps_shipping = new wps_shipping();
$shipping_rules = $wps_shipping->shipping_fees_string_2_array( stripslashes($fees_data) );

if( !empty($shipping_rules) ) : ?>

	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e('Country', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e('Weight', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e('Price', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e('Delete', 'wpshop'); ?></div>
	</div>

	<?php foreach ( $shipping_rules as $shipping_rule ) :
			$country_name = '';
			$code_country = explode('-', $shipping_rule['destination']);
			$code_country = $code_country[0];
			foreach ( $country_list as $key=>$country ) :
				if (  $key == $code_country ) :
					$country_name = $country;
				endif;
			endforeach;
			if ( !empty($shipping_rule['fees']) ) :
				foreach( $shipping_rule['fees'] as $p => $fee ) : ?>
				<div class="wps-table-content wps-table-row" data-element="<?php echo $shipping_rule['destination']; ?>|<?php echo $p; ?>|<?php echo $fee; ?>">
					<div class="wps-table-cell"><?php echo $country_name; ?> (<?php echo $shipping_rule['destination']; ?>)</div>
					<div class="wps-table-cell"><?php echo ($unity == 'kg') ? $p / 1000 : $p; ?> <?php _e( $unity, 'wpshop')?></div>
					<div class="wps-table-cell"><?php echo $fee; ?> <?php echo wpshop_tools::wpshop_get_currency(); ?></div>
					<div class="wps-table-cell"><a href="#" id="<?php echo $shipping_rule['destination']; ?>|<?php echo $p; ?>|<?php echo $shipping_mode_id; ?>" data-nonce="<?php echo wp_create_nonce( 'wpshop_ajax_delete_shipping_rule' ); ?>" class="delete_rule" title="<?php echo $shipping_mode_id; ?>"><i class="wps-icon-trash"></i></a></div>
				</div>
	<?php
				endforeach;
			endif;
	endforeach; ?>

<?php endif; ?>
