<?php if ( ! defined( 'ABSPATH' ) ) { exit;
}
class wps_message_ctr {

	/** Define the main directory containing the template for the current plugin
	 *
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 *
	 * @var string
	 */
	private $plugin_dirname = WPS_MESSAGE_DIR;
	public static $mails_display = 5;
	private static $xml_messages = null;
	function __construct() {

		/** Js */
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		// End if().
		$this->template_dir = WPS_MESSAGE_PATH . WPS_MESSAGE_DIR . '/templates/';
		// WP General actions
		add_action( 'admin_init', array( $this, 'wps_messages_init_actions' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'manage_' . WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE . '_posts_custom_column',  array( $this, 'messages_custom_columns' ) );
		add_filter( 'manage_edit-' . WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE . '_columns', array( $this, 'messages_edit_columns' ) );
		// Shortcodes
		add_shortcode( 'wps_message_histo', array( $this, 'display_message_histo_per_customer' ) );
		add_shortcode( 'order_customer_personnal_informations', array( $this, 'order_personnal_informations' ) );
		/** Ajax */
		add_action( 'wp_ajax_get_content_message', array( $this, 'get_content_message' ) );
	}

	/**
	 * For add js
	 */
	public function enqueue_scripts() {

		/** Css */
		add_thickbox();
		wp_register_style( 'wpeo-message-css', WPS_MESSAGE_URL . WPS_MESSAGE_DIR . '/assets/css/frontend.css', '', WPS_MESSAGE_VERSION );
		wp_enqueue_style( 'wpeo-message-css' );
		/** My js */
		wp_enqueue_script( 'wps-message-js', WPS_MESSAGE_URL . WPS_MESSAGE_DIR . '/assets/js/frontend.js', array( 'jquery', 'thickbox' ), WPS_MESSAGE_VERSION );
	}

	/**
	 * WPS Messages Admin init actions
	 */
	function wps_messages_init_actions() {

		$this->create_message_type();
	}

	/**
	 * Create the custom post type Message to manage them
	 */
	function create_message_type() {

		register_post_type( WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE, array(
			'labels' => array(
				'name' => __( 'Message', 'wpshop' ),
				'singular_name' => __( 'message', 'wpshop' ),
				'add_new' => __( 'Add message', 'wpshop' ),
				'add_new_item' => __( 'Add New message', 'wpshop' ),
				'edit' => __( 'Edit', 'wpshop' ),
				'edit_item' => __( 'Edit message', 'wpshop' ),
				'new_item' => __( 'New message', 'wpshop' ),
				'view' => __( 'View message', 'wpshop' ),
				'view_item' => __( 'View message', 'wpshop' ),
				'search_items' => __( 'Search messages', 'wpshop' ),
				'not_found' => __( 'No message found', 'wpshop' ),
				'not_found_in_trash' => __( 'No message found in trash', 'wpshop' ),
				'parent-item-colon' => '',
			),
			'description' => __( 'This is where store messages are stored.', 'wpshop' ),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'show_in_menu' => false,
			'hierarchical' => false,
			'show_in_nav_menus' => false,
			'rewrite' => false,
			'query_var' => true,
			'supports' => array( 'title', 'editor' ),
			'has_archive' => false,
		) );
	}

	/**
	 *	Create the different box for the product management page looking for the attribute set to create the different boxes
	 */
	function add_meta_boxes() {

		// Add message sending historic meta box
		add_meta_box( 'wpshop_message_histo',
			__( 'Message historic', 'wpshop' ),
			array( $this, 'message_histo_box' ),
		WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE, 'normal', 'high' );
	}

	/**
	 * META-BOX CONTENT - Display messages sending historic
	 *
	 * @param unknown_type $post
	 * @param unknown_type $params
	 */
	function message_histo_box( $post, $params ) {

		$output  = '<div id="message_histo_container">';
		$output .= $this->get_historic_message_by_type( $post->ID );
		$output .= '</div>';
		echo $output;
	}

	/**
	 * Display Message historic by type
	 *
	 * @param  integer $message_type_id : Message type ID
	 * @return string
	 */
	function get_historic_message_by_type( $message_type_id ) {

		global $wpdb;
		$output = '';
		if ( ! empty( $message_type_id ) ) {
			// Recover all sended messages
			$wps_message_mdl = new wps_message_mdl();
			$messages = $wps_message_mdl->get_messages_histo( $message_type_id );
			ob_start();
			require( wpshop_tools::get_template_part( WPS_MESSAGE_DIR, $this->template_dir, 'backend', 'message_historic' ) );
			$output .= ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	public static function get_xml_messages( $code = null ) {
		if ( is_null( self::$xml_messages ) ) {
			$xml_default_emails = file_get_contents( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/assets/datas/default_emails.xml' );
			$default_emails = new SimpleXMLElement( $xml_default_emails );
			self::$xml_messages = array();
			foreach ( $default_emails->xpath( '//emails/email' ) as $email ) {
				self::$xml_messages[ (string) $email->attributes()->code ] = array(
					'shop_type' => (string) $email->attributes()->shop_type,
					'object' => (string) $email->subject,
					'message' => (string) $email->content,
				);
			}
		}
		if ( is_null( $code ) ) {
			return self::$xml_messages;
		} elseif ( isset( self::$xml_messages[ $code ] ) ) {
			return self::$xml_messages[ $code ];
		} else {
			return false;
		}
	}
	/**
	 * Create all WPShop default messages
	 */
	public static function create_default_message() {
		// Read default emails for options creation
		foreach ( self::get_xml_messages() as $code => $email ) {
			if ( ( WPSHOP_DEFINED_SHOP_TYPE == $email['shop_type'] ) || ( 'sale' == WPSHOP_DEFINED_SHOP_TYPE ) ) {
				self::createMessage( $code, $email['object'], $email['message'] );
			}
		}
	}

	/**
	 * Create a message and save its ID in option database table
	 *
	 * @param string $code : Message code
	 * @param string $object : Message object code (Message Title)
	 * @param string $message : Message content
	 * @return integer message ID
	 */
	public static function createMessage( $code, $object = '', $message = '' ) {
		$id = 0;
		$xml_message = self::get_xml_messages( $code );
		$object = empty( $object ) ? $xml_message['object'] : $object;
		$message = empty( $message ) ? $xml_message['message'] : $message;
		$message_option = get_option( $code, null );
		if ( empty( $message_option ) || false === get_post_status( $message_option ) ) {
			$id = post_exists( __( $object , 'wpshop' ), self::customize_message( __( $message, 'wpshop' ) ) );
			if( $id == 0 ) {
				$new_message = array(
					'post_title' => __( $object , 'wpshop' ),
					'post_content' => self::customize_message( __( $message, 'wpshop' ) ),
					'post_status' => 'publish',
					'post_author' => 1,
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE,
				);
				$id = wp_insert_post( $new_message );
			}
			update_option( $code, $id );
		} else {
			$id = $message_option;
		}

		return $id;
	}

	/**
	 * Give the content by column
	 *
	 * @return array
	 */
	function messages_custom_columns( $column ) {

		global $post;
		$metadata = get_post_custom();
		switch ( $column ) {
			case 'extract':
				echo wp_trim_words( $post->post_content, 55 );
		break;
			case 'last_dispatch_date':
				if ( ! empty( $metadata['wpshop_message_last_dispatch_date'][0] ) ) {
					echo mysql2date( 'd F Y, H:i:s',$metadata['wpshop_message_last_dispatch_date'][0], true );
				} else { echo '-';
				}
		break;
		}
	}

	/**
	 * Set the custom colums
	 *
	 * @return array
	 */
	function messages_edit_columns( $columns ) {

		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Name', 'wpshop' ),
			'extract' => __( 'Extract from the message','wpshop' ),
			'date' => __( 'Creation date','wpshop' ),
			'last_dispatch_date' => __( 'Last dispatch date','wpshop' ),
		);
		return $columns;
	}

	/**
	 * Manage the display of Messages options configuration panel
	 *
	 * @param integer $current
	 * @return string
	 */
	function getMessageListOption( $current = 0 ) {

		$posts = query_posts( array(
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE,
			'posts_per_page' => '-1',
		) );
		$options = '';
		if ( ! empty( $posts ) ) {
			$options = '<option value="0">' . __( 'Select values from list', 'wpshop' ) . '</option>';
			foreach ( $posts as $p ) {
				$selected = $p->ID == $current ? ' selected="selected"': '';
				$options .= '<option value="' . $p->ID . '"' . $selected . '>' . $p->post_title . '</option>';
			}
		}
		wp_reset_query();
		return $options;
	}

	/**
	 * Display all messages which be sended to customer
	 *
	 * @param array   $args [message_id] : Message type id :
	 * @param integer $customer_id : ID to identifiate the customer
	 * @return string
	 */
	function display_message_histo_per_customer( $args, $customer_id = '' ) {
		$customer_id = ( ! empty( $customer_id ) ) ? $customer_id : ( isset( $args['cid'] ) && ! empty( $args['cid'] ) ? $args['cid'] : get_current_user_id() );
		$message_id = ( ! empty( $args ) && ! empty( $args['message_id'] ) ) ? $args['message_id'] : '';
		$message_elements = '';
		$wps_message_mdl = new wps_message_mdl();
		$messages_data = $wps_message_mdl->get_messages_histo( $message_id, $customer_id );

		$wps_customers_contacts = new WPS_Customers_Contacts();
		$contact_list = $wps_customers_contacts->get_customer_contact_list( get_post( $customer_id ) );
		$customer_email_contact_list = array();
		foreach ( $contact_list as $contact ) {
			$customer_email_contact_list[] = $contact['user_email'];
		}

		$messages_histo = array();
		foreach ( $messages_data as $meta_id => $messages ) :
			$i = 0;
			foreach ( $messages as $message ) :
				if ( in_array( $message['mess_user_email'], $customer_email_contact_list, true ) ) :
					$messages_histo[ $message['mess_dispatch_date'][0] ][ $i ]['title'] = $message['mess_title'];
					$messages_histo[ $message['mess_dispatch_date'][0] ][ $i ]['message'] = $message['mess_message'];
					$messages_histo[ $message['mess_dispatch_date'][0] ][ $i ]['dates'] = $message['mess_dispatch_date'];
					if ( ! empty( $message['mess_object_id'] ) ) {
						$messages_histo[ $message['mess_dispatch_date'][0] ][ $i ]['object'] = $message['mess_object_id'];
					}
					$i++;
				endif;
			endforeach;
		endforeach;

		ksort( $messages_histo );
		$messages_histo = array_reverse( $messages_histo );
		ob_start();
		require( wpshop_tools::get_template_part( WPS_MESSAGE_DIR, $this->template_dir, 'frontend', 'customer', 'messages' ) );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Add the message content and create a HTML structure message
	 *
	 * @param string $message : message content
	 * @return string
	 */
	public static function customize_message( $message ) {

		if ( ! empty( $message ) ) {
			ob_start();
			require( wpshop_tools::get_template_part( WPS_MESSAGE_DIR, WPS_MESSAGE_PATH . WPS_MESSAGE_DIR . '/templates/', 'backend', 'message_html_structure' ) );
			$message = ob_get_contents();
			ob_end_clean();
		}
		return $message;
	}

	/** Store a new message
	 *
	 * @return boolean
	 */
	function add_message( $recipient_id = 0, $email, $title, $message, $model_id, $object, $date = null ) {

		$date = empty( $date ) ? current_time( 'mysql', 0 ) : $date;
		$object_empty = array(
			'object_type' => '',
			'object_id' => 0,
		);
		$object = array_merge( $object_empty, $object );
		$historic = get_post_meta( $recipient_id, '_wpshop_messages_histo_' . $model_id . '_' . substr( $date, 0, 7 ), true );
		$data_to_insert = array(
			'mess_user_id' => $recipient_id,
			'mess_user_email' => $email,
			'mess_object_type' => $object['object_type'],
			'mess_object_id' => $object['object_id'],
			'mess_title' => $title,
			'mess_message' => $message,
			'mess_dispatch_date' => array( $date ),
		);
		$historic[] = $data_to_insert;
		update_post_meta( $recipient_id, '_wpshop_messages_histo_' . $model_id . '_' . substr( $date, 0, 7 ), $historic );
	}

	/**
	 * Create a custom Message, replace all "shortcodes" informations by dynamic informations
	 *
	 * @param string  $string : Message text
	 * @param array   $data : data to replace
	 * @param string  $model_name : Message model name
	 * @param boolean $duplicate_message : Duplicate a light message for historic storage
	 * @return string
	 */
	function customMessage( $string, $data, $model_name = '', $duplicate_message = false ) {

		$avant = array();
		$apres = array();
		$logo_option = get_option( 'wpshop_logo' );

		$data['your_shop_logo'] = ( ! empty( $logo_option ) ) ? '<img src="' . $logo_option . '" alt="' . get_bloginfo( 'name' ) . '" />' : '';

		foreach ( $data as $key => $value ) {
			$avant[] = '[' . $key . ']';
			switch ( $key ) {
				case 'order_content' :
					$apres[] = ( $duplicate_message ) ? '[order_content]' : $this->order_content_template_for_mail( $data['order_id'] );
					break;
				case 'order_addresses' :
					$apres[] = ( $duplicate_message ) ? '[order_addresses]' : $this->order_addresses_template_for_mail( $data['order_id'] );
					break;

				case 'order_billing_address' :
					$apres[] = ( $duplicate_message ) ? '[order_billing_address]' : $this->order_addresses_template_for_mail( $data['order_id'], 'billing' );
					break;

				case 'order_shipping_address' :
					$apres[] = ( $duplicate_message ) ? '[order_shipping_address]' : $this->order_addresses_template_for_mail( $data['order_id'], 'shipping' );
					break;

				case 'order_customer_comments' :
					$apres[] = ( $duplicate_message ) ? '[order_customer_comments]' : $this->order_customer_comment_template_for_mail( $data['order_id'] );
					break;
				case 'order_personnal_informations' :
					$apres[] = ( $duplicate_message ) ? '[order_personnal_informations]' : $this->order_personnal_informations();
					break;
				default :
					$apres[] = $value;
					break;
			}
		}
		$string = str_replace( $avant, $apres, $string );

		$string = apply_filters( 'wps_more_customized_message', $string, $data, $duplicate_message );

		if ( ($model_name != 'WPSHOP_NEW_ORDER_ADMIN_MESSAGE') ) {
			$string = preg_replace( '/\[(.*)\]/Usi', '', $string );
		}

		return $string;
	}

	/**
	 * Prepared the mail which would be send
	 *
	 * @param string $email : Receiver e-mail
	 * @param string $model_name : Message mmodel name
	 * @param array  $data : dynamic data to replace in e-mail
	 * @param string $object : message object
	 * @param file   $attached_file : File to attached to e-mail
	 */
	function wpshop_prepared_email( $email, $model_name, $data = array(), $object = array(), $attached_file = '' ) {
		global $wpdb;
		$data = apply_filters( 'wps_extra_data_to_send_in_email', $data );
		$model_id = get_option( $model_name, 0 );
		$query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->posts . ' WHERE ID = %s', $model_id );
		$post_message = $wpdb->get_row( $query );
		$duplicate_message = '';
		if ( ! empty( $post_message ) ) {
			$title = $this->customMessage( $post_message->post_title, $data, $model_name );
			$message = $this->customMessage( $post_message->post_content, $data, $model_name );
			// End if().
			if ( array_key_exists( 'order_content', $data ) || array_key_exists( 'order_addresses', $data ) || array_key_exists( 'order_customer_comments', $data ) ) {
				$duplicate_message = $this->customMessage( $post_message->post_content, $data, $model_name, true );
			}
			if ( ! empty( $email ) ) {
				$this->wpshop_email( $email, $title, $message, true, $model_id, $object, $attached_file, $duplicate_message );
			}
		}
	}

	/**
	 * Send an e-mail and store it in WPShop Sended e-mails historic
	 *
	 * @param string  $email : Receiver e-mail
	 * @param string  $title : Message title
	 * @param string  $message : Message content to send
	 * @param boolean $save : save message in historic
	 * @param integer $model_id : Message model ID
	 * @param array   $object : Message object
	 * @param file    $attachments : File to attached to e-mail
	 * @param string  $duplicate_message : lighter message to store
	 */
	function wpshop_email( $email, $title, $message, $save = true, $model_id, $object = array(), $attachments = '', $duplicate_message = '' ) {
		global $wpdb;
		// Sauvegarde
		if ( $save ) {
			$user = $wpdb->get_row( 'SELECT ID FROM ' . $wpdb->users . ' WHERE user_email="' . $email . '";' );
			$user_id = $user ? $user->ID : get_current_user_id();
			$query = $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_author = %d AND post_type = %s ', $user_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
			$user_post_id = $wpdb->get_var( $query );

			if ( ! empty( $duplicate_message ) ) {
				$this->add_message( $user_post_id, $email, $title, $duplicate_message, $model_id, $object );
			} else {
				$this->add_message( $user_post_id, $email, $title, $message, $model_id, $object );
			}
		}

		$emails = get_option( 'wpshop_emails', array() );
		$noreply_email = $emails['noreply_email'];
		// Split the email to get the name
		$vers_nom = substr( $email, 0, strpos( $email,'@' ) );

		// Headers du mail
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8\r\n";
		$headers .= 'From: ' . get_bloginfo( 'name' ) . ' <' . $noreply_email . '>' . "\r\n";

		// Mail en HTML
		@wp_mail( $email, $title, $message, $headers, $attachments );

		if ( ! empty( $attachments ) ) {
			unlink( $attachments );
		}
	}

	/**
	 * Order content Template
	 *
	 * @param integer $order_id : Order ID
	 * @return string
	 */
	function order_content_template_for_mail( $order_id ) {
		$message = '';
		if ( ! empty( $order_id ) ) {
			$currency_code = wpshop_tools::wpshop_get_currency( false );
			$orders_infos = get_post_meta( $order_id, '_order_postmeta', true );
			ob_start();
			require( wpshop_tools::get_template_part( WPS_MESSAGE_DIR, $this->template_dir, 'backend/mails', 'order_content_mail_template' ) );
			$message .= ob_get_contents();
			ob_end_clean();
		}
		return $message;
	}

	/**
	 * Order Adresses Template for e-mail
	 *
	 * @param integer $order_id : Order ID
	 * @param integer $address_type : Address type ID
	 * @return string
	 */
	function order_addresses_template_for_mail( $order_id, $address_type = '' ) {
		global $wpdb;
		$shipping_option = get_option( 'wpshop_shipping_address_choice' );
		$display_shipping = ( ! empty( $shipping_option ) && ! empty( $shipping_option['activate'] ) ) ? true : false;
		$message = '';
		if ( ! empty( $order_id ) ) {
			$order_addresses = get_post_meta( $order_id, '_order_info', true );
			if ( ! empty( $order_addresses ) ) {
				foreach ( $order_addresses as $key => $order_address ) {
					if ( ! empty( $order_address ) && ( empty( $address_type ) || $address_type == $key ) ) {

						if ( $key != 'shipping' || ($key == 'shipping' && $display_shipping) ) {
							$address_type_title = ( ! empty( $key ) && $key == 'billing' ) ? __( 'Billing address', 'wpshop' ) : __( 'Shipping address', 'wpshop' );
							$civility = '';
							if ( ! empty( $order_address['address']['civility'] ) ) {
								$query = $wpdb->prepare( 'SELECT label FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . ' WHERE id = %d', $order_address['address']['civility'] );
								$civility = $wpdb->get_var( $query );
							}

							// Address informations
							$customer_last_name = ( ! empty( $order_address['address']['address_last_name'] ) ) ? $order_address['address']['address_last_name'] : '';
							$customer_firtsname = ( ! empty( $order_address['address']['address_first_name'] ) ) ? $order_address['address']['address_first_name'] : '';
							$customer_company = ( ! empty( $order_address['address']['company'] ) ) ? $order_address['address']['company'] : '';
							$customer_address = ( ! empty( $order_address['address']['address'] ) ) ? $order_address['address']['address'] : '';
							$customer_zip_code = ( ! empty( $order_address['address']['postcode'] ) ) ? $order_address['address']['postcode'] : '';
							$customer_city = ( ! empty( $order_address['address']['city'] ) ) ? $order_address['address']['city'] : '';
							$customer_state = ( ! empty( $order_address['address']['state'] ) ) ? $order_address['address']['state'] : '';
							$customer_phone = ( ! empty( $order_address['address']['phone'] ) ) ? ' Tel. : ' . $order_address['address']['phone'] : '';
							$country = '';
							foreach ( unserialize( WPSHOP_COUNTRY_LIST ) as $key => $value ) {
								if ( ! empty( $order_address['address']['country'] ) && $key == $order_address['address']['country'] ) {
									$country = $value;
								}
							}
							$customer_country = $country;

							ob_start();
							require( wpshop_tools::get_template_part( WPS_MESSAGE_DIR, $this->template_dir, 'backend/mails', 'order_addresses_template_for_mail' ) );
							$message .= ob_get_contents();
							ob_end_clean();
						}
					}
				}
			}// End if().
		}// End if().
		return $message;
	}

	/**
	 * Order Customer's comment template for e-mail
	 *
	 * @param integer $order_id : Order ID
	 * @return string
	 */
	function order_customer_comment_template_for_mail( $order_id ) {
		global $wpdb;
		$message = '';
		if ( ! empty( $order_id ) ) {
			$query = $wpdb->prepare( 'SELECT post_excerpt FROM ' . $wpdb->posts . ' WHERE ID = %d', $order_id );
			$comment = $wpdb->get_var( $query );
			$order_infos = get_post_meta( $order_id, '_order_postmeta', true );
			if ( ! empty( $order_infos['order_key'] ) ) {
				$comment_title = __( 'Comments about the order', 'wpshop' );
			} else {
				$comment_title = __( 'Comments about the quotation', 'wpshop' );
			}
			ob_start();
			require( wpshop_tools::get_template_part( WPS_MESSAGE_DIR, $this->template_dir, 'backend/mails', 'order_email_customer_comments' ) );
			$message .= ob_get_contents();
			ob_end_clean();
		}
		return $message;
	}

	/**
	 * Order customer's Personnal informations template for e-mail
	 *
	 * @return string
	 */
	function order_personnal_informations() {
		global $wpdb;
		$user_id = get_current_user_id();
		$message = '';
		$customer_entity = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
		if ( ! empty( $customer_entity ) ) {

			$query = $wpdb->prepare( 'SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d AND status = %s', $customer_entity, 'valid' );
			$attributes_sets = $wpdb->get_results( $query );

			if ( ! empty( $attributes_sets ) ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPS_MESSAGE_DIR, $this->template_dir, 'backend/mails', 'order_personnal_informations_template_for_mail' ) );
				$message .= ob_get_contents();
				ob_end_clean();
			}
		}
		return $message;
	}

	/**
	 * Support historic messages
	 */
	function wpshop_messages_historic_correction() {
		global $wpdb;
		$query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key LIKE %s', '_wpshop_messages_histo_%' );
		$messages_histo = $wpdb->get_results( $query );

		foreach ( $messages_histo as $message ) {
			$query_user = $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_author = %d AND post_type = %s',  $message->post_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
			$user_post_id = $wpdb->get_var( $query_user );
			$wpdb->update( $wpdb->postmeta, array(
				'post_id' => $user_post_id,
				), array(
				'meta_id' => $message->meta_id,
			) );
		}
	}

	/** Ajax */
	/**
	 * Récupères le contenu du message
	 */
	public function get_content_message() {
		$_wpnonce = ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( ! wp_verify_nonce( $_wpnonce, 'get_content_message' ) ) {
			wp_die();
		}

		global $wpdb;
		$meta_id = (int) $_GET['meta_id'];

		$result = $wpdb->get_results( $wpdb->prepare( 'SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_id=%d', array( ($meta_id) ) ) );
		$result = unserialize( $result[0]->meta_value );
		$result = $result[0]['mess_message'];

		wp_die( $result );
	}

}
