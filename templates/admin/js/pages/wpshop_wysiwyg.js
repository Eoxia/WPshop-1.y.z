/*	Load dialog box 	*/
tinyMCEPopup.requireLangPack();
var wpShop_Dialog_Action = {
	init : function() {
		jQuery("select.chosen_select").chosen({disable_search_threshold: 5, no_results_text: WPSHOP_CHOSEN_NO_RESULT});
		/*	Default action when clicking on an element	*/
		jQuery(".wpshop_wysiwyg_shortcode_options, .wpshop_wysiwyg_shortcode_display_option, #selected_content li input[type=checkbox]").live('click', function(){
			if ( jQuery(this).hasClass('wpshop_shortcode_element_attribute') ) {
				if ( jQuery("#wpshop_wysiwyg_shortcode_inserter_type").val() == 'product_by_attribute' ) {
					var attribute_value_select = jQuery("#wpshop_attribute_value_for_shortcode_generation_" + jQuery(this).attr('id').replace('wpshop_attribute_', ''));
				}
				else if ( jQuery(this).hasClass('wpshop_shortcode_element_attribute') ) {
					var attribute_value_select = jQuery(".wpshop_shortcode_element_attribute_value_product_list_" + jQuery(this).attr('id').replace('wpshop_attribute_', '') + '_container');
				}
				
				if ( jQuery(this).is(":checked") ) {
					attribute_value_select.show();
				}
				else {
					attribute_value_select.hide();
				}
			}

			generate_shortcode();
		});

		/**/
		jQuery(".wpshop_shortcode_element_prodcut_listing_per_attribute_value").live('change', function(){
			generate_shortcode();
		});
		/**/
		jQuery(".wpshop_shortcode_element_attribute_value_product_list").live('change', function(){
			generate_shortcode();
		});

		/*	When user click on search button	*/
		jQuery("#search_element_button").live('click', function(){
			display_loading();
			var data = {
				action: "wpshop_element_search",
				wpshop_ajax_nonce: wpshop_wysiwyg_shortcode_insertion_search,
				wpshop_element_searched: jQuery("#search_element_text").val(),
				wpshop_element_type: jQuery("#wpshop_wysiwyg_shortcode_inserter_type").val()
			};
			jQuery.post(WPSHOP_AJAX_FILE_URL, data, function(response) {
				jQuery("#selected_content").html(response);
				hide_loading();
			}, 'json');
		});
		jQuery("#view_all_element_button").live('click', function(){
			display_loading();
			jQuery("#search_element_text").val("");
			var data = {
				action: "wpshop_element_search",
				wpshop_ajax_nonce: wpshop_wysiwyg_shortcode_insertion_search,
				wpshop_element_searched: "",
				wpshop_element_type: jQuery("#wpshop_wysiwyg_shortcode_inserter_type").val()
			};
			jQuery.post(WPSHOP_AJAX_FILE_URL, data, function(response) {
				jQuery("#selected_content").html(response);
				hide_loading();
			}, 'json');
		});


		/**
		 * Add a loading picture and a animation on result container
		 */
		function display_loading() {
			jQuery("#selected_content_container").animate({opacity:0.3},500);
			jQuery('#wpshop_loading').fadeIn('slow');

			var offset = jQuery("#selected_content_container ul").offset();
			var bottom_visible_block = offset.top + jQuery("#selected_content_container").height();

			if(offset.top > jQuery("#selected_content_container").scrollTop()){
				var top = (jQuery("#selected_content_container").scrollTop()+jQuery("#selected_content_container").height()-offset.top)/2-16;
			}
			else{
				var top = jQuery("#selected_content_container").scrollTop() - offset.top + (bottom_visible_block-jQuery("#selected_content_container").scrollTop())/2 - 16;
			}

			jQuery('#wpshop_loading').css({
				left: (jQuery("#selected_content_container ul").width()/2-16)+'px',
				top: top+'px'
			}).animate({'top':top});
		}
		/**
		 * Hide loading picture
		 */
		function hide_loading() {
			jQuery('#wpshop_loading').fadeOut('fast');
			jQuery("#selected_content_container").animate({opacity:1},500);
		}

		/**
		 *	Generate a shortcode from the selection made by the user
		 */
		function generate_shortcode() {
			/*	Check parameter for the shortcode	*/
			var shortcode_main_identifier = jQuery("#wpshop_wysiwyg_shortcode_inserter_shortcode_main_identifier").val();
			var display_type = jQuery("input[name=wpshop_wysiwyg_shortcode_display_type]:checked").val();
			var grouped = false;
			if ( jQuery("#wpshop_wysiwyg_shortcode_group").is(":checked") ) {
				var grouped = true;
			}
			var element_type = jQuery("#wpshop_wysiwyg_shortcode_inserter_type").val();

			var more_shortcode_attribute = 'type="' + display_type + '"';
			if ( element_type === 'categories' ) {
				if ( jQuery("#wpshop_wysiwyg_shortcode_options_categorie_display_product").is(":checked") ) {
					more_shortcode_attribute = more_shortcode_attribute + ' display="' + jQuery("#wpshop_wysiwyg_shortcode_options_categorie_display_product").val() + '"';
				}
			}

			var element_list = '';
			var i = 0;
			jQuery("#selected_content li input[type=checkbox]:checked.wpshop_shortcode_element").each(function() {
				var element_id = jQuery(this).val();

				/*	Specific action for product listing by attribute value	*/
				var specific_shortcode_attribute = '';
				if ( element_type === 'product_by_attribute' ) {
					var attribute_value = jQuery("#wpshop_attribute_value_for_shortcode_generation_" + jQuery(this).attr('id').replace('wpshop_attribute_', '') + " option:selected").val();
					specific_shortcode_attribute = ' att_value="' + attribute_value + '"';
				}
				if ( element_type === 'attribute_value' ) {
					var pid = jQuery("#wpshop_shortcode_element_attribute_value_product_list_" + jQuery(this).attr('id').replace('wpshop_attribute_', '') + " option:selected").val();
					specific_shortcode_attribute = ' pid="' + pid + '"';
					more_shortcode_attribute = '';
				}

				if ( grouped ) {
					element_list += element_id + ",";
				}
				else if ( (more_shortcode_attribute !== '') || (specific_shortcode_attribute !== '') ) {
					element_list += '[' + shortcode_main_identifier + '="' + element_id + '" ' + more_shortcode_attribute + specific_shortcode_attribute + ']';
				}
				i++;
			});

			if ( grouped ) {
				element_list = '[' + shortcode_main_identifier + '="' + element_list.slice(0, -1) + '" ' + more_shortcode_attribute + ']';
			}
			if ( i == 0 ) {
				element_list = '';
			}

			jQuery("#wpshop_created_shortcode").val(element_list);
		}
	},
	insert : function() {
		/*	Insert the generated code into content	*/
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, jQuery('#wpshop_created_shortcode').val());
		tinyMCEPopup.close();
		return false;
	}
};
tinyMCEPopup.onInit.add(wpShop_Dialog_Action.init, wpShop_Dialog_Action);