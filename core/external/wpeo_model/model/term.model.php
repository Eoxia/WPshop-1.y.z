<?php
/**
 * Définition des données des terms
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

if ( ! class_exists( '\eoxia\Term_Model' ) ) {
	/**
	 * Définition des données des terms
	 */
	class Term_Model extends Constructor_Data_Class {

		/**
		 * Définition du modèle principal des taxonomies
		 *
		 * @var array Les champs principaux d'une taxonomie
		 */
		protected $model = array(
			'id' => array(
				'type'		=> 'integer',
				'field'		=> 'term_id',
				'bydefault'	=> 0,
			),
			'type' => array(
				'type'		=> 'string',
				'field'		=> 'taxonomy',
				'bydefault'	=> 0,
			),
			'term_taxonomy_id' => array(
				'type'		=> 'integer',
				'field'		=> 'term_taxonomy_id',
				'bydefault'	=> 0,
			),
			'name' => array(
				'type'		=> 'string',
				'field'		=> 'name',
				'bydefault'	=> 0,
				'export'	=> true,
			),
			'description' => array(
				'type'		=> 'string',
				'field'		=> 'description',
				'bydefault'	=> 0,
			),
			'slug' => array(
				'export'	=> true,
				'type'		=> 'string',
				'field'		=> 'slug',
				'bydefault'	=> 0
			),
			'parent_id' => array(
				'export' 	=> true,
				'type'		=> 'integer',
				'field'		=> 'parent',
				'bydefault'	=> 0,
			),
			'post_id' => array(
				'type' 	=> 'integer',
				'field'	=>	'post_id',
				'bydefault' => 0,
			)
		);
	}
}
