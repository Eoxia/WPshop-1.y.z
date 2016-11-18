console.log( wps_product_variation_interface );
var attributes_generated = jQuery.extend({}, wps_product_variation_interface.variation);
for( x in attributes_generated ) {
	attributes_generated[x] = false;
}

var wps_variations_price_option_raw = {
	model: (function() {
		var result = [];
		jQuery.each( wps_product_variation_interface.variations_saved, function( saved_index, saved_element ) {
			var saved = {
				ID: saved_index,
				name: {
					model: (function() {
						var result_name = [];
						var options_names = (typeof saved_element.variation_def !== 'undefined' ) ? saved_element.variation_def : saved_element.variation_dif;
						for( option_name in options_names ) {
							if( typeof wps_product_variation_interface.variation[option_name] !== 'undefined' ) {
								attributes_generated[option_name] = true;
								result_name.push( {
									option_code: wps_product_variation_interface.variation[option_name].attribute_complete_def.code,
									option_type: wps_product_variation_interface.variation[option_name].attribute_complete_def.data_type,
									option_name: wps_product_variation_interface.variation[option_name].label,
									option_value: options_names[option_name],
									option_label: ( function() {
										for( var i = 0; wps_product_variation_interface.variation_value.length > i; i++ ) {
											if( options_names[option_name] == wps_product_variation_interface.variation_value[i].id ) {
												return wps_product_variation_interface.variation_value[i].label;
											}
										}
									} )()
								} );
							}
						}
						if( result_name.length > 1 ) {
							jQuery( 'input[name=question_combine_options][value=combine]' ).prop( "checked", true );
						}
						return result_name;
					})()
				},
				price_config: (function() {
					if( typeof saved_element.variation_dif !== 'undefined' && typeof saved_element.variation_dif.price_behaviour !== 'undefined' ) {
						if( typeof wps_product_variation_interface.attribute_in_variation !== 'undefined' && typeof wps_product_variation_interface.attribute_in_variation.price_behaviour !== 'undefined' && typeof wps_product_variation_interface.attribute_in_variation.price_behaviour.possible_value !== 'undefined' && typeof wps_product_variation_interface.attribute_in_variation.price_behaviour.possible_value[saved_element.variation_dif.price_behaviour] !== 'undefined' ) {
							result = wps_product_variation_interface.attribute_in_variation.price_behaviour.possible_value[saved_element.variation_dif.price_behaviour];
						}
					} else {
						var result = '=';
						if( typeof wps_product_variation_interface.variation_defining !== 'undefined' ) {
							if( typeof wps_product_variation_interface.variation_defining.options !== 'undefined' ) {
								if( typeof wps_product_variation_interface.variation_defining.options.price_behaviour !== 'undefined' ) {
									if( typeof wps_product_variation_interface.variation_defining.options.price_behaviour[0] !== 'undefined' && wps_product_variation_interface.variation_defining.options.price_behaviour[0] == 'addition' ) {
										var result = '+';
									}
								}
							}
						}
					}
					return result;
				})(),
				price_product: parseFloat( wps_product_variation_interface.product_price ),
				currency: wps_product_variation_interface.currency,
				piloting: wps_product_variation_interface.price_piloting,
				tx_tva: parseFloat( wps_product_variation_interface.tx_tva ),
				stock: fix_number( ( typeof saved_element.variation_dif !== 'undefined' && typeof saved_element.variation_dif.product_stock !== 'undefined' ) ? saved_element.variation_dif.product_stock : '0', 0 ),
				weight: fix_number( ( typeof saved_element.variation_dif !== 'undefined' && typeof saved_element.variation_dif.product_weight !== 'undefined' ) ? saved_element.variation_dif.product_weight : '0', 2 ),
				file: {
					model: {
						link: 'Click to add file',
						path: ''
					}
				},
				price_option_activate: 'checked'
			}
			config_id( saved );
			if( saved.price_config == '+' ) {
				saved.price_value = ( ( typeof saved_element.variation_dif !== 'undefined' && typeof saved_element.variation_dif.product_price !== 'undefined' ) ? saved_element.variation_dif.product_price : '0.00' ) - parseFloat( wps_product_variation_interface.product_price );
			} else if( saved.price_config == '=' ) {
				saved.price_value = ( typeof saved_element.variation_dif !== 'undefined' && typeof saved_element.variation_dif.product_price !== 'undefined' ) ? saved_element.variation_dif.product_price : '0.00';
			}
			saved.price_value = fix_number( saved.price_value, 2 );
			calcul_price( saved );
			result.push( saved );
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
				requiered: (function() {
					if( typeof wps_product_variation_interface.variation_defining.options !== 'undefined' && typeof wps_product_variation_interface.variation_defining.options.required_attributes !== 'undefined' && typeof wps_product_variation_interface.variation_defining.options.required_attributes[element.attribute_complete_def.code] !== 'undefined' ) {
						requiered = 'checked';
					} else {
						requiered = '';
					}
					return requiered;
				} )(),
				possibilities: {
					model: (function() {
						var result = [];
						var re = element.attribute_complete_def.default_value.match('s:13:"default_value";(.*)"(.*?)";');
						jQuery.each( element.available, function( index_values, element_values ) {
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
								} )(),
								value_possibility_selected: ( function() {
									if( element.values.indexOf( element_values ) > -1 ) {
										return ' selected';
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


function fix_number( parameter, decimal ) {
	if (typeof(decimal)==='undefined') decimal = 2;
	parameter = parseFloat( parseFloat( parameter ).toFixed(5) );
	var parameter_fixed = parameter.toFixed( decimal );
	if( parameter_fixed != parameter ) {
		parameter_fixed = parameter;
	}
	return parameter_fixed;
}

function config_id( parameter ) {
	parameter.price_config_id = (function() {
		if( typeof wps_product_variation_interface.attribute_in_variation !== 'undefined' && typeof wps_product_variation_interface.attribute_in_variation.price_behaviour !== 'undefined' && typeof wps_product_variation_interface.attribute_in_variation.price_behaviour.possible_value !== 'undefined' ) {
			for (value in wps_product_variation_interface.attribute_in_variation.price_behaviour.possible_value) {
				if( wps_product_variation_interface.attribute_in_variation.price_behaviour.possible_value[value] == parameter.price_config ) {
					return value;
				}
			}
		}
	})();
}

function calcul_price( parameter ) {
	if( parameter.price_config == '=' ) { parameter.price_option = parameter.price_value; }
	else if( parameter.price_config == '+' ) { parameter.price_option = fix_number( parseFloat( parameter.price_value ) + parameter.price_product ); }
	parameter.price_option = fix_number( parameter.price_option, 2 )
	parameter.vat = fix_number( parameter.price_option - ( parameter.price_option / ( 1 + ( parameter.tx_tva / 100 ) ) ) );
}

//////////////////////////////////// PLACEHOLDER SELECT ////////////////////////////////////
function placeholder_select( code ) {
	var select_element = jQuery( 'select[name="wps_variations_default[' + code + ']"]' );
	var optionPlaceholder = document.createElement( 'option' );
	optionPlaceholder.appendChild( document.createTextNode( select_element.data( 'placeholder-select' ) ) );
	jQuery( optionPlaceholder ).attr( 'disabled', 'disabled' );
	jQuery( optionPlaceholder ).attr( 'hidden', 'hidden' );
	var have_selected_value = false;
	select_element.find( 'option' ).each( function( index_select, element_select ) { if( element_select.getAttribute( 'selected' ) != null ) { have_selected_value = true; return false; } } );
	if( !have_selected_value ) {
		jQuery( optionPlaceholder ).attr( 'selected', 'selected' );
		select_element.addClass( 'placeholder-select' );
		jQuery( select_element ).change( function() {
			select_element.removeClass( 'placeholder-select' );
		} );
	}
	select_element.prepend( optionPlaceholder );
}
////////////////////////////////////////////////////////////////////////////////////////////

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
		placeholder_select( parameter.code );
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
		placeholder_select( parameter.code );
	};
	jQuery.each( wps_variations_options_raw.model, function( index, element ) {
		element.possibilities.control = new WPSVariationOptionsInterface( 'wps_variations_possibilities_' + element.code, element.possibilities.model );
		jQuery('.chosen_select_' + element.code ).chosen({no_results_text: "No result found. Press enter to add "}).on('change', function(evt, params) {
			if( typeof( params.selected ) !== 'undefined' ) {
				for (var i = 0; i < evt.currentTarget.children.length; i++) {
					if( evt.currentTarget.children[i].value == params.selected ) {
						var child = jQuery.extend({}, element.possibilities.model[evt.currentTarget.children[i].dataset.identifier]);
						child.value_possibility_selected = ' selected';
						element.possibilities.control.change( evt.currentTarget.children[i].dataset.identifier, child );
						return true;
					}
				}
			}
			if( typeof( params.deselected ) !== 'undefined' ) {
				for (var i = 0; i < evt.currentTarget.children.length; i++) {
					if( evt.currentTarget.children[i].value == params.deselected ) {
						var child = jQuery.extend({}, element.possibilities.model[evt.currentTarget.children[i].dataset.identifier]);
						child.value_possibility_selected = '';
						element.possibilities.control.change( evt.currentTarget.children[i].dataset.identifier, child );
						return true;
					}
				}
			}
		}).parent().find('.chzn-container .search-field input[type=text]').keydown( function (evt) {
			var stroke, _ref, target, list;
			stroke = (_ref = evt.which) != null ? _ref : evt.keyCode;
			target = jQuery(evt.target);
			if (stroke === 13) {
				var value = jQuery.trim(target.val());
				var new_val = {
					value_possibility_is_default: '',
					value_possibility_code: 0,
					value_possibility_label: value,
					value_possibility_selected: ' selected'
				}
				var new_val_id = element.possibilities.control.push( new_val );
				jQuery('.chosen_select_' + element.code ).trigger('liszt:updated');
				jQuery('.chosen_select_' + element.code ).parent().click();
				var data = {
					action: "new_option_for_select_from_product_edition",
					wpshop_ajax_nonce: WPSHOP_NEWOPTION_CREATION_NONCE,
					attribute_code: element.code,
					attribute_new_label: value,
					attribute_selected_values: [],
					item_in_edition: jQuery("#post_ID").val()
				};
				jQuery.post(ajaxurl, data, function(response) {
					new_val.value_possibility_code = response[3];
					element.possibilities.control.change( new_val_id, new_val );
				}, 'json' );
				return true;
			}
		});
		placeholder_select( element.code );
	} );
	wps_variations_price_option_raw.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_raw', wps_variations_price_option_raw.model );
	wps_variations_price_option_raw.control.price = function( element ) {
		var id = jQuery( element ).closest( "ul[data-view-model='wps_variations_price_option_raw']" ).data('identifier');
		var parameter = jQuery.extend({}, wps_variations_price_option_raw.model[id]);
		var price_value = parseFloat( jQuery( element ).val().replace(',', '.') );
		if( !Number.isNaN( Number( price_value ) ) ) { parameter.price_value = fix_number( price_value ); }
		calcul_price( parameter );
		this.change( id, parameter );
		parameter.name.control.refresh();
		parameter.file.control.refresh();
	};
	wps_variations_price_option_raw.control.config = function( element ) {
		var id = jQuery( element ).closest( "ul[data-view-model='wps_variations_price_option_raw']" ).data('identifier');
		var parameter = jQuery.extend({}, wps_variations_price_option_raw.model[id]);
		if( parameter.price_config == '+' ) {
			parameter.price_config = '=';
		} else if( parameter.price_config == '=' ) {
			parameter.price_config = '+';
		}
		config_id( parameter );
		calcul_price( parameter );
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
	wps_variations_price_option_raw.control.file = function( element ) {
		wps_variations_price_option_raw.model[jQuery( element ).closest( "ul[data-view-model='wps_variations_price_option_raw']" ).data('identifier')].file.control.file( element );
	}
	wps_variations_price_option_raw.control.link = function( event, input ) {
		wps_variations_price_option_raw.model[jQuery( input ).closest( "ul[data-view-model='wps_variations_price_option_raw']" ).data('identifier')].file.control.link( event, input );
	}
	var display_price_tab = false;
	jQuery.each( wps_variations_price_option_raw.model, function( index, element ) {
		element.name.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_name_' + element.ID, element.name.model );
		element.file.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_file_' + element.ID, element.file.model );
		element.file.control.file = function( element ) {
			jQuery( element ).next().click();
		}
		element.file.control.link = function( event, input ) {
			var path = jQuery( input ).val();
			var file = event.target.files[0];
			var link = file.name;
			var data = new FormData();
			data.append( 'wpshop_file', file, file.name );
			data.append( 'action', 'upload_downloadable_file_action' );
			data.append( 'element_identifier', element.ID );
			data.append( '_wpnonce', jQuery( input ).parent().find( '[name=wpshop_file_nonce]' ).val() );
			data.append( '_wp_http_referer', jQuery( input ).parent().find( '[name=_wp_http_referer]' ).val() );
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				contentType: false,
				cache: false,
				processData:false,
				enctype: 'multipart/form-data',
				data: data
			}).done(function( response ) {
				console.log( response );
			});
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

	jQuery( '#wps_variations_apply_btn:not(.disabled)' ).click( function() {
		jQuery( this ).addClass( 'disabled' );
		if( typeof jQuery( 'input[name=question_combine_options]:checked' ).val() === 'undefined' ) { return; }
		var variation_to_delete = [];
		for ( var i = wps_variations_price_option_raw.model.length; wps_variations_price_option_raw.model.length != 0; i-- ) {
			if( typeof wps_variations_price_option_raw.model[i] !== 'undefined' ) {
				variation_to_delete.push( wps_variations_price_option_raw.model[i].ID );
			}
			wps_variations_price_option_raw.control.remove( i );
		}
		wpshop_variation_delete ( variation_to_delete, wps_product_variation_interface.nonce_delete );
		var result = [];
		if( jQuery( 'input[name=question_combine_options]:checked' ).val() == 'single' ) {
			var id = 0;
			jQuery.each( wps_variations_options_raw.model, function( index, element ) {
				if( element.generate != '' ) {
					jQuery.each( element.possibilities.model, function( possibility_index, possibility_element ) {
						if( typeof possibility_element.value_possibility_selected !== 'undefined' && possibility_element.value_possibility_selected != '' ) {
							result.push( {
								ID: id,
								name: {
									model: [ {
										option_code: element.code,
										option_type: element.type,
										option_name: element.label,
										option_value: possibility_element.value_possibility_code,
										option_label: possibility_element.value_possibility_label
									} ]
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
								weight: '0.00',
								file: {
									model: {
										link: 'Click to add file',
										path: ''
									}
								},
								price_option_activate: 'checked'
							} );
							id++;
						}
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
										option_code: deep_element.code,
										option_type: deep_element.type,
										option_name: deep_element.label,
										option_value: deep_possibility_element.value_possibility_code,
										option_label: deep_possibility_element.value_possibility_label
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
								weight: '0.00',
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
			var size = {};
			size.current = 0;
			size.total = wps_variations_price_option_raw.model.length - 1;
			jQuery.each( wps_variations_price_option_raw.model, function( index, element ) {
				var data =  {
					action: 'add_new_single_variation',
					wpshop_head_product_id: jQuery( '#post_ID' ).val(),
					variation_attr: {},
					wpshop_admin_use_attribute_for_single_variation_checkbox: {},
					data: true,
					_wpnonce: jQuery( '#wps_variations_apply_btn' ).data( 'nonce' )
				};
				jQuery.each( element.name.model,function( index_new_variation, element_new_variation ) {
					data.variation_attr[element_new_variation.option_code] = element_new_variation.option_value;
					data.wpshop_admin_use_attribute_for_single_variation_checkbox[element_new_variation.option_code] = element_new_variation.option_code;
				} );
				jQuery.post(ajaxurl, data, function( response ) {
					var parameter = jQuery.extend({}, element);
					parameter.ID = response.ID;
					wps_variations_price_option_raw.control.change( index, parameter );
					wps_variations_price_option_raw.model[index].name.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_name_' + response.ID, parameter.name.model );
					wps_variations_price_option_raw.model[index].file.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_file_' + response.ID, parameter.file.model );
					if( size.current >= size.total ) {
						jQuery( '#wps_variations_apply_btn' ).removeClass( 'disabled' );
					}
					size.current++;
				}, 'JSON');
				element.name.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_name_' + element.ID, element.name.model );
				element.file.control = new WPSVariationOptionsInterface( 'wps_variations_price_option_file_' + element.ID, element.file.model );
				element.file.control.file = function( element ) {
					jQuery( element ).next().click();
				}
				element.file.control.link = function( event, input ) {
					var path = jQuery( input ).val();
					var file = event.target.files[0];
					var link = file.name;
					var data = new FormData();
					data.append( 'wpshop_file', file, file.name );
					data.append( 'action', 'upload_downloadable_file_action' );
					data.append( 'element_identifier', element.ID );
					data.append( '_wpnonce', jQuery( input ).parent().find( '[name=wpshop_file_nonce]' ).val() );
					data.append( '_wp_http_referer', jQuery( input ).parent().find( '[name=_wp_http_referer]' ).val() );
					jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						contentType: false,
						cache: false,
						processData:false,
						enctype: 'multipart/form-data',
						data: data
					}).done(function( response ) {
						console.log( response );
					});
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
