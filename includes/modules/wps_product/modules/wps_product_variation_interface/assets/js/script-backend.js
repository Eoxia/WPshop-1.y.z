jQuery(document).ready( function() {
	var wps_variations_options = [];

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

	new WPSVariationOptionsInterface( 'wps_variations_options_raw', wps_variations_options );
	jQuery.each( wps_variations_options, function( index, element ) {
		new WPSVariationOptionsInterface( 'wps_variations_possibilities_' + element.code, element.possibilities );
		new WPSVariationOptionsInterface( 'wps_variations_default_' + element.code, element.default );
	} );
});

function WPSVariationOptionsInterface( identifier, model ) {
	var WPSVariationOptionsInterface = this;
	WPSVariationOptionsInterface.identifier = identifier;
	WPSVariationOptionsInterface.dataModel = model;
	if( jQuery.isArray( model ) ) {
		jQuery("[data-view-model='" + WPSVariationOptionsInterface.identifier + "']").each( function( index, element ) {
			WPSVariationOptionsInterface.viewModels[index] = element;
			var markerDOM = document.createElement( "span" );
			jQuery( markerDOM ).attr( "id", "marker_" + WPSVariationOptionsInterface.identifier + "_" + index );
			jQuery( markerDOM ).hide();
			jQuery( element ).after( markerDOM );
			jQuery( element ).remove();
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
				jQuery( "#marker_" + WPSVariationOptionsInterface.identifier + "_" + index ).after( clone );
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