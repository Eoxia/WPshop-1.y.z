<?php
/**
 * Les fonctions helpers des modèles
 *
 * @package Task manager\Plugin
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'eoxia\build_user_initial' ) ) {
	/**
	 * Construit les initiales des utilisateurs
	 *
	 * @param  User_class $user Les données de l'utilisateur.
	 * @return User_class       Les données de l'utilisateur avec les intiales
	 */
	function build_user_initial( $user ) {
		$initial = '';
		if ( ! empty( $user->firstname ) ) {
			$initial .= substr( $user->firstname, 0, 1 );
		}
		if ( ! empty( $user->lastname ) ) {
			$initial .= substr( $user->lastname, 0, 1 );
		}
		if ( empty( $initial ) ) {
			if ( ! empty( $user->login ) ) {
				$initial .= substr( $user->login, 0, 1 );
			}
		}
		$user->initial = $initial;
		return $user;
	}
}
