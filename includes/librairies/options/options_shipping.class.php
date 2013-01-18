<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Shipping options management
*
* Define the different method to manage the different shipping options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different shipping options
* @package wpshop
* @subpackage librairies
*/
class wpshop_shipping_options {
	/**
	*
	*/
	function declare_options(){
		add_settings_section('wpshop_shipping_rules', __('Shipping', 'wpshop'), array('wpshop_shipping_options', 'plugin_section_text'), 'wpshop_shipping_rules');
			register_setting('wpshop_options', 'wpshop_shipping_rules', array('wpshop_shipping_options', 'wpshop_options_validate_shipping_rules'));
			add_settings_field('wpshop_shipping_rule_by_min_max', __('Min-Max shipping fees', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_by_min_max_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			add_settings_field('wpshop_shipping_rule_free_from', __('Free shipping', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_free_from_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			add_settings_field('wpshop_shipping_rule_free_shipping', '', array('wpshop_shipping_options', 'wpshop_shipping_rule_free_shipping'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			// add_settings_field('wpshop_shipping_rule_free_shipping_from_date', '', array('wpshop_shipping_options', 'wpshop_shipping_rule_free_shipping_from_date'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			// add_settings_field('wpshop_shipping_rule_free_shipping_to_date', '', array('wpshop_shipping_options', 'wpshop_shipping_rule_free_shipping_to_date'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			//add_settings_field('wpshop_shipping_rule_by_weight', __('By weight', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_by_weight_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			//add_settings_field('wpshop_shipping_rule_by_percent', __('By percent', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_by_percent_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			//add_settings_field('wpshop_shipping_rule_by_nb_of_items', __('By number of items', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_by_nb_of_items_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');

		/* SHIPPING MODE */
		add_settings_section('wpshop_shipping_mode', __('Shipping mode', 'wpshop'), array('wpshop_shipping_options', 'plugin_section_text'), 'wpshop_shipping_mode');
		register_setting('wpshop_options', 'wpshop_custom_shipping', array('wpshop_shipping_options', 'wpshop_options_validate_shipping_fees'));
		add_settings_field('wpshop_custom_shipping', __('Custom shipping fees', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_fees_fields'), 'wpshop_shipping_mode', 'wpshop_shipping_mode');

		/* SHIPPING ADDRESS CHOICE */
		register_setting('wpshop_options', 'wpshop_shipping_address_choice', array('wpshop_shipping_options', 'wpshop_shipping_address_validator'));
		add_settings_field('wpshop_shipping_address_choice', __('Shipping address choice', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_address_field'), 'wpshop_shipping_mode', 'wpshop_shipping_mode');
	}

	// Common section description
	function plugin_section_text() {
		echo '';
	}

	function wpshop_shipping_fees_fields() {
		$fees = get_option('wpshop_custom_shipping', unserialize(WPSHOP_SHOP_CUSTOM_SHIPPING));
		$fees_data = $fees['fees'];
		$fees_active = $fees['active'];

		//echo wpshop_shipping::calculate_shipping_cost($dest='FR', $rule_code='weight', $att_value=101, $fees_data);
		//echo wpshop_shipping::calculate_shipping_cost($dest='FRs', $data=array('weight'=>501,'price'=>12.5), $fees_data);

		if(is_array($fees_data)) {
			$fees_data = wpshop_shipping::shipping_fees_array_2_string($fees_data);
		}
		echo '
		<input type="checkbox" name="custom_shipping_active" id="custom_shipping_active" '.($fees_active?'checked="checked"':null).' /> <label for="custom_shipping_active">'.__('Activate custom shipping fees','wpshop').'</label><a href="#" title="'.__('Custom shipping fees. Edit as you want but respect the syntax.','wpshop').'" class="wpshop_infobulle_marker">?</a><br />
		<div class="wpshop_shipping_method_parameter custom_shipping_active_content'.(!$fees_active?' wpshopHide':null).'" ><textarea id="wpshop_custom_shipping" name="wpshop_custom_shipping" cols="80" rows="12" >'.$fees_data.'</textarea></div>';
	}

	function wpshop_shipping_rule_by_min_max_field() {
		$id = 1;
		$currency = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$currency_code=$currencies[$currency];
		$rules = get_option('wpshop_shipping_rules',array());
		$default_rules = unserialize(WPSHOP_SHOP_SHIPPING_RULES);
		if(empty($rules)) $rules = $default_rules;
		if(empty($rules['min_max']['min']))$rules['min_max']['min']=$default_rules['min_max']['min'];
		if(empty($rules['min_max']['max']))$rules['min_max']['max']=$default_rules['min_max']['max'];

		echo '
<input type="hidden" id="amount_min" name="wpshop_shipping_rules[min_max][min]" />
<input type="hidden" id="amount_max" name="wpshop_shipping_rules[min_max][max]" />
<div id="slider-range_min_max" style="width:500px;margin:7px 0 0 10px;" class="slider_variable wpshop_options_slider wpshop_options_slider_shipping wpshop_options_slider_shipping_rules"></div>
<a href="#" title="'.__('Minimum and maximum amount for shipping','wpshop').'" class="wpshop_infobulle_marker">?</a>
<script type="text/javascript">
	wpshop(document).ready(function(){
		jQuery("#slider-range_min_max").slider({
			range: true,
			min: 0,
			max: 100,
			values: [ '.$rules['min_max']['min'].', '.$rules['min_max']['max'].' ],
			slide: function( event, ui ) {
				jQuery("#amount_min").val(ui.values[0]);
				jQuery("#slider-range_min_max a:first span strong").html(ui.values[0]+" '.$currency_code.'");
				jQuery("#amount_max").val(ui.values[1]);
				jQuery("#slider-range_min_max a:last span strong").html(ui.values[1]+" '.$currency_code.'");
			}
		});
		jQuery("#slider-range_min_max a:first").append("<span><strong>'.$rules['min_max']['min'].'"+" '.$currency_code.'</strong></span>");
		jQuery("#slider-range_min_max a:last").append("<span><strong>'.$rules['min_max']['max'].'"+" '.$currency_code.'</strong></span>");
		jQuery("#amount_min").val("'.$rules['min_max']['min'].'");
		jQuery("#amount_max").val("'.$rules['min_max']['max'].'");
	});
</script>';
	}

	function wpshop_shipping_rule_free_from_field() {
		$default_rules = unserialize(WPSHOP_SHOP_SHIPPING_RULES);

		$rules = get_option('wpshop_shipping_rules',array());
		if(empty($rules)) $rules = $default_rules;

		/*	Free shipping for all orders	*/
		echo '<div class="wpshop_free_fees" ><input type="checkbox" id="wpshop_shipping_rule_free_shipping" ' . (isset($rules['wpshop_shipping_rule_free_shipping']) && ($rules['wpshop_shipping_rule_free_shipping']) ? ' checked="checked" ' : '') . ' name="wpshop_shipping_rules[wpshop_shipping_rule_free_shipping]" />&nbsp;<label for="wpshop_shipping_rule_free_shipping" >'.__('Free shipping for all order', 'wpshop').'</label>
		<a href="#" title="'.__('Activate free shipping for all orders','wpshop').'" class="wpshop_infobulle_marker">?</a></div>';

		/*	Free shipping from given order amount	*/
		echo '<div class="wpshop_free_fees" ><input type="checkbox" id="wpshop_shipping_fees_freefrom_activation" name="free_from_active" '.((empty($rules['free_from']) || ($rules['free_from']==-1))?null:'checked="checked"').' />&nbsp;<label for="wpshop_shipping_fees_freefrom_activation" >'.__('Free shipping for order over amount below','wpshop').'</label>
<a href="#" title="'.__('Apply free shipping from the indicate amount. You can deactivate this option.','wpshop').'" class="wpshop_infobulle_marker">?</a></div>';
	}

	function wpshop_shipping_rule_free_shipping(){
		$currency = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$currency_code=$currencies[$currency];

		$activated = true;

		$default_rules = unserialize(WPSHOP_SHOP_SHIPPING_RULES);
		$rules = get_option('wpshop_shipping_rules',array());
		if(empty($rules))
			$rules = $default_rules;
		elseif(empty($rules['free_from']) || ($rules['free_from']<0)){
			$rules['free_from']=$default_rules['free_from'];
			$activated=false;
		}

		echo '
<div class="wpshop_shipping_method_parameter wpshop_shipping_fees_freefrom_activation_content'.(!$activated?" wpshopHide":null).'" >
	<input type="hidden" id="amount_free_from" name="wpshop_shipping_rules[free_from]" value="'.$rules['free_from'].'" />
	<div id="slider-range_free_from" class="slider_variable wpshop_options_slider wpshop_options_slider_shipping wpshop_options_slider_shipping_free_from"></div>
	<script type="text/javascript">
		wpshop(document).ready(function(){
			jQuery("#slider-range_free_from").slider({
				min: 0,
				max: 200,
				range: "min",
				value: '.(!empty($rules['free_from'])?$rules['free_from']:0).',
				slide: function( event, ui ) {
					jQuery("#amount_free_from").val(ui.value);
					jQuery("#slider-range_free_from a span strong").html(ui.value+" '.$currency_code.' ('.WPSHOP_PRODUCT_PRICE_PILOT.')");
				}
			});
			jQuery("#slider-range_free_from a").append("<span><strong>'.$rules['free_from'].' '.$currency_code.' ('.WPSHOP_PRODUCT_PRICE_PILOT.')</strong></span>");
		});
	</script>
</div>';
	}
	function wpshop_shipping_rule_free_shipping_from_date() {
	}
	function wpshop_shipping_rule_free_shipping_to_date() {
	}

	function wpshop_shipping_rule_by_weight_field() {
		$currency = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$currency_code=$currencies[$currency];
		$rules = get_option('wpshop_shipping_rules',array());
		echo '<input type="text" name="priority[]" value="1" style="float:right;width:50px;" />';
		echo '<textarea name="wpshop_shipping_rules[by_weight]" cols="80" rows="4">'.$rules['by_weight'].'</textarea><br />'.__('Example','wpshop').' : 500:5.45,1000:7.20,2000:10.30<br />'.__('Means','wpshop').' : 0 <= Weight < 500 (g) => 5.45 '.$currency_code.' etc..';
	}

	function wpshop_shipping_rule_by_percent_field() {
		$currency = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$currency_code=$currencies[$currency];
		$rules = get_option('wpshop_shipping_rules',array());
		echo '<input type="text" name="priority[]" value="2" style="float:right;width:50px;" />';
		echo '<textarea name="wpshop_shipping_rules[by_percent]" cols="80" rows="4">'.$rules['by_percent'].'</textarea><br />'.__('Example','wpshop').' : 100:8,200:6,300:4<br />'.__('Means','wpshop').' : 0 <= Amount < 100 ('.$currency_code.') => Shipping = 8% etc..';
	}

	function wpshop_shipping_rule_by_nb_of_items_field() {
		$currency = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$currency_code=$currencies[$currency];
		$rules = get_option('wpshop_shipping_rules',array());
		echo '<input type="text" name="priority[]" value="3" style="float:right;width:50px;" />';
		echo '<textarea name="wpshop_shipping_rules[by_nb_of_items]" cols="80" rows="4">'.$rules['by_nb_of_items'].'</textarea><br />'.__('Example','wpshop').' : 5:10,10:12,20:15<br />'.__('Means','wpshop').' : 0 <= Number of items < 5 (items) => 10 '.$currency_code.' etc..';
	}

	function wpshop_options_validate_shipping_rules($input) {
		$min = !empty($input['min_max']['min'])?preg_replace('#\D*?(\d+(\.\d+)?)\D*#', '$1', $input['min_max']['min']):'0';
		$max = !empty($input['min_max']['max'])?preg_replace('#\D*?(\d+(\.\d+)?)\D*#', '$1', $input['min_max']['max']):'0';

		$new_input['min_max'] = array('min'=>$min,'max'=>$max);

		if(isset($_POST['free_from_active']) && $_POST['free_from_active']=='on')
			// $new_input['free_from'] = preg_replace('#\D*?(\d+(\.\d+)?)\D*#', '$1', $input['free_from']);
			$new_input['free_from'] = $input['free_from'];
		else $new_input['free_from'] = -1;

		$new_input['wpshop_shipping_rule_free_shipping'] = $input['wpshop_shipping_rule_free_shipping'];
		// add_settings_error( 'wpshop_shipping_rule_free_shipping', 'texterror', 'Incorrect value entered!', 'error' );

		return $new_input;
	}

	function wpshop_options_validate_shipping_fees($input) {
		$fees = array();
		$fees['fees'] = wpshop_shipping::shipping_fees_string_2_array($input);
		$fees['active'] = isset($_POST['custom_shipping_active']) && $_POST['custom_shipping_active']=='on';

		return $fees;
	}
	
	function wpshop_shipping_address_validator($input){
		
		return $input;
	}
	
	function wpshop_shipping_address_field() {
		global $wpdb;
		$choice = get_option('wpshop_shipping_address_choice', unserialize(WPSHOP_SHOP_CUSTOM_SHIPPING));
		$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_name = "' .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. '" AND post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES. '"', '');
		$entity_id = $wpdb->get_var($query);
		
		$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = ' .$entity_id. '', '');
		$content = $wpdb->get_results($query);
		
		$input_def['name'] = 'wpshop_shipping_address_choice[choice]';
		$input_def['id'] = 'wpshop_shipping_address_choice[choice]';
		$input_def['possible_value'] = $content;
		$input_def['type'] = 'select';
		$input_def['value'] = $choice['choice'];
		
		$active = $choice['activate'];
		
		echo '<input type="checkbox" name="wpshop_shipping_address_choice[activate]" id="wpshop_shipping_address_choice[activate]" '.($active ? 'checked="checked"' :null).'/> <label for="active_shipping_address">'.__('Activate shipping address','wpshop').'</label></br/>
		<div">' .wpshop_form::check_input_type($input_def). '</div>';
	
	}
}































