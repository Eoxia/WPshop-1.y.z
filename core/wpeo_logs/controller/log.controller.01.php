<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Main controller file for WP logs module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 * @package wpeolog
 * @subpackage controller
 */

/**
 * Main controller class for WP logs module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 * @package wpeolog
 * @subpackage controller
 */

if ( !class_exists( "wpeologs_ctr" ) ) {

	/**
	 *
	 *
	 * @param string $service_name Le nom du service
	 * @param array $args
	 * @param int $criticality default 0
	 */
	function eo_log( $service_name, $args, $criticality = 0 ) {
		wpeologs_ctr::log_datas_in_files( $service_name, $args, $criticality );
	}

	class wpeologs_ctr {
		/**	Define the var containing directory name to logs	*/
		var $log_directory;

		static $file_separator = "!#logsep#!";

		public $array_criticality = array();

		/**
		 * construct - Initialize array criticality, add filter for content save pre and admin_enqueue_scripts
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

			// Service action
			add_action( 'admin_post_add', 'wpeo_log::add' );
			add_action( 'admin_post_add', 'wpeologs_ctr::add' );
			add_action( 'admin_post_edit_service', array( $this, 'edit_service' ) );
			add_action( 'admin_post_to_trash', array( $this, 'to_trash' ) );
			add_action( 'admin_post_file_to_trash', array( $this, 'file_to_trash' ) );

			/**	Call administration style definition	*/
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			// Transfert des anciens services
			$this->transfert();
		}

		/**
		 * Ajoutes le sous menu "Logs" au menu Tools de WordPress.
		 *
		 * @return void
		 */
		public function admin_menu() {
			add_submenu_page( 'tools.php', __( 'Logs', 'wpeolog-i18n' ), __( 'Logs', 'wpeolog-i18n' ), 'manage_options', 'wpeo-log-page', array( &$this, 'render_add_submenu_page' ) );
		}

		/**
		 * Le rendu du sous menu "Logs". Apelle le template main.php
		 */
		public function render_add_submenu_page() {

			$upload_dir = wp_upload_dir();
			$dir_file = $upload_dir['basedir'] . '/wpeolog/';

			$array_size_format = $this->get_array_size_format();
			$array_file_rotate_dropdown = array( 'on' => __( 'On', 'wpeolog-i18n' ), 'off' => __( 'Off', 'wpeolog-i18n' ) );
			$array_service = get_option( '_wpeo_log_settings' );
			$array_service = json_decode( $array_service, true );


			if ( !empty( $array_service ) ) {
				foreach ( $array_service as &$service ) {
					$service['error'] = $this->open_log( $service['name'] . '-error' );
					$service['warning'] = $this->open_log( $service['name'] . '-warning' );
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

			$array_data = $this->check_page();

			$count_warning = $array_data['count_warning'];
			$count_error = $array_data['count_error'];
			$count_info = count( $array_data['data']['data'] );

			$file = $array_data['data']['data'];
			$list_archive_file = $array_data['list_archive_file'];

			require_once( wpeo_template_01::get_template_part( WPEO_LOGS_DIR, WPEO_LOGS_TEMPLATES_MAIN_DIR, 'backend', 'main' ) );
		}

		private function check_page() {
			$action = !empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
			$type = !empty( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';

			if( empty( $action ) || empty( $type ) )
				return false;

			$service_id = !empty( $_GET['service_id'] ) ? (int) $_GET['service_id'] : 0;
			$key = !empty( $_GET['key'] ) ? (int) $_GET['key'] : 0;

			if ( 'view' == $action && isset( $service_id ) ) {
				$service = self::get_service_by_id( $service_id );

				if( $service == null )
					return false;

				$service_name = $service['name'];

				if ( !empty( $type ) ) {
					$service_name .= '-' . $type;
				}

				// Tous les fichiers
				$list_archive_file = $this->get_archive_file( $service_name );

				if( !empty( $key ) ) {
					$service_name .= '_' . $key;
				}

				$count_warning = $this->open_log( $service['name'] . '-warning' );
				$count_error = $this->open_log( $service['name'] . '-error' );

				$array_data = $this->open_log( $service_name );

				return array( 'data' => $array_data, 'list_archive_file' => $list_archive_file, 'count_warning' => $count_warning['count'], 'count_error' => $count_error['count'] );
			}
		}

		public function admin_enqueue_scripts($hook) {
			if( 'tools_page_wpeo-log-page' != $hook )
				return;

			wp_register_script( 'wps_statistics_js_chart', WPEO_LOGS_URL . '/asset/js/chart.js');
			wp_enqueue_script( 'wps_statistics_js_chart' );
		}

		private function open_log( $service_name ) {
			$upload_dir = wp_upload_dir();
			$dir_file = $upload_dir['basedir'] . '/wpeolog/' . $service_name . '.csv';

			if ( file_exists( $dir_file ) ) {
				$file = file( $dir_file );


				// Remove the first case empty
				array_shift( $file );

				foreach( $file as &$data ) {
					$data = explode( wpeologs_ctr::$file_separator, $data );
				}

				return array( 'data' => $file, 'count' => count( $file ) );
			}
			else {
				return array( 'data' => null, 'count' => 0 );
			}
		}

		private function get_archive_file( $name ) {
			// Get archive
			$upload_dir = wp_upload_dir();
			$dir_file = $upload_dir['basedir'] . '/wpeolog/';

			$array_glob_file = glob( $dir_file . $name . '*.csv' );

			foreach( $array_glob_file as &$glob_file ) {
				$glob_file = explode('/', $glob_file);
				$glob_file = $glob_file[count($glob_file) - 1];
			}

			return $array_glob_file;
		}

		/**
		 * DEBUG LOG - Save a file on the server with content for loggin different action sended
		 *
		 * @param string $service (Name module or post type)
		 * @param array $array_message ('object_id', 'message')
		 * @param int $criticality The message crit rate (0-2)
		 */
		public static function log_datas_in_files( $name, $array_message, $criticality ) {
			$upload_dir = wp_upload_dir();

			$backtrace = debug_backtrace();

			// On récupère le service
			$service = self::get_service_by_name( $name );

			if( $service == null ) {
			/** Créer le service s'il n'existe pas */
				$service = self::add( $name );
			}

			wp_mkdir_p( $upload_dir[ 'basedir' ] . '/wpeolog/' );

			if( $service == null )
				return null;

			$message = "
";
			$message .= current_time('mysql', 0) . self::$file_separator;
			$message .= get_current_user_id() . self::$file_separator;
			$message .= '"' . $name . '"' . self::$file_separator;
			$message .= ( !empty( $array_message['object_id'] ) ? $array_message['object_id'] : 'Not found' ) . self::$file_separator;

			// For post type
			if(!empty($array_message['previous_element'])) {
				$message .= '"' . base64_encode(serialize($array_message['previous_element'])) . '"' . self::$file_separator;
				$message .= '"' . base64_encode(serialize($array_message['previous_element_metas'])) . '"' . self::$file_separator;
			}

			if(!empty($array_message['message'])) {
				$message .= '"' . $array_message['message'] . '"' . self::$file_separator;
			}
			$message .= $criticality . self::$file_separator . $name . self::$file_separator;

			if ( !empty( $backtrace ) ) {
				foreach ( $backtrace as $t ) {
					$message .= ( !empty( $t['file'] ) ? $t['file'] : '' ) . ' line ' . ( !empty( $t['line'] ) ? $t['line'] : '' ) . ' function ' . $t['function'] . '()<br />';
				}
			}

			self::check_need_rotate( $service, $name . '-info', $message);

			$fp = fopen( $upload_dir[ 'basedir' ] . '/wpeolog/' . $name . '-info.csv', 'a');
			fwrite($fp, $message);
			fclose($fp);

			if( 2 <= $criticality ) {
				self::check_need_rotate( $service, $name . '-error', $message);

				$fp = fopen( $upload_dir[ 'basedir' ] . '/wpeolog/' . $name . '-error.csv', 'a');
				fwrite($fp, $message);
				fclose($fp);
			}
			else if( 1 == $criticality ) {
				self::check_need_rotate( $service, $name . '-warning', $message);

				$fp = fopen( $upload_dir[ 'basedir' ] . '/wpeolog/' . $name . '-warning.csv', 'a');
				fwrite( $fp, $message );
				fclose( $fp );
			}
		}

		/**
		* check_need_rotate  Checks if the file exceeds the maximum size
		*
		* @param string $file_link The file path to write
		*/
		public static function check_need_rotate( $service, $name, $message ) {
			$upload_dir = wp_upload_dir();

			$max_size = $service['size'];

			$file_link = $upload_dir[ 'basedir' ] . '/wpeolog/' . $name . '.csv';

			if( file_exists( $file_link ) ) {
				// Get full message
				$message = file_get_contents( $file_link ) . $message;

				$file_size = filesize( $file_link );

				if($file_size >= $max_size)
					self::rename_current_file( $service, $name, $file_link );
				else if(strlen($message) >= $max_size)
					self::rename_current_file( $service, $name, $file_link );

				return $file_link;
			}

		}

		/**
		 * rename_current_file - Rename the current file
		 *
		 * @param string $service
		 * @param string $file_link
		 */
		public static function rename_current_file( $service, $name, $file_link ) {
				$upload_dir = wp_upload_dir();

				$number_archive = $service['number'];

				if( file_exists ( $file_link ) ) {
					$file_explode = explode('.csv', $file_link);
					$get_all_file = glob($file_explode[0] . '*.csv');
					array_shift($get_all_file);
					arsort($get_all_file);

					foreach($get_all_file as $full_file) {
						$file = explode('/', $full_file);
						$file_name = $file[count($file) - 1];
						$file_name = explode('.', $file_name);

						$file_name[0]++;
						rename($full_file, $upload_dir[ 'basedir' ] . '/wpeolog/' . $file_name[0] . '.csv');

						// Check if not execeed the number archive
						$count = explode('_', $file_name[0]);

						if($count[1] > $number_archive && file_exists($upload_dir[ 'basedir' ] . '/wpeolog/' . $file_name[0] . '.csv')) {
							unlink($upload_dir[ 'basedir' ] . '/wpeolog/' . $file_name[0] . '.csv');
						}
					}
					rename( $file_link, $file_explode[0] . '_1.csv' );
				}
		}

		/**
		 * Récupères le service selon son nom.
		 *
		 * @param string $name Le nom du service
		 * @return array | null
		 */
		public static function get_service_by_name( $name ) {
			$array_service = get_option( '_wpeo_log_settings', array() );

			$getted_service = null;

			if ( !empty( $array_service ) ) {
				$array_service = !is_array( $array_service ) ? json_decode( $array_service, true ) : $array_service;

				foreach ( $array_service as $service ) {
					if( $service['name'] == $name ) {
						$getted_service = $service;
						break;
					}
				}
			}

			return $getted_service;
		}

		public static function get_service_by_id( $id ) {
			$array_service = get_option( '_wpeo_log_settings', array() );

			$getted_service = null;

			if ( !empty( $array_service ) ) {
				$array_service = json_decode( $array_service, true );

				foreach ( $array_service as $key => $service ) {
					if( $key == $id ) {
						$getted_service = $service;
						break;
					}
				}
			}

			return $getted_service;
		}


		/**
		 * convert to - Convert format to "oc" or deconvert
		 *
		 * @param float $input
		 * @param string $format
		 * @param boolean $convert
		 * @return float|number
		 */
		function convert_to($input, $format, $convert = true) {
			if($format == 'oc')
				return $input;

			$multiple = 0;

			if($format == 'ko')
				$multiple = 1024;
			else if($format == 'mo')
				$multiple = 1048576;
			else if($format == 'go')
				$multiple = 1073741824;

			if($convert)
				return $input * $multiple;
			else
				return $input / $multiple;
		}

		/**
		 * get_array_size_format
		 *
		 * @return multitype:string
		 */
		function get_array_size_format() {
			return array('oc' => 'Octets', 'ko' => 'Ko', 'mo' => 'Mo', 'go' => 'Go');
		}

		/**
		 * Crée un service par défaut et l'ajoutes au tableau JSON _wpeo_log_settings.
		 * Renvoie un message de type updated en transient pour afficher qu'un
		 * nouveau service à été crée.
		 *
		 * @param string $name Le nom du service
		 * @return void
		 */
		public static function add( $name ) {
			$data_service = array(
					'active' 		=> true,
					'name'			=> !empty( $name ) ? $name : 'new_log',
					'size' 			=> '1000000',
					'format' 		=> 'ko',
					'rotate'		=> false,
					'number' 		=> 0,
					'created_date'	=> current_time( 'mysql' ),
			);

			$array_current_settings = get_option( '_wpeo_log_settings' );
			if ( !empty( $array_current_settings ) ) {
				$array_current_settings = json_decode( $array_current_settings, true );
			}
			else {
				$array_current_settings = array();
			}

			$array_current_settings[] = $data_service;
			$success = update_option( '_wpeo_log_settings', json_encode( $array_current_settings ) );

			if ( $success ) {
				set_transient( 'log_message', json_encode( array( 'type' => 'updated', 'message' => __( 'A new service has been created!', 'wpeolog-i18n' ) ) ) );
			}

			if ( !empty( $name ) ) {
				return $data_service;
			}
			else {
				wp_safe_redirect( wp_get_referer() );
				die();
			}
		}

		public function edit_service() {
			$services = !empty($_POST['service']) ? (array) $_POST['service'] : array();

			if ( empty( $services ) || !is_array( $services ) ) {
				set_transient( 'log_message', json_encode( array( 'type' => 'error', 'message' => __( 'Invalid data to update service' ), 'wpeolog-i18n' ) ) );
				wp_safe_redirect( wp_get_referer() );
				die();
			}


			foreach( $services as &$service ) {
				// sanitize
				$service['active'] = sanitize_key( $service['active'] );
				$service['name'] = sanitize_key( $service['name'] );
				$service['format'] = sanitize_key( $service['format'] );
				$service['rotate'] = sanitize_key( $service['rotate'] );
				$service['number'] = ( int )$service['number'];
				$service['size'] = ( int )$this->convert_to( $service['size'], $service['format'] );
			}

			unset( $service );


			$array_current_setting = get_option( '_wpeo_log_settings' );
			$array_current_setting = json_decode( $array_current_setting, true );

			$service = !empty($_POST['service']) ? (array) $_POST['service'] : array();
			$array_current_setting = array_replace( $array_current_setting, $service );

			$success = update_option( '_wpeo_log_settings', json_encode( $array_current_setting ) );


			if( $success ) {
				set_transient( 'log_message', json_encode( array( 'type' => 'updated', 'message' => __( 'The services has been updated!', 'wpeolog-i18n' ) ) ) );
			}

			wp_safe_redirect( wp_get_referer() );
			die();
		}

		public function to_trash() {
			$wpnonce = !empty( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';

			if( empty( $wpnonce ) ) {
				wp_safe_redirect( wp_get_referer() );
				die();
			}

			$service_id = !empty( $_GET['service_id'] ) ? (int) $_GET['service_id'] : 0;

			if ( !isset( $wpnonce ) || !wp_verify_nonce( $wpnonce, 'to_trash_' . $service_id ) ) {
				wp_safe_redirect( wp_get_referer() );
				die();
			}

			$array_current_setting = get_option( '_wpeo_log_settings' );
			$array_current_setting = json_decode( $array_current_setting, true );

			if ( !empty( $array_current_setting[$service_id] ) ) {
				unset( $array_current_setting[$service_id] );
				$success = update_option( '_wpeo_log_settings', json_encode( $array_current_setting ) );

				if( $success ) {
					set_transient( 'log_message', json_encode( array( 'type' => 'updated', 'message' => __( sprintf( 'The service %d has been deleted!', $service_id ), 'wpeolog-i18n' ) ) ) );
				}
			}
			else {
				set_transient( 'log_message', json_encode( array( 'type' => 'error', 'message' => __( sprintf( 'The service %d is invalid!', $service_id ), 'wpeolog-i18n' ) ) ) );
			}

			wp_safe_redirect( wp_get_referer() );
			die();
		}

		/**
		 * Supprimes un fichier par le biais de la fonction unlink
		 *
		 * @param $_GET['file_name'] Le nom du fichier à supprimer
		 * @return void
		 */
		public function file_to_trash() {
			$upload_dir = wp_upload_dir();
			$dir_file = $upload_dir['basedir'] . '/wpeolog/';

			$file_name = sanitize_file_name( $_GET['file_name'] );

			$success = unlink( $dir_file . $file_name );
			if ( $success )
				set_transient( 'log_message', json_encode( array( 'type' => 'updated', 'message' => __( 'The file has been deleted!', 'wpeolog-i18n' ) ) ) );
			wp_safe_redirect( wp_get_referer() );
			die();
		}

		public function transfert() {
			$array_service = get_option( '_wpeo_log_settings' );

			$new_array_service = array();

			if( !empty( $array_service['my_services'] ) && is_array( $array_service['my_services'] ) ) {
			  foreach ( $array_service['my_services'] as $element ) {
					$new_array_service[] = array(
						"active" => $element["service_active"] == 1 ? true : false,
						"name" => $element["service_name"],
						"size" => $element["service_size"],
						"format" => $element["service_size_format"],
						"rotate" => $element["service_rotate"] == 1 ? true : false,
						"number" => $element["service_file"],
						"created_date" => current_time( "mysql" )
					);
				}

				update_option( '_wpeo_log_settings', json_encode( $new_array_service ) );
				update_option( '_wpeo_log_old_settings', $array_service );
			}
		}
	}
}

?>
