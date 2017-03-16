<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-alert-info"><span class="dashicons dashicons-lightbulb"></span><?php printf( __( 'Need help to configure your shipping rules ? <a href="%s" target="_blank">Opt for assistance</a>', 'wpshop'), 'https://shop.eoxia.com/ecommerce/assistance-personnalisee-wordpress/' ); ?></div>

<div class="wps-boxed">
	<span class="wps-h2"><?php _e('General configurations', 'wpshop')?></span>
	<div>
		<?php
			$wps_shipping_mode_config_extra_params = apply_filters('wps_shipping_mode_config_extra_params_'.$k, $k );
			echo ($wps_shipping_mode_config_extra_params != $k) ? $wps_shipping_mode_config_extra_params : '';
		?>
	</div>
	<div class="wps-form-group">
		<label for="wps_shipping_mode_<?php echo $k; ?>_explanation"><?php _e('Explanation', 'wpshop'); ?> :</label>
		<div class="wps-form"><textarea id="wps_shipping_mode_<?php echo $k; ?>_explanation" name="wps_shipping_mode[modes][<?php echo $k; ?>][explanation]"><?php echo ( !empty($shipping_mode['explanation']) ) ? $shipping_mode['explanation'] : ''; ?></textarea></div>
	</div>

	<span class="wps-h5"><?php _e('Free shipping cost configuration', 'wpshop')?></span>
	<div class="wps-row wps-gridwrapper2-padded">
		<!-- Free shipping for all orders -->
		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" name="wps_shipping_mode[modes][<?php echo $k; ?>][free_shipping]" id="<?php echo $k; ?>_free_shipping" <?php echo ( !empty($shipping_mode['free_shipping']) ) ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $k; ?>_free_shipping"><?php _e('Activate free shipping for all orders', 'wpshop'); ?></label></div>
		</div>

		<!-- Free shipping for orders which amount is over an amount -->
		<div>
			<div class="wps-form-group">
				<div class="wps-form"><input type="checkbox" name="wps_shipping_mode[modes][<?php echo $k; ?>][activate_free_shipping_from]" id="<?php echo $k; ?>_free_shipping" class="activate_free_shipping_cost_from" <?php echo ( !empty($shipping_mode['activate_free_shipping_from']) ) ? 'checked="checked"' : ''; ?> /><label for="<?php echo $k; ?>_free_from"><?php _e('Activate free shipping for order over amount below', 'wpshop'); ?></label></div>
			</div>
			<div class="wps-form-group" id="<?php echo $k; ?>_activate_free_shipping">
				<label><?php _e('Free shipping cost for order over amount below', 'wpshop'); ?> ( <?php echo wpshop_tools::wpshop_get_currency(); ?> ) :</label>
				<div class="wps-form"><input type="text" name="wps_shipping_mode[modes][<?php echo $k; ?>][free_from]" id="<?php echo $k; ?>_free_from" value="<?php echo ( !empty($shipping_mode['free_from']) ) ? $shipping_mode['free_from'] : ''; ?>" class="wps_little_input" /></div>
			</div>
		</div>
	</div>

	<!--  Min & Max Shipping cost Configuration -->
	<span class="wps-h5"><?php _e('Minimum and maximum limit shipping cost configuration', 'wpshop')?></span>
	<div class="wps-row">
		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" class="wps_shipping_mode_configuation_min_max" name="wps_shipping_mode[modes][<?php echo $k; ?>][min_max][activate]" id="<?php echo $k; ?>_min_max_activate" <?php echo (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['activate']) ) ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $k; ?>_min_max_activate"><?php _e('Activate the min. and max. shipping cost', 'wpshop'); ?></label></div>
		</div>
	</div>

	<div class="wps-row">
		<div class="wps-row wps-gridwrapper2-padded" id="<?php echo $k; ?>_min_max_shipping_rules_configuration">
			<div class="wps-form-group">
				<label><?php _e('Minimum', 'wpshop'); ?> ( <?php echo wpshop_tools::wpshop_get_currency(); ?> ) :</label>
				<div class="wps-form"><input type="text" name="wps_shipping_mode[modes][<?php echo $k; ?>][min_max][min]" value="<?php echo (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['min']) ) ? $shipping_mode['min_max']['min'] : ''; ?>" /></div>
			</div>

			<div class="wps-form-group">
				<label><?php _e('Maximum', 'wpshop'); ?> ( <?php echo wpshop_tools::wpshop_get_currency(); ?> ) :</label>
				<div class="wps-form"><input type="text" name="wps_shipping_mode[modes][<?php echo $k; ?>][min_max][max]"  value="<?php echo (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['max']) ) ? $shipping_mode['min_max']['max'] : ''; ?>" /></div>
			</div>
		</div>
	</div>

</div>

<!--  Shipping zone limitation configuration -->
<div class="wps-boxed">
	<span class="wps-h2"><?php _e('Area Shipping Limitation', 'wpshop')?></span>

	<span class="wps-h5">1. <?php _e('Countries Shipping Limitation', 'wpshop')?></span>
	<div class="wps-row">
		<label><?php _e('Choose all countries where you want to ship orders. Let empty you don\'t want limitations', 'wpshop'); ?></label>
		<div class="wps-form">
			<select name="wps_shipping_mode[modes][<?php echo $k; ?>][limit_destination][country][]" class="chosen_select" multiple data-placeholder="<?php __('Choose a Country', 'wpshop' ); ?>" style="width : 100%">
				<?php if( !empty($countries) ) :
				foreach( $countries as $key => $country) :	?>
				<option value="<?php echo $key; ?>"<?php echo ( (!empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['country']) && in_array($key, $shipping_mode['limit_destination']['country']) ) ? 'selected="selected"' : '' ); ?>><?php echo $country; ?></option>
			<?php endforeach; endif; ?>
			</select>
		</div>
	</div>

	<span class="wps-h5">2. <?php _e('Postcode Shipping Limitation', 'wpshop')?></span>
	<div class="wps-row">
		<label><?php _e('Write all allowed postcode, separate it by a comma. Let empty if you don\'t want limitations.', 'wpshop'); ?></label>
		<div class="wps-form">
			<textarea name="wps_shipping_mode[modes][<?php echo $k; ?>][limit_destination][postcode]"><?php echo ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['postcode']) ) ? $shipping_mode['limit_destination']['postcode'] : ''; ?></textarea>
		</div>
	</div>

	<span class="wps-h5">3. <?php _e('Department Shipping Limitation', 'wpshop')?></span>
	<div class="wps-row">
		<label><?php _e('Write all allowed department, separate it by a comma. Let empty if you don\'t want limitations.', 'wpshop'); ?></label>
		<div class="wps-form">
			<textarea name="wps_shipping_mode[modes][<?php echo $k; ?>][limit_destination][department]"><?php echo ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['department']) ) ? $shipping_mode['limit_destination']['department'] : ''; ?></textarea>
		</div>
	</div>

</div>

<div class="wps-boxed">
	<span class="wps-h2"><?php _e('Custom shipping rules', 'wpshop'); ?></span>
	<textarea id="<?php echo $k; ?>_wpshop_custom_shipping" name="wps_shipping_mode[modes][<?php echo $k; ?>][custom_shipping_rules][fees]" class="wpshopHide"><?php echo $fees_data; ?></textarea>

	<div class="wps-row wps-gridwrapper3-padded">
		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" name="wps_shipping_mode[modes][<?php echo $k; ?>][custom_shipping_rules][active]" id="<?php echo $k; ?>_custom_shipping_active" <?php echo ( !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['active']) )  ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $k; ?>_custom_shipping_active"><?php _e('Activate custom shipping fees','wpshop'); ?></label></div>
		</div>

		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" class="active_postcode_custom_shipping" id="<?php echo $k; ?>_custom_shipping_active_cp" name="wps_shipping_mode[modes][<?php echo $k; ?>][custom_shipping_rules][active_cp]" <?php echo ( !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['active_cp']) )  ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $k; ?>_custom_shipping_active_cp"> <?php _e('Activate custom shipping fees by postcode', 'wpshop'); ?></label></div>
		</div>

		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" class="active_department_custom_shipping" id="<?php echo $k; ?>_custom_shipping_active_department" name="wps_shipping_mode[modes][<?php echo $k; ?>][custom_shipping_rules][active_department]" <?php echo ( !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['active_department']) )  ? 'checked="checked"' : ''; ?> /> <label for="<?php echo $k; ?>_custom_shipping_active_department"> <?php _e('Activate custom shipping fees by department', 'wpshop'); ?></label></div>
		</div>
	</div>

	<span class="wps-h5"><?php _e('Configuration', 'wpshop'); ?></span>
	<div class="wps-row wps-gridwrapper3-padded">

		<div class="wps-form-group">
			<label for="<?php echo $k; ?>_country_list"><?php _e('Choose a country', 'wpshop'); ?> : </label>
			<div class="wps-form">
				<select id="<?php echo $k; ?>_country_list" name="country_list" class="shipping_mode_config_input">
					<?php if( !empty($countries) ) :
						foreach( $countries as $key => $country) :	?>
						<option value="<?php echo $key; ?>"><?php echo $country; ?></option>
					<?php endforeach; endif; ?>
				</select>
			</div>
		</div>

		<div class="wps-form-group postcode_rule" style="display:none;" >
			<label for="<?php echo $k; ?>_postcode_rule" class="postcode_rule"><?php _e('Postcode', 'wpshop'); ?> : </label>
			<div class="wps-form">
				<input type="text" name="postcode_rule" id="<?php echo $k; ?>_postcode_rule" class="shipping_rules_configuration_input postcode_rule"/>
			</div>
		</div>

		<div class="wps-form-group department_rule" style="display:none;" >
			<label for="<?php echo $k; ?>_department_rule" class="department_rule"><?php _e('Department', 'wpshop'); ?> : </label>
			<div class="wps-form">
				<input type="text" name="department_rule" id="<?php echo $k; ?>_department_rule" class="shipping_rules_configuration_input department_rule"/>
			</div>
		</div>

	</div>

	<div class="wps-row wps-gridwrapper2-padded">
		<div class="wps-form-group">
			<label for="<?php echo $k; ?>_weight_rule"><?php _e('Weight', 'wpshop'); ?> <strong>(<?php _e($unity, 'wpshop'); ?>)</strong> : </label>
			<div class="wps-form"><input type="text" name="weight_rule" id="<?php echo $k; ?>_weight_rule" class="shipping_rules_configuration_input"/></div>
		</div>

		<div class="wps-form-group">
			<label for="<?php echo $k; ?>_shipping_price"><?php _e('Price', 'wpshop'); ?> <strong>(<?php echo wpshop_tools::wpshop_get_currency(); ?> <?php echo WPSHOP_PRODUCT_PRICE_PILOT; ?>)</strong> : </label>
			<div class="wps-form"><input type="text" name="shipping_price" id="<?php echo $k; ?>_shipping_price" class="shipping_rules_configuration_input"/></div>
		</div>
	</div>

	<div class="wps-row">
		<input type="checkbox" id="<?php echo $k; ?>_main_rule" name="main_rule" value="OTHERS"/> <label for="<?php echo $k; ?>_main_rule" class="global_rule_checkbox_indic"><?php _e('Apply a common rule to all others countries','wpshop'); ?></label>
	</div>

	<div class="wps-form-group">
		<br/>
		<center><a id="<?php echo $k; ?>_save_rule" role="button" data-nonce="<?php echo wp_create_nonce( 'wpshop_ajax_save_shipping_rule' ); ?>" class="save_rules_button wps-bton-third-mini-rounded"><?php _e('Add the rule', 'wpshop'); ?></a></center>
		<br/>
	</div>

	<div class="wps-row wps-table wps-bloc-loader" id="<?php echo $k; ?>_shipping_rules_container" data-nonce="<?php echo wp_create_nonce( 'wpshop_ajax_display_shipping_rules' ); ?>">
		<?php echo $this->generate_shipping_rules_table( $fees_data, $k ); ?>
	</div>
</div>
<center><a role="button" class="wps-bton-first-rounded wps_save_payment_mode_configuration"><?php _e('Save', 'wpshop'); ?></a><br/><br/><br/></center>
