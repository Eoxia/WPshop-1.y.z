<?php
/**
 * Gestion des inclusions de fichier
 *
 * @package Evarisk\Plugin
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\Include_Util' ) ) {
	/**
	 * Gestion des inclusions de fichier
	 *
	 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
	 * @version 1.1.0.0
	 */
	class Include_Util extends \eoxia\Singleton_Util {
		/**
		 * Le constructeur obligatoirement pour utiliser la classe \eoxia\Singleton_Util
		 *
		 * @return void nothing
		 */
		protected function construct() {}

		/**
		 * Récupères les fichiers dans le dossier $folder_path
		 *
		 * @param  string $folder_path Le chemin du dossier.
		 * @return void                Nothing
		 */
		public function in_folder( $folder_path ) {
			$list_file_name = scandir( $folder_path );

			if ( ! empty( $list_file_name ) ) {
				foreach ( $list_file_name as $file_name ) {
					if ( '.' !== $file_name && '..' !== $file_name && 'index.php' !== $file_name && '.git' !== $file_name ) {
						$file_path = realpath( $folder_path . $file_name );
						// 'log_class::g()->start_ms( 'digi_boot_module_in_folder' );
						$file_success = require_once( $file_path );
						if ( class_exists( __NAMESPACE__ . '\log_service_class' ) ) {
							// 'log_class::g()->exec( 'digi_boot', 'digi_boot_module_in_folder', 'Inclus le fichier : ' . $file_name, array( 'path' => $file_path, 'success' => $file_success ) );
						}
					}
				}
			}

		}
	}
} // End if().
