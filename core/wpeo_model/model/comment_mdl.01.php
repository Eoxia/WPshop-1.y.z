<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier de définition du modèle des commentaires / File for comment model definition
 *
 * @author Evarisk development team <dev@eoxia.com>
 * @version 6.0
 * @package Model manager
 * @subpackage Custom post type
 */

/**
 * Classe de définition du modèle des commentaires / Class for comment model definition
 *
 * @author Evarisk development team <dev@eoxia.com>
 * @version 6.0
 * @package Model manager
 * @subpackage Custom post type
 */
class comment_mdl_01 extends constructor_model_ctr_01 {

	/**
	 * Définition du modèle principal des commentaires / Main definition for comment model
	 * @var array Les champs principaux des commentaires / Main fields for a comment
	 */
	protected $model = array(
		'id' => array(
			'type'		=> 'integer',
			'field'		=> 'comment_ID',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'parent_id' => array(
			'type'		=> 'integer',
			'field'		=> 'comment_parent',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'post_id' => array(
			'type'		=> 'integer',
			'field'		=> 'comment_post_ID',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'date' => array(
			'type'		=> 'string',
			'field'		=> 'comment_date',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'author_id' => array(
			'type'		=> 'integer',
			'field'		=> 'user_id',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'author_nicename' => array(
			'type'		=> 'string',
			'field'		=> 'comment_author',
			'function'	=> '',
			'default'	=> '',
			'required'	=> false,
		),
		'author_email' => array(
			'type'		=> 'string',
			'field'		=> 'comment_author_email',
			'function'	=> '',
			'default'	=> '',
			'required'	=> false,
		),
		'author_ip' => array(
			'type'		=> 'string',
			'field'		=> 'comment_author_IP',
			'function'	=> '',
			'default'	=> '',
			'required'	=> false,
		),
		'content' => array(
			'type'		=> 'string',
			'field'		=> 'comment_content',
			'function'	=> '',
			'default'	=> '',
			'required'	=> false,
		),
		'status' => array(
			'type'		=> 'string',
			'field'		=> 'comment_approved',
			'function'	=> '',
			'default'	=> '-34070',
			'required'	=> false,
		),
		'type' => array(
			'type'		=> 'string',
			'field'		=> 'comment_type',
			'function'	=> '',
			'default'	=> '',
			'required'	=> false,
		),
	);

	/**
	 * Construction de l'objet commentaire par remplissage du modèle / Build comment through fill in the model
	 *
	 * @param object $object L'object avec lequel il faut construire le modèle / The object which one to build
	 * @param string $meta_key Le nom de la "meta" contenant la définition complète de l'object sous forme json / The "meta" name containing the complete definition of object under json format
	 * @param boolean $cropped Permet de choisir si on construit le modèle complet ou uniquement les champs principaux / Allows to choose if the entire model have to be build or only main model
	 */
	public function __construct( $object, $meta_key, $cropped ) {
		parent::__construct( $object );

		/** If cropped, don't get meta */
		if(!$cropped) {
			/** Meta */
			$comment_meta = get_comment_meta( $this->id );
			$internal_meta = !empty( $comment_meta ) && !empty( $comment_meta[ $meta_key ] ) && !empty( $comment_meta[ $meta_key ][ 0 ] ) ? json_decode( $comment_meta[ $meta_key ][ 0 ], true ) : null;

			if( !empty( $this->array_option ) ) {
				foreach( $this->array_option as $key => $array ) {
					$this->option[ $key ] = $this->fill_value( $object, $comment_meta, $key, $array, $internal_meta );
				}
			}
		}
	}

}
