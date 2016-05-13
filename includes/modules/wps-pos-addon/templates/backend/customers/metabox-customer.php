<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wpspos-customer wpspos-customer-selected-container" >
<?php
	/**	Vérification du choix d'un client pour la commande en cours / Check if there is a customer already selected for current order	*/
	if ( !empty( $_SESSION ) && !empty( $_SESSION[ 'cart' ] ) && !empty( $_SESSION[ 'cart' ][ 'customer_id' ] ) ) {
		$this->display_selected_customer( $_SESSION[ 'cart' ][ 'customer_id' ] );
	}
	else {
		$response[ 'output' ] = __( 'No customer has been selected, please choose a customer or create a new one before try to create a new order', 'wps-pos-i18n' );
	}
?>
</div>
<div class="wpspos-customer-list" >
	<?php $this->metabox_customers(); ?>
</div>