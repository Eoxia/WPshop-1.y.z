<?php
/**
 * Plugin Name: WPShop Breadcrumb
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Breadcrumb
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WpShop Breadcrumb bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wpshop_breadcrumb") ) {
	class wpshop_breadcrumb {
		function __construct () {
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			add_shortcode('wpshop_breadcrumb', array(&$this, 'display_wpshop_breadcrumb'));
			add_action( 'wp_enqueue_scripts', array( 'wpshop_breadcrumb', 'frontend_css' ) );
		}

		public static function frontend_css() {
			/** Include CSS **/
			wp_register_style( 'wpshop_breadcrumb_css', plugins_url('templates/wpshop/css/wpshop_breadcrumb.css', __FILE__) );
			wp_enqueue_style( 'wpshop_breadcrumb_css' );
		}

		/** Load module/addon automatically to existing template list
		 *
		 * @param array $templates The current template definition
		 *
		 * @return array The template with new elements
		 */
		function custom_template_load( $templates ) {
			include('templates/wpshop/main_elements.tpl.php');
			$wpshop_display = new wpshop_display();
			$templates = $wpshop_display->add_modules_template_to_internal( $tpl_element, $templates );
			unset($tpl_element);

			return $templates;
		}

		/**
		 * Display the WPShop Breadcrumb
		 */
		function display_wpshop_breadcrumb () {
			global $wpdb; global $wp_query; global $wpdb;
			$output = $breadcrumb = '';
			/** Check if the queried object is aproduct or a category **/
			if ( !empty($wp_query) && !empty($wp_query->queried_object_id) ) {
				$current_id = $wp_query->queried_object_id;
				$object = $wp_query;
				$taxonomy =  !empty($object->queried_object->taxonomy) ? $object->queried_object->taxonomy : '';
				$on_product_page = false;

				/** Check if we are on a product_page OR Taxonomy Page**/
				if ( !empty($object->queried_object->post_type) && $object->queried_object->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
					$taxonomy = WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES;
					$breadcrumb_definition = array();
					$query = $wpdb->prepare ('SELECT term_taxonomy_id FROM '.$wpdb->term_relationships. ' WHERE object_id = %d', $wp_query->queried_object_id);
					$product_categories = $wpdb->get_results( $query );
					$max = $deeper_category_id = 0;
					if ( !empty($product_categories) ) {
						foreach( $product_categories as $product_category ) {
							$query = $wpdb->prepare( 'SELECT term_id FROM '.$wpdb->term_taxonomy.' WHERE term_taxonomy_id = %d LIMIT 1',$product_category->term_taxonomy_id );
							$cat_id = $wpdb->get_var( $query );

							$tmp_breadcrumb_definition = $this->get_breadcrumb ( $cat_id );
							if ($max <= count( $tmp_breadcrumb_definition ) ) {
								$max = count( $tmp_breadcrumb_definition );
								$deeper_category_id = $product_category->term_taxonomy_id;
							}
						}
					}

					$deeper_category_id = ( !empty($cat_id) ) ? $cat_id : $deeper_category_id;
					$breadcrumb_definition = $this->get_breadcrumb ( $deeper_category_id );
					$on_product_page = true;
				}
				/** If it is a custom post type **/
				elseif( !empty($object->queried_object->post_type) && $object->queried_object->post_type != WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) {
					$breadcrumb_definition = $this->get_breadcrumb ( $object->queried_object->ID, 'custom_post_type', $object->queried_object->post_type );
				}
				elseif( !empty($object->queried_object->taxonomy) ) {
					$breadcrumb_definition = $this->get_breadcrumb ( $object->queried_object->term_id, 'taxonomy', $object->queried_object->taxonomy );
				}
				else {
					$breadcrumb_definition = array();
				}

				/** Construct the breadcrumb **/
				if ( !empty($breadcrumb_definition) ) {
					$count_breadcrumb_definition = count($breadcrumb_definition);
					//echo '<pre>';print_r($breadcrumb_definition);echo '</pre>';
					$j = 0;
					for ( $i = ($count_breadcrumb_definition - 1); $i >= 0; $i-- ) {

						if ( $breadcrumb_definition[$i]['category_parent_id'] == 0 ) {
							$category_name = get_bloginfo();
							$category_link = site_url();
						}
						else {

							if ( !empty($taxonomy) ) {
								$term = get_term( $breadcrumb_definition[$i]['category_parent_id'], $taxonomy );
								$category_name = (!empty($term) && !empty( $term->name) ) ? $term->name : '';
								$category_link = (!empty($term) && !empty($term->slug) ) ? get_term_link( $term->slug, $taxonomy ) : '';
							}
							elseif( !empty($object->queried_object->post_type) && $object->queried_object->post_type != WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
								$post = get_post( $breadcrumb_definition[$i]['category_parent_id'] );
								$category_name = $post->post_title;
								$category_link = get_permalink( $breadcrumb_definition[$i]['category_parent_id'] );
							}

						}

						if ( $i == 0 && !$on_product_page ) {
							$output .= wpshop_display::display_template_element('wpshop_breadcrumb_first_element', array('CATEGORY_NAME' => $category_name ), array(), 'wpshop');
						}
						else {
							$tpl_component = $sub_tpl_component = array();
							$elements_list = $element_list = $tpl_component['OTHERS_CATEGORIES_LIST'] = '';
							if( isset( $breadcrumb_definition[$i]['category_parent_id']) ) {
								if ( !empty( $breadcrumb_definition[$i]['category_children'] ) && is_array($breadcrumb_definition[$i]['category_children']) ) {
									foreach( $breadcrumb_definition[$i]['category_children'] as $child_category ) {
										$child_term = get_term( $child_category, $taxonomy );
										$child_category_name = $child_term->name;
										$child_category_link = get_term_link( $child_term->slug, $taxonomy );
										$element_list .= wpshop_display::display_template_element('wpshop_breadcrumb_others_categories_list_element', array('ELEMENT_LIST_CATEGORY_NAME' => $child_category_name, 'ELEMENT_LIST_CATEGORY_LINK' => $child_category_link), array(), 'wpshop');
									}

									$elements_list = wpshop_display::display_template_element('wpshop_breadcrumb_others_categories_list', array('ELEMENTS_LIST' => $element_list), array(), 'wpshop');
								}
								$tpl_component['CATEGORY_LINK'] = $category_link;
								$tpl_component['OTHERS_CATEGORIES_LIST'] = $elements_list;
								$tpl_component['CATEGORY_NAME'] = $category_name;
								$output .= wpshop_display::display_template_element('wpshop_breadcrumb_element', $tpl_component, array(), 'wpshop');
								unset( $tpl_component );
							}

						}


						/**	Affichage du lien vers la boutique si on est sur une page produit ou catégories / Display a link to the shop if we are on a product or a category page	*/
						if ( ( 0 == $j ) && !empty( $object->queried_object ) && (
								( isset( $object->queried_object->post_type ) && !empty( $object->queried_object->post_type ) && ( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT == $object->queried_object->post_type ) )
								|| ( isset( $object->queried_object->taxonomy ) && !empty( $object->queried_object->taxonomy ) && ( WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES == $object->queried_object->taxonomy ) )
						)
						) {
							$product_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_product_page_id' ) );
							$url = get_permalink( $product_page_id );
							$shop_page = get_post( $product_page_id );
							$tpl_component['CATEGORY_LINK'] = $url;
							$tpl_component['OTHERS_CATEGORIES_LIST'] = '';
							$tpl_component['CATEGORY_NAME'] = $shop_page->post_title;
							$output .= wpshop_display::display_template_element('wpshop_breadcrumb_element', $tpl_component, array(), 'wpshop');
							$j++;
						}
					}
				}

				if ( !empty($post) && !empty( $post->ID ) && !empty($post->post_type) && $post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
// 					$tpl_component['CATEGORY_LINK'] = get_permalink( $post->ID );
// 					$tpl_component['OTHERS_CATEGORIES_LIST'] = '';
// 					$tpl_component['CATEGORY_NAME'] = $post->post_title;
// 					$output .= wpshop_display::display_template_element('wpshop_breadcrumb_element', $tpl_component, array(), 'wpshop');

					$output .= '<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="#">' .$post->post_title. '</a></li>';
				}
				$breadcrumb = wpshop_display::display_template_element('wpshop_breadcrumb', array('BREADCRUMB_CONTENT' => $output), array(), 'wpshop');

				return $breadcrumb;
			}
		}

		function get_breadcrumb( $current_category_id, $element_type = 'taxonomy', $identifer = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
			global $wpdb;
			$categories_id = array();
			if ( isset($current_category_id) ) {

				if ( $element_type == 'taxonomy' || $element_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) {
					$first_parent_category = false;
					if ( !empty($current_category_id) ) {
						$categories_id[] = array('category_parent_id' => $current_category_id, 'category_children' => '' );
					}
						do {
							$children_list = array();
							$query = $wpdb->prepare( 'SELECT parent FROM ' .$wpdb->term_taxonomy. ' WHERE term_id = %d', $current_category_id );
							$current_parent_id = $wpdb->get_var( $query );
							if ( $current_parent_id == 0 ) {
								$first_parent_category = true;
							}
							else {
								$children_list = $this->get_categories_for_parent ( $current_parent_id );
							}
							$categories_id[] = array('category_parent_id' => $current_parent_id, 'category_children' => $children_list );

							$current_category_id = $current_parent_id;
						} while ( $first_parent_category == false );
				}
				elseif ( $element_type == 'custom_post_type' ) {
					if ( !empty($current_category_id) ) {
						$categories_id[] = array('category_parent_id' => $current_category_id, 'category_children' => array() );
					}
					if ( $identifer != 'page' ) {
						$categories_id[] = array('category_parent_id' => wpshop_entities::get_entity_identifier_from_code( $identifer ), 'category_children' => array() );
					}
					$categories_id[] = array('category_parent_id' => 0, 'category_children' => array() );
				}

			}
			return $categories_id;
		}

		function get_categories_for_parent( $parent_id ) {
			global $wpdb;
			$children_list = array();
			if ( !empty($parent_id) ) {
				$query = $wpdb->prepare('SELECT term_id  FROM ' .$wpdb->term_taxonomy. ' WHERE parent = %d', $parent_id);
				$children = $wpdb->get_results( $query );

				if ( !empty( $children ) ) {
					foreach ( $children as $child ) {
						$children_list[] = $child->term_id;
					}
				}
			}
			return $children_list;
		}
	}
}

/**	Instanciate the module utilities if not	*/
if ( class_exists("wpshop_breadcrumb") ) {
	$wpshop_breadcrumb = new wpshop_breadcrumb();
}