/*	Define the jQuery noConflict var for the plugin	*/
var wpshop = jQuery.noConflict();

// Centre un �l�ment sur la page
jQuery.fn.center = function () {
	this.css("top", ( jQuery(window).height() - this.height() ) / 2 + "px");
	this.css("left", ( jQuery(window).width() - this.width() ) / 2 + "px");
	return this;
};

/*	Check all event on page load	*/
wpshop(document).ready(function(){
	/*	Add support for zoom on product thumbnail 	*/
	//wpshop('.wpshop_picture_zoom_in').jqzoom({zoomType: 'reverse'});
	
	function back2Element(element) {
		// On remonte en haut de page
		var offset = element.offset();
		jQuery('html, body').animate({ scrollTop: offset.top }, 800);
	}
	
	var options_login = {
		dataType:  'json',
        beforeSubmit: validate_login, // pre-submit callback 
        success: showResponse // post-submit callback
	};
	// bind form using 'ajaxForm' 
	if(wpshop("#login_form").length>0) {
		wpshop('#login_form').ajaxForm(options_login);
	}


	/*
	 * Manage form in frontend for adding new product
	 */
	if ( jQuery("#new_entity_quick_form").length > 0 ) {
		jQuery('#new_entity_quick_form').ajaxForm({
	        beforeSubmit: function(a,f,o) {
	        	animate_container('#new_entity_quick_form', jQuery("#new_entity_quick_form_container"));
	        },
	        success: function(data) {
	        	desanimate_container(jQuery("#new_entity_quick_form_container"));
	            var $out = jQuery('#wpshop_quick_add_entity_result');
	            $out.html(data);
	            jQuery(".wpshop_form_input_element input").each(function(){
					jQuery(this).val("");
				});
	        },
		});
	}

	var options_register = {
		dataType:  'json',
       // beforeSubmit: validate_register, // pre-submit callback 
        success: showResponse // post-submit callback
    }; 
    // bind form using 'ajaxForm' 
	if(wpshop("#register_form").length>0) {
		wpshop('#register_form').ajaxForm(options_register);
	}
	
	function validate_login(formData, jqForm, options) {
		for (var i=0; i < formData.length; i++) { 
			if (!formData[i].value) {
				jQuery('#reponseBox').hide().html('<div class="error_bloc">Please enter a value for both Username/Email and Password</div>').fadeIn(500);
				return false;
			} 
		}
		/*if(!is_email(jQuery('input[name=account_email]',jqForm).val())) {
			jQuery('#reponseBox').hide().html('<div class="error_bloc">Email invalid</div>').fadeIn(500);
			return false;
		}*/
		return true;
	}
	
	function validate_register(formData, jqForm, options) {
		var required_fields = ['account_first_name','account_last_name','account_email','account_password_1','account_password_2','billing_address','billing_city','billing_postcode','billing_country'];
		var required_fields_shipping = ['shipping_first_name','shipping_last_name','shipping_address','shipping_city','shipping_postcode','shipping_country'];
		
		// Verif
		for (var i=0; i < required_fields.length; i++) {
			if(jQuery('input[name='+required_fields[i]+']',jqForm).val() == '') {
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
			jQuery('#reponseBox').fadeOut(500);
			if ( responseText['url'] != '' ) {
				window.top.location.href = responseText['url'];
			}
			else {
				window.top.location.href = CURRENT_PAGE_URL;
			}
		}
		else {
			jQuery('#reponseBox').hide().html(responseText['reponse']).fadeIn(500);
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
			var element = jQuery(this).parent();
			jQuery('.wpshop_cart_loading_picture', element).removeClass('success error');
			jQuery('.wpshop_cart_loading_picture', element).css('display', 'inline');
			jQuery('#wpshop_add_to_cart_form').submit();
		});
	}
	else {
		jQuery('.wpshop_add_to_cart_button').live("click", function(){
			wpshop_product_add_to_cart( 'cart' , jQuery(this) );
		});
	}

	jQuery("input[name=takeOrder]").click(function() {
		if ( jQuery("#terms_of_sale").size() > 0 ) {
			if ( !jQuery("#terms_of_sale").is(':checked') ) {
				alert(  WPSHOP_ACCEPT_TERMS_OF_SALE );
				return false;
			}
		}
	});
	/** Variation live display	 */
	jQuery(".wpshop_display_information_about_value").live('change', function(){
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
			attribute_for_detail: attribute_for_detail,
		};
		/*	Launch mini cart with detail reload an price reload	*/
		jQuery.post(ajaxurl, data, function(response) {
			jQuery(".wpshop_product_variation_value_detail_main_container").html(response);
		});
	});
	jQuery(".wpshop_variation_selector_input, .wpshop_currency_field").live('change', function(){
		load_variation_summary();
	});
	load_variation_summary();
	function load_variation_summary() {
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

			var data = {
				action:"wpshop_variation_selection",
				wpshop_pdt: jQuery("#wpshop_pdt").val(),
				wpshop_variation: frontend_attribute_variation_selection,
				wpshop_free_variation: frontend_attribute_free_variation_selection,
				wpshop_current_for_display: frontend_currency,
			};
			/*	Launch mini cart with detail reload an price reload	*/
			jQuery.post(ajaxurl, data, function(response) {
				if ( response[0] ) {
					jQuery(".wpshop_product_price").html(response[1]['product_price_output']);
					jQuery("#wpshop_product_variation_summary_container").html(response[1]['product_output']);
				}
				jQuery(".wpshop_product_price").removeClass("wpshop_product_price_loading");
				jQuery(".wpshop_product_price").addClass("wpshop_product_price_is_loaded");
				setTimeout(function(){
					jQuery(".wpshop_product_price").removeClass("wpshop_product_price_is_loaded");
				}, '1500');
			}, 'json');
		}
	};

	/*
	 * Ask a quotation on a product page
	 */
	jQuery('.wpshop_ask_a_quotation_button').live("click", function(){
		wpshop_product_add_to_cart( 'quotation' , jQuery(this) );
	});


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
		
		jQuery.getJSON(WPSHOP_AJAX_URL, { post: "true", elementCode: "ajax_cartAction", action: "applyCoupon", coupon_code: coupon_code },
			function(data){
				if(data[0]) {
					reload_cart();
				}
				else {
					alert(data[1]);
				}
			}
		);
		return false;
	});
	
	// Gestion des crit�res en AJAX
	jQuery('select[name=sorting_criteria]').change(function(){
		_this = jQuery(this);
		if(jQuery('option:selected', this).val() != '')
			wpshop_get_product_by_criteria(1, _this);
		return false;
	});
	// Inverse l'ordre des r�sultats
	jQuery('.reverse_sorting').click(function(){
		_this = jQuery(this);
		
		var wpshop_sorting_bloc = _this.closest('.wpshop_products_block').children('.sorting_bloc');
		
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
	jQuery('.change_display_mode').click(function(){
		_this = jQuery(this);
		
		var wpshop_sorting_bloc = _this.closest('.wpshop_products_block').children('.sorting_bloc');

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
		var wpshop_product_container = eventElement.closest('.wpshop_products_block').children('.wpshop_product_container');
		var wpshop_sorting_bloc = eventElement.closest('.wpshop_products_block').children('.sorting_bloc');
		
		if(typeof(page_number)=='undefined') {
			var page_number=1;
			jQuery('input[name=page_number]').val(page_number);
		}
		jQuery('ul.pagination li').removeClass('active');
		jQuery('ul.pagination li:nth-child('+page_number+')').addClass('active');

		animate_container(wpshop_product_container, '.products_listing');

		var criteria = jQuery('.hidden_sorting_criteria_field option:selected',wpshop_sorting_bloc).val();
		
		var ajax_url = WPSHOP_AJAX_URL+'?post=true&elementCode=products_by_criteria&page_number='+page_number+'&criteria='+criteria;
		jQuery('.hidden_sorting_fields',wpshop_sorting_bloc).each(function() {
			ajax_url += '&'+jQuery(this).attr('name')+'='+jQuery(this).val();
		});

		if(xhr != null) xhr.abort();
 
		xhr = jQuery.getJSON(ajax_url, {}, function(data){
				if(data[0]) {
					// On injecte le nouveau contenu
					jQuery(wpshop_product_container).html(data[1]);
					desanimate_container(wpshop_product_container);
					// On remonte en haut de page
					var offset = wpshop_sorting_bloc.offset();
					jQuery('html, body').stop(true).animate({ scrollTop: offset.top }, 800);
				}
			}
		);
	}
	
	// Ferme la boite de dialogue
	jQuery("input.closeAlert").live('click', function(){
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
	
	jQuery('a.remove').live('click',function(){
		jQuery(this).addClass('loading');
		var element = jQuery(this).parent().parent();
		var pid = element.attr('id').substr(8);
		updateQty(element, pid, 0);
		return false;
	});
	
	jQuery('input[name=productQty]').live('change',function(){
		var input = jQuery(this);
		var element = input.parent().parent();
		var pid = element.attr('id').substr(8);
		var qty = input.val();
		updateQty(element, pid, qty);
		return false;
	});
	
	jQuery('a.productQtyChange').live('click',function(){
		var a = jQuery(this);
		var element = a.parent().parent();
		var input = jQuery('input[name=productQty]',element);
		var pid = element.attr('id').substr(8);
		if(a.html()=='+')
			var qty = parseInt(input.val())+1;
		else var qty = parseInt(input.val())-1;
		updateQty(element, pid, qty);
		return false;
	});
	
	jQuery('a.checkoutForm_login').click(function(){
		if(jQuery('#register').css('display')=='block'){
			var elementToShow = '#login';var elementToHide = '#register';
			var infosToShow = '#infos_login';var infosToHide = '#infos_register';
		}
		else {
			var elementToShow = '#register';var elementToHide = '#login';
			var infosToShow = '#infos_register';var infosToHide = '#infos_login';
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

	/*	Allows to fill the installation form without having to type anything	*/
	jQuery(".fill_form_checkout_for_test").click(function(){
		jQuery("input[name=account_first_name]").val("Test firstname");
		jQuery("input[name=account_last_name]").val("Test lastname");
		jQuery("input[name=account_company]").val("Test company");
		jQuery("input[name=account_email]").val("dev@eoxia.com");
		jQuery("input[name=account_password_1]").val("a");
		jQuery("input[name=account_password_2]").val("a");
		jQuery("input[name=billing_address]").val("5 bis rue du pont de lattes");
		jQuery("input[name=billing_postcode]").val("34000");
		jQuery("input[name=billing_city]").val("Montpellier");
		jQuery("input[name=billing_country]").val("France");
	});
});

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
function updateQty(element, pid, qty) {
	qty = qty<0 ? 0 : qty;
	jQuery('input[name=productQty]',element).val(qty);
	jQuery('a.remove',element).addClass('loading');
	var data = {
		action: "wpshop_set_qtyfor_product_into_cart",
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
		wpshop_cart_type: cart_type,
		wpshop_pdt_variation: variations
	};
	/*	Lancement de l'action d'ajout du produit au panier	*/
	jQuery.post(ajaxurl, data, function(response) {
		if ( response[0] ) {
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
				/*	Ajout d'une boite permettant de choisir si on continue la navigation ou si on va vers le panier	*/
				jQuery('body').append(response[1]);
				jQuery('.wpshop_superBackground').fadeIn();
				jQuery('.wpshop_popupAlert').fadeIn();

				/*	Centrage de la boite sur la page	*/
				jQuery('.wpshop_popupAlert').css("top", (jQuery(window).height()-jQuery('.wpshop_popupAlert').height())/2+"px");
				jQuery('.wpshop_popupAlert').css("left", (jQuery(window).width()-jQuery('.wpshop_popupAlert').width())/2+"px");
			}

			/*	Rechargement du widget contenant le mini panier	*/
			if(jQuery('.wpshop_cart_summary').attr("class") != undefined){
				jQuery('.wpshop_cart_summary').load(WPSHOP_AJAX_URL,{
					"post": "true",
					"elementCode": "reload_mini_cart"
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
		}
	}, 'json');
	return false;
}

function function_before_add_to_cart_form_submit(formData, jqForm, options) {
	jQuery('.wpshop_cart_loading_picture').removeClass('error success loading');
	jQuery('#wpshop_product_add_to_cart_form_result').remove();
	var form_is_complete = true;

	for (var i=0; i < formData.length; i++) {
		var element = document.getElementsByName(formData[i].name);

		jQuery(element).parent(".attribute_is_required_container").removeClass("wpshop_variation_required_attribute");
        if ( jQuery(element).hasClass("attribute_is_required_input") ) {
        	if ( (!formData[i].value) || (formData[i].value == 0) ) {
        		form_is_complete = false;
                jQuery(element).parent(".attribute_is_required_container").addClass("wpshop_variation_required_attribute");
            }
        }
    }
	
	/*jQuery(".attribute_is_required_input").each( function(){
		if ( !jQuery(this).is(":checked") ) {
    		form_is_complete = false;
            jQuery(this).parent(".attribute_is_required_container").addClass("wpshop_variation_required_attribute");
		}
	} );*/

	if ( !form_is_complete ) {
		//alert( wpshopConvertAccentTojs_front(WPSHOP_PRODUCT_VARIATION_REQUIRED_MSG) );
		jQuery('.wpshop_cart_loading_picture').addClass('error');
		jQuery('#wpshop_add_to_cart_form').before( WPSHOP_PRODUCT_VARIATION_REQUIRED_MSG );
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
			/*	Ajout d'une boite permettant de choisir si on continue la navigation ou si on va vers le panier	*/
			jQuery('body').append(responseText[1]);
			jQuery('.wpshop_superBackground').fadeIn();
			jQuery('.wpshop_popupAlert').fadeIn();

			/*	Centrage de la boite sur la page	*/
			jQuery('.wpshop_popupAlert').css("top", (jQuery(window).height()-jQuery('.wpshop_popupAlert').height())/2+"px");
			jQuery('.wpshop_popupAlert').css("left", (jQuery(window).width()-jQuery('.wpshop_popupAlert').width())/2+"px");
		}

		//document.location = responseText[2];
	}
	else {
		$class_to_put = 'error';
		/*	Affichage du statut de la demande à coté du bouton	*/
		jQuery('.wpshop_cart_loading_picture', jQuery(".wpshop_add_to_cart_button").parent()).addClass($class_to_put);
		alert(responseText[1]);
	}
}

/**
 * Fonction de rechargement du contenu du panier
 * 
 * @returns {Boolean}
 */
function reload_cart() {
	jQuery('div.cart').animate({opacity:0.4},500);
	jQuery('span#wpshop_loading').css({display:'block',marginLeft:(jQuery('div.cart').width()/2-16)+'px',marginTop:(jQuery('div.cart').height()/2-16)+'px'});
	jQuery.get(WPSHOP_AJAX_URL, { post: "true", elementCode: "ajax_display_cart" },
		function(html){
			jQuery('div.cart').html(html).animate({opacity:1},500);
			jQuery('span#wpshop_loading').css('display','none');
		}
	);
	if(jQuery('.wpshop_cart_summary').attr("class") != undefined){
		jQuery('.wpshop_cart_summary').load(WPSHOP_AJAX_URL,{
			"post": "true",
			"elementCode": "reload_mini_cart"
		});
	}
	return false;
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

/*
 * jQuery UI Effects 1.8.16
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/
 */
jQuery.effects||function(f,j){function m(c){var a;if(c&&c.constructor==Array&&c.length==3)return c;if(a=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(c))return[parseInt(a[1],10),parseInt(a[2],10),parseInt(a[3],10)];if(a=/rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(c))return[parseFloat(a[1])*2.55,parseFloat(a[2])*2.55,parseFloat(a[3])*2.55];if(a=/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(c))return[parseInt(a[1],
16),parseInt(a[2],16),parseInt(a[3],16)];if(a=/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(c))return[parseInt(a[1]+a[1],16),parseInt(a[2]+a[2],16),parseInt(a[3]+a[3],16)];if(/rgba\(0, 0, 0, 0\)/.exec(c))return n.transparent;return n[f.trim(c).toLowerCase()]}function s(c,a){var b;do{b=f.curCSS(c,a);if(b!=""&&b!="transparent"||f.nodeName(c,"body"))break;a="backgroundColor"}while(c=c.parentNode);return m(b)}function o(){var c=document.defaultView?document.defaultView.getComputedStyle(this,null):this.currentStyle,
a={},b,d;if(c&&c.length&&c[0]&&c[c[0]])for(var e=c.length;e--;){b=c[e];if(typeof c[b]=="string"){d=b.replace(/\-(\w)/g,function(g,h){return h.toUpperCase()});a[d]=c[b]}}else for(b in c)if(typeof c[b]==="string")a[b]=c[b];return a}function p(c){var a,b;for(a in c){b=c[a];if(b==null||f.isFunction(b)||a in t||/scrollbar/.test(a)||!/color/i.test(a)&&isNaN(parseFloat(b)))delete c[a]}return c}function u(c,a){var b={_:0},d;for(d in a)if(c[d]!=a[d])b[d]=a[d];return b}function k(c,a,b,d){if(typeof c=="object"){d=
a;b=null;a=c;c=a.effect}if(f.isFunction(a)){d=a;b=null;a={}}if(typeof a=="number"||f.fx.speeds[a]){d=b;b=a;a={}}if(f.isFunction(b)){d=b;b=null}a=a||{};b=b||a.duration;b=f.fx.off?0:typeof b=="number"?b:b in f.fx.speeds?f.fx.speeds[b]:f.fx.speeds._default;d=d||a.complete;return[c,a,b,d]}function l(c){if(!c||typeof c==="number"||f.fx.speeds[c])return true;if(typeof c==="string"&&!f.effects[c])return true;return false}f.effects={};f.each(["backgroundColor","borderBottomColor","borderLeftColor","borderRightColor",
"borderTopColor","borderColor","color","outlineColor"],function(c,a){f.fx.step[a]=function(b){if(!b.colorInit){b.start=s(b.elem,a);b.end=m(b.end);b.colorInit=true}b.elem.style[a]="rgb("+Math.max(Math.min(parseInt(b.pos*(b.end[0]-b.start[0])+b.start[0],10),255),0)+","+Math.max(Math.min(parseInt(b.pos*(b.end[1]-b.start[1])+b.start[1],10),255),0)+","+Math.max(Math.min(parseInt(b.pos*(b.end[2]-b.start[2])+b.start[2],10),255),0)+")"}});var n={aqua:[0,255,255],azure:[240,255,255],beige:[245,245,220],black:[0,
0,0],blue:[0,0,255],brown:[165,42,42],cyan:[0,255,255],darkblue:[0,0,139],darkcyan:[0,139,139],darkgrey:[169,169,169],darkgreen:[0,100,0],darkkhaki:[189,183,107],darkmagenta:[139,0,139],darkolivegreen:[85,107,47],darkorange:[255,140,0],darkorchid:[153,50,204],darkred:[139,0,0],darksalmon:[233,150,122],darkviolet:[148,0,211],fuchsia:[255,0,255],gold:[255,215,0],green:[0,128,0],indigo:[75,0,130],khaki:[240,230,140],lightblue:[173,216,230],lightcyan:[224,255,255],lightgreen:[144,238,144],lightgrey:[211,
211,211],lightpink:[255,182,193],lightyellow:[255,255,224],lime:[0,255,0],magenta:[255,0,255],maroon:[128,0,0],navy:[0,0,128],olive:[128,128,0],orange:[255,165,0],pink:[255,192,203],purple:[128,0,128],violet:[128,0,128],red:[255,0,0],silver:[192,192,192],white:[255,255,255],yellow:[255,255,0],transparent:[255,255,255]},q=["add","remove","toggle"],t={border:1,borderBottom:1,borderColor:1,borderLeft:1,borderRight:1,borderTop:1,borderWidth:1,margin:1,padding:1};f.effects.animateClass=function(c,a,b,
d){if(f.isFunction(b)){d=b;b=null}return this.queue(function(){var e=f(this),g=e.attr("style")||" ",h=p(o.call(this)),r,v=e.attr("class");f.each(q,function(w,i){c[i]&&e[i+"Class"](c[i])});r=p(o.call(this));e.attr("class",v);e.animate(u(h,r),{queue:false,duration:a,easing:b,complete:function(){f.each(q,function(w,i){c[i]&&e[i+"Class"](c[i])});if(typeof e.attr("style")=="object"){e.attr("style").cssText="";e.attr("style").cssText=g}else e.attr("style",g);d&&d.apply(this,arguments);f.dequeue(this)}})})};
f.fn.extend({_addClass:f.fn.addClass,addClass:function(c,a,b,d){return a?f.effects.animateClass.apply(this,[{add:c},a,b,d]):this._addClass(c)},_removeClass:f.fn.removeClass,removeClass:function(c,a,b,d){return a?f.effects.animateClass.apply(this,[{remove:c},a,b,d]):this._removeClass(c)},_toggleClass:f.fn.toggleClass,toggleClass:function(c,a,b,d,e){return typeof a=="boolean"||a===j?b?f.effects.animateClass.apply(this,[a?{add:c}:{remove:c},b,d,e]):this._toggleClass(c,a):f.effects.animateClass.apply(this,
[{toggle:c},a,b,d])},switchClass:function(c,a,b,d,e){return f.effects.animateClass.apply(this,[{add:a,remove:c},b,d,e])}});f.extend(f.effects,{version:"1.8.16",save:function(c,a){for(var b=0;b<a.length;b++)a[b]!==null&&c.data("ec.storage."+a[b],c[0].style[a[b]])},restore:function(c,a){for(var b=0;b<a.length;b++)a[b]!==null&&c.css(a[b],c.data("ec.storage."+a[b]))},setMode:function(c,a){if(a=="toggle")a=c.is(":hidden")?"show":"hide";return a},getBaseline:function(c,a){var b;switch(c[0]){case "top":b=
0;break;case "middle":b=0.5;break;case "bottom":b=1;break;default:b=c[0]/a.height}switch(c[1]){case "left":c=0;break;case "center":c=0.5;break;case "right":c=1;break;default:c=c[1]/a.width}return{x:c,y:b}},createWrapper:function(c){if(c.parent().is(".ui-effects-wrapper"))return c.parent();var a={width:c.outerWidth(true),height:c.outerHeight(true),"float":c.css("float")},b=f("<div></div>").addClass("ui-effects-wrapper").css({fontSize:"100%",background:"transparent",border:"none",margin:0,padding:0}),
d=document.activeElement;c.wrap(b);if(c[0]===d||f.contains(c[0],d))f(d).focus();b=c.parent();if(c.css("position")=="static"){b.css({position:"relative"});c.css({position:"relative"})}else{f.extend(a,{position:c.css("position"),zIndex:c.css("z-index")});f.each(["top","left","bottom","right"],function(e,g){a[g]=c.css(g);if(isNaN(parseInt(a[g],10)))a[g]="auto"});c.css({position:"relative",top:0,left:0,right:"auto",bottom:"auto"})}return b.css(a).show()},removeWrapper:function(c){var a,b=document.activeElement;
if(c.parent().is(".ui-effects-wrapper")){a=c.parent().replaceWith(c);if(c[0]===b||f.contains(c[0],b))f(b).focus();return a}return c},setTransition:function(c,a,b,d){d=d||{};f.each(a,function(e,g){unit=c.cssUnit(g);if(unit[0]>0)d[g]=unit[0]*b+unit[1]});return d}});f.fn.extend({effect:function(c){var a=k.apply(this,arguments),b={options:a[1],duration:a[2],callback:a[3]};a=b.options.mode;var d=f.effects[c];if(f.fx.off||!d)return a?this[a](b.duration,b.callback):this.each(function(){b.callback&&b.callback.call(this)});
return d.call(this,b)},_show:f.fn.show,show:function(c){if(l(c))return this._show.apply(this,arguments);else{var a=k.apply(this,arguments);a[1].mode="show";return this.effect.apply(this,a)}},_hide:f.fn.hide,hide:function(c){if(l(c))return this._hide.apply(this,arguments);else{var a=k.apply(this,arguments);a[1].mode="hide";return this.effect.apply(this,a)}},__toggle:f.fn.toggle,toggle:function(c){if(l(c)||typeof c==="boolean"||f.isFunction(c))return this.__toggle.apply(this,arguments);else{var a=k.apply(this,
arguments);a[1].mode="toggle";return this.effect.apply(this,a)}},cssUnit:function(c){var a=this.css(c),b=[];f.each(["em","px","%","pt"],function(d,e){if(a.indexOf(e)>0)b=[parseFloat(a),e]});return b}});f.easing.jswing=f.easing.swing;f.extend(f.easing,{def:"easeOutQuad",swing:function(c,a,b,d,e){return f.easing[f.easing.def](c,a,b,d,e)},easeInQuad:function(c,a,b,d,e){return d*(a/=e)*a+b},easeOutQuad:function(c,a,b,d,e){return-d*(a/=e)*(a-2)+b},easeInOutQuad:function(c,a,b,d,e){if((a/=e/2)<1)return d/
2*a*a+b;return-d/2*(--a*(a-2)-1)+b},easeInCubic:function(c,a,b,d,e){return d*(a/=e)*a*a+b},easeOutCubic:function(c,a,b,d,e){return d*((a=a/e-1)*a*a+1)+b},easeInOutCubic:function(c,a,b,d,e){if((a/=e/2)<1)return d/2*a*a*a+b;return d/2*((a-=2)*a*a+2)+b},easeInQuart:function(c,a,b,d,e){return d*(a/=e)*a*a*a+b},easeOutQuart:function(c,a,b,d,e){return-d*((a=a/e-1)*a*a*a-1)+b},easeInOutQuart:function(c,a,b,d,e){if((a/=e/2)<1)return d/2*a*a*a*a+b;return-d/2*((a-=2)*a*a*a-2)+b},easeInQuint:function(c,a,b,
d,e){return d*(a/=e)*a*a*a*a+b},easeOutQuint:function(c,a,b,d,e){return d*((a=a/e-1)*a*a*a*a+1)+b},easeInOutQuint:function(c,a,b,d,e){if((a/=e/2)<1)return d/2*a*a*a*a*a+b;return d/2*((a-=2)*a*a*a*a+2)+b},easeInSine:function(c,a,b,d,e){return-d*Math.cos(a/e*(Math.PI/2))+d+b},easeOutSine:function(c,a,b,d,e){return d*Math.sin(a/e*(Math.PI/2))+b},easeInOutSine:function(c,a,b,d,e){return-d/2*(Math.cos(Math.PI*a/e)-1)+b},easeInExpo:function(c,a,b,d,e){return a==0?b:d*Math.pow(2,10*(a/e-1))+b},easeOutExpo:function(c,
a,b,d,e){return a==e?b+d:d*(-Math.pow(2,-10*a/e)+1)+b},easeInOutExpo:function(c,a,b,d,e){if(a==0)return b;if(a==e)return b+d;if((a/=e/2)<1)return d/2*Math.pow(2,10*(a-1))+b;return d/2*(-Math.pow(2,-10*--a)+2)+b},easeInCirc:function(c,a,b,d,e){return-d*(Math.sqrt(1-(a/=e)*a)-1)+b},easeOutCirc:function(c,a,b,d,e){return d*Math.sqrt(1-(a=a/e-1)*a)+b},easeInOutCirc:function(c,a,b,d,e){if((a/=e/2)<1)return-d/2*(Math.sqrt(1-a*a)-1)+b;return d/2*(Math.sqrt(1-(a-=2)*a)+1)+b},easeInElastic:function(c,a,b,
d,e){c=1.70158;var g=0,h=d;if(a==0)return b;if((a/=e)==1)return b+d;g||(g=e*0.3);if(h<Math.abs(d)){h=d;c=g/4}else c=g/(2*Math.PI)*Math.asin(d/h);return-(h*Math.pow(2,10*(a-=1))*Math.sin((a*e-c)*2*Math.PI/g))+b},easeOutElastic:function(c,a,b,d,e){c=1.70158;var g=0,h=d;if(a==0)return b;if((a/=e)==1)return b+d;g||(g=e*0.3);if(h<Math.abs(d)){h=d;c=g/4}else c=g/(2*Math.PI)*Math.asin(d/h);return h*Math.pow(2,-10*a)*Math.sin((a*e-c)*2*Math.PI/g)+d+b},easeInOutElastic:function(c,a,b,d,e){c=1.70158;var g=
0,h=d;if(a==0)return b;if((a/=e/2)==2)return b+d;g||(g=e*0.3*1.5);if(h<Math.abs(d)){h=d;c=g/4}else c=g/(2*Math.PI)*Math.asin(d/h);if(a<1)return-0.5*h*Math.pow(2,10*(a-=1))*Math.sin((a*e-c)*2*Math.PI/g)+b;return h*Math.pow(2,-10*(a-=1))*Math.sin((a*e-c)*2*Math.PI/g)*0.5+d+b},easeInBack:function(c,a,b,d,e,g){if(g==j)g=1.70158;return d*(a/=e)*a*((g+1)*a-g)+b},easeOutBack:function(c,a,b,d,e,g){if(g==j)g=1.70158;return d*((a=a/e-1)*a*((g+1)*a+g)+1)+b},easeInOutBack:function(c,a,b,d,e,g){if(g==j)g=1.70158;
if((a/=e/2)<1)return d/2*a*a*(((g*=1.525)+1)*a-g)+b;return d/2*((a-=2)*a*(((g*=1.525)+1)*a+g)+2)+b},easeInBounce:function(c,a,b,d,e){return d-f.easing.easeOutBounce(c,e-a,0,d,e)+b},easeOutBounce:function(c,a,b,d,e){return(a/=e)<1/2.75?d*7.5625*a*a+b:a<2/2.75?d*(7.5625*(a-=1.5/2.75)*a+0.75)+b:a<2.5/2.75?d*(7.5625*(a-=2.25/2.75)*a+0.9375)+b:d*(7.5625*(a-=2.625/2.75)*a+0.984375)+b},easeInOutBounce:function(c,a,b,d,e){if(a<e/2)return f.easing.easeInBounce(c,a*2,0,d,e)*0.5+b;return f.easing.easeOutBounce(c,
a*2-e,0,d,e)*0.5+d*0.5+b}})}(jQuery);
/*
 * jQuery UI Effects Highlight 1.8.16
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Highlight
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(b){b.effects.highlight=function(c){return this.queue(function(){var a=b(this),e=["backgroundImage","backgroundColor","opacity"],d=b.effects.setMode(a,c.options.mode||"show"),f={backgroundColor:a.css("backgroundColor")};if(d=="hide")f.opacity=0;b.effects.save(a,e);a.show().css({backgroundImage:"none",backgroundColor:c.options.color||"#ffff99"}).animate(f,{queue:false,duration:c.duration,easing:c.options.easing,complete:function(){d=="hide"&&a.hide();b.effects.restore(a,e);d=="show"&&!b.support.opacity&&
this.style.removeAttribute("filter");c.callback&&c.callback.apply(this,arguments);a.dequeue()}})})}})(jQuery);