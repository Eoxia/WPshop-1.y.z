<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<input type="button" data-id="<?php echo get_the_ID(); ?>" data-nonce="<?php echo wp_create_nonce( 'reset_bubble_all_user' ); ?>" class="button-secondary wpeo-reset-bubble-all-user" value="<?php _e("Reset this bubble for all users", self::$name_i18n); ?>" />
