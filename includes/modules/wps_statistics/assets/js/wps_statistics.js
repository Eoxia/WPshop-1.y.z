jQuery( document ).ready(function() {
	jQuery( '.wps_statistics_date' ).datepicker( {
		dateFormat: 'yy-mm-dd'
	} );

	jQuery( document ).on( 'click', '.wps-statistics-quick-links', function( event ) {
		event.preventDefault();

		jQuery( 'input[name=wps_statistics_start_date]' ).val( jQuery( this ).attr( 'data-from' ) );
		jQuery( 'input[name=wps_statistics_end_date]' ).val( jQuery( this ).attr( 'data-to' ) );
		jQuery( '#wps_statistics_date_customizer' ).submit();
	} );

	jQuery( '#wps_statistics_date_customizer' ).submit( function() {
		jQuery( this ).closest( 'div.wps-bloc-loader' ).addClass( 'wps-bloc-loading' );
		jQuery( this ).ajaxSubmit({
			success: function( response, status, xhr, $form ) {
				jQuery( '#wps_statistics_custom_container' ).html( response ).removeClass( 'wps-bloc-loading' );
			}
		});

		return false;
	});

	jQuery.plot(
		jQuery( '#wps_stats_chart' ),
		[ { label: wpsStats.numberOfSales, data: wpsStatsDatas.numberOfSales }, { label: wpsStats.salesAmount, data: wpsStatsDatas.salesAmount, yaxis: 2 } ],
		{
		series: {
			lines: { show: true },
			points: { show: true }
		},
		grid: {
			show: true,
			aboveData: false,
			color: '#545454',
			backgroundColor: '#fff',
			borderWidth: 2,
			borderColor: '#ccc',
			clickable: false,
			hoverable: true,
			markings: weekendAreas
		},
		xaxis: {
			mode: 'time',
			timeformat: '%d %b',
			tickLength: 1,
			minTickSize: [1, 'day']
		},
		yaxes: [ { min: 0, tickSize: 1, tickDecimals: 0 }, { position: 'right', min: 0, tickDecimals: 2 } ],
		colors: ['#21759B', '#ed8432']
	} );

	function showTooltip( x, y, contents ) {
		jQuery( '<div id="tooltip">' + contents + '</div>' ).css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			border: '1px solid #fdd',
			padding: '2px',
			'background-color': '#fee',
			opacity: 0.80
		}).appendTo( 'body' ).fadeIn( 200 );
	}

	var previousPoint = null;
	jQuery( '#wps_stats_chart' ).bind( 'plothover', function( event, pos, item ) {
		if ( item ) {
			if ( previousPoint != item.dataIndex ) {
				previousPoint = item.dataIndex;

				jQuery( '#tooltip' ).remove();

				if ( item.series.label == wpsStats.numberOfSales ) {
					var y = item.datapoint[1];
					showTooltip( item.pageX, item.pageY, y + wpsStats.sales );
				} else {
					var y = item.datapoint[1].toFixed( 2 );
					showTooltip( item.pageX, item.pageY, y + wpsStats.wpshopCurrency );
				}
			}
		} else {
			jQuery( '#tooltip' ).remove();
			previousPoint = null;
		}
	});

	function weekendAreas( axes ) {
		var markings = [];
		var d = new Date( axes.xaxis.min );

		// Go to the first Saturday
		d.setUTCDate( d.getUTCDate() - ( ( d.getUTCDay() + 1 ) % 7 ) )
		d.setUTCSeconds( 0 );
		d.setUTCMinutes( 0 );
		d.setUTCHours( 0 );
		var i = d.getTime();
		do {

			// When we don't set yaxis, the rectangle automatically
			// extends to infinity upwards and downwards
			markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
			i += 7 * 24 * 60 * 60 * 1000;
		} while ( i < axes.xaxis.max );

		return markings;
	}
});
