<?php if ( !defined( 'ABSPATH' ) ) exit;

if(!empty($my_wishlist) && is_array($my_wishlist)):
	?>
	<div class="wpshop_product_container" data-nonce="<?php echo wp_create_nonce( 'products_by_criteria' ); ?>">
		<div class="container_product_listing wps-bloc-loader">
			<ul class="products_listing wpshop_clearfix grid_3 grid_mode">
				<?php
				foreach($my_wishlist as $product):
					echo $products->product_mini_output($product, 0, 'grid');
				endforeach;
				?>
			</ul>
		</div>
		<!--<h3>
			<?php _e('Use this link for share your wishlist', 'wps_wishlist_i18n'); ?>
		</h3> -->
		<!--<div class='full-width'>
			<input type='text' class='full-width' value='<?php echo get_site_url(); ?>/customer/<?php echo $name_user; ?>/?name_wishlist=<?php echo $name_wishlist; ?>' />
		</div>-->
	</div>
	<?php
else:
	_e('No product found in this wishlist', 'wps_wishlist_i18n');
endif;
