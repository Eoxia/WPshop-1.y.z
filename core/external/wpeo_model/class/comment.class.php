<?php
/**
 * Gestion des commentaires (POST, PUT, GET, DELETE)
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

if ( ! class_exists( '\eoxia\Comment_Class' ) ) {
	/**
	 * Gestion des commentaires (POST, PUT, GET, DELETE)
	 */
	class Comment_Class extends Singleton_Util {
		/**
		 * Le nom du modèle à utiliser.
		 *
		 * @var string
		 */
		protected $model_name = 'comment_model';

		/**
		 * La clé principale pour enregistrer les meta données.
		 *
		 * @var string
		 */
		protected $meta_key = '_comment';

		/**
		 * Le type du commentaire
		 *
		 * @var string
		 */
		protected $comment_type	= '';

		/**
		 * Uniquement utile pour DigiRisk...
		 *
		 * @var string
		 */
		protected $identifier_helper = 'comment';

		/**
		 * Fonction de callback après avoir récupérer le modèle en mode GET.
		 *
		 * @var array
		 */
		protected $after_get_function = array( '\eoxia\construct_current_date' );

		/**
		 * Fonction de callback avant d'insérer les données en mode POST.
		 *
		 * @var array
		 */
		protected $before_post_function = array( '\eoxia\convert_date' );

		/**
		 * Fonction de callback avant de dispatcher les données en mode POST.
		 *
		 * @var array
		 */
		protected $before_model_post_function = array();

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
		protected $before_put_function = array( '\eoxia\convert_date' );

		/**
		 * Fonction de callback avant de dispatcher les données en mode PUT.
		 *
		 * @var array
		 */
		protected $before_model_put_function = array();

		/**
		 * Fonction de callback après avoir mis à jour les données en mode PUT.
		 *
		 * @var array
		 */
		protected $after_put_function = array();

		/**
		 * Le constructeur pour Singleton_Util
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @return void
		 */
		protected function construct() {}

		/**
		 * Permet de récupérer le schéma avec les données du modèle par défault.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @return Comment_Model
		 */
		public function get_schema() {
			$model_name = $this->model_name;
			$model = new $model_name( array() );
			return $model->get_model();
		}

		/**
		 * Récupères les données selon le modèle définis.
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param array   $args Les paramètres de get_comments @https://codex.wordpress.org/Function_Reference/get_comments.
		 * @param boolean $single Si on veut récupérer un tableau, ou qu'une seule entrée.
		 *
		 * @return Comment_Model
		 */
		public function get( $args = array(
				'post_id' => 0,
				'parent' => 0,
			),
			$single = false ) {

			$array_model = array();
			$array_comment = array();

			if ( ! empty( $this->comment_type ) ) {
				$args['type'] = $this->comment_type;
				$args['status'] = '-34070';
			}

			if ( empty( $args['status'] ) && ! empty( $this->status ) ) {
				$args['status'] = $this->status;
			}

			if ( ! empty( $args['id'] ) ) {
				$array_comment[] = get_comment( $args['id'], ARRAY_A );
			} elseif ( isset( $args['schema'] ) ) {
				$array_comment[] = array();
			} else {
				$array_comment = get_comments( $args );
			}

			$list_comment = array();

			if ( ! empty( $array_comment ) ) {
				foreach ( $array_comment as $key => $comment ) {
					$comment = (array) $comment;

					if ( ! empty( $comment['comment_ID'] ) ) {
						$list_meta = get_comment_meta( $comment['comment_ID'] );
						foreach ( $list_meta as &$meta ) {
							$meta = array_shift( $meta );
						}

						$comment = array_merge( $comment, $list_meta );

						if ( ! empty( $comment[ $this->meta_key ] ) ) {
							$comment = array_merge( $comment, json_decode( $comment[ $this->meta_key ], true ) );
							unset( $comment[ $this->meta_key ] );
						}
					}

					$model_name = $this->model_name;
					$list_comment[ $key ] = new $model_name( $comment );
					$list_comment[ $key ] = Model_Util::exec_callback( $list_comment[ $key ], $this->after_get_function );
				}
			} else {
				$model_name = $this->model_name;
				$list_comment[0] = new $model_name( array() );
				$list_comment[0] = Model_Util::exec_callback( $list_comment[0], $this->after_get_function );
			} // End if().

			if ( true === $single && 1 === count( $list_comment ) ) {
				$list_comment = $list_comment[0];
			}

			return $list_comment;
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
			$data = (array) $data;
			$model_name = $this->model_name;

			if ( empty( $data['id'] ) ) {
				$data = Model_Util::exec_callback( $data, $this->before_model_post_function );
				$data = new $model_name( $data, array( false ) );

				// Ajout du comment type et du status.
				// @todo: Enlevez ce truc bizarre.
				if ( empty( $data->type ) ) {
					$data->type = $this->comment_type;
					$data->status = '-34070';
				}

				$data = Model_Util::exec_callback( $data, $this->before_post_function );

				if ( ! empty( $data->error ) && $data->error ) {
					return false;
				}

				$data->id = wp_insert_comment( $data->do_wp_object() );

				$data = Model_Util::exec_callback( $data, $this->after_post_function );
			} else {
				$data = Model_Util::exec_callback( $data, $this->before_model_put_function );
				$current_data = $this->get( array(
					'id' => $data['id'],
				), true );

				$obj_merged = (object) array_merge( (array) $current_data, (array) $data );
				$data = new $model_name( (array) $obj_merged );
				$data = Model_Util::exec_callback( $data, $this->before_put_function );

				if ( ! empty( $data->error ) && $data->error ) {
					return false;
				}

				wp_update_comment( $data->do_wp_object() );

				$data = Model_Util::exec_callback( $data, $this->after_put_function );
			} // End if().

			Save_Meta_Class::g()->save_meta_data( $data, 'update_comment_meta', $this->meta_key );

			return $data;
		}

		/**
		 * Renvoie le type du commentaire
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @return string Le type du commentaire.
		 */
		public function get_type() {
			return $this->comment_type;
		}

		/**
		 * Pourquoi cette function ?
		 *
		 * @todo: Pourquoi cette function ?
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @return string Le type du commentaire.
		 */
		public function get_post_type() {
			return $this->get_type();
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
