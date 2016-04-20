<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier de gestion du modèle des taxinomies / File for term model management
 *
 * @author Evarisk development team <dev@evarisk.com>
 * @version 6.0
 * @package Digirisk model manager
 * @subpackage Taxonomies
 */

/**
 * Classe de gestion du modèle des taxinomies / Class for term model management
 *
 * @author Evarisk development team <dev@evarisk.com>
 * @version 6.0
 * @package Digirisk model manager
 * @subpackage Taxonomies
 */
class term_ctr_01 {

	/**
	 * Nom du modèle a utiliser par défaut / Name of default model to use
	 * @var string
	 */
	protected $model_name = 'term_mdl_01';

	/**
	 * Nom de la meta stockant les données / Meta name for data storage
	 * @var string
	 */
	protected $meta_key = '_wpeo_term';

	/**
	 * Nom de la taxinomie par défaut / Name of default taxonomie
	 * @var string
	 */
	protected $taxonomy = 'category';

	/**
	 * Base de l'url pour la récupération au travers de l'API / Base slug for retriving through API
	 * @var string
	 */
	protected $base = 'term';

	/**
	 * Numéro de la version courante pour l'API / Current version number for API
	 * @var string
	 */
	protected $version = '0.1';

	/**
	 * Instanciation du controleur principal pour les éléments de type "term" dans wordpress / Instanciate main controller for "term" elements' type into wordpress
	 */
	public function __construct() {
		/**	Ajout des routes personnalisées pour les éléments de type "term" / Add specific routes for "term" elements' type	*/
		add_filter( 'json_endpoints', array( &$this, 'callback_register_route' ) );
	}

	/**
	 * Mise à jour d'une taxinomie / Update the taxonomy
	 *
	 * @param array|Object $data Les données de la taxinomie a sauvegarder dans la base de données / Datas o ftaxonomy to save into database
	 *
	 * @return Object L'objet sauvegardé / The saved object
	 */
	public function update( $data ) {
		$object = $data;

		/**	Dans le cas d'un tableau on le lit pour construire l'objet / If passed data is an array read and build an object from it	*/
		if( is_array( $data ) ) {
			$object = new $this->model_name( $data, $this->meta_key );
		}

		/**	Sauvegarde des données dans la base de données / Save data into database	*/
		$wp_category_danger = wp_update_term( $object->id, $this->get_taxonomy(), $object->do_wp_object() );
		if ( !is_wp_error( $wp_category_danger ) ) {
			/** Mise à jour des options / Save options */
			if( !empty( $object->option ) ) {
				$object->save_meta_data( $object, 'update_term_meta', $this->meta_key );
			}
		}

		return $object;
	}

	/**
	 * Création d'une taxinomie / Create the taxonomy
	 *
	 * @param array|Object $data Les données de la taxinomie a sauvegarder dans la base de données / Datas o ftaxonomy to save into database
	 *
	 * @return Object L'objet sauvegardé / The saved object
	 */
	public function create( $data ) {
		$object = $data;

		if( is_array( $data ) ) {
			$object = new $this->model_name( $data, $this->meta_key );
		}

		$wp_category_danger = wp_insert_term( $object->name, $this->get_taxonomy(), array(
			'description'	=> $object->description,
			'slug'	=> $object->slug,
			'parent'	=> $object->parent_id,
		) );

		if ( !is_wp_error( $wp_category_danger ) ) {
			$object->id = $wp_category_danger[ 'term_id' ];
			$object->term_taxonomy_id = $wp_category_danger[ 'term_taxonomy_id' ];

			/** Mise à jour des options / Save options */
			if( !empty( $object->option ) ) {
				$object->save_meta_data( $object, 'update_term_meta', $this->meta_key );
			}

			return $object;
		}
		else {
			/**
			 * @todo return error when creation does not work
			 */
			return $wp_category_danger;
		}
	}

	/**
	 * Suppresion d'une taxinomie / Delete the taxonomy
	 *
	 * @param integer $id L'identifiant de la taxinomie a supprimer / The taxonomy identifier to delete
	 */
	public function delete( $id ) {
		wp_delete_term( $id );
	}

	/**
	 * Retourne une taxinomie construite a partir du modèle / Return a taxonomy builded from the model
	 *
	 * @param integer $id l'identifiant de la taxinomie a retourner / The taxonomy identifier
	 * @param boolean $cropped Optionnal Permet de choisir si il faut retourner la taxinomie complète ou uniquement les chamsp principaux / Allow to define if the taxonomy must be completly returned or just main fileds must be returned
	 *
	 * @return object la taxinomie construite selon le modèle / The taxonomy builded according to the model
	 */
	public function show( $id, $cropped = false ) {
		/**	Récupération de la taxinomie depuis wordpress / Get the taxonomy from wordpress	*/
 		$wp_term = get_term_by( 'id', $id, $this->taxonomy, OBJECT );

 		/**	Construction de la taxinomie selon le modèle défini / Build the taxonomy according to the model	*/
		$term = new $this->model_name( $wp_term, $this->meta_key, $cropped );

		return $term;
	}

	public function show_model() {
			return _e( 'Try to get the model definition', 'term_ctr_mdl' );
	}

	/**
	 * Retourne une liste de taxinomies selon les paramètres donnés / Return a taxonomy list according to given parameters
	 *
	 * @param array $args_where Optionnal Les paramètres du filtre permettant de récupérer les taxinomies / Parameters allowing to retrieve taxonomies
	 * @param boolean $cropped Optionnal Permet de choisir si il faut retourner la taxinomie complète ou uniquement les chamsp principaux / Allow to define if the taxonomy must be completly returned or just main fileds must be returned
	 *
	 * @return array La liste des taxinomies correspondantes aux paramètres / Taxonomies corresping to parameters
	 */
	public function index( $args = array(), $cropped = false ) {
		$array_model = array();

		$term_final_args = array_merge( $args, array( 'hide_empty' => false, ) );
		$array_term = get_terms( $this->taxonomy, $term_final_args );

		if( !empty( $array_term ) ) {
			foreach( $array_term as $key => $term ) {
				$array_model[$key] = new $this->model_name( $term, $this->meta_key, $cropped );
			}
		}

		return $array_model;
	}

	/**
	 * Ajoute les routes par défaut pour les éléments de type POST dans wordpress / Add default routes for POST element type into wordpress
	 *
	 * @param array $array_route Les routes existantes dans l'API REST de wordpress / Existing routes into Wordpress REST API
	 *
	 * @return array La liste des routes personnalisées ajoutées aux routes existantes / The personnalized routes added to existing
	 */
	public function callback_register_route( $array_route ) {
		/**	Récupération de la définition du model pour l'élément / Get model structure for element	*/
		$array_route['/' . $this->version . '/show_model/' . $this->base ] = array(
				array( array( $this, 'show_model' ), WP_JSON_Server::READABLE )
		);

		/** Récupération de la liste complète des éléments / Get all existing elements */
		$array_route['/' . $this->version . '/get/' . $this->base ] = array(
				array( array( $this, 'index' ), WP_JSON_Server::READABLE | WP_JSON_Server::ACCEPT_JSON )
		);

		/** Récupération d'un élément donné / Get a given element */
		$array_route['/' . $this->version . '/get/' . $this->base . '/(?P<id>\d+)'] = array(
				array( array( $this, 'show' ), WP_JSON_Server::READABLE |  WP_JSON_Server::ACCEPT_JSON )
		);

		/** Mise à jour d'un élément / Update an element */
		$array_route['/' . $this->version . '/post/' . $this->base . ''] = array(
				array( array( $this, 'update' ), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON ),
		);

		/** Suppression d'un élément / Delete an element */
		$array_route['/' . $this->version . '/delete/' . $this->base . '/(?P<id>\d+)'] = array(
				array( array( $this, 'delete' ), WP_JSON_Server::DELETABLE | WP_JSON_Server::ACCEPT_JSON ),
		);

		return $array_route;
	}

	/**
	 * GETTER - Récupération du type de l'élément courant / Get the current element type
	 *
	 * @return string Le type d'élément courant / The current element type
	 */
	public function get_taxonomy() {
		return $this->taxonomy;
	}

}
