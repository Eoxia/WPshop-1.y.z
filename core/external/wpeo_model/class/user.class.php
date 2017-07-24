<?php
/**
 * Gestion des utilisateurs (POST, PUT, GET, DELETE)
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

if ( ! class_exists( '\eoxia\User_Class' ) ) {
	/**
	 * Gestion des utilisateurs (POST, PUT, GET, DELETE)
	 */
	class User_Class extends Singleton_Util {
		/**
		 * Le nom du modèle
		 *
		 * @var string
		 */
		protected $model_name = 'user_model';

		/**
		 * La clé principale pour post_meta
		 *
		 * @var string
		 */
		protected $meta_key = '_wpeo_user';

		/**
		 * Utiles pour récupérer la clé unique
		 *
		 * @todo Rien à faire ici
		 * @var string
		 */
		protected $identifier_helper = 'user';

		/**
		 * Utiles pour DigiRisk
		 *
		 * @todo Rien à faire ici
		 * @var string
		 */
		public $element_prefix = 'U';

		/**
		 * Fonction de callback après avoir récupérer les données dans la base de donnée en mode GET.
		 *
		 * @var array
		 */
		protected $after_get_function = array( 'build_user_initial' );

		/**
		 * Fonction de callback avant d'insérer les données en mode POST.
		 *
		 * @var array
		 */
		protected $before_post_function = array();

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
		protected $before_put_function = array();

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
		 * @param array   $args Les paramètres de get_users @https://codex.wordpress.org/Function_Reference/get_users.
		 * @param boolean $single Si on veut récupérer un tableau, ou qu'une seule entrée.
		 *
		 * @return Comment_Model
		 */
		public function get( $args = array(), $single = false ) {
			$list_user = array();
			$list_model_user = array();

			$model_name = $this->model_name;

			if ( ! empty( $args['id'] ) ) {
				$list_user[] = get_user_by( 'id', $args['id'] );
			} elseif ( isset( $args['schema'] ) ) {
				$list_user[] = array();
			} else {
				$list_user = get_users( $args );
			}

			if ( ! empty( $list_user ) ) {
				foreach ( $list_user as $element ) {
					$element = (array) $element;

					if ( ! empty( $element['ID'] ) ) {
						$list_meta = get_user_meta( $element['ID'] );
						foreach ( $list_meta as &$meta ) {
							$meta = array_shift( $meta );
						}

						$element = array_merge( $element, $list_meta );

						if ( ! empty( $element['data'] ) ) {
							$element = array_merge( $element, (array) $element['data'] );
							unset( $element['data'] );
						}

						if ( ! empty( $element[ $this->meta_key ] ) ) {
							$element = array_merge( $element, json_decode( $element[ $this->meta_key ], true ) );
							unset( $element[ $this->meta_key ] );
						}
					}

					$data = new $model_name( $element );
					$data = Model_Util::exec_callback( $data, $this->after_get_function );
					$list_model_user[] = $data;
				}
			}

			if ( true === $single && 1 === count( $list_model_user ) ) {
				$list_model_user = $list_model_user[0];
			}

			return $list_model_user;
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
				$data = new $model_name( (array) $data );
				$data = Model_Util::exec_callback( $data, $this->before_post_function );

				if ( ! empty( $data->error ) && $data->error ) {
					return false;
				}

				$data->id = wp_insert_user( $data->do_wp_object() );

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

				wp_update_user( $data->do_wp_object() );

				$data = Model_Util::exec_callback( $data, $this->after_put_function );
			}

			Save_Meta_Class::g()->save_meta_data( $data, 'update_user_meta', $this->meta_key );

			return $data;
		}

		/**
		 * Supprimes un utilisateur
		 *
		 * @todo: Utile ?
		 *
		 * @since 1.0.0.0
		 * @version 1.3.0.0
		 *
		 * @param  integer $id L'ID de l'utilisateur.
		 */
		public function delete( $id ) {
			wp_delete_user( $id );
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
}
