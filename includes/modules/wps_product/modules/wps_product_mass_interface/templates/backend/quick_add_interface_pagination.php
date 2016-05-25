<?php if ( !defined( 'ABSPATH' ) ) exit;
 if ( !empty( $paginate ) ) : ?>
	<ul class="wps-mass-product-pagination">
	<?php $i = 1; ?>
	<?php foreach ( $paginate as $page ) : ?>
		<li>
		<?php if ( 1 == $i ) : ?>
			<?php echo str_replace( "href=''", 'href="' . admin_url( 'admin-ajax.php?action=wps_add_quick_interface' ) . '"', $page ); ?>
		<?php else: ?>
			<?php echo $page; ?>
		<?php endif; ?>
		</li>
		<?php $i++; ?>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>