jQuery( document ).ready( function(){
//	tb_show("My Caption", ajaxurl + '?action=get_content_message&width=800&post_id=56&date=27_2015-05');

	jQuery('.wps-my-message .wps-table-row').click(function() {
		var meta_id = jQuery(this).data('id');
		var date = jQuery(this).data('date');
		var title = jQuery(this).find('.wps-message-title').text();
		var _wpnonce = jQuery( this ).data( 'nonce' );
		if(meta_id != undefined) {
			tb_show(title, ajaxurl + '?action=get_content_message&width=800&height=600&_wpnonce=' + _wpnonce + '&meta_id=' + meta_id + '&date=' + date);
		}
	});
});
