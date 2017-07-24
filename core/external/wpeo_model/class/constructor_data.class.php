<?php
/**
 * Gestion de la construction des données selon les modèles.
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

if ( ! class_exists( '\eoxia\Constructor_Data_Class' ) ) {
	/**
	 * Gestion de la construction des données selon les modèles.
	 */
	class Constructor_Data_Class extends Helper_Class {

		/**
		 * Appelle la méthode pour dispatcher les données.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param Array $data Les données en brut.
		 */
		protected function __construct( $data ) {
			$this->dispatch_wordpress_data( $data, $data );
			Log_Class::g()->exec( 'digirisk_construct_data', '', __( 'Unable to transfer risk to wordpress system.', 'wp-digi-dtrans-i18n' ), array(
				'object_id' => '',
				'object' => $this,
			), 0 );
		}

		/**
		 * Dispatches les données selon le modèle.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param array  $all_data       Toutes les données.
		 * @param array  $data           Les données actuelles.
		 * @param object $current_object L'objet en cours de construction.
		 * @param array  $model          La définition des données.
		 * @return object
		 */
		private function dispatch_wordpress_data( $all_data, $data, $current_object = null, $model = array() ) {
			if ( empty( $model ) ) {
				$model = $this->model;
			}

			if ( null === $current_object ) {
				$current_object = $this;
			}

			foreach ( $model as $field_name => $field_def ) {
				$current_object->$field_name = $this->set_default_data( $field_name, $field_def );

				// Est-ce qu'il existe des enfants ?
				if ( isset( $field_def['field'] ) && isset( $data[ $field_def['field'] ] ) && ! isset( $field_def['child'] ) ) {
					$current_object->$field_name = $data[ $field_def['field'] ];
				} elseif ( isset( $field_def['child'] ) ) {
					$current_data = ! empty( $all_data[ $field_name ] ) ? $all_data[ $field_name ] : array();

					if ( empty( $current_object->$field_name ) ) {
						$current_object->$field_name = new \stdClass();
					}

					$current_object->$field_name = $this->dispatch_wordpress_data( $all_data, $current_data, $current_object->$field_name, $field_def['child'] );
				}

				// Est-ce que le field_name existe en donnée (premier niveau) ?
				if ( isset( $data[ $field_name ] ) && isset( $field_def ) && ! isset( $field_def['child'] ) ) {
					$current_object->$field_name = $data[ $field_name ];
				}

				if ( isset( $field_def['required'] ) && $field_def['required'] && ! isset( $current_object->$field_name ) ) {
					$this->error = true;
				}

				if ( ! empty( $field_def['type'] ) ) {
					settype( $current_object->$field_name, $field_def['type'] );
					if ( 'string' === $field_def['type'] ) {
						$current_object->$field_name = stripslashes( $current_object->$field_name );
					}

					if ( ! empty( $field_def['array_type'] ) ) {
						if ( ! empty( $current_object->$field_name ) ) {
							foreach ( $current_object->$field_name as &$element ) {
								settype( $element, $field_def['array_type'] );
							}
						}
					}
				}
			} // End foreach().

			return $current_object;
		}

		/**
		 * Si la définition bydefault existe, récupères la valeur.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param string $field_name Le nom du champ.
		 * @param array  $field_def  La définition du champ.
		 *
		 * @return mixed						 La donnée par défaut.
		 */
		private function set_default_data( $field_name, $field_def ) {
			if ( isset( $field_def['bydefault'] ) ) {
				return $field_def['bydefault'];
			}
		}

		/**
		 * Convertis le modèle en un tableau compatible WordPress.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @return array Tableau compatible avec les fonctions WordPress.
		 */
		public function do_wp_object() {
			$object = array();

			foreach ( $this->model as $field_name => $field_def ) {
				if ( ! empty( $field_def['field'] ) && isset( $this->$field_name ) ) {
					$object[ $field_def['field'] ] = $this->$field_name;
				}
			}

			return $object;
		}
	}
} // End if().
