<?php
/*
 * General
	{WPSHOP_CART_LINK}		=> Link for the cart page
	{WPSHOP_CURRENCY}		=> Currency defined for the shop
 *
 */

$tpl_element = array();

/**
 *
 *
 * Frontend button
 *
 *
 */
/*	"Product unavailable" button	*/
ob_start();
?>
<button type="button" disabled="disabled" class="no_stock"><?php _e('Soon available', 'wpshop'); ?></button><?php
$tpl_element['unavailable_product_button'] = ob_get_contents();
ob_end_clean();


/*	"Add to cart" button	*/
/**
 * {WPSHOP_PRODUCT_ID}
 */
ob_start();
?>
<button type="button" id="wpshop_add_to_cart_{WPSHOP_PRODUCT_ID}" class="wpshop_add_to_cart_button wpshop_products_listing_bton_panier_active"><?php _e('Add to cart', 'wpshop'); ?></button><span class="wpshop_cart_loading_picture"></span><?php
$tpl_element['add_to_cart_button'] = ob_get_contents();
ob_end_clean();


/*	"Ask quotation" button	*/
/**
 * {WPSHOP_PRODUCT_ID}
 */
ob_start();
?>
<button type="button" id="wpshop_ask_a_quotation_{WPSHOP_PRODUCT_ID}" class="wpshop_products_listing_bton_panier_active wpshop_ask_a_quotation_button"><?php _e('Ask a quotation', 'wpshop'); ?></button><?php
$tpl_element['ask_quotation_button'] = ob_get_contents();
ob_end_clean();



/*	Mini cart container	*/
/**
 * {WPSHOP_CART_MINI_CONTENT}
 */
ob_start();
?>
<div class="wpshop_cart_summary_detail" ></div><div class="wpshop_cart_alert" ></div>
<div class="wpshop_cart_summary" >{WPSHOP_CART_MINI_CONTENT}</div><?php
$tpl_element['mini_cart_container'] = ob_get_contents();
ob_end_clean();


/*	Mini cart content	*/
/**
 * {WPSHOP_CART_LINK}
 * {WPSHOP_PDT_CPT}
 * {WPSHOP_CART_TOTAL_AMOUNT}
 */
ob_start();
?>
<a href="{WPSHOP_CART_LINK}"><?php echo sprintf(__('Your have %s item(s) in your cart','wpshop'), '{WPSHOP_PDT_CPT}').' - {WPSHOP_CART_TOTAL_AMOUNT}'?> {WPSHOP_CURRENCY}</a><?php
$tpl_element['mini_cart_content'] = ob_get_contents();
ob_end_clean();


/*	product added to cart popup	*/
/**
 * {WPSHOP_CART_LINK}
 */
ob_start();
?>
<div class="wpshop_superBackground"></div>
<div class="wpshop_popupAlert">
	<h1><?php _e('Your product has been sucessfuly added to your cart', 'wpshop'); ?></h1>
	<a href="{WPSHOP_CART_LINK}"><?php _e('View my cart','wpshop'); ?></a> <input type="button" class="button-secondary closeAlert" value="<?php _e('Continue shopping','wpshop'); ?>" />
</div><?php
$tpl_element['product_added_to_cart_message'] = ob_get_contents();
ob_end_clean();



/*	Product is new	*/
ob_start();
?>
<span class="vignette_nouveaute"><?php _e('New', 'wpshop'); ?></span><?php
$tpl_element['product_is_new_sticker'] = ob_get_contents();
ob_end_clean();


/*	Product is featured	*/
ob_start();
?>
<span class="vignette_en_vedette"><?php _e('Featured', 'wpshop'); ?></span><?php
$tpl_element['product_is_featured_sticker'] = ob_get_contents();
ob_end_clean();



/**
 *
 *
 * Product front attribute display
 *
 *
 */
/*	Display the global container for product attribute	*/
/**
 * {WPSHOP_PDT_TABS}
 * {WPSHOP_PDT_TAB_DETAIL}
 */
ob_start();
?>
<div id="wpshop_product_feature"><ul>{WPSHOP_PDT_TABS}</ul>{WPSHOP_PDT_TAB_DETAIL}</div><?php
$tpl_element['product_attribute_container'] = ob_get_contents();
ob_end_clean();

/*	Define each tab for product attribute display	*/
/**
 * {WPSHOP_ATTRIBUTE_SET_CODE}
 * {WPSHOP_ATTRIBUTE_SET_NAME}
 */
ob_start();
?>
<li><a href="#{WPSHOP_ATTRIBUTE_SET_CODE}" >{WPSHOP_ATTRIBUTE_SET_NAME}</a></li><?php
$tpl_element['product_attribute_tabs'] = ob_get_contents();
ob_end_clean();

/*	Define each tab content for product attribute display	*/
/**
 * {WPSHOP_ATTRIBUTE_SET_CODE}
 * {WPSHOP_ATTRIBUTE_SET_CONTENT}
 */
ob_start();
?>
<div id="{WPSHOP_ATTRIBUTE_SET_CODE}"><ul>{WPSHOP_ATTRIBUTE_SET_CONTENT}</ul></div><?php
$tpl_element['product_attribute_tabs_detail'] = ob_get_contents();
ob_end_clean();

/*	Display each attribute label/value for products	*/
/**
 * {WPSHOP_PDT_ENTITY_CODE}
 * {WPSHOP_ATTRIBUTE_CODE}
 * {WPSHOP_ATTRIBUTE_LABEL}
 * {WPSHOP_ATTRIBUTE_VALUE}
 * {WPSHOP_ATTRIBUTE_VALUE_UNIT}
 */
ob_start();
?>
<li><span class="{WPSHOP_PDT_ENTITY_CODE}_frontend_attribute_label {WPSHOP_ATTRIBUTE_CODE}_label" >{WPSHOP_ATTRIBUTE_LABEL}</span>&nbsp;:&nbsp;<span class="{WPSHOP_PDT_ENTITY_CODE}_frontend_attribute_value {WPSHOP_ATTRIBUTE_CODE}_value" >{WPSHOP_ATTRIBUTE_VALUE}{WPSHOP_ATTRIBUTE_VALUE_UNIT}</span></li><?php
$tpl_element['product_attribute_display'] = ob_get_contents();
ob_end_clean();

/*	Define attribute unit template	*/
/**
 * {WPSHOP_ATTRIBUTE_UNIT}
 */
ob_start();
?>
&nbsp;({WPSHOP_ATTRIBUTE_UNIT})<?php
$tpl_element['product_attribute_unit'] = ob_get_contents();
ob_end_clean();

/*	Define attribute display for select list with internal data	*/
/**
 * {WPSHOP_ATTRIBUTE_VALUE_POST_LINK}
 * {WPSHOP_ATTRIBUTE_VALUE_POST_TITLE}
 */
ob_start();
?>
<a href="{WPSHOP_ATTRIBUTE_VALUE_POST_LINK}" target="wpshop_entity_element" >{WPSHOP_ATTRIBUTE_VALUE_POST_TITLE}</a><?php
$tpl_element['product_attribute_value_internal'] = ob_get_contents();
ob_end_clean();




/**
 *
 *
 * Product front display
 *
 *
 */
/*	Product complete sheet	*/
/*
 * {WPSHOP_PRODUCT_THUMBNAIL}
 * {WPSHOP_PRODUCT_GALERY_PICS}
 * {WPSHOP_PRODUCT_PRICE}
 * {WPSHOP_PRODUCT_INITIAL_CONTENT}
 * {WPSHOP_PRODUCT_BUTTON_ADD_TO_CART}
 * {WPSHOP_PRODUCT_BUTTON_QUOTATION}
 * {WPSHOP_PRODUCT_BUTTONS}
 * {WPSHOP_PRODUCT_BUTTONS}
 * {WPSHOP_PRODUCT_GALERY_DOCS}
 * {WPSHOP_PRODUCT_FEATURES}
 */
ob_start();
?>
<div id="product_main_information_container" >
	<div id="product_galery" >
		{WPSHOP_PRODUCT_THUMBNAIL}
		{WPSHOP_PRODUCT_GALERY_PICS}
	</div>
	<div id="product_wp_initial_content" >
		{WPSHOP_PRODUCT_PRICE}
		<p>{WPSHOP_PRODUCT_INITIAL_CONTENT}</p>
		{WPSHOP_PRODUCT_BUTTONS}
		<div id="product_document_galery_container" >{WPSHOP_PRODUCT_GALERY_DOCS}</div>
	</div>
</div>
<div id="product_attribute_container" >{WPSHOP_PRODUCT_FEATURES}</div><?php
$tpl_element['product_complete_tpl'] = ob_get_contents();
ob_end_clean();

/*	Product mini display (List)	*/
/*
 * {WPSHOP_PRODUCT_CLASS}
 * {WPSHOP_PRODUCT_EXTRA_STATE}
 * {WPSHOP_PRODUCT_PERMALINK}
 * {WPSHOP_PRODUCT_TITLE}
 * {WPSHOP_PRODUCT_THUMBNAIL}
 * {WPSHOP_PRODUCT_PRICE}
 * {WPSHOP_PRODUCT_DESCRIPTION}
 * {WPSHOP_PRODUCT_BUTTONS}
 *
 * {WPSHOP_PRODUCT_IS_NEW}
 * {WPSHOP_PRODUCT_IS_FEATURED}
 * {WPSHOP_PRODUCT_BUTTON_ADD_TO_CART}
 * {WPSHOP_PRODUCT_BUTTON_QUOTATION}
 * {WPSHOP_PRODUCT_EXCERPT}
 * {WPSHOP_PRODUCT_OUTPUT_TYPE}
 */
ob_start();
?>
<li class="product_main_information_container-mini-list clearfix wpshop_clear {WPSHOP_PRODUCT_CLASS}">
	{WPSHOP_PRODUCT_EXTRA_STATE}
	<a href="{WPSHOP_PRODUCT_PERMALINK}" class="product_thumbnail-mini-list" title="{WPSHOP_PRODUCT_TITLE}">{WPSHOP_PRODUCT_THUMBNAIL}</a>
	<span class="product_information-mini-list">
		<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" class="clearfix">
			<h2>{WPSHOP_PRODUCT_TITLE}</h2>
			{WPSHOP_PRODUCT_PRICE}
			<p class="wpshop_liste_description">{WPSHOP_PRODUCT_DESCRIPTION}</p>
		</a>
		{WPSHOP_PRODUCT_BUTTONS}
	</span>
</li><?php
$tpl_element['product_mini_list'] = ob_get_contents();
ob_end_clean();

/*	Product mini display (grid)	*/
/*
 * {WPSHOP_PRODUCT_CLASS}
 * {WPSHOP_PRODUCT_EXTRA_STATE}
 * {WPSHOP_PRODUCT_PERMALINK}
 * {WPSHOP_PRODUCT_TITLE}
 * {WPSHOP_PRODUCT_THUMBNAIL}
 * {WPSHOP_PRODUCT_PRICE}
 * {WPSHOP_PRODUCT_DESCRIPTION}
 * {WPSHOP_PRODUCT_BUTTONS}
 *
 * {WPSHOP_PRODUCT_IS_NEW}
 * {WPSHOP_PRODUCT_IS_FEATURED}
 * {WPSHOP_PRODUCT_BUTTON_ADD_TO_CART}
 * {WPSHOP_PRODUCT_BUTTON_QUOTATION}
 * {WPSHOP_PRODUCT_EXCERPT}
 * {WPSHOP_PRODUCT_OUTPUT_TYPE}
 */
ob_start();
?>
<li class="product_main_information_container-mini-grid {WPSHOP_PRODUCT_CLASS}">
	<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" >
		<span class="wpshop_mini_grid_thumbnail">{WPSHOP_PRODUCT_THUMBNAIL}</span>
		{WPSHOP_PRODUCT_EXTRA_STATE}
		<h2>{WPSHOP_PRODUCT_TITLE}</h2>
		{WPSHOP_PRODUCT_PRICE}
	</a>
	{WPSHOP_PRODUCT_BUTTONS}
</li><?php
$tpl_element['product_mini_grid'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><span class="wpshop_products_listing_price">{WPSHOP_PRODUCT_PRICE}</span><?php
$tpl_element['product_price_template_mini_output'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><h2>{WPSHOP_PRODUCT_PRICE}</h2><?php
$tpl_element['product_price_template_complete_sheet'] = ob_get_contents();
ob_end_clean();


/*	Sorting bloc criteria list	*/
/*
 * {WPSHOP_SORTING_CRITERIA_LIST}
 */
ob_start();
?>
	<span>
		<?php _e('Sorting','wpshop'); ?>
		<select name="sorting_criteria" class="hidden_sorting_criteria_field" >
			<option value="" selected="selected"><?php _e('Choose...','wpshop'); ?></option>
			{WPSHOP_SORTING_CRITERIA_LIST}
		</select>
	</span><?php
$tpl_element['product_listing_sorting_criteria'] = ob_get_contents();
ob_end_clean();


/*	Sorting bloc */
/*
 * {WPSHOP_SORTING_HIDDEN_FIELDS}
 * {WPSHOP_SORTING_CRITERIA}
 * {WPSHOP_DISPLAY_TYPE_STATE_GRID}
 * {WPSHOP_DISPLAY_TYPE_STATE_LIST}
 */
ob_start();
?>
<div class="sorting_bloc">
	{WPSHOP_SORTING_HIDDEN_FIELDS}{WPSHOP_SORTING_CRITERIA}
	<ul class="wpshop_sorting_tools">
		<li><a href="#" class="ui-icon product_asc_listing reverse_sorting" title="<?php _e('Reverse','wpshop'); ?>"></a></li>
		<li><a href="#" class="change_display_mode list_display{WPSHOP_DISPLAY_TYPE_STATE_LIST}" title="<?php _e('Change to list display','wpshop'); ?>"></a></li>
		<li><a href="#" class="change_display_mode grid_display{WPSHOP_DISPLAY_TYPE_STATE_GRID}" title="<?php _e('Change to grid display','wpshop'); ?>"></a></li>
	</ul>
</div><?php
$tpl_element['product_listing_sorting'] = ob_get_contents();
ob_end_clean();


/**
 *
 *
 * Product front attachment galery
 *
 *
 */
/*	Product thumbnail (No thumbnail)	*/
ob_start();
?>
<img src="<?php echo WPSHOP_DEFAULT_PRODUCT_PICTURE; ?>" alt="<?php _e('Product has no image', 'wpshop'); ?>" class="default_picture_thumbnail" /><?php
$tpl_element['product_thumbnail_default'] = ob_get_contents();
ob_end_clean();

/*	Product thumbnail	*/
/**
 * {WPSHOP_PRODUCT_THUMBNAIL_URL}
 * {WPSHOP_PRODUCT_THUMBNAIL}
 */
ob_start();
?>
<a href="{WPSHOP_PRODUCT_THUMBNAIL_URL}" id="product_thumbnail" class="wpshop_picture_zoom_in" >{WPSHOP_PRODUCT_THUMBNAIL}</a><?php
$tpl_element['product_thumbnail'] = ob_get_contents();
ob_end_clean();

/*	Product attachment galery	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}
 */
ob_start();
?>
<ul class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_galery clearfix" >{WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}</ul><?php
$tpl_element['product_attachment_picture_galery'] = ob_get_contents();
ob_end_clean();

/*	Product attachment item picture ()	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}
 * {WPSHOP_ATTACHMENT_ITEM_GUID}
 * {WPSHOP_ATTACHMENT_ITEM_PICTURE}
 */
ob_start();
?>
<li class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_item {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}" ><a href="{WPSHOP_ATTACHMENT_ITEM_GUID}" rel="appendix" >{WPSHOP_ATTACHMENT_ITEM_PICTURE}</a></li><?php
$tpl_element['product_attachment_item_picture'] = ob_get_contents();
ob_end_clean();

/*	Product attachment galery	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}
 */
ob_start();
?>
<ul class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_galery clearfix" >{WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}</ul><?php
$tpl_element['product_attachment_galery'] = ob_get_contents();
ob_end_clean();

/*	Product attachment item document	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}
 * {WPSHOP_ATTACHMENT_ITEM_GUID}
 * {WPSHOP_ATTACHMENT_ITEM_TITLE}
 */
ob_start();
?>
<li class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_item {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}" ><a href="{WPSHOP_ATTACHMENT_ITEM_GUID}" target="product_document" ><span>{WPSHOP_ATTACHMENT_ITEM_TITLE}</span></a></li><?php
$tpl_element['product_attachment_item_document'] = ob_get_contents();
ob_end_clean();



/**
 *
 *
 * Categories display
 *
 *
 */
/*	Mini category (list)	*/
/*
 * {WPSHOP_CATEGORY_LINK}
 * {WPSHOP_CATEGORY_THUMBNAIL}
 * {WPSHOP_CATEGORY_TITLE}
 * {WPSHOP_CATEGORY_DESCRIPTION}
 * {WPSHOP_CATEGORY_ITEM_WIDTH}
 *
 * {WPSHOP_CATEGORY_ID}
 * {WPSHOP_CATEGORY_DISPLAY_TYPE}
 */
ob_start();
?><div class="category_main_information_container-mini-list wpshop_clear" >
	<a href="{WPSHOP_CATEGORY_LINK}" >
	<div class="category_thumbnail-mini-list" >{WPSHOP_CATEGORY_THUMBNAIL}</div>
		<div class="category_information-mini-list" >
			<div class="category_title-mini-list" >{WPSHOP_CATEGORY_TITLE}</div>
			<div class="category_more-mini-list" >{WPSHOP_CATEGORY_DESCRIPTION}</div>
		</div>
	</a>
</div><?php
$tpl_element['category_mini_list'] = ob_get_contents();
ob_end_clean();

/*	Mini category (grid)	*/
/*
 * {WPSHOP_CATEGORY_LINK}
 * {WPSHOP_CATEGORY_THUMBNAIL}
 * {WPSHOP_CATEGORY_TITLE}
 * {WPSHOP_CATEGORY_DESCRIPTION}
 * {WPSHOP_CATEGORY_ITEM_WIDTH}
 *
 * {WPSHOP_CATEGORY_ID}
 * {WPSHOP_CATEGORY_DISPLAY_TYPE}
 */
ob_start();
?><div class="category_main_information_container-mini-grid" style="width:{WPSHOP_ITEM_WIDTH};" >
	<a href="{WPSHOP_CATEGORY_LINK}" >
		<div class="category_thumbnail-mini-grid" >{WPSHOP_CATEGORY_THUMBNAIL}</div>
		<div class="category_information-mini-grid" >
			<div class="category_title-mini-grid" >{WPSHOP_CATEGORY_TITLE}</div>
			<div class="category_title-mini-grid" >{WPSHOP_CATEGORY_DESCRIPTION}</div>
		</div>
	</a>
</div><?php
$tpl_element['category_mini_grid'] = ob_get_contents();
ob_end_clean();




/**
 *
 *
 * Account display
 *
 *
 */
/*	Login account display	*/
/*
 * {WPSHOP_ACCOUNT_LOGIN_FORM}
 * {WPSHOP_ACCOUNT_NEW_CREATION}
 */
ob_start();
?><div id="reponseBox"></div>
<form method="post" id="login_form" action="<?php echo WPSHOP_AJAX_FILE_URL; ?>">
	<input type="hidden" name="post" value="true" />
	<input type="hidden" name="elementCode" value="ajax_login" />
	<div class="create-account">{WPSHOP_ACCOUNT_LOGIN_FORM}</div>
	<input type="submit" name="submitLoginInfos" value="<?php _e('Login', 'wpshop'); ?>" />
</form>
<p>{WPSHOP_ACCOUNT_NEW_CREATION}</p><?php
$tpl_element['account_login_form'] = ob_get_contents();
ob_end_clean();


/*	New entity quick add form	*/
ob_start();
?>
<div id="new_entity_quick_form_container" >
	<span id="wpshop_loading"> </span>
	<div class="wpshop_quick_add_entity_result wpshopHide" id="wpshop_quick_add_entity_result" ></div>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="POST" id="new_entity_quick_form">
		<input type="hidden" name="attribute_set_id" id="attribute_set_id" value="{WPSHOP_ENTITY_ATTRIBUTE_SET_ID}" />
		<input type="hidden" name="entity_type" id="entity_type" value="{WPSHOP_ENTITY_TYPE}" />
		<input type="hidden" name="action" id="action" value="wpshop_quick_add_entity" />
		<input type="hidden" name="wpshop_ajax_nonce" id="wpshop_ajax_nonce" value="{WPSHOP_ENTITY_QUICK_ADDING_FORM_NONCE}" />
		{WPSHOP_NEW_ENTITY_FORM_DETAILS}
		<input type="submit" name="quick_entity_add_button" id="quick_entity_add_button" value="{WPSHOP_ENTITY_QUICK_ADD_BUTTON_TEXT}" />
	</form>
	{WPSHOP_DIALOG_BOX}
</div><?php
$tpl_element['quick_entity_add_form'] = ob_get_contents();
ob_end_clean();


ob_start();
?>
<input type="text" value="{WPSHOP_WP_FIELD_VALUE}" name="wp_fields[{WPSHOP_WP_FIELD_NAME}]" id="wp_fields_{WPSHOP_WP_FIELD_NAME}" /><?php
$tpl_element['quick_entity_wp_internal_field_text'] = ob_get_contents();
ob_end_clean();

ob_start();
?>
<input type="file" value="{WPSHOP_WP_FIELD_VALUE}" name="wp_fields[{WPSHOP_WP_FIELD_NAME}]" id="wp_fields_{WPSHOP_WP_FIELD_NAME}" /><?php
$tpl_element['quick_entity_wp_internal_field_file'] = ob_get_contents();
ob_end_clean();

ob_start();
?>
<div class="clear">
	<div class="wpshop_form_label {WPSHOP_ENTITY_TYPE_TO_CREATE}_{WPSHOP_WP_FIELD_NAME}_label _{WPSHOP_WP_FIELD_NAME}_label alignleft">{WPSHOP_WP_FIELD_LABEL}</div>
	<div class="wpshop_form_input_element {WPSHOP_ENTITY_TYPE_TO_CREATE}_{WPSHOP_WP_FIELD_NAME}_input _{WPSHOP_WP_FIELD_NAME}_input alignleft">{WPSHOP_WP_FIELD_INPUT}</div>
</div><?php
$tpl_element['quick_entity_wp_internal_field_output'] = ob_get_contents();
ob_end_clean();

?>