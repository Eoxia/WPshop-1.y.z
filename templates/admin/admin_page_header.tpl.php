<div class="wrap wpshopMainWrap" >
	<div id="wpshopLoadingPicture" class="wpshopHide" ><img src="<?php echo WPSHOP_LOADING_ICON; ?>" alt="loading picture" class="wpshopPageMessage_Icon" /></div>
	<div id="wpshopMessage" class="fade below-h2 wpshopPageMessage <?php echo (($actionInformationMessage != '') ? 'wpshopPageMessage_Updated' : ''); ?>" ><?php _e($actionInformationMessage, 'wpshop'); ?></div>

	<div class="icon32 wpshopPageIcon icon32-<?php echo $current_page_slug; ?>" ><?php if(!empty($pageIcon)): ?><img alt="<?php _e($iconAlt, 'wpshop'); ?>" src="<?php _e($pageIcon); ?>" title="<?php _e($iconTitle, 'wpshop'); ?>" /><?php else: ?>&nbsp;<?php endif; ?></div>

	<div class="pageTitle" id="pageTitleContainer" >
		<h2 ><?php _e($pageTitle, 'wpshop'); ?>
		<?php if($hasAddButton): ?><a href="<?php echo $addButtonLink ?>" class="button add-new-h2" ><?php _e('Add', 'wpshop') ?></a><?php endif; ?>
		</h2>
	</div>
	<div id="champsCaches" class="wpshop_cls wpshopHide" ></div>
	<div class="wpshop_cls" id="wpshopMainContent" >