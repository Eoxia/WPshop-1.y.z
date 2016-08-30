<?php if ( !defined( 'ABSPATH' ) ) exit;
class wpshop_customer_search {

	function __construct() {
		if  ( is_admin() ) {
			add_filter( 'posts_where', array(&$this, 'wpshop_search_where_in_customer') );
		}
	}

	public function wpshop_search_where_in_customer( $where ) {
		$post_type = !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		$s = !empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$entity_filter = !empty( $_GET['filter'] ) ? sanitize_text_field( $_GET['filter'] ) : '';

		if( is_admin() && ( !empty($post_type) && ( $post_type == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ) ) && ( !empty( $s ) || !empty( $entity_filter ) ) ) {
			global $wpdb;

			$where = "	AND {$wpdb->posts}.post_type = '" . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . "'";

			if( !empty( $entity_filter ) ) {
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

			if( !empty( $s ) ) {
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

		return $where;
	}

}

if ( class_exists("wpshop_customer_search") ) {
	$wpshop_customer_search = new wpshop_customer_search();
}
