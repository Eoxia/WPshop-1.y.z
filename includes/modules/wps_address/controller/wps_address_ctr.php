<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * File defining class for addresses initialisation
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 * @package Geolocalisation
 */

/** Check if the plugin WPS_LOCALISATION_VERSION is defined. If not defined script will be stopped here */
if ( !defined( 'WPS_LOCALISATION_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpeo_geoloc') );
}

/**
 * Addresses initialisation class
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 * @package Geolocalisation
 */
class wps_address {

	/**
	 * Initialise Addresses component management
	 */
	function __construct() {
		/**	Create customer entity type on wordpress initilisation*/
		add_action( 'init', array( $this, 'create_addresses_entity' ) );

		/**	Add filters for addresses list	*/
		add_filter( 'bulk_actions-edit-' . WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, array( $this, 'addresses_list_table_bulk_actions' ) );
		add_filter( 'manage_edit-' . WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS . '_columns', array( $this, 'list_table_header' ) );
		add_action( 'manage_' . WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS . '_posts_custom_column' , array( $this, 'list_table_column_content' ), 10, 2 );

		/**	Filter search for customers	*/
		add_filter( 'pre_get_posts', array( $this, 'addresses_custom_query' ) );

		/**	Load	*/
		add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );


		/**	Add shortocde listener for addresses	*/
		add_shortcode( 'wps_address_list', array( &$this, 'get_addresses') );
		add_shortcode( 'wps_addresses', array( &$this, 'shortcode_callback_display_addresses') );
		add_shortcode( 'wps_addresses_list', array( &$this, 'shortcode_display_addresses_list' ) );

		/**	Add listener for ajax actions	*/
		add_action( 'wp_ajax_wps_load_address_form', array( &$this, 'wps_load_address_form') ); // DONE
		add_action( 'wp_ajax_wps_save_address', array( &$this, 'wps_save_address') ); // DONE
		add_action( 'wp_ajax_wps-address-edition-form-load', array( &$this, 'load_address_edition_form' ) ); // DONE
		add_action( 'wp_ajax_wps-address-display-an-address', array( &$this, 'display_address' ) ); // DONE
// 		add_action( 'wap_ajax_wps-address-save-address', array( &$this, 'wps_save_address' ) );
		add_action( 'wp_ajax_wps-address-display-list', array( &$this, 'display_addresses_list' ) ); // DONE
		add_action( 'wp_ajax_wps-address-add-new', array( &$this, 'display_address_adding_form' ) ); // DONE
		add_action( 'wp_ajax_wps_delete_an_address', array( &$this, 'wps_delete_an_address' ) ); // DONE
		add_action( 'wp_ajax_wps_reload_address_interface', array( &$this, 'wps_reload_address_interface' ) );
// 		add_action( 'wap_ajax_display_address_form', array( &$this, '') );
// 		add_action( 'wap_ajax_wps-add-an-address-in-admin', array( $this, 'wps_add_an_address_in_admin' ) );

		/*	Include the different javascript	*/
		add_action( 'wp_enqueue_scripts', array( &$this, 'frontend_js' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ) );

		/**	Add addresses metaboxes to wordpress element	*/
		add_action('add_meta_boxes', array( &$this, 'addresses_metaboxes'), 1);
	}

	function shortcode_display_addresses_list( $args ) {
		$addresses = $this->get_addresses_list( $args[ 'id' ] );
		require_once( wpshop_tools::get_template_part( WPS_LOCALISATION_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, 'backend', 'addresses') );
	}

	/**
	 * Create the addresses entity
	 */
	function create_addresses_entity() {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT P.post_title, PM.meta_value FROM {$wpdb->posts} AS P INNER JOIN {$wpdb->postmeta} AS PM ON (PM.post_id = P.ID) WHERE P.post_name = %s AND PM.meta_key = %s", WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, '_wpshop_entity_params' );
		$entity_definition = $wpdb->get_row( $query );
		$entity_params = !empty( $entity_definition ) && !empty( $entity_definition->meta_value ) ? unserialize( $entity_definition->meta_value ) : null;

		$post_type_params = array(
			'labels' => array(
				'name'					=> __( 'Addresses' , 'wpshop' ),
				'singular_name' 		=> __( 'Address', 'wpshop' ),
				'add_new_item' 			=> __( 'New address', 'wpshop' ),
				'add_new' 				=> __( 'New address', 'wpshop' ),
				'edit_item' 			=> __( 'Edit address', 'wpshop' ),
				'new_item' 				=> __( 'New address', 'wpshop' ),
				'view_item' 			=> __( 'View address', 'wpshop' ),
				'search_items' 			=> __( 'Search in addresses', 'wpshop' ),
				'not_found' 			=> __( 'No address found', 'wpshop' ),
				'not_found_in_trash' 	=> __( 'No address founded in trash', 'wpshop' ),
				'parent_item_colon' 	=> '',
			),
			'description'         	=> '',
			'supports'            	=> !empty($entity_params['support']) ? $entity_params['support'] : array( 'title' ),
			'hierarchical'        	=> false,
			'public'              	=> false,
			'show_ui'             	=> false,
			'show_in_menu'        	=> 'edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
			'show_in_nav_menus'   	=> false,
			'show_in_admin_bar'   	=> false,
			'can_export'          	=> false,
			'has_archive'         	=> false,
			'exclude_from_search' 	=> true,
			'publicly_queryable'  	=> false,
			'rewrite'			  	=> false,
		);
		register_post_type( WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, $post_type_params );
	}

	/**
	 * Filter bulk actions into customer list table
	 *
	 * @param array $actions Current available actions list
	 *
	 * @return array The new action list to use into customer list table
	 */
	function addresses_list_table_bulk_actions( $actions ){
		unset( $actions[ 'edit' ] );
		unset( $actions[ 'trash' ] );

		return $actions;
	}

	/**
	 * Change the addresses list table header to display custom informations
	 *
	 * @param array $current_header The current header list displayed to filter and modify for new output
	 *
	 * @return array The new header to display
	 */
	function list_table_header( $current_header ) {
		unset( $current_header['title'] );
		unset( $current_header['date'] );

		$current_header['address_identifier'] = __( 'Address ID', 'wpshop' );
		$current_header['address_element_name'] = __( 'Element', 'wpshop' );
		$current_header['address_type'] = __( 'Address type', 'wpshop' );
		$current_header['address_content'] = __( 'Address', 'wpshop' );

		return $current_header;
	}

	/**
	 * Display the content into list table column
	 *
	 * @param string $column The column identifier to modify output for
	 * @param integer $post_id The current post identifier
	 */
	function list_table_column_content( $column, $post_id ) {
		global $wpdb;

		/**	Get wp_users idenfifier from customer id	*/
		$query = $wpdb->prepare( "SELECT PA.post_parent AS post_parent, PA.post_author AS post_author, PC.ID AS parent_id, PC.post_title AS post_title, PC.post_type AS post_type FROM {$wpdb->posts} AS PC INNER JOIN {$wpdb->posts} AS PA ON ( PA.post_parent = PC.ID ) WHERE PA.ID = %d", $post_id);
		$address_associated_element = $wpdb->get_row( $query );

		/**	SCOTCH - Define the associated element type - SCOTCH	*/
		$address_associated_element_type = $address_associated_element->post_type;
		if ( $address_associated_element->post_parent == $address_associated_element->post_author ) {
			$address_associated_element_type = WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;
		}

		/**	Get the	associated element definition	*/
		$associated_element_definition = get_post_type_object( $address_associated_element_type );

		/**	Get address informations	*/
		$address_meta = get_post_meta( $post_id );

		/**	Get user data	*/
		switch ( $address_associated_element_type ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS:
				$associated_customer = get_userdata( $address_associated_element->post_author );
				$customer_name_to_display = $associated_customer->display_name;
				if ( !empty( $associated_customer->first_name ) && !empty( $associated_customer->last_name ) ) {
					$customer_name_to_display = $associated_customer->last_name . ' ' .  $associated_customer->first_name ;
				}
				$element_main_infos = ( !empty( $associated_element_definition ) && !empty( $associated_element_definition->labels ) && !empty( $associated_element_definition->labels->singular_name ) ?  $associated_element_definition->labels->singular_name . ' - ' : "" ) . $address_associated_element->parent_id . ' - ' . $customer_name_to_display;
			break;

			default:
				$element_main_infos = ( !empty( $associated_element_definition ) && !empty( $associated_element_definition->labels ) && !empty( $associated_element_definition->labels->singular_name ) ?  $associated_element_definition->labels->singular_name . ' - ' : "" ) . $address_associated_element->parent_id . ' - ' . $address_associated_element->post_title;
			break;
		}

		/**	Switch current column for custom case	*/
		$use_template = true;
		switch ( $column ) { }

		/**	Require the template for displaying the current column	*/
		if ( $use_template ) {
			$template = wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, 'backend', 'addresses_listtable/' . $column );
			if ( is_file( $template ) ) {
				require( $template );
			}
		}
	}

	/**
	 * WORDPRESS QUERY HOOK - Hook the query for displaying addresses list
	 *
	 * @param WP_Object $query The current query launched for retrieving addresses
	 */
	function addresses_custom_query( $query ) {
		if( is_admin() && $query->query['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS && $query->is_main_query() ) {
	        add_filter( 'posts_orderby', array( $this, 'addresses_custom_query_order' ) );
		}
	}

	/**
	 * WORDPRESS QUERY HOOK - QUERY ORDER - Change order for addresses: order list by parent ID
	 *
	 * @return WP_Object The order parameters
	 */
	function addresses_custom_query_order() {
		global $wpdb;

		return "$wpdb->posts.post_parent DESC, ID DESC";
	}

	/**
	 * Check in database if there are addresses associated to current post type
	 *
	 * @param string $post The current post type
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function addresses_metaboxes( $post ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s AND post_type = %s", $post, WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES );
		$parent = $wpdb->get_var( $query );
		if ( !empty( $parent ) ) {
			$address_meta_box_checking = get_post_meta( $parent, '_wpshop_entity_attached_address', true);
			if ( !empty($address_meta_box_checking) ) {
				add_meta_box( 'wps_attached_addresses', __('Attached addresses', 'wpshop'), array( &$this, 'addresses_metaboxes_content' ), $post, 'normal', 'default' );
			}
		}
	}

	/**
	 * Call the different element to display addresses into associated metaboxes into backend part
	 *
	 * @param object $post The current post definition
	 * @param array $args A list of parameters allowing to specify the element to display
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function addresses_metaboxes_content( $post, $args ) {
		$addresses = self::get_addresses_list( $post->ID );
		require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "address", "metabox") );
	}

	/**
	 * Load the different javascript librairies
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function frontend_js() {
		wp_register_style( 'wps_address_frontend_css', WPS_ADDRESS_URL . '/assets/css/frontend.css' );
		wp_enqueue_style( 'wps_address_frontend_css' );
		wp_enqueue_script( 'wps_address_js',  WPS_ADDRESS_URL . '/assets/frontend/js/wps_address.js', array( 'jquery', 'jquery-form' ) );
	}

	function admin_scripts() {
		global $current_screen;
	    if ( ! in_array( $current_screen->post_type, array( WPSHOP_NEWTYPE_IDENTIFIER_ORDER, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ), true ) )
	        return;

		wp_enqueue_script( 'wps_address_js',  WPS_ADDRESS_URL . '/assets/backend/js/wps_address.js', array( 'jquery', 'jquery-form' ) );
		wp_register_style( 'wps_address_backend_css', WPS_ADDRESS_URL . '/assets/backend/css/backend.css'/*, array( '' )*/ );
		wp_enqueue_style( 'wps_address_backend_css' );
	}

	/** Load module/addon automatically to existing template list
	 *
	 * @param array $templates The current template definition
	 *
	 * @return array The template with new elements
	 */
	function custom_template_load( $templates ) {
		include( WPS_LOCALISATION_TEMPLATES_MAIN_DIR . 'wpshop/main_elements.tpl.php');

		$wpshop_display = new wpshop_display();
		$templates = $wpshop_display->add_modules_template_to_internal( $tpl_element, $templates );
		unset($tpl_element);

		return $templates;
	}




	/**
	 * Get adress list for an user without hidden attributes
	 * @param Integer $user_id
	 * @return Ambigous <multitype:, mixed, string, boolean, unknown, string>
	 */
	public static function get_addresses_list_no_hidden_attributes( $user_id ) {
		global $wpdb;
		$query = $wpdb->prepare( 'SELECT ID FROM '. $wpdb->posts . ' WHERE post_type = %s AND post_parent = %s', WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, $user_id );
		$addresses_ids = $wpdb->get_col( $query );
		$address_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS );
		$addresses_result = array();
		foreach( $addresses_ids as $address_id ) {
			$address_post_meta = get_post_meta( $address_id, '_wpshop_address_metadata', true );
			$address_type_id = get_post_meta( $address_id, '_wpshop_address_attribute_set_id', true );
			$query = $wpdb->prepare( 'SELECT attribute_id FROM '.WPSHOP_DBT_ATTRIBUTE_DETAILS . ' WHERE attribute_set_id = %d AND entity_type_id = %d ORDER BY position', $address_type_id, $address_entity_id );
			$attributes_ids = $wpdb->get_col( $query );
			if( !empty( $attributes_ids ) ) {
				foreach( $attributes_ids as $attributes_id ) {
					$attribute_def = wpshop_attributes::getElement( $attributes_id, '"valid"', 'id' );
					if( !empty( $attribute_def ) && !empty( $address_post_meta[ $attribute_def->code ] ) && $attribute_def->frontend_input != 'hidden' ) {
						$addresses_result[ $address_type_id ][ $address_id ][ $attribute_def->code ]['label'] = stripslashes( __( $attribute_def->frontend_label , 'wpshop' ) );
						$addresses_result[ $address_type_id ][ $address_id ][ $attribute_def->code ]['value'] = stripslashes( __( $address_post_meta[ $attribute_def->code ], 'wpshop' ) );
					}
				}
			}
		}
		return $addresses_result;
	}

	/**
	 * Get adress list for an user
	 * @param Integer $user_id
	 * @return Ambigous <multitype:, mixed, string, boolean, unknown, string>
	 */
	public static function get_addresses_list( $user_id ) {
		global $wpdb;
		$addresses_list = array();
		$query = $wpdb->prepare( 'SELECT ID FROM '. $wpdb->posts. ' WHERE post_type = %s AND post_parent = %s', WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, $user_id );
		$addresses = $wpdb->get_results( $query );
		foreach( $addresses as $address ) {
			$address_post_meta = get_post_meta( $address->ID, '_wpshop_address_metadata', true);
			$address_type_post_meta = get_post_meta( $address->ID, '_wpshop_address_attribute_set_id', true);

			if( !empty($address_post_meta) && !empty($address_type_post_meta) ) {
				$addresses_list[$address_type_post_meta][$address->ID] = $address_post_meta;
			}
		}
		return $addresses_list;
	}

	/** Display Address**/
	public static function display_an_address( $address, $address_id = '', $address_type_id = '' ) {
		global $wpdb;
		$countries = unserialize(WPSHOP_COUNTRY_LIST);
		$output = '';
		if ( !empty($address) ) {
			$has_model = false;

			/** Check if a model exists**/
			if ( !empty($address_id) || !empty( $address_type_id ) ) {
				$address_type = ( !empty($address_type_id) ) ? $address_type_id : get_post_meta( $address_id, '_wpshop_address_attribute_set_id', true );
				if( !empty($address_type) ) {
					/** Shipping & Billing option **/
					$shipping_option = get_option( 'wpshop_shipping_address_choice' );
					if ( !empty($shipping_option) && !empty($shipping_option['choice']) && $shipping_option['choice'] == $address_type && !empty($shipping_option['display_model']) ) {
						$display_model =  $shipping_option['display_model'];
						$has_model = true;

					}
					else {
						$billing_option = get_option( 'wpshop_billing_address' );
						if ( !empty($billing_option) && !empty($billing_option['choice']) && $billing_option['choice'] == $address_type && !empty($billing_option['display_model']) ) {
							$display_model =  $billing_option['display_model'];
							$has_model = true;
						}
					}
				}
			}
			$has_model = false;
			if (  $has_model ) {
				foreach( $display_model as $group_id => $group ) {
					foreach( $group as $att_id => $att ) {
						if ( !empty($att) ) {
							// Get attribute def
							$attribute_id = str_replace( 'attribute_', '' , $att );
							if( !empty($attribute_id) ) {
								$attribute_def = wpshop_attributes::getElement( $attribute_id, '"valid"', 'id' );
								if ( !empty($attribute_def) ) {
									if ( $attribute_def->frontend_input == 'select' ) {
										$query = $wpdb->prepare( 'SELECT value FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d',$address[ $attribute_def->code] );
										$output .= '<strong>'.__( $attribute_def->frontend_label, 'wpshop').' :</strong> '.__( $wpdb->get_var( $query ), 'wpshop' ).' ';
									}
									elseif( $attribute_def->frontend_verification == 'country' ) {
										$output .= ( !empty($countries[ $address[ $attribute_def->code] ]) ) ? '<strong>'.__( $attribute_def->frontend_label, 'wpshop').' :</strong> '.__( $countries[ $address[ $attribute_def->code] ], 'wpshop' ).' ' : '';
									}
									else {
										$output .= ( !empty($address[ $attribute_def->code]) ) ? '<strong>'.__( $attribute_def->frontend_label, 'wpshop').' :</strong> '.$address[ $attribute_def->code].' ' : ' ';
									}
								}
								//End Line
								$next_element = next( $display_model[$group_id] );
								$end_line = strstr( $next_element, '-end-line-', true );
								if ( !empty($end_line) && $end_line == 'wps-attribute' ) {
									$output .= '<br/>';
								}
							}
						}
					}
				}
			}
			else {
				if( !empty($address_type) ) {
					$tmp_array = array();
					$address_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS );
					$query = $wpdb->prepare( 'SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_DETAILS . ' WHERE attribute_set_id = %d AND entity_type_id = %d ORDER BY position', $address_type, $address_entity_id);
					$attributes_ids = $wpdb->get_results( $query );
					if( !empty($attributes_ids) ) {
						foreach( $attributes_ids as $attributes_id ) {
							if ( !empty( $attributes_id->attribute_id ) ) {
								$attribute_def = wpshop_attributes::getElement( $attributes_id->attribute_id, '"valid"', 'id' );
								if( $attribute_def->frontend_input != 'hidden') {
									if( !empty($attribute_def) && !empty($address[ $attribute_def->code ]) && $attribute_def->frontend_input != 'hidden' ) {
										$tmp_array[ $attribute_def->code]['label'] =  stripslashes( __( $attribute_def->frontend_label , 'wpshop' ) );

										if( $attribute_def->frontend_verification == 'country' ) {
											$tmp_array[ $attribute_def->code]['value'] =  ( !empty($countries[ $address[ $attribute_def->code] ]) ) ? stripslashes( __( $countries[ $address[ $attribute_def->code] ], 'wpshop' ) ) : stripslashes( $address[ $attribute_def->code ] );
										}
										elseif( in_array( $attribute_def->frontend_input, array('select', 'checkbox') ) ) {
											$query = $wpdb->prepare( 'SELECT label FROM '. WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $address[ $attribute_def->code] );
											$value = $wpdb->get_var( $query );
											$tmp_array[ $attribute_def->code]['value'] = ( !empty($value) ) ? stripslashes(  __( $value, 'wpshop' ) ) : '';
										}
										else {
											$tmp_array[ $attribute_def->code]['value'] = stripslashes(   __( $address[ $attribute_def->code ], 'wpshop' ) );
										}
									}
								}
							}
						}
						$address = $tmp_array;
					}
				}
				foreach( $address as $element_code => $element_value ) {
					if( is_array($element_value) ) {
						$output .= '<span class="wps-'.$element_code.'"><strong>' .stripslashes( $element_value['label'] ). ' :</strong> ' .stripslashes( $element_value['value'] ). '</span>';
					}
					else {
						$output .= '<span class="wps-'.$element_code.'">' .stripslashes( $element_value ). '</span>';
					}
				}
			}

		}
		return $output;
	}

	/**
	 * Get all addresses for current customer for display
	 *
	 * @param integer $address_type_id The current identifier of address type -> attribute_set_id
	 * @param string $address_type A string allowing to display
	 *
	 * @return string The complete html output for customer addresses
	 */
	function get_addresses_by_type( $address_type_id, $address_type_title, $args = array() ) {
		global $wpdb;
		/**	Get current customer addresses list	*/
		if ( is_admin() ) {
			$post = !empty( $_GET['post'] ) ? (array) $_GET['post'] : array();
			$post = get_post( $post );
			if ( !empty($post->post_parent) ) {
				$customer_id = $post->post_parent;
			}
			else {
				$customer_id = $post->post_author;
			}
		}
		else {
			$customer_id = get_current_user_id();
		}

		$query = $wpdb->prepare("
				SELECT ADDRESSES.ID
				FROM " . $wpdb->posts . " AS ADDRESSES
					INNER JOIN " . $wpdb->postmeta . " AS ADDRESSES_META ON (ADDRESSES_META.post_id = ADDRESSES.ID)
				WHERE ADDRESSES.post_type = %s
					AND ADDRESSES.post_parent = %d
				AND ADDRESSES_META.meta_key = %s
				AND ADDRESSES_META.meta_value = %d",
				WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, $customer_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_attribute_set_id', $address_type_id);
		$addresses = $wpdb->get_results($query);
		$addresses_list = '';


		/**	Initialize	*/
		$tpl_component = array();
		$tpl_component['CUSTOMER_ADDRESS_TYPE_TITLE'] = ( !empty($args) && !empty($args['first']) && $args['first'] ) ? __('Your address', 'wpshop') : $address_type_title;
		$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
		$tpl_component['ADDRESS_BUTTONS'] = '';
		if( count($addresses) > 0 ) {
			$tpl_component['ADD_NEW_ADDRESS_LINK'] = get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))) . (strpos(get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))), '?')===false ? '?' : '&amp;'). 'action=add_address&type=' .$address_type_id;
		}
		else {
			$tpl_component['ADD_NEW_ADDRESS_LINK'] = get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))) . (strpos(get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))), '?')===false ? '?' : '&amp;'). 'action=add_address&type=' .$address_type_id .'&first';
		}
		$tpl_component['ADDRESS_TYPE'] = ( !empty($address_type_title) && ($address_type_title == __('Shipping address', 'wpshop'))) ? 'shipping_address' : 'billing_address';
		$tpl_component['ADD_NEW_ADDRESS_TITLE'] = sprintf(__('Add a new %s', 'wpshop'), ( ( !empty($args) && !empty($args['first']) && $args['first'] ) ? __('address', 'wpshop') : $address_type_title ));


		/**	Read customer list	*/
		if( count($addresses) > 0 ) {
			/**	Get the fields for addresses	*/
			$address_fields = wps_address::get_addresss_form_fields_by_type($address_type_id);
			$first = true;
			$tpl_component['ADDRESS_COMBOBOX_OPTION'] = '';
			$nb_of_addresses = 0;
			foreach ( $addresses as $address ) {
				// Display the addresses
				/** If there isn't address in SESSION we display the first address of list by default */
				if ( empty($_SESSION[$tpl_component['ADDRESS_TYPE']]) && $first && !is_admin() ) {
					$address_id = $address->ID;
					if ( !is_admin() ) {
						$_SESSION[$tpl_component['ADDRESS_TYPE']] = $address->ID;
					}
				}
				else {
					$address_id = ( !empty($_SESSION[$tpl_component['ADDRESS_TYPE']]) )  ? $_SESSION[$tpl_component['ADDRESS_TYPE']] : '';
				}
				$address_selected_infos = get_post_meta($address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
				$address_infos = get_post_meta($address->ID, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);


				if ( !empty($address_infos) ) {

					$tpl_component['ADDRESS_ID'] = $address->ID;
					/** If no address was selected, we select the first of the list **/
					$tpl_component['CUSTOMER_ADDRESS_CONTENT'] = self::display_an_address($address_fields, $address_selected_infos, $address_id);
					$tpl_component['ADDRESS_BUTTONS'] = wpshop_display::display_template_element('addresses_box_actions_button_edit', $tpl_component);
					$tpl_component['choosen_address_LINK_EDIT'] = get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))) . (strpos(get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))), '?')===false ? '?' : '&') . 'action=editAddress&amp;id='.$address_id;
					$tpl_component['DEFAULT_ADDRESS_ID'] = $address_id;
					$tpl_component['ADRESS_CONTAINER_CLASS'] = ' wpshop_customer_adress_container_' . $address->ID;
					$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = wpshop_display::display_template_element('display_address_container', $tpl_component);
					if ( empty($tpl_component['CUSTOMER_ADDRESS_CONTENT']) ) {
						$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = '<span style="color:red;">'.__('No data','wpshop').'</span>';
					}

					$tpl_component['ADDRESS_COMBOBOX_OPTION'] .= '<option value="' .$address->ID. '" ' .( ( !empty($_SESSION[$tpl_component['ADDRESS_TYPE']]) && $_SESSION[$tpl_component['ADDRESS_TYPE']] == $address->ID) ? 'selected="selected"' : null). '>' . (!empty($address_infos['address_title']) ? $address_infos['address_title'] : $address_type_title) . '</option>';
					$nb_of_addresses++;
				}
				$first = false;
			}
			$tpl_component['ADDRESS_COMBOBOX'] = '';
			if ( !is_admin() ) {
				$tpl_component['ADDRESS_COMBOBOX'] = (!empty($tpl_component['ADDRESS_COMBOBOX_OPTION']) && ($nb_of_addresses > 1)) ? wpshop_display::display_template_element('addresses_type_combobox', $tpl_component) : '';
			}
		}
		else {
			if ( !empty($args) && !empty($args['first']) && $args['first'] ) {
				$tpl_component['ADDRESS_TYPE'] = 'first_address';
			}
			$tpl_component['ADDRESS_ID'] = 0;
			$tpl_component['DEFAULT_ADDRESS_ID'] = 0;
			$tpl_component['ADDRESS_COMBOBOX'] = '';
			$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = sprintf( __('You don\'t have any %s, %splease create a new one%s', 'wpshop'), ( (!empty($args) && !empty($args['first']) && $args['first']) ? __('address', 'wpshop') : strtolower($address_type_title) ) , '<a href="' . $tpl_component['ADD_NEW_ADDRESS_LINK'] . '" >', '</a>' );
		}

		$tpl_component['ADDRESS_BUTTONS'] .= wpshop_display::display_template_element('addresses_box_actions_button_new_address', $tpl_component);
		if ( !empty($args['only_display']) && ($args['only_display'] == 'yes') ) {
			$tpl_component['ADDRESS_BUTTONS'] = '';
		}

		$addresses_list .= wpshop_display::display_template_element('display_addresses_by_type_container', $tpl_component);



		return $addresses_list;
	}

	/**
	 * Construction et affichage du formulaire de création/modification d'une adresse / Build and return queried address form
	 *
	 * @param integer $address_type_id L'identifiant du type d'adresse a éditer (facturation/livraison/etc) / Address type identifier (billing/shipping/...).
	 * @param integer $address_id Optionnal. Si fournit correspond à l'identifiant de l'adresse à éditer / If given: identifier of the address we want to update.
	 * @param integer $customer_id Optinnal. Si fournit corrspond à l'identifiant du client pour lequel créer/éditer l'adresse / If given: Identifier of customer to create/update address for.
	 *
	 * @return array Un tableau contenant l'affihage du formulaire en premier index et le titre du forulaire en second index.
	 */
	function loading_address_form( $address_type_id, $address_id = '', $customer_id = '' ) {
		$first_address_checking = false;
		// $customer_id = wps_customer_ctr::get_customer_id_by_author_id( $user_id );
		if ( ! empty( $address_id ) ) {
			$title = __( 'Edit your address', 'wpshop' );
		} elseif ( $address_type_id ) {
			$billing_option = get_option( 'wpshop_billing_address' );

			$addresses = self::get_addresses_list( $customer_id );
			$list_addresses = ( ! empty( $addresses[ $billing_option['choice'] ] ) ) ? $addresses[ $billing_option['choice'] ] : array();
			$first_address_checking = ( empty( $list_addresses ) ) ? true : false;

			$title = __( 'Add a new address', 'wpshop' );
		}

		ob_start();
		require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, 'common', 'address', 'form' ) );
		$form = ob_get_clean();

		return array( $form, $title );
	}

	/**
	 * Generate an array with all fields for the address form construction. Classified by address type.
	 * @param $typeof
	 * @return array
	 */
	public static function get_addresss_form_fields_by_type ( $typeof, $id ='' ) {
		$submit_billing_and_shipping_info = !empty( $_POST['submitbillingAndShippingInfo'] ) ? sanitize_key( $_POST['submitbillingAndShippingInfo'] ) : '';

		$address = array();
		$all_addresses = array();
		$attribute = !empty( $_POST['attribute'] ) ? (array) $_POST['attribute'] : array();
		/*	Get the attribute set details in order to build the product interface	*/

		$atribute_set_details = wpshop_attributes_set::getAttributeSetDetails($typeof, "'valid'");
		if ( !empty($atribute_set_details) ) {
			foreach ($atribute_set_details as $productAttributeSetDetail) {
				$address = array();
				$group_name = $productAttributeSetDetail['name'];

				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute) {
						if(!empty($attribute->id)) {
							if ( !empty( $submit_billing_and_shipping_info ) ) {
								$value = $attribute[$typeof][$attribute->data_type][$attribute->code];
							}
							else {
								$value = wpshop_attributes::getAttributeValueForEntityInSet($attribute->data_type, $attribute->id, wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS), (int)$id, array('intrinsic' => $attribute->is_intrinsic, 'backend_input' => $attribute->backend_input));
							}
							$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute, $value, array() );
							$attribute_output_def['id'] = 'address_' . $typeof . '_' .$attribute_output_def['id'];
							$address[str_replace( '-', '_', sanitize_title($group_name) ).'_'.$attribute->code] = $attribute_output_def;
						}
					}
				}

				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['name'] = $group_name;
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['content'] = $address;
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['id'] = str_replace('-', '_', sanitize_title($group_name));
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['attribute_set_id'] = $productAttributeSetDetail['attribute_set_id'];
			}
		}
		return $all_addresses;
	}



	/**
	 * Treat the differents fields of form and classified them by form
	 *
	 * @param  integer $attribute_set_id     L'identifiant du groupe d'attribut à utiliser.
	 * @param  integer $address_id_to_copy   [description]
	 * @param  array   $address_info_to_copy [description]
	 *
	 * @return array                        [description]
	 */
	public static function save_address_infos( $attribute_set_id, $address_id_to_copy = 0, $address_info_to_copy = array(), $customer_id = false, $post_id = false ) {
		if ( empty( $address_info_to_copy ) ) {
			$address_info_to_copy = (array) $_POST;
		}

		$adress_save_the_first = ! empty( $address_info_to_copy['wps-address-save-the-first'] ) ? sanitize_text_field( $address_info_to_copy['wps-address-save-the-first'] ) : '';

		$attribute = (array) $address_info_to_copy['attribute'];

		$type_of_form = (int) $address_info_to_copy['type_of_form'];
		$current_item_edited = ! empty( $address_info_to_copy['attribute'][ $attribute_set_id ]['item_id'] ) ? (int) wpshop_tools::varSanitizer( $address_info_to_copy['attribute'][ $attribute_set_id ]['item_id'] ) : null;
		$current_attribute_set_id = ! empty( $address_info_to_copy['current_attribute_set_id'] ) ? (int) $address_info_to_copy['current_attribute_set_id'] : '';
		$shipping_to_billing = ! empty( $address_info_to_copy['wps-shipping-to-billing'] ) ? sanitize_text_field( $address_info_to_copy['wps-shipping-to-billing'] ) : '';
		$shipping_to_billing_id = ! empty( $address_info_to_copy['wps-shipping-to-billing-id'] ) ? (int) $address_info_to_copy['wps-shipping-to-billing-id'] : $address_id_to_copy;

		// Create or update the post address
		// @TODO : REQUEST.
		$customer_id_from_cookie = ! empty( $_COOKIE ) && ! empty( $_COOKIE['wps_current_connected_customer'] ) ? (int) $_COOKIE['wps_current_connected_customer'] : wps_customer_ctr::get_customer_id_by_author_id( get_current_user_id() );
		$customer_id = ( ! empty( $customer_id ) ? (int) $customer_id : ( ! empty( $_REQUEST['user']['customer_id'] ) ? (int) $_REQUEST['user']['customer_id'] : $customer_id_from_cookie ) );
		$post_id = ! empty( $post_id ) ? (int) $post_id : ( ! empty( $_REQUEST['post_ID'] ) ? (int) $_REQUEST['post_ID'] : 0 );

		$post_address = array(
			'post_title' => ! empty( $attribute ) && ! empty( $attribute[ $attribute_set_id ] ) && ! empty( $attribute[ $attribute_set_id ]['varchar'] ) && ! empty( $attribute[ $attribute_set_id ]['varchar']['address_title'] ) ? $attribute[ $attribute_set_id ]['varchar']['address_title'] : '',
			'post_status' => 'draft',
			'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
		);

		$edit_other_thing = true;

		if ( empty( $current_item_edited ) && ( empty( $current_attribute_set_id ) || $current_attribute_set_id != $attribute_set_id ) ) {
			$post_address['post_author'] = get_current_user_id();
			$post_address['post_parent'] = empty( $customer_id ) && ! empty( $post_id ) ? $post_id : $customer_id;
			$current_item_edited = wp_insert_post( $post_address );
			if ( is_admin() ) {
				$attribute[ $attribute_set_id ]['item_id'] = $current_item_edited;
			}
		} else {
			$post_address['ID'] = $current_item_edited;
			wp_update_post( $post_address );
		}

		/* Shipping to billing save */
		if ( ! empty( $shipping_to_billing ) ) {
			$wps_shipping_to_billing = array( 'wps-shipping-to-billing' => $shipping_to_billing );

			if ( ! empty( $shipping_to_billing_id ) ) {
				$wps_shipping_to_billing['wps-shipping-to-billing-id'] = $shipping_to_billing_id;
			}
			update_post_meta( $current_item_edited, 'wps-shipping-to-billing', $wps_shipping_to_billing );
		}

		// Update the post_meta of address.
		update_post_meta( $current_item_edited, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, $attribute_set_id );

		foreach ( $attribute[ $attribute_set_id ] as $type => $type_content ) {
			$attribute_not_to_do = array();
			if ( is_array( $type_content ) ) {
				foreach ( $type_content as $code => $value ) {
					$attribute_def = wpshop_attributes::getElement( $code, "'valid'", 'code' );
					if ( ! empty( $attribute_def->_need_verification ) && 'yes' === $attribute_def->_need_verification ) {
						$code_verif = $code . '2';
						$attribute_not_to_do[] = $code_verif;
						if ( ! empty( $attributes[ $code_verif ] ) ) {
							unset( $attributes[ $code_verif ] );
						}
					}
					if ( ! in_array( $code, $attribute_not_to_do, true ) ) {
						$attributes[ $code ] = $value;
					}
				}
			}
		}

		$attributes = apply_filters( 'wps-address-coordinate-calculation', $attributes );

		$result = wpshop_attributes::setAttributesValuesForItem( $current_item_edited, $attributes, false, '' );
		$result['current_id'] = $current_item_edited;

		if ( ! empty( $result['current_id'] ) ) {
			$billing_option = get_option( 'wpshop_billing_address' );
			if ( ! empty( $billing_option ) && ! empty( $billing_option['choice'] ) && $billing_option['choice'] === $attribute_set_id ) {
				$_SESSION['billing_address'] = $result['current_id'];
			} else {
				$_SESSION['shipping_address'] = $result['current_id'];
			}
		}

		return $result;
	}

	/**
	 * Display the differents forms fields
	 * @param string $type : Type of address
	 * @param string $first : Customer first address ?
	 * @param string $referer : Referer website page
	 * @param string $admin : Display this form in admin panel
	 */
	public static function display_form_fields($type, $id = '', $first = '', $referer = '', $special_values = array(), $options = array(), $display_for_admin = array(), $other_customer = '' ) {
		global $wpshop, $wpshop_form, $wpdb;

		$choosen_address = get_option('wpshop_billing_address');
		$shipping_address = get_option('wpshop_shipping_address_choice');
		$output_form_fields = $form_model = '';

		$user_id = ( ! empty($other_customer) ) ? $other_customer : get_current_user_id();

		if ( empty($type) ) {
			$type = $choosen_address['choice'];
		}

		$result = wps_address::get_addresss_form_fields_by_type($type, $id);

		/** Check if it's shipping or billing **/
		if ( $type == $choosen_address['choice'] ) {
			$form_model =  ( !empty($choosen_address['display_model']) ) ? $choosen_address['display_model'] : null;
		}
		elseif( $type == $shipping_address['choice'] ) {
			$form_model = ( !empty($shipping_address['display_model']) ) ? $shipping_address['display_model'] : null;
		}


		$form = $result[$type];
		// Take the post id to make the link with the post meta of  address
		$values = array();
		// take the address informations
		$current_item_edited = !empty($id) ? (int)$id : null;

		foreach ( $form as $group_id => $group_fields) {
			if ( empty($options) || (!empty($options) && ($options['title']))) $output_form_fields .= '<h2>'.__( $group_fields['name'], 'wpshop' ).'</h2>';
			$end_line_indicator = 0; $fields_limit_per_line = -1;
			foreach ( $group_fields['content'] as $key => $field) {
				$attribute_def = wpshop_attributes::getElement( $field['name'], $element_status = "'valid'", $field_to_search = 'code' );
				/** Grid opening **/
				if ( !empty($form_model) && !empty($form_model[$group_id]) && in_array('wps-attribute-end-line-'.$end_line_indicator, $form_model[$group_id]) && $fields_limit_per_line == -1 ) {
					$current_key = array_search( 'wps-attribute-end-line-'.$end_line_indicator, $form_model[$group_id] );
					$current_attribute_key = array_search( 'attribute_'.$attribute_def->id, $form_model[$group_id] );

					if( $current_attribute_key > $current_key ) {
// 						/** Define limit **/
// 						if( in_array('wps-attribute-end-line-' . ($end_line_indicator + 1 ) , $form_model[$group_id]) ) {
// 							$next_key = array_search( 'wps-attribute-end-line-'.( $end_line_indicator + 1 ), $form_model[$group_id] );
// 							$fields_limit_per_line = $next_key - $current_key - 1;
// 							$fields_limit_per_line = ( $fields_limit_per_line > 6 )  ? 6 : $fields_limit_per_line;
// 						}
// 						else {
// 							$current_key = array_search( 'wps-attribute-end-line-'.$end_line_indicator, $form_model[$group_id] );
// 							$end_tab = count($form_model[$group_id]) - 1;
// 							$fields_limit_per_line = $end_tab - $current_key - 1;
// 							$fields_limit_per_line = ( $fields_limit_per_line > 6 )  ? 6 : $fields_limit_per_line;
// 						}
						if ( !empty($fields_limit_per_line) && $fields_limit_per_line != -1 ) {
							if ( $fields_limit_per_line == 1 ) {
								$output_form_fields .= '<div class="wps-row">';
							}
							else {
								$output_form_fields .= '<div class="wps-row wps-gridwrapper' .$fields_limit_per_line. '-padded">';
							}
						}
					}
				}

				if ( empty($options['field_to_hide']) || !is_array($options['field_to_hide']) || !in_array( $key, $options['field_to_hide'] ) ) {
					$attributeInputDomain = 'attribute[' . $type . '][' . $field['data_type'] . ']';
					// Test if there is POST var or if user have already fill his address infos and fill the fields with these infos
					$referer = !empty($_POST['referer']) ? sanitize_text_field( $_POST['referer'] ) : '';
					if( !empty($referer) ) {
						$value = !empty( $_POST[$form['id']."_".$field['name']] ) ? sanitize_text_field( $_POST[$form['id']."_".$field['name']] ) : '';
					}



					// Fill Automaticly some fields when it's an address creation
					switch ( $field['name']) {
						case 'address_title' :
							if( empty($field['value']) ) {
								/** Count Billing and shipping address **/
								$billing_address_count = $shipping_address_count = 1;
								if ( get_current_user_id() != 0 ) {
									$addresses = get_posts( array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, 'post_parent' => get_current_user_id(), 'post_status' => 'draft') );
									if ( !empty($addresses) ) {
										foreach( $addresses as $address ) {
											$address_type = get_post_meta( $address->ID, '_wpshop_address_attribute_set_id', true);
											if ( !empty($address_type) ){
												if ( !empty( $shipping_address_choice['choice'] ) && $address_type == $shipping_address_choice['choice'] ) {
													$shipping_address_count++;
												}
												else{
													$billing_address_count++;
												}
											}
										}
									}
								}
								$field['value'] = ( $type == $choosen_address['choice'] ) ? __('Billing address', 'wpshop').( ($billing_address_count > 1) ? ' '.$billing_address_count : '' ) : __('Shipping address', 'wpshop').( ($shipping_address_count > 1) ? ' '.$shipping_address_count : '');

							}
							break;
						case 'address_last_name' :
							if( empty($field['value']) ) {
								$usermeta_last_name = get_user_meta( $user_id, 'last_name', true);
								$field['value'] = ( !empty($usermeta_last_name) ) ? $usermeta_last_name :  '';
							}
							break;
						case 'address_first_name' :
							if( empty($field['value']) ) {
								$usermeta_first_name = get_user_meta( $user_id, 'first_name', true);
								$field['value'] = ( !empty($usermeta_first_name) ) ? $usermeta_first_name :  '';
							}
							break;
						case 'address_user_email' :
							if( empty($field['value']) ) {
								$user_infos = get_userdata( $user_id );
								$field['value'] = ( !empty($user_infos) && !empty($user_infos->user_email) ) ? $user_infos->user_email :  '';
							}
							break;
						default :
							$field['value'] = ( !empty($field['value']) ) ? $field['value'] : '';
							break;
					}



					/** Fill fields if $_POST exist **/
					if ( !empty( $attribute[$type][$field['data_type']][$field['name']] ) ) {
						$field['value'] = sanitize_text_field( $_POST['attribute'][$type][$field['data_type']][$field['name']] );
					}


					if( $field['name'] == 'address_title' && !empty($first) && $type == __('Billing address', 'wpshop') ) {
						$value = __('Billing address', 'wpshop');
					}
					elseif( $field['name'] == 'address_title' && !empty($first) && $type == __('Shipping address', 'wpshop') ) {
						$value = __('Shipping address', 'wpshop');
					}

					if ( !empty($special_values[$field['name']]) ) {
						$field['value'] = $special_values[$field['name']];
					}

					$template = 'wpshop_account_form_input';
					if ( $field['type'] == 'hidden' ) {
						$template = 'wpshop_account_form_hidden_input';
					}

					if ( $field['frontend_verification'] == 'country' ) {
						$field['type'] = 'select';
						/** display a country list **/
						$countries_list = unserialize(WPSHOP_COUNTRY_LIST);
						$possible_values = array_merge(array('' => __('Choose a country')), $countries_list);

						$limit_countries_list = get_option( 'wpshop_limit_country_list' );
						$default_country_choice = get_option( 'wpshop_country_default_choice' );
						if ( !empty($limit_countries_list) ) {
							$possible_values = array();
							if ( count($limit_countries_list) > 1 ) {
								$possible_values[''] = __('Choose a country');
							}
							foreach( $limit_countries_list as $country_code) {
								if ( !empty($countries_list) && !empty($countries_list[$country_code]) ) {
									$possible_values[$country_code] = $countries_list[$country_code];
								}
							}
						}

						$field['value'] = ( !empty($default_country_choice) && array_key_exists($default_country_choice, $possible_values ) ) ? $default_country_choice : '';
						$field['possible_value'] = $possible_values;
						$field['valueToPut'] = 'index';

					}



					$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
					$input_tpl_component = array();

					//$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
					$input_tpl_component['CUSTOMER_FORM_INPUT_NAME'] = ( !empty($field['name']) ) ? $field['name'] : '';
					$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = ( $field['type'] != 'hidden' ) ? stripslashes( __( $field['label'], 'wpshop' ) ) . ( ( $field['required'] == 'yes' ) ? ' <em>*</em>' : '') : '';
					$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL_OPTIONS'] = ' for="' . $field['id'] . '"';
					$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain);
					//$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);


					$output_form_fields .= wpshop_display::display_template_element('wps_address_field', $input_tpl_component, array(), 'wpshop');


					unset($input_tpl_component);

					if ( $field['_need_verification'] == 'yes' ) {
						$field['name'] = $field['name'] . '2';
						$field['id'] = $field['id'] . '2';
						$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
						$input_tpl_component = array();
						$input_tpl_component['CUSTOMER_FORM_INPUT_NAME'] = $field['name'];
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = __( $field['label'], 'wpshop' ) . ( ( ($field['required'] == 'yes' && !is_admin()) || ($field['name'] == 'address_user_email' && is_admin()) ) ? ' <span class="required">*</span>' : '');
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL_OPTIONS'] = ' for="' . $field['id'] . '"';
						$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = sprintf( __('Confirm %s', 'wpshop'), strtolower( __( $field['label'], 'wpshop' ) ) ). ( ($field['required'] == 'yes') && !is_admin() ? ' <span class="required">*</span>' : '');
						$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain) . $field['options'];
						//$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);
						$output_form_fields .= wpshop_display::display_template_element('wps_address_field', $input_tpl_component, array(), 'wpshop');
						unset($input_tpl_component);
					}
				}

				/** Grid closing **/
				if( $fields_limit_per_line != -1 && !empty($fields_limit_per_line) ) {
					$fields_limit_per_line--;
					if( $fields_limit_per_line == 0 ) {
						$output_form_fields .= '</div>';
						$fields_limit_per_line = -1;
						$end_line_indicator++;
					}
				}
			}
		}

		if ( $type ==  $choosen_address['choice'] ) {
			$output_form_fields .= '<input type="hidden" name="billing_address" value="'.$choosen_address['choice'].'" />';
		}
		$shipping_address_options = get_option('wpshop_shipping_address_choice');
		if ( $type ==  $shipping_address_options['choice'] ) {
			$output_form_fields .= '<input type="hidden" name="shipping_address" value="' .$shipping_address_options['choice']. '" />';
		}
		$output_form_fields .= '<input type="hidden" name="type_of_form" value="' .$type. '" /><input type="hidden" name="attribute[' .$type. '][item_id]" value="' .$current_item_edited. '" />';

		$output_form_fields .= ( $user_id != get_current_user_id() ) ? '<input type="hidden" name="user[customer_id]" value="' .$user_id. '" />' : '';

		if ( empty($first) ) $output_form_fields = wpshop_display::display_template_element('wpshop_customer_addresses_form', array('CUSTOMER_ADDRESSES_FORM_CONTENT' => $output_form_fields, 'CUSTOMER_ADDRESSES_FORM_BUTTONS' => ''));

		return $output_form_fields;
	}

	/**
	 * Copy Shipping address datas in billing corresponding datas
	 * @param integer $shipping_address_type_id
	 * @param integer $billing_address_type_id
	 */
	function shipping_to_billing( $shipping_address_type_id, $billing_address_type_id ) {
		global $wpdb;
		$tmp_array = array();
		$tmp_array = !empty( $_REQUEST ) ? (array)$_REQUEST : array();

		$billing_fields = array();
		if( !empty($tmp_array) && !empty($tmp_array['attribute']) && !empty($tmp_array['attribute'][$shipping_address_type_id]) ) {
			foreach ($tmp_array['attribute'][$shipping_address_type_id] as $key => $attribute_group ) {
				if ( is_array($attribute_group) ) {
					foreach( $attribute_group as $field_name => $value ) {
						$attribute_def = wpshop_attributes::getElement( sanitize_text_field( $field_name ), "'valid'", 'code' );
						if( !empty($attribute_def) ) {
							$query = $wpdb->prepare( 'SELECT * FROM '. WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE status = %s AND attribute_id = %d AND attribute_set_id = %s', 'valid', (int)$attribute_def->id, sanitize_text_field( $billing_address_type_id ) );
							$attribute_exist = $wpdb->get_var( $query );
							if ( !empty($attribute_exist) ) {
								$tmp_array['attribute'][$billing_address_type_id][$attribute_def->data_type][$field_name] = sanitize_text_field( $value );
							}
						}
					}
				}
			}
		}

		return $tmp_array;
	}

	/**
	 * Affichage du shortcode générant la liste des adresses clients
	 *
	 * @version 1.4.4.3
	 *
	 * @param  array $args Les arguments passés au shortcode.
	 */
	function shortcode_callback_display_addresses( $args ) {
		$customer_id = ! empty( $args ) && ! empty( $args['cid'] ) ? (int) $args['cid'] : wps_customer_ctr::get_customer_id_by_author_id( get_current_user_id() );
		return $this->display_addresses_interface( $customer_id );
	}

	/**
	 * Display Address Inteface
	 *
	 * @param integer $customer_id
	 * @param boolean $admin_display
	 * @param integer $order_id
	 *
	 * @return string
	 */
	function display_addresses_interface( $customer_id = '', $admin_display = false, $order_id = '' ) {
		$output = $extra_class = $billing_address_display = $shipping_address_display = $first_address_checking = '';

		if ( $customer_id ) {
			$shipping_option = get_option( 'wpshop_shipping_address_choice' );
			$billing_option = get_option( 'wpshop_billing_address' );

			/** Shipping address */
			// Check if is only downloadable else display address.
			$cart_is_downloadable = false;
			if ( ! empty( $_SESSION['cart'] ) && ! empty( $_SESSION['cart']['order_items'] ) ) {
				foreach ( $_SESSION['cart']['order_items'] as $c ) {
					$product = wpshop_products::get_product_data( $c['item_id'] );
					// Check if it's a variation and check the parent product.
					if ( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION === get_post_type( $c['item_id'] ) ) {
						$parent_def = wpshop_products::get_parent_variation( $c['item_id'] );
						if ( ! empty( $parent_def ) && ! empty( $parent_def['parent_post_meta'] ) && ! empty( $parent_def['parent_post_meta']['is_downloadable_'] ) ) {
							$product['is_downloadable_'] = $parent_def['parent_post_meta']['is_downloadable_'];
						}
					}
					if ( ! empty( $product['is_downloadable_'] ) && ( strtolower( __( $product['is_downloadable_'], 'wpshop' ) ) === strtolower( __( 'Yes', 'wpshop' ) ) ) ) {
						$cart_is_downloadable = true;
					} else {
						$cart_is_downloadable = false;
						break;
					}
				}
			}

			$checkout_address_type = array(
				'shipping' => __( 'Shipping address', 'wpshop' ),
				'billing' => __( 'Billing address', 'wpshop' ),
			);
			$shipping_is_avalaible = true;
			foreach ( $checkout_address_type as $address_type => $address_title ) {
				$display_address_of_type = true;
				switch ( $address_type  ) {
					case 'shipping':
						if ( empty( $shipping_option ) || empty( $shipping_option['activate'] ) || $cart_is_downloadable ) {
							$display_address_of_type = false;
							$shipping_is_avalaible = false;
						} else {
							$address_type_id = $shipping_option['choice'];
						}
					break;
					case 'billing':
						$address_type_id = $billing_option['choice'];
					break;
				}

				if ( $display_address_of_type ) {
					$box_content = ! $admin_display ? self::display_address_interface_content( $address_type_id, $address_title, '', $address_type, $customer_id ) : '';
					$first_address_checking = ( empty( $box_content ) && ( 'shipping' === $address_type ) ) ? true : false;

					ob_start();
					require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, ( $admin_display ? 'backend' : 'frontend' ), 'address', 'container' ) );
					$output .= ob_get_contents();
					ob_end_clean();
				}
			}
		}

		return $output;
	}

	/**
	 * [display_address_interface_content description]
	 *
	 * @param  [type]  $address_type_id  [description].
	 * @param  [type]  $address_title    [description].
	 * @param  [type]  $selected_address [description].
	 * @param  [type]  $type             [description].
	 * @param  string  $customer_id      [description].
	 * @param  boolean $admin_display    [description].
	 * @param  string  $order_id         [description].
	 *
	 * @return [type]                    [description]
	 */
	public static function display_address_interface_content( $address_type_id, $address_title, $selected_address = null, $type, $customer_id = '', $admin_display = false, $order_id = '' ) {
		$customer_id_from_cookie = ! empty( $_COOKIE ) && ! empty( $_COOKIE['wps_current_connected_customer'] ) ? (int) $_COOKIE['wps_current_connected_customer'] : wps_customer_ctr::get_customer_id_by_author_id( get_current_user_id() );
		$customer_id = ! empty( $customer_id ) && ! empty( $customer_id ) && is_int( (int) $customer_id ) ? (int) $customer_id : $customer_id_from_cookie;

		$select_id = ( ! empty( $type ) && ( 'shipping' === $type ) ) ?  'shipping_address_address_list' : 'billing_address_address_list';
		$output = '';

		if ( ! empty( $address_type_id ) ) {
			$addresses = self::get_addresses_list( $customer_id );
			$list_addresses = ( ! empty( $addresses[ $address_type_id ] ) ) ? $addresses[ $address_type_id ] : array();
			if ( empty( $list_addresses ) ) {
				$form = self::display_form_fields( $address_type_id );
			}

			ob_start();
			require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, ( $admin_display ? 'backend' : 'frontend' ), 'address', 'content' ) );
			$output .= ob_get_contents();
			ob_end_clean();
		}

		return $output;
	}


	/**
	 * AJAX - Display addresses list for a given element
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function display_addresses_list() {
		$_wpnonce = ( !empty( $_REQUEST['_wpnonce'] ) ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'display_addresses_list' ) )
			die();

		$post_id = (int) $_POST['post_id'];
		$addresses = $this->get_addresses_list( $post_id );
		require_once( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "addresses" ) );
		die();
	}

	/**
	 * AJAX - Display selected address for a given element
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function display_address() {
		$_wpnonce = ( !empty( $_REQUEST['_wpnonce'] ) ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_address_display_an_address' ) )
			die();

		$adress_id = (int) $_POST['address_id'];
		$address_post_meta = get_post_meta( $adress_id, '_wpshop_address_metadata', true);
		$address_type_post_meta = get_post_meta( $adress_id, '_wpshop_address_attribute_set_id', true);
		if( !empty($address_post_meta) && !empty($address_type_post_meta) ) {
			$addresses_list[$address_type_post_meta][ $adress_id ] = $address_post_meta;
		}
		$address_open = true;
		foreach ( $addresses_list as $address_type => $addresses_list_by_type ) :
			foreach ( $addresses_list_by_type as $address_id => $address ) :
				require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "address" ) );
			endforeach;
		endforeach;

		die();
	}

	/**
	 * AJAX - Display address form or a given element. CHeck if there are many types of addresses in order to choose address type dropdown or directly address form
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function display_address_adding_form() {
		global $wpdb;

		$_wpnonce = ( !empty( $_REQUEST['_wpnonce'] ) ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'display_address_adding_form' ) )
			die();

		$address_id = 0;
		$post_ID = (int)$_POST[ 'post_id' ];
		$element = get_post( $post_ID );
		$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s ", $element->post_type);
		$element_cpt = $wpdb->get_var( $query );
		$attached_addresses = get_post_meta( $element_cpt, '_wpshop_entity_attached_address', true );

		if ( !empty( $attached_addresses ) ) {
			if ( count( $attached_addresses ) == 1 ) {
				$address_type_id = $attached_addresses[ 0 ];
				require_once( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "address", "form" ) );
			}
			else {
				require_once( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "addresses", "types" ) );
			}
		}
		else {
			printf( __( 'No addresses are attached to this element type %s', 'wpeo_geoloc' ), $element->post_type);
		}

		die();
	}

	/**
	 * AJAX - Display address form for address edition
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function load_address_edition_form() {
		$_wpnonce = ( !empty( $_REQUEST['_wpnonce'] ) ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_address_edition_form_load' ) )
			die();

		$address_id = (int)$_POST[ 'element_id' ];
		$post_ID = (int)$_POST[ 'post_id' ];
		$address_type_id = get_post_meta( $address_id, '_wpshop_address_attribute_set_id', true);
		$wpeogeo_adress = !empty( $_POST[ 'wpeogeo-address-type-chosen-for-creation' ] ) ? (int) $_POST[ 'wpeogeo-address-type-chosen-for-creation' ] : 0;
		if ( empty( $address_id ) && empty( $address_type_id ) && !empty( $wpeogeo_adress ) ) {
			$address_type_id = $wpeogeo_adress;
		}

		require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, WPS_LOCALISATION_TEMPLATES_MAIN_DIR, "backend", "address", "form" ) );
		die();
	}

	/**
	 * AJAX - Delete an address
	 */
	function wps_delete_an_address() {
		$_wpnonce = ( !empty( $_REQUEST['_wpnonce'] ) ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_delete_an_address' ) )
			die();

		$status = false; $response = '';
		$address_id = ( !empty( $_POST['address_id']) ) ? (int) $_POST['address_id'] : null;
		if( !empty($address_id) ) {
			/** Check if user is author of address **/
			$address = get_post( $address_id );
			if( !empty($address) && !empty($address->post_type) && $address->post_type == WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS && !empty($address->post_author) && $address->post_author == get_current_user_id() ) {
				wp_delete_post( $address_id, true );
				$status = true;
			}
		}
		echo json_encode( array('status' => $status, 'response' => $response) );
		die();
	}

	/**
	 * AJAX - Relad Address Interface in new checkout tunnel
	 */
	function wps_reload_address_interface() {
		check_ajax_referer( 'wps_reload_address_interface' );

		global $wpdb;
		$status = false; $response = '';
		$address_type = !empty($_POST['address_type']) ? (int) $_POST['address_type'] : null;
		$selected_address = !empty($_POST['address_id']) ? (int) $_POST['address_id'] : null;

		if ( !empty( $address_type ) ) {
			$billing_option = get_option( 'wpshop_billing_address' );
			if ( !empty($billing_option) && !empty($billing_option['choice']) && $billing_option['choice'] == $address_type ) {
				$type = 'billing';
			}
			else {
				$type = 'shipping';
			}
			$query = $wpdb->prepare( 'SELECT name FROM '.WPSHOP_DBT_ATTRIBUTE_SET .' WHERE id = %d ', $address_type );
			$address_title = __( $wpdb->get_var( $query ), 'wpshop' );
			$response = self::display_address_interface_content( $address_type, $address_title, $selected_address, $type );
			$status = true;
		}
		echo json_encode( array( 'status' => $status, 'response' => $response ) );
		die();
	}

	/**
	 * AJAX - Load address form in Modal Box
	 */
	function wps_load_address_form() {
		$address_type_id = ( ! empty( $_POST['address_type_id'] ) ) ? sanitize_text_field( $_POST['address_type_id'] ) : '';
		check_ajax_referer( 'wps_load_address_form_' . $address_type_id );

		$address_id = ( ! empty( $_POST['address_id'] ) ) ? (int) $_POST['address_id'] : '';
		$customer_id_from_cookie = ! empty( $_COOKIE ) && ! empty( $_COOKIE['wps_current_connected_customer'] ) ? (int) $_COOKIE['wps_current_connected_customer'] : wps_customer_ctr::get_customer_id_by_author_id( get_current_user_id() );

		wp_die( wp_json_encode( self::loading_address_form( $address_type_id, $address_id, $customer_id_from_cookie ) ) );
	}

	/**
	 * AJAX - Function for save address
	 */
	function wps_save_address() {
		check_ajax_referer( 'wps_save_address' );
		global $wpshop, $wpdb;

		$status = false;
		$result = $address_type = $same_address_type = '';

		$adress_save_the_first = ! empty( $_POST['wps-address-save-the-first'] ) ? sanitize_text_field( $_POST['wps-address-save-the-first'] ) : '';
		$attribute = ! empty( $_POST['attribute'] ) ? (array) $_POST['attribute'] : '';

		foreach ( $attribute as $id_group => $attribute_group ) {
			$address_type = $id_group;
			$group = wps_address::get_addresss_form_fields_by_type( $id_group );
			foreach ( $group as $attribute_sets ) {
				foreach ( $attribute_sets as $attribute_set_field ) {
					$validate = $wpshop->validateForm( $attribute_set_field['content'], $attribute[ $id_group ], 'address_edition' );
				}

				if ( $validate ) {
					$shipping_save = self::save_address_infos( $id_group );

					$wps_shipping_to_billing = ! empty( $_POST['wps-shipping-to-billing'] ) ? sanitize_text_field( $_POST['wps-shipping-to-billing'] ) : '';
					if ( ! empty( $wps_shipping_to_billing ) ) {
						$billing_option = get_option( 'wpshop_billing_address' );
						$shipping_option = get_option( 'wpshop_shipping_address_choice' );

						$address_info_to_copy = self::shipping_to_billing( $shipping_option['choice'], $billing_option['choice'] );
						self::save_address_infos( $billing_option['choice'], $shipping_save['current_id'], $address_info_to_copy );
						$same_address_type = $billing_option['choice'];
					}

					$status = true;
					if ( ! empty( $adress_save_the_first ) ) {
						$query = $wpdb->prepare( 'SELECT name FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE id = %d ', $address_type );
						$address_title = __( $wpdb->get_var( $query ), 'wpshop' );
						$result = self::display_address_interface_content( $address_type, $address_title, $shipping_save['current_id'], $adress_save_the_first );
					}
				} else {
					if ( ! empty( $wpshop->errors ) ) {
						$result = '<div class="wps-alert wps-alert-error">' . __( 'Some errors have been detected', 'wpshop' ) . ' : <ul>';
						foreach ( $wpshop->errors as $error ) {
							$result .= '<li>' . $error . '</li>';
						}
						$result .= '</ul></div>';
					}
				}
			}
		}

		wp_die( wp_json_encode( array( $status, $result, $address_type, $same_address_type ) ) );
	}

}
