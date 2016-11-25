jQuery(document).ready(function(wpsjq) {

	////////////////////////////////////////////////////////////////////////////////////////////////////////// VARIABLES

	MODAL_URL = MODAL_URL + '/modal.php';
	var wps_speed_slideUpDown = 250;

	var wps_speed_slideUp = 150;

	var wps_speed_slideDown = 150;

	/* wps-modal *************************************************************************************/

	function wps_modal_opener(){
		//alert('modal opener');
		wpsjq('.wps-modal-wrapper').addClass('wps-modal-opened');
		wps_hide_content();
		wpsjq('.wps-modal-wrapper').bind('mousewheel', function() {
		     return true;
		});
	}
	function wps_modal_closer(){
		//alert('modal closer');
		wps_show_content();
		wpsjq('.wps-modal-wrapper').removeClass('wps-modal-opened');
		//wpsjq('body').removeClass('wps-body-overlay');
	}
	wpsjq(document).on('click','.wps-modal-opener',function(e){
		e.preventDefault();
		wps_modal_opener();
	});
	wpsjq(document).on('click','.wpsjq-closeModal',function(e){
		e.preventDefault();
		wps_modal_closer();
	});

	wpsjq(document).on('click','.wps-modal-overlay',function(e){
		e.preventDefault();
		wps_modal_closer();
	});
	wpsjq(document).keyup(function(e) { /********************* Touche Echap ***************/
	  if (e.keyCode == 27) {
	  	e.preventDefault();
		wps_modal_closer();
		wps_close_cart();
	  }
	});

	/* ************************************************************************************************/

	/* wps-ui-tab *************************************************************************************/

	function wps_ui_tab_action(wps_tab,init){
		wpsjq(wps_tab).find('> div > div').hide();
		var wps_tab_item = wpsjq(wps_tab).find('> ul .wps-activ a').attr('data-toogle');
		if( init ){
			wpsjq(wps_tab).find('> div .'+wps_tab_item).show();
		} else {
			wpsjq(wps_tab).find('> div .'+wps_tab_item).show();
		}
	}
	function wps_ui_tab_init(){
		wpsjq( '.wps-ui-tab' ).each(function( index ) {
		  wps_ui_tab_action(wpsjq(this),true);
		});
	}
	wps_ui_tab_init();
	wpsjq('.wps-ui-tab').on('click','> ul li a',function(e){
		e.preventDefault();
		wpsjq(this).closest('.wps-ui-tab').find('> ul li').removeClass('wps-activ');
		wpsjq(this).parent().addClass('wps-activ');
		wps_ui_tab_action(wpsjq(this).closest('.wps-ui-tab'));
	})

	/* ************************************************************************************************/

	/* wps-ui-tab *************************************************************************************/

	function wps_ui_accordion_action(wps_accordion,init){

		if(init) {
			wpsjq(wps_accordion).find('> div > div').hide();
			wpsjq(wps_accordion).find('> div > div:first-child').show();
			wpsjq(wps_accordion).find('.wps-current-accordion').find('div').stop(true).show();
		} else {

			wpsjq(wps_accordion).find('> div > div').stop(true).slideUp(wps_speed_slideUpDown);
			wpsjq(wps_accordion).find('> div > div:first-child').stop(true).show();
			wpsjq(wps_accordion).find('.wps-current-accordion').find('div').stop(true).slideDown(wps_speed_slideUpDown);
		}
	}
	function wps_ui_accordion_init(){
		wpsjq( '.wps-ui-accordion' ).each(function( index ) {
		  wps_ui_accordion_action(wpsjq(this),true);
		});
	}

	wps_ui_accordion_init();

	wpsjq('.wps-ui-accordion').on('click','> div > div:first-child a',function(e){
		e.preventDefault();
		wpsjq(this).closest('.wps-ui-accordion').find('> div').removeClass('wps-current-accordion');
		wpsjq(this).parent().parent().addClass('wps-current-accordion');
		wps_ui_accordion_action(wpsjq(this).closest('.wps-ui-accordion'));
	})


	/* .wps-list-expander *******************************************************************************/

	if(wpsjq( '.wps-list-expander' ).length){
		wpsjq( '.wps-list-expander' ).each(function( index ) {
			 wpsjq(this).on('click', 'li', function() {
			 	wpsjq(this).parent().find('.wps-activ').removeClass('wps-activ');
			 	wpsjq(this).addClass('wps-activ');
			 	wpsjq(this).find('input[type="radio"]').attr('checked', true);
				wpsjq(this).closest('.wps-list-expander').find('li .wps-list-expander-content').stop(true).slideUp(wps_speed_slideUp);
				wpsjq(this).find('.wps-list-expander-content').stop(true).slideDown(wps_speed_slideDown);
			});
		});
	}

	/* **************************************************************************************************/

	/* .wps-alert ***************************************************************************************/

	wpsjq('[class*="wps-alert"]').on('click','[class*="wps-bton"][class*="close"]',function(e){
		e.preventDefault();
		wpsjq(this).parent().stop(true).slideUp(wps_speed_slideUpDown);
	})

	/* **************************************************************************************************/


	/* */

	function wps_mobil_table_generator(t){
		var label = [];
		t.find('.wps-table-header > .wps-table-cell').each(function( index ) {
		  	label.push(wpsjq(this).html());
		});
		t.find('.wps-table-content').each(function() {
		  	wpsjq(this).find('.wps-table-cell').each(function( index ) {
		  		if(label[index]){
				  	wpsjq(this).prepend('<label>'+label[index]+'</label>');
				  }
			});
		});
	}
	wpsjq( ".wps-table" ).each(function( index ) {
	  	wps_mobil_table_generator(wpsjq(this));
	});

	/* */

	/* wps-list radio ***********************************************************************************/

	/*function wps_list_opener(item){
		item.parent().find('.wps-form-list-content').stop(true).slideUp(wps_speed_slideUpDown);
		item.find('.wps-form-list-content').stop(true).slideDown(wps_speed_slideUpDown);
	}

	wpsjq('.wps-form-list').on('click','li',function(e){
		e.preventDefault();
		wpsjq(this).parent().find('li').removeClass('wps-list-open');
		wpsjq(this).addClass('wps-list-open');
		wpsjq(this).find('input[type=radio]').attr('checked',true);
		wps_list_opener(wpsjq(this));
	});*/


	function wpsjq_get_attr_dataname(cible, dataAttr, defaultVal){
		return cible.attr(dataAttr) ? cible.attr(dataAttr) : defaultVal;
	}


	if(wpsjq('.wps-owlCarousel').length){

		var cible = wpsjq('.wps-owlCarousel');

		var item = wpsjq_get_attr_dataname(cible, 'data-item', 5);

		var autoplay = wpsjq_get_attr_dataname(cible, 'data-autoplay', false);

		wpsjq('.wps-owlCarousel').owlCarousel({
			slideSpeed : 300,
			paginationSpeed : 400,
			singleItem:false,
			items : item,
			navigation: false,
			pagination: true,
			autoPlay : autoplay,
			stopOnHover : true,
			touchDrag : true,
			mouseDrag: true,
			autoHeight : true
		});
	}





	/* **************************************************************************************************/

	var wps_check_h_taskbars = 0;


	/* wps-taskbar-sticker ******************************************************************************/
/*
	function init_wps_taskbar(){
		wpsjq('.wps-taskbar-sticker').wrap('<div class="wps-taskbar-sticker-container" />');
		wps_check_h_taskbars += wpsjq('.wps-taskbar-sticker-container').height();
	}

	function launch_wps_taskbar(){
		init_wps_taskbar();
		var iw = wpsjq('.wps-taskbar-sticker-container').width();
		var ih = wpsjq('.wps-taskbar-sticker-container').height();

		wpsjq(window).resize(function() {
		  	iw = wpsjq('.wps-taskbar-sticker-container').width();
			ih = wpsjq('.wps-taskbar-sticker-container').height();
		 	size_taskbar();
		});

		wpsjq(document).on('scroll',function(e){
			var tb = wpsjq('.wps-taskbar-sticker');
			var s = wpsjq(document).scrollTop();
			var tby = tb.offset().top;
			if(!(tb.hasClass( 'wps-sticked-taskbar' ))) {
				if (s > tby) {
					tb.addClass('wps-sticked-taskbar');
				} else {
					tb.removeClass('wps-sticked-taskbar');
				};
			}
			size_taskbar();

			pc = wpsjq('.wps-taskbar-sticker-container').offset().top;
			pt = wpsjq('.wps-taskbar-sticker').offset().top;
			if((pt-pc <= 0) || (iw < 800)){
				tb.removeClass('wps-sticked-taskbar');
			}
		});

		function size_taskbar(){
			wpsjq('.wps-taskbar-sticker').css('width',iw);
			wpsjq('.wps-taskbar-sticker-container').css('height',ih)
		}
	}
	if (wpsjq('.wps-taskbar-sticker').length) {
		launch_wps_taskbar();
	}
*/
	/* wps-sidebar-sticker ******************************************************************************/
/*
	var wps_sticked_sidebar_init_offset = 0;
	var wps_size_container;
	var wps_screen_size_checker = true;
	var target;
	var position_init_sidebar;
	function init_wps_sticker_sidebar(){

		wpsjq('.wps-sidebar-sticker').wrapInner('<div class="wps-sidebar-sticker-container" />');
		wps_size_container = wpsjq('.wps-sidebar-sticker-container').width();
		target = wpsjq('.wps-sidebar-sticker-container');
		position_init_sidebar = target.offset().top;
		var w = target.closest('[class*="wps-gridwrapper"]');

		var hw = w.height();
		w.addClass('test'+hw);
		var tw = w.offset().top;



	}
	function launch_wps_sticker_sidebar(){
		if (wpsjq('.wps-sidebar-sticker').length) {
			init_wps_sticker_sidebar();
			wpsjq(document).on('scroll',function(e){
				wps_postion_sticked_sidebar();
			});
		}
	}
	function wps_postion_sticked_sidebar(){
		var th = target.height();
		var tpy = target.offset().top;
		var cpy = target.parent().offset().top;
		var s = wpsjq(document).scrollTop();
		var w = target.closest('[class*="wps-gridwrapper"]');
		var hw = w.height();
		var tw = w.offset().top;


		if((th >= wpsjq(window).height()-300 ) || wpsjq('body').hasClass('wps-mobil')) {
			target.removeClass('wps-sticked-sidebar');
			target.removeClass('wps-sticked-bottom-sidebar');
		}else{
			target.parent().css('height',hw-40);
			if(s > position_init_sidebar){
				if(s+40 > (hw+tw-th) ){
					target.addClass('wps-sticked-bottom-sidebar');
					target.removeClass('wps-sticked-sidebar');
				}else{
					target.addClass('wps-sticked-sidebar');
					target.removeClass('wps-sticked-bottom-sidebar');
				}
			}else{
				target.removeClass('wps-sticked-sidebar');
			}
			target.css('width',wps_size_container);
		}
	}

	launch_wps_sticker_sidebar();
*/
	/* **************************************************************************************************/

	/* wps_check_mobil size for JS***********************************************************************/

	function wps_check_mobil(){
		var b = wpsjq(document);
		var bw = b.width();
		if(bw >= 960 ){
			wpsjq('body').removeClass('wps-mobil');
		}else {
			wpsjq('body').addClass('wps-mobil');
		}
	}

	wpsjq(window).resize(function() {
		wps_check_mobil();
		_wps_check_mobil_filters();
	});
	wps_check_mobil();


	/* **************************************************************************************************/

	/* wps-filters **************************************************************************************/

	var tf = wpsjq('.wps-filter-aside');

	function wps_filter_openclose(){
		if(tf.hasClass('wps-current-open')){
			tf.removeClass('wps-current-open');
			tf.find('.wps-filters-body').slideUp();

		}else{
			tf.addClass('wps-current-open');
			tf.find('.wps-filters-body').slideDown();
		}
	}
	if(tf.length){
		wpsjq('.wps-filter-aside .wps-filters-header').on('click','a',function(e){
			e.preventDefault();
			wps_filter_openclose();
		});
	}
	function _wps_check_mobil_filters (){
		if(!(wpsjq('body').hasClass('wps-mobil'))){
		}
	}

	/* **************************************************************************************************/

	/* UI slider */

	function wps_create_slider_ui(c){
		var range_min = c.attr('data-range-min');
		var range_max = c.attr('data-range-max');
		var v_min = c.attr('data-min');
		var v_max = c.attr('data-max');
		if(( range_min != undefined ) && ( range_max != undefined )) {
			if( v_min == undefined ){
				v_min = range_min;
			}
			if( v_max == undefined ){
				v_max = range_max;
			}
			c.wrap('<div class="wps-slider-ui-container" >');
			c.before('<span class="wps-slider-ui-field wps-slider-ui-field-for">'+v_min+'</span>');
			c.after('<span class="wps-slider-ui-field wps-slider-ui-field-to">'+v_max+'</span>');
			c.after('<input type="hidden" class="wps-slider-ui-field-min" value="'+v_min+'">');
			c.after('<input type="hidden" class="wps-slider-ui-field-max" value="'+v_max+'">');
			c.noUiSlider({
			    range: [range_min, range_max]
			   ,start: [v_min, v_max]
			   ,handles: 2
			   ,slide: function(){
			      var values = wpsjq(this).val();
			      var min = Math.round(values[0]);
			      var max = Math.round(values[1]);
			      c.parent().find('.wps-slider-ui-field-for').text(min);
			      c.parent().find('.wps-slider-ui-field-to').text(max);
			      c.parent().find('.wps-slider-ui-field-min').attr('value',min);
			      c.parent().find('.wps-slider-ui-field-max').attr('value',max);
			   }
			});
		}
	}

	if(wpsjq('.wps-slider-ui').length){
		wpsjq('.wps-slider-ui').each(function( index ) {
		  wps_create_slider_ui(wpsjq(this));
		});
	}

	/* Toogle filter groups */

	function wps_filter_toogle_animate (c){
		if(c.hasClass('wps-filter-group-open')){
			c.find('.wps-filter-body').slideDown(wps_speed_slideUpDown);
			c.find('.wps-filter-header span').addClass('wps-rotate_90');
		}else {
			c.find('.wps-filter-body').slideUp(wps_speed_slideUpDown);
			c.find('.wps-filter-header span').removeClass('wps-rotate_90');
		}
	}

	function wps_create_filter_toogle(c){

		c.find('.wps-filter-header h3').append('<span class="wps-bton-icon-simple-angle-right-alignRight"></span>');
		c.find('.wps-filter-header h3').wrapInner('<a href="#"/>');

		c.on('click','.wps-filter-header h3 a',function(e){
			e.preventDefault();
			c.toggleClass('wps-filter-group-open');
			wps_filter_toogle_animate(c);
		})
		wps_filter_toogle_animate(c);
	}

	if(wpsjq('[class*="wps"][class*="filter"][class*="group"][class*="toogle"]').length){
		wpsjq('[class*="wps"][class*="filter"][class*="group"][class*="toogle"]').each(function( index ) {
		  wps_create_filter_toogle(wpsjq(this));
		});
	}

	/* **************************************************************************************************/

	/* wps-tool-bar */

	function wps_toolbar_action(wps_toolbar,init){
		var wps_toolbar_item = wpsjq(wps_toolbar).find('.wps-toolbar-header ul .wps-toolbar-current a').attr('data-toogle');
		if(init){
			wpsjq(wps_toolbar).find('.wps-toolbar-body > div').hide();
			wpsjq(wps_toolbar).find('.wps-toolbar-body > .'+wps_toolbar_item).stop('true').show();
		}else{
			wpsjq(wps_toolbar).find('.wps-toolbar-body > div').slideUp(wps_speed_slideUpDown);
			wpsjq(wps_toolbar).find('.wps-toolbar-body > .'+wps_toolbar_item).stop('true').slideDown(wps_speed_slideUpDown);
		}
	}
	function wps_toolbar_closer(wl){
		var wps_toolbar_item = wl.attr('data-toogle');
		wl.parent().removeClass('wps-toolbar-current');
		wl.closest('.wps-toolbar').find('.wps-toolbar-body .'+wps_toolbar_item).stop(true).slideUp(wps_speed_slideUpDown);
	}
	function wps_toolbar_init(){
		wps_toolbar_action(wpsjq( '.wps-toolbar' ),true);
	}

	wps_toolbar_init();

	wpsjq('.wps-toolbar').on('click','.wps-toolbar-header ul li a',function(e){
		e.preventDefault();
		if(!(wpsjq(this).parent().hasClass('wps-toolbar-current'))){
			wpsjq(this).closest('.wps-toolbar-header').find('> ul li').removeClass('wps-toolbar-current');
			wpsjq(this).parent().addClass('wps-toolbar-current');
			wps_toolbar_action(wpsjq(this).closest('.wps-toolbar'));
		} else {
			wps_toolbar_closer(wpsjq(this));
		}

	})
	wpsjq('.wps-toolbar').on('click','.wps-close',function(){
		var c = wpsjq(this).closest('.wps-toolbar-body').parent().find('.wps-toolbar-header > ul li');
		c.removeClass('wps-toolbar-current');
		c.closest('.wps-toolbar').find('.wps-toolbar-body > div').slideUp(wps_speed_slideUpDown);
	})

	/* **************************************************************************************************/

	/* Panier */

	function wps_cart_events(){
		wpsjq('.wps-action-mini-cart-opener').on({
			   click: function(e) {
			   	e.preventDefault();
			   	wps_open_cart();
		  	}
		  });
		wpsjq('.wpsjq-closeFixedCart').on({
			click: function(e) {
			   	e.preventDefault();
			   	wps_close_cart();
		  	}
		})
		/*wpsjq('.wps-header-mini-cart').on({
			   mouseleave: function() {
			   wps_close_cart();
		  	}
		  });*/
	}

	function wps_open_cart(){
	 	wpsjq('.wps-cart-activator').addClass( "wps-activ" );
	   	wps_hide_content();
	}
	function wps_close_cart(){
		wpsjq('.wps-cart-activator').removeClass( "wps-activ" );
	  	wps_show_content();
	}
	function wps_hide_content(){
		wpsjq('html').addClass('wpsjq-modal-opened');
	}
	function wps_show_content(){
		wpsjq('html').removeClass('wpsjq-modal-opened');
	}

	/*wpsjq('.wps-header-mini-cart .wps-mini-cart-header').on({
	   click: function() {
	    wpsjq( this ).parent().addClass( "wps-mini-cart-opened" );
	   	wps_hide_content();
	  }
	});*/

	/* Show room */

	function wps_create_showroom(){
		t =  wpsjq('.wpsjq-showroom img');
		imgset = t.clone(true);
		imgset2 = imgset;
		wpsjq('.wpsjq-showroom').empty();


		wpsjq('.wpsjq-showroom').prepend(imgset);

		wpsjq('.wpsjq-showroom').prepend('fait chier cette merde');

		wpsjq('.wpsjq-showroom').prepend(imgset2);

	}

	if(wpsjq('.wpsjq-showroom').length){
		//wps_create_showroom();
	}

	/* */

	if(wpsjq('.wps-action-mini-cart-opener').length){
		wps_cart_events();
	}


	wpsjq('.wps-modal-overlay').on({
	   click: function() {
		    //wpsjq('.wps-cart-activator').removeClass( "wps-activ" );
		  	//wps_show_content();
		    //wps_show_content();
		    wps_close_cart();
		}
	});

	/*wpsjq('.wps-fixed-tool-bar').on({
		mouseenter: function() {
	    	wpsjq( 'body' ).addClass( "wps-fixed-mini-cart-opened" );
	    	wps_hide_content();
	  },
	  	mouseleave: function() {
	    	wpsjq( 'body' ).removeClass( "wps-fixed-mini-cart-opened" );
	    	wps_show_content();
		}
	})*/


	/* */
	/* Multi select */

	/*function wps_check_tags(c){
		var title = c.text();
		var value = c.val();
		c.closest('.wps-multi-select-tagmode-container').find('.wps-multi-select-tagmode-tags').append('<span class="wps-multi-select-tag">'+title+'<button type="button" class="wps-close"></button></span>');
		//t = v.closest('.wps-multi-select-tagmode-container');
	}

	function wps_create_multiselect_tagmode(c){

		c.wrap('<div class="wps-multi-select-tagmode-container" />');
		c.after('<div class="wps-multi-select-tagmode-tags"></div>');

		c.change(function() {
			var v = wpsjq(this).find("option:selected");
			//var t = wpsjq(this).find("option:selected");
			wps_check_tags(v);
			//t.detach();
			//t.attr('disabled','disabled');

		  	//alert(v);
		});


	}

	if(wpsjq('.wps-multi-select-tagmode').length){
		wpsjq('.wps-multi-select-tagmode').each(function( index ) {
		  wps_create_multiselect_tagmode(wpsjq(this));
		});
	}*/

	/* */

	/*wpsjq('button').on('click', function(){
		wpsjq(this).toggleClass('wps-bton-loading');
	})*/

	/* Section taskbar navigator */

	//wpsjq('.wps-section-taskbar').on('click', 'a', function(e){
		//e.preventDefault();
		//wps_open_section_navigator(wpsjq(this));
	//});
	//wps_open_section_navigator(wpsjq('.wps-section-taskbar a.wps-activ'));
	function wps_open_section_navigator(t){

		//wpsjq('.wps-section-taskbar a').removeClass('wps-activ');
		wpsjq(t).addClass('wps-activ');

		current = wpsjq('.wps-section-content > .wps-activ');
		next = wpsjq('.wps-section-content > div[data-display="'+t.attr('data-target')+'"]');

		current.addClass('wps-goToLeft');
		wpsjq('.wps-section-content div').removeClass('wps-activ');

		setTimeout(function() {
			next.addClass('wps-activ');
			h = next.height();
			current.removeClass('wps-goToLeft');
			next.parent().animate({
			    height: h
			  }, 300);
		}, 400);


		//wpsjq('.wps-section-taskbar a').removeClass('wps-activ');
		//wpsjq(t).addClass('wps-activ');
		//wpsjq('.wps-section-content > div').removeClass('wps-activ');
		//c = wpsjq('.wps-section-content > div[data-display="'+t.attr('data-target')+'"]').addClass('wps-activ');
		//c.addClass('wps-activ');
		/*setTimeout(function() {
		     c.addClass('wps-activ');
		}, 5000);*/
	}

	/* Cart */

	wpsjq('.wps-cart-attributes-container').on('click', '> a', function(e){
		e.preventDefault();
		wpsjq(this).parent().find('.wps-cart-attributes').slideToggle(200);
		wpsjq(this).parent().toggleClass('wps-activ');
	})

	/* Adresse */
	wp_select_adresses( '.wps-change-adresse');
	/*
	function wps_select_adresse(target){
		t = target.find('option:selected').attr('data-target');
		c = target.parent().parent();
		h = c.find('.wps-adresse[data-slug="'+t+'"]').height();
		c.find('.wps-adresse.wps-activ').addClass('wps-inactiv').removeClass('wps-activ');

		c.find('.wps-adresse-listing-select').animate({
		    height: h+30
		  }, 200);

		setTimeout(function() {
		   	c.find('.wps-adresse').removeClass('wps-inactiv');
			c.find('.wps-adresse[data-slug="'+t+'"]').addClass('wps-activ');
		}, 200);
	}

	wpsjq('.wps-change-adresse').on('change', function(e){
		wps_select_adresse(wpsjq(this));
	});

	setTimeout(function() {
	     wps_select_adresse(wpsjq('.wps-change-adresse'));
	}, 10);

	*/

	/* */

	/* Animate plusun */
	var wps_plusun_timer;
	wpsjq('.wps-animate-plusun').on('click', function(e){
		e.preventDefault();
		clearTimeout(wps_plusun_timer);
		t = wpsjq(this);
		t.addClass('wps-animated');
		wps_plusun_timer = setTimeout(function() {
		t.removeClass('wps-animated');
		}, 2000);
	});

	/* Animate boxplusun */
	var wps_modal_addone_timer;
	var i = 1;

	wpsjq('.wps-animate-boxplusun').on('click', function(e){
		e.preventDefault();
		wps_modal_addone();
	});

	function wps_modal_addone(){
		clearTimeout(wps_modal_addone_timer);
		t = wpsjq('[class*="wps-boxplusun"]');
		if(t.hasClass('wps-activ')){
			i++;
			//t.find('span').html(i);
		}else{
			t.addClass('wps-activ');
		}
		//t.find('span').html('+'+i)
		wps_modal_addone_timer = setTimeout(function() {
			t.removeClass('wps-activ');
			i = 1;
		}, 1600);
	}


	/* Fiche produit */

	wps_product_slider = wpsjq(".wps-showroom-slider-content");

	 wps_product_slider.owlCarousel({
		slideSpeed : 300,
		paginationSpeed : 400,
		singleItem:true,
		navigation: false,
		pagination:false,
		afterAction : syncPosition,
		autoPlay : false,
		stopOnHover : true,
		touchDrag : true,
		mouseDrag: true,
		autoHeight : true
	});

	function syncPosition(el){
		var current = this.currentItem;
		wpsjq('.wps-showroom-slider-thumbnails a').removeClass('wps-activ');
		wpsjq('.wps-showroom-slider-thumbnails > a').eq(current).addClass('wps-activ');
	}


	wpsjq(".wps-showroom-slider-thumbnails").on("click", "> a", function(e){
		e.preventDefault();
		var number = wpsjq(this).index();
		wps_product_slider.trigger("owl.goTo",number);
	});


	/***************************** Zoom Loupe */

	function wps_get_val_on_percentage(p,v){
		p = (p*v)/100;
		return p;
	}

	if(wpsjq('[class*="wps-zoom-loupe"]').length){

		/*loupeWidth = wpsjq('#wps-product-thumbnail [class*="wps-zoom-loupe"] ').width();
	  	loupeHeight = wpsjq('#wps-product-thumbnail [class*="wps-zoom-loupe"] ').height();

	  	loupeCoordonn = wpsjq('#wps-product-thumbnail [class*="wps-zoom-loupe"]').position();

		loupeX = loupeCoordonn.left;
		loupeY = loupeCoordonn.top;*/

		wpsjq('.wps-showroom-slider-content a').on({
	  		mousemove: function(e) {

	  			mouseX = e.pageX-wpsjq(this).offset().left;
	  			mouseY = e.pageY-wpsjq(this).offset().top;

	  			divWidth = wpsjq(this).width();
	  			divHeight = wpsjq(this).height();

		    	pXm = (mouseX/divWidth)*100;
		    	pYm = (mouseY/divHeight)*100;

		    	pXt = wpsjq(this).find('[class*="wps-zoom-loupe"] img').width();
		    	pYt = wpsjq(this).find('[class*="wps-zoom-loupe"] img').height();


		    	mvtX = pXt-wpsjq(this).find('[class*="wps-zoom-loupe"]').width();
		    	mvtY = pYt-wpsjq(this).find('[class*="wps-zoom-loupe"]').height();

		    	rXm = -(mvtX/100)*pXm;
		    	rYm = -(mvtY/100)*pYm;

		    	if(!(wpsjq(this).find('[class*="wps-zoom-loupe"]').hasClass('wps-activpaused'))){
		    		wpsjq(this).find('[class*="wps-zoom-loupe"] img').css('left', rXm);
		    		wpsjq(this).find('[class*="wps-zoom-loupe"] img').css('top', rYm);
		    	}

		    	if(divWidth > (wpsjq(this).find('[class*="wps-zoom-loupe"] img').width()-200)){
		    		wpsjq(this).find('[class*="wps-zoom-loupe"]').removeClass('wps-activ');
		    		wpsjq(this).find('[class*="wps-zoom-loupe"]').closest('a').removeClass('wps-cursorCross');
		    	}else{
		    		wpsjq(this).find('[class*="wps-zoom-loupe"]').closest('a').addClass('wps-cursorCross');
		    	}
			},
			mouseenter: function(e){
				wpsjq(this).find('[class*="wps-zoom-loupe"]').addClass('wps-activ');
				wpsjq(this).find('[class*="wps-zoom-loupe"]').removeClass('wps-activpaused');
			},
			mouseleave: function(e){
				wpsjq(this).find('[class*="wps-zoom-loupe"]').removeClass('wps-activ');
			},
			mousedown: function(e){
				wpsjq(this).find('[class*="wps-zoom-loupe"]').addClass('wps-activpaused');
			},
			mouseup: function(e){
				wpsjq(this).find('[class*="wps-zoom-loupe"]').removeClass('wps-activpaused');
			}
		});
	}


	/* Catalogue sorting */

	function wps_replace_class(t, s, r){
		c = t.attr('class').replace(s, r);
		t.removeClass();
		t.addClass(c);
	}

	function wps_change_catalog_mode(t,type){
		clt = t.closest('[class*="wps"][class*="catalog"][class*="container"]').find('[class*="wps"][class*="product"][class*="catalog"]');
		cl = clt.attr('class');
		if( type == 'grid' ){
			wps_replace_class(t, 'grid', 'list');
			wps_replace_class(clt, 'listwrapper', 'gridwrapper');
		}else if( type == 'list' ){
			wps_replace_class(t, 'list', 'grid');
			wps_replace_class(clt, 'gridwrapper', 'listwrapper');
		}
	}
	wpsjq('[class*="wps"][class*="catalog"][class*="sorting"]').on('click','button[class*="grid"]', function(){
		wps_change_catalog_mode( wpsjq(this),'grid' );
	})
	wpsjq('[class*="wps"][class*="catalog"][class*="sorting"]').on('click','button[class*="list"]', function(){
		wps_change_catalog_mode( wpsjq(this),'list' );
	})

	/* */


	/* Position Mosaic */

	function wps_position_mosaic(){

		//if(wpsjq('[class*="wps"][class*="mosaic').length){
			var unit = wpsjq('[class*="wps"][class*="mosaic1x1"]').width();
			wpsjq('[class*="wps"][class*="mosaic1x"]').css('height',unit);

			wpsjq('[class*="wps"][class*="mosaic2x"]').css('height',unit*2);

			wpsjq('[class*="wps"][class*="mosaic3x"]').css('height',unit*3);

			wpsjq('[class*="wps"][class*="mosaic4x"]').css('height',unit*6);

			wpsjq('[class*="wps"][class*="mosaic5x"]').css('height',unit*6);

			wpsjq('[class*="wps"][class*="mosaic6x"]').css('height',unit*6);

			wpsjq('[class*="wps"][class*="mosaic7x"]').css('height',unit*7);

			wpsjq('[class*="wps"][class*="mosaic8x"]').css('height',unit*8);

		//}

	}

	wps_position_mosaic();

	wpsjq(window).resize(function() {
		wps_position_mosaic();
	});

	/* */

	/* Rating product */

	var star = '<span></span>'

	function wps_rating_product(){
		var chaine='';
		if(wpsjq('.wps-rating')){
			t = wpsjq('.wps-rating');
			nb_star = t.attr('data-stars');
			rate = t.attr('data-rating');
			for(i = 0; i< nb_star; i++){
				chaine += i<rate ? '<span class="wps-icon-star-activ"></span>' : '<span class="wps-icon-star"></span>';
			}
			t.append(chaine);
		}
	}
	wps_rating_product();

	/* */

	/* affichage des couleurs */


	function wps_display_colors(){
		t = wpsjq('.wps-color-selector-activated');
		t.wrap('<div class="wps-color-selector-wrapper" />');
		t.hide();
		wpsjq('.wps-color-selector-activated option').each(function(){
			var c = wpsjq(this).attr('data-color')
			wpsjq('.wps-color-selector-wrapper').append('<a class="wps-color-picker" style="background:'+c+';" href="">test</a>')
		});
	}


	wps_function_laucher(wpsjq( '.wps-color-selector-activated'), 'wps_display_colors' );

	//$.each([52, 97], wps_function_laucher);

	//function foo(index, value) {
	   // alert(index + ': ' + value);
	//}


	function wps_function_laucher( _target, _function ){
		_target.length ? eval(_function+'()') : false;
	}


	/* Data title */


	wpsjq('.wps-icon-minihelper').each(function( index ) {
		c =  wpsjq( this ).parent();
		t = wpsjq( this );
		t.css('position', 'relative');


		t.append('<span class="wps-helper">'+t.attr('data-title')+'</span>');
	});
	/* */

});
/** Functions **/
//Animate addresses
function wps_select_adresse(target){
	t = target.find('option:selected').attr('data-target');
	//c = target.parent().parent().parent().parent();
	c = target.closest('.wps-address-container');
	h = c.find('.wps-adresse[data-slug="'+t+'"]').height();
	c.find('.wps-adresse.wps-activ').addClass('wps-inactiv').removeClass('wps-activ');
	c.find('.wps-adresse-listing-select').animate({
	    height: h+30
	  }, 200);
	c.find('.wps-adresse').removeClass('wps-inactiv');
	c.find('.wps-adresse[data-slug="'+t+'"]').addClass('wps-activ');
}


function wp_select_adresses(target){
	jQuery( target ).each(function() {
		wps_select_adresse(jQuery(this));
		jQuery(this).on('change', function(e){
			wps_select_adresse(jQuery(this));
		});
	});
}
