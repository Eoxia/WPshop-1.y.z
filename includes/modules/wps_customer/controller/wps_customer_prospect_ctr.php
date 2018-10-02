<?php
/**
 * Handle Customer Prospect
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2006 2018 Eoxia <dev@eoxia.com>.
 *
 * @license   GPLv3 <https://spdx.org/licenses/GPL-3.0-or-later.html>
 *
 * @package   WPshop\Classes
 *
 * @since     1.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WPS Customer Prospect Class.
 */
class WPS_Customer_Prospect  {

	public $statuses;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		$this->statuses = array(
			'do-not-contact' => array(
				'id'   => -1,
				'text' => __( 'Do not contact', 'wpshop' ),
				'icon' => '',

			),
			'to-be-contacted' => array(
				'id'   => 0,
				'text' => __( 'To be contacted', 'wpshop' ),
				'icon' => '',
			),
			'contact-in-process' => array(
				'id'   => 2,
				'text' => __( 'Contact in process', 'wpshop' ),
				'icon' => '',
			),
			'contact-done' => array(
				'id'   => 3,
				'text' => __( 'Contact done', 'wpshop' ),
				'icon' => '',
			)
		);

		add_action( 'save_post_wpshop_customers', array( $this, 'callback_save_post' ), 11, 2 );

		add_filter( 'manage_edit-wpshop_customers_columns', array( $this, 'callback_wpshop_customers_posts_columns' ), 11 );
		add_filter( 'manage_wpshop_customers_posts_custom_column', array( $this, 'callback_wpshop_customers_posts_custom_column' ), 10, 2 );
		add_filter( 'manage_edit-wpshop_customers_sortable_columns', array( $this, 'callback_wpshop_customers_sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'callback_pre_get_posts' ) );
	}

	/**
	 * Save fk_stcomm meta.
	 *
	 * @since 1.7.0
	 *
	 * @param  integer $post_id The post ID.
	 * @param  WP_Post $post    The WP_Post Object.
	 */
	public function callback_save_post( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$fk_stcomm = isset( $_POST['fk_stcomm'] ) ? (int) $_POST['fk_stcomm'] : 0;
		update_post_meta( $post_id, 'fk_stcomm', $fk_stcomm );
	}

	/**
	 * Ajoutes les colones "Generate date" et "Preview TXT".
	 *
	 * @since 1.7.0
	 *
	 * @param array $columns Les colonnes à rajouter.
	 *
	 * @return array         Les colonnes à rajouter.
	 */
	public function callback_wpshop_customers_posts_columns( $columns ) {
		$columns['prospect_status'] = __( 'Prospect status', 'wpshop' );
		return $columns;
	}
	/**
	 * Le contenu des colonnes par post.
	 *
	 * @since 1.7.0
	 *
	 * @param string  $column  Le slug de la colonne.
	 * @param integer $post_id L'ID du post.
	 */
	public function callback_wpshop_customers_posts_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'prospect_status':
				$current_id_status = (int) get_post_meta( $post_id, 'fk_stcomm', true );
				$current_status    = $this->statuses['to-be-contacted'];

				if ( ! empty( $this->statuses ) ) {
					foreach ( $this->statuses as $status ) {
						if ( $status['id'] === $current_id_status ) {
							$current_status = $status;
						}
					}
				}

				echo $current_status['text'];
				break;
			default:
				break;
		}
	}

	public function callback_wpshop_customers_sortable_columns( $columns ) {
		$columns['prospect_status'] = 'prospect_order';
		return $columns;
	}

	public function callback_pre_get_posts( $query ) {
		if ( ! is_admin() || $query->is_main_query() ) {
			return;
		}

		if ( 'prospect_order' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', 'fk_stcomm' );
			$query->set( 'meta_type', 'numeric' );
		}
	}
}

new WPS_Customer_Prospect();
