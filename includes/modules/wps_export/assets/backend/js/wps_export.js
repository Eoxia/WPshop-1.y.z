jQuery(document).ready( function($) {

    $('.datepicker').datepicker({
    	dateFormat : 'yy-mm-dd'
    });
	$( "#wps_export_list" ).change(function show_groups() {
		export_list = $( "#wps_export_list" ).val();
		$( "#wps_export_dates_group" ).hide();
		$( "#wps_export_minp_group" ).hide();
		switch(export_list) {
			case 'orders_date':
			case 'users_date':
				$( "#wps_export_dates_group" ).show();
				$( "#wps_export_download_btn" ).show();
				break;
			case 'users_orders':
				$( "#wps_export_minp_group" ).show();
				$( "#wps_export_download_btn" ).show();
				break;
		}
	});

	$( "#wps_export_download_btn" ).click( function() {
		redirect = true;
		$( "#wps_export_download_btn" ).switchClass('wps-bton-first-mini-rounded', 'wps-bton-first-mini-rounded-loading');
		export_list = $( "#wps_export_list" ).val();
		switch(export_list) {
			case 'users_all':
				url = 'users=users_all';
				break;
			case 'customers_all':
				url = 'users=customers_all';
				break;
			case 'users_newsletters_site':
				url = 'users=newsletters_site';
				break;
			case 'users_newsletters_site_partners':
				url = 'users=newsletters_site_partners';
				break;
			case 'users_date':
				bdte = $( "#wps_export_bdte" ).val();
				edte = $( "#wps_export_edte" ).val();
				url = 'users=date&bdte='+bdte+'&edte='+edte;
				break;
			case 'users_orders':
				url = 'users=orders';
				if( $( "#wps_export_minp_forder" ).is(':checked') ) {
					url += '&free_order=yes'
				} else {
					min_p = $( "#wps_export_minp" ).val();
					url += '&minp='+min_p;
				}
				break;
			case 'orders_date':
				bdte = $( "#wps_export_bdte" ).val();
				edte = $( "#wps_export_edte" ).val();
				url = 'orders=date&bdte='+bdte+'&edte='+edte;
				break;
			default:
				redirect = false;
				break;
		}
		if(redirect)
			loc = $(location).attr('href')+'&download_';
			$(location).attr('href', loc+url);
		$( "#wps_export_download_btn" ).delay(1000).switchClass('wps-bton-first-mini-rounded-loading', 'wps-bton-first-mini-rounded');
	});
});
