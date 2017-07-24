<?php
namespace eoxia;

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( '\eoxia\log_page_class' ) ) {
	class log_page_class extends \eoxia\Singleton_Util {
		protected function construct() {}

		public function get_array_size_format() {
			return array('oc' => 'Octets', 'ko' => 'Ko', 'mo' => 'Mo', 'go' => 'Go');
		}

		public function open_log( $service_name ) {
			$upload_dir = wp_upload_dir();
			$dir_file = $upload_dir['basedir'] . '/wpeolog/' . $service_name . '.csv';

			if ( file_exists( $dir_file ) ) {
				$file = file( $dir_file );
				// Remove the first case empty
				array_shift( $file );
				foreach( $file as &$data ) {
					$data = explode( log_class::$file_separator, $data );
					$data[3] = base64_decode( $data[3] );
					$data[4] = stripcslashes( $data[4] );
				}

				return array( 'data' => $file, 'count' => count( $file ) );
			}
			else {
				return array( 'data' => null, 'count' => 0 );
			}
		}

		public function check_page() {
			if( empty( $_GET['action'] ) || empty( $_GET['type'] ) )
				return false;

			$action = sanitize_text_field( $_GET['action'] );
			$type = sanitize_text_field( !empty( $_GET['type'] ) ?  $_GET['type'] : '' );

			if ( !isset( $_GET['service_id'] ) ) return false;
			else $service_id = (int) $_GET['service_id'];
			if ( isset( $_GET['key'] ) && 0 !== (int) $_GET['key'] ) $key = (int) $_GET['key'];

			if ( 'view' == $action && isset( $service_id ) ) {
				$service = log_service_class::g()->get_service_by_id( $service_id );
				if( $service == null )
					return false;
				$service_name = $service['name'];
				if ( !empty( $type ) ) {
					$service_name .= '-' . $type;
				}

				// Tous les fichiers
				$list_archive_file = log_archive_class::g()->get_archive_file( $service_name );
				if( !empty( $key ) ) {
					$service_name .= '_' . $key;
				}

				$count_warning = $this->open_log( $service['name'] . '-warning' );
				$count_error = $this->open_log( $service['name'] . '-error' );
				$array_data = $this->open_log( $service_name );

				return array( 'data' => $array_data, 'list_archive_file' => $list_archive_file, 'count_warning' => $count_warning['count'], 'count_error' => $count_error['count'] );
			}
		}

	}
}

?>
