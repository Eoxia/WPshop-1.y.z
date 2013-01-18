<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

class wpshop_dashboard {
	function display_dashboard() {
		global $order_status, $wpdb;

	ob_start();
?>
	<div id="wpshop_dashboard">

		<div id="dashboard-widgets" class="metabox-holder">

			<div class="postbox-container" style="width:49%;">

				<div id="wpshop_right_now" class="wpshop_right_now postbox">
					<h3><?php _e('Right Now', 'wpshop') ?></h3>
					<div class="inside">

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
										<td class="first b"><a href="users.php"><?php $result = count_users(); echo $result['total_users']; ?></a></td>
										<td class="t"><a href="users.php"><?php _e('Customers', 'wpshop'); ?></a></td>
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
									$orders = get_posts($args);
									$order_completed = $order_shipped = $order_awaiting_payment = $order_denied = $order_canceled = 0;
									if ($orders) {
										foreach ($orders as $o) {
											$order = get_post_meta($o->ID, '_order_postmeta', true);
											if(!empty($order)){
												switch($order['order_status']) {
													case 'completed': $order_completed++; break;
													case 'shipped': $order_shipped++; break;
													case 'awaiting_payment': $order_awaiting_payment++; break;
													case 'denied': $order_denied++; break;
													case 'canceled': $order_canceled++; break;
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

								</tbody>
							</table>
						</div>
						<div class="versions">
							<p id="wp-version-message"><?php _e('You are using', 'wpshop'); ?> <strong>WPShop <?php echo WPSHOP_VERSION; ?></strong></p>
						</div>
						<div class="clear"></div>
					</div>

				</div><!-- postbox end -->

				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Customers stats', 'wpshop') ?></span></h3>
					<div class="inside">

						<span class="alignright"><?php $result = count_users(); echo $result['total_users']; ?></span>
						<label><?php _e('Number of customers', 'wpshop'); ?></label><br />

						<?php
						// New customers
						$query = $wpdb->prepare('SELECT COUNT(*) FROM '.$wpdb->users.' WHERE user_registered > (NOW()-INTERVAL 7 DAY)', '');
						$result = $wpdb->get_var($query);
						?>
						<span class="alignright"><?php echo $result; ?></span>
						<label><?php _e('New customers', 'wpshop'); ?></label><br />

						<?php
						// Number of customers who ordered
						$query = $wpdb->prepare('SELECT COUNT(DISTINCT post_author) FROM '.$wpdb->posts.' WHERE post_type="'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'"', '');
						$result = $wpdb->get_var($query);
						?>
						<span class="alignright"><?php echo $result; ?></span>
						<label><?php _e('Number of customers who ordered', 'wpshop'); ?></label>

					</div>
				</div><!-- postbox end -->

				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Products stats', 'wpshop') ?></span></h3>
					<div class="inside">

						<?php
						// Number of products on sale
						$query = $wpdb->prepare('SELECT COUNT(*) FROM '.$wpdb->posts.' WHERE post_type="'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'" AND post_status="publish"', '');
						$result = $wpdb->get_var($query);
						?>
						<span class="alignright"><?php echo $result; ?></span>
						<label><?php _e('Number of products on sale', 'wpshop'); ?></label><br />

						<!--<span class="alignright">0</span>
						<label><?php //_e('Number of promotional products', 'wpshop'); ?></label><br />-->

						<?php

						$args = array(
							'numberposts'     => -1,
							'orderby'         => 'post_date',
							'order'           => 'DESC',
							'post_type'       => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
							'post_status'     => 'publish'
						);
						$products = get_posts($args);
						$in_string='';
						foreach ($products as $p) {
							$in_string.=$p->ID.',';
						}
						$in_string=substr($in_string,0,-1);

						// Number of products out of stock
						$query = $wpdb->prepare('
							SELECT COUNT(DISTINCT(wp_wpshop__attribute_value_decimal.entity_id)) FROM wp_wpshop__attribute_value_decimal
							LEFT JOIN wp_wpshop__attribute ON wp_wpshop__attribute.id = wp_wpshop__attribute_value_decimal.attribute_id
							WHERE wp_wpshop__attribute.code="product_stock" AND wp_wpshop__attribute_value_decimal.value>0 AND wp_wpshop__attribute_value_decimal.entity_id IN('.$in_string.')
						', '');
						$result_stock_sup0 = $wpdb->get_var($query);
						$result_stock_sup0 = !empty($result_stock_sup0) ? $result_stock_sup0 : 0;

						$result = $number_of_products-$result_stock_sup0;
						?>
						<span class="alignright"><?php echo $result; ?></span>
						<label><?php _e('Number of products out of stock', 'wpshop'); ?></label><br />

						<?php
						// Number of products sold the last 7 days
						$result=0;
						$query = $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE post_type="'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'" AND post_date > (NOW()-INTERVAL 7 DAY)', '');
						$data = $wpdb->get_results($query, ARRAY_A);
						foreach($data as $d) {
							$postmeta = get_post_meta($d['ID'], '_order_postmeta', true);
							if(!empty($postmeta) && !empty($postmeta['order_items'])) {
								foreach($postmeta['order_items'] as $i) {
									$result += $i['item_qty'];
								}
							}
						}
						?>
						<span class="alignright"><?php echo $result; ?></span>
						<label><?php _e('Number of products sold the last 7 days', 'wpshop'); ?></label>

					</div>
				</div><!-- postbox end -->

				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Recent Orders', 'wpshop') ?></span></h3>
					<div class="inside">
						<?php
							$args = array(
								'numberposts'     => 10,
								'orderby'         => 'post_date',
								'order'           => 'DESC',
								'post_type'       => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
								'post_status'     => 'publish'
							);
							$orders = get_posts( $args );
							if ($orders) {
								echo '<ul class="recent-orders">';
								foreach ($orders as $o) :

									$order = get_post_meta($o->ID, '_order_postmeta', true);

									$nb_items = !empty($order['order_items']) ? sizeof($order['order_items']) : 0;
									$total = !empty($order['order_grand_total']) ? $order['order_grand_total'] : 0;

									echo '
									<li>
										<span class="order-status '.$order['order_status'].'">'.__($order_status[$order['order_status']],'wpshop').'</span>
										<a href="'.admin_url('post.php?post='.$o->ID).'&amp;action=edit">'.get_the_time('l j F Y, h:i:s A', $o->ID).'</a><br />
										'.$nb_items.' '.__('items', 'wpshop').' &mdash; <span class="order-cost">'.__('Total', 'wpshop').': '.$total.' '.wpshop_tools::wpshop_get_currency().'</span>
									</li>';

								endforeach;
								echo '</ul>';
							}
							else {
								echo __('There is no order yet','wpshop');
							}
						?>
					</div>
				</div><!-- postbox end -->



			</div>
			<div class="postbox-container" style="width:49%; float:right;">

				<?php
					global $current_month_offset;

					$current_month_offset = (int) date('m');

					if (isset($_GET['month'])) $current_month_offset = (int) $_GET['month'];
				?>
				<div class="postbox stats" id="wpshop-stats">
					<h3 class="hndle">
						<?php if ($current_month_offset!=date('m')) : ?>
							<a href="admin.php?page=wpshop_dashboard&amp;month=<?php echo $current_month_offset+1; ?>" class="next"><?php echo __('Next Month','wpshop'); ?> &rarr;</a>
						<?php endif; ?>
						<a href="admin.php?page=wpshop_dashboard&amp;month=<?php echo $current_month_offset-1; ?>" class="previous">&larr; <?php echo __('Previous Month','wpshop'); ?></a>
						<span><?php _e('Monthly Sales', 'wpshop') ?></span></h3>
					<div class="inside">
						<div id="placeholder" style="width:100%; height:300px; position:relative;"></div>
						<script type="text/javascript">
							/* <![CDATA[ */

							jQuery(function(){

								function weekendAreas(axes) {
									var markings = [];
									var d = new Date(axes.xaxis.min);
									// go to the first Saturday
									d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
									d.setUTCSeconds(0);
									d.setUTCMinutes(0);
									d.setUTCHours(0);
									var i = d.getTime();
									do {
										// when we don't set yaxis, the rectangle automatically
										// extends to infinity upwards and downwards
										markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
										i += 7 * 24 * 60 * 60 * 1000;
									} while (i < axes.xaxis.max);

									return markings;
								}

								<?php

									function orders_this_month( $where = '' ) {
										global $current_month_offset;

										$month = $current_month_offset;
										$year = (int) date('Y');

										$first_day = strtotime("{$year}-{$month}-01");
										$last_day = strtotime('-1 second', strtotime('+1 month', $first_day));

										$after = date('Y-m-d', $first_day);
										$before = date('Y-m-d', $last_day);

										$where .= " AND post_date > '$after'";
										$where .= " AND post_date < '$before'";

										return $where;
									}
									add_filter( 'posts_where', 'orders_this_month' );

									$args = array(
										'numberposts'      => -1,
										'orderby'          => 'post_date',
										'order'            => 'DESC',
										'post_type'        => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
										'post_status'      => 'publish' ,
										'suppress_filters' => false
									);
									$orders = get_posts( $args );

									$order_counts = array();
									$order_amounts = array();

									// Blank date ranges to begin
									$month = $current_month_offset;
									$year = (int) date('Y');

									$first_day = strtotime("{$year}-{$month}-01");
									$last_day = strtotime('-1 second', strtotime('+1 month', $first_day));

									if ((date('m') - $current_month_offset)==0) :
										$up_to = date('d', strtotime('NOW'));
									else :
										$up_to = date('d', $last_day);
									endif;
									$count = 0;

									while ($count < $up_to) :

										$time = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $first_day))).'000';

										$order_counts[$time] = 0;
										$order_amounts[$time] = 0;

										$count++;
									endwhile;

									if ($orders) :
										foreach ($orders as $order) :

											$order_data = get_post_meta($order->ID, '_order_postmeta', true);

											if ($order_data['order_status']=='denied' || $order_data['order_status']=='awaiting_payment') continue;

											$time = strtotime(date('Ymd', strtotime($order_data['order_date']))).'000';

											$order_grand_total = !empty($order_data['order_grand_total']) ? $order_data['order_grand_total'] : 0;

											if (isset($order_counts[$time])) : $order_counts[$time]++;
											else : $order_counts[$time] = 1; endif;

											if (isset($order_amounts[$time])) $order_amounts[$time] = $order_amounts[$time] + $order_grand_total;
											else $order_amounts[$time] = (float) $order_grand_total;

										endforeach;
									endif;

									remove_filter( 'posts_where', 'orders_this_month' );
								?>

								var d = [
									<?php
										$values = array();
										foreach ($order_counts as $key => $value) $values[] = "[$key, $value]";
										echo implode(',', $values);
									?>
								];

								for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;

								var d2 = [
									<?php
										$values = array();
										foreach ($order_amounts as $key => $value) $values[] = "[$key, $value]";
										echo implode(',', $values);
									?>
								];

								for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;

								var plot = jQuery.plot(jQuery("#placeholder"), [ { label: "<?php echo __('Number of sales','wpshop'); ?>", data: d }, { label: "<?php echo __('Sales amount','wpshop'); ?>", data: d2, yaxis: 2 } ], {
									series: {
										lines: { show: true },
										points: { show: true }
									},
									grid: {
										show: true,
										aboveData: false,
										color: '#545454',
										backgroundColor: '#fff',
										borderWidth: 2,
										borderColor: '#ccc',
										clickable: false,
										hoverable: true,
										markings: weekendAreas
									},
									xaxis: {
										mode: "time",
										timeformat: "%d %b",
										tickLength: 1,
										minTickSize: [1, "day"]
									},
									yaxes: [ { min: 0, tickSize: 1, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
									colors: ["#21759B", "#ed8432"]
								});

								function showTooltip(x, y, contents) {
									jQuery('<div id="tooltip">' + contents + '</div>').css( {
										position: 'absolute',
										display: 'none',
										top: y + 5,
										left: x + 5,
										border: '1px solid #fdd',
										padding: '2px',
										'background-color': '#fee',
										opacity: 0.80
									}).appendTo("body").fadeIn(200);
								}

								var previousPoint = null;
								jQuery("#placeholder").bind("plothover", function (event, pos, item) {
									if (item) {
										if (previousPoint != item.dataIndex) {
											previousPoint = item.dataIndex;

											jQuery("#tooltip").remove();

											if (item.series.label=="<?php echo __('Number of sales','wpshop'); ?>") {

												var y = item.datapoint[1];
												showTooltip(item.pageX, item.pageY, y+" <?php echo __('sales','wpshop'); ?>");

											} else {

												var y = item.datapoint[1].toFixed(2);
												showTooltip(item.pageX, item.pageY, y+" <?php echo wpshop_tools::wpshop_get_currency(); ?>");

											}

										}
									}
									else {
										jQuery("#tooltip").remove();
										previousPoint = null;
									}
								});

							});

							/* ]]> */
						</script>
					</div>
				</div><!-- postbox end -->

				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Orders stats', 'wpshop') ?></span></h3>
					<div class="inside">

						<?php
						// Number of active order
						/*$result=0;
						$query = $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE post_type="'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'"');
						$data = $wpdb->get_results($query, ARRAY_A);
						foreach($data as $d) {
							$postmeta = get_post_meta($d['ID'], '_order_postmeta', true);
							if(!empty($postmeta) && !empty($postmeta['order_status']) && $postmeta['order_status']!='completed')
								$result++;
						}*/
						$num_posts = wp_count_posts(WPSHOP_NEWTYPE_IDENTIFIER_ORDER);
						$result = number_format_i18n($num_posts->publish);
						?>
						<span class="alignright"><?php echo $result; ?></span>
						<label><?php _e('Number of active order', 'wpshop'); ?></label><br />

						<?php
						$result=0;
						// Number of orders the last 7 days
						$query = $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE post_type="'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'" AND post_date > (NOW()-INTERVAL 7 DAY)', '');
						$data = $wpdb->get_results($query, ARRAY_A);
						foreach($data as $d) {
							$postmeta = get_post_meta($d['ID'], '_order_postmeta', true);
							if(!empty($postmeta) && !empty($postmeta['order_key']) && substr($postmeta['order_key'],0,2)=='OR')
								$result++;
						}
						?>
						<span class="alignright"><?php echo $result; ?></span>
						<label><?php _e('Number of orders the last 7 days', 'wpshop'); ?></label><br />

						<?php
						$result=0;
						// Number of orders the last 7 days
						$query = $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE post_type="'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'" AND post_date > (NOW()-INTERVAL 7 DAY)', '');
						$data = $wpdb->get_results($query, ARRAY_A);
						foreach($data as $d) {
							$postmeta = get_post_meta($d['ID'], '_order_postmeta', true);
							if(!empty($postmeta) && !empty($postmeta['order_grand_total']))
								$result+=$postmeta['order_grand_total'];
						}
						if(count($data)>0)
							$result = round($result/count($data),2);
						?>
						<span class="alignright"><?php echo wpshop_tools::wpshop_get_currency(); ?> <?php echo $result; ?></span>
						<label><?php _e('Cart price average', 'wpshop'); ?></label>

					</div>
				</div><!-- postbox end -->

				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Quick product add', 'wpshop') ?></span></h3>
					<div class="inside">
						<?php do_shortcode('[wpshop_entities post_type="wpshop_product" fields="post_title, post_thumbnail" attribute_set_id="1" button_text="' . __('Add a new product', 'wpshop') . '" ]'); ?>
						<?php //do_shortcode('[wpshop_entities post_type="wpshop_product" fields="post_title" attribute_set_id="1" button_text="' . __('Add a new product', 'wpshop') . '" ]'); ?>
					</div>
				</div><!-- postbox end -->

			</div>
		</div>
	</div>
	<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}

?>