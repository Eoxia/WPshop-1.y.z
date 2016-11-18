<?php if ( !defined( 'ABSPATH' ) ) exit;
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
class wpshop_categories
{
	/**
	* Retourne une liste de cat�gorie
	* @param boolean $formated : formatage du r�sultat oui/non
	* @param string $product_search : recherche demand�e
	* @return mixed
	**/
	public static function product_list_cats($formated=false, $product_search=null) {
		$where  = array('hide_empty' => false);
		if(!empty($product_search))
			$where = array_merge($where, array('name__like'=>$product_search));

		$data = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, $where);
		$cats=array();
		foreach($data as $d){
			$cats[$d->term_id] = $d->name;
		}

		// Si le formatage est demand�
		$cats_string='';
		if($formated) {
			if(!empty($cats)):
				foreach($cats as $key=>$value) {
					$cats_string.= '
					<li><input type="checkbox" class="wpshop_shortcode_element wpshop_shortcode_element_categories" value="'.$key.'" id="'.WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES.'-'.$key.'" name="cats[]" /><label for="'.WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES.'-'.$key.'" > '.$value.'</label></li>';
				}
			endif;
		}
		return $formated?$cats_string:$cats;
	}

	/**
	*	Call wordpress function that declare a new term type in order to define the product as wordpress term (taxonomy)
	*/
	public static function create_product_categories(){
		$options = get_option('wpshop_catalog_categories_option', null);
		$slug = array(
				'slug' => '',
				'with_front' => true,
				'hierarchical' => true,
		);
		( empty($options['wpshop_catalog_categories_slug']) || $options['wpshop_catalog_categories_slug'] == '/' ) ? $slug = false : $slug['slug'] = $options['wpshop_catalog_categories_slug'];
		register_taxonomy(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT), array(
			'labels' => array(
				'name' => __('WPShop categories', 'wpshop'),
				'singular_name' => __('WPShop category', 'wpshop'),
				'add_new_item' => __('Add new WPShop category', 'wpshop'),
				'add_new' => _x( 'Add new', 'admin menu: add new wpshop category', 'wpshop'),
				'add_new_item' => __('Add new WPShop category', 'wpshop'),
				'edit_item' => __('Edit WPShop category', 'wpshop'),
				'new_item' => __('New WPShop category', 'wpshop'),
				'view_item' => __('View WPShop category', 'wpshop' ),
				'search_items' => __('Search WPShop categories', 'wpshop'),
				'not_found' =>  __('No WPShop categories found', 'wpshop'),
				'not_found_in_trash' => __('No WPShop categories found in trash', 'wpshop'),
				'parent_item_colon' => '',
				'menu_name' => __('WPShop Categories', 'wpshop')
			),
			'rewrite' => $slug,
			'hierarchical' => true,
			'public' => true,
			'show_in_nav_menus' => true
		));
	}

	/**
	*	Build a complete tree with the categories. Contains the complete tree for a given category and a children list for easy checking
	*
	*	@param integer $category_id The category identifier we want to get the tree element for
	*
	*	@return array $categories_list An array ordered by category with its children
	*/
	public static function category_tree($category_id = 0){
		$categories_list = array();

		$categories = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, 'hide_empty=0&parent=' . $category_id);
		if(count($categories) > 0){
			foreach($categories as $category){
				/*	If necessary un-comment this line in order to get the complete tree for the category	*/
				// $categories_list[$category->term_id]['children_tree'] = self::category_tree($category->term_id);
				$categories_list[$category->term_id]['children_category'] = get_term_children($category->term_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);

				/*	Get the product list for the category	*/
				$products = get_posts(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES => $category->slug));
				foreach($products as $product){
					$categories_list[$category->term_id]['children_product'][] = $product->ID;
				}
			}
		}

		return $categories_list;
	}
	/**
	*	Get the sub categories of a given category
	*
	*	@param integer $parent_category The main category we want to have the sub categories for
	*	@param array $instance The current instance of the widget, allows to get the different selected parameters
	*
	* @return mixed $widget_content The widget content build from option
	*/
	public static function category_tree_output($category_id = 0, $instance) {
		global $category_has_sub_category;

		$widget_content = '';
		$category_tree = wpshop_categories::category_tree($category_id);
		if((!isset($instance['wpshop_widget_categories']) && !isset($instance['show_all_cat'])) || ($instance['show_all_cat'] == 'yes')){
			$categories = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, 'hide_empty=0&parent=' . $category_id);
			if(count($categories) > 0){
				foreach($categories as $category){
					ob_start();
					require(wpshop_display::get_template_file('categories-widget.tpl.php'));
					$widget_content .= ob_get_contents();
					ob_end_clean();
				}
				$category_has_sub_category = true;
			}
			else{
				$category_has_sub_category = false;
			}
		}

		return $widget_content;
	}


	/**
	*	Add additionnal fields to the category edition form
	*/
	public static function category_edit_fields(){
		$category_id = (int) $_REQUEST["tag_ID"];
		$category_meta_information = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);
		$tpl_component = array();
		wp_enqueue_media();
		$category_thumbnail_preview = '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail_preview" />';
		/*	Check if there is already a picture for the selected category	*/

		if ( !empty($category_meta_information['wpshop_category_picture']) ) {
			$image_post = wp_get_attachment_image( $category_meta_information['wpshop_category_picture'], 'thumbnail', false, array('class' => 'category_thumbnail_preview') );
			$category_thumbnail_preview = ( !empty($image_post) ) ? $image_post : '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail_preview" />';
		}


		$tpl_component['CATEGORY_DELETE_PICTURE_BUTTON'] = '';
		if( !empty($category_meta_information) && !empty($category_meta_information['wpshop_category_picture']) ) {
			$tpl_component['CATEGORY_DELETE_PICTURE_BUTTON'] = '<a href="#" role="button" id="wps-delete-category-picture" data-nonce="' . wp_create_nonce( 'wps_delete_picture_category' ) . '" class="wps-bton-second-mini-rounded">' .__( 'Delete the category picture', 'wpshop' ). '</a> ';
		}
		$tpl_component['CATEGORY_PICTURE_ID'] = ( ( !empty($category_meta_information['wpshop_category_picture']) ) ? $category_meta_information['wpshop_category_picture'] : '' );

		$tpl_component['CATEGORY_THUMBNAIL_PREVIEW'] = $category_thumbnail_preview;
		if(isset($category_id)){
			$tpl_component['CATEGORY_TAG_ID'] = $category_id;
			$tpl_component['CATEGORY_FILTERABLE_ATTRIBUTES'] = '';
			$wpshop_category_products = wpshop_categories::get_product_of_category( $category_id );
			$filterable_attributes_list = array();
			foreach ( $wpshop_category_products as $wpshop_category_product ) {
				$elementId = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
				if ( !empty($elementId) ) {
					$product_attributes = wpshop_attributes::get_attribute_list_for_item($elementId, $wpshop_category_product);
					if ( !empty($product_attributes) ) {
						foreach ( $product_attributes as $key => $product_attribute ) {
							if ( !empty($product_attribute) && !empty($product_attribute->is_filterable) && strtolower(__($product_attribute->is_filterable, 'wpshop')) == strtolower(__('Yes', 'wpshop')) ) {
								if  ( !array_key_exists($product_attribute->attribute_id, $filterable_attributes_list) ) {
									$filterable_attributes_list[$product_attribute->attribute_id] = $product_attribute;
									$sub_tpl_component['CATEGORY_FILTERABLE_ATTRIBUTE_ID'] =  $product_attribute->attribute_id;
									$sub_tpl_component['CATEGORY_FILTERABLE_ATTRIBUTE_NAME'] =  __($product_attribute->frontend_label, 'wpshop');
									if ( !empty($category_meta_information) && !empty($category_meta_information['wpshop_category_filterable_attributes']) && array_key_exists($product_attribute->attribute_id, $category_meta_information['wpshop_category_filterable_attributes']) ) {
										$sub_tpl_component['CATEGORY_FILTERABLE_ATTRIBUTE_CHECKED'] = 'checked="checked"';
									}
									else {
										$sub_tpl_component['CATEGORY_FILTERABLE_ATTRIBUTE_CHECKED'] = '';
									}

									$tpl_component['CATEGORY_FILTERABLE_ATTRIBUTES'] .= wpshop_display::display_template_element('wpshop_category_filterable_attribute_element', $sub_tpl_component, array(), 'admin');
									unset($sub_tpl_component);
								}
							}
						}
					}
				}
			}
		 }
		 else {
		 	$tpl_component['CATEGORY_TAG_ID'] = 1;
		 }
		 $output = wpshop_display::display_template_element('wpshop_category_edit_interface_admin', $tpl_component, array(), 'admin');
		 echo $output;
	}

	/**
	*	Save the different extra fields added for the plugin
	*
	*	@param integer $category_id The category identifier we want to save extra fields for
	*	@param mixed $tt_id
	*
	*	@return void
	*/
	public static function category_fields_saver($category_id, $tt_id){
		global $wpdb;
		$category_meta = array();
		$category_option = get_option( WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);

		$wps_category_picture_id = !empty($_POST['wps_category_picture_id']) ? (int) $_POST['wps_category_picture_id'] : null;
		$filterable_attribute_for_category = ( !empty($_POST['filterable_attribute_for_category']) && is_array($_POST['filterable_attribute_for_category']) ) ? (array) $_POST['filterable_attribute_for_category'] : null;

		if ( isset( $wps_category_picture_id ) ) {
			$attach_id = intval( $wps_category_picture_id );
			$category_option['wpshop_category_picture'] = $attach_id;
		}

		if ( isset( $filterable_attribute_for_category ) ) {
			$category_option['wpshop_category_filterable_attributes'] = $filterable_attribute_for_category;
		}
		else {
			$category_option['wpshop_category_filterable_attributes'] = array();
		}
		update_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id, $category_option);

		/** Update filter values **/
		$wpshop_filter_search = new wps_filter_search();
		$wpshop_filter_search->stock_values_for_attribute( array($category_id) );
	}

	/**
	*	Add extra column to categories listing interface
	*
	*	@param array $columns Actual columns to add extra columns to
	*
	*	@return array $columns The new array with additionnal colu
	*/
	public static function category_manage_columns($columns){
	    unset( $columns["cb"] );

	    $custom_array = array(
				'cb' => '<input type="checkbox" />',
				'wpshop_category_thumbnail' => __('Thumbnail', 'wpshop')
	    );

	    $columns = array_merge( $custom_array, $columns );

	    return $columns;
	}

	/**
	*	Define the content of extra columns to add to categories listing interface
	*/
	public static function category_manage_columns_content($string, $column_name, $category_id){
		$category_meta_information = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);
		$category_thumbnail_preview = '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail_preview" />';
		/*	Check if there is already a picture for the selected category	*/
		if ( !empty($category_meta_information['wpshop_category_picture']) ) {
			$image_post = wp_get_attachment_image( $category_meta_information['wpshop_category_picture'], 'thumbnail', false, array('class' => 'category_thumbnail_preview') );
			$category_thumbnail_preview = ( !empty($image_post) ) ? $image_post : '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail_preview" />';
		}
		$category = get_term_by('id', $category_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
		$name = $category->name;

		$image = $category_thumbnail_preview;
    	return $image;
	}


	/**
	*	Display a category in a list
	*
	*	@param object $category The category definition
	*	@param string $output_type The output type defined from plugin option
	*
	*	@return mixed $content Output the category list
	*/
	public static function category_mini_output($category, $output_type = 'list'){
		$content = '';
		/*	Get the different informations for output	*/
		$category_meta_information = ( !empty($category) && !empty($category->term_id) ) ? get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category->term_id) : '';
		$categoryThumbnail = '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="wps-category-thumbnail" />';
		/*	Check if there is already a picture for the selected category	*/
		if ( !empty($category_meta_information['wpshop_category_picture']) ) {
			$image_post = wp_get_attachment_image( $category_meta_information['wpshop_category_picture'], 'wps-categorie-display', false, array('class' => 'wps-category-thumbnail') );
			$categoryThumbnail = ( !empty($image_post) ) ? $image_post : '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="wps-category-thumbnail" />';
		}


		$category_title = ( !empty($category) && !empty($category->name) ) ? $category->name : '';
		$category_more_informations = ( !empty($category) && !empty($category->description) ) ? wp_trim_words( $category->description, 30, ' [...]' ) : '';
		$category_link = ( !empty($category) && !empty($category->term_id) ) ?  get_term_link((int)$category->term_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES) : '';

		// $item_width = null;
		// /*	Make some treatment in case we are in grid mode	*/
		// if($output_type == 'grid'){
		// 		Determine the width of a component in a line grid
		// 	$element_width = (100 / WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE);
		// 	$item_width = (round($element_width) - 1) . '%';
		// }

		/*
		 * Template parameters
		 */
		//$template_part = 'category_mini_' . $output_type;
		$template_part = 'category_mini';
		$tpl_component = array();
		$tpl_component['CATEGORY_LINK'] = $category_link;
		$tpl_component['CATEGORY_THUMBNAIL'] = $categoryThumbnail;
		$tpl_component['CATEGORY_TITLE'] = $category_title;
		$tpl_component['CATEGORY_DESCRIPTION'] = $category_more_informations;
		//$tpl_component['ITEM_WIDTH'] = $item_width;
		$tpl_component['CATEGORY_ID'] = ( !empty($category) && !empty($category->term_id) ) ? $category->term_id : '';
		$tpl_component['CATEGORY_DISPLAY_TYPE'] = $output_type;

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
			//echo $template_part.'-'.$tpl_component.'<br>';
			$content = wpshop_display::display_template_element($template_part, $tpl_component);
		}
		unset($tpl_component);

		return $content;
	}

	/**
	* Traduit le shortcode et affiche une cat�gorie
	* @param array $atts : tableau de param�tre du shortcode
	* @return mixed
	**/
	public static function wpshop_category_func($atts) {
		global $wpdb;
		$string = '';
		if ( !empty($atts['cid']) ) {
			$atts['type'] = (!empty($atts['type']) && in_array($atts['type'],array('grid','list'))) ? $atts['type'] : 'grid';

			$cat_list = explode(',', $atts['cid']);

			if ( (count($cat_list) > 1) || ( !empty($atts['display']) && ($atts['display'] == 'only_cat') ) ) {
				if( count($cat_list) == 1) {
					$args = array('taxonomy' => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, 'parent' => $cat_list[0]);
					$categories = get_terms( $args );
					foreach($categories as $category) {
						$cat_list[] = $category->term_id;
					}
				}
				$string .= '
					<div class="wpshop_categories_' . $atts['type'] . '" >';
					foreach( $cat_list as $cat_id ){
						$sub_category_def = get_term($cat_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
						$string .= wpshop_categories::category_mini_output($sub_category_def, $atts['type']);
					}
				$string .= '
					</div>';
			}
			else {
				$sub_category_def = get_term($atts['cid'], WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);

				if ( empty($atts['display']) || ($atts['display'] != 'only_products') ){
					$string .= wpshop_categories::category_mini_output($sub_category_def, $atts['type']);
					$string .= '
					<div class="category_product_' . $atts['type'] . '" >
						<h2 class="category_content_part_title" >'.__('Category\'s product list', 'wpshop').'</h2>';
				}

				$string .= wpshop_products::wpshop_products_func($atts);

				if ( empty($atts['display']) || ($atts['display'] != 'only_products') ){
					$string .= '</div>';
				}
			}
		}
		else {
			$string .= __('No categories found for display', 'wpshop');
		}

		return do_shortcode($string);
	}

	public static function get_product_of_category( $category_id ) {
		$product_id_list = array();
		if ( !empty($category_id) ) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT T.* FROM " . $wpdb->term_relationships . " AS T INNER JOIN " . $wpdb->posts . " AS P ON ((P.ID = T.object_id) AND (P.post_status = %s)) WHERE T.term_taxonomy_id = %d ", 'publish', $category_id);
			$relationships = $wpdb->get_results($query);
			if ( !empty($relationships) ) {
				foreach ( $relationships as $relationship ) {
					$product_id_list[] = $relationship->object_id;
				}
			}
		}
		return $product_id_list;
	}

	/**
	 * Get the category thumbnail by the category id. Get the option
	 * wpshop_product_category_$id, check if the option is an array and if the key
	 * wpshop_category_picture(id post value) it's not empty, if it is the case set the
	 * previously value in $id_picture. Use wp_get_attachment_image_src with the
	 * $id_picture for get the informations of attachment like: url, with, height and
	 * resized image (true for resized image, false if it is the original).
	 *
	 * @see get_option
	 * @see wp_get_attachment_image_src
	 * @param unknown_type $id
	 * @param unknown_type $size
	 * @param unknown_type $attr
	 * @return (string or array)
	 */
	public static function get_the_category_thumbnail($id, $size = 'thumbnail', $icon = false) {
		/** Get the attachment/post ID */
		$array_option_category 	= get_option('wpshop_product_category_' . $id);

		/** If not attachment/post ID in the category, return "No thumbnail in the category" */
		if(is_array($array_option_category) && empty($array_option_category['wpshop_category_picture']))
			return __('No thumbnail in the category', 'wpshop');

		/** Set attachment/post ID in $id_picture */
		$id_picture = $array_option_category['wpshop_category_picture'];

		/**
		 * Set the post thumbnail in $post_thumbnail
		 * @get_the_post_thumbnail - WordPress function
		 */
		$post_thumbnail = wp_get_attachment_image_src($id_picture, $size, $icon);

		if(!$post_thumbnail)
			return __('No thumbnail in this post', 'wpshop');

		return $post_thumbnail;
	}
}
