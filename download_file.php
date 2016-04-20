<?php
/**
* Plugin force download
*
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage includes
*/

/*	Wordpress - Ajax functionnality activation	*/
DEFINE('DOING_AJAX', true);
/*	Wordpress - Specify that we are in wordpress admin	*/
DEFINE('WP_ADMIN', true);
/*	Wordpress - Main bootstrap file that load wordpress basic files	*/
require_once('../../../wp-load.php');
/*	Wordpress - Admin page that define some needed vars and include file	*/
require_once(ABSPATH . 'wp-admin/includes/admin.php');

// T�l�chargement produit t�l�chargeable
if (!empty($_GET['download']) && !empty($_GET['oid'])) {
	$variation_id = '';
	if(is_user_logged_in()) {
		$user_id = get_current_user_id();
		$order = get_post_meta($_GET['oid'], '_order_postmeta', true);
		if(!empty($order)) {
			$download_codes = get_user_meta($user_id, '_order_download_codes_'.$_GET['oid'], true);
			if ( !empty($download_codes) && is_array($download_codes) ) {
				foreach ( $download_codes as $downloadable_product_id => $d ) {
					$is_encrypted = false;
					if ( $d['download_code'] == $_GET['download'] ) {
						wpshop_tools::create_custom_hook ('encrypt_actions_for_downloadable_product', array( 'order_id' => $_GET['oid'], 'download_product_id' => $downloadable_product_id ) );
						
						if ( get_post_type( $downloadable_product_id ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
							$parent_def = wpshop_products::get_parent_variation( $downloadable_product_id );
							if ( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
								$parent_post = $parent_def['parent_post'];
								$variation_id = $downloadable_product_id;
								$downloadable_product_id = $parent_post->ID;
							}
							
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
							$encrypted_plugin_path = get_post_meta( $_GET['oid'], '_download_file_path_'.$_GET['oid'].'_'.( ( !empty( $variation_id ) && get_post_type( $variation_id ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) ? $variation_id : $downloadable_product_id ), true);
							if ( !empty($encrypted_plugin_path) ) {
								$link = WPSHOP_UPLOAD_DIR.$encrypted_plugin_path;
								$is_encrypted = true;
							}

							wpshop_tools::forceDownload($link, $is_encrypted);
						}
					}
				}
			}
		}
	}
	else {
		wp_redirect( get_permalink( wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id')) ) );
	}
}
echo __('Impossible to download the file you requested', 'wpshop');