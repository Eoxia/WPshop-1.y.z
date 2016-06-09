<div id="wps_variations_summary">
	<div data-view-model="wps_variations_options_summary" id="wps_variations_summary_display">%summary%</div><div id="wps_variations_parameters"><span class="dashicons dashicons-admin-generic"></span></div>
</div>
<div id="wps_variations_options">
	<ul id="wps_variations_options_title">
		<li>Generate options</li>
		<li>Option</li>
		<li>Requiered</li>
		<li>Possibilities</li>
		<li>Default</li>
	</ul>
	<ul data-view-model="wps_variations_options_raw" class="wps_variations_options_raw" data-variation="%code%">
		<li class="wps_variations_generate_col"><input name="wps_variations_generate_option" type="checkbox"%generate%></li>
		<li class="wps_variations_label_col">%label%</li>
		<li class="wps_variations_requiered_col"><input name="wps_variations_requiered_option" type="checkbox"%requiered%></li>
		<li class="wps_variations_possibilities_col"><select class="chosen_select" name="wps_variations_possibilities" multiple><option data-view-model="wps_variations_possibilities_%code%" value="%value_possibility_code%">%value_possibility_label%</option></select></li>
		<li class="wps_variations_default_col"><select name="wps_variations_default"><option data-view-model="wps_variations_default_%code%" value="%value_default_code%"%value_default_selected%>%value_default_label%</option></select></li>
	</ul>
</div>
<div id="wps_variations_questions">
</div>
<div id="wps_variations_tabs">
	<ul>
		<li>Options prices</li>
		<li id="wps_variations_apply_btn" class="">Apply modifications</li>
	</ul>
</div>