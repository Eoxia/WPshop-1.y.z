<?php
/**
 * [class description]
 *
 * @package wpshop
 * @subpackage string
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div class="wps-boxed">
	<span class="wps-h5"><?php _e( 'WPShop is also...', 'wpshop' ); ?></span>
	<div class="wps-gridwrapper4-padded">
		<div><a href="https://shop.eoxia.com/ecommerce/assistance-personnalisee-wordpress/" target="_blank" title="<?php echo esc_attr( 'Assistance', 'wpshop' ); ?>"><img src="<?php echo esc_attr( WPSHOP_MEDIAS_IMAGES_URL ); ?>assistance_wpshop.jpg" alt="WPSHOP Assistance" /></a><div class="wps-h5"><center><?php echo esc_html_e( 'Assistance', 'wpshop' ); ?></center></div><center><?php echo esc_html_e( 'To assist you in your WPShop Experience', 'wpshop' ); ?></center></div>
		<div><a href="https://shop.eoxia.com/themes/" target="_blank" title="<?php echo esc_attr( 'WPSHOP Themes', 'wpshop' ); ?>"><img src="<?php echo esc_attr( WPSHOP_MEDIAS_IMAGES_URL ); ?>themes_wpshop.jpg" alt="WPSHOP Themes" /></a><div class="wps-h5"><center><?php echo esc_html_e( 'WPSHOP Themes', 'wpshop' ); ?></center></div><center><?php echo esc_html_e( 'To offer to your customer all WPShop\'s powerful experience', 'wpshop' ); ?></center></div>
		<div><a href="https://shop.eoxia.com/boutique/wpshop/" target="_blank" title="<?php echo esc_attr( 'WPSHOP\'s add-ons', 'wpshop' ); ?>"><img src="<?php echo esc_attr( WPSHOP_MEDIAS_IMAGES_URL ); ?>modules_wpshop.jpg" alt="WPSHOP Assistance" /></a><div class="wps-h5"><center><?php echo esc_html_e( 'WPSHOP\'s add-ons', 'wpshop' ); ?></center></div><center><?php echo esc_html_e( 'To boost your shop with new functions', 'wpshop' ); ?></center></div>
		<div><a href="http://forums.eoxia.com" target="_blank" title="<?php echo esc_attr( 'WPSHOP\'s Forum', 'wpshop' ); ?>"><img src="<?php echo esc_attr( WPSHOP_MEDIAS_IMAGES_URL ); ?>forum_wpshop.jpg" alt="Forum Assistance" /></a><div class="wps-h5"><center><?php echo esc_html_e( 'WPSHOP\'s Forum', 'wpshop' ); ?></center></div><center><?php esc_html_e( 'To respond at your questions', 'wpshop' ); ?></center></div>
	</div>
	<br/><br/>
	<span class="wps-h6"><?php esc_html_e( 'Be connected', 'wpshop' ); ?></span>
	<div class="wps-gridwrapper2-padded">
		<div>
			<div class="fb-like" data-href="https://fr-fr.facebook.com/wpshopplugin" data-layout="standard" data-action="like" data-show-faces="true" data-share="true"></div>
		</div>
		<div>
			<a href="https://twitter.com/wpshop_plugin" class="twitter-follow-button" data-show-count="false" data-lang="fr" data-size="large">Suivre @wpshop_plugin</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		</div>
	</div>
</div>

<div class="wps-boxed">
	<span class="wps-h5"><?php esc_html_e( 'WPShop\'s Video Tutorials', 'wpshop' )?></span>
	<div><?php $this->wpshop_rss_tutorial_videos(); ?></div>
</div>
