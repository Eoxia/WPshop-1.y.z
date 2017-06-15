<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_customer_mdl {

	function __construct() {

	}

	/**
	 * Return users list
	 * @return array
	 */
	function getUserList( $limit = null ) {
		$args = array(
			'orderby'      => 'user_email',
			'order'        => 'ASC',
			'number'       => !empty( $limit ) ? $limit : "",
			'count_total'  => false,
			'fields'       => 'all'
		);
		return get_users( $args );
	}

	/**
	 * Get existing customer list
	 *
	 * @uses WP_Query
	 *
	 * @param integer $nb_per_page Optionnal. The number of element to display per pages
	 * @param integer $offset Optionnal. The current page offset to take in care for pagination definition
	 *
	 * @return WP_Query The complete query for customer list retrieving
	 */
	function get_customer_list( $nb_per_page = 10, $offset = 0, $extra_args = array() ) {
		/**	Define args for listing	*/
		$customer_list_args = array(
			'post_type'				=> WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
			'post_status'			=> array( 'pending', 'draft', 'publish', 'private' ),
			'posts_per_page'	=> $nb_per_page,
		);
		if ( -1 !== $nb_per_page ) {
			$customer_list_args['offset'] = $offset * $nb_per_page;
		}

		$wpshop_customer_search = new wpshop_customer_search();
		add_filter( 'posts_where', array( $wpshop_customer_search, 'wpshop_search_where_in_customer' ) );

		/**	Get customer list with builtin wordpress function	*/
		$customer_list_query = new WP_Query( wp_parse_args( $extra_args, $customer_list_args ) );

		/**	Remove previously added filter for search extension	*/
		remove_filter( 'posts_where', array( $wpshop_customer_search, 'wpshop_search_where_in_customer' ) );

		return $customer_list_query;
	}

	/**
	 * Add a condition to WP_Query in order to get element regarding query search
	 *
	 * @param string $where The current query restriction
	 * @param object $wp_query Current sended query
	 *
	 * @return string The new query restriction with the user search parameters
	 */
	function wps_customer_search_extend( $where, &$wp_query = "" ) {
		global $wpdb;

		$search_term = !empty( $_GET[ 'term' ] ) ? sanitize_text_field($_GET[ 'term' ] ) : ( !empty( $_GET[ 's' ] ) ? sanitize_text_field( $_GET[ 's' ] ) : '' );
		if ( !empty( $search_term ) ) {
			$where .= " AND (
						( {$wpdb->posts}.post_title LIKE '%" . esc_sql( $wpdb->esc_like( $search_term ) ) . "%' )
							OR
						( {$wpdb->posts}.post_author IN ( SELECT ID FROM {$wpdb->users} WHERE display_name LIKE '%" . esc_sql( $wpdb->esc_like( $search_term ) ) . "%' OR user_email LIKE '%" . esc_sql( $wpdb->esc_like( $search_term ) ) . "%' OR user_nicename LIKE '%" . esc_sql( $wpdb->esc_like( $search_term ) ) . "%' ) )
							OR
						( {$wpdb->posts}.post_author IN ( SELECT user_id FROM {$wpdb->usermeta} WHERE ( meta_key = 'first_name' AND meta_value LIKE '%" . esc_sql( $wpdb->esc_like( $search_term ) ) . "%' ) OR ( meta_key = 'last_name' AND meta_value LIKE '%" . esc_sql( $wpdb->esc_like( $search_term ) ) . "%' ) OR ( meta_key = 'nickname' AND meta_value LIKE '%" . esc_sql( $wpdb->esc_like( $search_term ) ) . "%' ) ) )
					)";
		}

		return $where;
	}

}
