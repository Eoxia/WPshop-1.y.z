<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap wpshopMainWrap" >
	<div id="wpshopLoadingPicture" class="wpshopHide" ><img src="<?php echo WPSHOP_LOADING_ICON; ?>" alt="loading picture" class="wpshopPageMessage_Icon" /></div>
	<div id="wpshopMessage" class="fade below-h2 wpshopPageMessage <?php echo ( !empty( $actionInformationMessage ) ? 'wpshopPageMessage_Updated' : ''); ?>" ><?php !empty( $actionInformationMessage ) ? _e( $actionInformationMessage, 'wpshop' ) : ''; ?></div>

	<div class="pageTitle" id="pageTitleContainer" >
		<h2 ><?php _e( 'Shop dashboard', 'wpshop'); ?></h2>
	</div>
	<div id="champsCaches" class="wpshop_cls wpshopHide" ></div>
	<div class="wpshop_cls" id="wpshopMainContent" >

	<?php apply_filters( 'wps-dashboard-notice', '' ); ?>

	<div id="wpshop_dashboard">

	<div id="dashboard-widgets" class="metabox-holder">

		<div class="postbox-container" style="width:49%;">

			<div id="wpshop_right_now" class="wpshop_right_now postbox">
				<h3><span class="dashicons dashicons-info"></span> <?php _e('Right Now', 'wpshop') ?></h3>
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
									<?php
									$query = $wpdb->prepare( "SELECT COUNT( DISTINCT us.ID ) FROM {$wpdb->users} us JOIN {$wpdb->posts} ON us.ID = post_author AND post_type = %s", 'wpshop_shop_order' );
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
								$orders = get_posts($args);
								$order_completed = $order_shipped = $order_awaiting_payment = $order_denied = $order_canceled = $order_refunded = 0;
								if ($orders) {
									foreach ($orders as $o) {
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
					<div class="wpshop_cls"></div>
				</div>



			</div><!-- postbox end -->



			<div class="postbox">
				<h3 class="hndle"><span class="dashicons dashicons-performance"></span> <span><?php _e('Quick Links', 'wpshop') ?></span></h3>
				<div class="inside">
					<div class="wps-gridwrapper5-padded wpshop-gridwrapper-quick-link">
						<div><div class="wps_quick_link_icon"><a href="<?php echo admin_url( 'post-new.php?post_type=wpshop_product' ); ?>"><span class="dashicons dashicons-archive"></span></a></div><center><a href="<?php echo admin_url( 'post-new.php?post_type=wpshop_product' ); ?>"><?php _e( 'Create a new product', 'wpshop'); ?></a></center></div>
						<div><div class="wps_quick_link_icon"><a href="<?php echo admin_url( 'post-new.php?post_type=wpshop_shop_order' ); ?>"><span class="dashicons dashicons-cart"></span></a></div><center><a href="<?php echo admin_url( 'post-new.php?post_type=wpshop_shop_order' ); ?>"><?php _e( 'Create order', 'wpshop'); ?></a></center></div>
						<div><div class="wps_quick_link_icon"><a href="<?php echo admin_url( 'post-new.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ); ?>"><span class="dashicons dashicons-businessman"></span></a></div><center><a href="<?php echo admin_url( 'post-new.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ); ?>"><?php _e( 'Create a customer', 'wpshop'); ?></a></center></div>
						<div><div class="wps_quick_link_icon"><a href="<?php echo admin_url( 'admin.php?page=wpshop_statistics' ); ?>"><span class="dashicons dashicons-chart-line"></span></a></div><center><a href="<?php echo admin_url( 'admin.php?page=wpshop_statistics' ); ?>"><?php _e( 'Statistics', 'wpshop'); ?></a></center></div>
						<div><div class="wps_quick_link_icon"><a href="<?php echo admin_url( 'options-general.php?page=wpshop_option#wpshop_display_option' ); ?>"><span class="dashicons dashicons-admin-appearance"></span></a></div><center><a href="<?php echo admin_url( 'options-general.php?page=wpshop_option' ); ?>"><?php _e( 'Customize your shop', 'wpshop'); ?></a></center></div>
					</div>
				</div>
			</div><!-- postbox end -->




			<div class="postbox">
				<h3 class="hndle"><span class="dashicons dashicons-chart-pie"></span> <span><?php _e('Customers stats', 'wpshop') ?></span></h3>
				<div class="inside">
					<div class="wps-table">
						<div class="wps-table-header wps-table-row">
							<div class="wps-table-cell"><?php _e('Number of users', 'wpshop'); ?></div>
							<div class="wps-table-cell"><?php $result = count(get_users()); echo $result; ?></div>
							<div class="wps-table-cell"><?php echo ( !empty($result) ) ? '<a href="' . admin_url(). 'admin.php?page=wpshop_dashboard&download_users=users_all" role="button" id="download_all_users_contacts" class="wps-bton-first-rounded">' .__( 'Download the list', 'wpshop' ). '</a>' : ''; ?></div>
						</div>



						<div class="wps-table-header wps-table-row">
							<?php
							$query = $wpdb->prepare( "SELECT COUNT( DISTINCT user_id ) FROM {$wpdb->usermeta} JOIN {$wpdb->posts} ON post_author = user_id AND post_type = %s WHERE meta_key = %s AND ( meta_value LIKE ('%%%s%%') || meta_value LIKE ('%%%s%%') )", 'wpshop_shop_order', 'user_preferences', 's:16:"newsletters_site";i:1;', 's:16:"newsletters_site";b:1;' );
							$nbcustomers_site = $wpdb->get_var($query);
							?>
							<div class="wps-table-cell"><?php _e('Number of customers who wants to receive shop newsletters', 'wpshop'); ?></div>
							<div class="wps-table-cell"><?php echo $nbcustomers_site; ?></div>
							<div class="wps-table-cell"><?php echo ( !empty($nbcustomers_site) ) ? '<a href="' . admin_url(). 'admin.php?page=wpshop_dashboard&download_users=newsletters_site" role="button" id="download_newsletter_contacts" class="wps-bton-first-rounded">' .__( 'Download the list', 'wpshop' ). '</a>' : ''; ?></div>
						</div>

						<div class="wps-table-header wps-table-row">
							<?php
							$query = $wpdb->prepare( "SELECT COUNT( DISTINCT user_id ) FROM {$wpdb->usermeta} JOIN {$wpdb->posts} ON post_author = user_id AND post_type = %s WHERE meta_key = %s AND ( meta_value LIKE ('%%%s%%') || meta_value LIKE ('%%%s%%') )", 'wpshop_shop_order', 'user_preferences', 's:25:"newsletters_site_partners";i:1;', 's:25:"newsletters_site_partners";b:1;' );
							$nbcustomers_site_partners = $wpdb->get_var($query);
							?>
							<div class="wps-table-cell"><?php _e('Number of customers who wants to receive partners newsletters', 'wpshop'); ?></div>
							<div class="wps-table-cell"><?php echo $nbcustomers_site_partners; ?></div>
							<div class="wps-table-cell"><?php echo ( !empty($nbcustomers_site_partners) ) ? '<a href="' . admin_url(). 'admin.php?page=wpshop_dashboard&download_users=newsletters_site_partners" role="button" id="download_newsletter_partners_contacts" class="wps-bton-first-rounded">' .__( 'Download the list', 'wpshop' ). '</a>' : ''; ?></div>
						</div>
					</div>
				</div>
			</div><!-- postbox end -->

			<?php
				if( class_exists('wps_export_ctr') ) {
					$wps_export = new wps_export_ctr();
					$wps_export->wps_export_tpl();
				}
			?>


			<!--  BOX ORDERS -->
			<div class="postbox">
				<h3 class="hndle"><span class="dashicons dashicons-flag"></span> <span><?php _e('Recent Orders', 'wpshop') ?></span></h3>
				<div class="inside">
					<?php echo $this->wpshop_dashboard_orders(); ?>
				</div>
			</div><!-- postbox end -->


		</div>
		<div class="postbox-container" style="width:49%; float:right;">

			<?php
				global $current_month_offset;

				$current_month_offset = (int) date('m');
				$current_month_offset = isset( $_GET['month'] ) ? (int) $_GET['month'] : $current_month_offset;
			?>
			<div class="postbox stats" id="wpshop-stats">
				<h3 class="hndle"><span class="dashicons dashicons-chart-area"></span>
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
				<h3 class="hndle"><span class="dashicons dashicons-heart"></span> <span>WPShop : WordPress e-commerce</span></h3>
				<div class="inside">

					<div class="wps-boxed">
						<span class="wps-h5"><?php _e( 'WPShop is also...', 'wpshop'); ?></span>
						<div class="wps-gridwrapper4-padded">
							<div><a href="http://shop.eoxia.com/ecommerce/wpshop_product/assistance-wordpress/" target="_blank" title="<?php _e( 'Assistance', 'wpshop'); ?>"><img src="<?php echo WPSHOP_MEDIAS_IMAGES_URL; ?>assistance_wpshop.jpg" alt="WPSHOP Assistance" /></a><div class="wps-h5"><center><?php _e( 'Assistance', 'wpshop'); ?></center></div><center><?php _e('To assist you in your WPShop Experience', 'wpshop'); ?></center></div>
							<div><a href="http://shop.eoxia.com/boutique/shop/themes-wpshop/" target="_blank" title="<?php _e( 'WPSHOP Themes', 'wpshop'); ?>"><img src="<?php echo WPSHOP_MEDIAS_IMAGES_URL; ?>themes_wpshop.jpg" alt="WPSHOP Themes" /></a><div class="wps-h5"><center><?php _e( 'WPSHOP Themes', 'wpshop'); ?></center></div><center><?php _e('To offer to your customer all WPShop\'s powerful experience', 'wpshop'); ?></center></div>
							<div><a href="http://shop.eoxia.com/boutique/shop/modules-wpshop/" target="_blank" title="<?php _e( 'WPSHOP\'s add-ons', 'wpshop'); ?>"><img src="<?php echo WPSHOP_MEDIAS_IMAGES_URL; ?>modules_wpshop.jpg" alt="WPSHOP Assistance" /></a><div class="wps-h5"><center><?php _e( 'WPSHOP\'s add-ons', 'wpshop'); ?></center></div><center><?php _e('To boost your shop with new functions', 'wpshop'); ?></center></div>
							<div><a href="http://forums.eoxia.com/forum/wpshop" target="_blank" title="<?php _e( 'WPSHOP\'s Forum', 'wpshop'); ?>"><img src="<?php echo WPSHOP_MEDIAS_IMAGES_URL; ?>forum_wpshop.jpg" alt="Forum Assistance" /></a><div class="wps-h5"><center><?php _e( 'WPSHOP\'s Forum', 'wpshop'); ?></center></div><center><?php _e('To respond at your questions', 'wpshop'); ?></center></div>
						</div>
						<br/><br/>
						<span class="wps-h6"><?php _e( 'Be connected', 'wpshop'); ?></span>
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
						<span class="wps-h5"><?php _e( 'WPShop\'s Video Tutorials', 'wpshop')?></span>
						<div><?php $this->wpshop_rss_tutorial_videos(); ?></div>
					</div>

				</div>
			</div>



			<div class="postbox">
				<h3 class="hndle"><span class="dashicons dashicons-format-status"></span> <span><?php _e('WPShop news', 'wpshop') ?></span></h3>
				<div class="inside">
					<?php $this->wpshop_rss_feed(); ?>
				</div>
			</div><!-- postbox end -->



		</div>
	</div>
</div>
	</div>
	<div class="wpshop_cls wpshopHide" id="ajax-response"></div>
	<span class="infobulle"></span>
</div>
