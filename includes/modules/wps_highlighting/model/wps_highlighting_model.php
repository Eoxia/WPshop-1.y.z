<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_highlighting_model {
	
	var $id;
	var $title;
	var $hook;
	var $link;
	
	function __construct( $id = '',  $title = '', $hook = '', $link = '') {
		if( !empty($id) ) {
			$this->id = $id;
			$this->title = $title;
			$this->hook = $hook;
			$this->link = $link;
		}
	}
	
	function Create( $title, $hook, $link ) {
		if( empty($this->id) ) {
			if( !empty($title) && !empty($hook) && !empty($link) ) {
				$this->title = $title;
				$this->hook = $hook;
				$this->link = $link;
			}
		}
	}
	
	function get_highlighting( $hook = '', $id = '' ) {
		global $wpdb;
		$data = array();
		if( !empty($id) ) {
			$highlighting  = get_post( $id );
			$hook_metadata = get_post_meta( $id, '_wps_highlighting_hook', true );
			$link_metadata = get_post_meta( $id, '_wps_highlighting_link', true );
			// Fill Data
			$data[0]['post_data'] = $highlighting;
			$data[0]['post_meta']['hook'] = $hook_metadata;
			$data[0]['post_meta']['link'] = $link_metadata;
		}
		else {
			$highlightings = get_posts( array( 'posts_per_page' => -1, 'post_type' => WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING ) );
			foreach( $highlightings as $highlighting ) {
				$hook_metadata = get_post_meta( $highlighting->ID, '_wps_highlighting_hook', true );
				$link_metadata = get_post_meta( $highlighting->ID, '_wps_highlighting_link', true );
				// Fill Data
				if( empty($hook) || ( !empty($hook) && $hook == $hook_metadata ) ) {
					$data[$highlighting->ID]['post_data'] = $highlighting;
					$data[$highlighting->ID]['post_meta']['hook'] = $hook_metadata;
					$data[$highlighting->ID]['post_meta']['link'] = $link_metadata;
				}
			}
		}
		return $data;
	}
	
	
	
	
}