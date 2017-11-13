<?php
/**
 * Customer administration management file
 *
 * @package WPShop
 * @subpackage Customer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage Customer administration functions
 *
 * @author ALLEGRE Jérôme - EOXIA
 */
class wps_customer_admin {

	/**
	 * Instanciation de la gestion des clients dans l'administration
	 */
	function __construct() {
		/**
		 * Modification des textes par défaut de WordPress (Publier -> Enregister) pour les pages de gestions des clients
		 *
		 * @since 1.4.4.3
		 */
		add_filter( 'gettext', array( $this, 'change_admin_cpt_text_filter' ), 20, 3 );

		/** Define templates path */
		$this->template_dir = WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . '/templates/';

		/** WordPress hooks */
		add_action( 'admin_enqueue_scripts', array( $this, 'customer_admin_assets' ) );
		add_action( 'save_post', array( $this, 'save_entity_customer' ), 10, 2 );
		add_filter( 'post_updated_messages', array( $this, 'customer_post_messages' ) );
		add_filter( 'map_meta_cap', array( $this, 'callback_disallow_customer_total_deletion' ), 10, 4 );

		/** Ajax Listener */
		add_action( 'wp_ajax_wps_order_refresh_customer_informations', array( $this, 'wps_order_refresh_customer_informations' ) );
		add_action( 'wp_ajax_wps_load_customer_creation_form_in_admin', array( $this, 'wps_load_customer_creation_form_in_admin' ) );
		add_action( 'wp_ajax_wps_order_refresh_customer_list', array( $this, 'wps_order_refresh_customer_list' ) );
	}

	/**
	 * Change the text in the admin for my customer
	 *
	 * @param  string $translated_text   Texte traduit.
	 * @param  string $untranslated_text Identifiant du texte non traduit.
	 * @param  string $domain            Domaine de traduction.
	 *
	 * @return string                    [description]
	 */
	function change_admin_cpt_text_filter( $translated_text, $untranslated_text, $domain ) {
		global $typenow;

		if ( is_admin() && WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS === $typenow ) {
			switch ( $untranslated_text ) {
				case 'Publish':
					$translated_text = __( 'Save','wpshop' );
				break;
			}
		}
		return $translated_text;
	}

	/**
	 * Add metas in user when customer is modified
	 *
	 * @param integer $post_id The current saved post id / L'identifiant post qui vient d'être sauvegardé.
	 * @param WP_Post $post THe entire post currently saved / Le post qui vient d'être sauvegardé.
	 */
	public function save_entity_customer( $post_id, $post ) {
		if ( ( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS !== $post->post_type ) || ( 'auto-draft' === $post->post_status ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		$custom_args = array();
		$default_args = array(
			'ID'					=> $post_id,
		);
		if ( 'trash' !== $post->post_status ) {
			$custom_args['post_status']	= 'draft';
		}

		if ( ! empty( $_POST ) && ! empty( $_POST['wps_customer_contacts_default_id'] ) && ( (int) $_POST['wps_customer_contacts_default_id'] !== $post->post_author )  ) { // WPCS: CSRF ok.
			$custom_args['post_author'] = (int) $_POST['wps_customer_contacts_default_id'];
		}

		/** Récupération des informations envoyées par l'administrateur et ajout de la possibilité d'étendre/modifier les données envoyées avec un filtre */
		$sended_attributes = ( ! empty( $_POST ) && ! empty( $_POST['attribute'] ) ? (array) $_POST['attribute'] : array() ); // WPCS: CSRF ok.
		$data_to_save = apply_filters( 'wps_save_customer_extra_filter', $sended_attributes );
		if ( ! empty( $sended_attributes ) ) {
			/*    Save the attributes values into wpshop eav database    */
			$update_from = '';
			$lang = WPSHOP_CURRENT_LOCALE;
			if ( ! empty( $data_to_save['icl_post_language'] ) ) {
				$query = $wpdb->prepare( "SELECT locale FROM {$wpdb->prefix}icl_locale_map WHERE code = %s", $data_to_save['icl_post_language'] );
				$lang = $wpdb->get_var( $query ); // WPCS: unprepared SQL ok.
			}
			wpshop_attributes::saveAttributeForEntity( $sended_attributes, wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ), $post_id, $lang, $update_from );
		}

		/**
		 * Sauvegarde complémentaire pour le client: changement du statut du post pour passage en brouillon pour ne pas afficher les clients dans le site.
		 *
		 * Nécessite la suppresion du hook de sur la fonction courante avant le lancement et la réactivation de ce même hook pour ne pas provoquer de boucle infinie
		 *
		 * @see https://codex.wordpress.org/Function_Reference/wp_update_post
		 */
		remove_action( 'save_post', array( $this, 'save_entity_customer' ) );
		wp_update_post( wp_parse_args( $custom_args, $default_args ) );
		add_action( 'save_post', array( $this, 'save_entity_customer' ) );
	}

	/**
	 * Modification des inforamtions affichées lors de l'enregistrement des clients dans le panneau d'administration.
	 * Change admin notices when a customer is saved from admin panel. In order to avoid user mistake.
	 *
	 * @param  array $messages Initial messages list to change.
	 *
	 * @return array           New messages list.
	 */
	function customer_post_messages( $messages ) {
		$customer = get_post();

		$messages[ WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ] = array(
			0		=> '', // Unused. Messages start at index 1.
			1		=> __( 'Customer updated.', 'wpshop' ),
			2		=> __( 'Custom field updated.', 'wpshop' ),
			3		=> __( 'Custom field deleted.', 'wpshop' ),
			4		=> __( 'Customer updated.', 'wpshop' ),
			5		=> isset( $_GET['revision'] ) ? sprintf( __( 'Customer restored to revision from %s', 'wpshop' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6		=> __( 'Customer saved.', 'wpshop' ),
			7		=> __( 'Customer saved.', 'wpshop' ),
			8		=> __( 'Customer submitted.', 'wpshop' ),
			9		=> sprintf(
				__( 'Customer scheduled for: <strong>%1$s</strong>.', 'wpshop' ),
				date_i18n( __( 'M j, Y @ G:i', 'wpshop' ), strtotime( $customer->post_date ) )
			),
			10	=> __( 'Customer saved.', 'wpshop' ),
		);

		return $messages;
	}

	/**
	 * CORE - Install all extra-modules in "Modules" folder
	 */
	function install_modules() {
		/**	Define the directory containing all exrta-modules for current plugin	*/
		$module_folder = WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . '/modules/';

		/**	Check if the defined directory exists for reading and including the different modules	*/
		if ( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );
			foreach ( $parent_folder_content as $folder ) {
				if ( $folder && '.' !== substr( $folder, 0, 1 ) && is_dir( $module_folder . $folder ) ) {
					$child_folder_content = scandir( $module_folder . $folder );
					if ( file_exists( $module_folder . $folder . '/' . $folder . '.php' ) ) {
						$f = $module_folder . $folder . '/' . $folder . '.php';
						include( $f );
					}
				}
			}
		}
	}

	/**
	 * Add Scripts
	 */
	function customer_admin_assets() {
		global $current_screen;
		if ( in_array( $current_screen->post_type, array( WPSHOP_NEWTYPE_IDENTIFIER_ORDER, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ), true ) ) {
			wp_enqueue_script( 'wps_customer_admin_js', WPS_ACCOUNT_URL . '/' . WPS_ACCOUNT_DIR . '/assets/backend/js/wps_customer_backend.js', '', WPSHOP_VERSION );
			wp_register_style( 'wpshop-modules-customer-backend-styles', WPS_ACCOUNT_URL . '/' . WPS_ACCOUNT_DIR . '/assets/backend/css/backend.css', '', WPSHOP_VERSION );
			wp_enqueue_style( 'wpshop-modules-customer-backend-styles' );
		}
	}


	/**
	 * AJAX - Customer creation form
	 */
	function wps_load_customer_creation_form_in_admin() {
		check_ajax_referer( 'wps_load_customer_creation_form_in_admin' );

		echo do_shortcode( '[wps_signup display="admin"]' );
		wp_die();
	}


	/**
	 * AJAX - Reload Customer list
	 */
	function wps_order_refresh_customer_list() {
		check_ajax_referer( 'wps_order_refresh_customer_list' );

		$status = false;
		$response = '';
		$customer_id = ( ! empty( $_POST['customer_id'] ) ) ? intval( $_POST['customer_id'] ) : null;
		if ( ! empty( $customer_id ) ) {
			$wps_customer = new wps_customer_ctr();
			$response = $wps_customer->customer_select( $customer_id );
			$status = true;
		}

		wp_die( wp_json_encode( array( 'status' => $status, 'response' => $response ) ) );
	}

	/**
	 * AJAX - Refresh customer informations
	 *
	 * @version 1.4.4.3
	 */
	function wps_order_refresh_customer_informations() {
		check_ajax_referer( 'wps_order_refresh_customer_informations' );

		$status = false;
		$output = '';
		$customer_id = ( ! empty( $_POST['customer_id'] ) ) ? intval( $_POST['customer_id'] ) : null;
		$order_id = ( ! empty( $_POST['order_id'] ) ) ? intval( $_POST['order_id'] ) : null;
		if ( ! empty( $order_id ) && ! empty( $customer_id ) ) {
			ob_start();
			self::display_customer_informations_in_order( $order_id, $customer_id );
			$output = ob_get_clean();
			$status = true;
		}

		wp_die( wp_json_encode( array( 'status' => $status, 'output' => $output ) ) );
	}

	/**
	 * Affichage du contenu le boite contenant les informations du client dans les commandes / Display the metabox content for customer in orders
	 *
	 * @since 1.4.4.3
	 *
	 * @param  integer $order_id    L'identifiant de la commande sur laquelle on se trouve / The order identifier we are on.
	 * @param  integer $customer_id L'identifiant du client pour lequel on veut afficher les informations / The customer identifier we want to display information for.
	 */
	public static function display_customer_informations_in_order( $order_id, $customer_id = 0 ) {
		// Récupération des informations de la commande dans la base de données / Retrieve order information from database.
		$order_metadata = get_post_meta( $order_id, '_order_postmeta', true );
		$order_infos = get_post_meta( $order_id, '_order_info', true );

		// On vérifie si un client est doit être sélectionné / Check if a customer is already selected.
		$customer_id = ( ! empty( $customer_id ) ? (int) $customer_id : ( ! empty( $order_metadata['customer_id'] )  ? $order_metadata['customer_id'] : '' ) );

		// Récupération des informations d'adresses du client connecté / Retrieve addresses informations for the current customer.
		// Adresse de facturation / Billing address.
		$billing_address_option = get_option( 'wpshop_billing_address' );
		$billing_address_option = ( ! empty( $billing_address_option ) && ! empty( $billing_address_option['choice'] ) ) ? $billing_address_option['choice'] : '';

		// Adresse de livraison / Shipping address.
		$shipping_address_content = '';
		$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );
		// Vérification de l'activation ou non des livraisons pour l'affichage des adresses correspondantes / Check shipping addresses state in order to display or not addresses.
		$shipping_addresses_activated = ( ! empty( $shipping_address_option ) && ! empty( $shipping_address_option['activate'] ) ) ? true : false;

		// Vérification du statut de la commande pour définir les actions disponibles / Check order status in order to know what action are available.
		$order_update_close = false;
		if ( empty( $order_metadata ) || empty( $order_metadata['customer_id'] ) || ( 'awaiting_payment' === $order_metadata['order_status'] ) ) {
			$order_update_close = true;
		}

		if ( ! empty( $customer_id ) ) {
			// Récupération des informations sur le client / Retrieve customer datas.
			$wps_account = new wps_account_ctr();
			$customer_datas = $wps_account->display_account_informations( $customer_id, false, true );

			if ( true === $order_update_close ) {
				$wps_address = new wps_address();
				$addresses = $wps_address->display_addresses_interface( $customer_id, true );
			} else {
				$wps_address_admin = new wps_address_admin();
				$addresses = $wps_address_admin->display_customer_address_in_order( $order_id, 'billing' );
				if ( true === $shipping_addresses_activated ) {
					$addresses .= $wps_address_admin->display_customer_address_in_order( $order_id, 'shipping' );
				}
			}
		}

		require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_TPL, 'backend/customer-informations', 'wps-order-customer-informations' ) );
	}

	/**
	 * [callback_disallow_customer_total_deletion description]
	 *
	 * @param array   $caps    [description].
	 * @param string  $cap     [description].
	 * @param integer $user_id [description].
	 * @param array   $args    [description].
	 *
	 * @return array          [description]
	 */
	public function callback_disallow_customer_total_deletion( $caps, $cap, $user_id, $args ) {
		// Nothing to do.
		if ( 'delete_post' !== $cap || empty( $args[0] ) ) {
			return $caps;
		}

		// Target the payment and transaction post types.
		if ( in_array( get_post_type( $args[0] ), array( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ), true ) ) {
			$caps[] = 'do_not_allow';
		}

		return $caps;
	}

}
