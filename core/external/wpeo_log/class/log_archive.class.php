<?php

namespace eoxia;

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( '\eoxia\log_archive_class' ) ) {
class log_archive_class extends \eoxia\Singleton_Util {
		protected function construct() {}

		public function get_archive_file( $name ) {
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
		* check_need_rotate  Checks if the file exceeds the maximum size
		*
		* @param string $file_link The file path to write
		*/
		public function check_need_rotate( $service, $name, $message ) {
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
		public function rename_current_file( $service, $name, $file_link ) {
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
	}

}
?>
