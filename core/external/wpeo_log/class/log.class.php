<?php
namespace eoxia;

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( '\eoxia\log_class' ) ) {
	class log_class extends \eoxia\Singleton_Util {
		public static $file_separator = "!#logsep#!";
		public static $timestart = array();
		public static $timeend = array();

		protected function construct() {
			$upload_dir = wp_upload_dir();
			wp_mkdir_p( $upload_dir[ 'basedir' ] . '/wpeolog/' );
		}

		public function start_ms( $custom_name ) {
			if (empty( self::$timestart[$custom_name] ) ) {
				self::$timestart[$custom_name] = microtime(true);
			}
		}

		public static function exec( $service_name, $custom_name, $message = '', $data = array(), $criticality = 0 ) {
			if ( \eoxia\Config_Util::$init['external']->wpeo_log->log ) {
				$ms = 0;

				if ( !empty( self::$timestart[$custom_name] ) ) {
					self::$timeend[$custom_name] = microtime(true);
					$ms = number_format( (self::$timeend[$custom_name] - self::$timestart[$custom_name]), \eoxia\Config_Util::$init['external']->wpeo_log->number_decimal_ms );
					unset(self::$timestart[$custom_name]);
				}

				$service = log_service_class::g()->create_service( $service_name );

				if ( $service === null ) {
					return null;
				}

				$data['error'] = error_get_last();

				$output_to_log = "
	";

				// La date
				$output_to_log .= current_time( 'mysql' ) . self::$file_separator;
				// L'auteur
				$output_to_log .= get_current_user_id() . self::$file_separator;
				// Le nom du service
				$output_to_log .= $service_name . self::$file_separator;
				// message
				$output_to_log .= base64_encode($message) . self::$file_separator;
				// Les données
				$output_to_log .= addslashes( json_encode( $data ) ) . self::$file_separator;
				// Temps d'éxécution
				$output_to_log .= $ms . self::$file_separator;
				// Le niveau du log
				$output_to_log .= $criticality;

				if ( !empty( $output_to_log ) ) {
					self::write_to_file( $service, $service_name, $output_to_log, $criticality );
				}
			}
		}

		public static function write_to_file( $service, $name_service, $output_to_log, $criticality ) {
			log_archive_class::g()->check_need_rotate( $service, $name_service . '-info.csv', $output_to_log );
			$upload_dir = wp_upload_dir();
			$fp = fopen( $upload_dir[ 'basedir' ] . '/wpeolog/' . $name_service . '-info.csv', 'a');
			fwrite( $fp, $output_to_log );
			fclose( $fp );

			if ( 2 <= $criticality ) {
				$type = 'error';
			}

			if ( 1 == $criticality ) {
				$type = 'warning';
			}

			if ( !empty( $type ) ) {
				log_archive_class::g()->check_need_rotate( $service, $name_service . '-' . $type . '.csv', $output_to_log );

				$fp = fopen( $upload_dir[ 'basedir' ] . '/wpeolog/' . $name_service . '-' . $type . '.csv', 'a');
				fwrite( $fp, $output_to_log );
				fclose( $fp );
			}
		}
	}

}
?>
