var wps_variations_options_raw = {
	model: (function() {
		var result = [];
		jQuery.each( wps_product_variation_interface.variation, function( index, element ) {
			result.push( {
				code: element.attribute_complete_def.code,
				generate: '',
				label: element.label,
				requiered: '',
				possibilities: {
					model: (function() {
						var result = [];
						jQuery.each( element.values, function( index_values, element_values ) {
							result.push( {
								value_possibility_code: element_values,
								value_possibility_label: 'no_label'
							} );
						} );
						return result;
					})()
				},
				default: {
						model: {
						value_default_code: 'code',
						value_default_selected: '',
						value_default_label: 'no_label'
					}
				}
			} );
		} );
		return result;
	})()
};

var wps_variations_options_summary = {
	model: {
		summary: ''
	}
};

jQuery(document).ready( function() {
	/////////////////////////////////////////// TABS ///////////////////////////////////////////
	jQuery('.wps_variations_tabs').hide();
	jQuery('#wps_variations_tabs li').click( function(e) {
		e.preventDefault();
		if( jQuery( this ).data( 'tab' ) ) {
			jQuery( this ).toggleClass( 'active' );
			jQuery( '#' + jQuery( this ).data( 'tab' ) ).toggle();
		}
	});
	////////////////////////////////////////////////////////////////////////////////////////////

	wps_variations_options_summary.control = new WPSVariationOptionsInterface( 'wps_variations_options_summary', wps_variations_options_summary.model );
	wps_variations_options_summary.control.summary = function() {
		var not_generate = [];
		var generate = [];
		jQuery.each( wps_variations_options_raw.model, function( index, element ) {
			if( element.generate == '' ) {
				not_generate.push( ' | ' + element.label );
			} else {
				generate.push( ' X ' + element.label );
			}
		} );
		var result = '';
		var first = true;
		jQuery.each( generate.concat( not_generate ), function( index, element ) {
			if(first) {
				result += element.substr(3);
				first = false;
			} else {
				result += element;
			}
		} );
		this.change( { summary: result } );
	};
	wps_variations_options_summary.control.summary();
	wps_variations_options_raw.control = new WPSVariationOptionsInterface( 'wps_variations_options_raw', wps_variations_options_raw.model );
	wps_variations_options_raw.control.requiered = function( element ) {
		var id = jQuery( element ).closest( "ul[data-view-model='wps_variations_options_raw']" ).data('identifier');
		var parameter = jQuery.extend({}, wps_variations_options_raw.model[id]);
		if( jQuery( element ).is( ':checked' ) ) {
			parameter.requiered = 'checked';
		} else {
			parameter.requiered = '';
		}
		this.change( id, parameter );
		parameter.default.control.refresh();
		parameter.possibilities.control.refresh();
		jQuery('.chosen_select_' + parameter.code ).chosen();
	};
	wps_variations_options_raw.control.generate = function( element ) {
		var id = jQuery( element ).closest( "ul[data-view-model='wps_variations_options_raw']" ).data('identifier');
		var parameter = jQuery.extend({}, wps_variations_options_raw.model[id]);
		if( jQuery( element ).is( ':checked' ) ) {
			parameter.generate = 'checked';
		} else {
			parameter.generate = '';
		}
		this.change( id, parameter );
		parameter.default.control.refresh();
		parameter.possibilities.control.refresh();
		wps_variations_options_summary.control.summary();
		jQuery('.chosen_select_' + parameter.code ).chosen();
	};
	jQuery.each( wps_variations_options_raw.model, function( index, element ) {
		element.default.control = new WPSVariationOptionsInterface( 'wps_variations_default_' + element.code, element.default.model );
		element.possibilities.control = new WPSVariationOptionsInterface( 'wps_variations_possibilities_' + element.code, element.possibilities.model );
		jQuery('.chosen_select_' + element.code ).chosen();
	} );

	jQuery( '#wps_variations_apply_btn' ).click( function() {
		console.log( wps_variations_options );
	} );
});

/* function myFunction() {
    console.log('It works!');
}

var name = 'myFunction';

window[name].call(); */