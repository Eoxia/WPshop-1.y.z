<?php if ( !defined( 'ABSPATH' ) ) exit;
 if(!empty($this->array_bubble)): ?>
  <script type="text/javascript">
    /* <![CDATA[ */
    ( function($) {
      var first_pointer = "";
      jQuery(document).ready(function() {
        <?php foreach($this->array_bubble as $key => $bubble): ?>
          <?php
          $meta = get_post_meta($bubble->ID, $this->post_metakey, true);
          if( !empty( $meta ) ) :
          $data_next = (!empty($meta) && !empty($meta['actions']['next'])) ? $meta['actions']['next'] : '';
          $button_next = (!empty($meta) && !empty($meta['actions']['use_next_button'])) ? '<input data-name="wpeo_bubble_' . $bubble->post_name . '" data-action="' .  $meta['actions']['type_next'] . '" data-nonce="' . wp_create_nonce( 'dismiss_my_pointer' ) . '" data-next="' . $data_next . '" type="button" class="button-primary wpeo-bubble-next" value="' . __('Next', self::$name_i18n) . '" />' : '';
          $content = $this->format_string_php_to_js("<h3>" . $bubble->post_title . "</h3><p>" . $bubble->post_content . "<br />" . $button_next . "</p>");
          ?>
          <?php $meta = get_post_meta($bubble->ID, $this->post_metakey, true); ?>
          $('<?php echo $meta['position']["anchor_id"]; ?>').pointer({
            content: '<?php echo $content; ?>',
            pointerClass: 'wpeo_bubble_<?php echo $bubble->post_name; ?> wp-my-pointer',
            position: {
              edge: "top",
              align: "left",
            },
            close: function() {
              jQuery.post( ajaxurl, {
                pointer: 'wpeo_bubble_<?php echo $bubble->post_name; ?>',
                action: 'dismiss-wp-pointer'
              });
            }
          }).pointer('open');
          /** Unset */
          <?php
          unset($data_next);
          unset($button_next);
          ?>
          /** Get first pointer */
          if("" === first_pointer && "" != $('.wpeo_bubble_<?php echo $bubble->post_name; ?>').text()) {
            first_pointer = '<?php echo $bubble->post_name; ?>';
          }
          /** The offset */
          var position_x = <?php echo (!empty($meta['position']['position_x'])) ? $meta['position']['position_x'] : 0; ?>;
          var position_y = <?php echo (!empty($meta['position']['position_y'])) ? $meta['position']['position_y'] : 0; ?>;
          var left = parseInt(jQuery('.wpeo_bubble_<?php echo $bubble->post_name; ?>').css('left')) - position_x;
          var top = parseInt(jQuery('.wpeo_bubble_<?php echo $bubble->post_name; ?>').css('top')) - position_y;
          jQuery('.wpeo_bubble_<?php echo $bubble->post_name; ?>').css('left', left).css('top', top);
        <?php endif; endforeach; ?>
        jQuery('.wp-my-pointer').hide();
        /** Display the first pointer */
        if(first_pointer != "") {
          jQuery('.wpeo_bubble_' + first_pointer).fadeIn();
        }
      });
    })(jQuery);
    /* ]]> */
  </script>
<?php endif; ?>
