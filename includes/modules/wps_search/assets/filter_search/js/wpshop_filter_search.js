jQuery(document).ready(function() {

	jQuery(".chzn-select").chosen();
	jQuery(".filter_search_checkbox").attr( 'checked', true );

	// Action on Slider
	jQuery( document ).on( 'mouseup','#wpshop_filter_search_container .wps-slider-ui', function() {
		var id = jQuery( this).attr( 'id' ).replace( 'slider', '' );
		var amount_min = amount_max = '';
		jQuery( '#amount_min' + id ).val( amount_min );
		jQuery( '#amount_max' + id ).val( amount_max );
		make_filter_search_request ();
	});

	// Action on select box
	jQuery('#wpshop_filter_search_container').on('change', '.filter_search_element', function() {
		make_filter_search_request ();
	});

	// Action on Multiple select
	jQuery('#wpshop_filter_search_container').on('change', '.chzn-select', function() {
		make_filter_search_request ();
	});

	// Action on radio & checkbox
	jQuery('#wpshop_filter_search_container').on('click', '.filter_search_radiobox, .filter_search_checkbox', function() {
		make_filter_search_request ();
	});


	/**
	 * Init Filter search fields
	 */
	jQuery(document).on('click', '#init_fields', function() {
		jQuery( '#init_fields' ).addClass( 'wps-bton-loading' );

		// Init Select & multiple selects
		jQuery('#filter_search_action select').each( function() {
			jQuery( this ).removeAttr('selected');
			var id = jQuery(this).attr('id');
			jQuery("#" + id).val("all_attribute_values").trigger("liszt:updated");
		});

		// Init Checkboxes & radioboxes
		jQuery( '.filter_search_radiobox, filter_search_checkbox' ).attr( 'checked', true );

		jQuery( '#init_fields' ).removeClass( 'wps-bton-loading' );

		// Action to search
		make_filter_search_request ();
	});

	/**
	 * Function which do the search
	 */
	function make_filter_search_request () {
		jQuery('#filter_search_action').ajaxForm({
			dataType: 'json',
			beforeSubmit : function() {
				//jQuery('.container_product_listing').html('<div class="wpshop_loading_picture"></div>');
				jQuery( '.container_product_listing' ).addClass( 'wps-bloc-loading' );
			},
			success: function(response) {
				jQuery('.wpshop_products_block').html(response['result']);
				jQuery('#wpshop_filter_search_count_products').html( response['products_count'] );
				jQuery( '.container_product_listing' ).removeClass( 'wps-bloc-loading' );
			}
		}
		).submit();
	}


});
