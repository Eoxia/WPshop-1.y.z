<?php
/**
 * Gestion des termes (POST, PUT, GET, DELETE)
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

if ( ! class_exists( '\eoxia\Term_Class' ) ) {
	/**
	 * Gestion des termes (POST, PUT, GET, DELETE)
	 */
	class Term_Class extends Singleton_Util {

		/**
		 * Le nom du modèle
		 *
		 * @var string
		 */
		protected $model_name = 'term_model';

		/**
		 * La clé principale pour post_meta
		 *
		 * @var string
		 */
		protected $meta_key = '_wpeo_term';

		/**
		 * Le nom de la taxonomie
		 *
		 * @var string
		 */
		protected $taxonomy = 'category';

		/**
		 * Utiles pour récupérer la clé unique
		 *
		 * @todo Rien à faire ici
		 * @var string
		 */
		protected $identifier_helper = 'term';

		/**
		 * Fonction de callback après avoir récupérer le modèle en mode GET.
		 *
		 * @var array
		 */
		protected $after_get_function = array();

		/**
		 * Fonction de callback avant d'insérer les données en mode POST.
		 *
		 * @var array
		 */
		protected $before_post_function = array();

		/**
		 * Fonction de callback après avoir inséré les données en mode POST.
		 *
		 * @var array
		 */
		protected $after_post_function = array();

		/**
		 * Fonction de callback avant de mêttre à jour les données en mode PUT.
		 *
		 * @var array
		 */
		protected $before_put_function = array();

		/**
		 * Fonction de callback après avoir mis à jour les données en mode PUT.
		 *
		 * @var array
		 */
		protected $after_put_function = array();

		/**
		 * Le constructeur
		 *
		 * @return void
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 */
		protected function construct() {}

		/**
		 * Permet de récupérer le schéma avec les données du modèle par défault.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @return Object
		 */
		public function get_schema() {
			$model_name = $this->model_name;
			$model = new $model_name( array(), array() );
			return $model->get_model();
		}

		/**
		 * Récupères les données selon le modèle définis.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param array   $args Les paramètres de get_terms @https://codex.wordpress.org/Function_Reference/get_terms.
		 * @param boolean $single Si on veut récupérer un tableau, ou qu'une seule entrée.
		 *
		 * @return Object
		 */
		public function get( $args = array(), $single = false ) {
			$list_term = array();
			$array_term = array();

			$model_name = $this->model_name;

			$term_final_args = array_merge( $args, array(
				'hide_empty' => false,
			) );

			if ( ! empty( $args['id'] ) ) {
				$array_term[] = get_term_by( 'id', $args['id'], $this->taxonomy, ARRAY_A );
			} elseif ( ! empty( $args['post_id'] ) ) {
				$array_term = wp_get_post_terms( $args['post_id'], $this->taxonomy, $term_final_args );

				if ( empty( $array_term ) ) {
					$array_term[] = array();
				}
			} elseif ( isset( $args['schema'] ) ) {
				$array_term[] = array();
			} else {
				$array_term = get_terms( $this->taxonomy, $term_final_args );
			}

			if ( ! empty( $array_term ) ) {
				foreach ( $array_term as $key => $term ) {
					$term = (array) $term;

					if ( ! empty( $args['post_id'] ) ) {
						$term['post_id'] = $args['post_id'];
					}

					if ( ! empty( $term['term_id'] ) ) {
						$list_meta = get_term_meta( $term['term_id'] );
						foreach ( $list_meta as &$meta ) {
							$meta = array_shift( $meta );
						}

						$term = array_merge( $term, $list_meta );

						if ( ! empty( $term[ $this->meta_key ] ) ) {
							$term = array_merge( $term, json_decode( $term[ $this->meta_key ], true ) );
							unset( $term[ $this->meta_key ] );
						}
					}

					$list_term[ $key ] = new $model_name( $term );

					$list_term[ $key ] = Model_Util::exec_callback( $list_term[ $key ], $this->after_get_function );
				}
			}

			if ( true === $single && 1 === count( $list_term ) ) {
				$list_term = $list_term[0];
			}

			return $list_term;
		}

		/**
		 * Appelle la méthode update.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param  Array $data Les données.
		 * @return Array $data Les données
		 */
		public function create( $data ) {
			return $this->update( $data );
		}

		/**
		 * Insère ou met à jour les données dans la base de donnée.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param  Array $data Les données a insérer ou à mêttre à jour.
		 * @return Object      L'objet construit grâce au modèle.
		 */
		public function update( $data ) {
			$model_name = $this->model_name;
			$object = new $model_name( (array) $data );

			/**	Sauvegarde des données dans la base de données / Save data into database	*/
			if ( empty( $object->id ) ) {
				$object = Model_Util::exec_callback( $object, $this->before_post_function );
				$wp_category_danger = wp_insert_term( $object->name, $this->get_taxonomy(), array(
					'description'	=> ! empty( $object->description ) ? $object->description : '',
					'slug'	=> ! empty( $object->slug ) ? $object->slug : sanitize_title( $object->name ),
					'parent'	=> ! empty( $object->parent_id ) ? (int) $object->parent_id : 0,
				) );
				$object = Model_Util::exec_callback( $object, $this->after_post_function );
			} else {
				$object = Model_Util::exec_callback( $object, $this->before_put_function );
				$wp_category_danger = wp_update_term( $object->id, $this->get_taxonomy(), $object->do_wp_object() );
				$object = Model_Util::exec_callback( $object, $this->after_put_function );
			}

			if ( ! is_wp_error( $wp_category_danger ) ) {
				$object->id = $wp_category_danger['term_id'];
				$object->term_taxonomy_id = $wp_category_danger['term_taxonomy_id'];

				save_meta_class::g()->save_meta_data( $object, 'update_term_meta', $this->meta_key );
			} else {
				if ( ! empty( $wp_category_danger->error_data['term_exists'] ) && is_int( $wp_category_danger->error_data['term_exists'] ) ) {
					$list_term_model = $this->get( array(
						'id' => $wp_category_danger->error_data['term_exists'],
					) );
					return $list_term_model[0];
				} else {
					return array();
				}
			}

			return $object;
		}

		/**
		 * Supprime un term
		 *
		 * @todo: Inutile ?
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param int $id L'ID du term (term_id).
		 */
		public function delete( $id ) {
			wp_delete_term( $id );
		}

		/**
		 * Récupères la taxonomie
		 *
		 * @since 1.0.0.0
		 * @version 1.3.6.0
		 *
		 * @return string Le nom de la taxonomie
		 */
		public function get_taxonomy() {
			return $this->taxonomy;
		}

		/**
		 * Retourne la taxonomie
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @return string Le post type
		 *
		 * @todo: Doublon
		 */
		public function get_post_type() {
			return $this->get_taxonomy();
		}

		/**
		 * Utile uniquement pour DigiRisk.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @return string L'identifiant des commentaires pour DigiRisk.
		 */
		public function get_identifier_helper() {
			return $this->identifier_helper;
		}
	}
} // End if().
