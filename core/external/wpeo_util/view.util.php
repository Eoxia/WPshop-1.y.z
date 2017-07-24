<?php
/**
 * Gestion des vues (template)
 *
 * @package Evarisk\Plugin
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\View_Util' ) ) {
	/**
	 * Gestion des vues
	 *
	 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
	 * @version 1.1.0.0
	 */
	class View_Util extends Singleton_Util {

		/**
		 * Le constructeur obligatoirement pour utiliser la classe \eoxia\Singleton_Util
		 *
		 * @return void nothing
		 */
		protected function construct() {}

		/**
		 * Appelle la vue avec les paramètres et calcule automatiquement les MicroSeconds
		 *
		 * @param  string $module_name           Le nom du module.
		 * @param  string $view_path_without_ext Le chemin vers le fichier à partir du dossier "view" du module.
		 * @param  array  $args                  Les données à transmettre à la vue.
		 * @return void                        	 nothing
		 */
		public static function exec( $namespace, $module_name, $view_path_without_ext, $args = array(), $filter = true ) {
			$path_to_view = Config_Util::$init[ $namespace ]->$module_name->path . '/view/' . $view_path_without_ext . '.view.php';

			if ( $filter ) {
				$args = apply_filters( $module_name . '_' . $view_path_without_ext, $args, $module_name, $view_path_without_ext );
			}
			extract( $args );
			require( $path_to_view );
			// log_class::g()->exec( __NAMESPACE__ . '_\eoxia\View_Util_exec', '\eoxia\View_Util_exec', 'Chargement de la vue : ' . $path_to_view, $args );
		}
	}
} // End if().
