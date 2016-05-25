<?php if ( !defined( 'ABSPATH' ) ) exit;
$shortcode = ( empty($shortcode) ) ? array() : $shortcode;
foreach( $shortcodes as $categorie => $cat_content ) {
	
	echo '<h3>' . $categorie . '</h3>';
	
	echo (!empty($cat_content['desc_cat_' . $categorie])) ? '<p>' . $cat_content['desc_cat_' . $categorie] . '</p>' : '';
	
	$cat_content['items'] = ( empty($cat_content['items']) ) ? array() : $cat_content['items'];
	foreach( $cat_content['items'] as $id_shortcode => $shortcode ) {
		if ( !empty($id_shortcode) ) {
			
			?><p><?php
			
			echo !empty($shortcode['title']) ? '<b>' . __($shortcode['title']) . '</b> ' : '';
			
			?><code>[<?php
			
			echo __($id_shortcode);
			
			$shortcode['args'] = ( empty($shortcode['args']) ) ? array() : $shortcode['args'];
			foreach( $shortcode['args'] as $argument => $parameter ) {
				echo ' ' . $argument . '="' . $parameter . '"';
			}
			
			?>]</code> <?php

			echo !empty($shortcode['description']) ?  __($shortcode['description']) . ' ' : '';
			
			echo '<i style="color:';
			if( $shortcode['active'] == 'true' ) {
				echo 'green">' . __('Active', 'wpshop');
			} else {
				echo 'red">' . __('Inactive', 'wpshop');
			}
			echo '</i>';
			
			?></p><?php
			
		}
	}
}