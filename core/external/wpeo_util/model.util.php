<?php
/**
 * Gestion des modèles
 *
 * @package Evarisk\Plugin
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\Model_Util' ) ) {
	/**
	 * Gestion des modèles
	 *
	 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
	 * @version 1.1.0.0
	 */
	class Model_Util extends \eoxia\Singleton_Util {
		public static $namespace = '';
		/**
		 * Le constructeur obligatoirement pour utiliser la classe \eoxia\Singleton_Util
		 *
		 * @return void nothing
		 */
		protected function construct() {}

		public function set_namespace( $namespace ) {
			self::$namespace = $namespace;
		}

		public function get_namespace() {
			return self::$namespace . '\\';
		}

		public static function exec_callback( $object, $functions ) {
			if ( ! empty( $functions ) ) {
				foreach ( $functions as $function ) {
					$object = call_user_func( $function, $object );
				}
			}

			return $object;
		}
	}
}
