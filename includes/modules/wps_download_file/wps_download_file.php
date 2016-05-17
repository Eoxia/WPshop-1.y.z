<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
* Plugin force download
*
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage includes
*/

require_once( dirname( __FILE__ ) . '/controller/wps_download_file_ctr.php' );
new wps_download_file_ctr();