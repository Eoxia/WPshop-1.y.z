<label for="page"><span><?php _e("Page", self::$name_i18n); ?></span>
  <select class="wpeo-bubble-select-page" name="meta[position][page]">
    <?php if(!empty($array_pages)): ?>
      <?php foreach($array_pages as $key => $page): ?>
        <option <?php selected($key, !empty($meta['position']['page']) ? $meta['position']['page'] : 'menu-dashboard', true); ?> value="<?php echo $key; ?>"><?php echo str_replace('0', '', $page . ' (' . $key . ')'); ?></option>
      <?php endforeach; ?>
    <?php endif; ?>
  </select>
</label>

<label for="filter"><span><?php _e("Filter" ,self::$name_i18n); ?></span>
  <input type="text" id="filter" name="meta[position][filter]" value="<?php echo !empty($meta['position']['filter']) ? $meta['position']['filter'] : ""; ?>" />
</label>

<label for="anchor_id"><span><?php _e("Anchor (ID or Class)", self::$name_i18n); ?></span>
  <input type="text" id="anchor_id" name="meta[position][anchor_id]" value="<?php echo !empty($meta['position']['anchor_id']) ? $meta['position']['anchor_id'] : ""; ?>" />
</label>

<label for="position_x"><span><?php _e("Position x", self::$name_i18n); ?></span>
  <input type="number" id="position_x" name="meta[position][position_x]" value="<?php echo !empty($meta['position']['position_x']) ? $meta['position']['position_x'] : "0"; ?>" />
</label>

<label for="position_y"><span><?php _e("Position y", self::$name_i18n); ?></span>
  <input type="number" id="position_y" name="meta[position][position_y]" value="<?php echo !empty($meta['position']['position_y']) ? $meta['position']['position_y'] : "0"; ?>" />
</label>
