<li>
  <h4><?php _e("Url", self::$name_i18n); ?> #<span><?php echo $key; ?></span></h4>
  <label for="paramater"><span><?php _e("Paramater", self::$name_i18n); ?></span>
    <input type="text" id="paramater" name="meta[urls][paramater][]" value="<?php echo $url['paramater']; ?>" />
  </label>

  <label for="value"><span><?php _e("Value", self::$name_i18n); ?></span>
    <input type="text" id="value" name="meta[urls][value][]" value="<?php echo $url['value']; ?>" />
  </label>
</li>
