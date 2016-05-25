<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wps-opinion-error-container"></div>
<form method="post" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" id="wps-add-opinion-form">

	<div class="wps-row wps-gridwrapper2-padded">

		<div class="wps-form-group">
			<input type="hidden" name="action" value="wps-opinion-save-form"/>
			<?php wp_nonce_field( 'wps_opinion_save_form' ); ?>
			<input type="hidden" name="wps-opinion-product-id" value="<?php echo $pid; ?>"/>
			<label for="wps-opinion-comment"><?php _e( 'Your opinion', 'wps_opinion' ); ?></label>
			<div class="wps-form"><textarea id="wps-opinion-comment" name="wps-opinion-comment"></textarea></div>
		</div>
		<div class="wps-form-group">
			<div>
				<input type="hidden" name="action" value="wps-opinion-save-form"/>
				<label for="wps-opinion-comment"><?php _e( 'Your rate', 'wps_opinion' ); ?></label>
				<div class="wps-form">
					<select name="wps-opinion-rate" id="wps-opinion-rate" >
						<?php for( $i = 0; $i <= 5 ; $i++) { ?>
						<option value="<?php echo $i; ?>" ><?php echo $i; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		   <div>
		   	<label for="wps-opinion-stars"><?php _e( 'Star rate', 'wps_opinion' ); ?></label>
			   	<div class="wps-form" id="wps-opinion-star-container" data-nonce="<?php echo wp_create_nonce( 'wps_update_opinion_star_rate' ); ?>">
			   		<?php
			   			$wps_opinion_ctr = new wps_opinion_ctr();
			   			echo $wps_opinion_ctr->display_stars( 0 );
			   		?>
			   	</div>
		   </div>
	   </div>

	</div>

</form>
<div class="wps-form-group">
	<button class="wps-bton-first-rounded" id="wps-save-opinion"><?php _e( 'Save your opinion', 'wps_opinion' ); ?></button>
</div>
