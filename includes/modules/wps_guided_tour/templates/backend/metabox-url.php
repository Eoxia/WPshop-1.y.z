<input type="hidden" class="wpeo-bubble-url-key" value="<?php echo !empty($meta['urls']) ? count($meta['urls']) : 1; ?>" />
<ul class="wpeo-bubble-urls">
  <?php if(!empty($meta['urls'])): ?>
    <?php foreach($meta['urls'] as $key => $url): ?>
      <?php require( wpsBubbleTemplate_ctr::get_template_part( WPS_GUIDED_DIR, WPS_GUIDED_TEMPLATES_MAIN_DIR, 'backend', 'metabox-url', 'input')); ?>
    <?php endforeach; ?>
  <?php else: ?>
    <?php $key = 0; $url = array('paramater' => '', 'value' => ''); ?>
    <?php require( wpsBubbleTemplate_ctr::get_template_part( WPS_GUIDED_DIR, WPS_GUIDED_TEMPLATES_MAIN_DIR, 'backend', 'metabox-url', 'input')); ?>
  <?php endif; ?>
</ul>
<span class="dashicons dashicons-plus-alt wpeo-bubble-add-url"></span>
