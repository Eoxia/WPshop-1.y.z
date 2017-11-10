<?php
/**
 * Fichier de gestion des clients dans WPShop
 *
 * @package WPShop
 * @subpackage Customer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage Customer general and front-end functions
 *
 * @author ALLEGRE Jérôme - EOXIA
 */
class wps_customer_ctr {

	/**
	 * Stock l'identifiant du client sur lequel on est actuellement pour éviter de faire des requêtes tout le temps
	 *
	 * @var array
	 */
	public static $customer_user_identifier_cache = array();

	/**
	 * Instanciation des clients dans WPShop
	 */
	public function __construct() {
		/** Create customer entity type on wordpress initilisation */
		add_action( 'init', array( $this, 'create_customer_entity' ) );

		// Redirection vers la page d'édition d'un utilisateur après sa création.
		add_filter( 'wp_redirect', array( $this, 'wp_redirect_after_user_new' ), 1 );

		add_filter( 'wp_redirect', array( $this, 'wp_redirect_after_user_new' ), 1 );

		/** When a wordpress user is created, create a customer (post type) */
		add_action( 'user_register', array( $this, 'create_entity_customer_when_user_is_created' ) );
		add_action( 'edit_user_profile_update', array( $this, 'update_entity_customer_when_profile_user_is_update' ) );

		/** Add filters for customer list */
		add_filter( 'bulk_actions-edit-' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, array( $this, 'customer_list_table_bulk_actions' ) );
		add_filter( 'manage_edit-' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . '_columns', array( $this, 'list_table_header' ) );
		add_action( 'manage_' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . '_posts_custom_column', array( $this, 'list_table_column_content' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( &$this, 'list_table_filters' ) );
		add_filter( 'parse_query', array( &$this, 'list_table_filter_parse_query' ) );

		/** Filter search for customers */
		//add_filter( 'pre_get_posts', array( $this, 'customer_search' ) );

		/** Customer options for the shop */
		add_action( 'wsphop_options', array( &$this, 'declare_options' ), 8 );
		add_action( 'wp_ajax_wps_customer_search', array( $this, 'ajax_search_customer' ) );

		add_action( 'set_current_user', array( $this, 'hook_login_for_setting_customer_id' ) );

		add_action( 'wps_after_check_order_payment_total_amount', array( $this, 'compil_customer_due_amount_after_payment_creation' ) );
		add_action( 'manage_posts_extra_tablenav', array( $this, 'add_customer_due_amount_compil_button' ) );
		add_action( 'wp_ajax_wps-customer-due-amount-compil', array( $this, 'ajax_callback_wps_customer_due_amount_compil' ) );
	}

	/**
	 * Affiche la liste des clients de la boutique / Display customer list
	 *
	 * @param integer $selected_user L'identifiant du client à sélectionner par défaut si il est fourni / The customer identifier to select automatically if given.
	 * @param boolean $multiple Optionnal. Permet de définir si le choix du client peut être multiple ou si un ceul client doit être choisi à la fois / Define if several customer could be selected or if only one could be selected.
	 * @param boolean $disabled Optionnal. Permet de définir si la liste est sélectionnable ou non / Define if the list is usable or not.
	 *
	 * @return string L'affichage de la lsite déroulanet contenant les clients de la boutique / THe select list of customers
	 */
	public static function customer_select( $selected_user = 0, $multiple = false, $disabled = false ) {
		$content_output = '';

		$wps_customer_mdl = new wps_customer_mdl();
		$customers = $wps_customer_mdl->get_customer_list( -1 );

		require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, 'common', 'customer', 'select' ) );

		return $content_output;
	}

	/**
	 * Définition d'un cookie avec 'idetifiant du client auqule l'utilisateur est affecté : set a cookie with the customer identifier if connected user is associated to only one customer
	 */
	public function hook_login_for_setting_customer_id() {
		$user_id = get_current_user_id();
		$customer_id = wps_customer_ctr::get_customer_id_by_author_id( $user_id );
		if ( empty( $customer_id ) ) {
			$query = $GLOBALS['wpdb']->prepare( "SELECT post_id FROM {$GLOBALS['wpdb']->postmeta} WHERE meta_key = %s AND meta_value LIKE %s ORDER BY meta_id LIMIT 1", '_wpscrm_associated_user', "%;i:$user_id;%" );
			$customer_id = $GLOBALS['wpdb']->get_var( $query );

			if ( ! empty( $user_id ) && empty( $customer_id ) ) {
				self::create_entity_customer_when_user_is_created( $user_id );
			}
		}

		setcookie( 'wps_current_connected_customer', $customer_id, strtotime( '+30 days' ), SITECOOKIEPATH, COOKIE_DOMAIN, is_ssl() );
	}

	/**
	 * AJAX callback for customer search
	 */
	public function ajax_search_customer() {
		check_ajax_referer( 'wps_customer_search' );

		$term = ! empty( $_GET ) && ! empty( $_GET['term'] ) && is_string( (string) $_GET['term'] ) ? (string) $_GET['term'] : $term;
		$list = $this->search_customer( $term );

		wp_die( wp_json_encode( $list ) );
	}

	/**
	 * Search function for customer / Fonction de recherche des clients
	 *
	 * @param string $term The term we have to search into database for getting existing users / Le terme qu'il faut rechercher pour retrouver les utilisateurs dans la base de données.
	 */
	public function search_customer( $term ) {
		$users = array();
		$search_users = new WP_User_Query( array(
			'search'				 => '*' . esc_attr( $term ) . '*',
			'search_columns' => array( 'user_login', 'user_url', 'user_email', 'user_nicename', 'display_name' ),
		) );
		$user_query_results = $search_users->get_results();
		if ( ! empty( $user_query_results ) ) {
			foreach ( $user_query_results as $user ) {
				$users[ $user->ID ]['id'] = $user->ID;
				$users[ $user->ID ]['display_name'] = $user->display_name;
				$users[ $user->ID ]['email'] = $user->user_email;
				$users[ $user->ID ]['last_name'] = $user->last_name;
				$users[ $user->ID ]['first_name'] = $user->first_name;
			}
		}
		$search_users_in_meta = new WP_User_Query( array(
			'meta_query'		 => array(
				'relation' => 'OR',
				array(
					'key'		 => 'first_name',
					'value'	 => $term,
					'compare' => 'LIKE',
				),
				array(
					'key'		 => 'last_name',
					'value'	 => $term,
					'compare' => 'LIKE',
				),
			),
		) );
		$user_query_results_2 = $search_users_in_meta->get_results();
		if ( ! empty( $user_query_results_2 ) ) {
			foreach ( $user_query_results_2 as $user ) {
				$users[ $user->ID ]['id'] = $user->ID;
				$users[ $user->ID ]['display_name'] = $user->display_name;
				$users[ $user->ID ]['email'] = $user->user_email;
				$users[ $user->ID ]['last_name'] = $user->last_name;
				$users[ $user->ID ]['first_name'] = $user->first_name;
			}
		}
		if ( empty( $users ) ) {
			$users[] = array(
				'id'		=> null,
				'label'	=> __( 'Create a new user', 'wpshop' ),
			);
		}

		return $users;
	}

	/**
	 * Récupère l'id du client selon son author id / Get customer id by the author id
	 *
	 * @param int $author_id L'identifiatn de l'utilisateur dont on veut avoir le numéro de client / User id we want the customer ID for.
	 *
	 * @return int $customer_id
	 */
	public static function get_customer_id_by_author_id( $author_id ) {
		if ( ! isset( self::$customer_user_identifier_cache[ $author_id ] ) ) {
			$query = $GLOBALS['wpdb']->prepare( "SELECT ID FROM {$GLOBALS['wpdb']->posts} WHERE post_author = %d AND post_type = %s ORDER BY ID ASC", $author_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
			self::$customer_user_identifier_cache[ $author_id ] = $GLOBALS['wpdb']->get_var( $query );
		}

		return self::$customer_user_identifier_cache[ $author_id ];
	}

	/**
	 * Récupère l'id de l'utilisateur selon l'id du client / Get author id by customer id
	 *
	 * @method get_author_id_by_customer_id
	 * @param	int $customer_id ID in wpshop_customers type.
	 *
	 * @return int
	 */
	public static function get_author_id_by_customer_id( $customer_id ) {
		$flipped = ! empty( self::$customer_user_identifier_cache ) ? array_flip( self::$customer_user_identifier_cache ) : '';
		if ( ! isset( $flipped[ $customer_id ] ) ) {
			$author_id = $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( "SELECT post_author FROM {$GLOBALS['wpdb']->posts} WHERE ID = %d", $customer_id ) );
			self::$customer_user_identifier_cache[ $author_id ] = $customer_id;
			$flipped[ $customer_id ] = $author_id;
		}

		return $flipped[ $customer_id ];
	}

	/**
	 * Create the customer entity
	 */
	public function create_customer_entity() {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT P.post_title, PM.meta_value FROM {$wpdb->posts} AS P INNER JOIN {$wpdb->postmeta} AS PM ON (PM.post_id = P.ID) WHERE P.post_name = %s AND PM.meta_key = %s", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, '_wpshop_entity_params' );
		$customer_entity_definition = $wpdb->get_row( $query ); // WPCS: unprepared sql ok.
		$current_entity_params = ! empty( $customer_entity_definition ) && ! empty( $customer_entity_definition->meta_value ) ? unserialize( $customer_entity_definition->meta_value ) : null;

		$post_type_params = array(
			'labels' => array(
				'name' => __( 'Customers', 'wpshop' ),
				'singular_name' => __( 'Customer', 'wpshop' ),
				'add_new_item' => __( 'New customer', 'wpshop' ),
				'add_new' => __( 'New customer', 'wpshop' ),
				'edit_item' => __( 'Edit customer', 'wpshop' ),
				'new_item' => __( 'New customer', 'wpshop' ),
				'view_item' => __( 'View customer', 'wpshop' ),
				'search_items' => __( 'Search in customers', 'wpshop' ),
				'not_found' => __( 'No customer found', 'wpshop' ),
				'not_found_in_trash' => __( 'No customer founded in trash', 'wpshop' ),
				'parent_item_colon' => '',
			),
			'description' => '',
			'supports' => ! empty( $current_entity_params['support'] ) ? $current_entity_params['support'] : array( 'title' ),
			'hierarchical' => false,
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'can_export' => false,
			'has_archive' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'rewrite' => false,
			'menu_icon' => 'dashicons-id-alt',
			'capabilities' => array(
				'create_posts' => 'wpshop_view_dashboard',
				'edit_post' => 'wpshop_view_dashboard',
				'edit_posts' => 'wpshop_view_dashboard',
				'edit_others_posts' => 'wpshop_view_dashboard',
				'publish_posts' => 'wpshop_view_dashboard',
				'read_post' => 'wpshop_view_dashboard',
				'read_private_posts' => 'wpshop_view_dashboard',
				'delete_post' => 'wpshop_view_dashboard',
				'delete_posts' => 'wpshop_view_dashboard',
			),
		);

		register_post_type( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $post_type_params );
	}

	/**
	 * Customer options for the shop
	 */
	public static function declare_options() {
		if ( WPSHOP_DEFINED_SHOP_TYPE == 'sale' ) {
			$wpshop_shop_type = ! empty( $_POST['wpshop_shop_type'] ) ? sanitize_text_field( $_POST['wpshop_shop_type'] ) : '';
			$old_wpshop_shop_type = ! empty( $_POST['old_wpshop_shop_type'] ) ? sanitize_text_field( $_POST['old_wpshop_shop_type'] ) : '';

			if ( ( $wpshop_shop_type == '' || $wpshop_shop_type != 'presentation' )
					&& ( $old_wpshop_shop_type == '' || $old_wpshop_shop_type != 'presentation' ) ) {
					/**		Add module option to wpshop general options		*/
					register_setting( 'wpshop_options', 'wpshop_cart_option', array( 'wps_customer_ctr', 'wpshop_options_validate_customers_newsleters' ) );
					add_settings_field( 'display_newsletters_subscriptions', __( 'Display newsletters subscriptions', 'wpshop' ), array( 'wps_customer_ctr', 'display_newsletters_subscriptions' ), 'wpshop_cart_info', 'wpshop_cart_info' );
			}
		}
	}

	/**
	 * Validation des options spécifiques aux utilisateurs
	 *
	 * @param array $input Les réglages que l'on souhaite enregistrer et qu'il faut vérifier.
	 *
	 * @return array Les réglages des clients qui ont été vérifiés
	 */
	public static function wpshop_options_validate_customers_newsleters( $input ) {
			return $input;
	}

	/**
	 * Affichage des champs permettant les réglages concernant les newsletters des clients dans les réglages de la boutique
	 */
	public static function display_newsletters_subscriptions() {
		$cart_option = get_option( 'wpshop_cart_option', array() );
		$output = '';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_display_newsletter_site_subscription';
		$input_def['type'] = 'checkbox';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = ! empty( $cart_option['display_newsletter']['site_subscription'] ) ? $cart_option['display_newsletter']['site_subscription'][0] : 'no';
		$input_def['possible_value'] = 'yes';
		$output .= wpshop_form::check_input_type( $input_def, 'wpshop_cart_option[display_newsletter][site_subscription]' ) . '<label for="' . $input_def['id'] . '">' . __( 'Newsletters of the site', 'wpshop' ) . '</label><a href="#" title="' . __( 'Check this box if you want display newsletter site subscription', 'wpshop' ) . '" class="wpshop_infobulle_marker">?</a><br>';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_display_newsletter_partner_subscription';
		$input_def['type'] = 'checkbox';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = ! empty( $cart_option['display_newsletter']['partner_subscription'] ) ? $cart_option['display_newsletter']['partner_subscription'][0] : 'no';
		$input_def['possible_value'] = 'yes';
		$output .= wpshop_form::check_input_type( $input_def, 'wpshop_cart_option[display_newsletter][partner_subscription]' ) . '<label for="' . $input_def['id'] . '">' . __( 'Newsletters of the partners', 'wpshop' ) . '</label><a href="#" title="' . __( 'Check this box if you want display newsletter partners subscription', 'wpshop' ) . '" class="wpshop_infobulle_marker">?</a><br>';

		echo $output; // WPCS: XSS ok.
	}

	/**
	 * WordPress Hook - Redirection de l'administrateur après la création d'un compte utilisateur
	 *
	 * @param  string $location l'url vers laquelle la redirection doit avoir lieu.
	 *
	 * @see wp_redirect()
	 *
	 * @return string           La nouvelle url pour la redirection.
	 */
	public function wp_redirect_after_user_new( $location ) {
		global $pagenow;

		if ( is_admin() && $pagenow === 'user-new.php' ) {
			$user_details = get_user_by( 'email', $_REQUEST[ 'email' ] );
			$user_id = $user_details->ID;

			if( $location == 'users.php?update=add&id=' . $user_id )
			return add_query_arg( array( 'user_id' => $user_id ), 'user-edit.php' );
		}

		return $location;
	}

	/**
	 * Sélection automatique du role client lors de la création d'un utilisateur dans WordPress
	 */
	public function admin_user_customer_js() {
		echo "<script type='text/javascript'>jQuery(document).ready(function($) { $('#role').val('customer').change(); });\n</script>";
	}

	/**
	 * Create an entity of customer type when a new user is created
	 *
	 * @param integer $user_id L'identifiant de l'utilisateur qui vient d'être créé et pour qui on va créer le client.
	 */
	public static function create_entity_customer_when_user_is_created( $user_id ) {
		if ( ! is_admin() || strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' )  ) {
			$user_data = get_userdata( $user_id );
			$user_info = array_merge( get_object_vars( $user_data->data ), array_map( 'self::array_map_create_entity_customer_when_user_is_created', get_user_meta( $user_id ) ) );
			$customer_post_ID = wp_insert_post( array( 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'post_author' => $user_id, 'post_title' => $user_data->user_nicename ) );
			self::save_customer_synchronize( $customer_post_ID, $user_id, $user_info );

			/** Change metabox Hidden Nav Menu Definition to display WPShop categories' metabox */
			$usermeta = get_post_meta( $user_id, 'metaboxhidden_nav-menus', true );
			if ( ! empty( $usermeta ) && is_array( $usermeta ) ) {
				$data_to_delete = array_search( 'add-wpshop_product_category', $usermeta );
				if ( false !== $data_to_delete ) {
					unset( $usermeta[ $data_to_delete ] );
					update_user_meta( $user_id, 'metaboxhidden_nav-menus', $usermeta );
				}
			}
		}
	}
	private static function array_map_create_entity_customer_when_user_is_created( $a ) {
		return $a[0];
	}

	/**
	 * Update an entity of customer type when a user profile is update
	 *
	 * @param integer $user_id
	 */
	public static function update_entity_customer_when_profile_user_is_update( $user_id ) {
		if ( ! is_admin() || strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' )  ) {
			$user_data = get_userdata($user_id);
			$user_info = array_merge(get_object_vars($user_data->data), array_map('self::array_map_create_entity_customer_when_user_is_created', get_user_meta($user_id)));
			$customer_post_ID = self::get_customer_id_by_author_id($user_id);
			self::save_customer_synchronize($customer_post_ID, $user_id, $user_info);
		}
	}


	public static function prevent_send_mail_from_wordpress() {
		return false;
	}

	public static function save_customer_synchronize( $customer_post_ID, $user_id, $user_info ) {
		global $wpdb;
		global $wpshop;
		$exclude_user_meta = array('user_login', 'user_nicename', 'user_email', 'user_pass', 'user_url', 'user_registered', 'user_activation_key', 'user_status', 'display_name');
		$wps_entities = new wpshop_entities();
		$element_id = $wps_entities->get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
		$query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d', $element_id);
		$attribute_set_id = $wpdb->get_var($query);
		$attributes_default = array();
		if (! empty($attribute_set_id)) {
			$group = wps_address::get_addresss_form_fields_by_type($attribute_set_id);
			foreach ($group as $attribute_sets) {
				foreach ($attribute_sets as $attribute_set_field) {
					foreach ($attribute_set_field['content'] as $attribute) {
						if (isset($attribute['value'])) {
							if (is_serialized($attribute['value'])) {
								$unserialized_value = unserialize($attribute['value']);
								if (isset($unserialized_value['default_value'])) {
									$attributes_default[$attribute['name']] = $unserialized_value['default_value'];
								}
							} else {
								$attributes_default[$attribute['name']] = $attribute['value'];
							}
						}
					}
				}
			}
		}
		$user_info = array_merge($attributes_default, $user_info);
		add_filter( 'send_password_change_email', array( get_class(), 'prevent_send_mail_from_wordpress' ) );
		foreach ($user_info as $user_meta => $user_meta_value) {
			$attribute_def = wpshop_attributes::getElement($user_meta, "'valid'", 'code');
			if (! empty($attribute_def)) {
				//Save data in user meta
				if (in_array($user_meta, $exclude_user_meta)) {
					if ($user_meta == 'user_pass') {
						$new_password = wpshop_tools::varSanitizer($user_meta_value);
						if (wp_hash_password($new_password) == get_user_meta($user_id, $user_meta, true)) {
							continue;
						}
					}
					wp_update_user(array('ID' => $user_id, $user_meta => wpshop_tools::varSanitizer($user_meta_value)));
				} else {
					update_user_meta($user_id, $user_meta, wpshop_tools::varSanitizer($user_meta_value));
				}
				//Save data in attribute tables, ckeck first if exist to know if Insert or Update
				$query = $wpdb->prepare('SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . strtolower($attribute_def->data_type) . ' WHERE entity_type_id = %d AND entity_id = %d AND attribute_id = %d', $element_id, $customer_post_ID, $attribute_def->id);
				$checking_attribute_exist = $wpdb->get_results($query);
				if (! empty($checking_attribute_exist)) {
					$wpdb->update(
						WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . strtolower($attribute_def->data_type),
						array(
							'value' => wpshop_tools::varSanitizer($user_meta_value)),
							array(
								'entity_type_id' => $element_id,
								'entity_id' => $customer_post_ID,
								'attribute_id' => $attribute_def->id,
							)
					);
				} else {
					$wpdb->insert(
					WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . strtolower($attribute_def->data_type),
					array(
						'entity_type_id' => $element_id,
						'attribute_id' => $attribute_def->id,
						'entity_id' => $customer_post_ID,
						'user_id' => $user_id,
						'creation_date_value' => current_time('mysql', 0),
						'language' => 'fr_FR',
						'value' => wpshop_tools::varSanitizer($user_meta_value),
						)
					);
				}
			}
		}
		remove_filter( 'send_password_change_email', array( get_class(), 'prevent_send_mail_from_wordpress' ) );
	}

	/**
	 * Change the customer list table header to display custom informations
	 *
	 * @param array $current_header The current header list displayed to filter and modify for new output.
	 *
	 * @return array The new header to display
	 */
	public function list_table_header( $current_header ) {
		unset( $current_header['title'] );
		unset( $current_header['date'] );

		$current_header['customer-identifier'] = __( 'Customer ID', 'wpshop' );
		$current_header['customer-name'] = __( 'Customer name', 'wpshop' );
		// $current_header['customer_name'] = '<span class="wps-customer-last_name" >' . __('Last-name', 'wpshop') . '</span><span class="wps-customer-first_name" >' . __('First-name', 'wpshop') . '</span>';
		// $current_header['customer_email'] = __('E-mail', 'wpshop');
		$current_header['customer-orders'] = __( 'Customer last order', 'wpshop' );
		$current_header['customer-due-amount'] = __( 'Due amount', 'wpshop' );
		// $current_header['customer-contacts'] = __( 'Contacts', 'wpshop' );
		$current_header['customer_date_subscription'] = __( 'Subscription', 'wpshop' );
		// $current_header['customer_date_lastlogin'] = __( 'Last login date', 'wpshop' );

		return $current_header;
	}

	/**
	 * Display the content into list table column
	 *
	 * @param string  $column THe column identifier to modify output for.
	 * @param integer $post_id The current post identifier.
	 */
	public function list_table_column_content( $column, $post_id ) {
		global $wpdb;
		/**		Get wp_users identifier from customer id		*/
		$customer_post_author = self::get_author_id_by_customer_id( $post_id );

		/**		Get current post informations		*/
		$customer_post = get_post( $post_id );

		/**		Switch current column for custom case		*/
		$use_template = true;
		switch ( $column ) {
			case 'customer-identifier' :
				echo esc_html( $post_id );
				$use_template = false;
			break;

			case 'customer-due-amount' :
				$customer_due_amount = get_post_meta( $post_id, '_wps_customer_due_amount', true );
				if ( ! empty( $customer_due_amount ) ) {
					echo esc_html( wpshop_tools::formate_number( $customer_due_amount ) . '' . wpshop_tools::wpshop_get_currency() );
				} else {
					esc_html_e( '-', 'wpshop' );
				}
				$use_template = false;
			break;

			case 'customer_date_subscription' :
				echo mysql2date( get_option( 'date_format' ), $customer_post->post_date, true );
				$use_template = false;
			break;

			case 'customer_date_lastlogin' :
				$last_login = get_user_meta( $customer_post_author, 'last_login_time', true );
				if ( ! empty( $last_login ) ) :
					echo mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_login, true );
				else :
					esc_html_e( 'Never logged in', 'wpshop' );
				endif;
				$use_template = false;
			break;
		}

		/**		Require the template for displaying the current column		*/
		if ( $use_template ) {
			$template = wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . '/templates/', 'backend', 'customer_listtable/' . $column );
			if ( is_file( $template ) ) {
				require $template;
			}
		}
	}

	/**
	 * Filter bulk actions into customer list table
	 *
	 * @param array $actions Current available actions list.
	 *
	 * @return array The new action list to use into customer list table
	 */
	public function customer_list_table_bulk_actions( $actions ) {
		unset( $actions['edit'] );
		// unset( $actions['trash'] );
		unset( $actions['delete'] );

		return $actions;
	}

	public function list_table_filters() {
		$post_type = ! empty($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
		if (isset($post_type)) {
			if (post_type_exists($post_type) && ($post_type == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS)) {
				$filter_possibilities = array();
				$filter_possibilities[''] = __('-- Select Filter --', 'wpshop');
				$filter_possibilities['orders'] = __('List customers with orders', 'wpshop');
				$filter_possibilities['no_orders'] = __('List customers without orders', 'wpshop');
				echo wpshop_form::form_input_select('entity_filter', 'entity_filter', $filter_possibilities, (! empty($_GET['entity_filter']) ? sanitize_text_field($_GET['entity_filter']) : ''), '', 'index');
			}
		}
	}

	public function list_table_filter_parse_query( $query ) {
		global $pagenow, $wpdb;
		$post_type = ! empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		$entity_filter = ! empty( $_GET['entity_filter'] ) ? sanitize_text_field( $_GET['entity_filter'] ) : '';
		if ( is_admin() && ( $pagenow == 'edit.php' ) && ! empty( $post_type ) && ( $post_type == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ) && ! empty( $entity_filter ) ) {
			$check = null;
			switch ( $entity_filter ) {
				case 'orders':
					$sql_query = $wpdb->prepare(
						"SELECT ID
							FROM {$wpdb->posts}
							WHERE post_type = %s
							AND post_status != %s
							AND post_author IN (
							SELECT post_author
							FROM {$wpdb->posts}
							WHERE post_type = %s
							AND post_status != %s)", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'auto-draft', WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'auto-draft');
					$check = 'post__in';
				break;
				case 'no_orders':
					$sql_query = $wpdb->prepare(
						"SELECT ID
							FROM {$wpdb->posts}
							WHERE post_type = %s
							AND post_status != %s
							AND post_author NOT IN (
							SELECT post_author
							FROM {$wpdb->posts}
							WHERE post_type = %s
							AND post_status != %s)", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'auto-draft', WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'auto-draft');
					$check = 'post__in';
				break;
			}

			if ( ! empty( $check ) ) {
				$results = $wpdb->get_results( $sql_query );
				$user_id_list = array();
				foreach ( $results as $item ) {
					$user_id_list[] = $item->ID;
				}
				if ( empty( $post_id_list ) ) {
					$post_id_list[] = 'no_result';
				}
				$query->query_vars[ $check ] = $user_id_list;
			}
			$query->query_vars['post_type'] = WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;
			$query->query_vars['post_status'] = 'any';
		}
	}

	/**
	 * Récupère la liste de tous les clients / Get the list of all customers
	 *
	 * @return array
	 */
	public static function get_all_customer( $post_status = 'publish' ) {
		global $wpdb;

		$query = $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $post_status);
		$list_customer = $wpdb->get_results($query);

		return $list_customer;
	}

	/**
	 * Essaie de créer une corrélation entre le formulaire que l'on passe et customer ( ex: billing_address => customer )
	 *
	 * @param array $form Passer directement le $_POST pour créer un client.
	 * @return array ou int : 1 = Aucun mail, 2 = L'utilisateur existe déjà
	 */
	public static function quick_add_customer( $form ) {
		$return = 1;
		if ( ! empty( $form ) ) {
			global $wpdb;
			$customer_type_id = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
			$query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d', $customer_type_id);
			$customer_entity_id = $wpdb->get_var($query);
			$attributes_set = wpshop_attributes_set::getElement($customer_entity_id);
			$account_attributes = wpshop_attributes_set::getAttributeSetDetails((! empty($attributes_set->id)) ? $attributes_set->id : '', "'valid'");

			$customer_attributes_to_save = $customer_attributes_to_save_temp = array();

			foreach ($form['attribute'] as $attribute_type => $attributes) {
				foreach ($attributes as $attribute_code => $attribute_value) {
					$customer_attributes_compare[$attribute_code] = $attribute_type;
				}
			}

			foreach ($account_attributes as $account_attribute_group) {
				foreach ($account_attribute_group['attribut'] as $attribute) {
					foreach (preg_grep('/' . $attribute->code . '/', array_keys($customer_attributes_compare)) as $codeForm) {
						if ($customer_attributes_compare[$codeForm] == $attribute->data_type) {
							$user[$attribute->code] = array('attribute_id' => $attribute->id, 'data_type' => $attribute->data_type, 'value' => sanitize_text_field($_POST['attribute'][$attribute->data_type][$codeForm]));
							if ($attribute->code == 'user_email') {
								$email_founded = true;
							}
						}
					}
				}
			}

			if ($email_founded && is_email($user['user_email']['value'])) {
				$user_id = username_exists($user['user_email']['value']);
				if (empty($user_id)) {
					$user_name = isset($user['user_login']['value']) ? $user['user_login']['value'] : $user['user_email']['value'];
					$user_pass = isset($user['user_pass']['value']) ? $user['user_pass']['value'] : wp_generate_password(12, false);
					$user_id = wp_create_user($user_name, $user_pass, $user['user_email']['value']);

					if (!is_wp_error($user_id)) {
						$customer_entity_request = $wpdb->prepare('SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $user_id);
						$customer_entity_id = $wpdb->get_var($customer_entity_request);
						$return = array();
						$return['integer']['ID'] = $user_id;

						foreach ($user as $meta_key => $meta_values) {
							update_user_meta($user_id, $meta_key, $meta_values['value']);
							$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . strtolower($meta_values['data_type']), array('entity_type_id' => $customer_type_id, 'attribute_id' => $meta_values['attribute_id'], 'entity_id' => $customer_entity_id, 'user_id' => $user_id, 'creation_date_value' => current_time('mysql', 0), 'language' => 'fr_FR', 'value' => $meta_values['value']));
							$return[$meta_values['data_type']][$meta_key] = $meta_values['value'];
						}
					}
				} else {
					$return = 2;
				}
			}
		}

		return $return;
	}


	/**
	 * Compil customer due amount for displaying
	 *
	 * @param  integer $customer_id Customer identifier we want to compil due amount for.
	 *
	 * @return float                Customer's due amount.
	 */
	public function wps_compil_customer_due_amount( $customer_id ) {
		$due_amount = 0;

		$customer_orders = new WP_Query( array(
			'post_type'      => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
			'posts_per_page' => -1,
			'post_parent'    => $customer_id,
			'post_status'    => 'any',
		) );
		if ( $customer_orders->have_posts() ) {
			foreach ( $customer_orders->posts as $customer_order ) {
				$order_meta = get_post_meta( $customer_order->ID, '_order_postmeta', true );
				$due_amount += $order_meta['order_amount_to_pay_now'];
			}
		}

		update_post_meta( $customer_id, '_wps_customer_due_amount', $due_amount );

		return $due_amount;
	}

	/**
	 * Callback pour le calcul du montant du par un client après ajout d'un nouveau paiement sur la commande.
	 *
	 * @param  integer $order_id L'identifiant de la commande pour laquell on ajout un paiement.
	 */
	public function compil_customer_due_amount_after_payment_creation( $order_id ) {
		$order_def = get_post( $order_id );
		$customer_id = $order_def->post_parent;

		$due_amount = $this->wps_compil_customer_due_amount( $customer_id );
	}

	/**
	 * Lance le calcul de la somme due par client pour toutes les commandes partiellement payée
	 */
	public function launch_customer_due_amount_compilation() {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT P.post_parent as CUSTOMER_ID FROM {$wpdb->postmeta} AS PM INNER JOIN {$wpdb->posts} AS P ON P.ID = PM.post_id WHERE PM.meta_key = %s AND PM.meta_value LIKE %s", '_order_postmeta', '%s:12:"order_status";s:14:"partially_paid";%' );
		$customer_having_orders_partially_paid = $wpdb->get_results( $query ); // WPCS: unprepared sql ok.

		foreach ( $customer_having_orders_partially_paid as $customer ) {
			$this->wps_compil_customer_due_amount( $customer->CUSTOMER_ID );
		}
	}

	/**
	 * Ajoute un boutton permettante de recompiler les montants du de tous le clients de la boutique. Ce boutton est disponible dans l'interfave des clients.
	 */
	function add_customer_due_amount_compil_button( $where ) {
		$post_type = ! empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		if ( ( 'top' === $where ) && ! empty( $post_type ) && post_type_exists( $post_type ) && ( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS === $post_type ) ) {
			require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, 'backend/customer_listtable', 'customer_due_amount_compil_button' ) );
		}
	}

	public function ajax_callback_wps_customer_due_amount_compil() {
		$this->launch_customer_due_amount_compilation();

		wp_send_json_success();
	}

}
