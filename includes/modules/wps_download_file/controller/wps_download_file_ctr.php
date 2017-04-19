<?php if ( !defined( 'ABSPATH' ) ) exit;

class wps_download_file_ctr {
	public function __construct() {
		add_action( 'admin_post_wps_download_file', array( $this, 'wps_download_file' ) );
		add_action( 'admin_post_nopriv_wps_download_file', array( $this, 'wps_download_file' ) );
	}

	// Function to find download link by
	public static function get_product_download_link( $oid, $item ) {
		global $wpdb;

		$parent_def = array();
		$item_id = $item['item_id'];
		$item_post_type = get_post_type( $item['item_id'] );
		if ( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION === $item_post_type ) {
			$parent_def = wpshop_products::get_parent_variation( $item['item_id'] );
			if ( ! empty( $parent_def ) && ! empty( $parent_def['parent_post'] ) ) {
				$parent_post = $parent_def['parent_post'];
				$item_id = $parent_post->ID;
				$item_title = $parent_post->post_title;
			}
		}

		$download_link = '';
		$item_id_for_download = null;
		/** Check if the product or the head product is a download product	*/
		if ( ! empty( $parent_def ) ) {
			$parent_meta = $parent_def['parent_post_meta'];
			if ( ! empty( $parent_meta['is_downloadable_'] ) ) {
				$query = $wpdb->prepare( 'SELECT value FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . ' WHERE id = %d', $parent_meta['is_downloadable_'] );
				$downloadable_option_value = $wpdb->get_var( $query );
				if ( ! empty( $downloadable_option_value ) ) {
					$item['item_is_downloadable_'] = $downloadable_option_value;
				}
			}
		}
		if ( ! empty( $item ) && ! empty( $item['item_is_downloadable_'] ) && ( strtolower( __( $item['item_is_downloadable_'], 'wpshop') ) == strtolower( __('Yes', 'wpshop') ) ) ) {
			$item_id_for_download = $item_id;
		}
		if ( isset( $item['item_meta']['variations'] ) ) {
			foreach ( $item['item_meta']['variations'] as $variation_id => $variation ) {
				if ( isset( $variation['item_meta']['is_downloadable_'] ) ) {
					$item_id_for_download = $item_id . '__' . $variation_id;
				}
			}
		}

		/** In case there is a item identifier defined for download */
		if ( null !== $item_id_for_download ) {
			$download_codes = get_user_meta( get_current_user_id(), '_order_download_codes_' . $oid, true );
			/**	Check if the current product exist into download code list, if not check if there is a composition between parent product and children product	*/
			if ( empty( $download_codes[ $item_id_for_download ] ) ) {
				$item_id_component = explode( "__", $item_id_for_download );
				if ( ! empty( $item_id_component ) && ( $item_id_component[0] != $item_id_for_download ) ) {
					$item_id_for_download = $item_id_component[0];
				} elseif ( ! empty( $download_codes[ $item['item_id'] ] ) ) {
					$item_id_for_download = $item['item_id'];
				}
			}

			if ( ! empty( $download_codes ) && ! empty( $download_codes[ $item_id_for_download ] ) && ! empty( $download_codes[ $item_id_for_download ]['download_code'] ) ) {
				$download_link = admin_url( 'admin-post.php?action=wps_download_file&amp;oid=' . $oid . '&amp;download=' . $download_codes[ $item_id_for_download ]['download_code'] );
			}
		}

		return $download_link;
	}

	// Download product downloadable
	public function wps_download_file() {
		$download = !empty( $_GET['download'] ) ? sanitize_text_field( $_GET['download'] ) : '';
		$oid = !empty( $_GET['oid'] ) ? (int) $_GET['oid'] : 0;

		if ( !empty( $download ) && !empty( $oid ) ) {
			$variation_id = '';
			$order = get_post_meta($oid, '_order_postmeta', true);
			if(!empty($order) && !empty( $order['customer_id'] )) {
				$download_codes = get_user_meta( /*wps_customer_ctr::get_author_id_by_customer_id(*/ $order['customer_id'] /*)*/, '_order_download_codes_'.$oid, true);
				if ( !empty($download_codes) && is_array($download_codes) ) {
					foreach ( $download_codes as $downloadable_product_id => $d ) {
						$is_encrypted = false;
						if ( $d['download_code'] == $download ) {
							wpshop_tools::create_custom_hook ('encrypt_actions_for_downloadable_product', array( 'order_id' => $oid, 'download_product_id' => $downloadable_product_id ) );

							if ( get_post_type( $downloadable_product_id ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
								$parent_def = wpshop_products::get_parent_variation( $downloadable_product_id );
								if ( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
									$parent_post = $parent_def['parent_post'];
									$variation_id = $downloadable_product_id;
									$downloadable_product_id = $parent_post->ID;
								}

							} else {
								$downloadable_product_id = explode( "__", $downloadable_product_id );
								$downloadable_product_id = isset( $downloadable_product_id[1] ) ? $downloadable_product_id[1] : $downloadable_product_id[0];
							}

							$link = wpshop_attributes::get_attribute_option_output(
								array('item_id' => $downloadable_product_id, 'item_is_downloadable_'=>'yes'),
								'is_downloadable_', 'file_url', $order
							);

							if ( $link !== false ) {
								$uploads = wp_upload_dir();
								$basedir = $uploads['basedir'];
								$pos = strpos($link, 'uploads');
								$link = $basedir.substr($link,$pos+7);
								/** If plugin is encrypted **/
								$encrypted_plugin_path = get_post_meta( $oid, '_download_file_path_'.$oid.'_'.( ( !empty( $variation_id ) && get_post_type( $variation_id ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) ? $variation_id : $downloadable_product_id ), true);
								if ( !empty($encrypted_plugin_path) ) {
									$link = WPSHOP_UPLOAD_DIR.$encrypted_plugin_path;
									$is_encrypted = true;
								}

								$overload_force_download = apply_filters( 'wps_download_file_overload_force_download', false );
								if ( !$overload_force_download ) {
									wpshop_tools::forceDownload($link, $is_encrypted);
								}
								else {
									wpshop_tools::wpshop_safe_redirect( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $link ) );
								}
							}
						}
					}
				}
			}
			else {
				wp_redirect( get_permalink( wpshop_tools::get_page_id( get_option('wpshop_myaccount_page_id') ) ) );
			}
		}
		echo __('Impossible to download the file you requested', 'wpshop');
	}
}
