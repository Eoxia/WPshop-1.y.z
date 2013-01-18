// START RICH TEXT EDIT
function kwsTriggerSave() {
	var rich = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden();
	if (rich) {
		ed = tinyMCE.activeEditor;
		if ( 'mce_fullscreen' == ed.id || 'wp_mce_fullscreen' == ed.id ) {
			tinyMCE.get(0).setContent(ed.getContent({format : 'raw'}), {format : 'raw'});
		}
		tinyMCE.triggerSave();
	}
}


//Return the basename like php function
function basename(path) {
	return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

/*	Function allowing to set order as completed	*/
function mark_order_as_completed(element, oid){
	// Display loading...
	element.addClass('loading');

	var data = {
		action: "change_order_state",
		wpshop_ajax_nonce: jQuery("#input_wpshop_change_order_state").val(),
		order_id: oid,
		order_state: 'completed'
	};
	jQuery.post(ajaxurl, data, function(response) {
		if ( response[0] ) {
			jQuery('mark#order_status_'+oid).hide().html(data[2]).fadeIn(500);
			jQuery('mark#order_status_'+oid).attr('class', data[1]);
			// Hide loading and replace button!
			element.attr('class', 'button markAsShipped order_'+oid).html(data['new_button_title']);
			window.top.location.href = WPSHOP_ADMIN_URL + "post.php?post=" + oid + "&action=edit";
		}
		else {
			alert( response[1] );
		}
	}, 'json');
}

/*	Function allowing to display form for customer address into admin order interface	*/
function display_customer_address_form ( customer_id ) {
	var data = {
		action: "order_customer_adress_load",
		wpshop_ajax_nonce: jQuery("#input_wpshop_order_customer_adress_load").val(),
		"customer_id":customer_id
	};
	jQuery.post(ajaxurl, data, function(response) {
		if ( response[0] ) {
			jQuery("#wpshop_customer_id").val(response[2]);
			jQuery("#customer_address_form").empty();
			jQuery("#customer_address_form").html(response[1]);
		}
	}, "json");
}

/*	Function allowing to scroll a page automatically	*/
function wpshop_go_to(ancre){
	var speed = 1000;
	jQuery("html,body").animate({scrollTop:jQuery(ancre).offset().top},speed,"swing",function(){
		if(ancre != "body")
				window.location.hash = ancre;
		else
				window.location.hash = "#";
		jQuery(ancre).attr("tabindex","-1");
		jQuery(ancre).focus();
		jQuery(ancre).removeAttr("tabindex");
	});
}

function calcul_price_from_ET(){
	var ht_amount = jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_HT).val().replace(",", ".");
	if ( jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_SPECIAL_PRICE).val() != 'undefined') {
		//ht_amount = jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_SPECIAL_PRICE).val().replace(",", ".");
	}

	var value_tx = jQuery("#wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX + "_value_" + jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX).val()).val();

	var tax_rate = 1 + (value_tx / 100);

	var ttc_amount = ht_amount * tax_rate;
	jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TTC).val(ttc_amount.toFixed(5));
	var tva_amount = ttc_amount - ht_amount;
	jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX_AMOUNT).val(tva_amount.toFixed(5));
}

function calcul_price_from_ATI(){
	var ttc_amount = jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TTC).val().replace(",", ".");
	if ( jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_SPECIAL_PRICE).val() != 'undefined') {
		//var ttc_amount = jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_SPECIAL_PRICE).val().replace(",", ".");
	}

	var value_tx = jQuery("#wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX + "_value_" + jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX).val()).val();

	var tax_rate = 1 + (value_tx / 100);

	var ht_amount = ttc_amount / tax_rate;
	jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_HT).val(ht_amount.toFixed(5));
	var tva_amount = ttc_amount - ht_amount;
	jQuery(".wpshop_form_input_element .wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX_AMOUNT).val(tva_amount.toFixed(5));
}

function animate_container(container, sub_container) {
	jQuery(sub_container, container).animate({opacity:0.3},500);

	jQuery('#wpshop_loading').fadeIn('slow');
	
	var offset = jQuery(container).offset();
	var bottom_visible_block = offset.top + jQuery(container).height();
	
	var top = jQuery(window).scrollTop() - offset.top + (bottom_visible_block-jQuery(window).scrollTop())/2 - 16;
	if(offset.top > jQuery(window).scrollTop())
		top = (jQuery(window).scrollTop()+jQuery(window).height()-offset.top)/2-16;		
	
	jQuery('#wpshop_loading').css({left:(jQuery(container).width()/2-16)+'px',top:top+'px'}).animate({'top':top});
}
function desanimate_container(container) {
	jQuery('#wpshop_loading').fadeOut('slow');
	jQuery(container).animate({opacity:1},500);
}


/**
*	Function for showing a message on a page after an action
*
*	@param string message The message to add to the page
*
*/
function wpshopShowMessage(message){
	wpshop("#wpshopMessage").addClass("wpshopPageMessage_Updated");
	wpshop("#wpshopMessage").html(wpshopConvertAccentTojs(message));
}
/**
*	Function for hidding the message on the page after an action
*
*	@param string timeToWaitForHiding The time the counter will take before hiding and emptying the page message
*
*/
function hideShowMessage(timeToWaitForHiding){
	setTimeout(function(){
		wpshop("#wpshopMessage").removeClass("wpshopPageMessage_Updated");
		wpshop("#wpshopMessage").html("");
	}, timeToWaitForHiding);
}

/**
*	Contains different function for the common interface into the plugin
*
*	@param string currentType The type of element we want to delete to determin wich form we have to submit
*	@param string returnAlertMessage The message showed to the user before changing page if he click on the return button and that there are changes on the page
*	@param string deleteElementMessage The message showed to the user before submitting the form
*
*/
function wpshopMainInterface(currentType, returnAlertMessage, deleteElementMessage){
	(function(){
		/*	Change the interface layout by adding tabs for navigation	*/
		jQuery("#wpshopFormManagementContainer").tabs();

		/*	Add an indicator on the page for usert alert when changing something in the page and clicking on return button	*/
		jQuery("#" + currentType + "_form input, #" + currentType + "_form textarea").keypress(function(){
			jQuery("#" + currentType + "_form_has_modification").val("yes");
		});
		jQuery("#" + currentType + "_form select").change(function(){
			jQuery("#" + currentType + "_form_has_modification").val("yes");
		});

		/*	Action when clicking on the delete button	*/
		jQuery("#delete").click(function(){
			jQuery("#" + currentType + "_action").val("delete");
			deleteElement(currentType, deleteElementMessage);
		});
		if(jQuery("#" + currentType + "_action").val() == "delete"){
			deleteElement(currentType, deleteElementMessage);
		}

		/*	Action when clicking on the save/add/saveandcontinue button	*/
		jQuery("#save, #add").click(function(){
			jQuery("#" + currentType + "_form").submit();
		});
		jQuery("#saveandcontinue").click(function(){
			jQuery("#" + currentType + "_form").attr("action", jQuery("#" + currentType + "_form").attr("action") + jQuery("#wpshopFormManagementContainer li.ui-tabs-selected a").attr("href"));
			jQuery("#" + currentType + "_action").val(jQuery("#" + currentType + "_action").val() + "andcontinue");
			jQuery("#" + currentType + "_form").submit();
		});

		/*	When clicking on return button show an alert message to the user to prevent that something has been changed into the page	*/
		jQuery(".cancelButton").click(function(){
			if((jQuery("#" + currentType + "_form_has_modification").val() == "yes")){
				if(!confirm(wpshopConvertAccentTojs(returnAlertMessage))){
					return false;
				}
			}
		});
	})(wpshop);
}

/**
*	When clicking on submit button or link, Ask the question to the user if he is sure that he want to delete the current element if not, stay on the current page in edit mode
*
*	@param string currentType The type of element we want to delete to determin wich form we have to submit
*	@param string deleteElementMessage The message showed to the user before submitting the form
*
*/
function deleteElement(currentType, deleteElementMessage){
	if(confirm(wpshopConvertAccentTojs(deleteElementMessage))){
		wpshop("#" + currentType + "_form").submit();
	}
	else{
		wpshop("#" + currentType + "_action").val("edit");
	}
}

/**
*	Allows to convert html special chars to normal chars in javascript messages
*
*	@param string text The text we want to change html special chars into normal chars
*
*/
function wpshopConvertAccentTojs(text){
	text = text.replace(/&Agrave;/g, "\300");
	text = text.replace(/&Aacute;/g, "\301");
	text = text.replace(/&Acirc;/g, "\302");
	text = text.replace(/&Atilde;/g, "\303");
	text = text.replace(/&Auml;/g, "\304");
	text = text.replace(/&Aring;/g, "\305");
	text = text.replace(/&AElig;/g, "\306");
	text = text.replace(/&Ccedil;/g, "\307");
	text = text.replace(/&Egrave;/g, "\310");
	text = text.replace(/&Eacute;/g, "\311");
	text = text.replace(/&Ecirc;/g, "\312");
	text = text.replace(/&Euml;/g, "\313");
	text = text.replace(/&Igrave;/g, "\314");
	text = text.replace(/&Iacute;/g, "\315");
	text = text.replace(/&Icirc;/g, "\316");
	text = text.replace(/&Iuml;/g, "\317");
	text = text.replace(/&Eth;/g, "\320");
	text = text.replace(/&Ntilde;/g, "\321");
	text = text.replace(/&Ograve;/g, "\322");
	text = text.replace(/&Oacute;/g, "\323");
	text = text.replace(/&Ocirc;/g, "\324");
	text = text.replace(/&Otilde;/g, "\325");
	text = text.replace(/&Ouml;/g, "\326");
	text = text.replace(/&Oslash;/g, "\330");
	text = text.replace(/&Ugrave;/g, "\331");
	text = text.replace(/&Uacute;/g, "\332");
	text = text.replace(/&Ucirc;/g, "\333");
	text = text.replace(/&Uuml;/g, "\334");
	text = text.replace(/&Yacute;/g, "\335");
	text = text.replace(/&THORN;/g, "\336");
	text = text.replace(/&Yuml;/g, "\570");
	text = text.replace(/&szlig;/g, "\337");
	text = text.replace(/&agrave;/g, "\340");
	text = text.replace(/&aacute;/g, "\341");
	text = text.replace(/&acirc;/g, "\342");
	text = text.replace(/&atilde;/g, "\343");
	text = text.replace(/&auml;/g, "\344");
	text = text.replace(/&aring;/g, "\345");
	text = text.replace(/&aelig;/g, "\346");
	text = text.replace(/&ccedil;/g, "\347");
	text = text.replace(/&egrave;/g, "\350");
	text = text.replace(/&eacute;/g, "\351");
	text = text.replace(/&ecirc;/g, "\352");
	text = text.replace(/&euml;/g, "\353");
	text = text.replace(/&igrave;/g, "\354");
	text = text.replace(/&iacute;/g, "\355");
	text = text.replace(/&icirc;/g, "\356");
	text = text.replace(/&iuml;/g, "\357");
	text = text.replace(/&eth;/g, "\360");
	text = text.replace(/&ntilde;/g, "\361");
	text = text.replace(/&ograve;/g, "\362");
	text = text.replace(/&oacute;/g, "\363");
	text = text.replace(/&ocirc;/g, "\364");
	text = text.replace(/&otilde;/g, "\365");
	text = text.replace(/&ouml;/g, "\366");
	text = text.replace(/&oslash;/g, "\370");
	text = text.replace(/&ugrave;/g, "\371");
	text = text.replace(/&uacute;/g, "\372");
	text = text.replace(/&ucirc;/g, "\373");
	text = text.replace(/&uuml;/g, "\374");
	text = text.replace(/&yacute;/g, "\375");
	text = text.replace(/&thorn;/g, "\376");
	text = text.replace(/&yuml;/g, "\377");
	text = text.replace(/&oelig;/g, "\523");
	text = text.replace(/&OElig;/g, "\522");
	return text;
}

/**
*	Method used to save the new order in a sortable list
*/
function saveAttibuteState(table){
	wpshop(".newOrder").each(function(){
		currentIdentifier = wpshop(this).attr("id").replace("newOrder", "");
		newOrder = wpshop("#attribute_group_" + currentIdentifier + "_details").sortable("toArray");
		wpshop("#newOrder" + currentIdentifier + "").val(newOrder);
		wpshop("#" + table + "_form_has_modification").val("yes");
	});
}
/**
*	Method to change a basic list into a sortable list
*/
function make_list_sortable(table){
	/*	Make the attribute list into set section sortable	*/
	wpshop(".wpshop_attr_set_section_details").sortable({
		cancel: ".ui-state-disabled",
		placeholder: "ui-state-highlight",
		revert:true,
		forcePlaceholderSize : true,
		tolerance:'intersect',
		connectWith: "ul.wpshop_attr_set_section_details",
		update: function(){
			saveAttibuteState(table);
		}
	}).disableSelection();

	/*	Make the different set section sortable	*/
	wpshop(".attribute_set_group_details").sortable({
		cancel: ".ui-state-disabled",
		placeholder: "ui-state-highlight",
		revert:true,
		forcePlaceholderSize : true,
		tolerance:'intersect',
		update: function(){
			wpshop("#wpshop_attribute_set_section_order").val(wpshop(".attribute_set_group_details").sortable("toArray"));
		}
	});

	/*	Add set section edition action	*/
	jQuery(".wpshop_attr_tool_box_edit").click(function(){
		var check_area = false;
		var current_set_section_id = jQuery(this).closest("li.attribute_set_section_container").attr("id").replace("attribute_group_", "");
		var same_area = false;
		if(jQuery("#wpshop_att_set_section_edition_container_" + current_set_section_id).is(":visible")){
			same_area = true;
		}
		jQuery(".wpshop_att_set_section_edition_container").hide();
		if ( ( !same_area && check_area ) || ( !check_area ) ) {
			jQuery("#wpshop_att_set_section_edition_container_" + current_set_section_id).show();
		}
	});

	/*	When modifying the set section name put the new value into	*/
	jQuery(".wpshop_attribute_set_section_name").live("blur", function(){
		var current_set_section_id = jQuery(this).closest("div.wpshop_att_set_section_edition_container").attr("id").replace("wpshop_att_set_section_edition_container_", "");
		jQuery("#wpshop_attr_set_section_name_"+current_set_section_id).html(jQuery(this).val());
	});
}

function update_order_product_content(order_id, pdt_list_to_delete){
	var product_list_qty_to_update = new Array();
	jQuery("input[name=productQty]").each(function(){
		product_list_qty_to_update.push(jQuery(this).attr("id").replace("wpshop_product_order_", "") + "_x_" + jQuery(this).val());
	});
	jQuery("#order_product_container").load(WPSHOP_AJAX_FILE_URL,{
		"post":"true",
		"elementCode":"ajax_refresh_order",
		"action":"order_product_content",
		"elementIdentifier":order_id,
		"product_to_delete":pdt_list_to_delete,
		"product_to_update_qty":product_list_qty_to_update,
		"order_shipping_cost":jQuery(".wpshop_order_shipping_cost_custom_admin").val()
	});
}


function wpshop_variation_delete( variation_to_delete ) {
	var data = {
		action: "delete_variation",
		current_post_id: variation_to_delete,
		wpshop_ajax_nonce: jQuery("#wpshop_variation_management").val()
	};
	jQuery.post(ajaxurl, data, function(response){
		for( responseis in response ) {
			jQuery("#wpshop_variation_metabox_" + response[responseis]).remove();
		};
		if ( jQuery(".variation_existing_main_container div.wpshop_variation_metabox").length <= 0 ) {
			jQuery(".wpshop_variation_controller").hide();

			var data = {
				action: "wpshop_delete_head_product_variation_def",
				wpshop_ajax_nonce: jQuery("#wpshop_variation_management").val(),
				current_post_id: jQuery("#post_ID").val(),
			};
			jQuery.post(ajaxurl, data, function(response){
				
			});
		}
	}, 'json');
}
function wpshop_create_variation( action ) {
	var checkboxes = [];
	var box_checked = false;
	jQuery(".variation_attribute_usable").each(function() {
		if( jQuery(this).is(':checked') ){
			checkboxes.push(jQuery(this).val());
			box_checked = true;
		}
	});
	if (box_checked) {
		var data = {
			action: action,
			wpshop_attribute_to_use_for_variation: checkboxes,
			current_post_id: jQuery("#post_ID").val(),
			wpshop_ajax_nonce: jQuery("#wpshop_variation_management").val()
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery(".wpshop_variations").html(response);
			jQuery(".variation_attribute_usable").each(function() {
				jQuery(this).prop('checked', false);
			});
			jQuery(".wpshop_admin_variation_combined_dialog").dialog("close");
		});
	}
	else {
		alert( wpshopConvertAccentTojs( WPSHOP_NO_ATTRIBUTES_SELECT_FOR_VARIATION ) );
	}
}
