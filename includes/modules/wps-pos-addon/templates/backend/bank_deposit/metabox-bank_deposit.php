<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div>
	<div class="fullSize">
		<label for="fromdate" class="width-100p inline">
			<?php _e( 'Sort by dates', 'wps-pos-i18n' ); ?>
		</label>
		<span class="inline sortbydates">
			<input id="fromdate" type="text" name="fromdate" class="sort_by_dates halfSize" placeholder="<?php _e( 'Begin date', 'wps-pos-i18n' );?>">
			<input type="text" name="todate" class="sort_by_dates halfSize" placeholder="<?php _e( 'End date', 'wps-pos-i18n' );?>">
		</span>
		<span class="inline width-360p">
			<button id="this_day" class="wps-bton-first-rounded"><?php _e( 'This day', 'wps-pos-i18n' );?></button>
			<button id="this_week" class="wps-bton-first-rounded"><?php _e( 'This week', 'wps-pos-i18n' );?></button>
			<button id="this_year" class="wps-bton-first-rounded"><?php _e( 'This year', 'wps-pos-i18n' );?></button>
		</span>
	</div>
	<div class="fullSize">
		<label for="search" class="width-100p inline">
			<?php _e( 'Filters', 'wps-pos-i18n' ); ?>
		</label>
		<span class="width-340p inline">
			<button class="method wps-bton-first-rounded" data-value="check"><?php _e( 'Check', 'wps-pos-i18n' );?></button>
			<button class="method wps-bton-first-rounded" data-value="money"><?php _e( 'Money', 'wps-pos-i18n' );?></button>
			<button class="method wps-bton-first-rounded" data-value="credit_cart"><?php _e( 'Credit card', 'wps-pos-i18n' );?></button>
		</span>
		<input class="searchInput inline" id="search" name="search" type="text" placeholder="<?php _e( 'Search', 'wps-pos-i18n' );?>">
	</div>
	<br>
	<table cellspacing="0" class="fullSize borderGrey">
		<thead>
			<tr class="bgGrey">
				<th class="padd20">
					<?php _e( 'Order', 'wps-pos-i18n' ); ?>
				</th>
				<th class="padd20">
					<?php _e( 'Date', 'wps-pos-i18n' ); ?>
				</th>
				<th class="padd20">
					<?php _e( 'Products', 'wps-pos-i18n' ); ?>
				</th>
				<th class="padd20">
					<?php _e( 'Amount', 'wps-pos-i18n' ); ?>
				</th>
				<th class="padd20">
					<?php _e( 'Method', 'wps-pos-i18n' ); ?>
				</th>
			</tr>
		</thead>
		<tbody class="">
			<tr id="model_row">
				<td class="paddLR20 alignCenter">
					<input type="hidden" value="%id%" name="ids_payment">
					%order_key%
				</td>
				<td class="paddLR20 alignCenter">
					%date%
				</td>
				<td class="paddLR20 alignCenter">
					%products%
				</td>
				<td class="paddLR20 alignCenter">
					%amount% <?php echo wpshop_tools::wpshop_get_currency(); ?>
				</td>
				<td class="paddLR20 alignCenter">
					%method%
				</td>
			</tr>
			<tr id="model_no_results" style="display: none;">
				<td class="paddLR20 " colspan="5">
					<?php _e( 'No result', 'wps-pos-i18n' ); ?>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td>
				</td>
			</tr>
		</tfoot>
	</table>
	<br>
	<div class="clearFix">
		<div class="bgGrey padd20 borderGrey alignRight">
			<span class="halfSize"><?php _e( 'Total bank deposit', 'wps-pos-i18n' ); ?> : </span>
			<span class="halfSize bold" id="total_amount" data-currency="<?php echo wpshop_tools::wpshop_get_currency(); ?>"></span>
		</div>
		<button id="download" class="wps-bton-third-rounded alignRight marginTop10p"><i class="dashicons dashicons-download"></i><?php _e( 'Download deposit', 'wps-pos-i18n' ); ?></button>
	</div>
</div>