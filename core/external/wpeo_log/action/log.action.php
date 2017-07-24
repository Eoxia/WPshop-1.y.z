<?php
namespace eoxia;

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( '\eoxia\log_action' ) ) {
	class log_action {
		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

			// Service action
			add_action( 'admin_post_add', array( $this, 'add' ) );
			add_action( 'admin_post_reset', array( $this, 'reset' ) );
			add_action( 'admin_post_edit_service', array( $this, 'edit_service' ) );
			add_action( 'admin_post_to_trash', array( $this, 'to_trash' ) );
			add_action( 'admin_post_file_to_trash', array( $this, 'file_to_trash' ) );
		}

		public function reset() {
			delete_option( '_wpeo_log_settings' );

			wp_safe_redirect( wp_get_referer() );
			die();
		}

		/**
		* Ajoutes le sous menu "Logs" au menu Tools de WordPress.
		*
		* @return void
		*/
		public function admin_menu() {
			add_submenu_page( 'tools.php', __( 'Logs', 'digirisk' ), __( 'Logs', 'digirisk' ), 'manage_options', 'wpeo-log-page', array( &$this, 'render_add_submenu_page' ) );
		}

		/**
		* Le rendu du sous menu "Logs". Apelle le template main.php
		*/
		public function render_add_submenu_page() {
			$upload_dir = wp_upload_dir();
			$dir_file = $upload_dir['basedir'] . '/wpeolog/';
			$array_size_format = log_page_class::g()->get_array_size_format();
			$array_file_rotate_dropdown = array( 'on' => __( 'On', 'digirisk' ), 'off' => __( 'Off', 'digirisk' ) );
			$array_service = get_option( '_wpeo_log_settings' );
			$array_service = json_decode( $array_service, true );
			if ( !empty( $array_service ) ) {
				foreach ( $array_service as &$service ) {
					$service['error'] = log_page_class::g()->open_log( $service['name'] . '-error' );
					$service['warning'] = log_page_class::g()->open_log( $service['name'] . '-warning' );
				}
			}
			unset( $service );

			$count_service_active = 0;
			$count_service_desactive = 0;

			$page_transient = get_transient( 'log_message' );
			if ( !empty( $page_transient ) ) {
				delete_transient( 'log_message' );
				$page_transient = json_decode( $page_transient, true );
			}

			$array_data = log_page_class::g()->check_page();

			$count_warning = $array_data['count_warning'];
			$count_error = $array_data['count_error'];
			$count_info = count( $array_data['data']['data'] );
			$file = $array_data['data']['data'];
			$list_archive_file = $array_data['list_archive_file'];

			require ( Config_Util::$init['main']->full_plugin_path . \eoxia\Config_Util::$init['wpeo_log']->path . '/view/main.view.php' );
		}

	}

	new log_action();
}

?>
