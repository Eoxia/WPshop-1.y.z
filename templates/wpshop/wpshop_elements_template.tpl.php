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
/*	"Product unavailable" button 	|					Bouton Ajouter au panier Désactivé */
ob_start();
?>
<button itemprop="availability" content="out_of_stock" type="button" disabled="disabled" class="no_stock"><?php _e('Soon available', 'wpshop'); ?></button><?php
$tpl_element['unavailable_product_button'] = ob_get_contents();
ob_end_clean();


/*	"Add to cart" button	|							Bouton Ajouter au panier */
/**
 * {WPSHOP_PRODUCT_ID}
 */
ob_start();
?>
<button itemprop="availability" content="in_stock" type="button" id="wpshop_add_to_cart_{WPSHOP_PRODUCT_ID}" class="wpshop_add_to_cart_button wpshop_products_listing_bton_panier_active"><?php _e('Add to cart', 'wpshop'); ?></button><span class="wpshop_cart_loading_picture"></span><?php
$tpl_element['add_to_cart_button'] = ob_get_contents();
ob_end_clean();

/*	"Go to product configuration" button	|			Bouton de configuration du produit si il contient des declinaisons */
ob_start();
?>
<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" id="wpshop_add_to_cart_{WPSHOP_PRODUCT_ID}" itemprop="availability" content="to_configure" class="wpshop_configure_product_button wpshop_products_listing_bton_panier_active" ><?php _e('Configure product', 'wpshop'); ?></a><?php
$tpl_element['configure_product_button'] = ob_get_contents();
ob_end_clean();


/*	"Ask quotation" button	| 							Bouton Demander un devis */
/**
 * {WPSHOP_PRODUCT_ID}
 */
ob_start();
?>
<button itemprop="availability" content="preorder" type="button" id="wpshop_ask_a_quotation_{WPSHOP_PRODUCT_ID}" class="wpshop_products_listing_bton_panier_active wpshop_ask_a_quotation_button"><?php _e('Ask a quotation', 'wpshop'); ?></button><?php
$tpl_element['ask_quotation_button'] = ob_get_contents();
ob_end_clean();



/*	Mini cart container	|								Mini Panier Container */
/**
 * {WPSHOP_CART_MINI_CONTENT}
 */
ob_start();
?>
<div class="wpshop_cart_summary_detail" ></div><div class="wpshop_cart_alert" ></div>
<div class="wpshop_cart_summary" >{WPSHOP_CART_MINI_CONTENT}</div><?php
$tpl_element['mini_cart_container'] = ob_get_contents();
ob_end_clean();


/*	Mini cart content									Mini Panier contenu */
/**
 * {WPSHOP_CART_LINK}										- Lien vers le panier
 * {WPSHOP_PDT_CPT}											- Nombre de produit dans le panier
 * {WPSHOP_CART_TOTAL_AMOUNT}								- Montant total du panier
 */
ob_start();
?>
<a href="{WPSHOP_CART_LINK}"><?php echo sprintf(__('Your have %s item(s) in your cart','wpshop'), '{WPSHOP_PDT_CPT}').' - {WPSHOP_CART_TOTAL_AMOUNT}'?> {WPSHOP_CURRENCY}</a><?php
$tpl_element['mini_cart_content'] = ob_get_contents();
ob_end_clean();


/*	Cart table header and footer						Header tableau panier (Page) */
ob_start();
?>	<tr>
		<th><?php _e('Product name', 'wpshop'); ?></th>
		<th class="center"><?php _e('Unit price ET', 'wpshop'); ?></th>
		<th class="center"><?php _e('Quantity', 'wpshop'); ?></th>
		<th><?php _e('Total price ET', 'wpshop'); ?></th>
		<th><?php _e('Total price ATI', 'wpshop'); ?></th>
		<th class="center"><?php _e('Action', 'wpshop'); ?></th>
	</tr><?php
$tpl_element['cart_table_column_def'] = ob_get_contents();
ob_end_clean();

/*	Cart table header and footer						Tableau panier (page) */
ob_start();
?><table id="cartContent">
<thead>
{WPSHOP_CART_TABLE_COLUMN_DEF}
</thead>
<tfoot>
{WPSHOP_CART_TABLE_COLUMN_DEF}
</tfoot>
<tbody>
{WPSHOP_CART_CONTENT}
</tbody>
</table><?php
$tpl_element['cart_table_def'] = ob_get_contents();
ob_end_clean();

/*	Cart line detail									Ligne tableau panier (page) */
ob_start();
?><tr id="product_{WPSHOP_CART_LINE_ITEM_ID}">
	<td>
		<input type="hidden" value="{WPSHOP_CART_LINE_ITEM_QTY}" name="currentProductQty" /><a href="{WPSHOP_CART_LINE_ITEM_LINK}">{WPSHOP_CART_LINE_ITEM_NAME}</a>
		<ul class="wpshop_cart_variation_details" >{WPSHOP_CART_PRODUCT_MORE_INFO}</ul>
	</td>
	<td class="product_price_ht center">{WPSHOP_CART_LINE_ITEM_PUHT} {WPSHOP_CURRENCY}</td>
	<td class="center" style="min-width:125px;">{WPSHOP_CART_LINE_ITEM_QTY_}</td>
	<td class="total_price_ht center"><span>{WPSHOP_CART_LINE_ITEM_TPHT} {WPSHOP_CURRENCY}</span></td>
	<td class="total_price_ttc center"><span>{WPSHOP_CART_LINE_ITEM_TPTTC} {WPSHOP_CURRENCY}</span></td>
	<td class="center">{WPSHOP_CART_LINE_ITEM_REMOVER}</td>
</tr><?php
$tpl_element['cart_line'] = ob_get_contents();
ob_end_clean();


/*	Product quantity updater	| 						Panier tableau formulaire + - quantité */
ob_start();
?><a href="#" class="productQtyChange">-</a><input type="text" value="{WPSHOP_CART_LINE_ITEM_QTY}" name="productQty" id="wpshop_product_order_{WPSHOP_CART_LINE_ITEM_ID}"  /><a href="#" class="productQtyChange">+</a><?php
$tpl_element['cart_qty_content'] = ob_get_contents();
ob_end_clean();


/*	Product cart remover	|							Panier tableau supprimer élément */
ob_start();
?><a href="#" class="remove" title="<?php _e('Remove', 'wpshop'); ?>"><?php _e('Remove', 'wpshop'); ?></a><?php
$tpl_element['cart_line_remove'] = ob_get_contents();
ob_end_clean();


/*	Product variation detail in cart					Panier detail des variations */
ob_start();
?><li class="wpshop_cart_variation_details_item wpshop_cart_variation_details_item_{WPSHOP_VARIATION_ID} wpshop_cart_variation_details_item_{WPSHOP_VARIATION_ATT_CODE}" >{WPSHOP_VARIATION_NAME} : {WPSHOP_VARIATION_VALUE}</li><?php
$tpl_element['cart_variation_detail'] = ob_get_contents();
ob_end_clean();


/*	Vouncher field into cart							Coupons de reduction */
ob_start();
?><div class="wpshop_cart_vouncher_field_container" ><?php _e('Discount coupon','wpshop'); ?> : <input type="text" name="coupon_code" value="" /> <a href="#" class="submit_coupon"><?php _e('Submit the coupon','wpshop'); ?></a></div><?php
$tpl_element['cart_vouncher_part'] = ob_get_contents();
ob_end_clean();


/*	Empty cart button									Vidage du panier */
ob_start();
?><div class="wpshop_cart_buttons_container" ><div class="alignright" ><input type="submit" value="{WPSHOP_CART_BUTTON_VALIDATE_TEXT}" name="cartCheckout" class="alignright" /><br/><a href="#" class="emptyCart alignright" >{WPSHOP_BUTTON_EMPTY_CART_TEXT}</a></div></div><?php
$tpl_element['cart_buttons'] = ob_get_contents();
ob_end_clean();


/*	Cart Total summaries line content 					Contenu des lignes des totaux du panier */
ob_start();
?><div class="wpshop_cart_summary_line{WPSHOP_CART_SUMMARY_LINE_SPECIFIC}" >{WPSHOP_CART_SUMMARY_TITLE} : <span class="right{WPSHOP_CART_SUMMARY_AMOUNT_CLASS}" >{WPSHOP_CART_SUMMARY_AMOUNT} {WPSHOP_CURRENCY}</span></div><?php
$tpl_element['cart_summary_line_content'] = ob_get_contents();
ob_end_clean();


/*	Cart main page						Template general page panier */
ob_start();
?><span id="wpshop_loading">&nbsp;</span>
<div class="cart" >
	{WPSHOP_CART_OUTPUT}
	<div>
		<div><?php _e('Total ET','wpshop'); ?> : <span class="total_ht right">{WPSHOP_CART_PRICE_ET} {WPSHOP_CURRENCY}</span></div>
		{WPSHOP_CART_TAXES}
		<div id="order_shipping_cost" ><?php _e('Shipping','wpshop'); ?> <?php _e('ATI','wpshop'); ?> : <span class="right">{WPSHOP_CART_SHIPPING_COST} {WPSHOP_CURRENCY}</span></div>
		{WPSHOP_CART_DISCOUNT_SUMMARY}
		<div class="bold clear" ><?php _e('Total ATI','wpshop'); ?> : <span class="total_ttc right bold">{WPSHOP_CART_TOTAL_ATI} {WPSHOP_CURRENCY}</span></div>
		{WPSHOP_CART_VOUNCHER}
	</div>
	{WPSHOP_CART_BUTTONS}
</div><?php
$tpl_element['cart_main_page'] = ob_get_contents();
ob_end_clean();


/*	product added to cart popup							Panier Popup après ajout au panier */
ob_start();
?>
<div class="wpshop_superBackground"></div>
<div class="wpshop_popupAlert">
	<h1><?php _e('Your product has been sucessfuly added to your cart', 'wpshop'); ?></h1>
	<a href="{WPSHOP_CART_LINK}"><?php _e('View my cart','wpshop'); ?></a> <input type="button" class="button-secondary closeAlert" value="<?php _e('Continue shopping','wpshop'); ?>" />
</div><?php
$tpl_element['product_added_to_cart_message'] = ob_get_contents();
ob_end_clean();


/*	Current product variation	*/


/*	Product is new	|									Nouveauté produit */
ob_start();
?>
<span class="vignette_nouveaute"><?php _e('New', 'wpshop'); ?></span><?php
$tpl_element['product_is_new_sticker'] = ob_get_contents();
ob_end_clean();


/*	Product is featured	|								En vedette produit */
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
/*	Display the global container for product attribute	| 				Container single produit ui tab attribute */
/**
 * {WPSHOP_PDT_TABS}
 * {WPSHOP_PDT_TAB_DETAIL}
 */
ob_start();
?>
<div id="wpshop_product_feature"><ul>{WPSHOP_PDT_TABS}</ul>{WPSHOP_PDT_TAB_DETAIL}</div><?php
$tpl_element['product_attribute_container'] = ob_get_contents();
ob_end_clean();

/*	Define each tab for product attribute display						Ui tab attribute */
/**
 * {WPSHOP_ATTRIBUTE_SET_CODE}
 * {WPSHOP_ATTRIBUTE_SET_NAME}
 */
ob_start();
?>
<li><a href="#{WPSHOP_ATTRIBUTE_SET_CODE}" >{WPSHOP_ATTRIBUTE_SET_NAME}</a></li><?php
$tpl_element['product_attribute_tabs'] = ob_get_contents();
ob_end_clean();

/*	Define each tab content for product attribute display				Ui tab attribute */
/**
 * {WPSHOP_ATTRIBUTE_SET_CODE}
 * {WPSHOP_ATTRIBUTE_SET_CONTENT}
 */
ob_start();
?>
<div id="{WPSHOP_ATTRIBUTE_SET_CODE}"><ul>{WPSHOP_ATTRIBUTE_SET_CONTENT}</ul></div><?php
$tpl_element['product_attribute_tabs_detail'] = ob_get_contents();
ob_end_clean();

/*	Display each attribute label/value for products	|					Ui tab attribute */
/**
 * {WPSHOP_PDT_ENTITY_CODE}
 * {WPSHOP_ATTRIBUTE_CODE}
 * {WPSHOP_ATTRIBUTE_LABEL}
 * {WPSHOP_ATTRIBUTE_VALUE}
 * {WPSHOP_ATTRIBUTE_VALUE_UNIT}
 */
ob_start();
?>
<li>
	<span class="{WPSHOP_PDT_ENTITY_CODE}_frontend_attribute_label {WPSHOP_ATTRIBUTE_CODE}_label" >
	{WPSHOP_ATTRIBUTE_LABEL}
	</span> :
	<span class="{WPSHOP_PDT_ENTITY_CODE}_frontend_attribute_value {WPSHOP_ATTRIBUTE_CODE}_value" >
		{WPSHOP_ATTRIBUTE_VALUE}{WPSHOP_ATTRIBUTE_VALUE_UNIT}
	</span>
</li><?php
$tpl_element['product_attribute_display'] = ob_get_contents();
ob_end_clean();

/*	Define attribute unit template	|									Unités */
/**
 * {WPSHOP_ATTRIBUTE_UNIT}
 */
ob_start();
?>
&nbsp;({WPSHOP_ATTRIBUTE_UNIT})<?php
$tpl_element['product_attribute_unit'] = ob_get_contents();
ob_end_clean();

/*	Define attribute display for select list with internal data	|		Variations */
/**
 * {WPSHOP_ATTRIBUTE_VALUE_POST_LINK}
 * {WPSHOP_ATTRIBUTE_VALUE_POST_TITLE}
 */
ob_start();
?>
<a href="{WPSHOP_ATTRIBUTE_VALUE_POST_LINK}" target="wpshop_entity_element" >{WPSHOP_ATTRIBUTE_VALUE_POST_TITLE}</a><?php
$tpl_element['product_attribute_value_internal'] = ob_get_contents();
ob_end_clean();


/*	Define variation display	*/
ob_start();
?><div class="wpshop_variation{WPSHOP_VARIATION_CONTAINER_CLASS}" ><label for="{WPSHOP_VARIATION_IDENTIFIER}"{WPSHOP_VARIATION_LABEL_HELPER} class="wpshop_variation_label" >{WPSHOP_VARIATION_LABEL}</label> : {WPSHOP_VARIATION_INPUT}</div><?php
$tpl_element['product_variation_item'] = ob_get_contents();
ob_end_clean();

/*	Define variation display	*/
ob_start();
?>{WPSHOP_VARIATION_VALUE}<?php
$tpl_element['product_variation_item_possible_values'] = ob_get_contents();
ob_end_clean();

/*	Define variation display	*/
ob_start();
?><form action="<?php echo admin_url('admin-ajax.php')?>" method="POST" id="wpshop_add_to_cart_form" ><input type="hidden" name="wpshop_pdt" id="wpshop_pdt" value="{WPSHOP_VARIATION_FORM_ELEMENT_ID}" /><input type="hidden" name="action" value="wpshop_add_product_to_cart" /><input type="hidden" name="wpshop_cart_type" value="cart" />{WPSHOP_VARIATION_FORM_VARIATION_LIST}</form><?php
$tpl_element['product_variation_form'] = ob_get_contents();
ob_end_clean();



/**
 *
 *
 * Product front display
 *
 *
 */
/*	Product complete sheet	|										Détails produits (single) */
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
<div id="product_main_information_container" itemscope itemtype="http://data-vocabulary.org/Product" >
	<div id="product_galery" >
		{WPSHOP_PRODUCT_THUMBNAIL}
		{WPSHOP_PRODUCT_GALERY_PICS}
	</div>
	<div id="product_wp_initial_content" itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers" >
		{WPSHOP_PRODUCT_PRICE}
		<p itemprop="description">{WPSHOP_PRODUCT_INITIAL_CONTENT}</p>
		{WPSHOP_PRODUCT_VARIATIONS}
		{WPSHOP_PRODUCT_BUTTONS}
		<div id="product_document_galery_container" >{WPSHOP_PRODUCT_GALERY_DOCS}</div>
	</div>
</div>
<div id="product_attribute_container" >{WPSHOP_PRODUCT_FEATURES}</div><?php
$tpl_element['product_complete_tpl'] = ob_get_contents();
ob_end_clean();


/*	Product mini display (List)										Produits mini liste */
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
<li class="product_main_information_container-mini-list clearfix wpshop_clear {WPSHOP_PRODUCT_CLASS}" itemscope itemtype="http://data-vocabulary.org/Product" >
	{WPSHOP_PRODUCT_EXTRA_STATE}
	<a href="{WPSHOP_PRODUCT_PERMALINK}" class="product_thumbnail-mini-list" title="{WPSHOP_PRODUCT_TITLE}">{WPSHOP_PRODUCT_THUMBNAIL}</a>
	<span class="product_information-mini-list" itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers">
		<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" class="clearfix">
			<h2 itemprop="name" >{WPSHOP_PRODUCT_TITLE}</h2>
			{WPSHOP_PRODUCT_PRICE}
			<p itemprop="description" class="wpshop_liste_description">{WPSHOP_PRODUCT_EXCERPT}</p>
		</a>
		{WPSHOP_PRODUCT_BUTTONS}
	</span>
</li><?php
$tpl_element['product_mini_list'] = ob_get_contents();
ob_end_clean();

/*	Product mini display (grid)									Produits mini grid */
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
<li class="product_main_information_container-mini-grid {WPSHOP_PRODUCT_CLASS}" itemscope itemtype="http://data-vocabulary.org/Product" >
	<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers" >
		<span class="wpshop_mini_grid_thumbnail">{WPSHOP_PRODUCT_THUMBNAIL}</span>
		{WPSHOP_PRODUCT_EXTRA_STATE}
		<h2 itemprop="name" >{WPSHOP_PRODUCT_TITLE}</h2>
		{WPSHOP_PRODUCT_PRICE}
	</a>
	{WPSHOP_PRODUCT_BUTTONS}
</li><?php
$tpl_element['product_mini_grid'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><div class="container_product_listing" ><ul class="products_listing clearfix{WPSHOP_PRODUCT_CONTAINER_TYPE_CLASS}" >{WPSHOP_PRODUCT_LIST}</ul></div><?php
$tpl_element['product_list_container'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><span itemprop="price" class="wpshop_products_listing_price">{WPSHOP_PRODUCT_PRICE}</span><?php
$tpl_element['product_price_template_mini_output'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><h2 itemprop="price" class="wpshop_product_price" >{WPSHOP_PRODUCT_PRICE}</h2><?php
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
<li class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_item {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}" ><a href="{WPSHOP_ATTACHMENT_ITEM_GUID}" rel="appendix" >{WPSHOP_ATTACHMENT_ITEM_PICTURE_THUMBNAIL}</a></li><?php
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



/*	Product attachment item document	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}
 * {WPSHOP_ATTACHMENT_ITEM_GUID}
 * {WPSHOP_ATTACHMENT_ITEM_TITLE}
 */
ob_start();
?>
<li class="{WPSHOP_CUSTOMER_ADDRESS_ELEMENT_KEY}" >{WPSHOP_CUSTOMER_ADDRESS_ELEMENT}</li><?php
$tpl_element['customer_address_display'] = ob_get_contents();
ob_end_clean();




/**
 *
 *
 * Account display
 *
 *
 */
/*	Account form	*/
ob_start();
?><h2><?php _e('Personal information', 'wpshop'); ?></h2><div class="wpshop_customer_personnal_informations_form_container" >{WPSHOP_ACCOUNT_FORM_FIELD}</div><?php
$tpl_element['wpshop_account_form'] = ob_get_contents();
ob_end_clean();

/*	Account / Address form input	*/
ob_start();
?><div class="formField{WPSHOP_CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS}" ><label>{WPSHOP_CUSTOMER_FORM_INPUT_LABEL}</label>{WPSHOP_CUSTOMER_FORM_INPUT_FIELD}</div><?php
$tpl_element['wpshop_account_form_input'] = ob_get_contents();
ob_end_clean();

/*	Account / Address form HIDDEN input	*/
ob_start();
?>{WPSHOP_CUSTOMER_FORM_INPUT_FIELD}<?php
$tpl_element['wpshop_account_form_hidden_input'] = ob_get_contents();
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
		<div class="wpshop_new_entity_form_field_container" >
			<div class="wpshop_new_entity_form_field wpshop_new_entity_form_field_specific" >{WPSHOP_NEW_ENTITY_FORM_DETAILS}</div>
		</div>
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


ob_start();
?>
<div class="wpshop_attribute_new_creator_condition" ><?php _e('Or', 'wpshop'); ?></div><div class="wpshop_attribute_new_creator_field" ><input type="text" placeholder="<?php _e('Create a new element', 'wsphop'); ?>" name="{WPSHOP_NEW_ELEMENT_CREATION_FIELD}" /></div><?php
$tpl_element['quick_entity_specific_field_new_element'] = ob_get_contents();
ob_end_clean();


ob_start();
?><div class="wpshop_product_variation_summary_main_container" ><h3 class="widget-title"><?php _e('Product configuration summary', 'wpshop'); ?></h3><div class="wpshop_product_variation_summary_currency_selector" >{WPSHOP_CURRENCY_SELECTOR}</div><div class="wpshop_product_variation_summary_container" id="wpshop_product_variation_summary_container" ></div></div><?php
$tpl_element['wpshop_product_configuration_summary'] = ob_get_contents();
ob_end_clean();


ob_start();
?><div class="wpshop_product_variation_summary_product_name" >{WPSHOP_PRODUCT_MAIN_INFO_PRODUCT_NAME}</div>
<ul class="wpshop_product_variation_summary_product_details" >{WPSHOP_PRODUCT_VARIATION_SUMMARY_DETAILS}</ul>
<div class="wpshop_product_variation_summary_product_final_price alignright" ><?php _e('Product final price', 'wpshop'); ?> {WPSHOP_PRODUCT_MAIN_INFO_PRODUCT_PRICE} {WPSHOP_CURRENCY_CHOOSEN} </div><?php
$tpl_element['wpshop_product_configuration_summary_detail'] = ob_get_contents();
ob_end_clean();


ob_start();
?><div class="wpshop_product_variation_value_detail_main_container" id="wpshop_product_variation_value_detail_main_container" ></div><?php
$tpl_element['wpshop_product_variation_value_detail_container'] = ob_get_contents();
ob_end_clean();

ob_start();
?><h3 class="widget-title"><?php _e('Details about', 'wpshop'); ?> {WPSHOP_VARIATION_ATTRIBUTE_NAME_FOR_DETAIL}</h3>
<div class="wpshop_product_variation_value_detail_container" >
	<div class="wpshop_product_variation_value_detail_title" >{WPSHOP_VARIATION_VALUE_TITLE_FOR_DETAIL}</div>
	<div class="wpshop_product_variation_value_detail_description" >{WPSHOP_VARIATION_VALUE_DESC_FOR_DETAIL}</div>
	<div class="wpshop_product_variation_value_detail_link" ><a href="{WPSHOP_VARIATION_VALUE_LINK_FOR_DETAIL}" target="_blank" ><?php _e('View details', 'wpshop'); ?></a></div>
</div><?php
$tpl_element['wpshop_product_variation_value_detail_content'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Checkout page
 *
 */
ob_start();
?><form method="post" name="checkoutForm" action="<?php echo get_permalink(get_option('wpshop_checkout_page_id')); ?>" >
	<h2>{WPSHOP_CHECKOUT_SUMMARY_TITLE}</h2>
	{WPSHOP_CHECKOUT_CUSTOMER_ADDRESSES_LIST}
	{WPSHOP_CHECKOUT_CART_CONTENT}
	{WPSHOP_CHECKOUT_TERM_OF_SALES}
	<div>
		<?php _e('Comments about the order','wpshop'); ?>
		<textarea name="order_comments"></textarea>
	</div>
	{WPSHOP_CHECKOUT_PAYMENT_METHODS}
	<div{WPSHOP_CHECKOUT_PAYMENT_BUTTONS_CONTAINER}>
		{WPSHOP_CHECKOUT_PAYMENT_BUTTONS}
	</div>
</form><?php
$tpl_element['wpshop_checkout_page'] = ob_get_contents();
ob_end_clean();

/**
 * Checkout page validation button
 */
ob_start();
?><input type="submit" name="takeOrder" value="{WPSHOP_CHECKOUT_PAGE_VALIDATION_BUTTON_TEXT}" /><?php
$tpl_element['wpshop_checkout_page_validation_button'] = ob_get_contents();
ob_end_clean();

/**
 * Payment method bloc
 */
ob_start();
?><table class="blockPayment{WPSHOP_CHECKOUT_PAYMENT_METHOD_STATE_CLASS}">
	<tr>
		<td class="paymentInput rounded-left"><input type="radio" name="modeDePaiement"{WPSHOP_CHECKOUT_PAYMENT_METHOD_INPUT_STATE} value="{WPSHOP_CHECKOUT_PAYMENT_METHOD_IDENTIFIER}" /></td>
		<td class="paymentImg"><img src="<?php echo WPSHOP_TEMPLATES_URL; ?>{WPSHOP_CHECKOUT_PAYMENT_METHOD_ICON}" alt="{WPSHOP_CHECKOUT_PAYMENT_METHOD_NAME}" title="<?php echo sprintf(__('Pay by %s', 'wpshop'), '{WPSHOP_CHECKOUT_PAYMENT_METHOD_NAME}'); ?>" /></td>
		<td class="paymentName">{WPSHOP_CHECKOUT_PAYMENT_METHOD_NAME}</td>
		<td class="last rounded-right">{WPSHOP_CHECKOUT_PAYMENT_METHOD_EXPLANATION}</td>
	</tr>
</table><?php
$tpl_element['wpshop_checkout_page_payment_method_bloc'] = ob_get_contents();
ob_end_clean();

/**
 * Check method confiramtion message
 */
ob_start();
?><p><?php _e('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop'); ?></p>
<p><?php _e('You have to send the check with the good amount to the adress :', 'wpshop'); ?></p>
<p>{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_NAME}<br/>
{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_STREET}<br/>
{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_POSTCODE}, {WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_CITY}<br/>
{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_COUNTRY}</p>
<p><?php _e('Your order will be shipped upon receipt of the check.', 'wpshop'); ?></p><?php
$tpl_element['wpshop_checkout_page_check_confirmation_message'] = ob_get_contents();
ob_end_clean();


/**
 *
 *
 * Frontend search
 *
 *
 */
/*	Form field	*/
ob_start();
?>
<div><label{WPSHOP_FIELD_LABEL_POINTER}>{WPSHOP_FIELD_LABEL_TEXT}</label> : {WPSHOP_FIELD_INPUT}</div><?php
$tpl_element['advanced_search_form_input'] = ob_get_contents();
ob_end_clean();

/*	Form	*/
ob_start();
?>
<form method="post" >
	<div><label for="wpshop_search_post_title" ><?php _e('Name','wpshop'); ?></label> : <input type="text" class="wpshop_advanced_search_field wpshop_advanced_search_field_post_title" name="wpshop_search_post_title" name="wpshop_search_post_title"  value="{WPSHOP_SEARCHED_POST_TITLE}" /></div>
	{WPSHOP_SPECIAL_FIELDS}
	<input type="submit" name="search" value="<?php _e('Search','wpshop'); ?>" />
</form><?php
$tpl_element['advanced_search_form'] = ob_get_contents();
ob_end_clean();

?>