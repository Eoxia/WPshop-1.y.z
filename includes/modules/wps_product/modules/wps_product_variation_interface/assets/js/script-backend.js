jQuery(document).ready( function() {
	var wps_variations_options = [];

	jQuery('.wps_variations_tabs').hide();

	jQuery('#wps_variations_tabs li').click( function(e) {
		e.preventDefault();
		if( jQuery( this ).data( 'tab' ) ) {
			jQuery( this ).toggleClass( 'active' );
			jQuery( '#' + jQuery( this ).data( 'tab' ) ).toggle();
		}
	});

	jQuery.each( wps_product_variation_interface.variation, function( index, element ) {
		wps_variations_options.push( {
			code: element.attribute_complete_def.code,
			generate: '',
			label: element.label,
			requiered: '',
			possibilities: (function() {
				var result = [];
				jQuery.each( element.values, function( index, element ) {
					result.push( {
						value_possibility_code: element,
						value_possibility_label: 'no_label'
					} );
				} );
				return result;
			})(),
			default: (function() {
				return {
					value_default_code: 'code',
					value_default_selected: '',
					value_default_label: 'no_label'
				};
			})()
		} );
	} );

	var wps_variations_options_raw = new WPSVariationOptionsInterface( 'wps_variations_options_raw', wps_variations_options );
	console.log( wps_variations_options_raw.dataModel );
	//new WPSVariationOptionsInterface( 'wps_variations_options_summary', wps_variations_options_raw );
	jQuery.each( wps_variations_options, function( index, element ) {
		new WPSVariationOptionsInterface( 'wps_variations_possibilities_' + element.code, element.possibilities );
		new WPSVariationOptionsInterface( 'wps_variations_default_' + element.code, element.default );
		jQuery('.chosen_select_' + element.code ).chosen();
	} );

	jQuery( '#wps_variations_apply_btn' ).click( function() {
		console.log( wps_variations_options );
	} );

	new WPSVariationOptionsInterface( 'wps_variations_price_option_raw', wps_variations_price_option );
});

function WPSVariationOptionsInterface( identifier, model ) {
	var WPSVariationOptionsInterface = this;
	WPSVariationOptionsInterface.identifier = identifier;
	WPSVariationOptionsInterface.dataModel = model;
	if( jQuery.isArray( model ) ) {
		jQuery("[data-view-model='" + WPSVariationOptionsInterface.identifier + "']").each( function( index, element ) {
			WPSVariationOptionsInterface.viewModels[index] = element;
			var parent = { DOM: jQuery( element ).parent().clone() };
			parent.DOM.find("> *").each(function(parentIndex, parentElement) {
				parent.DOM.find( parentElement ).remove();
			});
			if( parent.DOM.text().trim() === '' ) {
				if( jQuery( element ).parent().find("> *").length > 1 && jQuery( element ).prev().length == 1 ) {
					var element_prev = jQuery( element ).prev();
					WPSVariationOptionsInterface.viewModels[index].markerFunc = function( parameter ) {
						element_prev.after( parameter );
					}
					jQuery( element ).remove();
				} else {
					var element_parent = jQuery( element ).parent();
					WPSVariationOptionsInterface.viewModels[index].markerFunc = function( parameter ) {
						element_parent.prepend( parameter );
					}
					jQuery( element ).remove();
				}
			} else {
				var markerDOM = document.createElement( "span" );
				jQuery( markerDOM ).attr( "id", "marker_" + WPSVariationOptionsInterface.identifier + "_" + index );
				jQuery( markerDOM ).hide();
				jQuery( element ).after( markerDOM );
				jQuery( element ).remove();
				WPSVariationOptionsInterface.viewModels[index].markerFunc = function( parameter ) {
					jQuery( markerDOM ).after( parameter );
				}
			}
		} );
		WPSVariationOptionsInterface.push = function( parameter ) {
			WPSVariationOptionsInterface.dataModel.push( parameter );
			var id = WPSVariationOptionsInterface.dataModel.length - 1;
			jQuery.each( WPSVariationOptionsInterface.viewModels, function( index, element ) {
				var clone = jQuery( element ).clone();
				clone.data( "id", WPSVariationOptionsInterface.identifier + "-" + index + "-" + id );
				for( var property in parameter ) {
					var regex = new RegExp( '%' + property + '%', "g" );
					clone = jQuery( clone ).get( 0 ).outerHTML.replace( regex, parameter[property] );
				}
				element.markerFunc( clone );
			} );
			return id;
		}
		WPSVariationOptionsInterface.change = function( id, parameter ) {
			WPSVariationOptionsInterface.dataModel[id] = parameter;
			jQuery.each( WPSVariationOptionsInterface.viewModels, function( index, element ) {
				var clone = jQuery( element ).clone();
				clone.data( "id", WPSVariationOptionsInterface.identifier + "-" + index + "-" + id );
				for( var property in parameter ) {
					var regex = new RegExp( '%' + property + '%', "g" );
					jQuery( "[data-id='" + WPSVariationOptionsInterface.identifier + "-" + index + "-" + id + "']" ).html( clone.html().replace( regex, parameter[property] ) );
				}
			} );
		}
		WPSVariationOptionsInterface.remove = function( id ) {
			WPSVariationOptionsInterface.dataModel.splice( id, 1 );
			jQuery.each( WPSVariationOptionsInterface.viewModels, function( index, element ) {
				jQuery( "[data-id='" + WPSVariationOptionsInterface.identifier + "-" + index + "-" + id + "']" ).remove();
			} );
		}
		jQuery.each( model, function( index, element ) {
			WPSVariationOptionsInterface.push( element );
		});
	} else {
		jQuery("[data-view-model='" + WPSVariationOptionsInterface.identifier + "']").each( function( index, element ) {
			WPSVariationOptionsInterface.viewModels[index] = element;
		} );
		WPSVariationOptionsInterface.change = function( parameter ) {
			WPSVariationOptionsInterface.dataModel = parameter;
			jQuery.each( WPSVariationOptionsInterface.viewModels, function( index, element ) {
				var clone = jQuery( element ).clone();
				for( var property in parameter ) {
					var regex = new RegExp( '%' + property + '%', "g" );
					jQuery( element ).html( clone.html().replace( regex, parameter[property] ) );
				}
			} );
		}
		WPSVariationOptionsInterface.change( model );
	}
}
WPSVariationOptionsInterface.prototype = {
	dataModel: null,
	viewModels: []
}