<?php
/**
 * Définition des données des utilisateurs
 *
 * @author Jimmy Latour <dev@eoxia.com>
 * @since 1.0.0.0
 * @version 1.3.0.0
 * @copyright 2015-2017
 * @package wpeo_model
 * @subpackage model
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( '\eoxia\User_Model' ) ) {
	/**
	 * Définition des données des utilisateurs
	 */
	class User_Model extends Constructor_Data_Class {

		/**
		 * Définition des différentes couleurs pour l'avatar
		 *
		 * @var array
		 */
		public $avatar_color = array(
			'50a1ed'
		);

		/**
		 * L'url pour l'avatar
		 *
		 * @var string
		 */
		private static $gravatar_url = 'http://www.gravatar.com/avatar/';

		/**
		 * Définition du modèle principal des utilisateurs
		 *
		 * @var array Les champs principaux d'un utilisateur
		 */
		protected $model = array(
			'id' => array(
				'type'		=> 'integer',
				'field'		=> 'ID',
			),
			'email' => array(
				'type'			=> 'string',
				'field'			=> 'user_email',
				'required'	=> true,
			),
			'login' => array(
				'type'			=> 'string',
				'field'			=> 'user_login',
				'required'	=> true,
			),
			'password' => array(
				'type'			=> 'string',
				'field'			=> 'user_pass',
				'required'	=> true,
			),
			'displayname' => array(
				'type'		=> 'string',
				'field'		=> 'display_name',
			),
			'date' => array(
				'type'		=> 'string',
				'field'		=> 'user_registered',
			),
			'avatar' => array(
				'type'			=> 'string',
				'meta_type' => 'single',
				'field'			=> 'avatar',
				'bydefault'	=> '',
			),
			'avatar_color' => array(
				'type'			=> 'string',
				'meta_type'	=> 'single',
				'field'			=> 'avatar_color',
				'bydefault'	=> '',
			),
			'initial'		=> array(
				'type'			=> 'string',
				'meta_type'	=> 'single',
				'field'			=> 'initial',
				'bydefault'	=> '',
			),
			'firstname'		=> array(
				'type'			=> 'string',
				'meta_type'	=> 'single',
				'field'			=> 'first_name',
				'bydefault'	=> '',
				'required'	=> true,
			),
			'lastname'		=> array(
				'type'			=> 'string',
				'meta_type'	=> 'single',
				'field'			=> 'last_name',
				'bydefault'	=> '',
				'required'	=> true,
			),
		);

		/**
		 * Retourne l'adresse url de l'image gravatar d'un utilisateur / Return the gravatar picture for a given user.
		 * Utilisation: il faut utiliser cette valeur dans l'attribut src de la balise image, c'est à ce moment qu'il faut définir les différents paramètres que l'on souhaite (Taille:?s=; Image par défaut:?d=[404|blank|...])
		 * Use: Use the value into src attribute of img html tag, parameters have to be set at this moment (Size:?s=; Default picture:?d=[404|blank|...])
		 * Documentation complète / Complete documentation : https://fr.gravatar.com/site/implement/images/
		 *
		 * @param object $user Les données de l'utilisateur courant / Current user data.
		 *
		 * @return string L'adresse url du gravatar de l'utilisateur / The url address of current user gravatar
		 */
		public static function build_user_avatar_url( $user ) {
			if ( empty( $user ) || empty( $user->user_email ) ) {
				return self::$gravatar_url . '00000000000000000000000000000000?d=blank';
			}

			return self::$gravatar_url . md5( $user->user_email );
		}

	}

} // End if().
