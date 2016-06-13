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
		var wps_variations_price_option_raw = {
			model: (function() {
				var result = [];
				if( false ) {
					jQuery.each( wps_variations_options_raw.model, function( index, element ) {
						if( element.generate != '' ) {
							jQuery.each( element.possibilities.model, function( possibility_index, possibility_element ) {
								result.push( {
									ID: 0,
									name: {
										model: {
											option_name: element.label,
											option_value: possibility_element.value_possibility_label
										}
									},
									price_config: '+',
									price_value: 0,
									price_option: 0,
									currency: '€',
									piloting: 'ati',
									vat: 0,
									price_option_activate: ''
								} );
							} );
						}
					} );
				} else {
					var raw;
					jQuery.each( wps_variations_options_raw.model, function( deep_index, deep_element ) {
						if( deep_element.generate != '' ) {
							if( typeof raw === 'undefined' ) { raw = [1]; }
							jQuery.each( raw, function( index_raw, element_raw ) {
								if( element_raw == 1 ) { raw.splice( index_raw, 1 ); }
								jQuery.each( deep_element.possibilities.model, function( deep_possibility_index, deep_possibility_element ) {
									if( (h.length==undefined||h[0]==undefined) && (h.length!==0||h[0]!==undefined) ) { raw[deep_possibility_index] = []; };
									raw[deep_possibility_index].push( {
										option_name: deep_element.label,
										option_value: deep_possibility_element.value_possibility_label
									} );
								} );
							} );
						}
					} );
					/*result.push( {
						ID: 0,
						name: {
							model: []
						},
						price_config: '+',
						price_value: 0,
						price_option: 0,
						currency: '€',
						piloting: 'ati',
						vat: 0,
						price_option_activate: ''
					} );*/
					console.log(raw);
					/*var test = [1];
					jQuery.each( wps_variations_options_raw.model, function( deep_index, deep_element ) {
						if( deep_element.generate != '' ) {
							var testy = [];
							jQuery.each( test, function() {
								jQuery.each( deep_element.possibilities.model, function( deep_possibility_index, deep_possibility_element ) {
									testy.push(deep_possibility_element);
								} );
							} );
							test = testy;
						}
					} );
					console.log(test);*/
				}
				return result;
			})()
		};
		new WPSVariationOptionsInterface( 'wps_variations_price_option_raw', wps_variations_price_option_raw.model );
	} );

});

/* function myFunction() {
    console.log('It works!');
}

var name = 'myFunction';

window[name].call(); */