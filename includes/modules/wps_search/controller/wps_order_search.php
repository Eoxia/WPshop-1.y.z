<?php if ( !defined( 'ABSPATH' ) ) exit;
class wpshop_order_search {

	function __construct() {
		if  ( is_admin() ) {
			//add_action('posts_where_request', array(&$this, 'wpshop_search_where_in_order'));
			add_filter( 'posts_where', array(&$this, 'wpshop_search_where_in_order'), 10, 2 );
		}
	}

	/**
	 * Add table for search query
	 * @param string $join The current
	 * @return string The new search query table list
	 */
	public function wpshop_search_join( $join ) {
		global $wpdb;

		$post_type = !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';

		if( is_search() || (is_admin() && $post_type == WPSHOP_NEWTYPE_IDENTIFIER_ORDER) ) {
			$join .= " LEFT JOIN $wpdb->postmeta ON " . $wpdb->posts . ".ID = $wpdb->postmeta.post_id ";
		}

		return $join;
	}

	public function wpshop_search_where_in_order( $where ) {
		global $wpdb;

		$s = !empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$post_type = !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';

		if ( !empty( $s ) && !empty( $post_type ) && $post_type == WPSHOP_NEWTYPE_IDENTIFIER_ORDER ) {

			$where = "	AND {$wpdb->posts}.post_type = '" . WPSHOP_NEWTYPE_IDENTIFIER_ORDER . "'";

			if( !empty( $s ) ) {
				$s_soundex = soundex( sanitize_text_field( $_GET['s'] ) );
				$s = strtoupper( sanitize_text_field( $_GET['s'] ) );
				$where .= "AND (
									(
										{$wpdb->posts}.ID IN (
											SELECT PM.post_id AS ID
											FROM {$wpdb->postmeta} AS PM
											WHERE
											(
												PM.meta_key = '_order_postmeta'
												AND UPPER( PM.meta_value ) LIKE '%{$s}%'
											)
											OR
											(
												PM.meta_key = '_order_info'
												AND UPPER( PM.meta_value ) LIKE '%{$s}%'
												OR SOUNDEX( PM.meta_value ) = '{$s_soundex}'
											)
										)
									)
									OR
									(
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
								)";
			}
		}

		//echo '<pre>'; print_r($where); echo '</pre>'; exit();

		return $where;
	}

}

if ( class_exists("wpshop_order_search") ) {
	$wpshop_order_search = new wpshop_order_search();
}
