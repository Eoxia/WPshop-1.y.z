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
	if(is_user_logged_in()) {
		$user_id = get_current_user_id();
		$order = get_post_meta($_GET['oid'], '_order_postmeta', true);
		if(!empty($order)) {

			$download_codes = get_user_meta($user_id, '_order_download_codes_'.$_GET['oid'], true);

			foreach($download_codes as $d) {
				if($d['download_code'] == $_GET['download']) {

					$link = wpshop_attributes::get_attribute_option_output(
						array('item_id' => $d['item_id'], 'item_is_downloadable_'=>'yes'),
						'is_downloadable_', 'file_url', $order
					);

					if($link!==false) {
						$uploads = wp_upload_dir();
						$basedir = $uploads['basedir'];
						$pos = strpos($link, 'uploads');
						$link = $basedir.substr($link,$pos+7);
						wpshop_tools::forceDownload($link);
					}
				}
			}
		}
	}
}
echo __('Impossible to download the file you requested', 'wpshop');

?>