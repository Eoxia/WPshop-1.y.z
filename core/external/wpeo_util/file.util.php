<?php
/**
 * Gestion des fichiers
 *
 * @package Evarisk\Plugin
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\File_Util' ) ) {
	/**
	 * Gestion des fichiers
	 *
	 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
	 * @version 1.1.0.0
	 */
	class File_Util extends \eoxia\Singleton_Util {
		/**
		 * Le constructeur obligatoirement pour utiliser la classe \eoxia\Singleton_Util
		 *
		 * @return void nothing
		 */
		protected function construct() {}

		/**
		 * Upload le fichier $file et créer les méta données de ce fichier.
		 *
		 * @param  mixed $file        Les données du fichier.
		 * @param  int   $element_id  L'ID de l'élément pour l'attachement du fichier.
		 * @return int            		L'id de l'attachement
		 */
		public static function move_file_and_attach( $file, $element_id ) {
			if ( ! is_string( $file ) || ! is_int( $element_id ) || ! is_file( $file ) ) {
				return false;
			}

			$wp_upload_dir = wp_upload_dir();

			// Transfère le thumbnail.
			$upload_result = wp_upload_bits( basename( $file ), null, file_get_contents( $file ) );

			$filetype = wp_check_filetype( basename( $upload_result['file'] ), null );
			/**	Set the default values for the current attachement	*/
			$attachment_default_args = array(
					'guid'           => $wp_upload_dir['url'] . '/' . basename( $upload_result['file'] ),
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $upload_result['file'] ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
			);

			/**	Save new picture into database	*/
			$attach_id = wp_insert_attachment( $attachment_default_args, $upload_result['file'], $element_id );

			/**	Create the different size for the given picture and get metadatas for this picture	*/
			$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_result['file'] );
			/**	Finaly save pictures metadata	*/
			wp_update_attachment_metadata( $attach_id, $attach_data );

			return $attach_id;
		}
	}
} // End if().
