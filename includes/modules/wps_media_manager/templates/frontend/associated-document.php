<?php if ( !defined( 'ABSPATH' ) ) exit;
 if ( $document_number >= 1 ) : ?>
	<h2><?php _e( 'Associated document', 'wpshop' ); ?></h2>
	<?php echo self::display_attachment_gallery( 'document', $product_document_galery_content ); ?>
<?php endif; ?>