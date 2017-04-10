<div class="table table_content">
	<p class="sub"><?php _e('Shop Content', 'wpshop'); ?></p>
	<table>
		<tbody>
			<tr class="first">

				<td class="first b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>"><?php
					$num_posts = wp_count_posts(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
					$number_of_products = number_format_i18n( $num_posts->publish );
					echo $number_of_products;
				?></a></td>
				<td class="t"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>"><?php _e('Products', 'wpshop'); ?></a></td>
			</tr>
			<tr>
				<td class="first b"><a href="edit-tags.php?taxonomy=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES; ?>&amp;post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>"><?php echo wp_count_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES); ?></a></td>
				<td class="t"><a href="edit-tags.php?taxonomy=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES; ?>&amp;post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>"><?php _e('Product Categories', 'wpshop'); ?></a></td>
			</tr>
			<tr>
				<td class="first b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>">
				<?php $num_posts = wp_count_posts(WPSHOP_NEWTYPE_IDENTIFIER_ORDER); echo number_format_i18n($num_posts->publish); ?></a></td>
				<td class="t"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>"><?php _e('Orders', 'wpshop'); ?></a></td>
			</tr>
			<tr>
				<?php
				$query = $wpdb->prepare( "SELECT COUNT( DISTINCT us.ID ) FROM {$wpdb->users} us JOIN {$wpdb->posts} ON us.ID = post_author AND post_type = %s", WPSHOP_NEWTYPE_IDENTIFIER_ORDER );
				$result = $wpdb->get_var($query);
				?>
				<td class="first b"><a href="users.php"><?php echo $result; ?></a></td>
				<td class="t"><a href="users.php"><?php _e('Customers', 'wpshop'); ?></a></td>
			</tr>
			<tr>
				<td class="first b"><a href="users.php"><?php $result = count(get_users()); echo $result; ?></a></td>
				<td class="t"><a href="users.php"><?php _e('Users', 'wpshop'); ?></a></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="table table_discussion">
	<p class="sub"><?php _e('Orders', 'wpshop'); ?></p>
	<table>
		<tbody>
			<?php
			$args = array(
				'numberposts'     => -1,
				'orderby'         => 'post_date',
				'order'           => 'DESC',
				'post_type'       => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
				'post_status'     => 'publish'
			);
			$orders = get_posts( $args );
			$order_completed = $order_shipped = $order_awaiting_payment = $order_denied = $order_canceled = $order_refunded = 0;
			if ( $orders ) {
				foreach ( $orders as $o ) {
					$order = get_post_meta($o->ID, '_order_postmeta', true);
					if(!empty($order['order_status'])){
						switch($order['order_status']) {
							case 'completed': $order_completed++; break;
							case 'shipped': $order_shipped++; break;
							case 'awaiting_payment': $order_awaiting_payment++; break;
							case 'denied': $order_denied++; break;
							case 'canceled': $order_canceled++; break;
							case 'refunded' : $order_refunded++; break;
						}
					}
				}
			}
			?>

			<tr>
				<td class="b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=completed"><span class="total-count"><?php echo $order_completed; ?></span></a></td>
				<td class="last t"><a class="completed" href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=completed"><?php _e('Completed', 'wpshop'); ?></a></td>
			</tr>

			<tr>
				<td class="b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=shipped"><span class="total-count"><?php echo $order_shipped; ?></span></a></td>
				<td class="last t"><a class="shipped" href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=completed"><?php _e('Shipped', 'wpshop'); ?></a></td>
			</tr>

			<tr class="first">
				<td class="b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=awaiting_payment"><span class="total-count"><?php echo $order_awaiting_payment; ?></span></a></td>
				<td class="last t"><a class="pending" href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=awaiting_payment"><?php _e('Awaiting payment', 'wpshop'); ?></a></td>
			</tr>

			<tr>
				<td class="b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=denied"><span class="total-count"><?php echo $order_denied; ?></span></a></td>
				<td class="last t"><a class="denied" href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=denied"><?php _e('Denied', 'wpshop'); ?></a></td>
			</tr>
			<tr>
				<td class="b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=canceled"><span class="total-count"><?php echo $order_canceled; ?></span></a></td>
				<td class="last t"><a class="canceled" href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=canceled"><?php _e('Canceled', 'wpshop'); ?></a></td>
			</tr>
			<tr>
				<td class="b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=refunded"><span class="total-count"><?php echo $order_refunded; ?></span></a></td>
				<td class="last t"><a class="refunded" href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>&amp;shop_order_status=refunded"><?php _e('Refunded', 'wpshop'); ?></a></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="versions">
	<p id="wp-version-message"><?php _e('You are using', 'wpshop'); ?> <strong>WPShop <?php echo WPSHOP_VERSION; ?></strong></p>
</div>
