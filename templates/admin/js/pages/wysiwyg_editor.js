(function() {
	tinymce.create( 'tinymce.plugins.wpshop_wysiwyg_shortcodes', {

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl: function( n, cm ) {
			switch ( n ) {
				case 'wpshop_wysiwyg_button':
					var wpshop_wysiwyg_button = cm.createMenuButton( n, {
						title: WPSHOP_BUTTON_DESCRIPTION,
						image: false
					});

					wpshop_wysiwyg_button.onRenderMenu.add(function( c, m ) {
						/*	Define wpshop products shortcodes menu	*/
						var wpshop_product_shortode;
						wpshop_product_shortode = m.addMenu({ title: wpshop_mce_shortcode.product_listing });
						wpshop_product_shortode.add({ title: WPSHOP_WYSIWYG_PRODUCT_LISTING_BY_PID_TITLE, onclick: function() {
							tinyMCE.activeEditor.windowManager.open({
								file: WPSHOP_ADMIN_URL + 'admin-post.php?action=wps_shortcodes_wysiwyg_dialog&type=product&post_type=' + jQuery( '#post_type' ).val(),
								width: 800,
								height: 600,
								inline: 1
							});
						} });
						wpshop_product_shortode.add({ title: WPSHOP_WYSIWYG_PRODUCT_LISTING_BY_ATTRIBUTE_TITLE, onclick: function() {
							tinyMCE.activeEditor.windowManager.open({
								file: WPSHOP_ADMIN_URL + 'admin-post.php?action=wps_shortcodes_wysiwyg_dialog&type=product_by_attribute&post_type=' + jQuery( '#post_type' ).val(),
								width: 800,
								height: 600,
								inline: 1
							});
						} });

						/*	Define wpshop categories shortcode menu	*/
						m.add({ title: WPSHOP_WYSIWYG_MENU_TITLE_CATEGORIES, onclick: function() {
	                        tinyMCE.activeEditor.windowManager.open({
	        					file: WPSHOP_ADMIN_URL + 'admin-post.php?action=wps_shortcodes_wysiwyg_dialog&type=categories&post_type=' + jQuery( '#post_type' ).val(),
	        					width: 800,
	        					height: 600,
	        					inline: 1
	        				});
	                    } });

						/*	Define wpshop attributes shortcode menu	*/
						m.add({ title: WPSHOP_WYSIWYG_MENU_TITLE_ATTRIBUTE_VALUE, onclick: function() {
							tinyMCE.activeEditor.windowManager.open({
								file: WPSHOP_ADMIN_URL + 'admin-post.php?action=wps_shortcodes_wysiwyg_dialog&type=attribute_value&post_type=' + jQuery( '#post_type' ).val(),
								width: 800,
								height: 600,
								inline: 1
							});
						} });

						/*	Define wpshop custom tags	*/
						if ( jQuery( '#post_type' ).val() === 'page' ) {
							var wpshop_custom_tags;
							wpshop_custom_tags = m.addMenu({ title: WPSHOP_CUSTOM_TAGS_TITLE });
							wpshop_custom_tags.add({ title: WPSHOP_CUSTOM_TAGS_CART, onclick: function() {
								tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, '[wpshop_cart]' );
							} });
							wpshop_custom_tags.add({ title: WPSHOP_CUSTOM_TAGS_CART_MINI, onclick: function() {
								tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, '[wpshop_mini_cart]' );
							} });
							wpshop_custom_tags.add({ title: WPSHOP_CUSTOM_TAGS_CHECKOUT, onclick: function() {
								tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, '[wpshop_checkout]' );
							} });
							wpshop_custom_tags.add({ title: WPSHOP_CUSTOM_TAGS_ACCOUNT, onclick: function() {
								tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, '[wpshop_myaccount]' );
							} });
							wpshop_custom_tags.add({ title: WPSHOP_CUSTOM_TAGS_SHOP, onclick: function() {
								tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, '[wpshop_products]' );
							} });
							//wpshop_custom_tags.add({title : WPSHOP_CUSTOM_TAGS_ADVANCED_SEARCH, onclick : function() {
	                        //	tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[wpshop_custom_search]');
	                        //}});
						}

						/*	Define wpshop custom message content	*/
						if ( jQuery("#post_type").val() === WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE) {

							var wpshop_custom_message;
							wpshop_custom_message = m.addMenu({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_TITLE});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_FIRST_NAME, onclick : function() {
								tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[customer_first_name]');
							}});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_LAST_NAME, onclick : function() {
								tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[customer_last_name]');
							}});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_EMAIL, onclick : function() {
								tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[customer_email]');
							}});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_ID, onclick : function() {
								tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[order_key]');
							}});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_PAYPAL_TRANSACTION_ID, onclick : function() {
								tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[paypal_order_key]');
							}});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_PAYMENT_METHOD, onclick : function() {
	                        	tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[order_payment_method]');
	                        }});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_CUSTOMER_PERSONNAL_INFORMATIONS, onclick : function() {
	                        	tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[order_personnal_informations]');
	                        }});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_CONTENT, onclick : function() {
	                        	tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[order_content]');
	                        }});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_ADDRESSES, onclick : function() {
	                        	tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[order_addresses]');
	                        }});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_BILLING_ORDER_ADDRESS, onclick : function() {
	                        	tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[order_billing_address]');
	                        }});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_SHIPPING_ORDER_ADDRESS, onclick : function() {
								tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[order_shipping_address]');
							}});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_SHIPPING_METHOD, onclick : function() {
	                        	tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[order_shipping_method]');
	                        }});
							wpshop_custom_message.add({title : WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_COMMENT, onclick : function() {
	                        	tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[order_customer_comments]');
	                        }});

						}
					});
					if (( jQuery("#post_type").val() === 'page' ) || ( jQuery("#post_type").val() === 'post' ) || ( jQuery("#post_type").val() === WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE ) || ( jQuery("#post_type").val() === WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT )) {
						return wpshop_wysiwyg_button;
					}
				break;
			}
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Wpshop shortcode add',
				author : 'Eoxia',
				authorurl : 'http://www.eoxia.com',
				version : "1.0"
			};
		}
	});

	// Register plugin
	//tinymce.PluginManager.add('wpshop_wysiwyg_shortcodes', tinymce.plugins.wpshop_wysiwyg_shortcodes);
	tinymce.PluginManager.add('wpshop_wysiwyg_shortcodes', function(editor, url) {
		if (( jQuery("#post_type").val() === 'page' ) || ( jQuery("#post_type").val() === 'post' ) || ( jQuery("#post_type").val() === WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE ) || ( jQuery("#post_type").val() === WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT )) {
			var wpshop_btn = {
				title: WPSHOP_BUTTON_DESCRIPTION,
				type: 'menubutton',
				icon: 'wp_code',
				menu: [
					{
						title : WPSHOP_WYSIWYG_MENU_TITLE_PRODUCT_LISTING,
						menu: [
							{
								title: WPSHOP_WYSIWYG_PRODUCT_LISTING_BY_PID_TITLE,
								onclick: function() {
									editor.windowManager.open({
										file: WPSHOP_ADMIN_URL + 'admin-post.php?action=wps_shortcodes_wysiwyg_dialog&type=product&post_type=' + jQuery("#post_type").val(),
										width: 800,
										height: 600,
										inline: 1
									});
								}
							},
							{
								title: WPSHOP_WYSIWYG_PRODUCT_LISTING_BY_ATTRIBUTE_TITLE,
								onclick: function() {
									editor.windowManager.open({
										file: WPSHOP_ADMIN_URL + 'admin-post.php?action=wps_shortcodes_wysiwyg_dialog&type=product_by_attribute&post_type=' + jQuery("#post_type").val(),
										width: 800,
										height: 600,
										inline: 1
									});
								}
							},
						]
					},
					{
						title: WPSHOP_WYSIWYG_MENU_TITLE_CATEGORIES,
						onclick: function() {
							editor.windowManager.open({
								file: WPSHOP_ADMIN_URL + 'admin-post.php?action=wps_shortcodes_wysiwyg_dialog&type=categories&post_type=' + jQuery("#post_type").val(),
								width: 800,
								height: 600,
								inline: 1
							});
						}
					},
					{
						title: WPSHOP_WYSIWYG_MENU_TITLE_ATTRIBUTE_VALUE,
						onclick: function() {
							editor.windowManager.open({
								file: WPSHOP_ADMIN_URL + 'admin-post.php?action=wps_shortcodes_wysiwyg_dialog&type=attribute_value&post_type=' + jQuery("#post_type").val(),
								width: 800,
								height: 600,
								inline: 1
							});
						}
					},
				]
			}

			if ( jQuery("#post_type").val() === 'page') {
				wpshop_btn.menu.push({
					title : WPSHOP_CUSTOM_TAGS_TITLE,
					menu: [
						{
							title: WPSHOP_CUSTOM_TAGS_CART,
							onclick: function() {
								editor.execCommand('mceInsertContent', false, '[wpshop_cart]');
							}
						},
						{
							title: WPSHOP_CUSTOM_TAGS_CART_MINI,
							onclick: function() {
								editor.execCommand('mceInsertContent', false, '[wpshop_mini_cart]');
							}
						},
						{
							title: WPSHOP_CUSTOM_TAGS_CHECKOUT,
							onclick: function() {
								editor.execCommand('mceInsertContent', false, '[wpshop_checkout]');
							}
						},
						{
							title: WPSHOP_CUSTOM_TAGS_ACCOUNT,
							onclick: function() {
								editor.execCommand('mceInsertContent', false, '[wpshop_myaccount]');
							}
						},
						{
							title: WPSHOP_CUSTOM_TAGS_SHOP,
							onclick: function() {
								editor.execCommand('mceInsertContent', false, '[wpshop_products]');
							}
						},
					]
				});
			}

			if ( jQuery("#post_type").val() === WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE) {
				wpshop_btn.menu.push({
					title: WPSHOP_CUSTOM_MESSAGE_CONTENT_TITLE,
					menu: [
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_FIRST_NAME,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[customer_first_name]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_LAST_NAME,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[customer_last_name]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_EMAIL,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[customer_email]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_ID,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[order_key]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_PAYPAL_TRANSACTION_ID,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[paypal_order_key]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_PAYMENT_METHOD,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[order_payment_method]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_CUSTOMER_PERSONNAL_INFORMATIONS,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[order_personnal_informations]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_CONTENT,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[order_content]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_ORDER_ADDRESSES,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[order_addresses]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_BILLING_ORDER_ADDRESS,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[order_billing_address]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_SHIPPING_ORDER_ADDRESS,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[order_shipping_address]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_SHIPPING_METHOD,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[order_shipping_method]');
							}
						},
						{
							title : WPSHOP_CUSTOM_MESSAGE_CONTENT_CUSTOMER_COMMENT,
							onclick : function() {
								editor.execCommand('mceInsertContent', false, '[order_customer_comments]');
							}
						},
					]
				});
			}

			editor.addButton('wpshop_wysiwyg_shortcodes', wpshop_btn);
		}
	});
})();
