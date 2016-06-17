<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier du controlleur pour le module client du logiciel de caisse pour WP-Shop / Main controller file for customer into point of sale management plugin
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */

/**
 * Classe du controlleur pour le module client du logiciel de caisse pour WP-Shop / Main controller class for customer into point of sale management plugin
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_pos_addon_customer {

	/**
	 * Instanciation principale du module Client du logiciel de caisse / Call the different element to instanciate the customer module
	 */
	function __construct() {
		/**	Call dashboard metaboxes	*/
		add_action( 'admin_init', array( $this, 'dashboard_metaboxes' ) );

		/**	Hook wordpress user search	*/
		add_action( 'pre_user_query', array( $this, 'extended_user_search' ) );

		/**	Point d'accroche AJAX / AJAX listeners	*/
		/**	Choix d'un utilisateur comme propriatiare de la commande en cours / Set a customer as order owner	*/
		add_action( 'wp_ajax_wpspos_set_customer_order', array( $this, 'ajax_pos_customer_choice' ) );
		/**	Recherche parmis les utilisateurs existants / search into existing user list	*/
		add_action( 'wp_ajax_wpspos-customer-search', array( $this, 'ajax_pos_customer_search' ) );
	}

	/**
	 * WORDPRESS HOOK - Modification des paramètres de la recherche client pour le logiciel de caisse / Modify customer search parameters for POS addon
	 *
	 * @param WP_Object $user_query L'objet contenant la requête actuelle de recherche de client / Current user query
	 */
	function extended_user_search ( $user_query ) {
		global $wps_pos_addon_menu;
		$screen = get_current_screen();

		if ( !empty( $screen ) && !empty( $screen->id ) && $screen->id == $wps_pos_addon_menu ) {
			$user_query->query_where = str_replace( "AND (user_login LIKE", "OR (user_login LIKE", $user_query->query_where );
		}
	}

	/**
	 * WP CUSTOM HOOk - Appel dex blocs d'affichage pour l'interface du logiciel de caisse / Call metaboxes for POS addon dashboard
	 */
	function dashboard_metaboxes() {
		global $wpdb;
		/**	Create metaboxes for upper area	*/

		/**	Create metaboxes for left side	*/
		/*ob_start();
		require_once( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/customers', 'metabox_title', 'list' ) );
		$metabox_title = ob_get_contents();
		ob_end_clean();*/
		//add_meta_box( 'wpspos-dashboard-customer-metabox', $metabox_title, array( $this, 'metabox_customers' ), 'wpspos-dashboard', 'wpspos-dashboard-left' );

		/**	Create metaboxes for right side	*/
		ob_start();
		require_once( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/customers', 'metabox_title', 'selected' ) );
		$metabox_title = ob_get_contents();
		ob_end_clean();
		add_meta_box( 'wpspos-dashboard-order-customer-metabox', $metabox_title, array( $this, 'metabox_customer' ), 'wpspos-dashboard', 'wpspos-dashboard-right' );
	}

	/**
	 * Construction de la liste des clients pour une lettre donnée / Generate an array of customer for a letter
	 *
	 * @param string $letter La première lettre définissant le client / The first letter of customer name
	 *
	 * @return array La liste des utilisateurs correspondant à la lettre donnée / The customer list corresponding to given letter
	 */
	function get_customer_by_alphabet( $letter ) {
		$users_list = array();

		/** Get the default customer **/
		$default_customer_option = get_option( 'wpshop_pos_addon_default_customer_id' );
		if ( !empty($default_customer_option) ) {
			$default_user = get_user_by('id', $default_customer_option);
			if ( !empty($default_user) ) {
				$user_infos = array(
					'ID' => $default_user->ID,
					'last_name' => get_user_meta($default_user->ID, 'last_name', true),
					'first_name' => get_user_meta($default_user->ID, 'first_name', true),
					'email' => '-',
				);
				$users_list[] = $user_infos;
			}
		}

		$users = get_users();
		if ( !empty($letter) ) {
			foreach ( $users as $user ) {
				if ( !empty( $user ) && !empty( $user->roles ) && !empty( $user->roles[0] ) && ( $user->roles[0] != 'Administrator' ) ) {
					$user_infos = array();

					/** Check the username, if last name and first name are empty we select email **/
					$last_name_meta = get_user_meta( $user->ID, 'last_name', true);
					$first_name_meta = get_user_meta( $user->ID, 'first_name', true);
					$user_data = $user->data;
					$email_data = $user_data->user_email;

					if ( !empty($last_name_meta) ) {
						$user_name = $last_name_meta;
					}
					elseif( !empty( $first_name_meta)) {
						$user_name = $first_name_meta;
					}
					else {
						$user_name = $email_data;
					}

					if ( $letter == __('ALL', 'wps-pos-i18n') || (strtolower(substr($user_name, 0, 1)) == strtolower($letter) && $user_name != __('Default', 'wps-pos-i18n') ) ) {
						$user_infos = array(
							'ID' => $user->ID,
							'last_name' => $last_name_meta,
							'first_name' => $first_name_meta,
							'email' => $email_data,
						);
						$users_list[] = $user_infos;
					}
				}
			}
		}

		return $users_list;
	}

	/**
	 * AFFICHAGE / DISPLAY - Affiche la liste des utilisateurs suivant la lettre donnnées / Display the customer list regarding given letter
	 *
	 * @param string $letter La première lettre définissant le client / The first letter of customer name
	 *
	 * @return string La liste des utilisateurs sous forme HTML / HTML output for customer list
	 */
	function display_customer_list( $letter ) {
		$output = '';

		if ( !empty($letter) ) {
			$customer_list = $this->get_customer_by_alphabet( $letter);
			$tpl_component = array();
			$element = '';
			if ( !empty($customer_list) ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/customers', 'customers' ) );
				$output = ob_get_contents();
				ob_end_clean();
			}
		}

		return $output;
	}

	/**
	 * CUSTOMER - Check if there is a billing address for the choosen customer, if not we create one.
	 * @param integer $customer_id
	 * @return integer billing address ID
	 */
	function check_customer_billing_address( $customer_id ) {
		global $wpdb;
		$billing_address_id = 0;
		$billing_option = get_option('wpshop_billing_address');
		if ( !empty($customer_id) ) {
			$query = $wpdb->prepare('SELECT * FROM '.$wpdb->posts .' WHERE post_author = %d AND post_type = %s', $customer_id, WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS);
			$customer_addresses = $wpdb->get_results( $query );
			if ( !empty($customer_addresses) && is_array($customer_addresses) ) {
				if (!empty($billing_option) && !empty($billing_option['choice']) ) {
					$billing_address_entity_id = $billing_option['choice'];
					foreach( $customer_addresses as $customer_address ) {
						$address_post_meta = get_post_meta($customer_address->ID, '_wpshop_address_attribute_set_id', true);
						if ( $address_post_meta == $billing_address_entity_id ) {
							$billing_address_id = $customer_address->ID;
							continue;
						}
					}
				}
			}
			else {
				/** Create a billing address for this customer **/
				$user_infos = get_user_by('id', $customer_id);
				$billing_address = array();
				$billing_address['address_title'] = __('Billing address', 'wps-pos-i18n');
				$billing_address['address_last_name'] = get_user_meta($customer_id, 'last_name', true);
				$billing_address['address_first_name'] = get_user_meta($customer_id, 'first_name', true);
				$billing_address['address_user_email'] = $user_infos->user_email;

				$address_post = array(
					'post_author' => $customer_id,
					'post_date' => current_time('mysql', 0),
					'post_title' => __('Billing address', 'wps-pos-i18n'),
					'post_status' => 'draft',
					'post_name' =>  WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
					'post_parent' => $customer_id,
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
				);
				$billing_address_id = wp_insert_post( $address_post );
				update_post_meta( $billing_address_id, '_wpshop_address_metadata', $billing_address);
				update_post_meta($billing_address_id, '_wpshop_address_attribute_set_id', $billing_option['choice'] );
			}
		}
		return $billing_address_id;
	}

	/*
	 * CUSTOMER - Get all orders for customer
	 * @param integer $customer_id
	 * @return array of objects order
	 */
	function get_orders_customer( $per_page, $customer_id ) {
		if( !isset( $this->wps_orders_customer ) ) {
			$this->wps_orders_customer = array();
		}
		if( !isset( $this->wps_orders_customer[$customer_id] ) ) {
			$args = array(
					'posts_per_page' 	=> $per_page,
					'post_type'			=> WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
					'post_status' 		=> 'publish',
					'meta_query' 		=> array(
						'relation' 			=> 'AND',
						array(
								'key' 			=> '_order_postmeta',
								'value' 		=> serialize( 'customer_id' ) . serialize( $customer_id ),
								'compare' 		=> 'LIKE',
						),
						array(
							'relation' 			=> 'OR',
							array(
									'key'			=> '_order_postmeta',
									'value' 		=> serialize( 'order_status' ) . serialize( 'pos' ),
									'compare' 		=> 'LIKE',
							),
							array(
									'key'			=> '_order_postmeta',
									'value' 		=> serialize( 'shipping_method' ) . serialize( 'default_shipping_mode_for_pos' ),
									'compare' 		=> 'LIKE',
							),
						),
					),
			);
			$query = new WP_Query( $args );
			$orders = $query->posts;
			foreach( $orders as $order ) {
				$order->_order_postmeta = get_post_meta( $order->ID, '_order_postmeta', true );
			}
			$this->wps_orders_customer[$customer_id] = $orders;
		}
		return $this->wps_orders_customer[$customer_id];
	}

	/**
	 * WP CUSTOM METABOX - Affichage de la boite permettant le choix du client / Display a custom metabox for choosing a customer
	 */
	function metabox_customers() {
		global $wpdb;

		$available_letters = array();//$wpdb->get_var( $query );

		/**	Check the first letter available for product to choose the good one when displaying default interface	*/
		$letters_having_customers = array();//array_unique( explode( ',', $available_letters ) );
		sort( $letters_having_customers );

		require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/customers', 'metabox', 'customers' ) );
	}

	/**
	 * WP CUSTOM METABOX - Affichage de la boite contant les informations du client sélectionné pour la commande / Display the metabox for displaying the selected customer for current order
	 */
	function metabox_customer() {
		require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/customers', 'metabox', 'customer' ) );
	}

	/**
	 * Affiche le résumé concernant les informations du client sélectionné / Display the summary for selected customer account
	 *
	 * @param integer $customer_id L'identifiant du client que l'on souhaite afficher / The customer identifier that we want to display sumary for
	 */
	function display_selected_customer( $customer_id ) {
		/**	Récupération des données de l'utilisateur / Get selected user account informations	*/
		$customer_infos = get_userdata( (int) $customer_id );

		$_SESSION[ 'wps-pos-addon' ] = 1;

		$_SESSION[ 'billing_address' ] = $this->check_customer_billing_address( $customer_id );
		$shipping_address_id = get_option( 'wpshop_pos_addon_shop_address' );
		$_SESSION[ 'shipping_address' ] = ( !empty($shipping_address_id) && sanitize_key( $shipping_address_id ) ) ? $shipping_address_id : '';

		/**	Inclusion du fichier d'affichage / Include the display file	*/
		require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/customers', 'metabox-selected', 'customer' ) );
	}

	/**
	 * AJAX - Selection et affectation du client à la commande en cours / Set selected user as new order owner
	 */
	function ajax_pos_customer_choice() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'ajax_pos_customer_choice' ) )
			wp_die();

		$response = array(
			'status'		=> false,
			'element_type'	=> 'customer',
			'output'		=> '',
		);
		$selected_customer = ( !empty( $_POST ) && !empty( $_POST[ 'customer' ] ) && is_int( (int) $_POST[ 'customer' ] ) ) ? (int) $_POST[ 'customer' ] : null;

		if ( !empty( $selected_customer ) ) {
			$_SESSION[ 'cart' ][ 'customer_id' ] = $selected_customer;
			$response[ 'status' ] = true;

			/**	Affichage du client sélectionné pour la commande courante / Display selected customer account summary	*/
			ob_start();
			$this->display_selected_customer( $selected_customer );
			$response[ 'output' ] = ob_get_contents();
			ob_end_clean();
		}
		else {
			$response[ 'output' ] = __( 'No customer has been selected, please choose a customer or create a new one before try to create a new order', 'wps-pos-i18n' );
		}

		wp_die( json_encode( $response ) );
	}

	/**
	 * AJAX - Recherche dans la liste des clients du site / Search into website customer list
	 */
	function ajax_pos_customer_search() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'ajax_pos_customer_search' ) )
			wp_die();

		global $wpdb;
		$term = ( !empty( $_POST['term'] ) ) ? sanitize_text_field( $_POST['term'] ) : '';

		/** Get the default customer **/
		$default_customer_option = get_option( 'wpshop_pos_addon_default_customer_id' );
		if ( !empty($default_customer_option) ) {
			$default_user = get_user_by('id', $default_customer_option);
			if ( !empty($default_user) ) {
				$user_infos = array(
					'ID' => $default_user->ID,
					'last_name' => get_user_meta($default_user->ID, 'last_name', true),
					'first_name' => get_user_meta($default_user->ID, 'first_name', true),
					'email' => '-',
				);
				$customer_list[] = $user_infos;
			}
		}

		$wps_customer_ctr = new wps_customer_ctr();
		$customer_list = array_merge( $customer_list, $wps_customer_ctr->search_customer( $term ) );

		/**	Display the customer list into metabox	*/
		ob_start();
		require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/customers', 'customers' ) );
		$output = ob_get_contents();
		ob_end_clean();

		wp_die( $output );
	}

}

?>
