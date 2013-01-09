/*	Change input type for datetime input	*/
// jQuery(".wpshop_input_datetime").datepicker();
// jQuery(".wpshop_input_datetime").datepicker("option", "dateFormat", "yy-mm-dd");
// jQuery(".wpshop_input_datetime").datepicker("option", "changeMonth", true);
// jQuery(".wpshop_input_datetime").datepicker("option", "changeYear", true);
// jQuery(".wpshop_input_datetime").datepicker("option", "navigationAsDateFormat", true);

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
	/*	Action when user click on the new variation button	*/
	jQuery("#wpshop_dialog_new_variation_button").live('click', function() {
		var checkboxes = [];
		var box_checked = false;
		jQuery(".wpshop_list_of_attribute_for_variation li input[type=checkbox]").each(function() {
			if( jQuery(this).is(':checked') ){
				checkboxes.push(jQuery(this).val());
				box_checked = true;
			}
		});
		if (box_checked) {
			var data = {
				action: "add_new_variation",
				checkboxes: checkboxes,
				current_post_id: jQuery("#post_ID").val(),
				wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_variation_creation"); ?>'
			};
			jQuery.post(ajaxurl, data, function(response){
				jQuery(".wpshop_product_variations").html(response);
				jQuery(".wpshop_list_of_attribute_for_variation li input[type=checkbox]").each(function() {
					jQuery(this).prop('checked', false);
				});
			});
		}
		else {
			alert( wpshopConvertAccentTojs( WPSHOP_NO_ATTRIBUTES_SELECT_FOR_VARIATION ) );
		}
	});
	/*	Action when user ask to delete a variation	*/
	jQuery(".product_variation_button_delete").live('click', function() {
		if( confirm(wpshopConvertAccentTojs("<?php echo __('Are you sure you want to delete this variation?', 'wpshop'); ?>")) ) {
			var data = {
				action: "delete_variation",
				current_post_id: jQuery(this).attr("id").replace('wpshop_variation_delete_', ''),
				wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_delete_variation"); ?>'
			};
			jQuery.post(ajaxurl, data, function(response){
				if(response[0]){
					jQuery("#wpshop_product_variation_metabox_" + response[1]).fadeOut('slow');
				}
				else{
					alert(wpshopConvertAccentToJs( "<?php __('An error occured while deleting selected variation', 'wpshop'); ?>" ));
				}
			}, 'json');
		}
	});
	/*	Action when user want to duplicate a product variation	*/
	jQuery(".product_variation_button_duplicate").live('click', function() {
		var data = {
			action: "duplicate_variation",
			current_post_id: jQuery(this).attr("id").replace('wpshop_variation_duplicate_', ''),
			wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_variation_duplication"); ?>'
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery(".wpshop_product_variations").html(response);
		}, 'json');
	});


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


/*	Manage select list attributes	*/
	/*	Add a box for adding element to select list on the fly	*/
	jQuery("#wpshop_new_attribute_option_value_add").dialog({
		modal: true,
		autoOpen:false,
		show: "blind",
		buttons:{
			"<?php _e('Add', 'wpshop'); ?>": function(){
				var data = {
					action: "new_option_for_select_from_product_edition",
					wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_new_option_for_attribute_creation"); ?>',
					attribute_code: jQuery("#wpshop_attribute_type_select_code").val(),
					attribute_new_label: jQuery("#wpshop_new_attribute_option_value").val(),
					item_in_edition: jQuery("#post_ID").val()
				};
				jQuery.post(ajaxurl, data, function(response) {
					if( response[0] ) {
						var container = "wpshop_product_" + response[2] + "_input";
						jQuery("." + container).html( response[1] );
						jQuery("select.chosen_select").chosen({disable_search_threshold: 5, no_results_text: WPSHOP_CHOSEN_NO_RESULT});
						jQuery("#wpshop_new_attribute_option_value_add").dialog("close");
					}
					else {
						alert( response[1] );
					}
					jQuery("#wpshop_new_attribute_option_value_add").children("img").hide();
					jQuery("#wpshop_attribute_type_select_code").val("");
				}, "json");
	
				jQuery(this).children("img").show();
			},
			"<?php _e('Cancel', 'wpshop'); ?>": function(){
				jQuery(this).dialog("close");
			}
		},
		close:function(){
			jQuery("#wpshop_new_attribute_option_value").val("");
		}
	});
	jQuery(".wpshop_icons_add_new_value_to_option_list").live('click', function(){
		jQuery("#wpshop_attribute_type_select_code").val(jQuery(this).attr("rel"));
		jQuery("#wpshop_new_attribute_option_value_add").dialog("open");
	});


/*	Start product attachment management	*/
	/*	Delete an attachment	*/
	jQuery(".delete_post_thumbnail").live('click',function(){
		if (confirm(WPSHOP_MSG_CONFIRM_THUMBNAIL_DELETION)) {
			var data = {
				action: "delete_product_thumbnail",
				wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_delete_product_thumbnail"); ?>',
				attachement_id: jQuery(this).attr('id').replace('thumbnail_', '')
			}
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
		}
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
		   var classes = jQuery(this).attr('class').split(' ');

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
