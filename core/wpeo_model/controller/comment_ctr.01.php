<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * CRUD Functions pour les comments
 * @author Jimmy Latour
 * @version 0.1
 */
class comment_ctr_01 {
	protected $model_name = 'comment_mdl_01';
	protected $meta_key = '_comment';
	protected $comment_type	= '';

	protected $base = 'comment';
	protected $version = '0.1';

	public function __construct() {
		/**	Ajout des routes personnalisées pour les éléments de type "commentaires" / Add specific routes for "comments" elements' type	*/
		add_filter( 'json_endpoints', array( &$this, 'callback_register_route' ) );
	}

	public function update( $data ) {
		$object = $data;

		if( is_array( $data ) ) {
			$object = new $this->model_name( $data, $this->meta_key, false );
		}
		wp_update_comment( $object->do_wp_object() );

		/** On insert ou on met à jour les meta */
		if( !empty( $object->option ) ) {
			$object->save_meta_data( $object, 'update_comment_meta', $this->meta_key );
		}

		return $object;
	}

	public function create( $data ) {

		$object = $data;

		if( is_array( $data ) ) {
			$object = new $this->model_name( $data, $this->meta_key );
			$object->type = $this->comment_type;
		}

		$object->id = wp_insert_comment( $object->do_wp_object() );

		/** On insert ou on met à jour les meta */
		if( !empty( $object->option ) ) {
			$object->save_meta_data( $object, 'update_comment_meta', $this->meta_key  );
		}

		return $object;
	}

	public function delete( $id ) {
		wp_delete_comment( $id );
	}

	public function show( $id, $cropped = false ) {
		$comment = get_comment( $id );

		if( !empty( $comment ) )
			$comment = new $this->model_name( $comment, $this->meta_key, $cropped );

		return $comment;
	}

	public function index( $post_id = 0, $args_where = array( 'parent' => 0, 'status' => -34070, ), $cropped = false ) {
		$array_model = array();

		$args = array(
			'post_id' 	=> $post_id,
		);

		if ( !empty( $this->comment_type ) )
			$args['type'] = $this->comment_type;

		$args = array_merge($args, $args_where);
		$array_comment = get_comments( $args );

		if( !empty( $array_comment ) ) {
			foreach( $array_comment as $comment ) {
				$array_model[] = new $this->model_name( $comment, $this->meta_key, $cropped );
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

	public function get_type() {
		return $this->comment_type;
	}

}
