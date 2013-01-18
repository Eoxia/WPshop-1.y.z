/*	Start custom display management	*/
	jQuery("#wpshop_product_attribute_display_choice").click(function() {
		if ( jQuery(this).is(":checked") ) {
			jQuery("#wpshop_product_attribute_frontend_display_container").hide();
		}
		else {
			jQuery("#wpshop_product_attribute_frontend_display_container").show();
		}
	});


/*	Start variation management	*/
	jQuery(".wpshop_admin_use_attribute_for_single_variation_checkbox").live("change", function(){
		var current_attribute_id = jQuery(this).attr("id").replace("wpshop_admin_use_attribute_for_single_variation_checkbox_", "");
		if ( jQuery(this).is(":checked") ) {
			jQuery(".wpshop_attribute_input_for_variation_" + current_attribute_id).show();
		}
		else {
			jQuery(".wpshop_attribute_input_for_variation_" + current_attribute_id).hide();
		}
	});

	/*	Create a dialog box for new variation creation	*/
	jQuery(".wpshop_admin_variation_single_dialog").dialog({
		modal: true,
		dialogClass: "wpshop_uidialog_box",
		autoOpen:false,
		show: "blind",
		width: 500,
		resizable: false,
		buttons:{
			 "create-single" : {
				text: WPSHOP_ADD_TEXT,
				click: function(){
					var box_checked = false;
					jQuery(".wpshop_admin_use_attribute_for_single_variation_checkbox").each(function(){
						if ( jQuery(this).is(":checked") ) {
							if (( jQuery(".wpshop_product_attribute_" + jQuery(this).attr("id").replace("wpshop_admin_use_attribute_for_single_variation_checkbox_", "")).val() != "" ) && ( jQuery(".wpshop_product_attribute_" + jQuery(this).attr("id").replace("wpshop_admin_use_attribute_for_single_variation_checkbox_", "")).val() != "0" )) {
								box_checked = true;
							}
						}
					});

					if ( box_checked ) {
						jQuery("#wpshop_admin_variation_definition").submit();
					}
					else {
						alert( wpshopConvertAccentTojs( WPSHOP_NO_ATTRIBUTES_SELECT_FOR_VARIATION ) );
					};
					
				},
				class: "button-primary",
			 }
		},
		close: function(){
			jQuery(".wpshop_admin_variation_single_dialog").html("");
		},
	});
	jQuery("#wpshop_new_variation_single_button").live('click', function(){
		jQuery(".wpshop_admin_variation_single_dialog").html(jQuery(".wpshop_loading_").html());
		var data = {
			action: "new_single_variation_definition",
			wpshop_ajax_nonce: jQuery("#wpshop_variation_management").val(),
			current_post_id: jQuery("#post_ID").val(),
		};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery(".wpshop_admin_variation_single_dialog").html(response);
		});
		jQuery(".wpshop_admin_variation_single_dialog").dialog("open");
	});


	/*	Create a dialog box for new variation creation	*/
	jQuery(".wpshop_admin_variation_combined_dialog").dialog({
		modal: true,
		dialogClass: "wpshop_uidialog_box",
		autoOpen:false,
		show: "blind",
		resizable: false,
		width: 500,
		height: 250,
		buttons:{
			 "create-combined" : {
				text: WPSHOP_CREATE_TEXT,
				click: function(){
		        	animate_container('.wpshop_admin_variation_combined_dialog', jQuery(".wpshop_admin_variation_combined_dialog"));
					wpshop_create_variation("add_new_variation_list");
					desanimate_container(jQuery(".wpshop_admin_variation_combined_dialog"));
				},
				class: "button-primary",
			 }
		},
		close: function(){
			jQuery(".wpshop_admin_variation_combined_dialog").html("");
		},
	});
	/*	Action when user click on the new variation button	*/
	jQuery("#wpshop_new_variation_list_button").live('click', function() {
		jQuery(".wpshop_admin_variation_combined_dialog").html(jQuery(".wpshop_loading_").html());
		var data = {
			action: "new_combined_variation_list_definition",
			wpshop_ajax_nonce: jQuery("#wpshop_variation_management").val(),
			current_post_id: jQuery("#post_ID").val(),
		};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery(".wpshop_admin_variation_combined_dialog").html(response);
		});
		jQuery(".wpshop_admin_variation_combined_dialog").dialog("open");
	});

	/*	Action when user ask to delete a variation	*/
	jQuery(".wpshop_variation_button_delete").live('click', function() {
		if( confirm(wpshopConvertAccentTojs("<?php echo __('Are you sure you want to delete this variation?', 'wpshop'); ?>")) ) {
			var variation_to_delete = new Array();
			variation_to_delete.push( jQuery(this).attr("id").replace('wpshop_variation_delete_', '') );
			wpshop_variation_delete ( variation_to_delete );
			return false;
		}
	});
	jQuery("#wpshop_admin_variation_mass_delete_button").live('click', function(){
		if( confirm(wpshopConvertAccentTojs("<?php echo __('Are you sure you want to delete all selected variation?', 'wpshop'); ?>")) ) {
			var variation_to_delete = new Array();
			jQuery(".wpshop_variation_mass_select_input").each(function() {
				if ( jQuery(this).is(":checked") ) {
					variation_to_delete.push( jQuery(this).val() );
				}
			});
			wpshop_variation_delete ( variation_to_delete );
			return false;
		}
	});
	
	/*	Create a dialog box for new variation creation	*/
	jQuery(".wpshop_admin_variation_parameter_dialog").dialog({
		modal: true,
		dialogClass: "wpshop_uidialog_box",
		autoOpen:false,
		show: "blind",
		resizable: false,
		width: 600,
		buttons:{
			"option-save" : {
				text: WPSHOP_SAVE_PRODUCT_OPTIONS_PARAMS,
				click: function(){
					jQuery("#wpshop_variation_parameter_form").submit();
				},
				class: "button-primary",
			}
		},
		close: function(){
			jQuery(".wpshop_admin_variation_parameter_dialog").html("");
		},
	});
	jQuery("#wpshop_variation_parameters_button").live('click', function(){
		jQuery(".wpshop_variation_parameters_button").html(jQuery(".wpshop_loading_").html());
		var data = {
			action: "admin_variation_parameters",
			wpshop_ajax_nonce: jQuery("#wpshop_variation_management").val(),
			current_post_id: jQuery("#post_ID").val(),
		};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery(".wpshop_admin_variation_parameter_dialog").html(response);
		});
		jQuery(".wpshop_admin_variation_parameter_dialog").dialog("open");
	});

	/*	Allows variation order by drag and drop	*/
	//	jQuery(".variation_existing_main_container").sortable();

	/*	Variation details viewing	*/
	jQuery(".wpshop_variation_metabox .wpshop_variation_metabox_row").live('click', function() {
		var current_variation_id = jQuery(this).parent().attr("id").replace("wpshop_variation_metabox_", "");
		jQuery(".wpshop_variation_def_details").stop(true).slideUp(200);
		jQuery(".wpshop_variation_metabox").removeClass('wpshop_current_variation');
		jQuery("#wpshop_variation_def_details_" + current_variation_id).stop(true).slideToggle(200);
		setTimeout(function(){
			if ( jQuery("#wpshop_variation_def_details_" + current_variation_id).is(":visible") ) {
				jQuery("#wpshop_variation_def_details_" + current_variation_id).parent().addClass('wpshop_current_variation');
			}
		}, 500);
	});

	/* Select / Deselect all existing variation	*/
	jQuery("#wpshop_variation_list_selection_controller").live("click", function(){
		var state = true;
		if (jQuery(this).is(":checked")) {
			state = true;
			jQuery(".wpshop_variation_metabox_col_close").addClass('wpshop_variation_metabox_col_close_current');
		}
		else {
			state = false;
			jQuery(".wpshop_variation_metabox_col_close").removeClass('wpshop_variation_metabox_col_close_current');
		}
		jQuery(".wpshop_variation_mass_select_input").each(function() {
			jQuery(this).prop("checked", state);
		});
	});
	jQuery(".wpshop_variation_mass_select_input").live("click", function(){
		if (jQuery(this).is(":checked")) {
			jQuery(".wpshop_variation_metabox_col_close").addClass('wpshop_variation_metabox_col_close_current');
		}
		else{
			jQuery("#wpshop_variation_list_selection_controller").prop("checked", false);
			var no_checked = false;
			jQuery(".wpshop_variation_mass_select_input").each(function() {
				if (jQuery(this).is(":checked")) {
					no_checked = true;
				}
			});
			if (!no_checked) {
				jQuery(".wpshop_variation_metabox_col_close").removeClass('wpshop_variation_metabox_col_close_current');
			}
		}
	});
/*	End variation management	*/



	/*	Action for product duplication	*/
	jQuery(".wpshop_product_duplication_button").click(function() {
		var product_id = jQuery(this).attr("id").replace("wpshop_product_id_", "");

		/*	Display loading picture	*/
		jQuery("#wpshop_loading_duplicate_pdt_" + product_id).removeClass('success error');
		jQuery("#wpshop_loading_duplicate_pdt_" + product_id).show();

		/*	Launch ajax request	*/
		var data = {
			action: "duplicate_product",
			current_post_id: product_id,
			wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_product_duplication"); ?>'
		};
		jQuery.post(ajaxurl, data, function(response){
			if ( response[0] ) {
				jQuery("#wpshop_loading_duplicate_pdt_" + product_id).addClass('success');
				jQuery("#wpshop_loading_duplicate_pdt_" + product_id).after(response[1]);
			}
			else {
				jQuery("#wpshop_loading_duplicate_pdt_" + product_id).addClass('error');
			}
		}, 'json');

		return false;
	});


/*	Start product attachment management	*/
	/*	Delete an attachment	*/
	jQuery(".delete_post_thumbnail").live('click',function(){
		if (confirm(WPSHOP_MSG_CONFIRM_THUMBNAIL_DELETION)) {
			var data = {
				action: "delete_product_thumbnail",
				wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_delete_product_thumbnail"); ?>',
				attachement_id: jQuery(this).attr('id').replace('thumbnail_', '')
			};
			jQuery.post(ajaxurl, data, function(response){
				if (response[0]) {
					jQuery("#thumbnail_" + response[1]).parent('li').fadeOut('slow');
				}
				else {
					alert(wpshopConvertAccentTojs("<?php _e('An error occured while deleting attachement', 'wpshop'); ?>"));
				}
			}, 'json');
		}
	});
	/*	Reload the attachment container 	*/
	jQuery(".reload_box_attachment img").live('click', function(){
		jQuery(this).attr("src", "<?php echo WPSHOP_LOADING_ICON; ?>");
		var data = {
			action: "reload_product_attachment",
			part_to_reload: jQuery(this).attr("id"),
			current_post_id: jQuery("#post_ID").val(),
			wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_reload_product_attachment_part"); ?>'
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery(".product_attachment_list_" + response[1].replace('reload_', '')).html(response[0]);
			jQuery("#" + response[1]).attr("src", "<?php echo WPSHOP_MEDIAS_ICON_URL . 'reload_vs.png'; ?>");
		}, 'json');
	});

/*	Manage options for a file input	*/
	jQuery('.wpshop_form_input_element select').change(function() {
		var myclass = jQuery(this).attr('name').split('[');
		myclass = myclass[2].slice(0,-1);

		/*	Check if value is realy set to "yes/true"	*/
		if(jQuery('option:selected',this).val() && (jQuery('option:selected',this).val().toLowerCase() == 'yes')) {
			jQuery('.attribute_option_'+myclass).show();
		} else jQuery('.attribute_option_'+myclass).hide();
	});
	

/*	Update product information from bulk edition	*/
	jQuery( '#bulk_edit' ).live('click', function() {
	   var $bulk_row = jQuery( '#bulk-edit' );

	   /*	Read the post id list 	*/
	   var $post_ids = new Array();
	   $bulk_row.find( '#bulk-titles' ).children().each( function() {
		   $post_ids.push( jQuery( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
	   });

	   /*	Read the different field to save 	*/
	   var data_to_save = new Array();
	   $bulk_row.find( '.wpshop_bulk_and_quick_edit_input' ).each( function() {
		   data_to_save.push( jQuery(this).attr( 'name' ) + '_-val-_' + jQuery(this).val() );
	   });

	   /*	Read the different post	*/
		var data = {
			action: "product_bulk_edit_save",
			post_ids: $post_ids,
			attribute: data_to_save,
			wpshop_ajax_nonce: '<?php echo wp_create_nonce("product_bulk_edit_save"); ?>'
		};
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			async: false,
			cache: false,
			data: data
		});
	});
