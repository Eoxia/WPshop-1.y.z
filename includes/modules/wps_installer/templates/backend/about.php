<?php if ( !defined( 'ABSPATH' ) ) exit;

$sub_page = !empty( $_GET['sub-page'] ) ? sanitize_text_field( $_GET['sub-page'] ) : '';

?>
<div class="wrap about-wrap wps-about-wrap">
	<h1><?php _e( 'Welcome to WPShop', 'wpshop'); ?></h1>
	<div class="about-text"><?php _e( 'Thanks for using WPShop as your online shop solution. We hope that you will enjoy the features we develop for you.', 'wpshop'); ?></div>
	<div class="wp-badge" ><?php printf( __( 'Version %s', 'wpshop'), WPSHOP_VERSION ); ?></div>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab<?php echo !empty( $sub_page ) ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( "admin.php?page=wpshop_about" ); ?>"><?php _e( 'Introduction to WPShop', 'wpshop'); ?></a>
		<!--
			<a class="nav-tab<?php echo ( !empty( $sub_page ) && ( "credits" == $sub_page ) ? ' nav-tab-active' : '' ); ?>" href="<?php echo admin_url( "admin.php?page=wpshop_about&sub-page=credits" ); ?>"><?php _e( 'Credits', 'wpshop'); ?></a>
		 -->
	</h2>

<?php $about_sub_page = ''; ?>
<?php if ( !empty( $sub_page ) ) : ?>
	<?php $about_sub_page = sanitize_text_field( $sub_page ); ?>
<?php else : ?>
	<?php $about_sub_page = 'introduction'; ?>
<?php endif; ?>

<?php if ( !empty( $about_sub_page ) ) : ?>
	<?php require( wpshop_tools::get_template_part( WPS_INSTALLER_DIR, WPSINSTALLER_TPL_DIR, "backend", "about", $about_sub_page ) ); ?>
<?php endif; ?>

</div>
