<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier de définition du modèle des utilisateurs / File for user model definition
 *
 * @author Evarisk development team <dev@eoxia.com>
 * @version 6.0
 * @package Model manager
 * @subpackage Custom post type
 */

/**
 * CLasse de définition du modèle des utilisateurs / Class for user model definition
 *
 * @author Evarisk development team <dev@eoxia.com>
 * @version 6.0
 * @package Model manager
 * @subpackage Custom post type
 */
class user_mdl_01 extends constructor_model_ctr_01 {

	/**
	 * Définition des couleurs pour les utilisateurs si ils n'ont pas de gravatar / Define color list for user that don't have gravatar
	 *
	 * @var array Une liste de couleurs prédéfinies pour les avatars / A pre-defined color for user avatar
	 */
	public $avatar_color = array(
		'e9ad4f',
		'50a1ed',
		'e05353',
		'e454a2',
		'47e58e',
		'734fe9',
	);

	/**
	 * Définition de l'url du site gravatar / Define gravatar website url
	 *
	 * @var string L'url du site gravatar permettant de récupérer le gravatar d'un utiilsateur / The gravatar main url for getting user gravatar
	 */
	private static $gravatar_url = 'http://www.gravatar.com/avatar/';

	/**
	 * Définition du modèle principal des utilisateurs / Main definition for user model
	 * @var array Les champs principaux d'un utilisateur / Main fields for a user
	 */
	protected $model = array(
		'id' => array(
			'type'		=> 'integer',
			'field'		=> 'ID',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'email' => array(
				'type'		=> 'string',
				'field'		=> 'user_email',
				'function'	=> '',
				'default'	=> 0,
				'required'	=> false,
		),
		'login' => array(
				'type'		=> 'string',
				'field'		=> 'user_login',
				'function'	=> '',
				'default'	=> 0,
				'required'	=> false,
		),
// 		'password' => array(
// 				'type'		=> 'string',
// 				'field'		=> 'user_pass',
// 				'function'	=> '',
// 				'default'	=> 0,
// 				'required'	=> false,
// 		),
		'displayname' => array(
				'type'		=> 'string',
				'field'		=> 'display_name',
				'function'	=> '',
				'default'	=> 0,
				'required'	=> false,
		),
		'date' => array(
				'type'		=> 'string',
				'field'		=> 'user_registered',
				'function'	=> '',
				'default'	=> 0,
				'required'	=> false,
		),
	);

	/**
	 * Définition du modèle pour les champs secondaires des utilisateurs / Secondary fields definition for user model
	 * @var array Les champs secondaires d'un utilisateur / Secondary field for a user
	 */
	protected $array_option = array(
		'user_info' => array(
			'avatar' => array(
				'type'		=> 'string',
				'field_type'	=> 'computed',
				'field'		=> '',
				'function' => 'user_mdl_01::build_user_avatar_url',
				'default'	=> '',
				'required'	=> false,
			),
			'avatar_color' => array(
				'type'		=> 'string',
				'field_type' => 'meta',
				'field'		=> 'avatar_color',
				'default'	=> '',
				'required'	=> false,
			),
			'initial' => array(
				'type'		=> 'string',
				'field_type' => 'meta',
				'field'		=> 'initial',
				'default'	=> '',
				'required'	=> false,
			),
			'firstname' => array(
				'type'		=> 'string',
				'field_type'	=> 'meta',
				'field'		=> 'first_name',
				'default'	=> '',
				'required'	=> false,
			),
			'lastname' => array(
				'type'		=> 'string',
				'field_type'	=> 'meta',
				'field'		=> 'last_name',
				'default'	=> 0,
				'required'	=> false,
			),
			'address_id' => array(
				'type'		=> 'array',
				'field'		=> '',
				'function'	=> '',
				'default'	=> 0,
				'required'	=> false,
			),
			'phone' => array(
				'type'		=> 'array',
				'field_type'	=> 'meta',
				'field'		=> '_phone',
				'function'	=> '',
				'default'	=> null,
				'required'	=> false,
			),
		),
		'user_right' => array(
			'type' => array(
				'type'		=> 'array',
				'field_type'		=> 'meta',
				'field'		=> 'roles',
				'function'	=> '',
				'default'	=> 0,
				'required'	=> false,
			),
			'code' => array(
				'type'		=> 'array',
				'field_type'		=> 'meta',
				'field'		=> 'allcaps',
				'function'	=> '',
				'default'	=> 0,
				'required'	=> false,
			),
		),
	);

	/**
	 * Construction de l'objet utilisateur par remplissage du modèle / Build user through fill in the model
	 *
	 * @param object $object L'object avec lequel il faut construire le modèle / The object which one to build
	 * @param string $meta_key Le nom de la "meta" contenant la définition complète de l'object sous forme json / The "meta" name containing the complete definition of object under json format
	 * @param boolean $cropped Permet de choisir si on construit le modèle complet ou uniquement les champs principaux / Allows to choose if the entire model have to be build or only main model
	 */
	public function __construct( $object, $meta_key, $cropped = false ) {
		/**	Instanciation du constructeur de modèle principal / Instanciate the main model constructor	*/
		parent::__construct( $object );

		/** If cropped don't get meta */
		if ( !$cropped ) {
			$user_meta = get_user_meta( $this->id );

			if ( !empty( $user_meta ) )
				$user_meta = array_merge( $user_meta, get_user_meta( $this->id, $meta_key ) );
			else
				$user_meta = get_user_meta( $this->id, $meta_key );

			$internal_meta = !empty( $user_meta ) && !empty( $user_meta[ $meta_key ] ) && !empty( $user_meta[ $meta_key ][ 0 ] ) ? json_decode( $user_meta[ $meta_key ][ 0 ], true ) : null;

			if ( !empty( $this->array_option ) ) {
				foreach( $this->array_option as $key => $array ) {
					$this->option[ $key ] = $this->fill_value( $object, $user_meta, $key, $array, $internal_meta );
				}
			}
		}
	}

	/**
	 * Construit les initiales d'un utilisateurs donné / Build initial for a given user
	 *
	 * @param object $user Les données de l'utilisateur courant / Current user data
	 *
	 * @return string Les initiales de l'utilisateur courant / Current user initial
	 */
	public static function build_user_initial( $user ){
		$initial = '';

		if ( !empty( $user->option['user_info']['firstname'] ) ) {
			$initial .= substr( $user->option['user_info']['firstname'], 0, 1 );
		}
		if ( !empty( $user->option['user_info']['lastname'] ) ) {
			$initial .= substr( $user->option['user_info']['lastname'], 0, 1 );
		}

		if ( empty( $initial ) ) {
			if ( !empty( $user->login ) ) {
				$initial .= substr( $user->login, 0, 1 );
			}
		}

		return $initial;
	}

	/**
	 * Retourne l'adresse url de l'image gravatar d'un utilisateur / Return the gravatar picture for a given user.
	 * Utilisation: il faut utiliser cette valeur dans l'attribut src de la balise image, c'est à ce moment qu'il faut définir les différents paramètres que l'on souhaite (Taille:?s=; Image par défaut:?d=[404|blank|...])
	 * Use: Use the value into src attribute of img html tag, parameters have to be set at this moment (Size:?s=; Default picture:?d=[404|blank|...])
	 * Documentation complète / Complete documentation : https://fr.gravatar.com/site/implement/images/
	 *
	 * @param object $user Les données de l'utilisateur courant / Current user data
	 *
	 * @return string L'adresse url du gravatar de l'utilisateur / The url address of current user gravatar
	 */
	public static function build_user_avatar_url( $user ) {
		if ( empty( $user ) || empty( $user->user_email ) )
			return self::$gravatar_url . '00000000000000000000000000000000?d=blank';

		return self::$gravatar_url . md5( $user->user_email );
	}

}
