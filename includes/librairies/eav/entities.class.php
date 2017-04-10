<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Define utilities to manage entities
 *
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage librairies
 */

/*
 * Check if file is include. No direct access possible with file url
 */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Define utilities to manage entities
 *
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_entities {

	public static $entities_cache = array();

	/**
	 * Define the custom post type for entities into wpshop
	 */
	public static function create_wpshop_entities_type() {
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, array(
			'labels' => array(
				'name'					=> __( 'Entities', 'wpshop' ),
				'singular_name' 		=> __( 'Entity', 'wpshop' ),
				'add_new_item' 			=> __( 'Add new entity', 'wpshop' ),
				'add_new' 				=> __( 'Add new entity', 'wpshop' ),
				'add_new_item' 			=> __( 'Add new entity', 'wpshop' ),
				'edit_item' 			=> __( 'Edit entity', 'wpshop' ),
				'new_item' 				=> __( 'New entity', 'wpshop' ),
				'view_item' 			=> __( 'View entity', 'wpshop' ),
				'search_items' 			=> __( 'Search entities', 'wpshop' ),
				'not_found' 			=> __( 'No entities found', 'wpshop' ),
				'not_found_in_trash' 	=> __( 'No entities found in Trash', 'wpshop' ),
				'parent_item_colon' 	=> '',
			),
			'supports' 				=> array( 'title', 'editor', 'page-attributes' ),
			'public' 				=> true,
			'has_archive'			=> true,
			'publicly_queryable' 	=> false,
			'show_in_nav_menus' 	=> false,
			'show_in_menu' 			=> WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES,
			'exclude_from_search'	=> true,
			'capabilities'			=> array(
				'edit_post' 			 => 'wpshop_view_dashboard',
		        'edit_posts' 			 => 'wpshop_view_dashboard',
		        'edit_others_posts' 	 => 'wpshop_view_dashboard',
		        'publish_posts' 		 => 'wpshop_view_dashboard',
		        'read_post' 			 => 'wpshop_view_dashboard',
		        'read_private_posts' 	 => 'wpshop_view_dashboard',
				'delete_posts'			 => 'delete_product'
			),
		));
	}

	/**
	 * Define the different metaboxes for entities management
	 */
	public static function add_meta_boxes( ) {
		global $post;
		/** Metabox allowing to choose the different part displayed into an element of an entities	*/
		add_meta_box(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES . '_support_section', __('Part to display', 'wpshop'), array('wpshop_entities', 'wpshop_entity_support_section'), WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, 'normal', 'high');

		/** Metabox allowgin to choose a custome rewrite for an entiy	*/
		add_meta_box(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES . '_rewrite', __('Rewrite for entity', 'wpshop'), array('wpshop_entities', 'wpshop_entity_rewrite'), WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, 'normal', 'high');

		if ( !in_array( $post->post_name, unserialize( WPSHOP_DEFAULT_CUSTOM_TYPES ) ) ) {
			/** Display or not address in admin menu	*/
			add_meta_box(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES . '_admin_menu_display', __('Display in admin menu', 'wpshop'), array('wpshop_entities', 'wpshop_display_entity_in_admin_menu'), WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, 'side', 'low');
		}

		/** Join address to entity	*/
		if ( !in_array( $post->post_name, array( WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS ) ) ) {
			add_meta_box(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES . '_join_address_to_entity', __('Join Address to this entity', 'wpshop'), array('wpshop_entities', 'wpshop_join_address_to_entity'), WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, 'side', 'low');
		}
	}

	/**
	 * The metabox content for entity type support section in entity edition page
	 *
	 * @param object $post The entity type being edited
	 */
	public static function wpshop_entity_support_section( $entity ) {
		$output = '';
		$support_list = unserialize(WPSHOP_REGISTER_POST_TYPE_SUPPORT);

		$current_entity_params = get_post_meta($entity->ID, '_wpshop_entity_params', true);

		unset($input_def);$input_def=array();
		$input_def['type'] = 'checkbox';

		foreach ( $support_list as $support ) {
			$input_def['id'] = 'wpshop_entity_support';
			$input_def['name'] = $support;
			$input_def['possible_value'] = array($support);
			if ( !empty($current_entity_params['support']) && in_array($support, $current_entity_params['support']) ) {
				$input_def['value'] = $support;
			}

			$output .= '<p class="wpshop_entities_support_type wpshop_entities_support_type_' . $support . '" >' . wpshop_form::check_input_type($input_def, 'wpshop_entity_support') . ' <label for="'.$input_def['id'].'_'.$support.'">' . __($support, 'wpshop') . '</label></p>';
		}
		$output .= '<p class="wpshop_cls" ></p>';

		echo $output;
	}

	/**
	 * The metabox content for entity type rewrite
	 *
	 * @param unknown_type $entity
	 */
	public static function wpshop_entity_rewrite( $entity ) {
		$output = '';

		$current_entity_params = get_post_meta($entity->ID, '_wpshop_entity_params', true);

		unset($input_def);$input_def=array();
		$input_def['type'] = 'text';
		$input_def['id'] = 'wpshop_entity_rewrite';
		$input_def['name'] = 'wpshop_entity_rewrite[slug]';
		$input_def['value'] = (!empty($current_entity_params['rewrite']['slug']) ? $current_entity_params['rewrite']['slug'] :'');

		$output .= '<p><label for="'.$input_def['id'].'">' . __('Choose how this entity will be rewrite in front side. If you let it empty default will be taken', 'wpshop') . '</label><br/>' . wpshop_form::check_input_type($input_def) . '</p>';

		echo $output;
	}

	public static function wpshop_display_entity_in_admin_menu() {
		$checked = '';
		$post = !empty( $_GET['post'] ) ? (int) $_GET['post'] : 0;
		if ( !empty($post) ) {
			$current_entity_params = get_post_meta( $post, '_wpshop_entity_params', true);

			if ( !empty($current_entity_params['display_admin_menu']) ) {
				$checked = 'checked ="checked"';
				}
		}
		$output = '<input type="checkbox" id="wpshop_display_in_admin_menu" name="wpshop_display_in_admin_menu" ' .$checked. '/><label for="wpshop_display_in_admin_menu"> '.__('Display in admin menu', 'wpshop').'</label>';
		echo $output;
	}

	/**
	 * Save custom information for entity type
	 */
	public static function save_entity_type_custom_informations() {
		$post_id = !empty($_POST['post_ID']) ? intval( sanitize_key($_POST['post_ID']) ) : null;
		$post_support = !empty($_POST['wpshop_entity_support']) && is_array( (array)$_POST['wpshop_entity_support'] ) ? (array)$_POST['wpshop_entity_support'] : null;
		$wpshop_entity_rewrite = !empty($_POST['wpshop_entity_rewrite']) ? (array) $_POST['wpshop_entity_rewrite'] : null;
		$wpshop_entity_display_menu = !empty($_POST['wpshop_display_in_admin_menu']) ? sanitize_key($_POST['wpshop_display_in_admin_menu']) : null;

		$address_type = !empty ($_POST['address_type']) ? (array) $_POST['address_type'] : null;
		if ( isset($address_type) ) {
			$save_array = array();
			foreach ( $address_type as $key=>$value ) {
				$save_array[] = intval( sanitize_text_field($value) );
			}
			update_post_meta( $post_id, '_wpshop_entity_attached_address', $save_array );
		}

		if ( get_post_type($post_id) == WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES ) {
			update_post_meta($post_id, '_wpshop_entity_params', array('support' => $post_support, 'rewrite' => $wpshop_entity_rewrite, 'display_admin_menu'=>$wpshop_entity_display_menu));
			flush_rewrite_rules();
		}
	}

	/**
	 * Permite to join one or several address to an entity
	 */
	public static function wpshop_join_address_to_entity () {
		global $wpdb;
		// Select the id of the entity address
		$query = $wpdb->prepare('SELECT id FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_name = %s', WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS);
		$entity_id = $wpdb->get_var( $query );
		//Get the Post_meta
		$attached_address_values = get_post_meta( intval(wpshop_tools::varSanitizer( (!empty($_GET['post']) ? $_GET['post'] : '') )), '_wpshop_entity_attached_address', true );
		//Select and Display all addresses type
		$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d', $entity_id);
		$addresses = $wpdb->get_results( $query);
		$output = '';
		foreach ( $addresses as $address ) {
			$output .= '<p><input type="checkbox" id="' .$address->name.'_'.$address->id.'" name="address_type[' .$address->name. ']" value="'.$address->id.'" ' .( ( !empty($attached_address_values) && in_array( $address->id, $attached_address_values) ) ? 'checked="checked"' : '' ). ' /> <label for="' .$address->name.'_'.$address->id.'">'.$address->name.'</label></p>';
		}
		echo $output;
	}

	/**
	 * Create the new custom post type from define entities
	 */
	public static function create_wpshop_entities_custom_type() {
		/*
		 * Retrieve existing entities
		 */
		$entities = query_posts(array(
			'post_type' 	=> WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES,
			'post_status'	=> 'publish'
		));

		/*
		 * Read the entity list for custom type declaration
		 */
		if ( !empty($entities) ) {
			foreach ( $entities as $entity ) {
				$wpshop_builtin_types = unserialize( WPSHOP_DEFAULT_CUSTOM_TYPES );
				if ( !empty( $entity->post_name ) && !in_array( $entity->post_name, $wpshop_builtin_types ) ) {
					$current_entity_params = get_post_meta($entity->ID, '_wpshop_entity_params', true);
					if ( !empty($current_entity_params['display_admin_menu']) ) {
						$show_in_menu = WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES;
					}
					else {
						$show_in_menu = false;
					}
					$post_type_params = array(
						'labels' => array(
							'name'					=> __( $entity->post_title , 'wpshop' ),
							'singular_name' 		=> __( $entity->post_title, 'wpshop' ),
							'add_new_item' 			=> sprintf( __( 'Add new %s', 'wpshop' ), $entity->post_title),
							'add_new' 				=> sprintf( __( 'Add new %s', 'wpshop' ), $entity->post_title),
							'add_new_item' 			=> sprintf( __( 'Add new %s', 'wpshop' ), $entity->post_title),
							'edit_item' 			=> sprintf( __( 'Edit %s', 'wpshop' ), $entity->post_title),
							'new_item' 				=> sprintf( __( 'New %s', 'wpshop' ), $entity->post_title),
							'view_item' 			=> sprintf( __( 'View %s', 'wpshop' ), $entity->post_title),
							'search_items' 			=> sprintf( __( 'Search %s', 'wpshop' ), $entity->post_title),
							'not_found' 			=> sprintf( __( 'No %s found', 'wpshop' ), $entity->post_title),
							'not_found_in_trash' 	=> sprintf( __( 'No %s found in Trash', 'wpshop' ), $entity->post_title),
							'parent_item_colon' 	=> '',
						),
						'description' 			=> $entity->post_content,
						'supports' 				=> !empty($current_entity_params['support']) ? $current_entity_params['support'] : array(),
						'public' 				=> true,
						'has_archive'			=> true,
						'publicly_queryable' 	=> true,
						'show_in_nav_menus' 	=> false,
						'show_in_menu' 			=> $show_in_menu,
						'exclude_from_search'	=> false,
						'rewrite'				=> !empty($current_entity_params['rewrite']) ? $current_entity_params['rewrite'] : array(),
						'hierarchical'			=> true,
					);
					register_post_type($entity->post_name, $post_type_params );
				}

				/**	Add basic metabox */
				add_action('add_meta_boxes', array('wpshop_entities', 'add_meta_boxes_to_custom_types'), 1);

				/** Call action for saving custom informations	*/
				add_action('save_post', array('wpshop_entities', 'save_entities_custom_informations'));

			}

			add_filter( 'map_meta_cap', array('wpshop_entities', 'map_meta_cap'), 10, 4 );
			/*
			 * Reset query for security reasons
			 */
			wp_reset_query();
		}
	}

	/**
	 * Manage capabilities for current entity
	 * @param array $caps
	 * @param string $cap
	 * @param integer $user_id
	 * @param array $args
	 */
	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( !empty($args) ) {
			$post = get_post($args[0]);
			if ( false && !empty($post) && is_object($post) && ($post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES) && (($post->post_name == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)  || ($post->post_name == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS) || ($post->post_name == WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS)) && ($cap == 'delete_product') ) {
				$caps = 'wpshop_view_dashboard';
			}
		}

		return $caps;
	}


	/**
	 * Add metaboxes to the custom post types defined by entities
	 */
	public static function add_meta_boxes_to_custom_types( $post ) {
		global $post,
			   $wpdb;

		/*
		 * Add a specific metabox for customer
		 */
		if ($post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS) {
			//add_meta_box($post->post_type . '_customers_purchase', __('Orders', 'wpshop'), array('wpshop_orders', 'display_orders_for_customer'), $post->post_type, 'normal');
			//add_meta_box ($post->post_type.'_customer_note', __('Customers Notes' , 'wpshop'), array('wpshop_customer', 'display_notes_for_customer'), $post->post_type, 'side');
		}

		/*
		 * Product are managed from another place
		 */
		if ( $post->post_type != WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
			/*
			 * Get the attribute set list for current entity
			 */
			$attribute_set_list = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code($post->post_type));

			/*
			 * Check if a attribute set is defined for current entity
			 */
			$attribute_set_id = get_post_meta($post->ID, sprintf(WPSHOP_ATTRIBUTE_SET_ID_META_KEY, $post->post_type), true);

			if(((count($attribute_set_list) == 1) || ((count($attribute_set_list) > 1) && !empty($attribute_set_id)))){
				if((count($attribute_set_list) == 1) || empty($attribute_set_id)){
					$attribute_set_id = $attribute_set_list[0]->id;
				}

				/*
				 * Get attribute list for the current entity
				 */
				$currentTabContent = wpshop_attributes::entities_attribute_box($attribute_set_id, $post->post_type, $post->ID);

				$fixed_box_exist = false;
				/*
				 * Read the different element for building output for current entity
				 */
				if ( !empty($currentTabContent['box']) && is_array($currentTabContent['box']) ) {
					foreach ($currentTabContent['box'] as $boxIdentifier => $boxTitle) {
						if (!empty($currentTabContent['box'][$boxIdentifier.'_backend_display_type']) &&( $currentTabContent['box'][$boxIdentifier.'_backend_display_type']=='movable-tab')) {
							add_meta_box($post->post_type . '_' . $boxIdentifier, __($boxTitle, 'wpshop'), array('wpshop_entities', 'meta_box_content'), $post->post_type, 'normal', 'default', array('currentTabContent' => $currentTabContent['boxContent'][$boxIdentifier]));
						}
						else $fixed_box_exist = true;
					}
				}
				if ($fixed_box_exist && $post->post_type != WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ) {
					add_meta_box($post->post_type . '_fixed_tab', __('Informations', 'wpshop'), array('wpshop_entities', 'meta_box_content_datas'), $post->post_type, 'normal', 'high', array('currentTabContent' => $currentTabContent));
				}
			}
			elseif (count($attribute_set_list) > 1) {
				$input_def['id'] = $post->post_type.'_attribute_set_id';
				$input_def['name'] = $post->post_type.'_attribute_set_id';
				$input_def['value'] = '';
				$input_def['type'] = 'select';
				$input_def['possible_value'] = $attribute_set_list;

				$input_def['value'] = '';
				foreach ($attribute_set_list as $set) {
					if( $set->default_set == 'yes' ) {
						$input_def['value'] = $set->id;
					}
				}

				$currentTabContent = '
		<ul class="attribute_set_selector" >
			<li class="attribute_set_selector_title_select" ><label for="title" >' . sprintf(__('Choose a title for the %s', 'wpshop'), get_the_title(wpshop_entities::get_entity_identifier_from_code($post->post_type))) . '</label></li>
			<li class="attribute_set_selector_group_selector" ><label for="' . $input_def['id'] . '" >' . sprintf(__('Choose an attribute group for this %s', 'wpshop'), get_the_title(wpshop_entities::get_entity_identifier_from_code($post->post_type))) . '</label>&nbsp;'.wpshop_form::check_input_type($input_def).'</li>
			<li class="attribute_set_selector_save_instruction" >' . sprintf(__('Save the %s with the "Save draft" button on the right side', 'wpshop'), get_the_title(wpshop_entities::get_entity_identifier_from_code($post->post_type))) . '</li>
			<li class="attribute_set_selector_after_save_instruction" >' . __('Once the group chosen, the different attribute will be displayed here', 'wpshop') . '</li>
		</ul>';

				add_meta_box($post->post_type . '_attribute_set_selector',sprintf( __('%s attributes', 'wpshop'), get_the_title(wpshop_entities::get_entity_identifier_from_code($post->post_type))), array('wpshop_entities', 'meta_box_content'), $post->post_type, 'normal', 'high', array('currentTabContent' => $currentTabContent));
			}

		}
	}

	/**
	 * Define the content of metaboxes for entities. This function is used for creating one metabox for each attribute set section when configuration is set for this kind of display
	 *
	 * @param object $post The current post definition
	 * @param array $metaboxArgs Parameters list passed through wordpress "add_meta_box" function
	 */
	public static function meta_box_content($post, $metaboxArgs) {
		/*	Add the extra fields defined by the default attribute group in the general section	*/
		echo '<div class="wpshop_extra_field_container" >' . $metaboxArgs['args']['currentTabContent'] . '</div>';
	}


	/**
	 * Define metabox content for attribute set section configured to be displayed as tabs
	 *
	 * @param object $post The current post definition
	 * @param array $metaboxArgs Parameters list passed through wordpress "add_meta_box" function
	 */
	public static function meta_box_content_datas($post, $metaboxArgs) {

		$currentTabContent = $metaboxArgs['args']['currentTabContent'];

		echo '<div id="fixed-tabs" class="wpshop_tabs wpshop_detail_tabs entities_attribute_tabs ' . $post->post_type . '_attribute_tabs" >
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

		if (!empty($currentTabContent['boxMore'])) {
			echo $currentTabContent['boxMore'];
		}
		echo '</div>';
	}

	/**
	 * Save informations for current entity
	 */
	public static function save_entities_custom_informations( $post_id ) {
    global $wpdb, $wpshop_account;

		$edit_other_thing = !empty( $_POST['edit_other_thing'] ) ? (int) $_POST['edit_other_thing'] : 0;

		if ( ( ( !empty($post_id) && empty( $edit_other_thing ) ) || ( !empty($post_id) && !(bool)$edit_other_thing ) )
				 && ( get_post_type( $post_id ) != WPSHOP_NEWTYPE_IDENTIFIER_ORDER )  ) {
			$current_post_type = get_post_type( $post_id );
			$current_post_type_text = !empty( $_REQUEST[$current_post_type . '_attribute_set_id'] ) ? sanitize_text_field( $_REQUEST[$current_post_type . '_attribute_set_id'] ) : '';
			/*	Vérification de l'existence de l'envoi de l'identifiant du set d'attribut	*/
			if	( !empty($current_post_type_text) ) {
				$attribute_set_id = intval( $current_post_type_text );
				$attribet_set_infos = wpshop_attributes_set::getElement($attribute_set_id, "'valid'", 'id');

				if ( $attribet_set_infos->entity == sanitize_key( $current_post_type ) ) {
					/*	Enregistrement de l'identifiant du set d'attribut associé à l'entité	*/
					update_post_meta($post_id, sprintf(WPSHOP_ATTRIBUTE_SET_ID_META_KEY, $current_post_type), $attribute_set_id);

					/*	Enregistrement de tous les attributs	*/
					$current_post_type_attributes = !empty($_REQUEST[$current_post_type . '_attribute']) ? (array)$_REQUEST[$current_post_type . '_attribute'] : null;
					if ( isset($current_post_type_attributes) ) {
						/*	Traduction des virgule en point pour la base de donnees	*/
						if ( !empty($current_post_type_attributes['decimal']) ) {
							foreach($current_post_type_attributes['decimal'] as $attributeName => $attributeValue){
								if(!is_array($attributeValue)){
									$current_post_type_attributes['decimal'][$attributeName] = str_replace(',','.',$current_post_type_attributes['decimal'][$attributeName]);
								}
							}
						}
						/*	Enregistrement des valeurs des différents attributs	*/
						wpshop_attributes::saveAttributeForEntity($current_post_type_attributes, wpshop_entities::get_entity_identifier_from_code($current_post_type), $post_id, WPSHOP_CURRENT_LOCALE);

						/*	Enregistrement des valeurs des attributs dans les metas de l'entité => Permet de profiter de la recherche native de wordpress	*/
						$productMetaDatas = array();
						foreach ( $current_post_type_attributes as $attributeType => $attributeValues ) {
							foreach ( $attributeValues as $attributeCode => $attributeValue ) {
								$productMetaDatas[$attributeCode] = $attributeValue;
							}
						}

						update_post_meta($post_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);
					}
				}
			}

			$attribute = !empty($_REQUEST['attribute']) ? (array) $_REQUEST['attribute'] : null;
			$post_id = !empty($_REQUEST['post_ID']) ? (int) $_REQUEST['post_ID'] : null;
			if ( isset($attribute) ) {
				$current_id = array();
				foreach ( $attribute as $key=>$values ) {
					$ad_id = '';
					$addresses_id = get_post_meta($post_id, '_wpshop_attached_address', true);
					if ( !empty($addresses_id) ) {
						foreach ( $addresses_id as $address_id) {
							$address_type = get_post_meta($address_id, '_wpshop_address_attribute_set_id', true);
							if ($address_type == $key) {
								$ad_id = $address_id;
							}
						}
					}
					if( !empty( $ad_id ) ) {
						// @TODO : REQUEST
						// $_REQUEST['item_id'] = $ad_id;
						$result = wps_address::save_address_infos( $key );
						$current_id[] = $result['current_id'];
					}
				}
				if( !empty( $current_id ) ) {
					update_post_meta ($post_id, '_wpshop_attached_address', $current_id);
				}
			}
			else {
				$current_id = array();

				$address_type = !empty($_REQUEST['address_type']) ? (array) $_REQUEST['address_type'] : null;
				if ( isset($address_type) ) {
					foreach ( $address_type as $key=>$value ) {
						$current_id[] = $value;
					}
				}
				update_post_meta ($post_id, '_wpshop_entity_attached_address', $current_id);
			}
		}

		flush_rewrite_rules();
	}


	/**
	 * Get existant entities list
	 *
	 * @return array The entire entities list
	 */
	public static function get_entities_list() {
		$entities_list = array();
		$entities = query_posts(array(
			'post_type' 		=> WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES,
			'post_status' 		=> 'publish',
			'orderby'			=> 'menu_order',
			'order'				=> 'ASC',
			'posts_per_page' 	=> '-1',
		));

		if ( !empty($entities) ) {
			foreach ($entities as $entity_index => $entity) {
				$entities_list[$entity->ID] = $entity->post_title;
			}
		}
		wp_reset_query();

		return $entities_list;
	}

	/**
	 * Retrieve the identifier for an entity from its code
	 *
	 * @param string $code The entity code we want to get identifier for
	 * @param string $post_status Optionnal The status of the entity we want to get
	 *
	 * @return integer The entity identifier that match to given parameters
	 */
	public static function get_entity_identifier_from_code($code, $post_status = 'publish', $entity_code = WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES) {
		if ( ! isset( self::$entities_cache[$entity_code][$code][$post_status] ) ) {
			global $wpdb;
			self::$entities_cache[$entity_code][$code][$post_status] = null;
			$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type=%s AND post_status=%s AND post_name=%s ORDER BY menu_order ASC", $entity_code, $post_status, $code);
			self::$entities_cache[$entity_code][$code][$post_status] = $wpdb->get_var($query);
		}

		return self::$entities_cache[$entity_code][$code][$post_status];
	}

	/**
	 * Duplicate an element of wpshop_entity type
	 *
	 * @param integer $post_id
	 */
	public static function duplicate_entity_element($post_id) {
		global $wpdb;

		/*	Get current post information	*/
		$post_infos = get_post( $post_id, ARRAY_A );
		/*	Set new information for post that will be created	*/
		unset($post_infos['ID']);
		$post_infos['post_date'] = current_time('mysql', 1);
		$post_infos['post_date_gmt'] = current_time('mysql', 1);
		$post_infos['post_modified'] = current_time('mysql', 1);
		$post_infos['post_modified_gmt'] = current_time('mysql', 1);
		$post_infos['post_status'] = 'draft';
		$post_infos['post_title'] = $post_infos['post_title'] . ' - ' . sprintf(__('Copy of %s', 'wpshop'), $post_id);
		$numcopy = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_name LIKE '" . $post_infos['post_name'] . "-%'");
		$numcopy += 1;
		$post_infos['post_name'] = $post_infos['post_name'] . '-' . $numcopy;
		$post_infos['guid'] = NULL;

		/*	Insert the new post	*/
		$last_post = wp_insert_post($post_infos);

		/*	If there is no error then duplicate meta informations	*/
		if ( is_int($last_post) && !empty($last_post) ) {
			$meta_creation = true;

			$current_post_meta = get_post_meta($post_id);
			foreach ( $current_post_meta as $post_meta_key => $post_meta_value ) {
				$meta_is_array = ( !empty( $post_meta_value[0] ) && wpshop_tools::is_serialized( $post_meta_value[0] ) ) ? unserialize( $post_meta_value[0] ) : '';
				$meta_real_value = (is_array($meta_is_array) ? $meta_is_array : $post_meta_value[0]);
				$meta_creation = update_post_meta($last_post, $post_meta_key, $meta_real_value);
			}
			/*	Duplicate element taxonomy	*/
			/*	Check the taxonomy to get	*/
			switch ( get_post_type($post_id) ) {
				case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
					$taxonomy = WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES;
					break;
				default:
					$taxonomy = '';
					break;
			}
			$post_taxonomies = wp_get_post_terms( $post_id,  $taxonomy);
			foreach ( $post_taxonomies as $post_taxonomy ) {
				wp_set_post_terms( $last_post, $post_taxonomy->term_id, $taxonomy, true);
			}

			/*	Create a post meta allowing to know if the element has been duplicated from another	*/
			update_post_meta($last_post, '_wpshop_duplicate_element', $post_id);

			$new_element_link = '<a class="wpshop_cls wpshop_duplicate_entity_element_link" href="' . admin_url('post.php?post=' . $last_post . '&action=edit') . '" >' . __('Go on the new element edit page', 'wpshop') . '</a>';
			if ( $meta_creation ) {
				return array('true', '<br/>' . $new_element_link, $last_post );
			}
			else {
				return array('true', '<br/>' . __('Some errors occured while duplicating meta information, but element has been created.', 'wpshop') . ' ' . $new_element_link);
			}
		}

		return array('false', __('An error occured while duplicating element', 'wpshop'));
	}


	/**
	 * Define custom columns header display in post_type page for wpshop entities
	 *
	 * @param string $columns The default column for the post_type given in second parameter
	 * @param string $post_type The post type of the currentpage
	 *
	 * @return array The new columns to display for the post_type given in second parameter
	 */
	public static function custom_columns_header($columns, $post_type) {
		global $wpdb;

		/*	Get the attribute list to display as custom column for the current entity	*/
		$query = $wpdb->prepare("SELECT code, frontend_label FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATT WHERE status=%s AND is_used_in_admin_listing_column=%s AND entity_id=%d", 'valid', 'yes', self::get_entity_identifier_from_code($post_type));
		$attributes_list = $wpdb->get_results($query);
		$wpshop_custom_columns = array();
		foreach ( $attributes_list as $attribute ) {
			$wpshop_custom_columns[$attribute->code] = __($attribute->frontend_label, 'wpshop');
		}

		/*	Check the current entity to display custom column correctly. Add the custom column into default column list	*/
		switch ( $post_type ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
				$columns = array_merge(array(
					'cb' => '<input type="checkbox" />',
					'picture' => __('Picture', 'wpshop'),
					'title' => __('Product name', 'wpshop')
				), $wpshop_custom_columns);


				$columns['author'] = __('Creator', 'wpshop');
				$columns['date'] = __('Date', 'wpshop');

				break;
		}

		return $columns;
	}

	/**
	 * Define custom columns content display in post_type page for wpshop entities
	 *
	 * @param string $columns The default column for the post_type given in second parameter
	 * @param integer $post_id The current post identifier to get information for display
	 */
	public static function custom_columns_content($column, $post_id) {
		$post_type = get_post_type($post_id);

		switch ( $post_type ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
				$column_content = '<strong>-</strong>';
				$product = wpshop_products::get_product_data($post_id);

				switch ($column) {
					case 'picture' :
						$column_content = get_the_post_thumbnail( $post_id, 'thumbnail');
					break;
					case "product_stock":
						if( !empty($product['product_stock']) )
							$column_content = (int)$product['product_stock'].' '.__('unit(s)','wpshop');
					break;

					case "product_price":
						if( !empty($product['product_price']) )
							$column_content = wpshop_prices::get_product_price( $product, 'price_display', 'complete_sheet');
					break;

					case "tx_tva":
						if( !empty($product['product_price']) )
							$column_content = number_format($product[$column],2,'.', ' ').' %';
					break;
					default:
						if ( !empty($product[$column]) ) {
							$attribute_prices = unserialize(WPSHOP_ATTRIBUTE_PRICES);
							if ( in_array($column, $attribute_prices) ) {
								$column_content = number_format($product[$column],2,'.', ' ').' '.wpshop_tools::wpshop_get_currency();

							}
							else
								$column_content = $product[$column];
						}
						break;
				}
				break;
			default:
				$column_content = '';
				break;
		}

		echo $column_content;
	}

	/**
	 * Display a form allowing to create an entity from frontend with a shortcode
	 * @param array $shortcode_args The different parameters for the shortocde: the field list for the form, different parameters for the entity to create
	 */
	public static function wpshop_entities_shortcode( $shortcode_args ) {
		global $wpshop_account, $wpdb;
		$output = $form_content = '';
		$quick_entity_add_button = !empty( $_POST['quick_entity_add_button'] ) ? (int) $_POST['quick_entity_add_button'] : 0;
		if ( get_current_user_id() > 0 ) {
			if ( !empty( $quick_entity_add_button ) ) {
				$attributes = array();
				$attribute = !empty($_POST['attribute']) ? (array) $_POST['attribute'] : array();
				foreach ( $attribute as $attribute_type => $attribute ) {
					foreach ( $attribute as $attribute_code => $attribute_value ) {
						$attributes[$attribute_code] = $attribute_value;
					}
				}
				$title = sanitize_text_field($_POST['wp_fields']['post_title']);
				$result = wpshop_products::addProduct($title, '', $attributes, 'complete');
			}

			if ( empty($shortcode_args['attribute_set_id']) || empty($shortcode_args['post_type']) ) {
				$output = __('This form page is invalid because no set or type or content is defined. Please contact administrator with this error message', 'wpshop');
			}
			else {
				$entity_identifier = wpshop_entities::get_entity_identifier_from_code($shortcode_args['post_type']);
				$attribute_set_def = wpshop_attributes_set::getElement($shortcode_args['attribute_set_id'], "'valid'");

				if ( empty($entity_identifier) || empty($attribute_set_def) || ($entity_identifier != $attribute_set_def->entity_id) ) {
					$output = __('This form page is invalid because type and set are not linked. Please contact administrator with this error message', 'wpshop');
				}
				else {
					/** Display wordpress fields */
					foreach ( explode(', ', $shortcode_args['fields']) as $field_name ) {
						$label = '';
						switch ( $field_name ) {
							case 'post_title':
								switch ( $shortcode_args['post_type'] ) {
									case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
										$label = __('Product title', 'wpshop');
										break;
									default:
										$label = __('Name', 'wpshop');
										break;
								}

								$field_type = 'text';
							break;
							case 'post_thumbnail':
								switch ( $shortcode_args['post_type'] ) {
									case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
										$label = __('Product picture', 'wpshop');
									break;
									default:
										$label = __('Default picture', 'wpshop');
									break;
								}

								$field_type = 'file';
							break;
						}

						if ( !empty( $label ) ) {
							$template_part = 'quick_entity_wp_internal_field_' . $field_type;
							$tpl_component = array();
							$tpl_component['WP_FIELD_NAME'] = $field_name;
							$tpl_component['WP_FIELD_VALUE'] = '';
							$input = wpshop_display::display_template_element($template_part, $tpl_component);
							unset($tpl_component);

							$template_part = 'quick_entity_wp_internal_field_output';
							$tpl_component = array();
							$tpl_component['ENTITY_TYPE_TO_CREATE'] = $shortcode_args['post_type'];
							$tpl_component['WP_FIELD_NAME'] = $field_name;
							$tpl_component['WP_FIELD_LABEL'] = $label;
							$tpl_component['WP_FIELD_INPUT'] = $input;
							$form_content .= wpshop_display::display_template_element($template_part, $tpl_component);
							unset($tpl_component);
						}
					}

					/** Display attributes fields	*/
					$query = $wpdb->prepare("
SELECT ATT.code
FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATT
	INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTR_DET ON ((ATTR_DET.status = 'valid') AND (ATTR_DET.attribute_id = ATT.id) AND (ATTR_DET.entity_type_id = ATT.entity_id))
	INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATT_GROUP ON ((ATT_GROUP.status = 'valid') AND (ATT_GROUP.attribute_set_id = ATTR_DET.attribute_set_id) AND (ATT_GROUP.id = ATTR_DET.attribute_group_id))
WHERE ATT.is_used_in_quick_add_form = %s
	AND ATT.status= %s
	AND ATT.entity_id = %d
	AND ATTR_DET.attribute_set_id = %d
GROUP BY ATT.code
ORDER BY ATT_GROUP.position, ATTR_DET.position"
						, 'yes', 'valid', wpshop_entities::get_entity_identifier_from_code($shortcode_args['post_type']), $shortcode_args['attribute_set_id']);
					$attribute_for_creation = $wpdb->get_results($query);
					foreach ( $attribute_for_creation as $attribute ) {
						$attr_field = wpshop_attributes::display_attribute( $attribute->code, 'frontend'/* (is_admin() ? 'admin' : 'frontend') */ );
						$form_content .= $attr_field['field'];
					}

					/**	Check if there are extra parameters	*/
					if ( !empty( $shortcode_args['extra_element'] ) ) {
						$extra_element = explode(', ', $shortcode_args['extra_element']);
						foreach ( $extra_element as $element) {
							$element_def = explode('!#wps#!', $element);
							$element_type = $element_def[0];
							$element_id = $element_def[1];

							if ( $element_type == WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS ) {
								$form_content .= '<div class="wpshop_entity_address_container">';
// 								$form_content .= $wpshop_account->display_form_fields($element_id, null, 'not');
								$form_content .= '</div><div class="wpshop_cls"></div>';
							}
						}
					}
				}
			}

			$template_part = 'quick_entity_add_form';
			$tpl_component = array();
			$tpl_component['ENTITY_TYPE'] = $shortcode_args['post_type'];
			$tpl_component['ENTITY_ATTRIBUTE_SET_ID'] = !empty( $shortcode_args['attribute_set_id'] ) ? $shortcode_args['attribute_set_id'] : 0;
			$tpl_component['NEW_ENTITY_FORM_DETAILS'] = $form_content;
			$tpl_component['ENTITY_QUICK_ADDING_FORM_NONCE'] = wp_create_nonce("wpshop_add_new_entity_ajax_nonce");
			$tpl_component['ENTITY_QUICK_ADD_BUTTON_TEXT'] = __($shortcode_args['button_text'], 'wpshop');

			/*	Ajout de la boite permettant d'ajouter des valeurs aux attributs de type liste deroulante a la volee	*/
			$dialog_title = __('New value', 'wpshop');
			$dialog_identifier = 'new_value_for_entity';
			$dialog_input_identifier = 'wpshop_new_attribute_option_value';
			ob_start();
			include(WPSHOP_TEMPLATES_DIR.'admin/add_new_element_dialog.tpl.php');
			$tpl_component['DIALOG_BOX'] = ob_get_contents();
			ob_end_clean();
			$tpl_component['DIALOG_BOX'] .= '<input type="hidden" name="wpshop_attribute_type_select_code" value="" id="wpshop_attribute_type_select_code" />';
			$tpl_component['DIALOG_BOX'] = '';
			$output = wpshop_display::display_template_element($template_part, $tpl_component, array(), 'wpshop');
			echo $output;
		}
		else {
			echo $wpshop_account->display_login_form();
		}
	}

	/**
	 * Create a new element for a entity type into database
	 * @param string $entity_type The type of element to create
	 * @param string $name The element name to create
	 * @param string $description A description for the element to create
	 * @param array $attributes A list containing all attributes defining the element to create
	 * @param array $extra_params A list of extra parameters for the element creation
	 * @return array The new entity identifier AND the status of attribute save with a messaege in case the save action failed
	 */
	public static function create_new_entity($entity_type, $name, $description, $attributes = array(), $extra_params = array()) {
		global $wpdb;

		/** Check if user is already connected	*/
		$user_id = function_exists('is_user_logged_in') && is_user_logged_in() ? get_current_user_id() : 'NaN';

		/** The arguments needed for a entity (post) creation	*/
		$entity_args = array(
			'post_type' 		=> $entity_type,
			'post_title' 		=> $name,
			'post_status' 		=> 'publish',
			'post_excerpt'		=> $description,
			'post_content' 		=> $description,
			'post_author' 		=> $user_id,
			'comment_status' 	=> 'closed'
		);

		/** Add the new product	*/
		$entity_id = wp_insert_post($entity_args);

		do_action( 'wps_entity_more_action' , $entity_id, $attributes);

		/** Update the attribute set id for the current product	*/
		if ( !empty($extra_params['attribute_set_id']) ) {
			$attribute_set_id = $extra_params['attribute_set_id'];
		}
		else {
			$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE status = %s AND entity_id = %d AND default_set = %s", 'valid', wpshop_entities::get_entity_identifier_from_code($entity_type) , 'yes');
			$attribute_set_id = $wpdb->get_var($query);
		}
		update_post_meta($entity_id, '_' . $entity_type . '_attribute_set_id', $attribute_set_id);

		$response = wpshop_attributes::setAttributesValuesForItem($entity_id, $attributes, true);

		return array($response, $entity_id);
	}


	/**
	 * Allows to create a new custom post type from a csv file, allowing to create default entities or import new entities
	 *
	 * @param string $identifier The custom post type identifier. This identifier is unique into database
	 *
	 * @return array The different response element for the request. $result: Boolean representing if creation is OK / $container: Where the result must be placed into output code / $output: The html content to output
	 */
	public static function create_cpt_from_csv_file( $identifier, $custom_file = '' ) {
		global $wpdb;
		$output = '';
		$container = '';
		$result = true;

		$custom_post_type_default_structure = array(
			'post_title' => 'mandatory',
			'post_name' => 'mandatory',
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES
		);

		/**	Check custom post type exsitance	*/
		$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s", $identifier);
		$custom_post_type_identifier = $wpdb->get_var($query);
		$container = 'wpshop_cpt_' . $identifier;

		$file_uri = !empty( $custom_file ) ? $custom_file : WPSHOP_TEMPLATES_DIR . 'default_datas/' . $identifier . '.csv';
		if ( is_file( $file_uri ) && empty($custom_post_type_identifier) ) {
			$csv_file_default_data = file($file_uri, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			$db_field_definition = explode( ";", $csv_file_default_data[0] );
			$db_datas_definition = explode( ";", $csv_file_default_data[1] );

			$has_error = false;
			$errors = array();
			foreach ( $custom_post_type_default_structure as $field_name => $field_default_value ) {
				if ( !in_array( str_replace( 'post_', '', $field_name ) , $db_field_definition ) ) {
					if ( $field_name == 'post_name' ) {
						$db_datas_definition[] = $identifier;
						$db_field_definition[] = str_replace( 'post_', '', $field_name );
					}
					else if ( $field_default_value == 'mandatory' ) {
						$has_error = true;
						$errors[] = $field_name;
					}
					else {
						$db_datas_definition[] = $field_default_value;
						$db_field_definition[] = str_replace( 'post_', '', $field_name );
					}
				}
			}

			if ( $has_error ) {
				$result = false;
				$output = sprintf( __('You have to fill %s, they are mandatory for custom type creation', 'wpshop'), implode(',', $errors) );
			}
			else {
				$custom_post_type_def = array();
				foreach ( $db_field_definition as $field_position => $field_name ) {
					$custom_post_type_def['post_' . $field_name] = $db_datas_definition[$field_position];
				}
				$new_custom_post_type = wp_insert_post( $custom_post_type_def );
				if ( is_int($new_custom_post_type) && !empty($new_custom_post_type) ) {
					$result = true;
				}

				$check_cpt = wpshop_entities::check_default_custom_post_type( $identifier, array(), $result, $custom_file );
				$output = $check_cpt[1];
			}
		}

		return array($result, $container, $output);
	}

	/**
	 * Check if a given custom post type exists into database for current installation
	 *
	 * @param string $identifier The custom post type identifier. This identifier is unique into database
	 * @param array $tpl_component An array with already existing template element (Allows to merge existing and new)
	 *
	 * @return array The different response element for the request. $has_error: A boolean result of request / $output: The complete html output for custom post type check / $tpl_componene: A mode complete list of element of templates
	 */
	public static function check_default_custom_post_type( $identifier, $tpl_component ) {
		global $wpdb;
		$has_error = false;

		/**	Check if custom post type exists	*/
		$query = $wpdb->prepare("SELECT post_title FROM " . $wpdb->posts . " WHERE post_name = %s", $identifier);
		$custom_post_type_title = $wpdb->get_var($query);
		if ( !empty($custom_post_type_title) ) {
			$tpl_component['CUSTOM_POST_TYPE_IDENTIFIER'] = '<img class="wpshop_tools_check_icon no_error" src="' . WPSHOP_MEDIAS_ICON_URL . 'informations/success_s.png" /> ' . $custom_post_type_title . ' (' . $identifier . ')';
			$tpl_component['TOOLS_CUSTOM_POST_TYPE_CONTAINER_CLASS'] = ' no_error';
			$tpl_component['CUSTOM_POST_TYPE_DEFAULT_ATTRIBUTES'] = '';
			$template_part = 'wpshop_admin_tools_default_datas_check_main_element_content_no_error';

			$attributes_for_cpt = wpshop_entities::check_default_cpt_attributes( $identifier, $tpl_component, $has_error );
			$has_error = $attributes_for_cpt[0];
			$tpl_component['CUSTOM_POST_TYPE_DEFAULT_ATTRIBUTES'] = $attributes_for_cpt[1];
		}
		else {
			$tpl_component['CUSTOM_POST_TYPE_IDENTIFIER'] = '<img class="wpshop_tools_check_icon error" src="' . WPSHOP_MEDIAS_ICON_URL . 'informations/error_s.png" /> ' . $identifier;
			$tpl_component['TOOLS_CUSTOM_POST_TYPE_CONTAINER_CLASS'] = ' error';
			$template_part = 'wpshop_admin_tools_default_datas_check_main_element_content_error';
			$has_error = true;
		}

		$output = wpshop_display::display_template_element($template_part, $tpl_component, array(), 'admin');

		return array($has_error, $output, $tpl_component);
	}

	/**
	 * Check if a given list of attributes exist for a given custom post type
	 *
	 * @param string $identifier The custom post type identifier. This identifier is unique into database
	 * @param array $tpl_component An array with already existing template element (Allows to merge existing and new)
	 * @param boolean $has_error The current state of request. Could be false if the parent custom post type does not exist
	 *
	 * @return array The different response element for the request. $has_error: A boolean information for request result / $output: The complete html output for attribute check
	 */
	public static function check_default_cpt_attributes( $identifier, $tpl_component, $has_error, $custom_file = '' ) {
		global $wpdb, $attribute_displayed_field;
		$output = '';

		$cpt_attributes_file_uri = !empty( $custom_file ) ? $custom_file : WPSHOP_TEMPLATES_DIR . 'default_datas/' . $identifier . '-attributes.csv';
		if ( is_file( $cpt_attributes_file_uri ) ) {
			/**	Read lines into file defining default datas */
			$csv_file_default_data = file($cpt_attributes_file_uri, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			if ( !empty($csv_file_default_data) ) {
				$header_line = explode(';', $csv_file_default_data[0]);
				unset($csv_file_default_data[0]);
				$code_column = null;
				$available_columns = array();
				foreach ( $header_line as $column_key => $column_value ) {
					if ( $column_value == 'code' ) {
						$code_column = $column_key;
// 						$available_columns[$column_value] = $column_key;
					}
					else if ( in_array( $column_value, array('frontend_label')/* $attribute_displayed_field */ ) ) {
						$available_columns[$column_value] = $column_key;
					}
				}

				/**	Read the complete file content	*/
				$attribute_ok = $attribute_not_ok = '  ';
				foreach ( $csv_file_default_data as $line_index => $line_content ) {
					$line_contents = explode(';', $line_content);
					$query = $wpdb->prepare("SELECT id, frontend_label FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s AND entity_id = %d", $line_contents[$code_column], wpshop_entities::get_entity_identifier_from_code($identifier));
					$attribute = $wpdb->get_row( $query );
					if ( !empty($line_contents) ) {
						foreach ( $line_contents as $line_column => $line_column_value ) {
							if ( in_array( $line_column, $available_columns ) ) {
								if ( !empty($attribute) ) {
									$attribute_ok .= $attribute->frontend_label . ', ';
								}
								else {
									$attribute_not_ok .= $line_column_value . ', ';
									$has_error = true;
								}
							}
						}
					}
				}
				$attribute_not_ok = substr( $attribute_not_ok, 2, -2 );
				if ( !empty($attribute_not_ok) ) {
					$output .= wpshop_display::display_template_element('wpshop_admin_tools_default_datas_check_main_element_content_attributes_error', array_merge( $tpl_component, array( 'CUSTOM_POST_TYPE_DEFAULT_ATTRIBUTES_LIST' => $attribute_not_ok )), array(), 'admin');
				}
				$attribute_ok = substr( $attribute_ok, 2, -2 );
				if ( !empty($attribute_ok) ) {
					$output .= wpshop_display::display_template_element('wpshop_admin_tools_default_datas_check_main_element_content_attributes_no_error', array_merge( $tpl_component, array( 'CUSTOM_POST_TYPE_DEFAULT_ATTRIBUTES_LIST' => $attribute_ok )), array(), 'admin');
				}
			}
		}

		return array( $has_error, $output );
	}

	/**
	 * Allows to create attributes for a custom post type from a csv file, allowing to create default attributes or import new attributes
	 *
	 * @param string $identifier The custom post type identifier to create attributes for. This identifier is unique into database
	 *
	 * @return array The different response element for the request. $result: Boolean representing if creation is OK / $container: Where the result must be placed into output code / $output: The html content to output
	 */
	public static function create_cpt_attributes_from_csv_file( $identifier, $custom_file = '' ) {
		global $wpdb;

		$output = $container = '';
		$result = true;
		$container = 'wpshop_cpt_' . $identifier . ' ul.wpshop_tools_default_datas_repair_attribute_container';
		$excluded_column = array( 'available_values' );

		$file_uri = !empty( $custom_file ) ? $custom_file : WPSHOP_TEMPLATES_DIR . 'default_datas/' . $identifier . '-attributes.csv';
		if ( is_file( $file_uri ) ) {
			$entity_id = wpshop_entities::get_entity_identifier_from_code($identifier);
			$csv_file_default_data = file($file_uri, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			$db_field_definition = explode( ";", $csv_file_default_data[0] );
			$code_column = null;
			foreach ( $db_field_definition as $column_index => $column_name ) {
				if ( $column_name == 'code' ) {
					$code_column = $column_index;
					continue;
				}
			}
			unset($csv_file_default_data[0]);

			if ( !empty($code_column) || ($code_column == 0) ) {
				foreach ( $csv_file_default_data as $line_index => $line_content ) {
					$attribute_definition = explode( ";", $line_content );
					$query = $wpdb->prepare( "SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s AND entity_id = %d", $attribute_definition[$code_column], $entity_id);
					$attribute_identifier = $wpdb->get_var($query);

					if ( empty($attribute_identifier) ) {
						$attribute_def = array();
						$attribute_values = $default_value = null;
						foreach ( $db_field_definition as $column_index => $column_name ) {
							$column_name = trim($column_name);
							if ( !empty($column_name) && !in_array($column_name, $excluded_column) ) {
								$column_value = $attribute_definition[$column_index];
								switch ( $column_name ) {
									case 'frontend_label':
										$column_value = __( $column_value, 'wpshop' );
										break;
								}
								$attribute_def[$column_name] = ( !empty($attribute_definition[$column_index]) ) ? $column_value : '';
							}
							else {
								switch ( $column_name ) {
									case 'available_values':
										$attribute_values = $attribute_definition[$column_index];
									break;
								}
							}

							switch ( $column_name ) {
								case 'default_value':
									$default_value = __( $attribute_definition[$column_index], 'wpshop' );
								break;
							}
						}
						$attribute_def['entity_id'] = $entity_id;
						$wpdb->insert(WPSHOP_DBT_ATTRIBUTE, $attribute_def);
						$last_attribute_id = $wpdb->insert_id;

						/**	Create values for select element	*/
						if ( !empty($attribute_values) ) {
							$list_of_values_to_create = explode( ',', $attribute_values );
							if ( !empty($list_of_values_to_create) ) {
								foreach ( $list_of_values_to_create as $value ) {
									$value_element = explode( '!:!:!', $value);
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $last_attribute_id, 'label' => __( $value_element[0], 'wpshop' ), 'value' => __( (!empty($value_element[1]) ? $value_element[1] : strtolower($value_element[0]) ), 'wpshop' )));

									if ( $default_value == (!empty($value_element[1]) ? $value_element[1] : strtolower($value_element[0])) ) {
										$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $wpdb->insert_id), array('id' => $last_attribute_id, 'default_value' => $default_value));
									}
								}
							}
						}

					}
				}
			}
		}

		$check_cpt = wpshop_entities::check_default_cpt_attributes( $identifier, array(), false, $custom_file );
		$output = $check_cpt[1];

		return array($result, $container, $output);
		die();
	}

}

?>
