<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier de définition du modèle des taxinomies / File for term model definition
 *
 * @author Evarisk development team <dev@eoxia.com>
 * @version 6.0
 * @package Model manager
 * @subpackage Custom post type
 */

/**
 * Classe de définition du modèle des taxinomies / Class for term model definition
 *
 * @author Evarisk development team <dev@eoxia.com>
 * @version 6.0
 * @package Model manager
 * @subpackage Custom post type
 */
class term_mdl_01 extends constructor_model_ctr_01 {

	/**
	 * Définition du modèle principal des taxinomies / Main definition for taxonomy model
	 * @var array Les champs principaux d'une taxinomie / Main fields for a taxonomy
	 */
	protected $model = array(
		'id' => array(
			'type'		=> 'integer',
			'field'		=> 'term_id',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'type' => array(
			'type'		=> 'string',
			'field'		=> 'taxonomy',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'term_taxonomy_id' => array(
			'type'		=> 'integer',
			'field'		=> 'term_taxonomy_id',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'name' => array(
			'type'		=> 'string',
			'field'		=> 'name',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'description' => array(
			'type'		=> 'string',
			'field'		=> 'description',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'slug' => array(
			'type'		=> 'string',
			'field'		=> 'slug',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'parent_id' => array(
			'type'		=> 'integer',
			'field'		=> 'parent',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
	);

	/**
	 * Définition du modèle pour les champs secondaires des taxinomies / Secondary fields definition for taxonomy model
	 * @var array Les champs secondaires d'une taxinomie / Secondary field for a taxonomy
	 */
	protected $array_option = array(
		'group' => array(
			'type'		=> 'integer',
			'field'		=> 'term_group',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'thumbnail_id' => array(
			'type'		=> 'integer',
			'field_type'	=> 'meta',
			'field'		=> '_thumbnail_id',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
	);

	/**
	 * Construction de l'objet taxinomie par remplissage du modèle / Build taxonomy through fill in the model
	 *
	 * @param object $object L'object avec lequel il faut construire le modèle / The object which one to build
	 * @param string $meta_key Le nom de la "meta" contenant la définition complète de l'object sous forme json / The "meta" name containing the complete definition of object under json format
	 * @param boolean $cropped Permet de choisir si on construit le modèle complet ou uniquement les champs principaux / Allows to choose if the entire model have to be build or only main model
	 */
	public function __construct( $object, $meta_key, $cropped ) {
		/**	Instanciation du constructeur de modèle principal / Instanciate the main model constructor	*/
		parent::__construct( $object );

		/** If cropped don't get meta */
		if( !$cropped ) {
			/** Lecture des "metas" pour la taxinomie / Read taxonomy "meta" */
			$meta = get_term_meta( $this->id );
			$internal_meta = !empty( $meta ) && !empty( $meta[ $meta_key ] ) && !empty( $meta[ $meta_key ][ 0 ] ) ? json_decode( $meta[ $meta_key ][ 0 ], true ) : null;

			if( !empty( $this->array_option ) ) {
				foreach( $this->array_option as $key => $array ) {
					$this->option[ $key ] = $this->fill_value( $object, $meta, $key, $array, $internal_meta );
				}
			}
		}
	}

}
