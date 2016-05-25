jQuery( document ).ready( function() {
	/** Sort pictures list **/
	function sort() {
		jQuery( '#product_media_list' ).sortable(
			{
				stop : function() {
					var output = '';
					jQuery( '#product_media_list li').each( function() {
						var id = jQuery( this ).attr( 'id' );
						id = id.replace( 'media_', '' );
						output += id+',';
						jQuery( '#product_media_indicator' ).val( output );
					});
				}
			});
	}

	/** Display Picture **/
	function display_pictures() {
		var data = {
				action: "display_pictures_in_backend",
				_wpnonce: jQuery( "#selected_media_container" ).data( 'nonce' ),
				media_id : jQuery( '#product_media_indicator' ).val()
				};
		jQuery.post(ajaxurl, data, function(response){
			if( response['status'] ) {
				jQuery( '#selected_media_container' ).html( response['response'] );
				sort();
			}
		}, 'json');
	}

	sort();

	/** Upload Picture list **/
	jQuery( document ).on( 'click', '#upload_wps_product_media', function(e) {
		e.preventDefault();
		var uploader = wp.media( {
			title : jQuery( '#upload_wps_product_media' ).html(),
			multiple : true
		}).on('select', function() {
			var attachments = [];
			var selected_picture = uploader.state().get( 'selection' );
			selected_picture.map( function( attachment ) {
				attachment = attachment.toJSON();
				attachments.push( attachment );
			});
			var output = jQuery( '#product_media_indicator' ).val();
			jQuery( attachments ).each( function(k, v) {
				output += v.id + ',';
			});
			jQuery( '#product_media_indicator' ).val( output );
			display_pictures();
		}).open();
	});

	/** Delete Picture **/
	jQuery( document ).on( 'click', '.delete-picture', function(e) {
		e.preventDefault();
		var id = jQuery( this).closest( 'li' ).attr( 'id' );
		id = id.replace( 'media_', '' );
		var media_id =  jQuery( '#product_media_indicator' ).val();
		media_id = media_id.replace( id + ',', '' );
		jQuery( '#product_media_indicator' ).val( media_id );
		display_pictures();
	});

});
