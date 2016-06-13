<div id="wps_variations_summary">
	<div data-view-model="wps_variations_options_summary" id="wps_variations_summary_display"><b>%summary%</b></div><div id="wps_variations_parameters"><span class="dashicons dashicons-admin-generic"></span></div>
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
		<li class="wps_variations_default_col"><select name="wps_variations_default"><option data-view-model="wps_variations_default_%code%" value="%value_default_code%"%value_default_selected%>%value_default_label%</option></select></li>
	</ul>
</div>
<div id="wps_variations_questions">
</div>
<div id="wps_variations_tabs">
	<ul>
		<li data-tab="wps_variations_price_option_tab">Options prices</li>
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
		<li>%ID%</li>
		<li><span data-view-model="">%option_name%<span>%option_value%</span></span></li>
		<li><span class="wps_variations_price_option_price_config" data-change-price-config="%ID%">%price_config%</span>%price_value%</li>
		<li><b>%price_option%%currency%</b>%piloting%</li>
		<li>%vat%%currency%</li>
		<li data-view-model="wps_variations_price_option_attributes%"></li>
		<li><input name="wps_variations_price_option_activate" type="checkbox"%price_option_activate%></li>
	</ul>
</div>