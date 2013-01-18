<?php
/**
* WpShop categories menu widget management
*
*	This file contains the different methods for categories menu widget management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class WP_Widget_Wpshop_Product_categories extends WP_Widget {

	/**
	* Widget Constuctor
	*
	*	@return An instance of wp widget
	*/
	function WP_Widget_Wpshop_Product_categories(){
		$params = array(
			'classname' => 'widget_wpshop_pdt_categories',
			'description' => __('Wpshop product categories widget', 'wpshop')
		);
		$this->WP_Widget('wpshop_pdt_categories', __('Wpshop Categories', 'wpshop'), $params);
	}

	/**
	*	Define the content for the widget
	*
	*	@param mixed $instance The current widget instance
	*/
	function form($instance){
		$instance = wp_parse_args((array) $instance, array(
			'title' => '',
			'show_product' => '',
			'show_all_cat' => '',
			'wpshop_widget_categories' => ''
		));

		$title    		= esc_attr($instance['title']);
		$show_all_cat	= esc_attr($instance['show_all_cat']);
		$show_product	= esc_attr($instance['show_product']);
		if ( !isset( $instance['wpshop_widget_categories'] ) ){
			$categories = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array('hide_empty' => '0', 'parent' => 0));
			if(!empty($categories)){
				foreach($categories as $category){
					$instance['wpshop_widget_categories'][$category->term_id] = 'on';
				}
			}
		}
		$wpshop_widget_categories	= esc_attr($instance['wpshop_widget_categories']);
		$checked = (($show_product != '') && ($show_product == 'yes')) ? 'checked="checked"' : '';
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title', 'wpshop' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_all_cat' ); ?>" class="wpshop-widget-all-cat" name="<?php echo $this->get_field_name( 'show_all_cat' ); ?>" type="hidden" value="yes" />
			<input <?php echo $checked; ?> id="<?php echo $this->get_field_id( 'show_product' ); ?>" name="<?php echo $this->get_field_name( 'show_product' ); ?>" type="checkbox" value="yes" />
			<label for="<?php echo $this->get_field_id( 'show_product' ); ?>" ><?php _e( 'Show product', 'wpshop' ); ?></label>
		</p>
<?php
	}



	/**
	* Widget Output
	*
	* @param array $args
	* @param array $instance Widget values.
	*/
	function widget($args, $instance){
		$widget_content = '';

		/*	Get the default args from wordpress	*/
		extract($args);

		/*	Get the widget title from the admin configuration	*/
		$title = apply_filters('widget_title', (empty($instance['title']) && ($instance['title'] != 'vide')) ? __('Catalog', 'wpshop') : (($instance['title'] == 'vide') ? '&nbsp;' : $instance['title']));

		/*	Get the widget's content	*/
		$widget_content = '<ul class="main_cat_tree_widget" >' . wpshop_categories::category_tree_output(0, $instance) . '</ul>';

		/*	Add the different element to the widget	*/
		$widget_content = $before_widget . $before_title . $title . $after_title . $widget_content . $after_widget;

		echo $widget_content;
	}



	/**
	*	Get the sub categories of a given category
	*
	*	@param integer $parent_category The main category we want to have the sub categories for
	*/
	function category_tree_selector_output($category_id = 0, $wpshop_widget_categories, $instance){
		$category_tree_output = '';

		$categories = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array('hide_empty' => '0', 'parent' => $category_id));
		if(count($categories) > 0){
			foreach($categories as $category){
				$checked = (is_array($instance['wpshop_widget_categories']) && in_array($category->term_id, $instance['wpshop_widget_categories'])) ? ' checked="checked" ' : '';
				$category_main_class = ($category_id > 0) ? 'class="wpshop_categories_children"' : '';
				$category_tree_output .= '
<ul ' . $category_main_class . ' >
	<li><input ' . $checked . ' type="checkbox" name="' . $this->get_field_name('wpshop_widget_categories') . '[' . $category->term_id . ']" value="' . $category->term_id . '" id="' . $this->get_field_id('wpshop_widget_categories') . '-' . $category->term_id . '" class="categories_in_widget" /><label for="' . $this->get_field_id('wpshop_widget_categories') . '-' . $category->term_id . '" >' . $category->name . '</label>
		' . self::category_tree_selector_output($category->term_id, $wpshop_widget_categories, $instance) . '
	</li>
</ul>';
			}
		}

		return $category_tree_output;
	}

}