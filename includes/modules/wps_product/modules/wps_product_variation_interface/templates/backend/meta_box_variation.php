<div id="wps_variations_summary">
	<div data-view-model="wps_variations_options_summary" id="wps_variations_summary_display"><b>%summary%</b></div><div id="wps_variations_parameters" data-nonce="<?php echo wp_create_nonce( 'wps_remove_variation_interface' ); ?>"><span class="dashicons dashicons-admin-generic"></span></div>
</div>
<div id="wps_variations_options">
	<ul id="wps_variations_options_title">
		<li>Generate options</li>
		<li>Option</li>
		<li>Requiered</li>
		<li>Possibilities</li>
		<li>Default</li>
	</ul>
	<ul data-view-model="wps_variations_options_raw" class="wps_variations_options_raw">
		<li class="wps_variations_generate_col"><input name="wps_variations_generate_option" onchange="wps_variations_options_raw.control.generate(this)" type="checkbox"%generate%></li>
		<li class="wps_variations_label_col">%label%</li>
		<li class="wps_variations_requiered_col"><input name="wps_variations_requiered_option" onchange="wps_variations_options_raw.control.requiered(this)" type="checkbox"%requiered%></li>
		<li class="wps_variations_possibilities_col"><select class="chosen_select_%code%" name="wps_variations_possibilities" multiple data-placeholder="Select some options"><option data-view-model="wps_variations_possibilities_%code%" value="%value_possibility_code%">%value_possibility_label%</option></select></li>
		<li class="wps_variations_default_col"><select name="wps_variations_default"><option data-view-model="wps_variations_possibilities_%code%" value="%value_possibility_code%"%value_possibility_is_default%>%value_possibility_label%</option></select></li>
	</ul>
</div>
<div id="wps_variations_questions">
	<ul>
		<li class="wps_variations_questions_question_col">Do you want to manage each options singly or combine them ?</li>
		<li class="wps_variations_questions_answers_col">
			<input id="question_combine_options_<?php echo $id = uniqid(); ?>" name="question_combine_options" type="radio" value="combine">
			<label for="question_combine_options_<?php echo $id; ?>">Combined options</label>
			<input id="question_combine_options_<?php echo $id = uniqid(); ?>" name="question_combine_options" type="radio" value="single">
			<label for="question_combine_options_<?php echo $id; ?>">Distinct options</label>
		</li>
	</ul>
</div>
<div id="wps_variations_tabs">
	<ul>
		<li data-tab="wps_variations_price_option_tab" class="disabled">Options prices</li>
		<li id="wps_variations_apply_btn" class="">Apply modifications</li>
	</ul>
</div>
<div id="wps_variations_price_option_tab" class="wps_variations_tabs">
	<ul id="wps_variations_price_option_tab_title">
		<li>ID</li>
		<li>Options</li>
		<li>Prices</li>
		<li>Final prices</li>
		<li>VAT</li>
		<li data-view-model="wps_variations_price_option_attributes_col"></li>
		<li>Activate</li>
	</ul>
	<ul data-view-model="wps_variations_price_option_raw">
		<li class="wps_variations_price_id_col">%ID%</li>
		<li class="wps_variations_price_name_col"><span data-view-model="wps_variations_price_option_name_%ID%">%option_name%<span class="option_value">%option_value%</span></span></li>
		<li class="wps_variations_price_config_col"><span class="wps_variations_price_option_price_config" onclick="wps_variations_price_option_raw.control.config(this)" data-change-price-config="%ID%">%price_config%</span><input type="text" pattern="[0-9]*" onchange="wps_variations_price_option_raw.control.price(this)" name="wps_variations_price_option_price_value" align="right" value="%price_value%"></li>
		<li class="wps_variations_price_final_col"><b>%price_option%%currency%</b> %piloting%</li>
		<li class="wps_variations_price_vat_col">%vat%%currency%</li>
		<li class="wps_variations_price_attributes_col" data-view-model="wps_variations_price_option_attributes_%ID%"></li>
		<li class="wps_variations_price_active_col"><input name="wps_variations_price_option_activate" type="checkbox" %price_option_activate%></li>
	</ul>
</div>