<?php namespace eoxia;

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( '\eoxia\Term_Attachment_Class' ) ) {
	class term_attachment_class extends Singleton_Util {
		protected $model_name = 'term_model';
		protected $meta_key = '_wpeo_term';
		protected $taxonomy = 'attachment_category';
		protected $base = 'term';
		protected $version = '0.1';
		protected $identifier_helper = 'term';

		protected function construct() {}
	}
}
