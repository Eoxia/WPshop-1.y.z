<?php if ( !defined( 'ABSPATH' ) ) exit;
$permalink_option = get_option( 'permalink_structure' );
$account_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_myaccount_page_id' ) );
$account_dashboard_part = !empty( $_GET['account_dashboard_part'] ) ? sanitize_text_field( $_GET['account_dashboard_part'] ) : '';
?>

<?php $user_id = get_current_user_id(); ?>

<?php if ( 0 !== $user_id ) :
	$account_user = get_userdata( $user_id ); ?>
<div class="wps-user-dashboard" >
	<?php do_action( 'wps_user_dashboard_header', $user_id, $account_user ); ?>

	<div class="wps-user-info has-sub-menu" >
		<span class="wps-user-name">
			<?php echo get_avatar( $user_id, 40 ); ?>
			<strong><?php echo esc_html( $account_user->data->user_login ); ?></strong>
		</span>
		<ul class="sub-menu" >
		<?php
		if ( function_exists( 'current_user_switched' ) ) :
			$old_user = current_user_switched();
			if ( $old_user && $url = user_switching::maybe_switch_url( $old_user ) ) :
				printf( '<li><a href="%s">Switch back</a></li>', esc_url( $url ) );
			endif;
		endif;
		?>
			<li><a href="<?php echo esc_url( wp_logout_url( site_url() ) ); ?>"><i class="wps-icon-power"></i>&nbsp;<?php esc_html_e( 'Se déconnecter', 'eoxia' ); ?></a></li>
		</ul>
	</div>
</div>
<?php endif; ?>

<section class="wps-section-account">
	<div class="wps-section-taskbar">
		<ul>
			<li class="<?php echo ( ( empty($account_dashboard_part) || ( !empty($account_dashboard_part) && $account_dashboard_part == 'account' ) ) ? 'wps-activ' : '' ); ?>">
				<a data-target="menu1" href="<?php echo get_permalink($account_page_id).( (!empty($permalink_option) ? '?' : '&' ).'account_dashboard_part=account' ); ?>" title="" class="">
					<i class="wps-icon-user"></i>
					<span><?php _e( 'Account', 'wpshop'); ?></span>
				</a>
			</li>
			<li class="<?php echo ( ( !empty($account_dashboard_part) && $account_dashboard_part == 'address') ? 'wps-activ' : '' ); ?>">
				<a href="<?php echo get_permalink($account_page_id).( (!empty($permalink_option) ? '?' : '&' ).'account_dashboard_part=address' ); ?>" title="" class="">
					<i class="wps-icon-address"></i>
					<span><?php _e( 'Addresses', 'wpshop'); ?></span>
				</a>
			</li>
			<li class="<?php echo ( ( !empty($account_dashboard_part) && $account_dashboard_part == 'order') ? 'wps-activ' : '' ); ?>">
				<a href="<?php echo get_permalink($account_page_id).( (!empty($permalink_option) ? '?' : '&' ).'account_dashboard_part=order' ); ?>" title="" class="">
					<i class="wps-icon-truck"></i>
					<span><?php _e( 'Orders', 'wpshop'); ?></span>
				</a>
			</li>
			<li class="<?php echo ( ( !empty($account_dashboard_part) && $account_dashboard_part == 'coupon') ? 'wps-activ' : '' ); ?>">
				<a href="<?php echo get_permalink($account_page_id).( (!empty($permalink_option) ? '?' : '&' ).'account_dashboard_part=coupon' ); ?>" title="" class="">
					<i class="wps-icon-promo"></i>
					<span><?php _e( 'Coupons', 'wpshop'); ?></span>
				</a>
			</li>
			<?php $opinion_option = get_option( 'wps_opinion' );
			if( !empty($opinion_option) && !empty($opinion_option['active']) ) : ?>
			<li class="<?php echo ( ( !empty($account_dashboard_part) && $account_dashboard_part == 'opinion') ? 'wps-activ' : '' ); ?>">
				<a href="<?php echo get_permalink($account_page_id).( (!empty($permalink_option) ? '?' : '&' ).'account_dashboard_part=opinion' ); ?>" title="" class="">
					<i class="wps-icon-chat"></i>
					<span><?php _e( 'Opinions', 'wpshop'); ?></span>
				</a>
			</li>
			<?php endif; ?>
			<li class="<?php echo ( ( !empty($account_dashboard_part) && $account_dashboard_part == 'messages') ? 'wps-activ' : '' ); ?>">
				<a href="<?php echo get_permalink($account_page_id).( (!empty($permalink_option) ? '?' : '&' ).'account_dashboard_part=messages' ); ?>" title="" class="">
					<i class="wps-icon-email"></i>
					<span><?php _e( 'Messages', 'wpshop' ); ?></span>
				</a>
			</li>
			<?php echo apply_filters('wps_my_account_extra_part_menu', ''); ?>
		</ul>
	</div>
	<div class="wps-section-content">
		<div class="wps-activ" id="wps_dashboard_content" data-nonce="<?php echo wp_create_nonce( 'wps_refresh_add_opinion_list' ); ?>">
			<?php echo $content; ?>
		</div>
	</div>
</section>
