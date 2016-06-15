function WPSVariationOptionsInterface( identifier, model ) {
	var WPSVariationOptionsInterface = this;
	WPSVariationOptionsInterface.identifier = identifier;
	WPSVariationOptionsInterface.viewModels = [];
	if( jQuery.isArray( model ) ) {
		function privatePush( id, parameter ) {
			jQuery.each( WPSVariationOptionsInterface.viewModels, function( index, element ) {
				var clone = jQuery( element ).clone();
				jQuery( clone ).attr( 'data-identifier', id );
				jQuery( clone ).attr( 'data-view-model-id', index );
				for( var property in parameter ) {
					var regex = new RegExp( '%' + property + '%', "g" );
					clone = jQuery( clone ).get( 0 ).outerHTML.replace( regex, parameter[property] );
				}
				element.markerFunc( clone );
			} );
		}
		WPSVariationOptionsInterface.push = function( parameter ) {
			model.push( parameter );
			var id = model.length - 1;
			privatePush( id, parameter );
			return id;
		}
		WPSVariationOptionsInterface.change = function( id, parameter ) {
			model[id] = parameter;
			jQuery.each( WPSVariationOptionsInterface.viewModels, function( index, element ) {
				var clone = jQuery( element ).clone();
				jQuery( clone ).attr( 'data-identifier', id );
				jQuery( clone ).attr( 'data-view-model-id', index );
				for( var property in parameter ) {
					var regex = new RegExp( '%' + property + '%', "g" );
					clone = jQuery( clone ).get( 0 ).outerHTML.replace( regex, parameter[property] );
				}
				jQuery( "[data-view-model='" + WPSVariationOptionsInterface.identifier + "'][data-view-model-id='" + index + "'][data-identifier='" + id + "']" ).html( jQuery( clone ).html() );
			} );
		}
		WPSVariationOptionsInterface.remove = function( id ) {
			model.splice( id, 1 );
			jQuery( "[data-view-model='" + WPSVariationOptionsInterface.identifier + "'][data-identifier='" + id + "']" ).remove();
		}
		WPSVariationOptionsInterface.refresh = function() {
			jQuery("[data-view-model='" + WPSVariationOptionsInterface.identifier + "']").each( function( index, element ) {
				WPSVariationOptionsInterface.viewModels[index] = element;
				var parent = { DOM: jQuery( element ).parent().clone() };
				parent.DOM.find("> *").each(function(parentIndex, parentElement) {
					parent.DOM.find( parentElement ).remove();
				});
				if( parent.DOM.text().trim() === '' ) {
					var element_parent = jQuery( element ).parent();
					if( element_parent.find("> *").length > 1 && jQuery( element ).prev().length == 1 ) {
						var element_prev = jQuery( element ).prev();
						WPSVariationOptionsInterface.viewModels[index].markerFunc = function( parameter ) {
							var preview = element_prev;
							if( element_parent.find("[data-view-model='" + WPSVariationOptionsInterface.identifier + "'][data-view-model-id='" + index + "']:last").length >= 1 ) {
								preview = element_parent.find("[data-view-model='" + WPSVariationOptionsInterface.identifier + "'][data-view-model-id='" + index + "']:last");
							}
							preview.after( parameter );
						}
						jQuery( element ).remove();
					} else {
						WPSVariationOptionsInterface.viewModels[index].markerFunc = function( parameter ) {
							element_parent.append( parameter );
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
						var preview = markerDOM;
						if( markerDOM.find("[data-view-model='" + WPSVariationOptionsInterface.identifier + "'][data-view-model-id='" + index + "']:last").length >= 1 ) {
							preview = markerDOM.find("[data-view-model='" + WPSVariationOptionsInterface.identifier + "'][data-view-model-id='" + index + "']:last");
						}
						preview.after( parameter );
					}
				}
			} );
			jQuery.each( model, function( index, element ) {
				privatePush( index, element );
			});
		}
	} else {
		WPSVariationOptionsInterface.change = function( parameter ) {
			model = parameter;
			jQuery.each( WPSVariationOptionsInterface.viewModels, function( index, element ) {
				var clone = jQuery( element ).clone();
				jQuery( clone ).attr( 'data-view-model-id', index );
				for( var property in parameter ) {
					var regex = new RegExp( '%' + property + '%', "g" );
					clone = jQuery( clone ).get( 0 ).outerHTML.replace( regex, parameter[property] );
				}
				jQuery( "[data-view-model='" + WPSVariationOptionsInterface.identifier + "'][data-view-model-id='" + index + "']" ).html( jQuery( clone ).html() );
			} );
		}
		WPSVariationOptionsInterface.refresh = function() {
			jQuery("[data-view-model='" + WPSVariationOptionsInterface.identifier + "']").each( function( index, element ) {
				WPSVariationOptionsInterface.viewModels[index] = jQuery( element ).clone();
				jQuery( element ).attr( 'data-view-model-id', index );
			} );
			WPSVariationOptionsInterface.change( model );
		}
	}
	WPSVariationOptionsInterface.refresh();
}