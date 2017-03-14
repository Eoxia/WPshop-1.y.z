<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($sales_informations) ): ?>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Date', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Order ID', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Customer', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Order Status', 'wpshop'); ?></div>
		<div class="wps-table-cell"></div>
	</div>
	<?php foreach( $sales_informations as $sale_informations) : ?>
		<div class="wps-table-content wps-table-row">
			<div class="wps-table-cell"><?php echo mysql2date('d F Y, H:i:s',$sale_informations['order_date'], true);?></div>
			<div class="wps-table-cell"><?php echo $sale_informations['order_key']; ?></div>
			<div class="wps-table-cell"><?php echo $sale_informations['customer_name']. ' '.$sale_informations['customer_firstname'].' ('.$sale_informations['customer_email'].')'; ?></div>
			<div class="wps-table-cell"><span class="wps-label-<?php echo $color_label[ strtolower($sale_informations['order_status']) ]; ?>"><?php _e( $order_status[ strtolower($sale_informations['order_status']) ], 'wpshop' ); ?></span></div>
			<div class="wps-table-cell"><a href="<?php echo admin_url('post.php?post=' .$sale_informations['order_id']. '&action=edit'); ?>" class="wps-bton-mini-rounded-first" target="_blank"><?php _e( 'See the order', 'wpshop'); ?></a></div>
		</div>
	<?php endforeach; ?>
</div>
<?php $big = 999999999; // need an unlikely integer
echo paginate_links( array(
	'base' => '%_%',
	'format' => '?paged_sales=%#%',
	'current' => absint( isset( $_GET['paged_sales'] ) ? $_GET['paged_sales'] : 1 ),
	'total' => $orders->max_num_pages
) ); ?>
<?php else : ?>
	<div class="wps-alert-info"><?php _e( 'This product has never been ordered', 'wpshop'); ?></div>
<?php endif; ?>
