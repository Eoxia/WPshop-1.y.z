var postID = 0;
var element = undefined;

jQuery(document).ready(function() {
	jQuery('.wps-add-to-wishlist').click(function() {
		jQuery(this).addClass('wps-bton-loading');

		element = jQuery(this);

		postID = jQuery(this).attr('data-id');

		open_modal_wishlist();

	});

	/** Create wishlist and add product to it */
	jQuery(document).on('click', '.create-wishlist-and-add-product-to-it', function() {
		jQuery(this).addClass('wps-bton-loading');

		element = jQuery(this);

		var name_wishlist = jQuery('.wps-name-wishlist').val();

		var data = {
			'action': 'wps-create-wishlist-and-add-product-to-it',
			'postID': jQuery('.wps-product-id').val(),
			'name_wishlist': name_wishlist,
		};

		if('' != name_wishlist) {
			jQuery.post(ajaxurl, data, function(response) {
				element.removeClass('wps-bton-loading');
				jQuery( '.wpsjq-closeModal').click();
			});
		}
		else {
			jQuery('.wps-name-wishlist').shake(2, 13, 250);
			element.removeClass('wps-bton-loading');
		}
	});

	/** Add to wishlist */
	jQuery(document).on('click', '.wps-add-product-to-wishlist', function() {
		jQuery(this).addClass('wps-bton-loading');

		var element = jQuery(this);

		var data = {
			'action': 'wps-add-to-wishlist',
			'postID': jQuery('.wps-product-id').val(),
			'name_wishlist': jQuery(this).html(),
		};

		jQuery.post(ajaxurl, data, function(response) {
			element.removeClass('wps-bton-loading');
			jQuery( '.wpsjq-closeModal').click();
		});
	});

	/** Display wishlist in account panel */
	jQuery(document).on('click', '.wps-display-wishlist', function() {
		var name_wishlist = jQuery(this).html();

		var element = jQuery(this);

		jQuery('.wps-display-wishlist').removeClass('wps-button-activ');
		jQuery(this).addClass('wps-button-activ');

		jQuery(this).addClass('wps-bton-loading');

		var data = {
			'action': 'wps-load-wishlist',
			'name_wishlist': name_wishlist,
		};

		jQuery('.wps-container-product-in-wishlist').load(ajaxurl, data, function() { element.removeClass('wps-bton-loading'); });
	});
});

function open_modal_wishlist() {
	var data = {
		'action': 'wps-load-modal',
		'postID': postID,
	};

	jQuery.post(ajaxurl, data, function(response) {
		if(response['need_login'] != undefined) {
			var data = {
				'action': 'wps-get-login-form',
			};

			jQuery.post(ajaxurl, data, function(response) {
				fill_the_modal('Wishlist', response, '');
				jQuery( '#wps_login_form_container' ).hide();
				jQuery( '#wps_signup_form_container' ).hide();
			});
		}
		else {
			element.removeClass('wps-bton-loading');
			fill_the_modal(response['title'], response['content'], '');
		}
	});
}

jQuery.fn.shake = function(intShakes, intDistance, intDuration) {
	this.each(function() {
		jQuery(this).css({
			position: "relative"
		});

		for (var x = 1; x <= intShakes; x++) {
			jQuery(this).animate({
				left: (intDistance * -1)
			}, (((intDuration / intShakes) / 4))).animate({
				left: intDistance
			}, ((intDuration / intShakes) / 2)).animate({
				left: 0
			}, (((intDuration / intShakes) / 4)));
		}
	});
	return this;
};
