<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($discount_data['type']) && ( $discount_data['type'] == 'discount_amount' || $discount_data['type'] == 'discount_rate' ) ) : ?>
<span class="wps-badge-big-bottomLeft-rouge">
	-<?php echo wpshop_tools::formate_number( $discount_data['value'] )?><?php echo ( ($discount_data['type'] == 'discount_amount') ? wpshop_tools::wpshop_get_currency(false) : '<span>%</span>' ) ?>
</span>
<?php endif; ?>
