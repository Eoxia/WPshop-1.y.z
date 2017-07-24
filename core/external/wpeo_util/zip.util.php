<?php
/**
 * Gestion des fichiers ZIP
 *
 * @package Evarisk\Plugin
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\ZIP_Util' ) ) {
	/**
	 * Gestion des fichiers ZIP
	 *
	 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
	 * @version 1.1.0.0
	 */
	class ZIP_Util extends \eoxia\Singleton_Util {
		/**
		 * Le constructeur obligatoirement pour utiliser la classe \eoxia\Singleton_Util
		 *
		 * @return void nothing
		 */
		protected function construct() {}

		/**
		 *
		 * Dézippes l'archive $zip_path dans $destination_path
		 * Retournes tous les noms des fichiers contenus dans l'archive
		 *
		 * @param  string $zip_path         Le chemin vers l'archive.
		 * @param  string $destination_path Le chemin d'extraction des fichiers.
		 * @return $data = array('state' => true|false, 'list_file' => array( 0 => filename ) );
		 */
		public function unzip( $zip_path, $destination_path ) {
			$zip = new \ZipArchive;
			$data = array( 'state' => true, 'list_file' => array() );

			if ( $zip->open( $zip_path ) === true ) {
				if ( ! $zip->extractTo( $destination_path ) ) {
					$data['state'] = false;
				}

				// Récupérations de tous les fichiers.
				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$filename = $zip->getNameIndex( 0 );

					if ( isset( $filename ) ) {
						$data['list_file'][] = $filename;
					}
				}

				$zip->close();
			} else {
				$data['state'] = false;
			}

			return $data;
		}
	}
} // End if().
