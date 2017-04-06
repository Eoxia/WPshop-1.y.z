<?php
/**
 * Display the last order list
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ordered_products = array();
foreach ( $shop_orders as $order ) {
	foreach ( $order['order_postmeta']['order_items'] as $product_id => $product_def ) {
		if ( ! isset( $ordered_products[ $product_id ]['count'] ) ) {
			$ordered_products[ $product_id ]['count'] = 0;
		}
		$ordered_products[ $product_id ]['count']++;
		$ordered_products[ $product_id ]['id'] = $product_id;
		$ordered_products[ $product_id ]['name'] = $product_def['item_name'];
	}
}
usort( $ordered_products, function( $a, $b ) {
	if ($a['count'] == $b['count']) {
    return 0;
  }
  return ($a['count'] > $b['count']) ? -1 : 1;
} );
$best_sales = array_slice( $ordered_products, 0, 5 );
if ( !empty( $best_sales ) ) :
?>
<div class="wps-table">
<?php foreach( $best_sales as $product ) : ?>
	<div class="wps-table wps-table-row">
		<div class="wps-table-cell textleft" ><a href="<?php echo admin_url('post.php?post=' .$product['id']. '&action=edit'); ?>"><?php echo $product['name']; ?></a></div>
		<div class="wps-table-cell" ><?php printf( __('%s sales', 'wpshop'), $product['count']); ?></div>
	</div>
<?php endforeach; ?>
</div>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No orders have been placed for the moment', 'wpshop'); ?></div>
<?php endif; ?>
