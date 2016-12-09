<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_filter_search {

	/** Define the main directory containing the template for the current plugin
	* @var string
	*/
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_SEARCH_DIR;


	function __construct() {
		// Template Load
		$this->template_dir = WPS_SEARCH_PATH . WPS_SEARCH_DIR . "/templates/";
		// Display Filter search
		add_shortcode('wpshop_filter_search', array( $this, 'display_filter_search'));

		// WP General actions
		add_action('save_post', 'wps_filter_search::save_displayed_price_meta');
		add_action('save_post', array(&$this, 'stock_values_for_attribute'));

		// Add scripts
		add_action( 'wp_enqueue_scripts', array( &$this, 'add_frontend_scripts' ) );

		// Ajax Actions
		add_action('wp_ajax_filter_search_action',array(&$this, 'wpshop_ajax_filter_search_action'));
		add_action('wp_ajax_nopriv_filter_search_action',array(&$this, 'wpshop_ajax_filter_search_action'));
	}


	function add_frontend_scripts() {
		wp_enqueue_script( 'wpshop_filter_search_chosen', WPSHOP_JS_URL.'jquery-libs/chosen.jquery.min.js' );
		wp_enqueue_script( 'wpshop_filter_search_js', WPS_SEARCH_URL.WPS_SEARCH_DIR.'/assets/filter_search/js/wpshop_filter_search.js' );
	}


	/**
	 * Display Filter search interface
	 * @return string
	 */
	function display_filter_search () {
		global $wp_query;
		$output = '';
		if ( !empty($wp_query) && !empty($wp_query->queried_object_id) ) {
			$category_id = $wp_query->queried_object_id;
			$category_option =  get_option('wpshop_product_category_'.$category_id);
			if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) ) {
				$output = $this->construct_wpshop_filter_search_interface( $category_id );
			}
		}
		return $output;
	}

	/**
	 * Construct Search interface
	 * @param integer $category_id
	 * @return string
	 */
	function construct_wpshop_filter_search_interface ( $category_id ) {
		global $wpdb;
		$filter_elements = '';
		if ( !empty($category_id) ) {
			$category_option =  get_option('wpshop_product_category_'.$category_id);
			if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && is_array($category_option['wpshop_category_filterable_attributes']) ) {
				foreach ( $category_option['wpshop_category_filterable_attributes'] as $k => $attribute ) {
					$attribute_def = wpshop_attributes::getElement($k);
					$filter_elements .= $this->construct_element( $attribute_def, $category_id );
					$unity = '';
					if ( !empty($attribute_def->_default_unit) ) {
						$query = $wpdb->prepare('SELECT unit FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id= %d', $attribute_def->_default_unit);
						$unity = $wpdb->get_var( $query );
					}

					//$tpl_component['DEFAULT_UNITY'.'_'.$attribute_def->code] = $unity;
				}
			}
		}
		ob_start();
		require( wpshop_tools::get_template_part( WPS_SEARCH_DIR, $this->template_dir, "frontend", "filter_search/filter_search_interface") );
		$filter_search_interface = ob_get_contents();
		ob_end_clean();
		return $filter_search_interface;
	}

	/**
	 * Controller to construct filter element on its attribute type
	 * @param object $attribute_def
	 * @param integer $category_id
	 * @return string
	 */
	function construct_element ( $attribute_def, $category_id ) {
		global $wpdb;
		$current_category_children = array();
		$args = array(
				'type'		=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
				'taxonomy'  => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES,
				'child_of'  => $category_id
		);
		$current_category_children = get_categories($args);

		if ( !empty( $attribute_def ) ) {
			switch ( $attribute_def->frontend_input ) {
				case 'text' :
					if ( $attribute_def->data_type == 'decimal' || $attribute_def->data_type == 'integer') {
						return $this->get_filter_element_for_integer_data( $attribute_def, $category_id, $current_category_children );
					}
					else {
						return $this->get_filter_element_for_text_data( $attribute_def, $category_id, $current_category_children );
					}
					break;

				case 'select' : case 'multiple-select' :
					return $this->get_filter_element_for_list_data ( $attribute_def, $category_id, $current_category_children, $attribute_def->frontend_input, 'select');
				break;

				case 'checkbox' :
					return $this->get_filter_element_for_list_data ( $attribute_def, $category_id, $current_category_children, $attribute_def->frontend_input, 'checkbox');
				break;

				case 'radio' :
					return $this->get_filter_element_for_list_data ( $attribute_def, $category_id, $current_category_children, $attribute_def->frontend_input, 'radio' );
				break;

			}
		}
	}

	/**
	 * Construct the element when it's a text Data
	 * @param StdObject $attribute_def
	 * @return string
	 */
	function get_filter_element_for_text_data( $attribute_def, $category_id, $current_category_child  ) {
		global $wpdb;
		$output = '';
		$category_option = get_option('wpshop_product_category_'.$category_id);
		$list_values = '';

		if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) && is_array($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
			foreach( $category_option['wpshop_category_filterable_attributes'][$attribute_def->id] as $attribute_value ) {
				$list_values .= '<option value="' .$attribute_value. '">' .$attribute_value. '</option>';
			}

		}
		ob_start();
		require( wpshop_tools::get_template_part( WPS_SEARCH_DIR, $this->template_dir, "frontend", "filter_search/filter_elements/element_combobox") );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * Construct the element when it's a list Data
	 * @param StdObject $attribute_def
	 * @return string
	 */
	function get_filter_element_for_list_data ( $attribute_def, $category_id, $current_category_child, $field_type, $type = 'select') {
		global $wpdb;
		$output = $list_values = '';
		$category_option = get_option('wpshop_product_category_'.$category_id);
		if ( !empty( $attribute_def) ){
			// Recovery values
			if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && isset($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
				$available_attribute_values = $category_option['wpshop_category_filterable_attributes'][$attribute_def->id];
			}

			// Store options for attribute
			$stored_available_attribute_values = array();
			$query = $wpdb->prepare( 'SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS .' WHERE attribute_id = %d ORDER BY position ASC', $attribute_def->id);
			$attributes_options = $wpdb->get_results( $query );
			if ( $attribute_def->data_type_to_use == 'internal') {
				if ( !empty( $attribute_def->default_value ) ) {
					$attribute_default_value = $attribute_def->default_value;
					$attribute_default_value = unserialize($attribute_default_value);

					$query = $wpdb->prepare( 'SELECT * FROM ' .$wpdb->posts. ' WHERE post_type = %s ORDER BY menu_order ASC', $attribute_default_value['default_value']);
					$elements = $wpdb->get_results( $query );

					if ( !empty( $elements) ) {
						foreach ( $elements as $element ) {
							if ( array_key_exists($element->ID,$available_attribute_values ) ) {
								$stored_available_attribute_values[] = array( 'option_id' => $element->ID, 'option_label' => $element->post_title );
							}
						}
					}
				}
			}
			else {
				foreach ( $attributes_options as $attributes_option ) {
					if ( in_array($attributes_option->label, $available_attribute_values) ) {
						$key_value = array_search( $attributes_option->label, $available_attribute_values);
						$stored_available_attribute_values[] = array('option_id' => $key_value, 'option_label' => $attributes_option->label );
					}
				}
			}
			// Sort Stored values
			ksort( $stored_available_attribute_values);
			if ( !empty($stored_available_attribute_values) && is_array($stored_available_attribute_values) ) {
				// Construct List values
				foreach( $stored_available_attribute_values as $stored_available_attribute_value ) {
					$list_values .= '<option value="' .$stored_available_attribute_value['option_id']. '">' .$stored_available_attribute_value['option_label']. '</option>';
				}

				// Display the good template file
				switch( $type ) {
					case 'radio' :
						ob_start();
						require( wpshop_tools::get_template_part( WPS_SEARCH_DIR, $this->template_dir, "frontend", "filter_search/filter_elements/element_radiobox") );
						$output = ob_get_contents();
						ob_end_clean();
					break;
					case 'checkbox' :
						ob_start();
						require( wpshop_tools::get_template_part( WPS_SEARCH_DIR, $this->template_dir, "frontend", "filter_search/filter_elements/element_checkbox") );
						$output = ob_get_contents();
						ob_end_clean();
						break;
					default :
						if ( $field_type == 'multiple-select' ) {
							ob_start();
							require( wpshop_tools::get_template_part( WPS_SEARCH_DIR, $this->template_dir, "frontend", "filter_search/filter_elements/element_multiple_select") );
							$output = ob_get_contents();
							ob_end_clean();
						}
						else {
							ob_start();
							require( wpshop_tools::get_template_part( WPS_SEARCH_DIR, $this->template_dir, "frontend", "filter_search/filter_elements/element_combobox") );
							$output = ob_get_contents();
							ob_end_clean();
						}
					break;
				}
			}
		}
		return $output;
	}

	/**
	 * Construct the element when it's a decimal Data
	 * @param StdObject $attribute_def
	 * @return string
	 */
	function get_filter_element_for_integer_data ( $attribute_def, $category_id, $current_category_children  ) {
		$min_value = $max_value = 0;
		$sub_tpl_component = array();
		$output = '';
		$first  = true;
		/** Get allproducts of category **/
		$category_product_ids = wpshop_categories::get_product_of_category( $category_id );
		$category_option = get_option('wpshop_product_category_'.$category_id );
		$amount_min =  ( !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]['min']) ) ? number_format($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]['min'],2, '.', '') : 0;
		$amount_max =  ( !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]['max']) ) ? number_format($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]['max'],2, '.', '') : 0;

		if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
			ob_start();
			require( wpshop_tools::get_template_part( WPS_SEARCH_DIR, $this->template_dir, "frontend", "filter_search/filter_elements/element_slider") );
			$output = ob_get_contents();
			ob_end_clean();

		}
		return $output;
	}

	/**
	 * Save Products attribute values for List attribute data for a products category
	 * @param integer $category_id
	 * @param std_object $attribute_def
	 * @param array $current_category_child
	 */
	function save_values_for_list_filterable_attribute( $category_id, $attribute_def, $current_category_children ) {
		global $wpdb;
		$category_option = get_option('wpshop_product_category_'.$category_id);
		$products = wpshop_categories::get_product_of_category( $category_id );
		/** If there are sub-categories take all products of sub-categories **/
		if ( !empty($current_category_children) ) {
			foreach ( $current_category_children as $current_category_child ) {
				$sub_categories_product_ids = wpshop_categories::get_product_of_category( $current_category_child->term_taxonomy_id );
				if ( !empty($sub_categories_product_ids) ) {
					foreach ( $sub_categories_product_ids as $sub_categories_product_id ) {
						if ( !in_array($sub_categories_product_id, $products) ) {
							$products[] = $sub_categories_product_id;
						}
					}
				}
			}
		}


		if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
			$category_option['wpshop_category_filterable_attributes'][$attribute_def->id] = array();
		}


		if ( !empty( $attribute_def) ){
			$available_attribute_values = array();
			$test = array();
			foreach ( $products as $product ) {
				$available_attribute_values = array_merge( $available_attribute_values, wpshop_attributes::get_affected_value_for_list( $attribute_def->code, $product, $attribute_def->data_type_to_use) ) ;
			}

			$available_attribute_values = array_flip($available_attribute_values);
			$data_to_save = array();
			if ( !empty($available_attribute_values) ) {
				$data_to_save = array();
				foreach( $available_attribute_values as $k => $available_attribute_value ) {
					if (  $attribute_def->data_type_to_use == 'internal' ) {
						$attribute_name = get_the_title( $k );
					}
					else {
						$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE attribute_id = %d AND id = %d', $attribute_def->id, $k);

						$attribute_name = $wpdb->get_var( $query );
					}
					if (!empty($attribute_name) && !empty($k) ) {
						if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && isset($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
							$data_to_save[$k] = $attribute_name;
							$category_option['wpshop_category_filterable_attributes'][$attribute_def->id] = $data_to_save;
						}
					}
				}
			}
		}
		update_option('wpshop_product_category_'.$category_id, $category_option);
	}

	/**
	 * Save Products attribute values for integer attribute data for a products category
	 * @param integer $category_id
	 * @param std_object $attribute_def
	 * @param array $current_category_child
	 */
	function save_values_for_integer_filterable_attribute ( $category_id, $attribute_def, $current_category_child ) {
		$first = true;
		$category_option = get_option('wpshop_product_category_'.$category_id);
		$category_product_ids = wpshop_categories::get_product_of_category( $category_id );
		$min_value = $max_value = 0;
		/** If there are sub-categories take all products of sub-categories **/
		if ( !empty($current_category_children) ) {
			foreach ( $current_category_children as $current_category_child ) {
				$sub_categories_product_ids = wpshop_categories::get_product_of_category( $current_category_child->term_taxonomy_id );
				if ( !empty($sub_categories_product_ids) ) {
					foreach ( $sub_categories_product_ids as $sub_categories_product_id ) {
						if ( !in_array($sub_categories_product_id, $category_product_ids) ) {
							$category_product_ids[] = $sub_categories_product_id;
						}
					}
				}
			}
		}

		/** For each product of category check the value **/
		if ( !empty( $category_product_ids ) ) {
			$price_piloting_option = get_option('wpshop_shop_price_piloting');
			foreach ($category_product_ids as $category_product_id) {

				if ( $attribute_def->code == WPSHOP_PRODUCT_PRICE_TTC || $attribute_def->code == WPSHOP_PRODUCT_PRICE_HT ) {

					$product_infos = wpshop_products::get_product_data($category_product_id);
					$product_price_infos = wpshop_prices::check_product_price($product_infos);
					if (!empty($product_price_infos) && !empty($product_price_infos['fork_price']) && !empty($product_price_infos['fork_price']['have_fork_price']) && $product_price_infos['fork_price']['have_fork_price'] ) {


						$max_value = ( !empty($product_price_infos['fork_price']['max_product_price']) && $product_price_infos['fork_price']['max_product_price'] > $max_value ) ? $product_price_infos['fork_price']['max_product_price'] : $max_value;
						$min_value = (!empty($product_price_infos['fork_price']['min_product_price']) && ( ( $product_price_infos['fork_price']['min_product_price'] < $min_value) || $first ) ) ?  $product_price_infos['fork_price']['min_product_price'] : $min_value;
					}
					else {
						if (!empty($product_price_infos) && !empty($product_price_infos['discount']) && !empty($product_price_infos['discount']['discount_exist'] ) && $product_price_infos['discount']['discount_exist'] ) {
							$product_data = (!empty($price_piloting_option) &&  $price_piloting_option == 'HT')  ? $product_price_infos['discount']['discount_et_price'] : $product_price_infos['discount']['discount_ati_price'];

						}
						else {

							$product_data = (!empty($price_piloting_option) &&  $price_piloting_option == 'HT')  ? $product_price_infos['et'] : $product_price_infos['ati'];
						}
						$max_value = ( !empty($product_data) && $product_data > $max_value ) ? $product_data : $max_value;
						$min_value = (!empty($product_data) && ( ( $product_data < $min_value) || $first )  ) ?  $product_data : $min_value;
					}
				}
				else {
					$product_postmeta = get_post_meta($category_product_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
					$product_data = $product_postmeta[$attribute_def->code];
					$max_value = ( !empty($product_data) && $product_data > $max_value ) ? $product_data : $max_value;
					$min_value = (!empty($product_data) && ( ( $product_data < $min_value) || $first ) ) ?  $product_data : $min_value;
				}
				$first = false;
			}
			$category_option['wpshop_category_filterable_attributes'][$attribute_def->id] = array('min' => $min_value, 'max' => $max_value);
		}
		/** Update the category option **/
		update_option('wpshop_product_category_'.$category_id, $category_option);
	}

	/**
	 * Save Products attribute values for Text attribute data for a products category
	 * @param integer $category_id
	 * @param std_object $attribute_def
	 * @param array $current_category_child
	 */
	function save_values_for_text_filterable_attribute ( $category_id, $attribute_def, $current_category_child ) {
		$category_option = get_option('wpshop_product_category_'.$category_id);
		$category_product_ids = wpshop_categories::get_product_of_category( $category_id );
		/** If there are sub-categories take all products of sub-categories **/
		$list_values = array();
		if ( !empty($current_category_children) ) {
			foreach ( $current_category_children as $current_category_child ) {
				$sub_categories_product_ids = wpshop_categories::get_product_of_category( $current_category_child->term_taxonomy_id );
				if ( !empty($sub_categories_product_ids) ) {
					foreach ( $sub_categories_product_ids as $sub_categories_product_id ) {
						if ( !in_array($sub_categories_product_id, $category_product_ids) ) {
							$category_product_ids[] = $sub_categories_product_id;
						}
					}
				}
			}
		}
		if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
			if ( !empty( $category_product_ids ) ) {
				$product_data = '';
				foreach ( $category_product_ids as $category_product_id ) {
					$product_postmeta = get_post_meta($category_product_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
					$product_data = ( !empty($product_postmeta[$attribute_def->code]) ) ? $product_postmeta[$attribute_def->code] : '';
					if ( !in_array( $product_data,  $list_values) ) {
						$list_values[] = $product_data;
						if ( !empty($product_data) ) {
							$category_option['wpshop_category_filterable_attributes'][$attribute_def->id][] = $product_data;
						}
					}
				}
			}
		}
		update_option('wpshop_product_category_'.$category_id, $category_option);
	}

	/**
	 * Save the price which is displayed on website
	 */
	public static function save_displayed_price_meta( $product_id = 0 ) {
		$ID = !empty( $_POST['ID'] ) ? (int) $_POST['ID'] : 0;
		$post_type = !empty( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';

		if ( !empty( $product_id ) || ( !empty( $ID ) && !empty($post_type) && $post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) ) {
			$product_id = !empty( $product_id ) ? $product_id : $ID;

			$price_piloting = get_option('wpshop_shop_price_piloting');
			$product_data = wpshop_products::get_product_data($product_id);
			$price_infos = wpshop_prices::check_product_price($product_data);

			if ( !empty($price_infos) ) {
				if ( !empty($price_infos['discount']) &&  !empty($price_infos['discount']['discount_exist']) ) {
					$displayed_price = ( !empty($price_piloting) && $price_piloting == 'HT') ? $price_infos['discount']['discount_et_price'] : $price_infos['discount']['discount_ati_price'];
				}
				else if( !empty($price_infos['fork_price']) && !empty($price_infos['fork_price']['have_fork_price']) ) {
					$displayed_price = $price_infos['fork_price']['min_product_price'];
				}
				else {
					$displayed_price = ( !empty($price_piloting) && $price_piloting == 'HT') ? $price_infos['et'] : $price_infos['ati'];
				}
				update_post_meta($product_id, '_wpshop_displayed_price', number_format($displayed_price,2, '.','') );
			}
		}
	}

	/**
	 * Save values for attributes
	 * @param unknown_type $values
	 */
	function stock_values_for_attribute( $categories_id = array() ) {
		@set_time_limit( 900 );

		$tax_input = ( !empty( $_POST['tax_input'] ) && !empty( $_POST['tax_input']['wpshop_product_category'] ) ) ? (int) $_POST['tax_input']['wpshop_product_category'] : '';
		$post_type = !empty( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
		if (  !empty( $tax_input ) && !empty($post_type) && $post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
			$categories_id = $tax_input;
		}

		if ( !empty( $categories_id )  ) {
			if ( $categories_id && is_array($categories_id) ) {
				foreach( $categories_id as $taxonomy_id ) {
					if ( $taxonomy_id != 0 ) {
						$current_category_children = array();
						$args = array(
								'type'		=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
								'taxonomy'  => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES,
								'child_of'  => $taxonomy_id
						);
						$current_category_children = get_categories($args);

						$category_option = get_option('wpshop_product_category_'.$taxonomy_id);
						if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && is_array($category_option['wpshop_category_filterable_attributes']) ) {
							foreach ( $category_option['wpshop_category_filterable_attributes'] as $k => $filterable_attribute ) {
								$attribute_def = wpshop_attributes::getElement($k);
								if ( !empty( $attribute_def) ) {
									switch ( $attribute_def->frontend_input ) {
										case 'text' :
											if ( $attribute_def->data_type == 'decimal' || $attribute_def->data_type == 'integer') {
												$this->save_values_for_integer_filterable_attribute( $taxonomy_id, $attribute_def, $current_category_children );
											}
											else {
												$this->save_values_for_text_filterable_attribute( $taxonomy_id, $attribute_def, $current_category_children );
											}
											break;

										case 'select' : case 'checkbox' : case 'radio' : case 'multiple-select' :
											$this->save_values_for_list_filterable_attribute( $taxonomy_id, $attribute_def, $current_category_children );
											break;

									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Pick up all filter search element type
	 * @param integer $category_id
	 * @return array
	 */
	function pick_up_filter_search_elements_type ( $category_id ) {
		$filter_search_elements = array();
		if ( !empty($category_id) ) {
			$category_option =  get_option('wpshop_product_category_'.$category_id);
			if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) ) {
				foreach ( $category_option['wpshop_category_filterable_attributes'] as $k => $attribute ) {
					$attribute_def = wpshop_attributes::getElement($k);
					if ( !empty($attribute_def) ) {
						if ( $attribute_def->frontend_input == 'text' ) {
							$filter_search_elements['_'.$attribute_def->code] = array('type' => 'fork_values');
						}
						elseif( in_array( $attribute_def->frontend_input, array('checkbox', 'multiple-select', 'radio', 'select') ) ) {
							$filter_search_elements['_'.$attribute_def->code] = array('type' => 'multiple_select_value');
						}
						elseif ( !in_array($attribute_def->frontend_input, array('hidden', 'textarea', 'password') ) )  {
							$filter_search_elements['_'.$attribute_def->code] = array('type' => 'select_value');
						}
					}
				}
			}
		}
		return $filter_search_elements;
	}

	/**
	 * AJAX - Action to search with selected attributes values
	 */
	function wpshop_ajax_filter_search_action () {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wpshop_ajax_filter_search_action' ) )
			wp_die();

		global $wpdb;
		$category_id =  !empty($_POST['wpshop_filter_search_category_id']) ? wpshop_tools::varSanitizer( $_POST['wpshop_filter_search_category_id'] ) : 0;
		$filter_search_elements = $this->pick_up_filter_search_elements_type($category_id);
		$page_id = ( !empty( $_POST['wpshop_filter_search_current_page_id']) ) ? wpshop_tools::varSanitizer( $_POST['wpshop_filter_search_current_page_id'] ) : 1;
		$request_cmd = '';
		$status = false;
		$data = array();
		foreach ( $filter_search_elements as $k=>$filter_search_element) {
			$search = isset( $_REQUEST['filter_search'.$k] ) ? sanitize_text_field( $_REQUEST['filter_search'.$k] ) : '';
			$amount_min = !isset( $_REQUEST['amount_min'.$k] ) ? 0 : sanitize_text_field( $_REQUEST['amount_min'.$k] );
			$amount_max = !isset( $_REQUEST['amount_max'.$k] ) ? 0 : sanitize_text_field( $_REQUEST['amount_max'.$k] );
			$datatype_element = array( 'select_value', 'multiple_select_value', 'fork_values');
			if ( (in_array($filter_search_element['type'], $datatype_element) && ( isset($search) && $search == 'all_attribute_values' ) ) ||
				( ($filter_search_element['type'] == 'select_value' || $filter_search_element['type'] == 'multiple_select_value' ) &&  $search == '' ) ||
				( $filter_search_element['type'] == 'fork_values' && ( $amount_min == 0 || $amount_max == 0 ) ) ) {
				unset( $filter_search_elements[$k]);
			}
		}
		$request_cmd = '';
		$first = true;
		$i = 1;
		$filter_search_elements_count = count($filter_search_elements);

		/** Get subcategories **/
		$current_category_children = array();
		$args = array(
				'type'		=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
				'taxonomy'  => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES,
				'child_of'  => $category_id
		);
		$current_category_children = get_categories($args);
		/** Construct the array for SELECT query IN **/
		$categories_id = array();
		$categories_id[] = $category_id;
		if ( !empty($current_category_children) ) {
			foreach ( $current_category_children as $current_category_child ) {
				$categories_id[] = $current_category_child->term_taxonomy_id;
			}
		}

		/** Make the array **/
		$array_for_query = implode(',', $categories_id);

		/** SQL request Construct for pick up all product with one of filter search element value **/
		if ( !empty( $filter_search_elements ) ) {
			foreach ( $filter_search_elements as $k=>$filter_search_element ) {
				$search = isset( $_REQUEST['filter_search'.$k] ) ? $_REQUEST['filter_search'.$k] : '';
				$amount_min = !isset( $_REQUEST['amount_min'.$k] ) ? 0 : sanitize_text_field( $_REQUEST['amount_min'.$k] );
				$amount_max = !isset( $_REQUEST['amount_max'.$k] ) ? 0 : sanitize_text_field( $_REQUEST['amount_max'.$k] );

				if ( !empty($filter_search_element['type']) && !empty($search) && $filter_search_element['type'] == 'select_value' && $search != 'all_attribute_values') {
					$request_cmd .= 'SELECT meta_key, post_id FROM ' .$wpdb->postmeta. ' INNER JOIN ' .$wpdb->posts. ' ON  post_id = ID WHERE (meta_key = "'.$k.'" AND meta_value = "'.$search.'") AND post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'" AND post_status = "publish" ';
					$request_cmd .= ' AND post_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id IN ('.$array_for_query.') ) ';
				}
				else if($filter_search_element['type'] == 'fork_values') {
					$request_cmd .= 'SELECT meta_key, post_id FROM ' .$wpdb->postmeta. ' INNER JOIN ' .$wpdb->posts. ' ON  post_id = ID WHERE (meta_key = "'.( ( !empty($k) && $k == '_product_price' ) ? '_wpshop_displayed_price' : $k).'" AND meta_value BETWEEN '.$amount_min.' AND '.$amount_max.') AND post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'" AND post_status = "publish"';
					$request_cmd .= ' AND post_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id IN ('.$array_for_query.') ) ';
				}
				else if( $filter_search_element['type'] == 'multiple_select_value' ) {
					/** Check the attribute id **/
					$attribute_def = wpshop_attributes::getElement(substr($k, 1), "'valid'", 'code');
					if ( !empty($attribute_def) ) {
						$request_cmd .= 'SELECT CONCAT("_", code) AS meta_key, ATT_INT.entity_id AS post_id FROM ' .WPSHOP_DBT_ATTRIBUTE. ', '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.' AS ATT_INT WHERE attribute_id = id AND attribute_id = '.$attribute_def->id;
						$first = true;
						if ( !empty($search) && is_array($search) ){
							foreach ( $search as $r ) {
								if ( $first) {
									$request_cmd .= ' AND (value ="' . $r. '"';
									$first = false;
								}
								else {
									$request_cmd .= ' OR value ="' . $r. '"';
								}
							}
							$request_cmd .= ')';
						}
						elseif(  !empty($search) )  {
							$request_cmd .= ' AND (value ="' . $search . '" )';
						}
						$request_cmd .= ' AND ATT_INT.entity_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id IN ('.$array_for_query.') ) ';


					}
				}


				if ($i < count($filter_search_elements) ) {
					$request_cmd .= ' UNION ';
				}
				$i++;
			}
		} else {
			$request_cmd .= 'SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id IN ('.$array_for_query.')';
		}
		/** SQL Request execution **/
		$query = $wpdb->prepare($request_cmd, '');
		$results = $wpdb->get_results($query);

		$first = true;
		$final_result = array();

		$temp_result = array();
		$first_key = null;

		$last = '';
		/** Transform the query result array **/
		foreach ( $results as $result ) {
			$result->meta_key = ( !empty($result->meta_key) && $result->meta_key == '_wpshop_displayed_price' ) ? '_product_price' : $result->meta_key;
			if ( $last != $result->meta_key ){
				$filter_search_elements[$result->meta_key]['count'] = 1;
				$last = $result->meta_key;
			}
			else
				$filter_search_elements[$result->meta_key]['count']++;

			$filter_search_elements[$result->meta_key]['values'][$result->post_id] = $result->post_id;
		}


		/** Check the smaller array of attributes **/
		$smaller_array = '';
		$smaller_array_count = -1;
		foreach ( $filter_search_elements as $k=>$filter_search_element ) {
			if ( empty($filter_search_element['count']) ) {
				$smaller_array_count = 0;
				$smaller_array = $k;
			}
			elseif( $smaller_array_count == -1 || $filter_search_element['count'] <= $smaller_array_count ) {
				$smaller_array_count = $filter_search_element['count'];
				$smaller_array = $k;
			}

		}

		/** Compare the smaller array with the others **/
		if ( !empty($smaller_array_count) ) {
			$temp_tab = $filter_search_elements[$smaller_array]['values'];
			foreach ( $filter_search_elements as $filter_search) {
				foreach ( $temp_tab as $value ) {
					if ( !in_array($value, $filter_search['values']) ) {
						/** If value don't exist in the smaller array, delete it **/
						$key = array_key_exists($value, $temp_tab);
						if ( $key ) {
							unset($temp_tab[$value]);
						}
					}
				}
			}
			/** Final result to display the products **/
			if ( !empty( $temp_tab) ) {
				$final_result = $temp_tab;
			}
		}
		else {
			$final_result = array();
		}

		$products_count = count($final_result);
		$products_count = sprintf(__('%s products corresponds to your search.', 'wpshop'),$products_count) ;

		/** If there is products for this filter search **/
		$status = true;
		if ( !empty($final_result) ) {
			$data['status'] = true;
			$data['result']  = do_shortcode( '[wpshop_products pid="' . implode(',', $final_result) . '" cid="' . $category_id . '" container="no" ]' ) ;
			$data['products_count'] = $products_count;
		}
		else {
			$data['status'] = false;
			$data['result'] = '<div class="container_product_listing">'.__('There is no product for this filter search.', 'wpshop').'</div>';
			$data['products_count'] = __('No products corresponds to your search', 'wpshop');
		}
		echo json_encode( $data );
		die();
	}
}
