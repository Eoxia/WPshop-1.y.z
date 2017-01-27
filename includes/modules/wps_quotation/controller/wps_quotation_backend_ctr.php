<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_quotation_backend_ctr {
	/*
	 * Declare filter and actions
	 */
	public function __construct() {
		/**	Appel des styles pour l'administration / Call style for administration	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_assets' ) );

		/**	Définition du filtre permettant le champs d'activation du module avec un code obtenu sur la boutique / Define the filter allowing to display an input for module activation with a given code from the shop	*/
		add_filter( 'wps-addon-extra-info', array( $this, 'display_addon_activation_form' ) );
		/**	Déclaration de la requête Ajax permettant de vérifier le code d'activation et d'activer le module le cas échéant / Declare ajax request allowing to check the code and activate addon in case the code is correct	*/
		add_action( 'wp_ajax_check_code_for_activation', array( $this, 'check_code_for_activation' ) );

		add_filter( 'wps-filter-free-product-bton-tpl', array( $this, 'wps_free_product_bton_tpl' ) );
		add_action( 'wp_ajax_wps_free_product_form_page_tpl', array( $this, 'wps_free_product_form_page_tpl' ) );
		add_action( 'wp_ajax_wps_create_new_free_product', array( $this, 'wps_create_new_free_product' ) );
		add_action( 'init', array( $this, 'wps_free_product_post_status' ) );
	}


	/**
	 * Inclusion des feuilles de styles pour l'administration / Admin css enqueue
	 */
	function admin_assets($hook) {
		if ( $hook != 'settings_page_wpshop_option' )
			return;

		wp_enqueue_script( 'wps_quotation_admin_js', WPS_QUOTATION_URL . '/assets/js/backend.js', array( 'jquery', ), WPSHOP_VERSION );
	}



	/*
	 * Create a new post status for free products
	 */
	function wps_free_product_post_status(){
		register_post_status( 'free_product', array(
			'label'                     => __( 'Free product', 'wpshop' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
		) );
	}
	/*
	 * Template for display button - Filter : 'wps_orders\templates\backend\product-listing\wps_orders_product_listing.php'
	 * @param integer $order_id ID of order
	 */
	public function wps_free_product_bton_tpl($order_id) {
		$order_post_meta = get_post_meta( $order_id, '_wpshop_order_status', true );
		if ( 'completed' != $order_post_meta ) {
			require ( wpshop_tools::get_template_part( WPS_QUOTATION_DIR, WPS_QUOTATION_TEMPLATES_MAIN_DIR, "backend", "add_free_product_bton_tpl") );
		}
	}
	/*
	 * Template for display form (AjaxForm) - Call from : Line 3 'templates\backend\add_free_product_form_page_tpl.php'
	 */
	public function wps_free_product_form_page_tpl() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_free_product_form_page_tpl' ) )
			wp_die();

		$order_id = ( !empty($_GET['oid']) ) ? intval( $_GET['oid']) : null;
		require ( wpshop_tools::get_template_part( WPS_QUOTATION_DIR, WPS_QUOTATION_TEMPLATES_MAIN_DIR, "backend", "add_free_product_form_page_tpl") );
		wp_die();
	}
	/*
	 * Ajax - Free product function creation
	 * @return boolean $status Status of request ajax
	 * @return string $message Message of error
	 * @return integer $pid Product ID
	 */
	public function wps_create_new_free_product() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'wps_create_new_free_product' ) )
			wp_die();

		global $wpdb;
		$status = false;
		$output = __('Error at product creation!', 'wpshop');
		$new_product_id = -1;

		$post_title = ( !empty($_POST['post_title']) ) ? sanitize_text_field( $_POST['post_title'] ) : -1;
		$post_content = ( !empty($_POST['post_content']) ) ? sanitize_text_field( $_POST['post_content'] ) : '';
		$attributes = ( !empty($_POST['attribute']) ) ? (array) $_POST['attribute'] : array();

		if( $post_title != -1 ) {
			$new_product_id = wp_insert_post( array(
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
				'post_status' => 'free_product',
				'post_title' => $post_title,
				'post_content' => $post_content,
			) );
			if( !is_object( $new_product_id ) ) {
				$attribute_set_list = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code('wpshop_product'));
				$id_attribute_set = null;
				foreach( $attribute_set_list as $attribute_set ) {
					if( $attribute_set->slug == 'free_product' ) {
						$id_attribute_set = $attribute_set->id;
						break;
					}
				}
				update_post_meta( $new_product_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute_set_id', $id_attribute_set );
				$data_to_save['post_ID'] = $data_to_save['product_id'] = intval( $new_product_id );
				$data_to_save['wpshop_product_attribute'] = ( !empty($attributes) ) ? $attributes : array();
				$data_to_save['user_ID'] = get_current_user_id();
				$data_to_save['action'] = 'editpost';
				$status = false;
				$output = __('Product created partially!', 'wpshop');
				if( !empty($new_product_id) && !empty( $data_to_save['user_ID'] ) ) {
					$product_class = new wpshop_products();
					$product_class->save_product_custom_informations( $new_product_id, $data_to_save );
					$status = true;
					$output = __('Product created successfully.', 'wpshop');
				}
			}
		}

		echo json_encode( array( 'status' => $status, 'message' => $output, 'pid' => $new_product_id ) );
		wp_die();
	}


	/**
	 * DISPLAY - Affiche le champs permettant d'entrer le code d'activation du module / Display the field allowing to enter the code for module activation
	 *
	 * @param string $module Le nom du module en cours d'affichage et pour lequel il faut afficher le champs/ The module being displayed and to add input for
	 */
	function display_addon_activation_form( $module ) {
		if ( WPS_QUOTATION_DIR == $module ) {

			/** Récupération du statut du module devis / Get current quotation addon state */
			$addon_option = get_option( 'wpshop_addons' );

			if ( empty( $addon_option ) || ( !empty( $addon_option[ 'WPSHOP_ADDONS_QUOTATION' ] ) && ( false === ( $addon_option['WPSHOP_ADDONS_QUOTATION']['activate'] ) ) ) ) {
				$quotation_module_def = get_plugin_data( WPS_QUOTATION_PATH . '/wps_quotation.php' );
				require ( wpshop_tools::get_template_part( WPS_QUOTATION_DIR, WPS_QUOTATION_TEMPLATES_MAIN_DIR, "backend", "addon", "activation" ) );
			}
			else if ( !empty( $addon_option[ 'WPSHOP_ADDONS_QUOTATION' ] ) && ( true === ( $addon_option['WPSHOP_ADDONS_QUOTATION']['activate'] ) ) ) {
				require ( wpshop_tools::get_template_part( WPS_QUOTATION_DIR, WPS_QUOTATION_TEMPLATES_MAIN_DIR, "backend", "addon", "activated" ) );
			}
		}
	}

	function check_code_for_activation() {
		$_wpnonce = !empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'check_code_for_activation' ) )
			wp_die();

		$response = array(
			'status' => false,
			'message' => __( 'Activation code for quotation addon is invalid, please check it and try again.', 'wpshop' ),
		);
		$required_code = wpshop_tools::get_plugin_validation_code( WPS_QUOTATION_DIR, site_url() );
		$code = sanitize_text_field( $_POST[ 'code' ] );
		if ( $code === $required_code ) {
			$this->action_to_do_to_activate();
			$response = array(
				'status' => true,
				'message' => __( 'Quotation addon have been successfully activated.', 'wpshop' ),
			);
		}

		wp_die( json_encode( $response ) );
	}

	/**
	 * Action to do when the activation code is OK
	 */
	function action_to_do_to_activate(){
		global $wpdb;
		/** Activate the plug in **/
		$addon_option = get_option( 'wpshop_addons' );
		$addon_option['WPSHOP_ADDONS_QUOTATION']['activate'] = true;
		$addon_option['WPSHOP_ADDONS_QUOTATION']['activation_date'] = current_time('mysql', 0);

		update_option( 'wpshop_addons', $addon_option );

		/** Activate the Quotation attribute **/
		$wpdb->update( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid'), array('code' => 'quotation_allowed') );

		$attribute_def = wpshop_attributes::getElement( 'quotation_allowed', '"valid"', 'code' );

		/** Add the attribute in attributes groups section **/
		$query = $wpdb->prepare('SELECT id, attribute_set_id FROM ' .WPSHOP_DBT_ATTRIBUTE_GROUP. ' WHERE code = %s', 'general');
		$attribute_set_sections = $wpdb->get_results( $query );

		if ( !empty($attribute_set_sections) ) {
			$entity_type_def = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
			if ( !empty( $entity_type_def) ) {
				foreach ( $attribute_set_sections as $attribute_set_section ) {
					$query = $wpdb->prepare('SELECT COUNT(*) AS count_attribute_affectation FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d AND attribute_id = %d', $entity_type_def, $attribute_set_section->attribute_set_id, $attribute_set_section->id, $attribute_def->id);
					$count_attribute_affectation = $wpdb->get_var( $query );

					if ( $count_attribute_affectation == 0 ) {
						$query = $wpdb->prepare('SELECT MAX(position) AS max_position FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d', $entity_type_def, $attribute_set_section->attribute_set_id, $attribute_set_section->id);
						$max_position = $wpdb->get_var( $query );
						/** Insert attribute detail **/
						$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $entity_type_def, 'attribute_set_id' =>  $attribute_set_section->attribute_set_id, 'attribute_group_id' => $attribute_set_section->id, 'attribute_id' => $attribute_def->id, 'position' => (int)$max_position + 1) );
					}
				}
			}
		}
	}

}
