<?php if ( !defined( 'ABSPATH' ) ) exit;
	/*	Define default classes	*/
	$product_container_class = 'wpshop_hide';
	$product_title_class = '';

	$link = get_term_link((int)$category_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES) . '/' . $product->post_name;

	global $wp_query;
	/*	Check if the we are on a product page	*/
	if(isset($wp_query->get_queried_object()->ID) && ($wp_query->get_queried_object()->ID > 0)){
		/*	Check if the current item we are adding into the menu is the item we are on	*/
		if($wp_query->get_queried_object()->ID == $product->ID){
			$product_container_class = '';
			$product_title_class = 'wpshop_current_item';
		}
	}

	if($wp_query->query_vars[WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES] == $category_slug){
		$product_container_class = '';
	}

?>
<ul class="wpshop_product_widget <?php echo $product_container_class; ?> wpshop_category_sub_content_<?php echo $category_id; ?> wpshop_top_level_product" id="wpshop_product_widget<?php echo $category_slug . '-' . $product->ID; ?>" >
	<li>
		<a class="product_title <?php echo $product_title_class; ?>" href="<?php echo $link; ?>" ><?php echo esc_html( $product->post_title ); ?></a>
	</li>
</ul>