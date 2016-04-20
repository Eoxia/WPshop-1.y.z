jQuery( document ).ready( function() {
	var selected_payment_mode = '';
	jQuery( document ).on( 'click', '.wps-item', function(e) {
		e.preventDefault();
		selected_payment_mode = jQuery( this ).attr( 'id' );
		jQuery( '#wps-selected-payment-method').val( selected_payment_mode );
		jQuery( '.wps-item').removeClass( 'wps-item-activ');
		jQuery( this ).addClass( 'wps-item-activ');
		
	});
	
	jQuery( document ).on( 'click' , 'input[name=wps-payment-method]', function() {
		//Active the selected method
		jQuery(this).closest( 'ul' ).children( 'li' ).removeClass('wps-activ');
		jQuery(this).closest( 'li' ).addClass('wps-activ');
	});
	
	
});