<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-gridwrapper">
	<div class="wps-grid4x6">
		<span class="wps-h3"><?php _e('My wishlist', 'wps_wishlist_i18n'); ?></span>
	</div>
</div>

<p class='margin-top-20'>
<?php
if(!empty($wishlist_list)):
	foreach($wishlist_list as $name_wishlist => $wishlist):
		?>
		<button class="wps-display-wishlist wps-bton-first-mini-rounded"><?php echo $name_wishlist; ?></button>
		<?php
	endforeach;
else:
	_e('No wishlist actually', 'wps_wishlist_i18n');
endif;
?>
</p>

<p class='wps-container-product-in-wishlist margin-top-20'>
</p>
