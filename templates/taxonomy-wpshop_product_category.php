<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>
		<div id="primary">
			<div id="content" role="main">

<?php
	/* Queue the first post, that way we know
	 * what date we're dealing with (if that is the case).
	 *
	 * We reset this later so we can run the loop
	 * properly with a call to rewind_posts().
	 */
	if ( have_posts() )
		the_post();


	$output_type = (isset($wpshop_display_option['wpshop_display_list_type']) && ($wpshop_display_option['wpshop_display_list_type'] != '')) ? $wpshop_display_option['wpshop_display_list_type'] : 'grid';

	$category_has_content = false;
	$category_has_sub_content = false;
	/*	Check what must be outputed on the page (Defined in plugin option)	*/
	if(!is_array($wpshop_display_option['wpshop_display_cat_sheet_output']) || in_array('category_description', $wpshop_display_option['wpshop_display_cat_sheet_output'])):
		$category_has_content = true;

		$taxonomy_informations = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $wp_query->queried_object->term_id);
		if ( !empty($taxonomy_informations['wpshop_category_picture']) ) {
			$image_post = wp_get_attachment_image( $taxonomy_informations['wpshop_category_picture'], 'wps-categorie-mini-display', false, array('class' => 'category_thumbnail_preview') );
		}
		$category_thumbnail_preview = ( !empty($image_post) ) ? $image_post : '';
?>
			<div class="wps-taxonomy-wrapper">
					<?php echo do_shortcode('[wpshop_filter_search]'); ?>
					<article id="post-<?php $wp_query->queried_object->term_id; ?>" <?php post_class(); ?>>
						<header class="wps-entry-header">
							<div class="wps-entry-thumbnail"><?php echo $category_thumbnail_preview; ?></div>
							<div class="wps-entry-caption">
								<h1 class="wps-entry-title"><?php echo $wp_query->queried_object->name; ?></h1>
								<p class="wps-entry-description"><?php echo do_shortcode( wp_trim_words( $wp_query->queried_object->description, 30, ' [...]' ) ); ?></p>
							</div>
						</header><!-- .entry-header -->
	<?php
		endif;
	?>
					<div class="wps-categorie-wrapper" >
	<?php
			/*	Check what must be outputed on the page (Defined in plugin option)	*/
			if(!is_array($wpshop_display_option['wpshop_display_cat_sheet_output']) || in_array('category_subcategory', $wpshop_display_option['wpshop_display_cat_sheet_output'])):
					$category_tree = wpshop_categories::category_tree($wp_query->queried_object->term_id);
					if(is_array($category_tree) && (count($category_tree) > 0)):
						$category_has_content = true;
						$category_has_sub_content = true;
	?>
	<!--	Start category content display -->
						<div class="wps-categorie-content <?php echo $wpshop_display_option['wpshop_display_list_type'] ?>wrapper<?php echo $wpshop_display_option['wpshop_display_grid_element_number'] ?>" >
							<!-- <h2 class="category_content_part_title" ><?php _e('Category\'s sub-category list', 'wpshop'); ?></h2> -->
	<?php
						foreach($category_tree as $sub_category_id => $sub_category_content){
							$sub_category_definition = get_term($sub_category_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
							echo wpshop_categories::category_mini_output($sub_category_definition, $output_type);
						}
	?>
						</div>
	<?php 	endif;
			endif;
	?>

						<?php

								/*	Check what must be outputed on the page (Defined in plugin option)	*/
								if(!is_array($wpshop_display_option['wpshop_display_cat_sheet_output']) || in_array('category_subproduct', $wpshop_display_option['wpshop_display_cat_sheet_output'])):
									if ( count( wpshop_categories::get_product_of_category( $wp_query->queried_object->term_taxonomy_id ) ) > 0 ) :
										$category_has_content = true;
										$category_has_sub_content = true;
										echo do_shortcode('[wpshop_products cid="'.$wp_query->queried_object->term_id.'" type="'.$output_type.'"]');
									endif;
								elseif(is_array($wpshop_display_option['wpshop_display_cat_sheet_output']) && !in_array('category_subproduct', $wpshop_display_option['wpshop_display_cat_sheet_output'])):
									$category_has_sub_content = true;
								endif;
						?>

						<?php if ((!$category_has_content) || (!$category_has_sub_content)) : ?>
						<!--	If there is nothing to output into this page -->
							<div class="wps-alert-info" ><?php _e('There is nothing to output here', 'wpshop'); ?></div>
						<?php endif; ?>

					</div>
					</article>
				</div><!-- wps-taxonomy-wrapper -->
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
