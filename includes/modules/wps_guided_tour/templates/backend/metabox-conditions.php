<label for="option_name"><span><?php _e("Option name", self::$name_i18n); ?></span>
  <input type="text" id="option_name" name="meta[conditions][option_name]" value="<?php echo !empty($meta['conditions']['option_name']) ? $meta['conditions']['option_name'] : ""; ?>" />
</label>

<label for="data_name"><span><?php _e("Data name", self::$name_i18n); ?></span>
  <input type="text" id="data_name" name="meta[conditions][data_name]" value="<?php echo !empty($meta['conditions']['data_name']) ? $meta['conditions']['data_name'] : ""; ?>" />
</label>

<label for="option_value"><span><?php _e("Option value", self::$name_i18n); ?></span>
  <input type="text" id="option_value" name="meta[conditions][option_value]" value="<?php echo !empty($meta['conditions']['option_value']) ? $meta['conditions']['option_value'] : ""; ?>" />
</label>
