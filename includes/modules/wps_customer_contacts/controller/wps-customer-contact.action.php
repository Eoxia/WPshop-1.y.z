<?php
/**
 * Gestion des contacts pour les clients dans WPShop / WpShop Customer contacts management
 *
 * @author Eoxia dev team <dev@eoxia.com>
 * @version 1.0.0.0
 * @package Customers
 * @subpackage modules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialise les scripts JS et CSS du Plugin
 * Ainsi que le fichier MO
 */
class WPS_Customers_Contacts {

	/**
	 * Définition de la clé permettant de retrouver la liste des contacts du clients actuel / Define the meta key allowing to get the contact list for current customer.
	 *
	 * @var string
	 */
	private $user_contact_list_meta_key = '_wpscrm_associated_user';

	/**
	 * Instanciation des actions pour la gestion des clients
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		// Appel du filtre permettant d'ajouter des informations dans la liste des méthodes de contacts dans les profil utilisateur.
		add_filter( 'user_contactmethods', array( $this, 'add_contact_method_to_user' ), 20, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'callback_admin_enqueue_scripts' ), 11 );
		add_action( 'wp_enqueue_scripts', array( $this, 'callback_enqueue_scripts' ), 11 );

		/** Affichage de la liste déroulante des clients associés dans le compte de l'utilisateur connecté / Display associated customer list into connected user account */
		add_action( 'wps_user_dashboard_header', array( $this, 'callback_customer_dashboard' ), 10, 2 );

		// Association des utilisateurs à un client / Associate a user to a customer.
		add_action( 'wp_ajax_wps_customer_contacts_associate', array( $this, 'ajax_callback_associate_user' ) );
		// Dissociation d'un utilisateur à un client / Dissociate a user from a customer.
		add_action( 'wp_ajax_wps_customer_contacts_dissociate', array( $this, 'ajax_callback_dissociate_user' ) );
		// Changement de l'utilisateur par défaut (auteur) d'un client / Change the default user (author) for a customer.
		add_action( 'wp_ajax_wps_customer_contacts_change_default', array( $this, 'ajax_callback_change_default_user' ) );

		// Changement de compte client dans le frontend pour l'utilisateur connecté / Switch between customer account for current connected user.
		add_action( 'wp_ajax_wps_customer_switch_to', array( $this, 'ajax_callback_switch_customer' ) );
	}

	/**
	 * Mise en place des metaboxes pour gérer les clients / Create metaboxes for customer managemenr
	 *
	 * @param string $post_type Le type d'élément sur lequel se trouve l'utilisateur / Element type where the user is.
	 */
	function add_meta_box( $post_type ) {
		add_meta_box( 'wps_customer_contacts', __( 'Customer contact list', 'wpshop' ), array( $this, 'customer_contact_list_callback' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'high' );
	}

	/**
	 * Initialise le fichier style.min.css et backend.min.js du plugin.
	 *
	 * @return void nothing
	 */
	public function callback_admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( in_array( $screen->id, array( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ), true ) ) {
			wp_register_style( 'wps_customer_contacts-style', WPS_CUST_CONTACT_URL . 'assets/css/wps_customer_contacts.backend.css', array(), WPSHOP_VERSION );
			wp_enqueue_style( 'wps_customer_contacts-style' );
			wp_register_script( 'wps_customer_contacts-script', WPS_CUST_CONTACT_URL . 'assets/js/wps_customer_contacts.backend.js', array( 'jquery', 'jquery-form', 'jquery-ui-autocomplete' ), WPSHOP_VERSION );
			wp_localize_script( 'wps_customer_contacts-script', 'wpshopCrm', array(
				'confirm_user_dissociation' 	=> __( 'Are you sure you want to dissociate this user', 'wpshop' ),
				'confirm_change_default_user' => __( 'Are you sure you want to change default user for current customer?', 'wpshop' ),
			) );
			wp_enqueue_script( 'wps_customer_contacts-script' );
			add_thickbox();
		}
	}

	/**
	 * Enqueue scripts and style in frontend
	 */
	public function callback_enqueue_scripts() {
		$pagename = get_query_var( 'pagename' );
		if ( in_array( $pagename, array( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ), true ) ) {
			wp_register_style( 'task-manager-frontend-style', WPS_CUST_CONTACT_URL . 'assets/css/frontend.css', array(), WPSHOP_VERSION );
			wp_enqueue_style( 'task-manager-frontend-style' );
		}
		wp_register_script( 'wps_customer_contacts-frontend-script', WPS_CUST_CONTACT_URL . 'assets/js/wps_customer_contacts.frontend.js', array(), WPSHOP_VERSION );
		wp_enqueue_script( 'wps_customer_contacts-frontend-script' );
	}

	/**
	 * Affichage de la liste des contacts associés au client en cours d'édition / Display associated user list for current customer
	 *
	 * @param WP_Post $customer La définition principale du client actuellement en cours d'édition / Current edited customer definition.
	 */
	function customer_contact_list_callback( $customer ) {
		$users = $this->get_customer_contact_list( $customer );

		/** Display user list for current customer */
		require( wpshop_tools::get_template_part( WPS_CUST_CONTACT_DIR, WPS_CUST_CONTACT_TPL, 'backend', 'contacts' ) );
	}

	/**
	 * Récupération de la liste des contacts pour un client
	 *
	 * @param  WP_Post $customer La définition complète du client pour lequel récupérer les contacts.
	 *
	 * @return [type]           [description]
	 */
	function get_customer_contact_list( $customer ) {
		/** Define user list */
		$users = array();

		/** Get associated users' */
		$associated_users = (array) get_post_meta( $customer->ID, $this->user_contact_list_meta_key, true );
		$user_list = wp_parse_id_list( array_merge( $associated_users, array( $customer->post_author ) ) );
		if ( ! empty( $user_list ) ) {
			foreach ( $user_list as $user_id ) {
				if ( 0 !== $user_id ) {
					$associated_user = get_user_by( 'ID', $user_id );
					$user_metas = get_user_meta( $user_id );
					if ( is_object( $associated_user ) ) {
						$users[ $user_id ] = wp_parse_args( $associated_user->data, array(
							'last_name'		=> $associated_user->last_name,
							'first_name'	=> $associated_user->first_name,
							'is_default'	=> ( $user_id === (int) $customer->post_author ? true : false ),
							'metas'				=> $user_metas,
						) );
					}
				}
			}
		}

		return $users;
	}

	/**
	 * Affichage de la liste des clients disponible pour l'utilisateur actuellement connecté / Display customer list for currently connected user
	 *
	 * @param integer $user_id Identifiant de l'utilisateur actuellement connecté / User identifier currenly connected.
	 * @param WP_User $account_user La définition du compte utilisateur / The user account definition.
	 */
	function callback_customer_dashboard( $user_id, $account_user ) {
		$customers = array();

		$customers_from_posts = new WP_Query( array(
			'post_type'				=> WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
			'author'					=> $user_id,
			'posts_per_page'	=> -1,
			'post_status'			=> 'any',
		) );
		if ( $customers_from_posts->have_posts() ) {
			$customers = array_merge( $customers, $customers_from_posts->posts );
		}

		$customers_from_meta = new WP_Query( array(
			'post_type'				=> WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
			'posts_per_page'	=> -1,
			'post_status'			=> 'any',
			'author__not_in'  => array( $user_id ),
			'meta_query'			=> array(
				array(
					'key'			=> $this->user_contact_list_meta_key,
					'value'		=> 'i:' . $user_id . ';',
					'compare'	=> 'LIKE',
					'type'		=> 'CHAR',
				),
			),
		) );
		if ( $customers_from_meta->have_posts() ) {
			$customers = array_merge( $customers, $customers_from_meta->posts );
		}
		usort( $customers, function( $a, $b ) {
			if ( $a->ID === $b->ID ) {
				return 0;
			}
			return ($a < $b) ? -1 : 1;
		});

		if ( ! empty( $customers ) && ( 1 < count( $customers ) ) ) {
			/** Display user list for current customer */
			require_once( wpshop_tools::get_template_part( WPS_CUST_CONTACT_DIR, WPS_CUST_CONTACT_TPL, 'frontend', 'customer', 'choice' ) );
		}
	}

	/**
	 * Filtre la liste des méthodes de contact de WordPress pour ajouter le numéro de téléphone / Filter WordPress default contact method list in order to add phone numbre.
	 *
	 * @param array   $contact_methods La liste actuelle des méthodes permettant de contacter l'utilisateur / The current method list to contact a user.
	 * @param WP_user $user          L'utilisateur en court d'édition / The current edited user.
	 */
	public function add_contact_method_to_user( $contact_methods, $user ) {
		$wps_contact_method = array(
			'wps_phone'	=> __( 'Phone number', 'wpshop' ),
		);

		return array_merge( $wps_contact_method, $contact_methods );
	}

	/**
	 * Ajax callback - Associate a user to a customer
	 */
	function ajax_callback_associate_user() {
		check_ajax_referer( 'wps_customer_contacts_associate' );

		$customer_id = ! empty( $_POST ) && ! empty( $_POST['CID'] ) && is_int( (int) $_POST['CID'] ) ? (int) $_POST['CID'] : null;
		$user_id = ! empty( $_POST ) && ! empty( $_POST['UID'] ) && is_int( (int) $_POST['UID'] ) ? (int) $_POST['UID'] : null;

		$associated_users = get_post_meta( $customer_id, $this->user_contact_list_meta_key, true );
		if ( ! empty( $associated_users ) ) {
			$new_associated_users = wp_parse_id_list( array_merge( $associated_users, array( $user_id ) ) );
		} else {
			$new_associated_users = array( $user_id );
		}

		update_post_meta( $customer_id, $this->user_contact_list_meta_key, $new_associated_users );
		$this->customer_contact_list_callback( get_post( $customer_id ) );
		wp_die();
	}

	/**
	 * Ajax callback - Associate a user to a customer
	 */
	function ajax_callback_dissociate_user() {
		check_ajax_referer( 'wps_customer_contacts_dissociate' );

		$customer_id = ! empty( $_POST ) && ! empty( $_POST['CID'] ) && is_int( (int) $_POST['CID'] ) ? (int) $_POST['CID'] : null;
		$user_id = ! empty( $_POST ) && ! empty( $_POST['UID'] ) && is_int( (int) $_POST['UID'] ) ? (int) $_POST['UID'] : null;

		$associated_users = get_post_meta( $customer_id, $this->user_contact_list_meta_key, true );
		foreach ( $associated_users as $key => $id ) {
			if ( $id === $user_id ) {
				unset( $associated_users[ $key ] );
			}
		}

		update_post_meta( $customer_id, $this->user_contact_list_meta_key, $associated_users );
		$this->customer_contact_list_callback( get_post( $customer_id ) );
		wp_die();
	}

	/**
	 * Ajax callback - Change the default user for a customer
	 */
	function ajax_callback_change_default_user() {
		check_ajax_referer( 'wps_customer_contacts_change_default' );

		$customer_id = ! empty( $_POST ) && ! empty( $_POST['CID'] ) && is_int( (int) $_POST['CID'] ) ? (int) $_POST['CID'] : null;
		$user_id = ! empty( $_POST ) && ! empty( $_POST['UID'] ) && is_int( (int) $_POST['UID'] ) ? (int) $_POST['UID'] : null;
		$old_user_id = ! empty( $_POST ) && ! empty( $_POST['current_default_user_id'] ) && is_int( (int) $_POST['current_default_user_id'] ) ? (int) $_POST['current_default_user_id'] : null;

		$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, array( 'post_author' => $user_id ), array( 'ID' => $customer_id ) );
		$customer_default_user_changes = get_post_meta( $customer_id, '_wps_customer_default_user_histo', true );
		$customer_default_user_changes[] = array(
			'date'						=> current_time( 'mysql' ),
			'old_post_author'	=> $old_user_id,
			'update_author'		=> get_current_user_id(),
		);
		update_post_meta( $customer_id, '_wps_customer_default_user_histo', $customer_default_user_changes );

		$associated_users = get_post_meta( $customer_id, $this->user_contact_list_meta_key, true );
		if ( ! empty( $associated_users ) ) {
			$new_associated_users = wp_parse_id_list( array_merge( $associated_users, array( $old_user_id ) ) );
		} else {
			$new_associated_users = array( $old_user_id );
		}
		update_post_meta( $customer_id, $this->user_contact_list_meta_key, $new_associated_users );

		$this->customer_contact_list_callback( get_post( $customer_id ) );
		wp_die();
	}

	/**
	 * Ajax callback - Change le cookie de l'utilisateur pour passer sur un autre client
	 */
	function ajax_callback_switch_customer() {
		// check_ajax_referer( 'wps-customer-switch-to' );

		$customer_id = ! empty( $_POST ) && ! empty( $_POST['cid'] ) && is_int( (int) $_POST['cid'] ) ? (int) $_POST['cid'] : null;

		unset( $_COOKIE['wps_current_connected_customer'] );
		setcookie( 'wps_current_connected_customer', $customer_id, strtotime( '+30 days' ), SITECOOKIEPATH, COOKIE_DOMAIN, is_ssl() );

		wp_die( wp_json_encode( array( 'status' => true ) ) );
	}

}

new WPS_Customers_Contacts();
