<?php
/**
 * Gestion des posts
 *
 * @package Evarisk\Plugin
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\Post_Util' ) ) {
	/**
	 * Gestion des posts
	 *
	 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
	 * @version 1.1.0.0
	 */
	class Post_Util extends \eoxia\Singleton_Util {
		/**
		 * Le constructeur obligatoirement pour utiliser la classe \eoxia\Singleton_Util
		 *
		 * @return void nothing
		 */
		protected function construct() {}

		/**
		 * Est ce que le post est un parent des enfants ?
		 *
		 * @param int $parent_id (test: 10) L'id du post parent.
		 * @param int $children_id (test: 11) L'id du post enfant.
		 *
		 * @return bool true|false
		 */
		public static function is_parent( $parent_id, $children_id ) {
			$list_parent_id = get_post_ancestors( $children_id );
			if ( ! empty( $list_parent_id ) && in_array( $parent_id, $list_parent_id, true ) ) {
				return true;
			}
			return false;
		}
	}
}
