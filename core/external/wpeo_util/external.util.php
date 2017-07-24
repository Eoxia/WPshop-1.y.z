<?php
/**
 * Gestion des externals
 *
 * @package Evarisk\Plugin
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\External_Util' ) ) {
	/**
	 * Gestion des externals
	 *
	 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
	 * @version 1.1.0.0
	 */
	class External_Util extends \eoxia\Singleton_Util {
		/**
		 * Le constructeur obligatoirement pour utiliser la classe \eoxia\Singleton_Util
		 *
		 * @return void nothing
		 */
		protected function construct() {}

		/**
		 * Parcours le fichier digirisk.config.json pour récupérer les chemins vers tous les modules.
		 * Initialise ensuite un par un, tous ses modules.
		 *
		 * @return mixed WP_Error si les configs de DigiRisk ne sont pas initialisés, ou si aucun module n'est présent.
		 */
		public function exec( $path, $plugin_slug ) {
			if ( empty( \eoxia\Config_Util::$init[ $plugin_slug ]->externals ) ) {
				return new \WP_Error( 'broke', __( 'Aucun module a charger', $plugin_slug ) );
			}

			if ( ! empty( \eoxia\Config_Util::$init[ $plugin_slug ]->externals ) ) {
				foreach ( \eoxia\Config_Util::$init[ $plugin_slug ]->externals as $external_json_path ) {
					self::inc_config( $plugin_slug, $path . $external_json_path );
					self::inc( $plugin_slug, $path . $external_json_path );
				}
			}
		}

		/**
		 * Appelle la méthode init_config de \eoxia\Config_Util pour initialiser les configs du module
		 *
		 * @param  string $module_json_path Le chemin vers le dossier du module.
		 * @return void                   	nothing
		 */
		public function inc_config( $plugin_slug, $module_json_path ) {
			\eoxia\Config_Util::g()->init_config( $module_json_path, $plugin_slug );
		}

		/**
		 * Inclus les dépendences du module (qui sont défini dans le config.json du module en question)
		 *
		 * @param  string $module_json_path Le chemin vers le module.
		 * @return void                   	nothing
		 */
		public function inc( $plugin_slug, $module_json_path ) {
			$module_name = basename( $module_json_path, '.config.json' );
			$module_path = dirname( $module_json_path );

			if ( ! empty( \eoxia\Config_Util::$init['external'] ) ) {
				foreach ( \eoxia\Config_Util::$init['external'] as $external_name => $config ) {
					if ( ! empty( $config->dependencies ) ) {
						foreach ( $config->dependencies as $folder_name => $folder_conf ) {
							$path_to_module_and_dependence_folder = $config->path . '/' . $folder_name . '/';

							if ( ! empty( $folder_conf->priority ) ) {
								$this->inc_priority_file( $path_to_module_and_dependence_folder, $folder_name, $folder_conf->priority );
							}

							\eoxia\Include_util::g()->in_folder( $path_to_module_and_dependence_folder );
						}
					}
				}
			}
		}

		/**
		 * Inclus les fichiers prioritaires qui se trouvent dans la clé "priority" dans le .config.json du module
		 *
		 * @param  string $path_to_module_and_dependence_folder Le chemin vers le module.
		 * @param  string $dependence_folder                    le chemin vers le dossier à inclure.
		 * @param  array  $list_priority_file                    La liste des chemins des fichiers à inclure en priorité.
		 * @return void                                       	nothing
		 */
		public function inc_priority_file( $path_to_module_and_dependence_folder, $dependence_folder, $list_priority_file ) {
			if ( ! empty( $list_priority_file ) ) {
				foreach ( $list_priority_file as $file_name ) {
					$path_file = realpath( $path_to_module_and_dependence_folder . $file_name . '.' . $dependence_folder . '.php' );

					require_once( $path_file );
				}
			}
		}
	}
} // End if().
