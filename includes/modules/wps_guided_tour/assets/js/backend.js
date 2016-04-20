jQuery( document ).ready( function(){
  jQuery(document).on("click", ".wpeo-bubble-next", function() {
    var action = jQuery(this).data('action');
    var next = jQuery(this).data('next');
    var name = jQuery(this).data('name');

    // Si c'est un lien
    if(action === 'link') {
      jQuery.post( ajaxurl, {
        pointer: name,
        action: 'dismiss-wp-pointer'
      },
      function() {
        window.location.href = next;
      });
    }
    else if(action === 'bubble') {
      jQuery(".wpeo_bubble_" + next).fadeIn();
      jQuery(this).closest('.wp-my-pointer').fadeOut();
      jQuery.post( ajaxurl, {
        "pointer": name,
        "action": 'dismiss-my-pointer',
      }, function() {});
    }
  });
  /** Clique sur reset button */
  jQuery(".wpeo-reset-bubble-all-user").click(function() {
    var data = {
      "action": "reset-bubble-all-user",
      "post_ID": jQuery(this).data('id'),
    };

    jQuery.post(ajaxurl, data, function() {});
  });
  /** Change le select du type next */
  jQuery('.wpeo-bubble-select-type-next').on('change', function() {
    jQuery('.wpeo-next').attr('disabled', true).css('display', 'none');
    jQuery('.wpeo-bubble-next-' + jQuery(this).val()).attr('disabled', false).css('display', 'inline');
  });
  /** Add url */
  jQuery('.wpeo-bubble-add-url').click(function() {
    jQuery('.wpeo-bubble-urls').append('<li>' + jQuery('.wpeo-bubble-urls li:last').html() + '</li>');
    var current_key = jQuery('.wpeo-bubble-url-key').val();
    jQuery('.wpeo-bubble-urls li:last').find('h4 span').html(current_key);
    current_key++;
    jQuery('.wpeo-bubble-url-key').val(current_key);
  });
});