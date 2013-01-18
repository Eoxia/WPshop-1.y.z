<?php
/**
* Products management method file
*
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class wpshop_products {
	/**
	*	Définition du code de la classe courante
	*/
	const currentPageCode = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
	const current_page_variation_code = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION;

	/**
	*	Déclaration des produits et variations en tant que "post" de wordpress
	*
	*	@see register_post_type()
	*/
	function create_wpshop_products_type() {

		/*	Définition des produits 	*/
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, array(
			'labels' => array(
				'name'					=> __( 'Products', 'wpshop' ),
				'singular_name' 		=> __( 'Catalog', 'wpshop' ),
				'add_new_item' 			=> __( 'Add new product', 'wpshop' ),
				'add_new' 				=> __( 'Add new product', 'wpshop' ),
				'add_new_item' 			=> __( 'Add new product', 'wpshop' ),
				'edit_item' 			=> __( 'Edit product', 'wpshop' ),
				'new_item' 				=> __( 'New product', 'wpshop' ),
				'view_item' 			=> __( 'View product', 'wpshop' ),
				'search_items' 			=> __( 'Search products', 'wpshop' ),
				'not_found' 			=> __( 'No products found', 'wpshop' ),
				'not_found_in_trash' 	=> __( 'No products found in Trash', 'wpshop' ),
				'parent_item_colon' 	=> ''
			),
			'supports' 				=> unserialize(WPSHOP_REGISTER_POST_TYPE_SUPPORT),
			'public' 				=> true,
			'has_archive'			=> true,
			'show_in_nav_menus' 	=> true,
			// 'rewrite' 			=> false,	//	For information see below
			'taxonomies' 			=> array( WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES ),
			'menu_icon' 			=> WPSHOP_MEDIAS_URL . "icones/wpshop_menu_icons.png"
		));

		/*	Définition des variations de produit (Déclinaisons)	*/
		register_post_type( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, array(
			'labels'				=> array(
				'name' 					=> __( 'Variations', 'wpshop' ),
				'singular_name' 		=> __( 'Variation', 'wpshop' ),
				'add_new' 				=> __( 'Add Variation', 'wpshop' ),
				'add_new_item' 			=> __( 'Add New Variation', 'wpshop' ),
				'edit' 					=> __( 'Edit', 'wpshop' ),
				'edit_item' 			=> __( 'Edit Variation', 'wpshop' ),
				'new_item' 				=> __( 'New Variation', 'wpshop' ),
				'view' 					=> __( 'View Variation', 'wpshop' ),
				'view_item' 			=> __( 'View Variation', 'wpshop' ),
				'search_items' 			=> __( 'Search Variations', 'wpshop' ),
				'not_found' 			=> __( 'No Variations found', 'wpshop' ),
				'not_found_in_trash' 	=> __( 'No Variations found in trash', 'wpshop' ),
				'parent_item_colon' 	=> ''
			),
			'supports' 				=> unserialize(WPSHOP_REGISTER_POST_TYPE_SUPPORT),
			'public' 				=> true,
			'has_archive'			=> true,
			'show_in_nav_menus' 	=> true,
			'show_in_menu' 			=> 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,

			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'hierarchical' 			=> false,

// 			'public' 				=> true,
// 			'show_ui' 				=> false,
// 			'rewrite' 				=> false,
// 			'query_var'				=> true,
// 			'supports' 				=> array( 'title', 'editor', 'page-attributes', 'thumbnail' ),
// 			'show_in_nav_menus' 	=> false
			)
		);

		// add to our plugin init function
		global $wp_rewrite;
		/*	Slug url is set into option	*/
		$options = get_option('wpshop_catalog_product_option', array());
		$gallery_structure = (!empty($options['wpshop_catalog_product_slug']) ? $options['wpshop_catalog_product_slug'] : 'catalog') . '/%' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '%/%' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '%';
		$wp_rewrite->add_rewrite_tag('%' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '%', '([^/]+)', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . "=");
		$wp_rewrite->add_permastruct(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $gallery_structure, false);
	}

	/**
	*	Create the different bow for the product management page looking for the attribute set to create the different boxes
	*/
	function add_meta_boxes() {
		global $post, $currentTabContent;

		if(!empty($post->post_type) && ( ($post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) || ($post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION) ) ) {
			/*	Get the attribute set list for the current entity	*/
			$attributeEntitySetList = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode));
			/*	Check if the meta information of the current product already exists 	*/
			$post_attribute_set_id = get_post_meta($post->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
			/*	Check if the product has been saved without meta information set	*/
			$attribute_set_id = wpshop_attributes::get_attribute_value_content('product_attribute_set_id', $post->ID, self::currentPageCode);

			/*	Check if an attribute has already been choosen for the curernt entity or if the user has to choose a entity set before continuing	*/
			if(((count($attributeEntitySetList) == 1) || ((count($attributeEntitySetList) > 1) && (($post_attribute_set_id > 0) || (isset($attribute_set_id->value) && ($attribute_set_id->value > 0)))))){
				if((count($attributeEntitySetList) == 1) || (($post_attribute_set_id <= 0) && ($attribute_set_id->value <= 0))){
					$post_attribute_set_id = $attributeEntitySetList[0]->id;
				}
				elseif(($post_attribute_set_id <= 0) && ($attribute_set_id->value > 0)){
					$post_attribute_set_id = $attribute_set_id->value;
				}
				$currentTabContent = wpshop_attributes::entities_attribute_box($post_attribute_set_id, self::currentPageCode, $post->ID);

				$fixed_box_exist = false;
				/*	Get all the other attribute set for hte current entity	*/
				if(isset($currentTabContent['box']) && count($currentTabContent['box']) > 0){
					foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
						if(!empty($currentTabContent['box'][$boxIdentifier.'_backend_display_type']) &&( $currentTabContent['box'][$boxIdentifier.'_backend_display_type'] == 'movable-tab')){
							add_meta_box('wpshop_product_' . $boxIdentifier, __($boxTitle, 'wpshop'), array('wpshop_products', 'meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default', array('boxIdentifier' => $boxIdentifier));
						}
						else $fixed_box_exist = true;
					}
				}
				if ( $fixed_box_exist ) {
					add_meta_box('wpshop_product_fixed_tab', __('Product data', 'wpshop'), array('wpshop_products', 'product_data_meta_box'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'high', array('currentTabContent' => $currentTabContent));
					add_meta_box('wpshop_product_fixed_tab', __('Product data', 'wpshop'), array('wpshop_products', 'product_data_meta_box'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, 'normal', 'high', array('currentTabContent' => $currentTabContent));
				}

				add_meta_box('wpshop_wpshop_variations', __('Product variation', 'wpshop'), array('wpshop_products', 'meta_box_variations'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
				// Actions
				add_meta_box('wpshop_product_actions', __('Actions', 'wpshop'), array('wpshop_products', 'product_actions_meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'side', 'default');
			}
			else if ( count($attributeEntitySetList) > 1 ) {
				$input_def['id'] = 'product_attribute_set_id';
				$input_def['name'] = 'product_attribute_set_id';
				$input_def['value'] = '';
				$input_def['type'] = 'select';
				$input_def['possible_value'] = $attributeEntitySetList;

				$input_def['value'] = '';
				foreach ($attributeEntitySetList as $set) {
					if( $set->default_set == 'yes' ) {
						$input_def['value'] = $set->id;
					}
				}

				$currentTabContent['boxContent']['attribute_set_selector'] = '
	<ul class="attribute_set_selector" >
		<li class="attribute_set_selector_title_select" ><label for="title" >' . __('Choose a title for your product', 'wpshop') . '</label></li>
		<li class="attribute_set_selector_group_selector" ><label for="' . $input_def['id'] . '" >' . __('Choose an attribute group for this product', 'wpshop') . '</label>&nbsp;'.wpshop_form::check_input_type($input_def, self::currentPageCode.'_attribute[integer]').'</li>
		<li class="attribute_set_selector_save_instruction" >' . __('Save the product with the "Save draft" button on the right side', 'wpshop') . '</li>
		<li class="attribute_set_selector_after_save_instruction" >' . __('Once the group chosen, the different attribute will be displayed here', 'wpshop') . '</li>
	</ul>';

				add_meta_box('wpshop_product_attribute_set_selector', __('Product attributes', 'wpshop'), array('wpshop_products', 'meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'high', array('boxIdentifier' => 'attribute_set_selector'));
			}

			add_meta_box('wpshop_product_picture_management', __('Picture management', 'wpshop'), array('wpshop_products', 'meta_box_picture'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
			add_meta_box('wpshop_product_document_management', __('Document management', 'wpshop'), array('wpshop_products', 'meta_box_document'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
		}

	}

	/**
	 * Display the fixed box
	 */
	function product_data_meta_box($post, $metaboxArgs) {
		$output = '';

		$currentTabContent = $metaboxArgs['args']['currentTabContent'];

		echo '<div id="fixed-tabs" class="wpshop_tabs wpshop_detail_tabs wpshop_product_attribute_tabs" >
				<ul>';
		if(!empty($currentTabContent['box'])){
			foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
				if(!empty($currentTabContent['boxContent'][$boxIdentifier])) {
					if($currentTabContent['box'][$boxIdentifier.'_backend_display_type']=='fixed-tab') {
						echo '<li><a href="#tabs-'.$boxIdentifier.'">'.__($boxTitle, 'wpshop').'</a></li>';
					}
				}
			}
		}
		echo '<li><a href="#tabs-product-related">'.__('Related products', 'wpshop').'</a></li>';
		echo '<li class="wpshop_product_data_display_tab" ><a href="#tabs-product-display">'.__('Product display', 'wpshop').'</a></li>';
		echo '</ul>';

		if(!empty($currentTabContent['box'])){
			foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
				if(!empty($currentTabContent['boxContent'][$boxIdentifier])) {
					if($currentTabContent['box'][$boxIdentifier.'_backend_display_type']=='fixed-tab') {
						echo '<div id="tabs-'.$boxIdentifier.'">'.$currentTabContent['boxContent'][$boxIdentifier].'</div>';
					}
				}
			}
		}

		echo '<div id="tabs-product-related">' . self::related_products_meta_box_content($post) . '</div>';
		echo '<div id="tabs-product-display">' . self::product_frontend_display_config_meta_box($post) . '</div>';
		if (!empty($currentTabContent['boxMore'])) {
			echo $currentTabContent['boxMore'];
		}
		echo '</div>';

		echo $output;
	}

	/**
	 * Output the content for related product metabox
	 * @param object $post The current edited post
	 * @return string
	 */
	function related_products_meta_box_content( $post ) {
		$content = $existing_selection = '';

		if( !empty($post->ID) ) {
			$related_products_id = get_post_meta($post->ID, WPSHOP_PRODUCT_RELATED_PRODUCTS, true);
			if( !empty($related_products_id) && !empty($related_products_id[0]) ) {
				foreach ($related_products_id as $related_product_id) {
					$existing_selection .= '<option selected value="' . $related_product_id . '" >' . get_the_title($related_product_id) . '</option>';
				}
			}
		}

		$content = '<p>' . __('Type the begin of the product name in the field below in order to add it to the related product list', 'wpshop') . '</p>
			<select name="related_products_list[]" id="related_products_list" class="ajax_chosen_select" multiple >' . $existing_selection . '</select>
			<input type="hidden" id="wpshop_ajax_search_element_type" name="wpshop_ajax_search_element_type" value="' . $post->post_type . '" />
			<input type="hidden" id="wpshop_nonce_ajax_search" name="wpshop_nonce_ajax_search" value="' . wp_create_nonce("wpshop_element_search") . '" />';

		return $content;
	}

	/**
	 * Define the metabox content for the action box
	 * @param obejct $post The current element being edited
	 */
	function product_actions_meta_box_content( $post ) {
		$output = '';
		/*
		 * Template parameters
		*/
		$template_part = 'wpshop_duplicate_product';
		$tpl_component = array();
		$tpl_component['PRODUCT_ID'] = $post->ID;

		/*
		 * Build template
		*/
		$output = wpshop_display::display_template_element($template_part, $tpl_component, array(), 'admin');
		unset($tpl_component);

		echo $output;
	}

	/**
	 *	Define the metabox for managing products pictures
	 */
	function meta_box_picture($post, $metaboxArgs){
		global $post;
		$product_picture_galery_metabox_content = '';

		$product_picture_galery_metabox_content = '
<a href="media-upload.php?post_id=' . $post->ID . '&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=566" class="thickbox" title="Manage Your Product Images" >' . __('Add pictures for the product', 'wpshop' ) . '</a>
<div class="alignright reload_box_attachment" ><img src="' . WPSHOP_MEDIAS_ICON_URL . 'reload_vs.png" alt="' . __('Reload the box', 'wpshop') . '" title="' . __('Reload the box', 'wpshop') . '" class="reload_attachment_box" id="reload_box_picture" /></div>
<ul id="product_picture_list" class="product_attachment_list product_attachment_list_box_picture clear" >' . self::product_attachement_by_type($post->ID, 'image/', 'media-upload.php?post_id=' . $post->ID . '&amp;tab=gallery&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=566') . '</ul>';

		echo $product_picture_galery_metabox_content;
	}

	/**
	 *	Define the metabox for managing products documents
	 */
	function meta_box_document($post, $metaboxArgs){
		$output = '';

		$output = '
<a href="media-upload.php?post_id=' . $post->ID . '&amp;TB_iframe=1&amp;width=640&amp;height=566" class="thickbox clear" title="Manage Your Product Document" >' . __('Add documents for the document', 'wpshop' ) . '</a> (Seuls les documents <i>.pdf</i> seront pris en compte)
<div class="alignright reload_box_attachment" ><img src="' . WPSHOP_MEDIAS_ICON_URL . 'reload_vs.png" alt="' . __('Reload the box', 'wpshop') . '" title="' . __('Reload the box', 'wpshop') . '" class="reload_attachment_box" id="reload_box_document" /></div>
<ul id="product_document_list" class="product_attachment_list product_attachment_list_box_document clear" >' . self::product_attachement_by_type($post->ID, 'application/pdf', 'media-upload.php?post_id=' . $post->ID . '&amp;tab=library&amp;TB_iframe=1&amp;width=640&amp;height=566') . '</ul>';

		echo $output;
	}

	/**
	 *	Define the content of the product main information box
	 */
	function meta_box_content($post, $metaboxArgs){
		global $currentTabContent;

		/*	Add the extra fields defined by the default attribute group in the general section	*/
		echo '<div class="wpshop_extra_field_container" >' . $currentTabContent['boxContent'][$metaboxArgs['args']['boxIdentifier']] . '</div>';
	}

	function attached_address_meta_box( $post ) {
		global $wpdb; global $wpshop_account;
		$output = '';
		$entity_type = get_post( intval( wpshop_tools::varSanitizer($post->ID)) );
		$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE post_name = "' .WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT. '" AND post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES. '"', '');
		$entity_id = $wpdb->get_row ( $query );

		$attached_addresses = get_post_meta($entity_id->ID, '_wpshop_entity_attached_address', true);
		if ( !empty($attached_addresses) ) {
			$addresses_id = get_post_meta($_GET['post'], '_wpshop_attached_address', true);
			foreach ( $attached_addresses as $attached_address) {
				$ad_id = '';
				if ( !empty($addresses_id) ) {
					foreach ( $addresses_id as $address_id ) {
						$address_type = get_post_meta($address_id, '_wpshop_address_attribute_set_id', true);
						if ($address_type == $attached_address) {
							$ad_id = $address_id;
						}
					}
				}
				$output .= $wpshop_account->display_form_fields($attached_address, $ad_id);
			}
		}
		else {
			$output .= sprintf( __('If you want to affect an address for this product. You have to configure the address type to link into %sEntity -> Product%s', 'wpshop'), '<a href="' . admin_url('post.php?post=' . wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) . '&amp;action=edit') . '" >', '</a>' );
		}

		echo $output;
	}

	/**
	 * Define the metabox content for product custom display in product
	 * @param object $post The current element being edited
	 * @return string The metabox content
	 */
	function product_frontend_display_config_meta_box( $post ) {
		$content = '';

		$product_attribute_frontend_display_config = null;
		if( !empty($post->ID) ) {
			$product_attribute_frontend_display_config = get_post_meta($post->ID, WPSHOP_PRODUCT_FRONT_DISPLAY_CONF, true);

			$extra_options = get_option('wpshop_extra_options', array());
			$column_count = (!empty($extra_options['WPSHOP_COLUMN_NUMBER_PRODUCT_EDITION_FOR_FRONT_DISPLAY'])?$extra_options['WPSHOP_COLUMN_NUMBER_PRODUCT_EDITION_FOR_FRONT_DISPLAY']:3);
			$attribute_list = wpshop_attributes::getElementWithAttributeAndValue(wpshop_entities::get_entity_identifier_from_code( self::currentPageCode ), $post->ID, get_locale());
			$column = 1;

			if ( WPSHOP_DEFINED_SHOP_TYPE == 'sale' ) {
				$sub_tpl_component = array();
				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_NAME'] = __('Action on product', 'wpshop');

				$tpl_component = array();
				$tpl_component['ADMIN_ATTRIBUTE_LABEL'] = __('Add to cart button', 'wpshop');
				$tpl_component['ADMIN_ATTRIBUTE_FD_NAME'] = self::currentPageCode . '_attr_frontend_display[product_action_button][add_to_cart]';
				$tpl_component['ADMIN_ATTRIBUTE_FD_ID'] = $post->ID . '_product_action_button_add_to_cart';
				$button_is_set_to_be_displayed = (WPSHOP_DEFINED_SHOP_TYPE == 'sale') ? 'yes' : 'no';
				$tpl_component['ADMIN_ATTRIBUTE_COMPLETE_SHEET_CHECK'] = wpshop_attributes::check_attribute_display( $button_is_set_to_be_displayed, $product_attribute_frontend_display_config, 'product_action_button', 'add_to_cart', 'complete_sheet') ? ' checked="checked"' : '';
				$tpl_component['ADMIN_ATTRIBUTE_MINI_OUTPUT_CHECK'] = wpshop_attributes::check_attribute_display( $button_is_set_to_be_displayed, $product_attribute_frontend_display_config, 'product_action_button', 'add_to_cart', 'mini_output') ? ' checked="checked"' : '';
				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_CONTENT'] = wpshop_display::display_template_element('wpshop_admin_attr_config_for_front_display', $tpl_component, array(), 'admin');
				unset($tpl_component);

				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_FD_NAME'] = self::currentPageCode . '_attr_frontend_display[product_action_button][add_to_cart]';
				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_FD_ID'] = 'product_action_button_add_to_cart';
				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_INPUT_CHECKBOX'] = '';
				$sub_content[1] = wpshop_display::display_template_element('wpshop_admin_attr_set_section_for_front_display', $sub_tpl_component, array(), 'admin');
			}

			if ( !empty($attribute_list[$post->ID]) && is_array($attribute_list[$post->ID]) ) {
				foreach ( $attribute_list[$post->ID] as $attribute_set_section_name => $attribute_set_section_content ) {
					if ( !isset($sub_content[$column]) ) {
						$sub_content[$column] = '';
					}

					$attribute_sub_output = '';
					foreach ( $attribute_set_section_content['attributes'] as $attribute_id => $attribute_def ) {
						if ( $attribute_def['attribute_code'] != 'product_attribute_set_id' ) {
							$tpl_component = array();
							$tpl_component['ADMIN_ATTRIBUTE_LABEL'] = $attribute_def['frontend_label'];
							$tpl_component['ADMIN_ATTRIBUTE_FD_NAME'] = self::currentPageCode . '_attr_frontend_display[attribute][' . $attribute_def['attribute_code'] . ']';
							$tpl_component['ADMIN_ATTRIBUTE_FD_ID'] = $post->ID . '_' . $attribute_def['attribute_code'];
							$tpl_component['ADMIN_ATTRIBUTE_COMPLETE_SHEET_CHECK'] = wpshop_attributes::check_attribute_display( $attribute_def['is_visible_in_front'], $product_attribute_frontend_display_config, 'attribute', $attribute_def['attribute_code'], 'complete_sheet') ? ' checked="checked"' : '';
							$tpl_component['ADMIN_ATTRIBUTE_MINI_OUTPUT_CHECK'] = wpshop_attributes::check_attribute_display( $attribute_def['is_visible_in_front_listing'], $product_attribute_frontend_display_config, 'attribute', $attribute_def['attribute_code'], 'mini_output') ? ' checked="checked"' : '';
							$attribute_sub_output .= wpshop_display::display_template_element('wpshop_admin_attr_config_for_front_display', $tpl_component, array(), 'admin');
							unset($tpl_component);
						}
					}

					$tpl_component = array();
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_NAME'] = $attribute_set_section_name;
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_CONTENT'] = $attribute_sub_output;
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_FD_NAME'] = self::currentPageCode . '_attr_frontend_display[attribute_set_section][' . $attribute_set_section_content['code'] . ']';
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_FD_ID'] = $attribute_set_section_content['code'];
					$ADMIN_ATTRIBUTE_SET_SECTION_COMPLETE_SHEET_CHECK = wpshop_attributes::check_attribute_display( $attribute_set_section_content['display_on_frontend'], $product_attribute_frontend_display_config, 'attribute_set_section', $attribute_set_section_content['code'], 'complete_sheet') ? ' checked="checked"' : '';
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_INPUT_CHECKBOX'] = '<input type="checkbox" name="' .  self::currentPageCode . '_attr_frontend_display[attribute_set_section][' . $attribute_set_section_content['code'] . '][complete_sheet]" id="' .  $attribute_set_section_content['code'] . '_complete_sheet" value="yes"' . $ADMIN_ATTRIBUTE_SET_SECTION_COMPLETE_SHEET_CHECK . ' /><label for="' .  $attribute_set_section_content['code'] . '_complete_sheet" >' . __('Display in product page', 'wpshop') . '</label>';
					$sub_content[$column] .= wpshop_display::display_template_element('wpshop_admin_attr_set_section_for_front_display', $tpl_component, array(), 'admin');
					$column++;
					if ( $column > $column_count ){
						$column = 1;
					}
				}
			}
			$tpl_component = array();
			$tpl_component['ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT'] = '';
			for ( $i=1; $i<=$column_count; $i++ ) {
				if (!empty($sub_content[$i]))
					$tpl_component['ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT'] .= '<div class="alignleft" >' . $sub_content[$i] . '</div>';
			}
			$tpl_component['ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT_CLASS'] = empty($product_attribute_frontend_display_config) ? ' class="wpshopHide" ' : '';
			$tpl_component['ADMIN_PRODUCT_ATTRIBUTE_FRONTEND_DISPLAY_MAIN_CHOICE_CHECK'] = empty($product_attribute_frontend_display_config) ? ' checked="checked"' : '';
			$tpl_component['ADMIN_ATTRIBUTE_FD_NAME'] = self::currentPageCode . '_attr_frontend_display';

			$content = wpshop_display::display_template_element('wpshop_admin_attr_set_section_for_front_display_default_choice',$tpl_component, array(), 'admin') . '<div class="clear"></div>';
		}

		return $content;
	}

	/**
	 * Retrieve the attribute list used for sorting product into frontend listing
	 * @return array The attribute list to use for listing sorting
	 */
	function get_sorting_criteria() {
		global $wpdb;

		$data = array(array('code' => 'title', 'frontend_label' => __('Product name', 'wpshop')), array('code' => 'date', 'frontend_label' => __('Date added', 'wpshop')), array('code' => 'modified', 'frontend_label' => __('Date modified', 'wpshop')));

		$query = $wpdb->prepare('SELECT code, frontend_label FROM '.WPSHOP_DBT_ATTRIBUTE.' WHERE is_used_for_sort_by="yes"', '');
		$results = $wpdb->get_results($query, ARRAY_A);
		if(!empty($results))$data = array_merge($data, $results);

		return $data;
	}

	function get_products_matching_attribute($attr_name, $attr_value) {
		global $wpdb;

		$products = array();
		$query = "SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code=%s";
		$data = (array)$wpdb->get_row($wpdb->prepare($query, $attr_name));

		if(!empty($data)) {
			// Find which table to take
			if($data['data_type']=='datetime') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME; }
			elseif($data['data_type']=='decimal') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL; }
			elseif($data['data_type']=='integer') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER; }
			elseif($data['data_type']=='options') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS; }
			elseif($data['data_type']=='text') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT; }
			elseif($data['data_type']=='varchar') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR; }

			if(isset($table_name)) {
				// If the value is an id of a select, radio or checkbox
				if(in_array($data['backend_input'], array('select','multiple-select', 'radio','checkbox'))) {

					$query = $wpdb->prepare("
						SELECT ".$table_name.".entity_id FROM ".$table_name."
						LEFT JOIN ".WPSHOP_DBT_ATTRIBUTE." AS ATT ON ATT.id = ".$table_name.".attribute_id
						LEFT JOIN ".WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS." AS ATT_OPT ON ".$table_name.".value = ATT_OPT.id
						WHERE ATT.code=%s AND ATT_OPT.value=%s", $attr_name, $attr_value
					);
					$data = $wpdb->get_results($query);

				}
				else {

					$query = $wpdb->prepare("
						SELECT ".$table_name.".entity_id FROM ".$table_name."
						INNER JOIN ".WPSHOP_DBT_ATTRIBUTE." AS ATT ON ATT.id = ".$table_name.".attribute_id
						WHERE ATT.code=%s AND ".$table_name.".value=%s", $attr_name, sprintf('%.5f', $attr_value) // force useless zero like 48.58000
					);
					$data = $wpdb->get_results($query);

				}
			} else return __('Incorrect shortcode','wpshop');
		} else return __('Incorrect shortcode','wpshop');

		if(!empty($data)) {
			foreach($data as $p) {
				$products[] = $p->entity_id;
			}
		}
		return $products;
	}

	/**
	 * Related product shortcode reader
	 *
	 * @param array $atts {
	 *	pid : Product idenfifier to get related element for
	 *	display_mode : The output mode if defined (grid || list)
	 * }
	 *
	 * @return string
	 *
	 */
	function wpshop_related_products_func($atts) {
		global $wp_query;

		$atts['product_type'] = 'related';
		if(empty($atts['pid'])) $atts['pid'] = $wp_query->posts[0]->ID;

		return self::wpshop_products_func($atts);
	}

	/**
	* Display a list of product from a shortcode
	*
	* @param array $atts {
	*	limit : The number of element to display
	*	order : The information to order list by
	*	sorting : List order (ASC | DESC)
	*	display : Display size (normal | mini)
	*	type : Display tyep (grid | list) only work with display=normal
	*	pagination : The number of element per page
	* }
	*
	* @return string
	*
	**/
	function wpshop_products_func($atts) {
		global $wpdb;
		global $wp_query;

		$have_results = false;
		$output_results = true;
		$type = ( empty($atts['type']) OR !in_array($atts['type'], array('grid','list')) ) ? WPSHOP_DISPLAY_LIST_TYPE : $atts['type'];
		$pagination = isset($atts['pagination']) ? intval($atts['pagination']) : WPSHOP_ELEMENT_NB_PER_PAGE;
		$cid = !empty($atts['cid']) ? $atts['cid'] : 0;
		$pid = !empty($atts['pid']) ? $atts['pid'] : 0;
		$order_by_sorting = (!empty($atts['sorting']) && ($atts['sorting'] == 'DESC')) ? 'DESC' : 'ASC';
		$limit = isset($atts['limit']) ? intval($atts['limit']) : 0;
		$grid_element_nb_per_line = !empty($atts['grid_element_nb_per_line']) ? $atts['grid_element_nb_per_line'] : WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE;
		$attr = '';

		$sorting_criteria = self::get_sorting_criteria();

		/*
		 * Get products which have att_name equal to att_value
		 */
		if (!empty($atts['att_name']) && !empty($atts['att_value'])) {
			$attr = $atts['att_name'].':'.$atts['att_value'];

			$products = self::get_products_matching_attribute($atts['att_name'], $atts['att_value']);

			// Foreach on the found products
			if ( !empty($products) ) {
				$pid = implode(',',$products);
				if(empty($pid))$output_results = false;
			}
			else $output_results = false;
		}

		/*
		 * Get related products
		 */
		if (!empty($atts['product_type'])) {
			switch ($atts['product_type']) {
				case 'related':
					$product_id = !empty($atts['pid']) ? (int)$atts['pid'] : get_the_ID();
					$type = !empty($atts['display_mode']) && in_array($atts['display_mode'],array('list','grid')) ? $atts['display_mode'] : WPSHOP_DISPLAY_LIST_TYPE;
					$grid_element_nb_per_line = !empty($atts['grid_element_nb_per_line']) ? $atts['grid_element_nb_per_line'] : WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE;

					$pids = get_post_meta((int)$product_id, WPSHOP_PRODUCT_RELATED_PRODUCTS, true);
					if ( !empty($pids) && !empty($pids[0]) ) {
						$pid = implode(',', $pids);
					}
					if(empty($pid))$output_results = false;
				break;
			}
		}

		/*
		 * Output all the products
		 */
		if ( $output_results ) {
			$data = self::wpshop_get_product_by_criteria((!empty($atts['order']) ? $atts['order'] : (!empty($atts['creator']) ? ($atts['creator'] == 'current') : '')), $cid, $pid, $type, $order_by_sorting, 1, $pagination, $limit, $grid_element_nb_per_line);
			if ( $data[0] ) {
				$have_results = true;
				$string = $data[1];
			}
		}

		/*
		 * If there are result to display
		 */
		if ( $have_results ) {
			$sorting = '';
			if ( !empty($pid) ) {
				$product_list = explode(',', $pid);
				if ( count($product_list) == 1 ) {
					$atts['sorting'] = 'no';
				}
			}

			/*
			 * Template parameters
			 */
			$template_part = 'product_listing_sorting';
			$tpl_component = array();

			/*
			 * Build template
			*/
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$sorting = ob_get_contents();
				ob_end_clean();
			}
			else {
				/*
				 * Display hidden field every times
				 */
				$sub_template_part = 'product_listing_sorting_hidden_field';
				$sub_tpl_component = array();
				$sub_tpl_component['DISPLAY_TYPE'] = $type;
				$sub_tpl_component['ORDER'] = $order_by_sorting;
				$sub_tpl_component['PRODUCT_NUMBER'] = $pagination;
				$sub_tpl_component['CURRENT_PAGE'] = 1;
				$sub_tpl_component['CATEGORY_ID'] = $cid;
				$sub_tpl_component['PRODUCT_ID'] = $pid;
				$sub_tpl_component['ATTR'] = $attr;
				$tpl_component['SORTING_HIDDEN_FIELDS'] = wpshop_display::display_template_element($sub_template_part, $sub_tpl_component, array(), 'admin');
				unset($sub_tpl_component);

				if ( (!empty($sorting_criteria) && is_array($sorting_criteria)) ) {
					$sub_template_part = 'product_listing_sorting_criteria';
					$sub_tpl_component = array();
					$criteria = '';
					foreach($sorting_criteria as $c):
						$criteria .= '<option value="' . $c['code'] . '">' . __($c['frontend_label'],'wpshop') . '</option>';
					endforeach;
					$sub_tpl_component['SORTING_CRITERIA_LIST'] = $criteria;
					$tpl_component['SORTING_CRITERIA'] = wpshop_display::display_template_element($sub_template_part, $sub_tpl_component);
					unset($sub_tpl_component);
				}

				if ( empty($atts['sorting']) || ( !empty($atts['sorting']) && ($atts['sorting'] != 'no') ) ) {
					$tpl_component['DISPLAY_TYPE_STATE_GRID'] = $type == 'grid' ?' active' : null;
					$tpl_component['DISPLAY_TYPE_STATE_LIST'] = $type == 'list' ?' active' : null;
					$sorting = wpshop_display::display_template_element($template_part, $tpl_component);
				}
				else if ( !empty($atts['sorting']) && ($atts['sorting'] == 'no') ) {
					$sub_template_part = 'product_listing_sorting_criteria_hidden';
					$sub_tpl_component = array();
					$sub_tpl_component['CRITERIA_DEFAULT'] = !empty($sorting_criteria[0]['code']) ? $sorting_criteria[0]['code'] : 'title';
					$tpl_component['SORTING_CRITERIA'] = wpshop_display::display_template_element($sub_template_part, $sub_tpl_component, array(), 'admin');
					unset($sub_tpl_component);

					$template_part = 'product_listing_sorting_hidden';
					$sorting = wpshop_display::display_template_element($template_part, $tpl_component, array(), 'admin');
				}
			}
			unset($tpl_component);

			$string = '<div class="wpshop_products_block">'.$sorting.'<div class="wpshop_product_container">'.$string.'</div></div>';
		}
		else if ( empty($atts['no_result_message']) || ($atts['no_result_message'] != 'no') ) {
			$string = __('There is nothing to output here', 'wpshop');
		}

		return do_shortcode($string);
	}

	function wpshop_get_product_by_criteria( $criteria=null, $cid=0, $pid=0, $display_type, $order='ASC', $page_number, $products_per_page=0, $nb_of_product_limit=0, $grid_element_nb_per_line=WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE ) {
		global $wpdb;

		$string = '<span id="wpshop_loading">&nbsp;</span>';
		$have_results = false;
		$display_type = (!empty($display_type) && in_array($display_type,array('grid','list'))) ? $display_type : 'grid';

		$query = array(
		 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
		 'order' => $order,
		 'posts_per_page' => $products_per_page,
		 'paged' => $page_number
		);

		// If the limit is greater than zero, hide pagination and change posts_per_page var
		if ( $nb_of_product_limit > 0 ) {
			$query['posts_per_page'] = $nb_of_product_limit;
			unset($query['paged']);
		}
		if( !empty($pid) ) {
			if(!is_array($pid)){
				$pid = explode(',', $pid);
			}
			$query['post__in'] = $pid;
		}
		if ( !empty($cid) ) {
			$cid = explode(',', $cid);
			$query['tax_query'] = array(array(
				'taxonomy' => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES,
				'field' => 'id',
				'terms' => $cid,
				'operator' => 'IN'
			));
		}
		if($criteria != null) {
			switch($criteria){
				case 'creator':
				case 'author':
					$query['author'] = get_current_user_id();
					break;
				case 'title':
				case 'date':
				case 'modified':
				case 'rand':
					$query['orderby'] = $criteria;
					break;
				default:
					if(!empty($pid)) {
						$post_meta = get_post_meta($pid, '_'.$criteria, true);
					}
					else{
						$check_meta = $wpdb->prepare("SELECT COUNT(meta_id) as meta_criteria FROM " . $wpdb->postmeta . " WHERE meta_key = %s", '_'.$criteria);
						$post_meta = $wpdb->get_var($check_meta);
					}
					if(!empty($post_meta)){
						$query['orderby'] = 'meta_value';
						$query['meta_key'] = '_'.$criteria;
					}
					break;
			}
		}

		$custom_query = new WP_Query( $query );
		if ( $custom_query->have_posts() ) {
			$have_results = true;

			// ---------------- //
			// Products listing //
			// ---------------- //
			$current_position = 1;
			$product_list = '';
			while ($custom_query->have_posts()) : $custom_query->the_post();
				$cats = get_the_terms(get_the_ID(), WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
				$cats = !empty($cats) ? array_values($cats) : array();
				$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
				$product_list .= self::product_mini_output(get_the_ID(), $cat_id, $display_type, $current_position, $grid_element_nb_per_line);
				$current_position++;
			endwhile;
			$tpl_component = array();
			$tpl_component['PRODUCT_CONTAINER_TYPE_CLASS'] = ($display_type == 'grid' ? ' ' . $display_type . '_' . $grid_element_nb_per_line : '') . ' '. $display_type .'_mode';
			$tpl_component['PRODUCT_LIST'] = $product_list;
			$string = wpshop_display::display_template_element('product_list_container', $tpl_component);

			// --------------------- //
			// Pagination management //
			// --------------------- //
			if($nb_of_product_limit==0) {
				$paginate = paginate_links(array(
					'base' => '#',
					'current' => $page_number,
					'total' => $custom_query->max_num_pages,
					'type' => 'array',
					'prev_next' => false
				));
				if(!empty($paginate)) {
					$string .= '<ul class="pagination">';
					foreach($paginate as $p) {
							$string .= '<li>'.$p.'</li>';
					}
					$string .= '</ul>';
				}
			}
		}
		wp_reset_query(); // important

		return array($have_results, $string);
	}

	/**
	 * Update quantity for a product
	 * @param integer $product_id The product we want to update quantity for
	 * @param decimal $qty The new quantity
	 */
	function reduce_product_stock_qty($product_id, $qty) {
		global $wpdb;

		$product = self::get_product_data($product_id);
		if (!empty($product)) {
			$newQty = $product['product_stock']-$qty;
			if ($newQty >= 0) {
				$query = '
					SELECT wp_wpshop__attribute_value_decimal.value_id
					FROM wp_wpshop__attribute_value_decimal
					LEFT JOIN wp_wpshop__attribute ON wp_wpshop__attribute_value_decimal.attribute_id = wp_wpshop__attribute.id
					WHERE wp_wpshop__attribute_value_decimal.entity_id='.$product_id.' AND wp_wpshop__attribute.code="product_stock"
					LIMIT 1
				';
				$value_id = $wpdb->get_var($query);
				$update = $wpdb->update('wp_wpshop__attribute_value_decimal', array('value' => wpshop_tools::wpshop_clean($newQty)), array('value_id' => $value_id));
			}
		}
	}

	/**
	 * Retrieve an array with complete information about a given product
	 * @param integer $product_id
	 * @param boolean $for_cart_storage
	 * @return array Information about the product defined by first parameter
	 */
	function get_product_data( $product_id, $for_cart_storage = false ) {
		global $wpdb;

		$query = '
			SELECT P.*, PM.meta_value AS attribute_set_id
			FROM '.$wpdb->posts.' AS P
				INNER JOIN '.$wpdb->postmeta.' AS PM ON (PM.post_id=P.ID)
			WHERE
				P.ID = ' . $product_id . '
				AND ( (P.post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'") OR (P.post_type = "' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION . '") )
				AND P.post_status = "publish"
				AND	PM.meta_key = "_wpshop_product_attribute_set_id"
			LIMIT 1
		';
		$product = $wpdb->get_row($query);

		$product_data = array();
		$product_meta = array();

		if(!empty($product)) {
			$product_data['product_id'] = $product->ID;
			$product_data['post_name'] = $product->post_name;
			$product_data['product_name'] = $product->post_title;
			$product_data['post_title'] = $product->post_title;

			$product_data['product_author_id'] = $product->post_author;
			$product_data['product_date'] = $product->post_date;
			$product_data['product_content'] = $product->post_content;
			$product_data['product_excerpt'] = $product->post_excerpt;

			$product_data['product_meta_attribute_set_id'] = $product->attribute_set_id;

			$data = wpshop_attributes::get_attribute_list_for_item(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $product->ID, WPSHOP_CURRENT_LOCALE, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			foreach($data as $attribute){
				$data_type = 'attribute_value_'.$attribute->data_type;
				$value = $attribute->$data_type;
				if (in_array($attribute->backend_input, array('select','multiple-select', 'radio','checkbox'))) {
					$value = wpshop_attributes::get_attribute_type_select_option_info($value, 'value');
				}

				// Special traitment regarding attribute_code
				switch($attribute->attribute_code) {
					case 'product_weight':
						$value *= 1000;
					break;
					default:
						$value = !empty($value) ? $value : 0;
					break;
				}
				$product_data[$attribute->attribute_code] = $value;

				if(!$for_cart_storage OR $for_cart_storage && $attribute->is_recordable_in_cart_meta == 'yes') {
					$meta = get_post_meta($product->ID, 'attribute_option_'.$attribute->attribute_code, true);
					if(!empty($meta)) {
						$product_meta[$attribute->attribute_code] = $meta;
					}
				}
			}

			if ( $product->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION) {
				$variation_details = get_post_meta($product->ID, '_wpshop_variations_attribute_def', true);
				foreach ( $variation_details as $attribute_code => $attribute_value) {

					$attribute_definition = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');

					$product_meta['variation_definition'][$attribute_code]['UNSTYLED_VALUE'] = stripslashes(wpshop_attributes::get_attribute_type_select_option_info($attribute_value, 'label', $attribute_definition->data_type_to_use, true));
					$product_meta['variation_definition'][$attribute_code]['NAME'] = $attribute_definition->frontend_label;
					switch( $attribute_definition->backend_input ) {
						case 'select':
						case 'multiple-select':
						case 'radio':
						case 'checkbox':
							$attribute_value = wpshop_attributes::get_attribute_type_select_option_info($attribute_value, 'label', $attribute_definition->data_type_to_use);
							break;
					}
					$product_meta['variation_definition'][$attribute_code]['VALUE'] = stripslashes($attribute_value);
				}
			}

			$product_data['item_meta'] = !empty($product_meta) ? $product_meta : array();
			/*
			 * Get the display definition for the current product for checking custom display
			 */
			$product_data['custom_display'] = get_post_meta($product_id, WPSHOP_PRODUCT_FRONT_DISPLAY_CONF, true);
		}

		return $product_data;
	}

	/**
	 * Add a product into the db. This function is used for the EDI
	 * @param $name Name of the product
	 * @param $description Description of the product
	 * @param $attrs List of the attributes and values of the product
	 * @return boolean
	*/
	function addProduct($name, $description, $attrs=array()) {

		$new_product = wpshop_entities::create_new_entity(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $name, $description, $attrs);

		return $new_product[0];
	}

	/**
	 * Retrieve a product listing
	 * @param boolean $formated If the output have to be formated or not
	 * @param string $product_search Optionnal Define a search term for request
	 * @return object|string If $formated is set to true will display an html output with all product. Else return a wordpress database object with the product list
	 */
	function product_list($formated=false, $product_search=null) {
		global $wpdb;

		$query_extra_params = $query_extra_params_value = '';
		if( !empty($product_search) ) {
			$query_extra_params = " AND post_title LIKE '%%".$product_search."%%'";
			if ( is_array($product_search) ) {
				$query_extra_params = " AND ID IN (%s)";
				$query_extra_params_value = implode(",", $product_search);
			}
		}

		$query = $wpdb->prepare("SELECT ID, post_title FROM " . $wpdb->posts . " WHERE post_type=%s AND post_status=%s" . $query_extra_params, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish', $query_extra_params_value);
		$data = $wpdb->get_results($query);

		/*
		 * Make some arangement on output if parameter is given
		 */
		if ( $formated ) {
			$product_string='';
			foreach ($data as $d) {
				$product_string .= '
					<li class="wpshop_shortcode_element_container wpshop_shortcode_element_container_product" >
						<input type="checkbox" class="wpshop_shortcode_element wpshop_shortcode_element_product" value="'.$d->ID.'" id="'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'-'.$d->ID.'" name="products[]" /><label for="'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'-'.$d->ID.'" > '.$d->post_title.'</label>
					</li>';
			}
		}

		return $formated ? $product_string : $data;
	}

	/**
	 * Enregistrement des données pour le produit
	 */
	function save_product_custom_informations( $post_id ) {
		global $wpdb;

		if ( !empty($_REQUEST['post_ID']) && (get_post_type($_REQUEST['post_ID']) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) ) {
			if ( !empty($_REQUEST[wpshop_products::currentPageCode . '_attribute']) ) {
				/*	Fill the product reference automatically if nothing is sent	*/
				if ( empty($_REQUEST[wpshop_products::currentPageCode . '_attribute']['varchar']['product_reference']) ) {
					$query = $wpdb->prepare("SELECT MAX(ID) AS PDCT_ID FROM " . $wpdb->posts, '');
					$last_ref = $wpdb->get_var($query);
					$_REQUEST[wpshop_products::currentPageCode . '_attribute']['varchar']['product_reference'] = WPSHOP_PRODUCT_REFERENCE_PREFIX . str_repeat(0, WPSHOP_PRODUCT_REFERENCE_PREFIX_NB_FILL) . $last_ref;
				}
				else {
					/* Check if the product reference existing in the database */
					$ref = $_REQUEST[wpshop_products::currentPageCode . '_attribute']['varchar']['product_reference'];
					$query = $wpdb->prepare("SELECT value_id FROM ".WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR." WHERE value = %s AND entity_id != %d AND entity_type_id = %d", $ref, $_REQUEST['post_ID'], wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
					$existing_reference = $wpdb->get_var( $query );
					/* If this product reference exist -> Create a new product reference */
					if ( $wpdb->num_rows > 0 ) {
						$query = $wpdb->prepare("SELECT MAX(ID) AS PDCT_ID FROM " . $wpdb->posts, '');
						$last_ref = $wpdb->get_var($query);
						$_REQUEST[wpshop_products::currentPageCode . '_attribute']['varchar']['product_reference'] = WPSHOP_PRODUCT_REFERENCE_PREFIX . str_repeat(0, WPSHOP_PRODUCT_REFERENCE_PREFIX_NB_FILL) . $last_ref;
					}
				}

				/*	Save the attributes values into wpshop eav database	*/
				$update_from = !empty($_REQUEST[wpshop_products::currentPageCode . '_provenance']) ? $_REQUEST[wpshop_products::currentPageCode . '_provenance'] : '';
				wpshop_attributes::saveAttributeForEntity($_REQUEST[wpshop_products::currentPageCode . '_attribute'], wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $_REQUEST['post_ID'], get_locale(), $update_from);

				/*	Update product price looking for shop parameters	*/
				wpshop_products::calculate_price( $_REQUEST['post_ID'] );

				/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
				$productMetaDatas = array();
				foreach ( $_REQUEST[wpshop_products::currentPageCode . '_attribute'] as $attributeType => $attributeValues ) {
					foreach ( $attributeValues as $attributeCode => $attributeValue ) {
						if ( $attributeCode == 'product_attribute_set_id' ) {
							/*	Update the attribute set id for the current product	*/
							update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, $attributeValue);
						}
						$productMetaDatas[$attributeCode] = $attributeValue;
					}
				}
				update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);
			}

			if ( !empty($_REQUEST[wpshop_products::currentPageCode . '_attr_frontend_display']) && empty($_REQUEST[wpshop_products::currentPageCode . '_attr_frontend_display']['default_config']) ) {
				update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_FRONT_DISPLAY_CONF, $_REQUEST[wpshop_products::currentPageCode . '_attr_frontend_display']);
			}
			else if ( $_REQUEST['action'] != 'autosave') {
				delete_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_FRONT_DISPLAY_CONF);
			}

			/*	Save product variation	*/
			if ( !empty($_REQUEST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION]) ) {
				foreach ( $_REQUEST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION] as $variation_id => $variation_definition ) {
					foreach ( unserialize(WPSHOP_ATTRIBUTE_PRICES) as $price_attribute_code) {
						$price_attr_def = wpshop_attributes::getElement($price_attribute_code, "'valid'", 'code');
						if ( is_array($variation_definition['attribute'][$price_attr_def->data_type]) && !array_key_exists($price_attribute_code, $variation_definition['attribute'][$price_attr_def->data_type]) ) {
							$variation_definition['attribute'][$price_attr_def->data_type][$price_attribute_code] = !empty($_REQUEST[wpshop_products::currentPageCode . '_attribute'][$price_attr_def->data_type][$price_attribute_code]) ? $_REQUEST[wpshop_products::currentPageCode . '_attribute'][$price_attr_def->data_type][$price_attribute_code] : 1;
						}
					}
					/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
					$variation_metadata = array();
					foreach ( $variation_definition['attribute'] as $attributeType => $attributeValues ) {
						foreach ( $attributeValues as $attributeCode => $attributeValue ) {
							$variation_metadata[$attributeCode] = $attributeValue;
						}
					}
					update_post_meta($variation_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $variation_metadata);
					wpshop_attributes::saveAttributeForEntity($variation_definition['attribute'], wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $variation_id, get_locale());
					/*	Update product price looking for shop parameters	*/
					wpshop_products::calculate_price( $variation_id );
				}
			}

			/*	Update the related products list*/
			if ( !empty($_REQUEST['related_products_list']) ) {
				update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_RELATED_PRODUCTS, $_REQUEST['related_products_list']);
			}
			else if ( $_REQUEST['action'] != 'autosave') {
				delete_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_RELATED_PRODUCTS);
			}
		}

		flush_rewrite_rules();
	}

	/**
	 * Allows to define a specific permalink for each product by checking the parent categories
	 *
	 * @param mixed $permalink The actual permalink of the element
	 * @param object $post The post we want to set the permalink for
	 * @param void
	 *
	 * @return mixed The new permalink for the current element
	 */
	function set_product_permalink($permalink, $post, $unknown){
		global $wp_query;

		if ($post->post_type != WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)
			return $permalink;

		$product_categories = wp_get_object_terms( $post->ID, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES );

		if(count($product_categories) == 0){			/*	Product has only one category we get the only available slug	*/
			$product_category_slug = WPSHOP_UNCATEGORIZED_PRODUCT_SLUG;
		}
		elseif(count($product_categories) == 1){	/*	Product has only one category we get the only available slug	*/
			$product_category_slug = $product_categories[0]->slug;
		}
		else{																			/*	Product has several categories choose the slug of the we want	*/
			$product_category_slugs = array();
			foreach($product_categories as $product_category){
				$product_category_slugs[] = $product_category->slug;
			}
			$product_category_slug = self::currentPageCode;
		}

		$permalink = str_replace('%' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '%', $product_category_slug, $permalink);
		return apply_filters('wpshop_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_permalink', $permalink, $post->ID );
	}

	/**
	*	Get the aproduct attachement list for a given product and a given attachement type
	*
	*	@param string $attachement_type The attachement type we want to get for the product
	*
	*	@return mixed $product_attachement_list The attachement list for the current product and for the defined type
	*/
	function product_attachement_by_type($product_id, $attachement_type = 'image/', $url_on_click = ''){
		$product_attachement_list = '';

		$attachments = get_posts(array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $product_id));
		if ( is_array( $attachments ) && ( count( $attachments ) > 0)  ) {
			$product_thumbnail = get_post_thumbnail_id($product_id);
			$attachmentsNumber = 0;
			foreach ( $attachments as $attachment ) {
				if ( is_int( strpos( $attachment->post_mime_type, $attachement_type ) ) ) {
					$url = $attachment->guid;
					$link_option = '';
					if ( $url_on_click != '' ) {
						$url = $url_on_click;
						$link_option = ' class="thickbox" ';
					}
					/*	Build the attachment output with the different parameters	*/
					$attachment_icon = 0;
					$attachement_more_informations = '';
					if ( $attachement_type == 'image/' ) {
						if ( $link_option == '' ) {
							$link_option = 'rel="appendix"';
						}
						$li_class = "product_picture_item";
						if ( $product_thumbnail == $attachment->ID ) {
							// $attachement_more_informations = '<br/><span class="product_thumbnail_indicator" >' . __('Product thumbnail', 'wpshop') . '</span>';
						}
					}
					else {
						if ( !empty ( $link_option ) ) {
							$link_option = 'target="product_document"';
						}
						$li_class = "product_document_item";
						$attachment_icon = 1;
						$attachement_more_informations = '<br/><span>' . $attachment->post_title . '</span>';
					}

					/*	Add the attchment to the list	*/
					$attachment_output = wp_get_attachment_image($attachment->ID, 'thumbnail', $attachment_icon);
					if ( !empty( $attachment_output ) ) {
						$product_attachement_list .= '<li class="' . $li_class . '" ><a href="' . $url . '" ' . $link_option . ' >' . $attachment_output . '</a>' . $attachement_more_informations . '<span class="delete_post_thumbnail" id="thumbnail_'.$attachment->ID.'"></span></li>';

						$attachmentsNumber++;
					}
				}
			}

			if($attachmentsNumber <= 0){
				$product_attachement_list .= '<li class="product_document_item" >' . __('No attachement were found for this product', 'wpshop') . '</li>';
			}
		}
		return $product_attachement_list;
	}

	/**
	*	Define output for product
	*
	*	@param mixed $initialContent The initial product content defined into wordpress basic admin interface
	*	@param integer $product_id The product identifier we want to get and output attribute for
	*
	*	@return mixed $content The content to add or to modify the product output in frontend
	*/
	function product_complete_sheet_output($initialContent, $product_id) {
		$content = $attributeContentOutput = '';

		/*
		 * Log number of view for the current product
		 */
		$product_view_number = get_post_meta($product_id, WPSHOP_PRODUCT_VIEW_NB, true);
		$product_view_number++;
		update_post_meta($product_id, WPSHOP_PRODUCT_VIEW_NB, $product_view_number);

		/*
		 * Get product definition
		*/
		$product = self::get_product_data($product_id);

		/*
		 * Get the product thumbnail
		 */
		$productThumbnail = wpshop_display::display_template_element('product_thumbnail_default', array());
		if ( has_post_thumbnail($product_id) ) {
			$thumbnail_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
			$tpl_component = array();
			$tpl_component['PRODUCT_THUMBNAIL_URL'] = $thumbnail_url[0];
			$tpl_component['PRODUCT_THUMBNAIL'] = get_the_post_thumbnail( $product_id, 'wpshop-product-galery' );
			$image_attributes = wp_get_attachment_metadata( get_post_thumbnail_id() );
			foreach ( $image_attributes['sizes'] as $size_name => $size_def) {
				$tpl_component['PRODUCT_THUMBNAIL_' . strtoupper($size_name)] = wp_get_attachment_image(get_post_thumbnail_id(), $size_name);
			}
			$productThumbnail = wpshop_display::display_template_element( 'product_thumbnail', $tpl_component );
			unset($tpl_component);
		}

		/*	Get attachement file for the current product	*/
		$product_picture_galery_content = $product_document_galery_content = '';
		$picture_number = $document_number = $index_li = 0;
		$attachments = get_posts( array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $product_id) );
		if ( is_array($attachments) && (count($attachments) > 0) ) {
			$picture_increment = $document_increment = 1;
			foreach ($attachments as $attachment) {
				$tpl_component = array();
				$tpl_component['ATTACHMENT_ITEM_GUID'] = $attachment->guid;
				$tpl_component['ATTACHMENT_ITEM_TITLE'] = $attachment->post_title;
				if (is_int(strpos($attachment->post_mime_type, 'image/')) && ($attachment->ID != get_post_thumbnail_id())) {
					$tpl_component['ATTACHMENT_ITEM_TYPE'] = 'picture';
					$tpl_component['ATTACHMENT_ITEM_SPECIFIC_CLASS'] = (!($picture_increment%WPSHOP_DISPLAY_GALLERY_ELEMENT_NUMBER_PER_LINE)) ? 'wpshop_gallery_picture_last' : '';
					$tpl_component['ATTACHMENT_ITEM_PICTURE'] = wp_get_attachment_image($attachment->ID, 'full');
					$image_attributes = wp_get_attachment_metadata( $attachment->ID );
					foreach ( $image_attributes['sizes'] as $size_name => $size_def) {
						$tpl_component['ATTACHMENT_ITEM_PICTURE_' . strtoupper($size_name)] = wp_get_attachment_image($attachment->ID, $size_name);
					}

					/*
					 * Template parameters
					*/
					$template_part = 'product_attachment_item_picture';
					$tpl_component['PRODUCT_ID'] = $product_id;

					/*
					 * Build template
					*/
					$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
					if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
						/*	Include the old way template part	*/
						ob_start();
						require_once(wpshop_display::get_template_file($tpl_way_to_take[1]));
						$product_picture_galery_content .= ob_get_contents();
						ob_end_clean();
					}
					else {
						$product_picture_galery_content .= wpshop_display::display_template_element($template_part, $tpl_component);
					}

					$index_li++;
					$picture_number++;
					$picture_increment++;
				}
				if (is_int(strpos($attachment->post_mime_type, 'application/pdf'))) {
					$tpl_component['ATTACHMENT_ITEM_TYPE'] = 'document';
					$tpl_component['ATTACHMENT_ITEM_SPECIFIC_CLASS'] = (!($document_increment%WPSHOP_DISPLAY_GALLERY_ELEMENT_NUMBER_PER_LINE)) ? 'wpshop_gallery_document_last' : '';
					/*
					 * Template parameters
					*/
					$template_part = 'product_attachment_item_document';
					$tpl_component['PRODUCT_ID'] = $product_id;

					/*
					 * Build template
					*/
					$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
					if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
						/*	Include the old way template part	*/
						ob_start();
						require(wpshop_display::get_template_file($tpl_way_to_take[1]));
						$product_document_galery_content .= ob_get_contents();
						ob_end_clean();
					}
					else {
						$product_document_galery_content .= wpshop_display::display_template_element($template_part, $tpl_component);
					}

					$document_number++;
					$document_increment++;
				}
				unset($tpl_component);
			}
		}

		$product_picture_galery = ($picture_number >= 1) ? self::display_attachment_gallery( 'picture', $product_picture_galery_content ) : '';
		$product_document_galery = ($document_number >= 1) ? self::display_attachment_gallery( 'document', $product_document_galery_content) : '';

		/*	Get the different attribute affected to the product	*/
		$product_atribute_list = wpshop_attributes::getElementWithAttributeAndValue(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $product_id, get_locale(), '', 'frontend');
		if ( is_array($product_atribute_list) && (count($product_atribute_list) > 0) ) {
			foreach ( $product_atribute_list[$product_id] as $attributeSetSectionName => $attributeSetContent ) {
				$attributeToShowNumber = 0;
				$attributeOutput = '';

				foreach ( $attributeSetContent['attributes'] as $attributeId => $attributeDefinition ) {
					/*	Check the value type to check if empty or not	*/
					if ( $attributeDefinition['data_type'] == 'int' ) {
						$attributeDefinition['value'] = (int)$attributeDefinition['value'];
					}
					else if ( $attributeDefinition['data_type'] == 'decimal' ) {
						$attributeDefinition['value'] = (float)$attributeDefinition['value'];
					}

					/*
					 * Check if the attribute is set to be displayed in frontend
					 */
					$attribute_display_state = wpshop_attributes::check_attribute_display( $attributeDefinition['is_visible_in_front'], $product['custom_display'], 'attribute', $attributeDefinition['code'], 'complete_sheet');

					/*	Output the field if the value is not null	*/
					if ( (is_array($attributeDefinition['value']) OR ((trim($attributeDefinition['value']) != '') && ($attributeDefinition['value'] > '0'))) && $attribute_display_state) {
						$attribute_unit_list = '';
						if ( $attributeDefinition['unit'] != '' ) {
							/*
							 * Template parameters
							 */
							$template_part = 'product_attribute_unit';
							$tpl_component = array();
							$tpl_component['ATTRIBUTE_UNIT'] = $attributeDefinition['unit'];

							/*
							 * Build template
							 */
							$attribute_unit_list = wpshop_display::display_template_element($template_part, $tpl_component);
							unset($tpl_component);
						}
						$attribute_value = $attributeDefinition['value'];
						if ( $attributeDefinition['data_type'] == 'datetime' ) {
							$attribute_value = mysql2date('d/m/Y', $attributeDefinition['value'], true);
						}
						if ( $attributeDefinition['backend_input'] == 'select' ) {
							$attribute_value = wpshop_attributes::get_attribute_type_select_option_info($attributeDefinition['value'], 'label', $attributeDefinition['data_type_to_use']);
						}
						// Manage differently if its an array of values or not
						if ( $attributeDefinition['backend_input'] == 'multiple-select') {
							$attribute_value = '';
							if ( is_array($attributeDefinition['value']) ) {
								foreach ($attributeDefinition['value'] as $v) {
									$attribute_value .= ', '.wpshop_attributes::get_attribute_type_select_option_info($v, 'label', $attributeDefinition['data_type_to_use']);
								}
							}
							else $attribute_value = ', '.wpshop_attributes::get_attribute_type_select_option_info($attributeDefinition['value'], 'label', $attributeDefinition['data_type_to_use']);
							$attribute_value = substr($attribute_value,2);
						}

						/*
						 * Template parameters
						 */
						$template_part = 'product_attribute_display';
						$tpl_component = array();
						$tpl_component['PDT_ENTITY_CODE'] = self::currentPageCode;
						$tpl_component['ATTRIBUTE_CODE'] = $attributeDefinition['attribute_code'];
						$tpl_component['ATTRIBUTE_LABEL'] = __($attributeDefinition['frontend_label'], 'wpshop');
						$tpl_component['ATTRIBUTE_VALUE'] = stripslashes($attribute_value);
						$tpl_component['ATTRIBUTE_VALUE_UNIT'] =  $attribute_unit_list;

						/*
						 * Build template
						 */
						$attributeOutput .= wpshop_display::display_template_element($template_part, $tpl_component);
						unset($tpl_component);

						$attributeToShowNumber++;
					}
				}

				/*
				 * Check if the attribute set section is set to be displayed in frontend
				 */
				$attribute_set_display_state = wpshop_attributes::check_attribute_display( $attributeSetContent['display_on_frontend'], $product['custom_display'], 'attribute_set_section', $attributeSetContent['code'], 'complete_sheet');

				if ( !$attribute_set_display_state ) {
					$attributeToShowNumber = 0;
					$attributeOutput = '';
				}
				$product_atribute_list[$product_id][$attributeSetSectionName]['count'] = $attributeToShowNumber;
				$product_atribute_list[$product_id][$attributeSetSectionName]['output'] = $attributeOutput;
			}

			// Gestion de l'affichage
			$tab_list = $content_list = '';
			foreach ( $product_atribute_list[$product_id] as $attributeSetSectionName => $attributeSetContent ) {
				if ( !empty($attributeSetContent['count']) > 0 ) {
					/*
					 * Template parameters
					*/
					$template_part = 'product_attribute_tabs';
					$tpl_component = array();
					$tpl_component['ATTRIBUTE_SET_CODE'] = $attributeSetContent['code'];
					$tpl_component['ATTRIBUTE_SET_NAME'] = __($attributeSetSectionName, 'wpshop');

					/*
					 * Build template
					*/
					$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
					if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
						/*	Include the old way template part	*/
						ob_start();
						require(wpshop_display::get_template_file($tpl_way_to_take[1]));
						$tab_list .= ob_get_contents();
						ob_end_clean();
					}
					else {
						$tab_list .= wpshop_display::display_template_element($template_part, $tpl_component);
					}
					unset($tpl_component);

					/*
					 * Template parameters
					*/
					$template_part = 'product_attribute_tabs_detail';
					$tpl_component = array();
					$tpl_component['ATTRIBUTE_SET_CODE'] = $attributeSetContent['code'];
					$tpl_component['ATTRIBUTE_SET_CONTENT'] = $attributeSetContent['output'];

					/*
					 * Build template
					*/
					$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
					if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
						/*	Include the old way template part	*/
						ob_start();
						require(wpshop_display::get_template_file($tpl_way_to_take[1]));
						$content_list .= ob_get_contents();
						ob_end_clean();
					}
					else {
						$content_list .= wpshop_display::display_template_element($template_part, $tpl_component);
					}
					unset($tpl_component);
				}
			}

			if ( $tab_list != '' ) {
				/*
				 * Template parameters
				 */
				$template_part = 'product_attribute_container';
				$tpl_component = array();
				$tpl_component['PDT_TABS'] = $tab_list;
				$tpl_component['PDT_TAB_DETAIL'] = $content_list;

				/*
				 * Build template
				 */
				$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
				if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
					/*	Include the old way template part	*/
					ob_start();
					require(wpshop_display::get_template_file($tpl_way_to_take[1]));
					$attributeContentOutput = ob_get_contents();
					ob_end_clean();
				}
				else {
					$attributeContentOutput = wpshop_display::display_template_element($template_part, $tpl_component);
				}
				unset($tpl_component);
			}

		}

		/** Retrieve product price */
		$productPrice = self::get_product_price($product, 'price_display', 'complete_sheet');

		/** Check if there is at less 1 product in stock	*/
		$productStock = wpshop_cart::check_stock($product_id, 1);
		$productStock = $productStock===true ? 1 : null;

		/** Define "Add to cart" button	 */
		$add_to_cart_button_display_state = wpshop_attributes::check_attribute_display( ((WPSHOP_DEFINED_SHOP_TYPE == 'sale') ? 'yes' : 'no'), $product['custom_display'], 'product_action_button', 'add_to_cart', 'complete_sheet');
		$add_to_cart_button = $add_to_cart_button_display_state ? self::display_add_to_cart_button($product_id, $productStock, 'complete') : '';

		/** Define "Ask a quotation" button	*/
		$quotation_button = self::display_quotation_button($product_id, (!empty($product['quotation_allowed']) ? $product['quotation_allowed'] : null));

		/** Template parameters	*/
		$template_part = 'product_complete_tpl';
		$tpl_component = array();
		$tpl_component['PRODUCT_VARIATIONS'] = wpshop_products::wpshop_variation($product_id);
		$tpl_component['PRODUCT_ID'] = $product_id;
		$tpl_component['PRODUCT_THUMBNAIL'] = $productThumbnail;
		$tpl_component['PRODUCT_GALERY_PICS'] = $product_picture_galery;
		$tpl_component['PRODUCT_PRICE'] = $productPrice;
		$tpl_component['PRODUCT_INITIAL_CONTENT'] = $initialContent;
		$tpl_component['PRODUCT_BUTTON_ADD_TO_CART'] = $add_to_cart_button;
		$tpl_component['PRODUCT_BUTTON_QUOTATION'] = $quotation_button;
		$tpl_component['PRODUCT_BUTTONS'] = $tpl_component['PRODUCT_BUTTON_ADD_TO_CART'] . $tpl_component['PRODUCT_BUTTON_QUOTATION'];
		$tpl_component['PRODUCT_GALERY_DOCS'] = $product_document_galery;
		$tpl_component['PRODUCT_FEATURES'] = $attributeContentOutput;

		/** Build template	*/
		$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
		if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
			/*	Include the old way template part	*/
			ob_start();
			require(wpshop_display::get_template_file($tpl_way_to_take[1]));
			$content = ob_get_contents();
			ob_end_clean();
		}
		else {
			$content = wpshop_display::display_template_element($template_part, $tpl_component);
		}
		unset($tpl_component);

		return $content;
	}

	/**
	*	Display a product not a list
	*/
	function product_mini_output($product_id, $category_id, $output_type = 'list', $current_item_position = 1, $grid_element_nb_per_line = WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE) {
		$content = $product_information = $product_class = '';

		/*
		 * Get the product thumbnail
		 */
		$productThumbnail = wpshop_display::display_template_element('product_thumbnail_default', array());
		if(has_post_thumbnail($product_id)){
			$productThumbnail = get_the_post_thumbnail($product_id, 'thumbnail');
		}

		/*
		 * Get product definition
		 */
		$product = self::get_product_data($product_id);

		/*	Get the product information for output	*/
		if ( !empty($product) ) {
			$product_title = $product['post_title'];
			$product_name = $product['post_name'];
			$product_link = get_permalink($product_id);
			$product_more_informations = $product['product_content'];
			$product_excerpt = get_the_excerpt();
			if ( strpos($product['product_content'], '<!--more-->') ) {
				$post_content = explode('<!--more-->', $product['product_content']);
				$product_more_informations = $post_content[0];
			}
		}
		else {
			$productThumbnail = wpshop_display::display_template_element('product_thumbnail_default', array());
			$product_title = '<i>'.__('This product does not exist', 'wpshop').'</i>';
			$product_link = '';
			$product_more_informations = '';
			$product_excerpt = '';
		}


		/*
		 * Get product definition
		 */
		$product = self::get_product_data($product_id);

		/*
		 * Retrieve product price
		 */
		$productPrice = self::get_product_price($product, 'price_display', array('mini_output', $output_type));

		/*
		 * Check if there is at less 1 product in stock
		 */
		$productStock = wpshop_cart::check_stock($product_id, 1);
		$productStock = $productStock===true ? 1 : null;

		/*
		 * Define "Add to cart" button
		 */
		$add_to_cart_button_display_state = wpshop_attributes::check_attribute_display( ((WPSHOP_DEFINED_SHOP_TYPE == 'sale') ? 'yes' : 'no'), $product['custom_display'], 'product_action_button', 'add_to_cart', 'mini_output');
		$add_to_cart_button = $add_to_cart_button_display_state ? self::display_add_to_cart_button($product_id, $productStock, 'mini') : '';

		/*
		 * Define "Ask a quotation" button
		 */
		$quotation_button = self::display_quotation_button($product_id, (!empty($product['quotation_allowed']) ? $product['quotation_allowed'] : null));

		$product_new_def = self::display_product_special_state('declare_new', $output_type, (!empty($product['declare_new']) ? $product['declare_new'] : 'no'), (!empty($product['set_new_from']) ? $product['set_new_from'] : ''), (!empty($product['set_new_to']) ? $product['set_new_to'] : ''));
		$product_new = $product_new_def['output'];
		$product_class .= $product_new_def['class'];

		$product_featured_def = self::display_product_special_state('highlight_product', $output_type, (!empty($product['highlight_product']) ? $product['highlight_product'] : 'no'), (!empty($product['highlight_from']) ? $product['highlight_from'] : ''), (!empty($product['highlight_to']) ? $product['highlight_to'] : ''));
		$product_featured = $product_new_def['output'];
		$product_class .= $product_featured_def['class'];

		if ( !($current_item_position%$grid_element_nb_per_line) ) {
			$product_class .= ' wpshop_last_product_of_line';
		}

		/*
		 * Template parameters
		*/
		$template_part = 'product_mini_' . $output_type;
		$tpl_component = array();
		$tpl_component['PRODUCT_ID'] = $product_id;
		$tpl_component['PRODUCT_CLASS'] = $product_class;
		$tpl_component['PRODUCT_BUTTON_ADD_TO_CART'] = $add_to_cart_button;
		$tpl_component['PRODUCT_BUTTON_QUOTATION'] = $quotation_button;
		$tpl_component['PRODUCT_BUTTONS'] = $tpl_component['PRODUCT_BUTTON_ADD_TO_CART'] . $tpl_component['PRODUCT_BUTTON_QUOTATION'];
		$tpl_component['PRODUCT_PRICE'] = $productPrice;
		$tpl_component['PRODUCT_PERMALINK'] = $product_link;
		$tpl_component['PRODUCT_TITLE'] = $product_title;
		$tpl_component['PRODUCT_NAME'] = $product_name;
		$tpl_component['PRODUCT_DESCRIPTION'] = $product_more_informations;
		$tpl_component['PRODUCT_IS_NEW'] = $product_new;
		$tpl_component['PRODUCT_IS_FEATURED'] = $product_featured;
		$tpl_component['PRODUCT_EXTRA_STATE'] = $tpl_component['PRODUCT_IS_NEW'] . $tpl_component['PRODUCT_IS_FEATURED'];
		$tpl_component['PRODUCT_THUMBNAIL'] = $productThumbnail;
		$tpl_component['PRODUCT_EXCERPT'] = $product_excerpt;
		$tpl_component['PRODUCT_OUTPUT_TYPE'] = $output_type;

		/*
		 * Build template
		*/
		$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
		if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
			/*	Include the old way template part	*/
			ob_start();
			require(wpshop_display::get_template_file($tpl_way_to_take[1]));
			$content = ob_get_contents();
			ob_end_clean();
		}
		else {
			$content = wpshop_display::display_template_element($template_part, $tpl_component);
		}
		unset($tpl_component);

		return $content;
	}

	/**
	*	Get the products (post) of a given category
	*
	*	@param string $category_slug The category slug we want to get the product list for
	*
	*	@return mixed $widget_content The output for the product list
	*/
	function get_product_of_category($category_slug, $category_id){
		global $top_categories;
		$widget_content = '';

		$args = array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES => $category_slug);
		$products = get_posts($args);
		if(is_array($products) && (count($products) > 0)){
			foreach($products as $product){
				ob_start();
				require(wpshop_display::get_template_file('categories_products-widget.tpl.php'));
				$widget_content .= ob_get_contents();
				ob_end_clean();
			}
		}

		echo $widget_content;
	}

	/**
	 *
	 * @param unknown_type $selected_product
	 * @return string
	 */
	function custom_product_list($selected_product = array()){
		global $wpdb;

		/*	Start the table definition	*/
		$tableId = 'wpshop_product_list';
		$tableTitles = array();
		$tableTitles[] = '';
		$tableTitles[] = __('Id', 'wpshop');
		$tableTitles[] = __('Lastname', 'wpshop');
		$tableTitles[] = __('Firstname', 'wpshop');
		$tableTitles[] = __('Subscription date', 'wpshop');
		$tableTitles[] = __('Billing address', 'wpshop');
		$tableTitles[] = __('Shipping address', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_product_selector_column';
		$tableClasses[] = 'wpshop_product_identifier_column';
		$tableClasses[] = 'wpshop_product_quantity_column';
		$tableClasses[] = 'wpshop_product_sku_column';
		$tableClasses[] = 'wpshop_product_name_column';
		$tableClasses[] = 'wpshop_product_link_column';
		$tableClasses[] = 'wpshop_product_price_column';

		/*	Get post list	*/
		$posts = query_posts(array(
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT
		));
		if(!empty($posts)){
			$current_line_index = 0;
			foreach($posts as $post){
				$tableRowsId[$current_line_index] = 'product_' . $post->ID;

				$post_info = get_post_meta($post->ID, '_wpshop_product_metadata', true);

				unset($tableRowValue);
				$tableRowValue[] = array('class' => 'wpshop_product_selector_cell', 'value' => '<input type="checkbox" name="wp_list_product[]" value="' . $post->ID . '" class="wpshop_product_cb_dialog" id="wpshop_product_cb_dialog_' . $post->ID . '" />');
				$tableRowValue[] = array('class' => 'wpshop_product_identifier_cell', 'value' => '<label for="wpshop_product_cb_dialog_' . $post->ID . '" >' . WPSHOP_IDENTIFIER_PRODUCT . $post->ID . '</label>');
				$tableRowValue[] = array('class' => 'wpshop_product_quantity_cell', 'value' => '<a href="#" class="order_product_action_button qty_change">-</a><input type="text" name="wpshop_pdt_qty[' . $post->ID  . ']" value="1" class="wpshop_order_product_qty" /><a href="#" class="order_product_action_button qty_change">+</a>');
				$tableRowValue[] = array('class' => 'wpshop_product_sku_cell', 'value' => $post_info['product_reference']);
				$tableRowValue[] = array('class' => 'wpshop_product_name_cell', 'value' => $post->post_title);
				$tableRowValue[] = array('class' => 'wpshop_product_link_cell', 'value' => '<a href="' . $post->guid . '" target="wpshop_product_view_product" target="wpshop_view_product" >' . __('View product', 'wpshop') . '</a><br/>
		<a href="' . admin_url('post.php?post=' . $post->ID  . '&action=edit') . '" target="wpshop_edit_product" >' . __('Edit product', 'wpshop') . '</a>');
				$tableRowValue[] = array('class' => 'wpshop_product_price_cell', 'value' => __('Price ET', 'wpshop') . '&nbsp;:&nbsp;' . $post_info[WPSHOP_PRODUCT_PRICE_HT] . '&nbsp;' . wpshop_tools::wpshop_get_currency() . '<br/>' . __('Price ATI', 'wpshop') . '&nbsp;:&nbsp;' . $post_info[WPSHOP_PRODUCT_PRICE_TTC] . '&nbsp;' . wpshop_tools::wpshop_get_currency());
				$tableRows[] = $tableRowValue;

				$current_line_index++;
			}
			wp_reset_query();
		}
		else{
			$tableRowsId[] = 'no_product_found';
			unset($tableRowValue);
			$tableRowValue[] = array('class' => 'wpshop_product_selector_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_identifier_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_quantity_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_sku_cell', 'value' => __('No element to ouput here', 'wpshop'));
			$tableRowValue[] = array('class' => 'wpshop_product_name_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_link_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_price_cell', 'value' => '');
			$tableRows[] = $tableRowValue;
		}

		return wpshop_display::getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, '', false) . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $tableId . '").dataTable({
			"bLengthChange": false,
			"bSort": false,
			"bInfo": false
		});
	});
</script>';
	}

	/**
	 * Allows to manage output for special state for a product (New product/highlight product)
	 *
	 * @param string $special The type of special type we want to output
	 * @param string $output_type The current display type (used for product listing)
	 * @param string $special_state_def The value allowing to test if we have to display a special state for the product
	 * @param datetime $special_state_start The start date if applicable for the special state
	 * @param datetime $special_state_end The end date if applicable for the special state
	 *
	 * @return array $product_special_state The product special state
	 */
	function display_product_special_state($special, $output_type, $special_state_def, $special_state_start, $special_state_end) {
		$product_special_state = array();
		$product_special_state['output'] = $product_special_state['class'] = '';

		/*
		 * Get product special state definition
		*/
		$special_state_def = !empty($special_state_def) ? $special_state_def : 'No';
		$special_state_start = !empty($special_state_start) ? substr($special_state_start, 0, 10) : null;
		$special_state_end = !empty($special_state_end) ? substr($special_state_end, 0, 10) : null;

		/*
		 * Get current time
		*/
		$current_time = substr(current_time('mysql', 0), 0, 10);

		/** PRODUCT MARK AS NEW */
		$show_product_special_state = false;
		if ( (strtolower($special_state_def) === strtolower(__('Yes', 'wpshop')) ) &&
				(empty($special_state_start) || ($special_state_start == '0000-00-00') || ($special_state_start >= $current_time)) &&
				(empty($special_state_end) || ($special_state_end == '0000-00-00') || ($special_state_end <= $current_time)) ) {
			$show_product_special_state = true;
		}

		if ( $show_product_special_state ) {
			/*
			 * Check the type of special output needed
			 */
			switch ( $special ) {
				case 'declare_new':
					$product_special_state['class'] = ' wpshop_product_is_new_' . $output_type;
					$template_part = 'product_is_new_sticker';
				break;

				case 'highlight_product':
					$product_special_state['class'] = ' wpshop_product_featured_' . $output_type;
					$template_part = 'product_is_featured_sticker';
				break;
			}

			/*
			 * Template parameters
			*/
			$tpl_component = array();

			/*
			 * Build template
			*/
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$product_special_state['output'] = ob_get_contents();
				ob_end_clean();
			}
			else {
				$product_special_state['output'] = wpshop_display::display_template_element($template_part, $tpl_component);
			}
			unset($tpl_component);
		}

		return $product_special_state;
	}

	/**
	 * Prepare product price for saving and easier read later
	 *
	 * @param integer $element_id Identifier of current product
	 */
	function calculate_price( $element_id ) {
		global $wpdb;

		$query = $wpdb->prepare(
				"SELECT ATTR_VAL.value, ATTR_VAL.attribute_id, ATTR.code
			FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR
				INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATTR_VAL ON ((ATTR_VAL.attribute_id = ATTR.id) AND (ATTR_VAL.entity_id = %d))
			WHERE ATTR.code IN ('" . implode("', '",  unserialize(WPSHOP_ATTRIBUTE_PRICES)) . "')
			UNION
			SELECT ATTR_OPT_VAL.value, ATTR_VAL.attribute_id, ATTR.code
			FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR
				INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATTR_VAL ON ((ATTR_VAL.attribute_id = ATTR.id) AND (ATTR_VAL.entity_id = %d))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " AS ATTR_OPT_VAL ON (ATTR_OPT_VAL.id = ATTR_VAL.value)
			WHERE ATTR.code IN ('" . implode("', '",  unserialize(WPSHOP_ATTRIBUTE_PRICES)) . "')",
				$element_id, $element_id
		);
		$element_prices = $wpdb->get_results($query);

		/*	Order results	*/
		$prices_attribute = array();
		foreach ( $element_prices as $element_price) {
			$prices_attribute[$element_price->code] = $element_price;
		}

		if ( !empty($prices_attribute) ) {
			/*	Get basic amount	*/
			$base_amount = $prices_attribute[constant('WPSHOP_PRODUCT_PRICE_' . WPSHOP_PRODUCT_PRICE_PILOT)]->value;
			if ( !empty($prices_attribute[WPSHOP_PRODUCT_SPECIAL_PRICE]->value) ) {
				$base_amount = $prices_attribute[WPSHOP_PRODUCT_SPECIAL_PRICE]->value;
			}

			/*	Get VAT rate	*/
			if ( !empty($prices_attribute[WPSHOP_PRODUCT_PRICE_TAX]) ) {
				$tax_rate = 1 + ($prices_attribute[WPSHOP_PRODUCT_PRICE_TAX]->value / 100);
			}
			else {
				$query = $wpdb->prepare("SELECT default_value FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", WPSHOP_PRODUCT_PRICE_TAX);
				$tax_rate = $wpdb->get_var($query);
			}

			/*	Check configuration to know how to make the calcul for the product	*/
			if ( WPSHOP_PRODUCT_PRICE_PILOT == 'HT' ) {
				$all_vat_include_price = $base_amount * $tax_rate;
				$exclude_vat_price = $base_amount;
			}
			if ( WPSHOP_PRODUCT_PRICE_PILOT == 'TTC' ) {
				$all_vat_include_price = $base_amount;
				$exclude_vat_price = $all_vat_include_price / $tax_rate;
			}
			$vat_amount = $all_vat_include_price - $exclude_vat_price;
			if ( empty($prices_attribute[WPSHOP_PRODUCT_SPECIAL_PRICE]->value) ) {
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $all_vat_include_price), array('entity_id' => $element_id, 'attribute_id' => $prices_attribute[WPSHOP_PRODUCT_PRICE_TTC]->attribute_id));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $exclude_vat_price), array('entity_id' => $element_id, 'attribute_id' => $prices_attribute[WPSHOP_PRODUCT_PRICE_HT]->attribute_id));
			}
			$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $vat_amount), array('entity_id' => $element_id, 'attribute_id' => $prices_attribute[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT]->attribute_id));
		}
	}

	/**
	 * Allows to get the correct price for a product
	 *
	 * @param object $product An object with the product definition
	 * @param string $return_type The type the price have to be returned under
	 * @param string $output_type The current output type (mini | complete)
	 *
	 * @return boolean|string Boolean: If the product price is set for cart adding | String: An error message if the price is not well set OR The product price
	 */
	function get_product_price($product, $return_type, $output_type = '', $only_price = false) {
		global  $wpdb;
		$productCurrency = wpshop_tools::wpshop_get_currency();

		if ( $return_type == 'check_only' ) {
			/*
			 * Check if the product price has been set
			 */
			if(isset($product[WPSHOP_PRODUCT_PRICE_TTC]) && $product[WPSHOP_PRODUCT_PRICE_TTC] === '') return __('This product cannot be purchased - the price is not yet announced', 'wpshop');
			/*
			 * Check if the product price is coherent (not less than 0)
			 */
			if(isset($product[WPSHOP_PRODUCT_PRICE_TTC]) && $product[WPSHOP_PRODUCT_PRICE_TTC] < 0) return __('This product cannot be purchased - its price is negative', 'wpshop');

			return true;
		}
		else if ( $return_type == 'price_display' ) {
			$the_price = $product[WPSHOP_PRODUCT_PRICE_TTC];

			$display_type = $output_type;
			if ( !empty($output_type) && is_array($output_type) ) {
				$display_type = $output_type[0];
				$display_sub_type = $output_type[1];
			}

			/*
			 * Get the definition for attribute price: allows to define if the price have to displayed or not
			 */
			$price_attribute = wpshop_attributes::getElement(WPSHOP_PRODUCT_PRICE_TTC, "'valid'", 'code');

			/*
			 * Check price configuration for output
			 */
			$price_display = wpshop_attributes::check_attribute_display( (($display_type == 'mini_output' ) ? $price_attribute->is_visible_in_front_listing : $price_attribute->is_visible_in_front), $product['custom_display'], 'attribute', WPSHOP_PRODUCT_PRICE_TTC, $display_type);

			/*
			 * Check the current output type and the price attribute configuration for knowing the output to take
			 */
			if ( !$price_display ) {
				$price_display = '';
			}
			else {
				$price = !empty( $the_price ) ? wpshop_display::format_field_output('wpshop_product_price', $the_price) . ' ' . $productCurrency : __('Unknown price','wpshop');

				/** Template parameters	*/
				$template_part = 'product_price_template_' . $display_type;
				$tpl_component = array();
				$tpl_component['PRODUCT_PRICE'] = $price;
				$tpl_component['PRODUCT_ORIGINAL_PRICE'] = ($price != __('Unknown price','wpshop')) ? $price : '';

				/** For each attribute in price set section: create an element for display	*/
				$atribute_list = wpshop_attributes::get_attribute_list_in_same_set_section( WPSHOP_PRODUCT_PRICE_TTC );
				if ( !empty($atribute_list) && is_array($atribute_list) ) {
					foreach ( $atribute_list as $attribute) {
						if ( !empty($product[$attribute->code]) && wpshop_attributes::check_attribute_display( (($display_type == 'mini_output' ) ? $attribute->is_visible_in_front_listing : $attribute->is_visible_in_front), $product['custom_display'], 'attribute', $attribute->code, $display_type) ) {
							$tpl_component['PRODUCT_PRICES_' . strtoupper($attribute->code)] = wpshop_display::format_field_output('wpshop_product_price', $product[$attribute->code]) . ' ' . $productCurrency;
						}
						else {
							$tpl_component['PRODUCT_PRICES_' . strtoupper($attribute->code)] = '';
						}
					}
				}

				/**	Check if there are variaiton for current product	*/
				$current_product_variation = wpshop_products::get_variation( $product['product_id'] );
				if ( !empty($current_product_variation) ) {
					$head_wpshop_variation_definition = get_post_meta( $product['product_id'], '_wpshop_variation_defining', true );
					/** Check if the price to display must be the lowest price of variation */
					if ( !empty($head_wpshop_variation_definition['options']['price_display']) && !empty($head_wpshop_variation_definition['options']['price_display']['lower_price']) && ($head_wpshop_variation_definition['options']['price_display']['lower_price'] == 'on') ) {
						$lower_price = 0;
						$price_index = constant('WPSHOP_PRODUCT_PRICE_' . WPSHOP_PRODUCT_PRICE_PILOT);
						foreach ($current_product_variation as $variation_id => $variation_definition) {
							if ( !empty($variation_definition['variation_dif']) && !empty($variation_definition['variation_dif'][$price_index]) ) {
								if ( $variation_definition['variation_dif'][$price_index] < $lower_price ) {
									$lower_price = $variation_definition['variation_dif'][$price_index];
								}
							}
							if ( !empty($variation_definition['variation_dif']) ) {
								foreach ($variation_definition['variation_dif'] as $attribute_code => $attribute_value_for_variation) {
									$attribute = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
									if ( !empty($attribute_value_for_variation) && wpshop_attributes::check_attribute_display( (($display_type == 'mini_output' ) ? $attribute->is_visible_in_front_listing : $attribute->is_visible_in_front), $product['custom_display'], 'attribute', $attribute_code, $display_type) ) {
										$tpl_component['PRODUCT_PRICES_' . strtoupper($attribute_code)] = wpshop_display::format_field_output('wpshop_product_price', $attribute_value_for_variation) . ' ' . $productCurrency;
									}
									else {
										$tpl_component['PRODUCT_PRICES_' . strtoupper($attribute_code)] = '';
									}
								}
							}
						}
						$tpl_component['PRODUCT_PRICE'] = !empty( $lower_price ) ? wpshop_display::format_field_output('wpshop_product_price', $lower_price) . ' ' . $productCurrency : $price;
					}

					/**	Check if the text price from must be displayed before price	*/
					if ( !empty($head_wpshop_variation_definition['options']['price_display']) && ($head_wpshop_variation_definition['options']['price_display']['text_from'] == 'on') ) {
						$tpl_component['PRODUCT_PRICE'] = __('Price from', 'wpshop') . ' ' . $tpl_component['PRODUCT_PRICE'];
					}
				}

				$price_display = wpshop_display::display_template_element($template_part, $tpl_component);

				unset($tpl_component);

				/** Build template	*/
				if ( $only_price ) {
					$price_display = $price;
				}
				else {
					$tpl_to_check = ($display_type == 'complete_sheet') ? 'product_complete_tpl' : 'product_mini_' . $display_sub_type;
					$tpl_way_to_take = wpshop_display::check_way_for_template($tpl_to_check);
					if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
						$price_display = $price;
					}
					else if ( is_file(get_stylesheet_directory() . '/wpshop/wpshop_elements_template.tpl.php') ) {
						$file_path = get_stylesheet_directory() . '/wpshop/wpshop_elements_template.tpl.php';

						require($file_path);
						if ( !empty($tpl_element) && !empty($tpl_element[$tpl_to_check]) ) {
							$price_display = $price;
						}
					}
				}
			}

			return $price_display;
		}

		return false;
	}

	/**
	 * Allows to get the good button for adding product to cart
	 *
	 * @param integer $product_id The product identifier
	 * @param boolean $productStock If there is the possibility to add the given product to the cart
	 *
	 * @return string $button The html output for the button
	 */
	function display_add_to_cart_button($product_id, $productStock, $output_type = 'mini') {
		$button = '';
		if ( WPSHOP_DEFINED_SHOP_TYPE == 'sale' ) {
			/*
			 * Check if current product has variation for button display
			 */
			$variations_list = array_merge( wpshop_products::get_variation( $product_id ), wpshop_attributes::get_attribute_user_defined( array('entity_type_id' => self::currentPageCode) ) );

			/*
			 * Template parameters
			 */
			$template_part = (!empty($variations_list) && ($output_type == 'mini')) ? 'configure_product_button' : (!empty($productStock) ? 'add_to_cart_button' : 'unavailable_product_button');
			$tpl_component = array();
			$tpl_component['PRODUCT_ID'] = $product_id;
			$tpl_component['PRODUCT_PERMALINK'] = get_permalink($product_id);
			$tpl_component['PRODUCT_TITLE'] = get_the_title($product_id);

			/*
			 * Build template
			 */
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$button = ob_get_contents();
				ob_end_clean();
			}
			else {
				$button = wpshop_display::display_template_element($template_part, $tpl_component, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $product_id, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . 'output_type' => $output_type));
			}
			unset($tpl_component);
		}

		return $button;
	}

	/**
	 * Allows to get the good button for adding product to a quotation
	 *
	 * @param integer $product_id The product identifier
	 * @param boolean $product_quotation_state The state of the quotation addons
	 *
	 * @return string $button The html output for the button
	 */
	function display_quotation_button($product_id, $product_quotation_state) {
		$quotation_button = '';

		if ( (!empty($product_quotation_state) && $product_quotation_state==strtolower(__('yes', 'wpshop'))) && (empty($_SESSION['cart']['cart_type']) || ($_SESSION['cart']['cart_type'] == 'quotation')) ) {
			/*
			 * Template parameters
			*/
			$template_part = 'ask_quotation_button';
			$tpl_component = array();
			$tpl_component['PRODUCT_ID'] = $product_id;

			/*
			 * Build template
			*/
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$quotation_button = ob_get_contents();
				ob_end_clean();
			}
			else {
				$quotation_button = wpshop_display::display_template_element($template_part, $tpl_component);
			}
			unset($tpl_component);
		}

		return $quotation_button;
	}

	/**
	 * Return the output for a product attachement gallery (picture or document)
	 *
	 * @param string $attachement_type The type of attachement to output. allows to define with type of template to take
	 * @param string $content The gallery content build previously
	 *
	 * @return string The attachement gallery output
	 */
	function display_attachment_gallery( $attachement_type, $content ) {
		$galery_output = '';

		/*
		 * Get the template part for given galery type
		 */
		switch ( $attachement_type ) {
			case 'picture':
					$template_part = 'product_attachment_picture_galery';
				break;
			case 'document':
					$template_part = 'product_attachment_galery';
				break;
		}

		/*
		 * Template parameters
		 */
		$tpl_component = array();
		$tpl_component['PRODUCT_ATTACHMENT_OUTPUT_CONTENT'] = $content;
		$tpl_component['ATTACHMENT_ITEM_TYPE'] = $attachement_type;

		/*
		 * Build template
		 */
		$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
		if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
			/*	Include the old way template part	*/
			ob_start();
			require(wpshop_display::get_template_file($tpl_way_to_take[1]));
			$galery_output = ob_get_contents();
			ob_end_clean();
		}
		else {
			$galery_output = wpshop_display::display_template_element($template_part, $tpl_component);
		}
		unset($tpl_component);

		return $galery_output;
	}


	/**
	 * Define the metabox to display in product edition page in backend
	 * @param object $post The current element displayed for edition
	 */
	function meta_box_variations( $post ) {
		$output = '';

		/*	Variations container	*/
		$tpl_component = array();
		$tpl_component['ADMIN_VARIATION_CONTAINER'] = self::display_variation_admin( $post->ID );
		$output .= wpshop_display::display_template_element('wpshop_admin_variation_metabox', $tpl_component, array(), 'admin');

		echo '<span class="wpshop_loading_ wpshopHide" ><img src="' . admin_url('images/loading.gif') . '" alt="loading picture" /></span>' . $output . '<div class="clear" ></div>';
	}

	function creation_variation_callback( $possible_variations, $element_id ) {
		/*
		 * Get existing variation
		 */
		$existing_variations_in_db = wpshop_products::get_variation( $element_id );
		$existing_variations = array();
		if ( !empty($existing_variations_in_db) ) {
			foreach ( $existing_variations_in_db as $variations_def) {
				$existing_variations[] = $variations_def['variation_def'];
			}
		}

		/*
		 * New variation definition
		 */
		$attribute_defining_variation = get_post_meta($element_id, '_wpshop_variation_defining', true);
		foreach ( $possible_variations as $varation_definition ) {
			if ( in_array($varation_definition, $existing_variations) ) {
				continue;
			}

			$attribute_to_set = array();
			foreach ( $varation_definition as $attribute_code => $attribute_selected_value ) {
				$attribute = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
				$attribute_to_set[$attribute->data_type][$attribute_code] = $attribute_selected_value;
				if ( empty($attribute_defining_variation['attributes']) || (!in_array($attribute_code, $attribute_defining_variation['attributes'])) ) {
					$attribute_defining_variation['attributes'][] = $attribute_code;
				}
			}
			$variation_id = wpshop_products::create_variation($element_id, $attribute_to_set);
		}
		update_post_meta($element_id, '_wpshop_variation_defining', $attribute_defining_variation );

		return $variation_id;
	}

	/**
	 * Create a new variation for product
	 *
	 * @param integer $head_product The product identifier to create the new variation for
	 * @param array $variation_attributes Attribute list for the variation
	 * @return mixed <number, WP_Error> The variation identifier or an error in case the creation was not succesfull
	 */
	function create_variation( $head_product, $variation_attributes ) {
		$variation = array(
			'post_title' => sprintf(__('Product %s variation %s', 'wpshop'), $head_product, get_the_title( $head_product )),
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'post_parent' => $head_product,
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION
		);
		$variation_id = wp_insert_post( $variation );

		wpshop_attributes::saveAttributeForEntity($variation_attributes, wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $variation_id, get_locale(), '');

		/*	Update product price looking for shop parameters	*/
		wpshop_products::calculate_price( $variation_id );

		/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
		$productMetaDatas = array();
		foreach ( $variation_attributes as $attributeType => $attributeValues ) {
			foreach ( $attributeValues as $attributeCode => $attributeValue ) {
				if ( !empty($attributeValue) ) {
					$productMetaDatas[$attributeCode] = $attributeValue;
				}
			}
		}
		update_post_meta($variation_id, '_wpshop_variations_attribute_def', $productMetaDatas);
		update_post_meta($variation_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);
		update_post_meta($variation_id, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, get_post_meta($head_product, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true));

		return $variation_id;
	}

	/**
	 * Get variation list for a given product
	 *
	 * @param integer $head_product The product identifier to get the variation for
	 * @return object The variation list
	 */
	function get_variation( $head_product ) {
		$variations_output = array();
		$variations = query_posts(array(
			'post_type' 	=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION,
			'post_parent' 	=> $head_product,
			'orderby' 		=> 'ID',
			'order' 		=> 'ASC',
			'posts_per_page'=> -1
		));

		if ( !empty( $variations ) ) {
			$head_wpshop_variation_definition = get_post_meta( $head_product, '_wpshop_variation_defining', true );

			foreach ( $variations as $post_def ) {
				$data = wpshop_attributes::get_attribute_list_for_item( wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $post_def->ID, WPSHOP_CURRENT_LOCALE, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
				foreach ( $data as $content ) {
					$attribute_value = 'attribute_value_' . $content->data_type;
					if ( !empty($head_wpshop_variation_definition['attributes']) && in_array($content->code, $head_wpshop_variation_definition['attributes']) ) {
						$variations_output[$post_def->ID]['variation_def'][$content->code] = $content->$attribute_value;
					}
					else if ( !empty($content->$attribute_value) ) {
						$variations_output[$post_def->ID]['variation_dif'][$content->code] = $content->$attribute_value;
					}
				}
				$variations_output[$post_def->ID]['post'] = $post_def;
			}
		}

		return $variations_output;
	}

	/**
	 * Affichage des variations d'un produit dans l'administration
	 *
	 * @param integer $head_product L'identifiant du produit dont on veut afficher les variations
	 * @return string Le code html permettant l'affichage des variations dans l'interface d'édition du produit
	 */
	function display_variation_admin( $head_product ) {
		$output = '';

		/*	Récupération de la liste des variations pour le produit en cours d'édition	*/
		$variations = self::get_variation( $head_product );

		/*	Affichage de la liste des variations pour le produit en cours d'édition	*/
		if ( !empty($variations) && is_array($variations) ) {
			$existing_variation_list = wpshop_display::display_template_element('wpshop_admin_existing_variation_controller', array(), array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $head_product), 'admin');

			foreach ( $variations as $variation ) {
				$tpl_component = array();
				$tpl_component['ADMIN_EXISTING_VARIATIONS_CLASS'] = ' wpshop_variation_' . self::currentPageCode;
				$tpl_component['VARIATION_IDENTIFIER'] = $variation['post']->ID;
				$tpl_component['VARIATION_DETAIL'] = '  ';
				if ( !empty($variation['variation_def']) ) {
					foreach ( $variation['variation_def'] as $variation_key => $variation_value ) {
						if ( !empty($variation_value) ) {
							$attribute_def_for_variation = wpshop_attributes::getElement($variation_key, "'valid'", 'code');
							$tpl_component['VARIATION_DETAIL'] .= '<input type="hidden" name="' . self::current_page_variation_code . '[' . $variation['post']->ID . '][attribute][' . $attribute_def_for_variation->data_type . '][' . $variation_key . ']" value="' . $variation_value . '" />' . wpshop_display::display_template_element('wpshop_admin_variation_item_def_header', array('VARIATION_ATTRIBUTE_CODE' => $attribute_def_for_variation->frontend_label, 'VARIATION_ATTRIBUTE_CODE_VALUE' => stripslashes(wpshop_attributes::get_attribute_type_select_option_info($variation_value, 'label', $attribute_def_for_variation->data_type_to_use, true))), array(), 'admin');
						}
					}
				}
				$tpl_component['VARIATION_DETAIL'] = substr($tpl_component['VARIATION_DETAIL'], 0, -2);

				$tpl_component['ADMIN_VARIATION_SPECIFIC_DEFINITION_CONTAINER_CLASS'] = ' wpshopHide';
				$tpl_component['VARIATION_DEFINITION'] = wpshop_attributes::get_variation_attribute( array('input_class' => ' ', 'field_name' => wpshop_products::current_page_variation_code . '[' . $variation['post']->ID . ']','page_code' => self::current_page_variation_code, 'field_id' => self::current_page_variation_code . '_' . $variation['post']->ID, 'variation_dif_values' => (!empty($variation['variation_dif']) ? $variation['variation_dif'] : array())) );
				$tpl_component['VARIATION_DEFINITION_CONTENT'] = wpshop_display::display_template_element('wpshop_admin_variation_item_specific_def', $tpl_component, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $head_product, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION => $variation['post']->ID), 'admin');

				/*	Add the variation definition to output	*/
				$existing_variation_list .= wpshop_display::display_template_element('wpshop_admin_variation_item_def', $tpl_component, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $head_product, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION => $variation['post']->ID), 'admin');
			}

			$output .= wpshop_display::display_template_element('wpshop_admin_existing_variation_list', array('ADMIN_EXISTING_VARIATIONS_CONTAINER_CLASS' => '', 'ADMIN_EXISTING_VARIATIONS_CONTAINER' => $existing_variation_list), array(), 'admin');
			/*	Reset de la liste des résultats pour éviter les comportements indésirables	*/
			wp_reset_query();
		}
		else {
			$output = __('No variation found for this product. Please use button above for create one', 'wpshop');
		}

		return $output;
	}

	/**
	 * Retrieve and display the variation for a given product
	 * @param integer $product_id The product identifier to get variation for
	 */
	function wpshop_variation( $post_id = '' ) {
		global $wp_query;
		$output = '';

		$product_id = empty($post_id) ? $wp_query->post->ID : $post_id ;
		$wpshop_product_attributes_frontend_display = get_post_meta( $product_id, '_wpshop_product_attributes_frontend_display', true );
		$head_wpshop_variation_definition = get_post_meta( $product_id, '_wpshop_variation_defining', true );
		$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($product_id, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
		$output_order = array();

		if ( count($product_attribute_order_detail) > 0 ) {
			foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
				foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
					if ( !empty($attribute_def->code) )
						$output_order[$attribute_def->code] = $position;
				}
			}
		}

		/*	Vérification de l'existence de déclinaison pour le produit	*/
		$variations_params = array();
		$wpshop_variation_list = self::get_variation( $product_id );
		$variation_attribute = array();
		$variation_attribute_ordered = array();
		if ( !empty($wpshop_variation_list) ) {
			$possible_values = array();
			foreach ($wpshop_variation_list as $variation) {
				if (!empty($variation['variation_def']) ) {
					foreach ( $variation['variation_def'] as $attribute_code => $attribute_value ) {
						$attribute_db_definition = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
						$possible_values[$attribute_code][0] = __('Choose a value', 'wpshop');
						$tpl_component = array();
						if ( !empty($attribute_value) && ($attribute_db_definition->data_type_to_use == 'custom')) {
							$tpl_component['VARIATION_VALUE'] = stripslashes(wpshop_attributes::get_attribute_type_select_option_info($attribute_value, 'label', 'custom'));
						}
						else if ( !empty($attribute_value) && ($attribute_db_definition->data_type_to_use == 'internal')) {
							$post_def = get_post($attribute_value);
							$tpl_component['VARIATION_VALUE'] = stripslashes($post_def->post_title);
						}

						if ( !empty($variation['variation_dif']) ) {
							foreach ( $variation['variation_dif'] as $attribute_dif_code => $attribute_dif_value) {
								$wpshop_prices_attributes = unserialize(WPSHOP_ATTRIBUTE_PRICES);
								$the_value = $attribute_dif_value;
								if ( in_array($attribute_dif_code, $wpshop_prices_attributes) ) {
									$the_value = wpshop_display::format_field_output('wpshop_product_price', $attribute_dif_value);
								}
								$tpl_component['VARIATION_DIF_' . strtoupper($attribute_dif_code)] = stripslashes($the_value);
							}
						}
						if ( !empty($attribute_value) ) {
							$possible_values[$attribute_code][$attribute_value] = wpshop_display::display_template_element('product_variation_item_possible_values', $tpl_component);
						}
						unset($tpl_component);
					}
				}
			}

			$variation_tpl = array();
			if ( !empty($head_wpshop_variation_definition['attributes']) ) {
				foreach ( $head_wpshop_variation_definition['attributes'] as $attribute_code ) {
					$attribute_db_definition = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
					$attribute_display_state = wpshop_attributes::check_attribute_display( $attribute_db_definition->is_visible_in_front, $wpshop_product_attributes_frontend_display, 'attribute', $attribute_code, 'complete_sheet');
					if ( $attribute_display_state ) {
						$is_required = ( (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['required_attributes']) && ( in_array( $attribute_code, $head_wpshop_variation_definition['options']['required_attributes']) )) ) ? true : false;
						$input_def = array();
						$input_def['type'] = $attribute_db_definition->frontend_input;
						$value = isset($head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code]) ? $head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code] : (!empty($attribute_db_definition->default_value) ? $attribute_db_definition->default_value : null);
						if ( in_array($attribute_db_definition->frontend_input, array('radio', 'checkbox')) ) {
							unset($possible_values[$attribute_code][0]);
							$value = array($value);
						}
						$input_def['id'] = 'wpshop_variation_attr_' . $attribute_code;
						$input_def['name'] = $attribute_code;
						$input_def['possible_value'] = $possible_values[$attribute_code];
						$input_def['valueToPut'] = 'index';
						$input_def['value'] = $value;

						$input_def['options']['label']['original'] = true;
						$input_def['option'] = ' class="wpshop_variation_selector_input' . ($is_required ? ' attribute_is_required_input attribute_is_required_input_' . $attribute_code . ' ' : '') . ( $attribute_db_definition->_display_informations_about_value == 'yes' ? ' wpshop_display_information_about_value' : '' ) . ' ' . (( is_admin() ) ? $attribute_db_definition->backend_css_class : $attribute_db_definition->frontend_css_class) . '" ';

						$tpl_component = array();
						$attribute_output_def['value'] = isset($head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code]) ? $head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code] : $attribute_output_def['value'];
						$tpl_component['VARIATION_INPUT'] = wpshop_form::check_input_type($input_def, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION);
						$tpl_component['VARIATION_LABEL'] = ($is_required ? '<span class="attribute_is_required attribute_is_required_' . $attribute_code . '" >' . $attribute_db_definition->frontend_label . '</span> <span class="required" >*</span>' : $attribute_db_definition->frontend_label);
						$tpl_component['VARIATION_CODE'] = $attribute_code;
						$tpl_component['VARIATION_LABEL_HELPER'] = !empty($attribute_db_definition->frontend_help_message) ? ' title="' . $attribute_db_definition->frontend_help_message . '" ' : '';
						$tpl_component['VARIATION_IDENTIFIER'] = $input_def['id'];
						$tpl_component['VARIATION_PARENT_ID'] = $product_id;
						$tpl_component['VARIATION_PARENT_TYPE'] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
						$tpl_component['VARIATION_CONTAINER_CLASS'] = ($is_required ? ' attribute_is_required_container attribute_is_required_container_' . $attribute_code : '') . ' wpshop_variation_' . $attribute_code . ' wpshop_variation_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . ' wpshop_variation_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_' . $product_id;

						$variation_tpl['VARIATION_COMPLETE_OUTPUT_' . strtoupper($attribute_code)] = wpshop_display::display_template_element('product_variation_item', $tpl_component);
						$variation_attribute_ordered[$output_order[$attribute_code]] = $variation_tpl['VARIATION_COMPLETE_OUTPUT_' . strtoupper($attribute_code)];
					}
					$variation_attribute[] = $attribute_code;
				}
			}

		}
		$variation_tpl['VARIATION_FORM_ELEMENT_ID'] = $product_id;
		wp_reset_query();

		$attribute_defined_to_be_user_defined = wpshop_attributes::get_attribute_user_defined( array('entity_type_id' => self::currentPageCode) );
		if ( !empty($attribute_defined_to_be_user_defined) ) {
			foreach ( $attribute_defined_to_be_user_defined as $attribute_not_in_variation_but_user_defined ) {
				$is_required = ( (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['required_attributes']) && ( in_array( $attribute_not_in_variation_but_user_defined->code, $head_wpshop_variation_definition['options']['required_attributes']) )) ) ? true : false;
				$attribute_display_state = wpshop_attributes::check_attribute_display( $attribute_not_in_variation_but_user_defined->is_visible_in_front, $wpshop_product_attributes_frontend_display, 'attribute', $attribute_not_in_variation_but_user_defined->code, 'complete_sheet');
				if ( $attribute_display_state && !in_array($attribute_not_in_variation_but_user_defined->code, $variation_attribute) && ($attribute_not_in_variation_but_user_defined->is_used_for_variation == 'no') ) {
					$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute_not_in_variation_but_user_defined, (is_array($head_wpshop_variation_definition) && isset($head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_not_in_variation_but_user_defined->code]) ? $head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_not_in_variation_but_user_defined->code] : null ));

					$tpl_component = array();
					$attribute_output_def['option'] = ' class="wpshop_variation_selector_input' . ($is_required ? ' attribute_is_required_input attribute_is_required_input_' . $attribute_not_in_variation_but_user_defined->code : '') . ' ' . ( str_replace('"', '', str_replace('class="', '', $attribute_output_def['option'])) ) . ' ' . (( is_admin() ) ? $attribute_not_in_variation_but_user_defined->backend_css_class : $attribute_not_in_variation_but_user_defined->frontend_css_class) . '" ';
					$tpl_component['VARIATION_INPUT'] = wpshop_form::check_input_type($attribute_output_def, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION . '[free]') . $attribute_output_def['options'];
					$tpl_component['VARIATION_LABEL'] = ($is_required ? '<span class="attribute_is_required attribute_is_required_' . $attribute_not_in_variation_but_user_defined->code . '" >' . $attribute_not_in_variation_but_user_defined->frontend_label . '</span> <span class="required" >*</span>' : $attribute_not_in_variation_but_user_defined->frontend_label);
					$tpl_component['VARIATION_CODE'] = $attribute_not_in_variation_but_user_defined->code;
					$tpl_component['VARIATION_LABEL_HELPER'] = $attribute_output_def['title'];
					$tpl_component['VARIATION_IDENTIFIER'] = $attribute_output_def['id'];
					$tpl_component['VARIATION_PARENT_ID'] = $product_id;
					$tpl_component['VARIATION_PARENT_TYPE'] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
					$tpl_component['VARIATION_CONTAINER_CLASS'] = ($is_required ? ' attribute_is_required_container attribute_is_required_container_' . $attribute_not_in_variation_but_user_defined->code : '') . ' wpshop_variation_' . $attribute_not_in_variation_but_user_defined->code . ' wpshop_variation_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . ' wpshop_variation_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_' . $product_id;

					$variation_tpl['VARIATION_COMPLETE_OUTPUT_' . strtoupper($attribute_not_in_variation_but_user_defined->code)] = wpshop_display::display_template_element('product_variation_item', $tpl_component);
					$variation_attribute_ordered[$output_order[$attribute_not_in_variation_but_user_defined->code]] = $variation_tpl['VARIATION_COMPLETE_OUTPUT_' . strtoupper($attribute_not_in_variation_but_user_defined->code)];
				}
			}
		}

		ksort($variation_attribute_ordered);
		$variation_tpl['VARIATION_FORM_VARIATION_LIST'] = '';
		foreach ( $variation_attribute_ordered as $attribute_variation_to_output ) {
			$variation_tpl['VARIATION_FORM_VARIATION_LIST'] .= $attribute_variation_to_output;
		}

		$output = wpshop_display::display_template_element('product_variation_form', $variation_tpl);

		return $output;
	}

	/**
	 * Display the current configuration for a given product
	 * @param array $shortcode_attribute Some parameters given by the shortcode for display
	 */
	function wpshop_product_variations_summary( $shortcode_attribute ) {
		global $wp_query;
		$output = '';

		if ( $wp_query->query_vars['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
			$output .= wpshop_display::display_template_element('wpshop_product_configuration_summary', array('CURRENCY_SELECTOR' => wpshop_attributes_unit::wpshop_shop_currency_list_field()));
		}

		echo $output;
	}

	/**
	 * Display information for a given value of an attribute defined as an entity, when attribute option for detail view is set as true
	 * @param array $shortcode_attribute Some parameters given by the shortcode for display
	 */
	function wpshop_product_variation_value_detail( $shortcode_attribute ) {
		global $wp_query;
		if ( $wp_query->query_vars['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
			echo wpshop_display::display_template_element('wpshop_product_variation_value_detail_container', array());
		}
	}

	/**
	 * Build the product structure with variation for product choosed by the user into frontend sheet
	 * @param array $selected_variation THe list of variation choosed by the user in product frontend sheet
	 * @param integer $product_id The basic product choose by the user in frontend
	 * @return array The product list for adding to the cart build by variation priority
	 */
	function get_variation_by_priority( $selected_variation, $product_id ) {
		global $wpdb;
		$product_to_add_to_cart = array();
		$variation_attribute = array();

		if ( !empty( $selected_variation ) ) {
			$product_variation_configuration = get_post_meta($product_id, '_wpshop_variation_defining', true);
			$priority = (!empty($product_variation_configuration['options']) && !empty($product_variation_configuration['options']['priority'][0]) ) ?  $product_variation_configuration['options']['priority'][0] : 'combined';
			$product_to_add_to_cart[$product_id]['defined_variation_priority'] = 'combined';

			if ( isset($selected_variation['free']) ) {
				unset($selected_variation['free']);
			}

			/*	Get combined varaitions	*/
			$combined_variations = array();
			$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->postmeta . " AS P_META INNER JOIN " . $wpdb->posts . " as P ON ((P.ID = P_META.post_id) AND (P.post_parent = %d)) WHERE P_META.meta_key = '_wpshop_variations_attribute_def' AND P_META.meta_value = '" . serialize($selected_variation) . "'", $product_id);
			$combined_variation_id = $wpdb->get_var($query);
			if ( !empty($combined_variation_id) ) {
				$combined_variations[] = $combined_variation_id;
			}

			/*	Get single variations	*/
			$single_variations = array();
			foreach ( $selected_variation as $attribute_code => $attribute_value ) {
				if ( !empty($attribute_value) ) {
					$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->postmeta . " AS P_META INNER JOIN " . $wpdb->posts . " as P ON ((P.ID = P_META.post_id) AND (P.post_parent = %d)) WHERE P_META.meta_value = '" . serialize(array($attribute_code => $attribute_value)) . "'", $product_id);
					$single_variation_id = $wpdb->get_var($query);
					if ( !empty($single_variation_id) ) {
						$single_variations[] = $single_variation_id;
					}
				}
			}

			/*	Check current product variation options definition	*/
			if ( ($priority == 'combined') && !empty($combined_variations) ) {
				foreach ( $combined_variations as $combined_variation_id ) {
					$product_to_add_to_cart[$product_id]['variations'][] = $combined_variation_id;
				}
				$product_to_add_to_cart[$product_id]['variation_priority'] = 'combined';
			}
			else if ( ($priority == 'combined') && empty($combined_variations) && !empty($single_variations) ) {
				foreach ( $single_variations as $single_variation_id ) {
					$product_to_add_to_cart[$product_id]['variations'][] = $single_variation_id;
				}
				$product_to_add_to_cart[$product_id]['variation_priority'] = 'single';
			}
			else if ( ($priority == 'single') && !empty($single_variations)) {
				foreach ( $single_variations as $single_variation_id ) {
					$product_to_add_to_cart[$product_id]['variations'][] = $single_variation_id;
				}
				$product_to_add_to_cart[$product_id]['variation_priority'] = 'single';
			}
			else if ( ($priority == 'single') && empty($single_variations) && !empty($combined_variations)) {
				foreach ( $combined_variations as $combined_variation_id ) {
					$product_to_add_to_cart[$product_id]['variations'][] = $combined_variation_id;
				}
				$product_to_add_to_cart[$product_id]['variation_priority'] = 'combined';
			}
			if ( !empty($selected_variation['free']) ) {
				foreach ( $selected_variation['free'] as $free_variation_code => $free_variation_value) {
					if ( !empty($free_variation_value) ) {
						$product_to_add_to_cart[$product_id]['free_variation'][$free_variation_code] = $free_variation_value;
					}
				}
			}

			if ( empty($product_to_add_to_cart[$product_id]['variations']) && empty($product_to_add_to_cart[$product_id]['free_variation']) ) {
				$product_to_add_to_cart[$product_id]['variation_priority'] = 'simple';
			}
		}

		return $product_to_add_to_cart;
	}

	/**
	 * Return the good element price into cart from admin variation configuration for current product
	 * @param array $product_into_cart The complete product definition for cart and order
	 * @param array $product_variation Contain the list of selected variation choose by the client into product frontend sheet
	 * @param integer $head_product_id The basic product ht user choose variation for
	 * @param array $variations_options An array with the variation options
	 * @return array The complete product information for cart/order with the new prices defined by variations
	 */
	function get_variation_price_behaviour( $product_into_cart, $product_variation, $head_product_id, $variations_options ) {
		if ( !empty($product_variation) ) {
			$product_variation_configuration = get_post_meta($head_product_id, '_wpshop_variation_defining', true);
			$price_behaviour = (!empty($product_variation_configuration['options']) && !empty($product_variation_configuration['options']['price_behaviour'][0]) ) ?  $product_variation_configuration['options']['price_behaviour'][0] : 'addition';

			$additionnal_price = array();
			$additionnal_price[WPSHOP_PRODUCT_PRICE_HT] = $additionnal_price[WPSHOP_PRODUCT_PRICE_TTC] = $additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = 0;
			if ( (count($product_variation) > 1) || ($variations_options['type'] == 'single') ) {
				foreach ( $product_variation as $variation_id ) {
					$product_variation_def = wpshop_products::get_product_data($variation_id, true);
					$product_into_cart['item_meta']['variations'][$variation_id] = $product_variation_def;

					$additionnal_price[WPSHOP_PRODUCT_PRICE_HT] += $product_variation_def[WPSHOP_PRODUCT_PRICE_HT];
					$additionnal_price[WPSHOP_PRODUCT_PRICE_TTC] += $product_variation_def[WPSHOP_PRODUCT_PRICE_TTC];
					$additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] += $product_variation_def[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];

					foreach ($product_variation_def['item_meta']['variation_definition'] as $attribute_variation_code => $attribute_variation_value ) {
						$product_into_cart['product_reference'] .= '#' . $variation_id;
						$product_into_cart['product_name'] .= ' - ' . $attribute_variation_value['UNSTYLED_VALUE'];
					}
				}
			}
			else {
				$head_product = wpshop_products::get_product_data($head_product_id, true);

				$additionnal_price[WPSHOP_PRODUCT_PRICE_HT] += $product_into_cart[WPSHOP_PRODUCT_PRICE_HT];
				$additionnal_price[WPSHOP_PRODUCT_PRICE_TTC] += $product_into_cart[WPSHOP_PRODUCT_PRICE_TTC];
				$additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] += $product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];

				/*	Reinitialise basic information	*/
				$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] = $head_product[WPSHOP_PRODUCT_PRICE_HT];
				$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] = $head_product[WPSHOP_PRODUCT_PRICE_TTC];
				$product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = $head_product[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];
				$product_into_cart['product_name'] = $head_product['product_name'];
				$product_into_cart['item_meta']['head_product'][$product_into_cart['product_id']] = $head_product_id;
			}

			/*	If variation are existing we add the prices to the default price	*/
			if ( !empty($additionnal_price) ) {
				if ( $price_behaviour == 'addition' ) {
					$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] += $additionnal_price[WPSHOP_PRODUCT_PRICE_HT];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] += $additionnal_price[WPSHOP_PRODUCT_PRICE_TTC];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] += $additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];
				}
				elseif ( $price_behaviour == 'replacement' ) {
					$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] = $additionnal_price[WPSHOP_PRODUCT_PRICE_HT];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] = $additionnal_price[WPSHOP_PRODUCT_PRICE_TTC];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = $additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];
				}
			}
		}

		return $product_into_cart;
	}

}

?>