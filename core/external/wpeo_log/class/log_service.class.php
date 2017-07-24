<?php
/**
 * File for service log definition
 *
 * @package wpeo_logs
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\log_service_class' ) ) {
	/**
	 * Class for service log definition
	 */
	class log_service_class extends \eoxia\Singleton_Util {

		/**
		 * Instanciate the log service
		 */
		protected function construct() {}

		/**
		 * Crée un service par défaut et l'ajoutes au tableau JSON _wpeo_log_settings.
		 * Renvoie un message de type updated en transient pour afficher qu'un
		 * nouveau service à été crée.
		 *
		 * @param string $name Le nom du service.
		 */
		public function add( $name ) {
			$data_service = array(
					'active' 		=> true,
					'name'			=> ! empty( $name ) ? $name : 'new_log',
					'size' 			=> '1000000',
					'format' 		=> 'ko',
					'rotate'		=> false,
					'number' 		=> 0,
					'created_date'	=> current_time( 'mysql' ),
			);
			$array_current_settings = get_option( '_wpeo_log_settings' );
			if ( ! empty( $array_current_settings ) && ! is_array( $array_current_settings ) ) {
				$array_current_settings = json_decode( $array_current_settings, true );
			} else {
				$array_current_settings = array();
			}
			$array_current_settings[] = $data_service;
			$success = update_option( '_wpeo_log_settings', wp_json_encode( $array_current_settings ) );
			if ( $success ) {
				set_transient( 'log_message', wp_json_encode( array( 'type' => 'updated', 'message' => __( 'A new service has been created!', 'digirisk' ) ) ) );
			}
			if ( ! empty( $name ) ) {
				return $data_service;
			} else {
				wp_safe_redirect( wp_get_referer() );
				die();
			}
		}

		/**
		 * Get a service definition by id
		 *
		 * @param  integer $id Service identifier to get complete definition for.
		 * @return array     The service definition
		 */
		public function get_service_by_id( $id ) {
			$array_service = get_option( '_wpeo_log_settings', array() );
			$getted_service = null;
			if ( ! empty( $array_service ) ) {
				$array_service = json_decode( $array_service, true );
				foreach ( $array_service as $key => $service ) {
					if ( $key == $id ) {
						$getted_service = $service;
						break;
					}
				}
			}
			return $getted_service;
		}

		/**
		 * Récupères le service selon son nom.
		 *
		 * @param string $name Le nom du service.
		 *
		 * @return array | null
		 */
		public static function get_service_by_name( $name ) {
			$array_service = get_option( '_wpeo_log_settings', array() );
			$getted_service = null;
			if ( ! empty( $array_service ) ) {
				$array_service = ! is_array( $array_service ) ? json_decode( $array_service, true ) : $array_service;
				foreach ( $array_service as $service ) {
					if ( ! empty( $service ) && ! empty( $service['name'] ) && ( $service['name'] === $name ) ) {
						$getted_service = $service;
						break;
					}
				}
			}
			return $getted_service;
		}

		/**
		 * Create a new service in database in case it does not exists
		 *
		 * @param  string $service_name The service name to check if it exists or to create if it is not the case.
		 *
		 * @return array               The service definition
		 */
		public function create_service( $service_name ) {
			$service = self::get_service_by_name( $service_name );

			if ( null === $service ) {
				/** Créer le service s'il n'existe pas */
				$service = self::add( $service_name );
			}

			return $service;
		}

	}

}
