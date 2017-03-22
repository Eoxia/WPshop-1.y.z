/*	Define the jQuery noConflict var for the plugin	*/
var wpshop = jQuery.noConflict();

//Centre un �l�ment sur la page
jQuery.fn.center = function() {
	this.css( 'top', ( jQuery( window ).height() - this.height() ) / 2 + 'px' );
	this.css( 'left', ( jQuery( window ).width() - this.width() ) / 2 + 'px' );
	return this;
};

jQuery('.single-wpshop_product .single-featured-image-header:has(.attachment-twentyseventeen-featured-image)').css("display","none");

/*	Check all event on page load	*/
wpshop( document ).ready(function() {
	jQuery( '.wpshop_product_container' ).address(function( event ) {

	}).bind( 'change', function( event ) {
		jQuery( '.page-numbers' ).address(function() {

			return jQuery( this ).attr( 'href' ).replace( location.pathname, '' );
		});

		var id = jQuery.address.queryString();
		if ( id != null ) {
			var page_number = id.replace( 'page_product=', '' );
			if ( page_number != '' ) {
				page_number = parseInt( page_number );
				if ( isNaN( page_number ) ) {
					wpshop_get_product_by_criteria_on_load( 1 );
				} else {
					wpshop_get_product_by_criteria_on_load( page_number );
				}
			}
		}
	});

/*
	JQuery(".main_cat_tree_widget").treeview({
		collapsed: true,
		animated: "medium",
		control:"#sidetreecontrol",
		persist: "location"
	});
*/
	//JQuery( ".main_cat_tree_widget" ).menu();
	/*	Add support for zoom on product thumbnail 	*/
	//Wpshop('.wpshop_picture_zoom_in').jqzoom({zoomType: 'reverse'});

	function back2Element( element ) {
		// On remonte en haut de page
		var offset = element.offset();
		jQuery( 'html, body' ).animate({ scrollTop: offset.top }, 800 );
	}

	var options_login = {
		dataType:  'json',
		beforeSubmit: validate_login, // Pre-submit callback
		success: showResponse // Post-submit callback
	};
	// Bind form using 'ajaxForm'
	if ( wpshop( '#login_form' ).length > 0 ) {
		wpshop( '#login_form' ).ajaxForm( options_login );
	}


	/** Manage form in frontend for adding new product	*/
	if ( jQuery( '#new_entity_quick_form' ).length > 0 ) {
		jQuery( '#new_entity_quick_form' ).ajaxForm({
			dataType:  'json',
	        beforeSubmit: function( a, f, o ) {
	        	animate_container( '#new_entity_quick_form', jQuery( '#new_entity_quick_form_container' ) );
	        },
	        success: function( response ) {
	        	desanimate_container( jQuery( '#new_entity_quick_form_container' ) );
	        	if ( response[0] ) {
		            jQuery( '#wpshop_quick_add_entity_result' ).addClass( 'success' );
		            jQuery( '#new_entity_quick_form' )[0].reset();
	        	} else {
		            jQuery( '#wpshop_quick_add_entity_result' ).addClass( 'error' );
	        	}
	        	jQuery( '#wpshop_quick_add_entity_result' ).html( response[1] );
	        	jQuery( '#wpshop_quick_add_entity_result' ).show();
	            setTimeout(function() {
	            	jQuery( '#wpshop_quick_add_entity_result' ).html( '' );
		            jQuery( '#wpshop_quick_add_entity_result' ).slideUp();
		            jQuery( '#wpshop_quick_add_entity_result' ).removeClass( 'success' );
		            jQuery( '#wpshop_quick_add_entity_result' ).removeClass( 'error' );
	            }, 3500 );
	        }
		});
	}

	var options_register = {
		dataType:  'json',
    // BeforeSubmit: validate_register, // pre-submit callback
     success: showResponse // Post-submit callback
 };
 // Bind form using 'ajaxForm'
	if ( wpshop( '#register_form' ).length > 0 ) {
		wpshop( '#register_form' ).ajaxForm( options_register );
	}

	function validate_login( formData, jqForm, options ) {
		for ( var i = 0; i < formData.length; i++ ) {
			if ( ! formData[i].value ) {
				jQuery( '#reponseBox' ).hide().html( '<div class="error_bloc">Please enter a value for both Username/Email and Password</div>' ).fadeIn( 500 );
				return false;
			}
		}
		return true;
	}

	function validate_register( formData, jqForm, options ) {
		var required_fields = ['account_first_name', 'account_last_name', 'account_email', 'account_password_1', 'account_password_2', 'billing_address', 'billing_city', 'billing_postcode', 'billing_country'];
		var required_fields_shipping = ['shipping_first_name', 'shipping_last_name', 'shipping_address', 'shipping_city', 'shipping_postcode', 'shipping_country'];

		// Verif
		for ( var i = 0; i < required_fields.length; i++ ) {
			if ( jQuery( 'input[name=' + required_fields[i] + ']', jqForm).val() == '') {
				jQuery('#reponseBox').hide().html('<div class="error_bloc">'+WPSHOP_REQUIRED_FIELD_ERROR_MESSAGE+'</div>').fadeIn(500);
				back2Element(jQuery('#reponseBox'));
				return false;
			}
		}

		// Si la case est coch� on v�rifie l'adresse de livraison
		if(jQuery('input[name=shiptobilling]',jqForm).prop('checked') == false) {
			for (var i=0; i < required_fields_shipping.length; i++) {
				if(jQuery('input[name='+required_fields_shipping[i]+']',jqForm).val() == '') {
					jQuery('#reponseBox').hide().html('<div class="error_bloc">'+WPSHOP_REQUIRED_FIELD_ERROR_MESSAGE+'</div>').fadeIn(500);
					back2Element(jQuery('#reponseBox'));
					return false;
				}
			}
		}

		// Email valide
		if(!is_email(jQuery('input[name=account_email]',jqForm).val())) {
			jQuery('#reponseBox').hide().html('<div class="error_bloc">'+WPSHOP_INVALID_EMAIL_ERROR_MESSAGE+'</div>').fadeIn(500);
			back2Element(jQuery('#reponseBox'));
			return false;
		}

		// Les mots de passe correspondent?
		if(jQuery('input[name=account_password_1]',jqForm).val() != jQuery('input[name=account_password_2]',jqForm).val()) {
			jQuery('#reponseBox').hide().html('<div class="error_bloc">'+WPSHOP_UNMATCHABLE_PASSWORD_ERROR_MESSAGE+'</div>').fadeIn(500);
			back2Element(jQuery('#reponseBox'));
			return false;
		}

		// Si tout est OK on lance la requete AJAX
		return true;
	}

	function showResponse(responseText, statusText, xhr, $form)  {
		if(responseText['status']) {
			jQuery('#reponseBox').fadeOut('slow');
			if ( responseText['url'] != '' ) {
				window.top.location.href = responseText['url'];
			}
			else {
				window.top.location.href = CURRENT_PAGE_URL;
			}
		}
		else {
			jQuery('#reponseBox').hide().html(responseText['reponse']).fadeIn('slow');
			back2Element(jQuery('#reponseBox'));
		}
	}

	function is_email(email) {
	   var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	   if(reg.test(email) == false) {
		  return false;
	   } else return true;
	}

	if(jQuery("#wpshop_product_feature").length>0) {
		wpshop("#wpshop_product_feature").tabs();
	}

	/*	Define the tools for the widget containing the different categories and products	*/
	wpshop(".wpshop_open_category").click(function(){
		widget_menu_animation(wpshop(this));
	});

	/**
	 * Gestion de l'affichage des images en grand (lightbox)
	 */
	wpshop("a[rel=appendix]").fancybox({
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});
	wpshop("a#product_thumbnail").fancybox({
		'titleShow'     : false
	});

	/*
	 * Add a product into cart.
	 *
	 * If the template is the default a form is present
	 * For older installation support do a test
	 */
	if (jQuery("#wpshop_add_to_cart_form").length > 0) {
		var form_options_add_to_cart = {
			dataType:		'json',
			beforeSubmit: 	function_before_add_to_cart_form_submit,
			success: 		function_after_form_success,
		};
		jQuery('#wpshop_add_to_cart_form').ajaxForm(form_options_add_to_cart);
		jQuery('.wpshop_add_to_cart_button').live("click", function(){
			/*	Affichage d'une indication de chargement	*/
			var id = jQuery( this ).attr( 'id' ).replace( 'wpshop_add_to_cart_', '' );
			if( id == jQuery( '#wpshop_pdt' ).val() ) {
				//var element = jQuery(this).parent();
				jQuery('#wpshop_add_to_cart_form').submit();
			}
			else {
				wpshop_product_add_to_cart( 'cart' , jQuery(this) );
			}
		});

		jQuery('.wpshop_ask_a_quotation_button').live("click", function(){
			/*	Affichage d'une indication de chargement	*/
			var element = jQuery(this).parent();
			jQuery('.wpshop_cart_loading_picture', element).removeClass('success error');
			jQuery('.wpshop_cart_loading_picture', element).css('display', 'inline');
			jQuery('#wpshop_add_to_cart_form input[name=wpshop_cart_type]').val('quotation');
			jQuery('#wpshop_add_to_cart_form').submit();
		});
	}
	else {
		jQuery('.wpshop_add_to_cart_button').live("click", function(){
			wpshop_product_add_to_cart( 'cart' , jQuery(this) );
		});
		jQuery('.wpshop_ask_a_quotation_button').live("click", function(){
			wpshop_product_add_to_cart( 'quotation' , jQuery(this) );
		});
	}

	jQuery("input[name=takeOrder], button[name=takeOrder]").click(function() {
		if ( jQuery("#terms_of_sale").size() > 0 ) {
			if ( !jQuery("#terms_of_sale").is(':checked') ) {
				alert(  WPSHOP_ACCEPT_TERMS_OF_SALE );
				return false;
			}
		}
	});
	/** Variation live display	 */
	var wpshop_display_info_about_value_ajax_request = null;
	jQuery(".wpshop_display_information_about_value").live('change', function(){
		var _wpnonce = jQuery( this ).data( 'nonce' );
		var attribute_for_detail = [];
		jQuery(".wpshop_display_information_about_value").each(function(){
			if ( (jQuery(this).attr("type") == "checkbox") || (jQuery(this).attr("type") == "radio") ) {
				if (jQuery(this).is(":checked")) {
					attribute_for_detail.push( jQuery(this).attr("name").replace("wps_pdt_variations[", "").replace("]", "") + "-_variation_val_-" + jQuery(this).val() );
				}
			}
			else {
				attribute_for_detail.push( jQuery(this).attr("name").replace("wps_pdt_variations[", "").replace("]", "") + "-_variation_val_-" + jQuery(this).val() );
			}
		});

		var data = {
			action:"wpshop_ajax_variation_selection_show_detail_for_value",
			_wpnonce: _wpnonce,
			attribute_for_detail: attribute_for_detail,
		};

		if ( wpshop_display_info_about_value_ajax_request != null ) {
			wpshop_display_info_about_value_ajax_request.abort();
		}
		/**	Launch mini cart with detail reload an price reload	*/
		wpshop_display_info_about_value_ajax_request = jQuery.post(ajaxurl, data, function(response) {
			jQuery(".wpshop_product_variation_value_detail_main_container").html(response);
		});
	});
	jQuery(".wpshop_variation_selector_input, .wpshop_currency_field").live('change', function(){
		load_variation_summary( jQuery( this ).data( 'nonce' ) );
	});
	jQuery(".wpshop_variation_selector_input, .wpshop_currency_field").live('keyup', function(){
		load_variation_summary( jQuery( this ).data( 'nonce' ) );
	});
	jQuery(".wpshop_variation_selector_input, .wpshop_currency_field").live('blur', function(){
		load_variation_summary( jQuery( this ).data( 'nonce' ) );
	});
	// load_variation_summary( jQuery( '#_wpnonce' ).val() );


	/*
	 * Empty complete cart
	 */
	jQuery('.emptyCart').live('click',function() {
		jQuery('#cartContent .remove').each(function() {
			jQuery(this).click();
		});
		return false;
	});

	/**
	 * Application d'un coupon sur un panier
	 */
	jQuery('.submit_coupon').live('click', function() {
		var coupon_code = jQuery('input[name=coupon_code]').val();

		if(coupon_code == '')
			return false;

		jQuery.getJSON(ajaxurl, { action: "wps_cart_action_apply_coupon", coupon_code: coupon_code, _wpnonce: jQuery(this).data('nonce') },
			function(data){
				if(data[0]) {
					reload_cart();
				}
				else {
					alert( data[1] );
				}
			}
		);
		return false;
	});

	// Gestion des crit�res en AJAX
	jQuery('select[name=sorting_criteria]').live('change', function(){
		_this = jQuery(this);
		if(jQuery('option:selected', this).val() != '')
			wpshop_get_product_by_criteria(1, _this);
		return false;
	});
	// Inverse l'ordre des r�sultats
	jQuery('.reverse_sorting').live('click', function(){
		_this = jQuery(this);

		_this.toggleClass('inversed');

		var wpshop_sorting_bloc = _this.closest('.wpshop_products_block').children('.wps-catalog-sorting');

		var order = jQuery('input[name=order]',wpshop_sorting_bloc).val()=='ASC'?'DESC':'ASC';
		// On enregistre la config
		jQuery('input[name=order]',wpshop_sorting_bloc).val(order);
		jQuery('.reverse_sorting',wpshop_sorting_bloc).toggleClass('product_asc_listing');
		wpshop_get_product_by_criteria(1, _this);
		return false;
	});
	// Change de page via la pagination
	jQuery('ul.pagination li a').live("click", function(){
		_this = jQuery(this);
		var page_number = jQuery(this).html();
		// On enregistre la config
		jQuery('input[name=page_number]').val(page_number);
		wpshop_get_product_by_criteria(page_number, _this);
		return false;
	});
	// Passe d'un mode d'affichage � un autre
	jQuery('.change_display_mode').live('click',function(){
		_this = jQuery(this);

		var wpshop_sorting_bloc = _this.closest('.wpshop_products_block').children('.wps-catalog-sorting');


		//_this.closest('.wpshop_products_block').find('.wps-catalog-container').addClass('wps-bloc-loading');


		//display_type = jQuery(this).attr('class').replace('_display','');
		if(jQuery(this).hasClass('list_display')) var display_type='list';
		else var display_type='grid';

		if(jQuery('input[name=display_type]',wpshop_sorting_bloc).val() != display_type){
			// On enregistre la config
			jQuery('input[name=display_type]',wpshop_sorting_bloc).val(display_type);

			jQuery('.list_display',wpshop_sorting_bloc).toggleClass('active');
			jQuery('.grid_display',wpshop_sorting_bloc).toggleClass('active');

			var page_number = jQuery('input[name=page_number]',wpshop_sorting_bloc).val();
			wpshop_get_product_by_criteria(page_number, _this);
		}
		return false;
	});

	jQuery( document).on('keyup', '.wpshop_product_qty_input', function() {
		jQuery('#wpshop_pdt_qty').val( jQuery(this).val() );
	});

	function animate_container(container, sub_container) {
		jQuery(sub_container, container).animate({opacity:0.3},500);

		jQuery('#wpshop_loading').fadeIn('slow');

		var offset = jQuery(container).offset();
		var bottom_visible_block = offset.top + jQuery(container).height();

		if(offset.top > jQuery(window).scrollTop())
			var top = (jQuery(window).scrollTop()+jQuery(window).height()-offset.top)/2-16;
		else
			var top = jQuery(window).scrollTop() - offset.top + (bottom_visible_block-jQuery(window).scrollTop())/2 - 16;

		jQuery('#wpshop_loading').css({left:(jQuery(container).width()/2-16)+'px',top:top+'px'}).animate({'top':top});
	}
	function desanimate_container(container) {
		jQuery('#wpshop_loading').fadeOut('slow');
		jQuery(container).animate({opacity:1},500);
	}

	xhr = null;
	function wpshop_get_product_by_criteria(page_number, eventElement) {
		// Select the block

		eventElement.closest('.wpshop_products_block').find('.wps-catalog-container').addClass('wps-bloc-loading');

		var wpshop_product_container = eventElement.closest('.wpshop_products_block').children('.wps-catalog-container');
		var _wpnonce = wpshop_product_container.data( 'nonce' );
		var wpshop_sorting_bloc = eventElement.closest('.wpshop_products_block').children('.wps-catalog-sorting');

		if(typeof(page_number)=='undefined') {

			var page_number=1;
			jQuery('input[name=page_number]').val(page_number);
		}

		jQuery('ul.pagination li').removeClass('active');
		jQuery('ul.pagination li:nth-child('+page_number+')').addClass('active');

		//animate_container(wpshop_product_container, '.products_listing');

		var criteria = jQuery('.hidden_sorting_criteria_field option:selected',wpshop_sorting_bloc).val();

		var ajax_url = ajaxurl+'?action=wps_products_by_criteria&_wpnonce='+_wpnonce+'&page_number='+page_number+'&criteria='+criteria;
		jQuery('.hidden_sorting_fields',wpshop_sorting_bloc).each(function() {
			ajax_url += '&'+jQuery(this).attr('name')+'='+jQuery(this).val();
		});

		if(xhr != null) xhr.abort();

		xhr = jQuery.getJSON(ajax_url, {}, function(data){
				if(data[0]) {
					// On injecte le nouveau contenu

					jQuery(wpshop_product_container).html(data[1]);

					jQuery(wpshop_product_container).removeClass('wps-bloc-loading');
					//eventElement.closest('.wpshop_products_block').find('.wps-catalog-container').removeClass('wps-bloc-loading');
					//desanimate_container(wpshop_product_container);
					// On remonte en haut de page
					//var offset = wpshop_sorting_bloc.offset();
					//jQuery('html, body').stop(true).animate({ scrollTop: offset.top }, 800);
				}
			}
		);
	}

	function wpshop_get_product_by_criteria_on_load ( page_number ) {
		jQuery('.wpshop_product_container').html( WPSHOP_LOADER_ICON_JQUERY_ADDRESS );
		var _wpnonce = jQuery( '.wpshop_product_container').data( 'nonce' );
		var offset = jQuery('.sorting_bloc').offset();
		jQuery('html, body').stop(true).animate({ scrollTop: offset.top }, 800);
		var criteria = '';
		var ajax_url = ajaxurl+'?action=wps_products_by_criteria&_wpnonce='+_wpnonce+'&page_number='+page_number+'&criteria='+criteria;
		jQuery('.hidden_sorting_fields').each(function() {
			ajax_url += '&'+jQuery(this).attr('name')+'='+jQuery(this).val();
		});

		if(xhr != null) xhr.abort();

		xhr = jQuery.getJSON(ajax_url, {}, function(data){
				if(data[0]) {
					// On injecte le nouveau contenu
					jQuery('.wpshop_product_container').html(data[1]);
					desanimate_container( jQuery('.wpshop_product_container') );
					// On remonte en haut de page
					var offset = jQuery('.sorting_bloc').offset();
					jQuery('html, body').stop(true).animate({ scrollTop: offset.top }, 800);
				}
			}
		);
	}


	// Ferme la boite de dialogue
	jQuery(".closeAlert").live('click', function(){
		jQuery('.wpshop_superBackground').fadeOut('slow', function(){
			jQuery(this).remove();
		});
		jQuery('.wpshop_popupAlert').fadeOut('slow', function(){
			jQuery(this).remove();
		});
	});

	jQuery(document).on( 'click', '.wpshop_superBackground', function() {
		jQuery('.wpshop_superBackground').fadeOut('slow', function(){
			jQuery(this).remove();
		});
		jQuery('.wpshop_popupAlert').fadeOut('slow', function(){
			jQuery(this).remove();
		});
	});

	/* --------------- */
	/* Cart management */
	/* --------------- */

	jQuery('a.recalculate-cart-button').live('click',function(){
		reload_cart();
		return false;
	});

	// @TODO : A vérifier
	// jQuery('a.remove').live('click',function(){
	// 	jQuery(this).addClass('loading');
	// 	var element = jQuery(this).parent().parent();
	// 	var pid = element.attr('id').substr(8);
	// 	updateQty(element, pid, 0, _wpnonce);
	// 	return false;
	// });

	jQuery('input[name=productQty]').live('change',function(){
		var _wpnonce = jQuery( this ).data( 'nonce' );
		var input = jQuery(this);
		var element = input.parent().parent();
		var pid = element.attr('id').substr(8);
		var qty = input.val();
		updateQty(element, pid, qty, _wpnonce);
		return false;
	});

	jQuery('a.productQtyChange').live('click',function(){
		var _wpnonce = jQuery( this ).data( 'nonce' );
		var a = jQuery(this);
		var element = a.parent().parent();
		var input = jQuery('input[name=productQty]',element);
		var pid = element.attr('id').substr(8);
		if( a.hasClass('wpshop_more_product_qty_in_cart') )
			var qty = parseInt(input.val())+1;
		else var qty = parseInt(input.val())-1;
		updateQty(element, pid, qty, _wpnonce);
		return false;
	});

	jQuery('a.checkoutForm_login').click(function(){
		var elementToShow = '';
		var elementToHide = '';
		var infosToShow = '';
		var infosToHide = '';

		if (jQuery('#register').css('display')=='block') {
			elementToShow = '#login';
			elementToHide = '#register';
			infosToShow = '#infos_login';
			infosToHide = '#infos_register';
		}
		else {
			elementToShow = '#register';
			elementToHide = '#login';
			infosToShow = '#infos_register';
			infosToHide = '#infos_login';
		}

		jQuery(infosToShow).show(); jQuery(infosToHide).hide();
		jQuery(elementToHide).fadeOut(250,function(){
			jQuery(elementToShow).fadeIn(250);
		});
		return false;
	});

	jQuery('input[type=checkbox][name=shiptobilling]').click(function(){
		if (jQuery(this).attr('checked')=='checked') {
			jQuery('#shipping_infos_bloc').fadeOut(250);
		}
		else jQuery('#shipping_infos_bloc').fadeIn(250);
	});

	jQuery('table.blockPayment').click(function() {
		jQuery('table.blockPayment').removeClass('active');
		jQuery('table.blockPayment input[type=radio]').attr('checked', false);
		jQuery(this).addClass('active');
		jQuery('input[type=radio]',this).attr('checked', true);
	});



	jQuery(".address_choice_select").live('change', function() {
		var id = jQuery(this).attr('id');
		var address_id = jQuery(this).val();
		jQuery("#choosen_address_"+id).html();
		jQuery("loader_"+id).show();
		var data = {
				action: "change_address",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				"address_type":jQuery(this).attr('id'),
				"address_id":jQuery(this).val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response[0] ) {
					reload_shipping_mode( address_id );
					jQuery("#choosen_address_"+id).html(response[1]);
					jQuery("#edit_link_"+id).html(response[2]);
					jQuery("#hidden_input_"+id).val( address_id );
					if( id == 'shipping_address') {
						reload_cart();
						reload_cart();
					}

					if ( response[3] != null ) {
						jQuery('input[name=takeOrder]').hide();
						jQuery('#wpshop_checkout_payment_buttons').html(response[4]);
					}
					else {
						jQuery('input[name=takeOrder]').show();
					}
				}
				else {
					jQuery("loader_"+id).hide();
					jQuery("#choosen_address_"+id).html();
				}
			}, "json");

	});

	/** Restart an order **/
	jQuery('#restart_order_loader').hide();
	jQuery(document).on( 'click', '#restart_order', function() {

		jQuery('#restart_order_loader').fadeIn();
		var data = {
			action: "restart_the_order",
			_wpnonce: jQuery( this ).data( 'nonce' ),
			order_id : jQuery('#wps_order_id').val()
		};
		jQuery.post( ajaxurl, data, function(response) {
			if ( response['status'] ) {
				jQuery('#restart_order').hide();
				if ( response['add_to_cart_checking'] ) {
					alert( response['add_to_cart_checking_message'] );
				}
				else {
					window.location.replace(response['response']);
				}
				reload_cart();
			}
			else {
				alert( response['response'] );
			}
			jQuery('#restart_order_loader').fadeOut();
		}, 'json');
	});


	/** Make an order again **/
	jQuery(document).on('click', '.make_order_again', function() {
		var id = jQuery(this).attr('id');
		jQuery('#make_order_again_loader_' + id).removeClass('wpshopHide');
		jQuery(this).addClass('wps-bton-loading');
		var data = {
			action: "restart_the_order",
			_wpnonce: jQuery( this ).data( 'nonce' ),
			order_id : id,
			make_order_again : '1'
		};
		jQuery.post(ajaxurl, data, function(response) {
			if ( response['status'] ) {
				jQuery('#make_order_again_loader_' + id).addClass('wpshopHide');
				if ( response['add_to_cart_checking'] ) {
					alert( response['add_to_cart_checking_message'] );
					jQuery('.make_order_again').removeClass('wps-bton-loading');
				}
				else {
					window.location.replace(response['response']);
					jQuery('.make_order_again').removeClass('wps-bton-loading');
				}
				reload_cart();
			}
			else {
				alert( response['response'] );
				jQuery('#make_order_again_loader_' + id).addClass('wpshopHide');
				jQuery('.make_order_again').removeClass('wps-bton-loading');
			}
			jQuery('#restart_order_loader').fadeOut();
		}, 'json');

	});

	if ( jQuery( '.error_bloc' ).length > 0 ) {
		jQuery( '#wpshop_checkout_payment_buttons').hide();
	}

});

/**
* Define the function allowing to display summary about current variation definition *
*/
var wpshop_load_variation_summary = null;
function load_variation_summary( nonce ) {
	var frontend_attribute_variation_selection = [];
	var frontend_attribute_free_variation_selection = [];
	var frontend_currency = null;
	if ( jQuery(".wpshop_currency_field").length > 0) {
		frontend_currency = jQuery(".wpshop_currency_field").val();
	}

	var has_variation_displayed = false;
	jQuery(".wpshop_variation_selector_input").each(function(){
		if ( (jQuery(this).attr("type") == "checkbox") || (jQuery(this).attr("type") == "radio") ) {
			if (jQuery(this).is(":checked")) {
				frontend_attribute_variation_selection.push( jQuery(this).attr("name").replace("wps_pdt_variations[", "").replace("]", "") + "-_variation_val_-" + jQuery(this).val() );
				has_variation_displayed = true;
			}
		}
		else if ( jQuery(this).attr("id").slice(0, 10) == "attribute_" ) {
			frontend_attribute_free_variation_selection.push( jQuery(this).attr("name").replace("wps_pdt_variations[free][", "").replace("]", "") + "-_variation_val_-" + jQuery(this).val() );
			has_variation_displayed = true;
		}
		else {
			frontend_attribute_variation_selection.push( jQuery(this).attr("id").replace("wpshop_variation_attr_", "") + "-_variation_val_-" + jQuery(this).val() );
			has_variation_displayed = true;
		}
	});

	if ( has_variation_displayed ) {
		jQuery(".wpshop_product_price").addClass("wpshop_product_price_loading");
		jQuery(".wpshop_product_price").removeClass("wpshop_product_price_is_loaded");

		jQuery( '.wpshop_product_price' ).addClass( 'wps-bloc-loading' );
		//jQuery(".wpshop_save_money_message").html('');
		var data = {
			action:"wpshop_variation_selection",
			_wpnonce: nonce,
			wpshop_pdt: jQuery("#wpshop_pdt").val(),
			wpshop_variation: frontend_attribute_variation_selection,
			wpshop_free_variation: frontend_attribute_free_variation_selection,
			wpshop_current_for_display: frontend_currency
		};
		if ( wpshop_load_variation_summary != null ) {
			wpshop_load_variation_summary.abort();
		}
		/*	Launch mini cart with detail reload an price reload	*/
		wpshop_load_variation_summary = jQuery.post(ajaxurl, data, function(response) {
			if ( response[0] ) {
				jQuery(".wpshop_product_price.wpshop_product_price_loading").after(response[1]['product_price_output']);
				jQuery(".wpshop_product_price.wpshop_product_price_loading").remove();
				//jQuery(".wpshop_product_price").html(response[1]['product_price_output']);
				jQuery("#wpshop_product_variation_summary_container").html(response[1]['product_output']);

				/** Include image **/
				if( response[1]['wps_product_image']['img_url'] != null ) {
					jQuery( '#product_galery #product_thumbnail' ).html( response[1]['wps_product_image']['img'] );
					jQuery( '#product_galery #product_thumbnail' ).attr( 'href', response[1]['wps_product_image']['img_url'] );
					jQuery( '#wps_product_gallery_' + response[1]['wps_product_image']['img_id'] ).click();
				}

				if ( response[2] ) {
					jQuery('.wpshop_add_to_cart_button').hide();
					jQuery('.wpshop_ask_a_quotation_button').hide();
				}
				else {
					jQuery('.wpshop_add_to_cart_button').show();
					jQuery('.wpshop_ask_a_quotation_button').show();
				}
			}
			jQuery(".wpshop_product_price").removeClass("wpshop_product_price_loading");
			jQuery(".wpshop_product_price").addClass("wpshop_product_price_is_loaded");
			setTimeout(function(){
				jQuery(".wpshop_product_price").removeClass("wpshop_product_price_is_loaded");
			}, '1500');
		}, 'json');
	}
};

/**
* Define the function allowing to open or close the widget menu
*
* @param current_element
*/
function widget_menu_animation(current_element){
	current_category = current_element.attr("id").replace("wpshop_open_category_", "");
	if(current_element.hasClass("wpshop_category_closed")){
		current_element.removeClass("wpshop_category_closed");
		current_element.addClass("wpshop_category_opened");
		wpshop(".wpshop_category_sub_content_" + current_category).slideDown();
	}
	else{
		current_element.removeClass("wpshop_category_opened");
		current_element.addClass("wpshop_category_closed");
		wpshop(".wpshop_category_sub_content_" + current_category).slideUp();
	}
}

/**
* Update Product quatity into customer cart
*
* @param element
* @param pid
* @param qty
*/
function updateQty(element, pid, qty, _wpnonce) {
	qty = qty<0 ? 0 : qty;
	jQuery('input[name=productQty]',element).val(qty);
	jQuery('a.remove',element).addClass('loading');
	var data = {
		action: "wpshop_set_qtyfor_product_into_cart",
		_wpnonce: _wpnonce,
		product_id: pid,
		product_qty: qty,
	};
	jQuery.post(ajaxurl, data, function(response){
		if(response[0]) {
			/**	In case quantity to set is less or equal to null -> remove line from cart	*/
			if( qty <= 0) {
				element.fadeOut(250,function(){element.remove();});
			}
			else {
				jQuery('a.remove',element).removeClass('loading');
			}
			reload_cart();
			var chosen_method = jQuery( 'input[name=wps_shipping_method_choice]:checked' ).attr( 'id' );
			var _wpnonce = jQuery( 'input[name=wps_shipping_method_choice]:checked' ).data( 'nonce' );
			recalculate_shipping_cost( chosen_method, _wpnonce );
		}
		else {
			jQuery('a.remove',element).removeClass('loading');
			/**	Put the old value into product quantity	*/
			jQuery('input[name=productQty]',element).val(jQuery('input[name=currentProductQty]',element).val());
			alert(response[1]);
		}
	}, 'json');
}
/**
* Fonction d'ajout d'un produit dans le panier
*
* @param cart_type
* @param current_element
* @returns {Boolean}
*/
function wpshop_product_add_to_cart( cart_type, current_element ) {
	/*	Définition des actions par défaut (ajout d'un produit au panier)	*/
	var ajax_action = "add_product_to_cart";
	var replacement = "wpshop_add_to_cart_";
	var _wpnonce = jQuery( current_element ).data( 'nonce' );

	jQuery( current_element ).addClass( 'wps-bton-loading' );

	/*	Définition des actions dans le cas d'une demande de devis	*/
	if (cart_type == 'quotation') {
		var replacement = "wpshop_ask_a_quotation_";
		var ajax_action = "add_product_to_quotation";
	}

	/*	Affichage d'une indication de chargement	*/
	var element = current_element.parent();
	jQuery('.wpshop_cart_loading_picture', element).removeClass('success error');
	jQuery('.wpshop_cart_loading_picture', element).css('display', 'inline');

	/*	Récupération de l'identifiant du produit à ajouter au panier/devis	*/
	var pid = current_element.attr("id").replace(replacement, "");

	/*	Lecture de la liste des déclinaisons du produit a ajouter au panier	*/
	var variations = new Array;
	jQuery(".wpshop_variation_selector_input").each(function(){
		var attr_val = jQuery('option:selected',this).val();

		variations.push(attr_val);
	});

	/*	Paramètres pour l'ajout du produit au panier	*/
	var data = {
		action:"wpshop_add_product_to_cart",
		wpshop_pdt: pid,
		_wpnonce: _wpnonce,
		wpshop_pdt_qty: jQuery('.wpshop_product_qty_input').val(),
		wpshop_cart_type: cart_type,
		wpshop_pdt_variation : variations
	};
	/*	Lancement de l'action d'ajout du produit au panier	*/
	jQuery.post(ajaxurl, data, function(response) {
		if ( response[0] ) {
			reload_mini_cart();
			reload_summary_cart();
			reload_wps_cart();
			jQuery( current_element ).removeClass( 'wps-bton-loading' );
			/*	Affichage du statut de la demande à coté du bouton	*/
			jQuery('.wpshop_cart_loading_picture', element).addClass('success');
			setTimeout(function() {
				jQuery('.wpshop_cart_loading_picture', element).fadeOut('slow');
			}, 1500);

			/*	dans le cas d'une demande de devis, on renvoi directement sur la page de confirmation	*/
			if (cart_type == 'quotation') {
				/*	Suppression des boutons de demande de devis	*/
				jQuery(".wpshop_add_to_cart_button").remove();
			}

			/*	Dans le cas d'un ajout au panier, on affiche une boite	*/
			if (cart_type == 'cart') {
				/*	Suppression des boutons de demande de devis	*/
				jQuery(".wpshop_ask_a_quotation_button").remove();
			}

			if ( response[2] ) {
				/*	Redirection vers la page de finalisation de la demande	*/
				document.location = response[3];
				return false;
			}
			else {
				add_to_cart_animation ( response );
			}

			/**	Reload mini cart widget	*/
			if(jQuery('.wpshop_cart_summary').attr("class") != undefined){

				var data = {
					action: "wpshop_reload_mini_cart"
				};
				jQuery('.wpshop_cart_summary').load(ajaxurl, data, function(response){

				});
			}

			/*	Vidange des champs définissant les déclinaisons du produit 	*/
			jQuery(".wpshop_variation_selector_input").each(function(){
				jQuery('option:selected',this).val("");
			});
		}
		else {
			jQuery('.wpshop_cart_loading_picture', element).addClass('error');
			alert(response[1]);
			jQuery( current_element ).removeClass( 'wps-bton-loading' );
		}
	}, 'json');
	return false;
}


function add_to_cart_animation ( response ) {
	if ( (response[5][0] == 'animation') && jQuery('.wpshop_cart_summary').length ) {
		jQuery('body').append('<div class="add_to_cart_product_animation_container"></div>');
		if (jQuery("#product_thumbnail").length) {
			pos = jQuery('#product_thumbnail').offset();
			img_produit = jQuery('#product_thumbnail').html();
		}
		else {
			pos = jQuery('.product_thumbnail_' + response[4]).offset();
			img_produit = jQuery('.product_thumbnail_' + response[4]).html();
		}
		 jQuery('.add_to_cart_product_animation_container').css({'opacity' : 0, 'left' : pos.left, 'top':(pos.top - 200)});
		 var pos_cart = jQuery('.wpshop_cart_summary').offset();
		 jQuery('.add_to_cart_product_animation_container').html(img_produit);
		 jQuery('.add_to_cart_product_animation_container').animate({
			 opacity : 0.8,
			 left : pos_cart.left,
			 top : pos_cart.top
		 }, 1100, function() {
			 jQuery('.add_to_cart_product_animation_container').fadeOut('slow');
			 jQuery('.wpshop_cart_alert').html(response[5][1]);
			 jQuery('.wpshop_cart_alert').fadeIn('slow');
			 setTimeout(function() {
				 jQuery('.wpshop_cart_alert').fadeOut('slow');
			}, 2000);
		 });
	}
	else {

		fill_the_modal( response[5][1], response[7], response[8] );
		// jQuery( '.modal_product_related ul' ).removeClass( 'grid_3' ).addClass( 'grid_6');
		/*	Ajout d'une boite permettant de choisir si on continue la navigation ou si on va vers le panier	*/
		//jQuery('body').append(response[1]);
		//if ( response[6][0]  != null ) {
		//	jQuery('#product_img_dialog_box').html(response[6][0] );
		//}
		//if ( response[6][1]  != null ) {
		//	jQuery('.product_title_dialog_box').html(response[6][1]);
		//}
		//if ( response[6][2] != null ) {
		//	jQuery('#wpshop_add_to_cart_box_related_products').html(response[6][2]);
		//}
		//if ( response[6][3] != null ) {
		//	jQuery('.product_price_dialog_box').html(response[6][3]);
		//}
		//jQuery('.wpshop_superBackground').fadeIn();
		//jQuery('.wpshop_popupAlert').fadeIn();

		/*	Centrage de la boite sur la page	*/
		//jQuery('.wpshop_popupAlert').css("top", (window.innerHeight-jQuery('.wpshop_popupAlert').height())/2+"px");
		//jQuery('.wpshop_popupAlert').css("left", (jQuery(window).width()-jQuery('.wpshop_popupAlert').width())/2+"px");
	}
}



function function_before_add_to_cart_form_submit(formData, jqForm, options) {
	jQuery('.wpshop_cart_loading_picture').removeClass('error success loading');
	jQuery('#wpshop_product_add_to_cart_form_result').remove();
	var form_is_complete = true;

	var button_id = 'wpshop_add_to_cart_' + formData[0].value;
	jQuery( '#' + button_id ).addClass( 'wps-bton-loading' );

	var required_fields = [];
	var highlight_fields = [];
	jQuery( '.attribute_is_required_input' ).each(function( index, element ) {
		required_fields.push( jQuery( element ).attr( 'name' ) );
	});

	for (var i=0; i < formData.length; i++) {
		if( required_fields.indexOf( formData[i].name ) > -1 && ( ( !formData[i].value ) || ( formData[i].value == 0 ) ) ) {
			highlight_fields.push( {element: jQuery('*[name="'+formData[i].name+'"]'), border: jQuery('*[name="'+formData[i].name+'"]').css('border')} );
			form_is_complete = false;
		}
	}

	if ( !form_is_complete ) {
		jQuery('.wpshop_cart_loading_picture').addClass('error');
		jQuery('#wpshop_add_to_cart_form').before( WPSHOP_PRODUCT_VARIATION_REQUIRED_MSG );
		jQuery( '.wpshop_add_to_cart_button' ).removeClass( 'wps-bton-loading' );
		for(var i=0; i < highlight_fields.length; i++) {
			highlight_fields[i].element.css('border', '1px solid red');
		}
		setTimeout(function(){
			for(var i=0; i < highlight_fields.length; i++) {
				jQuery(highlight_fields[i].element).css('border', highlight_fields[i].border);
			}
		}, 3000);
	}

	return form_is_complete;
}

function function_after_form_success(responseText, statusText, xhr, $form) {
	if (responseText[0]) {
		$class_to_put = 'success';
		/*	Affichage du statut de la demande à coté du bouton	*/
		jQuery('.wpshop_cart_loading_picture', jQuery(".wpshop_add_to_cart_button").parent()).addClass($class_to_put);
		setTimeout(function(){
			jQuery('.wpshop_cart_loading_picture', jQuery(".wpshop_add_to_cart_button").parent()).fadeOut('slow');
		}, 1500);

		if ( responseText[2] ) {
			/*	Redirection vers la page de finalisation de la demande	*/
			document.location = responseText[3];
			return false;
		}
		else {
			add_to_cart_animation( responseText );

			reload_mini_cart();
			reload_summary_cart();
			reload_wps_cart();
		}
	}
	else {
		$class_to_put = 'error';
		/*	Affichage du statut de la demande à coté du bouton	*/
		jQuery('.wpshop_cart_loading_picture', jQuery(".wpshop_add_to_cart_button").parent()).addClass($class_to_put);
		alert(responseText[1]);
	}

	jQuery( '.wpshop_add_to_cart_button' ).removeClass( 'wps-bton-loading');
}

/**
* Fonction de rechargement du contenu du panier
*
* @returns {Boolean}
*/

function reload_cart() {
	jQuery('div.cart').animate({opacity:0.4},500);
	jQuery('span#wpshop_loading').css({display:'block',marginLeft:(jQuery('div.cart').width()/2-16)+'px',marginTop:(jQuery('div.cart').height()/2-16)+'px'});

	var data = {
		action: "wpshop_display_cart",
		_wpnonce: jQuery( '.wpshop_cart_summary' ).data( 'nonce' ),
		display_button: jQuery("#wpshop_cart_hide_button_current_state").val(),
	};
	jQuery.post(ajaxurl, data, function(response){
		jQuery('div.cart').html(response).animate({opacity:1},500);
		jQuery('span#wpshop_loading').css('display','none');
	});

	if(jQuery('.wpshop_cart_summary').attr("class") != undefined){
		var data = {
			action: "wpshop_reload_mini_cart",
			_wpnonce: jQuery( '.wpshop_cart_summary' ).data( 'nonce' ),
		};
		jQuery('.wpshop_cart_summary').load(ajaxurl, data, function(response){

		});
	}

	return false;
}




/** Reload Shipping Method **/
function reload_shipping_mode( address_id  ) {
	var data = {
		action: "wps_reload_shipping_mode",
		_wpnonce: jQuery( '#wps_shipping_modes_choice' ).data( 'nonce' ),
		address : address_id
	};
	jQuery.post(ajaxurl, data, function(response){
		if ( response['status'] )  {
			if ( response['allow_order'] ) {
				jQuery('#wps_shipping_modes_choice').fadeOut('slow');
				jQuery('#wps_shipping_modes_choice').html( response['response']);
				jQuery('#wps_shipping_modes_choice').fadeIn( 'slow', function () {
					var chosen_method = jQuery( 'input[name=wps_shipping_method_choice]:checked' ).attr( 'id' );
					var _wpnonce = jQuery( 'input[name=wps_shipping_method_choice]:checked' ).data( 'nonce' );
					recalculate_shipping_cost( chosen_method, _wpnonce );
				} );
				jQuery( '#wpshop_checkout_payment_buttons' ).show();
			}
			else {
				jQuery( '#wpshop_checkout_payment_buttons').hide();
				jQuery('#wps_shipping_modes_choice').fadeOut('slow');
				jQuery('#wps_shipping_modes_choice').html( response['response']);
				jQuery('#wps_shipping_modes_choice').fadeIn( 'slow', function () {
					var chosen_method = jQuery( 'input[name=wps_shipping_method_choice]:checked' ).attr( 'id' );
					var _wpnonce = jQuery( 'input[name=wps_shipping_method_choice]:checked' ).data( 'nonce' );
					recalculate_shipping_cost( chosen_method, _wpnonce );
				} );
			}
		}
		else {
			jQuery( '#wpshop_checkout_payment_buttons').hide();
			jQuery('#wps_shipping_modes_choice').fadeOut( 'slow' );
			jQuery('#wps_shipping_modes_choice').html( response['response']);
			jQuery('#wps_shipping_modes_choice').fadeIn( 'slow' );
		}
	}, 'json');

}


/** Shipping Mode Choice **/
jQuery(document).on('click', 'input[name=wps_shipping_method_choice]', function() {
	var chosen_method = jQuery( this ).attr( 'id' );
	var _wpnonce = jQuery( this ).data( 'nonce' );
	recalculate_shipping_cost( chosen_method, _wpnonce );
});


function recalculate_shipping_cost( chosen_method, _wpnonce ) {
	var data = {
			action: "wps_calculate_shipping_cost",
			_wpnonce: _wpnonce,
			chosen_method : chosen_method
		};
		jQuery.post(ajaxurl, data, function(response){
			if ( response['status'] )  {
				reload_cart();
			}
		}, 'json');
}


function wpshopConvertAccentTojs_front(text){
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
