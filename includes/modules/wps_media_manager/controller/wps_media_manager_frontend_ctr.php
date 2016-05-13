<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_media_manager_frontend_ctr {

	public static function get_attachments( $pid, $type = 'gallery' ) {
		$attachments = array();
		$media_id_data = get_post_meta( $pid, '_wps_product_media', true );
		if( !empty($media_id_data) ) {
			$media_id = explode( ',', $media_id_data );
			foreach($media_id as $id) {
				if( ( ( wp_attachment_is_image( $id ) && $type == 'gallery' ) || ( !empty( $id ) && !wp_attachment_is_image( $id ) && $type == 'attachments' ) ) ) {
					$attachment_data = get_post( $id );
					if( !empty( $attachment_data ) && !empty($attachment_data->ID) ) {
						$attachments[] = $attachment_data;
					}
				}
			}
		}
		return $attachments;
	}

	/**
	 * Return the output for a product attachement gallery (picture or document)
	 *
	 * @param string $attachement_type The type of attachement to output. allows to define with type of template to take
	 * @param string $content The gallery content build previously
	 *
	 * @return string The attachement gallery output
	 */
	public static function display_attachment_gallery( $attachement_type, $content ) {
		$galery_output = '';

		/*
		 * Get the template part for given galery type
		 */
		switch ( $attachement_type ) {
			case 'picture':
					$template_part = 'product_attachment_picture_galery';
				break;
			case 'document':
					$template_part = 'product_attachment_galery';
				break;
		}

		/*
		 * Template parameters
		 */
		$tpl_component = array();
		$tpl_component['PRODUCT_ATTACHMENT_OUTPUT_CONTENT'] = $content;
		$tpl_component['ATTACHMENT_ITEM_TYPE'] = $attachement_type;

		/*
		 * Build template
		 */
		$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
		if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
			/*	Include the old way template part	*/
			ob_start();
			require(wpshop_display::get_template_file($tpl_way_to_take[1]));
			$galery_output = ob_get_contents();
			ob_end_clean();
		}
		else {
			$galery_output = wpshop_display::display_template_element($template_part, $tpl_component);
		}
		unset($tpl_component);

		return $galery_output;
	}

	/**
	 * Get product Complete Sheet
	 * @param integer $pid
	 * @return Ambigous <string, string>
	 */
	public static function get_product_complete_sheet_galery( $pid ) {
		$output = '';
		if( !empty($pid) ) {
			$tpl_component = $sub_tpl_component = array();
			$attachments = array();
			$tpl_component['THUMBNAILS'] = '';
			$tpl_component['SLIDER_CONTENT'] = '';

			/**	Check and get the product thumbnail	*/
			$principal_thumbnail_id = get_post_meta( $pid, '_thumbnail_id', true);
			if( !empty($principal_thumbnail_id) ) {
				$attachments[0] = get_post( $principal_thumbnail_id );
			}
			else {
				$sub_tpl_component['THUMBNAIL_GALLERY_THUMBNAIL'] = '';
				$sub_tpl_component['IMAGE_SLIDER_FULL'] = '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="" />';
				$sub_tpl_component['THUMBNAIL_GALLERY_FULL'] = '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="" />';
				$sub_tpl_component['THUMBNAIL_GALLERY_THUMBNAIL_ID'] = '';

				$tpl_component[ 'SLIDER_CONTENT' ] .= wpshop_display::display_template_element( 'wps_product_complete_sheet_gallery_slider_element', $sub_tpl_component );
				$tpl_component[ 'THUMBNAILS' ] = '';
			}

			/**	Get product associated pictures	*/
			$allowed_mime_type = get_allowed_mime_types();

			$attachments = array_merge( $attachments, (array)self::get_attachments( $pid ) );

			/**	In case there are picture read and display them into product sheet	*/
			if ( !empty($attachments) ) {
				foreach( $attachments as $attachment) {
					if( !empty($attachment) && !empty($attachment->post_mime_type) && in_array( $attachment->post_mime_type, $allowed_mime_type ) ) {
						//IMAGE SLIDER
						$image_attributes = wp_get_attachment_metadata( $attachment->ID );
						$sub_tpl_component['THUMBNAIL_GALLERY_THUMBNAIL'] = '';
						$sub_tpl_component['THUMBNAIL_GALLERY_THUMBNAIL_ID'] = $attachment->ID;
						if ( !empty($image_attributes) && !empty($image_attributes['sizes']) && is_array($image_attributes['sizes']) ) {
							foreach ( $image_attributes['sizes'] as $size_name => $size_def) {
								$p = wp_get_attachment_image( $attachment->ID, $size_name);
								$src = wp_get_attachment_image_src( $attachment->ID, $size_name );
								$sub_tpl_component['THUMBNAIL_GALLERY_' . strtoupper($size_name)] = '';
								$sub_tpl_component['IMAGE_SLIDER_' . strtoupper($size_name)] = '';
								if( !empty($p) ) {
									$sub_tpl_component['SRC_IMAGE_SLIDER_' . strtoupper($size_name)] = ( !empty($src) ) ? $src[0] : '';
									$sub_tpl_component['IMAGE_SLIDER_' . strtoupper($size_name)] = ( !empty( $p ) ) ? $p : '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="" />';
									$sub_tpl_component['THUMBNAIL_GALLERY_' . strtoupper($size_name)] = ( !empty( $p ) ) ? $p : '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="" />';
								}
							}
							$p = wp_get_attachment_image( $attachment->ID, 'full');
							$src = wp_get_attachment_image_src( $attachment->ID, 'full' );
							if( !empty($p) ) {
								$sub_tpl_component['SRC_IMAGE_SLIDER_FULL'] = ( !empty($src) ) ? $src[0] : '';
								$sub_tpl_component['IMAGE_SLIDER_FULL'] = ( !empty($p) ) ? $p : '';
								$sub_tpl_component['THUMBNAIL_GALLERY_FULL'] = ( !empty($p) ) ? $p : '';
							}
						}
						else {
							$p = wp_get_attachment_image( $attachment->ID, 'full' );
							$src = wp_get_attachment_image_src( $attachment->ID, 'full' );
							$sub_tpl_component['SRC_IMAGE_SLIDER_FULL'] = ( !empty($src) ) ? $src[0] : '';
							$sub_tpl_component['IMAGE_SLIDER_FULL'] = ( !empty( $p ) ) ? $p : '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="" />';
							$sub_tpl_component['THUMBNAIL_GALLERY_FULL'] = ( !empty( $p ) ) ? $p : '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="" />';
						}
						if ( !empty( $sub_tpl_component['IMAGE_SLIDER_FULL'] ) ) {
							$tpl_component[ 'SLIDER_CONTENT' ] .= wpshop_display::display_template_element( 'wps_product_complete_sheet_gallery_slider_element', $sub_tpl_component );
							if ( ( 1 < count( $attachments ) ) || ( ( 1 == count( $attachments ) ) && empty( $principal_thumbnail_id ) ) ) {
								$tpl_component[ 'THUMBNAILS' ] .= wpshop_display::display_template_element( 'wps_product_complete_sheet_gallery_thumbnail_element', $sub_tpl_component );
							}
						}
						unset( $sub_tpl_component );
					}
					else {
						$sub_tpl_component['SRC_IMAGE_SLIDER_FULL'] = '';
						$sub_tpl_component['IMAGE_SLIDER_FULL'] = '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="" />';
						$sub_tpl_component['THUMBNAIL_GALLERY_FULL'] = '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="" />';
					}
				}
			}

			$output = wpshop_display::display_template_element('wps_product_complete_sheet_gallery', $tpl_component);
			unset($tpl_component);
		}
		return $output;
	}

	public static function get_product_complete_sheet_attachments( $product_id ) {
		$output = '';

		/**	Get attachement file for the current product	*/
		$product_picture_galery_content = $product_document_galery_content = '';
		$picture_number = $document_number = $index_li = 0;

		$attachments = self::get_attachments( $product_id, 'attachments' );

		if ( is_array($attachments) && (count($attachments) > 0) ) {
			$picture_increment = $document_increment = 1;
			foreach ($attachments as $attachment) {
				$tpl_component = array();
				$attachment_url = wp_get_attachment_url( $attachment->ID );
				$attachment_extension = explode('.', $attachment_url);
				$attachment_extension = $attachment_extension[1];
				$tpl_component['ATTACHMENT_ITEM_GUID'] = $attachment_url;
				$tpl_component['ATTACHMENT_ITEM_TITLE'] = $attachment->post_title;
				$tpl_component['ATTACHMENT_ITEM_EXTENSION'] = $attachment_extension;
				if(is_int(strpos($attachment->post_mime_type, 'application/')) || is_int(strpos($attachment->post_mime_type, 'text/'))) {
					$tpl_component['ATTACHMENT_ITEM_TYPE'] = 'document';
					$tpl_component['ATTACHMENT_ITEM_SPECIFIC_CLASS'] = (!($document_increment%WPSHOP_DISPLAY_GALLERY_ELEMENT_NUMBER_PER_LINE)) ? 'wpshop_gallery_document_last' : '';
					/** Template parameters	*/
					$template_part = 'product_attachment_item_document';
					$tpl_component['PRODUCT_ID'] = $product_id;

					/** Build template	*/
					$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
					if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
						/*	Include the old way template part	*/

						ob_start();
						require(wpshop_display::get_template_file($tpl_way_to_take[1]));
						$product_document_galery_content .= ob_get_contents();
						ob_end_clean();
					}
					else {
						$product_document_galery_content .= wpshop_display::display_template_element($template_part, $tpl_component);
					}

					$document_number++;
					$document_increment++;
				}
				unset($tpl_component);
			}

			ob_start();
			require( wpshop_tools::get_template_part( WPS_MEDIA_MANAGER_DIR, WPS_MEDIA_MANAGER_TEMPLATE_DIR, 'frontend', 'associated', 'document' ) );
			$output = ob_get_contents();
			ob_end_clean();
		}

		return $output;
	}

}
