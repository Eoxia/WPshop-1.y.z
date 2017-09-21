<?php if ( !defined( 'ABSPATH' ) ) exit;
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
<span itemprop="availability" content="out_of_stock" class="wps-label wps-vert" >
	<?php _e('Soon available', 'wpshop'); ?>
</span>
<?php
$tpl_element['unavailable_product_button'] = ob_get_contents();
ob_end_clean();


/*	"Add to cart" button	|							Bouton Ajouter au panier */
/**
 * {WPSHOP_PRODUCT_ID}
 */
ob_start();
?>
<button itemprop="availability" content="in_stock" data-nonce="<?php echo wp_create_nonce( 'ajax_pos_product_variation_selection' ); ?>" id="wpshop_add_to_cart_{WPSHOP_PRODUCT_ID}" class="wpshop_add_to_cart_button wps-bton-first"><i class="wps-icon-basket"></i><?php _e('Add to cart', 'wpshop'); ?></button>
<?php
$tpl_element['add_to_cart_button'] = ob_get_contents();
ob_end_clean();

/*	"Go to product configuration" button	|			Bouton de configuration du produit si il contient des declinaisons */
ob_start();
?>

<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" role="button" id="wpshop_add_to_cart_{WPSHOP_PRODUCT_ID}" itemprop="availability" content="to_configure" class="wps-bton-first"><i class="wps-icon-pencil"></i><?php _e('Configure product', 'wpshop'); ?></a>
<?php
$tpl_element['configure_product_button'] = ob_get_contents();
ob_end_clean();


/*	"Ask quotation" button	| 							Bouton Demander un devis */
/**
 * {WPSHOP_PRODUCT_ID}
 */
ob_start();
?>
<button itemprop="availability" content="in_stock" data-nonce="<?php echo wp_create_nonce( 'ajax_pos_product_variation_selection' ); ?>" id="wpshop_ask_a_quotation_{WPSHOP_PRODUCT_ID}" class="wpshop_ask_a_quotation_button wps-bton-second"><i class="wps-icon-quotation"></i><?php _e('Ask a quotation', 'wpshop'); ?></button><?php
$tpl_element['ask_quotation_button'] = ob_get_contents();
ob_end_clean();

/*	"Ask quotation" button	| 							Bouton Demander un devis */
/**
 * {WPSHOP_PRODUCT_ID}
 */
ob_start();
?>
<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" id="wpshop_add_a_quotation_{WPSHOP_PRODUCT_ID}" itemprop="availability" content="to_configure" class="wpshop_products_listing_bton_panier_active wpshop_ask_a_quotation_button  wps-bton-second" ><?php _e('Ask a quotation', 'wpshop'); ?></a><?php
$tpl_element['configure_quotation_button'] = ob_get_contents();
ob_end_clean();

/*	Mini cart container	|								Mini Panier Container */
/**
 * {WPSHOP_CART_MINI_CONTENT}
 */
ob_start();
?>
<div class="wpshop_cart_summary_detail" ></div><div class="wpshop_cart_alert" ></div>
<div class="wpshop_cart_summary" data-nonce="<?php echo wp_create_nonce( '' ); ?>" >{WPSHOP_CART_MINI_CONTENT}</div>
<?php
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
<a href="{WPSHOP_CART_LINK}"><?php echo sprintf(__('Your have %s item(s) in your cart','wpshop'), '{WPSHOP_PDT_CPT}').' - {WPSHOP_CART_TOTAL_AMOUNT}'?> {WPSHOP_CURRENCY}</a>
<div class="wpshop_cart_free_shipping_cost_alert">{WPSHOP_FREE_SHIPPING_COST_ALERT}</div>

<?php
$tpl_element['mini_cart_content'] = ob_get_contents();
ob_end_clean();


/*	Cart table header and footer						Header tableau panier (Page) */
ob_start();
?>	<tr>
		<th></th>
		<th><?php _e('Product name', 'wpshop'); ?></th>
		<th class="center"><?php _e('Unit price ET', 'wpshop'); ?></th>
		<th class="center"><?php _e('Quantity', 'wpshop'); ?></th>
		<th class="center"><?php _e('Discount', 'wpshop'); ?></th>
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
	<td class="cart_product_picture">{WPSHOP_CART_LINE_ITEM_PICTURE}</td>
	<td>
		<input type="hidden" value="{WPSHOP_CART_LINE_ITEM_QTY}" name="currentProductQty" />{WPSHOP_CART_PRODUCT_NAME}
		<ul class="wpshop_cart_variation_details" >{WPSHOP_CART_PRODUCT_MORE_INFO}</ul>
	</td>
	<td class="product_price_ht center">{WPSHOP_CART_LINE_ITEM_PUHT} {WPSHOP_CURRENCY}</td>
	<td class="center" style="min-width:125px;">{WPSHOP_CART_LINE_ITEM_QTY_}</td>
	<td class="total_price_ht center" style="min-width:60px;"><span>{WPSHOP_CART_LINE_ITEM_DISCOUNT_AMOUNT} {WPSHOP_CURRENCY}</span></td>
	<td class="total_price_ht center"><span>{WPSHOP_CART_LINE_ITEM_TPHT} {WPSHOP_CURRENCY}</span></td>
	<td class="total_price_ttc center"><span>{WPSHOP_CART_LINE_ITEM_TPTTC} {WPSHOP_CURRENCY}</span></td>
	<td class="center">{WPSHOP_CART_LINE_ITEM_REMOVER}</td>
</tr><?php
$tpl_element['cart_line'] = ob_get_contents();
ob_end_clean();


/*	Product link	| 						 */
ob_start();
?><a href="{WPSHOP_CART_LINE_ITEM_LINK}">{WPSHOP_CART_LINE_ITEM_NAME}</a><?php
$tpl_element['cart_product_name'] = ob_get_contents();
ob_end_clean();


/*	Product quantity updater	| 						Panier tableau formulaire + - quantité */
ob_start();
?><a href="#" class="productQtyChange wpshop_less_product_qty_in_cart" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>">-</a><input type="text" value="{WPSHOP_CART_LINE_ITEM_QTY}" name="productQty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" id="wpshop_product_order_{WPSHOP_CART_LINE_ITEM_ID}"  /><a href="#" class="productQtyChange wpshop_more_product_qty_in_cart">+</a><?php
$tpl_element['cart_qty_content'] = ob_get_contents();
ob_end_clean();


/*	Product cart remover	|							Panier tableau supprimer élément */
ob_start();
?><a href="#" class="remove" title="<?php _e('Remove', 'wpshop'); ?>"><?php _e('Remove', 'wpshop'); ?></a><?php
$tpl_element['cart_line_remove'] = ob_get_contents();
ob_end_clean();


/*	Product variation detail in cart					Panier detail des variations */
ob_start();
?><li class="wpshop_cart_variation_details_item wps-label wpshop_cart_variation_details_item_{WPSHOP_VARIATION_ID} wpshop_cart_variation_details_item_{WPSHOP_VARIATION_ATT_CODE}" >{WPSHOP_VARIATION_NAME} : {WPSHOP_VARIATION_VALUE}</li><?php
$tpl_element['cart_variation_detail'] = ob_get_contents();
ob_end_clean();


/*	Vouncher field into cart							Coupons de reduction */
ob_start();
?><div class="wpshop_cart_vouncher_field_container" ><?php _e('Discount coupon','wpshop'); ?> : <input type="text" name="coupon_code" value="" /> <a href="#" class="submit_coupon" data-nonce="<?php echo wp_create_nonce( 'applyCoupon' ); ?>"><?php _e('Submit the coupon','wpshop'); ?></a></div><?php
$tpl_element['cart_vouncher_part'] = ob_get_contents();
ob_end_clean();


/*	Empty Quotation button									Vidage du panier */
ob_start();
?><div class="wpshop_cart_buttons_container" ><div class="alignright" ><input type="submit" value="<?php _e('Validate my quotation','wpshop'); ?>" name="cartCheckout" class="alignright" /><br/><a href="#" data-nonce="<?php echo wp_create_nonce( 'wps_empty_cart' ); ?>" class="emptyCart alignright" ><?php _e('Empty the quotation','wpshop'); ?></a></div></div><?php
$tpl_element['cart_quotation_buttons'] = ob_get_contents();
ob_end_clean();

/*	Empty cart button									Vidage du panier */
ob_start();
?><div class="wpshop_cart_buttons_container" ><div class="alignright" ><input type="submit" value="<?php _e('Validate my cart','wpshop'); ?>" name="cartCheckout" class="alignright" /><br/><a href="#" data-nonce="<?php echo wp_create_nonce( 'wps_empty_cart' ); ?>" class="emptyCart alignright" ><?php _e('Empty the cart','wpshop'); ?></a></div></div><?php
$tpl_element['cart_buttons'] = ob_get_contents();
ob_end_clean();


/*	Cart Total summaries line content 					Contenu des lignes des totaux du panier */
ob_start();
?><div class="wpshop_cart_summary_line{WPSHOP_CART_SUMMARY_LINE_SPECIFIC}" >{WPSHOP_CART_SUMMARY_TITLE} : <span class="right{WPSHOP_CART_SUMMARY_AMOUNT_CLASS}" >{WPSHOP_CART_SUMMARY_AMOUNT} {WPSHOP_CURRENCY}</span></div><?php
$tpl_element['cart_summary_line_content'] = ob_get_contents();
ob_end_clean();


/*	Cart main page						Template general page panier */
ob_start();
?>
<span id="wpshop_loading">&nbsp;</span>
<div class="cart" >
{WPSHOP_CART_OUTPUT}
<div class="wpshop_cart_summary_informations">
	<div><?php _e('Total ET','wpshop'); ?> : <span class="total_ht right">{WPSHOP_CART_PRICE_ET} {WPSHOP_CURRENCY}</span></div>
	{WPSHOP_CART_TAXES}
	<div id="order_shipping_cost" ><?php _e('Shipping','wpshop'); ?> <?php echo WPSHOP_PRODUCT_PRICE_PILOT; ?> : <span class="right">{WPSHOP_CART_SHIPPING_COST} {WPSHOP_CURRENCY}</span></div>
	{WPSHOP_CART_DISCOUNT_SUMMARY}
	<div class="bold wpshop_clear" ><?php _e('Total ATI','wpshop'); ?> : <span class="total_ttc right bold">{WPSHOP_CART_TOTAL_ATI} {WPSHOP_CURRENCY}</span></div>
	{WPSHOP_CART_PARTIAL_PAYMENT}
	{WPSHOP_CART_VOUNCHER}
</div>
{WPSHOP_CART_BUTTONS}
</div>
<?php
$tpl_element['cart_main_page'] = ob_get_contents();
ob_end_clean();


/** Cart Container **/
ob_start(); ?>
<span id="wpshop_loading">&nbsp;</span>
<div class="cart" >
{WPSHOP_CART_CONTENT}
</div>
<?php
$tpl_element['cart_container'] = ob_get_contents();
ob_end_clean();



/*	product added to cart popup							Panier Popup après ajout au panier */
ob_start();
?>
<div class="wpshop_superBackground"></div>
<div class="wpshop_popupAlert">
		<div id="product_img_dialog_box"></div>
		<div id="product_infos_dialog_box">
			<p><h1><?php _e('Your product has been sucessfuly added to your cart', 'wpshop'); ?></h1></p>
			<br/>
			<p><span class="product_title_dialog_box"></span></p>
			<p><span class="product_price_dialog_box"></span></p>

		</div>
		<div id="buttons_line_dialog_box">
				<div class="alignleft"><a href="{WPSHOP_CART_LINK}" class="bouton_wpshop"><?php _e('View my cart','wpshop'); ?></a></div>
				<div class="alignright"><a href="" class="bouton_wpshop_commander closeAlert" ><?php _e('Continue shopping','wpshop'); ?></a></div>
		</div>
		<div id="wpshop_add_to_cart_box_related_products"></div>

</div>
<?php
$tpl_element['product_added_to_cart_message'] = ob_get_contents();
ob_end_clean();


/**
 * ADD PRODUCT TO CART DIALOG BOX LINKED PRODUCTS
 */
ob_start();
?>
<h3><?php _e('Linked products', 'wpshop'); ?></h3>
{WPSHOP_RELATED_PRODUCTS}
<?php
$tpl_element['wpshop_add_to_cart_dialog_box_related_products'] = ob_get_contents();
ob_end_clean();

/**
 * ADD PRODUCT TO CART DIALOG BOX LINKED PRODUCT ELEMENT
 */

ob_start();
?>
<li><a href="{WPSHOP_RELATED_PRODUCT_LINK}" title="{WPSHOP_RELATED_PRODUCT_NAME}">{WPSHOP_RELATED_PRODUCT_THUMBNAIL}</a><br/>{WPSHOP_RELATED_PRODUCT_NAME}</li>
<?php
$tpl_element['wpshop_add_to_cart_dialog_box_related_product_element'] = ob_get_contents();
ob_end_clean();


/*	Current product variation	*/


/*	Product is new	|									Nouveauté produit */
ob_start();
?>
<span class="vignette_nouveaute wps-label wps-rouge"><i class="wps-icon-love"></i><span><?php _e('New', 'wpshop'); ?></span></span><?php
$tpl_element['product_is_new_sticker'] = ob_get_contents();
ob_end_clean();


/*	Product is featured	|								En vedette produit */
ob_start();
?>
<span class="vignette_en_vedette wps-label wps-vert"><i class="wps-icon-star"></i><span><?php _e('Featured', 'wpshop'); ?></span></span><?php
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

ob_start();
?><span class="{WPSHOP_PDT_ENTITY_CODE}_frontend_attribute_label {WPSHOP_ATTRIBUTE_CODE}_label" >{WPSHOP_ATTRIBUTE_LABEL}</span> : <?php
$tpl_element['product_attribute_display_label'] = ob_get_contents();
ob_end_clean();

ob_start();
?><span class="{WPSHOP_PDT_ENTITY_CODE}_frontend_attribute_value {WPSHOP_ATTRIBUTE_CODE}_value" >{WPSHOP_ATTRIBUTE_VALUE}{WPSHOP_ATTRIBUTE_VALUE_UNIT}</span><?php
$tpl_element['product_attribute_display_value'] = ob_get_contents();
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
?>
<div class="wps-form-group wpshop_variation{WPSHOP_VARIATION_CONTAINER_CLASS}">
	<label for="{WPSHOP_VARIATION_IDENTIFIER}"{WPSHOP_VARIATION_LABEL_HELPER} class="wpshop_variation_label{WPSHOP_VARIATION_LABEL_CLASS}">{WPSHOP_VARIATION_LABEL} :</label><span class="wps-help-inline wps-help-inline-title">{WPSHOP_VARIATION_REQUIRED_INDICATION}</span>
	<div class="wps-form wps-form-helped">
		{WPSHOP_VARIATION_INPUT}
	</div>
</div>
<?php
$tpl_element['product_variation_item'] = ob_get_contents();
ob_end_clean();

/**	Display variation required text */
ob_start();
_e('Required variation', 'wpshop'); ?><?php
$tpl_element['product_variation_item_is_required_explanation'] = ob_get_contents();
ob_end_clean();

/*	Define variation display	*/
ob_start();
?>{WPSHOP_VARIATION_VALUE}<?php
$tpl_element['product_variation_item_possible_values'] = ob_get_contents();
ob_end_clean();

/*	Define variation display	*/
ob_start();
?><form action="<?php echo admin_url('admin-ajax.php')?>" method="POST" id="wpshop_add_to_cart_form" >{WPSHOP_FROM_ADMIN_INDICATOR} {WPSHOP_ORDER_ID_INDICATOR} <input type="hidden" name="wpshop_pdt" id="wpshop_pdt" value="{WPSHOP_VARIATION_FORM_ELEMENT_ID}" /><?php wp_nonce_field( 'ajax_pos_product_variation_selection' ); ?><input type="hidden" name="wpshop_pdt_qty" id="wpshop_pdt_qty" value="{WPSHOP_PRODUCT_ADDED_TO_CART_QTY}" /><input type="hidden" name="action" value="wpshop_add_product_to_cart" /><input type="hidden" name="wpshop_cart_type" value="cart" />{WPSHOP_VARIATION_FORM_VARIATION_LIST}</form><?php
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
<section class="wps-single">
	<div class="wps-gridwrapper2">
		{WPSHOP_PRODUCT_COMPLETE_SHEET_GALLERY}
	<div>
		<div itemscope="" itemtype="http://schema.org/Product">
				<div class="wps-product-section">
					<h1 itemprop="name" class="entry-title">{WPSHOP_PRODUCT_TITLE}</h1>
					{WPSHOP_PRODUCT_RATE}
					<div class="wps-prices" itemscope itemtype="http://schema.org/Offer">{WPSHOP_PRODUCT_PRICE}</div>
				</div>
				<div class="wps-product-section">[wps_low_stock_alert id="{WPSHOP_PRODUCT_ID}"]</div>
				<div class="wps-product-section">
					<p itemprop="description">{WPSHOP_PRODUCT_INITIAL_CONTENT}</p>
				</div>
				<div class="wps-product-section">
					{WPSHOP_PRODUCT_VARIATIONS}
				</div>
			{WPSHOP_PRODUCT_QUANTITY_CHOOSER}
			{WPSHOP_PRODUCT_BUTTONS}

			<p>
				<?php echo apply_filters('wps-below-add-to-cart', "");?>
			</p>

			{WPSHOP_PRODUCT_GALERY_DOCS}
		</div>
	</div>
</div>
[wps_product_caracteristics pid="{WPSHOP_PRODUCT_ID}"]
</section>
<?php
$tpl_element['product_complete_tpl'] = ob_get_contents();
ob_end_clean();


ob_start();
?><div class="wps-productRating">[wps_star_rate_product pid="{WPSHOP_PRODUCT_ID}"]</div><?php
$tpl_element['product_rating'] = ob_get_contents();
ob_end_clean();


/** Product complete sheet galery slider element **/
ob_start();
?><div class="wps-product-section">
	<label><?php _e('Quantity', 'wpshop'); ?></label>
	<div class="wps-productQtyForm">
		<a class="wps-bton-icon-minus-small wps-cart-reduce-product-qty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" href=""><i class="wps-icon-minus"></i></a>
		<input id="wps-cart-product-qty-{WPSHOP_PRODUCT_ID}" class="wpshop_product_qty_input" type="text" value="1" />
		<a class="wps-bton-icon-plus-small wps-cart-add-product-qty" data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_set_qty_for_product_into_cart' ); ?>" href=""><i class="wps-icon-plus"></i></a>
	</div>
</div><?php
$tpl_element['product_complete_sheet_quantity_chooser'] = ob_get_contents();
ob_end_clean();


/** Product complete sheet new gallery **/
ob_start();
?><div class="wps-product-galery wpsjq-showroom"><div id="wps-product-thumbnail" class="wps-showroom-slider"><div class="wps-showroom-slider-content">{WPSHOP_SLIDER_CONTENT}</div></div><div class="wps-showroom-slider-thumbnails">{WPSHOP_THUMBNAILS}</div></div><?php
$tpl_element['wps_product_complete_sheet_gallery'] = ob_get_contents();
ob_end_clean();



/** Product complete sheet galery slider element **/
ob_start();
?><a href="#">{WPSHOP_IMAGE_SLIDER_FULL}<span class="wps-zoom-loupe">{WPSHOP_IMAGE_SLIDER_FULL}</span></a><?php
$tpl_element['wps_product_complete_sheet_gallery_slider_element'] = ob_get_contents();
ob_end_clean();


/** Product complete sheet gallery thumbnail element **/
ob_start();
?><a href="#" id="wps_product_gallery_{WPSHOP_THUMBNAIL_GALLERY_THUMBNAIL_ID}">{WPSHOP_THUMBNAIL_GALLERY_THUMBNAIL}</a><?php
$tpl_element['wps_product_complete_sheet_gallery_thumbnail_element'] = ob_get_contents();
ob_end_clean();






/*	Product mini display (List)										Produits mini liste */
ob_start();
?>
<li class="{WPSHOP_PRODUCT_CLASS}" itemscope itemtype="http://data-vocabulary.org/Product" >

	<a href="{WPSHOP_PRODUCT_PERMALINK}" class="" title="{WPSHOP_PRODUCT_TITLE}">
		{WPSHOP_PRODUCT_THUMBNAIL}
		<div class="wps-extras">
			{WPSHOP_PRODUCT_EXTRA_STATE}
		</div>
	</a>
	<span class="product_information-mini-list" itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers">
		<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" class="wpshop_clearfix">
			<span class="wps-title" itemprop="name" >{WPSHOP_PRODUCT_TITLE}</span>
			<span class="crossed_out_price">{WPSHOP_CROSSED_OUT_PRICE}</span> {WPSHOP_PRODUCT_PRICE}
			{WPSHOP_LOW_STOCK_ALERT_MESSAGE}
			<p itemprop="description" class="wpshop_liste_description">{WPSHOP_PRODUCT_EXCERPT}</p>
		</a>
		{WPSHOP_PRODUCT_BUTTONS}

	</span>
</li><?php
$tpl_element['product_mini_list'] = ob_get_contents();
ob_end_clean();

/*	Product mini display (grid)									Produits mini grid */
ob_start();
?>
<li itemscope="" itemtype="http://data-vocabulary.org/Product">
	<div>
		<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers">
			<span class="wps-thumbnail">
				{WPSHOP_PRODUCT_THUMBNAIL_WPSHOP-PRODUCT-GALERY}
				<span class="wps-extras">
					{WPSHOP_PRODUCT_EXTRA_STATE}
				</span>
				<span class="wps-hover">voir</span>
			</span>
			<span class="wps-caption">
				<span class="wps-title" itemprop="name" >{WPSHOP_PRODUCT_TITLE}</span>
				<span itemprop="price" class="wps-price-container">
			    	<span class="wps-price">{WPSHOP_PRODUCT_PRICE}</span>
			    </span>
			</span>
		</a>
		<div class="wps-action-container">
			{WPSHOP_PRODUCT_BUTTONS}
		</div>
	</div>
</li><?php
$tpl_element['product_mini_grid'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><div class="container_product_listing wps-bloc-loader" ><ul class="wps-catalog wps-{WPSHOP_PRODUCT_LIST_DISPLAY_TYPE}wrapper{WPSHOP_PRODUCT_LIST_PER_LINE}" >{WPSHOP_PRODUCT_LIST}</ul></div><?php
$tpl_element['product_list_container'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><span itemprop="price" class="wpshop_products_listing_price">{WPSHOP_CROSSED_OUT_PRICE} {WPSHOP_PRODUCT_PRICE} {WPSHOP_TAX_PILOTING}</span>
{WPSHOP_MESSAGE_SAVE_MONEY}
<?php
$tpl_element['product_price_template_mini_output'] = ob_get_contents();
ob_end_clean();

/*	Product price display template	*/
ob_start();
?><span class="crossed_out_price">{WPSHOP_CROSSED_OUT_PRICE_VALUE}</span><?php
$tpl_element['product_price_template_crossed_out_price'] = ob_get_contents();
ob_end_clean();

/*	Product price display template	*/
ob_start();
?>
<div class="wpshop_product_price wps-bloc-loader">
	{WPSHOP_CROSSED_OUT_PRICE}
	<span class="wps-price" itemprop="price">{WPSHOP_PRODUCT_PRICE}<span class="wps-tax-piloting">{WPSHOP_TAX_PILOTING}</span></span>
	{WPSHOP_MESSAGE_SAVE_MONEY}
</div>

<?php
$tpl_element['product_price_template_complete_sheet'] = ob_get_contents();
ob_end_clean();

/*	Sorting bloc criteria list	*/
/*
 * {WPSHOP_SORTING_CRITERIA_LIST}
 */
ob_start();
?>
	<span class="wps-bloc">
		<span><?php _e('Sorting','wpshop'); ?></span>
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
<div class="wps-catalog-sorting">
	{WPSHOP_SORTING_HIDDEN_FIELDS}

	<div class="wps-table-layout">
		{WPSHOP_SORTING_CRITERIA}
		<ul class="wps-sorting-tools">
			<li>
				<a href="#" class="ui-icon product_asc_listing reverse_sorting" title="<?php _e('Reverse','wpshop'); ?>">
					<i class="wps-icon-arrowdown"></i>
				</a>
			</li>
			<li>
				<a href="#" class="change_display_mode list_display{WPSHOP_DISPLAY_TYPE_STATE_LIST}" title="<?php _e('Change to list display','wpshop'); ?>">
					<i class="wps-icon-list"></i>
				</a>
			</li>
			<li>
				<a href="#" class="change_display_mode grid_display{WPSHOP_DISPLAY_TYPE_STATE_GRID}" title="<?php _e('Change to grid display','wpshop'); ?>">
					<i class="wps-icon-grid"></i>
				</a>
			</li>
		</ul>
	</div>

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
<ul class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_galery " >{WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}</ul><?php
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
<ul class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_galery wps-product-docs wpshop_clearfix" >{WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}</ul><?php
$tpl_element['product_attachment_galery'] = ob_get_contents();
ob_end_clean();

/*	Product attachment item document	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}
 * {WPSHOP_ATTACHMENT_ITEM_GUID}
 * {WPSHOP_ATTACHMENT_ITEM_TITLE}
 * {WPSHOP_ATTACHMENT_ITEM_EXTENSION}
 */
ob_start();
?>
<li class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_item wps-product-doc {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}" >
	<a href="{WPSHOP_ATTACHMENT_ITEM_GUID}" download="{WPSHOP_ATTACHMENT_ITEM_TITLE}">
		<i class="wps-icodoc wps-icodoc-{WPSHOP_ATTACHMENT_ITEM_EXTENSION}">
			<span>{WPSHOP_ATTACHMENT_ITEM_EXTENSION}</span>
		</i>
		<span>{WPSHOP_ATTACHMENT_ITEM_TITLE}</span>
	</a>
</li><?php
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
?><div class="wps-categorie-item">
	<a href="{WPSHOP_CATEGORY_LINK}" title="{WPSHOP_CATEGORY_TITLE}">
		<span class="wps-categorie-item-thumbnail" >
			{WPSHOP_CATEGORY_THUMBNAIL}
		</span>
		<span class="wps-categorie-item-caption" >
			<span class="wps-categorie-item-title" >{WPSHOP_CATEGORY_TITLE}</span>
			<span class="wps-categorie-item-description" >{WPSHOP_CATEGORY_DESCRIPTION}</span>
		</span>
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
?><div class="wps-categorie-item">
	<a href="{WPSHOP_CATEGORY_LINK}" title="{WPSHOP_CATEGORY_TITLE}">
		<span class="wps-categorie-item-thumbnail" >
			{WPSHOP_CATEGORY_THUMBNAIL}
		</span>
		<span class="wps-categorie-item-caption" >
			<span class="wps-categorie-item-title" >{WPSHOP_CATEGORY_TITLE}</span>
			<span class="wps-categorie-item-description" >{WPSHOP_CATEGORY_DESCRIPTION}</span>
		</span>
	</a>
</div><?php
$tpl_element['category_mini_grid'] = ob_get_contents();
ob_end_clean();

/*	Mini category */
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
?><div class="wps-categorie-item">
	<a href="{WPSHOP_CATEGORY_LINK}" title="{WPSHOP_CATEGORY_TITLE}">
		<span class="wps-categorie-item-thumbnail" >
			{WPSHOP_CATEGORY_THUMBNAIL}
		</span>
		<span class="wps-categorie-item-caption" >
			<span class="wps-categorie-item-title" >{WPSHOP_CATEGORY_TITLE}</span>
			<span class="wps-categorie-item-description" >{WPSHOP_CATEGORY_DESCRIPTION}</span>
		</span>
	</a>
</div><?php
$tpl_element['category_mini'] = ob_get_contents();
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
?>
<div class="wpshop_form_title"><h2>{WPSHOP_PERSONAL_INFORMATIONS_FORM_TITLE}</h2></div>
<div class="wpshop_customer_personnal_informations_form_container" >{WPSHOP_ACCOUNT_FORM_FIELD}</div>

<?php
$tpl_element['wpshop_account_form'] = ob_get_contents();
ob_end_clean();


/*	Login form	*/
ob_start();
?>
<div class="wpshop_form_title"><h2><?php _e('Log in')?></h2></div>
<!--   {WPSHOP_LOGIN_FORM} -->
<form method="post" action="<?php echo site_url(); ?>/wp-login.php" id="loginform" name="loginform">
	<p class="formField">
	<label for=""><?php _e('E-mail', 'wpshop'); ?> <span class="required">*</span></label>
	<input type="text" value="" id="user_login" name="log" />
	</p>
	<p class="formField">
	<label for=""><?php _e('Password', 'wpshop'); ?> <span class="required">*</span></label>
	<input type="password" value="" id="user_pass" name="pwd" />
	</p>
	<input type="hidden" value="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" name="redirect_to" />
	<input type="submit" value="<?php _e('Connexion', 'wpshop'); ?>" id="wp-submit" name="wp-submit">
</form>
<?php
$tpl_element['wpshop_login_form'] = ob_get_contents();
ob_end_clean();




/*	Account / Address form input	*/
ob_start();
?>
<p class="formField{WPSHOP_CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS}" ><label{WPSHOP_CUSTOMER_FORM_INPUT_LABEL_OPTIONS}>{WPSHOP_CUSTOMER_FORM_INPUT_LABEL}</label>{WPSHOP_CUSTOMER_FORM_INPUT_FIELD}</p><?php
$tpl_element['wpshop_account_form_input'] = ob_get_contents();
ob_end_clean();

/*	Account / Address form HIDDEN input	*/
ob_start();
?>{WPSHOP_CUSTOMER_FORM_INPUT_FIELD}<?php
$tpl_element['wpshop_account_form_hidden_input'] = ob_get_contents();
ob_end_clean();



/**	New entity quick add form	*/
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

/**	Display a input type text for wordpress internal fields (as post title)	*/
ob_start();
?>
<input type="text" value="{WPSHOP_WP_FIELD_VALUE}" name="wp_fields[{WPSHOP_WP_FIELD_NAME}]" id="wp_fields_{WPSHOP_WP_FIELD_NAME}" /><?php
$tpl_element['quick_entity_wp_internal_field_text'] = ob_get_contents();
ob_end_clean();

/**	Display a input type file for wordpress internal fields (as post thumbnail sender)	*/
ob_start();
?>
<input type="file" value="{WPSHOP_WP_FIELD_VALUE}" name="wp_fields[{WPSHOP_WP_FIELD_NAME}]" id="wp_fields_{WPSHOP_WP_FIELD_NAME}" /><?php
$tpl_element['quick_entity_wp_internal_field_file'] = ob_get_contents();
ob_end_clean();

/**	Define the container for internal input	*/
ob_start();
?>

<div class="wpshop_clear">
	<div class="wpshop_form_label {WPSHOP_ENTITY_TYPE_TO_CREATE}_{WPSHOP_WP_FIELD_NAME}_label _{WPSHOP_WP_FIELD_NAME}_label alignleft">{WPSHOP_WP_FIELD_LABEL}</div>
	<div class="wpshop_form_input_element {WPSHOP_ENTITY_TYPE_TO_CREATE}_{WPSHOP_WP_FIELD_NAME}_input _{WPSHOP_WP_FIELD_NAME}_input alignleft">{WPSHOP_WP_FIELD_INPUT}</div>
</div>
<!--
<div class="wps-form-group {WPSHOP_ENTITY_TYPE_TO_CREATE}_{WPSHOP_WP_FIELD_NAME}_label _{WPSHOP_WP_FIELD_NAME}_label">
	<label>{WPSHOP_WP_FIELD_LABEL}</label>
	<div class="wps-form {WPSHOP_ENTITY_TYPE_TO_CREATE}_{WPSHOP_WP_FIELD_NAME}_input _{WPSHOP_WP_FIELD_NAME}_input">{WPSHOP_WP_FIELD_INPUT}</div>
</div>
-->
<?php
$tpl_element['quick_entity_wp_internal_field_output'] = ob_get_contents();
ob_end_clean();

/**	Define template of element allowing to add a new to value to an attribute of list type	*/
ob_start();
?>
<div class="wpshop_attribute_new_creator_condition" ><?php _e('Or', 'wpshop'); ?></div><div class="wpshop_attribute_new_creator_field" ><input type="text" placeholder="<?php _e('Create a new element', 'wsphop'); ?>" name="{WPSHOP_NEW_ELEMENT_CREATION_FIELD}" /></div><?php
$tpl_element['quick_entity_specific_field_new_element'] = ob_get_contents();
ob_end_clean();



/**	Product configuration viewer main container	*/
/**		<div class="wpshop_product_variation_summary_currency_selector" >{WPSHOP_CURRENCY_SELECTOR}</div>		*/
ob_start();
?><div class="wpshop_product_variation_summary_main_container" ><h3 class="widget-title"><?php _e('Product configuration summary', 'wpshop'); ?></h3><div class="wpshop_product_variation_summary_container" id="wpshop_product_variation_summary_container" >{WPSHOP_PRODUCT_VARIATION_SELECTION_DISPLAY}</div><div class="wpshop_clear" ></div></div><?php
$tpl_element['wpshop_product_configuration_summary'] = ob_get_contents();
ob_end_clean();

/**	Product configuration viewer		Display all option for product with options	*/
ob_start();
?><div class="wpshop_product_variation_summary_product_name" >{WPSHOP_PRODUCT_MAIN_INFO_PRODUCT_NAME}</div>
<ul class="wpshop_product_variation_summary_product_details" >{WPSHOP_PRODUCT_VARIATION_SUMMARY_DETAILS}</ul>
{WPSHOP_PRODUCT_VARIATION_SUMMARY_MORE_CONTENT}
<div class="wpshop_product_variation_summary_product_final_price alignright" ><?php _e('Product final price', 'wpshop'); ?> {WPSHOP_PRODUCT_MAIN_INFO_PRODUCT_PRICE} {WPSHOP_CURRENCY_CHOOSEN} </div>
{WPSHOP_PRODUCT_VARIATION_SUMMARY_GRAND_TOTAL}
{WPSHOP_PARTIAL_PAYMENT_INFO}<?php
$tpl_element['wpshop_product_configuration_summary_detail'] = ob_get_contents();
ob_end_clean();

/*	Auto add to cart product line	| 						 */
ob_start();
?><div class="wpshop_product_variation_summary_auto_product alignright" >{WPSHOP_AUTO_PRODUCT_NAME} {WPSHOP_AUTO_PRODUCT_PRODUCT_PRICE} {WPSHOP_CURRENCY_CHOOSEN} </div><?php
$tpl_element['wpshop_product_configuration_summary_detail_auto_product'] = ob_get_contents();
ob_end_clean();

/*	Current product configuration grand total line	| 						 */
ob_start();
?><div class="wpshop_clear wpshop_product_variation_summary_grand_total alignright" ><?php _e('Grand total', 'wpshop'); ?> {WPSHOP_SUMMARY_FINAL_RESULT_PRICE} {WPSHOP_CURRENCY_CHOOSEN} </div><?php
$tpl_element['wpshop_product_configuration_summary_detail_final_result'] = ob_get_contents();
ob_end_clean();




/**	Main container for display information about attribute that are configured to display description in frontend	*/
ob_start();
?><div class="wpshop_clear wpshop_product_variation_value_detail_main_container" id="wpshop_product_variation_value_detail_main_container" ></div><?php
$tpl_element['wpshop_product_variation_value_detail_container'] = ob_get_contents();
ob_end_clean();

/**	Main container for display information about attribute that are configured to display description in frontend	*/
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
/*ob_start();
?><form method="post" name="checkoutForm" action="<?php echo get_permalink(wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id'))); ?>" >
	{WPSHOP_CHECKOUT_CUSTOMER_BILLING_ADDRESS}
	<h2><?php _e('Shipping method choice', 'wpshop'); ?></h2>
	<div id="wps_shipping_modes_choice" data-nonce="<?php echo wp_create_nonce( 'wps_shipping_modes_choice' ); ?>">{WPSHOP_CHECKOUT_CUSTOMER_SHIPPING_CHOICE}</div>
	<?php
	//echo do_shortcode('[wps_book_shipping]'); ?>

	<h2>{WPSHOP_CHECKOUT_SUMMARY_TITLE}</h2>
	{WPSHOP_CHECKOUT_CART_CONTENT}

	<div>
		<?php _e('Comments about the order','wpshop'); ?>
		<textarea name="order_comments"></textarea>
	</div>
	{WPSHOP_CHECKOUT_PAYMENT_METHODS}
	<div{WPSHOP_CHECKOUT_PAYMENT_BUTTONS_CONTAINER}>{WPSHOP_CHECKOUT_TERM_OF_SALES}
	 <div id="wpshop_checkout_payment_buttons">{WPSHOP_CHECKOUT_PAYMENT_BUTTONS}</div>
	</div>
</form><?php
$tpl_element['wpshop_checkout_page'] = ob_get_contents();
ob_end_clean();*/

/**
 * Checkout page validation button
 */
ob_start();
?><input type="submit" name="takeOrder" value="<?php _e('Order', 'wpshop')?>" /><?php
$tpl_element['wpshop_checkout_page_validation_button'] = ob_get_contents();
ob_end_clean();


/**
 * FInish the Order, Ordeer with an amount of Zero
 */
ob_start();
?><input type="submit" name="takeOrder" value="<?php _e('Finish the order', 'wpshop')?>" /><?php
$tpl_element['wpshop_checkout_page_finish_order_button'] = ob_get_contents();
ob_end_clean();


/**
 * Checkout page validation button
 */
ob_start();
?><input type="submit" name="takeOrder" value="<?php _e('Ask the quotation', 'wpshop'); ?>" /><?php
$tpl_element['wpshop_checkout_page_quotation_validation_button'] = ob_get_contents();
ob_end_clean();

/**
 * Impossible to order message
 */
ob_start();
?>
<div class="error_bloc"><?php _e('Sorry ! You can\'t order on this shop because we don\'t deliver in your country', 'wpshop'); ?></div>
<?php
$tpl_element['wpshop_checkout_page_impossible_to_order'] = ob_get_contents();
ob_end_clean();

ob_start();
?>
<div class="wpshop_checkout_page_form_sign_up">{WPSHOP_CHECKOUT_SIGNUP_FORM}</div>
<div class="wpshop_checkout_page_form_sign_up">{WPSHOP_CHECKOUT_LOGIN_FORM}</div>
<?php
$tpl_element['wpshop_checkout_sign_up_page'] = ob_get_contents();
ob_end_clean();

/**
 * Payment method bloc
 */
ob_start();
?><table class="blockPayment{WPSHOP_CHECKOUT_PAYMENT_METHOD_STATE_CLASS}">
	<tr>
		<td class="paymentInput rounded-left"><input type="radio" name="modeDePaiement"{WPSHOP_CHECKOUT_PAYMENT_METHOD_INPUT_STATE} value="{WPSHOP_CHECKOUT_PAYMENT_METHOD_IDENTIFIER}" /></td>
		<td class="paymentImg">{WPSHOP_CHECKOUT_PAYMENT_METHOD_ICON}</td>
		<td class="paymentName">{WPSHOP_CHECKOUT_PAYMENT_METHOD_NAME}</td>
		<td class="last rounded-right">{WPSHOP_CHECKOUT_PAYMENT_METHOD_EXPLANATION}</td>
	</tr>
</table><?php
$tpl_element['wpshop_checkout_page_payment_method_bloc'] = ob_get_contents();
ob_end_clean();


/**
 * Check method confirmation message
 */
ob_start();
?><p><?php _e('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop'); ?></p>
<p><?php echo sprintf(__('You have to send the check with an amount of %s to about "%s" to the adress :', 'wpshop'), '{WPSHOP_ORDER_AMOUNT} {WPSHOP_CURRENCY}', '{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_NAME}'); ?></p>
<p>{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_NAME}<br/>
{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_STREET}<br/>
{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_POSTCODE}, {WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_CITY}<br/>
{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_COUNTRY}</p>
<p><?php _e('Your order will be shipped upon receipt of the check.', 'wpshop'); ?></p><?php
$tpl_element['wpshop_checkout_page_check_confirmation_message'] = ob_get_contents();
ob_end_clean();


/**
 * Check method confirmation message
 */
ob_start();
?><p><?php _e('Thank you ! Your quotation has been sent. We will respond to you as soon as possible.', 'wpshop'); ?></p><?php
$tpl_element['wpshop_checkout_page_quotation_confirmation_message'] = ob_get_contents();
ob_end_clean();

/**
 * Quotation method confirmation message
 */
ob_start();
?><p><?php _e('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop'); ?></p>
<?php
$tpl_element['wpshop_checkout_page_free_confirmation_message'] = ob_get_contents();
ob_end_clean();


/**
 * Check method confirmation message
 */
ob_start();
?><a href="{WPSHOP_DOWNLOAD_LINK}" target="_blank"><?php _e( 'Download your product', 'wpshop'); ?></a>
<?php
$tpl_element['wpshop_checkout_page_free_download_link'] = ob_get_contents();
ob_end_clean();


/**
 * Cash on delivery method confirmation message
 */
ob_start();
?><p><?php _e('Thank you ! Your order as been successfully saved. You will pay your order on delivery. Thank you for your loyalty.', 'wpshop'); ?></p>
<?php
$tpl_element['wpshop_checkout_page_cash_on_delivery_confirmation_message'] = ob_get_contents();
ob_end_clean();

/**
 * Check method confirmation message
 */
ob_start();
?><p><?php _e('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop'); ?></p>
<p><?php _e('You have to do a bank transfer on account detailled below:', 'wpshop'); ?></p>
<p><?php _e('Bank name', 'wpshop'); ?> : {WPSHOP_BANKTRANSFER_CONFIRMATION_MESSAGE_BANK_NAME}<br/>
<?php _e('IBAN', 'wpshop'); ?> : {WPSHOP_BANKTRANSFER_CONFIRMATION_MESSAGE_IBAN}<br/>
<?php _e('BIC/SWIFT', 'wpshop'); ?> : {WPSHOP_BANKTRANSFER_CONFIRMATION_MESSAGE_BIC}<br/>
<?php _e('Account owner name', 'wpshop'); ?> : {WPSHOP_BANKTRANSFER_CONFIRMATION_MESSAGE_ACCOUNTOWNER}</p>
<p><?php _e('Your order will be shipped upon receipt of funds.', 'wpshop'); ?></p><?php
$tpl_element['wpshop_checkout_page_banktransfer_confirmation_message'] = ob_get_contents();
ob_end_clean();


/**	Display informations about partial payment	*/
ob_start();
?><div class="wpshop_clear alignright wpshop_partial_payment" ><?php _e('Payable now', 'wpshop'); ?> ({WPSHOP_PARTIAL_PAYMENT_CONFIG_AMOUNT}{WPSHOP_PARTIAL_PAYMENT_CONFIG_TYPE}) {WPSHOP_PARTIAL_PAYMENT_AMOUNT} {WPSHOP_CURRENCY_CHOOSEN}</div><?php
$tpl_element['wpshop_partial_payment_display'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Customer newsletter preference
 *
 */
ob_start();
?><div class="wpshop_customer_newsletter_pref_container" >
	<div class="wpshop_customer_newsletter_pref_site_container" ><input id="newsletters_site" type="checkbox" name="newsletters_site"{WPSHOP_CUSTOMER_PREF_NEWSLETTER_SITE}><label for="newsletters_site"><?php _e('I want to receive promotional information from the site','wpshop'); ?></label></div>
	<div class="wpshop_customer_newsletter_pref_site_partners_container" ><input id="newsletters_site_partners" type="checkbox" name="newsletters_site_partners"{WPSHOP_CUSTOMER_PREF_NEWSLETTER_SITE_PARTNERS}><label for="newsletters_site_partners"><?php _e('I want to receive promotional information from partner companies','wpshop'); ?></label></div>
</div><?php
$tpl_element['wpshop_customer_preference_for_newsletter'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Customer account information form
 *
 */
ob_start();
?><div id="reponseBox"></div>
<form  method="post" id="register_form" action="<?php echo admin_url('admin-ajax.php'); ?>">
	<input type="hidden" name="wpshop_ajax_nonce" value="{WPSHOP_CUSTOMER_ACCOUNT_INFOS_FORM_NONCE}" />
	<input type="hidden" name="action" value="wpshop_save_customer_account" />
	<div class="col1 wpshopShow" id="register_form_classic">
		{WPSHOP_CUSTOMER_ACCOUNT_INFOS_FORM}
		{WPSHOP_CUSTOMER_ACCOUNT_INFOS_FORM_BUTTONS}
	</div>
</form><?php
$tpl_element['wpshop_customer_account_infos_form'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Customer addresses form
 *
 */
ob_start();
?><div id="reponseBox"></div>
<form method="post" name="billingAndShippingForm" >
	<input type="hidden" name="action" value="wps_save_address" />
	<div class="col1 wpshopShow" id="register_form_classic">
		{WPSHOP_CUSTOMER_ADDRESSES_FORM_CONTENT}
		{WPSHOP_CUSTOMER_ADDRESSES_FORM_BUTTONS}
	</div>
<?php
$tpl_element['wpshop_customer_addresses_form'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Customer addresses type choice form
 *
 */
ob_start();
?><h1><?php _e('Address Type','wpshop'); ?></h1>
	<form id="selectNewAddress" method="post" action="{WPSHOP_ADDRESS_TYPE_CHOICE_FORM_ACTION}">
		<div class="create-account">
			<p><?php _e('Select the address type you want to create','wpshop'); ?></p>
			{WPSHOP_ADDRESS_TYPE_LISTING_INPUT}
		</div>
		<?php
		if( empty( $_SERVER['HTTP_REFERER'] ) ) {
			if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) {
				$referer = 'https://';
			} else {
				$referer = 'http://';
			}
			$referer .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		} else {
			$referer = $_SERVER['HTTP_REFERER'];
		}
		?>
		<input type="hidden" name="referer" value="<?php echo $referer; ?>" />
		<input type="submit" name="chooseAddressType" value="<?php _e('Choose','wpshop'); ?>" />
	</form><?php
$tpl_element['wpshop_customer_new_addresse_type_choice_form'] = ob_get_contents();
ob_end_clean();



ob_start();
?>{WPSHOP_VARIATION_NAME} : {WPSHOP_VARIATION_VALUE}<br/><?php
$tpl_element['common']['default']['admin_email_summary']['email_content']['product_option']['cart_variation_detail'] = ob_get_contents();
ob_end_clean();




/****ADDRESSES DASHBOARD TEMPLATE ****/
/*Addresses DashBoard Head-Links*/
ob_start();
?>
<p class="formField">
<a href="{WPSHOP_LOGOUT_LINK_ADDRESS_DASHBOARD}" title="<?php _e('Logout','wpshop'); ?>" class="right"><?php _e('Logout','wpshop'); ?></a>
<a href="{WPSHOP_ACCOUNT_LINK_ADDRESS_DASHBOARD}" title="<?php _e('Edit my account infos', 'wpshop'); ?>"><?php _e('Edit my account infos', 'wpshop'); ?></a>
</p>
<?php
$tpl_element['link_head_addresses_dashboard'] = ob_get_contents();
ob_end_clean();


/*Addresses DashBoard  shipping & billing addresses display*/
ob_start();
?><div id="wpshop_customer_adresses_container_{WPSHOP_ADDRESS_TYPE}" class="big wpshop_customer_adresses_container wpshop_customer_adresses_container_{WPSHOP_ADDRESS_TYPE}" >
<input type="hidden" id="hidden_input_{WPSHOP_ADDRESS_TYPE}" name="{WPSHOP_ADDRESS_TYPE}" value="{WPSHOP_DEFAULT_ADDRESS_ID}" />
	<h3>
		{WPSHOP_CUSTOMER_ADDRESS_TYPE_TITLE}
		{WPSHOP_ADDRESS_COMBOBOX}
	</h3>

	<div class="wpshop_addresses_management_buttons">
		{WPSHOP_ADDRESS_BUTTONS}
		<div class="wpshop_clear" ></div>
	</div>

	<div id="choosen_address_{WPSHOP_ADDRESS_TYPE}" class="choosen_address_{WPSHOP_ADDRESS_TYPE}">
		{WPSHOP_CUSTOMER_CHOOSEN_ADDRESS}
		<div class="wpshopHide" id="loader_{WPSHOP_ADDRESS_TYPE}" ><img src="{WPSHOP_LOADING_ICON}" alt="loading..." /></div>
	</div>
</div>
<?php
$tpl_element['display_addresses_by_type_container'] = ob_get_contents();
ob_end_clean();


ob_start();
?><div id="edit_link_{WPSHOP_ADDRESS_TYPE}" class="alignleft"><a href="{WPSHOP_choosen_address_LINK_EDIT}" title="<?php _e('Edit', 'wpshop'); ?>"><?php _e('Edit', 'wpshop'); ?></a></div>
<?php
$tpl_element['addresses_box_actions_button_edit'] = ob_get_contents();
ob_end_clean();

ob_start();
?>
<a href="{WPSHOP_ADD_NEW_ADDRESS_LINK}" id="add_new_address_{WPSHOP_ADDRESS_TYPE}" class="add_new_address alignright" title="{WPSHOP_ADD_NEW_ADDRESS_TITLE}">{WPSHOP_ADD_NEW_ADDRESS_TITLE}</a>
<?php
$tpl_element['addresses_box_actions_button_new_address'] = ob_get_contents();
ob_end_clean();


/* ADDRESSES LIST BY TYPE COMBOBOX*/
ob_start();
?><select class="alignright address_choice_select"  data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_change_address' ); ?>" id='{WPSHOP_ADDRESS_TYPE}'>{WPSHOP_ADDRESS_COMBOBOX_OPTION}</select><?php
$tpl_element['addresses_type_combobox'] = ob_get_contents();
ob_end_clean();


/* ADDRESS CONTAINER */
ob_start();
?><ul class="wpshop_customer_adress_container{WPSHOP_ADRESS_CONTAINER_CLASS}" >{WPSHOP_CUSTOMER_ADDRESS_CONTENT}</ul><?php
$tpl_element['display_address_container'] = ob_get_contents();
ob_end_clean();


/* ADDRESS EACH LINE */
ob_start();
?>
<li class="{WPSHOP_CUSTOMER_ADDRESS_ELEMENT_KEY}" >{WPSHOP_CUSTOMER_ADDRESS_ELEMENT}&nbsp;</li><?php
$tpl_element['display_address_line'] = ob_get_contents();
ob_end_clean();


/* ADDRESS EACH LINE */
ob_start();
?><div class="wpshop_terms_box" id="wpshop_terms_acceptation_box" >{WPSHOP_TERMS_ACCEPTATION_BOX_CONTENT}</div><?php
$tpl_element['wpshop_terms_box'] = ob_get_contents();
ob_end_clean();



/** Shipping Method **/
ob_start();
?>
<div class="wps_shipping_method_choice wps_shipping_method_{WPSHOP_SHIPPING_METHOD_CODE}" ><input type="radio" data-nonce="<?php echo wp_create_nonce( 'wps_calculate_shipping_cost' ); ?>" name="wps_shipping_method_choice" id="{WPSHOP_SHIPPING_METHOD_CODE}" value="{WPSHOP_SHIPPING_METHOD_NAME}" {WPSHOP_DEFAULT_SHIPPING_METHOD} /> <img src="{WPSHOP_SHIPPING_METHOD_IMG}" alt="" /> {WPSHOP_SHIPPING_METHOD_NAME} {WPSHOP_SHIPPING_METHOD_EXTRA_PARAMS}</div>
<div class="wps_shipping_method_additional_element_container {WPSHOP_SHIPPING_METHOD_CONTAINER_CLASS}" id="container_{WPSHOP_SHIPPING_METHOD_CODE}">{WPSHOP_SHIPPING_METHOD_CONTENT}</div>
<div class="clear"></div>
<?php
$tpl_element['shipping_method_choice'] = ob_get_contents();
ob_end_clean();


/** Restart the order Button **/
ob_start();
?>
<img src="{WPSHOP_RESTART_ORDER_LOADER}" alt="Loading..." id="restart_order_loader" class="alignright" style="border:0px solid #FFF" /><button data-nonce="<?php echo wp_create_nonce( 'ajax_wpshop_restart_the_order' ); ?>" id="restart_order" class="alignright wps-restart-order-btn" ><?php _e('Restart the order', 'wpshop'); ?></button>
<?php
$tpl_element['button_restart_the_order'] = ob_get_contents();
ob_end_clean();



/** LATEST PRODUCTS ORDERED **/
ob_start();
?>
<h2><?php _e('Latest products ordered', 'wpshop')?></h2>
{WPSHOP_LATEST_PRODUCTS_ORDERED}
<?php
$tpl_element['latest_products_ordered'] = ob_get_contents();
ob_end_clean();





/** New Modal Add to cart confirmation Footer **/
ob_start();
?>
<a class="wps-bton wps-bton-second wpsjq-closeModal"><?php _e( 'Continue shopping', 'wpshop'); ?></a>	<a href="{WPSHOP_LINK_CART_PAGE}" type="button" class="wps-bton wps-bton-first"><?php _e( 'Order', 'wpshop'); ?></a>
<?php
$tpl_element['wps_new_add_to_cart_confirmation_modal_footer'] = ob_get_contents();
ob_end_clean();

/** New Modal Add to cart confirmation Footer **/
ob_start();
?>
<div class="wps-modal-product">
	<a href="#" class="product_thumbnail-mini-list" title="{WPSHOP_PRODUCT_TITLE}">
		{WPSHOP_PRODUCT_PICTURE}
	</a>
	<span class="product_information-mini-list" itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers">
		<h2 itemprop="name" >{WPSHOP_PRODUCT_TITLE}</h2>
		<p>{WPSHOP_PRODUCT_PRICE}</p>
	</span>
</div>
<div>{WPSHOP_RELATED_PRODUCTS}</div>
<?php
$tpl_element['wps_new_add_to_cart_confirmation_modal'] = ob_get_contents();
ob_end_clean();
