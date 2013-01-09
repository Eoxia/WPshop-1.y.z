<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Document management method file
* 
*	This file contains the different methods for document management
* @author Eoxia <dev@eoxia.com>
* @version 1.2
* @package wpshop
* @subpackage librairies
*/

/**
*	This file contains the different methods for document management
* @author Eoxia <dev@eoxia.com>
* @version 1.2
* @package wpshop
* @subpackage librairies
*/
class wpshop_documents
{
	/**
	* This filter translates string before it is displayed
	* specifically for the words 'Use as featured image' with 'Use as Product Thumbnail' when the user is selecting a Product Thumbnail
	* using media gallery.
	*
	* @param $translation The current translation
	* @param $text The text being translated
	* @param $domain The domain for the translation
	*
	* @return string The translated / filtered text.
	*/
	function change_picture_translation($translation, $text, $domain = 'wpshop'){

		if(($text == 'Use as featured image') && isset($_REQUEST['post_id'])){

			$post = get_post( $_REQUEST['post_id'] );
			if (!empty($post->post_type) && $post->post_type != WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) return $translation;
			$translations = &get_translations_for_domain($domain);
			if ( empty($translations->entries['Use as product thumbnail']->translations[0]) ) return $translation;
			return $translations->entries['Use as product thumbnail']->translations[0];
		}

		return $translation;
	}

	/**
	*	
	*/
	function attachment_fields($form_fields, $post){
		/*	Get the current post informations	*/
		if(isset($_GET["post_id"])){
			$parent_post = get_post( absint($_GET["post_id"]) );
		}
		else{
			$parent_post = get_post( $post->post_parent );
		}

		if($parent_post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT){
			
		}

		return $form_fields;
	}

	/**
	*
	*/
	function galery_manager_css(){
		ob_start();
		include(WPSHOP_CSS_DIR . 'pages/wpshop_galery.css');
		$wpshop_galery_css = ob_get_contents();
		ob_end_clean();
		print '
<style type="text/css">
	' . $wpshop_galery_css . '
</style>';
	}

}