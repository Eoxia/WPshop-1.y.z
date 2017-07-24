<?php
/**
 * Classe helper pour les modèles.
 *
 * @author Jimmy Latour <dev@eoxia.com>
 * @since 1.0.0.0
 * @version 1.3.0.0
 * @copyright 2015-2017
 * @package wpeo_model
 * @subpackage class
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( '\eoxia\Helper_Class' ) ) {

	/**
	 * Classe helper pour les modèles.
	 */
	class Helper_Class {

		/**
		 * Récupères le modèle.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @return Object le modèle.
		 */
		public function get_model() {
			return $this->model;
		}

		/**
		 * Permet de faire echo sur un objet et supprimes la définition du modèle avant l'affichage.
		 *
		 * @return string void
		 */
		public function __toString() {
			$this->delete_model_for_print( $this );
			echo '<pre>'; print_r( $this ); echo '</pre>';
			return '';
		}

		/**
		 * Supprime le modèle.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param  object $current L'objet complet.
		 */
		private function delete_model_for_print( $current ) {
			if ( ! empty( $this->model ) ) {
				unset( $this->model );
			}

			foreach ( $current as &$content ) {
				if ( is_array( $content ) ) {
					foreach ( $content as &$model ) {
						if ( ! empty( $model->model ) ) {
							unset( $model->model );
							$this->delete_model_for_print( $model );
						}
					}
				}
			}
		}
	}
} // End if().
