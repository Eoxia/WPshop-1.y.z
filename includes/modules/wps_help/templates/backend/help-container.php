<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
    	$('<?php echo $help_case['pointer_id']; ?>').pointer({
	        content: '<?php echo str_replace( "'", "&#039;", $pointer_content ); ?>',
	        position : {
		        edge : '<?php echo $help_case['edge']; ?>',
				at : '<?php echo $help_case['at']; ?>',
				my : '<?php echo $help_case['my']; ?>'
	        },

	        close: function() {
	        	var data = {
					action: "close_wps_help_window",
					_wpnonce: '<?php echo wp_create_nonce( "wps_ajax_close_wps_help_window" ); ?>',
					pointer_id : '<?php echo $help_id; ?>'
				};
				jQuery.post(ajaxurl, data, function(response) {}, 'json');
	        }
		}).pointer('open');
	});
	//]]>
</script>
