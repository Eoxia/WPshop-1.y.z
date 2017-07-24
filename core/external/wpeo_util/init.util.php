<?php
/**
 * Initialise le fichier digirisk.config.json et tous les fichiers .config.json
 *
 * @package Evarisk\Plugin
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\Init_Util' ) ) {
	/**
	 * Cette classe initialise tous les fichiers config.json
	 *
	 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
	 * @version 1.1.0.0
	 */
	class Init_Util extends \eoxia\Singleton_Util {
		/**
		 * Le constructeur obligatoirement pour utiliser la classe \eoxia\Singleton_Util
		 *
		 * @return void nothing
		 */
		protected function construct() {}

		/**
		 * Appelles les méthodes read_core_util_file_and_include et init_main_config ainsi que init_module
		 *
		 * @return void nothing
		 */
		public function exec( $path, $plugin_slug ) {
			self::read_core_util_file_and_include( $path, $plugin_slug );
			self::init_main_config( $path, $plugin_slug );
			self::init_external( $path, $plugin_slug );
			Config_Util::$init['main'] = new \stdClass();
			Config_Util::$init['main']->full_plugin_path = $path;
			self::init_module( $path, $plugin_slug );
		}

		/**
		 * Listes la liste des fichiers ".utils" dans le dossier ./core/external/wpeo_util/
		 *
		 * @return mixed Si le dossier $path_to_core_folder_util n'existe pas ou si ce n'est pas un dossier, ça retourne un objet WP_Error
		 */
		private function read_core_util_file_and_include( $path, $plugin_slug ) {
			$path_to_core_folder_util = $path . 'core/external/wpeo_util/';
			if ( ! file_exists( $path_to_core_folder_util ) ) {
				return new \WP_Error( 'broke', __( 'Impossible de charger les fichiers .utils', $plugin_slug ) );
			}

			if ( ! is_dir( $path_to_core_folder_util ) ) {
				return new \WP_Error( 'broke', __( '$path_to_core_folder_util n\'est pas un dossier', $plugin_slug ) );
			}

			$list_file_name = scandir( $path_to_core_folder_util );

			if ( ! $list_file_name || ! is_array( $list_file_name ) ) {
				return new \WP_Error( 'broke', __( 'Impossible de charger les fichiers .utils', $plugin_slug ) );
			}

			if ( ! empty( $list_file_name ) ) {
				foreach ( $list_file_name as $file_name ) {
					if ( '.' !== $file_name && '..' !== $file_name && 'index.php' !== $file_name && '.git' !== $file_name ) {
						$file_path = realpath( $path_to_core_folder_util . $file_name );
						require_once( $file_path );
					}
				}
			}
		}

		/**
		 * Appelle la méthode init_config avec le fichier digirisk.config.json
		 *
		 * @return void nothing
		 */
		private function init_main_config( $path, $plugin_slug ) {
			$main_config_path = $plugin_slug . '.config.json';
			\eoxia\Config_Util::g()->init_config( $path . $main_config_path );
			Config_Util::$init[ $plugin_slug ]->path = $path;
		}

		private function init_external( $path, $plugin_slug ) {
			if ( empty( Config_Util::$init['external'] ) ) {
				Config_Util::$init['external'] = new \stdClass();
			}

			\eoxia\External_Util::g()->exec( $path, $plugin_slug );
		}

		/**
		 * Appelle la méthode exec_module de \eoxia\Module_Util pour initialiser tous les modules
		 *
		 * @return void nothing
		 */
		private function init_module( $path, $plugin_slug ) {
			\eoxia\Module_Util::g()->exec_module( $path, $plugin_slug );
		}
	}
} // End if().
