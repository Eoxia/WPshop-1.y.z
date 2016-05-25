<?php if ( !defined( 'ABSPATH' ) ) exit;
	/*	Define default classes	*/
	$category_class = 'wpshop_top_category';
	$category_container_class = 'wpshop_hide';
	$category_title_class = '';
	$category_state_class = 'ui-icon wpshop_category_closed';

	$display_product = !empty($instance['show_product']);
	global $wp_query;

	/*	Check if the we are on a term page (category)	*/
	if(isset($wp_query->get_queried_object()->term_id) && ($wp_query->get_queried_object()->term_id > 0)){
		/*	Check if the current item we are adding into the menu is the item we are on	*/
		if($wp_query->get_queried_object()->term_id == $category->term_id){
			$category_title_class = 'wpshop_current_item';
			$category_state_class = 'ui-icon wpshop_category_opened';
			$category_container_class = '';
		}

		if(is_array($category_tree[$category->term_id]['children_category'])){
			if(in_array($wp_query->get_queried_object()->term_id, $category_tree[$category->term_id]['children_category'])){
				$category_state_class = 'ui-icon wpshop_category_opened';
				$category_container_class = '';
			}
		}
	}
	/*	Check if the we are on a product page	*/
	if(isset($wp_query->get_queried_object()->ID) && ($wp_query->get_queried_object()->ID > 0)){
		if(!empty($category_tree[$category->term_id]['children_product']) && is_array($category_tree[$category->term_id]['children_product'])){
			if(in_array($wp_query->get_queried_object()->ID, $category_tree[$category->term_id]['children_product'])){
				$category_state_class = 'ui-icon wpshop_category_opened';
				$category_container_class = '';
			}
		}
	}

	if($category->parent != 0){
		$category_class = 'wpshop_sub_category wpshop_sub_category' . $category->parent;
		if(!empty($wp_query->get_queried_object()->term_id) && ($wp_query->get_queried_object()->term_id == $category->parent)) {
			$category_container_class = '';
		}
	}

	$link = get_term_link((int)$category->term_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);

	if(is_array($category_tree[$category->term_id])){
		if((!empty($category_tree[$category->term_id]['children_category']) &&  (!is_array($category_tree[$category->term_id]['children_category']) || (count($category_tree[$category->term_id]['children_category']) <= 0)))
			&& (!empty($category_tree[$category->term_id]['children_product']) && (!is_array($category_tree[$category->term_id]['children_product']) || (count($category_tree[$category->term_id]['children_product']) <= 0)))){
			$category_state_class = 'wpshop_category_empty';
		}
		elseif(!empty($category_tree[$category->term_id]['children_category']) &&  (!is_array($category_tree[$category->term_id]['children_category']) || (count($category_tree[$category->term_id]['children_category']) <= 0))
			&& (is_array($category_tree[$category->term_id]['children_product']) || (count($category_tree[$category->term_id]['children_product']) >= 0))
			&& ($display_product == '')){
			$category_state_class = 'wpshop_category_empty';
		}
	}

	if(!empty($wp_query->get_queried_object()->parent) && in_array($wp_query->get_queried_object()->parent, get_term_children((int)$category->term_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES))){
		$category_title_class .= ' wpshop_ancestor_current_item';
	}
	if(!empty($wp_query->get_queried_object()->parent) && ($wp_query->get_queried_object()->parent == $category->term_id)){
		$category_title_class .= ' wpshop_parent_current_item';
	}
?>
<li class="wpshop_cat_widget_item <?php echo $category_title_class; ?>" >
	<a class="widget_category_title" href="<?php echo $link; ?>" ><span><?php echo esc_html($category->name); ?></span></a>
<?php
		if($category_state_class != 'wpshop_category_empty'){
?>
	<ul class="wpshop_categories_widget <?php echo $category_class; ?>" id="wpshop_categories_widget_<?php echo $category->term_id; ?>" >
<?php
			echo wpshop_categories::category_tree_output($category->term_id, $instance);

			/*	Get the product of the current category	if the current category has no sub category*/
			global $category_has_sub_category;
			if(!$category_has_sub_category && ($display_product == 'yes')){
				wpshop_products::get_product_of_category($category->slug, $category->term_id);
			}
?>
	</ul>
<?php
		}
?>
</li>