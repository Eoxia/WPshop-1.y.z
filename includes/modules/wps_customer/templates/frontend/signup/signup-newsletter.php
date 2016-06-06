<?php if ( !defined( 'ABSPATH' ) ) exit; ?>
<?php if( !empty( $wpshop_cart_option['display_newsletter']['site_subscription'][0] ) && ( $wpshop_cart_option['display_newsletter']['site_subscription'][0] == 'yes' ) ) : ?>
	<div class="wps-form-group field-newsletters_site">
		<input id="newsletters_site" type="checkbox" name="newsletters_site" <?php echo ( (!empty($user_preferences['newsletters_site']) && $user_preferences['newsletters_site']== 1 ) ? ' checked="checked"' : null); ?>>
		<label for="newsletters_site"><?php _e('I want to receive promotional information from the site','wpshop'); ?></label>
	</div>
<?php endif; ?>
<?php if ( !empty( $wpshop_cart_option['display_newsletter']['partner_subscription'][0]) && ( $wpshop_cart_option['display_newsletter']['partner_subscription'][0] == 'yes' ) ) : ?>
	<div class="wps-form-group field-newsletters_site_partners">
		<input id="newsletters_site_partners" type="checkbox" name="newsletters_site_partners" <?php echo ((!empty($user_preferences['newsletters_site_partners']) && $user_preferences['newsletters_site_partners']==1 ) ? ' checked="checked"' : null); ?>/>
		<label for="newsletters_site_partners"><?php _e('I want to receive promotional information from partner companies','wpshop'); ?></label>
	</div>
<?php endif; ?>