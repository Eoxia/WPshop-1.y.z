<?php
/**
 * Gestion des attachements (POST, PUT, GET, DELETE)
 *
 * @author Jimmy Latour <dev@eoxia.com>
 * @since 1.0.0.0
 * @version 1.3.0.0
 * @copyright 2015-2017
 * @package wpeo_model
 * @subpackage class
 *
 * @todo: Ne peut pas marcher... A voir
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( '\eoxia\Attachment_Class' ) ) {

	/**
	 * Gestion des attachements (POST, PUT, GET, DELETE)
	 */
	class Attachment_Class extends Singleton_Util {
		protected $model_name = 'post_model';
		protected $post_type = 'attachment';
		protected $meta_key = '_wpeo_attachment';
		protected $version = '0.1.0.0';

		protected function construct() {}
	}
}
