jQuery(document).ready(function() {
	show_cart_rules_interface();
	// Hide the discount type configuration interface
	show_discount_type_interface('');

	jQuery('#wpshop_cart_rules_option_activate').live('click', function() {
		show_cart_rules_interface();
	});

	jQuery('.wpshop_cart_rules_option_discount_choice').live('click', function() {
		show_discount_type_interface ( jQuery(this).attr('id') );
	});

	/** Save the cart rule action */
	jQuery('#save_cart_rule').live('click', function() {

		var discount_type = jQuery('input[name=wpshop_cart_rules_option_rules_discount_type]:checked').val();
		if ( jQuery('#wpshop_cart_rules_option_cart_limen').val() != null ) {
			if ( discount_type == 'gift_product') {
				var selected_value =  jQuery('#' + discount_type + '_value option:selected').val();
			}
			else {
				var selected_value = jQuery('#' + discount_type + '_value').val();
			}
			var data = {
					action: "save_cart_rule",
					_wpnonce: jQuery( this ).data( 'nonce' ),
					cart_limen : jQuery('#wpshop_cart_rules_option_cart_limen').val(),
					discount_type : discount_type,
					discount_value : selected_value,
					customer_groups : jQuery('#wpshop_cart_rules_option_customer_group option:selected').val(),
					cart_rules : jQuery('#wpshop_cart_rules_data').val()
				};
				jQuery.post(ajaxurl, data, function(response) {
					if ( response['status'] ) {
						jQuery('#wpshop_cart_rules_data').val(response['response']);
						jQuery('#display_all_rules').html(response['display_rules']);
						jQuery('#wpshop_cart_rules_option_cart_limen').val('');
						jQuery('#wpshop_cart_rules_option_cart_limen').val('');
						var checked_button = jQuery('input[name=wpshop_cart_rules_option_rules_discount_type]:checked').attr('id');
						jQuery('#' + checked_button).attr('checked', false);
						show_discount_type_interface('');
					}

				}, 'json');
		}
		else {
			alert('Cart Limen is empty ! ');
		}
	});

	jQuery('.cart_line_delete_rule').live('click', function() {
		var data = {
				action: "delete_cart_rule",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				cart_rule_id : jQuery(this).attr('id'),
				cart_rules : jQuery('#wpshop_cart_rules_data').val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery('#wpshop_cart_rules_data').val(response['response']);
					jQuery('#display_all_rules').html(response['display_rules']);
				}

			}, 'json');
	});

	function show_discount_type_interface ( interface ) {
		jQuery('.cart_rules_discount_interface').slideUp();
		if ( interface != null ) {
			jQuery('#interface_'+interface).slideDown();
		}
	}

	function show_cart_rules_interface () {
		if ( jQuery('#wpshop_cart_rules_option_activate').is(':checked') ) {
			jQuery('#wpshop_cart_rules_interface').show();
		}
		else {
			jQuery('#wpshop_cart_rules_interface').hide();
		}
	}
});
