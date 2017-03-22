<div id="wps_variations_summary">
	<div data-view-model="wps_variations_options_summary" id="wps_variations_summary_display"><b>%summary%</b></div><a id="wps_variations_parameters" href="<?php print wp_nonce_url(get_edit_post_link($post->ID) . '&wps_variation_interface=false', 'wps_remove_variation_interface');?>" title="<?php _e('Back to old interface', 'wpshop'); ?>"><span class="dashicons dashicons-admin-generic"></span></a>
</div>
<div id="wps_variations_options">
	<ul id="wps_variations_options_title">
		<li><?php _e('Generate option', 'wpshop'); ?></li>
		<li><?php _e('Option', 'wpshop'); ?></li>
		<li><?php _e('Requiered', 'wpshop'); ?></li>
		<li><?php _e('Possibilities', 'wpshop'); ?></li>
		<li><?php _e('Default', 'wpshop'); ?></li>
	</ul>
	<ul data-view-model="wps_variations_options_raw" class="wps_variations_options_raw">
		<li class="wps_variations_generate_col"><input name="wps_variations_generate_option" onchange="wps_variations_options_raw.control.generate(this)" type="checkbox"%generate%></li>
		<li class="wps_variations_label_col">%label%</li>
		<li class="wps_variations_requiered_col"><input name="wpshop_variation_defining[options][required_attributes][%code%]" value="%code%" onchange="wps_variations_options_raw.control.requiered(this)" type="checkbox"%requiered%></li>
		<li class="wps_variations_possibilities_col"><select class="chosen_select_%code%" name="wpshop_product_attribute[%type%][%code%][]" multiple data-placeholder="Select some options"><option data-view-model="wps_variations_possibilities_%code%" value="%value_possibility_code%"%value_possibility_selected%>%value_possibility_label%</option></select></li>
		<li class="wps_variations_default_col"><select name="wpshop_variation_defining[options][attributes_default_value][%code%]" data-placeholder-select="Choose default"><option data-view-model="wps_variations_possibilities_%code%" value="%value_possibility_code%"%value_possibility_is_default%>%value_possibility_label%</option></select></li>
	</ul>
</div>
<div id="wps_variations_questions">
	<ul>
		<li class="wps_variations_questions_question_col"><?php _e('Do you want to manage each options singly or combine them ?', 'wpshop'); ?></li>
		<li class="wps_variations_questions_answers_col">
			<input id="question_combine_options_<?php echo $id = uniqid(); ?>" name="question_combine_options" type="radio" value="combine">
			<label for="question_combine_options_<?php echo $id; ?>"><?php _e('Combined options', 'wpshop'); ?></label>
			<input id="question_combine_options_<?php echo $id = uniqid(); ?>" name="question_combine_options" type="radio" value="single">
			<label for="question_combine_options_<?php echo $id; ?>"><?php _e('Distinct options', 'wpshop'); ?></label>
		</li>
	</ul>
</div>
<div id="wps_variations_tabs">
	<ul>
		<li data-tab="wps_variations_price_option_tab" class="disabled"><?php _e('Options prices', 'wpshop'); ?></li>
		<li id="wps_variations_apply_btn" data-nonce="<?php echo wp_create_nonce('wpshop_variation_management'); ?>"><?php _e('Apply modifications', 'wpshop'); ?></li>
	</ul>
</div>
<div id="wps_variations_price_option_tab" class="wps_variations_tabs">
	<ul id="wps_variations_price_option_tab_title">
		<li class="wps_variations_price_id_col"><?php _e('ID', 'wpshop'); ?></li>
		<li class="wps_variations_price_name_col"><?php _e('Options', 'wpshop'); ?></li>
		<li class="wps_variations_price_config_col"><?php _e('Prices', 'wpshop'); ?></li>
		<li class="wps_variations_price_final_col"><?php _e('Final prices', 'wpshop'); ?></li>
		<li class="wps_variations_price_vat_col"><?php _e('VAT', 'wpshop'); ?></li>
		<li class="wps_variations_price_stock_col"><?php _e('Stock', 'wpshop'); ?></li>
		<li class="wps_variations_price_weight_col"><?php _e('Weight', 'wpshop'); ?></li>
		<li class="wps_variations_price_reference_col"><?php _e('Ref. product', 'wpshop'); ?></li>
		<li class="wps_variations_price_file_col"><?php _e('Link download', 'wpshop'); ?></li>
		<?php echo apply_filters( 'wps_filters_product_variation_extra_columns_title', '' ); ?>
		<li class="wps_variations_price_active_col"><?php _e('Activate', 'wpshop'); ?></li>
	</ul>
	<ul data-view-model="wps_variations_price_option_raw">
		<li class="wps_variations_price_id_col">%ID%</li>
		<li class="wps_variations_price_name_col"><span data-view-model="wps_variations_price_option_name_%ID%"><input type="hidden" name="wps_pdt_variations[%ID%][attribute][%option_type%][%option_code%]" value="%option_value%">%option_name%<span class="option_value">%option_label%</span></span></li>
		<li class="wps_variations_price_config_col"><span class="wps_variations_price_option_price_config" onclick="wps_variations_price_option_raw.control.config(this)">%price_config%</span><input type="hidden" name="wps_pdt_variations[%ID%][attribute][integer][price_behaviour]" value="%price_config_id%"><input type="text" pattern="[0-9]+(\.[0-9][0-9]?)?" onchange="wps_variations_price_option_raw.control.price(this)" align="right" value="%price_value%"></li>
		<li class="wps_variations_price_final_col"><input type="hidden" name="wps_pdt_variations[%ID%][attribute][decimal][product_price]" value="%price_option%"><b>%price_option%%currency%</b> %piloting%</li>
		<li class="wps_variations_price_vat_col">%vat%%currency%</li>
		<li class="wps_variations_price_stock_col"><input type="text" pattern="[0-9]*" onchange="wps_variations_price_option_raw.control.stock(this)" name="wps_pdt_variations[%ID%][attribute][decimal][product_stock]" align="right" value="%stock%"></li>
		<li class="wps_variations_price_weight_col"><input type="text" pattern="[0-9]+(\.[0-9][0-9]?)?" onchange="wps_variations_price_option_raw.control.weight(this)" name="wps_pdt_variations[%ID%][attribute][decimal][product_weight]" align="right" value="%weight%"></li>
		<li class="wps_variations_price_reference_col"><input type="text" onchange="wps_variations_price_option_raw.control.reference(this)" name="wps_pdt_variations[%ID%][attribute][varchar][product_reference]" align="right" value="%reference%"></li>
		<li class="wps_variations_price_file_col" data-view-model="wps_variations_price_option_file_%ID%"><span class="wps_variations_price_option_price_file" onclick="wps_variations_price_option_raw.control.file(this)">%link%</span><input style="display: none;" type="file" name="wpshop_file" id="wpshop_file" onchange="wps_variations_price_option_raw.control.link(event, this)"><?php wp_nonce_field('ajax_wpshop_upload_downloadable_file_action', 'wpshop_file_nonce');?><a class="wps_variations_price_option_price_download_file" href="%path%" target="_blank" download="" style="display: %download%"><span class="dashicons dashicons-download"></span></a></li>
		<?php echo apply_filters( 'wps_filters_product_variation_extra_columns_content', '' ); ?>
		<li class="wps_variations_price_active_col"><input name="wps_pdt_variations[%ID%][status]" onclick="wps_variations_price_option_raw.control.activate(this)" type="checkbox" %price_option_activate%></li>
	</ul>
</div>
