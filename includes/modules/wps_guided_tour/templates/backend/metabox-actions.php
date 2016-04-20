<label for="use_next_button"><span><?php _e("Use next button", self::$name_i18n); ?></span>
  <input type="checkbox" name="meta[actions][use_next_button]" id="use_next_button" <?php echo (!empty($meta['actions']['use_next_button'])) ? 'checked="checked"' : ''; ?> >
</label>

<label for="type_next"><span><?php _e("Type next", self::$name_i18n); ?></span>
  <select class="wpeo-bubble-select-type-next" name="meta[actions][type_next]">
    <?php if(!empty($array_type_next)): ?>
      <?php foreach($array_type_next as $type_next): ?>
        <option <?php selected($type_next, !empty($meta['actions']['type_next']) ? $meta['actions']['type_next'] : '', true); ?> value="<?php echo $type_next; ?>"><?php echo $type_next; ?></option>
      <?php endforeach; ?>
    <?php endif; ?>
  </select>
</label>

<label for="next"><span><?php _e("Next action", self::$name_i18n); ?></span>
  <!-- For link -->
  <input <?php echo (!empty($meta) && !empty($meta['actions']) && !empty($meta['actions']['type_next']) && 'link' != $meta['actions']['type_next']) ? 'style="display: none;" disabled' : ''; ?> type="text" id="next" class="wpeo-next wpeo-bubble-next-link" name="meta[actions][next]" value="<?php echo !empty($meta['actions']['next']) ? $meta['actions']['next'] : ""; ?>" />

  <!-- For Bubble -->
  <?php if(!empty($array_bubbles)): ?>
    <select <?php echo ((!empty($meta) && !empty($meta['actions']) && !empty($meta['actions']['type_next']) && 'bubble' != $meta['actions']['type_next'])) || empty($meta['actions']) ? 'style="display: none;" disabled' : ''; ?> class="wpeo-next wpeo-bubble-next-bubble"  id="next" name="meta[actions][next]">
      <?php foreach($array_bubbles as $bubble): ?>
        <option <?php selected($bubble->post_name, !empty($meta['actions']['next']) ? $meta['actions']['next'] : '', true); ?> value="<?php echo $bubble->post_name; ?>"><?php echo $bubble->post_title . ' (' . $bubble->post_name . ')'; ?></option>
      <?php endforeach; ?>
    </select>
  <?php else: ?>
    <p><?php _e("No bubble found", self::$name_i18n); ?></p>
  <?php endif; ?>
</label>
