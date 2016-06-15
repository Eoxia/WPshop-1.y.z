var wps_variations_options_raw = {
	model: (function() {
		var result = [];
		console.log(wps_product_variation_interface);
		jQuery.each( wps_product_variation_interface.variation, function( index, element ) {
			result.push( {
				code: element.attribute_complete_def.code,
				generate: '',
				label: element.label,
				requiered: (function() { if( ['YES', 'OUI'].indexOf( element.attribute_complete_def.is_required.toUpperCase() ) +1 ) { requiered = 'checked'; } else { requiered = ''; } return requiered; } )(),
				possibilities: {
					model: (function() {
						var result = [];
						var re = element.attribute_complete_def.default_value.match('s:13:"default_value";(.*)"(.*?)";');
						jQuery.each( element.values, function( index_values, element_values ) {
							var is_default = '';
							if( re != null && re[2] == element_values ) {
								is_default = ' selected';
							}
							result.push( {
								value_possibility_is_default: is_default,
								value_possibility_code: element_values,
								value_possibility_label: ( function() {
									for( var i = 0; wps_product_variation_interface.variation_value.length > i; i++ ) {
										if( element_values == wps_product_variation_interface.variation_value[i].id ) {
											return wps_product_variation_interface.variation_value[i].label;
										}
									}
								} )()
							} );
						} );
						return result;
					})()
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

var wps_variations_price_option_raw = {
	model: []
};

jQuery(document).ready( function() {
	/////////////////////////////////////////// TABS ///////////////////////////////////////////
	jQuery('.wps_variations_tabs').hide();
	jQuery('#wps_variations_tabs li').click( function(e) {
		e.preventDefault();
		if( jQuery( this ).data( 'tab' ) && !jQuery( this ).hasClass( 'disabled' ) ) {
			jQuery( this ).toggleClass( 'active' );
			jQuery( '#' + jQuery( this ).data( 'tab' ) ).toggle();
		}
	});
	////////////////////////////////////////////////////////////////////////////////////////////

	jQuery( '#wps_variations_parameters' ).click( function (e) {
		e.preventDefault();
		var _wpnonce = this.dataset.nonce;
		jQuery.post( ajaxurl, { action: 'wps-remove-variation-interface', _wpnonce: _wpnonce } );
	} );

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
		parameter.possibilities.control.refresh();
		wps_variations_options_summary.control.summary();
		jQuery('.chosen_select_' + parameter.code ).chosen();
	};
	jQuery.each( wps_variations_options_raw.model, function( index, element ) {
		element.possibilities.control = new WPSVariationOptionsInterface( 'wps_variations_possibilities_' + element.code, element.possibilities.model );
		jQuery('.chosen_select_' + element.code ).chosen();
	} );
	wps_variations_price_option_raw.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_raw', wps_variations_price_option_raw.model );
	wps_variations_price_option_raw.control.price = function( element ) {
		var id = jQuery( element ).closest( "ul[data-view-model='wps_variations_price_option_raw']" ).data('identifier');
		var parameter = jQuery.extend({}, wps_variations_price_option_raw.model[id]);
		var price_value = parseFloat( jQuery( element ).val() );
		if( !Number.isNaN( Number( price_value ) ) ) {
			parameter.price_value = price_value;
		}
		if( parameter.price_config == '=' ) { parameter.price_option = parameter.price_value }
		else if( parameter.price_config == '+' ) { parameter.price_option = parseFloat( parameter.price_value + parameter.price_product ) }
		this.change( id, parameter );
		parameter.name.control.refresh();
	};
	wps_variations_price_option_raw.control.config = function( element ) {
		var id = jQuery( element ).closest( "ul[data-view-model='wps_variations_price_option_raw']" ).data('identifier');
		var parameter = jQuery.extend({}, wps_variations_price_option_raw.model[id]);
		if( parameter.price_config == '+' ) {
			parameter.price_config = '=';
		} else if( parameter.price_config == '=' ) {
			parameter.price_config = '+';
		}
		this.change( id, parameter );
		parameter.name.control.refresh();
	};

	jQuery( '#wps_variations_apply_btn' ).click( function() {
		if( typeof jQuery( 'input[name=question_combine_options]:checked' ).val() === 'undefined' ) { return; }
		for ( var i = wps_variations_price_option_raw.model.length; wps_variations_price_option_raw.model.length != 0; i-- ) {
			wps_variations_price_option_raw.control.remove( i );
		}
		var result = [];
		if( jQuery( 'input[name=question_combine_options]:checked' ).val() == 'single' ) {
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
							price_product: wps_product_variation_interface.product_price,
							currency: '€',
							piloting: 'ati',
							tx_tva: wps_product_variation_interface.tx_tva,
							vat: wps_product_variation_interface.tx_tva,
							price_option_activate: 'checked'
						} );
					} );
				}
			} );
		} else if( jQuery( 'input[name=question_combine_options]:checked' ).val() == 'combine' ) {
			var first = true;
			jQuery.each( wps_variations_options_raw.model, function( deep_index, deep_element ) {
				if( deep_element.generate != '' ) {
					if( first ) {
						result.push( { name: { model: [] } } );
						first = false;
					}
					var raw = result;
					result = [];
					var id = 0;
					jQuery.each( raw, function( index_raw, element_raw ) {
						jQuery.each( deep_element.possibilities.model, function( deep_possibility_index, deep_possibility_element ) {
							result.push( {
								ID: id,
								name: {
									model: element_raw.name.model.concat( [ {
										option_name: deep_element.label,
										option_value: deep_possibility_element.value_possibility_label
									} ] )
								},
								price_config: '+',
								price_value: 0,
								price_option: 0,
								price_product: wps_product_variation_interface.product_price,
								currency: '€',
								piloting: 'ati',
								tx_tva: wps_product_variation_interface.tx_tva,
								vat: ,
								price_option_activate: 'checked'
							} );
							id++;
						} );
					} );
				}
			} );
		}
		if( result != 0 ) {
			jQuery.each( result, function( index, element ) {
				wps_variations_price_option_raw.control.push( element );
			} );
			jQuery.each( wps_variations_price_option_raw.model, function( index, element ) {
				element.name.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_name_' + element.ID, element.name.model );
			} );
			jQuery( 'li[data-tab=wps_variations_price_option_tab]' ).removeClass( 'disabled' );
			if( !jQuery( 'li[data-tab=wps_variations_price_option_tab]' ).hasClass( 'active' ) ) {
				jQuery( 'li[data-tab=wps_variations_price_option_tab]' ).click();
			}
		}
	} );

});

/* function myFunction() {
    console.log('It works!');
}

var name = 'myFunction';

window[name].call(); */