<?php if ( !defined( 'ABSPATH' ) ) exit;
$tpl_element = array();

/**
 * WPS SHIPPING MODE MAIN INTERFACE
 */
ob_start();
?>
<ul id="shipping_mode_list_container">
{WPSHOP_INTERFACES}
</ul>

<div id="add_shipping_mode_modal" style="display:none;">
<h2><?php _e('Shipping Mode Creation', 'wpshop'); ?></h2>
<div class="wps_shipping_mode_configuration_part">
	<p><label><?php _e('Shipping Mode name', 'wpshop'); ?> : </label> <input type="text" id="shipping_mode_name"></p>
	<p style="text-align:center;" ><button class="button-primary" id="add_shipping_mode" ><?php _e('Add the shipping mode', 'wpshop'); ?></button><img src="{WPSHOP_LOADER_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>"  id="add_shipping_mode_loader" class="wpshopHide" /></p>
</div>
<div id="shipping_mode_creation_error"></div>
</div>
<a href="#TB_inline?width=600&amp;height=200&amp;inlineId=add_shipping_mode_modal" class="thickbox button-secondary" id="create_new_shipping_mode"><?php _e('Create a shipping mode', 'wpshop'); ?></a><?php
$tpl_element['admin']['default']['wps_shipping_mode_main'] = ob_get_contents();
ob_end_clean();

/**
 * WPS SHIPPING MODE EACH INTERFACE
 */
ob_start();
?>
<li class="wps_shipping_mode_container" id="container_{WPSHOP_SHIPPING_MODE_ID}">
<div class="shipping_mode_titre">
<label for="wps_shipping_mode_configuration_{WPSHOP_SHIPPING_MODE_ID}_name"><?php _e('Name', 'wpshop'); ?></label> : <input type="text" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][name]" id="wps_shipping_mode_configuration_{WPSHOP_SHIPPING_MODE_ID}_name" value="{WPSHOP_SHIPPING_MODE_NAME}" /><br/>
<label for="{WPSHOP_SHIPPING_MODE_ID}_logo"><?php _e('Logo', 'wpshop'); ?></label> :<input type="file" id="{WPSHOP_SHIPPING_MODE_ID}_logo" name="{WPSHOP_SHIPPING_MODE_ID}_logo" /><input type="hidden" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][logo]" value="{WPSHOP_SHIPPING_MODE_LOGO_POST_ID}" /><br/>
{WPSHOP_SHIPPING_MODE_THUMBNAIL}
</div>
<div class="shipping_mode_little_configuration">
<label for="activate_shipping_mode_{WPSHOP_SHIPPING_MODE_ID}"><?php _e('Activate', 'wpshop')?></label> <input type="checkbox" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][active]" class="shipping_mode_is_active" id="activate_shipping_mode_{WPSHOP_SHIPPING_MODE_ID}" {WPSHOP_SHIPPING_MODE_ACTIVE} />
<br/>
<label for="{WPSHOP_SHIPPING_MODE_ID}_default"><?php _e('Default shipping mode', 'wpshop'); ?></label> <input type="radio" name="wps_shipping_mode[default_choice]" value="{WPSHOP_SHIPPING_MODE_ID}" id="{WPSHOP_SHIPPING_MODE_ID}_default" {WPSHOP_DEFAULT_SHIPPING_MODE_ACTIVE} />
<br/>
<!-- <a href="#TB_inline?width=600&amp;height=650&amp;inlineId={WPSHOP_SHIPPING_MODE_ID}_configuration_interface" class="thickbox button-secondary" ><?php _e('Configure the shipping mode', 'wpshop'); ?></a> -->
<a href="#" id="{WPSHOP_SHIPPING_MODE_ID}_configuration_interface_opener" class="button-secondary shipping_mode_configuration_opener" ><?php _e('Configure the shipping mode', 'wpshop'); ?></a>
<div id="{WPSHOP_SHIPPING_MODE_ID}_configuration_interface" style="display:none;" class="wps_shipping_mode_configuration_interface" >
     {WPSHOP_SHIPPING_MODE_CONFIGURATION_INTERFACE}
</div>
</div>
</li>
<?php
$tpl_element['admin']['default']['wps_shipping_mode_each_interface'] = ob_get_contents();
ob_end_clean();



/**
 * WPS SHIPPING MODE EACH INTERFACE
 */
ob_start();
?>
<div class="wps-boxed">
	<span class="wps-h2"><?php _e('General configurations', 'wpshop')?></span>
	<div>{WPSHOP_EXTRA_CONTENT}</div>
	<div class="wps-form-group">
		<label for="wps_shipping_mode_{WPSHOP_SHIPPING_MODE_ID}_explanation"><?php _e('Explanation', 'wpshop'); ?> :</label>
		<div class="wps-form"><textarea id="wps_shipping_mode_{WPSHOP_SHIPPING_MODE_ID}_explanation" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][explanation]">{WPSHOP_EXPLANATION}</textarea></div>
	</div>

	<span class="wps-h5"><?php _e('Free shipping cost configuration', 'wpshop')?></span>
	<div class="wps-row wps-gridwrapper2-padded">
		<!-- Free shipping for all orders -->
		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][free_shipping]" id="{WPSHOP_SHIPPING_MODE_ID}_free_shipping" {WPSHOP_FREE_SHIPPING} /> <label for="{WPSHOP_SHIPPING_MODE_ID}_free_shipping"><?php _e('Activate free shipping for all orders', 'wpshop'); ?></label></div>

		</div>

		<!-- Free shipping for orders which amount is over an amount -->
		<div>
			<div class="wps-form-group">
				<div class="wps-form"><input type="checkbox" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][activate_free_shipping_from]" id="{WPSHOP_SHIPPING_MODE_ID}_free_shipping" class="activate_free_shipping_cost_from" {WPSHOP_ACTIVATE_FREE_SHIPPING_FROM} /><label for="{WPSHOP_SHIPPING_MODE_ID}_free_from"><?php _e('Activate free shipping for order over amount below', 'wpshop'); ?></label></div>
			</div>
			<div class="wps-form-group" id="{WPSHOP_SHIPPING_MODE_ID}_activate_free_shipping">
				<label><?php _e('Free shipping cost for order over amount below', 'wpshop'); ?> ( {WPSHOP_CURRENCY} ) :</label>
				<div class="wps-form"><input type="text" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][free_from]" id="{WPSHOP_SHIPPING_MODE_ID}_free_from" value="{WPSHOP_FREE_FROM_VALUE}" class="wps_little_input" /></div>
			</div>
		</div>
	</div>

	<!--  Min & Max Shipping cost Configuration -->
	<span class="wps-h5"><?php _e('Minimum and maximum limit shipping cost configuration', 'wpshop')?></span>
	<div class="wps-row">
		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" class="wps_shipping_mode_configuation_min_max" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][min_max][activate]" id="{WPSHOP_SHIPPING_MODE_ID}_min_max_activate" {WPSHOP_MIN_MAX_ACTIVATE} /> <label for="{WPSHOP_SHIPPING_MODE_ID}_min_max_activate"><?php _e('Activate the min. and max. shipping cost', 'wpshop'); ?></label></div>
		</div>
	</div>

	<div class="wps-row">
		<div class="wps-row wps-gridwrapper2-padded" id="{WPSHOP_SHIPPING_MODE_ID}_min_max_shipping_rules_configuration">
			<div class="wps-form-group">
				<label><?php _e('Minimum', 'wpshop'); ?> ( {WPSHOP_CURRENCY} ) :</label>
				<div class="wps-form"><input type="text" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][min_max][min]" value="{WPSHOP_MIN_VALUE}" /></div>
			</div>

			<div class="wps-form-group">
				<label><?php _e('Maximum', 'wpshop'); ?> ( {WPSHOP_CURRENCY} ) :</label>
				<div class="wps-form"><input type="text" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][min_max][max]"  value="{WPSHOP_MAX_VALUE}" /></div>
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
			<select name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][limit_destination][country][]" class="chosen_select" multiple data-placeholder="<?php __('Choose a Country', 'wpshop' ); ?>" style="width : 100%">
				{WPSHOP_COUNTRIES_LIST}
			</select>
		</div>
	</div>

	<span class="wps-h5">2. <?php _e('Postcode Shipping Limitation', 'wpshop')?></span>
	<div class="wps-row">
		<label><?php _e('Write all allowed postcode, separate it by a comma. Let empty if you don\'t want limitations.', 'wpshop'); ?></label>
		<div class="wps-form">
			<textarea name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][limit_destination][postcode]">{WPSHOP_SHIPPING_MODE_POSTCODE_LIMIT_DESTINATION}</textarea>
		</div>
	</div>

	<span class="wps-h5">3. <?php _e('Department Shipping Limitation', 'wpshop')?></span>
	<div class="wps-row">
		<label><?php _e('Write all allowed department, separate it by a comma. Let empty if you don\'t want limitations.', 'wpshop'); ?></label>
		<div class="wps-form">
			<textarea name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][limit_destination][department]">{WPSHOP_SHIPPING_MODE_DEPARTMENT_LIMIT_DESTINATION}</textarea>
		</div>
	</div>

</div>

<div class="wps-boxed">
	<span class="wps-h2"><?php _e('Custom shipping rules', 'wpshop'); ?></span>
	<textarea id="{WPSHOP_SHIPPING_MODE_ID}_wpshop_custom_shipping" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][custom_shipping_rules][fees]" class="wpshopHide" >{WPSHOP_CUSTOM_SHIPPING_FEES_DATA}</textarea>

	<div class="wps-row wps-gridwrapper3-padded">
		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][custom_shipping_rules][active]" id="{WPSHOP_SHIPPING_MODE_ID}_custom_shipping_active" {WPSHOP_CUSTOM_SHIPPING_RULES_ACTIVE} /> <label for="{WPSHOP_SHIPPING_MODE_ID}_custom_shipping_active"><?php _e('Activate custom shipping fees','wpshop'); ?></label></div>
		</div>

		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" class="active_postcode_custom_shipping" id="{WPSHOP_SHIPPING_MODE_ID}_custom_shipping_active_cp" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][custom_shipping_rules][active_cp]" {WPSHOP_CUSTOM_SHIPPING_ACTIVE_CP}/> <label for="{WPSHOP_SHIPPING_MODE_ID}_custom_shipping_active_cp"> <?php _e('Activate custom shipping fees by postcode', 'wpshop'); ?></label></div>
		</div>

		<div class="wps-form-group">
			<div class="wps-form"><input type="checkbox" class="active_department_custom_shipping" id="{WPSHOP_SHIPPING_MODE_ID}_custom_shipping_active_department" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][custom_shipping_rules][active_department]" {WPSHOP_CUSTOM_SHIPPING_ACTIVE_DEPARTMENT}/> <label for="{WPSHOP_SHIPPING_MODE_ID}_custom_shipping_active_department"> <?php _e('Activate custom shipping fees by department', 'wpshop'); ?></label></div>
		</div>
	</div>

	<span class="wps-h5"><?php _e('Configuration', 'wpshop'); ?></span>
	<div class="wps-row wps-gridwrapper3-padded">

		<div class="wps-form-group">
			<label for="{WPSHOP_SHIPPING_MODE_ID}_country_list"><?php _e('Choose a country', 'wpshop'); ?> : </label>
			<div class="wps-form">
				<select id="{WPSHOP_SHIPPING_MODE_ID}_country_list" name="country_list" class="shipping_mode_config_input">
					{WPSHOP_CUSTOM_SHIPPING_COUNTRY_LIST}
				</select>
			</div>
		</div>

		<div class="wps-form-group postcode_rule">
			<label for="{WPSHOP_SHIPPING_MODE_ID}_postcode_rule" class="postcode_rule"><?php _e('Postcode', 'wpshop'); ?> : </label>
			<div class="wps-form">
				<input type="text" name="postcode_rule" id="{WPSHOP_SHIPPING_MODE_ID}_postcode_rule" class="shipping_rules_configuration_input postcode_rule"/>
			</div>
		</div>

		<div class="wps-form-group department_rule">
			<label for="{WPSHOP_SHIPPING_MODE_ID}_department_rule" class="department_rule"><?php _e('Department', 'wpshop'); ?> : </label>
			<div class="wps-form">
				<input type="text" name="department_rule" id="{WPSHOP_SHIPPING_MODE_ID}_department_rule" class="shipping_rules_configuration_input department_rule"/>
			</div>
		</div>

	</div>

	<div class="wps-row wps-gridwrapper2-padded">
		<div class="wps-form-group">
			<label for="{WPSHOP_SHIPPING_MODE_ID}_weight_rule"><?php _e('Weight', 'wpshop'); ?> <strong>({WPSHOP_SHIPPING_WEIGHT_UNITY})</strong> : </label>
			<div class="wps-form"><input type="text" name="weight_rule" id="{WPSHOP_SHIPPING_MODE_ID}_weight_rule" class="shipping_rules_configuration_input"/></div>
		</div>

		<div class="wps-form-group">
			<label for="{WPSHOP_SHIPPING_MODE_ID}_shipping_price"><?php _e('Price', 'wpshop'); ?> <strong>({WPSHOP_CURRENCY} <?php echo WPSHOP_PRODUCT_PRICE_PILOT; ?>)</strong> : </label>
			<div class="wps-form"><input type="text" name="shipping_price" id="{WPSHOP_SHIPPING_MODE_ID}_shipping_price" class="shipping_rules_configuration_input"/></div>
		</div>
	</div>

	<div class="wps-row">
		<input type="checkbox" id="{WPSHOP_SHIPPING_MODE_ID}_main_rule" name="main_rule" value="OTHERS"/> <label for="{WPSHOP_SHIPPING_MODE_ID}_main_rule" class="global_rule_checkbox_indic"><?php _e('Apply a common rule to all others countries','wpshop'); ?></label>
	</div>

	<div class="wps-form-group">
		<center><a id="{WPSHOP_SHIPPING_MODE_ID}_save_rule" role="button" data-nonce="<?php echo wp_create_nonce( 'wpshop_ajax_save_shipping_rule' ); ?>" class="save_rules_button wps-bton-first-rounded"><?php _e('Add the rule', 'wpshop'); ?></a></center>
	</div>

	<div class="wps-row wps-table wps-bloc-loader" id="{WPSHOP_SHIPPING_MODE_ID}_shipping_rules_container" data-nonce="<?php echo wp_create_nonce( 'wpshop_ajax_display_shipping_rules' ); ?>">
		{WPSHOP_CUSTOM_SHIPPING_RULES_DISPLAY}
	</div>
</div>
<?php
$tpl_element['admin']['default']['wps_shipping_mode_configuration_interface'] = ob_get_contents();
ob_end_clean();




/**
 * SHIPPING RULES TABLE
 */

ob_start();
?>
<div><span class="wps-h5"><?php _e('Existing custom shipping rules', 'wpshop'); ?></span></div>
<div class="wps-table-header wps-table-row">
	<div class="wps-table-cell"><?php _e('Country', 'wpshop'); ?></div>
	<div class="wps-table-cell"><?php _e('Weight', 'wpshop'); ?></div>
	<div class="wps-table-cell"><?php _e('Price', 'wpshop'); ?></div>
	<div class="wps-table-cell"><?php _e('Delete', 'wpshop'); ?></div>
</div>
{WPSHOP_CUSTOM_SHIPPING_RULES_LINES}
<?php
$tpl_element['admin']['default']['shipping_rules_table'] = ob_get_contents();
ob_end_clean();


/**
 * SHIPPING RULES TABLE LINE
 */

ob_start();
?>
<div class="wps-table-content wps-table-row">
	<div class="wps-table-cell"><?php echo $country_name; ?> ({WPSHOP_SHIPPING_RULE_DESTINATION})</div>
	<div class="wps-table-cell">{WPSHOP_SHIPPING_RULE_WEIGHT} {WPSHOP_SHIPPING_RULE_WEIGHT_UNITY}</div>
	<div class="wps-table-cell">{WPSHOP_SHIPPING_RULE_FEE} {WPSHOP_SHIPPING_RULE_WEIGHT_CURRENCY}</div>
	<div class="wps-table-cell">
		<a href="#" id="{WPSHOP_SHIPPING_RULE_DESTINATION}|{WPSHOP_SHIPPING_RULE_WEIGHT}|{WPSHOP_SHIPPING_MODE_ID}" class="delete_rule" data-nonce="<?php echo wp_create_nonce( 'wpshop_ajax_delete_shipping_rule' ); ?>" title="{WPSHOP_SHIPPING_MODE_ID}"><i class="wps-icon-trash"></i></a>
	</div>
</div>
<?php
$tpl_element['admin']['default']['shipping_rules_table_line'] = ob_get_contents();
ob_end_clean();
