<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
	<div class="wps-address-item-header">
		<a href="#" class="<?php if (empty( $address_open )) : ?>wps-address-arrow-right<?php else: ?>wps-address-arrow-down<?php endif; ?>" >
			<?php if ( array_key_exists( 'address_title', $address ) ) :?>
				<?php echo $address[ 'address_title' ]; ?>
			<?php else: ?>
				<?php
					global $wpdb;
					$query = $wpdb->prepare( "SELECT name FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE id = %d", $address_type );
					echo __( $wpdb->get_var( $query ), 'wpshop' );
				?>
			<?php endif;?>
		</a>
		<span class="wps-address-actions-container alignright" >
			<a href="#" class="wps-address-icon-black wps-address-icon-edit alignleft" id="wps-address-action-edit-for-<?php echo $address_id ; ?>" title="<?php _e( 'Edit address', 'wpeo_geoloc' ); ?>" ></a>
		</span>
	</div>
	<div class="wps-address-item-content"<?php if (empty( $address_open )) : ?> style="display:none;"<?php endif; ?> >
		<div class="alignleft" >
			<?php echo wps_address::display_an_address( $address ) ; ?>
		</div>
		<?php do_action( 'wps-address-display-hook', array(
			'address_id' => $address_id,
			'address' => $address,
		) ); ?>
	</div>