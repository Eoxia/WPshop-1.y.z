<?php
/**
 * Fichier de gestion de la recherche client.
 *
 * @package WPShop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe de filtrage de la recherche pour les clients.
 */
class wpshop_customer_search {

	/**
	 * [__construct description]
	 */
	function __construct() {
		if ( is_admin() ) {
			add_filter( 'posts_where', array( $this, 'wpshop_search_where_in_customer' ) );
		}
	}

	/**
	 * Filtre la requete permettant la recherche des clients dans WPShop
	 *
	 * @param  string $where La requete actuelle pour la recherche.
	 *
	 * @return string        La requete modifiée pour rechercher les clients.
	 */
	public function wpshop_search_where_in_customer( $where ) {
		global $wpdb;

		$post_type = ! empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		$s = ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$entity_filter = ! empty( $_GET['filter'] ) ? sanitize_text_field( $_GET['filter'] ) : '';

		if ( is_admin() && ( ! empty( $post_type ) && ( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS === $post_type ) ) && ( ! empty( $s ) || ! empty( $entity_filter ) ) ) {

			$where = "	AND {$wpdb->posts}.post_type = '" . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . "'";

			if ( ! empty( $entity_filter ) ) {
				switch ( $entity_filter ) {
					case 'orders':
						$operator = 'IN';
						break;
					case 'no_orders':
						$operator = 'NOT IN';
						break;
				}
				$where .= "	AND (	{$wpdb->posts}.post_author {$operator} (
										SELECT {$wpdb->posts}.post_author
										FROM {$wpdb->posts}
										WHERE {$wpdb->posts}.post_type = '" . WPSHOP_NEWTYPE_IDENTIFIER_ORDER . "'
										AND {$wpdb->posts}.post_status != 'auto-draft'
									)
								)";
			}

			if ( ! empty( $s ) ) {
				$s_soundex = soundex( $s );
				$s = strtoupper( $s );
				$where .= "	AND ( 	{$wpdb->posts}.ID = '{$s}'
									OR UPPER( {$wpdb->posts}.post_title ) LIKE '%{$s}%'
									OR SOUNDEX( {$wpdb->posts}.post_title ) = '{$s_soundex}'
									OR (
										{$wpdb->posts}.post_author IN (
											SELECT U.ID
											FROM {$wpdb->users} AS U
											INNER JOIN {$wpdb->usermeta} AS UM
											ON ( UM.user_id = U.ID )
											WHERE
											(
												(
													UPPER( U.user_email ) LIKE '%{$s}%'
													OR SOUNDEX( U.user_email ) = '{$s_soundex}'
												)
												OR
												(
													UM.meta_key = 'first_name'
													AND UPPER( UM.meta_value ) LIKE '%{$s}%'
													OR SOUNDEX( UM.meta_value ) = '{$s_soundex}'
												)
												OR
												(
													UM.meta_key = 'last_name'
													AND UPPER( UM.meta_value ) LIKE '%{$s}%'
													OR SOUNDEX( UM.meta_value ) = '{$s_soundex}'
												)
											)
										)
									)
									OR (
										{$wpdb->posts}.post_author IN (
											SELECT P.post_author
											FROM {$wpdb->posts} AS P
											INNER JOIN {$wpdb->postmeta} AS PM
											ON ( PM.post_id = P.ID )
											WHERE
											(
												P.post_type = '" . WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS . "'
												AND PM.meta_key = '_wpshop_address_metadata'
												AND UPPER( PM.meta_value ) LIKE '%{$s}%'
												OR SOUNDEX( PM.meta_value ) = '{$s_soundex}'
											)
										)
									)
								)";
			}
		}

		// Exclude post with title containing "doublon" word.
		if ( is_admin() && ( ! empty( $post_type ) && ( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS === $post_type ) ) && ( false === strpos( $where, "post_status = 'trash'" ) ) ) {
			$where .= " AND {$wpdb->posts}.post_title NOT LIKE '%doublon%' AND UPPER( {$wpdb->posts}.post_title ) NOT LIKE '%DOUBLON%'";
		}
		return $where;
	}

}

if ( class_exists( 'wpshop_customer_search' ) ) {
	$wpshop_customer_search = new wpshop_customer_search();
}
