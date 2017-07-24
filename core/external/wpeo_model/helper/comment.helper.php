<?php
/**
 * Fonctions "helper" des commentaires
 *
 * @author Jimmy Latour <dev@eoxia.com>
 * @since 1.0.0.0
 * @version 1.3.0.0
 * @copyright 2015-2017
 * @package wpeo_model
 * @subpackage helper
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'eoxia\construct_current_date' ) ) {
	/**
	 * Met la date au bon format. Si la date n'existe pas, prend la date du jour.
	 *
	 * @param  Object $data L'objet courant.
	 *
	 * @return Object       L'objet avec la date.
	 *
	 * @since 1.0.0.0
	 * @version 1.3.0.0
	 */
	function construct_current_date( $data ) {
		$data->date = ! empty( $data->date ) ? date( 'd/m/Y', strtotime( $data->date ) ) : current_time( 'd/m/Y' );

		return $data;
	}
}

if ( ! function_exists( 'eoxia\construct_current_date_time' ) ) {
	/**
	 * Met la date au bon format. Si la date n'existe pas, prend la date du jour.
	 *
	 * @param  Object $data L'objet courant.
	 *
	 * @return Object       L'objet avec la date.
	 *
	 * @since 1.0.0.0
	 * @version 1.3.0.0
	 */
	function construct_current_date_time( $data ) {
		$data->date = ! empty( $data->date ) ? date( 'd/m/Y H:i', strtotime( $data->date ) ) : current_time( 'd/m/Y H:i' );
		$data->date_modified = ! empty( $data->date_modified ) ? date( 'd/m/Y H:i', strtotime( $data->date_modified ) ) : current_time( 'd/m/Y H:i' );

		return $data;
	}
}

if ( ! function_exists( 'eoxia\convert_date' ) ) {
	/**
	 * Convertie la date au format français dd/mm/yy en format SQL
	 *
	 * @param  object $data Les donnnées du modèle.
	 * @return object       Les donnnées du modèle avec la date au format SQL
	 *
	 * @since 1.0.0.0
	 * @version 1.3.0.0
	 */
	function convert_date( $data ) {
		if ( ! empty( $data ) && ! empty( $data->date ) ) {
			$data->date = date( 'Y-m-d', strtotime( str_replace( '/', '-', $data->date ) ) );
		}

		return $data;
	}
}

if ( ! function_exists( 'eoxia\convert_date_time' ) ) {
	/**
	 * Convertie la date au format français dd/mm/yy en format SQL
	 *
	 * @param  object $data Les donnnées du modèle.
	 * @return object       Les donnnées du modèle avec la date au format SQL
	 *
	 * @since 1.0.0.0
	 * @version 1.3.0.0
	 */
	function convert_date_time( $data ) {
		if ( ! empty( $data ) && ! empty( $data->date ) ) {
			$data->date = date( 'Y-m-d H:i', strtotime( str_replace( '/', '-', $data->date ) ) );
		}
		if ( ! empty( $data ) && ! empty( $data->date_modified ) ) {
			$data->date_modified = date( 'Y-m-d H:i', strtotime( str_replace( '/', '-', $data->date_modified ) ) );
		}

		return $data;
	}
}
