<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier de définition du modèle des éléments "custom post type" / File for "custom post types" element model definition
 *
 * @author Evarisk development team <dev@eoxia.com>
 * @version 6.0
 * @package Model manager
 * @subpackage Custom post type
 */

/**
 * Classe de définition du modèle des éléments "custom post type" / Class for "custom post types" element model definition
 *
 * @author Evarisk development team <dev@eoxia.com>
 * @version 6.0
 * @package Model manager
 * @subpackage Custom post type
 */
class post_mdl_01 extends constructor_model_ctr_01 {

	protected $model = array(
		'id' => array(
			'type'		=> 'integer',
			'field'		=> 'ID',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'parent_id' => array(
			'type'		=> 'integer',
			'field'		=> 'post_parent',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'author_id' => array(
			'type'		=> 'integer',
			'field'		=> 'post_author',
			'function'	=> '',
			'default'	=> 0,
			'required'	=> false,
		),
		'date' => array(
			'type'		=> 'string',
			'field'		=> 'post_date',
			'function'	=> '',
			'default'	=> '0000-00-00 00:00:00',
			'required'	=> false,
		),
		'date_modified' => array(
			'type'		=> 'string',
			'field'		=> 'post_modified',
			'function'	=> '',
			'default'	=> '0000-00-00 00:00:00',
			'required'	=> false,
		),
		'title' 	=> array(
			'type'		=> 'string',
			'field'		=> 'post_title',
			'function'	=> '',
			'default'	=> '',
			'required'	=> false,
		),
		'slug' 	=> array(
			'type'		=> 'string',
			'field'		=> 'post_name',
			'function'	=> '',
			'default'	=> '',
			'required'	=> false,
		),
		'content' => array(
			'type'		=> 'string',
			'field'		=> 'post_content',
			'function'	=> '',
			'default'	=> '',
			'required'	=> false,
		),
		'status' => array(
			'type'		=> 'string',
			'field'		=> 'post_status',
			'function'	=> '',
			'default' 	=> 'publish',
			'required' 	=> false,
		),
		'link' => array(
			'type'		=> 'string',
			'field'		=> 'guid',
			'function'	=> '',
			'default'	=> '',
			'required'	=> false,
		),
		'type' 	=> array(
			'type'		=> 'string',
			'field'		=> 'post_type',
			'function'	=> '',
			'default' 	=> 'post',
			'required' 	=> false,
		),
		'comment_status' 	=> array(
			'type'			=> 'string',
			'field'			=> 'comment_status',
			'function'		=> '',
			'default' 		=> 'open',
			'required' 		=> false,
		),
		'comment_count' => array(
			'type'		=> 'int',
			'field'		=> 'comment_count',
			'function'	=> '',
			'default' 	=> 0,
			'required' 	=> false,
		),
		'thumbnail_id' => array(
			'type'		=> 'int',
			'field_type'=> 'meta',
			'field'		=> '_thumbnail_id',
			'function'	=> '',
			'default' 	=> 0,
			'required' 	=> false,
		),
	);

	/**
	 * Construction de l'objet "custom post type" par remplissage du modèle / Build "custom post type" through fill in the model
	 *
	 * @param object $object L'object avec lequel il faut construire le modèle / The object which one to build
	 * @param string $meta_key Le nom de la "meta" contenant la définition complète de l'object sous forme json / The "meta" name containing the complete definition of object under json format
	 * @param boolean $cropped Permet de choisir si on construit le modèle complet ou uniquement les champs principaux / Allows to choose if the entire model have to be build or only main model
	 */
	public function __construct( $object, $meta_key, $cropped ) {
		/**	Instanciation du constructeur de modèle principal / Instanciate the main model constructor	*/
		parent::__construct( $object );

		/** If cropped don't get meta */
		if(!$cropped) {

			/** Meta */
			$post_meta = get_post_meta( $this->id );
			$internal_meta = !empty( $post_meta ) && !empty( $post_meta[ $meta_key ] ) && !empty( $post_meta[ $meta_key ][ 0 ] ) ? json_decode( $post_meta[ $meta_key ][ 0 ], true ) : null;
			if( !empty( $this->array_option ) ) {
				foreach( $this->array_option as $key => $array ) {
					$this->option[ $key ] = $this->fill_value( $object, $post_meta, $key, $array, $internal_meta );
				}
			}

		}
	}

}
