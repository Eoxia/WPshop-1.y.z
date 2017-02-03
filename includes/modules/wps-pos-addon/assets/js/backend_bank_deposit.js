jQuery( document ).ready( function() {
	///////////////////////////////////////// Create UI /////////////////////////////////////////
	jQuery( '.sort_by_dates' ).datepicker({
    	dateFormat: 'yy-mm-dd'
    });
	var total_payments;
	if ( typeof payments == 'undefined' ) {
		payments = [];
	}
	if ( typeof historics == 'undefined' ) {
		historics = [];
	}
	var payments_by_dates = payments;
	var payments_method = payments_by_dates;
	var payments_final = payments_method;
	array_display( payments_final );
	jQuery( '#this_day' ).click( function() {
		date = new Date();
		jQuery( 'input[name=\'fromdate\']' ).val( date.getFullYear() + '-' + ( date.getMonth() + 1 ) + '-' + date.getDate() );
		jQuery( 'input[name=\'todate\']' ).val( '' );
		jQuery( '.sort_by_dates' ).change();
	});
	jQuery( '#this_week' ).click( function() {
		from = new Date();
		from.setDate( ( from.getDate() - 7 ) + ( 8 - from.getDay() ) );
		to = new Date();
		to.setDate( to.getDate() + ( 7 - to.getDay() ) );
		jQuery( 'input[name=\'fromdate\']' ).val( from.getFullYear() + '-' + ( from.getMonth() + 1 ) + '-' + from.getDate() );
		jQuery( 'input[name=\'todate\']' ).val( to.getFullYear() + '-' + ( to.getMonth() + 1 ) + '-' + to.getDate() );
		jQuery( '.sort_by_dates' ).change();
	});
	jQuery( '#this_year' ).click( function() {
		date = new Date();
		jQuery( 'input[name=\'fromdate\']' ).val( date.getFullYear() + '-01-01' );
		jQuery( 'input[name=\'todate\']' ).val( date.getFullYear() + '-12-31' );
		jQuery( '.sort_by_dates' ).change();
	});
	jQuery( '.method' ).click( function() {
		changes( this.dataset.value );
	} );
	jQuery( '.sort_by_dates' ).change( function() {
		changes();
	} );
	jQuery( 'input[name=\'search\']' ).change( function() {
		changes();
	} );
	function changes( method ) {
		payments_by_dates = sort_by_dates( payments, jQuery( 'input[name=\'fromdate\']' ).val(), jQuery( 'input[name=\'todate\']' ).val() );
		payments_method = filters_method( payments_by_dates, method );
		payments_final = filters_search( payments_method, jQuery( '#search' ).val() );
		array_display( payments_final );
	}
	jQuery( '#download' ).click( function( event ) {
		event.preventDefault();
		save_historics( total_payments, payments_final );
	} );
	function sort_by_dates( payments_dates, from, to ) {
		var result = [];
		if ( from == '' && to == '' ) {
			result = payments_dates;
		} else {
			if ( from == '' && to != '' ) {
				start = new Date( '9999-01-01' );
				end = new Date( to );
			} else if ( from != "" && to == "" ) {
				start = new Date(from);
				end = new Date('9999-12-31');
			} else if( from != "" && to != "" ) {
				start = new Date(from);
				end = new Date(to);
	        }
			jQuery.each( payments_dates, function( index, element ) {
				d = new Date(element.date);
				if( start <= d && d <= end ) {
	            	result.push( element );
	            }
			} );
		}
		return result;
	}
	function filters_method( payments_method, method ) {
		var result = [];
		if( typeof method === 'undefined' ) {
			result = payments_method;
		} else {
			jQuery.each( payments_method, function( index, element ) {
				if( element.method == method ) {
					result.push( element );
				}
			} );
		}
		return result;
	}
	function filters_search( payments_search, s ) {
		var result = [];
		jQuery.each( payments_search, function( index, element ) {
			element_str = JSON.stringify( element );
			regexp = new RegExp( s, 'gi' );
			if( element_str.match(regexp) ) {
				result.push( element );
			}
		} );
		return result;
	}
	function array_display( payments_display ) {
		total_payments = 0;
		count = 0;
		jQuery( ".tr_row" ).remove();
		jQuery.each( payments_display, function( index, element ) {
			tr_model = jQuery( '#model_row' );
			clone = tr_model.clone();
			tr_model.before( clone );
			clone.removeAttr( "id" );
			clone.addClass( "tr_row" );
			clone.html( clone.html().replace( /%id%/g, element.id ) );
			clone.html( clone.html().replace( /%order_key%/g, element.order_key ) );
			clone.html( clone.html().replace( /%date%/g, element.date ) );
			products_string = '';
			first = true;
			jQuery.each( element.products, function( index, element ) {
				if( first ) {
					first = false;
					extra = '';
				} else {
					extra = ', ';
				}
				products_string += extra + element;
			});
			clone.html( clone.html().replace( /%products%/g, products_string ) );
			if( element.amount != undefined ) {
				total_payments = total_payments + parseFloat( element.amount );
				amount = element.amount
			} else {
				amount = 0;
				amount = amount.toFixed(2);
			}
			clone.html( clone.html().replace( /%amount%/g, amount ) );
			clone.html( clone.html().replace( /%method%/g, element.method ) );
			count += 1;
		} );
		if( count == 0 ) {
			jQuery( '#model_no_results' ).show();
		} else {
			jQuery( '#model_no_results' ).hide();
		}
		jQuery( "#total_amount" ).html( total_payments.toFixed(2) + " " + jQuery( "#total_amount" ).data( "currency" ) );
	}

	///////////////////////////////////////// Histo UI /////////////////////////////////////////
	var more_histo = false;
	array_display_histo( historics );
	jQuery( ".download_histo" ).click( function() {
		download_histo(
			jQuery( this ).closest( ".tr_histo_row" ).find( ".date_histo" ).val(),
			jQuery( this ).closest( ".tr_histo_row" ).find( ".amount_histo" ).val(),
			decodeURIComponent( jQuery( this ).closest( ".tr_histo_row" ).find( ".payments_dl" ).val() )
		);
	} );
	function download_histo( date, amount, payments_dl ) {
		redirect_post(
			templates_url + '?action=wps_bank_deposit&mode=pdf',
			{
				date: date,
				amount: amount,
				payments: payments_dl
			}
		);
	}
	function save_historics( amount, payments_histo ) {
		now = new Date();
		jQuery.post(
			ajaxurl + '?action=wps_bank_deposit', { action: "save_historic_query", date: now.getFullYear() + '-' + ( now.getMonth() + 1 ) + '-' + now.getDate() + ' ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds(), amount: amount, payments: payments_histo, }, function( response ) {
				array_display_histo( response );
			}, "json"
		);
		download_histo( now, amount, JSON.stringify( payments_histo ) );
	}
	function array_display_histo( saves ) {
		jQuery( ".tr_histo_row" ).remove();
		count = 0;
		jQuery.each( saves, function( index, element ) {
			if( count >= 10 && !more_histo ) { return false; }
			tr_model = jQuery( '#model_histo_row' );
			clone = tr_model.clone();
			tr_model.before( clone );
			clone.removeAttr( "id" );
			clone.addClass( "tr_histo_row" );
			clone.html( clone.html().replace( /%id%/g, element.id ) );
			clone.html( clone.html().replace( /%date%/g, element.date ) );
			clone.html( clone.html().replace( /%amount%/g, element.amount ) );
			clone.html( clone.html().replace( /%payments%/g, encodeURIComponent( JSON.stringify( element.payments ) ) ) );
			count += 1;
		} );
		if( count == 0 ) {
			jQuery( '#model_histo_no_results' ).show();
		} else {
			jQuery( '#model_histo_no_results' ).hide();
		}
	}
	function redirect_post(url, params) {
	    var f = jQuery("<form target='_blank' method='POST' style='display:none;'></form>").attr({
	        action: url
	    }).appendTo(document.body);
	    for (var i in params) {
	        if (params.hasOwnProperty(i)) {
	        	jQuery('<input type="hidden" />').attr({
	                name: i,
	                value: params[i]
	            }).appendTo(f);
	        }
	    }
	    f.submit();
	    f.remove();
	}
} );
