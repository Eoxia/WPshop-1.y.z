console.log( wps_product_variation_interface );
var attributes_generated = jQuery.extend({}, wps_product_variation_interface.variation);
for( x in attributes_generated ) {
	attributes_generated[x] = false;
}

var wps_variations_price_option_raw = {
	model: (function() {
		var result = [];
		jQuery.each( wps_product_variation_interface.variations_saved, function( saved_index, saved_element ) {
			result.push( {
				ID: saved_index,
				name: {
					model: (function() {
						var result_name = [];
						for( option_name in saved_element.variation_def ) {
							attributes_generated[option_name] = true;
							result_name.push( {
								option_code: wps_product_variation_interface.variation[option_name].attribute_complete_def.code,
								option_type: wps_product_variation_interface.variation[option_name].attribute_complete_def.data_type,
								option_name: wps_product_variation_interface.variation[option_name].label,
								option_value: saved_element.variation_def[option_name],
								option_label: ( function() {
									for( var i = 0; wps_product_variation_interface.variation_value.length > i; i++ ) {
										if( saved_element.variation_def[option_name] == wps_product_variation_interface.variation_value[i].id ) {
											return wps_product_variation_interface.variation_value[i].label;
										}
									}
								} )()
							} );
						}
						if( result_name.length > 1 ) {
							jQuery( 'input[name=question_combine_options][value=combine]' ).prop( "checked", true );
						}
						return result_name;
					})()
				},
				price_config: '+',
				price_value: fix_number( saved_element.variation_dif.product_price ),
				price_option: fix_number( wps_product_variation_interface.product_price ),
				price_product: parseFloat( wps_product_variation_interface.product_price ),
				currency: wps_product_variation_interface.currency,
				piloting: wps_product_variation_interface.price_piloting,
				tx_tva: parseFloat( wps_product_variation_interface.tx_tva ),
				vat: fix_number( saved_element.variation_dif.tva ),
				stock: '0',
				weight: '0',
				file: {
					model: {
						link: 'Click to add file',
						path: ''
					}
				},
				price_option_activate: 'checked'
			} );
		} );
		if( result.length > 0 && !jQuery( 'input[name=question_combine_options][value=combine]' ).is( ':checked' ) ) {
			jQuery( 'input[name=question_combine_options][value=single]' ).prop( "checked", true );
		}
		return result;
	} )()
};

var wps_variations_options_raw = {
	model: (function() {
		var result = [];
		jQuery.each( wps_product_variation_interface.variation, function( index, element ) {
			result.push( {
				code: element.attribute_complete_def.code,
				generate: (function() {
					if( attributes_generated[element.attribute_complete_def.code] ) { return ' checked'; } else { return ''; }
				})(),
				label: element.label,
				type: element.attribute_complete_def.data_type,
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


function fix_number( parameter ) {
	parameter = parseFloat( parseFloat( parameter ).toFixed(5) );
	var parameter_fixed = parameter.toFixed(2);
	if( parameter_fixed != parameter ) {
		parameter_fixed = parameter;
	}
	return parameter_fixed;
}

jQuery(document).ready( function() {
	/////////////////////////////////////////// TABS ///////////////////////////////////////////
	jQuery('.wps_variations_tabs').hide();
	jQuery('#wps_variations_tabs li').click( function(e) {
		e.preventDefault();
		if( jQuery( this ).data( 'tab' ) && !jQuery( this ).hasClass( 'disabled' ) ) {
			var $toggle = jQuery( this ).hasClass( 'active' );
			jQuery( '#wps_variations_tabs li' ).removeClass( 'active' );
			jQuery('.wps_variations_tabs').hide();
			if( !$toggle ) {
				jQuery( this ).addClass( 'active' );
				jQuery( '#' + jQuery( this ).data( 'tab' ) ).show();
			}
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
		var price_value = parseFloat( jQuery( element ).val().replace(',', '.') );
		if( !Number.isNaN( Number( price_value ) ) ) { parameter.price_value = fix_number( price_value ); }
		if( parameter.price_config == '=' ) { parameter.price_option = parameter.price_value; }
		else if( parameter.price_config == '+' ) { parameter.price_option = fix_number( parseFloat( parameter.price_value ) + parameter.price_product ); }
		parameter.vat = fix_number( parameter.price_option - ( parameter.price_option / ( 1 + ( parameter.tx_tva / 100 ) ) ) );
		this.change( id, parameter );
		parameter.name.control.refresh();
		parameter.file.control.refresh();
	};
	wps_variations_price_option_raw.control.config = function( element ) {
		var id = jQuery( element ).closest( "ul[data-view-model='wps_variations_price_option_raw']" ).data('identifier');
		var parameter = jQuery.extend({}, wps_variations_price_option_raw.model[id]);
		if( parameter.price_config == '+' ) {
			parameter.price_config = '=';
			parameter.price_option = parameter.price_value
		} else if( parameter.price_config == '=' ) {
			parameter.price_config = '+';
			parameter.price_option = fix_number( parseFloat( parameter.price_value ) + parameter.price_product );
		}
		parameter.vat = fix_number( parameter.price_option - ( parameter.price_option / ( 1 + ( parameter.tx_tva / 100 ) ) ) );
		this.change( id, parameter );
		parameter.name.control.refresh();
		parameter.file.control.refresh();
	};
	wps_variations_price_option_raw.control.stock = function( element ) {
		var id = jQuery( element ).closest( "ul[data-view-model='wps_variations_price_option_raw']" ).data('identifier');
		var parameter = jQuery.extend({}, wps_variations_price_option_raw.model[id]);
		var stock = parseInt( jQuery( element ).val() );
		if( !Number.isNaN( Number( stock ) ) ) { parameter.stock = stock; }
		this.change( id, parameter );
		parameter.name.control.refresh();
		parameter.file.control.refresh();
	};
	wps_variations_price_option_raw.control.weight = function( element ) {
		var id = jQuery( element ).closest( "ul[data-view-model='wps_variations_price_option_raw']" ).data('identifier');
		var parameter = jQuery.extend({}, wps_variations_price_option_raw.model[id]);
		var weight = parseFloat( jQuery( element ).val().replace(',', '.') );
		if( !Number.isNaN( Number( weight ) ) ) { parameter.weight = weight; }
		this.change( id, parameter );
		parameter.name.control.refresh();
		parameter.file.control.refresh();
	};
	jQuery.each( wps_variations_price_option_raw.model, function( index, element ) {
		element.name.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_name_' + element.ID, element.name.model );
		element.file.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_file_' + element.ID, element.file.model );
		element.file.control.file = function( element ) {
			//jQuery( element ).next().click();
		}
		element.file.control.link = function( event, input ) {
			var path = jQuery( input ).val();
			var file = event.target.files[0];
			var link = file.name;
			var reader = new FileReader();
			reader.onload = function(event) {
				jQuery.ajax({
					type: 'POST',
					url: 'upload.php',
           			enctype: 'multipart/form-data',
					data: {
						action: 'upload_downloadable_file_action',
						element_identifer: element.ID,
						wpshop_file: event.target.result,
						_wpnonce: jQuery( input ).parent().find( '[name=_wpnonce]' ).val(),
						_wp_http_referer: jQuery( input ).parent().find( '[name=_wp_http_referer]' ).val(),
					}
				}).done(function( response ) {
					console.log( response );
				});
			}
			reader.readAsDataURL(file);
			this.change( { link: link, path: path } );
		}
		display_price_tab = true;
	} );
	if( display_price_tab ) {
		jQuery( 'li[data-tab=wps_variations_price_option_tab]' ).removeClass( 'disabled' );
		if( !jQuery( 'li[data-tab=wps_variations_price_option_tab]' ).hasClass( 'active' ) ) {
			jQuery( 'li[data-tab=wps_variations_price_option_tab]' ).click();
		}
	}

	jQuery( '#wps_variations_apply_btn' ).click( function() {
		if( typeof jQuery( 'input[name=question_combine_options]:checked' ).val() === 'undefined' ) { return; }
		for ( var i = wps_variations_price_option_raw.model.length; wps_variations_price_option_raw.model.length != 0; i-- ) {
			wps_variations_price_option_raw.control.remove( i );
		}
		var result = [];
		if( jQuery( 'input[name=question_combine_options]:checked' ).val() == 'single' ) {
			var id = 0;
			jQuery.each( wps_variations_options_raw.model, function( index, element ) {
				if( element.generate != '' ) {
					jQuery.each( element.possibilities.model, function( possibility_index, possibility_element ) {
						result.push( {
							ID: id,
							name: {
								model: {
									option_code: element.code,
									option_type: element.type,
									option_name: element.label,
									option_value: possibility_element.value_possibility_code,
									option_label: possibility_element.value_possibility_label
								}
							},
							price_config: '+',
							price_value: '0.00',
							price_option: fix_number( wps_product_variation_interface.product_price ),
							price_product: parseFloat( wps_product_variation_interface.product_price ),
							currency: wps_product_variation_interface.currency,
							piloting: wps_product_variation_interface.price_piloting,
							tx_tva: parseFloat( wps_product_variation_interface.tx_tva ),
							vat: fix_number( wps_product_variation_interface.product_price - ( wps_product_variation_interface.product_price / ( 1 + ( wps_product_variation_interface.tx_tva / 100 ) ) ) ),
							stock: '0',
							weight: '0',
							file: {
								model: {
									link: 'Click to add file',
									path: ''
								}
							},
							price_option_activate: 'checked'
						} );
						id++;
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
										option_code: element.code,
										option_type: element.type,
										option_name: deep_element.label,
										option_value: possibility_element.value_possibility_code,
										option_label: possibility_element.value_possibility_label
									} ] )
								},
								price_config: '+',
								price_value: '0.00',
								price_option: fix_number( wps_product_variation_interface.product_price ),
								price_product: parseFloat( wps_product_variation_interface.product_price ),
								currency: wps_product_variation_interface.currency,
								piloting: wps_product_variation_interface.price_piloting,
								tx_tva: parseFloat( wps_product_variation_interface.tx_tva ),
								vat: fix_number( wps_product_variation_interface.product_price - ( wps_product_variation_interface.product_price / ( 1 + ( wps_product_variation_interface.tx_tva / 100 ) ) ) ),
								stock: '0',
								weight: '0',
								file: {
									model: {
										link: 'Click to add file',
										path: ''
									}
								},
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
				element.file.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_file_' + element.ID, element.file.model );
				element.file.control.file = function( element ) {
					jQuery( element ).next().click();
				}
				element.file.control.link = function( event, element ) {
					var path = jQuery( element ).val();
					var link = event.target.files[0].name;
					this.change( { link: link, path: path } );
				}
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